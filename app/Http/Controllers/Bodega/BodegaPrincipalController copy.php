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


class BodegaPrincipalController extends Controller
{
    
    public function index(){

        $proveedor= DB::connection('pgsql')->table('inventario.proveedor')
        ->where('estado1',1)
        ->get();
        
        $tipo_ingreso= DB::connection('pgsql')->table('bodega.tipo_ingreso')
        ->where('estado',1)->get();

        $bodega= DB::connection('pgsql')->table('bodega.bodega')
        ->where('idtipobod',1)
        ->where('estado',1)
        ->get();

        return view('gestion_bodega.ingreso_bodega_general',[
            "proveedor"=>$proveedor,
            "tipo_ingreso"=>$tipo_ingreso,
            "bodega"=>$bodega
        ]);
    }

    public function buscarMedicamentos($text){
        try{
           
            $medicamentos= DB::connection('pgsql')->table('bodega.medicamentos')
            ->where(function($c)use($text) {
                $c->where('nombre', 'like', '%'.$text.'%');
            })
            ->select('coditem as codigo_item',DB::raw("CONCAT(nombre,' - ', concentra,' - ', forma,' - ', presentacion) AS detalle"), 'cum as codi')
            ->where('activo','VERDADERO')
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
                // $c->where('insumo', 'ilike', '%'.$text.'%');
            })
            ->select('codinsumo as codigo_item','insumo as detalle', 'cudim as codi')
            ->where('codinsumo',31785)
            // ->where('activo','VERDADERO')
            // ->where('proxbode.idbodega',2)
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

    public function buscarLaboratorioMat($text){
        try{
           
            $insumos= DB::connection('pgsql')->table('bodega.items')
            ->where(function($c)use($text) {
                $c->where('descri', 'ilike', '%'.$text.'%');
            })
           
            ->select('codi_it as codigo_item','descri as detalle', 'codigo as codi')
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
           
            $insumos= DB::connection('pgsql')->table('bodega.items')
            ->where(function($c)use($text) {
                $c->where('descri', 'ilike', '%'.$text.'%');
            })
           
            ->select('codi_it as codigo_item','descri as detalle', 'codigo as codi')
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
           
            $insumos= DB::connection('pgsql')->table('bodega.items')
            ->where(function($c)use($text) {
                $c->where('descri', 'ilike', '%'.$text.'%');
            })
           
            ->select('codi_it as codigo_item','descri as detalle', 'codigo as codi')
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

    public function buscarMedicamentosLote($text){
        try{
           
            $medicamentos= DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
            ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
            ->leftJoin('bodega.medicamentos as med', 'med.coditem','proxbode.idprod')
            ->where('proxbode.idbodega',1)
            ->where('proxbode.existencia','>',0)
            ->where(function($c)use($text) {
                $c->where('nombre', 'like', '%'.$text.'%');
            })
            ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion) AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod')
            ->get();
            
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

    public function buscarInsumosLote($text){
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
            Log::error('BodegaPrincipalController => buscarInsumosLote => mensaje => '.$e->getMessage());
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
            ->select('lot.lote', 'lot.fcaduca','lot.regsan',DB::raw("CONCAT(item.descri,' - ', item.presen) AS detalle"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod')
        
            ->get();
            
            
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


    public function guardarIngreso(Request $request){
            
        $validator = Validator::make($request->all(), [
            'cmb_proveedor' => 'required',
            'cmb_bodega' => 'required',
            'tipo_ingreso_cmb' => 'required',
           
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario','error'=>true]);
        }

        $transaction=DB::transaction(function() use ($request){
            try{

                $tipocomp= TipoComprobante::where('idtipocomprobante',3)
                ->first();

                //registramos la cabecera
                $comprobante=new Comprobante();
                $comprobante->idtipo_comprobante=3;
                $comprobante->secuencial=$tipocomp->numcomp+1;
                $comprobante->descripcion=$tipocomp->mintipo;
                $comprobante->fecha_hora=date('Y-m-d H:i:s');
                $comprobante->fecha=date('Y-m-d');
                $comprobante->idbodega=$request->cmb_bodega;
                $comprobante->tipo=$request->tipo_ingreso_cmb;
                $comprobante->subtotal=$request->total_suma;
                $comprobante->total=$request->total_suma;              
                $comprobante->id_proveedor=$request->cmb_proveedor;
                $comprobante->id_usuario_ingresa=auth()->user()->id;
               

                if($comprobante->save()){

                    //datos detalle
                    $idmedicina_selecc=$request->idmedicina_selecc;
                    $idbodega_selecc=$request->idbodega_selecc; 
                    $cantidad=$request->cantidad;
                    $precio=$request->precio;
                    $descuento=$request->descuento;
                    $fecha_elab_=$request->fecha_elab_;
                    $fecha_caduc=$request->fecha_caduc;
                    $lote=$request->lote;
                    $reg_sani=$request->reg_sani;                    
                    $total=$request->total;                  
                  
                    $cont=0;
                    $subtota_comprobante=0;
                    //registramos los detalles localmente
                    while($cont < count($idmedicina_selecc)){

                        $subtota_comprobante=$subtota_comprobante + number_format(($descuento[$cont]),2,'.', '');
                      
                        $detalles=new DetalleComprobante();
                        $detalles->idcomprobante=$comprobante->idcomprobante;
                        $detalles->id_item=$idmedicina_selecc[$cont];
                        $detalles->id_bodega=$idbodega_selecc[$cont];
                        $detalles->cantidad=$cantidad[$cont];
                        $detalles->precio=number_format(($precio[$cont]),2,'.', '');
                        $detalles->descuento=number_format(($descuento[$cont]),2,'.', '');
                        $detalles->total=number_format(($total[$cont]),2,'.', '');
                        $detalles->iva=0;
                        $detalles->fecha=date('Y-m-d H:i:s');
                        $detalles->save(); 
                        
                        $existencia=new Existencia();
                        $existencia->iddetalle_comprobante=$detalles->iddetalle_comprobante;
                        $existencia->lote=$lote[$cont];
                        $existencia->suma=$cantidad[$cont];
                        $existencia->tipo="Ingreso";
                        $existencia->fecha_hora=date('Y-m-d H:i:s');
                        $existencia->reg_sanitario=$reg_sani[$cont];
                        $existencia->fecha_elaboracion=$fecha_elab_[$cont];
                        $existencia->fecha_caducidad=$fecha_caduc[$cont];
                        $existencia->fecha=date('Y-m-d');
                        $existencia->save();   

                        //ultimo
                        $ultimo =ProductoBodega::orderBy('idbodprod','desc')->first();
                        $sumauno=$ultimo->idbodprod;
                        
                        $ProductoBodegaOld=new ProductoBodega();
                        $ProductoBodegaOld->idbodprod=$sumauno+1;
                        $ProductoBodegaOld->idprod=$detalles->id_item;
                        $ProductoBodegaOld->idbodega=$detalles->id_bodega;
                        $ProductoBodegaOld->existencia=$detalles->cantidad;
                        $ProductoBodegaOld->precio=$detalles->precio;
                        $ProductoBodegaOld->precio2=0;
                        $ProductoBodegaOld->save(); 

                        $ultimolote =LoteProducto::orderBy('idlote','desc')->first();
                        $sumaunolote=$ultimolote->idlote;

                        $LoteProductoOld=new LoteProducto();
                        $LoteProductoOld->idlote=$sumaunolote+1;
                        $LoteProductoOld->idbodp=$ProductoBodegaOld->idbodprod;
                        $LoteProductoOld->lote=$existencia->lote;
                        $LoteProductoOld->felabora=$existencia->fecha_elaboracion;
                        $LoteProductoOld->fcaduca=$existencia->fecha_caducidad;
                        $LoteProductoOld->regsan=$existencia->reg_sanitario;
                        $LoteProductoOld->save(); 
                        
                        if($detalles->id_bodega==1){//medicamento
                            $actualizaStock=Medicamento::where('coditem',$detalles->id_item)
                            ->first();
                            $stock_Actual=$actualizaStock->stock_bod;
                            $actualizaStock->stock_bod=$stock_Actual + $detalles->cantidad;
                            $actualizaStock->save();  

                        }else if($detalles->id_bodega==2){//insumo
                            $actualizaInsumo=Insumo::where('codinsumo',$detalles->id_item)
                            ->first();
                            $stock_Actual=$actualizaStock->stockbod;
                            $actualizaInsumo->stockbod=$stock_Actual + $detalles->cantidad;
                            $actualizaInsumo->save(); 

                        }else if($detalles->id_bodega==8){//bodega de laboratorio materiales
                            $actualizaInsumo=Item::where('codi_it',$detalles->id_item)
                            ->first();
                            $stock_Actual=$actualizaStock->stock;
                            $actualizaInsumo->stock=$stock_Actual + $detalles->cantidad;
                            $actualizaInsumo->save(); 
                        } else if($detalles->id_bodega==13){//bodega de laboratorio reactivos
                            $actualizaInsumo=Item::where('codi_it',$detalles->id_item)
                            ->first();
                            $stock_Actual=$actualizaStock->stock;
                            $actualizaInsumo->stock=$stock_Actual + $detalles->cantidad;
                            $actualizaInsumo->save(); 
                        }
                        else {//bodega de laboratorio microbiologia (14)
                            $actualizaInsumo=Item::where('codi_it',$detalles->id_item)
                            ->first();
                            $stock_Actual=$actualizaStock->stock;
                            $actualizaInsumo->stock=$stock_Actual + $detalles->cantidad;
                            $actualizaInsumo->save(); 
                        }                  
                    
                        $cont=$cont+1;
                    } 
                    
                    $tipocomp->numcomp=$comprobante->secuencial;
                    $tipocomp->save();

                    //si tofdo ok el comprobante se crea
                    $comprobante_crear=Comprobante::find($comprobante->idcomprobante);
                    $comprobante_crear->estado="Activo";
                    $comprobante_crear->save();

                    return (['mensaje'=>'Informacion ingresada exitosamente','error'=>false]);
                }        


            } catch (\Throwable $e) {
                DB::rollback();
                Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
                return (['mensaje'=>'Ocurrió un error,intentelo más tarde','error'=>true]); 
            }
        });
        return ($transaction);
    }

    public function listado(){

        return view('gestion_bodega.listado_ingreso_bod_gral');
    }

    public function filtrarIngreso($ini, $fin){
        
        try{
            $permisos= DB::connection('pgsql')->table('bodega.comprobante as comp')
            ->leftJoin('inventario.proveedor as pr', 'pr.idprov','comp.id_proveedor')
            ->where(function($query)use($ini, $fin){
                $query->whereBetween('comp.fecha',[$ini, $fin]);
            })
            ->select('comp.descripcion','comp.secuencial','comp.fecha','pr.empresa','pr.ruc','comp.total','comp.id_usuario_ingresa', 'comp.idcomprobante')
            ->where('comp.estado','=','Activo')
            ->where('idtipo_comprobante',3)
            ->get();

            return response()->json([
                'error'=>false,
                'resultado'=>$permisos
            ]);
        }catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }

    public function listadoEgreso(){

        return view('gestion_bodega.listado_egreso_bod_gral');
    }

    public function filtrarEgresoBodega($ini, $fin){
        
        try{
            $permisos= DB::connection('pgsql')->table('bodega.comprobante as comp')
            ->where(function($query)use($ini, $fin){
                $query->whereBetween('comp.fecha',[$ini, $fin]);
            })
            ->select('comp.descripcion','comp.secuencial','comp.fecha','comp.observacion','comp.total','comp.id_usuario_ingresa', 'comp.idcomprobante')
            ->where('comp.estado','=','Activo')
            ->where('idtipo_comprobante',14)
            ->get();

            return response()->json([
                'error'=>false,
                'resultado'=>$permisos
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

       

        dd($request->all());
        $validator = Validator::make($request->all(), [
            'cmb_bodega' => 'required',           
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario','error'=>true]);
        }

        $transaction=DB::transaction(function() use ($request){
            try{

                $tipocomp= TipoComprobante::where('idtipocomprobante',14)
                ->get()->last();

                //registramos la cabecera
                $comprobante=new Comprobante();
                $comprobante->idtipo_comprobante=3;
                $comprobante->secuencial=$tipocomp->numcomp+1;
                $comprobante->descripcion=$tipocomp->mintipo;
                $comprobante->fecha_hora=date('Y-m-d H:i:s');
                $comprobante->fecha=date('Y-m-d');
                $comprobante->idbodega=$request->cmb_bodega;
                $comprobante->idtipo_comprobante=14;
                $comprobante->area=auth()->user()->persona->id_area;
                $comprobante->codigo_old="EgresoBG";
                // $comprobante->tipo;
                $comprobante->observacion=$request->motivo;
                $comprobante->subtotal=$request->total_suma;
                $comprobante->total=$request->total_suma;              
               
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

                        $detalles=new DetalleComprobante();
                        $detalles->idcomprobante=$comprobante->idcomprobante;
                        $detalles->id_item=$idmedicina_selecc[$cont];
                        $detalles->id_bodega=$idbodega_selecc[$cont];
                        $detalles->cantidad=$cantidad[$cont];
                        $detalles->precio=number_format(($precio[$cont]),2,'.', '');
                        $detalles->descuento=0;
                        $detalles->total=number_format(($total[$cont]),2,'.', '');
                        $detalles->iva=0;
                        $detalles->fecha=date('Y-m-d H:i:s');
                        $detalles->save(); 
                        
                        $existencia=new Existencia();
                        $existencia->iddetalle_comprobante=$detalles->iddetalle_comprobante;
                        $existencia->lote=$lote[$cont];
                        $existencia->resta=$cantidad[$cont];
                        $existencia->tipo="Egreso";
                        $existencia->fecha_hora=date('Y-m-d H:i:s');
                        $existencia->reg_sanitario=$reg_sani[$cont];
                        $existencia->fecha_elaboracion=$fecha_elab_[$cont];
                        $existencia->fecha_caducidad=$fecha_caduc[$cont];
                        $existencia->fecha=date('Y-m-d');
                        $existencia->save();   

                        //actualizamos el stock en la tabla productobodega
                        $actualizaStockOld =ProductoBodega::where('idbodprod',$idbodega_producto[$cont])->first();
                        $nuevoStock=$actualizaStockOld->existencia;
                        $nuevoStock_act=$nuevoStock - $existencia->resta;
                        $actualizaStockOld->existencia=$nuevoStock_act;                        
                        $actualizaStockOld->save(); 
                        
                        if($detalles->id_bodega==1){//medicamento
                            $actualizaStock=Medicamento::where('coditem',$detalles->id_item)
                            ->first();
                            $stock_Actual=$actualizaStock->stock_bod;
                            $actualizaStock->stock_bod=$stock_Actual - $detalles->cantidad;
                            $actualizaStock->save();  

                        }else if($detalles->id_bodega==2){//insumo
                            $actualizaInsumo=Insumo::where('codinsumo',$detalles->id_item)
                            ->first();
                            $stock_Actual=$actualizaStock->stockbod;
                            $actualizaInsumo->stockbod=$stock_Actual - $detalles->cantidad;
                            $actualizaInsumo->save(); 
                        
                        }else if($detalles->id_bodega==8){//bodega de laboratorio materiales
                            $actualizaInsumo=Item::where('codi_it',$detalles->id_item)
                            ->first();
                            $stock_Actual=$actualizaInsumo->stock;
                            $actualizaInsumo->stock=$stock_Actual - $detalles->cantidad;
                            $actualizaInsumo->save(); 
                        } else if($detalles->id_bodega==13){//bodega de laboratorio reactivos
                            $actualizaInsumo=Item::where('codi_it',$detalles->id_item)
                            ->first();
                            $stock_Actual=$actualizaInsumo->stock;
                            $actualizaInsumo->stock=$stock_Actual - $detalles->cantidad;
                            $actualizaInsumo->save(); 
                        }
                        else {//bodega de laboratorio microbiologia (14)
                            $actualizaInsumo=Item::where('codi_it',$detalles->id_item)
                            ->first();
                            $stock_Actual=$actualizaInsumo->stock;
                            $actualizaInsumo->stock=$stock_Actual - $detalles->cantidad;
                            $actualizaInsumo->save(); 
                        }      
                    
                        $cont=$cont+1;
                    } 
                    
                    $tipocomp->numcomp=$comprobante->secuencial;
                    $tipocomp->save();

                    //si tofdo ok el comprobante se crea
                    $comprobante_crear=Comprobante::find($comprobante->idcomprobante);
                    $comprobante_crear->estado="Activo";
                    $comprobante_crear->save();

                    return (['mensaje'=>'Informacion ingresada exitosamente','error'=>false]);
                }        


            } catch (\Throwable $e) {
                DB::rollback();
                Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
                return (['mensaje'=>'Ocurrió un error,intentelo más tarde','error'=>true]); 
            }
        });
        return ($transaction);
    }


    

  

}
