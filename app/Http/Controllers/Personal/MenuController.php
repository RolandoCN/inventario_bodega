<?php

namespace App\Http\Controllers\Personal;
use App\Http\Controllers\Controller;
use App\Models\Personal\Menu;
use \Log;
use Illuminate\Http\Request;
use DB;

class MenuController extends Controller
{
      
    public function index(){
        return view('gestion_menu.menu');
    }


    public function listar(){
        try{
            $menu=Menu::where('estado','!=','I')->get();
            return response()->json([
                'error'=>false,
                'resultado'=>$menu
            ]);
        }catch (\Throwable $e) {
            Log::error('MenuController => listar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }

    public function editar($id){
        try{
            $menu=Menu::where('estado','A')
            ->where('id_menu', $id)
            ->first();
            
            return response()->json([
                'error'=>false,
                'resultado'=>$menu
            ]);
        }catch (\Throwable $e) {
            Log::error('MenuController => editar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }
    

    public function guardar(Request $request){
        
        $messages = [
            'descripcion.required' => 'Debe ingresar la descripción',
            'url.required' => 'Debe ingresar la url',           
        ];
           

        $rules = [
            'descripcion' =>"required|string|max:100",
            'url' =>"required|string|max:100",
        ];

        $this->validate($request, $rules, $messages);
        try{
            
            $ultimo=Menu::orderBy('id_menu','desc')->first();
            if(is_null($ultimo)){
                $suma=1;
            }else{
                $suma=$ultimo->id_menu+1;
            }


            $guarda_menu=new Menu();
            $guarda_menu->id_menu=$suma;
            $guarda_menu->descripcion=$request->descripcion;
            $guarda_menu->url=$request->url;
            $guarda_menu->id_usuario_reg=auth()->user()->id;
            $guarda_menu->fecha_reg=date('Y-m-d H:i:s');
            $guarda_menu->estado="A";

            //validar que el menu no se repita
            $valida_menu=Menu::where('descripcion', $guarda_menu->descripcion)
            ->where('estado','A')
            ->first();

            if(!is_null($valida_menu)){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'El menú ya existe'
                ]);
            }

            //validar que la url no se repita
            $valida_url=Menu::where('url', $guarda_menu->url)
            ->where('estado','A')
            ->first();

            if(!is_null($valida_url)){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'La url ya existe'
                ]);
            }

           
            if($guarda_menu->save()){
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
            Log::error('MenuController => guardar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }


    public function actualizar(Request $request, $id){
        $messages = [
            'descripcion.required' => 'Debe ingresar la descripción',
            'url.required' => 'Debe ingresar la url',           
        ];
           

        $rules = [
            'descripcion' =>"required|string|max:100",
            'url' =>"required|string|max:100",
        ];
        $this->validate($request, $rules, $messages);
        try{

            $actualiza_menu= Menu::find($id);
            $actualiza_menu->descripcion=$request->descripcion;
            $actualiza_menu->url=$request->url;
            $actualiza_menu->id_usuario_act=auth()->user()->id;
            $actualiza_menu->fecha_actualiza=date('Y-m-d H:i:s');
            $actualiza_menu->estado="A";

            //validar que el menu no se repita
            $valida_menu=Menu::where('descripcion', $actualiza_menu->descripcion)
            ->where('estado','A')
            ->where('id_menu','!=',$id)
            ->first();

            if(!is_null($valida_menu)){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'La gestión ya existe'
                ]);
            }

            //validar que la url no se repita
            $valida_url=Menu::where('url', $actualiza_menu->url)
            ->where('estado','A')
            ->where('id_menu','!=',$id)
            ->first();

            if(!is_null($valida_url)){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'La url ya existe'
                ]);
            }

           
            if($actualiza_menu->save()){
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
            Log::error('MenuController => actualizar => mensaje => '.$e->getMessage());
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
            ->where('id_menu',$id)
            ->where('estado','A')
            ->first();
            // if(!is_null($veri_GestionMenu)){
            //     return response()->json([
            //         'error'=>true,
            //         'mensaje'=>'El menú está relacionado, no se puede eliminar'
            //     ]);
            // }

            $menu=Menu::find($id);
            $menu->id_usuario_act=auth()->user()->id;
            $menu->fecha_actualiza=date('Y-m-d H:i:s');
            $menu->estado="I";
            if($menu->save()){
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
            Log::error('MenuController => eliminar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

}
