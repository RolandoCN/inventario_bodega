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
use App\Models\Bodega\LoteProducto;
use App\Models\Bodega\ProductoBodega;
use App\Models\Bodega\Insumo;
use App\Models\Bodega\Item;
use App\Models\Bodega\Laboratorio;
use Storage;
use SplFileInfo;
use DateTime;


class InventarioController extends Controller
{
    
    public function inventarioVista(){

        $bodega= DB::connection('pgsql')->table('bodega.bodega')
        ->where('idtipobod',1)
        ->where('estado',1)
        ->get();

        return view('gestion_bodega.inventario',[
            "bodega"=>$bodega
        ]);
    }
 
    public function detalleMovimiento($id, $fini, $ffin){
        try{
          
            $medicamentos= DB::connection('pgsql')->table('bodega.existencia as ex')
            ->leftJoin('users as u', 'u.id','ex.idusuario')

            ->leftJoin('inventario.persona as per', 'per.idper','u.id_persona')
            ->where('ex.idbodprod',$id)
            ->select(DB::raw("CONCAT(per.ape1,' ', per.ape2,' ', per.nom1,' ', per.nom2) AS responsable")
            , 'ex.suma','ex.resta', 'ex.fecha_hora','ex.id_pedido','ex.id_pedido','ex.tipo','ex.cod')
            ->where(function($c) {
                $c->where('ex.resta', '>',0)
                ->orwhere('ex.suma','>',0);
            })
            ->where(function($q) use($fini, $ffin) {
                $q->whereDate('ex.fecha_hora', '>=', $fini)
                ->whereDate('ex.fecha_hora', '<=', $ffin);
            })
            ->get();
            
            // foreach($medicamentos as $act){
            //     if($act->tipo="Egreso Bodega desde DIALISIS"){
            //         $actualizaPed=Existencia::where('id_pedido', $act->id_pedido)->first();
            //         $actualizaPed->idusuario_solicita=58;
            //         $actualizaPed->cod="EABFA";
            //         // EABFA
            //         $actualizaPed->save();
            //     }
            // }
            // dd($medicamentos);
         
            return response()->json([
                'error'=>false,
                'resultado'=>$medicamentos
            ]);
            
        }catch (\Throwable $e) {
            Log::error('InventarioController => detalleMovimiento => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function reporteItemEgreso($id, $fini, $ffin, $bodega){
        try{
            $bodega_selecc=DB::connection('pgsql')->table('bodega.bodega')
            ->where('idbodega',$bodega)
            ->select('nombre','idtipobod')
            ->first();
            $tipo=$bodega_selecc->idtipobod;

            $prodBod=DB::connection('pgsql')->table('bodega.prodxbod')
            ->where('idprod',$id)
            ->where('idbodega',$bodega)
            ->get();
            
            $idProdBodArray=[];

            foreach($prodBod as $data){
                array_push($idProdBodArray, $data->idbodprod);
            }
         
            $medicamentos= DB::connection('pgsql')->table('bodega.existencia as ex')
            ->Join('users as u', 'u.id','ex.idusuario_solicita')
            ->leftJoin('inventario.persona as per', 'per.idper','u.id_persona')
            ->leftJoin('bodega.area as a', 'a.id_area','per.id_area')
            ->whereIn('ex.idbodprod',$idProdBodArray)
            ->select(DB::raw("CONCAT(per.ape1,' ', per.ape2,' ', per.nom1,' ', per.nom2) AS responsable")
            , 'ex.suma','ex.resta', 'ex.fecha_hora','ex.id_pedido','a.descripcion','ex.cod')
            ->where(function($c) {
                $c->where('ex.resta', '>',0);
                // ->orwhere('ex.suma','>',0);
            })
            ->where(function($q) use($fini, $ffin) {
                $q->whereDate('ex.fecha_hora', '>=', $fini)
                ->whereDate('ex.fecha_hora', '<=', $ffin);
            })
            ->where(function($q1) use($tipo) {
                if($tipo==2){
                    $q1->where('ex.cod','EABFA');
                }else {
                    
                    $q1->where('ex.cod','EABA');
                }
            })

            ->get();
           
            if(sizeof($medicamentos)==0){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'No existen descargos realizados'
                ]);
            }

            ##agrupamos por area
            $lista_final_agrupada=[];
            foreach ($medicamentos as $key => $item){                
                if(!isset($lista_final_agrupada[$item->descripcion])) {
                    $lista_final_agrupada[$item->descripcion]=array($item);
            
                }else{
                    array_push($lista_final_agrupada[$item->descripcion], $item);
                }
            }
       
            if($bodega==3 || $bodega==4 || $bodega==5 || $bodega==9 || $bodega==10 ){
                $item=DB::connection('pgsql')->table('bodega.items')
                ->where('codi_it',$id)
                ->first();
                $nombre_item=$item->descri;
            }else if($bodega==2 || $bodega==7 || $bodega==18 || $bodega==21){
                $item=DB::connection('pgsql')->table('bodega.insumo')
                ->where('codinsumo',$id)
                ->first();
                
                $nombre_item=$item->insumo;
            }else if($bodega==8 || $bodega==13 || $bodega==14 || $bodega==16 || $bodega==19 || $bodega==22  || $bodega==23  || $bodega==24  || $bodega==25  || $bodega==26  || $bodega==27  || $bodega==28  || $bodega==29){
                $item=DB::connection('pgsql')->table('bodega.laboratorio')
                ->where('id',$id)
                ->first();
                
                $nombre_item=$item->descri;
            }else if($bodega==30){
                $item=DB::connection('pgsql')->table('bodega.proteccion')
                ->where('id',$id)
                ->first();
                               
                $nombre_item=$item->descri;
            }else if($bodega==1 || $bodega==6 || $bodega==17 || $bodega==20 ){
                $item=DB::connection('pgsql')->table('bodega.medicamentos')
                ->where('coditem',$id)
                ->first();
                               
                $nombre_item=$item->nombre." ".$item->concentra." ".$item->forma." ".$item->presentacion;
            }


            $nombrePDF="InventarioEgresoItem.pdf";
            $pdf=\PDF::loadView('reportes.inventario_bodega_egreso_item',['listar'=>$lista_final_agrupada,'fini'=>$fini,'ffin'=>$ffin, 'item'=>$nombre_item,'bodega_selecc'=>$bodega_selecc]);

           
            $pdf->setPaper("A4", "portrait");

            // return $pdf->download($comprobante->descripcion.".pdf");
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
            Log::error('InventarioController => reporteItemEgreso => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }

    }

    public function detalleEgreso($id){

        try{          
           
            $medicamentos= DB::connection('pgsql')->table('bodega.pedido_bod_gral as pe')
            ->Join('users as u', 'u.id','pe.id_solicita')
            ->leftJoin('inventario.persona as per', 'per.idper','u.id_persona')
            ->leftJoin('bodega.area as a', 'a.id_area','per.id_area')
            ->where('pe.idpedido_bod_gral',$id)
        
            ->select(DB::raw("CONCAT(per.ape1,' ', per.ape2,' ', per.nom1,' ', per.nom2) AS solicitante")
            , 'pe.cantidad_entregada', 'pe.fecha_aprueba as fecha_hora','a.descripcion as area_nombre')
        
            ->get();
          
            return response()->json([
                'error'=>false,
                'resultado'=>$medicamentos
            ]);
            
        }catch (\Throwable $e) {
            Log::error('InventarioController => detalleEgreso => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function kardexItemBodega($id, $fini, $ffin, $bodega){

        try{  
            
            $kardex=DB::table('bodega.detalle_comprobante as dc')
            ->leftJoin('bodega.comprobante as c','c.idcomprobante','dc.idcomprobante')
            ->leftJoin('bodega.existencia as ex','ex.iddetalle_comprobante','dc.iddetalle_comprobante')
            ->leftJoin('users as u', 'u.id','c.id_usuario_ingresa')
            ->leftJoin('inventario.persona as per', 'per.idper','u.id_persona')
            ->leftJoin('bodega.tipo_comprobante_old as tc','tc.idtipocom','c.idtipo_comprobante')
            ->where(function($c) {
                $c->where('ex.resta', '>',0)
                ->orwhere('ex.suma','>',0);
            })
            ->where(function($q) use($fini, $ffin) {
                $q->whereDate('ex.fecha_hora', '>=', $fini)
                ->whereDate('ex.fecha_hora', '<=', $ffin);
            })

            ->where('dc.id_item',$id)
            ->whereIn('ex.cod',['IAB','EAB','EABA'])
            // ->where('c.idbodega',$bodega)
            ->select(DB::raw("CONCAT(per.ape1,' ', per.ape2,' ', per.nom1,' ', per.nom2) AS responsable")
            , 'ex.suma','ex.resta', 'ex.fecha_hora','ex.id_pedido','ex.id_pedido','ex.tipo','tc.ntipo','dc.precio','c.descripcion as decri_comp','c.secuencial','ex.cod','c.idbodega')
            ->orderby('ex.fecha_hora','asc')
            ->get();
            // dd($kardex);
                     
            return [
                'error'=>false,
                'resultado'=>$kardex
            ];
            
        }catch (\Throwable $e) {
            Log::error('InventarioController => kardexItemBodega => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return[
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ];
            
        }
    }

    public function kardexItemLoteBodega($id, $fini, $ffin, $bodega, $lote,$idbodprod){

        try{  
            
            $kardex=DB::table('bodega.detalle_comprobante as dc')
            ->leftJoin('bodega.comprobante as c','c.idcomprobante','dc.idcomprobante')
            ->leftJoin('bodega.existencia as ex','ex.iddetalle_comprobante','dc.iddetalle_comprobante')
            ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','ex.idbodprod')
            ->leftJoin('users as u', 'u.id','c.id_usuario_ingresa')
            ->leftJoin('inventario.persona as per', 'per.idper','u.id_persona')
            ->leftJoin('bodega.tipo_comprobante_old as tc','tc.idtipocom','c.idtipo_comprobante')
            ->where(function($c) {
                $c->where('ex.resta', '>',0)
                ->orwhere('ex.suma','>',0);
            })
            ->where(function($q) use($fini, $ffin) {
                $q->whereDate('ex.fecha_hora', '>=', $fini)
                ->whereDate('ex.fecha_hora', '<=', $ffin);
            })
            ->where('dc.id_item',$id)
            ->where('lot.lote',$lote)
            ->whereIn('ex.cod',['IAB','EAB','EABA'])
            ->select(DB::raw("CONCAT(per.ape1,' ', per.ape2,' ', per.nom1,' ', per.nom2) AS responsable")
            , 'ex.suma','ex.resta', 'ex.fecha_hora','ex.id_pedido','ex.id_pedido','ex.tipo','tc.ntipo','dc.precio','c.descripcion as decri_comp','c.secuencial','ex.cod','c.idbodega')
            ->orderby('ex.fecha_hora','asc')
            ->get();
                                
            return [
                'error'=>false,
                'resultado'=>$kardex
            ];
            
        }catch (\Throwable $e) {
            Log::error('InventarioController => kardexItemBodega => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return[
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ];
            
        }
    }


    public function kardexItemFarmacia($id, $fini, $ffin, $bodega){

        try{  
            
            $kardex=DB::table('bodega.detalle_comprobante as dc')
            ->leftJoin('bodega.comprobante as c','c.idcomprobante','dc.idcomprobante')
            ->leftJoin('bodega.existencia as ex','ex.iddetalle_comprobante','dc.iddetalle_comprobante')
            ->leftJoin('users as u', 'u.id','c.id_usuario_ingresa')
            ->leftJoin('inventario.persona as per', 'per.idper','u.id_persona')
            ->leftJoin('bodega.tipo_comprobante_old as tc','tc.idtipocom','c.idtipo_comprobante')
            ->where(function($c) {
                $c->where('ex.resta', '>',0)
                ->orwhere('ex.suma','>',0);
            })
            ->where(function($q) use($fini, $ffin) {
                $q->whereDate('ex.fecha_hora', '>=', $fini)
                ->whereDate('ex.fecha_hora', '<=', $ffin);
            })
            ->where(function($r) use($bodega) {
                if($bodega==20){//farmacia medicamento dialisis
                    $r->whereIn('c.idbodega', [17,20,31]);
                }else if($bodega==6){//farmacia medicamento gral
                    $r->whereIn('c.idbodega', [1,6]);
                }else if($bodega==21){//farmacia insumo dialisis
                    $r->whereIn('c.idbodega', [18,21,31]);
                    // dd("ss");
                    // $r->whereIn('c.idbodega', [18,21]);
                }else if($bodega==7){//farmacia insumo dialisis
                    $r->whereIn('c.idbodega', [2,7]);
                }else if($bodega==27){//farmacia lab materiales grsl
                    $r->whereIn('c.idbodega', [8,27]);
                }else if($bodega==22){//farmacia lab materiales dialisis
                    $r->whereIn('c.idbodega', [19,22]);
                }else if($bodega==28){//farmacia lab reactivos grsl
                    $r->whereIn('c.idbodega', [13,28]);
                }else if($bodega==25){//farmacia lab materiales dialisis
                    $r->whereIn('c.idbodega', [23,25]);
                }else if($bodega==29){//farmacia lab microbiologia grsl
                    $r->whereIn('c.idbodega', [14,29]);
                }else if($bodega==26){//farmacia lab microbiologia dialisis
                    $r->whereIn('c.idbodega', [24,26]);
                }
                   
            })

            ->where('dc.id_item',$id)
            ->whereIn('ex.cod',['IAFB','IABDF','EABFA','EF','EABF'])
            // ->where('c.idbodega',$bodega)
            ->select(DB::raw("CONCAT(per.ape1,' ', per.ape2,' ', per.nom1,' ', per.nom2) AS responsable")
            , 'ex.suma','ex.resta', 'ex.fecha_hora','ex.id_pedido','ex.id_pedido','ex.tipo','ex.cod','tc.ntipo','dc.precio','c.descripcion as decri_comp','c.secuencial','ex.cod','c.idbodega')
            ->orderby('ex.fecha_hora','asc')
            ->get();
                             
            return [
                'error'=>false,
                'resultado'=>$kardex
            ];
            
        }catch (\Throwable $e) {
            Log::error('InventarioController => detalleEgreso => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return[
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ];
            
        }
    }

    public function kardexItemLoteFarmacia($id, $fini, $ffin, $bodega, $lote,$idbodprod){

        try{  
            
            $kardex=DB::table('bodega.detalle_comprobante as dc')
            ->leftJoin('bodega.comprobante as c','c.idcomprobante','dc.idcomprobante')
            ->leftJoin('bodega.existencia as ex','ex.iddetalle_comprobante','dc.iddetalle_comprobante')
            ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','ex.idbodprod')
            ->leftJoin('users as u', 'u.id','c.id_usuario_ingresa')
            ->leftJoin('inventario.persona as per', 'per.idper','u.id_persona')
            ->leftJoin('bodega.tipo_comprobante_old as tc','tc.idtipocom','c.idtipo_comprobante')
            ->where(function($c) {
                $c->where('ex.resta', '>',0)
                ->orwhere('ex.suma','>',0);
            })
            ->where(function($q) use($fini, $ffin) {
                $q->whereDate('ex.fecha_hora', '>=', $fini)
                ->whereDate('ex.fecha_hora', '<=', $ffin);
            })
            ->where(function($r) use($bodega) {
                if($bodega==20){//farmacia medicamento dialisis
                    $r->whereIn('c.idbodega', [17,20,31]);
                }else if($bodega==6){//farmacia medicamento gral
                    $r->whereIn('c.idbodega', [1,6]);
                }else if($bodega==21){//farmacia insumo dialisis
                    $r->whereIn('c.idbodega', [18,21,31]);
                    // dd("ss");
                    // $r->whereIn('c.idbodega', [18,21]);
                }else if($bodega==7){//farmacia insumo dialisis
                    $r->whereIn('c.idbodega', [2,7]);
                }else if($bodega==27){//farmacia lab materiales grsl
                    $r->whereIn('c.idbodega', [8,27]);
                }else if($bodega==22){//farmacia lab materiales dialisis
                    $r->whereIn('c.idbodega', [19,22]);
                }else if($bodega==28){//farmacia lab reactivos grsl
                    $r->whereIn('c.idbodega', [13,28]);
                }else if($bodega==25){//farmacia lab materiales dialisis
                    $r->whereIn('c.idbodega', [23,25]);
                }else if($bodega==29){//farmacia lab microbiologia grsl
                    $r->whereIn('c.idbodega', [14,29]);
                }else if($bodega==26){//farmacia lab microbiologia dialisis
                    $r->whereIn('c.idbodega', [24,26]);
                }
                   
            })
            ->where('dc.id_item',$id)
            // ->where('dc.idbodprod',$idbodprod)
            ->where('lot.lote',$lote)
            ->whereIn('ex.cod',['IAFB','IABDF','EABFA','EF','EABF'])
            // ->where('c.idbodega',$bodega)
            ->select(DB::raw("CONCAT(per.ape1,' ', per.ape2,' ', per.nom1,' ', per.nom2) AS responsable")
            , 'ex.suma','ex.resta', 'ex.fecha_hora','ex.id_pedido','ex.id_pedido','ex.tipo','ex.cod','tc.ntipo','dc.precio','c.descripcion as decri_comp','c.secuencial','ex.cod','c.idbodega','lot.lote')
            ->orderby('ex.fecha_hora','asc')
            ->get();
            return [
                'error'=>false,
                'resultado'=>$kardex
            ];
            
        }catch (\Throwable $e) {
            Log::error('InventarioController => kardexItemLoteFarmacia => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return[
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ];
            
        }
    }

    public function buscarDetalleItemBod($idbodega, $lugar, $item){
        try{
            
            if($lugar=="FARMACIA"){
                if($idbodega==6 || $idbodega==20){
                    $medicamentos= DB::connection('pgsql')->table('bodega.medicamentos as med')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','med.coditem')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',2) // BODEGA 2
                    // ->where('proxbode.tipoprod',1) //MEDICAMENTOS
                    ->where('proxbode.idprod',$item)
                    ->where('bodega.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion,' [', med.coditem,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','med.cum as codigo_item','bodega.idbodega','bodega.idtipobod','proxbode.tipoprod','med.id_medicamento')
                    ->get();
                  
                   
                }else if($idbodega==7 || $idbodega==21){
                    $medicamentos= DB::connection('pgsql')->table('bodega.insumo as insu')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','insu.codinsumo')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',2) // BODEGA 1
                    // ->where('proxbode.tipoprod',2) //INSUMPS
                    ->where('proxbode.idprod',$item)
                    ->where('bodega.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(insu.insumo,' [', insu.codinsumo,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','bodega.idbodega','proxbode.idbodprod','insu.cudim as codigo_item') 
                    ->get();
                }else{
                    $medicamentos= DB::connection('pgsql')->table('bodega.laboratorio as lab')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','lab.id')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',2) // BODEGA 1
                    // ->where('proxbode.tipoprod',2) //INSUMPS
                    ->where('proxbode.idprod',$item)
                    // ->where('proxbode.idbodega',$idbodega)
                    ->where('bodega.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(lab.descri,' ',lab.presen,' [', lab.id,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','bodega.idbodega','proxbode.idbodprod','lab.id as codigo_item') 
                    
                    ->get();

                  
                }
            }
           
            if(($idbodega==1 || $idbodega==17) && $lugar=="BODEGA"){
              
                $medicamentos= DB::connection('pgsql')->table('bodega.medicamentos as med')
                ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','med.coditem')
                ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                ->where('bodega.idtipobod',1) // BODEGA 2
                ->where('proxbode.tipoprod',1) //MEDICAMENTOS
                ->where('proxbode.idprod',$item)
                ->where('bodega.idbodega',$idbodega)
                ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion,' [', med.coditem,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','med.cum as codigo_item','bodega.idbodega')
                ->get();

              

            }elseif(($idbodega==2 || $idbodega==18) && $lugar=="BODEGA"){
               
                $medicamentos= DB::connection('pgsql')->table('bodega.insumo as insu')
                ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','insu.codinsumo')
                ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                ->where('bodega.idtipobod',1) // BODEGA 1
                ->where('proxbode.tipoprod',2) //INSUMPS
                ->where('proxbode.idprod',$item)
                ->where('bodega.idbodega',$idbodega)
                ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(insu.insumo,' [', insu.codinsumo,']',' [', proxbode.idbodprod,']') AS detalle"), 'insu.insumo AS detalle1','proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','insu.cudim as codigo_item','bodega.idbodega') 
                ->get();
               

            }elseif($idbodega==19 && $lugar=="BODEGA"){
               
               
                $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
                ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                ->where('bodega.idtipobod',1) // BODEGA 1
                ->where('bodega.idbodega',$idbodega)
                ->where('proxbode.idprod',$item)
                //->where('proxbode.tipoprod',99) //bodega lab insumos
                ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codigo as codigo_item','proxbode.tipoprod')
                ->get();
               

            }elseif($idbodega==8 && $lugar=="BODEGA"){
               
                $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
                ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                ->where('bodega.idtipobod',1) // BODEGA 1
                ->where('proxbode.tipoprod',5) //bodega materiales
                ->where('proxbode.idprod',$item)
                ->where('bodega.idbodega',$idbodega)
                ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codigo as codigo_item')
                ->get();


            }elseif($idbodega==13 && $lugar=="BODEGA"){
         
                $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
                ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                ->where('bodega.idtipobod',1) // BODEGA 1
                ->where('proxbode.tipoprod',10) //bodega react
                ->where('proxbode.idprod',$item)
                ->where('bodega.idbodega',$idbodega)
                ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codigo as codigo_item')
                ->get();


            }elseif($idbodega==14 && $lugar=="BODEGA"){
                $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
                ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                ->where('bodega.idtipobod',1) // BODEGA 1
                ->where('proxbode.tipoprod',11) //bodega react
                ->where('proxbode.idprod',$item)
                ->where('bodega.idbodega',$idbodega)
                ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codigo as codigo_item')
                ->get();

            }elseif($idbodega==9 && $lugar=="BODEGA"){//tics
               

                $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
                ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                ->where('bodega.idtipobod',1) // BODEGA 1
                ->where('proxbode.tipoprod',7) //ticst
                ->where('proxbode.idprod',$item)
                ->where('bodega.idbodega',$idbodega)
                ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codi_it as codigo_item')
                ->get();
             
                
            }elseif($idbodega==3 && $lugar=="BODEGA"){
               
                $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
                ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                ->where('bodega.idtipobod',1) // BODEGA 1
                ->where('proxbode.tipoprod',4) //ofcina
                ->where('proxbode.idprod',$item)
                ->where('bodega.idbodega',$idbodega)
                ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codi_it as codigo_item')
                // ->DISTINCT('item.codi_it','lot.lote','proxbode.existencia','lot.fcaduca','proxbode.precio')
                ->get();
             
                
               
                
            }elseif($idbodega==4 && $lugar=="BODEGA"){

                $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
                ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                ->where('bodega.idtipobod',1) // BODEGA 1
                ->where('proxbode.tipoprod',3) //aseo
                ->where('proxbode.idprod',$item)
                ->where('bodega.idbodega',$idbodega)
                ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codi_it as codigo_item')
                // ->DISTINCT('item.codi_it','lot.lote','proxbode.existencia','lot.fcaduca','proxbode.precio')
                ->get();

                
            }elseif($idbodega==5 && $lugar=="BODEGA"){
                $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
                ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                ->where('bodega.idtipobod',1) // BODEGA 1
                ->where('proxbode.tipoprod',6) //herramienta
                ->where('proxbode.idprod',$item)
                ->where('bodega.idbodega',$idbodega)
                ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codi_it as codigo_item')
                // ->DISTINCT('item.codi_it','lot.lote','proxbode.existencia','lot.fcaduca','proxbode.precio')
                ->get();
                
            }elseif($idbodega==10 && $lugar=="BODEGA"){
                $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
               ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
               ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
               ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
               ->where('bodega.idtipobod',1) // BODEGA 1
               ->where('proxbode.tipoprod',8) //lenceria
               ->where('proxbode.idprod',$item)
               ->where('bodega.idbodega',$idbodega)
               ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codi_it as codigo_item')
            //    ->DISTINCT('item.codi_it','lot.lote','proxbode.existencia','lot.fcaduca','proxbode.precio')
               ->get();
               
            }elseif($idbodega==12 && $lugar=="BODEGA"){
                $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
                ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                ->where('bodega.idtipobod',1) // BODEGA 1
                ->where('proxbode.tipoprod',9) //otros
                ->where('bodega.idbodega',$idbodega)
                // ->where('item.activo','VERDADERO')
                ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codi_it as codigo_item')
                ->DISTINCT('item.codi_it','lot.lote','proxbode.existencia','lot.fcaduca','proxbode.precio')
                ->get();
            
            }elseif($idbodega==15 && $lugar=="BODEGA"){
                $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
                ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                ->where('bodega.idtipobod',1) // BODEGA 1
                ->where('proxbode.tipoprod',9) //formulario
                ->where('bodega.idbodega',$idbodega)
                // ->where('item.activo','VERDADERO')
                ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codi_it as codigo_item')
                // ->DISTINCT('item.codi_it','lot.lote','proxbode.existencia','lot.fcaduca','proxbode.precio')
                ->get();
            
            }
           
            return response()->json([
                'error'=>false,
                'resultado'=>$medicamentos
            ]);
               
        }catch (\Throwable $e) {
            Log::error('InventarioController => buscarDetalleItemBod => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    

    public function buscarDetalleItemBodFecha($idbodega, $lugar, $item, $f, $fini, $ffin){
        try{
            
            if($lugar=="FARMACIA"){
                if($idbodega==6 || $idbodega==20){
                    $medicamentos= DB::connection('pgsql')->table('bodega.medicamentos as med')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','med.coditem')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',2) // BODEGA 2
                    // ->where('proxbode.tipoprod',1) //MEDICAMENTOS
                    ->where('proxbode.idprod',$item)
                    ->where('bodega.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion,' [', med.coditem,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','med.cum as codigo_item','bodega.idbodega','bodega.idtipobod','proxbode.tipoprod','med.id_medicamento','med.coditem as iditem')
                    // ->DISTINCT('codigo_item','lot.lote','proxbode.existencia','lot.fcaduca','proxbode.precio')
                    ->get();
                    
                    $suma=0;
                    foreach($medicamentos as $data){
                       $suma=$suma+$data->existencia;

                    }
                    if($idbodega==20){
                        $actualizaStock=Medicamento::where('coditem',$data->iditem)->first();
                        $actualizaStock->stock_farm_dialisis=$suma;
                        $actualizaStock->save();
                    }else if($idbodega==6){
                        $actualizaStock=Medicamento::where('coditem',$data->iditem)->first();
                        $actualizaStock->stock=$suma;
                        $actualizaStock->save();
                    }
                   
                }else if($idbodega==7 || $idbodega==21){
                    $medicamentos= DB::connection('pgsql')->table('bodega.insumo as insu')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','insu.codinsumo')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',2) // BODEGA 1
                    // ->where('proxbode.tipoprod',2) //INSUMPS
                    ->where('proxbode.idprod',$item)
                    ->where('bodega.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(insu.insumo,' [', insu.codinsumo,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','bodega.idbodega','proxbode.idbodprod','insu.cudim as codigo_item','insu.codinsumo as iditem') 
                    // ->DISTINCT('insu.cudim','lot.lote','proxbode.existencia','lot.fcaduca','proxbode.precio')
                    ->get();

                    $suma=0;
                    foreach($medicamentos as $data){
                       $suma=$suma+$data->existencia;

                    }
                   
                    if($idbodega==21){
                        $actualizaStock=Insumo::where('codinsumo',$data->iditem)->first();
                        $actualizaStock->stock_farm_dialisis=$suma;
                        $actualizaStock->save();
                    }else if($idbodega==7){
                        $actualizaStock=Insumo::where('codinsumo',$data->iditem)->first();
                        $actualizaStock->stock=$suma;
                        $actualizaStock->save();
                    }
                    
                }else{
                    $medicamentos= DB::connection('pgsql')->table('bodega.laboratorio as lab')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','lab.id')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',2) // BODEGA 1
                    ->where('proxbode.idprod',$item)
                    ->where('bodega.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(lab.descri,' ',lab.presen,' [', lab.id,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','bodega.idbodega','proxbode.idbodprod','lab.id as codigo_item','lab.id as iditem') 
                    // ->DISTINCT('lab.id_item','lot.lote','proxbode.existencia','lot.fcaduca','proxbode.precio')
                    ->get();

                    $suma=0;
                    foreach($medicamentos as $data){
                       $suma=$suma+$data->existencia;
                    }

                    if($idbodega==22 || $idbodega==24 || $idbodega==25){
                        $actualizaStock=Laboratorio::where('id',$data->iditem)->first();
                        $actualizaStock->stock_diali_farmacia=$suma;
                        $actualizaStock->save();
                    }else if($idbodega==27 || $idbodega==28 || $idbodega==29){
                        $actualizaStock=Laboratorio::where('id',$data->iditem)->first();
                        $actualizaStock->stock_farmacia=$suma;
                        $actualizaStock->save();
                    }                    

                }
            }
            else{
                if(($idbodega==1 || $idbodega==17) && $lugar=="BODEGA"){
                
                    $medicamentos= DB::connection('pgsql')->table('bodega.medicamentos as med')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','med.coditem')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 2
                    ->where('proxbode.tipoprod',1) //MEDICAMENTOS
                    ->where('proxbode.idprod',$item)
                    ->where('bodega.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion,' [', med.coditem,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','med.cum as codigo_item','bodega.idbodega','med.coditem as iditem')
                    // ->DISTINCT('codigo_item','lot.lote','proxbode.existencia','lot.fcaduca','proxbode.precio')
                    ->get();

                    $suma=0;
                    foreach($medicamentos as $data){
                    $suma=$suma+$data->existencia;
                    }
                    if($idbodega==1){
                        $actualizaStock=Medicamento::where('coditem',$data->iditem)->first();
                        $actualizaStock->stock_bod=$suma;
                        $actualizaStock->save();
                    }else if($idbodega==17){
                        $actualizaStock=Medicamento::where('coditem',$data->iditem)->first();
                        $actualizaStock->stock_bod_dialisis=$suma;
                        $actualizaStock->save();
                    }

                

                }elseif(($idbodega==2 || $idbodega==18) && $lugar=="BODEGA"){
                
                    $medicamentos= DB::connection('pgsql')->table('bodega.insumo as insu')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','insu.codinsumo')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',2) //INSUMPS
                    ->where('proxbode.idprod',$item)
                    ->where('bodega.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(insu.insumo,' [', insu.codinsumo,']',' [', proxbode.idbodprod,']') AS detalle"), 'insu.insumo AS detalle1','proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','insu.cudim as codigo_item','bodega.idbodega','insu.codinsumo as iditem') 
                    // ->DISTINCT('insu.cudim','lot.lote','proxbode.existencia','lot.fcaduca','proxbode.precio')
                    ->get();

                    $suma=0;
                    foreach($medicamentos as $data){
                    $suma=$suma+$data->existencia;

                    }
                
                    if($idbodega==2){
                        $actualizaStock=Insumo::where('codinsumo',$data->iditem)->first();
                        $actualizaStock->stockbod=$suma;
                        $actualizaStock->save();
                    }else if($idbodega==18){
                        $actualizaStock=Insumo::where('codinsumo',$data->iditem)->first();
                        $actualizaStock->stock_bod_dialisis=$suma;
                        $actualizaStock->save();
                    }
                    
                }elseif(($idbodega==8 || $idbodega==19) && $lugar=="BODEGA"){//materiales
                

                    $medicamentos= DB::connection('pgsql')->table('bodega.laboratorio as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.id')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',5) //bodega materiales
                    ->where('proxbode.idprod',$item)
                    ->where('bodega.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.id,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codigo as codigo_item','item.id as iditem')
                    // ->DISTINCT('item.codi_it','lot.lote','proxbode.existencia','lot.fcaduca','proxbode.precio')
                    ->get();

                    $suma=0;
                    foreach($medicamentos as $data){
                    $suma=$suma+$data->existencia;

                    }

                    if($idbodega==19){
                        $actualizaStock=Laboratorio::where('id',$data->iditem)->first();
                        $actualizaStock->stock_dialisis=$data->existencia;
                        $actualizaStock->save();
                    }else if($idbodega==8){
                        $actualizaStock=Laboratorio::where('id',$data->iditem)->first();
                        $actualizaStock->stock=$data->existencia;
                        $actualizaStock->save();
                    }
                    
                
                }elseif(($idbodega==13 || $idbodega==23) && $lugar=="BODEGA"){//react
            
                    $medicamentos= DB::connection('pgsql')->table('bodega.laboratorio as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.id')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',10) //bodega react
                    ->where('proxbode.idprod',$item)
                    ->where('bodega.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.id,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codigo as codigo_item','item.id as iditem')
                    ->get();

                    $suma=0;
                    foreach($medicamentos as $data){
                    $suma=$suma+$data->existencia;

                    }
                    
                    if($idbodega==23){
                        $actualizaStock=Laboratorio::where('id',$data->iditem)->first();
                        $actualizaStock->stock_dialisis=$data->existencia;
                        $actualizaStock->save();
                    }else if($idbodega==13){
                        $actualizaStock=Laboratorio::where('id',$data->iditem)->first();
                        $actualizaStock->stock=$data->existencia;
                        $actualizaStock->save();
                    }
                
                }elseif(($idbodega==14 || $idbodega==24)&& $lugar=="BODEGA"){//micro
                    $medicamentos= DB::connection('pgsql')->table('bodega.laboratorio as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.id')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',11) //bodega micro
                    ->where('proxbode.idprod',$item)
                    ->where('bodega.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.id,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codigo as codigo_item','item.id as iditem')
                    ->get();

                    $suma=0;
                    foreach($medicamentos as $data){
                    $suma=$suma+$data->existencia;

                    }

                    if($idbodega==24){
                        $actualizaStock=Laboratorio::where('id',$data->iditem)->first();
                        $actualizaStock->stock_dialisis=$data->existencia;
                        $actualizaStock->save();
                    }else if($idbodega==14){
                        $actualizaStock=Laboratorio::where('id',$data->iditem)->first();
                        $actualizaStock->stock=$data->existencia;
                        $actualizaStock->save();
                    }
                

                }elseif($idbodega==9 && $lugar=="BODEGA"){//tics
                

                    $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',7) //ticst
                    ->where('proxbode.idprod',$item)
                    ->where('bodega.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codi_it as codigo_item')
                    ->get();
                
                    
                }elseif($idbodega==3 && $lugar=="BODEGA"){
                
                    $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',4) //ofcina
                    ->where('proxbode.idprod',$item)
                    ->where('bodega.idbodega',$idbodega)
                    // ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codi_it as codigo_item')
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codi_it as codigo_item','proxbode.existencia')
                    ->get();

                    
                }elseif($idbodega==4 && $lugar=="BODEGA"){

                    // $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                    // ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
                    // ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    // ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    // ->where('bodega.idtipobod',1) // BODEGA 1
                    // ->where('proxbode.tipoprod',3) //aseo
                    // ->where('proxbode.idprod',$item)
                    // ->where('bodega.idbodega',$idbodega)
                    // ->whereBetween('proxbode.fecha', [$fini, $ffin])
                    // ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codi_it as codigo_item')
                    // ->get();


                    $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',3) //aseo
                    ->where('proxbode.idprod',$item)
                    ->where('bodega.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codi_it as codigo_item')
                    ->get();


                
                
                }elseif($idbodega==30 && $lugar=="BODEGA"){

                    $medicamentos= DB::connection('pgsql')->table('bodega.proteccion as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.id')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',23) //protecc
                    ->where('proxbode.idprod',$item)
                    ->where('bodega.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.id,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.id as codigo_item')
                    ->get();

                
                }elseif($idbodega==5 && $lugar=="BODEGA"){
                    $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',6) //herramienta
                    ->where('proxbode.idprod',$item)
                    ->where('bodega.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codi_it as codigo_item')
                    ->get();
                    
                }elseif($idbodega==10 && $lugar=="BODEGA"){
                    $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
                ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                ->where('bodega.idtipobod',1) // BODEGA 1
                ->where('proxbode.tipoprod',8) //lenceria
                ->where('proxbode.idprod',$item)
                ->where('bodega.idbodega',$idbodega)
                ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codi_it as codigo_item')
                ->get();
                
                }elseif($idbodega==12 && $lugar=="BODEGA"){
                    $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',9) //otros
                    ->where('bodega.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codi_it as codigo_item')
                    ->DISTINCT('item.codi_it','lot.lote','proxbode.existencia','lot.fcaduca','proxbode.precio')
                    ->get();
                
                }elseif($idbodega==15 && $lugar=="BODEGA"){
                    $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',9) //formulario
                    ->where('bodega.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codi_it as codigo_item')
                    ->get();
                
                }
            }

            foreach($medicamentos as $key=> $data){
                
                $prodBod= DB::connection('pgsql')->table('bodega.existencia as ex')
                ->select(DB::raw('sum("resta") as cant_egreso'),DB::raw('sum("suma") as cant_ingreso'),
                DB::raw('count(*) as cantidadExis'),'idbodprod')
                ->groupby('idbodprod')
                // ->where('ex.cod','!=','REVERTIDO')
                ->where('idbodprod',$data->idbodprod)
               
                ->where(function($q) use($fini, $ffin) {
                    //$q->whereDate('ex.fecha_hora', '>=', $fini)
                    //->whereDate('ex.fecha_hora', '<=', $ffin);
                    $q->whereDate('ex.fecha_hora', '<=', $ffin);
                })
                ->first();
               
                if(is_null($prodBod)){
                    $totalItem=0;
                }
                else{
                    $cantidad=DB::connection('pgsql')->table('bodega.existencia')
                    ->select(DB::raw('sum("resta") as cant_egreso'),DB::raw('sum("suma") as cant_ingreso'),'idbodprod')
                    ->where('idbodprod',$prodBod->idbodprod)
                    ->whereDate('fecha_hora', '<=', $ffin)
                    ->groupby('idbodprod')
                    ->first();
                    if(!is_null($cantidad)){
                        $totalItem=$cantidad->cant_ingreso - $prodBod->cant_egreso;
                    }else{
                        $totalItem=0;
                    }

                   
                }    

                $medicamentos[$key]->existencia=$totalItem;
            }
           
            return response()->json([
                'error'=>false,
                'resultado'=>$medicamentos
            ]);
               
        }catch (\Throwable $e) {
            Log::error('InventarioController => buscarDetalleItemBodFecha => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function buscarDetalleItemLoteFecha($idbodprod, $lugar, $f, $fini, $ffin,$idbodega){
        try{
            
            if($lugar=="FARMACIA"){
                if($idbodega==6 || $idbodega==20){
                    $medicamentos= DB::connection('pgsql')->table('bodega.medicamentos as med')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','med.coditem')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',2) // BODEGA 2
                    ->where('proxbode.idbodprod',$idbodprod)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion,' [', med.coditem,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','med.cum as codigo_item','bodega.idbodega','bodega.idtipobod','proxbode.tipoprod','med.id_medicamento','med.coditem as iditem')
                    ->get();
                    
                   
                }else if($idbodega==7 || $idbodega==21){
                    $medicamentos= DB::connection('pgsql')->table('bodega.insumo as insu')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','insu.codinsumo')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',2) // BODEGA 1
                    ->where('proxbode.idbodprod',$idbodprod)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(insu.insumo,' [', insu.codinsumo,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','bodega.idbodega','proxbode.idbodprod','insu.cudim as codigo_item','insu.codinsumo as iditem') 
                    ->get();

                    
                }else{
                    $medicamentos= DB::connection('pgsql')->table('bodega.laboratorio as lab')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','lab.id')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',2) // BODEGA 1
                    ->where('proxbode.idbodprod',$idbodprod)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(lab.descri,' ',lab.presen,' [', lab.id,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','bodega.idbodega','proxbode.idbodprod','lab.id as codigo_item','lab.id as iditem') 
                    // ->DISTINCT('lab.id_item','lot.lote','proxbode.existencia','lot.fcaduca','proxbode.precio')
                    ->get();

                            

                }
            }
            else{
                if(($idbodega==1 || $idbodega==17) && $lugar=="BODEGA"){
                
                    $medicamentos= DB::connection('pgsql')->table('bodega.medicamentos as med')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','med.coditem')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 2
                    ->where('proxbode.tipoprod',1) //MEDICAMENTOS
                    ->where('proxbode.idbodprod',$idbodprod)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion,' [', med.coditem,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','med.cum as codigo_item','bodega.idbodega','med.coditem as iditem')
                    // ->DISTINCT('codigo_item','lot.lote','proxbode.existencia','lot.fcaduca','proxbode.precio')
                    ->get();

                   

                }elseif(($idbodega==2 || $idbodega==18) && $lugar=="BODEGA"){
                
                    $medicamentos= DB::connection('pgsql')->table('bodega.insumo as insu')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','insu.codinsumo')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',2) //INSUMPS
                    ->where('proxbode.idbodprod',$idbodprod)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(insu.insumo,' [', insu.codinsumo,']',' [', proxbode.idbodprod,']') AS detalle"), 'insu.insumo AS detalle1','proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','insu.cudim as codigo_item','bodega.idbodega','insu.codinsumo as iditem') 
                    // ->DISTINCT('insu.cudim','lot.lote','proxbode.existencia','lot.fcaduca','proxbode.precio')
                    ->get();

                    
                    
                }elseif(($idbodega==8 || $idbodega==19) && $lugar=="BODEGA"){//materiales
                

                    $medicamentos= DB::connection('pgsql')->table('bodega.laboratorio as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.id')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',5) //bodega materiales
                    ->where('proxbode.idbodprod',$idbodprod)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.id,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codigo as codigo_item','item.id as iditem')
                    // ->DISTINCT('item.codi_it','lot.lote','proxbode.existencia','lot.fcaduca','proxbode.precio')
                    ->get();

                    
                }elseif(($idbodega==13 || $idbodega==23) && $lugar=="BODEGA"){//react
            
                    $medicamentos= DB::connection('pgsql')->table('bodega.laboratorio as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.id')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',10) //bodega react
                    ->where('proxbode.idbodprod',$idbodprod)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.id,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codigo as codigo_item','item.id as iditem')
                    ->get();

                    
                }elseif(($idbodega==14 || $idbodega==24)&& $lugar=="BODEGA"){//micro
                    $medicamentos= DB::connection('pgsql')->table('bodega.laboratorio as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.id')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',11) //bodega micro
                    ->where('proxbode.idbodprod',$idbodprod)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.id,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codigo as codigo_item','item.id as iditem')
                    ->get();

                

                }elseif($idbodega==9 && $lugar=="BODEGA"){//tics
                

                    $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',7) //ticst
                    ->where('proxbode.idprod',$item)
                    ->where('bodega.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codi_it as codigo_item')
                    ->get();
                
                    
                }elseif($idbodega==3 && $lugar=="BODEGA"){
                
                    $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',4) //ofcina
                    ->where('proxbode.idprod',$item)
                    ->where('bodega.idbodega',$idbodega)
                    
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codi_it as codigo_item','proxbode.existencia')
                    ->get();

                    
                }elseif($idbodega==4 && $lugar=="BODEGA"){

                    $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',3) //aseo
                    ->where('proxbode.idprod',$item)
                    ->where('bodega.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codi_it as codigo_item')
                    ->get();


                
                
                }elseif($idbodega==30 && $lugar=="BODEGA"){

                    $medicamentos= DB::connection('pgsql')->table('bodega.proteccion as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.id')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',23) //protecc
                    ->where('proxbode.idprod',$item)
                    ->where('bodega.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.id,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.id as codigo_item')
                    ->get();

                
                }elseif($idbodega==5 && $lugar=="BODEGA"){
                    $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',6) //herramienta
                    ->where('proxbode.idprod',$item)
                    ->where('bodega.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codi_it as codigo_item')
                    ->get();
                    
                }elseif($idbodega==10 && $lugar=="BODEGA"){
                    $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
                ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                ->where('bodega.idtipobod',1) // BODEGA 1
                ->where('proxbode.tipoprod',8) //lenceria
                ->where('proxbode.idprod',$item)
                ->where('bodega.idbodega',$idbodega)
                ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codi_it as codigo_item')
                ->get();
                
                }elseif($idbodega==12 && $lugar=="BODEGA"){
                    $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',9) //otros
                    ->where('bodega.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codi_it as codigo_item')
                    ->DISTINCT('item.codi_it','lot.lote','proxbode.existencia','lot.fcaduca','proxbode.precio')
                    ->get();
                
                }elseif($idbodega==15 && $lugar=="BODEGA"){
                    $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.codi_it')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',9) //formulario
                    ->where('bodega.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.codi_it,']',' [', proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codi_it as codigo_item')
                    ->get();
                
                }
            }

            
           
            return response()->json([
                'error'=>false,
                'resultado'=>$medicamentos
            ]);
               
        }catch (\Throwable $e) {
            Log::error('InventarioController => buscarDetalleItemLoteFecha => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function FiltralistarInventario ($idbodega, $luga,$txt){
        if($idbodega==1){
            $medicamentos= DB::connection('pgsql')->table('bodega.medicamentos as med')
            ->select(DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion) as detalle"),
            'med.codigo','med.coditem as id_item','med.cum as cudim'
            ,'med.stock_bod as existencia','med.valor as precio','med.activo as estado','med.stock_min','med.stock_cri','med.es_dialisis')
            ->where(function($c)use($txt) {
                $c->where('nombre', 'ilike', '%'.$txt.'%')
                ->orWhere('codigo', 'ilike', '%'.$txt.'%')
                ->orWhere('cum', 'ilike', '%'.$txt.'%');
            })
            // ->where('med.activo','VERDADERO')
            ->get();
        }else if($idbodega==2){
            
            $medicamentos= DB::connection('pgsql')->table('bodega.insumo as insu')
            ->where(function($c)use($txt) {
                if($txt!=0){
                    $c->where('insumo', 'ilike', '%'.$txt.'%')
                    ->orWhere('codigo', 'ilike', '%'.$txt.'%')
                    ->orWhere('cudim', 'ilike', '%'.$txt.'%');
                }
                   
            })
            ->select('insu.insumo as detalle', 'insu.codigo','insu.codinsumo as id_item','insu.cudim'
            ,'insu.stockbod as existencia','insu.valor as precio','insu.activo as estado','insu.stockmin as stock_min','insu.stockcri as stock_cri','insu.codigo')
            // ->where('insu.activo','VERDADERO')
            ->get();
        }elseif($idbodega==8 || $idbodega==13 || $idbodega==14){
               
            $medicamentos= DB::connection('pgsql')->table('bodega.laboratorio as lab')
            ->where('lab.idbodega',$idbodega) // BODEGA laboratorio
            ->where(function($c)use($txt) {
                $c->where('descri', 'ilike', '%'.$txt.'%')
                ->orWhere('codigo', 'ilike', '%'.$txt.'%');
            })
            ->select(DB::raw("CONCAT(lab.descri,' ', lab.presen) AS detalle"),'lab.stock as existencia','lab.valor as precio','lab.activo as estado','lab.codigo','lab.id as id_item','lab.stockmin as stock_min','lab.stockcri as stock_cri','lab.codigo','lab.esbay')               
            ->get();
      


        }
          

        return response()->json([
            'error'=>false,
            'resultado'=>$medicamentos
        ]);

    }

    public function listarInventario($idbodega, $lugar){

        try{
          
            // if($lugar=="FARMACIA"){
            //     if($idbodega==6 || $idbodega==20){
            //         $medicamentos= DB::connection('pgsql')->table('bodega.medicamentos as med')
            //         ->select(DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion) as detalle"),
            //         'med.cum as codigo_item','med.coditem as id_item'
            //         ,'med.stock as existencia','med.valor as precio','med.activo as estado','med.stock_min','med.stock_cri','med.es_dialisis')
            //         ->where('med.activo','VERDADERO')
            //         ->where(function($q) use($idbodega) {
            //             if($idbodega==6){
            //                 $q->where('es_dialisis', null)
            //                 ->orWhere('es_dialisis', 'N');
            //             }else{
            //                 $q->where('es_dialisis', 'S');
            //             }
                           
            //         })
            //         ->distinct()
            //         ->get();

            //     }else if($idbodega==7 || $idbodega==21){
            //         $medicamentos= DB::connection('pgsql')->table('bodega.insumo as insu')
            //         ->select('insu.insumo as detalle',
            //         'insu.cudim as codigo_item','insu.codinsumo as id_item'
            //         ,'insu.stock as existencia','insu.valor as precio','insu.activo as estado','insu.stockmin as stock_min','insu.stockcri as stock_cri')
            //         ->where(function($q) use($idbodega) {
            //             if($idbodega==7){
            //                 $q->where('es_dialisis', null)
            //                 ->orWhere('es_dialisis', 'N');
            //             }else{
            //                 $q->where('es_dialisis', 'S');
            //             }
                           
            //         })
            //         ->where('insu.activo','VERDADERO')
            //         ->distinct()
            //         ->get();
                    
                               
            //     }else if($idbodega==22  || $idbodega==19 || $idbodega==16){
                   
            //         $medicamentos= DB::connection('pgsql')->table('bodega.farm_laboratorio as lab')
            //         // ->Join('bodega.prodxbod as proxbode', 'proxbode.idprod','insu.codinsumo')
            //         ->select(DB::raw("CONCAT(lab.nombre,'  ', lab.present) as detalle"),
            //         'lab.codinsumo as codigo_item','lab.id_item as id_item'
            //         ,'lab.stock_farmacia as existencia','lab.valor as precio','lab.activo as estado','lab.stockmin as stock_min','lab.stockcri as stock_cri','es_dialisis')
            //         ->where(function($q) use($idbodega) {
            //             if($idbodega==16){
            //                 $q->where('es_dialisis', null)
            //                 ->orWhere('es_dialisis', 'N');
            //             }else{
            //                 $q->where('es_dialisis', 'S');
            //             }
                           
            //         })
            //         ->where('lab.activo','VERDADERO')
            //         ->distinct()
            //         ->get();
            //         // dd($idbodega);
                
            //     }else{
            //         $medicamentos= DB::connection('pgsql')->table('bodega.farm_laboratorio as lab')
            //         // ->Join('bodega.prodxbod as proxbode', 'proxbode.idprod','insu.codinsumo')
            //         ->select(DB::raw("CONCAT(lab.nombre,'  ', lab.present) as detalle"),
            //         'lab.codinsumo as codigo_item','lab.id_item as id_item'
            //         ,'lab.stock_farmacia as existencia','lab.valor as precio','lab.activo as estado','lab.stockmin as stock_min','lab.stockcri as stock_cri')
            //         ->where('lab.activo','VERDADERO')
            //         ->distinct()
            //         ->get();
                   
            //     }
            // }

            if($idbodega==1 && $lugar=="BODEGA"){
              
                $medicamentos= DB::connection('pgsql')->table('bodega.medicamentos as med')
                ->select(DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion) as detalle"),
                'med.cum as codigo_item','med.coditem as id_item'
                ,'med.stock_bod as existencia','med.valor as precio','med.activo as estado','med.stock_min','med.stock_cri','med.es_dialisis')
               
                ->where('med.activo','VERDADERO')
                ->get();
                
               
            }elseif($idbodega==2 && $lugar=="BODEGA"){
               
              
                $medicamentos= DB::connection('pgsql')->table('bodega.insumo as insu')
                ->select('insu.insumo as detalle',
                'insu.cudim as codigo_item','insu.codinsumo as id_item'
                ,'insu.stockbod as existencia','insu.valor as precio','insu.activo as estado','insu.stockmin as stock_min','insu.stockcri as stock_cri','insu.codigo')
                ->where('insu.activo','VERDADERO')
                ->get();
                

            }elseif(($idbodega==8 || $idbodega==13 || $idbodega==14)&& $lugar=="BODEGA"){
               
              
                $medicamentos= DB::connection('pgsql')->table('bodega.laboratorio as lab')
                ->where('lab.idbodega',$idbodega) // BODEGA reactivo gral
                ->select(DB::raw("CONCAT(lab.descri,' ', lab.presen) AS detalle"),'lab.stock as existencia','lab.valor as precio','lab.activo as estado','lab.codigo as codigo_item','lab.id as id_item','lab.stockmin as stock_min','lab.stockcri as stock_cri','lab.codigo','lab.esbay')               
                ->get();
          


            }elseif($idbodega==9 && $lugar=="BODEGA"){
                //tics
                $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                ->where('item.idbodega',9) // BODEGA tics
                ->select(DB::raw("CONCAT(item.descri,' ', item.presen) AS detalle"),'item.stock as existencia','item.valor as precio','item.activo as estado','item.codigo as codigo_item','item.codi_it as id_item')
                ->get();
                
            }elseif($idbodega==3 && $lugar=="BODEGA"){
               
                $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                ->where('item.idbodega',3) // BODEGA ofica
                ->select(DB::raw("CONCAT(item.descri,' ', item.presen) AS detalle"),'item.stock as existencia','item.valor as precio','item.activo as estado','item.codigo as codigo_item','item.codi_it as id_item')
                ->get();
                
            }elseif($idbodega==4 && $lugar=="BODEGA"){
             

                $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                ->where('item.idbodega',4) // BODEGA aseo y limpieza
                ->select(DB::raw("CONCAT(item.descri,' ', item.presen) AS detalle"),'item.stock as existencia','item.valor as precio','item.activo as estado','item.codigo as codigo_item','item.codi_it as id_item')
                ->get();
                
            }elseif($idbodega==5 && $lugar=="BODEGA"){
               

                $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
                ->where('item.idbodega',5) // BODEGA herramientaa
                ->select(DB::raw("CONCAT(item.descri,' ', item.presen) AS detalle"),'item.stock as existencia','item.valor as precio','item.activo as estado','item.codigo as codigo_item','item.codi_it as id_item')
                ->get();
                
            }elseif($idbodega==10 && $lugar=="BODEGA"){
              
               $medicamentos= DB::connection('pgsql')->table('bodega.items as item')
               ->where('item.idbodega',10) // BODEGA lenceria 
               ->select(DB::raw("CONCAT(item.descri,' ', item.presen) AS detalle"),'item.stock as existencia','item.valor as precio','item.activo as estado','item.codigo as codigo_item','item.codi_it as id_item')
               ->get();
               
            }else if($idbodega==17 && $lugar=="BODEGA"){
              
                $medicamentos= DB::connection('pgsql')->table('bodega.medicamentos as med')
                ->select(DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion) as detalle"),
                'med.cum as codigo_item','med.coditem as id_item'
                ,'med.stock_bod as existencia','med.valor as precio','med.activo as estado','med.stock_min','med.stock_cri','med.es_dialisis')
                ->where('med.activo','VERDADERO')
                ->get();
                

            }else if($idbodega==18 && $lugar=="BODEGA"){               
              
                $medicamentos= DB::connection('pgsql')->table('bodega.insumo as insu')
                ->select('insu.insumo as detalle',
                'insu.cudim as codigo_item','insu.codinsumo as id_item'
                ,'insu.stockbod as existencia','insu.valor as precio','insu.activo as estado','insu.stockmin as stock_min','insu.stockcri as stock_cri')
                ->where('insu.activo','VERDADERO')
              
                ->get();
                
            }
           

            foreach($medicamentos as $key=> $item){
                $prodBod= DB::connection('pgsql')->table('bodega.prodxbod')
                ->select(DB::raw('sum("existencia") as totalitems'),DB::raw('sum("precio") as precioitems'),
                DB::raw('count(*) as cant'),'idprod')
                ->groupby('idprod')
                ->where('idprod',$item->id_item)
                ->where('idbodega',$idbodega)
                ->first();
                
               
                if(!is_null($prodBod)){ 
                   
                    $existenciaCons= DB::connection('pgsql')->table('bodega.prodxbod')
                    ->leftJoin('bodega.existencia as ex', 'ex.idbodprod','prodxbod.idbodprod')
                    ->select(DB::raw('sum("resta") as egreso'),DB::raw('sum("suma") as ingreso'),
                    DB::raw('count(*) as cantidadExis'),'prodxbod.idprod')
                    ->groupby('prodxbod.idprod')
                    ->where('prodxbod.idprod',$item->id_item)
                    ->where('prodxbod.idbodega',$idbodega)
                    ->first();


                    $existenciaMensual= DB::connection('pgsql')->table('bodega.prodxbod')
                    ->leftJoin('bodega.existencia as exis', 'exis.idbodprod','prodxbod.idbodprod')
                    ->select(DB::raw('sum("resta") as egresoMens'),DB::raw('sum("suma") as sumaMens'),
                    DB::raw('count(*) as cantidadex'),'prodxbod.idprod')
                    ->groupby('prodxbod.idprod')
                    ->where('prodxbod.idprod',$item->id_item)
                    ->where('prodxbod.idbodega',$idbodega)
                    ->whereYear('exis.fecha_hora', '=', date('Y'))
                    ->whereMonth('exis.fecha_hora', '=',date('m'))
                    ->first();

                    $cantidadEgresoCons=  DB::connection('pgsql')->table('bodega.prodxbod')
                    ->leftJoin('bodega.existencia as ex', 'ex.idbodprod','prodxbod.idbodprod')
                    ->select(DB::raw('count(*) as cantidadEgreso'),'prodxbod.idprod')
                    ->groupby('prodxbod.idprod')
                    ->where('prodxbod.idprod',$item->id_item)
                    ->where('prodxbod.idbodega',$idbodega)
                   
                    ->where('ex.resta','>',0)
                    ->first();
                  
                    if(!is_null($existenciaMensual)){                       
                        $medicamentos[$key]->egresadoMens=$existenciaMensual->egresomens;
                    }else{
                        $medicamentos[$key]->egresadoMens=0;
                    }


                    if(!is_null($existenciaCons)){                       
                        $medicamentos[$key]->egresado=$existenciaCons->egreso;
                        $medicamentos[$key]->cantidadex=$existenciaCons->cantidadexis;
                    }else{
                        $medicamentos[$key]->egresado=0;
                        $medicamentos[$key]->cantidadex=0;
                    }

                    if(!is_null($cantidadEgresoCons)){  
                        $cantidadEgresoTop=  DB::connection('pgsql')->table('bodega.prodxbod')
                        ->leftJoin('bodega.existencia as ex', 'ex.idbodprod','prodxbod.idbodprod')
                        ->where('prodxbod.idprod',$item->id_item)
                        ->where('prodxbod.idbodega',$idbodega)
                        ->where('ex.resta','>',0)
                        ->max('ex.resta');
                        
                                                                   
                        $medicamentos[$key]->cantidadegreso=$cantidadEgresoCons->cantidadegreso;
                        $medicamentos[$key]->cantidadegresoAlto=$cantidadEgresoTop;

                    }else{
                       
                        $medicamentos[$key]->cantidadegreso=0;
                        $medicamentos[$key]->cantidadegresoAlto=0;

                    }

                   
                    if($prodBod->totalitems != $item->existencia){
                        $medicamentos[$key]->total=$prodBod->totalitems;
                        $medicamentos[$key]->inconsis="Inconsistencia";
                        $medicamentos[$key]->total_precio=$prodBod->precioitems;
                        $medicamentos[$key]->cant_=$prodBod->cant;
                      
                        
                    }else{
                        $medicamentos[$key]->inconsis="Normal";
                        $medicamentos[$key]->total=$prodBod->totalitems;
                        $medicamentos[$key]->total_precio=$prodBod->precioitems;
                        $medicamentos[$key]->cant_=$prodBod->cant;
                       
                    }
                }else{
                    $medicamentos[$key]->inconsis="Normal";
                    $medicamentos[$key]->cant_=0;
                    $medicamentos[$key]->egresado=0;
                    $medicamentos[$key]->egresadoMens=0;
                }

                
            }
           
            return response()->json([
                'error'=>false,
                'resultado'=>$medicamentos
            ]);
               
        }catch (\Throwable $e) {
            Log::error('InventarioController => listarInventario => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
        }    

    }

  

    public function buscarInventario($idbodega, $lugar, $tipo,$fini, $ffin, $egreso=null){
       
        try{
           
            if($lugar=="FARMACIA"){
                
                if($idbodega==22 || $idbodega==25 || $idbodega==26 || $idbodega==27 || $idbodega==28 || $idbodega==29 ){
                    //tabla laboratorio    
                    $prodBod= DB::connection('pgsql')->table('bodega.prodxbod')
                    ->leftJoin('bodega.existencia as ex', 'ex.idbodprod','prodxbod.idbodprod')
                    ->leftJoin('bodega.laboratorio as ite', 'ite.id','prodxbod.idprod')
                    ->select(DB::raw('sum("resta") as cant_egreso'),DB::raw('sum("suma") as cant_ingreso'),
                    DB::raw('count(*) as cantidadExis'),'prodxbod.idprod','prodxbod.idbodprod',DB::raw('sum("precio") as total_precio'),
                    DB::raw('count(*) as cant'),DB::raw("CONCAT(ite.descri,' ', ite.presen) AS detalle"),'ite.id as id_item','ite.codigo as codigo_item','ite.codigo')
                    ->groupby('prodxbod.idprod','prodxbod.idbodprod','detalle','id_item','codigo_item')
                    ->distinct('id_item')
                    ->where('prodxbod.idbodega',$idbodega)
                    // ->where('ex.cod','!=','REVERTIDO')
                    ->get();
                   
                   
                }else if($idbodega==21){
                    //insumo dialisis farmacia
                    $prodBod= DB::connection('pgsql')->table('bodega.prodxbod')
                    ->leftJoin('bodega.existencia as ex', 'ex.idbodprod','prodxbod.idbodprod')
                    ->leftJoin('bodega.insumo as ite', 'ite.codinsumo','prodxbod.idprod')
                    ->select(DB::raw('sum("resta") as cant_egreso'),DB::raw('sum("suma") as cant_ingreso'),
                    DB::raw('count(*) as cantidadExis'),'prodxbod.idprod','prodxbod.idbodprod',DB::raw('sum("precio") as total_precio'),
                    DB::raw('count(*) as cant'),DB::raw("CONCAT(ite.insumo) AS detalle"),'ite.codinsumo as id_item','ite.cudim as codigo_item','ite.codigo')
                    ->groupby('prodxbod.idprod','prodxbod.idbodprod','detalle','id_item','codigo_item','ite.codigo')
                    ->distinct('id_item')
                    // ->whereIn('prodxbod.idbodega',[21,31])
                    ->where('prodxbod.idbodega',$idbodega)
                    // ->where('ex.cod','!=','REVERTIDO')
                    ->get();

                  
                }else if($idbodega==20){
                    //medicina dialisis farmacia
                    $prodBod= DB::connection('pgsql')->table('bodega.prodxbod')
                    ->leftJoin('bodega.existencia as ex', 'ex.idbodprod','prodxbod.idbodprod')
                    ->leftJoin('bodega.medicamentos as ite', 'ite.coditem','prodxbod.idprod')
                    ->select(DB::raw('sum("resta") as cant_egreso'),DB::raw('sum("suma") as cant_ingreso'),
                    DB::raw('count(*) as cantidadExis'),'prodxbod.idprod','prodxbod.idbodprod',DB::raw('sum("precio") as total_precio'),
                    DB::raw('count(*) as cant'),DB::raw("CONCAT(ite.nombre, ' ', ite.concentra, ' ', ite.forma, ' ', ite.presentacion ) AS detalle"),'ite.coditem as id_item','ite.cum as codigo_item','ite.codigo')
                    ->groupby('prodxbod.idprod','prodxbod.idbodprod','detalle','id_item','codigo_item','ite.codigo')
                    ->distinct('id_item')
                    ->where('prodxbod.idbodega',$idbodega)
                    // ->where('ex.cod','!=','REVERTIDO')
                    ->get();
                }else if($idbodega==7){
                    //insumo gral farmacia
                    $prodBod= DB::connection('pgsql')->table('bodega.prodxbod')
                    ->leftJoin('bodega.existencia as ex', 'ex.idbodprod','prodxbod.idbodprod')
                    ->leftJoin('bodega.insumo as ite', 'ite.codinsumo','prodxbod.idprod')
                    ->select(DB::raw('sum("resta") as cant_egreso'),DB::raw('sum("suma") as cant_ingreso'),
                    DB::raw('count(*) as cantidadExis'),'prodxbod.idprod','prodxbod.idbodprod',DB::raw('sum("precio") as total_precio'),
                    DB::raw('count(*) as cant'),DB::raw("CONCAT(ite.insumo) AS detalle"),'ite.codinsumo as id_item','ite.cudim as codigo_item','ite.codigo')
                    ->groupby('prodxbod.idprod','prodxbod.idbodprod','detalle','id_item','codigo_item','ite.codigo')
                    ->distinct('id_item')
                    ->where('prodxbod.idbodega',$idbodega)
                    // ->where('ex.cod','!=','REVERTIDO')
                    ->get();

                  

                }else if($idbodega==6){
                    //medicina gral farmacia
                    $prodBod= DB::connection('pgsql')->table('bodega.prodxbod')
                    ->leftJoin('bodega.existencia as ex', 'ex.idbodprod','prodxbod.idbodprod')
                    ->leftJoin('bodega.medicamentos as ite', 'ite.coditem','prodxbod.idprod')
                    ->select(DB::raw('sum("resta") as cant_egreso'),DB::raw('sum("suma") as cant_ingreso'),
                    DB::raw('count(*) as cantidadExis'),'prodxbod.idprod','prodxbod.idbodprod',DB::raw('sum("precio") as total_precio'),
                    DB::raw('count(*) as cant'),DB::raw("CONCAT(ite.nombre, ' ', ite.concentra, ' ', ite.forma, ' ', ite.presentacion ) AS detalle"),'ite.coditem as id_item','ite.cum as codigo_item','ite.codigo')
                    ->groupby('prodxbod.idprod','prodxbod.idbodprod','detalle','id_item','codigo_item','ite.codigo')
                    ->distinct('id_item')
                    ->where('prodxbod.idbodega',$idbodega)
                    // ->where('ex.cod','!=','REVERTIDO')
                    ->get();
                    
                }

            }else{ //Bodega General
                if($idbodega==3 || $idbodega==4 || $idbodega==5 || $idbodega==9 || $idbodega==10 || $idbodega==12 || $idbodega==15){
                    //tabla items
                    $prodBod= DB::connection('pgsql')->table('bodega.prodxbod')
                    ->leftJoin('bodega.existencia as ex', 'ex.idbodprod','prodxbod.idbodprod')
                    ->leftJoin('bodega.items as ite', 'ite.codi_it','prodxbod.idprod')
                    ->select(DB::raw('sum("resta") as cant_egreso'),DB::raw('sum("suma") as cant_ingreso'),
                    DB::raw('count(*) as cantidadExis'),'prodxbod.idprod','prodxbod.idbodprod',DB::raw('sum("precio") as total_precio'),
                    DB::raw('count(*) as cant'),DB::raw("CONCAT(ite.descri,' ', ite.presen) AS detalle"),'ite.codi_it as id_item','ite.codigo as codigo_item','ite.codigo')
                    ->groupby('prodxbod.idprod','prodxbod.idbodprod','detalle','id_item','codigo_item')
                    ->distinct('id_item')
                    ->where('prodxbod.idbodega',$idbodega)
                    // ->where('ex.cod','!=','REVERTIDO')
                    ->get();
                    // dd($prodBod);
                  
                }else if($idbodega==30){
                    //tabla proteccion
                    $prodBod= DB::connection('pgsql')->table('bodega.prodxbod')
                    ->leftJoin('bodega.existencia as ex', 'ex.idbodprod','prodxbod.idbodprod')
                    ->leftJoin('bodega.proteccion as ite', 'ite.id','prodxbod.idprod')
                    ->select(DB::raw('sum("resta") as cant_egreso'),DB::raw('sum("suma") as cant_ingreso'),
                    DB::raw('count(*) as cantidadExis'),'prodxbod.idprod','prodxbod.idbodprod',DB::raw('sum("precio") as total_precio'),
                    DB::raw('count(*) as cant'),DB::raw("CONCAT(ite.descri,' ', ite.presen) AS detalle"),'ite.id as id_item','ite.codigo as codigo_item')
                    ->groupby('prodxbod.idprod','prodxbod.idbodprod','detalle','id_item','codigo_item','ite.codigo')
                    ->distinct('id_item')
                    ->where('prodxbod.idbodega',$idbodega)
                    // ->where('ex.cod','!=','REVERTIDO')
                    ->get();
                }elseif(($idbodega==8 || $idbodega==19) && $lugar=="BODEGA"){ //materiales
                    //tabla laboratorio          
                    $prodBod= DB::connection('pgsql')->table('bodega.prodxbod')
                    ->leftJoin('bodega.existencia as ex', 'ex.idbodprod','prodxbod.idbodprod')
                    ->leftJoin('bodega.laboratorio as ite', 'ite.id','prodxbod.idprod')
                    ->select(DB::raw("CONCAT(ite.descri,' ', ite.presen) AS detalle"),'ite.id as id_item','ite.codigo as codigo_item','ite.codigo','prodxbod.idprod','prodxbod.idbodprod')

                    ->groupby('prodxbod.idprod','prodxbod.idbodprod','detalle','id_item','codigo_item')
                    ->distinct('id_item')
                    ->where('prodxbod.idbodega',$idbodega)
                    // ->where('ex.cod','!=','REVERTIDO')
                    ->get();

                }elseif(($idbodega==13 || $idbodega==23) && $lugar=="BODEGA"){ //reactivo
                    //tabla laboratorio     
                    $prodBod= DB::connection('pgsql')->table('bodega.prodxbod')
                    ->leftJoin('bodega.existencia as ex', 'ex.idbodprod','prodxbod.idbodprod')
                    ->leftJoin('bodega.laboratorio as ite', 'ite.id','prodxbod.idprod')
                    ->select(DB::raw('sum("resta") as cant_egreso'),DB::raw('sum("suma") as cant_ingreso'),
                    DB::raw('count(*) as cantidadExis'),'prodxbod.idprod','prodxbod.idbodprod',DB::raw('sum("precio") as total_precio'),
                    DB::raw('count(*) as cant'),DB::raw("CONCAT(ite.descri,' ', ite.presen) AS detalle"),'ite.id as id_item','ite.codigo as codigo_item','ite.codigo')
                    ->groupby('prodxbod.idprod','prodxbod.idbodprod','detalle','id_item','codigo_item')
                    ->distinct('id_item')
                    ->where('prodxbod.idbodega',$idbodega)
                    // ->where('ex.cod','!=','REVERTIDO')
                    ->get();


                }elseif(($idbodega==14 || $idbodega==24) && $lugar=="BODEGA"){
                    //tabla laboratorio     
                    $prodBod= DB::connection('pgsql')->table('bodega.prodxbod')
                    ->leftJoin('bodega.existencia as ex', 'ex.idbodprod','prodxbod.idbodprod')
                    ->leftJoin('bodega.laboratorio as ite', 'ite.id','prodxbod.idprod')
                    ->select(DB::raw('sum("resta") as cant_egreso'),DB::raw('sum("suma") as cant_ingreso'),
                    DB::raw('count(*) as cantidadExis'),'prodxbod.idprod','prodxbod.idbodprod',DB::raw('sum("precio") as total_precio'),
                    DB::raw('count(*) as cant'),DB::raw("CONCAT(ite.descri,' ', ite.presen) AS detalle"),'ite.id as id_item','ite.codigo as codigo_item','ite.codigo')
                    ->groupby('prodxbod.idprod','prodxbod.idbodprod','detalle','id_item','codigo_item')
                    ->distinct('id_item')
                    ->where('prodxbod.idbodega',$idbodega)
                    // ->where('ex.cod','!=','REVERTIDO')
                    ->get();

                }  elseif(($idbodega==1 || $idbodega==17) && $lugar=="BODEGA"){
                    //tabla medicina     
                    $prodBod= DB::connection('pgsql')->table('bodega.prodxbod')
                    ->leftJoin('bodega.existencia as ex', 'ex.idbodprod','prodxbod.idbodprod')
                    ->leftJoin('bodega.medicamentos as ite', 'ite.coditem','prodxbod.idprod')
                    ->select(DB::raw('sum("resta") as cant_egreso'),DB::raw('sum("suma") as cant_ingreso'),
                    DB::raw('count(*) as cantidadExis'),'prodxbod.idprod','prodxbod.idbodprod',DB::raw('sum("precio") as total_precio'),
                    DB::raw('count(*) as cant'),DB::raw("CONCAT(ite.nombre,' ', ite.concentra,' ', ite.forma,' ', ite.presentacion) AS detalle"),'ite.coditem as id_item','ite.cum as codigo_item','ite.cum','ite.codigo')
                    ->groupby('prodxbod.idprod','prodxbod.idbodprod','detalle','id_item','codigo_item','ite.cum','ite.codigo')
                    ->distinct('id_item')
                    ->where('prodxbod.idbodega',$idbodega)
                    // ->where('ex.cod','!=','REVERTIDO')
                    ->get();
                    
                    
                }elseif(($idbodega==2 || $idbodega==18) && $lugar=="BODEGA"){
                    //tabla insumos     
                  
                    $prodBod= DB::connection('pgsql')->table('bodega.prodxbod')
                    ->leftJoin('bodega.existencia as ex', 'ex.idbodprod','prodxbod.idbodprod')
                    ->leftJoin('bodega.insumo as ite', 'ite.codinsumo','prodxbod.idprod')
                    ->select(DB::raw('sum("resta") as cant_egreso'),DB::raw('sum("suma") as cant_ingreso'),
                    DB::raw('count(*) as cantidadExis'),'prodxbod.idprod','prodxbod.idbodprod',DB::raw('sum("precio") as total_precio'),
                    DB::raw('count(*) as cant'),DB::raw("CONCAT(ite.insumo) AS detalle"),'ite.codinsumo as id_item','ite.cudim as codigo_item','ite.codigo')
                    ->groupby('prodxbod.idprod','prodxbod.idbodprod','detalle','id_item','codigo_item','ite.codigo')
                    ->distinct('id_item')
                    ->where('prodxbod.idbodega',$idbodega)
                    // ->where('ex.cod','!=','REVERTIDO')
                    ->get();
                }
            }                


            foreach($prodBod as $key=> $data){
                
               
                $prodBodC= DB::connection('pgsql')->table('bodega.existencia as ex')
                ->select(DB::raw('sum("resta") as cant_egreso'),DB::raw('sum("suma") as cant_ingreso'),
                DB::raw('count(*) as cantidadExis'),'idbodprod')
                ->groupby('idbodprod')
                ->where('idbodprod',$data->idbodprod)
                ->where(function($q) use($fini, $ffin) {
                    $q->whereDate('ex.fecha_hora', '<=', $ffin);
                })
                ->first();
               
                if(is_null($prodBodC)){
                    $totalItem=0;
                }
                else{
                   
                    $cantidad= DB::connection('pgsql')->table('bodega.prodxbod')
                    ->leftJoin('bodega.existencia as ex', 'ex.idbodprod','prodxbod.idbodprod')
                    ->select(DB::raw('sum("resta") as egreso'),DB::raw('sum("suma") as ingreso'),DB::raw('sum("precio") as precio_ag'),
                    DB::raw('count(*) as cantidadExist'),'prodxbod.idprod')
                    ->groupby('prodxbod.idprod')
                    ->where('prodxbod.idprod',$data->idprod)
                    ->where('prodxbod.idbodega',$idbodega)
                    // ->where('prodxbod.idbodega',[21,31])
                    ->where(function($q) use($fini, $ffin) {
                        $q->whereDate('ex.fecha_hora', '<=', $ffin);
                    })
                    ->first();
                 
                    if(!is_null($cantidad)){
                       
                        $totalItem=$cantidad->ingreso - $cantidad->egreso;
                       
                        $precioPr=$cantidad->precio_ag / $cantidad->cantidadexist;
                        $prodBod[$key]->precio_ag=0;
                    }else{
                        $totalItem=0;
                        $precioPr=0;
                        $prodBod[$key]->precio_ag=0;
                    }
                    $prodBod[$key]->precio=$precioPr;
                   
                }   
                
                $prodBod[$key]->total=$totalItem;
                $prodBod[$key]->inconsis="Normal";              
                
                if($egreso=="S"){
                    $existenciaCons= DB::connection('pgsql')->table('bodega.prodxbod')
                    ->leftJoin('bodega.existencia as ex', 'ex.idbodprod','prodxbod.idbodprod')
                    ->select(DB::raw('sum("resta") as egreso'),DB::raw('sum("suma") as ingreso'),DB::raw('sum("precio") as precioegreso'),
                    DB::raw('count(*) as cantidadExis'),'prodxbod.idprod')
                    ->groupby('prodxbod.idprod')
                    ->where('prodxbod.idprod',$data->id_item)
                    ->where('prodxbod.idbodega',$idbodega)
                    //   ->where('prodxbod.idbodega',[21,31])
                    ->where(function($q) use($fini, $ffin) {
                        $q->whereDate('ex.fecha_hora', '>=', $fini)
                        ->whereDate('ex.fecha_hora', '<=', $ffin);
                    })
                    ->where(function($c) use($idbodega) {
                        if($idbodega==6 || $idbodega==7 ||$idbodega==20 || $idbodega==21 || $idbodega==22  || $idbodega ==25 || $idbodega ==26 || $idbodega==27  || $idbodega ==28 || $idbodega ==29 || $idbodega ==21 ){
                            $c->whereIn('ex.cod',['EABFA','EF']); //egreso bodega farmacia
                        }else{
                            $c->whereIn('ex.cod',['EABA','EAB']);
                        }
                        
                    })
                
                    ->first();

                    if(!is_null($existenciaCons)){         
                                
                        $prodBod[$key]->egresado=$existenciaCons->egreso;
                        $prodBod[$key]->cantidadex=$existenciaCons->cantidadexis;
                        $prodBod[$key]->precioegreso=$existenciaCons->precioegreso;
                    }else{
                        $prodBod[$key]->egresado=0;
                        $prodBod[$key]->cantidadex=0;
                        $prodBod[$key]->precioegreso=0;
                    
                    }
                }
            }
           
            return[
                'error'=>false,
                'resultado'=>$prodBod
            ];
                
        }catch (\Throwable $e) {
            Log::error('InventarioController => buscarInventario => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return[
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ];
            
        }
    }

    public function kardexItemBodegaPdf($idprod, $ini, $fin,$bodega){
       
        try{
            set_time_limit(0);
            ini_set("memory_limit",-1);
            ini_set('max_execution_time', 0);

            $texto="";
            $consultaPdf=$this->kardexItemBodega($idprod, $ini, $fin,$bodega);
            if($consultaPdf['error']==true){
                return[
                    'error'=>true,
                    'mensaje'=>'Ocurrió un error, intentelo más tarde'
                ];
            }  
            
            if($bodega==8 || $bodega==13 || $bodega==14 || $bodega==19 || $bodega==23 || $bodega==24 ){
                //tabla laboratorio    
                $prodBod= DB::connection('pgsql')->table('bodega.laboratorio as ite')
                ->select(DB::raw("CONCAT(ite.descri,' ', ite.presen) AS detalle"))
                ->where('ite.id',$idprod)
                ->first();
               
               
            }else if($bodega==2 || $bodega==18){
                //insumo 
                $prodBod= DB::connection('pgsql')->table('bodega.insumo as ite')
                ->select(DB::raw("CONCAT(ite.insumo) AS detalle"))
                ->where('ite.codinsumo',$idprod)
                ->first();
               
              
            }else if($bodega==1 || $bodega==17){
                //medicina farmacia
                $prodBod= DB::connection('pgsql')->table('bodega.medicamentos as ite')
                ->select(DB::raw("CONCAT(ite.nombre, ' ', ite.concentra, ' ', ite.forma, ' ', ite.presentacion ) AS detalle"))
                ->where('ite.coditem',$idprod)
                ->first();

            }else if($bodega==30){
                //proteccion
                $prodBod= DB::connection('pgsql')->table('bodega.proteccion as ite')
                ->select(DB::raw("CONCAT(ite.descri ) AS detalle"))
                ->where('ite.id',$idprod)
                ->first();

            }else{
                //item
                $prodBod= DB::connection('pgsql')->table('bodega.items as ite')
                ->select(DB::raw("CONCAT(ite.descri, ' ', ite.presen) AS detalle"))
                ->where('ite.codi_it',$idprod)
                ->first();
            }

            $bodega=DB::connection('pgsql')->table('bodega.bodega')
            ->where('idbodega',$bodega)->first();

            $nombrePDF="Kardex.pdf";
            $pdf=\PDF::loadView('reportes.inventario_kardex_farmacia',['data'=>$consultaPdf["resultado"],'bodega'=>$bodega,'fini'=>$ini,'ffin'=>$fin,'nombre_item'=>$prodBod,"texto"=>""]);

           
            $pdf->setPaper("A4", "landscape");

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
            Log::error('InventarioController => kardexItemBodegaPdf => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return[
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ];
            
        }
    }

    

    public function kardexItemFarmaciaPdf($idprod, $ini, $fin,$bodega){
       
        try{
            set_time_limit(0);
            ini_set("memory_limit",-1);
            ini_set('max_execution_time', 0);

            $consultaPdf=$this->kardexItemFarmacia($idprod, $ini, $fin,$bodega);
            if($consultaPdf['error']==true){
                return[
                    'error'=>true,
                    'mensaje'=>'Ocurrió un error, intentelo más tarde'
                ];
            }  
            

            if($bodega==22 || $bodega==25 || $bodega==26 || $bodega==27 || $bodega==28 || $bodega==29 ){
                //tabla laboratorio    
                $prodBod= DB::connection('pgsql')->table('bodega.laboratorio as ite')
                ->select(DB::raw("CONCAT(ite.descri,' ', ite.presen) AS detalle"))
                ->where('ite.id',$idprod)
                ->first();
               
               
            }else if($bodega==7 || $bodega==21){
                //insumo 
                $prodBod= DB::connection('pgsql')->table('bodega.insumo as ite')
                ->select(DB::raw("CONCAT(ite.insumo) AS detalle"))
                ->where('ite.codinsumo',$idprod)
                ->first();
              
            }else if($bodega==6 || $bodega==20){
                //medicina farmacia
                $prodBod= DB::connection('pgsql')->table('bodega.medicamentos as ite')
                ->select(DB::raw("CONCAT(ite.nombre, ' ', ite.concentra, ' ', ite.forma, ' ', ite.presentacion ) AS detalle"))
                ->where('ite.coditem',$idprod)
                ->first();

            }

            $bodega=DB::connection('pgsql')->table('bodega.bodega')
            ->where('idbodega',$bodega)->first();

            $nombrePDF="Kardex.pdf";
            $pdf=\PDF::loadView('reportes.inventario_kardex_farmacia',['data'=>$consultaPdf["resultado"],'bodega'=>$bodega,'fini'=>$ini,'ffin'=>$fin,'nombre_item'=>$prodBod,"texto"=>""]);

           
            $pdf->setPaper("A4", "landscape");

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
            Log::error('InventarioController => kardexItemFarmaciaPdf => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return[
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ];
            
        }
    }

    public function kardexItemLoteFarmaciaPdf($idprod, $ini, $fin, $bodega, $lote,$idbodprod){
       
        try{
            set_time_limit(0);
            ini_set("memory_limit",-1);
            ini_set('max_execution_time', 0);
           
           
            $texto="";
            $consultaPdf=$this->kardexItemLoteFarmacia($idprod, $ini, $fin,$bodega,$lote,$idbodprod);
          
            if($consultaPdf['error']==true){
                return[
                    'error'=>true,
                    'mensaje'=>'Ocurrió un error, intentelo más tarde'
                ];
            }  
            
            if($bodega==22 || $bodega==25 || $bodega==26 || $bodega==27 || $bodega==28 || $bodega==29 ){
                //tabla laboratorio    
                $prodBod= DB::connection('pgsql')->table('bodega.laboratorio as ite')
                ->select(DB::raw("CONCAT(ite.descri,' ', ite.presen) AS detalle"),'ite.codigo')
                ->where('ite.id',$idprod)
                ->first();
                $texto="CODIGO ".$prodBod->codigo. " -- LOTE ".$lote;
               
            }else if($bodega==7 || $bodega==21){
                //insumo 
                $prodBod= DB::connection('pgsql')->table('bodega.insumo as ite')
                ->select(DB::raw("CONCAT(ite.insumo) AS detalle"),'ite.cudim')
                ->where('ite.codinsumo',$idprod)
                ->first();

                $texto="CUDIM ".$prodBod->cudim. " -- LOTE ".$lote;
              
            }else if($bodega==1 ||$bodega==6 || $bodega==20){
                //medicina farmacia
                $prodBod= DB::connection('pgsql')->table('bodega.medicamentos as ite')
                ->select(DB::raw("CONCAT(ite.nombre, ' ', ite.concentra, ' ', ite.forma, ' ', ite.presentacion ) AS detalle"),'ite.cum')
                ->where('ite.coditem',$idprod)
                ->first();

                $texto="CUM ".$prodBod->cum. " -- LOTE ".$lote;
            }

            $bodega=DB::connection('pgsql')->table('bodega.bodega')
            ->where('idbodega',$bodega)->first();

            $nombrePDF="Kardex.pdf";
            $pdf=\PDF::loadView('reportes.inventario_kardex_farmacia',['data'=>$consultaPdf["resultado"],'bodega'=>$bodega,'fini'=>$ini,'ffin'=>$fin,'nombre_item'=>$prodBod,"texto"=>$texto]);

           
            $pdf->setPaper("A4", "landscape");

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
            Log::error('InventarioController => kardexItemLoteFarmaciaPdf => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return[
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ];
            
        }
    }


    public function kardexItemLoteBodegaPdf($idprod, $ini, $fin, $bodega, $lote,$idbodprod){
       
        try{
            set_time_limit(0);
            ini_set("memory_limit",-1);
            ini_set('max_execution_time', 0);
           
          
            $texto="";
            $consultaPdf=$this->kardexItemLoteBodega($idprod, $ini, $fin,$bodega,$lote,$idbodprod);
         
          
            if($consultaPdf['error']==true){
                return[
                    'error'=>true,
                    'mensaje'=>'Ocurrió un error, intentelo más tarde'
                ];
            }  
            
            if($bodega==22 || $bodega==25 || $bodega==26 || $bodega==27 || $bodega==28 || $bodega==29 || $bodega==19 || $bodega==23 || $bodega==24 || $bodega==8 || $bodega==13 || $bodega==14 ){
                //tabla laboratorio    
                $prodBod= DB::connection('pgsql')->table('bodega.laboratorio as ite')
                ->select(DB::raw("CONCAT(ite.descri,' ', ite.presen) AS detalle"),'ite.codigo')
                ->where('ite.id',$idprod)
                ->first();
                $texto="CODIGO ".$prodBod->codigo. " -- LOTE ".$lote;
               
            }else if($bodega==2 || $bodega==7 || $bodega==21){
                //insumo 
                $prodBod= DB::connection('pgsql')->table('bodega.insumo as ite')
                ->select(DB::raw("CONCAT(ite.insumo) AS detalle"),'ite.cudim')
                ->where('ite.codinsumo',$idprod)
                ->first();

                $texto="CUDIM ".$prodBod->cudim. " -- LOTE ".$lote;
              
            }else if($bodega==1 ||$bodega==6 || $bodega==20){
                //medicina farmacia
                $prodBod= DB::connection('pgsql')->table('bodega.medicamentos as ite')
                ->select(DB::raw("CONCAT(ite.nombre, ' ', ite.concentra, ' ', ite.forma, ' ', ite.presentacion ) AS detalle"),'ite.cum')
                ->where('ite.coditem',$idprod)
                ->first();

                $texto="CUM ".$prodBod->cum. " -- LOTE ".$lote;
            }

            $bodega=DB::connection('pgsql')->table('bodega.bodega')
            ->where('idbodega',$bodega)->first();

            $nombrePDF="Kardex.pdf";
            $pdf=\PDF::loadView('reportes.inventario_kardex_farmacia',['data'=>$consultaPdf["resultado"],'bodega'=>$bodega,'fini'=>$ini,'ffin'=>$fin,'nombre_item'=>$prodBod,"texto"=>$texto]);

           
            $pdf->setPaper("A4", "landscape");

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
            Log::error('InventarioController => kardexItemLoteBodegaPdf => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return[
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ];
            
        }
    }


    public function pdfInventario($idbodega, $lugar, $tipo,$fini, $ffin){
       
        try{
            set_time_limit(0);
            ini_set("memory_limit",-1);
            ini_set('max_execution_time', 0);

            $consultaPdf=$this->buscarInventario($idbodega, $lugar, $tipo,$fini, $ffin,'N');
            // dd($consultaPdf);
            if($consultaPdf['error']==true){
                return[
                    'error'=>true,
                    'mensaje'=>'Ocurrió un error, intentelo más tarde'
                ];
            }  
            
            $bodega=DB::connection('pgsql')->table('bodega.bodega')
            ->where('idbodega',$idbodega)->first();

            $nombrePDF="Inventario.pdf";
            $pdf=\PDF::loadView('reportes.inventario_bodega',['data'=>$consultaPdf["resultado"],'bodega'=>$bodega,'fini'=>$fini,'ffin'=>$ffin,'tipo'=>$tipo]);

           
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
            Log::error('InventarioController => pdfInventario => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return[
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ];
            
        }
    }

    public function pdfInventarioEgreso($idbodega, $lugar, $tipo,$fini, $ffin){
       
        try{
            set_time_limit(0);
            ini_set("memory_limit",-1);
            ini_set('max_execution_time', 0);

            $consultaPdf=$this->buscarInventario($idbodega, $lugar, $tipo,$fini, $ffin, 'S');
            if($consultaPdf['error']==true){
                return[
                    'error'=>true,
                    'mensaje'=>'Ocurrió un error, intentelo más tarde'
                ];
            }
            // dd($consultaPdf);
            $bodega=DB::connection('pgsql')->table('bodega.bodega')
            ->where('idbodega',$idbodega)->first();

            $nombrePDF="InventarioEgreso.pdf";
            $pdf=\PDF::loadView('reportes.inventario_bodega_egreso',['data'=>$consultaPdf["resultado"],'bodega'=>$bodega,'fini'=>$fini,'ffin'=>$ffin,'tipo'=>$tipo]);

           
            $pdf->setPaper("A4", "landscape");

            // return $pdf->download($comprobante->descripcion.".pdf");
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
            Log::error('InventarioController => pdfInventarioEgreso => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return[
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ];
            
        }
    }
    

    public function pdfInventarioEgresoArea($idbodega, $lugar, $tipo,$fini, $ffin){
       
        try{
            $fecha1 = new DateTime($fini);
            $fecha2 = new DateTime($ffin);
            $mes_diferencias=$fecha1->diff($fecha2);
            if($mes_diferencias->m >=1){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'Solo se permite la busqueda por un mes'
                ]);
            }
            set_time_limit(0);
            ini_set("memory_limit",-1);
            ini_set('max_execution_time', 0);

            $egreso=Existencia::with('solicita','prodbod','detalle')
            ->whereHas('prodbod', function($q) use($idbodega){
                $q->where('idbodega',$idbodega);
            })
           
            ->where(function($c) use($idbodega, $tipo, $fini, $ffin) {
                if($idbodega==20 || $idbodega==21 || $idbodega==22  || $idbodega ==25 || $idbodega ==26 || $idbodega==27  || $idbodega ==28 || $idbodega ==29 || $idbodega ==6 || $idbodega==7){
                    $c->where('cod','EABFA'); //egreso bodega farmacia
                }else{
                    $c->where('cod','EABA');
                }

                if($tipo!="T"){
                    $c->whereBetween('fecha', [$fini, $ffin]);
                }
                
            })
            
            ->get();
            
            #agrupamos por area
            $lista_final_agrupada=[];
            foreach ($egreso as $key => $item){        
                if(!is_null($item->detalle->comprobante->nomarea)){        
                    if(!isset($lista_final_agrupada[$item->detalle->comprobante->nomarea->descripcion])) {
                        if(!is_null($item->detalle->comprobante->nomarea)){
                            $lista_final_agrupada[$item->detalle->comprobante->nomarea->descripcion]=array($item);
                        }else{
                            
                        }
                            
                
                    }else{
                        if(!is_null($item->detalle->comprobante->nomarea)){
                            array_push($lista_final_agrupada[$item->detalle->comprobante->nomarea->descripcion], $item);
                        }else{
                           
                        }
                        
                    }
                }else{
                   
                }
            }
 
           
            $bodega=DB::connection('pgsql')->table('bodega.bodega')
            ->where('idbodega',$idbodega)->first();

            $nombrePDF="InventarioEgreso.pdf";
            $pdf=\PDF::loadView('reportes.inventario_bodega_egreso_area',['listar'=>$lista_final_agrupada,'bodega'=>$bodega,'fini'=>$fini,'ffin'=>$ffin,'tipo'=>$tipo]);

           
            $pdf->setPaper("A4", "portrait");

            // return $pdf->download($comprobante->descripcion.".pdf");
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
            Log::error('InventarioController => pdfInventarioEgresoArea => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return[
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ];
            
        }
    }

    public function EgresoAreaFarmacia($fini, $ffin,$idbodega){
       
        try{
            if($idbodega==7 || $idbodega==22|| $idbodega==25 || $idbodega==26 || $idbodega==27 || $idbodega==28 || $idbodega==29){
              
                $compranteEgreso=DB::connection('pgsql')->table('bodega.comprobante as c')    
                ->leftJoin('bodega.area as a', 'a.id_area','c.area') 
                ->leftJoin('esq_catalogos.servicio as s', 's.id_servicio','c.id_servicio')    
                ->leftJoin('public.users as uin', 'uin.id','c.id_usuario_ingresa')
                ->leftJoin('esq_datos_personales.personal as dp', 'dp.idpersonal','uin.id_persona')
                ->leftJoin('public.users as udes', 'udes.id','c.id_usuario_aprueba')
                ->leftJoin('esq_datos_personales.personal as p_apr', 'p_apr.idpersonal','udes.id_persona')
                ->where(function($q) use($idbodega){
                    $q->where('c.idbodega',$idbodega);
                })
                ->where('c.estado','Activo')
                ->whereIn('c.codigo_old',['Entregado','EntregadoF'])
                ->whereBetween('c.fecha_aprobacion', [$fini, $ffin])
                ->select('c.tipoarea','dp.cedula',DB::raw("CONCAT(dp.apellido1, ' ', dp.apellido2, ' ', dp.nombre1, ' ', dp.nombre2) AS profes"),DB::raw("CONCAT(c.descripcion, ' ', c.secuencial) AS compr"),DB::raw("CONCAT(p_apr.apellido1, ' ', p_apr.apellido2, ' ', p_apr.nombre1, ' ', p_apr.nombre2) AS despachador"),'c.idcomprobante','a.descripcion as tipoarea2','s.nombre as tipoarea3')
                ->get();

                
                
                // foreach($compranteEgreso as $key=> $data){
                //     if($data->tipoarea2=="HOSPITALIZACION"){
                //         $compranteEgreso[$key]->area_ser=$data->tipoarea3;
                //     }else{
                //         $compranteEgreso[$key]->area_ser=$data->tipoarea2;
                //     }
                // }
    
                // $lista_final_agrupada=[];
                // foreach ($compranteEgreso as $key => $item){                
                //     if(!isset($lista_final_agrupada[$item->area_ser])) {
                //         $lista_final_agrupada[$item->area_ser]=array($item);
                
                //     }else{
                //         array_push($lista_final_agrupada[$item->area_ser], $item);
                //     }
                // }
                
                //EgresoAreaFarmaciaDetalle

                foreach($compranteEgreso as $key=> $data){
                    if($data->tipoarea2=="EMERGENCIA"){
                        $compranteEgreso[$key]->tipoarea="EMERGENCIA";
                    }elseif ($data->tipoarea2=="CONSULTA EXTERNA") {
                        $compranteEgreso[$key]->tipoarea="CONSULTA EXTERNA";
                    }else{
                        $compranteEgreso[$key]->tipoarea="HOSPITALIZACION";
                    }
                }

                $lista_final_agrupada=[];
                foreach ($compranteEgreso as $key => $item){                
                    if(!isset($lista_final_agrupada[$item->tipoarea])) {
                        $lista_final_agrupada[$item->tipoarea]=array($item);
                
                    }else{
                        array_push($lista_final_agrupada[$item->tipoarea], $item);
                    }
                }

                // dd($lista_final_agrupada);
                
            }elseif($idbodega==21){
                    
                $compranteEgreso=DB::connection('pgsql')->table('bodega.comprobante as c')    
                ->leftJoin('bodega.area as a', 'a.id_area','c.area') 
                ->leftJoin('esq_catalogos.servicio as s', 's.id_servicio','c.id_servicio')    
                ->leftJoin('public.users as uin', 'uin.id','c.id_usuario_ingresa')
                ->leftJoin('esq_datos_personales.personal as dp', 'dp.idpersonal','uin.id_persona')
                ->leftJoin('public.users as udes', 'udes.id','c.id_usuario_aprueba')
                ->leftJoin('esq_datos_personales.personal as p_apr', 'p_apr.idpersonal','udes.id_persona')
                ->where(function($q) use($idbodega){
                    $q->where('c.idbodega',$idbodega);
                })
                ->where('c.estado','Activo')
                ->whereIn('c.codigo_old',['Entregado','EntregadoF'])
                ->whereBetween('c.fecha_aprobacion', [$fini, $ffin])
                ->select('c.tipoarea','dp.cedula',DB::raw("CONCAT(dp.apellido1, ' ', dp.apellido2, ' ', dp.nombre1, ' ', dp.nombre2) AS profes"),DB::raw("CONCAT(c.descripcion, ' ', c.secuencial) AS compr"),DB::raw("CONCAT(p_apr.apellido1, ' ', p_apr.apellido2, ' ', p_apr.nombre1, ' ', p_apr.nombre2) AS despachador"),'c.idcomprobante','a.descripcion as tipoarea2','s.nombre as tipoarea3')
                ->get();
                
                foreach($compranteEgreso as $key=> $data){
                    if($data->tipoarea2=="HOSPITALIZACION"){
                        $compranteEgreso[$key]->area_ser=$data->tipoarea3;
                    }else{
                        $compranteEgreso[$key]->area_ser=$data->tipoarea2;
                    }
                }
    
                $lista_final_agrupada=[];
                foreach ($compranteEgreso as $key => $item){                
                    if(!isset($lista_final_agrupada[$item->area_ser])) {
                        $lista_final_agrupada[$item->area_ser]=array($item);
                
                    }else{
                        array_push($lista_final_agrupada[$item->area_ser], $item);
                    }
                }
            }else{
                    
                $compranteEgreso=DB::connection('pgsql')->table('bodega.comprobante as c')  
                ->leftJoin('bodega.area as a', 'a.id_area','c.area')       
                ->leftJoin('public.users as uin', 'uin.id','c.id_usuario_ingresa')
                ->leftJoin('esq_datos_personales.personal as dp', 'dp.idpersonal','uin.id_persona')
                ->leftJoin('public.users as udes', 'udes.id','c.id_usuario_aprueba')
                ->leftJoin('esq_datos_personales.personal as p_apr', 'p_apr.idpersonal','udes.id_persona')
                ->where(function($q) use($idbodega){
                    $q->where('c.idbodega',$idbodega);
                })
                ->where('c.estado','Activo')
                ->whereIn('c.codigo_old',['Entregado','EntregadoF'])
                ->whereBetween('c.fecha_aprobacion', [$fini, $ffin])
                ->select('c.tipoarea','dp.cedula',DB::raw("CONCAT(dp.apellido1, ' ', dp.apellido2, ' ', dp.nombre1, ' ', dp.nombre2) AS profes"),DB::raw("CONCAT(c.descripcion, ' ', c.secuencial) AS compr"),DB::raw("CONCAT(p_apr.apellido1, ' ', p_apr.apellido2, ' ', p_apr.nombre1, ' ', p_apr.nombre2) AS despachador"),'c.idcomprobante','a.descripcion as tipoarea2')
                ->get();
            

                // $lista_final_agrupada=[];
                // foreach ($compranteEgreso as $key => $item){                
                //     if(!isset($lista_final_agrupada[$item->tipoarea])) {
                //         $lista_final_agrupada[$item->tipoarea]=array($item);
                
                //     }else{
                //         array_push($lista_final_agrupada[$item->tipoarea], $item);
                //     }
                // }

                foreach($compranteEgreso as $key=> $data){
                    if($data->tipoarea2=="EMERGENCIA"){
                        $compranteEgreso[$key]->tipoarea="EMERGENCIA";
                    }elseif ($data->tipoarea2=="CONSULTA EXTERNA") {
                        $compranteEgreso[$key]->tipoarea="CONSULTA EXTERNA";
                    }else{
                        $compranteEgreso[$key]->tipoarea="HOSPITALIZACION";
                    }
                }

                $lista_final_agrupada=[];
                foreach ($compranteEgreso as $key => $item){                
                    if(!isset($lista_final_agrupada[$item->tipoarea])) {
                        $lista_final_agrupada[$item->tipoarea]=array($item);
                
                    }else{
                        array_push($lista_final_agrupada[$item->tipoarea], $item);
                    }
                }
                // dd($lista_final_agrupada);
            }
            // dd($lista_final_agrupada);
            return response()->json([
                'error'=>false,
                'resultado'=>$lista_final_agrupada
            ]);
          
           
        }catch (\Throwable $e) {
            Log::error('InventarioController => EgresoAreaFarmacia => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return[
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ];
            
        }
    }

    public function EgresoAreaFarmaciaDetalle($fini, $ffin,$idbodega, $area){
       
        try{
            if($idbodega==7 || $idbodega==22|| $idbodega==25 || $idbodega==26 || $idbodega==27 || $idbodega==28 || $idbodega==29){
              
                $compranteEgreso=DB::connection('pgsql')->table('bodega.comprobante as c')    
                ->leftJoin('bodega.area as a', 'a.id_area','c.area') 
                ->leftJoin('esq_catalogos.servicio as s', 's.id_servicio','c.id_servicio')    
                ->leftJoin('public.users as uin', 'uin.id','c.id_usuario_ingresa')
                ->leftJoin('esq_datos_personales.personal as dp', 'dp.idpersonal','uin.id_persona')
                ->leftJoin('public.users as udes', 'udes.id','c.id_usuario_aprueba')
                ->leftJoin('esq_datos_personales.personal as p_apr', 'p_apr.idpersonal','udes.id_persona')
                ->where(function($q) use($idbodega){
                    $q->where('c.idbodega',$idbodega);
                })
                ->where('c.estado','Activo')
                ->whereIn('c.codigo_old',['Entregado','EntregadoF'])
                ->whereBetween('c.fecha_aprobacion', [$fini, $ffin])
                // ->where(function($q1) use($area){
                //     $q1->where('a.descripcion',$area)
                //     ->orwhere('s.nombre',$area);
                // })

                ->where(function($q1) use($area){
                    if($area=="HOSPITALIZACION"){
                        $q1->where('a.descripcion','<>','EMERGENCIA')
                        ->where('a.descripcion','<>','CONSULTA EXTERNA');
                    }else{
                        $q1->where('a.descripcion',$area)
                         ->orwhere('s.nombre',$area);
                    }
                        
                })

                // ->where('a.descripcion',$area)
                ->select('c.tipoarea','dp.cedula',DB::raw("CONCAT(dp.apellido1, ' ', dp.apellido2, ' ', dp.nombre1, ' ', dp.nombre2) AS profes"),DB::raw("CONCAT(c.descripcion, ' ', c.secuencial) AS compr"),DB::raw("CONCAT(p_apr.apellido1, ' ', p_apr.apellido2, ' ', p_apr.nombre1, ' ', p_apr.nombre2) AS despachador"),'c.idcomprobante','a.descripcion as tipoarea2','s.nombre as tipoarea3','c.fecha_aprobacion','c.idcomprobante',DB::raw("CONCAT(c.descripcion, ' - ', c.secuencial) AS comprob"))
                ->get();

                // foreach($compranteEgreso as $key=> $data){
                //     if($data->tipoarea2=="HOSPITALIZACION"){
                //         $compranteEgreso[$key]->area_ser=$data->tipoarea3;
                //     }else{
                //         $compranteEgreso[$key]->area_ser=$data->tipoarea2;
                //     }
                // }
    
                // $lista_final_agrupada=[];
                // foreach ($compranteEgreso as $key => $item){                
                //     if(!isset($lista_final_agrupada[$item->area_ser])) {
                //         $lista_final_agrupada[$item->area_ser]=array($item);
                
                //     }else{
                //         array_push($lista_final_agrupada[$item->area_ser], $item);
                //     }
                // }

                foreach($compranteEgreso as $key=> $data){
                    if($data->tipoarea2=="EMERGENCIA"){
                        $compranteEgreso[$key]->tipoarea="EMERGENCIA";
                    }elseif ($data->tipoarea2=="CONSULTA EXTERNA") {
                        $compranteEgreso[$key]->tipoarea="CONSULTA EXTERNA";
                    }else{
                        $compranteEgreso[$key]->tipoarea="HOSPITALIZACION";
                    }
                }

                $lista_final_agrupada=[];
                foreach ($compranteEgreso as $key => $item){                
                    if(!isset($lista_final_agrupada[$item->tipoarea])) {
                        $lista_final_agrupada[$item->tipoarea]=array($item);
                
                    }else{
                        array_push($lista_final_agrupada[$item->tipoarea], $item);
                    }
                }
                // dd($lista_final_agrupada);
               
                
            }elseif($idbodega==21 ){
                    
                $compranteEgreso=DB::connection('pgsql')->table('bodega.comprobante as c')    
                ->leftJoin('bodega.area as a', 'a.id_area','c.area') 
                ->leftJoin('esq_catalogos.servicio as s', 's.id_servicio','c.id_servicio')    
                ->leftJoin('public.users as uin', 'uin.id','c.id_usuario_ingresa')
                ->leftJoin('esq_datos_personales.personal as dp', 'dp.idpersonal','uin.id_persona')
                ->leftJoin('public.users as udes', 'udes.id','c.id_usuario_aprueba')
                ->leftJoin('esq_datos_personales.personal as p_apr', 'p_apr.idpersonal','udes.id_persona')
                ->where(function($q) use($idbodega){
                    $q->where('c.idbodega',$idbodega);
                })
                ->where('c.estado','Activo')
                ->whereIn('c.codigo_old',['Entregado','EntregadoF'])
                ->whereBetween('c.fecha_aprobacion', [$fini, $ffin])
                ->select('c.tipoarea','dp.cedula',DB::raw("CONCAT(dp.apellido1, ' ', dp.apellido2, ' ', dp.nombre1, ' ', dp.nombre2) AS profes"),DB::raw("CONCAT(c.descripcion, ' ', c.secuencial) AS compr"),DB::raw("CONCAT(p_apr.apellido1, ' ', p_apr.apellido2, ' ', p_apr.nombre1, ' ', p_apr.nombre2) AS despachador"),'c.idcomprobante','a.descripcion as tipoarea2','s.nombre as tipoarea3','c.fecha_aprobacion','c.idcomprobante',DB::raw("CONCAT(c.descripcion, ' - ', c.secuencial) AS comprob"))
                ->get();
                
                foreach($compranteEgreso as $key=> $data){
                    if($data->tipoarea2=="HOSPITALIZACION"){
                        $compranteEgreso[$key]->area_ser=$data->tipoarea3;
                    }else{
                        $compranteEgreso[$key]->area_ser=$data->tipoarea2;
                    }
                }
    
                $lista_final_agrupada=[];
                foreach ($compranteEgreso as $key => $item){                
                    if(!isset($lista_final_agrupada[$item->area_ser])) {
                        $lista_final_agrupada[$item->area_ser]=array($item);
                
                    }else{
                        array_push($lista_final_agrupada[$item->area_ser], $item);
                    }
                }
            }else{
                    
                $compranteEgreso=DB::connection('pgsql')->table('bodega.comprobante as c')   
                ->leftJoin('bodega.area as a', 'a.id_area','c.area')   
                ->leftJoin('esq_catalogos.servicio as s', 's.id_servicio','c.id_servicio')     
                ->leftJoin('public.users as uin', 'uin.id','c.id_usuario_ingresa')
                ->leftJoin('esq_datos_personales.personal as dp', 'dp.idpersonal','uin.id_persona')
                ->leftJoin('public.users as udes', 'udes.id','c.id_usuario_aprueba')
                ->leftJoin('esq_datos_personales.personal as p_apr', 'p_apr.idpersonal','udes.id_persona')
                ->where(function($q) use($idbodega){
                    $q->where('c.idbodega',$idbodega);
                })
                ->where('c.estado','Activo')
                ->whereIn('c.codigo_old',['Entregado','EntregadoF'])
                ->whereBetween('c.fecha_aprobacion', [$fini, $ffin])
                // ->where('c.tipoarea',$area)
                ->where(function($q1) use($area){
                    if($area=="HOSPITALIZACION"){
                        $q1->where('a.descripcion','<>','EMERGENCIA')
                        ->where('a.descripcion','<>','CONSULTA EXTERNA');
                    }else{
                        $q1->where('a.descripcion',$area)
                        ->orwhere('s.nombre',$area);
                    }
                        
                })
                ->select('c.tipoarea','dp.cedula',DB::raw("CONCAT(dp.apellido1, ' ', dp.apellido2, ' ', dp.nombre1, ' ', dp.nombre2) AS profes"),DB::raw("CONCAT(c.descripcion, ' ', c.secuencial) AS compr"),DB::raw("CONCAT(p_apr.apellido1, ' ', p_apr.apellido2, ' ', p_apr.nombre1, ' ', p_apr.nombre2) AS despachador"),DB::raw("CONCAT(c.descripcion, ' - ', c.secuencial) AS comprob"),'c.fecha_aprobacion','c.idcomprobante','a.descripcion as tipoarea2')
                ->get();
                // dd("pdfInventarioEgresoAreaBodega");
            
                foreach($compranteEgreso as $key=> $data){
                    if($data->tipoarea2=="EMERGENCIA"){
                        $compranteEgreso[$key]->tipoarea="EMERGENCIA";
                    }elseif ($data->tipoarea2=="CONSULTA EXTERNA") {
                        $compranteEgreso[$key]->tipoarea="CONSULTA EXTERNA";
                    }else{
                        $compranteEgreso[$key]->tipoarea="HOSPITALIZACION";
                    }
                }

                $lista_final_agrupada=[];
                foreach ($compranteEgreso as $key => $item){                
                    if(!isset($lista_final_agrupada[$item->tipoarea])) {
                        $lista_final_agrupada[$item->tipoarea]=array($item);
                
                    }else{
                        array_push($lista_final_agrupada[$item->tipoarea], $item);
                    }
                }
                // $lista_final_agrupada=[];
                // foreach ($compranteEgreso as $key => $item){                
                //     if(!isset($lista_final_agrupada[$item->tipoarea])) {
                //         $lista_final_agrupada[$item->tipoarea]=array($item);
                
                //     }else{
                //         array_push($lista_final_agrupada[$item->tipoarea], $item);
                //     }
                // }
            }
           
            return response()->json([
                'error'=>false,
                'resultado'=>$lista_final_agrupada
            ]);
          
           
        }catch (\Throwable $e) {
            Log::error('InventarioController => EgresoAreaFarmaciaDetalle => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return[
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ];
            
        }
    }

    public function pdfEgresoAreaFarmaciaDetalle($fini, $ffin,$idbodega, $area){
       
        try{
            set_time_limit(0);
            ini_set("memory_limit",-1);
            ini_set('max_execution_time', 0);
            
            if($idbodega==7 || $idbodega==22|| $idbodega==25 || $idbodega==26 || $idbodega==27 || $idbodega==28 || $idbodega==29){
              
                $compranteEgreso=DB::connection('pgsql')->table('bodega.comprobante as c')    
                ->leftJoin('bodega.area as a', 'a.id_area','c.area') 
                ->leftJoin('esq_catalogos.servicio as s', 's.id_servicio','c.id_servicio')    
                ->leftJoin('public.users as uin', 'uin.id','c.id_usuario_ingresa')
                ->leftJoin('esq_datos_personales.personal as dp', 'dp.idpersonal','uin.id_persona')
                ->leftJoin('public.users as udes', 'udes.id','c.id_usuario_aprueba')
                ->leftJoin('esq_datos_personales.personal as p_apr', 'p_apr.idpersonal','udes.id_persona')
                ->where(function($q) use($idbodega){
                    $q->where('c.idbodega',$idbodega);
                })
                ->where('c.estado','Activo')
                ->whereIn('c.codigo_old',['Entregado','EntregadoF'])
                ->whereBetween('c.fecha_aprobacion', [$fini, $ffin])
                ->where(function($q1) use($area){
                    $q1->where('a.descripcion',$area)
                    ->orwhere('s.nombre',$area);
                })
                ->select('c.tipoarea','dp.cedula',DB::raw("CONCAT(dp.apellido1, ' ', dp.apellido2, ' ', dp.nombre1, ' ', dp.nombre2) AS profes"),DB::raw("CONCAT(c.descripcion, ' ', c.secuencial) AS compr"),DB::raw("CONCAT(p_apr.apellido1, ' ', p_apr.apellido2, ' ', p_apr.nombre1, ' ', p_apr.nombre2) AS despachador"),'c.idcomprobante','a.descripcion as tipoarea2','s.nombre as tipoarea3','c.fecha_aprobacion','c.idcomprobante',DB::raw("CONCAT(c.descripcion, ' - ', c.secuencial) AS comprob"))
                ->get();

                foreach($compranteEgreso as $key=> $data){
                    if($data->tipoarea2=="HOSPITALIZACION"){
                        $compranteEgreso[$key]->area_ser=$data->tipoarea3;
                    }else{
                        $compranteEgreso[$key]->area_ser=$data->tipoarea2;
                    }
                }
    
                $lista_final_agrupada=[];
                foreach ($compranteEgreso as $key => $item){                
                    if(!isset($lista_final_agrupada[$item->area_ser])) {
                        $lista_final_agrupada[$item->area_ser]=array($item);
                
                    }else{
                        array_push($lista_final_agrupada[$item->area_ser], $item);
                    }
                }
               
                
            }elseif($idbodega==21 ){
                    
                $compranteEgreso=DB::connection('pgsql')->table('bodega.comprobante as c')    
                ->leftJoin('bodega.area as a', 'a.id_area','c.area') 
                ->leftJoin('esq_catalogos.servicio as s', 's.id_servicio','c.id_servicio')    
                ->leftJoin('public.users as uin', 'uin.id','c.id_usuario_ingresa')
                ->leftJoin('esq_datos_personales.personal as dp', 'dp.idpersonal','uin.id_persona')
                ->leftJoin('public.users as udes', 'udes.id','c.id_usuario_aprueba')
                ->leftJoin('esq_datos_personales.personal as p_apr', 'p_apr.idpersonal','udes.id_persona')
                ->where(function($q) use($idbodega){
                    $q->where('c.idbodega',$idbodega);
                })
                ->where('c.estado','Activo')
                ->whereIn('c.codigo_old',['Entregado','EntregadoF'])
                ->whereBetween('c.fecha_aprobacion', [$fini, $ffin])
                ->select('c.tipoarea','dp.cedula',DB::raw("CONCAT(dp.apellido1, ' ', dp.apellido2, ' ', dp.nombre1, ' ', dp.nombre2) AS profes"),DB::raw("CONCAT(c.descripcion, ' ', c.secuencial) AS compr"),DB::raw("CONCAT(p_apr.apellido1, ' ', p_apr.apellido2, ' ', p_apr.nombre1, ' ', p_apr.nombre2) AS despachador"),'c.idcomprobante','a.descripcion as tipoarea2','s.nombre as tipoarea3','c.fecha_aprobacion','c.idcomprobante',DB::raw("CONCAT(c.descripcion, ' - ', c.secuencial) AS comprob"))
                ->get();
                
                foreach($compranteEgreso as $key=> $data){
                    if($data->tipoarea2=="HOSPITALIZACION"){
                        $compranteEgreso[$key]->area_ser=$data->tipoarea3;
                    }else{
                        $compranteEgreso[$key]->area_ser=$data->tipoarea2;
                    }
                }
    
                $lista_final_agrupada=[];
                foreach ($compranteEgreso as $key => $item){                
                    if(!isset($lista_final_agrupada[$item->area_ser])) {
                        $lista_final_agrupada[$item->area_ser]=array($item);
                
                    }else{
                        array_push($lista_final_agrupada[$item->area_ser], $item);
                    }
                }
            }else{
                $compranteEgreso=DB::connection('pgsql')->table('bodega.comprobante as c')        
                ->leftJoin('public.users as uin', 'uin.id','c.id_usuario_ingresa')
                ->leftJoin('esq_datos_personales.personal as dp', 'dp.idpersonal','uin.id_persona')
                ->leftJoin('public.users as udes', 'udes.id','c.id_usuario_aprueba')
                ->leftJoin('esq_datos_personales.personal as p_apr', 'p_apr.idpersonal','udes.id_persona')
                ->where(function($q) use($idbodega){
                    $q->where('c.idbodega',$idbodega);
                })
                ->where('c.estado','Activo')
                ->whereIn('c.codigo_old',['Entregado','EntregadoF'])
                ->whereBetween('c.fecha_aprobacion', [$fini, $ffin])
                ->where('c.tipoarea',$area)
                ->select('c.tipoarea','dp.cedula',DB::raw("CONCAT(dp.apellido1, ' ', dp.apellido2, ' ', dp.nombre1, ' ', dp.nombre2) AS profes"),DB::raw("CONCAT(c.descripcion, ' ', c.secuencial) AS compr"),DB::raw("CONCAT(p_apr.apellido1, ' ', p_apr.apellido2, ' ', p_apr.nombre1, ' ', p_apr.nombre2) AS despachador"),DB::raw("CONCAT(c.descripcion, ' - ', c.secuencial) AS comprob"),'c.fecha_aprobacion','c.idcomprobante')
                ->get();
            

                $lista_final_agrupada=[];
                foreach ($compranteEgreso as $key => $item){                
                    if(!isset($lista_final_agrupada[$item->tipoarea])) {
                        $lista_final_agrupada[$item->tipoarea]=array($item);
                
                    }else{
                        array_push($lista_final_agrupada[$item->tipoarea], $item);
                    }
                }
            }
            
            $bodega=DB::connection('pgsql')->table('bodega.bodega')
            ->where('idbodega',$idbodega)->first();

          
            $nombrePDF="EgresoArea.pdf";
            $pdf=\PDF::loadView('reportes.farmacia.egreso_area_pdf',['listar'=>$lista_final_agrupada,'bodega'=>$bodega,'fini'=>$fini,'ffin'=>$ffin, 'area'=>$area]);

           
            $pdf->setPaper("A4", "portrait");

            // return $pdf->download($comprobante->descripcion.".pdf");
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
            Log::error('InventarioController => pdfEgresoAreaFarmaciaDetalle => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return[
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ];
            
        }
    }

    public function pdfInventarioEgresoAreaBodega($fini, $ffin,$idbodega){
       
        try{
            set_time_limit(0);
            ini_set("memory_limit",-1);
            ini_set('max_execution_time', 0);
            
            if($idbodega==7 || $idbodega==22|| $idbodega==25 || $idbodega==26 || $idbodega==27 || $idbodega==28 || $idbodega==29 ){
              
                $compranteEgreso=DB::connection('pgsql')->table('bodega.comprobante as c')    
                ->leftJoin('bodega.area as a', 'a.id_area','c.area') 
                ->leftJoin('esq_catalogos.servicio as s', 's.id_servicio','c.id_servicio')    
                ->leftJoin('public.users as uin', 'uin.id','c.id_usuario_ingresa')
                ->leftJoin('esq_datos_personales.personal as dp', 'dp.idpersonal','uin.id_persona')
                ->leftJoin('public.users as udes', 'udes.id','c.id_usuario_aprueba')
                ->leftJoin('esq_datos_personales.personal as p_apr', 'p_apr.idpersonal','udes.id_persona')
                ->where(function($q) use($idbodega){
                    $q->where('c.idbodega',$idbodega);
                })
                ->where('c.estado','Activo')
                ->whereIn('c.codigo_old',['Entregado','EntregadoF'])
                ->whereBetween('c.fecha_aprobacion', [$fini, $ffin])
               
                ->select('c.tipoarea','dp.cedula',DB::raw("CONCAT(dp.apellido1, ' ', dp.apellido2, ' ', dp.nombre1, ' ', dp.nombre2) AS profes"),DB::raw("CONCAT(c.descripcion, ' ', c.secuencial) AS compr"),DB::raw("CONCAT(p_apr.apellido1, ' ', p_apr.apellido2, ' ', p_apr.nombre1, ' ', p_apr.nombre2) AS despachador"),'c.idcomprobante','a.descripcion as tipoarea2','s.nombre as tipoarea3','c.fecha_aprobacion','c.idcomprobante',DB::raw("CONCAT(c.descripcion, ' - ', c.secuencial) AS comprob"))
                ->get();

                foreach($compranteEgreso as $key=> $data){
                    if($data->tipoarea2=="EMERGENCIA"){
                        $compranteEgreso[$key]->tipoarea="EMERGENCIA";
                    }elseif ($data->tipoarea2=="CONSULTA EXTERNA") {
                        $compranteEgreso[$key]->tipoarea="CONSULTA EXTERNA";
                    }else{
                        $compranteEgreso[$key]->tipoarea="HOSPITALIZACION";
                    }
                }

                $lista_final_agrupada=[];
                foreach ($compranteEgreso as $key => $item){                
                    if(!isset($lista_final_agrupada[$item->tipoarea])) {
                        $lista_final_agrupada[$item->tipoarea]=array($item);
                
                    }else{
                        array_push($lista_final_agrupada[$item->tipoarea], $item);
                    }
                }


                // foreach($compranteEgreso as $key=> $data){
                //     if($data->tipoarea2=="HOSPITALIZACION"){
                //         $compranteEgreso[$key]->area_ser=$data->tipoarea3;
                //     }else{
                //         $compranteEgreso[$key]->area_ser=$data->tipoarea2;
                //     }
                // }
    
                // $lista_final_agrupada=[];
                // foreach ($compranteEgreso as $key => $item){                
                //     if(!isset($lista_final_agrupada[$item->area_ser])) {
                //         $lista_final_agrupada[$item->area_ser]=array($item);
                
                //     }else{
                //         array_push($lista_final_agrupada[$item->area_ser], $item);
                //     }
                // }
               
                
            }elseif($idbodega==21){
                    
                $compranteEgreso=DB::connection('pgsql')->table('bodega.comprobante as c')    
                ->leftJoin('bodega.area as a', 'a.id_area','c.area') 
                ->leftJoin('esq_catalogos.servicio as s', 's.id_servicio','c.id_servicio')    
                ->leftJoin('public.users as uin', 'uin.id','c.id_usuario_ingresa')
                ->leftJoin('esq_datos_personales.personal as dp', 'dp.idpersonal','uin.id_persona')
                ->leftJoin('public.users as udes', 'udes.id','c.id_usuario_aprueba')
                ->leftJoin('esq_datos_personales.personal as p_apr', 'p_apr.idpersonal','udes.id_persona')
                ->where(function($q) use($idbodega){
                    $q->where('c.idbodega',$idbodega);
                })
                ->where('c.estado','Activo')
                ->whereIn('c.codigo_old',['Entregado','EntregadoF'])
                ->whereBetween('c.fecha_aprobacion', [$fini, $ffin])
                ->select('c.tipoarea','dp.cedula',DB::raw("CONCAT(dp.apellido1, ' ', dp.apellido2, ' ', dp.nombre1, ' ', dp.nombre2) AS profes"),DB::raw("CONCAT(c.descripcion, ' ', c.secuencial) AS compr"),DB::raw("CONCAT(p_apr.apellido1, ' ', p_apr.apellido2, ' ', p_apr.nombre1, ' ', p_apr.nombre2) AS despachador"),'c.idcomprobante','a.descripcion as tipoarea2','s.nombre as tipoarea3','c.fecha_aprobacion','c.idcomprobante',DB::raw("CONCAT(c.descripcion, ' - ', c.secuencial) AS comprob"))
                ->get();
                
                foreach($compranteEgreso as $key=> $data){
                    if($data->tipoarea2=="HOSPITALIZACION"){
                        $compranteEgreso[$key]->area_ser=$data->tipoarea3;
                    }else{
                        $compranteEgreso[$key]->area_ser=$data->tipoarea2;
                    }
                }
    
                $lista_final_agrupada=[];
                foreach ($compranteEgreso as $key => $item){                
                    if(!isset($lista_final_agrupada[$item->area_ser])) {
                        $lista_final_agrupada[$item->area_ser]=array($item);
                
                    }else{
                        array_push($lista_final_agrupada[$item->area_ser], $item);
                    }
                }
            }else{
                $compranteEgreso=DB::connection('pgsql')->table('bodega.comprobante as c') 
                ->leftJoin('bodega.area as a', 'a.id_area','c.area')        
                ->leftJoin('public.users as uin', 'uin.id','c.id_usuario_ingresa')
                ->leftJoin('esq_datos_personales.personal as dp', 'dp.idpersonal','uin.id_persona')
                ->leftJoin('public.users as udes', 'udes.id','c.id_usuario_aprueba')
                ->leftJoin('esq_datos_personales.personal as p_apr', 'p_apr.idpersonal','udes.id_persona')
                ->where(function($q) use($idbodega){
                    $q->where('c.idbodega',$idbodega);
                })
                ->where('c.estado','Activo')
                ->whereIn('c.codigo_old',['Entregado','EntregadoF'])
                ->whereBetween('c.fecha_aprobacion', [$fini, $ffin])
                ->select('c.tipoarea','dp.cedula',DB::raw("CONCAT(dp.apellido1, ' ', dp.apellido2, ' ', dp.nombre1, ' ', dp.nombre2) AS profes"),DB::raw("CONCAT(c.descripcion, ' ', c.secuencial) AS compr"),DB::raw("CONCAT(p_apr.apellido1, ' ', p_apr.apellido2, ' ', p_apr.nombre1, ' ', p_apr.nombre2) AS despachador"),DB::raw("CONCAT(c.descripcion, ' - ', c.secuencial) AS comprob"),'c.fecha_aprobacion','c.idcomprobante','a.descripcion as tipoarea2')
                ->get();
                
                foreach($compranteEgreso as $key=> $data){
                    if($data->tipoarea2=="EMERGENCIA"){
                        $compranteEgreso[$key]->tipoarea="EMERGENCIA";
                    }elseif ($data->tipoarea2=="CONSULTA EXTERNA") {
                        $compranteEgreso[$key]->tipoarea="CONSULTA EXTERNA";
                    }else{
                        $compranteEgreso[$key]->tipoarea="HOSPITALIZACION";
                    }
                }

                $lista_final_agrupada=[];
                foreach ($compranteEgreso as $key => $item){                
                    if(!isset($lista_final_agrupada[$item->tipoarea])) {
                        $lista_final_agrupada[$item->tipoarea]=array($item);
                
                    }else{
                        array_push($lista_final_agrupada[$item->tipoarea], $item);
                    }
                }

                // $lista_final_agrupada=[];
                // foreach ($compranteEgreso as $key => $item){                
                //     if(!isset($lista_final_agrupada[$item->tipoarea])) {
                //         $lista_final_agrupada[$item->tipoarea]=array($item);
                
                //     }else{
                //         array_push($lista_final_agrupada[$item->tipoarea], $item);
                //     }
                // }
            }
           
            $bodega=DB::connection('pgsql')->table('bodega.bodega')
            ->where('idbodega',$idbodega)->first();

          
            $nombrePDF="EgresoArea.pdf";
            $pdf=\PDF::loadView('reportes.farmacia.egreso_area_bodega',['listar'=>$lista_final_agrupada,'bodega'=>$bodega,'fini'=>$fini,'ffin'=>$ffin]);

           
            $pdf->setPaper("A4", "portrait");

            // return $pdf->download($comprobante->descripcion.".pdf");
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
            Log::error('InventarioController => pdfInventarioEgresoAreaBodega => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return[
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ];
            
        }
    }

   
  

}
