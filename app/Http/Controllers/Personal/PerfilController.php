<?php

namespace App\Http\Controllers\Personal;
use App\Http\Controllers\Controller;
use App\Models\Personal\Perfil;
use App\Models\Personal\PerfilAcceso;
use App\Models\Personal\Menu;
use App\Models\Personal\GestionMenu;
use App\Models\User;
use \Log;
use DB;
use Illuminate\Http\Request;

class PerfilController extends Controller
{
    public function index(){
       
        return view('gestion_acceso.perfil');
    }


    public function listar(){
        try{
            $perfil=Perfil::where('estado','!=','I')->get();
            return response()->json([
                'error'=>false,
                'resultado'=>$perfil
            ]);
        }catch (\Throwable $e) {
            Log::error('PerfilController => listar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }

    public function editar($id){
        try{
            $perfil=Perfil::where('estado','A')
            ->where('id_perfil', $id)
            ->first();
            
            return response()->json([
                'error'=>false,
                'resultado'=>$perfil
            ]);
        }catch (\Throwable $e) {
            Log::error('PerfilController => editar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }
    

    public function guardar(Request $request){
        
        $messages = [
            'descripcion.required' => 'Debe ingresar la descripcion',           
        ];
           

        $rules = [
            'descripcion' =>"required|string|max:100",
                 
        ];

        $this->validate($request, $rules, $messages);
        try{
           
            $ultimo=Perfil::orderBy('id_perfil','desc')->first();
            if(is_null($ultimo)){
                $suma=1;
            }else{
                $suma=$ultimo->id_perfil+1;
            }

            $guarda_perfil=new Perfil();
            $guarda_perfil->id_perfil=$suma;
            $guarda_perfil->descripcion=$request->descripcion;
            $guarda_perfil->id_usuario_reg=auth()->user()->id;
            $guarda_perfil->fecha_reg=date('Y-m-d H:i:s');
            $guarda_perfil->estado="A";

            //validar que el rol no se repita
            $valida_rol=Perfil::where('descripcion', $guarda_perfil->descripcion)
            ->where('estado','A')
            ->first();

            if(!is_null($valida_rol)){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'El rol ya existe'
                ]);
            }

           
            if($guarda_perfil->save()){
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
            Log::error('PerfilController => guardar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }


    public function actualizar(Request $request, $id){
       
    
        $messages = [
            'descripcion.required' => 'Debe ingresar la descripcion',           
        ];
           

        $rules = [
            'descripcion' =>"required|string|max:100",
                 
        ];

        $this->validate($request, $rules, $messages);
        try{

            $actualiza_rol= Perfil::find($id);
            $actualiza_rol->descripcion=$request->descripcion;
            $actualiza_rol->id_usuario_act=auth()->user()->id;
            $actualiza_rol->fecha_actualiza=date('Y-m-d H:i:s');
            $actualiza_rol->estado="A";

            //validar que el rol no se repita
            $valida_rol=Perfil::where('descripcion', $actualiza_rol->descripcion)
            ->where('estado','A')
            ->first();

            if(!is_null($valida_rol)){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'El rol ya existe'
                ]);
            }

           
            if($actualiza_rol->save()){
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
            Log::error('PerfilController => actualizar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function accesoPerfil($id){
        try{
            $menu=Menu::where('estado','!=','I')->get();
            foreach($menu as $key=> $data){
                $verificaAcceso=PerfilAcceso::where('id_perfil',$id)
                ->where('id_menu',$data->id_menu)->first();
                if(!is_null($verificaAcceso)){
                    $menu[$key]->accesoPerm="S";
                }else{
                    $menu[$key]->accesoPerm="N";
                }
            }
            
            return response()->json([
                'error'=>false,
                'resultado'=>$menu
            ]);
               
        }catch (\Throwable $e) {
            Log::error('PerfilController => eliminar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function mantenimientoAccesoPerfil($idmenu, $tipo, $idperfil){
       
        try{
            //agregamos
            if($tipo=="A"){
                //obtenemos el id de la gestion del menu
                $idGestion=GestionMenu::where('id_menu', $idmenu)->pluck('id_gestion')->first();
               
                $ultimo=PerfilAcceso::orderBy('id_perfil_acceso','desc')->first();
                if(is_null($ultimo)){
                    $suma=1;
                }else{
                    $suma=$ultimo->id_perfil_acceso+1;
                }

                $acceso_perf= new PerfilAcceso();
                $acceso_perf->id_perfil_acceso=$suma;
                $acceso_perf->id_perfil=$idperfil;
                $acceso_perf->id_menu=$idmenu;
                $acceso_perf->id_gestion=$idGestion;
                $acceso_perf->save();
                return response()->json([
                    'error'=>false,
                    'mensaje'=>'Información registrada exitosamente'
                ]);
            }else{
                //lo quitamos
                $quitar=PerfilAcceso::where('id_menu',$idmenu)
                ->where('id_perfil',$idperfil)->first();
                $quitar->delete();
                return response()->json([
                    'error'=>false,
                    'mensaje'=>'Información registrada exitosamente'
                ]);
            }
           

        }catch (\Throwable $e) {
            Log::error('PerfilController => mantenimientoAccesoPerfil => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function eliminar($id){
        try{
            //verificamos que no este asociado a un usuario
            $veri_PerfilUsuario=DB::table('bodega.per_perfil_usuario')
            ->where('id_perfil',$id)
            ->first();
            if(!is_null($veri_PerfilUsuario)){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'El perfil está asociado a un usuario, no se puede eliminar'
                ]);
            }

            $perfil=Perfil::find($id);
            $perfil->id_usuario_act=auth()->user()->id;
            $perfil->fecha_actualiza=date('Y-m-d H:i:s');
            $perfil->estado="I";
            if($perfil->save()){
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
            Log::error('PerfilController => eliminar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function datoPerfil(){
        $data=User::with('persona','perfil')->where('id',auth()->user()->id)->first();
       
        return response()->json([
            "error"=>false,
            "data"=>$data
        ]);

      
    }


}
