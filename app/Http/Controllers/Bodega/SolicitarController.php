<?php

namespace App\Http\Controllers\Bodega;
use App\Http\Controllers\Controller;
use App\Models\Personal\Especialidad;
use \Log;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator; 
use App\Models\Bodega\Comprobante; 
use App\Models\Bodega\DetalleComprobante; 
use App\Models\Bodega\Existencia; 
use App\Models\Bodega\Medicamento; 
use App\Models\Bodega\TipoComprobante;
use App\Models\Bodega\TipoComprobanteOld;
use App\Models\Bodega\LoteProducto;
use App\Models\Bodega\ProductoBodega;
use App\Models\Bodega\Insumo;
use App\Models\Bodega\Item;
use App\Models\Bodega\FarmLaboratorio;
use App\Models\Bodega\Laboratorio;
use App\Models\Bodega\ComprobanteOld; 
use App\Models\Bodega\DetalleComprobanteOld;
use App\Models\Bodega\PedidoBodegaGral;
use App\Models\Bodega\BodegaUsuario;
use App\Models\Bodega\Proteccion;
use Storage;
use SplFileInfo;

class SolicitarController extends Controller
{
    
    public function index(){
      
        $verificaBodega=BodegaUsuario::where('idusuario',auth()->user()->id)
        ->get();
        $idbodegas=[];
        foreach($verificaBodega as $data){
            array_push($idbodegas, $data->idbodega);
        }
        
        $bodega= DB::connection('pgsql')->table('bodega.bodega')
        ->whereIn('idbodega',$idbodegas)
        ->where('estado',1)
        ->get();               

        // return view('gestion_bodega.pedido_area',[
        //     "bodega"=>$bodega
        // ]);

        return view('gestion_bodega.pedido_area_new',[
            "bodega"=>$bodega
        ]);
    }

    public function historialPedido($usu, $iditem){
        try{
            $historial= DB::connection('pgsql')->table('bodega.pedido_bod_gral as ped')
            ->leftJoin('public.users as usu', 'usu.id','ped.id_aprueba')
            ->leftJoin('inventario.persona as per', 'per.idper','usu.id_persona')
            ->where('id_solicita',$usu)
            ->where('ped.iditem',$iditem)
            ->where('ped.estado','Entregado')
            ->select('cantidad_pedida', 'cantidad_entregada','fecha_solicita', 'fecha_aprueba',DB::raw("CONCAT(per.ape1,' ', per.ape2,' ', per.nom1,' ', per.nom2) AS respo"),'ped.idpedido_bod_gral')
            ->get();
            return response()->json([
                'error'=>false,
                'resultado'=>$historial
            ]);
        }catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function misPedidos(){
        try{
            $bodega= DB::connection('pgsql')->table('bodega.bodega')
            ->where('estado',1)
            ->get();
            
            // return view('gestion_bodega.listado_pedido_solicita',[
            //     "bodega"=>$bodega
            // ]);

            return view('gestion_bodega.listado_pedido_solicita_new',[
                "bodega"=>$bodega
            ]);

        }catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function buscarItemsStock($text, $idbodega){
        try{
           
            $items= DB::connection('pgsql')->table('bodega.items as item')
            ->join('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
            ->join('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
            ->where('item.stock','>',0)
            ->where(function($c)use($text) {
                $c->where('item.descri', 'ilike', '%'.$text.'%');
            })
            ->where('item.idbodega',$idbodega)
            ->select('lot.lote', 'lot.fcaduca','lot.regsan',DB::raw("CONCAT(item.descri,' - ', item.presen) AS detalle"),'item.stock as existencia','proxbode.idprod', 'proxbode.precio as p', 'lot.felabora','proxbode.idbodprod','item.codi_it')
            ->distinct('codi_it')
            ->get();

            foreach($items as $key=> $data){
                $prodBod= DB::connection('pgsql')->table('bodega.prodxbod')
                ->select(DB::raw('sum("existencia") as totalitems'),DB::raw('sum("precio") as precioitems'),
                DB::raw('count(*) as cant'),'idprod')
                ->groupby('idprod')
                ->where('idprod',$data->codi_it)
                ->where('idbodega',$idbodega)
                ->first();

                $precio_cal=$prodBod->precioitems / $prodBod->cant;

                $items[$key]->precio=number_format(($precio_cal),2,'.', '');
            }

            return response()->json([
                'error'=>false,
                'resultado'=>$items
            ]);
               
        }catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }
    
    public function buscarProteccionStock($text, $idbodega){
        try{
           
            $items= DB::connection('pgsql')->table('bodega.proteccion as item')
            ->join('bodega.prodxbod as proxbode', 'proxbode.idprod','item.id')
            ->join('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
            ->where('item.stock','>',0)
            ->where(function($c)use($text) {
                $c->where('item.descri', 'ilike', '%'.$text.'%');
            })
            ->where('item.idbodega',$idbodega)
            ->select('lot.lote', 'lot.fcaduca','lot.regsan',DB::raw("CONCAT(item.descri,' - ', item.presen) AS detalle"),'item.stock as existencia','proxbode.idprod', 'proxbode.precio as p', 'lot.felabora','proxbode.idbodprod','item.id')
            ->distinct('id')
            ->get();

            foreach($items as $key=> $data){
                $prodBod= DB::connection('pgsql')->table('bodega.prodxbod')
                ->select(DB::raw('sum("existencia") as totalitems'),DB::raw('sum("precio") as precioitems'),
                DB::raw('count(*) as cant'),'idprod')
                ->groupby('idprod')
                ->where('idprod',$data->id)
                ->where('idbodega',$idbodega)
                ->first();

                $precio_cal=$prodBod->precioitems / $prodBod->cant;

                $items[$key]->precio=number_format(($precio_cal),2,'.', '');
            }

            return response()->json([
                'error'=>false,
                'resultado'=>$items
            ]);
               
        }catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }
    
    public function guardarPedidoArea(Request $request){
       
        $validator = Validator::make($request->all(), [
            'cmb_bodega' => 'required',           
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario','error'=>true]);
        }

        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{

                $tipocomp_old= TipoComprobanteOld::where('idtipocom',11)
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
                $comprobante->idbodega=$request->cmb_bodega;

                $comprobante->observacion=$request->motivo;
                $comprobante->subtotal=$request->total_suma;
                $comprobante->total=$request->total_suma;              
                $comprobante->id_usuario_ingresa=auth()->user()->id;
                $comprobante->area=auth()->user()->persona->id_area;
                $comprobante->codigo_old="PedidoA";   

                if($comprobante->save()){

                    //datos detalle
                    $idmedicina_selecc=$request->idmedicina_selecc;
                    $idbodega_selecc=$request->idbodega_selecc; 
                    $cantidad=$request->cantidad;
                    $precio=$request->precio;
                   
                    $fecha_elab_=$request->fecha_elab_;
                    $fecha_caduc=$request->fecha_caduc;
                    $lote=$request->lote;
                    $reg_sani=$request->reg_sani;                    
                    $total=$request->total;         
                    
                    $idbodega_producto=$request->idbodega_producto;
                  
                    $cont=0;
                  
                    //registramos los detalles localmente
                    while($cont < count($idmedicina_selecc)){
                        $nuevoStock=0;
                        $nuevoStock_act=0;

                        $ultimo=DetalleComprobante::orderBy('iddetalle_comprobante','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->iddetalle_comprobante+1;
                        }
                        $total_item=0;
                        $total_item=$cantidad[$cont]*$precio[$cont];

                        $detalles=new DetalleComprobante();
                        $detalles->iddetalle_comprobante=$suma;
                        $detalles->idcomprobante=$comprobante->idcomprobante;
                        $detalles->id_item=$idmedicina_selecc[$cont];
                        $detalles->id_bodega=$idbodega_selecc[$cont];
                        $detalles->cantidad=$cantidad[$cont];
                        $detalles->precio=number_format(($precio[$cont]),2,'.', '');
                        $detalles->descuento=0;
                        $detalles->total=number_format(($total_item),2,'.', '');
                        $detalles->iva=0;
                        $detalles->fecha=date('Y-m-d H:i:s');
                        $detalles->save(); 

                        $ultimo=PedidoBodegaGral::orderBy('idpedido_bod_gral','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->idpedido_bod_gral+1;
                        }

                        //
                        $pedido_temp=new PedidoBodegaGral();
                        $pedido_temp->idpedido_bod_gral=$suma;
                        $pedido_temp->iddetallecomprobante=$detalles->iddetalle_comprobante;
                        $pedido_temp->lote=$lote[$cont];
                        $pedido_temp->fecha_caducidad=$fecha_caduc[$cont];
                        $pedido_temp->fecha_elabora=$fecha_elab_[$cont];
                        $pedido_temp->cantidad_pedida=$cantidad[$cont];
                        $pedido_temp->idbodega=$detalles->id_bodega;
                        $pedido_temp->id_solicita=auth()->user()->id;
                        $pedido_temp->fecha_solicita=date('Y-m-d H:i:s');
                        $pedido_temp->iditem=$detalles->id_item;;

                        // $pedido_temp->idbodpro=$idbodega_producto[$cont];
                        $pedido_temp->estado="Solicitado";
                        $pedido_temp->save();                       
                        $cont=$cont+1;
                    } 
                    
                  
                    $tipocomp_old->numcom=$comprobante->secuencial;
                    $tipocomp_old->save();

                    //si tofdo ok el comprobante se crea
                    $comprobante_crear=Comprobante::find($comprobante->idcomprobante);
                    $comprobante_crear->estado="Activo";
                    $comprobante_crear->codigo_old="Pedido";//pedido
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

    public function actualizaPedidoArea(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'bodega_seleccionda' => 'required',           
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario','error'=>true]);
        }

        $transaction=DB::connection('pgsql')->transaction(function() use ($request, $id){
            try{
                // $id=decrypt($id);
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
                $comprobante->idbodega=$request->bodega_seleccionda;
                $comprobante->observacion=$request->motivo;
                $comprobante->subtotal=$request->total_suma;
                $comprobante->total=$request->total_suma;              
                $comprobante->iduser_actualiza=auth()->user()->id;

                if($comprobante->save()){
                   
                    //datos detalle
                    $idmedicina_selecc=$request->idmedicina_selecc;
                    $idbodega_selecc=$request->idbodega_selecc; 
                    $cantidad=$request->cantidad;
                    $precio=$request->precio;
                   
                    $fecha_elab_=$request->fecha_elab_;
                    $fecha_caduc=$request->fecha_caduc;
                    $lote=$request->lote;
                    $reg_sani=$request->reg_sani;                    
                    $total=$request->total;         
                    
                    $idbodega_producto=$request->idbodega_producto;
                  
                    $cont=0;

                    //registramos los detalles localmente
                    while($cont < count($idmedicina_selecc)){
                        $nuevoStock=0;
                        $nuevoStock_act=0;

                        $ultimo=DetalleComprobante::orderBy('iddetalle_comprobante','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->iddetalle_comprobante+1;
                        }
                        $total_item=0;
                        $total_item=$cantidad[$cont]*$precio[$cont];

                        $detalles=new DetalleComprobante();
                        $detalles->iddetalle_comprobante=$suma;
                        $detalles->idcomprobante=$comprobante->idcomprobante;
                        $detalles->id_item=$idmedicina_selecc[$cont];
                        $detalles->id_bodega=$idbodega_selecc[$cont];
                        $detalles->cantidad=$cantidad[$cont];
                        $detalles->precio=number_format(($precio[$cont]),2,'.', '');
                        $detalles->descuento=0;
                        $detalles->total=number_format(($total_item),2,'.', '');
                        $detalles->iva=0;
                        $detalles->fecha=date('Y-m-d H:i:s');
                        $detalles->save(); 

                        if($bodega==19 || $bodega==23 || $bodega==24){ //lab dialisis
                            $comprobarStock=Laboratorio::where('id',$detalles->id_item)
                            ->first();
                            $stock_Actual=$comprobarStock->stock_dialisis;
                        }else if ($bodega==8 || $bodega==13 || $bodega==14 ){ //lab gral
                            $comprobarStock=Laboratorio::where('id',$detalles->id_item)
                            ->first();
                            $stock_Actual=$comprobarStock->stock;
                        }else if ($bodega==30){
                            $comprobarStock=Proteccion::where('id',$detalles->id_item)
                            ->first();
                            $stock_Actual=$comprobarStock->stock;
                        }else{
                            $comprobarStock=Item::where('codi_it',$detalles->id_item)
                            ->first();
                            $stock_Actual=$comprobarStock->stock;
                        }
                           
                        // comprobamos que el stock actual no sea menor a lo q se va a quitar
                        if($stock_Actual < $detalles->cantidad){
                            DB::connection('pgsql')->rollback();
                            return (['mensaje'=>'El stock actual del item '.$comprobarStock->descri. " es  ".$stock_Actual,'error'=>true]); 
                        }
                       
                        $ultimo=PedidoBodegaGral::orderBy('idpedido_bod_gral','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->idpedido_bod_gral+1;
                        }

                        //
                        $pedido_temp=new PedidoBodegaGral();
                        $pedido_temp->idpedido_bod_gral=$suma;
                        $pedido_temp->iddetallecomprobante=$detalles->iddetalle_comprobante;
                        $pedido_temp->lote=$lote[$cont];
                        $pedido_temp->fecha_caducidad=$fecha_caduc[$cont];
                        $pedido_temp->fecha_elabora=$fecha_elab_[$cont];
                        $pedido_temp->cantidad_pedida=$cantidad[$cont];
                        $pedido_temp->idbodega=$detalles->id_bodega;
                        $pedido_temp->id_solicita=auth()->user()->id;
                        $pedido_temp->fecha_solicita=date('Y-m-d H:i:s');
                        $pedido_temp->iditem=$detalles->id_item;;

                        // $pedido_temp->idbodpro=$idbodega_producto[$cont];
                        $pedido_temp->estado="Solicitado";
                        $pedido_temp->save();                       
                        $cont=$cont+1;
                    } 
                  


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

    public function validaPedidoArea(Request $request){
        $array_iddetalle=$request->array_iddetalle;
        $array_cantidad=$request->cantidad_validada;
        if(sizeof($array_iddetalle)==0 || sizeof($array_cantidad)==0){
            return (['mensaje'=>'Debe validar al menos un item','error'=>true]);
        }

        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{
                $comprobar=PedidoBodegaGral::whereIn('iddetallecomprobante', $request->array_iddetalle)
                ->where('estado',"Entregado")
                ->first();
                              
                if(!is_null($comprobar)){
                    return (['mensaje'=>'El pedido ya fue entregado','error'=>true]);
                }

                foreach($request->array_iddetalle as $key=> $iddetalle){
                    $validaPedido=PedidoBodegaGral::where('iddetallecomprobante', $iddetalle)->first();
                    //si no existe es xq lo elimino de la lista (actualizo)
                    if(is_null($validaPedido)){
                        DB::connection('pgsql')->rollback();
                        return (['mensaje'=>'El pedido fue actualizado, revise el detalle de nuevo','error'=>true, 'act'=>'S']);
                    }

                    //si actualizo la cantidad pedido
                    // if($validaPedido->cantidad_pedida != $request->cantidad_validada[$key]){
                    //     DB::connection('pgsql')->rollback();
                    //     return (['mensaje'=>'La cantidad pedida de uno de los items fue actualizado, revise el detalle de nuevo','error'=>true, 'act'=>'S']);
                    // }
                  
                   
                    $validaPedido->cantidad_entregada=$request->cantidad_validada[$key];
                    $validaPedido->id_aprueba=auth()->user()->id;
                    $validaPedido->fecha_aprueba=date('Y-m-d H:i:s');
                    $validaPedido->estado="Entregado";
                    $validaPedido->save();

                    $comprobar=DetalleComprobante::with('comprobante')
                    ->where('iddetalle_comprobante',$iddetalle)
                    ->first();

                    $idItem=$comprobar->id_item;
                    $idBodegaItem=$comprobar->id_bodega;

                    $actualizaStockPB =ProductoBodega::where('idprod',$idItem)
                    ->where('idbodega',$idBodegaItem)
                    ->where('existencia','>',0)
                    ->orderBy('idbodprod','asc')
                    ->get();
                  

                    $quita=0;
                    $cantida_ent=$validaPedido->cantidad_entregada;

                    foreach($actualizaStockPB as $data){
                      
                        if($cantida_ent < $data->existencia){

                            $actualizaStockOld =ProductoBodega::where('idbodprod',$data->idbodprod)
                            ->first();      
                                              
                            $nuevoStock=$actualizaStockOld->existencia;
                            $nuevoStock_act=$nuevoStock - $cantida_ent;
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
                            
                            $area_solicita=$comprobar->comprobante->nomarea->descripcion; 
                                                      
                            $existencia=new Existencia();
                            $existencia->idexistencia=$suma;
                            $existencia->iddetalle_comprobante=$iddetalle;
                            $existencia->lote=$validaPedido->lote;
                            $existencia->resta=$cantida_ent;
                            $existencia->tipo="Egreso Bodega desde ".$area_solicita;
                            $existencia->cod="EABA";
                            $existencia->fecha_hora=date('Y-m-d H:i:s');
                            $existencia->fecha_elaboracion=$validaPedido->fecha_elabora;
                            $existencia->fecha_caducidad=$validaPedido->fecha_caducidad;
                            $existencia->fecha=date('Y-m-d');
                            $existencia->idusuario=auth()->user()->id;
                            $existencia->id_pedido=$validaPedido->idpedido_bod_gral;
                            $existencia->idusuario_solicita=$validaPedido->id_solicita;
                            $existencia->idbodprod=$actualizaStockOld->idbodprod;
                            $existencia->save();   

                            break;

                        }else{
                            
                            $actualizaStockOld =ProductoBodega::where('idbodprod',$data->idbodprod)
                            ->first();
                             
                            $nuevoStock=$actualizaStockOld->existencia;
                            $nuevoStock_act=$nuevoStock - $cantida_ent;
                            if($nuevoStock_act<0){
                                $nuevoStock_act=0;
                            }
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
                            
                            $area_solicita=$comprobar->comprobante->nomarea->descripcion;                        
                            
                            $existencia=new Existencia();
                            $existencia->idexistencia=$suma;
                            $existencia->iddetalle_comprobante=$iddetalle;
                            $existencia->lote=$validaPedido->lote;
                            // $existencia->resta= $cantida_ent;
                            $existencia->resta= $nuevoStock;
                            $existencia->tipo="Egreso Bodega desde ".$area_solicita;
                            $existencia->cod="EABA";
                            $existencia->fecha_hora=date('Y-m-d H:i:s');
                            $existencia->fecha_elaboracion=$validaPedido->fecha_elabora;
                            $existencia->fecha_caducidad=$validaPedido->fecha_caducidad;
                            $existencia->fecha=date('Y-m-d');
                            $existencia->idusuario_solicita=$validaPedido->id_solicita;
                            $existencia->id_pedido=$validaPedido->idpedido_bod_gral;
                            $existencia->idusuario=auth()->user()->id;
                          
                            $existencia->idbodprod=$actualizaStockOld->idbodprod;
                            $existencia->save();   

                            $cantida_ent=$cantida_ent - $data->existencia;

                        }

                    }

                    if($idBodegaItem==30){//proteccion
                        $actualizaItem=Proteccion::where('id',$comprobar->id_item)
                        ->first();
                        $stock_Actual=$actualizaItem->stock;
                        $actualizaItem->stock=$stock_Actual - $request->cantidad_validada[$key];
                        $actualizaItem->save(); 

                        // comprobamos que el stock actual no sea menor a lo q se va a quitar
                        if($stock_Actual < $request->cantidad_validada[$key]){
                            DB::connection('pgsql')->rollback();
                            return (['mensaje'=>'El stock actual del item '.$actualizaItem->descri. " es  ".$stock_Actual,'error'=>true]); 
                        }

                    }else{

                        $actualizaItem=Item::where('codi_it',$comprobar->id_item)
                        ->first();
                        $stock_Actual=$actualizaItem->stock;
                        $actualizaItem->stock=$stock_Actual - $request->cantidad_validada[$key];
                        $actualizaItem->save(); 

                        // comprobamos que el stock actual no sea menor a lo q se va a quitar
                        if($stock_Actual < $request->cantidad_validada[$key]){
                            DB::connection('pgsql')->rollback();
                            return (['mensaje'=>'El stock actual del item '.$actualizaItem->descri. " es  ".$stock_Actual,'error'=>true]); 
                        }
                    }

                     
                }    
                $compr=Comprobante::where('idcomprobante',$comprobar->idcomprobante)->first();
                $compr->codigo_old="EntregadoB";
                $compr->id_usuario_aprueba=auth()->user()->id;
                $compr->fecha_aprobacion=date('Y-m-d H:i:s');
                $compr->save();
                
                return (['mensaje'=>'Items entregado exitosamente','error'=>false]);

            } catch (\Throwable $e) {
                DB::connection('pgsql')->rollback();
                Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
                return (['mensaje'=>'Ocurrió un error,intentelo más tarde','error'=>true]); 
            }
        });
        return ($transaction);
    }
    
}
