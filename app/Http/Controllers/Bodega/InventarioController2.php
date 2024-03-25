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
use Storage;
use SplFileInfo;

class InventarioController2 extends Controller
{
    
    public function inventarioVista(){

        $bodega= DB::connection('pgsql')->table('bodega.bodega')
        ->where('idtipobod',2)
        ->whereIn('idbodega',[6,7,20,21,22,25,26,27,28,29,])
        ->where('estado',1)
        ->get();

        return view('gestion_bodega.inventario2',[
            "bodega"=>$bodega
        ]);
    }

    public function buscarInventario($idbodega, $lugar, $opcion){
        try{

            if($idbodega==3 || $idbodega==4 || $idbodega==5 || $idbodega==9 || $idbodega==10 || $idbodega==30){
                if($opcion=="Individual"){
                    return [
                        'error'=>true,
                        'mensaje'=>'No se puede buscar en esta bodega con la opcion individual'
                    ];
                }
            }           

            if($lugar=="FARMACIA"){
                if($idbodega==6 || $idbodega==20 ){
                    $medicamentos= DB::connection('pgsql')->table('bodega.medicamentos as med')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','med.coditem')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('proxbode.idbodega',$idbodega)
                    
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion,' [', med.coditem,']',' [', proxbode.idbodprod,']') AS detalle"), DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion) AS detalle1"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','med.cum as codigo_item','proxbode.tipoprod')
                    ->get();                

                }elseif($idbodega==7 || $idbodega==21){
                    
                    $medicamentos= DB::connection('pgsql')->table('bodega.insumo as insu')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','insu.codinsumo')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('proxbode.idbodega',$idbodega)
                   
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(insu.insumo,' [', insu.codinsumo,']',' [', proxbode.idbodprod,']') AS detalle"), 'insu.insumo AS detalle1','proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','insu.cudim as codigo_item') 
                    // ->DISTINCT('insu.cudim','lot.lote')
                    ->get();


                }elseif($idbodega==22 || $idbodega==27){
                
                    $medicamentos= DB::connection('pgsql')->table('bodega.laboratorio as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.id')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('proxbode.tipoprod',5) //bodega materiales
                    ->where('proxbode.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.id,']',' [', proxbode.idbodprod,']') AS detalle"), DB::raw("CONCAT(item.descri,' ', item.presen) AS detalle1"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codigo as codigo_item')
                    ->get();                   


                }elseif($idbodega==25 || $idbodega==28 ){
                    
                    $medicamentos= DB::connection('pgsql')->table('bodega.laboratorio as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.id')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('proxbode.tipoprod',10) //bodega reactivo
                    ->where('proxbode.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.id,']',' [', proxbode.idbodprod,']') AS detalle"), DB::raw("CONCAT(item.descri,' ', item.presen) AS detalle1"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codigo as codigo_item')
                    ->get();
                }elseif($idbodega==26 || $idbodega==29){
                    $medicamentos= DB::connection('pgsql')->table('bodega.laboratorio as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.id')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('proxbode.tipoprod',11) //bodega micro
                    ->where('proxbode.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.id,']',' [', proxbode.idbodprod,']') AS detalle"), DB::raw("CONCAT(item.descri,' ', item.presen) AS detalle1"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codigo as codigo_item')
                    ->get();
                }
            }else{
                if($idbodega==1){
                
                    $medicamentos= DB::connection('pgsql')->table('bodega.medicamentos as med')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','med.coditem')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 2
                    ->where('proxbode.tipoprod',1) //MEDICAMENTOS
                    ->where('proxbode.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion,' [', med.coditem,']',' [', proxbode.idbodprod,']') AS detalle"), DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion) AS detalle1"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','med.cum as codigo_item')
                    ->get();   
                    
                  
                }elseif($idbodega==2){
                    
                    $medicamentos= DB::connection('pgsql')->table('bodega.insumo as insu')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','insu.codinsumo')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',2) //INSUMPS
                    ->where('proxbode.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(insu.insumo,' [', insu.codinsumo,']',' [', proxbode.idbodprod,']') AS detalle"), 'insu.insumo AS detalle1','proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','insu.cudim as codigo_item') 
                    // ->DISTINCT('insu.cudim','lot.lote')
                    ->get();


                }elseif($idbodega==8 || $idbodega==19){ //materiales
                    $medicamentos= DB::connection('pgsql')->table('bodega.laboratorio as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.id')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',5) //bodega materiales
                    ->where('proxbode.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.id,']',' [', proxbode.idbodprod,']') AS detalle"), DB::raw("CONCAT(item.descri,' ', item.presen) AS detalle1"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codigo as codigo_item')
                    ->get();
                }elseif($idbodega==13 || $idbodega==23){// reactivo

                    $medicamentos= DB::connection('pgsql')->table('bodega.laboratorio as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.id')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',10) //bodega reactivo
                    ->where('proxbode.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.id,']',' [', proxbode.idbodprod,']') AS detalle"), DB::raw("CONCAT(item.descri,' ', item.presen) AS detalle1"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codigo as codigo_item')
                    ->get();


                }elseif($idbodega==14 || $idbodega==24){ //bodega micro
                    $medicamentos= DB::connection('pgsql')->table('bodega.laboratorio as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.id')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',11) //bodega micro
                    ->where('proxbode.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen,' [', item.id,']',' [', proxbode.idbodprod,']') AS detalle"), DB::raw("CONCAT(item.descri,' ', item.presen) AS detalle1"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codigo as codigo_item')
                    ->get();
                }else if($idbodega==17){
                
                    $medicamentos= DB::connection('pgsql')->table('bodega.medicamentos as med')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','med.coditem')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 2
                    ->where('proxbode.tipoprod',1) //MEDICAMENTOS
                    // ->where('med.activo','VERDADERO')
                    ->where('proxbode.idbodega',$idbodega)
                    // ->where('med.es_dialisis','S')
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion,' [', med.coditem,']',' [', proxbode.idbodprod,']') AS detalle"), DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion) AS detalle1"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','med.cum as codigo_item')
                    ->get();   
                              
                }elseif($idbodega==18){
                    
                    $medicamentos= DB::connection('pgsql')->table('bodega.insumo as insu')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','insu.codinsumo')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('bodega.idtipobod',1) // BODEGA 1
                    ->where('proxbode.tipoprod',2) //INSUMPS
                    // ->where('insu.activo','VERDADERO')
                    ->where('proxbode.idbodega',$idbodega)
                    // ->where('insu.es_dialisis', 'S')
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(insu.insumo,' [', insu.codinsumo,']',' [', proxbode.idbodprod,']') AS detalle"), 'insu.insumo AS detalle1','proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','insu.cudim as codigo_item') 
                    // ->DISTINCT('insu.cudim','lot.lote')
                    ->get();


                }
               
                
            }
           
          
            $fecha_Actual=date('Y-m-d');
            $mas3Mese=date("Y-m-d",strtotime($fecha_Actual."+ 3 months"));
            
            return [
                'error'=>false,
                'resultado'=>$medicamentos,
                'meses'=>$mas3Mese
            ];
               
        }catch (\Throwable $e) {
            Log::error('InventarioController2 => buscarInventario => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function buscarInventarioIndLote($idbodega, $lugar, $opcion, $ini, $fin , $filtro_fecha){
        try{
            
            if($lugar=="FARMACIA"){
                if($idbodega==6 || $idbodega==20 ){
                    $medicamentos= DB::connection('pgsql')->table('bodega.medicamentos as med')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','med.coditem')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('proxbode.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca', DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion) AS detalle"),'med.cum as codigo_item',DB::raw('sum("existencia") as existencia'),'med.coditem as iditem','proxbode.idbodprod',DB::raw("CONCAT(lot.lote,'&_',med.coditem) as lote_ag"))
                    ->groupBy('lot.lote','lot.fcaduca','detalle','existencia','codigo_item','iditem','proxbode.idbodprod')
                    ->orderBy('detalle','desc')
                    ->get();  
                                      
                }elseif($idbodega==7 || $idbodega==21){
                    
                    $medicamentos= DB::connection('pgsql')->table('bodega.insumo as insu')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','insu.codinsumo')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where(function($r) use($idbodega) {
                        if($idbodega==21){//farmacia insumo dialisis
                            $r->whereIn('proxbode.idbodega', [21]);
                        }else{
                            $r->where('proxbode.idbodega',$idbodega);
                        }
                    })
                    
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan','insu.insumo AS detalle','insu.cudim as codigo_item',DB::raw('sum("existencia") as existencia'),'insu.codinsumo as iditem','proxbode.idbodprod',DB::raw("CONCAT(lot.lote,'&_',insu.cudim) as lote_ag"))
                   
                    ->groupBy('lot.lote','lot.fcaduca','lot.regsan','detalle','existencia','codigo_item','iditem','proxbode.idbodprod')
                    ->orderBy('detalle','asc')
                    ->get();
                    // dd("Dd");
                 
                }elseif($idbodega==22 || $idbodega==27){
                
                    $medicamentos= DB::connection('pgsql')->table('bodega.laboratorio as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.id')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    //->where('proxbode.tipoprod',5) //bodega materiales
                    ->where('proxbode.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen) AS detalle"),'item.codigo as codigo_item',DB::raw('sum("existencia") as existencia'),'item.id as iditem','proxbode.idbodprod',DB::raw("CONCAT(lot.lote,'&_',item.id) as lote_ag"))
                    ->groupBy('lot.lote','lot.fcaduca','lot.regsan','detalle','existencia','codigo_item','iditem','proxbode.idbodprod')
                    ->orderBy('detalle','asc')
                    ->get();                   


                }elseif($idbodega==25 || $idbodega==28 ){
                    
                    $medicamentos= DB::connection('pgsql')->table('bodega.laboratorio as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.id')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('proxbode.tipoprod',10) //bodega reactivo
                    ->where('proxbode.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen) AS detalle"),'item.codigo as codigo_item',DB::raw('sum("existencia") as existencia'),'item.id as iditem','proxbode.idbodprod',DB::raw("CONCAT(lot.lote,'&_',item.id) as lote_ag"))
                    ->groupBy('lot.lote','lot.fcaduca','lot.regsan','detalle','existencia','codigo_item','iditem','proxbode.idbodprod')
                    ->orderBy('detalle','asc')
                    ->get();                   
                }elseif($idbodega==26 || $idbodega==29){
                    $medicamentos= DB::connection('pgsql')->table('bodega.laboratorio as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.id')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('proxbode.tipoprod',11) //bodega micro
                    ->where('proxbode.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen) AS detalle"),'item.codigo as codigo_item',DB::raw('sum("existencia") as existencia'),'item.id as iditem','proxbode.idbodprod',DB::raw("CONCAT(lot.lote,'&_',item.id) as lote_ag"))
                    ->groupBy('lot.lote','lot.fcaduca','lot.regsan','detalle','existencia','codigo_item','iditem','proxbode.idbodprod')
                    ->orderBy('detalle','asc')
                    ->get();                   
                }
               
              
            }else{
                
                if($idbodega==1 || $idbodega==17){

                    $medicamentos= DB::connection('pgsql')->table('bodega.medicamentos as med')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','med.coditem')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('proxbode.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca', DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion) AS detalle"),'med.cum as codigo_item',DB::raw('sum("existencia") as existencia'),'med.coditem as iditem','proxbode.idbodprod',DB::raw("CONCAT(lot.lote,'&_',med.coditem) as lote_ag"))
                    ->groupBy('lot.lote','lot.fcaduca','detalle','existencia','codigo_item','iditem','proxbode.idbodprod')
                    ->orderBy('detalle','asc')
                    ->get();     
                                       
                    
                }elseif($idbodega==2 || $idbodega==18){
                    
                    $medicamentos= DB::connection('pgsql')->table('bodega.insumo as insu')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','insu.codinsumo')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('proxbode.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan','insu.insumo AS detalle','insu.cudim as codigo_item',DB::raw('sum("existencia") as existencia'),'insu.codinsumo as iditem','proxbode.idbodprod',DB::raw("CONCAT(lot.lote,'&_',insu.codinsumo) as lote_ag"))
                    ->groupBy('lot.lote','lot.fcaduca','lot.regsan','detalle','existencia','codigo_item','iditem','proxbode.idbodprod')
                    ->orderBy('detalle','asc')
                    ->get();

                }elseif($idbodega==8 || $idbodega==19){ //materiales
                    
                    $medicamentos= DB::connection('pgsql')->table('bodega.laboratorio as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.id')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('proxbode.tipoprod',5) //bodega materiales
                    ->where('proxbode.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen) AS detalle"),'item.codigo as codigo_item',DB::raw('sum("existencia") as existencia'),'item.id as iditem','proxbode.idbodprod',DB::raw("CONCAT(lot.lote,'&_',item.id) as lote_ag"))
                    ->groupBy('lot.lote','lot.fcaduca','lot.regsan','detalle','existencia','codigo_item','iditem','proxbode.idbodprod')
                    ->orderBy('detalle','asc')
                    ->get();     

                }elseif($idbodega==13 || $idbodega==23){// reactivo

                    $medicamentos= DB::connection('pgsql')->table('bodega.laboratorio as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.id')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('proxbode.tipoprod',10) //bodega reactivo
                    ->where('proxbode.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen) AS detalle"),'item.codigo as codigo_item',DB::raw('sum("existencia") as existencia'),'item.id as iditem','proxbode.idbodprod',DB::raw("CONCAT(lot.lote,'&_',item.id) as lote_ag"))
                    ->groupBy('lot.lote','lot.fcaduca','lot.regsan','detalle','existencia','codigo_item','iditem','proxbode.idbodprod')
                    ->orderBy('detalle','asc')
                    ->get(); 


                }elseif($idbodega==14 || $idbodega==24){ //bodega micro
                    $medicamentos= DB::connection('pgsql')->table('bodega.laboratorio as item')
                    ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','item.id')
                    ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                    ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                    ->where('proxbode.tipoprod',11) //bodega micro
                    ->where('proxbode.idbodega',$idbodega)
                    ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(item.descri,' ', item.presen) AS detalle"),'item.codigo as codigo_item',DB::raw('sum("existencia") as existencia'),'item.id as iditem','proxbode.idbodprod',DB::raw("CONCAT(lot.lote,'&_',item.id) as lote_ag"))
                    ->groupBy('lot.lote','lot.fcaduca','lot.regsan','detalle','existencia','codigo_item','iditem','proxbode.idbodprod')
                    ->orderBy('detalle','asc')
                    ->get(); 
                    
                }
                   
                    
                
            }

            if(sizeof($medicamentos)==0){
                return [
                    'error'=>true,
                    'mensaje'=>'No se encontro informacion'
                ];
            }
            
            if($filtro_fecha!="T"){
                 foreach($medicamentos as $key => $data){
                    $cantidad= DB::connection('pgsql')->table('bodega.prodxbod')
                    ->leftJoin('bodega.existencia as ex', 'ex.idbodprod','prodxbod.idbodprod')
                    ->select(DB::raw('sum("resta") as egreso'),DB::raw('sum("suma") as ingreso'),DB::raw('sum("precio") as precio_ag'),
                    DB::raw('count(*) as cantidadExist'),'prodxbod.idprod')
                    ->groupby('prodxbod.idprod')
                    ->where('prodxbod.idprod',$data->iditem)
                    ->where('prodxbod.idbodprod',$data->idbodprod)
                    ->where('prodxbod.idbodega',$idbodega)
                    ->where(function($q) use($ini, $fin) {
                        // $q->whereDate('ex.fecha_hora', '>=', $ini)
                        $q->whereDate('ex.fecha_hora', '<=', $fin);
                    })
                    ->first();
                    
                    if(!is_null($cantidad)){                       
                        $totalItem=$cantidad->ingreso - $cantidad->egreso;                   
                    }else{
                        $totalItem=0;
                    }

                    $medicamentos[$key]->total=$totalItem;
                   
                }

                  
                    
                //     $cantidad= DB::connection('pgsql')->table('bodega.prodxbod')
                //     ->leftJoin('bodega.existencia as ex', 'ex.idbodprod','prodxbod.idbodprod')
                //     ->select(DB::raw('sum("resta") as egreso'),DB::raw('sum("suma") as ingreso'),DB::raw('sum("precio") as precio_ag'),
                //     DB::raw('count(*) as cantidadExist'),'prodxbod.idprod')
                //     ->groupby('prodxbod.idprod')
                //     ->where('prodxbod.idprod',$data->iditem)
                //     ->where('prodxbod.idbodega',$idbodega)
                //     ->where('prodxbod.idbodprod',$data->idbodprod)
                //     ->where(function($q) use($ini, $fin) {
                //         $q->whereDate('ex.fecha_hora', '<=', $fin);
                //     })
                //     ->first();
                
                //     if(!is_null($cantidad)){
                    
                //         $totalItem=$cantidad->ingreso - $cantidad->egreso;
                //         $medicamentos[$key]->ingreso=$cantidad->ingreso;
                //         $medicamentos[$key]->egreso=$cantidad->egreso;
                    
                //     }else{
                //         $totalItem=0;
                        
                //     }
                    
                
                //     $medicamentos[$key]->total=$totalItem;
                // } 
            }
            #agrupamos por lote
            // $lista_final_agrupada=[];
            // foreach ($medicamentos as $key => $item){                
            //     if(!isset($lista_final_agrupada[$item->lote])) {
            //         $lista_final_agrupada[$item->lote]=array($item);
            
            //     }else{
            //         array_push($lista_final_agrupada[$item->lote], $item);
            //     }
            // }

            $lista_final_agrupada=[];
            foreach ($medicamentos as $key => $item){                
                if(!isset($lista_final_agrupada[$item->lote_ag])) {
                    $lista_final_agrupada[$item->lote_ag]=array($item);
            
                }else{
                    array_push($lista_final_agrupada[$item->lote_ag], $item);
                }
            }
            // dd($lista_final_agrupada);
            $medicamentos=$lista_final_agrupada;
                  
            return [
                'error'=>false,
                'resultado'=>$medicamentos
             
            ];
               
        }catch (\Throwable $e) {
            Log::error('InventarioController2 => buscarInventarioIndLote => mensaje => '.$e->getMessage());
            return [
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ];
            
        }
    }

    public function pdfInventarioIndividualFarmacia($idbodega, $filtro, $ini, $fin, $filt_fecha){
        try{
            set_time_limit(0);
            ini_set("memory_limit",-1);
            ini_set('max_execution_time', 0);
            
          
            $consultaPdf=$this->buscarInventarioIndLote($idbodega, "FARMACIA", "Individual", $ini, $fin, $filt_fecha);
      
            if($consultaPdf['error']==true){
                return[
                    'error'=>true,
                    'mensaje'=>'Ocurrió un error, intentelo más tarde'
                ];
            }  
            $bodega=DB::connection('pgsql')->table('bodega.bodega')
            ->where('idbodega',$idbodega)->first();

            $nombrePDF="InventarioIndividual.pdf";
            $pdf=\PDF::loadView('reportes.farmacia.inventario_bodega_individual',['data'=>$consultaPdf["resultado"],'bodega'=>$bodega,'filtro'=>$filtro, "filt_fecha"=>$filt_fecha, "ini"=>$ini, "fin"=>$fin]);

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
            Log::error('InventarioController => pdfInventarioIndividualFarmacia => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return[
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ];
            
        }
    }

    public function pdfInventarioIndividual($idbodega, $filtro, $ini, $fin, $filt_fecha){
        try{
            set_time_limit(0);
            ini_set("memory_limit",-1);
            ini_set('max_execution_time', 0);

            // $consultaPdf=$this->buscarInventario($idbodega, "BODEGA", "Individual");
            $consultaPdf=$this->buscarInventarioIndLote($idbodega, "BODEGA", "Individual", $ini, $fin, $filt_fecha);
           
            if($consultaPdf['error']==true){
                return[
                    'error'=>true,
                    'mensaje'=>'Ocurrió un error, intentelo más tarde'
                ];
            }  
            
            $bodega=DB::connection('pgsql')->table('bodega.bodega')
            ->where('idbodega',$idbodega)->first();

            $nombrePDF="InventarioIndividual.pdf";
           
            $pdf=\PDF::loadView('reportes.farmacia.inventario_bodega_individual',['data'=>$consultaPdf["resultado"],'bodega'=>$bodega,'filtro'=>$filtro, "filt_fecha"=>$filt_fecha, "ini"=>$ini, "fin"=>$fin]);

           
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
            Log::error('InventarioController => pdfInventarioIndividual => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return[
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ];
            
        }
    }

    
    
    public function actualizaExistencia(Request $request){
       
      
        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{

                foreach($request->array_bodprod as $key=> $idbodprod){
                    $actualizaExistencia=ProductoBodega::where('idbodprod', $idbodprod)->first();
                    $actualizaExistencia->existencia=$request->valor_lote[$key];
                    $actualizaExistencia->actualiza_enlinea="Si";
                    $actualizaExistencia->save();
                
                  
                }        

                return (['mensaje'=>'Informacion actualizada exitosamente','error'=>false]);
            } catch (\Throwable $e) {
                DB::connection('pgsql')->rollback();
                Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
                return (['mensaje'=>'Ocurrió un error,intentelo más tarde','error'=>true]); 
            }
        });
        return ($transaction);
    }

    public function verProdBodega($bodega, $prodbod){
       
        try{
            $detalle=ProductoBodega::with('lote')->where('idbodprod',$prodbod)->first();
            return (['resultado'=>$detalle,'error'=>false]);
        } catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return (['mensaje'=>'Ocurrió un error,intentelo más tarde','error'=>true]); 
        }
       
    }

    public function actualizaDetallePB(Request $request){
       
        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{
                $actualizaPBLote=LoteProducto::where('idbodp',$request->id_prod_bod_actualizar)->first();
                if(!is_null($actualizaPBLote)){
                    $actualizaPBLote->lote=$request->lote_actualizar;
                    $actualizaPBLote->felabora=$request->felab_actualizar;
                    $actualizaPBLote->fcaduca=$request->fcad_actualizar;
                    $actualizaPBLote->save();
                }

                $actualizaPrecio=ProductoBodega::find($request->id_prod_bod_actualizar);
                if(!is_null($actualizaPrecio)){
                    $actualizaPrecio->precio=number_format(($request->precio_actualizar),4,'.', '');
                    $actualizaPrecio->save();
                }

                $actualizaExistencia=Existencia::where('idbodprod',$request->id_prod_bod_actualizar)->first();
                if(!is_null($actualizaExistencia)){
                    $actualizaExistencia->lote=$request->lote_actualizar;
                    $actualizaExistencia->fecha_elaboracion=$request->felab_actualizar;
                    $actualizaExistencia->fecha_caducidad=$request->fcad_actualizar;
                    $actualizaExistencia->save();
                }

                return (['mensaje'=>'Informacion actualizada exitosamente','error'=>false]);
            } catch (\Throwable $e) {
                DB::connection('pgsql')->rollback();
                Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
                return (['mensaje'=>'Ocurrió un error,intentelo más tarde','error'=>true]); 
            }
        });
        return ($transaction);
    }
  

}
