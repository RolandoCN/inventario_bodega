<?php

namespace App\Http\Controllers\Personal;
use App\Http\Controllers\Controller;
use App\Models\Personal\Funcionario;
use App\Models\Personal\Permiso;
use \Log;
use Illuminate\Http\Request;
use DB;
class PermisoController extends Controller
{
    
  
    public function index(){
        $tipo_permiso=DB::table('per_tipo_permiso')
        ->where('estado','A')
        ->get();
        $tipo_justificacion=DB::table('per_justificacion')
        ->where('estado','A')
        ->get();
        return view('gestion_permiso.permiso',[
            "tipo_permiso"=>$tipo_permiso,
            "tipo_justificacion"=>$tipo_justificacion
        ]);
    }

    public function buscarFuncionario(Request $request){

        $data = [];
        if($request->has('q')){
            $search = $request->q;
            $text=mb_strtoupper($search);
            $data=Funcionario::where(function($query)use($text){
                $query->where('nombres', 'like', '%'.$text.'%')
                ->orWhere('apellidos', 'like', '%'.$text.'%')
                ->orWhere('cedula', 'like', '%'.$text.'%');
            })
            ->where('estado','A')
            ->take(10)->get();
        }
        
        return response()->json($data);

    } 

    public function infoFuncionario($id){
        try{
            $funcionario=DB::table('per_funcionario as f')
            ->leftJoin('per_area as area', 'area.id_area','f.id_area')
            ->leftJoin('per_ambito as amb', 'amb.id_ambito','f.id_ambito')
            ->leftJoin('per_ambito_ley as ley', 'ley.id_ambito_ley','f.id_ambito_ley')
            ->select('area.descripcion as nombre_area','amb.descripcion as nombre_amb',
            'ley.descripcion as nombre_ley', 'area.administrativo')
            ->where('f.estado','!=','I')
            ->where('f.id_funcionario', $id)
            ->first();
            return response()->json([
                'error'=>false,
                'resultado'=>$funcionario
            ]);
        }catch (\Throwable $e) {
            Log::error('PermisoController => listar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }

    public function justificaPersona($idjust, $idfunc, $ini, $fin){
        try{
            
            $fecha_ini_selecc=explode("-", $ini);
            $año_ini_selecc=$fecha_ini_selecc[0];

            $fecha_fin_selecc=explode("-", $fin);
            $año_fin_selecc=$fecha_fin_selecc[0];
           
            //si es certificado medico sumamos tanto laboral y particular
            
            if($idjust==4 || $idjust==15){
                $notifica=Permiso::where('estado','A')
                ->where('id_funcionario', $idfunc)
                ->where(function($c)use($idjust) {
                    $c->where('id_justificacion', '4')
                    ->orWhere('id_justificacion', '15');
                })
                ->where(function($q)use($año_ini_selecc,$año_fin_selecc) {
                    $q->whereBetween('fecha_fin',[$año_ini_selecc."-01-01", $año_fin_selecc."-12-31"])
                    ->orwhereBetween('fecha_ini',[$año_ini_selecc."-01-01", $año_fin_selecc."-12-31"]);
                })
                // ->where(function($q)use($año_ini_selecc,$año_fin_selecc) {
                //     $q->whereYear('fecha_ini', '=', $año_ini_selecc)
                //     ->orwhereYear('fecha_fin', '=', $año_fin_selecc);
                // })
              
                // ->select(DB::raw('sum(cant_hora) as cantidadHoras'))
                ->get();
            }else{
                $notifica=Permiso::where('estado','A')
                ->where('id_funcionario', $idfunc)
                ->where('id_justificacion', $idjust)
                ->where(function($q)use($año_ini_selecc,$año_fin_selecc) {
                    $q->whereBetween('fecha_fin',[$año_ini_selecc."-01-01", $año_fin_selecc."-12-31"])
                    ->orwhereBetween('fecha_ini',[$año_ini_selecc."-01-01", $año_fin_selecc."-12-31"]);
                })
                ->get();
            }
        
            // dd($notifica);
            $cant_dia_aux=0;
            foreach($notifica as $data){
                $fecha_ini=$data->fecha_ini;
                $separa_ini=explode("-", $fecha_ini);
            
                $solo_anio_ini=$separa_ini[0];

                $fecha_fin=$data->fecha_fin;
                
                $solo_anio_fin=explode("-", $fecha_fin);
                $solo_anio_fin=$solo_anio_fin[0];
               
                // if($solo_anio_ini != $año_ini_selecc){
                    //existe data en ese año
                    $existe=Permiso::where('estado','A')
                    ->where('id_permiso', $data->id_permiso)
                    ->where('id_funcionario', $idfunc)
                    ->where(function($q)use($año_ini_selecc,$año_fin_selecc) {
                        $q->whereBetween('fecha_fin',[$año_ini_selecc."-01-01", $año_fin_selecc."-12-31"])
                        ->orwhereBetween('fecha_ini',[$año_ini_selecc."-01-01", $año_fin_selecc."-12-31"]);
                    })
                        
                    ->first();
                    
                    if(!is_null($existe)){
                        
                        if($solo_anio_ini > $año_ini_selecc && $solo_anio_fin < $año_fin_selecc ){
                           
                            $date1 = new \DateTime($fecha_ini);
                            $date2 = new \DateTime($solo_anio_ini."-12-31");
                          
                           
                        }else{
                            // $date1 = new \DateTime($solo_anio_fin."-01-01");
                            // $date2 = new \DateTime($fecha_fin);
                            if($solo_anio_ini == $año_ini_selecc){
                                $date1 = new \DateTime($fecha_ini);
                                $date2 = new \DateTime($fecha_fin);
                              
                            }else{
                                // dd('d');
                                // $date1 = new \DateTime($solo_anio_fin."-01-01");
                                // $date2 = new \DateTime($fecha_fin);

                                $date1 = new \DateTime($solo_anio_ini."-01-01");
                                $date2 = new \DateTime($fecha_fin);
                            }
                        }
                      
                        $diff = $date1->diff($date2);
                        
                        $cant_dia_aux=$cant_dia_aux + $diff->days;
                        $cant_dia_aux=$cant_dia_aux+1; 

                    }
               
                // }else{
                    
                //     $existe=Permiso::where('estado','A')
                //     ->where('id_permiso', $data->id_permiso)
                //     ->where('id_funcionario', $idfunc)
                //     ->whereBetween('fecha_ini',[$año_ini_selecc."-01-01", $año_fin_selecc."-12-31"])
                //     ->first();
                   
                //     if(!is_null($existe)){
                //         dd($solo_anio_ini); 
                //         $date1 = new \DateTime($fecha_ini);
                //         $date2 = new \DateTime($solo_anio_ini."-12-31");
                
                //         $diff = $date1->diff($date2);
                //         $cant_dia_aux=$cant_dia_aux + $diff->days;
                //         $cant_dia_aux=$cant_dia_aux+1;      
                       
                //     }   
                // }  
             
               
            }
            $notifica=['cantidadHoras'=>$cant_dia_aux*8];
                   
            return response()->json([
                'error'=>false,
                'mensaje'=>$notifica
            ]);
            
        }catch (\Throwable $e) {
            Log::error('PermisoController => justificaPersona => mensaje => '.$e->getMessage().' linea => '.$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }
    

    public function guardar(Request $request){
       
        $messages = [
            'cmb_persona.required' => 'Debe seleccionar el funcionario',  
            'tipo_permiso.required' => 'Debe seleccionar el tipo permiso',           
            'tipo_justificacion.required' => 'Debe seleccionar el tipo justificación',  
            'observacion.required' => 'Debe ingresar la observacion',  

        ];
           

        $rules = [
            'cmb_persona' =>"required|string|max:10",
            'tipo_permiso' =>"required|string|max:100",
            'tipo_justificacion' =>"required|string|max:100",
            'observacion' =>"required|string|max:400",
                     
        ];

        $this->validate($request, $rules, $messages);
        try{     

            $guarda_permiso=new Permiso();
            $guarda_permiso->id_funcionario=$request->cmb_persona;
            $guarda_permiso->id_tipo_permiso=$request->tipo_permiso;
            
            if($guarda_permiso->id_tipo_permiso==2){
               
                $guarda_permiso->fecha_hora_ini= date('Y-m-d H:i:s', strtotime($request->fecha_ini));
                $guarda_permiso->fecha_hora_fin=date('Y-m-d H:i:s', strtotime($request->fecha_fin));
                $guarda_permiso->fecha_ini= date('Y-m-d', strtotime($request->fecha_ini));
                $guarda_permiso->fecha_fin=date('Y-m-d', strtotime($request->fecha_fin));

                $guarda_permiso->cant_dia=$request->cant_dias;
            }else if($guarda_permiso->id_tipo_permiso==1){
                
                $guarda_permiso->fecha_hora_ini=date('Y-m-d H:i:s', strtotime($request->fecha_hora_ini));
                $guarda_permiso->fecha_hora_fin=date('Y-m-d H:i:s', strtotime($request->fecha_hora_fin));
                $guarda_permiso->fecha_ini= date('Y-m-d', strtotime($request->fecha_hora_ini));
                $guarda_permiso->fecha_fin=date('Y-m-d', strtotime($request->fecha_hora_fin));

                $guarda_permiso->cant_dia=null;
            }else{
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'No se encontro informacion con el tipo permiso seleccionado'
                ]);
            }
           
           
            if(!is_null($request->cant_dias_horas)){
                if($request->cant_dias_horas<10){
                    $guarda_permiso->cant_hora="0".$request->cant_dias_horas.":00";
                }else{
                    $guarda_permiso->cant_hora=$request->cant_dias_horas.":00";
                }
               
            }else{
                $guarda_permiso->cant_hora=$request->cant_horas;
            }
              
          
            $guarda_permiso->id_justificacion=$request->tipo_justificacion;
            $guarda_permiso->observacion=$request->observacion;
            $guarda_permiso->id_usuario_reg=auth()->user()->id;
            $guarda_permiso->fecha_registro=date('Y-m-d H:i:s');
            $guarda_permiso->estado="A";
            $guarda_permiso->anio=date('Y');

            //validar que no haya dos permisos en el mismo dia
          
            if($guarda_permiso->id_tipo_permiso==2){
                $fec_ini=$guarda_permiso->fecha_hora_ini;
                $fec_fin=$guarda_permiso->fecha_hora_fin;

                $valida_permiso=Permiso::where('id_funcionario', $guarda_permiso->id_funcionario)
                ->where('estado','A')
                ->where(function($c)use($fec_ini,$fec_fin) {
                    $c->whereBetween('fecha_hora_ini',[$fec_ini, $fec_fin])
                    ->orwhereBetween('fecha_hora_fin', [$fec_ini, $fec_fin]);
                })
                ->first();

                if(!is_null($valida_permiso)){
                    return response()->json([
                        'error'=>true,
                        'mensaje'=>'El funcionario ya tiene asignado un permiso en el rango de fecha seleccionado'
                    ]);
                }
            }else if($guarda_permiso->id_tipo_permiso==1){
                
                $fec_ini=date('Y-m-d', strtotime($guarda_permiso->fecha_hora_ini));
                $fec_fin=date('Y-m-d', strtotime($guarda_permiso->fecha_hora_fin));
              
                $valida_permiso=Permiso::where('id_funcionario', $guarda_permiso->id_funcionario)
                ->where('estado','A')
                ->where(function($c)use($fec_ini,$fec_fin) {
                    $c->whereBetween('fecha_hora_ini',[$fec_ini, $fec_fin])
                    ->orwhereBetween('fecha_hora_fin', [$fec_ini, $fec_fin]);
                })
                ->first();

                if(!is_null($valida_permiso)){
                    return response()->json([
                        'error'=>true,
                        'mensaje'=>'El funcionario ya tiene asignado un permiso en el rango de fecha seleccionado'
                    ]);
                }

            }
            
            if($guarda_permiso->save()){
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
            Log::error('PermisoController => guardar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }

    public function listadoVista(){
        $tipo_permiso=DB::table('per_tipo_permiso')
        ->where('estado','A')
        ->get();
        $tipo_justificacion=DB::table('per_justificacion')
        ->where('estado','A')
        ->get();
        return view('gestion_permiso.listado',[
            "tipo_permiso"=>$tipo_permiso,
            "tipo_justificacion"=>$tipo_justificacion
        ]);
    }

    public function filtrarPermiso($ini, $fin){
        
        try{
            $permisos=DB::table('per_permiso as p')
            ->leftJoin('per_funcionario as f', 'f.id_funcionario','p.id_funcionario')
            ->leftJoin('per_area as a', 'a.id_area','f.id_area')
            ->leftJoin('per_tipo_permiso as tp', 'tp.id_tipo_permiso','p.id_tipo_permiso') 
            ->leftJoin('per_justificacion as j', 'j.id_justificacion','p.id_justificacion')  
            ->where(function($query)use($ini, $fin){
                $query->whereBetween('p.fecha_ini',[$ini, $fin])
                ->orwhereBetween('p.fecha_fin', [$ini, $fin]);
            })
            ->select('p.fecha_hora_ini','p.fecha_hora_fin', 'tp.descripcion as tipo',DB::raw("CONCAT(f.apellidos,' ', f.nombres) AS funcionario"),'a.descripcion as area_de', 'p.observacion'
            ,'j.descripcion as just','p.id_permiso')
            ->where('p.estado','!=','I')
            ->get();

            return response()->json([
                'error'=>false,
                'resultado'=>$permisos
            ]);
        }catch (\Throwable $e) {
            Log::error('PermisoController => justificaPersona => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }

    public function detalle($id){
        try{
            $permisos=DB::table('per_permiso as p')
            ->leftJoin('per_funcionario as f', 'f.id_funcionario','p.id_funcionario')
            ->leftJoin('per_area as a', 'a.id_area','f.id_area')
            ->leftJoin('per_tipo_permiso as tp', 'tp.id_tipo_permiso','p.id_tipo_permiso') 
            ->leftJoin('per_justificacion as j', 'j.id_justificacion','p.id_justificacion')
            ->leftJoin('users as ui', 'ui.id','p.id_usuario_reg')
            ->leftJoin('per_funcionario as emp_in', 'emp_in.id_funcionario','ui.id_persona')
            ->leftJoin('users as ua', 'ua.id','p.id_usuario_act') 
            ->leftJoin('per_funcionario as emp_act', 'emp_act.id_funcionario','ua.id_persona') 
            ->where('p.id_permiso',$id)
            ->select('p.fecha_hora_ini','p.fecha_hora_fin', 'tp.descripcion as tipo',DB::raw("CONCAT(f.cedula, ' - ', f.nombres,' ', f.apellidos) AS funcionario"),'a.descripcion as area_de', 'p.observacion'
            ,'j.descripcion as just','p.id_permiso','f.id_funcionario', 'p.id_justificacion', 'p.id_tipo_permiso','p.cant_hora', 'p.cant_dia', DB::raw("CONCAT(emp_in.apellidos,' ', emp_in.nombres) AS usuario_ingresa"), DB::raw("CONCAT(emp_act.apellidos,' ', emp_act.nombres) AS usuario_actualiza"),'p.id_permiso','p.fecha_registro','p.fecha_act')
            ->where('p.estado','!=','I')
            ->first();
            
            return response()->json([
                'error'=>false,
                'resultado'=>$permisos
            ]);
        }catch (\Throwable $e) {
            Log::error('PermisoController => eliminar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function actualizar(Request $request, $id){
        
        $messages = [
            'cmb_persona.required' => 'Debe seleccionar el funcionario',  
            'tipo_permiso.required' => 'Debe seleccionar el tipo permiso',           
            'tipo_justificacion.required' => 'Debe seleccionar el tipo justificación',  
            'observacion.required' => 'Debe ingresar la observacion',  

        ];
           

        $rules = [
            'cmb_persona' =>"required|string|max:10",
            'tipo_permiso' =>"required|string|max:100",
            'tipo_justificacion' =>"required|string|max:100",
            'observacion' =>"required|string|max:400",
                     
        ];

        $this->validate($request, $rules, $messages);
        try{     

            $actualiza_permiso= Permiso::find($id);
            $actualiza_permiso->id_funcionario=$request->cmb_persona;
            $actualiza_permiso->id_tipo_permiso=$request->tipo_permiso;

                       
            if($actualiza_permiso->id_tipo_permiso==2){
               
                $actualiza_permiso->fecha_hora_ini= date('Y-m-d H:i:s', strtotime($request->fecha_ini));
                $actualiza_permiso->fecha_hora_fin=date('Y-m-d H:i:s', strtotime($request->fecha_fin));

                $actualiza_permiso->fecha_ini= date('Y-m-d', strtotime($request->fecha_ini));
                $actualiza_permiso->fecha_fin=date('Y-m-d', strtotime($request->fecha_fin));

                $actualiza_permiso->cant_dia=$request->cant_dias;

            }else if($actualiza_permiso->id_tipo_permiso==1){
                
                $actualiza_permiso->fecha_hora_ini=date('Y-m-d H:i:s', strtotime($request->fecha_hora_ini));
                $actualiza_permiso->fecha_hora_fin=date('Y-m-d H:i:s', strtotime($request->fecha_hora_fin));

                $actualiza_permiso->fecha_ini= date('Y-m-d', strtotime($request->fecha_hora_ini));
                $actualiza_permiso->fecha_fin=date('Y-m-d', strtotime($request->fecha_hora_fin));

                $actualiza_permiso->cant_dia=null;
            }else{
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'No se encontro informacion con el tipo permiso seleccionado'
                ]);
            }

           
            if(!is_null($request->cant_dias_horas)){
                if($request->cant_dias_horas<10){
                    $actualiza_permiso->cant_hora="0".$request->cant_dias_horas.":00";
                }else{
                    $actualiza_permiso->cant_hora=$request->cant_dias_horas.":00";
                }
            }else{
                $actualiza_permiso->cant_hora=$request->cant_horas;
            }
              
          
            $actualiza_permiso->id_justificacion=$request->tipo_justificacion;
            $actualiza_permiso->observacion=$request->observacion;
            $actualiza_permiso->id_usuario_act=auth()->user()->id;
            $actualiza_permiso->fecha_act=date('Y-m-d H:i:s');
            $actualiza_permiso->estado="A";
            $actualiza_permiso->anio=date('Y');

            //validar que no haya dos permisos en el mismo dia
          
            if($actualiza_permiso->id_tipo_permiso==2){
                $fec_ini=$actualiza_permiso->fecha_hora_ini;
                $fec_fin=$actualiza_permiso->fecha_hora_fin;

                $valida_permiso=Permiso::where('id_funcionario', $actualiza_permiso->id_funcionario)
                ->where('estado','A')
                ->where(function($c)use($fec_ini,$fec_fin) {
                    $c->whereBetween('fecha_hora_ini',[$fec_ini, $fec_fin])
                    ->orwhereBetween('fecha_hora_fin', [$fec_ini, $fec_fin]);
                })
                ->where('id_permiso','!=', $id)
                ->first();

                if(!is_null($valida_permiso)){
                    return response()->json([
                        'error'=>true,
                        'mensaje'=>'El funcionario ya tiene asignado un permiso en el rango de fecha seleccionado'
                    ]);
                }
            }else if($actualiza_permiso->id_tipo_permiso==1){
                
                $fec_ini=date('Y-m-d', strtotime($actualiza_permiso->fecha_hora_ini));
                $fec_fin=date('Y-m-d', strtotime($actualiza_permiso->fecha_hora_fin));
              
                $valida_permiso=Permiso::where('id_funcionario', $actualiza_permiso->id_funcionario)
                ->where('estado','A')
                ->where(function($c)use($fec_ini,$fec_fin) {
                    $c->whereBetween('fecha_hora_ini',[$fec_ini, $fec_fin])
                    ->orwhereBetween('fecha_hora_fin', [$fec_ini, $fec_fin]);
                })
                ->where('id_permiso','!=', $id)
                ->first();

                if(!is_null($valida_permiso)){
                    return response()->json([
                        'error'=>true,
                        'mensaje'=>'El funcionario ya tiene asignado un permiso en el rango de fecha seleccionado'
                    ]);
                }

            }
            
            if($actualiza_permiso->save()){
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
            Log::error('PermisoController => actualizar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }

    public function eliminar($id){
        try{
            $elimina=Permiso::find($id);
            $elimina->id_usuario_act=auth()->user()->id;
            $elimina->fecha_act=date('Y-m-d H:i:s');
            $elimina->estado="I";
            if($elimina->save()){
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
            Log::error('PermisoController => eliminar => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }
    
}
