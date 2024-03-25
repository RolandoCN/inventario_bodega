<?php

namespace App\Http\Controllers\Bodega;
use App\Http\Controllers\Controller;
use App\Models\Personal\Especialidad;
use \Log;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator; 
use App\Models\Bodega\Paquete;
use App\Models\Bodega\DetallePaquete;
use App\Models\Bodega\Medicamento; 
use App\Models\Bodega\Insumo; 
use App\Models\Bodega\TipoComprobanteOld;
use App\Models\Bodega\Comprobante;
use App\Models\Bodega\DetalleComprobante;
use App\Models\Bodega\PedidoBodegaGral;
use App\Models\Bodega\ProductoBodega;
use App\Models\Bodega\BodegaUsuario;

class SolicitudPaqueteDialController extends Controller
{
    public function index(){

        $paquetes= DB::connection('pgsql')->table('bodega.paquetes')
        ->where('estado','A')
        ->get();
        return view('gestion_paquetes.solicita_farmacia',[
            "paquetes"=>$paquetes
        ]);
    }

    public function validaPaquete($id, $cantidad){
        // $detalle_paquetes= DetallePaquete::with('medicamento','insumo')->where('id_paquete',$id)
        // ->where('estado','A')
        // ->get();

        $valida_paquetes=DB::table('bodega.detalle_paquetes as de')
        ->leftJoin('bodega.medicamentos as m', 'm.coditem','de.id_item')
        ->leftJoin('bodega.insumo as i', 'i.codinsumo','de.id_item')
        ->where('id_paquete',$id)
        ->where('estado','A')
        ->select('de.iddetalle_paq','de.id_item','de.tipo','m.stock_farm_dialisis as stock_farm_med','i.stock_farm_dialisis as stock_farm_ins',DB::raw("CONCAT(m.nombre,' - ', m.concentra,' - ', m.forma,' - ', m.presentacion) AS descripcion_med"),DB::raw("CONCAT(i.insumo) AS descripcion_ins"),'de.cantidad')
        ->orderBy('descripcion_ins','asc')
        ->orderBy('descripcion_med','asc')
        ->get();

        $detalle_incumple=[];
        foreach($valida_paquetes as $key => $data){
            $cantidad_solic_x_item=0;

            if($data->tipo=="Insumo"){
                $nombre_item=$data->descripcion_ins;
                $cantidad_item=$data->cantidad;

                $cantidad_stock_farmacia=$data->stock_farm_ins;

                $cantidad_solic_x_item=$cantidad * $data->cantidad;

            }else{
                $nombre_item=$data->descripcion_med;
                $cantidad_item=$data->cantidad;

                $cantidad_stock_farmacia=$data->stock_farm_med;

                $cantidad_solic_x_item=$cantidad * $data->cantidad;
               
            }
            
            if($cantidad_solic_x_item > $cantidad_stock_farmacia){
                $valida_paquetes[$key]->info="N";
            }else{
                $valida_paquetes[$key]->info="S";
            }

            $valida_paquetes[$key]->cantidad_solic=$cantidad_solic_x_item;
            $valida_paquetes[$key]->cantidad_stock_farmacia=$cantidad_stock_farmacia;
        }

        return [
            'error'=>false,
            'resultado'=>$valida_paquetes
        ];
    }

    //SOLICITUD PAQUETES DESDE DIALISIS A FARMACIA
    public function guardarSolicitud(Request $request){
        
        $array_paquete=$request->idpaquete_selecc;
        $cantidad=$request->cantidad;
       
        if(sizeof($array_paquete)==0){
            return (['mensaje'=>'Debe seleccionar al menos un paquete','error'=>true]);
        }

        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{
               
                $tipocomp_old= TipoComprobanteOld::where('idtipocom',18)//TRANSFERENCIA PAQUTE
                ->orderBy('idtipocom','desc')
                ->first();

                //registramos la cabecera
                $ultimo=Comprobante::orderby('idcomprobante','desc')->first();
                if(is_null($ultimo)){
                    $suma=1;
                }else{
                    $suma=$ultimo->idcomprobante+1;
                }
                $comprobante=new Comprobante();
                $comprobante->idcomprobante=$suma;
                $comprobante->idtipo_comprobante=$tipocomp_old->idtipocom;
                $comprobante->secuencial=$tipocomp_old->numcom+1;
                $comprobante->descripcion=$tipocomp_old->razoncom;
                $comprobante->fecha_hora=date('Y-m-d H:i:s');
                $comprobante->fecha=date('Y-m-d');
                $comprobante->idbodega=31;
                $comprobante->observacion=$request->motivo;             
                $comprobante->id_usuario_ingresa=auth()->user()->id;
                $comprobante->area=auth()->user()->persona->id_area;

                if($comprobante->save()){

                    //datos detalle
                    $cant=$request->cantidad;
                    //recorremos los paquetes seleccionados
                    foreach($request->idpaquete_selecc as $key=> $idpaq){

                        //validamos los stock disponibles en farmacia de cada uno de los items de los paquetes
                        $validaPaq=$this->validaPaquete($idpaq, $cant[$key]);
                       
                        //caso de error al consultar
                        if($validaPaq['error']==true){
                            DB::connection('pgsql')->rollback();
                            return (['mensaje'=>'No se pudo comprobar la existencia en stock, intentelo mas tarde','error'=>true]);
                        }else{
                            // recorremos cada uno de los items
                            foreach($validaPaq['resultado'] as $detalle){
                               
                                //el stock es menor a lo solicitado
                                if($detalle->info=="N"){
                                    //insumos
                                    if($detalle->id_item >=30000){
                                        $stock_max=$detalle->stock_farm_ins;
                                        $nomb_item=$detalle->descripcion_ins;
                                    }else{ //medicina
                                        $stock_max=$detalle->stock_farm_med;
                                        $nomb_item=$detalle->descripcion_med;
                                    }
                                   
                                    DB::connection('pgsql')->rollback();
                                    return (['mensaje'=>'El stock del '.$request->nombrepaquete[$key].' del item '.$nomb_item.' es '.$stock_max.' y el solicitado es '.$cant[$key] * $detalle->cantidad,'error'=>true ,'idpaquete'=>$idpaq,'paquete'=>$request->nombrepaquete[$key]]);
                                
                                }else{
                                    //de acuerdo al item asignamos la bodega a buscar
                                    if($detalle->id_item >=30000){
                                        //insumo dialisis
                                        $idbodega_sel=21;
                                    }else{
                                        //medicina dialisis
                                        $idbodega_sel=20;
                                    }
                                    $fecha_Actual=date('Y-m-d');
                                    //ordenamos de acuerdo a la fecha de expiracion
                                    $prodbod=DB::table('bodega.prodxbod as pb')
                                    ->leftJoin('bodega.existencia as e', 'e.idbodprod','pb.idbodprod')
                                    ->where('pb.idbodega',$idbodega_sel)
                                    ->where('existencia','>=',$cant[$key])
                                    ->where('idprod',$detalle->id_item)
                                    ->whereDate('e.fecha_caducidad','>=',$fecha_Actual)
                                    ->orderBy('e.fecha_caducidad','asc')
                                    ->select('pb.idbodprod','pb.precio','pb.idbodega','e.lote','e.fecha_caducidad','e.fecha_elaboracion','pb.existencia')
                                    ->first();
                                    
                                    if(!is_null($prodbod)){

                                        //registramos los detalles
                                        $ultimo=DetalleComprobante::orderBy('iddetalle_comprobante','desc')->first();
                                        if(is_null($ultimo)){
                                            $suma=1;
                                        }else{
                                            $suma=$ultimo->iddetalle_comprobante+1;
                                        }
                                        $total_item=0;
                                        $total_item=$prodbod->precio * $cant[$key];
                                        $detalles=new DetalleComprobante();
                                        $detalles->iddetalle_comprobante=$suma;
                                        $detalles->idcomprobante=$comprobante->idcomprobante;
                                        $detalles->id_item=$detalle->id_item;
                                        $detalles->id_bodega=$idbodega_sel;
                                        $detalles->idbodprod=$prodbod->idbodprod;
                                        $detalles->cantidad=$cant[$key];
                                        $detalles->precio=number_format(($prodbod->precio),4,'.', '');
                                        $detalles->descuento=0;
                                        $detalles->total=number_format(($total_item),4,'.', '');
                                        $detalles->iva=0;
                                        $detalles->fecha=date('Y-m-d H:i:s');
                                        $detalles->save(); 

                                        $ultimo=PedidoBodegaGral::orderBy('idpedido_bod_gral','desc')->first();
                                        if(is_null($ultimo)){
                                            $suma=1;
                                        }else{
                                            $suma=$ultimo->idpedido_bod_gral+1;
                                        }

                                        //registramos el pedido
                                        $pedido_temp=new PedidoBodegaGral();
                                        $pedido_temp->idpedido_bod_gral=$suma;
                                        $pedido_temp->iddetallecomprobante=$detalles->iddetalle_comprobante;
                                        $pedido_temp->lote=$prodbod->lote;
                                        $pedido_temp->fecha_caducidad=$prodbod->fecha_caducidad;
                                        $pedido_temp->fecha_elabora=$prodbod->fecha_elaboracion;
                                        $pedido_temp->cantidad_pedida=$cant[$key];
                                        $pedido_temp->idbodega=$detalles->id_bodega;
                                        $pedido_temp->id_solicita=auth()->user()->id;
                                        $pedido_temp->fecha_solicita=date('Y-m-d H:i:s');
                                        $pedido_temp->idbodpro=$prodbod->idbodprod;
                                        $pedido_temp->iditem=$detalle->id_item;
                                        $pedido_temp->id_paquete=$idpaq;
                                        $pedido_temp->estado="Solicitado";
                                        $pedido_temp->save();                       
                                       

                                    }else{
                                        if($detalle->id_item >=30000){
                                            $stock_max=$detalle->stock_farm_ins;
                                            $nomb_item=$detalle->descripcion_ins;

                                        }else{ //medicina
                                            $stock_max=$detalle->stock_farm_med;
                                            $nomb_item=$detalle->descripcion_med;
                                        }

                                        DB::connection('pgsql')->rollback();
                                     
                                        return (['mensaje'=>'El stock del '.$request->nombrepaquete[$key].' del item '.$nomb_item.' es insuficiente para completar el pedido de los paquetes ','error'=>true ]);
                                    }

                                }

                            }
                        }
                    }

                    $tipocomp_old->numcom=$comprobante->secuencial;
                    $tipocomp_old->save();

                    //si tofdo ok el comprobante se crea
                    $comprobante_crear=Comprobante::find($comprobante->idcomprobante);
                    $comprobante_crear->estado="Activo";
                    $comprobante_crear->codigo_old="PedidoAFarm";//pedido
                    $comprobante->subtotal=$request->total_suma;
                    $comprobante->total=$request->total_suma; 
                    $comprobante_crear->save();

                    return (['mensaje'=>'Informacion ingresada exitosamente','error'=>false]);

                }        

            } catch (\Throwable $e) {
                DB::connection('pgsql')->rollback();
                Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
                return (['mensaje'=>'Ocurrió un error,intentelo más tarde','error'=>true]); 
            }
        });
        return ($transaction);
    }

    public function listado(){

        $paquetes= DB::connection('pgsql')->table('bodega.paquetes')
        ->where('estado','A')
        ->get();
        return view('gestion_paquetes.solicita_listado',[
            "paquetes"=>$paquetes
        ]);
    }

    public function listadoPedido($ini,$fin){
       
        try{
            
            $pedidos= DB::connection('pgsql')->table('bodega.comprobante as comp')
            ->leftJoin('public.users as usu', 'usu.id','comp.id_usuario_ingresa')
            ->leftJoin('inventario.persona as per', 'per.idper','usu.id_persona')
            ->leftJoin('bodega.area as a', 'a.id_area','per.id_area')
            ->leftJoin('bodega.per_perfil_usuario as per_usu', 'per_usu.id_usuario','usu.id')
            ->leftJoin('bodega.per_perfil as perf', 'perf.id_perfil','per_usu.id_perfil')
            ->where(function($query)use($ini, $fin){
                $query->whereBetween('comp.fecha',[$ini, $fin]);
            })
            ->leftJoin('bodega.bodega as bod', 'bod.idbodega','comp.idbodega')
            ->select('comp.descripcion','comp.secuencial','comp.fecha_hora','comp.observacion','comp.total','comp.id_usuario_ingresa', 'comp.idcomprobante','bod.nombre as nombre_bodega','comp.idbodega',DB::raw("CONCAT(per.ape1,' ', per.ape2,' ', per.nom1,' ', per.nom2) AS solicita"), "perf.descripcion as area1","comp.codigo_old","a.descripcion as area")
            ->where('comp.estado','=','Activo')
            ->whereIN('comp.codigo_old',['Pedido','PedidoAFarm','EntregadoF','EntregadoB'])
            ->where('comp.idtipo_comprobante',18)//paquete
            ->where('comp.id_usuario_ingresa',auth()->user()->id)
            ->get();

            

            foreach($pedidos as $key=> $data){
                $pedidos[$key]->idencryp=encrypt($data->idcomprobante);
                $pedidos[$key]->decrypt=decrypt( $pedidos[$key]->idencryp);
            }
          
            return response()->json([
                'error'=>false,
                'resultado'=>$pedidos
            ]);
        }catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }
    
    public function detallePedidoPaquete($id,$idbodega){
       
        try{
           
            $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
            ->leftJoin('bodega.detalle_comprobante as detcomp', 'detcomp.idcomprobante','comp.idcomprobante')
            ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
            ->leftJoin('bodega.medicamentos as medi', 'medi.coditem','detcomp.id_item')
            ->leftJoin('bodega.insumo as i', 'i.codinsumo','detcomp.id_item')
    
            ->select(DB::raw("CONCAT(medi.nombre,' - ', medi.concentra,' - ', medi.forma) AS nombre_item_med"),DB::raw("CONCAT(i.insumo) AS nombre_item_insumo"),'pedido.lote','pedido.fecha_caducidad','pedido.cantidad_entregada','pedido.cantidad_pedida','medi.coditem', 'detcomp.iddetalle_comprobante as iddetalle','detcomp.id_item','medi.stock_farm_dialisis as stock_med','i.stock_farm_dialisis as stock_ins')
            ->where('comp.idcomprobante',$id)
            ->where('comp.estado','Activo')
            ->whereIN('comp.codigo_old',['PedidoAFarm','EntregadoF','EntregadoB'])
            // ->groupby('detcomp.id_item','medi.nombre','medi.concentra','medi.forma','i.insumo','pedido.lote','pedido.fecha_caducidad','pedido.cantidad_entregada','pb.existencia','medi.coditem','detcomp.iddetalle_comprobante','pb.tipoprod')
            // ->distinct('detcomp.id_item')
            ->get();
            
            foreach($info as $key => $data){
        
                if($data->id_item>=30000){
                    $nombre_item=$data->nombre_item_insumo;
                    $stock_item=$data->stock_ins;
                   
                }else{
                    $nombre_item=$data->nombre_item_med;
                    $stock_item=$data->stock_med;
                }
                $info[$key]->nombre_item=$nombre_item;
                $info[$key]->stock_disp=$stock_item;
    
            }

            #agrupamos

            $lista_final_agrupada=[];
            foreach ($info as $key => $item){                
                if(!isset($lista_final_agrupada[$item->nombre_item])) {
                    $lista_final_agrupada[$item->nombre_item]=array($item);
            
                }else{
                    array_push($lista_final_agrupada[$item->nombre_item], $item);
                }
            }

        
            return response()->json([
                'error'=>false,
                'resultado'=>$lista_final_agrupada,
                'fecha'=>date('Y-m-d H:i:s')
            ]);
        }catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }

    public function editarPedido($id, $bodega){
        try{
           
            $comprobante=Comprobante::with('detalle_item','entregado','responsable','bodega','nomarea')->where('idcomprobante',$id)
            ->first();

            if($comprobante->codigo_old == "Entregado"){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'El pedido ya fue validado y no se puede actualizar'
                ]);
            }
            
            #agrupamos por paquete
            $lista_final_agrupada=[];
            foreach ($comprobante->detalle_item as $key => $item){                
                if(!isset($lista_final_agrupada[$item->pedido->id_paquete])) {
                    $lista_final_agrupada[$item->pedido->id_paquete]=array($item);
            
                }else{
                    array_push($lista_final_agrupada[$item->pedido->id_paquete], $item);
                }
            }
           
            $verificaBodega=BodegaUsuario::where('idusuario',auth()->user()->id)
            ->get();
            $idbodegas=[];
            foreach($verificaBodega as $data){
                array_push($idbodegas, $data->idbodega);
            }

            $bodega= DB::connection('pgsql')->table('bodega.bodega')
            // ->where('idtipobod',1)
            ->whereIn('idbodega',$idbodegas)
            ->where('estado',1)
            ->get();
    
                                                                                                                                                                                                                                                    
            return response()->json([
                'error'=>false,
                'resultado'=>$lista_final_agrupada,
                'cantidad_pedida_paq'=>$comprobante->cantidad_paquete,
                'motivo'=>$comprobante->observacion,
                'bodega'=>$bodega
            ]);
    
          
        }catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }

    //SOLICITUD PAQUETES DESDE DIALISIS A FARMACIA
    public function actualizarSolicitud(Request $request, $id){
       
        $array_paquete=$request->idpaquete_selecc;
        $cantidad=$request->cantidad;
       
        if(sizeof($array_paquete)==0){
            return (['mensaje'=>'Debe seleccionar al menos un paquete','error'=>true]);
        }

        $transaction=DB::connection('pgsql')->transaction(function() use ($request, $id){
            try{
               
                $comprobante=Comprobante::with('detalle')->where('idcomprobante',$id)->first();
                
                $bodega=$comprobante->idbodega;            
                if($comprobante->codigo_old != "Pedido"){
                    return (['mensaje'=>'El pedido ya fue entregado y no se puede actualizar','error'=>true, 'ent'=>'S']);
                }
                        
                foreach($comprobante->detalle as $detalle){
                    //eliminamos los pedidos anteriores
                    $eliminamos_pedidos=PedidoBodegaGral::where('iddetallecomprobante',$detalle->iddetalle_comprobante)
                    ->delete();
                }
                //eliminamos los detalles anteriores
                $eliminaDetalle=DetalleComprobante::where('idcomprobante',$id)->delete();

                $comprobante->fecha_hora_actualiza=date('Y-m-d H:i:s');
                $comprobante->idbodega=31;
                $comprobante->observacion=$request->motivo;
                $comprobante->subtotal=$request->total_suma;
                $comprobante->total=$request->total_suma; 
                $comprobante->cantidad_paquete=$request->cantidad[0];              
                $comprobante->iduser_actualiza=auth()->user()->id;

                if($comprobante->save()){

                    //datos detalle
                    $cant=$request->cantidad;
                    //recorremos los paquetes seleccionados
                    foreach($request->idpaquete_selecc as $key=> $idpaq){

                        //validamos los stock disponibles en farmacia de cada uno de los items de los paquetes
                        $validaPaq=$this->validaPaquete($idpaq, $cant[$key]);
                       
                       
                        //caso de error al consultar
                        if($validaPaq['error']==true){
                            DB::connection('pgsql')->rollback();
                            return (['mensaje'=>'No se pudo comprobar la existencia en stock, intentelo mas tarde','error'=>true]);
                        }else{
                            
                            // recorremos cada uno de los items
                            foreach($validaPaq['resultado'] as $detalle){
                                $cantidad_item=0;
                                
                                $cantidad_item=$cant[$key] * $detalle->cantidad;
                                
                               
                                //el stock es menor a lo solicitado
                                if($detalle->info=="N"){
                                    //insumos
                                    if($detalle->id_item >=30000){
                                        $stock_max=$detalle->stock_farm_ins;
                                        $nomb_item=$detalle->descripcion_ins;
                                    }else{ //medicina
                                        $stock_max=$detalle->stock_farm_med;
                                        $nomb_item=$detalle->descripcion_med;
                                    }
                                    DB::connection('pgsql')->rollback();
                                    return (['mensaje'=>'El stock del '.$request->nombrepaquete[$key].' del item '.$nomb_item.' es '.$stock_max.' y el solicitado es '.$cant[$key] * $detalle->cantidad,'error'=>true ,'idpaquete'=>$idpaq,'paquete'=>$request->nombrepaquete[$key]]);
                                
                                }else{
                                    //de acuerdo al item asignamos la bodega a buscar
                                    if($detalle->id_item >=30000){
                                        //insumo dialisis
                                        $idbodega_sel=21;
                                    }else{
                                        //medicina dialisis
                                        $idbodega_sel=20;
                                    }
                                    $fecha_Actual=date('Y-m-d');

                                    //ordenamos de acuerdo a la fecha de expiracion
                                    $prodbod=DB::table('bodega.prodxbod as pb')
                                    ->leftJoin('bodega.existencia as e', 'e.idbodprod','pb.idbodprod')
                                    ->where('pb.idbodega',$idbodega_sel)
                                    ->where('existencia','>=',$cantidad_item)
                                    ->where('idprod',$detalle->id_item)
                                    ->whereDate('e.fecha_caducidad','>=',$fecha_Actual)
                                    ->orderBy('e.fecha_caducidad','asc')
                                    ->select('pb.idbodprod','pb.precio','pb.idbodega','e.lote','e.fecha_caducidad','e.fecha_elaboracion')
                                    ->first();

                                    
                                    
                                    if(!is_null($prodbod)){

                                        //registramos los detalles
                                        $ultimo=DetalleComprobante::orderBy('iddetalle_comprobante','desc')->first();
                                        if(is_null($ultimo)){
                                            $suma=1;
                                        }else{
                                            $suma=$ultimo->iddetalle_comprobante+1;
                                        }
                                        $total_item=0;
                                        $total_item=$prodbod->precio * $cant[$key];
                                        $detalles=new DetalleComprobante();
                                        $detalles->iddetalle_comprobante=$suma;
                                        $detalles->idcomprobante=$comprobante->idcomprobante;
                                        $detalles->id_item=$detalle->id_item;
                                        $detalles->id_bodega=$prodbod->idbodega;
                                        $detalles->idbodprod=$prodbod->idbodprod;
                                        $detalles->cantidad=$cantidad_item;
                                        $detalles->precio=number_format(($prodbod->precio),4,'.', '');
                                        $detalles->descuento=0;
                                        $detalles->total=number_format(($total_item),4,'.', '');
                                        $detalles->iva=0;
                                        $detalles->fecha=date('Y-m-d H:i:s');
                                        $detalles->save(); 

                                        $ultimo=PedidoBodegaGral::orderBy('idpedido_bod_gral','desc')->first();
                                        if(is_null($ultimo)){
                                            $suma=1;
                                        }else{
                                            $suma=$ultimo->idpedido_bod_gral+1;
                                        }

                                        //registramos el pedido
                                        $pedido_temp=new PedidoBodegaGral();
                                        $pedido_temp->idpedido_bod_gral=$suma;
                                        $pedido_temp->iddetallecomprobante=$detalles->iddetalle_comprobante;
                                        $pedido_temp->lote=$prodbod->lote;
                                        $pedido_temp->fecha_caducidad=$prodbod->fecha_caducidad;
                                        $pedido_temp->fecha_elabora=$prodbod->fecha_elaboracion;
                                        $pedido_temp->cantidad_pedida=$cantidad_item;
                                        $pedido_temp->idbodega=$detalles->id_bodega;
                                        $pedido_temp->id_solicita=auth()->user()->id;
                                        $pedido_temp->fecha_solicita=date('Y-m-d H:i:s');
                                        $pedido_temp->idbodpro=$prodbod->idbodprod;
                                        $pedido_temp->iditem=$detalle->id_item;
                                        $pedido_temp->id_paquete=$idpaq;
                                        $pedido_temp->estado="Solicitado";
                                        $pedido_temp->save();                       
                                       

                                    }else{
                                        if($detalle->id_item >=30000){
                                            $stock_max=$detalle->stock_farm_ins;
                                            $nomb_item=$detalle->descripcion_ins;

                                        }else{ //medicina
                                            $stock_max=$detalle->stock_farm_med;
                                            $nomb_item=$detalle->descripcion_med;
                                        }

                                        DB::connection('pgsql')->rollback();
                                     
                                        return (['mensaje'=>'El stock del '.$request->nombrepaquete[$key].' del item '.$nomb_item.' es insuficiente para completar el pedido de los paquetes ','error'=>true ]);
                                    }

                                }

                            }
                        }
                    }

                   
                    //si tofdo ok el comprobante se crea
                    $comprobante_crear=Comprobante::find($comprobante->idcomprobante);
                    $comprobante_crear->estado="Activo";
                    $comprobante_crear->codigo_old="Pedido";//pedido
                    $comprobante->subtotal=$request->total_suma;
                    $comprobante->total=$request->total_suma; 
                    $comprobante_crear->save();

                    return (['mensaje'=>'Informacion ingresada exitosamente','error'=>false]);

                }        

            } catch (\Throwable $e) {
                DB::connection('pgsql')->rollback();
                Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
                return (['mensaje'=>'Ocurrió un error,intentelo más tarde','error'=>true]); 
            }
        });
        return ($transaction);
    }
}