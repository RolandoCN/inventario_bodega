<?php

namespace App\Http\Controllers\Personal;
use App\Http\Controllers\Controller;
use App\Models\Personal\Gestion;
use \Log;
use Illuminate\Http\Request;
use DB;

class GestionController extends Controller
{
    public function index(){
      
        return view('gestion_menu.gestion');
    }


    public function listar(){
        try{
            $gestion=Gestion::where('estado','!=','I')->get();
            return response()->json([
                'error'=>false,
                'resultado'=>$gestion
            ]);
        }catch (\Throwable $e) {
            Log::error('GestionController => listar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }

    public function editar($id){
        try{
            $gestion=Gestion::where('estado','A')
            ->where('id_gestion', $id)
            ->first();
            
            return response()->json([
                'error'=>false,
                'resultado'=>$gestion
            ]);
        }catch (\Throwable $e) {
            Log::error('GestionController => editar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }
    

    public function guardar(Request $request){
        
        $messages = [
            'descripcion.required' => 'Debe ingresar la descripción',           
        ];
           

        $rules = [
            'descripcion' =>"required|string|max:100",
        ];

        $this->validate($request, $rules, $messages);
        try{
            $ultimo=Gestion::get()->last();
            $guarda_gestion=new Gestion();
            $guarda_gestion->id_gestion=$ultimo->id_gestion+1;
            $guarda_gestion->descripcion=$request->descripcion;
            $guarda_gestion->icono=$request->icono;
            $guarda_gestion->id_usuario_reg=auth()->user()->id;
            $guarda_gestion->fecha_reg=date('Y-m-d H:i:s');
            $guarda_gestion->estado="A";

            //validar que la gestion no se repita
            $valida_gestion=Gestion::where('descripcion', $guarda_gestion->descripcion)
            ->where('estado','A')
            ->first();

            if(!is_null($valida_gestion)){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'La gestión ya existe'
                ]);
            }

           
            if($guarda_gestion->save()){
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
            Log::error('GestionController => guardar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }


    public function actualizar(Request $request, $id){
        $messages = [
            'descripcion.required' => 'Debe ingresar la descripción',           
        ];
           

        $rules = [
            'descripcion' =>"required|string|max:100",
        ];

        $this->validate($request, $rules, $messages);
        try{

            $actualiza_gestion= Gestion::find($id);
            $actualiza_gestion->descripcion=$request->descripcion;
            $actualiza_gestion->icono=$request->icono;
            $actualiza_gestion->id_usuario_act=auth()->user()->id;
            $actualiza_gestion->fecha_actualiza=date('Y-m-d H:i:s');
            $actualiza_gestion->estado="A";

            //validar que la gestion no se repita
            $valida_gestion=Gestion::where('descripcion', $actualiza_gestion->descripcion)
            ->where('estado','A')
            ->where('id_gestion','!=',$id)
            ->first();

            if(!is_null($valida_gestion)){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'La gestión ya existe'
                ]);
            }

           
            if($actualiza_gestion->save()){
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
            Log::error('GestionController => actualizar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function eliminar($id){
        try{

            //verificamos que no este asociado a una gestion-menu
            $veri_GestionMenu=DB::table('bodega.per_gestion_menu')
            ->where('id_gestion',$id)
            ->where('estado','A')
            ->first();
            if(!is_null($veri_GestionMenu)){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'La gestión está relacionada, no se puede eliminar'
                ]);
            }
            $gestion=Gestion::find($id);
            $gestion->id_usuario_act=auth()->user()->id;
            $gestion->fecha_actualiza=date('Y-m-d H:i:s');
            $gestion->estado="I";
            if($gestion->save()){
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
            Log::error('GestionController => eliminar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

}
