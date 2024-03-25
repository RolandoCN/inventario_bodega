<?php

namespace App\Http\Controllers\Bodega;
use App\Http\Controllers\Controller;
use App\Models\Personal\Especialidad;
use \Log;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator; 
use App\Models\Bodega\PaqueteCirugia;
use App\Models\Bodega\DetallePaqueteCirugia;
use App\Models\Bodega\Medicamento; 
use App\Models\Bodega\Insumo; 

class PaqueteCirugiaController extends Controller
{
    public function vistaIngreso(){
        return view('gestion_paquetes_cirugia.mantenimiento');
    }

    public function listar(){
        $paquetes= DB::connection('pgsql')->table('bodega.paquete_cirugia')
        ->where('estado','A')
        ->get();

        return response()->json([
            'error'=>false,
            'resultado'=>$paquetes
        ]);
    }

    public function editar($id){
        $paquetes= PaqueteCirugia::find($id);

        return response()->json([
            'error'=>false,
            'resultado'=>$paquetes
        ]);
    }


    public function guardarIngresoPaqueteCirugia(Request $request){
        $validator = Validator::make($request->all(), [
            'descripcion' => 'required',   
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario1','error'=>true]);
        }
        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{
                //verificamos si ya existe el paqurete
                $existe=PaqueteCirugia::where('descripcion',$request->descripcion)
                ->where('estado','A')
                ->first();
                if(!is_null($existe)){
                    return (['mensaje'=>'Ya existe el paquete ingresado','error'=>true]); 
                }

                $ultimoPaquete= PaqueteCirugia::orderBy('idpaquete_cirugia','desc')
                ->first();
              
                if(!is_null($ultimoPaquete)){
                    $nuevo=$ultimoPaquete->idpaquete_cirugia+1;
                }else{
                    $nuevo=1;
                }
              
                //registramos 
                $paquete=new PaqueteCirugia();
                $paquete->idpaquete_cirugia=$nuevo;
                $paquete->descripcion=$request->descripcion;
                $paquete->area=$request->area;
                $paquete->estado='A';
                $paquete->idusuario_reg=auth()->user()->id;
                $paquete->fecha_reg=date('Y-m-d H:i:s');
               
                if($paquete->save()){
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

    public function actualizarPaquete(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'descripcion' => 'required',   
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario1','error'=>true]);
        }
        $transaction=DB::connection('pgsql')->transaction(function() use ($request, $id){
            try{
                //verificamos si ya existe el paqurete
                $existe=PaqueteCirugia::where('descripcion',$request->descripcion)
                ->where('estado','A')
                ->where('idpaquete_cirugia','!=', $id)
                ->first();
                if(!is_null($existe)){
                    return (['mensaje'=>'Ya existe el paquete ingresado','error'=>true]); 
                }

                //actualiuzamos 
                $paquete= PaqueteCirugia::find($id);
                $paquete->descripcion=$request->descripcion;
                $paquete->area=$request->area;
                $paquete->estado='A';
                $paquete->idusuario_act=auth()->user()->id;
                $paquete->fecha_act=date('Y-m-d H:i:s');
               
                if($paquete->save()){
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

    public function eliminarPaquete($id){
        try{

            //verificamos que no tengas detalles
            $veri_Paquete=DB::table('bodega.detalle_paquetes_cirugia')
            ->where('id_paquete',$id)
            ->where('estado','A')
            ->first();
            if(!is_null($veri_Paquete)){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'El paquete está relacionado, no se puede eliminar'
                ]);
            }

            $paquete=PaqueteCirugia::find($id);
            $paquete->idusuario_act=auth()->user()->id;
            $paquete->fecha_act=date('Y-m-d H:i:s');
            $paquete->estado="I";
            if($paquete->save()){
                return response()->json([
                    'error'=>false,
                    'mensaje'=>'Información eliminada exitosamente'
                ]);
            }else{
                return response()->json([
                    'error'=>false,
                    'mensaje'=>'No se pudo eliminar la información'
                ]);
            }
               
        }catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function guardarDetallePaquete(Request $request){
      
        $validator = Validator::make($request->all(), [
            'item_selecci' => 'required',   
            'cantidad_item' => 'required',
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario1','error'=>true]);
        }

        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{
                //verificamos si ya existe el item en ese paquete
                $exite=DetallePaqueteCirugia::where('id_item',$request->item_selecci)
                ->where('id_paquete',$request->idpaquete_cab)
                ->where('estado','A')
                ->first();
                if(!is_null($exite)){
                    return (['mensaje'=>'Ya existe el item en el paquete seleccionado','error'=>true]); 
                }

                $ultimoDetalle= DetallePaqueteCirugia::orderBy('id_detalle_paquetes_cirugia','desc')
                ->first();
                if(!is_null($ultimoDetalle)){
                    $nuevo=$ultimoDetalle->id_detalle_paquetes_cirugia+1;
                }else{
                    $nuevo=1;
                }
              
                //registramos 
                $detalle=new DetallePaqueteCirugia();
                $detalle->id_detalle_paquetes_cirugia=$nuevo;
                $detalle->id_paquete=$request->idpaquete_cab;
                $detalle->id_item=$request->item_selecci;
                $detalle->tipo=$request->tipo_item;
                $detalle->cantidad=$request->cantidad_item;
                $detalle->estado='A';
                $detalle->idusuario_reg=auth()->user()->id;
                $detalle->fecha_reg=date('Y-m-d H:i:s');
               
                if($detalle->save()){
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

    public function detallePaquete($id){
        $detalle_paquetes= DetallePaqueteCirugia::with('medicamento','insumo')->where('id_paquete',$id)
        ->where('estado','A')
        ->get();

        return response()->json([
            'error'=>false,
            'resultado'=>$detalle_paquetes
        ]);
    }

    public function cargarItems(Request $request){
        $data = [];
        if($request->has('q')){
            $search = $request->q;
            $data=DB::table('bodega.medicamentos')->where(function($c)use($search) {
                $c->where('nombre', 'ilike', '%'.$search.'%')
                ->orwhere('concentra', 'ilike', '%'.$search.'%')
                ->orwhere('presentacion', 'ilike', '%'.$search.'%');
            })
            ->where('stock','>',0)
            ->select('coditem as id_item',DB::raw("CONCAT(nombre,' - ', concentra,' - ', forma,' - ', presentacion) AS descripcion"))
            ->take(10)->get();

            if(sizeof($data)==0){
                $data=DB::table('bodega.insumo')->where(function($c)use($search) {
                    $c->where('insumo', 'ilike', '%'.$search.'%');
                })
                ->where('stock','>',0)
                ->select('codinsumo as id_item',DB::raw("CONCAT(insumo) AS descripcion"))
                ->take(10)->get();
            }
        }
         
         return response()->json($data);
    }

    public function editarDetalle($id){
        $detalle_paquetes= DetallePaqueteCirugia::find($id);

        return response()->json([
            'error'=>false,
            'resultado'=>$detalle_paquetes
        ]);
    }

    public function itemSeleccionado($id){
        if($id >= 30000){
            $item=DB::table('bodega.insumo')
            ->where('codinsumo',$id)
            ->select('codinsumo as iditem_','insumo as nombre_item')
            ->get();
        }else{
            $item=DB::table('bodega.medicamentos')
            ->where('id_medicamento',$id)
            ->select('coditem as iditem_',DB::raw("CONCAT(nombre,' - ', concentra,' - ', forma,' - ', presentacion) AS nombre_item"))
            ->get();
        }
    
        return response()->json([
            'error'=>false,
            'resultado'=>$item
        ]);
    }
    

    public function actualizarDetallePaquete(Request $request, $id){
      
        $validator = Validator::make($request->all(), [
            'item_selecci' => 'required',   
            'cantidad_item' => 'required',
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario1','error'=>true]);
        }

        $transaction=DB::connection('pgsql')->transaction(function() use ($request, $id){
            try{
                //verificamos si ya existe el item en ese paquete
                $exite=DetallePaqueteCirugia::where('id_item',$request->item_selecci)
                ->where('id_paquete',$request->idpaquete_cab)
                ->where('estado','A')
                ->where('id_detalle_paquetes_cirugia','!=',$id)
                ->first();
                if(!is_null($exite)){
                    return (['mensaje'=>'Ya existe el item en el paquete seleccionado','error'=>true]); 
                }

                //actualizamos 
                $detalle= DetallePaqueteCirugia::find($id);
                $detalle->id_paquete=$request->idpaquete_cab;
                $detalle->id_item=$request->item_selecci;
                $detalle->tipo=$request->tipo_item;
                $detalle->cantidad=$request->cantidad_item;
                $detalle->estado='A';
                $detalle->idusuario_act=auth()->user()->id;
                $detalle->fecha_act=date('Y-m-d H:i:s');
               
                if($detalle->save()){
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

    public function eliminarDetallePaquete($id){
        try{

            $detallePaquete=DetallePaqueteCirugia::find($id);
            $detallePaquete->idusuario_act=auth()->user()->id;
            $detallePaquete->fecha_act=date('Y-m-d H:i:s');
            $detallePaquete->estado="I";
            if($detallePaquete->save()){
                return response()->json([
                    'error'=>false,
                    'mensaje'=>'Información eliminada exitosamente'
                ]);
            }else{
                return response()->json([
                    'error'=>false,
                    'mensaje'=>'No se pudo eliminar la información'
                ]);
            }
               
        }catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }
   
}
