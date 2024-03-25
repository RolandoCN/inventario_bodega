<?php

namespace App\Http\Controllers\Personal;
use App\Http\Controllers\Controller;
use App\Models\Personal\Perfil;
use App\Models\Personal\Persona;
use App\Models\Bodega\BodegaUsuario;
use App\Models\Bodega\Bodega;
use App\Models\User;
use App\Models\Personal\UsuarioPerfil;
use \Log;
use DB;
use Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function index(){
        $persona=Persona::get();      
        $perfil=Perfil::where('estado','A')->get();
        return view('gestion_acceso.usuario',[
            "persona"=>$persona,
            "perfil"=>$perfil
        ]);
    }


    public function listar(){
        try{
            $usuario=User::with('persona','perfil')->where('estado','!=','I')->get(); 
            return response()->json([
                'error'=>false,
                'resultado'=>$usuario
            ]);
        }catch (\Throwable $e) {
            Log::error('UsuarioController => listar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }

    public function editar($id){
        try{
          
            $usuario=User::with('perfil')->where('estado','A')
            ->where('id', $id)
            ->first();
                        
            return response()->json([
                'error'=>false,
                'resultado'=>$usuario
            ]);
        }catch (\Throwable $e) {
            Log::error('UsuarioController => editar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }
    

    public function guardar(Request $request){
       
        $messages = [
            'idpersona.required' => 'Debe seleccionar la persona',
            'idperfil.required' => 'Debe seleccionar el perfil',           
        ];
           

        $rules = [
            'idpersona' =>"required",
            'idperfil' =>"required",
                 
        ];

        $this->validate($request, $rules, $messages);

        $transaction=DB::transaction(function() use($request){
            try{ 
                $pers=Persona::where('idpersona', $request->idpersona)
                ->where('estado','A')->first();
                if(is_null($pers)){
                    return response()->json([
                        'error'=>true,
                        'mensaje'=>'La persona ya no se encuentra activa'
                    ]);
                } 
               
                $numero_ident_pers=$pers->ci;
               

                //validar que exista
                $existe_user=User::where('tx_login', $numero_ident_pers)
                ->whereIn('estado',['A','I'])
                ->first();
               
                if(!is_null($existe_user)){
                    if($existe_user->estado=='A'){
                                        
                        return response()->json([
                            "error"=>true,
                            "mensaje"=>"La persona seleccionada ya tiene asignada una cuenta de usuario"
                        ]);
                    }else{

                        $existe_user->idpersona=$request->idpersona;
                        $existe_user->tx_login=$numero_ident_pers;
                        $existe_user->password=Hash::make($numero_ident_pers);
                        $existe_user->estado='A';
                       
                        $existe_user->save();

                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->idcomprobante+1;
                        }
                        $UsuarioPerfil=UsuarioPerfil::where('id_usuario',$existe_user->id)->first();
                        if(!is_null($UsuarioPerfil)){

                            $UsuarioPerfil->id_perfil=$request->idperfil;

                            if($UsuarioPerfil->save()){
                                return response()->json([
                                    "error"=>false,
                                    "mensaje"=>"Cuenta creada exitosamente"
                                ]);
                            }else{
                                DB::Rollback();
                                return response()->json([
                                    "error"=>true,
                                    "mensaje"=>"No se pudo registrar la cuenta"
                                ]);
                            }
                        }
                        else{
                          
                            $UsuarioPerfil=new UsuarioPerfil();
                            $UsuarioPerfil->idusuario=$existe_user->id;
                            $UsuarioPerfil->idperfil=$request->idperfil;
                        
                            if($UsuarioPerfil->save()){
                                return response()->json([
                                    "error"=>false,
                                    "mensaje"=>"Cuenta creada exitosamente"
                                ]);
                            }else{
                                DB::Rollback();
                                return response()->json([
                                    "error"=>true,
                                    "mensaje"=>"No se pudo registrar la cuenta"
                                ]);
                            }
                        }
                    }
                }

                $Usuario=new User();
                $Usuario->idpersona=$request->idpersona;
                $Usuario->tx_login=$numero_ident_pers;
                $Usuario->password=Hash::make($numero_ident_pers);
                $Usuario->estado='A';
        
                if($Usuario->save()){
                    $UsuarioPerfil=new UsuarioPerfil();
                    $UsuarioPerfil->idusuario=$Usuario->id;
                    $UsuarioPerfil->idperfil=$request->idperfil;
                
                    if($UsuarioPerfil->save()){
                        return response()->json([
                            "error"=>false,
                            "mensaje"=>"Cuenta creada exitosamente"
                        ]);
                    }else{
                        DB::Rollback();
                        return response()->json([
                            "error"=>true,
                            "mensaje"=>"No se pudo registrar la cuenta"
                        ]);
                    }

                }else{
                    DB::Rollback();
                    return response()->json([
                        "error"=>true,
                        "mensaje"=>"No se pudo registrar la información del usuario"
                    ]);
                }

            }catch (\Throwable $e) {
                DB::Rollback();
                Log::error('UsuarioController => guardar => mensaje => '.$e->getMessage().' linea => '.$e->getLine());
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'Ocurrió un error'
                ]);
                
            }
        });
        return $transaction;
    }


    public function actualizar(Request $request, $id){
       
    
        $messages = [
            'idperfil.required' => 'Debe seleccionar el perfil',           
        ];
           

        $rules = [
            'idperfil' =>"required",
                 
        ];


        $this->validate($request, $rules, $messages);
        try{
            $user=User::find($id);
            $UsuarioPerfil= UsuarioPerfil::where('id_usuario',$id)->first();
            $UsuarioPerfil->id_perfil=$request->idperfil;
        
            if($UsuarioPerfil->save()){
                return response()->json([
                    "error"=>false,
                    "mensaje"=>"Información actualizada exitosamente"
                ]);
            }else{
                return response()->json([
                    "error"=>true,
                    "mensaje"=>"No se pudo actualizar la información"
                ]);
            }

        }catch (\Throwable $e) {
            Log::error('UsuarioController => actualizar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function eliminar($id){
        $transaction=DB::transaction(function() use($id){
            try{
            
                $usuario=User::where('id',$id)->first();
                $usuario->estado='I';
                $usuario->id_actualizado=auth()->user()->id;
                $usuario->fe_actualiza=date('Y-m-d H:i:s');
                $usuario->save();

                $UsuarioPerfil=UsuarioPerfil::where('id_usuario',$usuario->id)->first();
               
                //obtenemos el id del usuario logueado (si es el mismo al que se va eliminar lo mandamos al login)
                if(auth()->user()->id == $UsuarioPerfil->id_usuario){
                    $desloguear="S";
                }else{
                    $desloguear="N";
                }

                if($UsuarioPerfil->delete()){
                    return response()->json([
                        "error"=>false,
                        "mensaje"=>"Información eliminada exitosamente",
                        "desloguear"=>$desloguear
                    ]);
                }else{
                    return response()->json([
                        "error"=>true,
                        "mensaje"=>"No se pudo eliminar la información"
                    ]);
                }
                
            }catch (\Throwable $e) {
                DB::Rollback();
                Log::error('UsuarioController => eliminar => mensaje => '.$e->getMessage());
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'Ocurrió un error, intentelo más tarde'
                ]);
                
            }
        });
        return $transaction;
    }

    public function cambiarClave(Request $request){
      
        try {
            $validator = Validator::make($request->all(), [
                'clave_actual' => 'required|min:6|string|regex:/^[a-zA-Z0-9_\-@$&#.]{6,18}$/',
                'clave_nueva' => 'required|min:6|string|regex:/^[a-zA-Z0-9_\-@$&#.]{6,18}$/',
            ]);
            if ($validator->fails()) {    
                return response()->json([
                    'error' => true, 
                    'detalle' => 'Contraseña debe tener mínimo 6 caracteres'
                ]);

            }
            $usuario= auth()->User();

            if (Hash::check($request['clave_actual'], $usuario->password)){
            
                if($request['clave_nueva']==$request['clave_nueva_confirm']){

                    if (Hash::check($request['clave_nueva'], $usuario->password)){

                        return response()->json([
                            'error'=>true,
                            'detalle' => 'La nueva contraseña no puede ser igual a la anterior'
                        ]);

                    }else{

                        $usuario->password=bcrypt($request['clave_nueva']);
                        if($usuario->save()){
                            return response()->json(['error'=>false,'detalle'=>'Contraseña actualizada exitosamente']);
                        }
                        else{
                            return response()->json(['error'=>true,'detalle'=>'Error, inténtelo nuevamente']);
                        }

                    }
                    
                }else{
                    return response()->json(['error'=>true,'detalle'=>'Las contraseñas no coinciden']);
                } 
            }else{
                return response()->json(['error'=>true,'detalle'=>'La contraseña actual ingresada no es la correcta por favor verificar']);
            }

        } catch (\Throwable $th) {
            Log::error('UsuarioController,CambiarContrasenia:' . $th->getMessage()); 
            return response()->json(['error'=>true,'detalle'=>'Incovenientes al procesar la solicitud, intente nuevamente']);

        }
    }

    public function resetearPassword($idusuario){
        try{
            $existe=User::where('id',$idusuario)
            ->where('estado','A')
            ->first();

            if(is_null($existe)){
                return response()->json([
                    "error"=>true,
                    "mensaje"=>"No se encontró la información del usuario"
                ]);
            }

            $contrasenia_reseteada=$existe->tx_login;

            $existe->password=Hash::make($contrasenia_reseteada);
            $existe->estado='A';
            $existe->id_actualizado=auth()->user()->id_usuario;
            $existe->fe_actualiza=date('Y-m-d H:i:s');

            if($existe->save()){
                return response()->json([
                    "error"=>false,
                    "mensaje"=>"La contraseña ha sido reseteada exitosamente"
                ]);
            }else{
                return response()->json([
                    "error"=>true,
                    "mensaje"=>"No se pudo resetear la contraseña"
                ]);
            }
        } catch (\Throwable $th) {
            Log::error('UsuarioController,resetearPassword:' . $th->getMessage()); 
            return response()->json(['error'=>true,'detalle'=>'Incovenientes al procesar la solicitud, intente nuevamente']);

        }
    }

    public function bodegaUsuario($id){
        try{
            $bodegas=Bodega::where('estado','=',1)->get();
            foreach($bodegas as $key=> $data){
                $verificaBodega=BodegaUsuario::where('idusuario',$id)
                ->where('idbodega',$data->idbodega)->first();
                if(!is_null($verificaBodega)){
                    $bodegas[$key]->accesoPerm="S";
                }else{
                    $bodegas[$key]->accesoPerm="N";
                }
            }
            
            return response()->json([
                'error'=>false,
                'resultado'=>$bodegas
            ]);
               
        }catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }


    public function mantenimientoBodegaUser($idbodega, $tipo, $iduser){
       
        try{
            //agregamos
            if($tipo=="A"){
              
                $ultimo=BodegaUsuario::get()->last();
                if(is_null($ultimo)){
                   $suma=1;
                }else{
                    $suma=$ultimo->idbodega_usuario+1;
                }
                $bodega_user= new BodegaUsuario();
                $bodega_user->idbodega_usuario=$suma;
                $bodega_user->idusuario=$iduser;
                $bodega_user->idbodega=$idbodega;
                $bodega_user->save();
                return response()->json([
                    'error'=>false,
                    'mensaje'=>'Información registrada exitosamente'
                ]);
            }else{
                //lo quitamos
                $quitar=BodegaUsuario::where('idbodega',$idbodega)
                ->where('idusuario',$iduser)->first();
                $quitar->delete();
                return response()->json([
                    'error'=>false,
                    'mensaje'=>'Información quitada exitosamente'
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
