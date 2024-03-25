<?php

namespace App\Http\Controllers\Personal;
use App\Http\Controllers\Controller;
use \Log;
use Illuminate\Http\Request;
use DB;
use PDF;
use SplFileInfo;
use Storage;

class ReportePermisoController extends Controller
{
    public function vistaAreaReporte(){
        return view('gestion_reporte.vista_area');
    }

    public function vistaAreaIndividual(){
        $areas=DB::table('per_area')
        ->where('estado','A')->get();
        return view('gestion_reporte.vista_area_indiv',[
            "areas"=>$areas
        ]);
    }

    public function reporteAreaIndivFechas($ini, $fin, $area){
        try{
           
            $permisos_areas_ind=DB::table('per_permiso as p')
            ->leftJoin('per_funcionario as f', 'f.id_funcionario','p.id_funcionario')
            ->leftJoin('per_area as a', 'a.id_area','f.id_area')
            ->leftJoin('per_tipo_permiso as tp', 'tp.id_tipo_permiso','p.id_tipo_permiso') 
            ->leftJoin('per_justificacion as j', 'j.id_justificacion','p.id_justificacion')  
            ->where(function($query)use($ini, $fin){
                $query->whereBetween('p.fecha_ini',[$ini, $fin])
                ->orwhereBetween('p.fecha_fin', [$ini, $fin]);
            })
            ->where('f.id_area',$area)
           
            ->select(DB::raw("CONCAT(f.apellidos,' ', f.nombres) AS funcionario"),DB::raw(' a.id_area,count(a.id_area) as cant,j.descripcion as just'))
            ->groupBy('funcionario','a.id_area','j.descripcion')
            ->where('p.estado','!=','I')
            ->get();

            $area=DB::table('per_area')
            ->where('id_area', $area)
            ->select('descripcion')
            ->first();
          

            if(sizeof($permisos_areas_ind)<=0){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'No existen información con los datos enviados'
                ]);
            }

            $agrupadoFunc=[];
            foreach ($permisos_areas_ind as $key => $item){
                               
                if(!isset($agrupadoFunc[$item->funcionario])) {
                    $agrupadoFunc[$item->funcionario]=array($item);
                }else{
                    array_push($agrupadoFunc[$item->funcionario], $item);
                }
            }

           
            
            $tipo_just=DB::table('per_justificacion')
            ->where('estado','A')
            ->get();
           
            $nombrePDF="reporte_area_".$area->descripcion.".pdf";

            $pdf=PDF::LoadView('gestion_reporte.reporte_area_ind_pdf',['datos'=>$agrupadoFunc,'desde'=>$ini, "hasta"=>$fin, "tipo_just"=>$tipo_just, "area"=>$area]);
            $pdf->setPaper("A4", "landscape");

            $estadoarch = $pdf->stream();

            //lo guardamos en el disco temporal
            Storage::disk('public')->put(str_replace("", "",$nombrePDF), $estadoarch);
            $exists_destino = Storage::disk('public')->exists($nombrePDF); 
            if($exists_destino){ 
                return response()->json([
                    'error'=>false,
                    'pdf'=>$nombrePDF
                ]);
            }else{
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'No se pudo crear el documento'
                ]);
            }
            

        }catch (\Throwable $e) {
            Log::error('ReportePermisoController => reporteAreaIndivFechas => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }

    public function reporteAreaFechas($ini, $fin){
        try{
            $permisos_areas=DB::table('per_permiso as p')
            ->leftJoin('per_funcionario as f', 'f.id_funcionario','p.id_funcionario')
            ->leftJoin('per_area as a', 'a.id_area','f.id_area')
            ->leftJoin('per_tipo_permiso as tp', 'tp.id_tipo_permiso','p.id_tipo_permiso') 
            ->leftJoin('per_justificacion as j', 'j.id_justificacion','p.id_justificacion')  
            ->where(function($query)use($ini, $fin){
                $query->whereBetween('p.fecha_ini',[$ini, $fin])
                ->orwhereBetween('p.fecha_fin', [$ini, $fin]);
            })
            ->select(DB::raw('a.descripcion as area,a.id_area, count(a.id_area) as cant,j.descripcion as just' ))
            ->groupBy('j.descripcion','a.id_area','a.descripcion')
            ->where('p.estado','!=','I')
            ->get();

            if(sizeof($permisos_areas)<=0){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'No existen información con los datos enviados'
                ]);
            }

            $agrupadoArea=[];
            foreach ($permisos_areas as $key => $item){
                               
                if(!isset($agrupadoArea[$item->area])) {
                    $agrupadoArea[$item->area]=array($item);
                }else{
                    array_push($agrupadoArea[$item->area], $item);
                }
            }
            
            $tipo_just=DB::table('per_justificacion')
            ->where('estado','A')
            ->get();
           
            $nombrePDF="reporte_areas_global.pdf";

            $pdf=PDF::LoadView('gestion_reporte.reporte_area_pdf',['datos'=>$agrupadoArea,'desde'=>$ini, "hasta"=>$fin, "tipo_just"=>$tipo_just]);
            $pdf->setPaper("A4", "landscape");

            $estadoarch = $pdf->stream();

            //lo guardamos en el disco temporal
            Storage::disk('public')->put(str_replace("", "",$nombrePDF), $estadoarch);
            $exists_destino = Storage::disk('public')->exists($nombrePDF); 
            if($exists_destino){ 
                return response()->json([
                    'error'=>false,
                    'pdf'=>$nombrePDF
                ]);
            }else{
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'No se pudo crear el documento'
                ]);
            }
            

        }catch (\Throwable $e) {
            Log::error('ReportePermisoController => reporteAreaFechas => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }

    public function vistaDetalladoReporte(){
        return view('gestion_reporte.vista_detalllado');
    }

    public function reporteDetalladoFechas($ini, $fin){
        try{
           
            $permisos_detallado=DB::table('per_permiso as p')
            ->leftJoin('per_funcionario as f', 'f.id_funcionario','p.id_funcionario')
            ->leftJoin('per_area as a', 'a.id_area','f.id_area')
            ->leftJoin('per_tipo_permiso as tp', 'tp.id_tipo_permiso','p.id_tipo_permiso') 
            ->leftJoin('per_justificacion as j', 'j.id_justificacion','p.id_justificacion')  
            ->where(function($query)use($ini, $fin){
                $query->whereBetween('p.fecha_ini',[$ini, $fin])
                ->orwhereBetween('p.fecha_fin', [$ini, $fin]);
            })
            ->select('p.fecha_ini','p.fecha_fin', 'p.cant_dia','p.cant_hora', 'p.fecha_hora_ini','p.fecha_hora_fin', 'tp.descripcion as tipo',DB::raw("CONCAT(f.apellidos,' ', f.nombres) AS funcionario"),'f.cedula','a.descripcion as area_de', 'p.observacion'
            ,'j.descripcion as just','j.id_justificacion','p.id_permiso')
            ->where('p.estado','!=','I')
            ->get();

                       
            $separa_hora_min=0;
            $suma_hora=0;
            $suma_minutos=0;
            $cant_dias=0;
            $array_id_permiso=[];

            $cant_iess=0;
            $cant_iess_hora=0;
            $iess_separa_hora_min=0;
            $iess_suma_hora=0;
            $iess_suma_minutos=0;


            $cant_cm=0;
            $cant_cm_hora=0;
            $cm_separa_hora_min=0;
            $cm_suma_hora=0;
            $cm_suma_minutos=0;

            $cant_calamidad=0;
            $cant_calamidad_hora=0;
            $calamidad_separa_hora_min=0;
            $calamidad_suma_hora=0;
            $calamidad_suma_minutos=0;

            $cant_asunto=0;
            $cant_asunto_hora=0;
            $asunto_separa_hora_min=0;
            $asunto_suma_hora=0;
            $asunto_suma_minutos=0;

            $cant_otro=0;
            $cant_otro_hora=0;
            $otro_separa_hora_min=0;
            $otro_suma_hora=0;
            $otro_suma_minutos=0;
            
            foreach($permisos_detallado as $key =>$data){
               
                //solo tomamos en cuenta los valores del mes (cantidad hora dia)

                if($data->fecha_fin>$fin && !is_null($data->cant_dia)){
            
                   
                    $date_ini=new \DateTime($data->fecha_ini);
                    $date_fin=new \DateTime($fin);
                    $diff_ = $date_ini->diff($date_fin);
                    $dias_diferencia_=$diff_->days;
                    $dias_diferencia_=$dias_diferencia_+1;

                    $suma_hora=$suma_hora+($dias_diferencia_ * 8);

                    if($data->id_justificacion==2){
                        
                        $cant_calamidad=$cant_calamidad+1;
                        $calamidad_suma_hora=$calamidad_suma_hora+$suma_hora;
                                            
                    }

                    //iess
                    else if($data->id_justificacion==1 || $data->id_justificacion==6){
                        $cant_iess=$cant_iess+1;
                        $iess_suma_hora=$iess_suma_hora+$suma_hora;
                     
                    }

                    //certificado medico
                    else if($data->id_justificacion==4){
                        $cant_cm=$cant_cm+1;
                        $cm_suma_hora=$cm_suma_hora+$suma_hora;
                      
                    }
                     
                }
                else if($data->fecha_ini<$ini && !is_null($data->cant_dia)){
                 
                    $date_ini=new \DateTime($ini);
                    $date_fin=new \DateTime($data->fecha_fin);
                    $diff_ = $date_ini->diff($date_fin);
                    $dias_diferencia_=$diff_->days;
                    $dias_diferencia_=$dias_diferencia_+1;

                    $suma_hora=$suma_hora+($dias_diferencia_ * 8);
                   

                    if($data->id_justificacion==2){
                        $cant_calamidad=$cant_calamidad+1;
                        $calamidad_suma_hora=$calamidad_suma_hora+$suma_hora;
                       
                    }

                    //iess
                    else if($data->id_justificacion==1 || $data->id_justificacion==6){
                        $cant_iess=$cant_iess+1;
                        $iess_suma_hora=$iess_suma_hora+$suma_hora;
                     
                    }

                    //certificado medico
                    else if($data->id_justificacion==4){
                        $cant_cm=$cant_cm+1;
                        $cm_suma_hora=$cm_suma_hora+$suma_hora;
                      
                    }
                }
                else{
                    array_push($array_id_permiso, $data->id_permiso);
                    $separa_hora_min=explode(":", $data->cant_hora);
                    $suma_hora=$suma_hora+$separa_hora_min[0];
                    $suma_minutos=$suma_minutos+$separa_hora_min[1];

                    //iess
                    if($data->id_justificacion==1 || $data->id_justificacion==6){
                        $cant_iess=$cant_iess+1;

                        $iess_separa_hora_min=explode(":", $data->cant_hora);
                        $iess_suma_hora=$iess_suma_hora+$iess_separa_hora_min[0];
                        $iess_suma_minutos=$iess_suma_minutos+$iess_separa_hora_min[1];
                    }

                    //certificado medico
                    else if($data->id_justificacion==4){
                        $cant_cm=$cant_cm+1;

                        $cm_separa_hora_min=explode(":", $data->cant_hora);
                        $cm_suma_hora=$cm_suma_hora+$cm_separa_hora_min[0];
                        $cm_suma_minutos=$cm_suma_minutos+$cm_separa_hora_min[1];
                    }

                    //caslamidad
                    else if($data->id_justificacion==2){
                        $cant_calamidad=$cant_calamidad+1;

                        $calamidad_separa_hora_min=explode(":", $data->cant_hora);
                        $calamidad_suma_hora=$calamidad_suma_hora+$calamidad_separa_hora_min[0];
                        $calamidad_suma_minutos=$calamidad_suma_minutos+$calamidad_separa_hora_min[1];
                    }

                    //asuntos personales
                    else if($data->id_justificacion==3){
                        $cant_asunto=$cant_asunto+1;

                        $asunto_separa_hora_min=explode(":", $data->cant_hora);
                        $asunto_suma_hora=$asunto_suma_hora+$asunto_separa_hora_min[0];
                        $asunto_suma_minutos=$asunto_suma_minutos+$asunto_separa_hora_min[1];
                    }

                    //otros 
                    else{
                        $cant_otro=$cant_otro+1;

                        $otro_separa_hora_min=explode(":", $data->cant_hora);
                        $otro_suma_hora=$otro_suma_hora+$otro_separa_hora_min[0];
                        $otro_suma_minutos=$otro_suma_minutos+$otro_separa_hora_min[1];
                    }
                }
                

            }

            $permisos_x_func=DB::table('per_permiso as p')
            ->whereIn('id_permiso',$array_id_permiso)
            ->select('id_funcionario')
            ->distinct('id_funcionario')
            ->get();
           
            if($suma_minutos>=60){
                $suma_hora_aux=$suma_minutos/60;
                if($suma_minutos%60!=0){
                    $separa_hora_min=explode(".",$suma_hora_aux);                
                    $suma_hora=$suma_hora+$separa_hora_min[0];
                    $suma_minutos=$separa_hora_min[1];
                }else{
                    $suma_hora=$suma_hora+$suma_hora_aux;
                    $suma_minutos=0;
                }
                   
            }

            if($iess_suma_minutos>=60){
                $iess_suma_hora_aux=$iess_suma_minutos/60;
                if($iess_suma_minutos%60!=0){
                    $iess_separa_hora_min=explode(".",$iess_suma_hora_aux);  

                    $iess_suma_hora=$iess_suma_hora+$iess_separa_hora_min[0];
                    $iess_suma_minutos=$iess_separa_hora_min[1];
                }else{
                    $iess_suma_hora=$iess_suma_hora+$iess_suma_hora_aux;
                    $iess_suma_minutos=0;
                }
                   
            }

            if($iess_suma_hora>=8){
                $cant_dias_iess= $iess_suma_hora/8;
                $cant_dias_iess=explode(".",$cant_dias_iess);
                $cant_dias_iess=$cant_dias_iess[0];
            }else{
                $cant_dias_iess=0;
            }
                

            $data_iess=["cantidad_iess"=>$cant_iess,"iess_suma_hora"=>$iess_suma_hora,"iess_suma_minutos"=>$iess_suma_minutos,"cant_dias_iess"=>$cant_dias_iess];

            // cert medico
            if($cm_suma_minutos>=60){
                $cm_suma_hora_aux=$cm_suma_minutos/60;
                if($cm_suma_minutos%60!=0){
                    $iess_separa_hora_min=explode(".",$cm_suma_hora_aux);  

                    $cm_suma_hora=$cm_suma_hora+$iess_separa_hora_min[0];
                    $cm_suma_minutos=$iess_separa_hora_min[1];
                }else{
                    $cm_suma_hora=$cm_suma_hora+$cm_suma_hora_aux;
                    $cm_suma_minutos=0;
                }
                   
            }

            if($cm_suma_hora>=8){
                $cant_dias_cm= $cm_suma_hora/8;
                $cant_dias_cm=explode(".",$cant_dias_cm);
                $cant_dias_cm=$cant_dias_cm[0];
            }else{
                $cant_dias_cm=0;
            }
                

            $data_cert_med=["cantidad_cm"=>$cant_cm,"cm_suma_hora"=>$cm_suma_hora,"cm_suma_minutos"=>$cm_suma_minutos,"cant_dias_cm"=>$cant_dias_cm];


            //calamidad
            if($calamidad_suma_minutos>=60){
                $calam_suma_hora_aux=$calamidad_suma_minutos/60;
                if($calamidad_suma_minutos%60!=0){
                    $cala_separa_hora_min=explode(".",$calam_suma_hora_aux);  

                    $calamidad_suma_hora=$calamidad_suma_hora+$cala_separa_hora_min[0];
                    $calamidad_suma_minutos=$cala_separa_hora_min[1];
                }else{
                    $calamidad_suma_hora=$calamidad_suma_hora+$calam_suma_hora_aux;
                    $calamidad_suma_minutos=0;
                }
                   
            }

            if($calamidad_suma_hora>=8){
                $cant_dias_cala= $calamidad_suma_hora/8;
                $cant_dias_cala=explode(".",$cant_dias_cala);
                $cant_dias_cala=$cant_dias_cala[0];
            }else{
                $cant_dias_cala=0;
            }
                

            $data_calamidad=["cant_calamidad"=>$cant_calamidad,"calamidad_suma_hora"=>$calamidad_suma_hora,"calamidad_suma_minutos"=>$calamidad_suma_minutos,"cant_dias_cala"=>$cant_dias_cala];


            // //asunto
            if($asunto_suma_minutos>=60){
                $asunto_suma_hora_aux=$asunto_suma_minutos/60;
                if($asunto_suma_minutos%60!=0){
                    $asunto_separa_hora_min=explode(".",$asunto_suma_hora_aux);  

                    $asunto_suma_hora=$asunto_suma_hora+$asunto_separa_hora_min[0];
                    $asunto_suma_minutos=$asunto_separa_hora_min[1];
                }else{
                    $asunto_suma_hora=$asunto_suma_hora+$asunto_suma_hora_aux;
                    $asunto_suma_minutos=0;
                }
                   
            }

            if($asunto_suma_hora>=8){
                $cant_dias_asunto= $asunto_suma_hora/8;
                $cant_dias_asunto=explode(".",$cant_dias_asunto);
                $cant_dias_asunto=$cant_dias_asunto[0];
            }else{
                $cant_dias_asunto=0;
            }
                

            $data_asunto=["cant_asunto"=>$cant_asunto,"asunto_suma_hora"=>$asunto_suma_hora,"asunto_suma_minutos"=>$asunto_suma_minutos,"cant_dias_asunto"=>$cant_dias_asunto];


             //otro
             if($otro_suma_minutos>=60){
                $otro_suma_hora_aux=$otro_suma_minutos/60;
                if($otro_suma_minutos%60!=0){
                    $otro_separa_hora_min=explode(".",$otro_suma_hora_aux);  

                    $otro_suma_hora=$otro_suma_hora+$otro_separa_hora_min[0];
                    $otro_suma_minutos=$otro_separa_hora_min[1];
                }else{
                    $otro_suma_hora=$otro_suma_hora+$otro_suma_hora_aux;
                    $otro_suma_minutos=0;
                }
                   
            }

            if($otro_suma_hora>=8){
                $cant_dias_otro= $otro_suma_hora/8;
                $cant_dias_otro=explode(".",$cant_dias_otro);
                $cant_dias_otro=$cant_dias_otro[0];
            }else{
                $cant_dias_otro=0;
            }
                

            $data_otro=["cant_otro"=>$cant_otro,"otro_suma_hora"=>$otro_suma_hora,"otro_suma_minutos"=>$otro_suma_minutos,"cant_dias_otro"=>$cant_dias_otro];

        
            if(sizeof($permisos_detallado)<=0){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'No existen información con los datos enviados'
                ]);
            }

            $empleados=DB::table('per_funcionario')
            ->where('estado','A')
            ->get();
            $total_empleado=sizeof($empleados);

            $cant_losep_planta=0;
            $cant_losep_contrato=0;

            $cant_codigo_planta=0;
            $cant_codigo_contrato=0;

            $cant_otro_planta=0;
            $cant_otro_contrato=0;
            foreach($empleados as $data_emp){
                //losep planta
                if($data_emp->id_ambito_ley==1 && $data_emp->id_ambito==1){
                    $cant_losep_planta=$cant_losep_planta+1;
                }
                //losep contrato
                if($data_emp->id_ambito_ley==1 && $data_emp->id_ambito==2){
                    $cant_losep_contrato=$cant_losep_contrato+1;
                }

                //codigo planta
                if($data_emp->id_ambito_ley==2 && $data_emp->id_ambito==1){
                    $cant_codigo_planta=$cant_codigo_planta+1;
                }

                //codigo contrato
                if($data_emp->id_ambito_ley==2 && $data_emp->id_ambito==2){
                    $cant_codigo_contrato=$cant_codigo_contrato+1;
                }

                //otro planta
                if($data_emp->id_ambito_ley==3 && $data_emp->id_ambito==1){
                    $cant_otro_planta=$cant_otro_planta+1;
                }

                //otro contato
                if($data_emp->id_ambito_ley==3 && $data_emp->id_ambito==2){
                    $cant_otro_contrato=$cant_otro_contrato+1;
                }
            }

            $data_empleado=["total_empleados"=>$total_empleado, "cant_losep_planta"=>$cant_losep_planta, "cant_losep_contrato"=>$cant_losep_contrato, "cant_codigo_planta"=>$cant_codigo_planta, "cant_codigo_contrato"=>$cant_codigo_contrato,
            "cant_otro_planta"=>$cant_otro_planta, "cant_otro_contrato"=>$cant_otro_contrato];

            if($suma_hora>=8){
                $cant_dias= $suma_hora/8;
                $cant_dias=explode(".",$cant_dias);
                $cant_dias=$cant_dias[0];
            }else{
                $cant_dias=0;
            }

            $date1=new \DateTime($ini);
            $date2=new \DateTime($fin);
            $diff = $date1->diff($date2);
            $dias_diferencia=$diff->days;
            $dias_diferencia=$dias_diferencia+1;
            $cantidad_hora_planificada=$dias_diferencia * 8;
         
            $nombrePDF="reporte_detallado.pdf";

            $pdf=PDF::LoadView('gestion_reporte.reporte_detallado_pdf',['datos'=>$permisos_detallado,'desde'=>$ini, "hasta"=>$fin, "suma_hora"=>$suma_hora, "suma_minutos"=>$suma_minutos, "cant_dias"=>$cant_dias, "data_iess"=>$data_iess, "data_cert_med"=>$data_cert_med, "data_calamidad"=>$data_calamidad, "data_asunto"=>$data_asunto, "data_otro"=>$data_otro,"data_empleado"=>$data_empleado, "permisos_x_func"=>$permisos_x_func, "cantidad_hora_planificada"=>$cantidad_hora_planificada]);
            $pdf->setPaper("A4", "landscape");

            $estadoarch = $pdf->stream();

            //lo guardamos en el disco temporal
            Storage::disk('public')->put(str_replace("", "",$nombrePDF), $estadoarch);
            $exists_destino = Storage::disk('public')->exists($nombrePDF); 
            if($exists_destino){ 
                return response()->json([
                    'error'=>false,
                    'pdf'=>$nombrePDF
                ]);
            }else{
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'No se pudo crear el documento'
                ]);
            }
            

        }catch (\Throwable $e) {
            Log::error('ReportePermisoController => reporteDetalladoFechas => mensaje => '.$e->getMessage().' linea => '.$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }


    public function vistafuncionarioReporte(){
        return view('gestion_reporte.vista_funcionario');
    }

    public function reporteFuncionarioFechas($ini, $fin, $idfunc){
        try{
            $permisos_funcionario=DB::table('per_permiso as p')
            ->leftJoin('per_funcionario as f', 'f.id_funcionario','p.id_funcionario')
            ->leftJoin('per_area as a', 'a.id_area','f.id_area')
            ->leftJoin('per_tipo_permiso as tp', 'tp.id_tipo_permiso','p.id_tipo_permiso') 
            ->leftJoin('per_justificacion as j', 'j.id_justificacion','p.id_justificacion')  
            ->where(function($query)use($ini, $fin){
                $query->whereBetween('p.fecha_ini',[$ini, $fin])
                ->orwhereBetween('p.fecha_fin', [$ini, $fin]);
            })
            ->select('p.fecha_ini','p.fecha_fin', 'p.cant_dia','p.cant_hora', 'p.fecha_hora_ini','p.fecha_hora_fin', 'tp.descripcion as tipo',DB::raw("CONCAT(f.apellidos,' ', f.nombres) AS funcionario"),'f.cedula','a.descripcion as area_de', 'p.observacion'
            ,'j.descripcion as just','j.id_justificacion','p.id_permiso')
            ->where('p.estado','!=','I')
            ->where('p.id_funcionario', $idfunc)
            ->get();

                       
            $separa_hora_min=0;
            $suma_hora=0;
            $suma_minutos=0;
            $cant_dias=0;
            $array_id_permiso=[];

            $cant_iess=0;
            $cant_iess_hora=0;
            $iess_separa_hora_min=0;
            $iess_suma_hora=0;
            $iess_suma_minutos=0;


            $cant_cm=0;
            $cant_cm_hora=0;
            $cm_separa_hora_min=0;
            $cm_suma_hora=0;
            $cm_suma_minutos=0;

            $cant_calamidad=0;
            $cant_calamidad_hora=0;
            $calamidad_separa_hora_min=0;
            $calamidad_suma_hora=0;
            $calamidad_suma_minutos=0;

            $cant_asunto=0;
            $cant_asunto_hora=0;
            $asunto_separa_hora_min=0;
            $asunto_suma_hora=0;
            $asunto_suma_minutos=0;

            $cant_otro=0;
            $cant_otro_hora=0;
            $otro_separa_hora_min=0;
            $otro_suma_hora=0;
            $otro_suma_minutos=0;
            
            foreach($permisos_funcionario as $key =>$data){

                //solo tomamos en cuenta los valores del mes (cantidad hora dia)

                if($data->fecha_fin>$fin && !is_null($data->cant_dia)){
            
                   
                    $date_ini=new \DateTime($data->fecha_ini);
                    $date_fin=new \DateTime($fin);
                    $diff_ = $date_ini->diff($date_fin);
                    $dias_diferencia_=$diff_->days;
                    $dias_diferencia_=$dias_diferencia_+1;

                    $suma_hora=$suma_hora+($dias_diferencia_ * 8);

                    if($data->id_justificacion==2){
                        
                        $cant_calamidad=$cant_calamidad+1;
                        $calamidad_suma_hora=$calamidad_suma_hora+$suma_hora;
                                            
                    }

                    //iess
                    else if($data->id_justificacion==1 || $data->id_justificacion==6){
                        $cant_iess=$cant_iess+1;
                        $iess_suma_hora=$iess_suma_hora+$suma_hora;
                     
                    }

                    //certificado medico
                    else if($data->id_justificacion==4){
                        $cant_cm=$cant_cm+1;
                        $cm_suma_hora=$cm_suma_hora+$suma_hora;
                      
                    }
                     
                }
                else if($data->fecha_ini<$ini && !is_null($data->cant_dia)){
                 
                    $date_ini=new \DateTime($ini);
                    $date_fin=new \DateTime($data->fecha_fin);
                    $diff_ = $date_ini->diff($date_fin);
                    $dias_diferencia_=$diff_->days;
                    $dias_diferencia_=$dias_diferencia_+1;

                    $suma_hora=$suma_hora+($dias_diferencia_ * 8);
                   

                    if($data->id_justificacion==2){
                        $cant_calamidad=$cant_calamidad+1;
                        $calamidad_suma_hora=$calamidad_suma_hora+$suma_hora;
                       
                    }

                    //iess
                    else if($data->id_justificacion==1 || $data->id_justificacion==6){
                        $cant_iess=$cant_iess+1;
                        $iess_suma_hora=$iess_suma_hora+$suma_hora;
                     
                    }

                    //certificado medico
                    else if($data->id_justificacion==4){
                        $cant_cm=$cant_cm+1;
                        $cm_suma_hora=$cm_suma_hora+$suma_hora;
                      
                    }
                }
                else{
               
                    array_push($array_id_permiso, $data->id_permiso);
                    $separa_hora_min=explode(":", $data->cant_hora);
                    $suma_hora=$suma_hora+$separa_hora_min[0];
                    $suma_minutos=$suma_minutos+$separa_hora_min[1];

                    //iess
                    if($data->id_justificacion==1 || $data->id_justificacion==6){
                        $cant_iess=$cant_iess+1;

                        $iess_separa_hora_min=explode(":", $data->cant_hora);
                        $iess_suma_hora=$iess_suma_hora+$iess_separa_hora_min[0];
                        $iess_suma_minutos=$iess_suma_minutos+$iess_separa_hora_min[1];
                    }

                    //certificado medico
                    else if($data->id_justificacion==4){
                        $cant_cm=$cant_cm+1;

                        $cm_separa_hora_min=explode(":", $data->cant_hora);
                        $cm_suma_hora=$cm_suma_hora+$cm_separa_hora_min[0];
                        $cm_suma_minutos=$cm_suma_minutos+$cm_separa_hora_min[1];
                    }

                    //caslamidad
                    else if($data->id_justificacion==2){
                        $cant_calamidad=$cant_calamidad+1;

                        $calamidad_separa_hora_min=explode(":", $data->cant_hora);
                        $calamidad_suma_hora=$calamidad_suma_hora+$calamidad_separa_hora_min[0];
                        $calamidad_suma_minutos=$calamidad_suma_minutos+$calamidad_separa_hora_min[1];
                    }

                    //asuntos personales
                    else if($data->id_justificacion==3){
                        $cant_asunto=$cant_asunto+1;

                        $asunto_separa_hora_min=explode(":", $data->cant_hora);
                        $asunto_suma_hora=$asunto_suma_hora+$asunto_separa_hora_min[0];
                        $asunto_suma_minutos=$asunto_suma_minutos+$asunto_separa_hora_min[1];
                    }

                    //otros 
                    else{
                        $cant_otro=$cant_otro+1;

                        $otro_separa_hora_min=explode(":", $data->cant_hora);
                        $otro_suma_hora=$otro_suma_hora+$otro_separa_hora_min[0];
                        $otro_suma_minutos=$otro_suma_minutos+$otro_separa_hora_min[1];
                    }
                }

            }

            $permisos_x_func=DB::table('per_permiso as p')
            ->whereIn('id_permiso',$array_id_permiso)
            ->select('id_funcionario')
            ->distinct('id_funcionario')
            ->get();
            
            if($suma_minutos>=60){
                $suma_hora_aux=$suma_minutos/60;
                if($suma_minutos%60!=0){
                    $separa_hora_min=explode(".",$suma_hora_aux);                
                    $suma_hora=$suma_hora+$separa_hora_min[0];
                    $suma_minutos=$separa_hora_min[1];
                }else{
                    $suma_hora=$suma_hora+$suma_hora_aux;
                    $suma_minutos=0;
                }
                   
            }

            if($iess_suma_minutos>=60){
                $iess_suma_hora_aux=$iess_suma_minutos/60;
                if($iess_suma_minutos%60!=0){
                    $iess_separa_hora_min=explode(".",$iess_suma_hora_aux);  

                    $iess_suma_hora=$iess_suma_hora+$iess_separa_hora_min[0];
                    $iess_suma_minutos=$iess_separa_hora_min[1];
                }else{
                    $iess_suma_hora=$iess_suma_hora+$iess_suma_hora_aux;
                    $iess_suma_minutos=0;
                }
                   
            }

            if($iess_suma_hora>=8){
                $cant_dias_iess= $iess_suma_hora/8;
                $cant_dias_iess=explode(".",$cant_dias_iess);
                $cant_dias_iess=$cant_dias_iess[0];
            }else{
                $cant_dias_iess=0;
            }
                

            $data_iess=["cantidad_iess"=>$cant_iess,"iess_suma_hora"=>$iess_suma_hora,"iess_suma_minutos"=>$iess_suma_minutos,"cant_dias_iess"=>$cant_dias_iess];

            // cert medico
            if($cm_suma_minutos>=60){
                $cm_suma_hora_aux=$cm_suma_minutos/60;
                if($cm_suma_minutos%60!=0){
                    $iess_separa_hora_min=explode(".",$cm_suma_hora_aux);  

                    $cm_suma_hora=$cm_suma_hora+$iess_separa_hora_min[0];
                    $cm_suma_minutos=$iess_separa_hora_min[1];
                }else{
                    $cm_suma_hora=$cm_suma_hora+$cm_suma_hora_aux;
                    $cm_suma_minutos=0;
                }
                   
            }

            if($cm_suma_hora>=8){
                $cant_dias_cm= $cm_suma_hora/8;
                $cant_dias_cm=explode(".",$cant_dias_cm);
                $cant_dias_cm=$cant_dias_cm[0];
            }else{
                $cant_dias_cm=0;
            }
                

            $data_cert_med=["cantidad_cm"=>$cant_cm,"cm_suma_hora"=>$cm_suma_hora,"cm_suma_minutos"=>$cm_suma_minutos,"cant_dias_cm"=>$cant_dias_cm];


            //calamidad
            if($calamidad_suma_minutos>=60){
                $calam_suma_hora_aux=$calamidad_suma_minutos/60;
                if($calamidad_suma_minutos%60!=0){
                    $cala_separa_hora_min=explode(".",$calam_suma_hora_aux);  

                    $calamidad_suma_hora=$calamidad_suma_hora+$cala_separa_hora_min[0];
                    $calamidad_suma_minutos=$cala_separa_hora_min[1];
                }else{
                    $calamidad_suma_hora=$calamidad_suma_hora+$calam_suma_hora_aux;
                    $calamidad_suma_minutos=0;
                }
                   
            }

            if($calamidad_suma_hora>=8){
                $cant_dias_cala= $calamidad_suma_hora/8;
                $cant_dias_cala=explode(".",$cant_dias_cala);
                $cant_dias_cala=$cant_dias_cala[0];
            }else{
                $cant_dias_cala=0;
            }
                

            $data_calamidad=["cant_calamidad"=>$cant_calamidad,"calamidad_suma_hora"=>$calamidad_suma_hora,"calamidad_suma_minutos"=>$calamidad_suma_minutos,"cant_dias_cala"=>$cant_dias_cala];


            // //asunto
            if($asunto_suma_minutos>=60){
                $asunto_suma_hora_aux=$asunto_suma_minutos/60;
                if($asunto_suma_minutos%60!=0){
                    $asunto_separa_hora_min=explode(".",$asunto_suma_hora_aux);  

                    $asunto_suma_hora=$asunto_suma_hora+$asunto_separa_hora_min[0];
                    $asunto_suma_minutos=$asunto_separa_hora_min[1];
                }else{
                    $asunto_suma_hora=$asunto_suma_hora+$asunto_suma_hora_aux;
                    $asunto_suma_minutos=0;
                }
                   
            }

            if($asunto_suma_hora>=8){
                $cant_dias_asunto= $asunto_suma_hora/8;
                $cant_dias_asunto=explode(".",$cant_dias_asunto);
                $cant_dias_asunto=$cant_dias_asunto[0];
            }else{
                $cant_dias_asunto=0;
            }
                

            $data_asunto=["cant_asunto"=>$cant_asunto,"asunto_suma_hora"=>$asunto_suma_hora,"asunto_suma_minutos"=>$asunto_suma_minutos,"cant_dias_asunto"=>$cant_dias_asunto];


             //otro
             if($otro_suma_minutos>=60){
                $otro_suma_hora_aux=$otro_suma_minutos/60;
                if($otro_suma_minutos%60!=0){
                    $otro_separa_hora_min=explode(".",$otro_suma_hora_aux);  

                    $otro_suma_hora=$otro_suma_hora+$otro_separa_hora_min[0];
                    $otro_suma_minutos=$otro_separa_hora_min[1];
                }else{
                    $otro_suma_hora=$otro_suma_hora+$otro_suma_hora_aux;
                    $otro_suma_minutos=0;
                }
                   
            }

            if($otro_suma_hora>=8){
                $cant_dias_otro= $otro_suma_hora/8;
                $cant_dias_otro=explode(".",$cant_dias_otro);
                $cant_dias_otro=$cant_dias_otro[0];
            }else{
                $cant_dias_otro=0;
            }
                

            $data_otro=["cant_otro"=>$cant_otro,"otro_suma_hora"=>$otro_suma_hora,"otro_suma_minutos"=>$otro_suma_minutos,"cant_dias_otro"=>$cant_dias_otro];

        
            if(sizeof($permisos_funcionario)<=0){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'No existen información con los datos enviados'
                ]);
            }

            $empleados=DB::table('per_funcionario')
            ->where('estado','A')
            ->get();
            $total_empleado=sizeof($empleados);

            $cant_losep_planta=0;
            $cant_losep_contrato=0;

            $cant_codigo_planta=0;
            $cant_codigo_contrato=0;

            $cant_otro_planta=0;
            $cant_otro_contrato=0;
            foreach($empleados as $data_emp){
                //losep planta
                if($data_emp->id_ambito_ley==1 && $data_emp->id_ambito==1){
                    $cant_losep_planta=$cant_losep_planta+1;
                }
                //losep contrato
                if($data_emp->id_ambito_ley==1 && $data_emp->id_ambito==2){
                    $cant_losep_contrato=$cant_losep_contrato+1;
                }

                //codigo planta
                if($data_emp->id_ambito_ley==2 && $data_emp->id_ambito==1){
                    $cant_codigo_planta=$cant_codigo_planta+1;
                }

                //codigo contrato
                if($data_emp->id_ambito_ley==2 && $data_emp->id_ambito==2){
                    $cant_codigo_contrato=$cant_codigo_contrato+1;
                }

                //otro planta
                if($data_emp->id_ambito_ley==3 && $data_emp->id_ambito==1){
                    $cant_otro_planta=$cant_otro_planta+1;
                }

                //otro contato
                if($data_emp->id_ambito_ley==3 && $data_emp->id_ambito==2){
                    $cant_otro_contrato=$cant_otro_contrato+1;
                }
            }

            $data_empleado=["total_empleados"=>$total_empleado, "cant_losep_planta"=>$cant_losep_planta, "cant_losep_contrato"=>$cant_losep_contrato, "cant_codigo_planta"=>$cant_codigo_planta, "cant_codigo_contrato"=>$cant_codigo_contrato,
            "cant_otro_planta"=>$cant_otro_planta, "cant_otro_contrato"=>$cant_otro_contrato];

            if($suma_hora>=8){
                $cant_dias= $suma_hora/8;
                $cant_dias=explode(".",$cant_dias);
                $cant_dias=$cant_dias[0];
            }else{
                $cant_dias=0;
            }

            $date1=new \DateTime($ini);
            $date2=new \DateTime($fin);
            $diff = $date1->diff($date2);
            $dias_diferencia=$diff->days;
            $dias_diferencia=$dias_diferencia+1;
            $cantidad_hora_planificada=$dias_diferencia * 8;
         
            $nombrePDF="reporte_funcionario.pdf";

            $pdf=PDF::LoadView('gestion_reporte.reporte_funcionario_pdf',['datos'=>$permisos_funcionario,'desde'=>$ini, "hasta"=>$fin, "suma_hora"=>$suma_hora, "suma_minutos"=>$suma_minutos, "cant_dias"=>$cant_dias, "data_iess"=>$data_iess, "data_cert_med"=>$data_cert_med, "data_calamidad"=>$data_calamidad, "data_asunto"=>$data_asunto, "data_otro"=>$data_otro,"data_empleado"=>$data_empleado, "permisos_x_func"=>$permisos_x_func, "cantidad_hora_planificada"=>$cantidad_hora_planificada]);
            $pdf->setPaper("A4", "landscape");

            $estadoarch = $pdf->stream();

            //lo guardamos en el disco temporal
            Storage::disk('public')->put(str_replace("", "",$nombrePDF), $estadoarch);
            $exists_destino = Storage::disk('public')->exists($nombrePDF); 
            if($exists_destino){ 
                return response()->json([
                    'error'=>false,
                    'pdf'=>$nombrePDF
                ]);
            }else{
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'No se pudo crear el documento'
                ]);
            }
            

        }catch (\Throwable $e) {
            Log::error('ReportePermisoController => reporteFuncionarioFechas => mensaje => '.$e->getMessage().' linea => '.$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }

    public function vistaGprReporte(){
        return view('gestion_reporte.vista_gpr');
    }

    public function reporteGprMes($fecha){
       
        try{
            $separar_anio_mes=explode("-",$fecha);
            $anio=$separar_anio_mes[0];
            $mes=$separar_anio_mes[1];
         
            $permisos_gpr_mes=DB::table('per_permiso as p')
            ->leftJoin('per_funcionario as f', 'f.id_funcionario','p.id_funcionario')
            ->leftJoin('per_area as a', 'a.id_area','f.id_area')
            ->leftJoin('per_tipo_permiso as tp', 'tp.id_tipo_permiso','p.id_tipo_permiso') 
            ->leftJoin('per_justificacion as j', 'j.id_justificacion','p.id_justificacion')  
            ->where(function($query)use($anio, $mes){
                $query->whereYear('p.fecha_ini', '=', $anio)
                ->whereMonth('p.fecha_ini', '=',$mes)
                ->OrwhereYear('p.fecha_fin', '=', $anio)
                ->whereMonth('p.fecha_fin', '=',$mes);
            })

            ->select('p.fecha_ini','p.fecha_fin', 'p.cant_dia','p.cant_hora', 'p.fecha_hora_ini','p.fecha_hora_fin', 'tp.descripcion as tipo',DB::raw("CONCAT(f.apellidos,' ', f.nombres) AS funcionario"),'f.cedula','a.descripcion as area_de', 'p.observacion'
            ,'j.descripcion as just','j.id_justificacion','p.id_permiso')
            ->where('p.estado','!=','I')
            ->whereIn('p.id_justificacion',[1,2,4])// iess, calamidad, cert medico
            ->get();

           
            $separa_hora_min=0;
            $suma_hora=0;
            $suma_minutos=0;
            $cant_dias=0;
            $array_id_permiso=[];

            $cant_iess=0;
            $cant_iess_hora=0;
            $iess_separa_hora_min=0;
            $iess_suma_hora=0;
            $iess_suma_minutos=0;


            $cant_cm=0;
            $cant_cm_hora=0;
            $cm_separa_hora_min=0;
            $cm_suma_hora=0;
            $cm_suma_minutos=0;

            $cant_calamidad=0;
            $cant_calamidad_hora=0;
            $calamidad_separa_hora_min=0;
            $calamidad_suma_hora=0;
            $calamidad_suma_minutos=0;

            $cant_asunto=0;
            $cant_asunto_hora=0;
            $asunto_separa_hora_min=0;
            $asunto_suma_hora=0;
            $asunto_suma_minutos=0;

            $cant_otro=0;
            $cant_otro_hora=0;
            $otro_separa_hora_min=0;
            $otro_suma_hora=0;
            $otro_suma_minutos=0;

            $diff_=0;
            $dias_diferencia_=0;
            
            foreach($permisos_gpr_mes as $key =>$data){
                //solo tomamos en cuenta los valores del mes (cantidad hora dia)
                $anio_mes_ini=explode("-",$data->fecha_ini);
                $anio_mes_ini=$anio_mes_ini[0]."-".$anio_mes_ini[1];
                
                $anio_mes_fin=explode("-",$data->fecha_fin);
                $anio_mes_fin=$anio_mes_fin[0]."-".$anio_mes_fin[1];

                if($anio_mes_ini>=$fecha && $anio_mes_fin>$fecha && !is_null($data->cant_dia)){
            
                    $ultimo_dia_mes = new \DateTime( $fecha ); 
                    $ultimo_dia_mes=$ultimo_dia_mes->format( 'Y-m-t' );
                 
                    $date_ini=new \DateTime($data->fecha_ini);
                    $date_fin=new \DateTime($ultimo_dia_mes);
                    $diff_ = $date_ini->diff($date_fin);
                    $dias_diferencia_=$diff_->days;
                    $dias_diferencia_=$dias_diferencia_+1;

                    $suma_hora=$suma_hora+($dias_diferencia_ * 8);

                    if($data->id_justificacion==2){
                        $cant_calamidad=$cant_calamidad+1;
                        $calamidad_suma_hora=$calamidad_suma_hora+$suma_hora;
                       
                    }

                    //iess
                    else if($data->id_justificacion==1 || $data->id_justificacion==6){
                        $cant_iess=$cant_iess+1;
                        $iess_suma_hora=$iess_suma_hora+$suma_hora;
                     
                    }

                    //certificado medico
                    else if($data->id_justificacion==4){
                        $cant_cm=$cant_cm+1;
                        $cm_suma_hora=$cm_suma_hora+$suma_hora;
                      
                    }
                     
                }
                else if($anio_mes_fin<=$fecha && $anio_mes_ini<$fecha && !is_null($data->cant_dia)){

                    $primer_dia_mes = new \DateTime( $fecha ); 
                    $primer_dia_mes=$primer_dia_mes->format( 'Y-m-01' );
                 
                    $date_ini=new \DateTime($primer_dia_mes);
                    $date_fin=new \DateTime($data->fecha_fin);
                    $diff_ = $date_ini->diff($date_fin);
                    $dias_diferencia_=$diff_->days;
                    $dias_diferencia_=$dias_diferencia_+1;

                    $suma_hora=$suma_hora+($dias_diferencia_ * 8);

                    if($data->id_justificacion==2){
                        $cant_calamidad=$cant_calamidad+1;
                        $calamidad_suma_hora=$calamidad_suma_hora+$suma_hora;
                       
                    }

                    //iess
                    else if($data->id_justificacion==1 || $data->id_justificacion==6){
                        $cant_iess=$cant_iess+1;
                        $iess_suma_hora=$iess_suma_hora+$suma_hora;
                     
                    }

                    //certificado medico
                    else if($data->id_justificacion==4){
                        $cant_cm=$cant_cm+1;
                        $cm_suma_hora=$cm_suma_hora+$suma_hora;
                      
                    }
                }
                else{
                    array_push($array_id_permiso, $data->id_permiso);
                    $separa_hora_min=explode(":", $data->cant_hora);
                    $suma_hora=$suma_hora+$separa_hora_min[0];
                    $suma_minutos=$suma_minutos+$separa_hora_min[1];

                    //iess
                    if($data->id_justificacion==1 || $data->id_justificacion==6){
                        $cant_iess=$cant_iess+1;

                        $iess_separa_hora_min=explode(":", $data->cant_hora);
                        $iess_suma_hora=$iess_suma_hora+$iess_separa_hora_min[0];
                        $iess_suma_minutos=$iess_suma_minutos+$iess_separa_hora_min[1];
                    }

                    //certificado medico
                    else if($data->id_justificacion==4){
                        $cant_cm=$cant_cm+1;

                        $cm_separa_hora_min=explode(":", $data->cant_hora);
                        $cm_suma_hora=$cm_suma_hora+$cm_separa_hora_min[0];
                        $cm_suma_minutos=$cm_suma_minutos+$cm_separa_hora_min[1];
                    }

                    //caslamidad
                    else if($data->id_justificacion==2){
                        $cant_calamidad=$cant_calamidad+1;

                        $calamidad_separa_hora_min=explode(":", $data->cant_hora);
                        $calamidad_suma_hora=$calamidad_suma_hora+$calamidad_separa_hora_min[0];
                        $calamidad_suma_minutos=$calamidad_suma_minutos+$calamidad_separa_hora_min[1];
                    }

               
                }  

            }

        
            $permisos_x_func=DB::table('per_permiso as p')
            ->whereIn('id_permiso',$array_id_permiso)
            ->select('id_funcionario')
            ->distinct('id_funcionario')
            ->get();
           
            if($suma_minutos>=60){
                $suma_hora_aux=$suma_minutos/60;
                if($suma_minutos%60!=0){
                    $separa_hora_min=explode(".",$suma_hora_aux);                
                    $suma_hora=$suma_hora+$separa_hora_min[0];
                    $suma_minutos=$separa_hora_min[1];
                }else{
                    $suma_hora=$suma_hora+$suma_hora_aux;
                    $suma_minutos=0;
                }
                   
            }

           
            if($iess_suma_minutos>=60){
                $iess_suma_hora_aux=$iess_suma_minutos/60;
                if($iess_suma_minutos%60!=0){
                    $iess_separa_hora_min=explode(".",$iess_suma_hora_aux);  

                    $iess_suma_hora=$iess_suma_hora+$iess_separa_hora_min[0];
                    $iess_suma_minutos=$iess_separa_hora_min[1];
                }else{
                    $iess_suma_hora=$iess_suma_hora+$iess_suma_hora_aux;
                    $iess_suma_minutos=0;
                }
                   
            }

            if($iess_suma_hora>=8){
                $cant_dias_iess= $iess_suma_hora/8;
                $cant_dias_iess=explode(".",$cant_dias_iess);
                $cant_dias_iess=$cant_dias_iess[0];
            }else{
                $cant_dias_iess=0;
            }
                

            $data_iess=["cantidad_iess"=>$cant_iess,"iess_suma_hora"=>$iess_suma_hora,"iess_suma_minutos"=>$iess_suma_minutos,"cant_dias_iess"=>$cant_dias_iess];

            // cert medico
            if($cm_suma_minutos>=60){
                $cm_suma_hora_aux=$cm_suma_minutos/60;
                if($cm_suma_minutos%60!=0){
                    $iess_separa_hora_min=explode(".",$cm_suma_hora_aux);  

                    $cm_suma_hora=$cm_suma_hora+$iess_separa_hora_min[0];
                    $cm_suma_minutos=$iess_separa_hora_min[1];
                }else{
                    $cm_suma_hora=$cm_suma_hora+$cm_suma_hora_aux;
                    $cm_suma_minutos=0;
                }
                   
            }

            if($cm_suma_hora>=8){
                $cant_dias_cm= $cm_suma_hora/8;
                $cant_dias_cm=explode(".",$cant_dias_cm);
                $cant_dias_cm=$cant_dias_cm[0];
            }else{
                $cant_dias_cm=0;
            }
                

            $data_cert_med=["cantidad_cm"=>$cant_cm,"cm_suma_hora"=>$cm_suma_hora,"cm_suma_minutos"=>$cm_suma_minutos,"cant_dias_cm"=>$cant_dias_cm];


            //calamidad
            if($calamidad_suma_minutos>=60){
                $calam_suma_hora_aux=$calamidad_suma_minutos/60;
                if($calamidad_suma_minutos%60!=0){
                    $cala_separa_hora_min=explode(".",$calam_suma_hora_aux);  

                    $calamidad_suma_hora=$calamidad_suma_hora+$cala_separa_hora_min[0];
                    $calamidad_suma_minutos=$cala_separa_hora_min[1];
                }else{
                    $calamidad_suma_hora=$calamidad_suma_hora+$calam_suma_hora_aux;
                    $calamidad_suma_minutos=0;
                }
                   
            }

            if($calamidad_suma_hora>=8){
                $cant_dias_cala= $calamidad_suma_hora/8;
                $cant_dias_cala=explode(".",$cant_dias_cala);
                $cant_dias_cala=$cant_dias_cala[0];
            }else{
                $cant_dias_cala=0;
            }
           
                

            $data_calamidad=["cant_calamidad"=>$cant_calamidad,"calamidad_suma_hora"=>$calamidad_suma_hora,"calamidad_suma_minutos"=>$calamidad_suma_minutos,"cant_dias_cala"=>$cant_dias_cala];


            // //asunto
            if($asunto_suma_minutos>=60){
                $asunto_suma_hora_aux=$asunto_suma_minutos/60;
                if($asunto_suma_minutos%60!=0){
                    $asunto_separa_hora_min=explode(".",$asunto_suma_hora_aux);  

                    $asunto_suma_hora=$asunto_suma_hora+$asunto_separa_hora_min[0];
                    $asunto_suma_minutos=$asunto_separa_hora_min[1];
                }else{
                    $asunto_suma_hora=$asunto_suma_hora+$asunto_suma_hora_aux;
                    $asunto_suma_minutos=0;
                }
                   
            }

            if($asunto_suma_hora>=8){
                $cant_dias_asunto= $asunto_suma_hora/8;
                $cant_dias_asunto=explode(".",$cant_dias_asunto);
                $cant_dias_asunto=$cant_dias_asunto[0];
            }else{
                $cant_dias_asunto=0;
            }
                

            $data_asunto=["cant_asunto"=>$cant_asunto,"asunto_suma_hora"=>$asunto_suma_hora,"asunto_suma_minutos"=>$asunto_suma_minutos,"cant_dias_asunto"=>$cant_dias_asunto];


             //otro
             if($otro_suma_minutos>=60){
                $otro_suma_hora_aux=$otro_suma_minutos/60;
                if($otro_suma_minutos%60!=0){
                    $otro_separa_hora_min=explode(".",$otro_suma_hora_aux);  

                    $otro_suma_hora=$otro_suma_hora+$otro_separa_hora_min[0];
                    $otro_suma_minutos=$otro_separa_hora_min[1];
                }else{
                    $otro_suma_hora=$otro_suma_hora+$otro_suma_hora_aux;
                    $otro_suma_minutos=0;
                }
                   
            }

            if($otro_suma_hora>=8){
                $cant_dias_otro= $otro_suma_hora/8;
                $cant_dias_otro=explode(".",$cant_dias_otro);
                $cant_dias_otro=$cant_dias_otro[0];
            }else{
                $cant_dias_otro=0;
            }
                

            $data_otro=["cant_otro"=>$cant_otro,"otro_suma_hora"=>$otro_suma_hora,"otro_suma_minutos"=>$otro_suma_minutos,"cant_dias_otro"=>$cant_dias_otro];

        
            if(sizeof($permisos_gpr_mes)<=0){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'No existen información con los datos enviados'
                ]);
            }

            $empleados=DB::table('per_funcionario')
            ->where('estado','A')
            ->get();
            $total_empleado=sizeof($empleados);

            $cant_losep_planta=0;
            $cant_losep_contrato=0;

            $cant_codigo_planta=0;
            $cant_codigo_contrato=0;

            $cant_otro_planta=0;
            $cant_otro_contrato=0;
            foreach($empleados as $data_emp){
                //losep planta
                if($data_emp->id_ambito_ley==1 && $data_emp->id_ambito==1){
                    $cant_losep_planta=$cant_losep_planta+1;
                }
                //losep contrato
                if($data_emp->id_ambito_ley==1 && $data_emp->id_ambito==2){
                    $cant_losep_contrato=$cant_losep_contrato+1;
                }

                //codigo planta
                if($data_emp->id_ambito_ley==2 && $data_emp->id_ambito==1){
                    $cant_codigo_planta=$cant_codigo_planta+1;
                }

                //codigo contrato
                if($data_emp->id_ambito_ley==2 && $data_emp->id_ambito==2){
                    $cant_codigo_contrato=$cant_codigo_contrato+1;
                }

                //otro planta
                if($data_emp->id_ambito_ley==3 && $data_emp->id_ambito==1){
                    $cant_otro_planta=$cant_otro_planta+1;
                }

                //otro contato
                if($data_emp->id_ambito_ley==3 && $data_emp->id_ambito==2){
                    $cant_otro_contrato=$cant_otro_contrato+1;
                }
            }

            $data_empleado=["total_empleados"=>$total_empleado, "cant_losep_planta"=>$cant_losep_planta, "cant_losep_contrato"=>$cant_losep_contrato, "cant_codigo_planta"=>$cant_codigo_planta, "cant_codigo_contrato"=>$cant_codigo_contrato,
            "cant_otro_planta"=>$cant_otro_planta, "cant_otro_contrato"=>$cant_otro_contrato];

            if($suma_hora>=8){
                $cant_dias= $suma_hora/8;
                $cant_dias=explode(".",$cant_dias);
                $cant_dias=$cant_dias[0];
            }else{
                $cant_dias=0;
            }

            setlocale(LC_ALL,"es_ES@euro","es_ES","esp"); //IDIOMA ESPAÑOL
           
            $fecha_letra = strftime("%B de %Y", strtotime($fecha));
                     
            $nombrePDF="reporte_gpr.pdf";

            $responsable_uath=DB::table('per_parametro')
            ->where('codigo', 'RUATH')->select('descripcion','valor')
            ->first();

            // $asistente_uath=DB::table('per_parametro')
            // ->where('codigo', 'AUATH')->select('descripcion','valor')
            // ->first();

            $asistente_uath=auth()->user()->persona->nombres." ".auth()->user()->persona->apellidos;

            $pers_discapacidad=DB::table('per_parametro')
            ->where('codigo', 'TPD')->select('valor')
            ->first();

            $parametros=["responsable_uath"=>$responsable_uath, "asistente_uath"=>$asistente_uath,
            "pers_discapacidad"=>$pers_discapacidad];

            $pdf=PDF::LoadView('gestion_reporte.reporte_gpr_pdf',['datos'=>$permisos_gpr_mes,
            "suma_hora"=>$suma_hora, "suma_minutos"=>$suma_minutos, "cant_dias"=>$cant_dias, "data_iess"=>$data_iess, "data_cert_med"=>$data_cert_med, "data_calamidad"=>$data_calamidad, "data_asunto"=>$data_asunto, "data_otro"=>$data_otro,"data_empleado"=>$data_empleado, "permisos_x_func"=>$permisos_x_func,"fecha"=>$fecha, "parametros"=>$parametros, "fecha_letra"=>$fecha_letra ]);
            $pdf->setPaper("A4", "portrait");

          
            $estadoarch = $pdf->stream();

            //lo guardamos en el disco temporal
            Storage::disk('public')->put(str_replace("", "",$nombrePDF), $estadoarch);
            $exists_destino = Storage::disk('public')->exists($nombrePDF); 
            if($exists_destino){ 
                return response()->json([
                    'error'=>false,
                    'pdf'=>$nombrePDF
                ]);
            }else{
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'No se pudo crear el documento'
                ]);
            }
            

        }catch (\Throwable $e) {
            Log::error('ReportePermisoController => reporteGprMes => mensaje => '.$e->getMessage().' linea => '.$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }

    public function descargarPdf($archivo){
        try{   
        
            $exists_destino = Storage::disk('public')->exists($archivo); 

            if($exists_destino){
                return response()->download( storage_path('app/public/'.$archivo))->deleteFileAfterSend(true);
            }else{
                return back()->with(['error'=>'Ocurrió un error','estadoP'=>'danger']);
            } 

        } catch (\Throwable $th) {
            Log::error("ListadoTurnoController =>descargarPdf =>sms => ".$th->getMessage());
            return back()->with(['error'=>'Ocurrió un error','estadoP'=>'danger']);
        } 
    }
}
