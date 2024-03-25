<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Personal\Especialidad;
use \Log;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator; 
use Storage;
use SplFileInfo;
use Carbon\Carbon;
class AlimentosPacientesController extends Controller
{
    public function obtenerListado(){
        try{
            //consultamos las comidas registradas a pacientes
            $comidaPaciente=DB::table('esq_dietas.dieta_paciente as dp')
            ->where('dp.estado','A')
            ->where(function($c){
                $c->whereDate('dp.feha_registro',date('Y-m-d'))
                ->orwhereDate('dp.fecha_actualizacion',date('Y-m-d'));
            })
            ->leftJoin('esq_dietas.tipos_dietas as td', 'td.id_dieta','dp.id_dieta')
            ->leftJoin('esq_pacientes.pacientes as pac', 'pac.id_paciente','dp.id_paciente')
            ->leftJoin('esq_catalogos.servicio as serv', 'serv.id_servicio','dp.id_servicio')
            ->leftJoin('inventario.persona as pers_ing', 'pers_ing.idper','dp.id_agrego')
            ->leftJoin('inventario.persona as pers_act', 'pers_act.idper','dp.id_actualizo')
            ->select('pac.documento as cedula',DB::raw("CONCAT(pac.apellido1,' ', pac.apellido2,' ', pac.nombre1,' ', pac.nombre2) AS paciente_nombres"),'pac.fecha_nacimiento','dp.observacion','serv.nombre as detalle_serv','dp.id_servicio','dp.id_paciente'
            ,'dp.id_agrego',DB::raw("CONCAT(pers_ing.ape1,' ', pers_ing.ape2,' ', pers_ing.nom1,' ', pers_ing.nom2) AS resp_ingresa"),DB::raw("CONCAT(pers_act.ape1,' ', pers_act.ape2,' ', pers_act.nom1,' ', pers_act.nom2) AS resp_act"),'dp.id_actualizo','dp.feha_registro','dp.fecha_actualizacion','dp.id_registro','td.nombre as tipodieta','dp.id_dieta','dp.id_hospitalizacion')
            ->get();       
           
            foreach($comidaPaciente as $key=> $data){              
                $fechaNacimiento = Carbon::parse($data->fecha_nacimiento);
                $comidaPaciente[$key]->edad_actual=$fechaNacimiento->age; 

                $alta="S";
                #verificamos si es dialisis o otro area
                if($data->detalle_serv=="DIALISIS"){
                    // dd($data->id_hospitalizacion);
                    #comprobamos si aun esta en sillon
                    $estadoHosp=DB::table('esq_dialisis.cama_paciente')
                    ->where('id_registro', $data->id_hospitalizacion)
                    ->where('estado','A')->first();
                  
                    if(!is_null($estadoHosp)){
                        $alta="N";
                    }
                }else{
                    # si es otra area comprobamos si aun esta en cama
                    $estadoHosp=DB::table('esq_hospitalizacion.cama_paciente')
                    ->where('id_registro', $data->id_hospitalizacion)
                    ->where('estado','A')->first();
                    if(!is_null($estadoHosp)){
                        $alta="N";
                    }
                }
                
                if(!is_null($data->id_actualizo)){
                    $comidaPaciente[$key]->responsable=$data->resp_act; 
                    $comidaPaciente[$key]->idresponsable=$data->resp_act; 
                    $comidaPaciente[$key]->fecha=$data->fecha_actualizacion; 
                    $comidaPaciente[$key]->alta=$alta; 
                }else{
                    $comidaPaciente[$key]->responsable=$data->resp_ingresa; 
                    $comidaPaciente[$key]->idresponsable=$data->id_agrego; 
                    $comidaPaciente[$key]->fecha=$data->feha_registro;
                    $comidaPaciente[$key]->alta=$alta; 
                }
                
                  
            }

            return (['data'=>$comidaPaciente,'error'=>false]);
        } catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return (['mensaje'=>'Ocurrió un error,intentelo más tarde','error'=>true]); 
        }

    }

    public function historialComidaPaciente($idpaciente){
        try{
           
            $historialAlimentos=DB::table('esq_dietas.dieta_paciente as dp')
            ->whereIn('dp.estado',['A','F'])
            ->where('dp.id_paciente',$idpaciente)
            
            ->leftJoin('esq_dietas.tipos_dietas as td', 'td.id_dieta','dp.id_dieta')
            ->leftJoin('esq_pacientes.pacientes as pac', 'pac.id_paciente','dp.id_paciente')
            ->leftJoin('esq_catalogos.servicio as serv', 'serv.id_servicio','dp.id_servicio')
            ->leftJoin('inventario.persona as pers_ing', 'pers_ing.idper','dp.id_agrego')
            ->leftJoin('inventario.persona as pers_act', 'pers_act.idper','dp.id_actualizo')
            ->select('pac.documento as cedula',DB::raw("CONCAT(pac.apellido1,' ', pac.apellido2,' ', pac.nombre1,' ', pac.nombre2) AS paciente_nombres"),'pac.fecha_nacimiento','dp.observacion','serv.nombre as detalle_serv','dp.id_servicio','dp.id_paciente'
            ,'dp.id_agrego',DB::raw("CONCAT(pers_ing.ape1,' ', pers_ing.ape2,' ', pers_ing.nom1,' ', pers_ing.nom2) AS resp_ingresa"),DB::raw("CONCAT(pers_act.ape1,' ', pers_act.ape2,' ', pers_act.nom1,' ', pers_act.nom2) AS resp_act"),'dp.id_actualizo','dp.feha_registro','dp.fecha_actualizacion','dp.id_registro','td.nombre as tipodieta','dp.id_dieta','dp.id_hospitalizacion')
            ->get(); 
           
            foreach($historialAlimentos as $key=> $data){              
                $fechaNacimiento = Carbon::parse($data->fecha_nacimiento);
                $historialAlimentos[$key]->edad_actual=$fechaNacimiento->age; 

                $alta="S";
                #verificamos si es dialisis o otro area
                if($data->detalle_serv=="DIALISIS"){
                    // dd($data->id_hospitalizacion);
                    #comprobamos si aun esta en sillon
                    $estadoHosp=DB::table('esq_dialisis.cama_paciente')
                    ->where('id_registro', $data->id_hospitalizacion)
                    ->where('estado','A')->first();
                  
                    if(!is_null($estadoHosp)){
                        $alta="N";
                    }
                }else{
                    # si es otra area comprobamos si aun esta en cama
                    $estadoHosp=DB::table('esq_hospitalizacion.cama_paciente')
                    ->where('id_registro', $data->id_hospitalizacion)
                    ->where('estado','A')->first();
                    if(!is_null($estadoHosp)){
                        $alta="N";
                    }
                }
                
                if(!is_null($data->id_actualizo)){
                    $historialAlimentos[$key]->responsable=$data->resp_act; 
                    $historialAlimentos[$key]->idresponsable=$data->resp_act; 
                    $historialAlimentos[$key]->fecha=$data->fecha_actualizacion; 
                    $historialAlimentos[$key]->alta=$alta; 
                }else{
                    $historialAlimentos[$key]->responsable=$data->resp_ingresa; 
                    $historialAlimentos[$key]->idresponsable=$data->id_agrego; 
                    $historialAlimentos[$key]->fecha=$data->feha_registro;
                    $historialAlimentos[$key]->alta=$alta; 
                }
                
                  
            }
           
            return (['data'=>$historialAlimentos,'error'=>false]);
        } catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return (['mensaje'=>'Ocurrió un error,intentelo más tarde','error'=>true]); 
        }
    }
}