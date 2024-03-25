<?php

namespace App\Http\Controllers\Bodega;
use App\Http\Controllers\Controller;
use \Log;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator; 
use App\Models\Bodega\Comprobante; 
use App\Models\Bodega\ComprobanteReceta;
use App\Models\Bodega\DetallePedido;
use App\Models\Bodega\PedidoBodegaGral;
use App\Models\Bodega\TipoComprobanteOld;
use App\Models\Bodega\DetalleComprobante;
use App\Models\Bodega\ProductoBodega;
use App\Models\Bodega\BodegaUsuario;

class PedidoBodegaController extends Controller
{
    public function buscarStockItem(Request $request){
        $bodega=$request->param1;
        $data = [];
        if($request->has('q')){
            $search = $request->q;
            $text=mb_strtoupper($search);
            //medicamentos
            if($bodega==1 || $bodega==6 || $bodega==17 || $bodega==20){
                $data=DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
                ->leftJoin('bodega.medicamentos as med', 'med.coditem','proxbode.idprod')
                ->where(function($c)use($text) {
                    $c->where('med.nombre', 'ilike', '%'.$text.'%');
                })
                ->where('proxbode.idbodega',$bodega)
                ->where('proxbode.existencia','>',0)
                ->select(DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion) AS detalle"), 'proxbode.idprod', DB::raw('sum("existencia") as stock'))
                ->groupby('proxbode.idprod', 'detalle')
                ->take(50)
                ->get();
            }else if($bodega==2 || $bodega==7 || $bodega==18 || $bodega==21){
                //insumos
                $data=DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
                ->leftJoin('bodega.insumo as insu', 'insu.codinsumo','proxbode.idprod')
                ->where(function($c)use($text) {
                    $c->where('insu.insumo', 'ilike', '%'.$text.'%');
                })
                ->where('proxbode.idbodega',$bodega)
                ->where('proxbode.existencia','>',0)
                ->select(DB::raw("CONCAT(insu.insumo) AS detalle"), 'proxbode.idprod', DB::raw('sum("existencia") as stock'))
                ->groupby('proxbode.idprod', 'detalle')
                ->take(50)
                ->get();
            }else if($bodega==8 || $bodega==13 || $bodega==14 || $bodega==19 || $bodega==23 || $bodega==24 || $bodega==22 || $bodega==25 || $bodega==26 || $bodega==27 || $bodega==28 || $bodega==29){
                //laboratorio
                $data=DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
                ->leftJoin('bodega.laboratorio as lab', 'lab.id','proxbode.idprod')
                ->where(function($c)use($text) {
                    $c->where('lab.descri', 'ilike', '%'.$text.'%');
                })
                ->where('proxbode.idbodega',$bodega)
                ->where('proxbode.existencia','>',0)
                ->select(DB::raw("CONCAT(lab.descri) AS detalle"), 'proxbode.idprod', DB::raw('sum("existencia") as stock'))
                ->groupby('proxbode.idprod', 'detalle')
                ->take(50)
                ->get();
            }else if($bodega==30){
                //proteccion
                $data=DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
                ->leftJoin('bodega.proteccion as prot', 'prot.id','proxbode.idprod')
                ->where(function($c)use($text) {
                    $c->where('prot.descri', 'ilike', '%'.$text.'%');
                })
                ->where('proxbode.idbodega',$bodega)
                ->where('proxbode.existencia','>',0)
                ->select(DB::raw("CONCAT(prot.descri) AS detalle"), 'proxbode.idprod', DB::raw('sum("existencia") as stock'))
                ->groupby('proxbode.idprod', 'detalle')
                ->take(50)
                ->get();
            }else{
                //items
                $data=DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
                ->leftJoin('bodega.items as item', 'item.codi_it','proxbode.idprod')
                ->where(function($c)use($text) {
                    $c->where('item.descri', 'ilike', '%'.$text.'%');
                })
                ->where('proxbode.idbodega',$bodega)
                ->where('proxbode.existencia','>',0)
                ->select(DB::raw("CONCAT(item.descri) AS detalle"), 'proxbode.idprod', DB::raw('sum("existencia") as stock'))
                ->groupby('proxbode.idprod', 'detalle')
                ->take(50)
                ->get();

            }
        }        
        return response()->json($data);
    }

    public function validaItemSeleccionado($iditem, $bodega){
        try{
             //medicamentos
             if($bodega==1 || $bodega==6 || $bodega==17 || $bodega==20){
                $data=DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
                ->leftJoin('bodega.medicamentos as med', 'med.coditem','proxbode.idprod')
                ->where('proxbode.idbodega',$bodega)
                ->where('proxbode.existencia','>',0)
                ->where('proxbode.idprod', $iditem)
                ->select(DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion) AS detalle"), 'proxbode.idprod', DB::raw('sum("existencia") as stock'))
                ->groupby('proxbode.idprod', 'detalle')
                ->first();
            }else if($bodega==2 || $bodega==7 || $bodega==18 || $bodega==21){
                //insumos
                $data=DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
                ->leftJoin('bodega.insumo as insu', 'insu.codinsumo','proxbode.idprod')
                ->where('proxbode.idbodega',$bodega)
                ->where('proxbode.existencia','>',0)
                ->where('proxbode.idprod', $iditem)
                ->select(DB::raw("CONCAT(insu.insumo) AS detalle"), 'proxbode.idprod', DB::raw('sum("existencia") as stock'))
                ->groupby('proxbode.idprod', 'detalle')
                ->first();
            }else if($bodega==8 || $bodega==13 || $bodega==14 || $bodega==19 || $bodega==23 || $bodega==24 || $bodega==22 || $bodega==25 || $bodega==26 || $bodega==27 || $bodega==28 || $bodega==29){
                //laboratorio
                $data=DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
                ->leftJoin('bodega.laboratorio as lab', 'lab.id','proxbode.idprod')
                ->where('proxbode.idbodega',$bodega)
                ->where('proxbode.existencia','>',0)
                ->where('proxbode.idprod', $iditem)
                ->select(DB::raw("CONCAT(lab.descri) AS detalle"), 'proxbode.idprod', DB::raw('sum("existencia") as stock'))
                ->groupby('proxbode.idprod', 'detalle')
                ->first();
            }else if($bodega==30){
                //proteccion
                $data=DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
                ->leftJoin('bodega.proteccion as prot', 'prot.id','proxbode.idprod')
                ->where('proxbode.idbodega',$bodega)
                ->where('proxbode.existencia','>',0)
                ->where('proxbode.idprod', $iditem)
                ->select(DB::raw("CONCAT(prot.descri) AS detalle"), 'proxbode.idprod', DB::raw('sum("existencia") as stock'))
                ->groupby('proxbode.idprod', 'detalle')
                ->first();
            }else{
                //items
                $data=DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
                ->leftJoin('bodega.items as item', 'item.codi_it','proxbode.idprod')
                ->where('proxbode.idbodega',$bodega)
                ->where('proxbode.existencia','>',0)
                ->where('proxbode.idprod', $iditem)
                ->select(DB::raw("CONCAT(item.descri) AS detalle"), 'proxbode.idprod', DB::raw('sum("existencia") as stock'))
                ->groupby('proxbode.idprod', 'detalle')
                ->first();

            }

            return [
                'error'=>false,
                'resultado'=>$data
            ];

        }catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return [
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ];
            
        }

    }

    public function guardarPedidoBodegaDesdeFarmacia(Request $request){
        $validator = Validator::make($request->all(), [
            'cmb_bodega' => 'required',
            'motivo' => 'required',       
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario','error'=>true]);
        }

        if(sizeof($request->idmedicina_selecc)===0){
            return (['mensaje'=>'Debe agregar al menos un item','error'=>true]);
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
                $comprobante->subtotal=0;
                $comprobante->total=0;              
                $comprobante->id_usuario_ingresa=auth()->user()->id;
                $comprobante->area=auth()->user()->persona->id_area;  
                $comprobante->guarda_detalle_pedido="S";             

                if($comprobante->save()){
                    //datos detalle
                    $idmedicina_selecc=$request->idmedicina_selecc;
                    $idbodega_selecc=$request->idbodega_selecc; 
                    $cantidad=$request->cantidad;                  
                    $cont=0;
                  
                    //registramos los detalles localmente
                    while($cont < count($idmedicina_selecc)){
                        $nuevoStock=0;
                        $nuevoStock_act=0;

                        $ultimo=DetallePedido::orderBy('iddetalle_pedidos','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->iddetalle_pedidos+1;
                        }
                        $total_item=0;
                        $detalles=new DetallePedido();
                        $detalles->iddetalle_pedidos=$suma;
                        $detalles->idcomprobante=$comprobante->idcomprobante;
                        $detalles->id_item=$idmedicina_selecc[$cont];
                        $detalles->id_bodega=$idbodega_selecc[$cont];
                        $detalles->cantidad=$cantidad[$cont];
                       
                        $detalles->fecha=date('Y-m-d H:i:s');
                        $detalles->save(); 

                                     
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

    public function detallePedidoBodega($id,$idbodega){
       
        try{
           
            $anulado=Comprobante::where('idcomprobante',$id)
            ->select('codigo_old','guarda_detalle_pedido')
            ->first();

            if($anulado->codigo_old == "Entregado" || $anulado->codigo_old == "EntregadoF" || $anulado->codigo_old == "EntregadoB"){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'El pedido ya fue entregado'
                ]);
            }else{
                if($anulado->codigo_old=="Anulado"){
                    return response()->json([
                        'error'=>true,
                        'mensaje'=>'El pedido fue anulado'
                    ]);
                }
            }
            
          
            $validaParametro=DB::connection('pgsql')->table('bodega.parametro')
            ->where('estado','A')
            ->where('codigo','PermStockMas')
            ->first();
            
            if($anulado->guarda_detalle_pedido=="S"){
                $detalle=$this->procesaPedidos($id,$idbodega);
                if($detalle['error']==true){
                    return response()->json([
                        'error'=>true,
                        'mensaje'=>$detalle['mensaje']
                    ]);
                }
            }
                

            if($idbodega==1 || $idbodega==17 || $idbodega==6 ){//medicamento
                $info= DB::connection('pgsql')->table('bodega.detalle_comprobante as detcomp')
                ->leftJoin('bodega.comprobante as comp', 'comp.idcomprobante','detcomp.idcomprobante')
                ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                ->leftJoin('bodega.medicamentos as medi', 'medi.coditem','detcomp.id_item')
                ->leftJoin('bodega.prodxbod as pb', 'pb.idbodprod','pedido.idbodpro')
                ->select(DB::raw("CONCAT(medi.nombre,' - ', medi.concentra,' - ', medi.forma,' - ',medi.presentacion) AS nombre_item"),'pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','pb.existencia as stock','medi.coditem', 'detcomp.iddetalle_comprobante as iddetalle')
                ->where('detcomp.idcomprobante',$id)
                ->where('comp.estado','Activo')
                ->where('comp.codigo_old','Pedido')
                ->get();
               

            }else if($idbodega==2 || $idbodega==18 || $idbodega==7){//insumo
                
                $info= DB::connection('pgsql')->table('bodega.detalle_comprobante as detcomp')
                ->leftJoin('bodega.comprobante as comp', 'comp.idcomprobante','comp.idcomprobante')
                ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                ->leftJoin('bodega.insumo as insu', 'insu.codinsumo','detcomp.id_item')
                ->leftJoin('bodega.prodxbod as pb', 'pb.idbodprod','pedido.idbodpro')
                ->select('insu.insumo as nombre_item','pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','pb.existencia as stock','detcomp.idcomprobante','detcomp.iddetalle_comprobante as iddetalle','comp.codigo_old','insu.codinsumo as iditem')
                ->where('detcomp.idcomprobante',$id)
                ->where('comp.estado','Activo')
                ->where('comp.codigo_old','Pedido')
                ->distinct()
                ->get();
               
              
            }else if($idbodega==13 || $idbodega==23){//reactivo
                if($idbodega==13){
                   
                    $info= DB::connection('pgsql')->table('bodega.detalle_comprobante as detcomp')
                    ->leftJoin('bodega.comprobante as comp', 'comp.idcomprobante','comp.idcomprobante')
                    ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                    ->leftJoin('bodega.laboratorio as lab', 'lab.id','detcomp.id_item')
                    ->leftJoin('bodega.prodxbod as pb', 'pb.idbodprod','pedido.idbodpro')
                    ->select('lab.descri as nombre_item','pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','pb.existencia as stock','detcomp.idcomprobante','detcomp.iddetalle_comprobante as iddetalle','comp.codigo_old','lab.id as iditem')
                    ->where('detcomp.idcomprobante',$id)
                    ->where('comp.estado','Activo')
                    ->where('comp.codigo_old','Pedido')
                    ->distinct()
                    ->get();
                   
                   
                }else{
                  
                    $info= DB::connection('pgsql')->table('bodega.detalle_comprobante as detcomp')
                    ->leftJoin('bodega.comprobante as comp', 'comp.idcomprobante','comp.idcomprobante')
                    ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                    ->leftJoin('bodega.laboratorio as lab', 'lab.id','detcomp.id_item')
                    ->leftJoin('bodega.prodxbod as pb', 'pb.idbodprod','pedido.idbodpro')
                    ->select('lab.descri as nombre_item','pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','pb.existencia as stock','detcomp.idcomprobante','detcomp.iddetalle_comprobante as iddetalle','comp.codigo_old','lab.id as iditem')
                    ->where('detcomp.idcomprobante',$id)
                    ->where('comp.estado','Activo')
                    ->where('comp.codigo_old','Pedido')
                    ->distinct()
                    ->get();
                   
                }
                   

             
              
            }else if($idbodega==8 || $idbodega==19){//materiales
                $info= DB::connection('pgsql')->table('bodega.detalle_comprobante as detcomp')
                ->leftJoin('bodega.comprobante as comp', 'comp.idcomprobante','comp.idcomprobante')
                ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                ->leftJoin('bodega.laboratorio as lab', 'lab.id','detcomp.id_item')
                ->leftJoin('bodega.prodxbod as pb', 'pb.idbodprod','pedido.idbodpro')
                ->select('lab.descri as nombre_item','pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','pb.existencia as stock','detcomp.idcomprobante','detcomp.iddetalle_comprobante as iddetalle','comp.codigo_old','lab.id as iditem')
                ->where('detcomp.idcomprobante',$id)
                ->where('comp.estado','Activo')
                ->where('comp.codigo_old','Pedido')
                ->distinct()
                ->get();
            
            }else if($idbodega==14 || $idbodega==24){//micro
                $info= DB::connection('pgsql')->table('bodega.detalle_comprobante as detcomp')
                ->leftJoin('bodega.comprobante as comp', 'comp.idcomprobante','comp.idcomprobante')
                ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                ->leftJoin('bodega.laboratorio as lab', 'lab.id','detcomp.id_item')
                ->leftJoin('bodega.prodxbod as pb', 'pb.idbodprod','pedido.idbodpro')
                ->select('lab.descri as nombre_item','pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','pb.existencia as stock','detcomp.idcomprobante','detcomp.iddetalle_comprobante as iddetalle','comp.codigo_old','lab.id as iditem')
                ->where('detcomp.idcomprobante',$id)
                ->where('comp.estado','Activo')
                ->where('comp.codigo_old','Pedido')
                ->distinct()
                ->get();
            
            }else if($idbodega==30){
               
                $info= DB::connection('pgsql')->table('bodega.detalle_comprobante as detcomp')
                ->leftJoin('bodega.comprobante as comp', 'comp.idcomprobante','comp.idcomprobante')
                ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                ->leftJoin('bodega.proteccion as item', 'item.id','detcomp.id_item')
                ->select('item.descri as nombre_item','pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','item.stock as stock','detcomp.idcomprobante','detcomp.iddetalle_comprobante as iddetalle','comp.codigo_old','item.id as iditem')
                ->where('detcomp.idcomprobante',$id)
                ->where('comp.estado','Activo')
                ->where('comp.codigo_old','Pedido')
                ->distinct()
                ->get();
               


            }else{
               
                $info= DB::connection('pgsql')->table('bodega.detalle_comprobante as detcomp')
                ->leftJoin('bodega.comprobante as comp', 'comp.idcomprobante','comp.idcomprobante')
                ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')

                // ->leftJoin('bodega.prodxbod as prodxb', 'prodxb.idbodprod','pedido.idbodpro')
                ->leftJoin('bodega.items as item', 'item.codi_it','detcomp.id_item')
                ->select('item.descri as nombre_item','pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','item.stock as stock','detcomp.idcomprobante','detcomp.iddetalle_comprobante as iddetalle','comp.codigo_old','item.codi_it as iditem')
                ->where('detcomp.idcomprobante',$id)
                ->where('comp.estado','Activo')
                ->where('comp.codigo_old','Pedido')
                // ->where('comp.estado','Pedido')
                ->distinct()
                ->get();

                // dd($info);

            }   

                
            return response()->json([
                'error'=>false,
                'resultado'=>$info,
                'validaParametro'=>$validaParametro,
                'fecha_Actual'=>date('Y-m-d H:i:s')
            ]);
        }catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }

    public function detallePedidoBod($id,$idbodega){
       
        try{
            $comprobante= DB::connection('pgsql')->table('bodega.comprobante as comp')
            ->where('comp.idcomprobante',$id)
            ->select('codigo_old')
            ->first();
            $estado=$comprobante->codigo_old;
           
            if($idbodega==1 || $idbodega==17 || $idbodega==20 || $idbodega==6){//medicamento
                if($estado=="EntregadoF" || $estado=="EntregadoB" || $estado=="Entregado"){
                    $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
                    ->leftJoin('public.users as u', 'u.id','comp.id_anula')
                    ->leftJoin('inventario.persona as per', 'per.idper','u.id_persona')
                    ->leftJoin('bodega.detalle_comprobante as detcomp', 'detcomp.idcomprobante','comp.idcomprobante')
                    ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                    ->leftJoin('bodega.medicamentos as medi', 'medi.coditem','detcomp.id_item')
                    ->leftJoin('bodega.prodxbod as pb', 'pb.idbodprod','pedido.idbodpro')
                    ->select(DB::raw("CONCAT(medi.nombre,' - ', medi.concentra,' - ', medi.forma) AS nombre_item"),'pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','pedido.cantidad_entregada','pb.existencia as stock','medi.coditem', 'detcomp.iddetalle_comprobante as iddetalle',DB::raw("CONCAT(per.ci,' - ', per.ape1,' ', per.nom1) AS anulador"), 'comp.detalle_anula')
                    ->where('comp.idcomprobante',$id)
                    ->where('comp.estado','Activo')
                    ->whereIN('comp.codigo_old',['Pedido','EntregadoF','EntregadoB','PedidoAFarm','Anulado'])
                    ->distinct()
                    ->get();
                }else{
                    $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
                    ->leftJoin('public.users as u', 'u.id','comp.id_anula')
                    ->leftJoin('inventario.persona as per', 'per.idper','u.id_persona')
                    ->leftJoin('bodega.detalle_pedidos as detped', 'detped.idcomprobante','comp.idcomprobante')
                    ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detped.iddetalle_pedidos')
                    ->leftJoin('bodega.medicamentos as medi', 'medi.coditem','detped.id_item')
                    ->leftJoin('bodega.prodxbod as pb', 'pb.idbodprod','pedido.idbodpro')
                    ->select(DB::raw("CONCAT(medi.nombre,' - ', medi.concentra,' - ', medi.forma) AS nombre_item"),'pedido.lote','pedido.fecha_caducidad','detped.cantidad as cantidad_pedida','pedido.cantidad_entregada','pb.existencia as stock','medi.coditem', 'detped.iddetalle_pedidos as iddetalle',DB::raw("CONCAT(per.ci,' - ', per.ape1,' ', per.nom1) AS anulador"), 'comp.detalle_anula')
                    ->where('comp.idcomprobante',$id)
                    ->where('comp.estado','Activo')
                    ->whereIN('comp.codigo_old',['Pedido','EntregadoF','EntregadoB','PedidoAFarm','Anulado'])
                    ->distinct()
                    ->get();
                }
               
               
             
            }else if($idbodega==2 || $idbodega==7 || $idbodega==18 || $idbodega==21){//insumo
                if($estado=="EntregadoF" || $estado=="EntregadoB" || $estado=="Entregado"){
                    $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
                    ->leftJoin('public.users as u', 'u.id','comp.id_anula')
                    ->leftJoin('inventario.persona as per', 'per.idper','u.id_persona')
                    ->leftJoin('bodega.detalle_comprobante as detcomp', 'detcomp.idcomprobante','comp.idcomprobante')
                    ->leftJoin('bodega.insumo as insu', 'insu.codinsumo','detcomp.id_item')
                    ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                    ->leftJoin('bodega.prodxbod as pb', 'pb.idbodprod','pedido.idbodpro')
                    ->where('comp.idcomprobante',$id)
                    ->where('comp.estado','Activo')
                    ->whereIN('comp.codigo_old',['Pedido','EntregadoF','EntregadoB','PedidoAFarm','Anulado'])
                    ->select('insu.insumo as nombre_item','pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','pedido.cantidad_entregada','pb.existencia as stock','detcomp.iddetalle_comprobante as iddetalle','insu.codinsumo','comp.idcomprobante',DB::raw("CONCAT(per.ci,' - ', per.ape1,' ', per.nom1) AS anulador"), 'comp.detalle_anula')
                    ->get();
                }else{
                    $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
                    ->leftJoin('public.users as u', 'u.id','comp.id_anula')
                    ->leftJoin('inventario.persona as per', 'per.idper','u.id_persona')
                    ->leftJoin('bodega.detalle_pedidos as detped', 'detped.idcomprobante','comp.idcomprobante')
                    ->leftJoin('bodega.insumo as insu', 'insu.codinsumo','detped.id_item')
                    ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detped.iddetalle_pedidos')
                    ->leftJoin('bodega.prodxbod as pb', 'pb.idbodprod','pedido.idbodpro')
                    ->where('comp.idcomprobante',$id)
                    ->where('comp.estado','Activo')
                    ->whereIN('comp.codigo_old',['Pedido','EntregadoF','EntregadoB','PedidoAFarm','Anulado'])
                    ->select('insu.insumo as nombre_item','pedido.lote','pedido.fecha_caducidad','detped.cantidad as cantidad_pedida','pedido.cantidad_entregada','pb.existencia as stock','detped.iddetalle_pedidos as iddetalle','insu.codinsumo','comp.idcomprobante',DB::raw("CONCAT(per.ci,' - ', per.ape1,' ', per.nom1) AS anulador"), 'comp.detalle_anula')
                    ->get();
                }
                
              
                
            }else if($idbodega==8 || $idbodega==13 || $idbodega==14 || $idbodega==19 || $idbodega==23 || $idbodega==24){
                if($estado=="EntregadoF" || $estado=="EntregadoB" || $estado=="Entregado"){
                    //laboratorio gral
                    $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
                    ->leftJoin('public.users as u', 'u.id','comp.id_anula')
                    ->leftJoin('inventario.persona as per', 'per.idper','u.id_persona')
                    ->leftJoin('bodega.detalle_comprobante as detcomp', 'detcomp.idcomprobante','comp.idcomprobante')
                    ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                    ->leftJoin('bodega.laboratorio as item', 'item.id','detcomp.id_item')
                    ->leftJoin('bodega.prodxbod as pb', 'pb.idbodprod','pedido.idbodpro')
                    ->select('item.descri as nombre_item','pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','pedido.cantidad_entregada','pb.existencia as stock','detcomp.iddetalle_comprobante as iddetalle')
                    ->where('comp.idcomprobante',$id)
                    ->whereIN('comp.codigo_old',['Pedido','EntregadoF','EntregadoB','Anulado'])
                    ->distinct()
                    ->get();
                }else{
                    //laboratorio gral
                    $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
                    ->leftJoin('public.users as u', 'u.id','comp.id_anula')
                    ->leftJoin('inventario.persona as per', 'per.idper','u.id_persona')
                    ->leftJoin('bodega.detalle_pedidos as detped', 'detped.idcomprobante','comp.idcomprobante')
                    ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detped.iddetalle_pedidos')
                    ->leftJoin('bodega.laboratorio as item', 'item.id','detped.id_item')
                    ->leftJoin('bodega.prodxbod as pb', 'pb.idbodprod','pedido.idbodpro')
                    ->select('item.descri as nombre_item','pedido.lote','pedido.fecha_caducidad','detped.cantidad as cantidad_pedida','pedido.cantidad_entregada','pb.existencia as stock','detped.iddetalle_pedidos as iddetalle')
                    ->where('comp.idcomprobante',$id)
                    ->whereIN('comp.codigo_old',['Pedido','EntregadoF','EntregadoB','Anulado'])
                    ->distinct()
                    ->get();
                }
                
            }else if($idbodega==22 || $idbodega==25 || $idbodega==26 || $idbodega==27 || $idbodega==28 || $idbodega==29 ){
                //laboratorio dialisis
                if($estado=="EntregadoF" || $estado=="EntregadoB" || $estado=="Entregado"){
                    $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
                    ->leftJoin('public.users as u', 'u.id','comp.id_anula')
                    ->leftJoin('inventario.persona as per', 'per.idper','u.id_persona')
                    ->leftJoin('bodega.detalle_comprobante as detcomp', 'detcomp.idcomprobante','comp.idcomprobante')
                    ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                    ->leftJoin('bodega.laboratorio as lab', 'lab.id','detcomp.id_item')
                    ->leftJoin('bodega.prodxbod as pb', 'pb.idbodprod','pedido.idbodpro')
                    ->select('lab.descri as nombre_item','pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','pedido.cantidad_entregada','pb.existencia as stock','detcomp.idcomprobante','detcomp.iddetalle_comprobante as iddetalle','comp.codigo_old','lab.id as iditem',DB::raw("CONCAT(per.ci,' - ', per.ape1,' ', per.nom1) AS anulador"), 'comp.detalle_anula')
                    ->where('comp.idcomprobante',$id)
                    ->where('comp.estado','Activo')
                    // ->where('comp.codigo_old','PedidoAFarm')
                    ->whereIN('comp.codigo_old',['Pedido','EntregadoF','EntregadoB','Anulado'])
                    ->distinct()
                    ->get();
                }else{
                    //laboratorio gral
                    $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
                    ->leftJoin('public.users as u', 'u.id','comp.id_anula')
                    ->leftJoin('inventario.persona as per', 'per.idper','u.id_persona')
                    ->leftJoin('bodega.detalle_pedidos as detped', 'detped.idcomprobante','comp.idcomprobante')
                    ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detped.iddetalle_pedidos')
                    ->leftJoin('bodega.laboratorio as item', 'item.id','detped.id_item')
                    ->leftJoin('bodega.prodxbod as pb', 'pb.idbodprod','pedido.idbodpro')
                    ->select('item.descri as nombre_item','pedido.lote','pedido.fecha_caducidad','detped.cantidad as cantidad_pedida','pedido.cantidad_entregada','pb.existencia as stock','detped.iddetalle_pedidos as iddetalle')
                    ->where('comp.idcomprobante',$id)
                    ->whereIN('comp.codigo_old',['Pedido','EntregadoF','EntregadoB','Anulado','PedidoAFarm'])
                    ->distinct()
                    ->get();
                    
                }

            }else if($idbodega==30){
                if($estado=="EntregadoF" || $estado=="EntregadoB" || $estado=="Entregado"){
                    $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
                    ->leftJoin('public.users as u', 'u.id','comp.id_anula')
                    ->leftJoin('inventario.persona as per', 'per.idper','u.id_persona')
                    ->leftJoin('bodega.detalle_comprobante as detcomp', 'detcomp.idcomprobante','comp.idcomprobante')
                    ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')               
                    ->leftJoin('bodega.proteccion as item', 'item.id','detcomp.id_item')
                    ->select('item.descri as nombre_item','pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','pedido.cantidad_entregada','item.stock as stock','detcomp.iddetalle_comprobante as iddetalle',DB::raw("CONCAT(per.ci,' - ', per.ape1,' ', per.nom1) AS anulador"), 'comp.detalle_anula')
                    ->where('comp.idcomprobante',$id)
                    ->whereIN('comp.codigo_old',['Pedido','EntregadoF','EntregadoB','Anulado'])
                    ->distinct('iddetalle')
                    ->get();
                }else{
                    $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
                    ->leftJoin('public.users as u', 'u.id','comp.id_anula')
                    ->leftJoin('inventario.persona as per', 'per.idper','u.id_persona')
                    ->leftJoin('bodega.detalle_pedidos as detped', 'detped.idcomprobante','comp.idcomprobante')
                    ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detped.iddetalle_pedidos')               
                    ->leftJoin('bodega.proteccion as item', 'item.id','detped.id_item')
                    ->select('item.descri as nombre_item','pedido.lote','pedido.fecha_caducidad','detped.cantidad as cantidad_pedida','pedido.cantidad_entregada','item.stock as stock','detped.iddetalle_pedidos as iddetalle',DB::raw("CONCAT(per.ci,' - ', per.ape1,' ', per.nom1) AS anulador"), 'comp.detalle_anula')
                    ->where('comp.idcomprobante',$id)
                    ->whereIN('comp.codigo_old',['Pedido','EntregadoF','EntregadoB','Anulado'])
                    ->distinct('iddetalle')
                    ->get();
                }
              

            }else if($idbodega==31){
                //dialisis
                $info=[];
            }else{
                if($estado=="EntregadoF" || $estado=="EntregadoB"){
                    $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
                    ->leftJoin('bodega.detalle_comprobante as detcomp', 'detcomp.idcomprobante','comp.idcomprobante')
                    ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')                
                    ->leftJoin('bodega.items as item', 'item.codi_it','detcomp.id_item',DB::raw("CONCAT(per.ci,' - ', per.ape1,' ', per.nom1) AS anulador"), 'comp.detalle_anula')
                    ->select('item.descri as nombre_item','pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','pedido.cantidad_entregada','item.stock as stock','detcomp.iddetalle_comprobante as iddetalle')
                    ->where('comp.idcomprobante',$id)
                    ->whereIN('comp.codigo_old',['Pedido','EntregadoF','EntregadoB','Anulado'])
                    ->distinct()
                    ->get();
                }else{
                    $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
                    ->leftJoin('bodega.detalle_pedidos as detped', 'detped.idcomprobante','comp.idcomprobante')
                    ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detped.iddetalle_pedidos')                
                    ->leftJoin('bodega.items as item', 'item.codi_it','detped.id_item',DB::raw("CONCAT(per.ci,' - ', per.ape1,' ', per.nom1) AS anulador"), 'comp.detalle_anula')
                    ->select('item.descri as nombre_item','pedido.lote','pedido.fecha_caducidad','detped.iddetalle_pedidos as iddetalle','pedido.cantidad_entregada','detped.cantidad as cantidad_pedida','item.stock as stock','detped.iddetalle_pedidos as iddetalle')
                    ->where('comp.idcomprobante',$id)
                    ->whereIN('comp.codigo_old',['Pedido','EntregadoF','EntregadoB','Anulado'])
                    ->distinct()
                    ->get();
                }

                
            }             
                

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

    public function procesaPedidos($compr, $idbodega){

        $transaction=DB::connection('pgsql')->transaction(function() use ($compr, $idbodega){
            try{

                $total_comprobante=0;

                $compr_inv=DB::table('bodega.comprobante')
                ->select('observacion','id_usuario_ingresa','idcomprobante')
                ->where('idcomprobante',$compr)
                ->first();
                                                
                $detalleElimina=DetalleComprobante::where('idcomprobante', $compr)
                ->get();
                foreach($detalleElimina as $data){
                    $eliminaPedido=PedidoBodegaGral::where('iddetallecomprobante', $data->iddetalle_comprobante)
                    ->first();
                    $eliminaPedido->delete();
                }
                $EliminaDetalle=DetalleComprobante::where('idcomprobante', $compr)
                ->delete();

                $datosPa=DB::table('bodega.detalle_pedidos')
                ->where("idcomprobante", $compr)
                ->get(); 
                                        
                foreach($datosPa as $info){
                    $fecha_Actual=date('Y-m-d');
                    
                    $cantidad=$info->cantidad;
                    $item=$info->id_item; 
                    $fecha=$info->fecha;              

                    // $StockPB=DB::table('bodega.prodxbod as pb')
                    // ->leftJoin('bodega.existencia as e', 'e.idbodprod','pb.idbodprod')
                    // ->where('pb.idbodega',$idbodega)
                    // ->whereDate('e.fecha_caducidad','>=',$fecha_Actual)
                    // ->where('existencia','>',0)
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
                        $detalles->fecha=$fecha;
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
                        $pedido_temp->fecha_solicita=$fecha;
                        $pedido_temp->iditem=$item;
                        $pedido_temp->estado="Temporal";
                        $pedido_temp->save();  
                        
                        $total_comprobante=$total_comprobante +  $total_item;
                    }
                    else{
                       
                        foreach($StockPB as $data){

                            if($cantidad_item>0){
                            
                                if($cantidad_item <= $data->existencia){//

                                    if($data->existencia>0){//
                                        $Prodbod =ProductoBodega::with('lote')->where('idbodprod',$data->idbodprod)
                                        ->where('idprod',$item)
                                        ->first();   
                                        // dd($Prodbod); 
                                                                 
                                    
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
                                        $detalles->fecha=$fecha;
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
                                        $pedido_temp->fecha_solicita=$fecha;
                                        $pedido_temp->idbodpro=$Prodbod->idbodprod;
                                        $pedido_temp->iditem=$item;
                                        $pedido_temp->estado="Solicitado";
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
                                    $detalles->fecha=$fecha;
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

                                    $pedido_temp->cantidad_pedida=$nuevoStock;
                                    $pedido_temp->idbodega=$detalles->id_bodega;
                                    $pedido_temp->id_solicita=$compr_inv->id_usuario_ingresa;
                                    $pedido_temp->fecha_solicita=$fecha;
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


    public function editarPedido($id, $bodega){
        try{
           
            $entregado="Entregado";
            if($bodega==20){
                //receta dialisis
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'No se puede actualizar una receta'
                ]);
            }
            if($bodega==1 || $bodega==17 || $bodega==6){
                $comprobante=Comprobante::with('detalle_pedido','entregado','responsable')->where('idcomprobante',$id)
                ->first();
                $entregado="EntregadoF";
            }else if($bodega==2  || $bodega==7 || $bodega==18 || $bodega==21){ 
                $comprobante=Comprobante::with('detalle_pedido','entregado','responsable','paciente')->where('idcomprobante',$id)
                ->first();
                // dd($comprobante);
                $entregado="EntregadoF";
            }else if($bodega==8 || $bodega==13 || $bodega==14 || $bodega==19 || $bodega==23 || $bodega==24) { //laboratorios bodega
                $comprobante=Comprobante::with('detalle_pedido','entregado','recibido','bodega','proveedor','tipoIngreso','devolucion')->where('idcomprobante',$id)
                ->first();
                $entregado="EntregadoF";
            }else if($bodega==22 || $bodega==25 || $bodega==26 || $bodega==27 || $bodega==28 || $bodega==29) { //laboratorios farmacia
                $comprobante=Comprobante::with('detalle_pedido','entregado','recibido','bodega','proveedor','tipoIngreso','devolucion')->where('idcomprobante',$id)
                ->first();
                $entregado="EntregadoF";
            }else if($bodega==30){
                $comprobante=Comprobante::with('detalle_pedido','entregado','responsable','bodega','nomarea')->where('idcomprobante',$id)
                ->first();
            }else{
                $comprobante=Comprobante::with('detalle_pedido','entregado','responsable','bodega','nomarea')->where('idcomprobante',$id)
                ->first();
            }
          
            if($comprobante->codigo_old == "EntregadoF" || $comprobante->codigo_old == "Entregado" || $comprobante->codigo_old == "EntregadoB"){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'El pedido ya fue validado y no se puede actualizar'
                ]);
            }

            if($comprobante->codigo_old == "Anulado"){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'El pedido fue anulado y no se puede actualizar'
                ]);
            }

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
    
            return response()->json([
                'error'=>false,
                'resultado'=>$comprobante,
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

    public function actualizaPedidoBodega(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'bodega_seleccionda' => 'required',
            'motivo' => 'required',       
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario','error'=>true]);
        }

        if(sizeof($request->idmedicina_selecc)===0){
            return (['mensaje'=>'Debe agregar al menos un item','error'=>true]);
        }
        $transaction=DB::connection('pgsql')->transaction(function() use ($request, $id){
            try{
               
                $comprobante=Comprobante::with('detalle_pedido')->where('idcomprobante',$id)->first();
               
                $bodega=$comprobante->idbodega; 
                if($comprobante->codigo_old == "Anulado"){
                    return (['mensaje'=>'El pedido fue anulado y no se puede actualizar','error'=>true, 'ent'=>'S']);
                }           

                if($comprobante->codigo_old == "PedidoAFarm" || $comprobante->codigo_old == "Pedido"){
                    
                }else{
                    return (['mensaje'=>'El pedido ya fue entregado y no se puede actualizar','error'=>true, 'ent'=>'S']);
                }
                        
                //eliminamos los detalles anteriores
                $eliminaDetalle=DetallePedido::where('idcomprobante',$id)->delete();

                $comprobante->fecha_hora_actualiza=date('Y-m-d H:i:s');
                $comprobante->idbodega=$request->bodega_seleccionda;
                $comprobante->observacion=$request->motivo;        
                $comprobante->iduser_actualiza=auth()->user()->id;
                $comprobante->guarda_detalle_pedido="S";
               
                if($comprobante->save()){             

                    //datos detalle
                    $idmedicina_selecc=$request->idmedicina_selecc;
                    $idbodega_selecc=$request->idbodega_selecc; 
                    $cantidad=$request->cantidad;                  
                    $cont=0;
                  
                    //registramos los detalles localmente
                    while($cont < count($idmedicina_selecc)){
                        $nuevoStock=0;
                        $nuevoStock_act=0;

                        $ultimo=DetallePedido::orderBy('iddetalle_pedidos','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->iddetalle_pedidos+1;
                        }
                        $total_item=0;
                        $detalles=new DetallePedido();
                        $detalles->iddetalle_pedidos=$suma;
                        $detalles->idcomprobante=$comprobante->idcomprobante;
                        $detalles->id_item=$idmedicina_selecc[$cont];
                        $detalles->id_bodega=$idbodega_selecc[$cont];
                        $detalles->cantidad=$cantidad[$cont];                       
                        $detalles->fecha=date('Y-m-d H:i:s');
                        $detalles->save(); 

                                     
                        $cont=$cont+1;
                    } 
                    
                    return (['mensaje'=>'Informacion actualizada exitosamente','error'=>false]);
                }   

            } catch (\Throwable $e) {
                DB::connection('pgsql')->rollback();
                Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
                return (['mensaje'=>'Ocurrió un error,intentelo más tarde','error'=>true]); 
            }
        });
        return ($transaction);
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
               $comprobante->subtotal=0;
               $comprobante->total=0;              
               $comprobante->id_usuario_ingresa=auth()->user()->id;
               $comprobante->area=auth()->user()->persona->id_area;   
               $comprobante->guarda_detalle_pedido="S";            

               if($comprobante->save()){
                   //datos detalle
                   $idmedicina_selecc=$request->idmedicina_selecc;
                   $idbodega_selecc=$request->idbodega_selecc; 
                   $cantidad=$request->cantidad;                  
                   $cont=0;
                 
                   //registramos los detalles localmente
                   while($cont < count($idmedicina_selecc)){
                       $nuevoStock=0;
                       $nuevoStock_act=0;

                       $ultimo=DetallePedido::orderBy('iddetalle_pedidos','desc')->first();
                       if(is_null($ultimo)){
                           $suma=1;
                       }else{
                           $suma=$ultimo->iddetalle_pedidos+1;
                       }
                       $total_item=0;
                       $detalles=new DetallePedido();
                       $detalles->iddetalle_pedidos=$suma;
                       $detalles->idcomprobante=$comprobante->idcomprobante;
                       $detalles->id_item=$idmedicina_selecc[$cont];
                       $detalles->id_bodega=$idbodega_selecc[$cont];
                       $detalles->cantidad=$cantidad[$cont];
                      
                       $detalles->fecha=date('Y-m-d H:i:s');
                       $detalles->save(); 

                                    
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

    public function guardarPedidoInsumo(Request $request){
        
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
               $comprobante->subtotal=0;
               $comprobante->total=0;              
               $comprobante->id_usuario_ingresa=auth()->user()->id;
               $comprobante->area=auth()->user()->persona->id_area; 
               $comprobante->guarda_detalle_pedido="S";              

               if($comprobante->save()){
                   //datos detalle
                   $idmedicina_selecc=$request->idmedicina_selecc;
                   $idbodega_selecc=$request->idbodega_selecc; 
                   $cantidad=$request->cantidad;                  
                   $cont=0;
                 
                   //registramos los detalles localmente
                   while($cont < count($idmedicina_selecc)){
                       $nuevoStock=0;
                       $nuevoStock_act=0;

                       $ultimo=DetallePedido::orderBy('iddetalle_pedidos','desc')->first();
                       if(is_null($ultimo)){
                           $suma=1;
                       }else{
                           $suma=$ultimo->iddetalle_pedidos+1;
                       }
                       $total_item=0;
                       $detalles=new DetallePedido();
                       $detalles->iddetalle_pedidos=$suma;
                       $detalles->idcomprobante=$comprobante->idcomprobante;
                       $detalles->id_item=$idmedicina_selecc[$cont];
                       $detalles->id_bodega=$idbodega_selecc[$cont];
                       $detalles->cantidad=$cantidad[$cont];
                      
                       $detalles->fecha=date('Y-m-d H:i:s');
                       $detalles->save(); 

                                    
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
               $comprobante->subtotal=0;
               $comprobante->total=0;              
               $comprobante->id_usuario_ingresa=auth()->user()->id;
               $comprobante->area=auth()->user()->persona->id_area;  
               $comprobante->guarda_detalle_pedido="S";             

               if($comprobante->save()){
                   //datos detalle
                   $idmedicina_selecc=$request->idmedicina_selecc;
                   $idbodega_selecc=$request->idbodega_selecc; 
                   $cantidad=$request->cantidad;                  
                   $cont=0;
                 
                   //registramos los detalles localmente
                   while($cont < count($idmedicina_selecc)){
                       $nuevoStock=0;
                       $nuevoStock_act=0;

                       $ultimo=DetallePedido::orderBy('iddetalle_pedidos','desc')->first();
                       if(is_null($ultimo)){
                           $suma=1;
                       }else{
                           $suma=$ultimo->iddetalle_pedidos+1;
                       }
                       $total_item=0;
                       $detalles=new DetallePedido();
                       $detalles->iddetalle_pedidos=$suma;
                       $detalles->idcomprobante=$comprobante->idcomprobante;
                       $detalles->id_item=$idmedicina_selecc[$cont];
                       $detalles->id_bodega=$idbodega_selecc[$cont];
                       $detalles->cantidad=$cantidad[$cont];
                      
                       $detalles->fecha=date('Y-m-d H:i:s');
                       $detalles->save(); 

                                    
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
}