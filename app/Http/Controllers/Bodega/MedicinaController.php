<?php

namespace App\Http\Controllers\Bodega;
use App\Http\Controllers\Controller;
use App\Models\Personal\Especialidad;
use \Log;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator; 
use App\Models\Bodega\Medicamento; 
use App\Models\Bodega\Insumo; 
use App\Models\Bodega\Item;
use App\Models\Bodega\Laboratorio;
use App\Models\Bodega\InsumoAreaEspecialidad;
use App\Models\Bodega\MedicamentoAreaEspecialidad;
use App\Models\Bodega\AreaEspecialidad;

class MedicinaController extends Controller
{
   
    public function guardaMedicina(Request $request){
     
        $validator = Validator::make($request->all(), [
            'codigo' => 'required', 
            'nombre_med' => 'required',          
            'concentracion_med' => 'required',
            'forma_med' => 'required',
            'presentacion_med' => 'required',
            // 'stock_min' => 'required',
            // 'stock_cri' => 'required',
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario','error'=>true]);
        }
      
        
        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{

                //comprobamos que no haya sido ingresado
                $verifica=Medicamento::where('cum',$request->codigo)
                ->first();
                
                if(!is_null($verifica)){
                    //si ya existe activo
                    if($verifica->activo=="VERDADERO"){
                        return (['mensaje'=>'Ya existe un medicamento con el codigo ingresado','error'=>true]);
                    }else{
                        //lo actualizamos
                        $verifica->cum=$request->codigo;
                        $verifica->nombre=$request->nombre_med;
                        $verifica->codigo=$request->cod_esbay_med;
                        $verifica->concentra=$request->concentracion_med;
                        $verifica->presentacion=$request->presentacion_med;
                        $verifica->forma=$request->forma_med;
                        $verifica->activo="VERDADERO";
                        $verifica->stock_min=$request->stock_min;
                        $verifica->stock_cri=$request->stock_cri;
                        if($verifica->save()){
                            return (['mensaje'=>'Informacion ingresadaX exitosamente','error'=>false]);
                        }        

                    }
                }

                $ultimaMed= Medicamento::orderBy('id_medicamento','desc')
                ->first();
              
                //registramos la cabecera
                $med=new Medicamento();
                $med->id_medicamento=$ultimaMed->id_medicamento+1;
                $med->cum=$request->codigo;
                $med->codigo=$request->cod_esbay_med;
                $med->nombre=$request->nombre_med;
                $med->concentra=$request->concentracion_med;
                $med->presentacion=$request->presentacion_med;
                $med->forma=$request->forma_med;
                $med->stock_min=$request->stock_min;
                $med->stock_cri=$request->stock_cri;
                $med->coditem=$ultimaMed->coditem+1;
                $med->activo="VERDADERO";
              
                if($med->save()){
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


    public function actualizaMedicina(Request $request, $id){
     
        $validator = Validator::make($request->all(), [
            'codigo' => 'required', 
            'nombre_med' => 'required',          
            'concentracion_med' => 'required',
            'forma_med' => 'required',
            'presentacion_med' => 'required',
            // 'stock_min' => 'required',
            // 'stock_cri' => 'required',
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario','error'=>true]);
        }

        $transaction=DB::connection('pgsql')->transaction(function() use ($request, $id){
            try{

                //comprobamos que no haya sido ingresado
                $actualiza=Medicamento::where(function($q) {
                    $q->where('es_dialisis', null)
                    ->orWhere('es_dialisis', 'N');
                })
                ->where('coditem',$id)
                ->first();
                        
                //lo actualizamos
                $actualiza->cum=$request->codigo;
                $actualiza->nombre=$request->nombre_med;
                $actualiza->codigo=$request->cod_esbay_med;
                $actualiza->concentra=$request->concentracion_med;
                $actualiza->presentacion=$request->presentacion_med;
                $actualiza->forma=$request->forma_med;
                $actualiza->activo="VERDADERO";
                $actualiza->stock_min=$request->stock_min;
                $actualiza->stock_cri=$request->stock_cri;
                $actualiza->es_dialisis='N';

                $verifica=Medicamento::where('cum',$request->codigo)
                ->where('coditem','!=',$id)
                ->first();

                if(!is_null($verifica)){
                    if($verifica->activo=="VERDADERO"){
                        return (['mensaje'=>'Ya existe un medicamento con el codigo ingresado','error'=>true]);
                    }
                }

                if($actualiza->save()){
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

    public function guardaInsumo(Request $request){
       
        $validator = Validator::make($request->all(), [
            'cudim' => 'required', 
            'insumo' => 'required',          
            // 'stock_min_ins' => 'required',
            // 'stock_cri_ins' => 'required',
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario','error'=>true]);
        }

        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{

                //comprobamos que no haya sido ingresado
                $verifica=Insumo::where('cudim',$request->cudim)
                ->first();
                
                if(!is_null($verifica)){
                    //si ya existe activo
                    if($verifica->activo=="VERDADERO"){
                        return (['mensaje'=>'Ya existe un insumo con el codigo ingresado','error'=>true]);
                    }else{
                        //lo actualizamos
                        $verifica->cudim=$request->cudim;
                        $verifica->codigo=$request->cod_esbay_ins;
                        $verifica->insumo=$request->insumo;
                        $verifica->descrip=$request->desc_ins;
                        $verifica->espetec=$request->espec_tecn;
                        $verifica->idtipoinsu=$request->tipo_ins;
                        $verifica->activo="VERDADERO";
                        $verifica->stockmin=$request->stock_min_ins;
                        $verifica->stockcri=$request->stock_cri_ins;
                        if($verifica->save()){
                            return (['mensaje'=>'Informacion ingresada exitosamente','error'=>false]);
                        }        

                    }
                }

                $ultimoIns= Insumo::orderBy('codinsumo','desc')
                ->first();
              
                //registramos la cabecera
                $ins=new Insumo();
                $ins->codinsumo=$ultimoIns->codinsumo+1;
                $ins->cudim=$request->cudim;
                $ins->codigo=$request->cod_esbay_ins;
                $ins->insumo=$request->insumo;
                $ins->descrip=$request->desc_ins;
                $ins->espetec=$request->espec_tecn;
                $ins->idtipoinsu=$request->tipo_ins;
                $ins->activo="VERDADERO";
                $ins->stockmin=$request->stock_min_ins;
                $ins->stockcri=$request->stock_cri_ins;
                if($ins->save()){
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

    public function actualizaInsumo(Request $request, $id){
       
        $validator = Validator::make($request->all(), [
            'cudim' => 'required', 
            'insumo' => 'required',          
            // 'stock_min_ins' => 'required',
            // 'stock_cri_ins' => 'required',
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario','error'=>true]);
        }

        $transaction=DB::connection('pgsql')->transaction(function() use ($request, $id){
            try{

                //comprobamos que no haya sido ingresado
                $verifica=Insumo::where('cudim',$request->cudim)
                ->where('codinsumo','!=',$id)
                ->first();
                
                if(!is_null($verifica)){
                    //si ya existe activo
                    if($verifica->activo=="VERDADERO"){
                        return (['mensaje'=>'Ya existe un insumo con el codigo ingresado','error'=>true]);
                    }
                }

                $ins=Insumo::where('codinsumo',$id)
                ->first();

                $ins->cudim=$request->cudim;
                $ins->insumo=$request->insumo;
                $ins->descrip=$request->desc_ins;
                $ins->espetec=$request->espec_tecn;
                $ins->idtipoinsu=$request->tipo_ins;
                $ins->activo="VERDADERO";
                $ins->stockmin=$request->stock_min_ins;
                $ins->stockcri=$request->stock_cri_ins;
                if($ins->save()){
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
    
    public function guardaLaboratorio(Request $request){
            
        $validator = Validator::make($request->all(), [
            'cod_lab' => 'required', 
            'desc_lab' => 'required',          
            'idbod' => 'required',
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario','error'=>true]);
        }

        // return (['mensaje'=>'Sistema en mantenimientoz','error'=>true]);

        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{
                if($request->idbod==8){
                    $tipo=5;
                }
                else if($request->idbod==13){
                    $tipo=10;
                }else if($request->idbod==14){
                    $tipo=11;
                }
                //comprobamos que no haya sido ingresado
                $verifica=Laboratorio::where('codigo',$request->cod_lab)
                ->where('idbodega',$request->idbod)
                ->first();                
                if(!is_null($verifica)){
                                        
                    //lo actualizamos
                    $verifica->descri=$request->desc_lab;
                    $verifica->idbodega=$request->idbod;
                    $verifica->codigo=$request->cod_lab;
                    $verifica->esbay=$request->cod_esbay_lab;
                    $verifica->tipoit=$tipo;
                    $verifica->activo=1;
                    $verifica->stockmin=$request->stock_min_lab;
                    $verifica->stockcri=$request->stock_cri_lab;
                    if($verifica->save()){
                        return (['mensaje'=>'Informacion ingresada exitosamente','error'=>false]);
                    }       
                    
                }

                $ultimoLab= Laboratorio::orderBy('id','desc')
                ->first();
              
                //registramos la cabecera
                $lab=new Laboratorio();
                $lab->id=$ultimoLab->id+1;
                $lab->codigo=$request->cod_lab;
                $lab->esbay=$request->cod_esbay_lab;
                $lab->descri=$request->desc_lab;
                $lab->idbodega=$request->idbod;
                $lab->tipoit=$tipo;
                $lab->activo=1;
                $lab->stockmin=$request->stock_min_lab;
                $lab->stockcri=$request->stock_cri_lab;
              
                if($lab->save()){
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

    public function actualizaLaboratorio(Request $request, $id){
          
        $validator = Validator::make($request->all(), [
            'cod_lab' => 'required', 
            'desc_lab' => 'required',          
            'idbod' => 'required',
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario','error'=>true]);
        }

        $transaction=DB::connection('pgsql')->transaction(function() use ($request, $id){
            try{
                if($request->idbod==8){
                    $tipo=5;
                }
                else if($request->idbod==13){
                    $tipo=10;
                }else if($request->idbod==14){
                    $tipo=11;
                }
                //comprobamos que no haya sido ingresado
                $verifica=Laboratorio::where('codigo',$request->cod_lab)
                ->where('idbodega',$request->idbod)
                ->where('id','!=',$id)
                ->first();                
                if(!is_null($verifica)){
                    return (['mensaje'=>'Ya existe un insumo con el codigo ingresado','error'=>true]);
                }

                $lab=Laboratorio::where('idbodega',$request->idbod)
                ->where('id','=',$id)
                ->first();      
                $lab->codigo=$request->cod_lab;
                $lab->esbay=$request->cod_esbay_lab;
                $lab->descri=$request->desc_lab;
                $lab->idbodega=$request->idbod;
                $lab->tipoit=$tipo;
                $lab->activo=1;
                $lab->stockmin=$request->stock_min_lab;
                $lab->stockcri=$request->stock_cri_lab;
                if($lab->save()){
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

    public function guardaItem(Request $request){
      
        $validator = Validator::make($request->all(), [
            'mat_of' => 'required', 
            // 'prese_of' => 'required',          
            'idbodite' => 'required',
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario','error'=>true]);
        }
        // return (['mensaje'=>'Sistema en mantenimiento','error'=>true]);

        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{
                if($request->idbodite==3){
                    $tipo=4;
                }
                else if($request->idbodite==4){
                    $tipo=3;
                }else if($request->idbodite==5){
                    $tipo=6;
                }else if($request->idbodite==9){
                    $tipo=7;
                }else if($request->idbodite==10){
                    $tipo=8;
                }
                //comprobamos que no haya sido ingresado
                $verifica=Item::where('descri',$request->mat_of)
                ->first();                
                if(!is_null($verifica)){
                    return (['mensaje'=>'La informacion ya existe','error'=>true]);
                }

                $ultimoLab= Item::orderBy('codi_it','desc')
                ->first();
              
                //registramos la cabecera
                $item=new Item();
                $item->codi_it=$ultimoLab->codi_it+1;
                $item->descri=$request->mat_of;
                $item->idbodega=$request->idbodite;
                $item->presen=$request->prese_of;
                $item->codigo=$request->codigo_item;
                $item->tipoit=$tipo;
                if($item->save()){
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

    
    public function actualizaItem(Request $request, $id){
              
        $validator = Validator::make($request->all(), [
            'mat_of' => 'required', 
            // 'prese_of' => 'required',          
            'idbodite' => 'required',
            'codigo_item' => 'required'
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario','error'=>true]);
        }

        $transaction=DB::connection('pgsql')->transaction(function() use ($request, $id){
            try{
                if($request->idbodite==3){
                    $tipo=4;
                }
                else if($request->idbodite==4){
                    $tipo=3;
                }else if($request->idbodite==5){
                    $tipo=6;
                }else if($request->idbodite==9){
                    $tipo=7;
                }else if($request->idbodite==10){
                    $tipo=8;
                }

                $verifica=Item::where('codi_it','!=',$id)
                ->where('descri', $request->mat_of)
                ->first(); 
                if(!is_null($verifica)){
                    return (['mensaje'=>'La informacion ya existe','error'=>true]);
                }

                $item=Item::where('codi_it','=',$id)
                ->first();               
                $item->descri=$request->mat_of;
                $item->presen=$request->prese_of;
                $item->tipoit=$tipo;
                $item->codigo=$request->codigo_item;
                if($item->save()){
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


    public function guardaMedicinaDialisis(Request $request){
        
        $validator = Validator::make($request->all(), [
            'codigo_dialisis' => 'required', 
            'nombre_med_dialisis' => 'required',          
            'concentracion_med_dialisis' => 'required',
            'forma_med_dialisis' => 'required',
            'presentacion_med_dialisis' => 'required',
            'stock_min_dialisis' => 'required',
            'stock_cri_dialisis' => 'required',
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario','error'=>true]);
        }

        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{

                //comprobamos que no haya sido ingresado
                $verifica=Medicamento::where('cum',$request->codigo_dialisis)
                ->first();
                
                if(!is_null($verifica)){
                    //si ya existe activo
                    if($verifica->activo=="VERDADERO"){
                        return (['mensaje'=>'Ya existe un medicamento con el codigo ingresado','error'=>true]);
                    }else{
                        //lo actualizamos
                        $verifica->cum=$request->codigo_dialisis;
                        $verifica->codigo=$request->cod_esbay_med;
                        $verifica->nombre=$request->nombre_med_dialisis;
                        $verifica->concentra=$request->concentracion_med_dialisis;
                        $verifica->presentacion=$request->presentacion_med_dialisis;
                        $verifica->forma=$request->forma_med_dialisis;
                        $verifica->activo="VERDADERO";
                        $verifica->stock_min=$request->stock_min_dialisis;
                        $verifica->stock_cri=$request->stock_cri_dialisis;
                        if($verifica->save()){
                            return (['mensaje'=>'Informacion ingresadaX exitosamente','error'=>false]);
                        }        

                    }
                }

                $ultimaMed= Medicamento::orderBy('id_medicamento','desc')
                ->first();
              
                //registramos la cabecera
                $med=new Medicamento();
                $med->id_medicamento=$ultimaMed->id_medicamento+1;
                $med->nombre=$request->nombre_med_dialisis;
                $med->cum=$request->codigo_dialisis;
                $med->codigo=$request->cod_esbay_med;
                $med->concentra=$request->concentracion_med_dialisis;
                $med->presentacion=$request->presentacion_med_dialisis;
                $med->forma=$request->forma_med_dialisis;
                $med->stock_min=$request->stock_min_dialisis;
                $med->stock_cri=$request->stock_cri_dialisis;
                $med->coditem=$ultimaMed->coditem+1;
                $med->activo="VERDADERO";
               
                if($med->save()){
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

    public function actualizaMedicinaDialisis(Request $request, $id){
     
        $validator = Validator::make($request->all(), [
            'codigo_dialisis' => 'required', 
            'nombre_med_dialisis' => 'required',          
            'concentracion_med_dialisis' => 'required',
            'forma_med_dialisis' => 'required',
            'presentacion_med_dialisis' => 'required',
            'stock_min_dialisis' => 'required',
            'stock_cri_dialisis' => 'required',
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario','error'=>true]);
        }

        $transaction=DB::connection('pgsql')->transaction(function() use ($request, $id){
            try{

                //comprobamos que no haya sido ingresado
                $verifica=Medicamento::where('cum',$request->codigo_dialisis)
                ->where('es_dialisis', 'S')
                ->where('coditem','!=',$id)
                ->first();
                
                if(!is_null($verifica)){
                    //si ya existe activo
                    if($verifica->activo=="VERDADERO"){
                        return (['mensaje'=>'Ya existe un medicamento con el codigo ingresado','error'=>true]);
                    }else{
                        //lo actualizamos
                        $verifica->cum=$request->codigo_dialisis;
                        $verifica->nombre=$request->nombre_med_dialisis;
                        $verifica->concentra=$request->concentracion_med_dialisis;
                        $verifica->presentacion=$request->presentacion_med_dialisis;
                        $verifica->forma=$request->forma_med_dialisis;
                        $verifica->activo="VERDADERO";
                        $verifica->stock_min=$request->stock_min_dialisis;
                        $verifica->stock_cri=$request->stock_cri_dialisis;
                        $verifica->es_dialisis='S';
                        if($verifica->save()){
                            return (['mensaje'=>'Informacion ingresadaX exitosamente','error'=>false]);
                        }        

                    }
                }

                $med=Medicamento::where('es_dialisis', 'S')
                ->where('coditem','=',$id)
                ->first();
                $med->nombre=$request->nombre_med_dialisis;
                $med->cum=$request->codigo_dialisis;
                $med->concentra=$request->concentracion_med_dialisis;
                $med->presentacion=$request->presentacion_med_dialisis;
                $med->forma=$request->forma_med_dialisis;
                $med->stock_min=$request->stock_min_dialisis;
                $med->stock_cri=$request->stock_cri_dialisis;
                $med->activo="VERDADERO";
                $med->es_dialisis='S';

                if($med->save()){
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

    public function guardaInsumoDialisis(Request $request){
       
        $validator = Validator::make($request->all(), [
            'cudim_dialisi' => 'required', 
            'insumo_dialisi' => 'required',          
            'stock_min_ins_dialisi' => 'required',
            'stock_cri_ins_dialisi' => 'required',
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario','error'=>true]);
        }



        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{

                //comprobamos que no haya sido ingresado
                $verifica=Insumo::where('cudim',$request->cudim_dialisi)
                              
                ->first();
                
                if(!is_null($verifica)){
                    //si ya existe activo
                    if($verifica->activo=="VERDADERO"){
                        return (['mensaje'=>'Ya existe un insumo con el codigo ingresado','error'=>true]);
                    }else{
                        //lo actualizamos
                        $verifica->cudim=$request->cudim_dialisi;
                        $verifica->insumo=$request->insumo_dialisi;
                        $verifica->codigo=$request->cod_esbay_ins;
                        $verifica->descrip=$request->desc_ins_dialisi;
                        $verifica->espetec=$request->espec_tecn_dialisi;
                        $verifica->especiali="DIALISIS";
                        $verifica->es_dialisis="S";
                        $verifica->activo="VERDADERO";
                        $verifica->stockmin=$request->stock_min_ins_dialisi;
                        $verifica->stockcri=$request->stock_cri_ins_dialisi;
                        if($verifica->save()){
                            return (['mensaje'=>'Informacion ingresada exitosamente','error'=>false]);
                        }        

                    }
                }

                $ultimoIns= Insumo::orderBy('codinsumo','desc')
                ->first();
              
                //registramos la cabecera
                $ins=new Insumo();
                $ins->codinsumo=$ultimoIns->codinsumo+1;
                $ins->cudim=$request->cudim_dialisi;
                $ins->codigo=$request->cod_esbay_ins;
                $ins->insumo=$request->insumo_dialisi;
                $ins->descrip=$request->desc_ins_dialisi;
                $ins->espetec=$request->espec_tecn_dialisi;
                $ins->idtipoinsu=$request->tipo_ins;
                $ins->activo="VERDADERO";
                $ins->especiali="DIALISIS";
                $ins->es_dialisis="S";
                $ins->stockmin=$request->stock_min_ins_dialisi;
                $ins->stockcri=$request->stock_cri_ins_dialisi;
                if($ins->save()){
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

    public function actualizaInsumoDialisis(Request $request, $id){
       
        $validator = Validator::make($request->all(), [
            'cudim_dialisi' => 'required', 
            'insumo_dialisi' => 'required',          
            'stock_min_ins_dialisi' => 'required',
            'stock_cri_ins_dialisi' => 'required',
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario','error'=>true]);
        }

        $transaction=DB::connection('pgsql')->transaction(function() use ($request, $id){
            try{

                //comprobamos que no haya sido ingresado
                $verifica=Insumo::where('cudim',$request->cudim_dialisi)
                ->where('es_dialisis','S')
                ->where('codinsumo','!=',$id)               
                ->first();
                
                if(!is_null($verifica)){
                    //si ya existe activo
                    if($verifica->activo=="VERDADERO"){
                        return (['mensaje'=>'Ya existe un insumo con el codigo ingresado','error'=>true]);
                    }else{
                        //lo actualizamos
                        $verifica->cudim=$request->cudim_dialisi;
                        $verifica->insumo=$request->insumo_dialisi;
                        $verifica->descrip=$request->desc_ins_dialisi;
                        $verifica->espetec=$request->espec_tecn_dialisi;
                        $verifica->especiali="DIALISIS";
                        $verifica->es_dialisis="S";
                        $verifica->activo="VERDADERO";
                        $verifica->stockmin=$request->stock_min_ins_dialisi;
                        $verifica->stockcri=$request->stock_cri_ins_dialisi;
                        if($verifica->save()){
                            return (['mensaje'=>'Informacion ingresada exitosamente','error'=>false]);
                        }        

                    }
                }

                $ins=Insumo::where('es_dialisis','S')
                ->where('codinsumo','=',$id)               
                ->first();

                $ins->cudim=$request->cudim_dialisi;
                $ins->insumo=$request->insumo_dialisi;
                $ins->descrip=$request->desc_ins_dialisi;
                $ins->espetec=$request->espec_tecn_dialisi;
                $ins->idtipoinsu=$request->tipo_ins;
                $ins->activo="VERDADERO";
                $ins->especiali="DIALISIS";
                $ins->es_dialisis="S";
                $ins->stockmin=$request->stock_min_ins_dialisi;
                $ins->stockcri=$request->stock_cri_ins_dialisi;
                if($ins->save()){
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

    public function guardaLabDialisis(Request $request){
        
        $validator = Validator::make($request->all(), [
            'cod_lab_ins' => 'required', 
            'desc_lab_ins' => 'required',          
            'idbod_ins' => 'required',
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formularioZ','error'=>true]);
        }
        return (['mensaje'=>'Sistema en mantenimiento','error'=>true]);

        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{
               
                $tipo=99;
                //comprobamos que no haya sido ingresado
                $verifica=Item::where('codigo',$request->cod_lab_ins)
                ->where('idbodega',19)
                ->first();                
                if(!is_null($verifica)){                                        
                    
                    return (['mensaje'=>'El codigo ya existe','error'=>true]);
                           
                    
                }

                $ultimoLab= Item::orderBy('codi_it','desc')
                ->first();
              
                //registramos la cabecera
                $ins=new Item();
                $ins->codi_it=$ultimoLab->codi_it+1;
                $ins->codigo=$request->cod_lab_ins;
                $ins->descri=$request->desc_lab_ins;
                $ins->idbodega=$request->idbod_ins;
                $ins->tipoit=$tipo;
                if($ins->save()){
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

    public function actualizaLabDialisis(Request $request,$id){
        
        $validator = Validator::make($request->all(), [
            'cod_lab_ins' => 'required', 
            'desc_lab_ins' => 'required',          
            'idbod_ins' => 'required',
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formularioZ','error'=>true]);
        }

        $transaction=DB::connection('pgsql')->transaction(function() use ($request,$id){
            try{
               
                $tipo=99;
                //comprobamos que no haya sido ingresado
                $verifica=Item::where('codigo',$request->cod_lab_ins)
                ->where('idbodega',19)
                ->where('codi_it','!=',$id)
                ->first();                
                if(!is_null($verifica)){                                        
                    return (['mensaje'=>'La informacion ya existe','error'=>true]);
                }

                $item=Item::where('idbodega',19)->
                where('codi_it','=',$id)
                ->first();  
            
                $item->codigo=$request->cod_lab_ins;
                $item->descri=$request->desc_lab_ins;
                $item->idbodega=$request->idbod_ins;
                $item->tipoit=$tipo;
                if($item->save()){
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


    public function index(){
        // dd(auth()->user()->id);
        return view('gestion_bodega.mantenimiento');
    }

    public function vistaInsumo(){
        return view('gestion_bodega.insumo_parametriza');
    }

    public function accesoInsumo($id, $bodega){
        try{
            $areaEspecialidad=AreaEspecialidad::where('estado','!=','I')
            ->where('tipo','M')
            ->get();
            foreach($areaEspecialidad as $key=> $data){
                $verificaAcceso=InsumoAreaEspecialidad::where('idinsumo',$id)
                ->where('idarea_especialidad',$data->idarea_especialidad)->first();
                if(!is_null($verificaAcceso)){
                    $areaEspecialidad[$key]->accesoPerm="S";
                }else{
                    $areaEspecialidad[$key]->accesoPerm="N";
                }
            }
            
            return response()->json([
                'error'=>false,
                'resultado'=>$areaEspecialidad
            ]);
               
        }catch (\Throwable $e) {
            Log::error('MedicinaController => accesoInsumo => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function accesoInsumoEnf($id, $bodega){
        try{
            $areaEspecialidad=AreaEspecialidad::where('estado','!=','I')
            ->whereIn('tipo',['E','ELA','EX'])
            ->orderby('idarea_especialidad','asc')
            ->get();
            foreach($areaEspecialidad as $key=> $data){
                $verificaAcceso=InsumoAreaEspecialidad::where('idinsumo',$id)
                ->where('idarea_especialidad',$data->idarea_especialidad)->first();
                if(!is_null($verificaAcceso)){
                    $areaEspecialidad[$key]->accesoPerm="S";
                }else{
                    $areaEspecialidad[$key]->accesoPerm="N";
                }
            }
            
            return response()->json([
                'error'=>false,
                'resultado'=>$areaEspecialidad
            ]);
               
        }catch (\Throwable $e) {
            Log::error('MedicinaController => accesoInsumoEnf => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function accesoMedicinaEnfLider($id, $bodega){
        try{
            $areaEspecialidad=AreaEspecialidad::where('estado','!=','I')
            ->where('tipo','ELA')
            ->get();
            foreach($areaEspecialidad as $key=> $data){
                $verificaAcceso=MedicamentoAreaEspecialidad::where('id_medicina',$id)
                ->where('idarea_especialidad',$data->idarea_especialidad)->first();
                if(!is_null($verificaAcceso)){
                    $areaEspecialidad[$key]->accesoPerm="S";
                }else{
                    $areaEspecialidad[$key]->accesoPerm="N";
                }
            }
            
            return response()->json([
                'error'=>false,
                'resultado'=>$areaEspecialidad
            ]);
               
        }catch (\Throwable $e) {
            Log::error('MedicinaController => accesoMedicinaEnfLider => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function mantenimientoAccesoInsumo($idarea_esp, $tipo, $insumo){
       
        try{
            //agregamos
            if($tipo=="A"){
                
                $ultimo=InsumoAreaEspecialidad::orderBy('idinsumo_area_especialidad','desc')->first();
                if(is_null($ultimo)){
                    $suma=1;
                }else{
                    $suma=$ultimo->idinsumo_area_especialidad+1;
                }

                $acceso_perf= new InsumoAreaEspecialidad();
                $acceso_perf->idinsumo_area_especialidad=$suma;
                $acceso_perf->idarea_especialidad=$idarea_esp;
                $acceso_perf->idinsumo=$insumo;
                $acceso_perf->save();
                return response()->json([
                    'error'=>false,
                    'mensaje'=>'Información registrada exitosamente'
                ]);
            }else{
                //lo quitamos
                $quitar=InsumoAreaEspecialidad::where('idinsumo',$insumo)
                ->where('idarea_especialidad',$idarea_esp)->first();
                $quitar->delete();
                return response()->json([
                    'error'=>false,
                    'mensaje'=>'Información registrada exitosamente'
                ]);
            }
           

        }catch (\Throwable $e) {
            Log::error('MedicinaController => mantenimientoAccesoInsumo => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function vistaMedicamentos(){
        return view('gestion_bodega.medicamento_parametriza');
    }

    public function accesoMedicamentos($id, $bodega){
        try{
            $areaEspecialidad=AreaEspecialidad::where('estado','!=','I')
            ->where('tipo','M')
            ->get();
            foreach($areaEspecialidad as $key=> $data){
                $verificaAcceso=MedicamentoAreaEspecialidad::where('id_medicina',$id)
                ->where('idarea_especialidad',$data->idarea_especialidad)->first();
                if(!is_null($verificaAcceso)){
                    $areaEspecialidad[$key]->accesoPerm="S";
                }else{
                    $areaEspecialidad[$key]->accesoPerm="N";
                }
            }
            
            return response()->json([
                'error'=>false,
                'resultado'=>$areaEspecialidad
            ]);
               
        }catch (\Throwable $e) {
            Log::error('MedicinaController => accesoMedicamentos => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function agregarMedicamentosTodos($id, $bodega){
        try{
            $areaEspecialidad=AreaEspecialidad::where('estado','!=','I')
            ->where('tipo','M')
            ->get();
            foreach($areaEspecialidad as $key=> $data){
                $agregarAcceso=MedicamentoAreaEspecialidad::where('id_medicina',$id)
                ->where('idarea_especialidad',$data->idarea_especialidad)->first();
                if(!is_null($verificaAcceso)){
                    $areaEspecialidad[$key]->accesoPerm="S";
                }else{
                    $areaEspecialidad[$key]->accesoPerm="N";
                }
            }
            
            return response()->json([
                'error'=>false,
                'resultado'=>$areaEspecialidad
            ]);
               
        }catch (\Throwable $e) {
            Log::error('MedicinaController => accesoMedicamentosTodos => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function mantenimientoAccesoMedicamentos($idarea_esp, $tipo, $medicina){
       
        try{
            //agregamos
            if($tipo=="A"){
                
                $ultimo=MedicamentoAreaEspecialidad::orderBy('idmedicina_area_especialidad','desc')->first();
                if(is_null($ultimo)){
                    $suma=1;
                }else{
                    $suma=$ultimo->idmedicina_area_especialidad+1;
                }

                $acceso_perf= new MedicamentoAreaEspecialidad();
                $acceso_perf->idmedicina_area_especialidad=$suma;
                $acceso_perf->idarea_especialidad=$idarea_esp;
                $acceso_perf->id_medicina=$medicina;
                $acceso_perf->save();
                return response()->json([
                    'error'=>false,
                    'mensaje'=>'Información registrada exitosamente'
                ]);
            }else{
                //lo quitamos
                $quitar=MedicamentoAreaEspecialidad::where('id_medicina',$medicina)
                ->where('idarea_especialidad',$idarea_esp)->first();
                $quitar->delete();
                return response()->json([
                    'error'=>false,
                    'mensaje'=>'Información registrada exitosamente'
                ]);
            }
           

        }catch (\Throwable $e) {
            Log::error('MedicinaController => mantenimientoAccesoMedicamentos => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function DetalleItem($bod, $tipo, $item){
        if($bod==1 || $bod==17){ //medicina
            $medicamentos= DB::connection('pgsql')->table('bodega.medicamentos')
            ->where('coditem',$item)
            ->first();
        }else if($bod==2 || $bod==18){ //insumos
            $medicamentos= DB::connection('pgsql')->table('bodega.insumo')
            ->where('codinsumo',$item)
            ->first();
        }else if($bod==13 || $bod==14 || $bod==8){ //lab
            $medicamentos= DB::connection('pgsql')->table('bodega.laboratorio')
            ->where('id',$item)
            ->first();
        }else{
            $medicamentos= DB::connection('pgsql')->table('bodega.items')
            ->where('codi_it',$item)
            ->first();
        }
        return response()->json([
            'error'=>false,
            'resultado'=>$medicamentos
        ]);

    }

    public function verAcceso($id, $bod){

        try{
            if($bod==1 || $bod==17){
                $medicamentos= DB::connection('pgsql')->table('bodega.medicamentos')
                ->where('coditem',$id)
                ->first();
            }else if($bod==2 || $bod==18){
                $medicamentos= DB::connection('pgsql')->table('bodega.insumo')
                ->where('codinsumo',$id)
                ->first();
            }else if($bod==13 || $bod==14 || $bod==8 || $bod==3 || $bod==4 || $bod==5 || $bod==9 || $bod==10 | $bod==19){
                $medicamentos= DB::connection('pgsql')->table('bodega.items')
                ->where('codi_it',$id)
                ->first();
            }
            return response()->json([
                'error'=>false,
                'resultado'=>$medicamentos
            ]);

           
               
        }catch (\Throwable $e) {
            Log::error('MedicinaController => aggQuitarMedicina => mensaje => '.$e->getMessage().' line => '.$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function parametriza($item ,$esp, $bod, $valor){
        try{
            if($bod==1){
                $med= Medicamento::where('coditem',$item)->first();
                $med->$esp=$valor;
                $med->save();

                return response()->json([
                    'error'=>false,
                    'mensaje'=>"Valor actualizado exitosamente"
                ]);
            }

        }catch (\Throwable $e) {
            Log::error('MedicinaController => parametriza => mensaje => '.$e->getMessage().' line => '.$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    // public function enviarmensaje(Request $request){
    //     $contadorenvio=0;
    //     $contadornoenvio=0;
    //     if($request->mensaje==null){
    //         return response()->json(['error'=>true,'detalle'=>'Ingrese el mensaje']);
    //     }
    //     $mensaje=$request->mensaje;
        
    //     if($request->check_destinatarios_whatsap=='on'){
    //         if($request->destinatarios_whatsap==null){
    //             return response()->json(['error'=>true,'detalle'=>'Ingrese los destinatarios']);
    //         }
    //         $destinatarios=explode(';',$request->destinatarios_whatsap);
    //         foreach($destinatarios as $value){
    //             $nombres_celular=explode(',',$value);
    //             //nombre de persona
    //             $nombre=$nombres_celular[0];
    //             //correo
    //             $celular=strtolower($nombres_celular[1]);

    //             $phone=$celular; 
    //             $apiURL = 'https://api.chat-api.com/instance243743/';
    //             $token = 'izj825shjctq3n5x';
    //                 $data = json_encode(
    //                     array(
    //                         'chatId'=>$phone.'@c.us',
    //                         "body"=> 'https://enlinea.chone.gob.ec/images/bioseguridad.jpeg',
    //                         "filename"=>'Bioseguridad.jpeg',
    //                         "caption"=>'*Estimado/a '.$nombre."*\n\n{$mensaje}"
    //                                     )
    //                                 );
    //                             $url = $apiURL.'sendFile?token='.$token;
    //                             $options = stream_context_create(
    //                                 array('http' =>
    //                                     array(
    //                                         'method'  => 'POST',
    //                                         'header'  => 'Content-type: application/json',
    //                                         'content' => $data
    //                                     )
    //                                 )
    //                             );
    //                             $response = file_get_contents($url,false,$options);
    //                             $response=json_decode((string) $response);
    //                             if($response->sent==true){
    //                                 $contadorenvio++;
    //                             }else{
    //                                 $contadornoenvio++;
    //                             }

    //         }
    //     }

    //     // if($request->check_funcionarios_whatsapp=='on'){
    //     //     // consumir la api
    //     //     $phone=$celular; 
    //     //     $apiURL = 'https://api.chat-api.com/instance243743/';
    //     //     $token = 'izj825shjctq3n5x';
    //     //     $contadorenvio=0;
    //     //     $contadornoenvio=0;
    //     //         $data = json_encode(
    //     //             array(
    //     //                 'chatId'=>$phone.'@c.us',
    //     //                 "body"=> 'https://enlinea.chone.gob.ec/images/bioseguridad.jpeg',
    //     //                 "filename"=>'Bioseguridad.jpeg',
    //     //                     "caption"=>'*Estimado/a '.$nombre."*\n\nSi nos cuidamos todos, estaremos bien.\n 
    //     //                     Sigamos estás recomendaciones y conservemos la disciplina, la solidaridad y nuestra lucha por la vida.
    //     //                     🤍💛❤️"
    //     //                             )
    //     //                         );
    //     //                     $url = $apiURL.'sendFile?token='.$token;
    //     //                     $options = stream_context_create(
    //     //                         array('http' =>
    //     //                             array(
    //     //                                 'method'  => 'POST',
    //     //                                 'header'  => 'Content-type: application/json',
    //     //                                 'content' => $data
    //     //                             )
    //     //                         )
    //     //                     );
    //     //                     $response = file_get_contents($url,false,$options);
    //     //                     $response=json_decode((string) $response);
    //     //                     if($response->sent==true){
    //     //                         $contadorenvio++;
    //     //                     }else{
    //     //                         $contadornoenvio++;
    //     //                     }
    //     //     $contadorenvio=0;
    //     //     $contadornoenvio=0;
    //     // }

    //     if($request->check_destinatarios=='on'){
    //         if($request->destinatarios==null){
    //             return response()->json(['error'=>true,'detalle'=>'Ingrese los destinatarios']);
    //         }
    //         $destinatarios=explode(';',$request->destinatarios);
    //         foreach($destinatarios as $value){
    //             $nombres_correos=explode(',',$value);
    //             //nombre de persona
    //             $nombre=$nombres_correos[0];
                
    //             //correo
    //             $correo=strtolower($nombres_correos[1]);
    //             try {
    //                 Mail::send('notificaciones.emailmensaje', ['nombre'=>$nombre,'mensaje'=>$mensaje], function ($m) use ($correo) {
    //                     $m->to($correo)
    //                     ->subject('SI TU TE CUIDAS NOS CUIDAMOS TODOS');
    //                 });
    //                 $contadorenvio++;
    //             } catch (\Throwable $th) {
    //                 Log::error('No se puede enviar el menaje de notificaciones a '.$correo).' '.$th->getMessagge();
    //                 $contadornoenvio++;
    //             }

    //         }
        
    //     }
    // }
   
}
