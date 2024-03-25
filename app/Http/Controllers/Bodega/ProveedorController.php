<?php

namespace App\Http\Controllers\Bodega;
use App\Http\Controllers\Controller;
use App\Models\Personal\Especialidad;
use \Log;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator; 
use App\Models\Bodega\Proveedor;

class ProveedorController extends Controller
{
    public function cargaComboBodega(){
        $proveedor= DB::connection('pgsql')->table('bodega.proveedor')
        ->where('estado1',1)
        ->get();

        return response()->json([
            'error'=>false,
            'resultado'=>$proveedor
        ]);
    }
    public function guardar(Request $request){
      
        $validator = Validator::make($request->all(), [
            'ruc' => 'required',   
            'contacto' => 'required',
            'empresa' => 'required',
        ]);
        
        if($validator->fails()){
            return (['mensaje'=>'Complete todos los datos del formulario1','error'=>true]);
        }

        if(!is_null($request->email)){
            
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',   
                
            ]);
            
            if($validator->fails()){
                return (['mensaje'=>'La direccion de email es incorrecta','error'=>true]);
            }
        }

        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{
                //verificamos si ya existe el ruc
                $exite=Proveedor::where('ruc',$request->ruc)
                ->where('ruc','<>',0)
                ->first();
                if(!is_null($exite)){
                    return (['mensaje'=>'Ya existe una empresa con el ruc ingresado','error'=>true]); 
                }

                $ultimoProveedor= Proveedor::orderBy('idprov','desc')
                ->first();
              
                //registramos 
                $proveedor=new Proveedor();
                $proveedor->idprov=$ultimoProveedor->idprov+1;
                $proveedor->ruc=$request->ruc;
                $proveedor->contacto=strtoupper($request->contacto);
                $proveedor->empresa=strtoupper($request->empresa);
                $proveedor->mail=$request->email;
                $proveedor->telefono=$request->telefono;
                $proveedor->estado1=1;
               
                if($proveedor->save()){
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


   
}
