<?php

namespace App\Http\Controllers\Personal;
use App\Http\Controllers\Controller;
use App\Models\Personal\Area;
use App\Models\Personal\Funcionario;
use \Log;
use Illuminate\Http\Request;
use DB;

class FuncionarioController extends Controller
{
      
    public function index(){

        $area=Area::where('estado','!=','I')->get();
        $ambito=DB::table('per_ambito')
        ->where('estado','!=','I')->get();
        $ambito_ley=DB::table('per_ambito_ley')
        ->where('estado','!=','I')->get();

        return view('gestion_funcionario.funcionario',[
            "area"=>$area,
            "ambito"=>$ambito,
            "ambito_ley"=>$ambito_ley
        ]);
    }

    public function verPermisos(){
        try{
            $id=auth()->user()->id;
            $perm=DB::connection('pgsql')->table('bodega.boton_acceso')
            ->where('idusuario',$id)
            ->get();

            return response()->json([
                'error'=>false,
                'resultado'=>$perm
            ]);

        }catch (\Throwable $e) {
            Log::error('FuncionarioController => listar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }


    public function listar(){
        try{
            $funcionario=DB::table('per_funcionario as f')
            ->leftJoin('per_area as a', 'a.id_area','f.id_area')
            ->leftJoin('per_ambito as amb', 'amb.id_ambito','f.id_ambito') 
            ->leftJoin('per_ambito_ley as ambley', 'ambley.id_ambito_ley','f.id_ambito_ley')  
            ->where('f.estado','!=','I')
            ->select('f.id_funcionario','f.cedula',DB::raw("CONCAT(f.apellidos,' ', f.nombres) AS funcionario")
            ,'a.descripcion as area_de', 'amb.descripcion as ambito', 'ambley.descripcion as ambitoley')
            ->get();
            return response()->json([
                'error'=>false,
                'resultado'=>$funcionario
            ]);
        }catch (\Throwable $e) {
            Log::error('FuncionarioController => listar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }

    public function editar($id){
        try{
            $funcionario=Funcionario::where('estado','A')
            ->where('id_funcionario', $id)
            ->first();
            
            return response()->json([
                'error'=>false,
                'resultado'=>$funcionario
            ]);
        }catch (\Throwable $e) {
            Log::error('FuncionarioController => editar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }
    

    public function guardar(Request $request){
        
        $messages = [
            'cedula.required' => 'Debe ingresar la cedula',
            'apellidos.required' => 'Debe ingresar el apellido', 
            'nombres.required' => 'Debe ingresar el nombre',   
            'id_area.required' => 'Debe seleccionar el area',           
        ];
           

        $rules = [
            'cedula' =>"required|string|max:10",
            'apellidos' =>"required|string|max:200",
            'nombres' =>"required|string|max:200",
            'id_area' =>"required",
        ];

        $this->validate($request, $rules, $messages);
        try{
            $validaCedula=validarCedula($request->cedula);
            if($validaCedula==false){
                return response()->json([
                    "error"=>true,
                    "mensaje"=>"El numero de identificacion ingresado no es valido"
                ]);
            }  

            //validar que la cedula no se repita
            $validar_cedula=Funcionario::where('cedula', $request->cedula)
            ->whereIn('estado',['A','I'])
            ->first();
         
            if(!is_null($validar_cedula)){
                if($validar_cedula->estado=="A"){
                    return response()->json([
                        'error'=>true,
                        'mensaje'=>'El número de identificación ya existe'
                    ]);
                }else{
                    //ha sido eliminado lo actualizamos
                    $actualiza_funcionario=Funcionario::find($validar_cedula->id_funcionario);
                    $actualiza_funcionario->cedula=$request->cedula;
                    $actualiza_funcionario->apellidos=mb_strtoupper($request->apellidos);
                    $actualiza_funcionario->nombres=mb_strtoupper($request->nombres);
                    $actualiza_funcionario->id_area=$request->id_area;
                    $actualiza_funcionario->id_ambito=$request->id_ambito;
                    $actualiza_funcionario->id_ambito_ley=$request->id_ambito_ley;
                    $actualiza_funcionario->estado="A";
                    $actualiza_funcionario->f_actualizacion=date('Y-m-d H:i:s');
                    $actualiza_funcionario->id_usuario_act=auth()->user()->id;

                    if($actualiza_funcionario->save()){
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
                }
            }

                       
            $guarda_funcionario=new Funcionario();
            $guarda_funcionario->cedula=$request->cedula;
            $guarda_funcionario->apellidos=mb_strtoupper($request->apellidos);
            $guarda_funcionario->nombres=mb_strtoupper($request->nombres);
            $guarda_funcionario->id_area=$request->id_area;
            $guarda_funcionario->id_ambito=$request->id_ambito;
            $guarda_funcionario->id_ambito_ley=$request->id_ambito_ley;
            $guarda_funcionario->estado="A";
            $guarda_funcionario->f_creacion=date('Y-m-d H:i:s');
            $guarda_funcionario->id_usuario_crea=auth()->user()->id;

            if($guarda_funcionario->save()){
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
            Log::error('FuncionarioController => guardar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }


    public function actualizar(Request $request, $id){
        $messages = [
            'cedula.required' => 'Debe ingresar la cedula',
            'apellidos.required' => 'Debe ingresar el apellido', 
            'nombres.required' => 'Debe ingresar el nombre',   
            'id_area.required' => 'Debe seleccionar el area',           
        ];
           

        $rules = [
            'cedula' =>"required|string|max:10",
            'apellidos' =>"required|string|max:200",
            'nombres' =>"required|string|max:200",
            'id_area' =>"required",
        ];

        $this->validate($request, $rules, $messages);
        try{

            $validaCedula=validarCedula($request->cedula);
            if($validaCedula==false){
                return response()->json([
                    "error"=>true,
                    "mensaje"=>"El numero de identificacion ingresado no es valido"
                ]);
            }          

            $actualiza_funcionario=Funcionario::find($id);
            $actualiza_funcionario->cedula=$request->cedula;
            $actualiza_funcionario->apellidos=mb_strtoupper($request->apellidos);
            $actualiza_funcionario->nombres=mb_strtoupper($request->nombres);
            $actualiza_funcionario->id_area=$request->id_area;
            $actualiza_funcionario->id_ambito=$request->id_ambito;
            $actualiza_funcionario->id_ambito_ley=$request->id_ambito_ley;
            $actualiza_funcionario->estado="A";
            $actualiza_funcionario->f_actualizacion=date('Y-m-d H:i:s');
            $actualiza_funcionario->id_usuario_act=auth()->user()->id;

            //validar que la cedula no se repita
            $validar_cedula=Funcionario::where('cedula', $actualiza_funcionario->cedula)
            ->where('estado','A')
            ->where('id_funcionario', '!=', $id)
            ->first();

            if(!is_null($validar_cedula)){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'El número de identificación ya existe'
                ]);
            }

            if($actualiza_funcionario->save()){
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
            Log::error('FuncionarioController => actualizar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function eliminar($id){
        try{

            $funcionario=Funcionario::find($id);
            $funcionario->estado="I";
            $funcionario->f_actualizacion=date('Y-m-d H:i:s');
            $funcionario->id_usuario_act=auth()->user()->id;
           
            if($funcionario->save()){
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
            Log::error('FuncionarioController => eliminar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

}
