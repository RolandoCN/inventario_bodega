<?php

namespace App\Http\Controllers\Bodega;
use App\Http\Controllers\Controller;
use App\Models\Personal\Especialidad;
use \Log;
use Illuminate\Http\Request;
use DB;
use App\Models\Bodega\MedicamentoEspecialidad;

class EspecialidadMedicinaController extends Controller
{
    
    public function especialidadMedicinas(){

        $medicinas_especialidad= DB::connection('pgsql')->table('inventario.medicamento as med')
        ->where('idprod',53)
        ->where('insumo',0)
        ->get();
        // dd($medicinas_especialidad);

        $especialidad= DB::connection('pgsql')->table('agendamiento.especialidadmedica')->get();
        return view('gestion_bodega.medicina_especialidad',[
            "especialidad"=>$especialidad
        ]);
    }

    public function listaMedicinasEspecialidad($idesp){
        try{
            // $medicinas_especialidad= DB::connection('pgsql')->table('inventario.medicamento as med')
            // ->leftJoin('inventario.productos as pr', 'pr.idprod','med.idprod')
            // ->leftJoin('inventario.medicamento_especialidad as med_esp', 'med_esp.idprod','med.idprod')
            // ->where('pr.estado','=','1')
            // ->where('med.insumo','=','0')
            // ->select('pr.idprod','pr.nprod','med_esp.idespecialidad')
            // ->get();

            $medicamentos= DB::connection('pgsql')->table('inventario.medicamento as med')
            ->leftJoin('inventario.productos as pr', 'pr.idprod','med.idprod')
            // ->leftJoin('inventario.medicamento_especialidad as med_esp', 'med_esp.idprod','med.idprod')
            ->where('pr.estado','=','1')
            ->where('med.insumo','=','0')
            ->select('pr.idprod','pr.nprod')
            ->get();


            foreach($medicamentos as $key=> $data){
                //consultamos las medicinas x especialidad y comnprobamos si la tiene agregada
                $medicinas_especialidad=DB::connection('pgsql')
                ->table('inventario.medicamento_especialidad as med_esp')
                ->where('idespecialidad', $idesp)
                ->where('idprod', $data->idprod)
                ->first();

                if(!is_null($medicinas_especialidad)){
                    $medicamentos[$key]->pertenece="S";
                }else{
                    $medicamentos[$key]->pertenece="N";
                }
            }
            
            return response()->json([
                'error'=>false,
                'resultado'=>$medicamentos
            ]);
               
        }catch (\Throwable $e) {
            Log::error('EspecialidadMedicinaController => listaMedicinasEspecialidad => mensaje => '.$e->getMessage());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

    public function aggQuitarMedicina($idmed, $tipo, $idesp){
        try{
          
           
            //agregamos
            if($tipo=="A"){
                $nuevaMedicina=new MedicamentoEspecialidad();
                $nuevaMedicina->idespecialidad=$idesp;
                $nuevaMedicina->idprod=$idmed;
                $nuevaMedicina->save();
                return response()->json([
                    'error'=>false,
                    'mensaje'=>'Información registrada exitosamente'
                ]);
            }else{
                //lo quitamos
                $medicina_esp= MedicamentoEspecialidad::where('idprod',$idmed)
                ->where('idespecialidad', $idesp)
                ->first();
                if(is_null($medicina_esp)){
                    return response()->json([
                        'error'=>false,
                        'mensaje'=>'Información quitada exitosamente'
                    ]);
                }
               
                $medicina_esp->delete();
                return response()->json([
                    'error'=>false,
                    'mensaje'=>'Información quitada exitosamente'
                ]);
            }
               
        }catch (\Throwable $e) {
            Log::error('EspecialidadMedicinaController => aggQuitarMedicina => mensaje => '.$e->getMessage().' line => '.$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
            
        }
    }

}
