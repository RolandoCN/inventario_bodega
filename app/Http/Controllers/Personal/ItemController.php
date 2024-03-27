<?php

namespace App\Http\Controllers\Personal;
use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Models\Bodega\Producto;
use App\Models\Personal\Tarea;
use \Log;
use Illuminate\Http\Request;
use DB;

class ItemController extends Controller
{
    
  
    public function index(){
        $marca= DB::table('marca')
        ->where('estado','A')
        ->get();
        $modelo= DB::table('modelo')
        ->where('estado','A')
        ->get();
        return view('gestion_bodega.producto',[
            "marca"=>$marca,
            "modelo"=>$modelo,
           
        ]);
    }


    public function listar(){
        try{
            $persona=Producto::with('marca','modelo')->where('estado','!=','I')->get();
            return response()->json([
                'error'=>false,
                'resultado'=>$persona
            ]);
        }catch (\Throwable $e) {
            Log::error('ItemController => listar => mensaje => '.$e->getMessage());
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
            Log::error('ItemController => editar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }
    

    public function guardar(Request $request){
        
        try{
            // dd($request->all());
            $iva=0.12;
            if($request->cmb_iva=="Si"){
               $precio_venta=$request->precio;
               $valor_iva=$precio_venta*$iva;
               $valor_venta=$precio_venta + $valor_iva;

            }else{
                $precio_venta=$request->precio;
                $valor_iva=0;
                $valor_venta=$precio_venta + $valor_iva;
            }
            // dd($valor_venta);

            $guarda_producto=new Producto();
            $guarda_producto->codigo=$request->codigo;
            $guarda_producto->descripcion=$request->descripcion;
            $guarda_producto->id_marca=$request->cmb_marca;
            $guarda_producto->idmodelo=$request->cmb_modelo;
            $guarda_producto->detalle=$request->detalle;
            $guarda_producto->estado="A";
            $guarda_producto->precio=number_format(($precio_venta),2,'.', '');
            $guarda_producto->iva=number_format(($valor_iva),2,'.', '');
            $guarda_producto->valor_venta=number_format(($valor_venta),2,'.', '');
            $guarda_producto->grava_iva=$request->cmb_iva;

            //validar que codigo no se repita
            $valida_codigo=Producto::where('codigo', $guarda_producto->codigo)
            ->first();

            if(!is_null($valida_codigo)){
                if($valida_codigo->estado=="A"){
                    return response()->json([
                        'error'=>true,
                        'mensaje'=>'El número de cédula ya existe, en otra persona'
                    ]);
                }else{
                    $valida_codigo->codigo=$$request->codigo;
                    $valida_codigo->descripcion=$request->descripcion;
                    $valida_codigo->id_marca=$request->cmb_marca;
                    $valida_codigo->idmodelo=$request->cmb_modelo;
                    $valida_codigo->detalle=$request->detalle;
                    $valida_codigo->estado="A";
                    $valida_codigo->precio=number_format(($precio_venta),2,'.', '');
                    $valida_codigo->iva=number_format(($valor_iva),2,'.', '');
                    $valida_codigo->valor_venta==number_format(($valor_venta),2,'.', '');
                    $valida_codigo->grava_iva=$request->cmb_iva;
                    $valida_codigo->save();
                    return response()->json([
                        'error'=>false,
                        'mensaje'=>'Información actualizada exitosamente'
                    ]);
                }
                
            }

           
            if($guarda_producto->save()){
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
            Log::error('ItemController => guardar => mensaje => '.$e->getMessage());
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
            
            if($request->tipo_id==1){
                $numero_ident=$request->cedula_persona;
                $validaCedula=validarCedula($request->cedula_persona);
                if($validaCedula==false){
                    return response()->json([
                        "error"=>true,
                        "mensaje"=>"El numero de identificacion ingresado no es valido"
                    ]);
                }  
            }else if($request->tipo_id==2){
                $numero_ident=$request->ruc_persona;
            }
            
            $guarda_persona= Persona::find($id);
            $guarda_persona->numero_doc=$numero_ident;
            $guarda_persona->nombres=$request->nombres;
            $guarda_persona->apellidos=$request->apellidos;
            $guarda_persona->telefono=$request->telefono;
            $guarda_persona->correo_electronico=$request->email;
            $guarda_persona->estado="A";
            $guarda_persona->tipo_doc=$request->tipo_id;

            //validar que la cedula no se repita
            $valida_cedula=Persona::where('numero_doc', $guarda_persona->numero_doc)
            ->where('idpersona','!=', $id)
            ->first();

            if(!is_null($valida_cedula)){
                
                if($valida_cedula->estado=="A"){
                    return response()->json([
                        'error'=>true,
                        'mensaje'=>'El número de identificacion ya existe, en otra persona'
                    ]);
                }else{
                    $valida_cedula->numero_doc=$numero_ident;
                    $valida_cedula->nombres=$request->nombres;
                    $valida_cedula->apellidos=$request->apellidos;
                    $valida_cedula->telefono=$request->telefono;
                    $valida_cedula->correo_electronico=$request->email;
                    $valida_cedula->estado="A";
                    $valida_cedula->tipo_doc=$request->tipo_id;
                    $valida_cedula->save();
                    return response()->json([
                        'error'=>false,
                        'mensaje'=>'Información actualizada exitosamente'
                    ]);
                }
    
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
            Log::error('ItemController => actualizar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function eliminar($id){
        try{
            $persona=Persona::find($id);
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
            Log::error('ItemController => eliminar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }
    
}
