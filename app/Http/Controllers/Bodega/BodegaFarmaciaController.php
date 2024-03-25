<?php

namespace App\Http\Controllers\Bodega;
use App\Http\Controllers\Controller;
use App\Models\Personal\Especialidad;
use \Log;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator; 
use App\Models\Bodega\Comprobante; 
use App\Models\Bodega\ComprobanteReceta;
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
use App\Models\Bodega\InventarioComprobante;
use App\Models\Personal\Area;
use Storage;
use SplFileInfo;
use App\Models\Personal\UsuarioPerfil;
use App\Models\Personal\Perfil;
use App\Models\User;
use App\Http\Controllers\Bodega\PedidoBodegaController;

use Hash;

class BodegaFarmaciaController extends Controller
{
    private $objPedidos = null;
    
    public function __construct(){
        try{
            $this->objPedidos= new PedidoBodegaController();
                   
        }catch (\Throwable $e) {
            Log::error('ReportesExcelController => index => mensaje => '.$e->getMessage());
            return back();
        }
           
    
    }

    public function index(){

        // $comprobantePediatria=Comprobante::with('nomarea')
        // ->whereHas('nomarea', function ($q){
        //     $q->where('descripcion','PEDIATRIA');
        // })
        // // ->update(['area'=>53]);
        // ->get();

       
        $bodega= DB::connection('pgsql')->table('bodega.bodega')
        ->where('idtipobod',1)
        ->whereIn('idbodega',[19,23,24])
        ->where('estado',1)
        ->get();

        return view('gestion_farmacia.pedido_bodega_general',[
            "bodega"=>$bodega
       
        ]);
    }
    // Pedidos desde farmacia a bodega general
    public function guardarPedidoBodega(Request $request){
      
        $validator = Validator::make($request->all(), [
            'cmb_bodega' => 'required',           
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario','error'=>true]);
        }

        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{
               
                $tipocomp_old= TipoComprobanteOld::where('idtipocom',3)
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
                // $comprobante->codigo_old="PedidoF";   

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
                        $total_item=$precio[$cont] * $cantidad[$cont];
                        $detalles=new DetalleComprobante();
                        $detalles->iddetalle_comprobante=$suma;
                        $detalles->idcomprobante=$comprobante->idcomprobante;
                        $detalles->id_item=$idmedicina_selecc[$cont];
                        $detalles->id_bodega=$idbodega_selecc[$cont];
                        $detalles->cantidad=$cantidad[$cont];
                        $detalles->precio=number_format(($precio[$cont]),4,'.', '');
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
                        $pedido_temp->idbodpro=$idbodega_producto[$cont];
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

    //pedidos a farmacia
    public function guardarPedidoBodegaFarm(Request $request){
        
        $validator = Validator::make($request->all(), [
            'cmb_bodega' => 'required',           
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario','error'=>true]);
        }

        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{
                if($request->cmb_bodega==22 || $request->cmb_bodega==25 || $request->cmb_bodega==26){
                    //bodega lab dialisis
                    $tipo_comp=17;
                }else if($request->cmb_bodega==27 || $request->cmb_bodega==28 || $request->cmb_bodega==29){
                    //bodega lab gral
                    $tipo_comp=16;
                }else if($request->cmb_bodega==21){
                    //bodega lab gral
                    $tipo_comp=17;
                }else{
                    return (['mensaje'=>'No se encontro tipo comprobante para la bodega seleccionada','error'=>true]); 
                }

                $tipocomp_old= TipoComprobanteOld::where('idtipocom',$tipo_comp)
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
                        $total_item=$precio[$cont] * $cantidad[$cont];
                        $detalles=new DetalleComprobante();
                        $detalles->iddetalle_comprobante=$suma;
                        $detalles->idcomprobante=$comprobante->idcomprobante;
                        $detalles->id_item=$idmedicina_selecc[$cont];
                        $detalles->id_bodega=$idbodega_selecc[$cont];
                        $detalles->cantidad=$cantidad[$cont];
                        $detalles->precio=number_format(($precio[$cont]),4,'.', '');
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
                        $pedido_temp->idbodpro=$idbodega_producto[$cont];
                        $pedido_temp->estado="Solicitado";
                        $pedido_temp->save();                       
                        $cont=$cont+1;
                    } 
                    
                  
                    $tipocomp_old->numcom=$comprobante->secuencial;
                    $tipocomp_old->save();

                    //si tofdo ok el comprobante se crea
                    $comprobante_crear=Comprobante::find($comprobante->idcomprobante);
                    $comprobante_crear->estado="Activo";
                    $comprobante_crear->codigo_old="PedidoAFarm";//pedido
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
    // pedidos insumos
    public function guardarPedidoBodegaFarmInsumo(Request $request){
        
        $validator = Validator::make($request->all(), [
            'cmb_bodega' => 'required',           
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario','error'=>true]);
        }

        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{
                if($request->cmb_bodega==21){
                    //bodega insumos dialisis
                    $tipo_comp=19;
                }else if($request->cmb_bodega==7){
                    //bodega ins gral
                    $tipo_comp=20;
                }else if($request->cmb_bodega==6){
                    //bodega med gral
                    $tipo_comp=23;
                }else{
                    return (['mensaje'=>'No se encontro tipo comprobante para la bodega seleccionada','error'=>true]); 
                }

                $tipocomp_old= TipoComprobanteOld::where('idtipocom',$tipo_comp)
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
                        $total_item=$precio[$cont] * $cantidad[$cont];
                        $detalles=new DetalleComprobante();
                        $detalles->iddetalle_comprobante=$suma;
                        $detalles->idcomprobante=$comprobante->idcomprobante;
                        $detalles->id_item=$idmedicina_selecc[$cont];
                        $detalles->id_bodega=$idbodega_selecc[$cont];
                        $detalles->cantidad=$cantidad[$cont];
                        $detalles->precio=number_format(($precio[$cont]),4,'.', '');
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
                        $pedido_temp->idbodpro=$idbodega_producto[$cont];
                        $pedido_temp->estado="Solicitado";
                        $pedido_temp->save();                       
                        $cont=$cont+1;
                    } 
                    
                  
                    $tipocomp_old->numcom=$comprobante->secuencial;
                    $tipocomp_old->save();

                    //si tofdo ok el comprobante se crea
                    $comprobante_crear=Comprobante::find($comprobante->idcomprobante);
                    $comprobante_crear->estado="Activo";
                    $comprobante_crear->codigo_old="PedidoAFarm";//pedido
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

    public function actualizaPedidoLab(Request $request, $id){

       
        $validator = Validator::make($request->all(), [
            'bodega_seleccionda' => 'required',           
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario','error'=>true]);
        }

        $transaction=DB::connection('pgsql')->transaction(function() use ($request, $id){
            try{

                $comprobante=Comprobante::with('detalle')->where('idcomprobante',$id)->first();
                $bodega=$comprobante->idbodega;   

                if($comprobante->codigo_old == "Anulado"){
                    return (['mensaje'=>'El pedido fue anulado y no se puede actualizar','error'=>true, 'ent'=>'S']);
                }   
                
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
                            $comprobarStock=ProductoBodega::where('idbodprod',$idbodega_producto[$cont])
                            ->first();
                            $stock_Actual=$comprobarStock->existencia;
                            $produc=Laboratorio::where('id',$comprobarStock->idprod)
                            ->first();
                            $item_name=$produc->descri;
                           
                        }else if ($bodega==8 || $bodega==13 || $bodega==14 ){ //lab gral

                            $comprobarStock=ProductoBodega::where('idbodprod',$idbodega_producto[$cont])
                            ->first();
                            $stock_Actual=$comprobarStock->existencia;

                            $produc=Laboratorio::where('id',$comprobarStock->idprod)
                            ->first();

                            $item_name=$produc->descri;
                           
                        }else if ($bodega==2 || $bodega==18){ //insumos

                            $comprobarStock=ProductoBodega::where('idbodprod',$idbodega_producto[$cont])
                            ->first();
                            $stock_Actual=$comprobarStock->existencia;
                            
                            $produc=Insumo::where('codinsumo',$comprobarStock->idprod)
                            ->first();

                            $item_name=$produc->insumo;
                          
                        }else if ($bodega==1 || $bodega==17){ //medicamnetos

                            $comprobarStock=ProductoBodega::where('idbodprod',$idbodega_producto[$cont])
                            ->first();
                            $stock_Actual=$comprobarStock->existencia;
                            
                            $produc=Medicamento::where('coditem',$comprobarStock->idprod)
                            ->first();

                            $item_name=$produc->nombre." ".$produc->concentra." ".$produc->forma." ".$produc->presentacion;
                          
                        }else{
                            $comprobarStock=Item::where('codi_it',$detalles->id_item)
                            ->first();
                            $stock_Actual=$comprobarStock->stock;
                        }
                                     
                        // comprobamos que el stock actual no sea menor a lo q se va a quitar
                        if($stock_Actual < $detalles->cantidad){
                           
                            DB::connection('pgsql')->rollback();
                            return (['mensaje'=>'El stock actual del item '.$item_name. " es  ".$stock_Actual,'error'=>true]); 
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

                        $pedido_temp->idbodpro=$idbodega_producto[$cont];
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

    public function actualizaPedidoLabDialisis(Request $request, $id){

      
        $validator = Validator::make($request->all(), [
            'bodega_seleccionda' => 'required',           
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario','error'=>true]);
        }

        $transaction=DB::connection('pgsql')->transaction(function() use ($request, $id){
            try{
               
                $comprobante=Comprobante::with('detalle')->where('idcomprobante',$id)->first();
               
                $bodega=$comprobante->idbodega; 
                if($comprobante->codigo_old == "Anulado"){
                    return (['mensaje'=>'El pedido fue anulado y no se puede actualizar','error'=>true, 'ent'=>'S']);
                }           

                if($comprobante->codigo_old == "PedidoAFarm" || $comprobante->codigo_old == "Pedido"){
                    
                }else{
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

                        if($bodega==22 || $bodega==25 || $bodega==26){ //lab dialisis
                            $comprobarStock=ProductoBodega::where('idbodprod',$idbodega_producto[$cont])
                            ->first();
                            $stock_Actual=$comprobarStock->existencia;
                            $produc=Laboratorio::where('id',$comprobarStock->idprod)
                            ->first();

                            $item_name=$produc->descri;
                          
                        }else if ($bodega==27 || $bodega==28 || $bodega==29 ){ //lab gral
                            $comprobarStock=ProductoBodega::where('idbodprod',$idbodega_producto[$cont])
                            ->first();
                            $stock_Actual=$comprobarStock->existencia;
                            $produc=Laboratorio::where('id',$comprobarStock->idprod)
                            ->first();

                            $item_name=$produc->descri;
                           
                        }else if ($bodega==21 || $bodega==18 || $bodega==7 || $bodega==2){ //insumo 
                            $comprobarStock=ProductoBodega::where('idbodprod',$idbodega_producto[$cont])
                            ->first();
                            $stock_Actual=$comprobarStock->existencia;
                            $produc=Insumo::where('codinsumo',$comprobarStock->idprod)
                            ->first();

                            $item_name=$produc->insumo;

                        }else if ($bodega==20 || $bodega==17 || $bodega==6 || $bodega==1){ //medicamnetos 
                            $comprobarStock=ProductoBodega::where('idbodprod',$idbodega_producto[$cont])
                            ->first();
                            $stock_Actual=$comprobarStock->existencia;
                            $produc=Medicamento::where('coditem',$comprobarStock->idprod)
                            ->first();
                            $item_name=$produc->nombre." ".$produc->concentra." ".$produc->forma." ".$produc->presentacion;
                        }else{
                         
                            DB::connection('pgsql')->rollback();
                            return (['mensaje'=>'Bodega no encontrada','error'=>true]);
                        }
                                              
                        // comprobamos que el stock actual no sea menor a lo q se va a quitar
                        if($stock_Actual < $detalles->cantidad){
                           
                            DB::connection('pgsql')->rollback();
                            return (['mensaje'=>'El stock actual del item '.$item_name. " es  ".$stock_Actual,'error'=>true]); 
                        }
                       
                        $ultimo=PedidoBodegaGral::orderBy('idpedido_bod_gral','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->idpedido_bod_gral+1;
                        }

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

                        $pedido_temp->idbodpro=$idbodega_producto[$cont];
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

    //egreso desde farmacia
    public function vistaEgreso(){
        $bodega= DB::connection('pgsql')->table('bodega.bodega')
        ->where('idtipobod',2)
        ->whereIn('idbodega',[6,7,20,21,22,25,26,27,28,29])
       
        ->where('estado',1)
        ->get();
        return view('gestion_farmacia.egreso_bodega_farmacia',[
            "bodega"=>$bodega
        ]);
    }

    

    public function buscarMedicamentosLote($text, $idbodega){
        try{
           
            $medicamentos= DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
            ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
            ->leftJoin('bodega.medicamentos as med', 'med.coditem','proxbode.idprod')
            ->where('proxbode.idbodega',$idbodega)
            ->where('proxbode.existencia','>',0)
            ->where(function($c)use($text) {
                $c->where('nombre', 'ilike', '%'.$text.'%');
            })
            // ->where('med.activo','VERDADERO')
            ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion) AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','med.coditem as coditem')
            // ->DISTINCT('lot.lote', 'lot.fcaduca','proxbode.existencia')
            ->orderby('lot.fcaduca','asc')
            ->get();
          
            
            foreach($medicamentos as $key=> $med){
                $insu=DB::connection('pgsql')->table('bodega.medicamentos')
                ->where('coditem', $med->coditem)->first();
             
                if($idbodega==20){ //farmacia insum dialisis
                    if(!is_null($insu)){
                        if($insu->stock_farm_dialisis>0){
                            $medicamentos[$key]->permitir="Si";
                            $medicamentos[$key]->stock=$insu->stock_farm_dialisis;
                        }else{
                            $medicamentos[$key]->permitir="No";
                            $medicamentos[$key]->stock=$insu->stock_farm_dialisis;
                        }
                    }else{
                        $medicamentos[$key]->permitir="No";
                    }
                }else{
                    // farmacia insumo gral
                    if(!is_null($insu)){
                        if($insu->stock>0){
                            $medicamentos[$key]->permitir="Si";
                            $medicamentos[$key]->stock=$insu->stock;
                        }else{
                            $medicamentos[$key]->permitir="No";
                            $medicamentos[$key]->stock=$insu->stock;
                        }
                    }else{
                        $medicamentos[$key]->permitir="No";
                    }
                }
               
            }

            return response()->json([
                'error'=>false,
                'resultado'=>$medicamentos
            ]);
               
        }catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function buscarInsumosLote($text, $idbodega){
        try{
           
            $medicamentos= DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
            ->join('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
            ->join('bodega.insumo as insu', 'insu.codinsumo','proxbode.idprod')
            ->where('proxbode.idbodega',$idbodega)
            ->where('proxbode.existencia','>',0)
            ->where(function($c)use($text) {
                $c->where('insu.insumo', 'ilike', '%'.$text.'%');
            })
           
            ->select('lot.lote', 'lot.fcaduca','lot.regsan', 'insu.insumo AS detalle','proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','insu.codinsumo','proxbode.idbodega','insu.stock')
            ->orderby('lot.fcaduca','asc')
            ->get();

            foreach($medicamentos as $key=> $med){
                $insu=DB::connection('pgsql')->table('bodega.insumo')
                ->where('codinsumo', $med->codinsumo)->first();
                
                if($idbodega==21){ //farmacia insum dialisis
                    if(!is_null($insu)){
                        if($insu->stock_farm_dialisis>0){
                            $medicamentos[$key]->permitir="Si";
                            $medicamentos[$key]->stock=$insu->stock_farm_dialisis;
                        }else{
                            $medicamentos[$key]->permitir="No";
                            $medicamentos[$key]->stock=$insu->stock_farm_dialisis;
                        }
                    }else{
                        $medicamentos[$key]->permitir="No";
                    }
                }else{
                    // farmacia insumo gral
                    if(!is_null($insu)){
                        if($insu->stock>0){
                            $medicamentos[$key]->permitir="Si";
                            $medicamentos[$key]->stock=$insu->stock;
                        }else{
                            $medicamentos[$key]->permitir="No";
                            $medicamentos[$key]->stock=$insu->stock;
                        }
                    }else{
                        $medicamentos[$key]->permitir="No";
                    }
                }
               
            }
            
            
            return response()->json([
                'error'=>false,
                'resultado'=>$medicamentos
            ]);
               
        }catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function buscarLaboratorioFarmLote($text, $idbodega){
        try{
          
            $medicamentos= DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
            ->join('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
            ->join('bodega.laboratorio as farm', 'farm.id','proxbode.idprod')
            ->where('proxbode.idbodega',$idbodega)
            ->where('proxbode.existencia','>',0)
            ->where(function($c)use($text) {
                $c->where('farm.descri', 'ilike', '%'.$text.'%');
            })
           
            ->select('lot.lote', 'lot.fcaduca','lot.regsan', 'farm.descri AS detalle','proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','farm.codigo','farm.id','proxbode.idbodega')
            ->orderby('lot.fcaduca','asc')
            ->get();
            

            foreach($medicamentos as $key=> $med){
                $lab=DB::connection('pgsql')->table('bodega.laboratorio')
                ->where('id', $med->id)->first();
                if($idbodega==22 || $idbodega==25 || $idbodega==26){ //farmacia lab dialisis
                    if(!is_null($lab)){
                        if($lab->stock_diali_farmacia>0){
                            $medicamentos[$key]->permitir="Si";
                            $medicamentos[$key]->stock_farmacia=$lab->stock_diali_farmacia;
                        }else{
                            $medicamentos[$key]->permitir="No";
                            $medicamentos[$key]->stock_farmacia=$lab->stock_diali_farmacia;
                        }
                    }else{
                        $medicamentos[$key]->permitir="No";
                    }
                }else{
                    // farmacia lab gral
                    if(!is_null($lab)){
                        if($lab->stock_farmacia>0){
                            $medicamentos[$key]->permitir="Si";
                            $medicamentos[$key]->stock_farmacia=$lab->stock_farmacia;
                        }else{
                            $medicamentos[$key]->permitir="No";
                            $medicamentos[$key]->stock_farmacia=$lab->stock_farmacia;
                        }
                    }else{
                        $medicamentos[$key]->permitir="No";
                    }
                }
               
            }
            
            
            return response()->json([
                'error'=>false,
                'resultado'=>$medicamentos
            ]);
               
        }catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function vistaDevolucion(){
        $bodega= DB::connection('pgsql')->table('bodega.bodega')
        ->where('idtipobod',2)
        ->where('estado',1)
        ->get();
        return view('gestion_farmacia.devolucion_farmacia_bodega',[
            "bodega"=>$bodega
        ]);
    }


    public function listadoEgreso(){
        return view('gestion_farmacia.listado_egreso_bod_farm');
    }
    public function cargaPaciente(Request $request){
    
        $data = [];
        if($request->has('q')){
            $search = $request->q;
            $data=DB::connection('pgsql')->table('esq_pacientes.pacientes')
            ->where('documento', 'ilike', '%'.$search.'%')
            ->orwhere(DB::raw("CONCAT(apellido1, ' ', apellido2, ' ', nombre1, ' ', nombre2)"), 'ilike', '%'.$search.'%')
            ->select('id_paciente','documento',DB::raw("CONCAT(apellido1,' ',apellido2,' ',nombre1,' ',nombre2) AS nombre_paciente"))
            ->take(10)->get();
        }
        
        return response()->json($data);
    }

    public function filtrarEgresoBodega($ini, $fin, $pac){
        
        try{
          
            $data= DB::connection('pgsql')->table('bodega.comprobante as comp')
            ->leftJoin('public.users as usu', 'usu.id','comp.id_usuario_ingresa')
            ->leftJoin('inventario.persona as per', 'per.idper','usu.id_persona')
            ->leftJoin('esq_pacientes.pacientes as paci', 'paci.id_paciente','comp.id_paciente')
            ->where(function($query)use($ini, $fin, $pac){
                // $query->whereBetween('comp.fecha',[$ini, $fin]);
                if($pac==0){
                    $query->whereDate('comp.fecha_aprobacion','>=',$ini)
                    ->WhereDate('comp.fecha_aprobacion','<=',$fin);
                }else{
                    $query->where('comp.id_paciente',$pac);
                }
                    
            })
            ->select('comp.descripcion','comp.secuencial','comp.fecha_hora','comp.observacion','comp.total','comp.id_usuario_ingresa', 'comp.idcomprobante',DB::raw("CONCAT(per.ape1,' ', per.ape2,' ', per.nom1,' ', per.nom2) AS responsable"),'comp.idbodega','comp.idtipo_comprobante','comp.tipoarea as areasel',DB::raw("CONCAT(paci.apellido1,' ', paci.apellido2,' ', paci.nombre1,' ', paci.nombre2) AS paciente"),'paci.documento')
            ->where('comp.estado','=','Activo')
            ->where('comp.codigo_old','<>','DevolverBodega')
            ->whereNotIn('comp.codigo_old',['PedidoAFarm','Revertido','Anulado'])
            ->whereIn('idtipo_comprobante',[5,6,8,15,16,17,18,19,20,21,22,23])
            ->get();
                 
            return response()->json([
                'error'=>false,
                'resultado'=>$data
            ]);
        }catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }
    
    
    public function guardarEgreso(Request $request){
       
        $validator = Validator::make($request->all(), [
            'cmb_bodega' => 'required',           
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario','error'=>true]);
        }

        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{

                if($request->cmb_tipo=='Devolucion'){
                    $idtipoSel=6; //trasnsferencia de farmacia a bodega
                }else if($request->cmb_tipo=='Externo'){
                    $idtipoSel=15; // egreso externo de farmacia
                }else{
                    $idtipoSel=5; //egreso interno
                }
               
                $tipocomp_old= TipoComprobanteOld::where('idtipocom',$idtipoSel)
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
                $comprobante->fecha_aprobacion=date('Y-m-d H:i:s');
                $comprobante->fecha=date('Y-m-d');
                $comprobante->idbodega=$request->cmb_bodega;
                $comprobante->observacion=$request->motivo;
                $comprobante->subtotal=$request->total_suma;
                $comprobante->total=$request->total_suma;              
                $comprobante->id_usuario_ingresa=auth()->user()->id;
                $comprobante->area=auth()->user()->persona->id_area;
                $comprobante->codigo_old="EgresoF";   

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

                        //actualizamos el stock en la tabla productobodega
                        $actualizaStockOld =ProductoBodega::where('idbodprod',$idbodega_producto[$cont])->first();
                        $stockactPB=$actualizaStockOld->existencia;   
                        $nuevoStock=$actualizaStockOld->existencia;
                        $nuevoStock_act=$nuevoStock - $cantidad[$cont];
                        $actualizaStockOld->existencia=$nuevoStock_act;                        
                        $actualizaStockOld->save(); 
                        
                        $ultimo=DetalleComprobante::orderBy('iddetalle_comprobante','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->iddetalle_comprobante+1;
                        }

                        $total_item=0;
                        $total_item=$precio[$cont] * $cantidad[$cont];

                        $detalles=new DetalleComprobante();
                        $detalles->iddetalle_comprobante=$suma;
                        $detalles->idcomprobante=$comprobante->idcomprobante;
                        $detalles->id_item=$idmedicina_selecc[$cont];
                        $detalles->id_bodega=$idbodega_selecc[$cont];
                        $detalles->cantidad=$cantidad[$cont];
                        $detalles->precio=number_format(($precio[$cont]),4,'.', '');
                        $detalles->descuento=0;
                        $detalles->total=number_format(($total_item),4,'.', '');
                        $detalles->iva=0;
                        $detalles->fecha=date('Y-m-d H:i:s');
                        $detalles->idbodprod=$actualizaStockOld->idbodprod;
                        $detalles->save(); 
                        
                        $ultimo=Existencia::orderBy('idexistencia','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->idexistencia+1;
                        }
                        $existencia=new Existencia();
                        $existencia->idexistencia=$suma;
                        $existencia->iddetalle_comprobante=$detalles->iddetalle_comprobante;
                        $existencia->lote=$lote[$cont];
                        $existencia->resta=$cantidad[$cont];
                        $existencia->tipo="Egreso Farmacia";
                        $existencia->cod="EF";
                        $existencia->fecha_hora=date('Y-m-d H:i:s');
                        $existencia->reg_sanitario=$reg_sani[$cont];
                        $existencia->fecha_elaboracion=$fecha_elab_[$cont];
                        $existencia->fecha_caducidad=$fecha_caduc[$cont];
                        $existencia->fecha=date('Y-m-d');
                        $existencia->idusuario=auth()->user()->id;
                        $existencia->idbodprod=$actualizaStockOld->idbodprod;
                        $existencia->save();   

                   
                        if($detalles->id_bodega==6 || $detalles->id_bodega==20){//medicamento
                            $actualizaStock=Medicamento::where('coditem',$detalles->id_item)
                            ->first();
                            //restamos a la bodega farmacia
                            if($detalles->id_bodega==6){ //gral
                                $stock_Actual=$actualizaStock->stock;
                                $actualizaStock->stock=$stock_Actual - $detalles->cantidad;
                            }else{
                                //dialisis
                                $stock_Actual=$actualizaStock->stock_farm_dialisis;
                                $actualizaStock->stock_farm_dialisis=$stock_Actual - $detalles->cantidad;
                            }
                           
                            $actualizaStock->save();  

                            //comprobamos que el stock actual no sea menor a lo q se va a quitar
                            if($stockactPB < $existencia->resta){
                                DB::connection('pgsql')->rollback();
                                return (['mensaje'=>'El stock actual del item '.$actualizaStock->nombre. " ".$actualizaStock->concentra."".$actualizaStock->forma. " es menor a ".$existencia->resta,'error'=>true]); 
                            }

                        }else if($detalles->id_bodega==7 || $detalles->id_bodega==21){//insumo
                            $actualizaInsumo=Insumo::where('codinsumo',$detalles->id_item)
                            ->first();
                            
                            if($detalles->id_bodega==7){ //gral
                                $stock_Actual=$actualizaInsumo->stock;
                                $actualizaInsumo->stock=$stock_Actual - $detalles->cantidad;
                            }else{
                                //dialisis
                                $stock_Actual=$actualizaInsumo->stock_farm_dialisis;
                                $actualizaInsumo->stock_farm_dialisis=$stock_Actual - $detalles->cantidad;
                            }
                               
                            
                            $actualizaInsumo->save(); 

                            //comprobamos que el stock actual no sea menor a lo q se va a quitar
                            if($stockactPB < $existencia->resta){
                                DB::connection('pgsql')->rollback();
                                return (['mensaje'=>'El stock actual del item '.$actualizaInsumo->insumo. " es menor a ".$existencia->resta,'error'=>true]); 
                            }
                        
                        }else {//bodega de laboratorio 
                            $actualizaLab=FarmLaboratorio::where('id_item',$detalles->id_item)
                            ->first();
                            $stock_Actual=$actualizaLab->stock_farmacia;
                            $actualizaLab->stock_farmacia=$stock_Actual - $detalles->cantidad;
                            $actualizaLab->save(); 

                            //tabla principal
                            $actualizaLabPrin=Laboratorio::where('id',$detalles->id_item)
                            ->first();

                            if($detalles->id_bodega==27 || $detalles->id_bodega==28 || $detalles->id_bodega==29){ //lab geral
                                $stock_ActualBodSolicita=$actualizaLabPrin->stock_farmacia;
                                $actualizaLabPrin->stock_farmacia=$stock_ActualBodSolicita - $detalles->cantidad;
                            }else{//lab dialisisi farmacia
                                $stock_ActualBodSolicita=$actualizaLabPrin->stock_diali_farmacia;
                                $actualizaLabPrin->stock_diali_farmacia=$stock_ActualBodSolicita - $detalles->cantidad;
                            }
                               
                            $actualizaLabPrin->save(); 

                            //comprobamos que el stock actual no sea menor a lo q se va a quitar
                            if($stockactPB < $existencia->resta){
                                DB::connection('pgsql')->rollback();
                                return (['mensaje'=>'El stock actual del item '.$actualizaLab->nombre. " es menor a ".$existencia->resta,'error'=>true]); 
                            }
                        } 

                        //si es devolucion a bodega ingresamos el item a bodega
                        if($request->cmb_tipo=='Devolucion'){
                            // $devolucion=$this->guardarDevolucionBodega($request);
                            //ultimo 
                            $ultimo =ProductoBodega::orderBy('idbodprod','desc')->first();  
                            if(is_null($ultimo)){
                                $sumauno=1;
                            }else{
                                $sumauno=$ultimo->idbodprod;
                            }  
                            
                            if($idbodega_selecc[$cont]==25){ // farmacia dialisa lab reac
                                $idboddevolver=23;
                            }else if($idbodega_selecc[$cont]==26){ // farmacia dialisa lab micro
                                $idboddevolver=24;
                            }else if($idbodega_selecc[$cont]==22){ // farmacia dialisa lab material
                                $idboddevolver=19;
                            }else if($idbodega_selecc[$cont]==21){ // farmacia dialisa insumo
                                $idboddevolver=18;
                            }else if($idbodega_selecc[$cont]==20){ // farmacia dialisa medicina
                                $idboddevolver=17;
                            }else if($idbodega_selecc[$cont]==27){ // farmacia gral lab material
                                $idboddevolver=8;
                            }else if($idbodega_selecc[$cont]==28){ // farmacia gral lab react
                                $idboddevolver=13;
                            }else if($idbodega_selecc[$cont]==29){ // farmacia gral lab micro
                                $idboddevolver=14;
                            }else if($idbodega_selecc[$cont]==7){ // farmacia gral insumo
                                $idboddevolver=2;
                            }else if($idbodega_selecc[$cont]==6){ // farmacia gral MEDICAMENTOS
                                $idboddevolver=1;
                            }
                                                                            
                            $ProductoBodegaOld=new ProductoBodega();
                            $ProductoBodegaOld->idbodprod=$sumauno+1;
                            $ProductoBodegaOld->idprod=$idmedicina_selecc[$cont];
                            $ProductoBodegaOld->idbodega=$idboddevolver;
                            $ProductoBodegaOld->existencia=$cantidad[$cont];
                            $ProductoBodegaOld->precio=number_format(($precio[$cont]),4,'.', '');
                            $ProductoBodegaOld->precio2=0;
                            $ProductoBodegaOld->fecha=date('Y-m-d');
                            $ProductoBodegaOld->idusuario=auth()->user()->id;
                            $ProductoBodegaOld->sistema_old="ENLINEA";
                            

                            //dependiendo de la bodega seleccionamos el tipoprod
                            if($comprobante->idbodega==1 || $comprobante->idbodega==6 || $comprobante->idbodega==17 || $comprobante->idbodega==20){//bod gral medicamentos
                                $ProductoBodegaOld->tipoprod=1;
                            }else if($comprobante->idbodega==2 || $comprobante->idbodega==7 || $comprobante->idbodega==18 || $comprobante->idbodega==21){// bod gral insumos
                                $ProductoBodegaOld->tipoprod=2;
                            }else if($comprobante->idbodega==14 || $comprobante->idbodega==24 || $comprobante->idbodega==26 || $comprobante->idbodega==29){// bod lab microb
                                $ProductoBodegaOld->tipoprod=11;
                            }else if($comprobante->idbodega==8 || $comprobante->idbodega==19 || $comprobante->idbodega==22 || $comprobante->idbodega==27){// bod lab mater
                                $ProductoBodegaOld->tipoprod=5;
                            }else if($comprobante->idbodega==13 || $comprobante->idbodega==23 || $comprobante->idbodega==25 || $comprobante->idbodega==28){// bod lab react
                                $ProductoBodegaOld->tipoprod=10;
                            }
                            $ProductoBodegaOld->save(); 

                            $ultimo=Existencia::orderBy('idexistencia','desc')->first();
                            if(is_null($ultimo)){
                                $suma=1;
                            }else{
                                $suma=$ultimo->idexistencia+1;
                            }
                            $existencia=new Existencia();
                            $existencia->idexistencia=$suma;
                            $existencia->iddetalle_comprobante=$detalles->iddetalle_comprobante;
                            $existencia->lote=$lote[$cont];
                            $existencia->suma=$cantidad[$cont];
                            $existencia->tipo="Ingreso a Bodega";
                            $existencia->fecha_hora=date('Y-m-d H:i:s');
                            $existencia->reg_sanitario=$reg_sani[$cont];
                            $existencia->fecha_elaboracion=$fecha_elab_[$cont];
                            $existencia->fecha_caducidad=$fecha_caduc[$cont];
                            $existencia->cod="IAB";
                            $existencia->idusuario=auth()->user()->id;
                            $existencia->idbodprod=$ProductoBodegaOld->idbodprod;
                            $existencia->save();   

                            $ultimolote =LoteProducto::orderBy('idlote','desc')->first();
                            if(is_null($ultimolote)){
                                $sumaunolote=1;
                            }else{
                                $sumaunolote=$ultimolote->idlote;
                            }     
                          
                            $LoteProductoOld=new LoteProducto();
                            $LoteProductoOld->idlote=$sumaunolote+1;
                            $LoteProductoOld->idbodp=$ProductoBodegaOld->idbodprod;
                            $LoteProductoOld->lote=$existencia->lote;
                            $LoteProductoOld->felabora= $fecha_elab_[$cont];
                            $LoteProductoOld->fcaduca=$fecha_caduc[$cont];
                            $LoteProductoOld->regsan=$existencia->reg_sanitario;
                            $LoteProductoOld->sistema_old="ENLINEA";
                            $LoteProductoOld->save(); 
                            
                            if($detalles->id_bodega==1 || $detalles->id_bodega==6 || $detalles->id_bodega==17 || $detalles->id_bodega==20){//medicamento
                                //sumamos a la bodega gral
                                $actualizaMed=Medicamento::where('coditem',$detalles->id_item)
                                ->first();

                                if($detalles->id_bodega==6){ 
                                    $stock_Actual=$actualizaMed->stock_bod;
                                    $actualizaMed->stock_bod=$stock_Actual + $detalles->cantidad;
                                }else{
                                    //dialisis
                                    $stock_Actual=$actualizaMed->stock_bod_dialisis;
                                    $actualizaMed->stock_bod_dialisis=$stock_Actual + $detalles->cantidad;
                                }

                                $actualizaMed->save();  
        
                            }else if($detalles->id_bodega==2 || $detalles->id_bodega==7 || $detalles->id_bodega==18 || $detalles->id_bodega==21){//insumo
                                $actualizaInsumo=Insumo::where('codinsumo',$detalles->id_item)
                                ->first();
                                if($detalles->id_bodega==7){
                                    $stock_Actual=$actualizaInsumo->stockbod;
                                    $actualizaInsumo->stockbod=$stock_Actual + $detalles->cantidad;
                                }else{
                                    $stock_Actual=$actualizaInsumo->stock_bod_dialisis;
                                    $actualizaInsumo->stock_bod_dialisis=$stock_Actual + $detalles->cantidad;
                                }
                                
                                $actualizaInsumo->save(); 
    
                            }
                            else { //laboratorio
                               
                                $actualizaLab=Laboratorio::where('id',$detalles->id_item)
                                // ->where('idbodega',$detalles->id_bodega)
                                ->first();
                                
                                if($detalles->id_bodega==19 || $detalles->id_bodega==23 || $detalles->id_bodega==24){ //lab dialisi
                                    $stock_ActualBodSolicita=$actualizaLab->stock;
                                    $actualizaLab->stock=$stock_ActualBodSolicita + $cantidad[$cont];
                                    $esdialisi="N";
                                }else{//lab dialisisi
                                    $stock_ActualBodSolicita=$actualizaLab->stock_dialisis;
                                    $actualizaLab->stock_dialisis=$stock_ActualBodSolicita + $cantidad[$cont];
                                    $esdialisi="S";
                                }
                                $actualizaLab->save(); 
                 
                                //GUARDAMOS O ACTUALIZAMOS LA BODEGA LAB FARMACIA
                                $bodFarmacia= FarmLaboratorio::where('id_item',$detalles->id_item)
                                ->first();
    
                                if(is_null($bodFarmacia)){
                                   
                                    //agregamos $cantidad[$cont]
                                    $ultimo=FarmLaboratorio::orderBy('idfarm_lab','desc')->first();
                                    if(is_null($ultimo)){
                                        $suma=1;
                                    }else{
                                        $suma=$ultimo->idfarm_lab+1;
                                    }
    
                                    $newBodFarm= new FarmLaboratorio();
                                    $newBodFarm->idfarm_lab=$suma;
                                    $newBodFarm->id_item=$detalles->id_item;
                                    $newBodFarm->nombre=$actualizaLab->descri;
                                    $newBodFarm->present=$actualizaLab->presen;
                                    $newBodFarm->stock_farmacia=$actualizaLab->stock_farmacia;
                                    $newBodFarm->stockbod=$actualizaLab->stock;
                                    $newBodFarm->codinsumo=$actualizaLab->codinsumo;
                                    $newBodFarm->activo='VERDADERO';
                                    $newBodFarm->valor=$actualizaLab->valor;
                                    $newBodFarm->tipoprod=$ProductoBodegaOld->tipoprod;
                                    $newBodFarm->es_dialisis=$esdialisi;
                                    $newBodFarm->idbodega=$detalles->id_bodega;
                                    $newBodFarm->save();
                                }else{
                                    $stock_Actual=$bodFarmacia->stock_farmacia;
                                    //actualziamos
                                    $stock_Actual=$bodFarmacia->stock_farmacia;
                                    $bodFarmacia->id_item=$detalles->id_item;
                                    $bodFarmacia->nombre=$actualizaLab->descri;
                                    $bodFarmacia->present=$actualizaLab->presen;
                                    $bodFarmacia->stock_farmacia=$actualizaLab->stock_farmacia;
                                    $bodFarmacia->stockbod=$actualizaLab->stock;
                                    $bodFarmacia->codinsumo=$actualizaLab->codinsumo;
                                    $bodFarmacia->valor=$actualizaLab->valor;
                                    $bodFarmacia->tipoprod=$ProductoBodegaOld->tipoprod;
                                    $bodFarmacia->es_dialisis=$esdialisi;
                                    $bodFarmacia->idbodega=$detalles->id_bodega;
                                    $bodFarmacia->activo='VERDADERO';
                                    $bodFarmacia->save();
                                }
                            } 

                        }

                                            
                        $cont=$cont+1;
                    } 
                 
                    $tipocomp_old->numcom=$comprobante->secuencial;
                    $tipocomp_old->save();

                    //si tofdo ok el comprobante se crea
                    $comprobante_crear=Comprobante::find($comprobante->idcomprobante);
                    $comprobante_crear->estado="Activo";
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

    public function guardarDevolucionFarmBodega(Request $request){
        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{
                
                if($request->cmb_tipo=='Devolucion'){
                    $idtipoSel=6; //trasnsferencia de farmacia a bodega
                }else if($request->cmb_tipo=='Externo'){
                    $idtipoSel=15; // egreso externo de farmacia
                }else{
                    $idtipoSel=5; //egreso interno
                }
               
                $tipocomp_old= TipoComprobanteOld::where('idtipocom',$idtipoSel)
                ->orderBy('idtipocom','desc')
                ->first();

                //registramos la cabecera
                $ultimo=Comprobante::orderby('idcomprobante','desc')->first();
                if(is_null($ultimo)){
                    $suma=1;
                }else{
                    $suma=$ultimo->idcomprobante+1;
                }

                if($request->cmb_bodega==25){ // farmacia dialisa lab reac
                    $idboddevolver=23;
                }else if($request->cmb_bodega==26){ // farmacia dialisa lab micro
                    $idboddevolver=24;
                }else if($request->cmb_bodega==22){ // farmacia dialisa lab material
                    $idboddevolver=19;
                }else if($request->cmb_bodega==21){ // farmacia dialisa insumo
                    $idboddevolver=18;
                }else if($request->cmb_bodega==20){ // farmacia dialisa medicina
                    $idboddevolver=17;
                }else if($request->cmb_bodega==27){ // farmacia gral lab material
                    $idboddevolver=8;
                }else if($request->cmb_bodega==28){ // farmacia gral lab react
                    $idboddevolver=13;
                }else if($request->cmb_bodega==29){ // farmacia gral lab micro
                    $idboddevolver=14;
                }else if($request->cmb_bodega==7){ // farmacia gral insumo
                    $idboddevolver=2;
                }else if($request->cmb_bodega==6){ // farmacia gral MEDICAMENTOS
                    $idboddevolver=1;
                }

                $comprobante=new Comprobante();
                $comprobante->idcomprobante=$suma;
                $comprobante->idtipo_comprobante=$tipocomp_old->idtipocom;
                $comprobante->secuencial=$tipocomp_old->numcom+1;
                $comprobante->descripcion=$tipocomp_old->razoncom;
                $comprobante->fecha_hora=date('Y-m-d H:i:s');
                $comprobante->fecha_aprobacion=date('Y-m-d H:i:s');
                $comprobante->fecha=date('Y-m-d');
                $comprobante->idbodega=$idboddevolver;
                $comprobante->observacion=$request->motivo;
                $comprobante->subtotal=$request->total_suma;
                $comprobante->total=$request->total_suma;              
                $comprobante->id_usuario_ingresa=auth()->user()->id;
                $comprobante->area=auth()->user()->persona->id_area;
                $comprobante->codigo_old="DevolverBodega"; 
                $comprobante->estado="Activo"; 
                $comprobante->tipo=4; 
                $comprobante->id_proveedor=123; 
                
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
                    $total_comprobante=0;
                    //registramos los detalles localmente
                    while($cont < count($idmedicina_selecc)){

                        $ultimo=DetalleComprobante::orderBy('iddetalle_comprobante','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->iddetalle_comprobante+1;
                        }
                        $detalles=new DetalleComprobante();
                        $detalles->iddetalle_comprobante=$suma;
                        $detalles->idcomprobante=$comprobante->idcomprobante;
                        $detalles->id_item=$idmedicina_selecc[$cont];
                        $detalles->id_bodega=$idbodega_selecc[$cont];
                        $detalles->cantidad=$cantidad[$cont];
                        $detalles->precio=number_format(($precio[$cont]),4,'.', '');
                        $detalles->total=number_format(($total[$cont]),4,'.', '');
                        $detalles->iva=0;
                        $detalles->fecha=date('Y-m-d H:i:s');
                        $detalles->idbodprod=$idbodega_producto[$cont];
                        $detalles->save();   
                        
                        $ultimo=PedidoBodegaGral::orderBy('idpedido_bod_gral','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->idpedido_bod_gral+1;
                        }
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
                        $pedido_temp->idbodpro=$idbodega_producto[$cont];
                        $pedido_temp->iditem=$idmedicina_selecc[$cont];
                        $pedido_temp->estado="Solicitado";
                        $pedido_temp->save();        
                    
                        $cont=$cont+1;
                        $total_comprobante=$total_comprobante + $detalles->total;
                    } 
                    
                } 
                if($total_comprobante==0){
                    return (['mensaje'=>'No existe stock para los items solicitados','error'=>false, 'sin_stock'=>'S', 'listado'=>$datosPa]); 
                }

                $actualizaTotal=Comprobante::find($comprobante->idcomprobante);
                $actualizaTotal->subtotal=$total_comprobante;
                $actualizaTotal->total=$total_comprobante;
                $actualizaTotal->save();

                return (['mensaje'=>'Informacion registrada exitosamente','error'=>false]); 

            } catch (\Throwable $e) {
                DB::connection('pgsql')->rollback();
                Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
                return (['mensaje'=>'Ocurrió un error,intentelo más tarde','error'=>true]); 
            }
        });
        return ($transaction);
    }

   
    public function vistaIngreso(){

        $proveedor= DB::connection('pgsql')->table('bodega.proveedor')
        ->where('estado1',1)
        ->get();
        
        $tipo_ingreso= DB::connection('pgsql')->table('bodega.tipo_ingreso')
        ->where('estado',1)
        ->whereIn('idtipo_ingreso',[4,5])
        ->get();

        $bodega= DB::connection('pgsql')->table('bodega.bodega')
        ->where('idtipobod',2)
        ->whereIn('idbodega',[6,7,20,21,22,25,26,27,28,29])
        ->where('estado',1)
        ->get();

        $usuario= DB::connection('pgsql')->table('inventario.persona')
        ->where('estado',1)
        ->get();

        return view('gestion_farmacia.ingreso_bodega_farmacia',[
            "proveedor"=>$proveedor,
            "tipo_ingreso"=>$tipo_ingreso,
            "bodega"=>$bodega,
            "usuario"=>$usuario
        ]);
    }

    public function guardarIngreso(Request $request){
        
        $validator = Validator::make($request->all(), [
            'cmb_proveedor' => 'required',
            'cmb_bodega' => 'required',
            'tipo_ingreso_cmb' => 'required',
           
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario','error'=>true]);
        }

        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{

                if($request->tipo_ingreso_cmb==4){
                    $idtipoSel=10; //devolucion interna de farmacia
                }else{
                    $idtipoSel=2; //ajsuste de ingreso
                }

                $tipocomp_old= TipoComprobanteOld::where('idtipocom',$idtipoSel)
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
                $comprobante->tipo=$request->tipo_ingreso_cmb;
                $comprobante->subtotal=$request->total_suma;
                $comprobante->total=$request->total_suma;              
                $comprobante->id_proveedor=$request->cmb_proveedor;
                $comprobante->id_usuario_ingresa=auth()->user()->id;
                $comprobante->area=auth()->user()->persona->id_area;
                $comprobante->codigo_old="IngresoBF";
                $comprobante->iduser_devuelve=$request->cmb_user_dev;
                $comprobante->observacion=$request->observa;
                $comprobante->guia=$request->guia;
                
                if($comprobante->save()){

                    //datos detalle
                    $idmedicina_selecc=$request->idmedicina_selecc;
                    $idbodega_selecc=$request->idbodega_selecc; 
                    $cantidad=$request->cantidad;
                    $precio=$request->precio;
                    // $descuento=$request->descuento;
                    $fecha_elab_=$request->fecha_elab_;
                    $fecha_caduc=$request->fecha_caduc;
                    $lote=$request->lote;
                    $reg_sani=$request->reg_sani;                    
                    $total=$request->total;                  
                  
                    $cont=0;
                    $subtota_comprobante=0;
                    //registramos los detalles localmente
                    while($cont < count($idmedicina_selecc)){
                        
                        //ultimo 
                        $ultimo =ProductoBodega::orderBy('idbodprod','desc')->first();  
                        if(is_null($ultimo)){
                            $sumauno=1;
                        }else{
                            $sumauno=$ultimo->idbodprod;
                        }                    
                        
                        $ProductoBodegaOld=new ProductoBodega();
                        $ProductoBodegaOld->idbodprod=$sumauno+1;
                        $ProductoBodegaOld->idprod=$idmedicina_selecc[$cont];
                        $ProductoBodegaOld->idbodega=$idbodega_selecc[$cont];
                        $ProductoBodegaOld->existencia=$cantidad[$cont];
                        $ProductoBodegaOld->precio=number_format(($precio[$cont]),2,'.', '');
                        $ProductoBodegaOld->precio2=0;
                        $ProductoBodegaOld->sistema_old="ENLINEA";

                        //dependiendo de la bodega seleccionamos el tipoprod
                        if($comprobante->comprobante==6 || $comprobante->idbodega==17 || $comprobante->idbodega==20){//bod  medicamentos
                            $ProductoBodegaOld->tipoprod=1;
                        }else if($comprobante->idbodega==7 || $comprobante->idbodega==18 || $comprobante->idbodega==21){// bod  insumos
                            $ProductoBodegaOld->tipoprod=2;

                        }else if($comprobante->comprobante==14 || $comprobante->comprobante==24 || $comprobante->comprobante==26 || $comprobante->comprobante==29){// bod lab microb
                            $ProductoBodegaOld->tipoprod=11;
                        }else if($comprobante->comprobante==8 || $comprobante->comprobante==19 || $comprobante->comprobante==22 || $comprobante->comprobante==27){// bod lab mater
                            $ProductoBodegaOld->tipoprod=5;
                        }else if($comprobante->comprobante==13 || $comprobante->comprobante==23 || $comprobante->comprobante==25 || $comprobante->comprobante==28){// bod lab react
                            $ProductoBodegaOld->tipoprod=10;
                        }

                        $ProductoBodegaOld->save(); 
                        
                        $ultimo=DetalleComprobante::orderBy('iddetalle_comprobante','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->iddetalle_comprobante+1;
                        }

                        $total_item=0;
                        $total_item=$precio[$cont] * $cantidad[$cont];

                        $detalles=new DetalleComprobante();
                        $detalles->iddetalle_comprobante=$suma;
                        $detalles->idcomprobante=$comprobante->idcomprobante;
                        $detalles->id_item=$idmedicina_selecc[$cont];
                        $detalles->id_bodega=$idbodega_selecc[$cont];
                        $detalles->cantidad=$cantidad[$cont];
                        $detalles->precio=number_format(($precio[$cont]),4,'.', '');
                        $detalles->total=number_format(($total_item),4,'.', '');
                        $detalles->iva=0;
                        $detalles->fecha=date('Y-m-d H:i:s');
                        $detalles->idbodprod=$ProductoBodegaOld->idbodprod;
                        $detalles->save(); 

                        $ultimo=Existencia::orderBy('idexistencia','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->idexistencia+1;
                        }

                        //Valida lote
                        $bod=$idbodega_selecc[$cont];
                        $item=$idmedicina_selecc[$cont];
                        if($lote[$cont]){
                            $validaLote=Existencia::with('prodbod')
                            ->whereHas('prodbod', function ($q) use($bod, $item){
                                $q->where('idbodega',$bod)
                                ->where('idprod','<>',$item);
                            })
                            ->where('lote',$lote[$cont])
                            ->first();
                            
                            if(!is_null($validaLote) && $comprobante->tipo!=4){
                                $texto="";
                                if($bod==6 || $bod==20){
                                    $prod=DB::table('bodega.medicamentos as med')
                                    ->where('med.coditem', $validaLote->prodbod->idprod)
                                    ->select(DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion) AS detalle"))
                                    ->first();
                                    if(!is_null($prod)){
                                        $texto=", en el producto ".$prod->detalle;
                                    }
                                        
                                }else if($bod==7 || $bod==21){
                                    $prod=DB::table('bodega.insumo as insu')
                                    ->where('insu.codinsumo', $validaLote->prodbod->idprod)
                                    ->select(DB::raw("CONCAT(insu.insumo) AS detalle"))
                                    ->first();

                                    if(!is_null($prod)){
                                        $texto=", en el producto ".$prod->detalle;
                                    }
                                }else if($bod==22 || $bod==25 || $bod==26 || $bod==27 || $bod==28 || $bod==29){
                                    $prod=DB::table('bodega.laboratorio as lab')
                                    ->where('lab.id', $validaLote->prodbod->idprod)
                                    ->select(DB::raw("CONCAT(lab.descri) AS detalle"))
                                    ->first();

                                    if(!is_null($prod)){
                                        $texto=", en el producto ".$prod->detalle;
                                    }
                                }
                                // DB::connection('pgsql')->rollback();
                                // return (['mensaje'=>'El lote '.$validaLote->lote. ' ya existe'.$texto,'error'=>true]); 
                            }
                        }

                        $existencia=new Existencia();
                        $existencia->idexistencia=$suma;
                        $existencia->iddetalle_comprobante=$detalles->iddetalle_comprobante;
                        $existencia->lote=$lote[$cont];
                        $existencia->suma=$cantidad[$cont];
                        $existencia->tipo="Ingreso a Bodega Farmacia";
                        $existencia->fecha_hora=date('Y-m-d H:i:s');
                        $existencia->reg_sanitario=$reg_sani[$cont];
                        $existencia->fecha_elaboracion=$fecha_elab_[$cont];
                        $existencia->fecha_caducidad=$fecha_caduc[$cont];
                        $existencia->cod="IABDF";
                        $existencia->idusuario=auth()->user()->id;
                        $existencia->idbodprod=$ProductoBodegaOld->idbodprod;
                        $existencia->save();   
                      
                        $ultimolote =LoteProducto::orderBy('idlote','desc')->first();
                        if(is_null($ultimolote)){
                            $sumaunolote=1;
                        }else{
                            $sumaunolote=$ultimolote->idlote;
                        }     
                       

                        $LoteProductoOld=new LoteProducto();
                        $LoteProductoOld->idlote=$sumaunolote+1;
                        $LoteProductoOld->idbodp=$ProductoBodegaOld->idbodprod;
                        $LoteProductoOld->lote=$existencia->lote;
                        $LoteProductoOld->felabora= $fecha_elab_[$cont];
                        $LoteProductoOld->fcaduca=$fecha_caduc[$cont];
                        $LoteProductoOld->regsan=$existencia->reg_sanitario;
                        $LoteProductoOld->sistema_old="ENLINEA";
                        $LoteProductoOld->save(); 
                        
                        if($detalles->id_bodega==6 || $detalles->id_bodega==17 || $detalles->id_bodega==20){//medicamento
                            $actualizaStock=Medicamento::where('coditem',$detalles->id_item)
                            ->first();
                            $stock_Actual=$actualizaStock->stock;
                            $actualizaStock->stock=$stock_Actual + $detalles->cantidad;
                            $actualizaStock->save();  

                        }else if($detalles->id_bodega==7 || $detalles->id_bodega==18 || $detalles->id_bodega==21){//insumo
                            $actualizaInsumo=Insumo::where('codinsumo',$detalles->id_item)
                            ->first();
                            if($detalles->id_bodega==7){
                                $stock_Actual=$actualizaInsumo->stock;
                                $actualizaInsumo->stock=$stock_Actual + $detalles->cantidad;
                            }else{
                                $stock_Actual=$actualizaInsumo->stock_farm_dialisis;
                                $actualizaInsumo->stock_farm_dialisis=$stock_Actual + $detalles->cantidad;
                            }
                            
                            $actualizaInsumo->save(); 

                        }else { //dialisis material
                            $actualizaLab=Laboratorio::where('id',$detalles->id_item)
                            ->first();
                            if($detalles->id_bodega==27 || $detalles->id_bodega==28 || $detalles->id_bodega==29){ //lab geral
                                $stock_ActualBodSolicita=$actualizaLab->stock_farmacia;
                                $actualizaLab->stock_farmacia=$stock_ActualBodSolicita + $detalles->cantidad;
                                $esdialisi="N";
                            }else{//lab dialisisi
                                $stock_ActualBodSolicita=$actualizaLab->stock_diali_farmacia;
                                $actualizaLab->stock_diali_farmacia=$stock_ActualBodSolicita + $detalles->cantidad;
                                $esdialisi="S";
                            }
                            $actualizaLab->save(); 
             
                            //GUARDAMOS O ACTUALIZAMOS LA BODEGA LAB FARMACIA
                            $bodFarmacia= FarmLaboratorio::where('id_item',$detalles->id_item)
                            ->where('idbodega',$detalles->id_bodega)
                            ->first();

                            if(is_null($bodFarmacia)){
                               
                                //agregamos $cantidad[$cont]
                                $ultimo=FarmLaboratorio::orderBy('idfarm_lab','desc')->first();
                                if(is_null($ultimo)){
                                    $suma=1;
                                }else{
                                    $suma=$ultimo->idfarm_lab+1;
                                }

                                $newBodFarm= new FarmLaboratorio();
                                $newBodFarm->idfarm_lab=$suma;
                                $newBodFarm->id_item=$detalles->id_item;
                                $newBodFarm->nombre=$actualizaLab->descri;
                                $newBodFarm->present=$actualizaLab->presen;
                                $newBodFarm->stock_farmacia=$actualizaLab->stock_farmacia;
                                $newBodFarm->stockbod=$actualizaLab->stock;
                                $newBodFarm->codinsumo=$actualizaLab->codinsumo;
                                $newBodFarm->activo='VERDADERO';
                                $newBodFarm->valor=$actualizaLab->valor;
                                $newBodFarm->tipoprod=$ProductoBodegaOld->tipoprod;
                                $newBodFarm->es_dialisis=$esdialisi;
                                $newBodFarm->idbodega=$detalles->id_bodega;
                                $newBodFarm->save();
                            }else{
                                $stock_Actual=$bodFarmacia->stock_farmacia;
                                //actualziamos
                                $stock_Actual=$bodFarmacia->stock_farmacia;
                                $bodFarmacia->id_item=$detalles->id_item;
                                $bodFarmacia->nombre=$actualizaLab->descri;
                                $bodFarmacia->present=$actualizaLab->presen;
                                $bodFarmacia->stock_farmacia=$actualizaLab->stock_farmacia;
                                $bodFarmacia->stockbod=$actualizaLab->stock;
                                $bodFarmacia->codinsumo=$actualizaLab->codinsumo;
                                $bodFarmacia->valor=$actualizaLab->valor;
                                $bodFarmacia->tipoprod=$ProductoBodegaOld->tipoprod;
                                $bodFarmacia->es_dialisis=$esdialisi;
                                $bodFarmacia->idbodega=$detalles->id_bodega;
                                $bodFarmacia->activo='VERDADERO';
                                $bodFarmacia->save();
                            }
                        }               
                    
                        $cont=$cont+1;
                    } 
                    
                  
                    $tipocomp_old->numcom=$comprobante->secuencial;
                    $tipocomp_old->save();

                    //si tofdo ok el comprobante se crea
                    $comprobante_crear=Comprobante::find($comprobante->idcomprobante);
                    $comprobante_crear->estado="Activo";
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

    public function listadoIngresos(){

        return view('gestion_farmacia.listado_ingreso_bod_farmacia');
    }

    
    public function filtrarIngresoDirecto($ini, $fin){
        
        try{
            $ingresos= DB::connection('pgsql')->table('bodega.comprobante as comp')
            ->leftJoin('bodega.tipo_ingreso as tipo', 'tipo.idtipo_ingreso','comp.tipo')
            ->leftJoin('bodega.bodega as bod', 'bod.idbodega','comp.idbodega')
            ->leftJoin('bodega.proveedor as pr', 'pr.idprov','comp.id_proveedor')
            ->leftJoin('public.users as usu', 'usu.id','comp.id_usuario_ingresa')
            ->leftJoin('inventario.persona as per', 'per.idper','usu.id_persona')
            ->where(function($query)use($ini, $fin){
                $query->whereBetween('comp.fecha',[$ini, $fin]);
            })
            ->select('comp.descripcion','comp.secuencial','comp.fecha_hora','pr.empresa','pr.ruc','comp.total','comp.id_usuario_ingresa', 'comp.idcomprobante',DB::raw("CONCAT(per.ape1,' ', per.ape2,' ', per.nom1,' ', per.nom2) AS responsable"),'comp.idbodega','bod.nombre as nombre_bod','tipo.nombre as tipoIngreso')
            ->where('comp.estado','=','Activo')
            ->whereIn('idtipo_comprobante',[14,10,2])
            ->get();

            return response()->json([
                'error'=>false,
                'resultado'=>$ingresos
            ]);
        }catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }

    public function reporteIngresoBodFarmacia($id, $bodega){
        
        try{
           
            set_time_limit(0);
            ini_set("memory_limit",-1);
            ini_set('max_execution_time', 0);

            if($bodega==6 || $bodega==20){
                $comprobante=Comprobante::with('detalle','entregado','responsable','bodega')->where('idcomprobante',$id)
                ->first();
            }else if($bodega==7 || $bodega==21){
                $comprobante=Comprobante::with('detalle_insumo','entregado','responsable','bodega')->where('idcomprobante',$id)
                ->first();
            }else{
                // $comprobante=Comprobante::with('detalle_item','entregado','responsable','bodega','proveedor','tipoIngreso','devolucion')->where('idcomprobante',$id)
                // ->first();
                $comprobante=Comprobante::with('detalle_lab','entregado','recibido','bodega','proveedor','tipoIngreso','devolucion')->where('idcomprobante',$id)
                ->first();
            }
            
            $jefa= DB::connection('pgsql')->table('bodega.per_perfil')
            ->where('descripcion','Jefe Farmacia')
            ->where('estado','A')
            ->first();
          

            $dato= DB::connection('pgsql')->table('bodega.per_perfil_usuario as pu')
            ->leftJoin('users as u', 'u.id','pu.id_usuario')
            ->leftJoin('inventario.persona as per', 'per.idper','u.id_persona')
            ->where('pu.id_perfil',$jefa->id_perfil)
            ->select(DB::raw("CONCAT(per.ape1,' ', per.ape2,' ', per.nom1,' ', per.nom2) AS jefaBodega"),'per.ci as cedula')
            ->first();
                     
            if($comprobante->codigo_old=="Pedido"){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'La solicitud aun no a sido respondida'
                ]);
            }

            $nombrePDF=$comprobante->descripcion.".pdf";

            if($bodega==6 || $bodega==20){
                $pdf=\PDF::loadView('reportes.farmacia.ingreso_bod',['comprobante'=>$comprobante,'jefa'=>$dato]);
            }else if($bodega==7 || $bodega==21){
                $pdf=\PDF::loadView('reportes.farmacia.ingreso_bod_ins',['comprobante'=>$comprobante,'jefa'=>$dato]);
            }else{
               
                $pdf=\PDF::loadView('reportes.farmacia.ingreso_bod_lab',['comprobante'=>$comprobante,'jefa'=>$dato]);
            }

           
            $pdf->setPaper("A4", "portrait");

            $estadoarch = $pdf->stream();

            //lo guardamos en el disco temporal
            Storage::disk('public')->put(str_replace("", "",$nombrePDF), $estadoarch);
            $exists_destino = Storage::disk('public')->exists($nombrePDF); 
            if($exists_destino){ 
                return response()->json([
                    'error'=>false,
                    'pdf'=>$nombrePDF
                ]);
            }else{
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'No se pudo crear el documento'
                ]);
            }
        }catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }
 
    public function listaPedidoVista(){

        return view('gestion_farmacia.listado_pedido');
    }

    public function filtrarPedidoBodegaFarm($ini, $fin){
        
        try{

          
            $pedidos= DB::connection('pgsql')->table('bodega.comprobante as comp')
            ->leftJoin('public.users as usu', 'usu.id','comp.id_usuario_ingresa')
            ->leftJoin('inventario.persona as per', 'per.idper','usu.id_persona')
            ->leftJoin('bodega.area as a', 'a.id_area','comp.area')
            
            ->where(function($query)use($ini, $fin){
                if($fin!="f"){
                    $query->whereBetween('comp.fecha',[$ini, $fin]);
                }
                   
            })
            ->leftJoin('bodega.bodega as bod', 'bod.idbodega','comp.idbodega')
            ->select('comp.descripcion','comp.secuencial','comp.fecha_hora','comp.observacion','comp.total','comp.id_usuario_ingresa', 'comp.idcomprobante','bod.nombre as nombre_bodega','comp.idbodega',DB::raw("CONCAT(per.ape1,' ', per.ape2,' ', per.nom1,' ', per.nom2) AS solicita"),"comp.codigo_old","a.descripcion as area")
            ->where('comp.estado','=','Activo')
            ->whereIN('comp.codigo_old',['PedidoAFarm'])
            ->whereNotIn('idtipo_comprobante',[8,18,19,20]) //todos menos receta, insumos y paquete 
            ->get();

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

    //job aprobacion de insumo
    public function aprobarInsumoEntregado(){
        $listar=DB::table('bodega.comprobante')
        ->where('idtipo_comprobante',20)
        ->where('codigo_old','EntregadoF')
        ->select('id_comp_receta','descripcion','codigo_old')
        ->get();
        $id_comp_receta=[];
        foreach($listar as $data){
            if(!is_null($data->id_comp_receta)){
                array_push($id_comp_receta, $data->id_comp_receta);
            }
        }
        $actualiza=ComprobanteReceta::whereIn('idcomprobante',$id_comp_receta)
        ->update(['estado'=>99,'actualiza_job'=>'S']);
        Log::info("Job aprobacion insumo tabla inventario");
    }


    public function InsumoPendienteSincronizar($test=1){

        $transaction=DB::connection('pgsql')->transaction(function() use ($test){
            try{
                $receta= DB::connection('pgsql')->table('inventario.comprobante as comp')
                ->leftJoin('inventario.area as ar', 'ar.idarea','comp.idarea')
                ->leftJoin('inventario.receta_cie as r', 'r.idcomprobante','comp.idcomprobante')
                ->leftJoin('esq_rdacaa.cie10 as c10', 'c10.cie10_codigo','r.cod_4')
                ->where('comp.idtipocomprobante',10) //insumo
                ->where('comp.estado','2') //solicitado
                ->select('comp.idcomprobante','idresponsable','idpaciente','fecha','cie10_id','cie10_descripcion','comp.fecha','comp.hora','comp.idarea','ar.narea','comp.id_servicio')
                ->get();
               
                foreach($receta as $data){

                    $cedula= DB::connection('pgsql')->table('inventario.persona')
                    ->where('idper',$data->idresponsable)
                    ->select('ci','id_area')
                    ->first();
                   
                    $user= DB::connection('pgsql')->table('public.users')->where('tx_login', $cedula->ci)
                    ->whereIn('estado',['A','I'])
                    ->first();

                    if(is_null($user)){
                        $sincronizarUser=$this->sincronizaUsuario($cedula->ci, $data->idresponsable);
                        $user= DB::connection('pgsql')->table('public.users')->where('tx_login', $cedula->ci)
                        ->whereIn('estado',['A','I'])
                        ->first();
                    }
                    
                    //detalle                   
                    $tipocomp_old= TipoComprobanteOld::where('idtipocom',20)
                    ->orderBy('idtipocom','desc')
                    ->first();

                    
                    $id_servicio=null;
                    $id_especialidad=null;
                    if($data->idarea===3){
                        //consulta externa
                        $areaReceta=DB::connection('pgsql')->table('bodega.area')
                        ->where('descripcion','CONSULTA EXTERNA')
                        ->first();

                        $bodega_restar=7; //farmacia gral
                        $observ="PEDIDO INSUMO CONS EXTER";
                        $tipoAreaRec="CE";

                        //idespecialidad
                        $espec=DB::table('esq_profesional.medico_especialidad')
                        ->where('id_personal',$data->idresponsable)
                        ->select('id_especialidad')
                        ->where('habilitado','A')
                        ->first();
                       
                        $id_especialidad=$espec->id_especialidad;

                        $areaLic=$areaReceta->id_area;

                    }else if($data->idarea===22){
                        //dialisis
                        $areaReceta=DB::connection('pgsql')->table('bodega.area')
                        ->where('descripcion','HOSPITALIZACION')
                        ->first();

                        $observ="PEDIDO INSUMO DIALISIS";
                        $tipoAreaRec="Hospitalizacion";
                        $id_servicio=22;

                        $bodega_restar=21; //insumo dia
                        $areaLic=$areaReceta->id_area;

                    }else if($data->idarea===31 || $data->idarea===32){
                        //emergencia 
                        $areaReceta=DB::connection('pgsql')->table('bodega.area')
                        ->where('descripcion','EMERGENCIA')
                        ->first();

                        $observ="PEDIDO INSUMO EMERGENCIA";
                        $tipoAreaRec="Emergencia";
                        if($data->idarea===31){
                            $id_servicio=9000;
                        }else{
                            $id_servicio=9001;
                        }
                      
                        $bodega_restar=7; //insumo gral
                        $areaLic=$areaReceta->id_area;
                        
                    }else{
                      
                        $ultimo=Area::orderBy('id_area','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->id_area+1;
                        }

                        //verificamos si existe esa area(dependencia) q viene del sys, si no existe la registramos
                        $areaReceta=new Area;
                        $areaReceta->id_area=$suma;
                        $areaReceta->descripcion=$data->narea;
                        $areaReceta->estado='A';
                        $existe=Area::where('descripcion',$areaReceta->descripcion)
                        ->where('estado','A')->first();
                        if(is_null($existe)){
                            $areaReceta->save();
                            $areaLic=$areaReceta->id_area;
                        }else{
                            $areaLic=$existe->id_area;
                        }                       
                        $bodega_restar=7; //farmacia insumo gral
                        $id_servicio=$data->id_servicio;
                        $observ="PEDIDO INSUMO HOSPITALIZACION";
                        $tipoAreaRec="Hospitalizacion";
                   
                    }
                         
                    //registramos la cabecera
                    $ultimo=Comprobante::orderby('idcomprobante','desc')->first();
                    if(is_null($ultimo)){
                        $suma_=1;
                    }else{
                        $suma_=$ultimo->idcomprobante+1;
                    }                  
                              
                    $comprobante=new Comprobante();
                    $comprobante->idcomprobante=$suma_;
                    $comprobante->idtipo_comprobante=20; //insumo gral
                    $comprobante->secuencial=$tipocomp_old->numcom+1;
                    $comprobante->descripcion=$tipocomp_old->razoncom;
                    $comprobante->fecha_hora=$data->fecha." ".$data->hora;
                    $comprobante->fecha=$data->fecha;
                    $comprobante->idbodega=$bodega_restar;
                    $comprobante->observacion=$observ." -".$data->idcomprobante;
                    $comprobante->id_usuario_ingresa=$user->id;
                    $comprobante->area=$areaLic;
                    $comprobante->id_paciente=$data->idpaciente;
                    $comprobante->id_cie10=$data->cie10_id;
                    $comprobante->codigo_old="PedidoAFarm";
                    $comprobante->estado="Activo";
                    $comprobante->fecha_uso_item=$data->fecha;
                    $comprobante->tipoarea=$tipoAreaRec;
                    $comprobante->id_comp_receta=$data->idcomprobante;
                    $comprobante->id_servicio=$data->id_servicio;
                    $comprobante->id_especialidad=$id_especialidad;
                    
                    //existe
                    $existe=Comprobante::where('id_comp_receta',$data->idcomprobante)
                    // ->delete();
                    ->first();
               
                  
                    //solo si no existe
                    if(is_null($existe)){
                        $comprobante->save();
                        $tipocomp_old->numcom=$comprobante->secuencial;
                        $tipocomp_old->save();
                    }


                }

                return (['mensaje'=>'Receta procesadas al comprobante','error'=>false]); 

            } catch (\Throwable $e) {
                DB::connection('pgsql')->rollback();
                Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
                return (['mensaje'=>'Ocurrió un error,intentelo más tarde','error'=>true]); 
            }
        });
        return ($transaction);
    }

    // public function InsumoPendienteSincronizar($test=1){

    //     $transaction=DB::connection('pgsql')->transaction(function() use ($test){
    //         try{
    //             $receta= DB::connection('pgsql')->table('inventario.comprobante as comp')
    //             ->leftJoin('inventario.receta_cie as r', 'r.idcomprobante','comp.idcomprobante')
    //             ->leftJoin('esq_rdacaa.cie10 as c10', 'c10.cie10_codigo','r.cod_4')
    //             ->where('comp.idtipocomprobante',10) //insumo
    //             ->where('comp.estado','2') //solicitado
    //             ->select('comp.idcomprobante','idresponsable','idpaciente','fecha','cie10_id','cie10_descripcion','comp.fecha','comp.hora','comp.idarea')
    //             ->get();
              
                      
    //             foreach($receta as $data){

    //                 $cedula= DB::connection('pgsql')->table('inventario.persona')
    //                 ->where('idper',$data->idresponsable)
    //                 ->select('ci','id_area')
    //                 ->first();
                   
    //                 $user= DB::connection('pgsql')->table('public.users')->where('tx_login', $cedula->ci)
    //                 ->whereIn('estado',['A','I'])
    //                 ->first();
                    
    //                 //detalle                   
    //                 $tipocomp_old= TipoComprobanteOld::where('idtipocom',20)
    //                 ->orderBy('idtipocom','desc')
    //                 ->first();

    //                 //registramos la cabecera
    //                 $ultimo=Comprobante::orderby('idcomprobante','desc')->first();
    //                 if(is_null($ultimo)){
    //                     $suma=1;
    //                 }else{
    //                     $suma=$ultimo->idcomprobante+1;
    //                 }
                         
    //                 $areaLic=$cedula->id_area;
    //                 $bodega_restar=7; //insumo gral
    //                 $observ="PEDIDO INSUMO";
                              
    //                 $comprobante=new Comprobante();
    //                 $comprobante->idcomprobante=$suma;
    //                 $comprobante->idtipo_comprobante=20; //insumo gral
    //                 $comprobante->secuencial=$tipocomp_old->numcom+1;
    //                 $comprobante->descripcion=$tipocomp_old->razoncom;
    //                 $comprobante->fecha_hora=$data->fecha." ".$data->hora;
    //                 $comprobante->fecha=$data->fecha;
    //                 $comprobante->idbodega=$bodega_restar;
    //                 $comprobante->observacion=$observ." -".$data->idcomprobante;
    //                 $comprobante->id_usuario_ingresa=$user->id;
    //                 $comprobante->area=$areaLic;
    //                 $comprobante->id_paciente=$data->idpaciente;
    //                 $comprobante->id_cie10=$data->cie10_id;
    //                 $comprobante->codigo_old="PedidoAFarm";
    //                 $comprobante->estado="Activo";
    //                 $comprobante->fecha_uso_item=$data->fecha;
    //                 $comprobante->tipoarea="Hospitalizacion";
    //                 $comprobante->id_comp_receta=$data->idcomprobante;
                    
    //                 //existe
    //                 $existe=Comprobante::where('id_comp_receta',$data->idcomprobante)
    //                 // ->delete();
    //                 ->first();
               
                  
    //                 //solo si no existe
    //                 if(is_null($existe)){
    //                     $comprobante->save();
    //                     $tipocomp_old->numcom=$comprobante->secuencial;
    //                     $tipocomp_old->save();
    //                 }


    //             }

    //             return (['mensaje'=>'Receta procesadas al comprobante','error'=>false]); 

    //         } catch (\Throwable $e) {
    //             DB::connection('pgsql')->rollback();
    //             Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
    //             return (['mensaje'=>'Ocurrió un error,intentelo más tarde','error'=>true]); 
    //         }
    //     });
    //     return ($transaction);
    // }

    public function sincronizaUsuario($cedula, $idpersona){
       
        $transaction=DB::transaction(function() use($cedula, $idpersona){
            try{ 
                //consultamos las personas agregadas desde el sys
               
                $ultimo=User::orderby('id','desc')->first();
                $Usuario=new User();
                $Usuario->id=$ultimo->id+1;
                $Usuario->id_persona=$idpersona;
                $Usuario->tx_login=$cedula;
                $Usuario->password=Hash::make($cedula);
                $Usuario->estado='A';
                $Usuario->id_creadopor=0;
                $Usuario->fe_creacion=date('Y-m-d H:i:s');
                if($Usuario->save()){
                    $perfilInvitado=Perfil::where('descripcion','Invitado')
                    ->select('id_perfil')
                    ->first();
                    $ultimo=UsuarioPerfil::orderby('idperfil_usuario','desc')->first();
                    $UsuarioPerfil=new UsuarioPerfil();
                    $UsuarioPerfil->idperfil_usuario=$ultimo->idperfil_usuario+1;
                    $UsuarioPerfil->id_usuario=$Usuario->id;
                    $UsuarioPerfil->id_perfil=$perfilInvitado->id_perfil;
                
                    if($UsuarioPerfil->save()){
                        return [
                            "error"=>false,
                            "mensaje"=>"Cuenta creada exitosamente",
                            "idusuario"=>$Usuario->id
                        ];
                    }else{
                        DB::Rollback();
                        return[
                            "error"=>true,
                            "mensaje"=>"No se pudo registrar la cuenta"
                        ];
                    }

                }else{
                    DB::Rollback();
                    return[
                        "error"=>true,
                        "mensaje"=>"No se pudo registrar la información del usuario"
                    ];
                }
                   
               
            }catch (\Throwable $e) {
                DB::Rollback();
                Log::error('BodegaFarmaciaController => sincronizaUsuario => mensaje => '.$e->getMessage().' linea => '.$e->getLine());
                return [
                    'error'=>true,
                    'mensaje'=>'Ocurrió un error'
                ];
                
            }
        });
        return $transaction;
    }

    public function recetasPendientesSincronizar($test=1){
      
        $transaction=DB::connection('pgsql')->transaction(function() use ($test){
            try{
                $receta= DB::connection('pgsql')->table('inventario.comprobante as comp')
                ->leftJoin('inventario.area as ar', 'ar.idarea','comp.idarea')
                ->leftJoin('inventario.receta_cie as r', 'r.idcomprobante','comp.idcomprobante')
                ->leftJoin('esq_rdacaa.cie10 as c10', 'c10.cie10_codigo','r.cod_4')
                ->where('comp.idtipocomprobante',2) //receta
                ->where('comp.estado','2') //solicitado
                ->select('comp.idcomprobante','idmedico','idpaciente','fecha','cie10_id','cie10_descripcion','comp.fecha','comp.hora','comp.idarea','ar.narea','comp.id_servicio','comp.idbodega')
                ->distinct('comp.idcomprobante')
                ->get();
               
               
                foreach($receta as $data){

                    $cedula= DB::connection('pgsql')->table('inventario.persona')
                    ->where('idper',$data->idmedico)
                    ->select('ci')
                    ->first();
                   
                    $user= DB::connection('pgsql')->table('public.users')->where('tx_login', $cedula->ci)
                    ->whereIn('estado',['A','I'])
                    ->first();
                    if(is_null($user)){
                        $sincronizarUser=$this->sincronizaUsuario($cedula->ci, $data->idmedico);
                        $user= DB::connection('pgsql')->table('public.users')->where('tx_login', $cedula->ci)
                        ->whereIn('estado',['A','I'])
                        ->first();
                    }
                    
                    //detalle                   
                    $tipocomp_old= TipoComprobanteOld::where('idtipocom',8)
                    ->orderBy('idtipocom','desc')
                    ->first();

                  
                    $id_servicio=null;
                    $id_especialidad=null;
                    if($data->idarea===3){
                        //consulta externa
                        $areaReceta=DB::connection('pgsql')->table('bodega.area')
                        ->where('descripcion','CONSULTA EXTERNA')
                        ->first();

                        $bodega_restar=6; //farmacia gral
                        $observ="RECETA MEDICA CONS EXTER";
                        $tipoAreaRec="CE";

                        //idespecialidad
                        $espec=DB::table('esq_profesional.medico_especialidad')
                        ->where('id_personal',$data->idmedico)
                        ->select('id_especialidad')
                        ->where('habilitado','A')
                        ->first();
                        if(!is_null($espec)){
                            $id_especialidad=$espec->id_especialidad;
                        }else{
                            $id_especialidad=0;
                            log::info("medico problema especialidad ---> ".$data->idmedico);
                        }
                        
                        // $id_especialidad=0;

                        $idAreaRec=$areaReceta->id_area;

                    }else if($data->idarea===22){
                        //dialisis
                        $areaReceta=DB::connection('pgsql')->table('bodega.area')
                        ->where('descripcion','DIALISIS')
                        ->first();
                        $observ="RECETA MEDICA DIALISIS";
                        $tipoAreaRec="Hospitalizacion";
                        $id_servicio=22;

                        if($data->idbodega==6){
                            $bodega_restar=6; //farmacia gral
                        }else{
                            $bodega_restar=20; //farmacia dialisis
                        }
                            
                        $idAreaRec=$areaReceta->id_area;

                    }else if($data->idarea===24){
                        //hospitalizacion 
                        $areaReceta=DB::connection('pgsql')->table('bodega.area')
                        ->where('descripcion','HOSPITALIZACION')
                        ->first();
                        $observ="RECETA MEDICA HOSPITALIZACION";
                        $tipoAreaRec="Hospitalizacion";

                        $bodega_restar=6; //farmacia gral
                        $id_servicio=$data->id_servicio;
                        $idAreaRec=$areaReceta->id_area;

                    }else if($data->idarea===26){
                        //hospitalizacion del dia                       
                        $ultimo=Area::orderBy('id_area','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->id_area+1;
                        }

                        $areaReceta=new Area;
                        $areaReceta->id_area=$suma;
                        $areaReceta->descripcion=$data->narea;
                        $areaReceta->estado='A';
                        $existe=Area::where('descripcion',$areaReceta->descripcion)
                        ->where('estado','A')->first();
                        if(is_null($existe)){
                            $areaReceta->save();
                            $idAreaRec=$areaReceta->id_area;
                        }else{
                            $idAreaRec=$existe->id_area;
                        } 

                        $observ="RECETA MEDICA HOSPITALIZACION";
                        $tipoAreaRec="Hospitalizacion";

                        if($data->idbodega==6){
                            $bodega_restar=6; //farmacia gral
                            $id_servicio=26;
                        }else{
                            $bodega_restar=20; //farmacia dialisis
                            $id_servicio=26;
                        }

                      

                    }else if($data->idarea===31 || $data->idarea===32){
                        //emergencia 
                        $areaReceta=DB::connection('pgsql')->table('bodega.area')
                        ->where('descripcion','EMERGENCIA')
                        ->first();
                        $observ="RECETA MEDICA EMERGENCIA";
                        $tipoAreaRec="Emergencia";
                        if($data->idarea===31){
                            $id_servicio=9000;
                        }else{
                            $id_servicio=9001;
                        }
                      
                        $bodega_restar=6; //farmacia gral
                        $idAreaRec=$areaReceta->id_area;
                    }else{
                        $ultimo=Area::orderBy('id_area','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->id_area+1;
                        }

                        //verificamos si existe esa area(dependencia) q viene del sys, si no existe la registramos
                        $areaReceta=new Area;
                        $areaReceta->id_area=$suma;
                        $areaReceta->descripcion=$data->narea;
                        $areaReceta->estado='A';
                        $existe=Area::where('descripcion',$areaReceta->descripcion)
                        ->where('estado','A')->first();
                        if(is_null($existe)){
                            $areaReceta->save();
                            $idAreaRec=$areaReceta->id_area;
                        }else{
                            $idAreaRec=$existe->id_area;
                        } 
                                              
                        
                        
                        $bodega_restar=6; //farmacia gral
                        $id_servicio=$data->id_servicio;
                       
                        $observ="RECETA MEDICA HOSPITALIZACION";
                        $tipoAreaRec="Hospitalizacion";

                        
                    }
                    
                    $ultimo=Comprobante::orderBy('idcomprobante','desc')->first();
                    if(is_null($ultimo)){
                        $suma=1;
                    }else{
                        $suma=$ultimo->idcomprobante+1;
                    }
                    
                    $comprobante=new Comprobante();
                    $comprobante->idcomprobante=$suma;
                    $comprobante->idtipo_comprobante=8; //receta
                    $comprobante->secuencial=$tipocomp_old->numcom+1;
                    $comprobante->descripcion=$tipocomp_old->razoncom;
                    $comprobante->fecha_hora=$data->fecha." ".$data->hora;
                    $comprobante->fecha=$data->fecha;
                    $comprobante->idbodega=$bodega_restar;
                    $comprobante->observacion=$observ." -".$data->idcomprobante;
                    $comprobante->id_usuario_ingresa=$user->id;
                    $comprobante->area=$idAreaRec;
                    $comprobante->id_paciente=$data->idpaciente;
                    $comprobante->id_cie10=$data->cie10_id;
                    $comprobante->codigo_old="PedidoAFarm";
                    $comprobante->estado="Activo";
                    $comprobante->fecha_uso_item=$data->fecha;
                    $comprobante->tipoarea=$tipoAreaRec;
                    $comprobante->id_comp_receta=$data->idcomprobante;
                    $comprobante->id_servicio=$id_servicio;
                    $comprobante->id_especialidad=$id_especialidad;
                   
                    //existe
                    $existe=Comprobante::where('id_comp_receta',$data->idcomprobante)
                    ->where('estado','Activo')
                    ->where('codigo_old','<>','Revertido')
                    // ->delete();
                    ->first();
                   
                  
                    //solo si no existe
                    if(is_null($existe)){
                      
                        $comprobante->save();
                        $tipocomp_old->numcom=$comprobante->secuencial;
                        $tipocomp_old->save();
                    }


                }

                return (['mensaje'=>'Receta procesadas al comprobante','error'=>false]); 

            } catch (\Throwable $e) {
                DB::connection('pgsql')->rollback();
                Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
                return (['mensaje'=>'Ocurrió un error,intentelo más tarde','error'=>true]); 
            }
        });
        return ($transaction);
    }

    public function procesaPedidos($compr, $idbodega){

        $transaction=DB::connection('pgsql')->transaction(function() use ($compr, $idbodega){
            try{

                $total_comprobante=0;
            
                $compr_inv=DB::table('bodega.comprobante')
                ->select('observacion','id_usuario_ingresa','idcomprobante')
                ->where('idcomprobante',$compr)
                ->first();
                
                            
                $id_old=$compr_inv->observacion;
                $id_old=explode('-',$id_old);
             
                if($idbodega==7){                      
                    $datosPa=DB::table('inventario.detallecomprobante')
                    ->join('bodega.insumo', 'inventario.detallecomprobante.idbodprod', '=', 'bodega.insumo.codinsumo')
                    ->where("idcomprobante", $id_old[1])
                    ->select('cantidad','idbodprod','idcomprobante', DB::raw("CONCAT(insumo) as nombre_completo"))
                    ->get(); 
                }else{
                    $datosPa=DB::table('inventario.detallecomprobante')
                    ->join('bodega.medicamentos', 'inventario.detallecomprobante.idbodprod', '=', 'bodega.medicamentos.coditem')
                    ->where("idcomprobante", $id_old[1])
                    ->select('cantidad','idbodprod','idcomprobante', DB::raw("CONCAT(nombre, ' ', concentra, ' ', forma, ' ', presentacion) as nombre_completo"))
                    ->get(); 
                }               
               
                //elimino los detalle y pedidos previos
                $detalleElimina=DetalleComprobante::where('idcomprobante', $compr)
                ->get();
                foreach($detalleElimina as $data){
                    $eliminaPedido=PedidoBodegaGral::where('iddetallecomprobante', $data->iddetalle_comprobante)
                    ->first();
                    $eliminaPedido->delete();
                }
                $EliminaDetalle=DetalleComprobante::where('idcomprobante', $compr)
                ->delete();
                         
                foreach($datosPa as $info){
                    $cantidad=$info->cantidad;
                    $item=$info->idbodprod; 
                    $fecha_Actual=date('Y-m-d');
                 
                    // $StockPB=DB::table('bodega.prodxbod as pb')
                    // ->leftJoin('bodega.existencia as e', 'e.idbodprod','pb.idbodprod')
                    // ->where('pb.idbodega',$idbodega)
                    // ->where('existencia','>',0)
                    // ->where('idprod',$item)
                    // ->whereDate('e.fecha_caducidad','>=',$fecha_Actual)
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

                    $cantidad_item=0;                                
                    $cantidad_item=$cantidad;
                                    
                    if(sizeof($StockPB)==0){
                        $total_item=0;
                        //registramos los detalles
                        $ultimo=DetalleComprobante::orderBy('iddetalle_comprobante','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->iddetalle_comprobante+1;
                        }
                        $detalles=new DetalleComprobante();
                        $detalles->iddetalle_comprobante=$suma;
                        $detalles->idcomprobante=$compr;
                        $detalles->id_item=$item;
                        $detalles->id_bodega=$idbodega;
                        $detalles->cantidad=$cantidad_item;
                        $detalles->precio=0;
                        $detalles->descuento=0;
                        $detalles->total=0;
                        $detalles->iva=0;
                        $detalles->fecha=date('Y-m-d H:i:s');
                        $detalles->save(); 

                        $ultimo=PedidoBodegaGral::orderBy('idpedido_bod_gral','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->idpedido_bod_gral+1;
                        }

                        //registramos el pedido temporal
                        $pedido_temp=new PedidoBodegaGral();
                        $pedido_temp->idpedido_bod_gral=$suma;
                        $pedido_temp->iddetallecomprobante=$detalles->iddetalle_comprobante;
                        $pedido_temp->cantidad_pedida=$cantidad_item;
                        $pedido_temp->idbodega=$idbodega;
                        $pedido_temp->id_solicita=$compr_inv->id_usuario_ingresa;
                        $pedido_temp->fecha_solicita=date('Y-m-d H:i:s');
                        $pedido_temp->iditem=$item;
                        $pedido_temp->estado="Temporal";
                        $pedido_temp->save();  
                        
                        $total_comprobante=$total_comprobante +  $total_item;
                    }
                    else{
                       
                        foreach($StockPB as $data){

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
                                        // $pedido_temp->lote=$Prodbod->existencias->lote;
                                        // $pedido_temp->fecha_caducidad=$Prodbod->existencias->fecha_caducidad;
                                        // $pedido_temp->fecha_elabora=$Prodbod->existencias->fecha_elaboracion;

                                        $pedido_temp->lote=$Prodbod->lote->lote;
                                        $pedido_temp->fecha_elabora=$Prodbod->lote->felabora;
                                        $pedido_temp->fecha_caducidad=$Prodbod->lote->fcaduca;

                                        $pedido_temp->cantidad_pedida=$cantidad_item;
                                        $pedido_temp->idbodega=$idbodega;
                                        $pedido_temp->id_solicita=$compr_inv->id_usuario_ingresa;
                                        $pedido_temp->fecha_solicita=date('Y-m-d H:i:s');
                                        $pedido_temp->idbodpro=$Prodbod->idbodprod;
                                        $pedido_temp->iditem=$item;
                                        $pedido_temp->estado="Solicitado";
                                        // $pedido_temp->id_paquete=$datosPa->idpaquete;
                                        $pedido_temp->save();  
                                        
                                        $total_comprobante=$total_comprobante +  $total_item;

                                        break;
                                    }
                
                                }else{
                                    
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
                                    // $pedido_temp->lote=$Prodbod->existencias->lote;
                                    // $pedido_temp->fecha_caducidad=$Prodbod->existencias->fecha_caducidad;
                                    // $pedido_temp->fecha_elabora=$Prodbod->existencias->fecha_elaboracion;

                                    $pedido_temp->lote=$Prodbod->lote->lote;
                                    $pedido_temp->fecha_caducidad=$Prodbod->lote->felabora;
                                    $pedido_temp->fecha_elabora=$Prodbod->lote->fcaduca;


                                    $pedido_temp->cantidad_pedida=$nuevoStock;
                                    $pedido_temp->idbodega=$detalles->id_bodega;
                                    $pedido_temp->id_solicita=$compr_inv->id_usuario_ingresa;
                                    $pedido_temp->fecha_solicita=date('Y-m-d H:i:s');
                                    $pedido_temp->idbodpro=$Prodbod->idbodprod;
                                    $pedido_temp->iditem=$item;
                                    $pedido_temp->estado="Solicitado";
                                    $pedido_temp->save();
                                    
                                    $cantidad_item=$cantidad_item - $Prodbod->existencia;

                                    $total_comprobante=$total_comprobante +  $total_item;
                                }
                                
                            }

                        }
                    }
                }
                if($total_comprobante==0){
                    return (['mensaje'=>'No existe stock para los items solicitados','error'=>false, 'sin_stock'=>'S', 'listado'=>$datosPa]); 
                }
                $actualizaTotal=Comprobante::find($compr);
                $actualizaTotal->subtotal=$total_comprobante;
                $actualizaTotal->total=$total_comprobante;
                $actualizaTotal->save();

                return (['total_comprobante'=>$total_comprobante,'error'=>false]); 

            } catch (\Throwable $e) {
                DB::connection('pgsql')->rollback();
                Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
                return (['mensaje'=>'Ocurrió un error,intentelo más tarde','error'=>true]); 
            }
        });
        return ($transaction);
        
    }

    public function detallePedidoBodegaFarm($id,$idbodega){
       
        try{
            
            $info= DB::connection('pgsql')->table('bodega.comprobante as comp')           
            ->where('comp.idcomprobante',$id)
            ->where('comp.estado','Activo')
            ->whereIn('comp.codigo_old',['PedidoAFarm','Revertido'])
            ->first();
             
            if(is_null($info)){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'El pedido ya fue entregado'
                ]);
            }
            
            
            $datosReceta=0;
           
            if($idbodega==1 || $idbodega==17 || $idbodega==20 || $idbodega==6){//medicamento
                $tipo_comp=$info->idtipo_comprobante;
              
               
                if($idbodega==20 || $idbodega==6){
                        //procedemos a registrar los detalle y pedidos
                    if($tipo_comp!=23){
                        $detalle=$this->procesaPedidos($id,$idbodega);
                       
                        if($detalle['error']==true){
                            return response()->json([
                                'error'=>true,
                                'mensaje'=>$detalle['mensaje']
                            ]);
                        }
                    }else{
                        if($info->guarda_detalle_pedido=="S"){
                            $detalle=$this->objPedidos->procesaPedidos($id,$idbodega);                       
                            if($detalle['error']==true){
                                return response()->json([
                                    'error'=>true,
                                    'mensaje'=>$detalle['mensaje']
                                ]);
                            }
                        }
                    }

                    $datosReceta=DB::table('bodega.comprobante as comp')
                    ->leftJoin('esq_pacientes.pacientes as per_pac', 'per_pac.id_paciente','comp.id_paciente')
                    ->leftJoin('esq_rdacaa.cie10', 'cie10.cie10_id','comp.id_cie10')
                    ->leftJoin('esq_receta_electronica.receta as rec', 'rec.id_comprobante','comp.id_comp_receta')
                    ->leftJoin('esq_receta_electronica.receta_acompanante as acomp', 'acomp.id_receta','rec.id_receta')
                    ->select(DB::raw("CONCAT(per_pac.apellido1,' ', per_pac.apellido2,' ', per_pac.nombre1,' ', per_pac.nombre2) AS paciente"),
                   'per_pac.documento as cedula_paciente',DB::raw("CONCAT(cie10_codigo,' -- ',cie10_descripcion) AS descripcion_cie_10"),'rec.alergia','rec.signos_alarma','comp.idcomprobante',DB::raw("CONCAT(acomp.apellido1,' ', acomp.apellido2,' ', acomp.nombre1,' ', acomp.nombre2) AS acompanante"))
                   ->where('comp.idcomprobante', $id)
                    ->first();
                }

             
                $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
                ->leftJoin('bodega.detalle_comprobante as detcomp', 'detcomp.idcomprobante','comp.idcomprobante')
                ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                ->leftJoin('bodega.medicamentos as medi', 'medi.coditem','detcomp.id_item')
                ->leftJoin('bodega.prodxbod as pb', 'pb.idbodprod','pedido.idbodpro')
                ->select(DB::raw("CONCAT(medi.nombre,' - ', medi.concentra,' - ', medi.forma) AS nombre_item"),'pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','pb.existencia as stock','medi.coditem', 'detcomp.iddetalle_comprobante as iddetalle','medi.coditem as iditem')
                ->where('comp.idcomprobante',$id)
                ->where('comp.estado','Activo')
                ->where(function($q) use($idbodega) {
                    if($idbodega==1 || $idbodega==17){ //pedido a bodega general desde farmacia
                        $q->whereIn('comp.codigo_old',['PedidoAFarm','Revertido']);
                    }else{ //pedido a farmacia desde areas
                        $q->whereIn('comp.codigo_old',['PedidoAFarm','Revertido']);
                    }
                })
                ->distinct()
                ->get();
               
                foreach($info as $data){
                
                    if($idbodega==20){//farmacia dialisis medicamentso
                        $actualizaStock=Medicamento::where('coditem',$data->iditem)->first();
                        if(!is_null($actualizaStock)){
                            $actualizaStock->stock_farm_dialisis=$data->stock;
                            $actualizaStock->save();
                        }else{
                            // log::info("actualizaStock -medic -id".$data->iditem);
                        }
                    }else if($idbodega==17){ //bodega dialisis medicamentso
                        $actualizaStock=Medicamento::where('coditem',$data->iditem)->first();
                        if(!is_null($actualizaStock)){
                            $actualizaStock->stock_bod_dialisis=$data->stock;
                            $actualizaStock->save();
                        }else{
                            // log::info("actualizaStock -medic -id".$data->iditem);
                        }
                    }else if($idbodega==6){ //farmacia gral medicamentso
                        $actualizaStock=Medicamento::where('coditem',$data->iditem)->first();
                        if(!is_null($actualizaStock)){
                            $actualizaStock->stock=$data->stock;
                            $actualizaStock->save();
                        }else{
                            // log::info("actualizaStock -medic -id".$data->iditem);
                        }
                    }else if($idbodega==1){ //bodega gral medicamentso
                        $actualizaStock=Medicamento::where('coditem',$data->iditem)->first();
                        if(!is_null($actualizaStock)){
                            $actualizaStock->stock_bod=$data->stock;
                            $actualizaStock->save();
                        }else{
                            // log::info("actualizaStock -medic -id".$data->iditem);
                        }
                    }
                }
                

            }else if($idbodega==2  || $idbodega==7 || $idbodega==18 || $idbodega==21){//insumo
                
                if($idbodega==7 ){
                    //si es insumo por paciente desde hospitalizacion
                    if($info->id_comp_receta){
                        //procedemos a registrar los detalle y pedidos
                        $detalle=$this->procesaPedidos($id,$idbodega);
                        if($detalle['error']==true){
                            return response()->json([
                                'error'=>true,
                                'mensaje'=>$detalle['mensaje']
                            ]);
                        }
      
                    }else{
                        if($info->guarda_detalle_pedido=="S"){
                            $detalle=$this->objPedidos->procesaPedidos($id,$idbodega);                       
                            if($detalle['error']==true){
                                return response()->json([
                                    'error'=>true,
                                    'mensaje'=>$detalle['mensaje']
                                ]);
                            }
                        }
                    }
                      
                    $datosReceta=DB::table('bodega.comprobante as comp')
                    ->leftJoin('inventario.comprobante as comp_old', 'comp_old.idcomprobante','comp.id_comp_receta')
                    ->leftJoin('esq_catalogos.servicio as s', 's.id_servicio','comp.id_servicio')
                    ->leftJoin('esq_pacientes.pacientes as per_pac', 'per_pac.id_paciente','comp.id_paciente')
                    ->leftJoin('esq_rdacaa.cie10', 'cie10.cie10_id','comp.id_cie10')
                    ->leftJoin('esq_receta_electronica.receta as rec', 'rec.id_comprobante','comp.id_comp_receta')
                    ->leftJoin('esq_receta_electronica.receta_acompanante as acomp', 'acomp.id_receta','rec.id_receta')
                    ->select(DB::raw("CONCAT(per_pac.apellido1,' ', per_pac.apellido2,' ', per_pac.nombre1,' ', per_pac.nombre2) AS paciente"),
                    'per_pac.documento as cedula_paciente',DB::raw("CONCAT(cie10_codigo,' -- ',cie10_descripcion) AS descripcion_cie_10"),'rec.alergia','rec.signos_alarma','comp.idcomprobante',DB::raw("CONCAT(acomp.apellido1,' ', acomp.apellido2,' ', acomp.nombre1,' ', acomp.nombre2) AS acompanante"),'s.nombre as dependencia','comp.tipoarea',DB::raw("CONCAT(comp_old.cod_diagnostico,' - ', comp_old.descripcion_diagnostico) AS diagn"))
                    ->where('comp.idcomprobante', $id)
                    ->first();
                }

                $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
                ->leftJoin('bodega.detalle_comprobante as detcomp', 'detcomp.idcomprobante','comp.idcomprobante')
                ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                ->leftJoin('bodega.insumo as insu', 'insu.codinsumo','detcomp.id_item')
                ->leftJoin('bodega.prodxbod as pb', 'pb.idbodprod','pedido.idbodpro')
                ->select('insu.insumo as nombre_item','pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','pb.existencia as stock','detcomp.idcomprobante','detcomp.iddetalle_comprobante as iddetalle','insu.codinsumo as iditem')
                ->where('comp.idcomprobante',$id)
                ->where('comp.estado','Activo')
                // ->where('comp.codigo_old','Pedido')
                ->where(function($q) use($idbodega) {
                    if($idbodega==2 || $idbodega==18){ //pedido a bodega general desde farmacia
                        $q->whereIn('comp.codigo_old',['Pedido','Revertido']);
                    }else{ //pedido a farmacia desde laboratorio
                        $q->whereIn('comp.codigo_old',['PedidoAFarm','Revertido']);
                    }
                })
                ->distinct()
                ->get();


            }else if($idbodega==13 || $idbodega==23 || $idbodega==25 || $idbodega==28){//reactivo
           
                if($idbodega==25 || $idbodega==28){
                    if($info->guarda_detalle_pedido=="S"){
                        $detalle=$this->objPedidos->procesaPedidos($id,$idbodega);                       
                        if($detalle['error']==true){
                            return response()->json([
                                'error'=>true,
                                'mensaje'=>$detalle['mensaje']
                            ]);
                        }
                    }
                }
                                 
                $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
                ->leftJoin('bodega.detalle_comprobante as detcomp', 'detcomp.idcomprobante','comp.idcomprobante')
                ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                ->leftJoin('bodega.laboratorio as lab', 'lab.id','detcomp.id_item')
                ->leftJoin('bodega.prodxbod as pb', 'pb.idbodprod','pedido.idbodpro')
                ->select('lab.descri as nombre_item','pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','pb.existencia as stock','detcomp.idcomprobante','detcomp.iddetalle_comprobante as iddetalle','comp.codigo_old','lab.id as iditem')
                ->where('comp.idcomprobante',$id)
                ->where('comp.estado','Activo')
                ->where(function($q) use($idbodega) {
                    if($idbodega==13 || $idbodega==23){ //pedido a bodega general desde farmacia
                        $q->whereIn('comp.codigo_old',['Pedido','Revertido']);
                    }else{ //pedido a farmacia desde laboratorio
                        $q->whereIn('comp.codigo_old',['PedidoAFarm','Revertido']);
                    }
                })
                ->distinct()
                ->get();
                
              
            }else if($idbodega==8 ||$idbodega==19 || $idbodega==22 || $idbodega==27){//materiales

                if($idbodega==22 || $idbodega==27){
                    if($info->guarda_detalle_pedido=="S"){
                        $detalle=$this->objPedidos->procesaPedidos($id,$idbodega);                       
                        if($detalle['error']==true){
                            return response()->json([
                                'error'=>true,
                                'mensaje'=>$detalle['mensaje']
                            ]);
                        }
                    }
                }

                $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
                ->leftJoin('bodega.detalle_comprobante as detcomp', 'detcomp.idcomprobante','comp.idcomprobante')
                ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                ->leftJoin('bodega.laboratorio as lab', 'lab.id','detcomp.id_item')
                ->leftJoin('bodega.prodxbod as pb', 'pb.idbodprod','pedido.idbodpro')
                ->select('lab.descri as nombre_item','pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','pb.existencia as stock','detcomp.idcomprobante','detcomp.iddetalle_comprobante as iddetalle','comp.codigo_old','lab.id as iditem')
                ->where('comp.idcomprobante',$id)
                ->where('comp.estado','Activo')
                ->where(function($q) use($idbodega) {
                    if($idbodega==8 || $idbodega==19){ //pedido a bodega general desde farmacia
                        $q->whereIn('comp.codigo_old',['Pedido','Revertido']);
                    }else{ //pedido a farmacia desde laboratorio
                        $q->whereIn('comp.codigo_old',['PedidoAFarm','Revertido']);
                    }
                })
                ->distinct()
                ->get();
              
            
            }else if($idbodega==14 || $idbodega==24 || $idbodega==26 || $idbodega==29 ){//micro

                if($idbodega==26 || $idbodega==29){
                    if($info->guarda_detalle_pedido=="S"){
                        $detalle=$this->objPedidos->procesaPedidos($id,$idbodega);                       
                        if($detalle['error']==true){
                            return response()->json([
                                'error'=>true,
                                'mensaje'=>$detalle['mensaje']
                            ]);
                        }
                    }
                }

                $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
                ->leftJoin('bodega.detalle_comprobante as detcomp', 'detcomp.idcomprobante','comp.idcomprobante')
                ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                ->leftJoin('bodega.laboratorio as lab', 'lab.id','detcomp.id_item')
                ->leftJoin('bodega.prodxbod as pb', 'pb.idbodprod','pedido.idbodpro')
                ->select('lab.descri as nombre_item','pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','pb.existencia as stock','detcomp.idcomprobante','detcomp.iddetalle_comprobante as iddetalle','comp.codigo_old','lab.id as iditem')
                ->where('comp.idcomprobante',$id)
                ->where('comp.estado','Activo')
                ->where(function($q) use($idbodega) {
                    if($idbodega==14 || $idbodega==24){ //pedido a bodega general desde farmacia
                        $q->whereIn('comp.codigo_old',['Pedido','Revertido']);
                    }else{ //pedido a farmacia desde laboratorio
                        $q->whereIn('comp.codigo_old',['PedidoAFarm','Revertido']);
                    }
                })
                ->distinct()
                ->get();
            
            }else if($idbodega==31){ //desde dialisisi
               
                $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
                ->leftJoin('bodega.detalle_comprobante as detcomp', 'detcomp.idcomprobante','comp.idcomprobante')
                ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                ->leftJoin('bodega.medicamentos as medi', 'medi.coditem','detcomp.id_item')
                ->leftJoin('bodega.insumo as i', 'i.codinsumo','detcomp.id_item')
                ->select('pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','medi.stock_farm_dialisis as stock_med','i.stock_farm_dialisis as stock_ins','detcomp.idcomprobante','detcomp.iddetalle_comprobante as iddetalle','comp.codigo_old',DB::raw("CONCAT(medi.nombre,' - ', medi.concentra,' - ', medi.forma) AS nombre_item_med"),DB::raw("CONCAT(i.insumo) AS nombre_item_insumo"),'detcomp.id_item')
                ->where('comp.idcomprobante',$id)
                ->where('comp.estado','Activo')
                ->where('comp.codigo_old','PedidoAFarm')
                ->distinct()
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
                    $info[$key]->stock=$stock_item;
        
                }

                $lista_final_agrupada=[];
                foreach ($info as $key => $item){                
                    if(!isset($lista_final_agrupada[$item->nombre_item])) {
                        $lista_final_agrupada[$item->nombre_item]=array($item);
                
                    }else{
                        array_push($lista_final_agrupada[$item->nombre_item], $item);
                    }
                }

                $info=$lista_final_agrupada;

            
            }             
           
            return response()->json([
                'error'=>false,
                'resultado'=>$info,
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

    public function detallePedidoBodegaFarmTodo($id,$idbodega){
       
        try{
            
            $info=Comprobante::with('entregado','responsable','especialidad')->where('idcomprobante',$id)
            ->where('estado','Activo')
            ->whereIn('codigo_old',['Entregado','EntregadoF','EgresoF'])
            ->first();
                      
            if(is_null($info)){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'El pedido no se puede revertir'
                ]);
            }
           
            $dataInfo=$info;
            
            $datosReceta=0;
           
            if($idbodega==1 || $idbodega==17 || $idbodega==20 || $idbodega==6){//medicamento
             
                $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
                ->leftJoin('bodega.detalle_comprobante as detcomp', 'detcomp.idcomprobante','comp.idcomprobante')
                ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                ->leftJoin('bodega.medicamentos as medi', 'medi.coditem','detcomp.id_item')
                ->leftJoin('bodega.prodxbod as pb', 'pb.idbodprod','pedido.idbodpro')
                ->select(DB::raw("CONCAT(medi.nombre,' - ', medi.concentra,' - ', medi.forma) AS nombre_item"),'pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','pb.existencia as stock','medi.coditem', 'detcomp.iddetalle_comprobante as iddetalle','medi.coditem as iditem','detcomp.cantidad','pedido.idpedido_bod_gral','comp.codigo_old','pedido.cantidad_entregada','detcomp.precio')
                ->where('comp.idcomprobante',$id)
                ->where('comp.estado','Activo')
              
                ->distinct()
                ->get();

                if(is_null($dataInfo->total)){
                    $total=0;
                    foreach($info as $data){
                        if($data->cantidad_entregada > 0){
                            $precio=$data->cantidad_entregada * $data->precio;
                            $total=$total+$precio;
                        }
                    }
                    $dataInfo->total=number_format(($total),4,'.', '');
                    $dataInfo->save();
                }
              
                foreach($info as $data){
                
                    if($idbodega==20){//farmacia dialisis medicamentso
                        $actualizaStock=Medicamento::where('coditem',$data->iditem)->first();
                        $actualizaStock->stock_farm_dialisis=$data->stock;
                        $actualizaStock->save();
                    }else if($idbodega==17){ //bodega dialisis medicamentso
                        $actualizaStock=Medicamento::where('coditem',$data->iditem)->first();
                        $actualizaStock->stock_bod_dialisis=$data->stock;
                        $actualizaStock->save();
                    }else if($idbodega==6){ //farmacia gral medicamentso
                        $actualizaStock=Medicamento::where('coditem',$data->iditem)->first();
                        $actualizaStock->stock=$data->stock;
                        $actualizaStock->save();
                    }else if($idbodega==1){ //bodega gral medicamentso
                        $actualizaStock=Medicamento::where('coditem',$data->iditem)->first();
                        $actualizaStock->stock_bod=$data->stock;
                        $actualizaStock->save();
                    }
                }
                

            }else if($idbodega==2  || $idbodega==7 || $idbodega==18 || $idbodega==21){//insumo
              

                $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
                ->leftJoin('bodega.detalle_comprobante as detcomp', 'detcomp.idcomprobante','comp.idcomprobante')
                ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                ->leftJoin('bodega.insumo as insu', 'insu.codinsumo','detcomp.id_item')
                ->leftJoin('bodega.prodxbod as pb', 'pb.idbodprod','pedido.idbodpro')
                ->select('insu.insumo as nombre_item','pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','pb.existencia as stock','detcomp.idcomprobante','detcomp.iddetalle_comprobante as iddetalle','insu.codinsumo as iditem','pedido.cantidad_entregada')
                ->where('comp.idcomprobante',$id)
                ->where('comp.estado','Activo')               
                ->distinct()
                ->get();

                if(is_null($dataInfo->total)){
                    $total=0;
                    foreach($info as $data){
                        if($data->cantidad_entregada > 0){
                            $precio=$data->cantidad_entregada * $data->precio;
                            $total=$total+$precio;
                        }
                    }
                    $dataInfo->total=number_format(($total),4,'.', '');
                    $dataInfo->save();
                }
              
              
            }else if($idbodega==13 || $idbodega==23 || $idbodega==25 || $idbodega==28){//reactivo
                                 
                $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
                ->leftJoin('bodega.detalle_comprobante as detcomp', 'detcomp.idcomprobante','comp.idcomprobante')
                ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                ->leftJoin('bodega.laboratorio as lab', 'lab.id','detcomp.id_item')
                ->leftJoin('bodega.prodxbod as pb', 'pb.idbodprod','pedido.idbodpro')
                ->select('lab.descri as nombre_item','pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','pb.existencia as stock','detcomp.idcomprobante','detcomp.iddetalle_comprobante as iddetalle','comp.codigo_old','lab.id as iditem','pedido.cantidad_entregada')
                ->where('comp.idcomprobante',$id)
                ->where('comp.estado','Activo')
                ->distinct()
                ->get();

                if(is_null($dataInfo->total)){
                    $total=0;
                    foreach($info as $data){
                        if($data->cantidad_entregada > 0){
                            $precio=$data->cantidad_entregada * $data->precio;
                            $total=$total+$precio;
                        }
                    }
                    $dataInfo->total=number_format(($total),4,'.', '');
                    $dataInfo->save();
                }
                
              
            }else if($idbodega==8 ||$idbodega==19 || $idbodega==22 || $idbodega==27){//materiales
                $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
                ->leftJoin('bodega.detalle_comprobante as detcomp', 'detcomp.idcomprobante','comp.idcomprobante')
                ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                ->leftJoin('bodega.laboratorio as lab', 'lab.id','detcomp.id_item')
                ->leftJoin('bodega.prodxbod as pb', 'pb.idbodprod','pedido.idbodpro')
                ->select('lab.descri as nombre_item','pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','pb.existencia as stock','detcomp.idcomprobante','detcomp.iddetalle_comprobante as iddetalle','comp.codigo_old','lab.id as iditem','pedido.cantidad_entregada')
                ->where('comp.idcomprobante',$id)
                ->where('comp.estado','Activo')
                ->distinct()
                ->get();

                if(is_null($dataInfo->total)){
                    $total=0;
                    foreach($info as $data){
                        if($data->cantidad_entregada > 0){
                            $precio=$data->cantidad_entregada * $data->precio;
                            $total=$total+$precio;
                        }
                    }
                    $dataInfo->total=number_format(($total),4,'.', '');
                    $dataInfo->save();
                }
                
            
            }else if($idbodega==14 || $idbodega==24 || $idbodega==26 || $idbodega==29 ){//micro
                $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
                ->leftJoin('bodega.detalle_comprobante as detcomp', 'detcomp.idcomprobante','comp.idcomprobante')
                ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                ->leftJoin('bodega.laboratorio as lab', 'lab.id','detcomp.id_item')
                ->leftJoin('bodega.prodxbod as pb', 'pb.idbodprod','pedido.idbodpro')
                ->select('lab.descri as nombre_item','pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','pb.existencia as stock','detcomp.idcomprobante','detcomp.iddetalle_comprobante as iddetalle','comp.codigo_old','lab.id as iditem','pedido.cantidad_entregada')
                ->where('comp.idcomprobante',$id)
                ->where('comp.estado','Activo')
                ->distinct()
                ->get();

                if(is_null($dataInfo->total)){
                    $total=0;
                    foreach($info as $data){
                        if($data->cantidad_entregada > 0){
                            $precio=$data->cantidad_entregada * $data->precio;
                            $total=$total+$precio;
                        }
                    }
                    $dataInfo->total=number_format(($total),4,'.', '');
                    $dataInfo->save();
                }
            
            }else if($idbodega==31){ //desde dialisisi
               
                $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
                ->leftJoin('bodega.detalle_comprobante as detcomp', 'detcomp.idcomprobante','comp.idcomprobante')
                ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                ->leftJoin('bodega.medicamentos as medi', 'medi.coditem','detcomp.id_item')
                ->leftJoin('bodega.insumo as i', 'i.codinsumo','detcomp.id_item')
                ->select('pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','medi.stock_farm_dialisis as stock_med','i.stock_farm_dialisis as stock_ins','detcomp.idcomprobante','detcomp.iddetalle_comprobante as iddetalle','comp.codigo_old',DB::raw("CONCAT(medi.nombre,' - ', medi.concentra,' - ', medi.forma) AS nombre_item_med"),DB::raw("CONCAT(i.insumo) AS nombre_item_insumo"),'detcomp.id_item','pedido.cantidad_entregada')
                
                ->where('comp.idcomprobante',$id)
                ->where('comp.estado','Activo')
                ->where('comp.codigo_old','Entregado')
                ->distinct()
                ->get();

                if(is_null($dataInfo->total)){
                    $total=0;
                    foreach($info as $data){
                        if($data->cantidad_entregada > 0){
                            $precio=$data->cantidad_entregada * $data->precio;
                            $total=$total+$precio;
                        }
                    }
                    $dataInfo->total=number_format(($total),4,'.', '');
                    $dataInfo->save();
                }


                foreach($info as $key => $data){
        
                    if($data->id_item>=30000){
                        $nombre_item=$data->nombre_item_insumo;
                        $stock_item=$data->stock_ins;
                       
                    }else{
                        $nombre_item=$data->nombre_item_med;
                        $stock_item=$data->stock_med;
                    }
                    $info[$key]->nombre_item=$nombre_item;
                    $info[$key]->stock=$stock_item;
        
                }
            
            }   
            
            if(is_null($dataInfo->responsable)){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'Opcion no disponible para el tipo de comprobante'
                ]); 
            }
                
            return response()->json([
                'error'=>false,
                'resultado'=>$info,
                'datosReceta'=>$datosReceta,
                'info'=>$dataInfo
            ]);
        }catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }


    public function anularPedido(Request $request){
        try{
           
            $id=$request->idcomp_anula;
            $comprobante=Comprobante::where('idcomprobante',$id)
            ->where('estado','Activo')
            ->first();
            if(!is_null($comprobante)){
                if($comprobante->codigo_old=="Entregado" || $comprobante->codigo_old=="EntregadoF"){
                    return response()->json([
                        'error'=>true,
                        'mensaje'=>'El pedido ya fue entregado'
                    ]);
                }else if($comprobante->codigo_old=="Anulado"){
                    return response()->json([
                        'error'=>true,
                        'mensaje'=>'El pedido ya fue anulado'
                    ]);
                }
                $comprobante->codigo_old="Anulado";
                $comprobante->detalle_anula=$request->motivo;
                $comprobante->id_anula=auth()->user()->id;
                $comprobante->fecha_hora_actualiza=date('Y-m-d H:i:s');
                $comprobante->save();

                $actualizaComprobanteReceta=ComprobanteReceta::where('idcomprobante',$comprobante->id_comp_receta)
                ->first();
                if(!is_null($actualizaComprobanteReceta)){
                    $actualizaComprobanteReceta->estado=99;
                    $actualizaComprobanteReceta->save();
                }

                return response()->json([
                    'error'=>false,
                    'mensaje'=>'El comprobante fue anulado exitosamente'
                ]);
                    
            }else{
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'El pedido no se encuentra disponible de anulacion'
                ]);
            }
                       
           
        }catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }

    public function revertirPedido(Request $request){

        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{
                $id=$request->idcomp_rever;
                $comprobante=Comprobante::with('detalle','entregado','responsable','nomarea','paciente','cie','especialidad')->where('idcomprobante',$id)
                ->where('estado','Activo')
                ->whereIn('codigo_old',['Entregado','EntregadoF'])
                ->first();
                
                if(!is_null($comprobante)){
                
                    $comprobante->codigo_old="Revertido";
                    $comprobante->detalle_revierte=$request->motivo;
                    $comprobante->id_revertido=auth()->user()->id;
                    $comprobante->fecha_hora_actualiza=date('Y-m-d H:i:s');
                    $comprobante->save();

                    
                    foreach($comprobante->detalle as $data){
                        //procedemos a sumar los valores en la tabla prodxbod
                        $prodXbod=ProductoBodega::where('idbodprod', $data->idbodprod)->first();
                        $existActua=$prodXbod->existencia;
                        $prodXbod->existencia=$existActua + $data->cantidad;
                        $prodXbod->save();

                        //actualizamo estado en tabla existencia
                        $existenciaActualiza=Existencia::where('iddetalle_comprobante',$data->iddetalle_comprobante)
                        ->whereIn('cod',['EABFA','EAB','EABA'])
                        ->first();
                        $existenciaActualiza->cod="REVERTIDO";
                        $existenciaActualiza->save();

                        //actualizamos tabla pedido
                        $pedidoActualiza=PedidoBodegaGral::where('iddetallecomprobante',$data->iddetalle_comprobante)
                        ->whereIn('estado',['EntregadoF','Entregado'])
                        ->first();
                        $pedidoActualiza->estado="REVERTIDO";
                        $pedidoActualiza->save();

                    }
                    
                    $actualizaComprobanteReceta=ComprobanteReceta::where('idcomprobante',$comprobante->id_comp_receta)
                    ->first();
                    if(!is_null($actualizaComprobanteReceta)){
                        $actualizaComprobanteReceta->estado=22;
                        $actualizaComprobanteReceta->save();
                    }

                    return response()->json([
                        'error'=>false,
                        'mensaje'=>'El comprobante fue revertido exitosamente'
                    ]);
                        
                }else{
                    return response()->json([
                        'error'=>true,
                        'mensaje'=>'El pedido no se encuentra disponible de reversion'
                    ]);
                }
                        
            
            } catch (\Throwable $e) {
                DB::connection('pgsql')->rollback();
                Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
                return (['mensaje'=>'Ocurrió un error,intentelo más tarde','error'=>true]); 
            }
        });
        return ($transaction);
    }

    public function detalleItemPaquete($id,$item){
       
        try{
                          
                $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
                ->leftJoin('bodega.detalle_comprobante as detcomp', 'detcomp.idcomprobante','comp.idcomprobante')
                ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                ->leftJoin('bodega.paquetes as paq', 'paq.id_paquete','pedido.id_paquete')
                ->leftJoin('bodega.medicamentos as medi', 'medi.coditem','detcomp.id_item')
                ->leftJoin('bodega.insumo as i', 'i.codinsumo','detcomp.id_item')
                ->leftJoin('bodega.prodxbod as pb', 'pb.idbodprod','pedido.idbodpro')
                ->select('pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','medi.stock_farm_dialisis as stock_med','i.stock_farm_dialisis as stock_ins','detcomp.idcomprobante','detcomp.iddetalle_comprobante as iddetalle','comp.codigo_old',DB::raw("CONCAT(medi.nombre,' - ', medi.concentra,' - ', medi.forma) AS nombre_item_med"),DB::raw("CONCAT(i.insumo) AS nombre_item_insumo"),'detcomp.id_item','pedido.id_paquete','paq.descripcion as nombre_paq','pb.existencia','pb.idbodprod','pedido.idpedido_bod_gral','pedido.idbodpro','pb.existencia as ex')
                ->where('comp.idcomprobante',$id)
                ->where('comp.estado','Activo')
                ->where('iditem',$item)
                ->distinct()
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
                    // $info[$key]->stock=$stock_item;
                    $info[$key]->stock=$data->existencia;
        
                }

                $lista_final_agrupada=[];
                foreach ($info as $key => $item){                
                    if(!isset($lista_final_agrupada[$item->nombre_item])) {
                        $lista_final_agrupada[$item->nombre_item]=array($item);
                
                    }else{
                        array_push($lista_final_agrupada[$item->nombre_item], $item);
                    }
                }

                $info=$lista_final_agrupada;

            
                         
                
            return response()->json([
                'error'=>false,
                'resultado'=>$info
            ]);
        }catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }

    public function verificaStockItem($idbodega, $iditem){
        try{
            $item=DB::table('bodega.prodxbod as proxbode')
            ->select(DB::raw('sum("existencia") as stock'),'idprod')
            ->where('idbodega',  $idbodega)
            ->where('idprod',$iditem)
            ->groupby('idprod')
            ->first();
            return (['data'=>$item,'error'=>false]);
        }catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return ['error'=>true, 'mensaje'=>'Ocurrió un error'];
            
        }
    }
    
    public function validaPedidoAfarm(Request $request){
        
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


                    $comprobar=DetalleComprobante::with('comprobante')
                    ->where('iddetalle_comprobante',$iddetalle)
                    ->first();
                   
                    $idItem=$comprobar->id_item;
                    $idBodegaItem=$comprobar->id_bodega;
                  
                    $tipoArea=$comprobar->comprobante->tipoarea;
                    $fecha_hora_genera=$comprobar->comprobante->fecha_hora;
                    $solo_hora=date('H',strtotime($fecha_hora_genera));
                    $hora_actual=date('H');
              
                    if($tipoArea=="Hospitalizacion" || $tipoArea=="Emergencia"){
                        //un dia maximo de entrega
                        $fechaUso=$comprobar->comprobante->fecha_uso_item;
                        if(!is_null($fechaUso)){
                            $bloquear="S";
                            //si la hora de generacion es mayor a las 11pm (23) permito hasta antes de las  3am (03)
                            if($solo_hora == '23' && $hora_actual < '02'){
                               $bloquear="N";
                            }else if(strtotime($fechaUso) < strtotime(date('Y-m-d'))){
                                if($bloquear=="S"){
                                    return (['mensaje'=>'La fecha máxima de despacho de esta solicitud superó el limite','error'=>true, 'act'=>'S']);
                                }
                                
                            }
                        }
                    }else if($tipoArea=="CE"){
                        //3 dias maximo de entrega
                        $fechaUso=$comprobar->comprobante->fecha_uso_item;
                        if(!is_null($fechaUso)){
                            // date('Y-m-d')
                            $hoy=date('2023-11-03');
                           
                            $fecha_emite = strtotime($fechaUso);
                            $fecha_actual = strtotime($hoy);

                            $diferenciaSegundos = $fecha_actual - $fecha_emite;
                            $diferenciaDias = $diferenciaSegundos / 86400;
                           
                            if($diferenciaDias > 3){
                                return (['mensaje'=>'La fecha máxima de despacho de esta solicitud superó el limite','error'=>true, 'act'=>'S']);
                            }
                        }
                    }
                   
                    //si no existe es xq lo elimino de la lista (actualizo)
                    if(is_null($validaPedido)){
                        DB::connection('pgsql')->rollback();
                        return (['mensaje'=>'El pedido fue actualizado, revise el detalle de nuevo','error'=>true, 'act'=>'S','cerr'=>'S']);
                    }
                                        
                    $validaPedido->cantidad_entregada=$request->cantidad_validada[$key];
                    $validaPedido->id_aprueba=auth()->user()->id;
                    $validaPedido->fecha_aprueba=date('Y-m-d H:i:s');
                    $validaPedido->estado="Entregado";
                    $validaPedido->save();

                    if($validaPedido->save()){
                       
                        $area_solicita=$comprobar->comprobante->nomarea->descripcion; 
                        $ultimo=Existencia::orderBy('idexistencia','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->idexistencia+1;
                        }
                        $existencia=new Existencia();
                        $existencia->idexistencia=$suma;
                        //$existencia->idexistencia=$ultimo->idexistencia+1;
                        $existencia->iddetalle_comprobante=$iddetalle;
                        $existencia->lote=$validaPedido->lote;
                        $existencia->resta=$validaPedido->cantidad_entregada;
                        $existencia->tipo="Egreso Bodega desde ".$area_solicita;
                        $existencia->cod="EABFA";
                        $existencia->fecha_hora=date('Y-m-d H:i:s');
                        $existencia->fecha_elaboracion=$validaPedido->fecha_elabora;
                        $existencia->fecha_caducidad=$validaPedido->fecha_caducidad;
                        $existencia->fecha=date('Y-m-d');
                        $existencia->idusuario=auth()->user()->id;
                        $existencia->idbodprod=$validaPedido->idbodpro;
                        
                        $existencia->id_pedido=$validaPedido->idpedido_bod_gral;
                        $existencia->idusuario_solicita=$validaPedido->id_solicita;
                        
                        $existencia->save();   


                        $item=DetalleComprobante::where('iddetalle_comprobante',$iddetalle)->first();  
                        
                        $verificaStockFarmacia=$this->verificaStockItem($idBodegaItem, $comprobar->id_item);
                        if($verificaStockFarmacia['error']==true){
                           
                            DB::connection('pgsql')->rollback();
                            return (['mensaje'=>'No se pudo obtener el stock actual del item con id '.$item->id_item. ' en la bodega #'.$idBodegaItem,'error'=>true]); 
                        } 
                        if(is_null($verificaStockFarmacia['data'])){
                            $stockItemActualFarmacia=0;
                        }else{
                            $stockItemActualFarmacia=$verificaStockFarmacia['data']->stock;
                        }

                        //restamos la existencia de la bodega principal
                        $actualizaStockOld =ProductoBodega::where('idbodprod',$validaPedido->idbodpro)
                        ->first();                
                        $stockactPB=$actualizaStockOld->existencia;    //stock actual antes de restar
                        $nuevoStock=$actualizaStockOld->existencia;
                        $nuevoStock_act=$nuevoStock - $request->cantidad_validada[$key];
                        $actualizaStockOld->existencia=$nuevoStock_act;  
                        $actualizaStockOld->fecha=date('Y-m-d');
                        $actualizaStockOld->idusuario=auth()->user()->id;
                        $actualizaStockOld->save();
                      
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
    
                        }else if($idBodegaItem==22 || $idBodegaItem==25 || $idBodegaItem==26){
                                                        
                            $laboratorio= Laboratorio::where('id',$comprobar->id_item)
                            ->first();
                            // $stock_Actual=$laboratorio->stock_diali_farmacia;
                            $stock_Actual=$stockItemActualFarmacia;
                            $laboratorio->stock_diali_farmacia=$stock_Actual - $request->cantidad_validada[$key];
                            $laboratorio->save(); 
    
                            // comprobamos que el stock actual no sea menor a lo q se va a quitar
                            if($stock_Actual < $request->cantidad_validada[$key]){
                                DB::connection('pgsql')->rollback();
                                return (['mensaje'=>'El stock actual del item '.$laboratorio->descri. " es  ".$stock_Actual,'error'=>true]); 
                            }
    
                        }else if($idBodegaItem==29 || $idBodegaItem==27 || $idBodegaItem==28){

                            $laboratorio= Laboratorio::where('id',$comprobar->id_item)
                            ->first();
                           
                            // $stock_Actual=$laboratorio->stock_farmacia;
                            $stock_Actual=$stockItemActualFarmacia;
                            $laboratorio->stock_farmacia=$stock_Actual - $request->cantidad_validada[$key];
                            $laboratorio->save(); 
    
                            // comprobamos que el stock actual no sea menor a lo q se va a quitar
                            if($stock_Actual < $request->cantidad_validada[$key]){
                                DB::connection('pgsql')->rollback();
                                return (['mensaje'=>'El stock actual del item '.$laboratorio->descri. " es  ".$stock_Actual,'error'=>true]); 
                            }
                        }else if($idBodegaItem==21){

                            //insumo dialisis
                            $insumo= Insumo::where('codinsumo',$comprobar->id_item)
                            ->first();

                            // $stock_Actual=$insumo->stock_farm_dialisis;
                            $stock_Actual=$stockItemActualFarmacia;
                            $insumo->stock_farm_dialisis=$stock_Actual - $request->cantidad_validada[$key];
                            $insumo->save(); 
    
                            // comprobamos que el stock actual no sea menor a lo q se va a quitar
                            if($stock_Actual < $request->cantidad_validada[$key]){
                                DB::connection('pgsql')->rollback();
                                return (['mensaje'=>'El stock actual del item '.$insumo->insumo. " es  ".$stock_Actual,'error'=>true]); 
                            }
                        }else if($idBodegaItem==7){

                            //insumo gral
                            $insumo= Insumo::where('codinsumo',$comprobar->id_item)
                            ->first();
                            // $stock_Actual=$insumo->stock;
                            $stock_Actual=$stockItemActualFarmacia;
                            $insumo->stock=$stock_Actual - $request->cantidad_validada[$key];
                            $insumo->save(); 
    
                            // comprobamos que el stock actual no sea menor a lo q se va a quitar
                            if($stock_Actual < $request->cantidad_validada[$key]){
                                DB::connection('pgsql')->rollback();
                                return (['mensaje'=>'El stock actual del item '.$insumo->insumo. " es  ".$stock_Actual,'error'=>true]); 
                            }
                        }else if($idBodegaItem==20){//faramcia medicamentos dialiss
                          
                            //med
                            $medicamentos= Medicamento::where('coditem',$comprobar->id_item)
                            ->first();
                            // $stock_Actual=$medicamentos->stock_farm_dialisis;
                            $stock_Actual=$stockItemActualFarmacia;
                            $medicamentos->stock_farm_dialisis=$stock_Actual - $request->cantidad_validada[$key];
                            $medicamentos->save(); 
    
                            // comprobamos que el stock actual no sea menor a lo q se va a quitar
                            if($stock_Actual < $request->cantidad_validada[$key]){
                                DB::connection('pgsql')->rollback();
                                return (['mensaje'=>'El stock actual del item '.$medicamentos->nombre. " es  ".$stock_Actual,'error'=>true]); 
                            }
                        }else if($idBodegaItem==6){//faramcia medicamentos gral

                            //med
                            $medicamentos= Medicamento::where('coditem',$comprobar->id_item)
                            ->first();
                            // $stock_Actual=$medicamentos->stock;
                            $stock_Actual=$stockItemActualFarmacia;
                            $medicamentos->stock=$stock_Actual - $request->cantidad_validada[$key];
                            $medicamentos->save(); 
    
                            // comprobamos que el stock actual no sea menor a lo q se va a quitar
                            if($stock_Actual < $request->cantidad_validada[$key]){
                                DB::connection('pgsql')->rollback();
                                return (['mensaje'=>'El stock actual del item '.$medicamentos->nombre. " es  ".$stock_Actual,'error'=>true]); 
                            }
                        }else{

                            DB::connection('pgsql')->rollback();
                            return (['mensaje'=>'Bodega no encontrada','error'=>true]); 
                           
                           
                        }
                    }
                }    

                if(!is_null($item->idcomprobante)){
                    $compr=Comprobante::where('idcomprobante',$item->idcomprobante)->first();
                    $compr->codigo_old="EntregadoF";
                    $compr->id_usuario_aprueba=auth()->user()->id;
                    $compr->fecha_aprobacion=date('Y-m-d H:i:s');
                    //$compr->fecha=date('Y-m-d');
                    $compr->save();

                    if($compr->idbodega==20 || $compr->idbodega==6 ){
                        //actualizamos el estado del comprobante inventario (sistema sys)

                        $id_old_inv=$compr->observacion;
                        $id_old_inv=explode('-',$id_old_inv);
                                       
                        if(sizeof($id_old_inv)>1 && $id_old_inv[1]!=""){
                            $act=InventarioComprobante::find($id_old_inv[1]);
                            
                            if(!is_null($act)){
                                $act->estado=9; ///entregado
                                $act->save();
                            }
                               
                        }else{
                           
                        }
                        
                    }
                }
                    
                return (['mensaje'=>'Items entregado exitosamente','error'=>false]);

            } catch (\Throwable $e) {
                DB::connection('pgsql')->rollback();
                Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
                Log::error("bodegaFarmacia--->validaPedidoAfarm => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
                return (['mensaje'=>'Ocurrió un error,intentelo más tarde','error'=>true]); 
            }
        });
        return ($transaction);
    }

    public function dispensarMedicamentos(){
        return view('gestion_farmacia.dispensar_receta');
    }

    public function filtrarReceta($ini, $fin){
        
        try{

            //comprobamos si existen recetas solicitadas
            $consultaReceta=$this->recetasPendientesSincronizar();
            
            $pedidos= DB::connection('pgsql')->table('bodega.comprobante as comp')
            ->leftJoin('esq_catalogos.especialidad as esp', 'esp.id_especialidad','comp.id_especialidad')
            ->leftJoin('public.users as usu', 'usu.id','comp.id_usuario_ingresa')
            ->leftJoin('inventario.persona as per', 'per.idper','usu.id_persona')
            ->leftJoin('bodega.area as a', 'a.id_area','comp.area')
            ->leftJoin('esq_catalogos.servicio as s', 's.id_servicio','comp.id_servicio')
            ->leftJoin('esq_pacientes.pacientes as per_pac', 'per_pac.id_paciente','comp.id_paciente')
          
            ->where(function($query)use($ini, $fin){
                if($fin!="f"){
                    $query->whereBetween('comp.fecha',[$ini, $fin]);
                }
                   
            })
            ->leftJoin('bodega.bodega as bod', 'bod.idbodega','comp.idbodega')
            ->select('comp.descripcion','comp.secuencial','comp.fecha_hora','comp.observacion','comp.total','comp.id_usuario_ingresa', 'comp.idcomprobante','bod.nombre as nombre_bodega','comp.idbodega',DB::raw("CONCAT(per.ape1,' ', per.ape2,' ', per.nom1,' ', per.nom2) AS solicita"),"comp.codigo_old","a.descripcion as area",DB::raw("CONCAT(per_pac.apellido1,' ', per_pac.apellido2,' ', per_pac.nombre1,' ', per_pac.nombre2) AS paciente"),'per_pac.documento','s.nombre as dependencia','comp.area as id_area','comp.id_servicio','comp.id_especialidad','esp.nombre as espec_med','comp.tipoarea')
            ->where('comp.estado','=','Activo')
            ->whereIN('comp.codigo_old',['PedidoAFarm','Revertido'])
            ->where('idtipo_comprobante',8)
            // ->where('idtipo_comprobante',1)
            ->orderBy('comp.fecha_hora','desc')
            ->get();
            foreach($pedidos as $key=> $data){
               
                if($data->tipoarea=="CE"){
                    $pedidos[$key]->area_selec="CONSULTA EXTERNA";
                    $pedidos[$key]->servicio_selec=$data->espec_med;
                }else if($data->tipoarea=="Emergencia"){
                    $pedidos[$key]->area_selec="EMERGENCIA";
                    if($data->id_servicio==9000 || $data->id_servicio==31){
                        $pedidos[$key]->servicio_selec="TRIAGE";
                    }else{
                        $pedidos[$key]->servicio_selec="AMBULATORIO";
                    }
                    
                }else{
                    $pedidos[$key]->area_selec="HOSPITALIZACION";
                    $pedidos[$key]->servicio_selec=$data->dependencia;
                }
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


    public function dispensarInsumos(){
        return view('gestion_farmacia.dispensar_insumo');
    }

    public function filtrarInsumo($ini, $fin){
        
        try{

            //comprobamos si existen insumos solicitadas
            $insumoPendiente=$this->InsumoPendienteSincronizar();
            
            $pedidos= DB::connection('pgsql')->table('bodega.comprobante as comp')
            ->leftJoin('esq_catalogos.especialidad as esp', 'esp.id_especialidad','comp.id_especialidad')
            ->leftJoin('public.users as usu', 'usu.id','comp.id_usuario_ingresa')
            ->leftJoin('inventario.persona as per', 'per.idper','usu.id_persona')
            ->leftJoin('bodega.area as a', 'a.id_area','comp.area')
            ->leftJoin('esq_catalogos.servicio as s', 's.id_servicio','comp.id_servicio')
            ->leftJoin('esq_pacientes.pacientes as per_pac', 'per_pac.id_paciente','comp.id_paciente')
          
            ->where(function($query)use($ini, $fin){
                if($fin!="f"){
                    $query->whereBetween('comp.fecha',[$ini, $fin]);
                }
                   
            })
            ->leftJoin('bodega.bodega as bod', 'bod.idbodega','comp.idbodega')
            ->select('comp.descripcion','comp.secuencial','comp.fecha_hora','comp.observacion','comp.total','comp.id_usuario_ingresa', 'comp.idcomprobante','bod.nombre as nombre_bodega','comp.idbodega',DB::raw("CONCAT(per.ape1,' ', per.ape2,' ', per.nom1,' ', per.nom2) AS solicita"),"comp.codigo_old","a.descripcion as area",DB::raw("CONCAT(per_pac.apellido1,' ', per_pac.apellido2,' ', per_pac.nombre1,' ', per_pac.nombre2) AS paciente"),'per_pac.documento','s.nombre as dependencia','comp.area as id_area','comp.id_servicio','comp.id_especialidad','esp.nombre as espec_med','comp.tipoarea')
            ->where('comp.estado','=','Activo')
            ->whereIN('comp.codigo_old',['PedidoAFarm'])
            ->whereIn('idtipo_comprobante',[18,19,20])
            // ->where('idtipo_comprobante',1)
            ->orderBy('comp.fecha_hora','desc')
            ->get();
           
            foreach($pedidos as $key=> $data){
                if(is_null($data->documento)){
                    $pedidos[$key]->area_selec="";
                    $pedidos[$key]->servicio_selec=$data->area;
                }else{
                    if($data->tipoarea=="CE"){
                        $pedidos[$key]->area_selec="CONSULTA EXTERNA";
                        $pedidos[$key]->servicio_selec=$data->espec_med;
                    }else if($data->tipoarea=="Emergencia"){
                        $pedidos[$key]->area_selec="EMERGENCIA";
                        if($data->id_servicio==9000 || $data->id_servicio==31){
                            $pedidos[$key]->servicio_selec="TRIAGE";
                        }else{
                            $pedidos[$key]->servicio_selec="AMBULATORIO";
                        }
                        
                    }else{
                        $pedidos[$key]->area_selec="HOSPITALIZACION";
                        if(!is_null($data->dependencia)){
                            $pedidos[$key]->servicio_selec=$data->dependencia;
                        }else{
                            $pedidos[$key]->servicio_selec=$data->area;
                        }
                        
                    }
                }
                    
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

    public function listaDevolver(){
        return view('gestion_bodega.devolucion_famacia');
    }

    public function filtrarDevolverBodega($ini, $fin){
        
        try{
            
            $pedidos= DB::connection('pgsql')->table('bodega.comprobante as comp')
            ->leftJoin('public.users as usu', 'usu.id','comp.id_usuario_ingresa')
            ->leftJoin('inventario.persona as per', 'per.idper','usu.id_persona')
            ->leftJoin('bodega.area as a', 'a.id_area','per.id_area')
            ->leftJoin('bodega.per_perfil_usuario as per_usu', 'per_usu.id_usuario','usu.id')
            ->leftJoin('bodega.per_perfil as perf', 'perf.id_perfil','per_usu.id_perfil')
            ->where(function($query)use($ini, $fin){
                if($fin!="f"){
                    $query->whereBetween('comp.fecha',[$ini, $fin]);
                }
                   
            })
            ->leftJoin('bodega.bodega as bod', 'bod.idbodega','comp.idbodega')
            ->select('comp.descripcion','comp.secuencial','comp.fecha_hora','comp.observacion','comp.total','comp.id_usuario_ingresa', 'comp.idcomprobante','bod.nombre as nombre_bodega','comp.idbodega',DB::raw("CONCAT(per.ape1,' ', per.ape2,' ', per.nom1,' ', per.nom2) AS solicita"), "perf.descripcion as area1","comp.codigo_old","a.descripcion as area")
            ->where('comp.estado','=','Activo')
            ->whereIN('comp.codigo_old',['DevolverBodega'])
            ->where('idtipo_comprobante',6)
            ->get();

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

    public function validarDevolucion(Request $request){
        
        $idArrayDetalle=$request->array_iddetalle;
        $arrayCantidadValida=$request->cantidad_validada;
       
                    
        if(sizeof($idArrayDetalle)==0 || sizeof($arrayCantidadValida)==0){
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
                                                                         
                    $validaPedido->cantidad_entregada=$request->cantidad_validada[$key];
                    $validaPedido->id_aprueba=auth()->user()->id;
                    $validaPedido->fecha_aprueba=date('Y-m-d H:i:s');
                    $validaPedido->estado="Entregado";
                    $validaPedido->save();

                    if($validaPedido->save()){
                       
                        $ultimo=Existencia::orderBy('idexistencia','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->idexistencia+1;
                        }
                        $existencia=new Existencia();
                        $existencia->idexistencia=$suma;
                        $existencia->idexistencia=$ultimo->idexistencia+1;
                        $existencia->iddetalle_comprobante=$iddetalle;
                        $existencia->lote=$validaPedido->lote;
                        $existencia->resta=$validaPedido->cantidad_entregada;
                        $existencia->tipo="Egreso Farmacia";
                        $existencia->cod="EF";
                        $existencia->fecha_hora=date('Y-m-d H:i:s');
                        $existencia->fecha_elaboracion=$validaPedido->fecha_elabora;
                        $existencia->fecha_caducidad=$validaPedido->fecha_caducidad;
                        $existencia->fecha=date('Y-m-d');
                        $existencia->idusuario=auth()->user()->id;
                        $existencia->idbodprod=$validaPedido->idbodpro;                        
                        $existencia->id_pedido=$validaPedido->idpedido_bod_gral;
                        $existencia->idusuario_solicita=$validaPedido->id_solicita;                        
                        $existencia->save();   

                        $item=DetalleComprobante::where('iddetalle_comprobante',$iddetalle)->first();                       
                      
                        
                        if($validaPedido->idbodega==25){ // farmacia dialisa lab reac
                            $idboddevolver=23;
                        }else if($validaPedido->idbodega==26){ // farmacia dialisa lab micro
                            $idboddevolver=24;
                        }else if($validaPedido->idbodega==22){ // farmacia dialisa lab material
                            $idboddevolver=19;
                        }else if($validaPedido->idbodega==21){ // farmacia dialisa insumo
                            $idboddevolver=18;
                        }else if($validaPedido->idbodega==20){ // farmacia dialisa medicina
                            $idboddevolver=17;
                        }else if($validaPedido->idbodega==27){ // farmacia gral lab material
                            $idboddevolver=8;
                        }else if($validaPedido->idbodega==28){ // farmacia gral lab react
                            $idboddevolver=13;
                        }else if($validaPedido->idbodega==29){ // farmacia gral lab micro
                            $idboddevolver=14;
                        }else if($validaPedido->idbodega==7){ // farmacia gral insumo
                            $idboddevolver=2;
                        }else if($validaPedido->idbodega==6){ // farmacia gral MEDICAMENTOS
                            $idboddevolver=1;
                        }

                        //actualizamos el stock en la tabla productobodega
                        $actualizaStockOld =ProductoBodega::where('idbodprod',$validaPedido->idbodpro)->first();
                       
                        $stockactPB=$actualizaStockOld->existencia;   
                        $nuevoStock=$actualizaStockOld->existencia;
                        $nuevoStock_act=$nuevoStock - $existencia->resta;
                        $actualizaStockOld->existencia=$nuevoStock_act;                        
                        $actualizaStockOld->save(); 

                        $ultimo =ProductoBodega::orderBy('idbodprod','desc')->first();  
                        if(is_null($ultimo)){
                            $sumauno=1;
                        }else{
                            $sumauno=$ultimo->idbodprod;
                        }  
                        
                                                                        
                        $ProductoBodegaOld=new ProductoBodega();
                        $ProductoBodegaOld->idbodprod=$sumauno+1;
                        $ProductoBodegaOld->idprod=$validaPedido->iditem;
                        $ProductoBodegaOld->idbodega=$idboddevolver;
                        $ProductoBodegaOld->existencia=$validaPedido->cantidad_entregada;
                        $ProductoBodegaOld->precio=number_format(($item->precio),4,'.', '');
                        $ProductoBodegaOld->precio2=0;
                        $ProductoBodegaOld->fecha=date('Y-m-d');
                        $ProductoBodegaOld->idusuario=auth()->user()->id;
                        $ProductoBodegaOld->sistema_old="ENLINEA";
                        

                        //dependiendo de la bodega seleccionamos el tipoprod
                        if($validaPedido->idbodega==1 || $validaPedido->idbodega==6 || $validaPedido->idbodega==17 || $validaPedido->idbodega==20){//bod gral medicamentos
                            $ProductoBodegaOld->tipoprod=1;
                        }else if($validaPedido->idbodega==2 || $validaPedido->idbodega==7 || $validaPedido->idbodega==18 || $validaPedido->idbodega==21){// bod gral insumos
                            $ProductoBodegaOld->tipoprod=2;
                        }else if($validaPedido->idbodega==14 || $validaPedido->idbodega==24 || $validaPedido->idbodega==26 || $validaPedido->idbodega==29){// bod lab microb
                            $ProductoBodegaOld->tipoprod=11;
                        }else if($validaPedido->idbodega==8 || $validaPedido->idbodega==19 || $validaPedido->idbodega==22 || $validaPedido->idbodega==27){// bod lab mater
                            $ProductoBodegaOld->tipoprod=5;
                        }else if($validaPedido->idbodega==13 || $validaPedido->idbodega==23 || $validaPedido->idbodega==25 || $validaPedido->idbodega==28){// bod lab react
                            $ProductoBodegaOld->tipoprod=10;
                        }
                        $ProductoBodegaOld->save(); 

                        $ultimo=Existencia::orderBy('idexistencia','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->idexistencia+1;
                        }
                        $existencia=new Existencia();
                        $existencia->idexistencia=$suma;
                        $existencia->iddetalle_comprobante=$iddetalle;
                        $existencia->lote=$validaPedido->lote;
                        $existencia->suma=$validaPedido->cantidad_entregada;
                        $existencia->tipo="Ingreso a Bodega";
                        $existencia->fecha_hora=date('Y-m-d H:i:s');
                        $existencia->fecha_elaboracion=$validaPedido->fecha_elabora;
                        $existencia->fecha_caducidad=$validaPedido->fecha_caducidad;
                        $existencia->cod="IAB";
                        $existencia->idusuario=auth()->user()->id;
                        $existencia->idbodprod=$ProductoBodegaOld->idbodprod;                        
                        $existencia->id_pedido=$validaPedido->idpedido_bod_gral;
                        //$existencia->idusuario_solicita=$validaPedido->id_solicita;      
                        $existencia->save();   

                        $ultimolote =LoteProducto::orderBy('idlote','desc')->first();
                        if(is_null($ultimolote)){
                            $sumaunolote=1;
                        }else{
                            $sumaunolote=$ultimolote->idlote;
                        }     
                        
                        $LoteProductoOld=new LoteProducto();
                        $LoteProductoOld->idlote=$sumaunolote+1;
                        $LoteProductoOld->idbodp=$ProductoBodegaOld->idbodprod;
                        $LoteProductoOld->lote=$existencia->lote;
                        $LoteProductoOld->felabora= $existencia->fecha_elaboracion;
                        $LoteProductoOld->fcaduca= $existencia->fecha_caducidad;
                       // $LoteProductoOld->regsan=$existencia->reg_sanitario;
                        $LoteProductoOld->sistema_old="ENLINEA";
                        $LoteProductoOld->save(); 
                        
                        if($item->id_bodega==1 || $item->id_bodega==6 || $item->id_bodega==17 || $item->id_bodega==20){//medicamento
                            //sumamos a la bodega gral
                            $actualizaMed=Medicamento::where('coditem',$item->id_item)
                            ->first();

                            if($item->id_bodega==6){ 
                                $stock_Actual=$actualizaMed->stock_bod;
                                $actualizaMed->stock_bod=$stock_Actual + $item->cantidad;
                            }else{
                                //dialisis
                                $stock_Actual=$actualizaMed->stock_bod_dialisis;
                                $actualizaMed->stock_bod_dialisis=$stock_Actual + $item->cantidad;
                            }

                            $actualizaMed->save();  
    
                        }else if($item->id_bodega==2 || $item->id_bodega==7 || $item->id_bodega==18 || $item->id_bodega==21){//insumo
                            $actualizaInsumo=Insumo::where('codinsumo',$item->id_item)
                            ->first();
                            if($item->id_bodega==7){
                                $stock_Actual=$actualizaInsumo->stockbod;
                                $actualizaInsumo->stockbod=$stock_Actual + $item->cantidad;
                            }else{
                                $stock_Actual=$actualizaInsumo->stock_bod_dialisis;
                                $actualizaInsumo->stock_bod_dialisis=$stock_Actual + $item->cantidad;
                            }
                            
                            $actualizaInsumo->save(); 

                        }
                        else { //laboratorio
                            
                            $actualizaLab=Laboratorio::where('id',$item->id_item)
                            // ->where('idbodega',$detalles->id_bodega)
                            ->first();
                            
                            if($item->id_bodega==19 || $item->id_bodega==23 || $item->id_bodega==24){ //lab dialisi
                                $stock_ActualBodSolicita=$actualizaLab->stock;
                                $actualizaLab->stock=$stock_ActualBodSolicita + $cantidad[$cont];
                                $esdialisi="N";
                            }else{//lab dialisisi
                                $stock_ActualBodSolicita=$actualizaLab->stock_dialisis;
                                $actualizaLab->stock_dialisis=$stock_ActualBodSolicita + $cantidad[$cont];
                                $esdialisi="S";
                            }
                            $actualizaLab->save(); 
                
                            //GUARDAMOS O ACTUALIZAMOS LA BODEGA LAB FARMACIA
                            $bodFarmacia= FarmLaboratorio::where('id_item',$item->id_item)
                            ->first();

                            if(is_null($bodFarmacia)){
                                
                                //agregamos $cantidad[$cont]
                                $ultimo=FarmLaboratorio::orderBy('idfarm_lab','desc')->first();
                                if(is_null($ultimo)){
                                    $suma=1;
                                }else{
                                    $suma=$ultimo->idfarm_lab+1;
                                }

                                $newBodFarm= new FarmLaboratorio();
                                $newBodFarm->idfarm_lab=$suma;
                                $newBodFarm->id_item=$item->id_item;
                                $newBodFarm->nombre=$actualizaLab->descri;
                                $newBodFarm->present=$actualizaLab->presen;
                                $newBodFarm->stock_farmacia=$actualizaLab->stock_farmacia;
                                $newBodFarm->stockbod=$actualizaLab->stock;
                                $newBodFarm->codinsumo=$actualizaLab->codinsumo;
                                $newBodFarm->activo='VERDADERO';
                                $newBodFarm->valor=$actualizaLab->valor;
                                $newBodFarm->tipoprod=$ProductoBodegaOld->tipoprod;
                                $newBodFarm->es_dialisis=$esdialisi;
                                $newBodFarm->idbodega=$item->id_bodega;
                                $newBodFarm->save();
                            }else{
                                $stock_Actual=$bodFarmacia->stock_farmacia;
                                //actualziamos
                                $stock_Actual=$bodFarmacia->stock_farmacia;
                                $bodFarmacia->id_item=$item->id_item;
                                $bodFarmacia->nombre=$actualizaLab->descri;
                                $bodFarmacia->present=$actualizaLab->presen;
                                $bodFarmacia->stock_farmacia=$actualizaLab->stock_farmacia;
                                $bodFarmacia->stockbod=$actualizaLab->stock;
                                $bodFarmacia->codinsumo=$actualizaLab->codinsumo;
                                $bodFarmacia->valor=$actualizaLab->valor;
                                $bodFarmacia->tipoprod=$ProductoBodegaOld->tipoprod;
                                $bodFarmacia->es_dialisis=$esdialisi;
                                $bodFarmacia->idbodega=$detalles->id_bodega;
                                $bodFarmacia->activo='VERDADERO';
                                $bodFarmacia->save();
                            }
                        } 

                        
                    }  
                }    
                $compr=Comprobante::where('idcomprobante',$item->idcomprobante)->first();
                $compr->codigo_old="Entregado";
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
    
    public function vistaPacientes(){

        return view('pacientes.listar');
    }

    public function generarReportePaciente($fecha, $area){
        try{
            if($area=="EMERGENCIA"){
                $listar=DB::connection('pgsql')->table('esq_emergencia.cama_paciente as cama')    
                ->leftJoin('esq_emergencia.cama as c', 'c.id_cama','cama.id_cama')    
                ->leftJoin('esq_emergencia.cama_asignacion as c_asi', 'c_asi.id_cama','cama.id_cama')   
                ->leftJoin('esq_catalogos.servicio as ser', 'ser.id_servicio','c_asi.id_servicio')    
                ->leftJoin('esq_pacientes.pacientes as pc', 'pc.id_paciente','cama.id_paciente')
                ->select(DB::raw("CONCAT(pc.apellido1,' ', pc.apellido2,' ', pc.nombre1,' ', pc.nombre2) AS paciente"),
                'pc.documento as cedula_paciente','c.descripcion','ser.nombre','ser.id_servicio')
                ->where('cama.estado', 'A')
                ->where('cama.id_tipo_registro',1)
                ->where('c.id_estado',2)
                ->distinct()
                ->get();

                $resultado=$listar;
            }else if($area=="TODAS"){
                $listar=DB::connection('pgsql')->table('esq_emergencia.cama_paciente as cama')    
                ->leftJoin('esq_emergencia.cama as c', 'c.id_cama','cama.id_cama')    
                ->leftJoin('esq_emergencia.cama_asignacion as c_asi', 'c_asi.id_cama','cama.id_cama')   
                ->leftJoin('esq_catalogos.servicio as ser', 'ser.id_servicio','c_asi.id_servicio')    
                ->leftJoin('esq_pacientes.pacientes as pc', 'pc.id_paciente','cama.id_paciente')
                ->select(DB::raw("CONCAT(pc.apellido1,' ', pc.apellido2,' ', pc.nombre1,' ', pc.nombre2) AS paciente"),
                'pc.documento as cedula_paciente','c.descripcion','ser.nombre','ser.id_servicio')
                ->where('cama.estado', 'A')
                ->where('cama.id_tipo_registro',1)
                ->where('c.id_estado',2)
                ->distinct();
            
                $listarHos=DB::connection('pgsql')->table('esq_hospitalizacion.cama_paciente as cama')    
                ->leftJoin('esq_hospitalizacion.cama as c', 'c.id_cama','cama.id_cama') 
                ->leftJoin('esq_hospitalizacion.cama_asignacion as c_asi', 'c_asi.id_cama','cama.id_cama')   
                ->leftJoin('esq_catalogos.servicio as ser', 'ser.id_servicio','c_asi.id_servicio')    
                ->leftJoin('esq_pacientes.pacientes as pc', 'pc.id_paciente','cama.id_paciente')
                ->select(DB::raw("CONCAT(pc.apellido1,' ', pc.apellido2,' ', pc.nombre1,' ', pc.nombre2) AS paciente"),
                'pc.documento as cedula_paciente','c.descripcion','ser.nombre','ser.id_servicio')
                ->where('cama.estado', 'A')
                ->where('cama.id_tipo_registro',1)
                ->where('c.id_estado',2)
                ->distinct();
                
                $resultado = $listar->union($listarHos)->get();

            }else {
                $listarHos=DB::connection('pgsql')->table('esq_hospitalizacion.cama_paciente as cama')    
                ->leftJoin('esq_hospitalizacion.cama as c', 'c.id_cama','cama.id_cama') 
                ->leftJoin('esq_hospitalizacion.cama_asignacion as c_asi', 'c_asi.id_cama','cama.id_cama')   
                ->leftJoin('esq_catalogos.servicio as ser', 'ser.id_servicio','c_asi.id_servicio')    
                ->leftJoin('esq_pacientes.pacientes as pc', 'pc.id_paciente','cama.id_paciente')
                ->select(DB::raw("CONCAT(pc.apellido1,' ', pc.apellido2,' ', pc.nombre1,' ', pc.nombre2) AS paciente"),
                'pc.documento as cedula_paciente','c.descripcion','ser.nombre','ser.id_servicio')
                ->where('cama.estado', 'A')
                ->where('cama.id_tipo_registro',1)
                ->where('c.id_estado',2)
                ->where(function($query)use($area){
                    if($area!="TODOS"){
                        $query->where('ser.nombre',$area);
                    }               
                })
                ->distinct()
                ->get();   
                $resultado=$listarHos;
            }
            // return (['resultado'=>$resultado,'error'=>false]);


            #agrupamos
            $listado_final=[];
            foreach ($resultado as $key => $item){                
                if(!isset($listado_final[$item->nombre])) {
                    $listado_final[$item->nombre]=array($item);
            
                }else{
                    array_push($listado_final[$item->nombre], $item);
                }
            }        
            // return (['resultado'=>$listado_final,'error'=>false]);
            
            $nombrePDF="reporte_hospitalizacion.pdf";
           
            $pdf=\PDF::loadView('reportes.reporte_paciente',['resultado'=>$listado_final]);
            $pdf->setPaper("A4", "portrait");

            $estadoarch = $pdf->stream();

            //lo guardamos en el disco temporal
            Storage::disk('public')->put(str_replace("", "",$nombrePDF), $estadoarch);
            $exists_destino = Storage::disk('public')->exists($nombrePDF); 
            if($exists_destino){ 
                return response()->json([
                    'error'=>false,
                    'pdf'=>$nombrePDF
                ]);
            }else{
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'No se pudo crear el documento'
                ]);
            }
            return (['resultado'=>$resultado,'error'=>false]);

        } catch (\Throwable $e) {
            DB::connection('pgsql')->rollback();
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return (['mensaje'=>'Ocurrió un error,intentelo más tarde','error'=>true]); 
        }
    }

}
