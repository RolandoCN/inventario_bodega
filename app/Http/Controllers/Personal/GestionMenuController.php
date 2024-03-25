<?php

namespace App\Http\Controllers\Personal;
use App\Http\Controllers\Controller;
use App\Models\Personal\Menu;
use App\Models\Personal\Gestion;
use App\Models\Personal\GestionMenu;
use \Log;
use Illuminate\Http\Request;

class GestionMenuController extends Controller
{
    public function index(){
        $menu=Menu::where('estado','A')->get();
        $gestion=Gestion::where('estado','A')->get();
        return view('gestion_menu.gestion_menu',[
            "menu"=>$menu,
            "gestion"=>$gestion
        ]);
    }


    public function listar(){
        try{
            $gestion_menu=GestionMenu::with('menu','gestion')
            ->where('estado','!=','I')->get();
            return response()->json([
                'error'=>false,
                'resultado'=>$gestion_menu
            ]);
        }catch (\Throwable $e) {
            Log::error('GestionMenuController => listar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }

    public function editar($id){
        try{
            $gestion_menu=GestionMenu::where('estado','A')
            ->where('id_gestion_menu', $id)
            ->first();
            
            return response()->json([
                'error'=>false,
                'resultado'=>$gestion_menu
            ]);
        }catch (\Throwable $e) {
            Log::error('GestionMenuController => editar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }
    

    public function guardar(Request $request){
        
        $messages = [
            'gestion.required' => 'Debe seleccionar la gestión',  
            'menu.required' => 'Debe seleccionar el menu',           
        ];
           

        $rules = [
            'gestion' =>"required",
            'menu' =>"required",
        ];

        $this->validate($request, $rules, $messages);
        try{
            $ultimo=GestionMenu::get()->last();
            $guarda_gestion_menu=new GestionMenu();
            $guarda_gestion_menu->id_gestion_menu=$ultimo->id_gestion_menu+1;
            $guarda_gestion_menu->id_gestion=$request->gestion;
            $guarda_gestion_menu->id_menu=$request->menu;
            $guarda_gestion_menu->id_usuario_reg=auth()->user()->id;
            $guarda_gestion_menu->fecha_reg=date('Y-m-d H:i:s');
            $guarda_gestion_menu->estado="A";

            //validar que la gestion-menu no se repita
            $valida_gestion_menu=GestionMenu::where('id_menu', $guarda_gestion_menu->id_menu)
            ->where('estado','A')
            ->first();

            if(!is_null($valida_gestion_menu)){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'El menú ya existe'
                ]);
            }

           
            if($guarda_gestion_menu->save()){
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
            Log::error('GestionMenuController => guardar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }


    public function actualizar(Request $request, $id){
        $messages = [
            'gestion.required' => 'Debe seleccionar la gestión',  
            'menu.required' => 'Debe seleccionar el menu',           
        ];
           

        $rules = [
            'gestion' =>"required",
            'menu' =>"required",
        ];

        $this->validate($request, $rules, $messages);
        try{

            $actualiza_gestion_menu= GestionMenu::find($id);
            $actualiza_gestion_menu->id_gestion=$request->gestion;
            $actualiza_gestion_menu->id_menu=$request->menu;
            $actualiza_gestion_menu->id_usuario_reg=auth()->user()->id;
            $actualiza_gestion_menu->fecha_reg=date('Y-m-d H:i:s');
            $actualiza_gestion_menu->estado="A";

            //validar que la gestion-menu no se repita
            $valida_gestion_menu=GestionMenu::where('id_menu', $actualiza_gestion_menu->id_menu)
            ->where('estado','A')
            ->where('id_gestion_menu','!=',$id)
            ->first();

            if(!is_null($valida_gestion_menu)){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'El menú ya existe'
                ]);
            }

           
            if($actualiza_gestion_menu->save()){
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
            Log::error('GestionMenuController => actualizar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function eliminar($id){
        try{
            $gestion_menu=GestionMenu::find($id);
            $gestion_menu->id_usuario_act=auth()->user()->id;
            $gestion_menu->fecha_actualiza=date('Y-m-d H:i:s');
            $gestion_menu->estado="I";
            if($gestion_menu->save()){
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
            Log::error('GestionMenuController => eliminar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

}
