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
use App\Models\Bodega\Existencia;
use App\Models\Bodega\EntregaPaquete;


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
        // echo json_encode($valida_paquetes, JSON_FORCE_OBJECT);
    }

    public function validaPaqueteApi(Request $request){
        $cantidad=$request->cantidad;
        $id=$request->cantidad;
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
      
        // return [
        //     'error'=>false,
        //     'resultado'=>$valida_paquetes
        // ];
        echo json_encode($valida_paquetes, JSON_FORCE_OBJECT);
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
                //si existe un pedido pendiente no permitimos realizar la entrega
                $existe_pedido_pendiente=Comprobante::where('idtipo_comprobante',18)
                ->where('codigo_old','PedidoAFarm')
                ->where('estado','Activo')
                ->first();

                if(!is_null($existe_pedido_pendiente)){
                    return (['mensaje'=>'Existe un pedido pendiente de retirar','error'=>true]);
                }
                             
                $total_comprobante=0;
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
                    $nomb_paq=$request->nombrepaquete;
                    //recorremos los paquetes seleccionados
                    foreach($request->idpaquete_selecc as $key=> $idpaq){

                        //validamos los stock disponibles en farmacia de cada uno de los items de los paquetes
                        $validaPaq=$this->validaPaquete($idpaq, $cant[$key]);
                       
                        //caso de error al consultar
                        if($validaPaq['error']==true){
                            DB::connection('pgsql')->rollback();
                            return (['mensaje'=>'No se pudo comprobar la existencia en stock, intentelo mas tarde','error'=>true]);
                        }else{
                            // recorremos cada uno de los items x paquete
                            foreach($validaPaq['resultado'] as $detalle){                               
                               
                                //el stock es menor a lo solicitado (x lote)
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

                                    if($detalle->id_item >=30000){
                                        //insumo dialisis
                                        $idbodega_sel=21;
                                    }else{
                                        //medicina dialisis
                                        $idbodega_sel=20;
                                    }

                                    if($detalle->id_item >=30000){
                                        $stock_max=$detalle->stock_farm_ins;
                                        $nomb_item=$detalle->descripcion_ins;
                                    }else{ //medicina
                                        $stock_max=$detalle->stock_farm_med;
                                        $nomb_item=$detalle->descripcion_med;
                                    }
                                    
                                    //registramos los detalles
                                    $ultimo=EntregaPaquete::orderBy('identrega_paquete','desc')->first();
                                    if(is_null($ultimo)){
                                        $suma=1;
                                    }else{
                                        $suma=$ultimo->identrega_paquete+1;
                                    }
                                    //detalle de cada uno de los items x paquetes
                                    $entrega=new EntregaPaquete();
                                    $entrega->identrega_paquete=$suma;
                                    $entrega->idpaquete=$idpaq;
                                    $entrega->desc_paquete=$nomb_paq[$key];
                                    $entrega->iddetalle_paq=$detalle->iddetalle_paq;
                                    $entrega->id_item=$detalle->id_item;
                                    $entrega->idbodega=$idbodega_sel;
                                    $entrega->nombre_item=$nomb_item;
                                    $entrega->tipo=$detalle->tipo;
                                    $entrega->cantidad=$detalle->cantidad_solic;
                                    $entrega->fecha_solicitud=date('Y-m-d H:i:s');
                                    $entrega->id_solicita=auth()->user()->id;
                                    $entrega->cantidad_paquete=$cant[$key];
                                    $entrega->idcomprobante=$comprobante->idcomprobante;
                                    $entrega->save();
                                    
                                  
                                }

                            }
                        }
                       
                    }

                    $datosPa=EntregaPaquete::where("idcomprobante", $entrega->idcomprobante)->get();
                    $lista_final_agrupada=[];
                    foreach ($datosPa as $key => $item){                
                        if(!isset($lista_final_agrupada[$item->id_item])) {
                            $lista_final_agrupada[$item->id_item]=array($item);
                     
                        }else{
                            array_push($lista_final_agrupada[$item->id_item], $item);
                        }
                     }

                    foreach($lista_final_agrupada as $i=>  $dat){
                        $paqueteLote=$this->ItemLote($i, $entrega->idcomprobante);
                        if($paqueteLote['error']==true){
                            DB::connection('pgsql')->rollback();
                           
                            return (['mensaje'=>$paqueteLote['mensaje'],'error'=>true]); 
                        }
                    }
                    
                    $total_comprobante=$paqueteLote['total_comprobante'];

                   
                    $tipocomp_old->numcom=$comprobante->secuencial;
                    $tipocomp_old->save();
                  
                    //si tofdo ok el comprobante se crea
                    $comprobante_crear=Comprobante::find($comprobante->idcomprobante);
                    $comprobante_crear->estado="Activo";
                    $comprobante_crear->codigo_old="PedidoAFarm";//pedido
                    $comprobante_crear->subtotal=number_format(($total_comprobante),2,'.', '');
                    $comprobante_crear->total=number_format(($total_comprobante),2,'.', '');
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

   
    public function validaStockGeneral($id){

        $validaStockProd= DB::connection('pgsql')->table('bodega.pedido_bod_gral as pedido')
        ->leftJoin('bodega.detalle_comprobante as detcomp', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
        ->leftJoin('bodega.comprobante as comp', 'detcomp.idcomprobante','comp.idcomprobante')
        ->where('comp.idcomprobante',$id)
        ->select(DB::raw('sum("cantidad_pedida") as cantidad_pedida'),'iditem')
        ->groupby('iditem')
        ->get();
      
        
        foreach($validaStockProd as $info){
                      
            if($info->iditem >= 30000){
                $stock_ins=DB::table('bodega.insumo')
                ->where('codinsumo', $info->iditem)
                ->where('stock_farm_dialisis','<', $info->cantidad_pedida)
                ->select('stock_farm_dialisis','codinsumo','insumo as nombre_item')
                ->first();
                if(!is_null($stock_ins)){
                   
                    return (['error'=>'S', 'mensaje'=>'La cantidad del item '.$stock_ins->nombre_item. ' solicitado es '.$info->cantidad_pedida. ' y la cantidad en stock es '.$stock_ins->stock_farm_dialisis]);
                }

            }else{
                $stock_med=DB::table('bodega.medicamentos')
                ->where('coditem', $info->iditem)
                ->where('stock_farm_dialisis','<', $info->cantidad_pedida)
                ->select('stock_farm_dialisis','coditem',DB::raw("CONCAT(nombre,' - ', concentra,' - ', forma,' - ',presentacion) as nombre_item"))
                ->first();
                if(!is_null($stock_med)){
                    return (['error'=>'S', 'mensaje'=>'La cantidad del item '.$stock_med->nombre_item. ' solicitado es '.$info->cantidad_pedida. ' y la cantidad en stock es '.$stock_med->stock_farm_dialisis]);
                }
            }
        }
        
        return (['error'=>'N']);

      
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
            ->whereIN('comp.codigo_old',['Pedido','PedidoAFarm','EntregadoF','EntregadoB','Entregado'])
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
           
            // $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
            // ->leftJoin('bodega.detalle_comprobante as detcomp', 'detcomp.idcomprobante','comp.idcomprobante')
            // ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
            // ->leftJoin('bodega.medicamentos as medi', 'medi.coditem','detcomp.id_item')
            // ->leftJoin('bodega.insumo as i', 'i.codinsumo','detcomp.id_item')
            // ->select(DB::raw("CONCAT(medi.nombre,' - ', medi.concentra,' - ', medi.forma) AS nombre_item_med"),DB::raw("CONCAT(i.insumo) AS nombre_item_insumo"),'pedido.lote','pedido.fecha_caducidad','pedido.cantidad_entregada','pedido.cantidad_pedida','medi.coditem', 'detcomp.iddetalle_comprobante as iddetalle','detcomp.id_item','medi.stock_farm_dialisis as stock_med','i.stock_farm_dialisis as stock_ins')
            // ->where('comp.idcomprobante',$id)
            // ->where('comp.estado','Activo')
            // ->get();

            $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
            ->leftJoin('bodega.entrega_paquete as ent', 'ent.idcomprobante','comp.idcomprobante')
            ->leftJoin('bodega.medicamentos as medi', 'medi.coditem','ent.id_item')
            ->leftJoin('bodega.insumo as i', 'i.codinsumo','ent.id_item')
            ->select('ent.nombre_item','ent.cantidad as cantidad_pedida','ent.id_item', 'medi.stock_farm_dialisis as stock_med','i.stock_farm_dialisis as stock_ins')
            ->where('comp.idcomprobante',$id)
            ->where('comp.estado','Activo')
            ->get();

                  
            foreach($info as $key => $data){
        
                if($data->id_item>=30000){
                    $stock_item=$data->stock_ins;
                   
                }else{
                    $stock_item=$data->stock_med;
                }
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

            
            $datosPa=EntregaPaquete::where("idcomprobante", $id)->get();
            $lista_final_agrupada_valida=[];
            foreach ($datosPa as $key => $item){                
                if(!isset($lista_final_agrupada_valida[$item->id_item])) {
                    $lista_final_agrupada_valida[$item->id_item]=array($item);
             
                }else{
                    array_push($lista_final_agrupada_valida[$item->id_item], $item);
                }
            }

            //eliminamos los detalles y pedidos antes de asignarle
            $detalleElimina=DetalleComprobante::where('idcomprobante', $id)
            ->get();
            foreach($detalleElimina as $data){
                $eliminaPedido=PedidoBodegaGral::where('iddetallecomprobante', $data->iddetalle_comprobante)
                ->first();
                $eliminaPedido->delete();
            }
            $EliminaDetalle=DetalleComprobante::where('idcomprobante', $id)
            ->delete();

            $datosReceta=DB::table('bodega.comprobante as comp')
            ->leftJoin('esq_pacientes.pacientes as per_pac', 'per_pac.id_paciente','comp.id_paciente')
            ->leftJoin('esq_rdacaa.cie10', 'cie10.cie10_id','comp.id_cie10')
         
            ->select(DB::raw("CONCAT(per_pac.apellido1,'  ', per_pac.apellido2,'  ', per_pac.nombre1,'  ', per_pac.nombre2) AS paciente"),
           'per_pac.documento as cedula_paciente',DB::raw("CONCAT(cie10_codigo,' -- ',cie10_descripcion) AS descripcion_cie_10"))
           ->where('comp.idcomprobante', $id)
            ->first();

            foreach($lista_final_agrupada_valida as $i=>  $dat){
                $paqueteLote=$this->ItemLote($i, $id);
                if($paqueteLote['error']==true){
                    DB::connection('pgsql')->rollback();
                   
                    return (['mensaje'=>$paqueteLote['mensaje'],'error'=>true]); 
                }
            }

        
            return response()->json([
                'error'=>false,
                'resultado'=>$lista_final_agrupada,
                'fecha'=>date('Y-m-d H:i:s'),
                'datosReceta'=>$datosReceta
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
                    'mensaje'=>'El pedido ya fue entregado y no se puede actualizar'
                ]);
            }

            $paquete=EntregaPaquete::with("paquete")->where("idcomprobante",$id)
            ->get();
            
            #agrupamos por paquete
            $lista_final_agrupada=[];
            foreach ($paquete as $key => $item){ 
                              
                if(!isset($lista_final_agrupada[$item->idpaquete])) {
                    $lista_final_agrupada[$item->idpaquete]=array($item);
            
                }else{
                    array_push($lista_final_agrupada[$item->idpaquete], $item);
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
                // 'cantidad_pedida_paq'=>$comprobante->cantidad_paquete,
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

    public function ItemLote($identrega, $compr){

        $transaction=DB::connection('pgsql')->transaction(function() use ($identrega, $compr){
            try{

                $total_comprobante=0;
      
                $datosPa=EntregaPaquete::where("id_item", $identrega)
                ->where("idcomprobante",$compr)
                ->select(DB::raw('sum("cantidad") as cantidad'),'id_item','idbodega')
                ->groupby('id_item','idbodega')
                ->first();  
               
                $cantidad=$datosPa->cantidad;
                $item=$datosPa->id_item;
                $idbodega=$datosPa->idbodega;

                $paquete=EntregaPaquete::where("idcomprobante", $compr)->first();
                           
                if($item>=30000){
                    $stockInsumo=DB::table('bodega.insumo')
                    ->where('codinsumo',$item)
                    ->where('stock_farm_dialisis','<',$cantidad)
                    ->select('insumo as detalle','stock_farm_dialisis')
                    ->first();
                
                   
                    if(!is_null($stockInsumo)){
                        DB::connection('pgsql')->rollback();
                        return (['mensaje'=>'El stock  del item '.$stockInsumo->detalle.' es '.$stockInsumo->stock_farm_dialisis.' y el solicitado es '.$cantidad,'error'=>true]);
                    }
                }else{
                    $stockInsumo=DB::table('bodega.medicamentos')
                    ->where('coditem',$item)
                    ->where('stock_farm_dialisis','<',$cantidad)
                    ->select(DB::raw("CONCAT(nombre,' - ', concentra,' - ', forma) AS detalle"),'stock_farm_dialisis')
                    ->first();

                    if(!is_null($stockInsumo)){
                        DB::connection('pgsql')->rollback();
                        return (['mensaje'=>'El stock  del item '.$stockInsumo->detalle.' es '.$stockInsumo->stock_farm_dialisis.' y el solicitado es '.$cantidad,'error'=>true]);
                    }
                }
                $fecha_Actual=date('Y-m-d');
                // $StockPB=DB::table('bodega.prodxbod as pb')
                // ->leftJoin('bodega.existencia as e', 'e.idbodprod','pb.idbodprod')
                // ->where('pb.idbodega',$idbodega)
                // ->where('existencia','>',0)
                // ->whereDate('e.fecha_caducidad','>=',$fecha_Actual)
                // ->where('idprod',$item)
                // ->select('pb.existencia','pb.idbodprod','e.fecha_caducidad')       
                // ->distinct()
                // ->orderBy('e.fecha_caducidad','asc')
                // ->get();

                $StockPB=DB::table('bodega.prodxbod as pb')
                ->leftJoin('bodega.lotexprod as e', 'e.idbodp','pb.idbodprod')
                ->where('pb.idbodega',$idbodega)
                ->where('existencia','>',0)
                ->where('idprod',$item)
                ->whereDate('e.fcaduca','>=',$fecha_Actual)
                ->select('pb.existencia','pb.idbodprod','e.fcaduca')       
                ->distinct()
                ->orderBy('e.fcaduca','asc')
                ->get();
        
                
                if(sizeof($StockPB)==0){
                    DB::connection('pgsql')->rollback();                                     
                
                    return (['mensaje'=>'El stock insuficiente  ','error'=>true ]);
                }

                $cantidad_item=0;                                
                $cantidad_item=$cantidad;
            
                foreach($StockPB as $data){
                  
                   
                    // $existencia_Red=ProductoBodega::where('idbodprod',$data->idbodprod)
                    // ->update(['existencia_red'=>$data->existencia]);
                    // dd($data);  
                    if($cantidad_item>0){
                    
                        if($cantidad_item <= $data->existencia){//existencia_red

                            if($data->existencia>0){//existencia_red
                                // $Prodbod =ProductoBodega::with('existencias')->where('idbodprod',$data->idbodprod)
                                // ->where('idprod',$item)
                                // ->first();  

                                $Prodbod =ProductoBodega::with('lote')->where('idbodprod',$data->idbodprod)
                                ->where('idprod',$item)
                                ->first();
                               
                                
                                //registramos los detalles
                                $ultimo=DetalleComprobante::orderBy('iddetalle_comprobante','desc')->first();
                                if(is_null($ultimo)){
                                    $suma=1;
                                }else{
                                    $suma=$ultimo->iddetalle_comprobante+1;
                                }
                               
                                $total_item=0;
                                $total_item=$Prodbod->precio * $cantidad_item;
                                $detalles=new DetalleComprobante();
                                $detalles->iddetalle_comprobante=$suma;
                                $detalles->idcomprobante=$compr;
                                $detalles->id_item=$item;
                                $detalles->id_bodega=$idbodega;
                                $detalles->idbodprod=$Prodbod->idbodprod;
                                $detalles->cantidad=$cantidad_item;
                                $detalles->precio=number_format(($Prodbod->precio),4,'.', '');
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

                                $pedido_temp->lote=$Prodbod->lote->lote;
                                $pedido_temp->fecha_elabora=$Prodbod->lote->felabora;
                                $pedido_temp->fecha_caducidad=$Prodbod->lote->fcaduca;

                                // $pedido_temp->lote=$Prodbod->existencias->lote;
                                // $pedido_temp->fecha_caducidad=$Prodbod->existencias->fecha_caducidad;
                                // $pedido_temp->fecha_elabora=$Prodbod->existencias->fecha_elaboracion;

                                $pedido_temp->cantidad_pedida=$cantidad_item;
                                $pedido_temp->idbodega=$idbodega;
                                $pedido_temp->id_solicita=$paquete->id_solicita;
                                $pedido_temp->fecha_solicita=date('Y-m-d H:i:s');
                                $pedido_temp->idbodpro=$Prodbod->idbodprod;
                                $pedido_temp->iditem=$item;
                                $pedido_temp->id_paquete=$paquete->idpaquete;
                                $pedido_temp->estado="Solicitado";
                                $pedido_temp->save();  
                                
                                $total_comprobante=$total_comprobante +  $total_item;

                                break;
                            }
        
                        }else{
                            
                            //if($data->existencia_red>0){
                                // $Prodbod =ProductoBodega::with('existencias')->where('idbodprod',$data->idbodprod)
                                // ->where('idprod',$item)
                                // ->first();

                                $Prodbod =ProductoBodega::with('lote')->where('idbodprod',$data->idbodprod)
                                ->where('idprod',$item)
                                ->first();
                              
                                $nuevoStock=$Prodbod->existencia;
                                $nuevoStock_act=$nuevoStock - $cantidad_item;
                                if($nuevoStock_act<0){
                                    $nuevoStock_act=0;
                                }
                                //$Prodbod->existencia_red=$nuevoStock_act;                                        
                                //$Prodbod->save();

                                //registramos los detalles
                                $ultimo=DetalleComprobante::orderBy('iddetalle_comprobante','desc')->first();
                                if(is_null($ultimo)){
                                    $suma=1;
                                }else{
                                    $suma=$ultimo->iddetalle_comprobante+1;
                                }
                               
                                $total_item=0;
                                $total_item=$Prodbod->precio * $nuevoStock;
                                $detalles=new DetalleComprobante();
                                $detalles->iddetalle_comprobante=$suma;
                                $detalles->idcomprobante=$compr;
                                $detalles->id_item=$item;
                                $detalles->id_bodega=$idbodega;
                                $detalles->idbodprod=$Prodbod->idbodprod;
                                $detalles->cantidad=$nuevoStock;
                                $detalles->precio=number_format(($Prodbod->precio),4,'.', '');
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

                                $pedido_temp->lote=$Prodbod->lote->lote;
                                $pedido_temp->fecha_elabora=$Prodbod->lote->felabora;
                                $pedido_temp->fecha_caducidad=$Prodbod->lote->fcaduca;

                                // $pedido_temp->lote=$Prodbod->existencias->lote;
                                // $pedido_temp->fecha_caducidad=$Prodbod->existencias->fecha_caducidad;
                                // $pedido_temp->fecha_elabora=$Prodbod->existencias->fecha_elaboracion;
                                $pedido_temp->cantidad_pedida=$nuevoStock;
                                $pedido_temp->idbodega=$detalles->id_bodega;
                                $pedido_temp->id_solicita=$paquete->id_solicita;
                                $pedido_temp->fecha_solicita=date('Y-m-d H:i:s');
                                $pedido_temp->idbodpro=$Prodbod->idbodprod;
                                $pedido_temp->iditem=$item;
                                $pedido_temp->id_paquete=$paquete->idpaquete;
                                $pedido_temp->estado="Solicitado";
                                $pedido_temp->save();
                                
                                $cantidad_item=$cantidad_item - $Prodbod->existencia;

                                $total_comprobante=$total_comprobante +  $total_item;
                            }
                        //}
                    }

                }
                return (['total_comprobante'=>$total_comprobante,'error'=>false]); 

            } catch (\Throwable $e) {
                DB::connection('pgsql')->rollback();
                Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
                return (['mensaje'=>'Ocurrió un error,intentelo más tarde','error'=>true]); 
            }
        });
        return ($transaction);
        
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
                $total_comprobante=0;
                $comprobante=Comprobante::with('detalle')->where('idcomprobante',$id)->first();
                
                $bodega=$comprobante->idbodega;            
                if($comprobante->codigo_old != "PedidoAFarm"){
                    return (['mensaje'=>'El pedido ya fue entregado y no se puede actualizar','error'=>true, 'ent'=>'S']);
                }
                        
                foreach($comprobante->detalle as $detalle){
                    //eliminamos los pedidos anteriores
                    $eliminamos_pedidos=PedidoBodegaGral::where('iddetallecomprobante',$detalle->iddetalle_comprobante)
                    ->delete();
                }
                //eliminamos los detalles anteriores
                $eliminaDetalle=DetalleComprobante::where('idcomprobante',$id)->delete();

                $eliminaEntrega=EntregaPaquete::where('idcomprobante',$id)->delete();

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
                    $nomb_paq=$request->nombrepaquete;
                    //recorremos los paquetes seleccionados
                    foreach($request->idpaquete_selecc as $key=> $idpaq){

                        //validamos los stock disponibles en farmacia de cada uno de los items de los paquetes
                        $validaPaq=$this->validaPaquete($idpaq, $cant[$key]);
                       
                        //caso de error al consultar
                        if($validaPaq['error']==true){
                            DB::connection('pgsql')->rollback();
                            return (['mensaje'=>'No se pudo comprobar la existencia en stock, intentelo mas tarde','error'=>true]);
                        }else{
                            // recorremos cada uno de los items x paquete
                            foreach($validaPaq['resultado'] as $detalle){                               
                               
                                //el stock es menor a lo solicitado (x lote)
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

                                    if($detalle->id_item >=30000){
                                        //insumo dialisis
                                        $idbodega_sel=21;
                                    }else{
                                        //medicina dialisis
                                        $idbodega_sel=20;
                                    }

                                    if($detalle->id_item >=30000){
                                        $stock_max=$detalle->stock_farm_ins;
                                        $nomb_item=$detalle->descripcion_ins;
                                    }else{ //medicina
                                        $stock_max=$detalle->stock_farm_med;
                                        $nomb_item=$detalle->descripcion_med;
                                    }
                                    
                                    //registramos los detalles
                                    $ultimo=EntregaPaquete::orderBy('identrega_paquete','desc')->first();
                                    if(is_null($ultimo)){
                                        $suma=1;
                                    }else{
                                        $suma=$ultimo->identrega_paquete+1;
                                    }
                                    //detalle de cada uno de los items x paquetes
                                    $entrega=new EntregaPaquete();
                                    $entrega->identrega_paquete=$suma;
                                    $entrega->idpaquete=$idpaq;
                                    $entrega->desc_paquete=$nomb_paq[$key];
                                    $entrega->iddetalle_paq=$detalle->iddetalle_paq;
                                    $entrega->id_item=$detalle->id_item;
                                    $entrega->idbodega=$idbodega_sel;
                                    $entrega->nombre_item=$nomb_item;
                                    $entrega->tipo=$detalle->tipo;
                                    $entrega->cantidad=$detalle->cantidad_solic;
                                    $entrega->fecha_solicitud=date('Y-m-d H:i:s');
                                    $entrega->id_solicita=auth()->user()->id;
                                    $entrega->cantidad_paquete=$cant[$key];
                                    $entrega->idcomprobante=$comprobante->idcomprobante;
                                    $entrega->save();

                                  
                                    
                                    
                                }

                            }
                        }
                       
                    }


                    $datosPa=EntregaPaquete::where("idcomprobante", $entrega->idcomprobante)->get();
                    $lista_final_agrupada=[];
                    foreach ($datosPa as $key => $item){                
                        if(!isset($lista_final_agrupada[$item->id_item])) {
                            $lista_final_agrupada[$item->id_item]=array($item);
                     
                        }else{
                            array_push($lista_final_agrupada[$item->id_item], $item);
                        }
                     }

                    foreach($lista_final_agrupada as $i=>  $dat){
                        $paqueteLote=$this->ItemLote($i, $entrega->idcomprobante);
                        if($paqueteLote['error']==true){
                            DB::connection('pgsql')->rollback();
                           
                            return (['mensaje'=>$paqueteLote['mensaje'],'error'=>true]); 
                        }
                    }
                    
                    $total_comprobante=$paqueteLote['total_comprobante'];
                    //si tofdo ok el comprobante se crea
                    $comprobante_crear=Comprobante::find($comprobante->idcomprobante);
                    $comprobante_crear->estado="Activo";
                    $comprobante_crear->codigo_old="PedidoAFarm";//pedido
                    $comprobante_crear->subtotal=number_format(($total_comprobante),2,'.', '');
                    $comprobante_crear->total=number_format(($total_comprobante),2,'.', ''); 
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


    //ENTREGA PAQUETES DESDE DIALISIS A FARMACIA
    public function validarEntrega(Request $request){
       
        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{
                $total_comprobante=0;
                $actualizaComprobante=Comprobante::where('idcomprobante',$request->idcabeceraCompr)
                ->first();
                if($actualizaComprobante->codigo_old=="Entregado"){
                    DB::connection('pgsql')->rollback();
                    return (['mensaje'=>'El pedido ya fue entregado','error'=>true]);
                }

                if(strtotime($request->get('fecha_vali')) <= strtotime($actualizaComprobante->fecha_hora_actualiza)){
                    return (['mensaje'=>'El pedido ha sido actualizado, realice la peticion nuevamente','error'=>true, 'act'=>'S']);
                }
               
                $detalleItem=DetalleComprobante::with('comprobante')
                ->where('idcomprobante', $request->idcabeceraCompr)
                ->get();
                $iddetalle_array=[];
                foreach($detalleItem as $data){
                    array_push($iddetalle_array, $data->iddetalle_comprobante);
                    $cantida_ent=$data->cantidad;

                    $actualizaStockOld =ProductoBodega::where('idbodprod',$data->idbodprod)
                    ->first(); 
                  
                    if($data->cantidad > $actualizaStockOld->existencia){
                        if($actualizaStockOld->idprod >= 30000){
                            $nombre_item=DB::table('bodega.insumo')
                            ->where('codinsumo',$actualizaStockOld->idprod)
                            ->where('stock_farm_dialisis','<', $data->cantidad)
                            ->select('insumo as nombreItem','codinsumo as id')
                            ->first();
                          
                           
                        }else{
                            $nombre_item=DB::table('bodega.medicamentos')
                            ->where('coditem',$actualizaStockOld->idprod)
                            ->where('stock_farm_dialisis','<', $data->cantidad)
                            ->select(DB::raw("CONCAT(nombre, ' ', concentra, ' ', forma, ' ', presentacion ) AS nombreItem"),'coditem as id')
                            ->first();
                          
                        }
                        if(!is_null($nombre_item)){
                            DB::connection('pgsql')->rollback();                     
                            return (['mensaje'=>'No existe stock suficiente para el item '.$nombre_item->nombreItem,'error'=>true]);
                        }
                       
                    }else{

                        //procedemos a realizar la entrega (restamos de la bodega de farmacia)                                   
                        $StockAhora=$actualizaStockOld->existencia;
                        $nuevoStock_act=$StockAhora - $cantida_ent;
                        $actualizaStockOld->existencia=$nuevoStock_act;  
                        $actualizaStockOld->fecha=date('Y-m-d');
                        $actualizaStockOld->idusuario=auth()->user()->id;
                        $actualizaStockOld->save();

                        $ultimo=Existencia::orderBy('idexistencia','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->idexistencia+1;
                        } 
                        //actualizamos la tabla pedido
                        $Pedido=PedidoBodegaGral::where('iddetallecomprobante', $data->iddetalle_comprobante)
                        ->first();
                       
                        $Pedido->cantidad_entregada=$cantida_ent;
                        $Pedido->id_aprueba=auth()->user()->id;
                        $Pedido->fecha_aprueba=date('Y-m-d H:i:s');
                        $Pedido->estado="Entregado";
                        $Pedido->save();

                        $area_solicita=$data->comprobante->nomarea->descripcion; 
                        
                        //restamos la existencia de farmacia
                        $existencia=new Existencia();
                        $existencia->idexistencia=$suma;
                        $existencia->iddetalle_comprobante=$data->iddetalle_comprobante;
                        $existencia->lote=$Pedido->lote;
                        $existencia->resta=$cantida_ent;
                        $existencia->tipo="Egreso Bodega desde ".$area_solicita;
                        $existencia->cod="EABFA"; //EGRESO BODEGA A FARMACIA
                        $existencia->fecha_hora=date('Y-m-d H:i:s');
                        $existencia->fecha_elaboracion=$Pedido->fecha_elabora;
                        $existencia->fecha_caducidad=$Pedido->fecha_caducidad;
                        $existencia->fecha=date('Y-m-d');
                        $existencia->idusuario=auth()->user()->id;
                        $existencia->id_pedido=$Pedido->idpedido_bod_gral;
                        $existencia->idusuario_solicita=$Pedido->id_solicita;
                        $existencia->idbodprod=$actualizaStockOld->idbodprod;
                        $existencia->save();   

                        //sumamos a la bodega de dialisis
                        $ultimo =ProductoBodega::orderBy('idbodprod','desc')->first();  
                        if(is_null($ultimo)){
                            $sumauno=1;
                        }else{
                            $sumauno=$ultimo->idbodprod;
                        }  
                                                                    
                        $ProductoBodegaDialisis=new ProductoBodega();
                        $ProductoBodegaDialisis->idbodprod=$sumauno+1;
                        $ProductoBodegaDialisis->idprod=$actualizaStockOld->idprod;
                        $ProductoBodegaDialisis->idbodega=31;
                        $ProductoBodegaDialisis->existencia=$cantida_ent;
                        $ProductoBodegaDialisis->precio=number_format(($actualizaStockOld->precio),4,'.', '');
                        $ProductoBodegaDialisis->precio2=0;
                        $ProductoBodegaDialisis->fecha=date('Y-m-d');
                        $ProductoBodegaDialisis->idusuario=auth()->user()->id;
                        $ProductoBodegaDialisis->sistema_old="ENLINEA";
                        $ProductoBodegaDialisis->save();

                        //sumamos la existencia en esa bodega
                        $ultimo=Existencia::orderBy('idexistencia','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->idexistencia+1;
                        }
                        $existenciaDialisis=new Existencia();
                        $existenciaDialisis->idexistencia=$suma;
                        $existenciaDialisis->iddetalle_comprobante=$data->iddetalle_comprobante;
                        $existenciaDialisis->lote=$Pedido->lote;
                        $existenciaDialisis->suma=$cantida_ent;
                        $existenciaDialisis->tipo="Ingreso a Bodega Dialisis";
                        $existenciaDialisis->fecha_hora=date('Y-m-d H:i:s');
                        $existenciaDialisis->fecha_elaboracion=$Pedido->fecha_elabora;
                        $existenciaDialisis->fecha_caducidad=$Pedido->fecha_caducidad;
                        $existenciaDialisis->cod="IABD";
                        $existenciaDialisis->idusuario=auth()->user()->id;
                        $existenciaDialisis->idbodprod=$ProductoBodegaDialisis->idbodprod;
                        $existenciaDialisis->save();   


                        if($Pedido->iditem >=30000){
                            //actualizar la tabla principal de medicamentos e insumos el stock en farmacia
                            $insumo= Insumo::where('codinsumo',$Pedido->iditem)
                            ->first();
                            $stock_Actual=$insumo->stock_farm_dialisis;
                            $insumo->stock_farm_dialisis=$stock_Actual - $cantida_ent;
                            $insumo->save(); 

                            // comprobamos que el stock actual no sea menor a lo q se va a quitar
                            if($stock_Actual < $cantida_ent){
                                DB::connection('pgsql')->rollback();
                                return (['mensaje'=>'El stock actual del item '.$insumo->insumo. " es  ".$stock_Actual,'error'=>true]); 
                            }
                        }else{
                            
                            $medicamentos= Medicamento::where('coditem',$Pedido->iditem)
                            ->first();
                            $stock_Actual=$medicamentos->stock_farm_dialisis;
                            $medicamentos->stock_farm_dialisis=$stock_Actual - $cantida_ent;
                            $medicamentos->save(); 

                            // comprobamos que el stock actual no sea menor a lo q se va a quitar
                            if($stock_Actual < $cantida_ent){
                                DB::connection('pgsql')->rollback();
                                return (['mensaje'=>'El stock actual del item '.$medicamentos->nombre. " es  ".$stock_Actual,'error'=>true]); 
                            }
                        }

                        $total_comprobante=$total_comprobante + $ProductoBodegaDialisis->precio;
                            
                    }
                }
                $actualizaEntrega=EntregaPaquete::where('idcomprobante',$actualizaComprobante->idcomprobante)
                ->update(['fecha_aprueba'=>date('Y-m-d H:i:s'), 'id_aprueba'=>auth()->user()->id]);

                $actualizaComprobante->codigo_old="Entregado";
                $actualizaComprobante->fecha_aprobacion=date('Y-m-d H:i:s');            
                $actualizaComprobante->id_usuario_aprueba=auth()->user()->id;

                $actualizaComprobante->subtotal=number_format(($total_comprobante),2,'.', '');
                $actualizaComprobante->total=number_format(($total_comprobante),2,'.', '');

                $actualizaComprobante->id_usuario_aprueba=auth()->user()->id;
                $actualizaComprobante->save();

                //si no se completo la entrega de un item
                $pedidos=PedidoBodegaGral::whereIN('iddetallecomprobante', $iddetalle_array)
                ->where('estado','Solicitado')->first();
                if(!is_null($pedidos)){
                   
                    return (['mensaje'=>'No se pudo completar la entrega total de los items','error'=>true]); 
                }
               
                return (['mensaje'=>'Informacion ingresada exitosamente','error'=>false]);
  

            } catch (\Throwable $e) {
                DB::connection('pgsql')->rollback();
                Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
                return (['mensaje'=>'Ocurrió un error,intentelo más tarde','error'=>true]); 
            }
        });
        return ($transaction);
    }
}