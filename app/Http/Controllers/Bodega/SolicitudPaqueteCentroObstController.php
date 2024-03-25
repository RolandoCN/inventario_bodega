<?php

namespace App\Http\Controllers\Bodega;
use App\Http\Controllers\Controller;
use App\Models\Personal\Especialidad;
use \Log;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator; 
use App\Models\Bodega\Paquete;
use App\Models\Bodega\DetallePaquete;
use App\Models\Bodega\Medicamento; 
use App\Models\Bodega\Insumo; 
use App\Models\Bodega\TipoComprobanteOld;
use App\Models\Bodega\Comprobante;
use App\Models\Bodega\DetalleComprobante;
use App\Models\Bodega\PedidoBodegaGral;
use App\Models\Bodega\ProductoBodega;
use App\Models\Bodega\BodegaUsuario;
use App\Models\Bodega\Existencia;
use App\Models\Bodega\EntregaPaqueteCqCo;
use App\Models\Personal\Funcionario;

use App\Models\User;
use Hash;
use App\Models\Personal\UsuarioPerfil;
use App\Models\Personal\Perfil;

class SolicitudPaqueteCentroObstController extends Controller
{
    public function index($idpersona, $idm){
      
        $paquetes=DB::connection('pgsql')->table('bodega.paquete_cirugia')
        ->where('estado','A')
        ->where('area','CENTRO OBSTETRICO')
        ->get();

        $paciente= DB::connection('pgsql')->table('esq_pacientes.pacientes')
        ->where('id_paciente', $idpersona)
        ->first();
       
        $responsable= DB::connection('pgsql')->table('esq_datos_personales.personal')
        ->where('idpersonal', $idm)
        ->first();

        $url=DB::connection('pgsql')->table('bodega.parametro')
        ->where('estado', 'A')
        ->where('descripcion', 'Url')
        ->first();

        return view('gestion_paquetes_centro_obs.solicita_farmacia',[
            "paquetes"=>$paquetes,
            "paciente"=>$paciente,
            "responsable"=>$responsable,
            "url"=>$url
        ]);
    }

     //ENTREGA PAQUETES DESDE CENTRO OBSTETRICO A FARMACIA
     public function validarEntregaPaquete(Request $request){
       
        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{
                $total_comprobante=0;
                $actualizaComprobante=Comprobante::where('idcomprobante',$request->idcabeceraCompr)
                ->first();
                if($actualizaComprobante->codigo_old=="Entregado"){
                    DB::connection('pgsql')->rollback();
                    return (['mensaje'=>'El pedido ya fue entregado','error'=>true]);
                }

                if(strtotime($request->get('fecha_vali')) <= strtotime($actualizaComprobante->fecha_hora_actualiza)){
                    return (['mensaje'=>'El pedido ha sido actualizado, realice la peticion nuevamente','error'=>true, 'act'=>'S']);
                }
               
                $detalleItem=DetalleComprobante::with('comprobante')
                ->where('idcomprobante', $request->idcabeceraCompr)
                ->get();
                $iddetalle_array=[];
                foreach($detalleItem as $data){
                    array_push($iddetalle_array, $data->iddetalle_comprobante);
                    $cantida_ent=$data->cantidad;

                    $actualizaStockOld =ProductoBodega::where('idbodprod',$data->idbodprod)
                    ->first(); 
                  
                    if($data->cantidad > $actualizaStockOld->existencia){
                        if($actualizaStockOld->idprod >= 30000){
                            $nombre_item=DB::table('bodega.insumo')
                            ->where('codinsumo',$actualizaStockOld->idprod)
                            ->where('stock','<', $data->cantidad)
                            ->select('insumo as nombreItem','codinsumo as id')
                            ->first();
                          
                           
                        }else{
                            $nombre_item=DB::table('bodega.medicamentos')
                            ->where('coditem',$actualizaStockOld->idprod)
                            ->where('stock','<', $data->cantidad)
                            ->select(DB::raw("CONCAT(nombre, ' ', concentra, ' ', forma, ' ', presentacion ) AS nombreItem"),'coditem as id')
                            ->first();
                          
                        }
                        if(!is_null($nombre_item)){
                            DB::connection('pgsql')->rollback();                     
                            return (['mensaje'=>'No existe stock suficiente para el item '.$nombre_item->nombreItem,'error'=>true]);
                        }
                       
                    }else{

                        //procedemos a realizar la entrega (restamos de la bodega de farmacia)                                   
                        $StockAhora=$actualizaStockOld->existencia;
                        $nuevoStock_act=$StockAhora - $cantida_ent;
                        $actualizaStockOld->existencia=$nuevoStock_act;  
                        $actualizaStockOld->fecha=date('Y-m-d');
                        $actualizaStockOld->idusuario=auth()->user()->id;
                        $actualizaStockOld->save();

                        $ultimo=Existencia::orderBy('idexistencia','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->idexistencia+1;
                        } 
                        //actualizamos la tabla pedido
                        $Pedido=PedidoBodegaGral::where('iddetallecomprobante', $data->iddetalle_comprobante)
                        ->first();
                       
                        $Pedido->cantidad_entregada=$cantida_ent;
                        $Pedido->id_aprueba=auth()->user()->id;
                        $Pedido->fecha_aprueba=date('Y-m-d H:i:s');
                        $Pedido->estado="Entregado";
                        $Pedido->save();

                        $area_solicita=$data->comprobante->nomarea->descripcion; 
                        
                        //restamos la existencia de farmacia
                        $existencia=new Existencia();
                        $existencia->idexistencia=$suma;
                        $existencia->iddetalle_comprobante=$data->iddetalle_comprobante;
                        $existencia->lote=$Pedido->lote;
                        $existencia->resta=$cantida_ent;
                        $existencia->tipo="Egreso Bodega desde ".$area_solicita;
                        $existencia->cod="EABFA"; //EGRESO BODEGA A FARMACIA
                        $existencia->fecha_hora=date('Y-m-d H:i:s');
                        $existencia->fecha_elaboracion=$Pedido->fecha_elabora;
                        $existencia->fecha_caducidad=$Pedido->fecha_caducidad;
                        $existencia->fecha=date('Y-m-d');
                        $existencia->idusuario=auth()->user()->id;
                        $existencia->id_pedido=$Pedido->idpedido_bod_gral;
                        $existencia->idusuario_solicita=$Pedido->id_solicita;
                        $existencia->idbodprod=$actualizaStockOld->idbodprod;
                        $existencia->save();   

                        //sumamos a la bodega de dialisis
                        $ultimo =ProductoBodega::orderBy('idbodprod','desc')->first();  
                        if(is_null($ultimo)){
                            $sumauno=1;
                        }else{
                            $sumauno=$ultimo->idbodprod;
                        }  
                                                                    
                        $ProductoBodegaDialisis=new ProductoBodega();
                        $ProductoBodegaDialisis->idbodprod=$sumauno+1;
                        $ProductoBodegaDialisis->idprod=$actualizaStockOld->idprod;
                        $ProductoBodegaDialisis->idbodega=33;
                        $ProductoBodegaDialisis->existencia=$cantida_ent;
                        $ProductoBodegaDialisis->precio=number_format(($actualizaStockOld->precio),4,'.', '');
                        $ProductoBodegaDialisis->precio2=0;
                        $ProductoBodegaDialisis->fecha=date('Y-m-d');
                        $ProductoBodegaDialisis->idusuario=auth()->user()->id;
                        $ProductoBodegaDialisis->sistema_old="ENLINEA";
                        $ProductoBodegaDialisis->save();

                        //sumamos la existencia en esa bodega
                        $ultimo=Existencia::orderBy('idexistencia','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->idexistencia+1;
                        }
                        $existenciaDialisis=new Existencia();
                        $existenciaDialisis->idexistencia=$suma;
                        $existenciaDialisis->iddetalle_comprobante=$data->iddetalle_comprobante;
                        $existenciaDialisis->lote=$Pedido->lote;
                        $existenciaDialisis->suma=$cantida_ent;
                        $existenciaDialisis->tipo="Ingreso a Bodega Centro Quirurgico";
                        $existenciaDialisis->fecha_hora=date('Y-m-d H:i:s');
                        $existenciaDialisis->fecha_elaboracion=$Pedido->fecha_elabora;
                        $existenciaDialisis->fecha_caducidad=$Pedido->fecha_caducidad;
                        $existenciaDialisis->cod="IABCO";
                        $existenciaDialisis->idusuario=auth()->user()->id;
                        $existenciaDialisis->idbodprod=$ProductoBodegaDialisis->idbodprod;
                        $existenciaDialisis->save();   


                        if($Pedido->iditem >=30000){
                            //actualizar la tabla principal de medicamentos e insumos el stock en farmacia
                            $insumo= Insumo::where('codinsumo',$Pedido->iditem)
                            ->first();
                            $stock_Actual=$insumo->stock;
                            $insumo->stock=$stock_Actual - $cantida_ent;
                            $insumo->save(); 

                            // comprobamos que el stock actual no sea menor a lo q se va a quitar
                            if($stock_Actual < $cantida_ent){
                                DB::connection('pgsql')->rollback();
                                return (['mensaje'=>'El stock actual del item '.$insumo->insumo. " es  ".$stock_Actual,'error'=>true]); 
                            }
                        }else{
                            
                            $medicamentos= Medicamento::where('coditem',$Pedido->iditem)
                            ->first();
                            $stock_Actual=$medicamentos->stock;
                            $medicamentos->stock=$stock_Actual - $cantida_ent;
                            $medicamentos->save(); 

                            // comprobamos que el stock actual no sea menor a lo q se va a quitar
                            if($stock_Actual < $cantida_ent){
                                DB::connection('pgsql')->rollback();
                                return (['mensaje'=>'El stock actual del item '.$medicamentos->nombre. " es  ".$stock_Actual,'error'=>true]); 
                            }
                        }

                        $total_comprobante=$total_comprobante + $ProductoBodegaDialisis->precio;
                            
                    }
                }
                $actualizaEntrega=EntregaPaqueteCqCo::where('idcomprobante',$actualizaComprobante->idcomprobante)
                ->update(['fecha_aprueba'=>date('Y-m-d H:i:s'), 'id_aprueba'=>auth()->user()->id]);

                $actualizaComprobante->codigo_old="Entregado";
                $actualizaComprobante->fecha_aprobacion=date('Y-m-d H:i:s');            
                $actualizaComprobante->id_usuario_aprueba=auth()->user()->id;

                $actualizaComprobante->subtotal=number_format(($total_comprobante),2,'.', '');
                $actualizaComprobante->total=number_format(($total_comprobante),2,'.', '');

                $actualizaComprobante->id_usuario_aprueba=auth()->user()->id;
                $actualizaComprobante->save();

                //si no se completo la entrega de un item
                $pedidos=PedidoBodegaGral::whereIN('iddetallecomprobante', $iddetalle_array)
                ->where('estado','Solicitado')->first();
                if(!is_null($pedidos)){
                    return (['mensaje'=>'No se pudo completar la entrega total de los items','error'=>true]); 
                }
               
                return (['mensaje'=>'Informacion ingresada exitosamente','error'=>false]);
  

            } catch (\Throwable $e) {
                DB::connection('pgsql')->rollback();
                Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
                return (['mensaje'=>'Ocurrió un error,intentelo más tarde','error'=>true]); 
            }
        });
        return ($transaction);
    }

}