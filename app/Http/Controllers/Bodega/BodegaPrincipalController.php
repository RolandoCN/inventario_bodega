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
use App\Models\Bodega\Laboratorio;
use App\Models\Bodega\ComprobanteOld; 
use App\Models\Bodega\DetalleComprobanteOld;
use App\Models\Bodega\FarmLaboratorio;
use App\Models\Bodega\PedidoBodegaGral;
use App\Models\Bodega\Bodega;
use App\Models\Bodega\Proteccion;
use App\Models\Bodega\BodegaUsuario;
use Storage;
use SplFileInfo;
use App\Http\Controllers\Bodega\PedidoBodegaController;


class BodegaPrincipalController extends Controller
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

       
        $persona= DB::table('persona')
        ->where('estado','A')
        ->get();

        $bodega= DB::table('bodega')
        ->where('estado','A')
        ->get();

        $usuario= DB::table('inventario.persona')
        ->where('estado',1)
        ->get();

        return view('gestion_bodega.ingreso_bodega_general',[
            "persona"=>$persona,
            "bodega"=>$bodega,
            "usuario"=>$usuario
        ]);
    }

    public function buscarMedicDialisis($text){
        try{
           
            $medicamentos= DB::connection('pgsql')->table('bodega.medicamentos')
            ->where(function($c)use($text) {
                $c->where('nombre', 'ilike', '%'.$text.'%');
            })
          
            ->select('coditem as codigo_item',DB::raw("CONCAT(nombre,' - ', concentra,' - ', forma,' - ', presentacion) AS detalle"), 'cum as codi')
            // ->where('activo','VERDADERO')
            ->get();
            
            return response()->json([
                'error'=>false,
                'resultado'=>$medicamentos
            ]);
               
        }catch (\Throwable $e) {
            Log::error('BodegaPrincipalController => listaMedicinasEspecialidad => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    
    public function buscarLabDialisis($text){
        try{
            
            $insumos= DB::connection('pgsql')->table('bodega.items')
            ->where(function($c)use($text) {
                $c->where('descri', 'ilike', '%'.$text.'%');
            })
           
            ->select('codi_it as codigo_item','descri as detalle', 'codigo as codi')
            ->where('idbodega',19)
            ->orderby('detalle','asc')
            ->get();
            
            return response()->json([
                'error'=>false,
                'resultado'=>$insumos
            ]);
               
        }catch (\Throwable $e) {
            Log::error('BodegaPrincipalController => buscarLabDialisis => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function buscarProteccion($text){
        try{
            
            $data= DB::connection('pgsql')->table('bodega.proteccion')
            ->where(function($c)use($text) {
                $c->where('descri', 'ilike', '%'.$text.'%');
            })
           
            ->select('id as codigo_item','descri as detalle', 'codigo as codi')
            ->where('idbodega',30)
            ->orderby('detalle','asc')
            ->get();
            
            return response()->json([
                'error'=>false,
                'resultado'=>$data
            ]);
               
        }catch (\Throwable $e) {
            Log::error('BodegaPrincipalController => buscarProteccion => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }


    public function buscarMedicamentos($text){
        try{
           
            $medicamentos= DB::connection('pgsql')->table('bodega.medicamentos')
            ->where(function($c)use($text) {
                $c->where('nombre', 'ilike', '%'.$text.'%');
            })

            ->select('coditem as codigo_item',DB::raw("CONCAT(nombre,' - ', concentra,' - ', forma,' - ', presentacion) AS detalle"), 'cum as codi')
          
            ->get();
            
            return response()->json([
                'error'=>false,
                'resultado'=>$medicamentos
            ]);
               
        }catch (\Throwable $e) {
            Log::error('BodegaPrincipalController => listaMedicinasEspecialidad => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function buscarInsumos($text){
        try{
           
            $insumos= DB::connection('pgsql')->table('bodega.insumo')
            ->where(function($c)use($text) {
                $c->where('insumo', 'ilike', '%'.$text.'%')
                ->orwhere('descrip', 'ilike', '%'.$text.'%');
            })
            ->select('codinsumo as codigo_item','insumo as detalle', 'cudim as codi')
            ->orderby('detalle','asc')
            ->get();
            
            return response()->json([
                'error'=>false,
                'resultado'=>$insumos
            ]);
               
        }catch (\Throwable $e) {
            Log::error('BodegaPrincipalController => buscarInsumos => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function buscarInsumosDialisis($text){
        try{
           
            $insumos= DB::connection('pgsql')->table('bodega.insumo')
            ->where(function($c)use($text) {
                $c->where('insumo', 'ilike', '%'.$text.'%');
            })
            ->select('codinsumo as codigo_item','insumo as detalle', 'cudim as codi')
            
            ->orderby('detalle','asc')
            ->get();
            
            return response()->json([
                'error'=>false,
                'resultado'=>$insumos
            ]);
               
        }catch (\Throwable $e) {
            Log::error('BodegaPrincipalController => buscarInsumosDialisis => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function buscarLaboratorioMat($text){
        try{
            
            $insumos= DB::connection('pgsql')->table('bodega.laboratorio')
            ->where(function($c)use($text) {
                $c->where('descri', 'ilike', '%'.$text.'%');
            })
            ->select('id as codigo_item','descri as detalle', 'codigo as codi')
            ->where('idbodega',8)
            ->orderby('detalle','asc')
            ->get();
            
            return response()->json([
                'error'=>false,
                'resultado'=>$insumos
            ]);
               
        }catch (\Throwable $e) {
            Log::error('BodegaPrincipalController => buscarLaboratorioMat => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function buscarLaboratorioReact($text){
        try{
           
            $insumos= DB::connection('pgsql')->table('bodega.laboratorio')
            ->where(function($c)use($text) {
                $c->where('descri', 'ilike', '%'.$text.'%');
            })
           
            ->select('id as codigo_item','descri as detalle', 'codigo as codi')
            ->where('idbodega',13)
            ->orderby('detalle','asc')
            ->get();
            
            return response()->json([
                'error'=>false,
                'resultado'=>$insumos
            ]);
               
        }catch (\Throwable $e) {
            Log::error('BodegaPrincipalController => buscarLaboratorioReact => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function buscarLaboratorioMicrob($text){
        try{
           
            $insumos= DB::connection('pgsql')->table('bodega.laboratorio')
            ->where(function($c)use($text) {
                $c->where('descri', 'ilike', '%'.$text.'%');
            })
           
            ->select('id as codigo_item','descri as detalle', 'codigo as codi')
            ->where('idbodega',14)
            ->orderby('detalle','asc')
            ->get();
            
            return response()->json([
                'error'=>false,
                'resultado'=>$insumos
            ]);
               
        }catch (\Throwable $e) {
            Log::error('BodegaPrincipalController => buscarLaboratorioMicrob => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function buscarItem($text, $id){
        try{
            
            $insumos= DB::connection('pgsql')->table('bodega.items')
            ->where(function($c)use($text) {
                $c->where('descri', 'ilike', '%'.$text.'%');
            })

            ->where(function($q)use($id) {
                if($id==16){
                    $q->whereIn('idbodega',[8,13,14]);
                }else if($id==23){ //lab dialisis react
                    $q->whereIn('idbodega',[13]);
                }else if($id==19){ //lab dialisis materiales
                    $q->whereIn('idbodega',[8]);
                }else if($id==24){ //lab dialisis microb
                    $q->whereIn('idbodega',[14]);
                }else{
                    $q->where('idbodega',$id);
                }
            })
           
            ->select('codi_it as codigo_item','descri as detalle', 'codigo as codi')
           
            
            ->orderby('detalle','asc')
            ->get();
            
            return response()->json([
                'error'=>false,
                'resultado'=>$insumos
            ]);
               
        }catch (\Throwable $e) {
            Log::error('BodegaPrincipalController => buscarLaboratorioMicrob => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function buscarMedicamentosDevolucion($text, $bodega){
        try{
           
            $medicamentos= DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
            ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
            ->leftJoin('bodega.medicamentos as med', 'med.coditem','proxbode.idprod')
            ->where('proxbode.idbodega',$bodega)
            ->where(function($c)use($text) {
                $c->where('nombre', 'ilike', '%'.$text.'%');
            })
       
            ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion, '[', med.coditem,'][',proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','med.coditem as codigo_item')
            ->orderBy('lot.fcaduca','asc')
            ->get();            
            return response()->json([
                'error'=>false,
                'resultado'=>$medicamentos
            ]);
               
        }catch (\Throwable $e) {
            Log::error('BodegaPrincipalController => buscarMedicamentosDevolucion => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function buscarMedicamentosLote($text, $bodega){
        try{
            
                        
            $medicamentos= DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
            ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
            ->leftJoin('bodega.medicamentos as med', 'med.coditem','proxbode.idprod')
            ->where('proxbode.idbodega',$bodega)
            ->where('proxbode.existencia','>',0)
            ->where(function($c)use($text) {
                $c->where('nombre', 'ilike', '%'.$text.'%');
            })
            ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion, '[', med.coditem,'][',proxbode.idbodprod,']') AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','med.coditem')
            ->orderBy('lot.fcaduca','asc')
            ->take(25)
            ->get();

            foreach($medicamentos as $key=> $med){
                $insu=DB::connection('pgsql')->table('bodega.medicamentos')
                ->where('coditem', $med->coditem)->first();
                if($bodega==1){
                    if(!is_null($insu)){
                        if($insu->stock_bod>0){
                            $medicamentos[$key]->permitir="Si";
                            $medicamentos[$key]->stock_bod=$insu->stock_bod;
                        }else{
                            $medicamentos[$key]->permitir="No";
                            $medicamentos[$key]->stock_bod=$insu->stock_bod;
                        }
                    }else{
                        $medicamentos[$key]->permitir="No";
                    }
                }else if($bodega==6){
                    if(!is_null($insu)){
                        if($insu->stock>0){
                            $medicamentos[$key]->permitir="Si";
                            $medicamentos[$key]->stock=$insu->stock;
                            $medicamentos[$key]->stock1=$med->existencia;
                        }else{
                            $medicamentos[$key]->permitir="No";
                            $medicamentos[$key]->stock=$insu->stock;
                        }
                    }else{
                        $medicamentos[$key]->permitir="No";
                    }

                    $area_jefe=DB::connection('pgsql')->table('bodega.area_especialidad')
                    ->where('cedula',auth()->user()->tx_login)
                    ->first();

                    if(is_null($area_jefe)){
                        $medicamentos[$key]->permitir="XS";
                    }else{

                        $validaProd=DB::connection('pgsql')->table('bodega.medicina_area_especialidad')
                        ->where('idarea_especialidad',$area_jefe->idarea_especialidad)
                        ->where('id_medicina',$med->coditem)
                        ->first();
                                          
                        if(is_null($validaProd)){
                            $medicamentos[$key]->permitir="X";
                        }
                    }


                }else{
                    if(!is_null($insu)){
                        if($insu->stock_bod_dialisis>0){
                            $medicamentos[$key]->permitir="Si";
                            $medicamentos[$key]->stock_bod_dialisis=$insu->stock_bod_dialisis;
                        }else{
                            $medicamentos[$key]->permitir="No";
                            $medicamentos[$key]->stock_bod_dialisis=$insu->stock_bod_dialisis;
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
            Log::error('BodegaPrincipalController => buscarMedicamentosLote => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function buscarInsumosDevolucion($text, $idbodega){
        try{
           
            $medicamentos= DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
            ->join('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
            ->join('bodega.insumo as insu', 'insu.codinsumo','proxbode.idprod')
            // ->where('insu.activo','VERDADERO')
            ->where('proxbode.idbodega',$idbodega)
            ->where(function($c)use($text) {
                $c->where('insu.insumo', 'ilike', '%'.$text.'%');
            })

            ->select('lot.lote', 'lot.fcaduca','lot.regsan', 'insu.insumo AS detalle','proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','insu.codinsumo as codigo_item','proxbode.idbodega')
            ->orderby('lot.fcaduca','asc')
            ->get();

            return response()->json([
                'error'=>false,
                'resultado'=>$medicamentos
            ]);
               
        }catch (\Throwable $e) {
            Log::error('BodegaPrincipalController => buscarInsumosDevolucion => mensaje => '.$e->getMessage());
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
            // ->where('insu.activo','VERDADERO')
            ->where('proxbode.idbodega',$idbodega)
            ->where('proxbode.existencia','>',0)
            ->where(function($c)use($text) {
                $c->where('insu.insumo', 'ilike', '%'.$text.'%');
            })

            ->select('lot.lote', 'lot.fcaduca','lot.regsan', 'insu.insumo AS detalle','proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','insu.codinsumo','proxbode.idbodega')
            ->orderby('lot.fcaduca','asc')
            ->take(25)
            ->get();

            foreach($medicamentos as $key=> $med){
                $insu=DB::connection('pgsql')->table('bodega.insumo')
                ->where('codinsumo', $med->codinsumo)->first();
              
                if($idbodega==2){
                    if(!is_null($insu)){
                        if($insu->stockbod>0){
                            $medicamentos[$key]->permitir="Si";
                            $medicamentos[$key]->stockbod=$insu->stockbod;
                        }else{
                            $medicamentos[$key]->permitir="No";
                            $medicamentos[$key]->stockbod=$insu->stockbod;
                        }
                    }else{
                        $medicamentos[$key]->permitir="No";
                    }
                }else if($idbodega==7){
                    if(!is_null($insu)){
                        if($insu->stock>0){
                            $medicamentos[$key]->permitir="Si";
                            $medicamentos[$key]->stock=$insu->stock;
                            // $medicamentos[$key]->stock1=$insu->existencia;
                        }else{
                            $medicamentos[$key]->permitir="No";
                            $medicamentos[$key]->stock=$insu->stock;
                        }
                    }else{
                        $medicamentos[$key]->permitir="No";
                    }

                    if(is_null(auth()->user()->tx_login)){
                        $medicamentos[$key]->permitir="XS";
                    }else {
                        $area_param=DB::connection('pgsql')->table('bodega.area_especialidad')
                        ->where('cedula',auth()->user()->tx_login)
                        ->first();
                       
                        if(is_null($area_param)){
                            $medicamentos[$key]->permitir="XS";
                        }else{

                            //verificamos
                            $validaProd=DB::connection('pgsql')->table('bodega.insumo_area_especialidad')
                            ->where('idarea_especialidad',$area_param->idarea_especialidad)
                            ->where('idinsumo',$med->codinsumo)
                            ->first();
                                            
                            if(is_null($validaProd)){
                                $medicamentos[$key]->permitir="X";
                            }
                        }
                    }

                }else if($idbodega==18){
                    if(!is_null($insu)){
                        if($insu->stock_bod_dialisis>0){
                            $medicamentos[$key]->permitir="Si";
                            $medicamentos[$key]->stock_bod_dialisis=$insu->stock_bod_dialisis;
                        }else{
                            $medicamentos[$key]->permitir="No";
                            $medicamentos[$key]->stock_bod_dialisis=$insu->stock_bod_dialisis;
                        }
                    }else{
                        $medicamentos[$key]->permitir="No";
                    }
                }else{
                    if(!is_null($insu)){
                        // $insu->stock_farm_dialisis=$med->existencia;
                        // $insu->save();
                        if($insu->stock_farm_dialisis>0){
                            $medicamentos[$key]->permitir="Si";
                            $medicamentos[$key]->stock_farm_dialisis=$insu->stock_farm_dialisis;
                        }else{
                            $medicamentos[$key]->permitir="Si";
                            $medicamentos[$key]->stock_farm_dialisis=$insu->stock_farm_dialisis;
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
            Log::error('BodegaPrincipalController => buscarInsumosLote => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function buscarInsumosLoteDialisis($text, $idbodega){
        try{
          
            $medicamentos= DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
            ->join('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
            ->join('bodega.insumo as insu', 'insu.codinsumo','proxbode.idprod')
            // ->where('insu.activo','VERDADERO')
            ->where('proxbode.idbodega',$idbodega)
            ->where('proxbode.existencia','>',0)
            ->where(function($c)use($text) {
                $c->where('insu.insumo', 'ilike', '%'.$text.'%');
            })

            ->select('lot.lote', 'lot.fcaduca','lot.regsan', 'insu.insumo AS detalle','proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','insu.codinsumo','proxbode.idbodega')
            ->orderby('lot.fcaduca','asc')
            ->take(25)
            ->get();

            foreach($medicamentos as $key=> $med){
                $insu=DB::connection('pgsql')->table('bodega.insumo')
                ->where('codinsumo', $med->codinsumo)->first();
              
                if($idbodega==2){
                    if(!is_null($insu)){
                        if($insu->stockbod>0){
                            $medicamentos[$key]->permitir="Si";
                            $medicamentos[$key]->stockbod=$insu->stockbod;
                        }else{
                            $medicamentos[$key]->permitir="No";
                            $medicamentos[$key]->stockbod=$insu->stockbod;
                        }
                    }else{
                        $medicamentos[$key]->permitir="No";
                    }
                }else if($idbodega==7){
                    if(!is_null($insu)){
                        if($insu->stock>0){
                            $medicamentos[$key]->permitir="Si";
                            $medicamentos[$key]->stock=$insu->stock;
                            // $medicamentos[$key]->stock1=$insu->existencia;
                        }else{
                            $medicamentos[$key]->permitir="No";
                            $medicamentos[$key]->stock=$insu->stock;
                        }
                    }else{
                        $medicamentos[$key]->permitir="No";
                    }
                    
                    if(is_null(auth()->user()->tx_login)){
                        $medicamentos[$key]->permitir="XS";
                    }else {
                       
                        
                        $area_medico=DB::connection('pgsql')->table('esq_profesional.medico_especialidad AS me')
                        ->leftJoin('esq_datos_personales.personal as p', 'p.idpersonal','me.id_personal')
                        ->where('p.cedula',auth()->user()->tx_login)
                        ->select('*')
                        ->first();
                        if(!is_null($area_medico)){
                            $id_area_Esp=14;
                        }else{
                            $id_area_Esp=50;
                        }
                       
                        //verificamos
                        $validaProd=DB::connection('pgsql')->table('bodega.insumo_area_especialidad')
                        ->where('idarea_especialidad',$id_area_Esp)
                        ->where('idinsumo',$med->codinsumo)
                        ->first();
                                        
                        if(is_null($validaProd)){
                            $medicamentos[$key]->permitir="X";
                        }
                        
                    }

                }else if($idbodega==18){
                    if(!is_null($insu)){
                        if($insu->stock_bod_dialisis>0){
                            $medicamentos[$key]->permitir="Si";
                            $medicamentos[$key]->stock_bod_dialisis=$insu->stock_bod_dialisis;
                        }else{
                            $medicamentos[$key]->permitir="No";
                            $medicamentos[$key]->stock_bod_dialisis=$insu->stock_bod_dialisis;
                        }
                    }else{
                        $medicamentos[$key]->permitir="No";
                    }
                }else{
                    if(!is_null($insu)){
                        // $insu->stock_farm_dialisis=$med->existencia;
                        // $insu->save();
                        if($insu->stock_farm_dialisis>0){
                            $medicamentos[$key]->permitir="Si";
                            $medicamentos[$key]->stock_farm_dialisis=$insu->stock_farm_dialisis;
                        }else{
                            $medicamentos[$key]->permitir="Si";
                            $medicamentos[$key]->stock_farm_dialisis=$insu->stock_farm_dialisis;
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
            Log::error('BodegaPrincipalController => buscarInsumosLoteDialisis => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function buscarItemDevolucion($text, $idbodega){
        try{
           
            $items= DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
            ->join('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
            ->join('bodega.items as item', 'item.codi_it','proxbode.idprod')
            ->where('proxbode.idbodega',$idbodega)
            // ->where('proxbode.existencia','>',0)
            ->where(function($c)use($text) {
                $c->where('item.descri', 'ilike', '%'.$text.'%');
            })
            ->where('item.idbodega',$idbodega)
            ->select('lot.lote', 'lot.fcaduca','lot.regsan',DB::raw("CONCAT(item.descri) AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codi_it', 'codigo as codi','codi_it as codigo_item')
            ->orderBy('lot.fcaduca','asc')
            ->get();

            foreach($items as $key=> $med){
                $insu=DB::connection('pgsql')->table('bodega.items')
                ->where('codi_it', $med->codi_it)->first();
                if(!is_null($insu)){
                    if($insu->stock>0){
                        $items[$key]->permitir="Si";
                        $items[$key]->stock=$insu->stock;
                    }else{
                        $items[$key]->permitir="Si";
                        $items[$key]->stock=$insu->stock;
                    }
                }else{
                    $items[$key]->permitir="No";
                }
               
            }
            
            
            return response()->json([
                'error'=>false,
                'resultado'=>$items
            ]);
               
        }catch (\Throwable $e) {
            Log::error('BodegaPrincipalController => buscarItemsLote => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function buscarItemsLote($text, $idbodega){
        try{
           
            $items= DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
            ->join('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
            ->join('bodega.items as item', 'item.codi_it','proxbode.idprod')
            ->where('proxbode.idbodega',$idbodega)
            ->where('proxbode.existencia','>',0)
            ->where(function($c)use($text) {
                $c->where('item.descri', 'ilike', '%'.$text.'%');
            })
            ->where('item.idbodega',$idbodega)
            ->select('lot.lote', 'lot.fcaduca','lot.regsan',DB::raw("CONCAT(item.descri,' - ', item.presen) AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.codi_it')
            ->orderBy('lot.fcaduca','asc')
            ->get();

            foreach($items as $key=> $med){
                $insu=DB::connection('pgsql')->table('bodega.items')
                ->where('codi_it', $med->codi_it)->first();
                if(!is_null($insu)){
                    if($insu->stock>0){
                        $items[$key]->permitir="Si";
                        $items[$key]->stock=$insu->stock;
                    }else{
                        $items[$key]->permitir="No";
                        $items[$key]->stock=$insu->stock;
                    }
                }else{
                    $items[$key]->permitir="No";
                }
               
            }
            
            
            return response()->json([
                'error'=>false,
                'resultado'=>$items
            ]);
               
        }catch (\Throwable $e) {
            Log::error('BodegaPrincipalController => buscarItemsLote => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function buscarProteccionDevolucion($text, $idbodega){
        try{
           
            $items= DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
            ->join('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
            ->join('bodega.proteccion as item', 'item.id','proxbode.idprod')
            ->where('proxbode.idbodega',$idbodega)
            ->where(function($c)use($text) {
                $c->where('item.descri', 'ilike', '%'.$text.'%');
            })
            ->where('item.idbodega',$idbodega)
            ->select('lot.lote', 'lot.fcaduca','lot.regsan',DB::raw("CONCAT(item.descri,' - ', item.presen) AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.id as codigo_item')
            ->orderBy('lot.fcaduca','asc')
            ->get();
         
            
            return response()->json([
                'error'=>false,
                'resultado'=>$items
            ]);
               
        }catch (\Throwable $e) {
            Log::error('BodegaPrincipalController => buscarProteccionDevolucion => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function buscarProteccionLote($text, $idbodega){
        try{
           
            $items= DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
            ->join('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
            ->join('bodega.proteccion as item', 'item.id','proxbode.idprod')
            ->where('proxbode.idbodega',$idbodega)
            ->where('proxbode.existencia','>',0)
            ->where(function($c)use($text) {
                $c->where('item.descri', 'ilike', '%'.$text.'%');
            })
            ->where('item.idbodega',$idbodega)
            ->select('lot.lote', 'lot.fcaduca','lot.regsan',DB::raw("CONCAT(item.descri,' - ', item.presen) AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','item.id')
            ->orderBy('lot.fcaduca','asc')
            ->get();
         
            foreach($items as $key=> $med){
                $insu=DB::connection('pgsql')->table('bodega.proteccion')
                ->where('id', $med->id)->first();
                if(!is_null($insu)){
                    if($insu->stock>0){
                        $items[$key]->permitir="Si";
                        $items[$key]->stock=$insu->stock;
                    }else{
                        $items[$key]->permitir="No";
                        $items[$key]->stock=$insu->stock;
                    }
                }else{
                    $items[$key]->permitir="No";
                }
               
            }
            
            
            return response()->json([
                'error'=>false,
                'resultado'=>$items
            ]);
               
        }catch (\Throwable $e) {
            Log::error('BodegaPrincipalController => buscarProteccionLote => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }
    
    public function buscarLaboratorioMatDevolucion($text,$id){
        try{
           

            $medicamentos= DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
            ->join('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
            ->join('bodega.laboratorio as lab', 'lab.id','proxbode.idprod')
            ->where('proxbode.idbodega',$id)
            ->where(function($c)use($text) {
                $c->where('lab.descri', 'ilike', '%'.$text.'%');
            })
           
            ->select('lot.lote', 'lot.fcaduca','lot.regsan',DB::raw("CONCAT(lab.descri) AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod', 'lab.id as codigo_item')
        
            ->get();
            
            return response()->json([
                'error'=>false,
                'resultado'=>$medicamentos
            ]);
               
        }catch (\Throwable $e) {
            Log::error('BodegaPrincipalController => buscarLaboratorioMatDevolucion => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }


    public function buscarLaboratorioMatLote($text){
        try{
           
            $medicamentos= DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
            ->join('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
            ->join('bodega.items as item', 'item.codinsumo','proxbode.idprod')
            ->where('proxbode.idbodega',2)
            ->where('proxbode.existencia','>',0)
            ->where(function($c)use($text) {
                $c->where('item.descri', 'ilike', '%'.$text.'%');
            })
           
            ->select('lot.lote', 'lot.fcaduca','lot.regsan',DB::raw("CONCAT(item.descri,' - ', item.presen) AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod')
        
            ->get();
            
            
            return response()->json([
                'error'=>false,
                'resultado'=>$medicamentos
            ]);
               
        }catch (\Throwable $e) {
            Log::error('BodegaPrincipalController => buscarLaboratorioMatLote => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function buscarLaboratorioReactDevolucion($text, $id){
        try{
           
            $medicamentos= DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
            ->join('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
            ->join('bodega.insumo as insu', 'insu.codinsumo','proxbode.idprod')
            ->where('proxbode.idbodega',$id)
            ->where('proxbode.existencia','>',0)
            ->where(function($c)use($text) {
                $c->where('insu.insumo', 'ilike', '%'.$text.'%');
            })
           
            ->select('lot.lote', 'lot.fcaduca','lot.regsan', 'insu.insumo AS detalle','proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod')
        
            ->get();
            
            
            return response()->json([
                'error'=>false,
                'resultado'=>$medicamentos
            ]);
               
        }catch (\Throwable $e) {
            Log::error('BodegaPrincipalController => buscarLaboratorioReactDevolucion => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function buscarLaboratorioReactLote($text){
        try{
           
            $medicamentos= DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
            ->join('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
            ->join('bodega.insumo as insu', 'insu.codinsumo','proxbode.idprod')
            ->where('proxbode.idbodega',2)
            ->where('proxbode.existencia','>',0)
            ->where(function($c)use($text) {
                $c->where('insu.insumo', 'ilike', '%'.$text.'%');
            })
           
            ->select('lot.lote', 'lot.fcaduca','lot.regsan', 'insu.insumo AS detalle','proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod')
        
            ->get();
            
            
            return response()->json([
                'error'=>false,
                'resultado'=>$medicamentos
            ]);
               
        }catch (\Throwable $e) {
            Log::error('BodegaPrincipalController => buscarLaboratorioReactLote => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function buscarLaboratorioMicroLote($text){
        try{
           
            $medicamentos= DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
            ->join('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
            ->join('bodega.insumo as insu', 'insu.codinsumo','proxbode.idprod')
            ->where('proxbode.idbodega',2)
            ->where('proxbode.existencia','>',0)
            ->where(function($c)use($text) {
                $c->where('insu.insumo', 'ilike', '%'.$text.'%');
            })
           
            ->select('lot.lote', 'lot.fcaduca','lot.regsan', 'insu.insumo AS detalle','proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod')
        
            ->get();
            
            
            return response()->json([
                'error'=>false,
                'resultado'=>$medicamentos
            ]);
               
        }catch (\Throwable $e) {
            Log::error('BodegaPrincipalController => buscarLaboratorioMicroLote => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function buscarLaboratorioLote($text, $id){
        try{
            // dd($id);
            $medicamentos= DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
            ->join('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
            ->join('bodega.laboratorio as lab', 'lab.id','proxbode.idprod')
            ->where('proxbode.idbodega',$id)
            ->where('proxbode.existencia','>',0)
            ->where(function($c)use($text) {
                $c->where('lab.descri', 'ilike', '%'.$text.'%');
            })
           
            ->select('lot.lote', 'lot.fcaduca','lot.regsan', 'lab.descri AS detalle','proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod')
            ->orderBy('lot.fcaduca','asc')
            ->get();

            return response()->json([
                'error'=>false,
                'resultado'=>$medicamentos
            ]);
           

        }catch (\Throwable $e) {
            Log::error('BodegaPrincipalController => buscarLaboratorioLote => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
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

                $tipocomp_old= TipoComprobanteOld::where('idtipocom',1)
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
                $comprobante->area=auth()->user()->persona->id_area;;
                $comprobante->codigo_old="IngresoBG";
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
                        $ProductoBodegaOld->precio=number_format(($precio[$cont]),4,'.', '');
                        $ProductoBodegaOld->precio2=0;
                        $ProductoBodegaOld->fecha=date('Y-m-d');
                        $ProductoBodegaOld->idusuario=auth()->user()->id;
                        $ProductoBodegaOld->sistema_old="ENLINEA";

                        //dependiendo de la bodega seleccionamos el tipoprod
                        if($comprobante->idbodega==1 || $comprobante->idbodega==17){//bod gral medicamentos
                            $ProductoBodegaOld->tipoprod=1;
                        }else if($comprobante->idbodega==2 || $comprobante->idbodega==18){// bod gral insumos
                            $ProductoBodegaOld->tipoprod=2;
                        }else if($comprobante->idbodega==14 || $comprobante->idbodega==24){// bod lab microb
                            $ProductoBodegaOld->tipoprod=11;
                        }else if($comprobante->idbodega==8 || $comprobante->idbodega==19){// bod lab mater
                            $ProductoBodegaOld->tipoprod=5;
                        }else if($comprobante->idbodega==13 || $comprobante->idbodega==23){// bod lab react
                            $ProductoBodegaOld->tipoprod=10;
                        }else if($comprobante->idbodega==3){// bod oficina
                            $ProductoBodegaOld->tipoprod=4;
                        }else if($comprobante->idbodega==4){// bod aseo
                            $ProductoBodegaOld->tipoprod=3;
                        }else if($comprobante->idbodega==5){// bod herramienta
                            $ProductoBodegaOld->tipoprod=6;
                        }else if($comprobante->idbodega==9){// bod tics
                            $ProductoBodegaOld->tipoprod=7;
                        }else if($comprobante->idbodega==10){// bod lenceria
                            $ProductoBodegaOld->tipoprod=8;
                        }else if($comprobante->idbodega==30){// bod proteccion
                            $ProductoBodegaOld->tipoprod=23;
                        }
                        $ProductoBodegaOld->save(); 
                        
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
                                if($bod==1 || $bod==17){
                                    $prod=DB::table('bodega.medicamentos as med')
                                    ->where('med.coditem', $validaLote->prodbod->idprod)
                                    ->select(DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion) AS detalle"))
                                    ->first();
                                    if(!is_null($prod)){
                                        $texto=", en el producto ".$prod->detalle;
                                    }
                                        
                                }else if($bod==2 || $bod==18){
                                    $prod=DB::table('bodega.insumo as insu')
                                    ->where('insu.codinsumo', $validaLote->prodbod->idprod)
                                    ->select(DB::raw("CONCAT(insu.insumo) AS detalle"))
                                    ->first();

                                    if(!is_null($prod)){
                                        $texto=", en el producto ".$prod->detalle;
                                    }
                                }else if($bod==8 || $bod==13 || $bod==14 || $bod==19 || $bod==23 || $bod==24){
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
                        
                        if($detalles->id_bodega==1 || $detalles->id_bodega==17){//medicamento
                            $actualizaStock=Medicamento::where('coditem',$detalles->id_item)
                            ->first();
                           
                            if($detalles->id_bodega==1){//gral
                                
                                $stock_Actual=$actualizaStock->stock_bod;
                                $actualizaStock->stock_bod=$stock_Actual + $detalles->cantidad;
                            }else{//dialisis
                               
                                $stock_Actual=$actualizaStock->stock_bod_dialisis;
                                $actualizaStock->stock_bod_dialisis=$stock_Actual + $detalles->cantidad;
                            }
                               
                            $actualizaStock->save();  

                        }else if($detalles->id_bodega==2 || $detalles->id_bodega==18){//insumo
                            $actualizaInsumo=Insumo::where('codinsumo',$detalles->id_item)
                            ->first();
                            if($detalles->id_bodega==2){//gral
                                $stock_Actual=$actualizaInsumo->stockbod;
                                $actualizaInsumo->stockbod=$stock_Actual + $detalles->cantidad;
                            }else{//dialiis
                                $stock_Actual=$actualizaInsumo->stock_bod_dialisis;
                                $actualizaInsumo->stock_bod_dialisis=$stock_Actual + $detalles->cantidad;
                            }
                            $actualizaInsumo->save(); 

                        }else if($detalles->id_bodega==14 || $detalles->id_bodega==24){//lab micro
                            $actualizaLab=Laboratorio::where('id',$detalles->id_item)
                            ->first();
                            if($detalles->id_bodega==14){
                                $stock_Actual=$actualizaLab->stock;
                                $actualizaLab->stock=$stock_Actual + $detalles->cantidad;
                                $actualizaLab->save(); 
                            }else{
                                $stock_Actual=$actualizaLab->stock_dialisis;
                                $actualizaLab->stock_dialisis=$stock_Actual + $detalles->cantidad;
                                $actualizaLab->save(); 
                            }
                                
 
                        }else if($detalles->id_bodega==8 || $detalles->id_bodega==19){//lab materiales
                            $actualizaLab=Laboratorio::where('id',$detalles->id_item)
                            ->first();
                           
                            if($detalles->id_bodega==8){
                                $stock_Actual=$actualizaLab->stock;
                                $actualizaLab->stock=$stock_Actual + $detalles->cantidad;
                                $actualizaLab->save(); 
                            }else{
                                $stock_Actual=$actualizaLab->stock_dialisis;
                                $actualizaLab->stock_dialisis=$stock_Actual + $detalles->cantidad;
                                $actualizaLab->save(); 
                            }
                                

                        }else if($detalles->id_bodega==13 || $detalles->id_bodega==23){//lab react
                            $actualizaLab=Laboratorio::where('id',$detalles->id_item)
                            ->first();
                           
                            if($detalles->id_bodega==13){
                                $stock_Actual=$actualizaLab->stock;
                                $actualizaLab->stock=$stock_Actual + $detalles->cantidad;
                                $actualizaLab->save(); 
                            }else{
                                $stock_Actual=$actualizaLab->stock_dialisis;
                                $actualizaLab->stock_dialisis=$stock_Actual + $detalles->cantidad;
                                $actualizaLab->save(); 
                            }
                                

                        }else if($detalles->id_bodega==30){
                            //proteccion
                            $actualizaItem=Proteccion::where('id',$detalles->id_item)
                            ->first();
                            $stock_Actual=$actualizaItem->stock;                        
                            $actualizaItem->stock=$stock_Actual + $detalles->cantidad;                              
                            $actualizaItem->save(); 

                        }else{
                            //items
                            $actualizaItem=Item::where('codi_it',$detalles->id_item)
                            ->first();
                            $stock_Actual=$actualizaItem->stock;                        
                            $actualizaItem->stock=$stock_Actual + $detalles->cantidad;                              
                            $actualizaItem->save(); 
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

    public function listado(){

        return view('gestion_bodega.listado_ingreso_bod_gral');
    }

    public function filtrarIngreso($ini, $fin, $filtro, $idper){
        
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
            ->where(function($query2)use($filtro, $idper){
                if($filtro=="B"){
                    $query2->where('comp.id_usuario_ingresa',$idper);
                }else if($filtro=="P"){
                    $query2->where('comp.id_proveedor',$idper);
                }
                   
            })
            ->select('comp.descripcion','comp.secuencial','comp.fecha_hora','pr.empresa','pr.ruc','comp.total','comp.id_usuario_ingresa', 'comp.idcomprobante',DB::raw("CONCAT(per.ape1,' ', per.ape2,' ', per.nom1,' ', per.nom2) AS responsable"),'comp.idbodega','bod.nombre as nombre_bod','tipo.nombre as tipoIngreso')
            ->where('comp.estado','=','Activo')
            ->where('comp.codigo_old','<>','DevolverBodega')
            ->whereIn('idtipo_comprobante',[1,6])
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

    public function cargaProveedor(Request $request){
    
        $data = [];
        if($request->has('q')){
            $search = $request->q;
            $data=DB::connection('pgsql')->table('bodega.proveedor')
            ->where('ruc', 'ilike', '%'.$search.'%')
            ->orwhere('contacto', 'ilike', '%'.$search.'%')
            ->orwhere('empresa', 'ilike', '%'.$search.'%')
            ->select('idprov','ruc as documento','empresa AS nombre_empresa')
            ->take(10)->get();
        }
        
        return response()->json($data);
    }

    public function cargaBodeguero(Request $request){
    
        $data = [];
        if($request->has('q')){
            $search = $request->q;
           
            $data=DB::connection('pgsql')->table('public.users as usu')
            ->leftJoin('inventario.persona', 'usu.id_persona','persona.idper')
            ->leftJoin('bodega.per_perfil_usuario as p_usu', 'p_usu.id_usuario','usu.id')
            ->whereIn('p_usu.id_perfil',[5,8])
            ->where(function($query)use($search){
                $query->where('ci', 'ilike', '%'.$search.'%')
                ->orwhere(DB::raw("CONCAT(ape1, ' ', ape2, ' ', nom1, ' ', nom2)"), 'ilike', '%'.$search.'%');
            })            
            ->select('usu.id AS idper','ci as documento',DB::raw("CONCAT(ape1,' ',ape2,' ',nom1,' ',nom2) AS nombre_bodeguero"))
            ->take(10)->get();
        }
        
        return response()->json($data);
    }

    public function listadoEgreso(){

        return view('gestion_bodega.listado_egreso_bod_gral');
    }

    public function filtrarEgresoBodega($ini, $fin, $filtro, $idper){
        
        try{
            $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
            ->leftJoin('bodega.bodega as bod', 'bod.idbodega','comp.idbodega')
            ->leftJoin('public.users as usu', 'usu.id','comp.id_usuario_ingresa')
            ->leftJoin('inventario.persona as per', 'per.idper','usu.id_persona')
            ->where(function($query)use($ini, $fin){
                $query->whereBetween('comp.fecha',[$ini, $fin]);
            })
            ->where(function($query2)use($filtro, $idper){
                if($filtro=="B"){
                    $query2->where('comp.id_usuario_aprueba',$idper);
                }else if($filtro=="P"){
                    $query2->where('comp.id_proveedor',$idper);
                }
                   
            })
           
            ->select('comp.descripcion','comp.secuencial','comp.fecha_hora','comp.observacion','comp.total','comp.id_usuario_ingresa', 'comp.idcomprobante',DB::raw("CONCAT(per.ape1,' ', per.ape2,' ', per.nom1,' ', per.nom2) AS responsable"),'comp.idbodega','bod.nombre as nombre_bod','comp.idtipo_comprobante','comp.id_usuario_aprueba')
            ->where('comp.estado','=','Activo')
            // ->where('idtipo_comprobante',4)
            ->whereIn('idtipo_comprobante',[4,11,12,3])
            ->where('comp.codigo_old','<>','Pedido')
            ->get();
            
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

    public function vistaEgreso(){
        $bodega= DB::connection('pgsql')->table('bodega.bodega')
        ->where('idtipobod',1)
        ->where('estado',1)
        ->get();
        return view('gestion_bodega.egreso_bodega_general',[
            "bodega"=>$bodega
        ]);
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

                if($request->cmb_tipo=='Externo'){
                    $idtipoSel=4; //
                }else{
                    $idtipoSel=12; //i
                }

                $tipocomp_old= TipoComprobanteOld::where('idtipocom',$idtipoSel)
                ->orderBy('idtipocom','desc')
                ->first();

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
                $comprobante->area=auth()->user()->persona->id_area;;
                $comprobante->codigo_old="EgresoBG";

                $comprobante->observacion=$request->motivo;
                $comprobante->subtotal=$request->total_suma;
                $comprobante->total=$request->total_suma;   
                
                $comprobante->retirado_cedula=$request->cedula;
                $comprobante->retirado_nombre=$request->nombre_retira;   
               
                $comprobante->id_usuario_ingresa=auth()->user()->id;

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
                        $detalles=new DetalleComprobante();
                        $detalles->iddetalle_comprobante=$suma;
                        $detalles->idcomprobante=$comprobante->idcomprobante;
                        $detalles->id_item=$idmedicina_selecc[$cont];
                        $detalles->id_bodega=$idbodega_selecc[$cont];
                        $detalles->cantidad=$cantidad[$cont];
                        $detalles->precio=number_format(($precio[$cont]),4,'.', '');
                        $detalles->descuento=0;
                        $detalles->total=number_format(($total[$cont]),4,'.', '');
                        $detalles->iva=0;
                        $detalles->idbodprod=$idbodega_producto[$cont];
                        $detalles->fecha=date('Y-m-d H:i:s');
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
                        $existencia->tipo="Egreso a Bodega";
                        $existencia->cod="EAB";
                        $existencia->fecha_hora=date('Y-m-d H:i:s');
                        $existencia->reg_sanitario=$reg_sani[$cont];
                        $existencia->fecha_elaboracion=$fecha_elab_[$cont];
                        $existencia->fecha_caducidad=$fecha_caduc[$cont];
                        $existencia->idusuario=auth()->user()->id;
                        $existencia->idbodprod=$detalles->idbodprod;
                        $existencia->fecha=date('Y-m-d');
                        $existencia->save();   

                        //actualizamos el stock en la tabla productobodega 
                        $actualizaStockOld =ProductoBodega::where('idbodprod',$idbodega_producto[$cont])->first();
                        $stockactPB=$actualizaStockOld->existencia;                      
                        $nuevoStock_act=$stockactPB - $existencia->resta;
                        $actualizaStockOld->existencia=$nuevoStock_act; 
                        $actualizaStockOld->fecha=date('Y-m-d');
                        $actualizaStockOld->idusuario=auth()->user()->id;

                        $actualizaStockOld->save(); 
                        
                        if($detalles->id_bodega==1 || $detalles->id_bodega==17){//medicamento
                            $actualizaStock=Medicamento::where('coditem',$detalles->id_item)
                            ->first();

                            
                            if($detalles->id_bodega==1){//gral
                                $stock_Actual=$actualizaStock->stock_bod;
                                $actualizaStock->stock_bod=$stock_Actual - $detalles->cantidad;
                            }else{//dialisis
                                $stock_Actual=$actualizaStock->stock_bod_dialisis;
                                $actualizaStock->stock_bod_dialisis=$stock_Actual - $detalles->cantidad;

                            }

                            $actualizaStock->save();  

                            //comprobamos que el stock actual no sea menor a lo q se va a quitar
                            if($stockactPB < $existencia->resta){
                                DB::connection('pgsql')->rollback();
                                return (['mensaje'=>'El stock actual del item '.$actualizaStock->nombre. " ".$actualizaStock->concentra."".$actualizaStock->forma. " es menor a ".$existencia->resta,'error'=>true]); 
                            }


                        }else if($detalles->id_bodega==2 || $detalles->id_bodega==18){//insumo
                            $actualizaInsumo=Insumo::where('codinsumo',$detalles->id_item)
                            ->first();
                            if($detalles->id_bodega==2){
                                $stock_Actual=$actualizaInsumo->stockbod;
                                $actualizaInsumo->stockbod=$stock_Actual - $detalles->cantidad;
                            }else{
                                $stock_Actual=$actualizaInsumo->stock_bod_dialisis;
                                $actualizaInsumo->stock_bod_dialisis=$stock_Actual - $detalles->cantidad;
                            }
                               
                            $actualizaInsumo->save(); 

                            //comprobamos que el stock actual no sea menor a lo q se va a quitar
                            if($stockactPB < $existencia->resta){
                                DB::connection('pgsql')->rollback();
                                return (['mensaje'=>'El stock actual del item '.$actualizaInsumo->insumo. " es menor a ".$existencia->resta,'error'=>true]); 
                            }
                        
                        }else if($detalles->id_bodega==14 || $detalles->id_bodega==24){//lab micro
                            $actualizaLab=Laboratorio::where('id',$detalles->id_item)
                            ->first();
                            if($detalles->id_bodega==14){
                                $stock_Actual=$actualizaLab->stock;
                                $actualizaLab->stock=$stock_Actual - $detalles->cantidad;
                                $actualizaLab->save(); 
                            }else{
                                $stock_Actual=$actualizaLab->stock_dialisis;
                                $actualizaLab->stock_dialisis=$stock_Actual - $detalles->cantidad;
                                $actualizaLab->save(); 
                            }

                            //comprobamos que el stock actual no sea menor a lo q se va a quitar
                            if($stockactPB < $existencia->resta){
                                DB::connection('pgsql')->rollback();
                                return (['mensaje'=>'El stock actual del item '.$actualizaInsumo->insumo. " es menor a ".$existencia->resta,'error'=>true]); 
                            }
                                

                        }else if($detalles->id_bodega==8 || $detalles->id_bodega==19){//lab materiales
                            $actualizaLab=Laboratorio::where('id',$detalles->id_item)
                            ->first();
                           
                            if($detalles->id_bodega==8){
                                $stock_Actual=$actualizaLab->stock;
                                $actualizaLab->stock=$stock_Actual - $detalles->cantidad;
                                $actualizaLab->save(); 
                            }else{
                                $stock_Actual=$actualizaLab->stock_dialisis;
                                $actualizaLab->stock_dialisis=$stock_Actual - $detalles->cantidad;
                                $actualizaLab->save(); 
                            }

                            //comprobamos que el stock actual no sea menor a lo q se va a quitar
                            if($stockactPB < $existencia->resta){
                                DB::connection('pgsql')->rollback();
                                return (['mensaje'=>'El stock actual del item '.$actualizaInsumo->insumo. " es menor a ".$existencia->resta,'error'=>true]); 
                            }
                                

                        }else if($detalles->id_bodega==13 || $detalles->id_bodega==23){//lab react
                            $actualizaLab=Laboratorio::where('id',$detalles->id_item)
                            ->first();
                           
                            if($detalles->id_bodega==13){
                                $stock_Actual=$actualizaLab->stock;
                                $actualizaLab->stock=$stock_Actual - $detalles->cantidad;
                                $actualizaLab->save(); 
                            }else{
                                $stock_Actual=$actualizaLab->stock_dialisis;
                                $actualizaLab->stock_dialisis=$stock_Actual - $detalles->cantidad;
                                $actualizaLab->save(); 
                            }

                            //comprobamos que el stock actual no sea menor a lo q se va a quitar
                            if($stockactPB < $existencia->resta){
                                DB::connection('pgsql')->rollback();
                                return (['mensaje'=>'El stock actual del item '.$actualizaInsumo->insumo. " es menor a ".$existencia->resta,'error'=>true]); 
                            }
                                

                        }else if($detalles->id_bodega==30) {//bodega proteccion
                            $actualizaItem=Proteccion::where('id',$detalles->id_item)
                            ->first();
                            $stock_Actual=$actualizaItem->stock;
                            $actualizaItem->stock=$stock_Actual - $detalles->cantidad;
                            $actualizaItem->save(); 

                            //comprobamos que el stock actual no sea menor a lo q se va a quitar
                            if($stockactPB < $existencia->resta){
                                DB::connection('pgsql')->rollback();
                                return (['mensaje'=>'El stock actual del item '.$actualizaItem->descri. " es menor a ".$existencia->resta,'error'=>true]); 
                            }

                        }else {//bodega otros
                            $actualizaItem=Item::where('codi_it',$detalles->id_item)
                            ->first();
                            $stock_Actual=$actualizaItem->stock;
                            $actualizaItem->stock=$stock_Actual - $detalles->cantidad;
                            $actualizaItem->save(); 

                            //comprobamos que el stock actual no sea menor a lo q se va a quitar
                            if($stockactPB < $existencia->resta){
                                DB::connection('pgsql')->rollback();
                                return (['mensaje'=>'El stock actual del item '.$actualizaItem->descri. " es menor a ".$existencia->resta,'error'=>true]); 
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

                    return (['mensaje'=>'Informacion egresada exitosamente','error'=>false]);
                }        


            } catch (\Throwable $e) {
                DB::connection('pgsql')->rollback();
                Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
                return (['mensaje'=>'Ocurrió un error,intentelo más tarde','error'=>true]); 
            }
        });
        return ($transaction);
    }

    public function listaPedidoVista(){

        // return view('gestion_bodega.listado_pedido');
        return view('gestion_bodega.listado_pedido_new');
    }


    public function filtrarPedidoBodega($ini, $fin){
        
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
            // ->whereIN('comp.codigo_old',['Pedido','EntregadoF'])
            //  ->whereIN('comp.codigo_old',['Pedido','EntregadoF','EntregadoB'])
            ->whereIN('comp.codigo_old',['Pedido'])
            // ->where('idtipo_comprobante',1)
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

    public function detallePedidoBodega($id,$idbodega){
       
        try{
           
            $anulado=Comprobante::where('idcomprobante',$id)
            ->select('codigo_old')
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

            if($idbodega==1 || $idbodega==17 || $idbodega==6 ){//medicamento
                $info= DB::connection('pgsql')->table('bodega.detalle_comprobante as detcomp')
                ->leftJoin('bodega.comprobante as comp', 'comp.idcomprobante','detcomp.idcomprobante')
                ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                ->leftJoin('bodega.medicamentos as medi', 'medi.coditem','detcomp.id_item')
                ->leftJoin('bodega.prodxbod as pb', 'pb.idbodprod','pedido.idbodpro')
                ->select(DB::raw("CONCAT(medi.nombre,' - ', medi.concentra,' - ', medi.forma) AS nombre_item"),'pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','pb.existencia as stock','medi.coditem', 'detcomp.iddetalle_comprobante as iddetalle')
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
                'validaParametro'=>$validaParametro
            ]);
        }catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }

    public function listaPedidoVistaSolicitante(){
        $bodega= DB::connection('pgsql')->table('bodega.bodega')
        ->where('idtipobod',1)
        ->where('estado',1)
        ->get();
        
        return view('gestion_bodega.listado_pedido_solicita',[
            "bodega"=>$bodega
        ]);
    }
    //solcitudes x usuario logueafdo
    public function filtrarPedidoBodegaSol($ini, $fin){
        
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
            ->select('comp.descripcion','comp.secuencial','comp.fecha_hora','comp.observacion','comp.total','comp.id_usuario_ingresa', 'comp.idcomprobante','bod.nombre as nombre_bodega','comp.idbodega',DB::raw("CONCAT(per.ape1,' ', per.ape2,' ', per.nom1,' ', per.nom2) AS solicita"), "perf.descripcion as area1","comp.codigo_old","a.descripcion as area",'comp.guarda_detalle_pedido')
            ->where('comp.estado','=','Activo')
            ->where('comp.idtipo_comprobante','<>',18)
            ->whereIN('comp.codigo_old',['Pedido','PedidoAFarm','EntregadoF','EntregadoB','Anulado'])
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

    public function detallePedidoBodegaSoli($id,$idbodega){
       
        try{
         
            if($idbodega==1 || $idbodega==17 || $idbodega==20 || $idbodega==6){//medicamento
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
                ->get();
               
               
             
            }else if($idbodega==2 || $idbodega==7 || $idbodega==18 || $idbodega==21){//insumo
             
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
                
                
                
            }else if($idbodega==8 || $idbodega==13 || $idbodega==14 || $idbodega==19 || $idbodega==23 || $idbodega==24){
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
                
            }else if($idbodega==22 || $idbodega==25 || $idbodega==26 || $idbodega==27 || $idbodega==28 || $idbodega==29 ){
                //laboratorio dialisis
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
            

            }else if($idbodega==30){
               
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
              

            }else if($idbodega==31){
                //dialisis
                $info=[];
            }else{
                $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
                ->leftJoin('bodega.detalle_comprobante as detcomp', 'detcomp.idcomprobante','comp.idcomprobante')
                ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                
                ->leftJoin('bodega.items as item', 'item.codi_it','detcomp.id_item',DB::raw("CONCAT(per.ci,' - ', per.ape1,' ', per.nom1) AS anulador"), 'comp.detalle_anula')

                ->select('item.descri as nombre_item','pedido.lote','pedido.fecha_caducidad','pedido.cantidad_pedida','pedido.cantidad_entregada','item.stock as stock','detcomp.iddetalle_comprobante as iddetalle')

                ->where('comp.idcomprobante',$id)
                ->whereIN('comp.codigo_old',['Pedido','EntregadoF','EntregadoB','Anulado'])
                ->distinct()
                ->get();

                
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

    public function editarPedido($id, $bodega){
        try{
            // $id=decrypt($id);
            $entregado="Entregado";
            if($bodega==20){
                //receta dialisis
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'No se puede actualizar una receta'
                ]);
            }
            if($bodega==1 || $bodega==17 || $bodega==6){
                $comprobante=Comprobante::with('detalle','entregado','responsable')->where('idcomprobante',$id)
                ->first();
                $entregado="EntregadoF";
            }else if($bodega==2  || $bodega==7 || $bodega==18 || $bodega==21){ 
                $comprobante=Comprobante::with('detalle_insumo','entregado','responsable','paciente')->where('idcomprobante',$id)
                ->first();
            }else if($bodega==8 || $bodega==13 || $bodega==14 || $bodega==19 || $bodega==23 || $bodega==24) { //laboratorios bodega
                $comprobante=Comprobante::with('detalle_lab','entregado','recibido','bodega','proveedor','tipoIngreso','devolucion')->where('idcomprobante',$id)
                ->first();
                $entregado="EntregadoF";
            }else if($bodega==22 || $bodega==25 || $bodega==26 || $bodega==27 || $bodega==28 || $bodega==29) { //laboratorios farmacia
                $comprobante=Comprobante::with('detalle_lab','entregado','recibido','bodega','proveedor','tipoIngreso','devolucion')->where('idcomprobante',$id)
                ->first();
                $entregado="EntregadoF";
            }else if($bodega==30){
                $comprobante=Comprobante::with('detalle_proteccion','entregado','responsable','bodega','nomarea')->where('idcomprobante',$id)
                ->first();
            }else{
                $comprobante=Comprobante::with('detalle_item','entregado','responsable','bodega','nomarea')->where('idcomprobante',$id)
                ->first();
            }
          
            if($comprobante->codigo_old == $entregado){
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
            // ->where('idtipobod',1)
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

    public function anulaPedido(Request $request){
      
        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{
                $comprobante=Comprobante::find($request->idComprobanteAnula);
              
                if($comprobante->codigo_old == "PedidoAF" || ($comprobante->codigo_old == "Pedido")){
                                    
                    $detalle=DetalleComprobante::where('idcomprobante',$comprobante->idcomprobante)->get();
                    foreach ($detalle as $key => $data) {
                        //cambiamos el estado del pedido
                        $pedido=PedidoBodegaGral::where('iddetallecomprobante',$data->iddetalle_comprobante)->first();
                        $pedido->estado="Anulado";
                        $pedido->save();
                    }
                    $comprobante->codigo_old="Anulado";
                    $comprobante->id_anula=auth()->user()->id;
                    $comprobante->detalle_anula=$request->motivo_anulacion;
                    $comprobante->fecha_hora_actualiza=date('Y-m-d H:i:s');
                    $comprobante->save();

                    return (['mensaje'=>'Pedido anulado exitosamente','error'=>false]);
                }else{
                    return (['mensaje'=>'El pedido no se encuentra disponible de anulacion','error'=>true]); 
                }

                    
                  

            } catch (\Throwable $e) {
                DB::connection('pgsql')->rollback();
                Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
                return (['mensaje'=>'Ocurrió un error,intentelo más tarde','error'=>true]); 
            }
        });
        return ($transaction);
    }

    public function validaPedido(Request $request){
        
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
                  
                    
                    if(strtotime($validaPedido->fecha_solicita) >= strtotime($request->fecha_Act)){
                        DB::connection('pgsql')->rollback();
                        return (['mensaje'=>'El pedido fue actualizado, revise el detalle de nuevo','error'=>true, 'act'=>'S']);
                    }
                   

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
                        $existencia->tipo="Egreso Bodega desde Farmacia";
                        $existencia->cod="EABA";
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
                       
                        $ultimo =ProductoBodega::orderBy('idbodprod','desc')->first();
                        $sumauno=$ultimo->idbodprod;
                     

                        if($item->id_bodega==1){ //medi gral bode 
                            $id_bodega_farmacia=6; //med gral farm
                        }else if($item->id_bodega==2){ //insumos gral bode
                            $id_bodega_farmacia=7; //ins gral farm
                        }else if($item->id_bodega==17){ // med dialisis bode
                            $id_bodega_farmacia=20; // med dialisis farmacia
                        }else if($item->id_bodega==18){ // insu dialisis bode
                            $id_bodega_farmacia=21; // insu dialisis farm
                        }else if($item->id_bodega==19){ // lab dialisis mat bode
                            $id_bodega_farmacia=22; // lab dialisis mat farmacia
                        }else if($item->id_bodega==23){ // lab dialisis reac bode
                            $id_bodega_farmacia=25; // lab dialisis reac farm
                        }else if($item->id_bodega==24){ // lab dialisis  micro bode
                            $id_bodega_farmacia=26; // lab dialisis  micro farma
                        }else if($item->id_bodega==13){ // lab gral reac  bode
                            $id_bodega_farmacia=28;  // lab gral reac  farm
                        }else if($item->id_bodega==8){ // lab gral materi  bode
                            $id_bodega_farmacia=27; // lab gral mat  farma
                        }else if($item->id_bodega==14){ // lab gral micro  bode
                            $id_bodega_farmacia=29; // lab gral micro  farm
                        }

                        $verificaStockFarmacia=$this->verificaStockItem($id_bodega_farmacia, $item->id_item);
                        if($verificaStockFarmacia['error']==true){
                           
                            DB::connection('pgsql')->rollback();
                            return (['mensaje'=>'No se pudo obtener el stock actual del item con id '.$item->id_item. ' en la bodega #'.$item->id_item,'error'=>true]); 
                        } 
                        if(is_null($verificaStockFarmacia['data'])){
                            $stockItemActualFarmacia=0;
                        }else{
                            $stockItemActualFarmacia=$verificaStockFarmacia['data']->stock;
                        }
                        
                       
                        // dd($stockItemActualFarmacia);

                        //agregamos el stock en la tabla productobodega a la bodega q lo solicito
                        $ProductoBodegaOld=new ProductoBodega();
                        $ProductoBodegaOld->idbodprod=$sumauno+1;
                        $ProductoBodegaOld->idprod=$item->id_item;
                        $ProductoBodegaOld->idbodega=$id_bodega_farmacia;
                        $ProductoBodegaOld->existencia=$request->cantidad_validada[$key];
                        $ProductoBodegaOld->precio=$item->precio;
                        $ProductoBodegaOld->precio2=0;
                        $ProductoBodegaOld->sistema_old="ENLINEA";

                        $ProductoBodegaOld->fecha=date('Y-m-d');
                        $ProductoBodegaOld->idusuario=auth()->user()->id;

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
                        $existencia->tipo="Ingreso a Farmacia desde Bodega";
                        $existencia->cod="IAFB";
                        $existencia->fecha_hora=date('Y-m-d H:i:s');
                        $existencia->fecha_caducidad=$validaPedido->fecha_caducidad;
                        $existencia->fecha_elaboracion=$validaPedido->fecha_elabora;
                        $existencia->idusuario=auth()->user()->id;
                        $existencia->idbodprod=$ProductoBodegaOld->idbodprod;
                        $existencia->fecha=date('Y-m-d');
                        $existencia->save();   


                        //dependiendo de la bodega seleccionamos el tipoprod
                        if($item->id_bodega==1 || $item->id_bodega==17){//bod gral medicamentos
                            $ProductoBodegaOld->tipoprod=1;
                        }else if($item->id_bodega==2 || $item->id_bodega==18){// bod gral insumos
                            $ProductoBodegaOld->tipoprod=2;
                        }else if($item->id_bodega==14 || $item->id_bodega==24){// bod lab microb
                            $ProductoBodegaOld->tipoprod=11;
                        }else if($item->id_bodega==8 || $item->id_bodega==19){// bod lab mater
                            $ProductoBodegaOld->tipoprod=5;
                        }else if($item->id_bodega==13 || $item->id_bodega==23){// bod lab react
                            $ProductoBodegaOld->tipoprod=10;
                        }
                        $ProductoBodegaOld->save(); 

                        $verificaStock=$this->verificaStockItem($item->id_bodega, $item->id_item);
                        if($verificaStock['error']==true){
                           
                            DB::connection('pgsql')->rollback();
                            return (['mensaje'=>'No se pudo obtener el stock actual del item con id '.$item->id_item. ' en la bodega #'.$item->id_item,'error'=>true]); 
                        } 
                        if(is_null($verificaStock['data'])){
                            $stockItemActual=0;
                        }else{
                            $stockItemActual=$verificaStock['data']->stock;
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

                        $ultimolote =LoteProducto::orderBy('idlote','desc')->first();
                        $sumaunolote=$ultimolote->idlote;

                        $LoteProductoOld=new LoteProducto();
                        $LoteProductoOld->idlote=$sumaunolote+1;
                        $LoteProductoOld->idbodp=$ProductoBodegaOld->idbodprod;
                        $LoteProductoOld->lote=$existencia->lote;
                        $LoteProductoOld->felabora= $existencia->fecha_elaboracion;
                        $LoteProductoOld->fcaduca=$existencia->fecha_caducidad;
                        $LoteProductoOld->regsan=$existencia->reg_sanitario;
                        $LoteProductoOld->sistema_old="ENLINEA";
                        $LoteProductoOld->save(); 

                        
                        if($item->id_bodega==1 || $item->id_bodega==17){//medicamento
                            $actualizaStock=Medicamento::where('coditem',$item->id_item)
                            ->first();

                            if($item->id_bodega==1){
                                //restamos de la bodega principal
                                // $stock_ActualBodPrincipal=$actualizaStock->stock_bod;
                                $stock_ActualBodPrincipal=$stockItemActual;
                                $actualizaStock->stock_bod=$stock_ActualBodPrincipal - $request->cantidad_validada[$key];

                                //sumamos a la bodega solicitante
                                // $stock_ActualBodSolicita=$actualizaStock->stock;
                                $stock_ActualBodSolicita=$stockItemActualFarmacia;
                                $actualizaStock->stock=$stock_ActualBodSolicita + $request->cantidad_validada[$key];
                            }else{
                                //restamos de la bodega principal
                                $stock_ActualBodPrincipal=$stockItemActual;
                                // $stock_ActualBodPrincipal=$actualizaStock->stock_bod_dialisis;
                                $actualizaStock->stock_bod_dialisis=$stock_ActualBodPrincipal - $request->cantidad_validada[$key];

                                //sumamos a la bodega solicitante
                                $stock_ActualBodSolicita=$stockItemActualFarmacia;
                                // $stock_ActualBodSolicita=$actualizaStock->stock_farm_dialisis;
                                $actualizaStock->stock_farm_dialisis=$stock_ActualBodSolicita + $request->cantidad_validada[$key];
                            }
                                

                            $actualizaStock->save();  

                            //comprobamos que el stock actual no sea menor a lo q se va a quitar
                            if($stockactPB < $request->cantidad_validada[$key]){
                                DB::connection('pgsql')->rollback();
                                return (['mensaje'=>'El stock actual del item '.$actualizaStock->nombre. " ".$actualizaStock->concentra."".$actualizaStock->forma. " es menor a ".$request->cantidad_validada[$key],'error'=>true]); 
                            }
        
                        }else if($item->id_bodega==2 || $item->id_bodega==18){//insumo
                            $actualizaInsumo=Insumo::where('codinsumo',$item->id_item)
                            ->first();

                            if($item->id_bodega==2){
                                //restamos de la bodega principal
                                // $stock_ActualBodPrincipal=$actualizaInsumo->stockbod;
                                $stock_ActualBodPrincipal=$stockItemActual;
                                $actualizaInsumo->stockbod=$stock_ActualBodPrincipal - $request->cantidad_validada[$key];

                                //sumamos a la bodega solicitante
                                // $stock_ActualBodSolicita=$actualizaInsumo->stock;
                                $stock_ActualBodSolicita=$stockItemActualFarmacia;
                                $actualizaInsumo->stock=$stock_ActualBodSolicita + $request->cantidad_validada[$key];
                            }else{
                                //restamos de la bodega principal
                                $stock_ActualBodPrincipal=$stockItemActual;
                                $actualizaInsumo->stock_bod_dialisis=$stock_ActualBodPrincipal - $request->cantidad_validada[$key];

                                //sumamos a la bodega solicitante
                                // $stock_ActualBodSolicita=$actualizaInsumo->stock_farm_dialisis;
                                $stock_ActualBodSolicita=$stockItemActualFarmacia;
                                $actualizaInsumo->stock_farm_dialisis=$stock_ActualBodSolicita + $request->cantidad_validada[$key];
                            }
                               

                            $actualizaInsumo->save(); 

                            //comprobamos que el stock actual no sea menor a lo q se va a quitar
                            if($stockactPB < $request->cantidad_validada[$key]){
                                DB::connection('pgsql')->rollback();
                                return (['mensaje'=>'El stock actual del item '.$actualizaInsumo->insumo. " es menor a ".$request->cantidad_validada[$key],'error'=>true]); 
                            }
                        
                        }else{//laboratorio
                           
                            $actualizaLab=Laboratorio::where('id',$item->id_item)
                            ->first();
                            if($item->id_bodega==14 || $item->id_bodega==8 || $item->id_bodega==13){ //lab geral
                                //restamos de la bodega principal
                                // $stock_Actual=$actualizaLab->stock;
                                $stock_Actual=$stockItemActual;
                                $actualizaLab->stock=$stock_Actual -  $request->cantidad_validada[$key];
                                
                                //sumamos a la bodega solicitante
                                // $stock_ActualBodSolicita=$actualizaLab->stock_farmacia;
                                $stock_ActualBodSolicita=$stockItemActualFarmacia;
                                $actualizaLab->stock_farmacia=$stock_ActualBodSolicita + $request->cantidad_validada[$key];

                                $actualizaLab->save(); 

                            }else{ //lab dialisis

                                //restamos de la bodega principal
                                // $stock_Actual=$actualizaLab->stock_dialisis;
                                $stock_Actual=$stockItemActual;
                                $actualizaLab->stock_dialisis=$stock_Actual -  $request->cantidad_validada[$key];
                              
                                //sumamos a la bodega solicitante
                                // $stock_ActualBodSolicita=$actualizaLab->stock_diali_farmacia;
                                $stock_ActualBodSolicita=$stockItemActualFarmacia;
                                $actualizaLab->stock_diali_farmacia=$stock_ActualBodSolicita + $request->cantidad_validada[$key];

                                $actualizaLab->save(); 
                            }

                            //comprobamos que el stock actual no sea menor a lo q se va a quitar
                            if($stockactPB < $request->cantidad_validada[$key]){
                                DB::connection('pgsql')->rollback();
                                return (['mensaje'=>'El stock actual del item '.$actualizaLab->descri. " es menor a ".$request->cantidad_validada[$key],'error'=>true]); 
                            }

                            //GUARDAMOS O ACTUALIZAMOS LA BODEGA LAB FARMACIA
                            $bodFarmacia= FarmLaboratorio::where('id_item',$item->id_item)
                            ->where('idbodega',$item->id_bodega)
                            ->first();

                            if($item->id_bodega==19 || $item->id_bodega==23 || $item->id_bodega==24){
                                $esdialisi="S";
                            }else{
                                $esdialisi="N";
                            }
                           
                            if(is_null($bodFarmacia)){
                                $ultimo=FarmLaboratorio::orderBy('idfarm_lab','desc')->first();
                                if(is_null($ultimo)){
                                    $suma=1;
                                }else{
                                    $suma=$ultimo->idfarm_lab+1;
                                }
                                
                                //agregamos
                                $newBodFarm= new FarmLaboratorio();
                                $newBodFarm->idfarm_lab=$suma;
                                $newBodFarm->id_item=$item->id_item;
                                $newBodFarm->nombre=$actualizaLab->descri;
                                $newBodFarm->present=$actualizaLab->presen;
                                $newBodFarm->stock_farmacia=$request->cantidad_validada[$key];
                                $newBodFarm->stockbod=$actualizaLab->stock;
                                $newBodFarm->codinsumo=$actualizaLab->codinsumo;
                                $newBodFarm->activo='VERDADERO';
                                $newBodFarm->valor=$actualizaLab->valor;
                                $newBodFarm->es_dialisis=$esdialisi;
                                $newBodFarm->tipoprod=$ProductoBodegaOld->tipoprod;
                                $newBodFarm->idbodega=$item->id_bodega;
                                $newBodFarm->save();
                            }else{
                                $stock_Actual=$bodFarmacia->stock_farmacia;
                                $bodFarmacia->id_item=$item->id_item;
                                $bodFarmacia->nombre=$actualizaLab->descri;
                                $bodFarmacia->present=$actualizaLab->presen;
                                $bodFarmacia->stock_farmacia=$stock_Actual + $request->cantidad_validada[$key];
                                $bodFarmacia->stockbod=$actualizaLab->stock;
                                $bodFarmacia->codinsumo=$actualizaLab->codinsumo;
                                $bodFarmacia->valor=$actualizaLab->valor;
                                $bodFarmacia->tipoprod=$ProductoBodegaOld->tipoprod;
                                $bodFarmacia->es_dialisis=$esdialisi;
                                $bodFarmacia->idbodega=$item->id_bodega;
                                $bodFarmacia->activo='VERDADERO';
                                $bodFarmacia->save();
                            }

                            
                        } 
                    }  
                }    
                $compr=Comprobante::where('idcomprobante',$item->idcomprobante)->first();
                $compr->codigo_old="EntregadoF";
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

    
    public function reporteTransfBodGral($id, $bodega){
        
        try{
            set_time_limit(0);
            ini_set("memory_limit",-1);
            ini_set('max_execution_time', 0);

           
            if($bodega==1 || $bodega==6 || $bodega==17 || $bodega==20){ //medicamnentos
                $comprobante=Comprobante::with('detalle','entregado','responsable','nomarea','paciente','cie','especialidad')->where('idcomprobante',$id)
                ->first();
                
            }else if($bodega==2 || $bodega==7 || $bodega==18 || $bodega==21){ //insumos
                $comprobante=Comprobante::with('detalle_insumo','entregado','responsable','nomarea','paciente','cie')->where('idcomprobante',$id)
                ->first();
            }else if($bodega==8 || $bodega==13 || $bodega==14 || $bodega==19  || $bodega==22|| $bodega==23 || $bodega==24 || $bodega==25 || $bodega==26 || $bodega==27 || $bodega==28|| $bodega==29) { //laboratorios
                $comprobante=Comprobante::with('detalle_lab','entregado','recibido','bodega','proveedor','tipoIngreso','devolucion')->where('idcomprobante',$id)
                ->first();
            }else if($bodega==30){
                $comprobante=Comprobante::with('detalle_proteccion','entregado','responsable','bodega','nomarea')->where('idcomprobante',$id)
                ->first();

            }else if($bodega==31 || $bodega==32 || $bodega==33){
                $datos= DB::connection('pgsql')->table('bodega.comprobante as comp')
                ->leftJoin('bodega.area as a', 'a.id_area','comp.area')
                ->leftJoin('bodega.bodega as b', 'b.idbodega','comp.idbodega')
                ->leftJoin('public.users as u', 'u.id','comp.id_usuario_ingresa')
                ->leftJoin('inventario.persona as per', 'per.idper','u.id_persona')
              
                ->leftJoin('bodega.detalle_comprobante as detcomp', 'detcomp.idcomprobante','comp.idcomprobante')
                ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                ->leftJoin('bodega.medicamentos as medi', 'medi.coditem','detcomp.id_item')
                ->leftJoin('bodega.insumo as i', 'i.codinsumo','detcomp.id_item')        
                ->select(DB::raw("CONCAT(medi.nombre,' - ', medi.concentra,' - ', medi.forma) AS nombre_item_med"),DB::raw("CONCAT(i.insumo) AS nombre_item_insumo"),'pedido.lote','pedido.fecha_caducidad','pedido.cantidad_entregada','pedido.cantidad_pedida','medi.codigo as codigo_esbay_med', 'detcomp.precio as precio_item','detcomp.id_item','i.codigo as codigo_esbay_ins','comp.descripcion','comp.secuencial',DB::raw("CONCAT(per.ape1, ' - ',per.ape2, ' - ',per.nom1, ' - ',per.nom2) AS solicita"),'b.nombre','b.nombre','comp.codigo_old','a.descripcion as nombre_area')
                ->where('comp.idcomprobante',$id)
                ->where('comp.estado','Activo')          
                ->get();

                foreach($datos as $key => $data){            
                    if($data->id_item>=30000){
                        $nombre_item=$data->nombre_item_insumo;
                      
                    }else{
                        $nombre_item=$data->nombre_item_med;
                      
                    }
                    $datos[$key]->nombre_item_selecc=$nombre_item;
                       
                }

                $comprobante=Comprobante::with('entregado','responsable','paciente','cie')->find($id);

            }
            else{
                $comprobante=Comprobante::with('detalle_item','entregado','responsable','bodega','nomarea')->where('idcomprobante',$id)
                ->first();
            } 
          
            if($comprobante->codigo_old=="Pedido"){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'La solicitud aun no a sido respondida'
                ]);
            }
          
            $bodega_cons=Bodega::where('idbodega',$bodega)->first();
          
            $nombrePDF=$comprobante->descripcion.".pdf";
           
            if($bodega==1 || $bodega==6 || $bodega==17 || $bodega==20){
                
                if($bodega==20 || $bodega==6){
                    $detalleReceta=[];
                    if($comprobante->id_comp_receta){
                       
                        //detalle receta
                        $detalleReceta=DB::table('inventario.detallecomprobante as dc')
                        ->leftJoin('bodega.detalle_comprobante as dcb','dcb.id_item','dc.idbodprod')
                        ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','dcb.iddetalle_comprobante')
                        ->leftJoin('inventario.detallereceta as dr','dr.iddetalle','dc.iddetalle')
                        ->select('dr.dosis','dr.frec','dr.duracion','dr.iddetalle','dc.idcomprobante','dr.uso')
                        ->where('dc.idcomprobante',$comprobante->id_comp_receta)
                        ->where('dcb.idcomprobante',$id)
                        ->where('pedido.estado','Entregado')
                        ->get();

                        
                    }
                    $farm="S";
                }else{
                    $farm="N";
                    $detalleReceta=[];
                }
                $pdf=\PDF::loadView('reportes.transferencia_bod_med',['comprobante'=>$comprobante,'bodega_cons'=>$bodega_cons,'farm'=>$farm,'detalleReceta'=>$detalleReceta]);

            }else if($bodega==2 || $bodega==7 || $bodega==18 || $bodega==21){
                $triague_amb="";
                if($bodega==21 || $bodega==7){
                    $farm="S";
                }else{
                    $farm="N";
                }
                
                if($comprobante->area==33){
                    if($comprobante->id_servicio==31){
                        $triague_amb="TRIAGE";
                    }else{
                        $triague_amb="AMBULATORIO";
                    }
                }
                $pdf=\PDF::loadView('reportes.transferencia_bod_ins',['comprobante'=>$comprobante,'bodega_cons'=>$bodega_cons,'farm'=>$farm,'triague_amb'=>$triague_amb]);

            }else if($bodega==8 || $bodega==13 || $bodega==14 || $bodega==19  || $bodega==22|| $bodega==23 || $bodega==24 || $bodega==25 || $bodega==26 || $bodega==27 || $bodega==28|| $bodega==29){

                if($bodega==22 || $bodega==25 || $bodega==26 || $bodega==27 || $bodega==28|| $bodega==29){
                    $farm="S";
                }else{
                    $farm="N";
                }
                $pdf=\PDF::loadView('reportes.transferencia_bod_laboratorio',['comprobante'=>$comprobante,'bodega_cons'=>$bodega_cons,'farm'=>$farm]);

            }else if($bodega==30){
                $pdf=\PDF::loadView('reportes.transferencia_proteccion',['comprobante'=>$comprobante,'bodega_cons'=>$bodega_cons]);
                
            }else if($bodega==31 || $bodega==32 || $bodega==33){
                $farm="S";
                $pdf=\PDF::loadView('reportes.transferencia_paquete',['comprobante'=>$comprobante,'datos'=>$datos,'bodega_cons'=>$bodega_cons,'farm'=>$farm]);
                
            }else{
                $pdf=\PDF::loadView('reportes.transferencia_otro',['comprobante'=>$comprobante,'bodega_cons'=>$bodega_cons]);
                
            }
            $pdf->setPaper([0, 0, 180,  500]);//597

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

    public function reporteRolloFarmacia($id, $bodega){
        
        try{
            set_time_limit(0);
            ini_set("memory_limit",-1);
            ini_set('max_execution_time', 0);

            if($bodega==1 || $bodega==6 || $bodega==7 || $bodega==17 || $bodega==20 || $bodega==21){ //medicamnentos
                // $comprobante=Comprobante::with('entregado','responsable','nomarea','paciente','cie','especialidad')->where('idcomprobante',$id)
                // ->first();

                $comprobante= DB::connection('pgsql')->table('bodega.comprobante as comp')
                ->leftJoin('esq_catalogos.especialidad as esp', 'esp.id_especialidad','comp.id_especialidad')
                ->leftJoin('public.users as usu', 'usu.id','comp.id_usuario_ingresa')
                ->leftJoin('inventario.persona as per', 'per.idper','usu.id_persona')
                ->leftJoin('esq_catalogos.servicio as s', 's.id_servicio','comp.id_servicio')
                ->leftJoin('esq_pacientes.pacientes as per_pac', 'per_pac.id_paciente','comp.id_paciente')
                ->leftJoin('bodega.bodega as bod', 'bod.idbodega','comp.idbodega')
                ->leftJoin('bodega.area as a', 'a.id_area','comp.area')
                ->select(DB::raw("CONCAT(per.ape1,' ', per.ape2,' ', per.nom1,' ', per.nom2) AS solicita"),DB::raw("CONCAT(per_pac.apellido1,' ', per_pac.apellido2,' ', per_pac.nombre1,' ', per_pac.nombre2) AS paciente"),'per_pac.documento','s.nombre as dependencia','comp.area as id_area','comp.id_servicio','comp.id_especialidad','esp.nombre as espec_med','comp.tipoarea','a.descripcion as area')
                ->where('comp.estado','=','Activo')
                // ->whereIN('comp.codigo_old',['PedidoAFarm'])
                ->where('comp.idcomprobante',$id)
                ->orderBy('comp.fecha_hora','desc')
                ->get();
                // dd($comprobante);
                foreach($comprobante as $key=> $data){
    
                    if($data->tipoarea=="CE"){
                        $comprobante[$key]->area_selec="CONSULTA EXTERNA";
                        $comprobante[$key]->servicio_selec=$data->espec_med;
                    }else if($data->tipoarea=="Emergencia"){
                        $comprobante[$key]->area_selec="EMERGENCIA";
                        if($data->id_servicio==9000 || $data->id_servicio==31){
                            $comprobante[$key]->servicio_selec="TRIAGE";
                        }else{
                            $comprobante[$key]->servicio_selec="AMBULATORIO";
                        }
                        
                    }else{
                        $comprobante[$key]->area_selec="HOSPITALIZACION";
                        if(!is_null($data->dependencia)){
                            $comprobante[$key]->servicio_selec=$data->dependencia;
                        }else{
                            $comprobante[$key]->servicio_selec=$data->area;
                        }
                        // $comprobante[$key]->servicio_selec=$data->dependencia;
                    }
                }

            }
            else{
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'La solicitud aun no a sido respondida'
                ]);
            } 
            $nombrePDF="rollo_paciente.pdf";
            $pdf=\PDF::loadView('reportes.rollo_receta',['comprobante'=>$comprobante[0]]);
            // $pdf->setPaper([0, 0, 180,  597]);
            $pdf->setPaper([0, 0, 220,  297]); 
            // 50 o.25ml

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

    public function reporteIngresoBodGral($id, $bodega){
        
        try{
           
            set_time_limit(0);
            ini_set("memory_limit",-1);
            ini_set('max_execution_time', 0);

            if($bodega==1 || $bodega==17){ //medicamentos
                $comprobante=Comprobante::with('detalle','entregado','recibido','bodega','tipoIngreso','proveedor','tipoIngreso','devolucion')->where('idcomprobante',$id)
                ->first();
            }else if($bodega==2 || $bodega==17){ //insumos
                $comprobante=Comprobante::with('detalle_insumo','entregado','recibido','bodega','proveedor','tipoIngreso','devolucion')->where('idcomprobante',$id)
                ->first();
            }else if($bodega==8 || $bodega==13 || $bodega==14 || $bodega==19 || $bodega==23 || $bodega==24) { //laboratorios
                $comprobante=Comprobante::with('detalle_lab','entregado','recibido','bodega','proveedor','tipoIngreso','devolucion')->where('idcomprobante',$id)
                ->first();
            }else if($bodega==30){//proteccion
                $comprobante=Comprobante::with('detalle_proteccion','entregado','recibido','bodega','proveedor','tipoIngreso','devolucion')->where('idcomprobante',$id)
                ->first();
            }
            else{//items
                $comprobante=Comprobante::with('detalle_item','entregado','recibido','bodega','proveedor','tipoIngreso','devolucion')->where('idcomprobante',$id)
                ->first();
            }
            
            $jefa= DB::connection('pgsql')->table('bodega.per_perfil')
            ->where('descripcion','Jefe Bodega')
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
                      
            if($bodega==1 || $bodega==17){  
                $pdf=\PDF::loadView('reportes.ingreso_bod',['comprobante'=>$comprobante,'jefa'=>$dato]);
            }else if($bodega==2 || $bodega==18){
                $pdf=\PDF::loadView('reportes.ingreso_bod_ins',['comprobante'=>$comprobante,'jefa'=>$dato]);
            }else if($bodega==8 || $bodega==13 || $bodega==14 || $bodega==19 || $bodega==23 || $bodega==24){
                $pdf=\PDF::loadView('reportes.ingreso_bod_lab',['comprobante'=>$comprobante,'jefa'=>$dato]);
            }else if($bodega==30) {
                $pdf=\PDF::loadView('reportes.ingreso_bod_protecc',['comprobante'=>$comprobante,'jefa'=>$dato]);
                // $pdf=\PDF::loadView('reportes.test_impresora',['comprobante'=>$comprobante,'jefa'=>$dato]);
            }else{
                $pdf=\PDF::loadView('reportes.ingreso_bod_mat',['comprobante'=>$comprobante,'jefa'=>$dato]);
                // $pdf=\PDF::loadView('reportes.test_impresora',['comprobante'=>$comprobante,'jefa'=>$dato]);
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
    
    public function reporteEgresoBodFarm($id, $bodega){
        
        try{
            
            set_time_limit(0);
            ini_set("memory_limit",-1);
            ini_set('max_execution_time', 0);

            if($bodega==6 || $bodega==20){
                $comprobante=Comprobante::with('detalle','entregado','recibido','bodega')->where('idcomprobante',$id)
                ->first();
            }else if($bodega==7 || $bodega==21 || $bodega==2 ){
                $comprobante=Comprobante::with('detalle_insumo','entregado','recibido','bodega')->where('idcomprobante',$id)
                ->first();
               
            }else if($bodega==31 || $bodega==32 || $bodega==33){
                $comprobante_cons= DB::connection('pgsql')->table('bodega.comprobante as comp')
                ->leftJoin('bodega.bodega as b', 'b.idbodega','comp.idbodega')
                ->leftJoin('public.users as usu', 'usu.id','comp.id_usuario_ingresa')
                ->leftJoin('inventario.persona as per', 'per.idper','usu.id_persona')
                ->leftJoin('bodega.detalle_comprobante as detcomp', 'detcomp.idcomprobante','comp.idcomprobante')
                ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                ->leftJoin('bodega.medicamentos as medi', 'medi.coditem','detcomp.id_item')
                ->leftJoin('bodega.insumo as i', 'i.codinsumo','detcomp.id_item')        
                ->select(DB::raw("CONCAT(medi.nombre,' - ', medi.concentra,' - ', medi.forma) AS nombre_item_med"),DB::raw("CONCAT(i.insumo) AS nombre_item_insumo"),'pedido.lote','pedido.fecha_caducidad','pedido.cantidad_entregada','pedido.cantidad_pedida','medi.codigo as codigo_esbay_med', 'detcomp.iddetalle_comprobante as iddetalle','detcomp.id_item','i.codigo as codigo_esbay_ins','comp.descripcion','comp.secuencial',DB::raw("CONCAT(ape1, ' - ',ape2, ' - ',nom1, ' - ',nom2) AS responsable"),'b.nombre','b.nombre','detcomp.precio','detcomp.total')
                ->where('comp.idcomprobante',$id)
                ->where('comp.estado','Activo')          
                ->get();
              
                $comprobante=Comprobante::with('detalle_lab','entregado','recibido','bodega')->where('idcomprobante',$id)
                ->first();
                // dd($comprobante->entregado);

            }else{
                $comprobante=Comprobante::with('detalle_lab','entregado','recibido','bodega')->where('idcomprobante',$id)
                ->first();
            }
          
            $nombrePDF=$comprobante->descripcion.".pdf";
            // dd($comprobante);
           
            if($bodega==6 || $bodega==20){
                $pdf=\PDF::loadView('reportes.egreso_bod_med',['comprobante'=>$comprobante]);
            }else if($bodega==7 || $bodega==21 || $bodega==2 ){ 
                $pdf=\PDF::loadView('reportes.egreso_bod_ins',['comprobante'=>$comprobante]);
            }else if($bodega==31){
                $pdf=\PDF::loadView('reportes.farmacia.egreso_paquete_dialisis',['comprobante'=>$comprobante_cons]);
            }else if($bodega==32 || $bodega==33){  
                $pdf=\PDF::loadView('reportes.farmacia.egreso_paquete_cq',['comprobante'=>$comprobante_cons, 'comprobante_'=>$comprobante]);
            }else{
                $pdf=\PDF::loadView('reportes.egreso_bod_lab',['comprobante'=>$comprobante]);
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


    public function reporteEgresoBodGral($id, $bodega){
        
        try{
           
            set_time_limit(0);
            ini_set("memory_limit",-1);
            ini_set('max_execution_time', 0);

            if($bodega==1 || $bodega==17){//medicamentos
                $comprobante=Comprobante::with('detalle','entregado','recibido','bodega')->where('idcomprobante',$id)
                ->first();
            }else if($bodega==2 || $bodega==18){//insumos
                $comprobante=Comprobante::with('detalle_insumo','entregado','recibido','bodega')->where('idcomprobante',$id)
                ->first();
            }else if($bodega==8 || $bodega==13 || $bodega==14 || $bodega==19 || $bodega==23 || $bodega==24) { //laboratorios
                $comprobante=Comprobante::with('detalle_lab','entregado','recibido','bodega','proveedor','tipoIngreso','devolucion')->where('idcomprobante',$id)
                ->first();
            }else if($bodega==30){//proteccion
                $comprobante=Comprobante::with('detalle_proteccion','entregado','recibido','bodega')->where('idcomprobante',$id)
                ->first();
            }else{
                $comprobante=Comprobante::with('detalle_item','entregado','recibido','bodega')->where('idcomprobante',$id)
                ->first();
            }
            
            if($comprobante->codigo_old=="Pedido"){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'La solicitud aun no a sido respondida'
                ]);
            }

            $nombrePDF=$comprobante->descripcion.".pdf";

            if($bodega==1 || $bodega==17){
                $pdf=\PDF::loadView('reportes.egreso_bod_med',['comprobante'=>$comprobante]);
            }else if($bodega==2 || $bodega==18){
                $pdf=\PDF::loadView('reportes.egreso_bod_ins',['comprobante'=>$comprobante]);
            }else if($bodega==8 || $bodega==13 || $bodega==14 || $bodega==19 || $bodega==23 || $bodega==24){
                $pdf=\PDF::loadView('reportes.egreso_bod_lab',['comprobante'=>$comprobante]);
            }else if($bodega==30){
                $pdf=\PDF::loadView('reportes.egreso_bod_proteccion',['comprobante'=>$comprobante]);
            }else{
                $pdf=\PDF::loadView('reportes.egreso_bod_item',['comprobante'=>$comprobante]);
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

    public function reporteAntesTransferencia($id, $bodega){

        try{
            
            set_time_limit(0);
            ini_set("memory_limit",-1);
            ini_set('max_execution_time', 0);

            if($bodega==1 || $bodega==17 || $bodega==20 || $bodega==6){ //medicamnentos
                $comprobante=Comprobante::with('detalle','entregado','responsable','nomarea','paciente','cie','especialidad')->where('idcomprobante',$id)
                ->first();
                

            }else if($bodega==2 || $bodega==18 || $bodega==21 || $bodega==7){ //insumos
                $comprobante=Comprobante::with('detalle_insumo','entregado','responsable','nomarea','paciente','cie')->where('idcomprobante',$id)
                ->first();
            }else if($bodega==8 || $bodega==13 || $bodega==14 || $bodega==19  || $bodega==22|| $bodega==23 || $bodega==24 || $bodega==25 || $bodega==26 || $bodega==27 || $bodega==28|| $bodega==29) { //laboratorios
                $comprobante=Comprobante::with('detalle_lab','entregado','recibido','bodega','proveedor','tipoIngreso','devolucion')->where('idcomprobante',$id)
                ->first();
            }else if($bodega==31 || $bodega==32  || $bodega==33){
                $datos= DB::connection('pgsql')->table('bodega.comprobante as comp')
                ->leftJoin('bodega.area as a', 'a.id_area','comp.area')
                ->leftJoin('bodega.bodega as b', 'b.idbodega','comp.idbodega')
                ->leftJoin('public.users as u', 'u.id','comp.id_usuario_ingresa')
                ->leftJoin('inventario.persona as per', 'per.idper','u.id_persona')
              
                ->leftJoin('bodega.detalle_comprobante as detcomp', 'detcomp.idcomprobante','comp.idcomprobante')
                ->leftJoin('bodega.pedido_bod_gral as pedido', 'pedido.iddetallecomprobante','detcomp.iddetalle_comprobante')
                ->leftJoin('bodega.medicamentos as medi', 'medi.coditem','detcomp.id_item')
                ->leftJoin('bodega.insumo as i', 'i.codinsumo','detcomp.id_item')        
                ->select(DB::raw("CONCAT(medi.nombre,' - ', medi.concentra,' - ', medi.forma) AS nombre_item_med"),DB::raw("CONCAT(i.insumo) AS nombre_item_insumo"),'pedido.lote','pedido.fecha_caducidad','pedido.cantidad_entregada','pedido.cantidad_pedida','medi.codigo as codigo_esbay_med', 'detcomp.precio as precio_item','detcomp.id_item','i.codigo as codigo_esbay_ins','comp.descripcion','comp.secuencial',DB::raw("CONCAT(per.ape1, ' - ',per.ape2, ' - ',per.nom1, ' - ',per.nom2) AS solicita"),'b.nombre','b.nombre','comp.codigo_old','a.descripcion as nombre_area')
                ->where('comp.idcomprobante',$id)
                ->where('comp.estado','Activo')          
                ->get();

                foreach($datos as $key => $data){            
                    if($data->id_item>=30000){
                        $nombre_item=$data->nombre_item_insumo;
                      
                    }else{
                        $nombre_item=$data->nombre_item_med;
                      
                    }
                    $datos[$key]->nombre_item_selecc=$nombre_item;
                       
                }
               
                $comprobante=Comprobante::with('entregado','responsable','paciente','cie')->find($id);

            }else{
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'Opcion no disponible para la bodega seleccionada'
                ]);
            }
           
            $bodega_cons=Bodega::where('idbodega',$bodega)->first();
            $nombrePDF=$comprobante->descripcion.".pdf";
           
            if($bodega==1 || $bodega==17 || $bodega==20 | $bodega==6){
                if($bodega==20 || $bodega==6){
                    $farm="S";
                    $detalleReceta=[];
                    if($comprobante->id_comp_receta){
                        //detalle receta
                        $detalleReceta=DB::table('inventario.detallecomprobante as dc')
                        ->where('idcomprobante',$comprobante->id_comp_receta)
                        ->leftJoin('inventario.detallereceta as dr','dr.iddetalle','dc.iddetalle')
                        ->select('dr.dosis','dr.frec','dr.duracion','dr.iddetalle','dc.idcomprobante','dr.uso')
                        ->get();
                        
                    }
                 
                }else{
                    $farm="N";
                    $detalleReceta=[];
                }
                $pdf=\PDF::loadView('reportes.antes_validacion.previo_trans_med',['comprobante'=>$comprobante,'bodega_cons'=>$bodega_cons,'farm'=>$farm,'detalleReceta'=>$detalleReceta]);

            }else if($bodega==2 || $bodega==18 || $bodega==21 || $bodega==7){

                if($bodega==21 || $bodega==7){
                    $farm="S";
                    $pedidos= DB::connection('pgsql')->table('bodega.comprobante as comp')
                    ->leftJoin('esq_catalogos.especialidad as esp', 'esp.id_especialidad','comp.id_especialidad')
                    ->leftJoin('public.users as usu', 'usu.id','comp.id_usuario_ingresa')
                    ->leftJoin('inventario.persona as per', 'per.idper','usu.id_persona')
                    ->leftJoin('bodega.area as a', 'a.id_area','comp.area')
                    ->leftJoin('esq_catalogos.servicio as s', 's.id_servicio','comp.id_servicio')
                    ->leftJoin('esq_pacientes.pacientes as per_pac', 'per_pac.id_paciente','comp.id_paciente')
                  
                    ->leftJoin('bodega.bodega as bod', 'bod.idbodega','comp.idbodega')
                    ->select('comp.descripcion','comp.secuencial','comp.fecha_hora','comp.observacion','comp.total','comp.id_usuario_ingresa', 'comp.idcomprobante','bod.nombre as nombre_bodega','comp.idbodega',DB::raw("CONCAT(per.ape1,' ', per.ape2,' ', per.nom1,' ', per.nom2) AS solicita"),"comp.codigo_old","a.descripcion as area",DB::raw("CONCAT(per_pac.apellido1,' ', per_pac.apellido2,' ', per_pac.nombre1,' ', per_pac.nombre2) AS paciente"),'per_pac.documento','s.nombre as dependencia','comp.area as id_area','comp.id_servicio','comp.id_especialidad','esp.nombre as espec_med','comp.tipoarea')
                    ->where('comp.estado','=','Activo')
                    ->where('comp.idcomprobante',$id)
                    
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
                    $pedidos=$pedidos[0];
                }else{
                    $farm="N";
                    $pedidos=[];
                }
                // dd($pedidos);
                
                $pdf=\PDF::loadView('reportes.antes_validacion.previo_trans_ins',['comprobante'=>$comprobante,'bodega_cons'=>$bodega_cons,'farm'=>$farm, 'pedidos'=>$pedidos]);

            }else if($bodega==8 || $bodega==13 || $bodega==14 || $bodega==19  || $bodega==22|| $bodega==23 || $bodega==24 || $bodega==25 || $bodega==26 || $bodega==27 || $bodega==28|| $bodega==29){
                if($bodega==22 || $bodega==25 || $bodega==26 || $bodega==27 || $bodega==28|| $bodega==29){
                    $farm="S";
                }else{
                    $farm="N";
                }
                $pdf=\PDF::loadView('reportes.antes_validacion.previo_trans_lab',['comprobante'=>$comprobante,'bodega_cons'=>$bodega_cons,'farm'=>$farm]);
            }else if($bodega==31 || $bodega==32 || $bodega==33){
                $farm="S";
                $pdf=\PDF::loadView('reportes.antes_validacion.previo_paquete',['comprobante'=>$comprobante,'datos'=>$datos,'bodega_cons'=>$bodega_cons,'farm'=>$farm]);
                
            }
            
            if($bodega==20 || $bodega==6 || $bodega==21 || $bodega==7 || $bodega==22 || $bodega==25 || $bodega==26 || $bodega==27 || $bodega==28|| $bodega==29){
                $pdf->setPaper([0, 0, 180,  597]);
            }else{
                $pdf->setPaper([0, 0, 180,  597]);
            }
               

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


    public function descargarPdf($archivo){
        try{   
        
            $exists_destino = \Storage::disk('public')->exists($archivo); 

            if($exists_destino){
                return response()->download( storage_path('app/public/'.$archivo))->deleteFileAfterSend(true);
            }else{
                return back()->with(['error'=>'Ocurrió un error','estadoP'=>'danger']);
            } 

        } catch (\Throwable $th) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return back()->with(['error'=>'Ocurrió un error','estadoP'=>'danger']);
        } 
    }

  
    public function visualizarDoc($documentName){
        try {
             
            $info = new SplFileInfo($documentName);
            $extension = $info->getExtension();
            if($extension!= "pdf" && $extension!="PDF"){
                return \Storage::disk('public')->download($documentName);
            }else{
                // obtenemos el documento del disco en base 64
                $documentEncode= base64_encode(\Storage::disk('public')->get($documentName));
                return view("gestion_bodega.vistaPrevia")->with([
                    "documentName"=>$documentName,
                    "documentEncode"=>$documentEncode
                ]);        
            }            
        } catch (\Throwable $th) {
            Log::error("AprobacionEntregaController =>visualizardoc => Mensaje =>".$th->getMessage());
            abort("404");
        }

    }

}
