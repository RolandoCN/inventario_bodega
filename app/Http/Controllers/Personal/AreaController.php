<?php

namespace App\Http\Controllers\Personal;
use App\Http\Controllers\Controller;
use App\Models\Personal\Area;
use \Log;
use Illuminate\Http\Request;
use DB;
use App\Models\Personal\Funcionario;

class AreaController extends Controller
{
      
    public function index(){
        return view('gestion_area.area');
    }


    public function listar(){
        try{
            $area=Area::where('estado','!=','I')->get();
            return response()->json([
                'error'=>false,
                'resultado'=>$area
            ]);
        }catch (\Throwable $e) {
            Log::error('AreaController => listar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }

    public function editar($id){
        try{
            $area=Area::where('estado','A')
            ->where('id_area', $id)
            ->first();
            
            return response()->json([
                'error'=>false,
                'resultado'=>$area
            ]);
        }catch (\Throwable $e) {
            Log::error('AreaController => editar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }
    

    public function guardar(Request $request){
        
        $messages = [
            'descripcion.required' => 'Debe ingresar la descripción',
            'es_admin.required' => 'Debe seleccionar si es admin o no',           
        ];
           

        $rules = [
            'descripcion' =>"required|string|max:100",
            'es_admin' =>"required",
        ];

        $this->validate($request, $rules, $messages);
        try{

            $guarda_area=new Area();
            $guarda_area->descripcion=mb_strtoupper($request->descripcion);
            $guarda_area->administrativo=$request->es_admin;
            $guarda_area->estado="A";

            //validar que el menu no se repita
            $valida_menu=Area::where('descripcion', $guarda_area->descripcion)
            ->where('estado','A')
            ->first();

            if(!is_null($valida_menu)){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'El área ya existe'
                ]);
            }

            if($guarda_area->save()){
                return response()->json([
                    'error'=>false,
                    'mensaje'=>'Información registrada exitosamente'
                ]);
            }else{
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'No se pudo registrar la información'
                ]);
            }


        }catch (\Throwable $e) {
            Log::error('AreaController => guardar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }


    public function actualizar(Request $request, $id){
        $messages = [
            'descripcion.required' => 'Debe ingresar la descripción',
            'es_admin.required' => 'Debe seleccionar si es admin o no',           
        ];
           

        $rules = [
            'descripcion' =>"required|string|max:100",
            'es_admin' =>"required",
        ];

        $this->validate($request, $rules, $messages);
        try{

            $actualiza_area= Area::find($id);
            $actualiza_area->descripcion=mb_strtoupper($request->descripcion);
            $actualiza_area->administrativo=$request->es_admin;
            $actualiza_area->estado="A";

            //validar que el menu no se repita
            $valida_menu=Area::where('descripcion', $actualiza_area->descripcion)
            ->where('estado','A')
            ->where('id_area','!=',$id)
            ->first();

            if(!is_null($valida_menu)){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'El área ya existe'
                ]);
            }

            if($actualiza_area->save()){
                return response()->json([
                    'error'=>false,
                    'mensaje'=>'Información actualizada exitosamente'
                ]);
            }else{
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'No se pudo actualizar la información'
                ]);
            }


        }catch (\Throwable $e) {
            Log::error('AreaController => actualizar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function eliminar($id){
        try{

            //verificamos que no este asociado a un funcionario
            $veri_Funcionario=DB::table('per_funcionario')
            ->where('id_area',$id)
            ->where('estado','A')
            ->first();
            if(!is_null($veri_Funcionario)){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'El área está relacionado, no se puede eliminar'
                ]);
            }

            $area=Area::find($id);
            $area->estado="I";
           
            if($area->save()){
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
            Log::error('AreaController => eliminar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function areaFuncionario(){
        $area=Area::where('estado','!=','I')->get();
        return view('gestion_area.area_funcionario',[
            "area"=>$area
        ]);
    }

    public function listaFuncArea($id){
        try{
            $funcionario=DB::table('inventario.persona as f')
            ->leftJoin('bodega.area as a', 'a.id_area','f.id_area')
            ->where('f.estado',1)
            ->select('f.idper','f.ci as cedula',DB::raw("CONCAT(f.ape1,' ', f.ape2, ' ',f.nom1,' ', f.nom2) AS funcionario")
            ,'a.descripcion as area_de','a.id_area')
            ->get();

            foreach($funcionario as $key=> $data){
                
                if($data->id_area==$id){
                    $funcionario[$key]->pertenece="S";
                }else{
                    $funcionario[$key]->pertenece="N";
                }
            }
            
            return response()->json([
                'error'=>false,
                'resultado'=>$funcionario
            ]);
               
        }catch (\Throwable $e) {
            Log::error('AreaController => listaFuncArea => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function aggQuitarFuncionario($func, $tipo, $area){
        try{
            $area_func= Funcionario::where('idper',$func)
            ->where('estado',1)->first();
            
            if(is_null($area_func)){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'El funcionario fué dado de baja'
                ]);
            }
            //agregamos
            if($tipo=="A"){
              
                $area_func->id_area=$area;
                $area_func->save();
                return response()->json([
                    'error'=>false,
                    'mensaje'=>'Información actualizada exitosamente'
                ]);
            }else{
                //lo quitamos
                $area_func->id_area=0;
                $area_func->save();
                return response()->json([
                    'error'=>false,
                    'mensaje'=>'Información registrada exitosamente'
                ]);
            }
               
        }catch (\Throwable $e) {
            Log::error('AreaController => aggQuitarFuncionario => mensaje => '.$e->getMessage().' line => '.$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

}
