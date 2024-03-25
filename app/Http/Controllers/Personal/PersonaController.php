<?php

namespace App\Http\Controllers\Personal;
use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Models\Personal\Vehiculo;
use App\Models\Personal\Tarea;
use \Log;
use Illuminate\Http\Request;

class PersonaController extends Controller
{
    
  
    public function index(){
      
        return view('gestion_acceso.persona');
    }


    public function listar(){
        try{
            $persona=Persona::where('estado','!=','I')->get();
            return response()->json([
                'error'=>false,
                'resultado'=>$persona
            ]);
        }catch (\Throwable $e) {
            Log::error('PersonaController => listar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }

    public function editar($id){
        try{
            $persona=Persona::where('estado','A')
            ->where('idpersona', $id)
            ->first();
            
            return response()->json([
                'error'=>false,
                'resultado'=>$persona
            ]);
        }catch (\Throwable $e) {
            Log::error('PersonaController => editar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }
    

    public function guardar(Request $request){
        
        $messages = [
            'cedula_persona.required' => 'Debe ingresar la cédula',  
            'nombres.required' => 'Debe ingresar los nombres',           
            'apellidos.required' => 'Debe ingresar los apellidos',  
            'telefono.required' => 'Debe ingresar el telefono',  

        ];
           

        $rules = [
            'cedula_persona' =>"required|string|max:10",
            'nombres' =>"required|string|max:100",
            'apellidos' =>"required|string|max:100",
            'telefono' =>"required|string|max:10",
                     
        ];

        $this->validate($request, $rules, $messages);
        try{

            $validaCedula=validarCedula($request->cedula_persona);
            if($validaCedula==false){
                return response()->json([
                    "error"=>true,
                    "mensaje"=>"El numero de identificacion ingresado no es valido"
                ]);
            }          

            $guarda_persona=new Persona();
            $guarda_persona->cedula=$request->cedula_persona;
            $guarda_persona->nombres=$request->nombres;
            $guarda_persona->apellidos=$request->apellidos;
            $guarda_persona->telefono=$request->telefono;
            $guarda_persona->email=$request->email;
            $guarda_persona->id_usuario_reg=auth()->user()->id;
            $guarda_persona->fecha_reg=date('Y-m-d H:i:s');
            $guarda_persona->estado="A";

            //validar que la cedula no se repita
            $valida_cedula=Persona::where('cedula', $guarda_persona->cedula)
            ->where('estado','A')
            ->first();

            if(!is_null($valida_cedula)){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'El número de cédula ya existe, en otra persona'
                ]);
            }

           
            if($guarda_persona->save()){
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
            Log::error('PersonaController => guardar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }


    public function actualizar(Request $request, $id){
       
    
        $messages = [
            'cedula_persona.required' => 'Debe ingresar la cédula',  
            'nombres.required' => 'Debe ingresar los nombres',           
            'apellidos.required' => 'Debe ingresar los apellidos',  
            'telefono.required' => 'Debe ingresar el telefono',  

        ];
            

        $rules = [
            'cedula_persona' =>"required|string|max:10",
            'nombres' =>"required|string|max:100",
            'apellidos' =>"required|string|max:100",
            'telefono' =>"required|string|max:10",
                        
        ];

        $this->validate($request, $rules, $messages);
        try{

            $validaCedula=validarCedula($request->cedula_persona);
            if($validaCedula==false){
                return response()->json([
                    "error"=>true,
                    "mensaje"=>"El numero de identificacion ingresado no es valido"
                ]);
            }   
            
            $guarda_persona= Persona::find($id);
            $guarda_persona->cedula=$request->cedula_persona;
            $guarda_persona->nombres=$request->nombres;
            $guarda_persona->apellidos=$request->apellidos;
            $guarda_persona->telefono=$request->telefono;
            $guarda_persona->id_usuario_act=auth()->user()->id;
            $guarda_persona->email=$request->email;
            $guarda_persona->fecha_actualiza=date('Y-m-d H:i:s');
            $guarda_persona->estado="A";

            //validar que la cedula no se repita
            $valida_cedula=Persona::where('cedula', $guarda_persona->cedula)
            ->where('estado','A')
            ->where('idpersona','!=', $id)
            ->first();

            if(!is_null($valida_cedula)){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'El número de cédula ya existe, en otra persona'
                ]);
            }

            
            if($guarda_persona->save()){
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
            Log::error('PersonaController => actualizar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function eliminar($id){
        try{
            $persona=Persona::find($id);
            $persona->id_usuario_act=auth()->user()->id;
            $persona->fecha_actualiza=date('Y-m-d H:i:s');
            $persona->estado="I";
            if($persona->save()){
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
            Log::error('PersonaController => eliminar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }
    
}
