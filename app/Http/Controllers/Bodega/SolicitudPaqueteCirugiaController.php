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

class SolicitudPaqueteCirugiaController extends Controller
{
    public function index($idpersona, $idm){
      
        $paquetes=DB::connection('pgsql')->table('bodega.paquete_cirugia')
        ->where('estado','A')
        ->where('area','CENTRO QUIRURGICO')
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

        return view('gestion_paquetes_cirugia.solicita_farmacia',[
            "paquetes"=>$paquetes,
            "paciente"=>$paciente,
            "responsable"=>$responsable,
            "url"=>$url
        ]);
    }

    public function guardarUsuario($cedula, $password, $idbodega){
       
        $transaction=DB::transaction(function() use($cedula, $password, $idbodega){
            try{ 
                if($idbodega==32){
                    $lugar='CENTRO QUIRURGICO';
                }else{
                    $lugar='CENTRO OBSTETRICO';
                }

                $cq_area=DB::table('bodega.area')
                ->where('descripcion',$lugar)
                ->where('estado','A')
                ->first();

                $area_func= Funcionario::where('ci',$cedula)
                ->where('estado',1)->first();
                $area_func->id_area=$cq_area->id_area;
                $area_func->save();

                //valida contraseña
                $pass=md5($password);
               
                if($area_func->clave != $pass){
                    return [
                        "error"=>true,
                        "mensaje"=>"Contraseña incorrecta"
                    ];
                }

                             
                //validar que exista
                $existe_user=User::where('tx_login', $cedula)
                ->whereIn('estado',['A','I'])
                ->first();
               
                if(!is_null($existe_user)){
                    if($existe_user->estado=='A'){  
                        
                        //validamos que tenga acceder a pedir paquete e inusmo
                        $bodega_user=DB::table('bodega.bodega_usuario')
                        ->where('idusuario',$existe_user->id)
                        ->where('idbodega',$idbodega)
                        ->first();
                      
                        // if(is_null($bodega_user)){
                        //     DB::Rollback();
                        //     return [
                        //         "error"=>true,
                        //         "mensaje"=>"Usted no tiene acceso a realizar esta solicitud"
                        //     ];
                        // }
                           
                        return [
                            "error"=>false,
                            "existe"=>"S",
                            "idusuario"=>$existe_user->id,
                            "id_area"=>$cq_area->id_area
                        ];
                    }else{

                        $existe_user->id_persona=$area_func->idper;
                        $existe_user->tx_login=$cedula;
                        $existe_user->password=Hash::make($cedula);
                        $existe_user->estado='A';
                        $existe_user->id_creadopor=0;
                        $existe_user->fe_creacion=date('Y-m-d H:i:s');

                        $existe_user->save();

                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->idcomprobante+1;
                        }
                        $UsuarioPerfil=UsuarioPerfil::where('id_usuario',$existe_user->id)->first();
                        if(!is_null($UsuarioPerfil)){

                         
                            if($UsuarioPerfil->save()){
                                //validamos que tenga acceder a pedir paquete e insumo
                                $bodega_user=DB::table('bodega.bodega_usuario')
                                ->where('idusuario',$existe_user->id)
                                ->where('idbodega',$idbodega)
                                ->first();
                                // if(is_null($bodega_user)){
                                //     DB::Rollback();
                                //     return [
                                //         "error"=>true,
                                //         "mensaje"=>"Usted no tiene acceso a realizar esta solicitud"
                                //     ];
                                // }

                                return [
                                    "error"=>false,
                                    "mensaje"=>"Cuenta creada exitosamente",
                                    "idusuario"=>$existe_user->id,
                                    "id_area"=>$cq_area->id_area
                                ];
                            }else{
                                DB::Rollback();
                                return [
                                    "error"=>true,
                                    "mensaje"=>"No se pudo registrar la cuenta de la persona"
                                ];
                            }
                        }
                        else{
                            $perfilInvitado=Perfil::where('descripcion','Invitado')
                            ->select('id_perfil')
                            ->first();
                            $ultimo=UsuarioPerfil::orderby('idperfil_usuario','desc')->first();
                            $UsuarioPerfil=new UsuarioPerfil();
                            $UsuarioPerfil->idperfil_usuario=$ultimo->idperfil_usuario+1;
                            $UsuarioPerfil->id_usuario=$existe_user->id;
                            $UsuarioPerfil->id_perfil=$perfilInvitado->id_perfil; //invitado
                        
                            if($UsuarioPerfil->save()){

                                //validamos que tenga acceder a pedir paquete e inusmo
                                $bodega_user=DB::table('bodega.bodega_usuario')
                                ->where('idusuario',$existe_user->id)
                                ->where('idbodega',$idbodega)
                                ->first();
                                // if(is_null($bodega_user)){
                                //     DB::Rollback();
                                //     return [
                                //         "error"=>true,
                                //         "mensaje"=>"Usted no tiene acceso a realizar esta solicitud"
                                //     ];
                                // }

                                return [
                                    "error"=>false,
                                    "mensaje"=>"Cuenta creada exitosamente",
                                    "idusuario"=>$existe_user->id,
                                    "id_area"=>$cq_area->id_area
                                ];
                            }else{
                                DB::Rollback();
                                return [
                                    "error"=>true,
                                    "mensaje"=>"No se pudo registrar la cuenta"
                                ];
                            }
                        }
                    }
                }

                $ultimo=User::orderby('id','desc')->first();
                $Usuario=new User();
                $Usuario->id=$ultimo->id+1;
                $Usuario->id_persona=$area_func->idper;
                $Usuario->tx_login=$cedula;
                $Usuario->password=Hash::make($cedula);
                $Usuario->estado='A';
                $Usuario->id_creadopor=0;
                $Usuario->fe_creacion=date('Y-m-d H:i:s');
                if($Usuario->save()){
                    $perfilInvitado=Perfil::where('descripcion','Invitado')
                    ->select('id_perfil')
                    ->first();
                    $ultimo=UsuarioPerfil::orderby('idperfil_usuario','desc')->first();
                    $UsuarioPerfil=new UsuarioPerfil();
                    $UsuarioPerfil->idperfil_usuario=$ultimo->idperfil_usuario+1;
                    $UsuarioPerfil->id_usuario=$Usuario->id;
                    $UsuarioPerfil->id_perfil=$perfilInvitado->id_perfil;
                
                    if($UsuarioPerfil->save()){

                        //validamos que tenga acceder a pedir paquete e insumo
                        $bodega_user=DB::table('bodega.bodega_usuario')
                        ->where('idusuario',$Usuario->id)
                        ->where('idbodega',$idbodega)
                        ->first();
                        // if(is_null($bodega_user)){
                        //     DB::Rollback();
                        //     return [
                        //         "error"=>true,
                        //         "mensaje"=>"Usted no tiene acceso a realizar esta solicitud"
                        //     ];
                        // }

                        return [
                            "error"=>false,
                            "mensaje"=>"Cuenta creada exitosamente",
                            "idusuario"=>$Usuario->id,
                            "id_area"=>$cq_area->id_area
                        ];
                    }else{
                        DB::Rollback();
                        return[
                            "error"=>true,
                            "mensaje"=>"No se pudo registrar la cuenta"
                        ];
                    }

                }else{
                    DB::Rollback();
                    return[
                        "error"=>true,
                        "mensaje"=>"No se pudo registrar la información del usuario"
                    ];
                }

            }catch (\Throwable $e) {
                DB::Rollback();
                Log::error('SolicitudPaqueteCirugiaController => guardarUsuario => mensaje => '.$e->getMessage().' linea => '.$e->getLine());
                return [
                    'error'=>true,
                    'mensaje'=>'Ocurrió un error'
                ];
                
            }
        });
        return $transaction;
    }

    //SOLICITUD PAQUETES DESDE CIRUGIA Y GINECOLOGIA A FARMACIA
    public function guardarSolicitudCirugia(Request $request){
       
        $array_paquete=$request->idpaquete_selecc;
        $cantidad=$request->cantidad;
       
        if(sizeof($array_paquete)==0){
            return (['mensaje'=>'Debe seleccionar al menos un paquete','error'=>true]);
        }

        //fecha uso paquete
        if(is_null($request->fecha_uso)){
            return (['mensaje'=>'Debe ingresar la fecha de uso del paquete','error'=>true]);
        }

        if(strtotime($request->get('fecha_uso')) < strtotime(date('Y-m-d'))){
            return (['mensaje'=>'La fecha de uso no puede ser menor a la fecha actual','error'=>true, 'act'=>'S']);
        }

        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{
                $lugar=$request->lugar;
                if($lugar=="CQ"){
                    $tipo_com=21;
                    $bod=32;
                }else{
                    $tipo_com=22;
                    $bod=33;
                }
                //verificamos si esta creado el usuario
                $creaUser=$this->guardarUsuario($request->cedula_responsable, $request->password, $bod);
                if($creaUser["error"]==true){
                    return (['mensaje'=>$creaUser["mensaje"],'error'=>true]);
                }
                
                //si existe un pedido pendiente no permitimos realizar la entrega
                $existe_pedido_pendiente=Comprobante::where('idtipo_comprobante',$tipo_com)
                ->where('codigo_old','PedidoAFarm')
                ->where('estado','Activo')
                ->where('id_paciente',$request->id_paciente)
                ->first();

               
                if(!is_null($existe_pedido_pendiente)){
                    return (['mensaje'=>'Existe un pedido pendiente de retirar para el paciente seleccionado','error'=>true]);
                }

                //validamos que solo pueda solicitar un paquete en el dia
                $paquteDia=Comprobante::where('idtipo_comprobante',$tipo_com)
                ->whereIn('codigo_old',['PedidoAFarm','Entregado'])
                ->where('estado','Activo')
                ->where('id_paciente',$request->id_paciente)
                ->whereDate('fecha_uso_item',$request->fecha_uso)
                ->first();
              
                
                if(!is_null($paquteDia)){
                    // return (['mensaje'=>'Ya existe un paquete en el dia para el paciente seleccionado','error'=>true]);
                }
              
                             
                $total_comprobante=0;
                $tipocomp_old= TipoComprobanteOld::where('idtipocom',$tipo_com)//TRANSFERENCIA PAQUTE
                ->orderBy('idtipocom','desc')
                ->first();

                //registramos la cabecera
                $ultimo=Comprobante::orderby('idcomprobante','desc')->first();
                if(is_null($ultimo)){
                    $suma=1;
                }else{
                    $suma=$ultimo->idcomprobante+1;
                }
                $comprobante=new Comprobante();
                $comprobante->idcomprobante=$suma;
                $comprobante->idtipo_comprobante=$tipocomp_old->idtipocom;
                $comprobante->secuencial=$tipocomp_old->numcom+1;
                $comprobante->descripcion=$tipocomp_old->razoncom;
                $comprobante->fecha_hora=date('Y-m-d H:i:s');
                $comprobante->fecha=date('Y-m-d');
                $comprobante->idbodega=$bod;
                $comprobante->observacion=$request->motivo;             
                $comprobante->id_usuario_ingresa=$creaUser["idusuario"];
                $comprobante->area=$creaUser["id_area"];
                $comprobante->id_paciente=$request->id_paciente;
                $comprobante->id_cie10=$request->cie_10;
                $comprobante->fecha_uso_item=$request->fecha_uso;
                $comprobante->tipoarea="Hospitalizacion";
                if($comprobante->save()){
                   
                    //datos detalle
                    $cant=$request->cantidad;
                    $nomb_paq=$request->nombrepaquete;
                    //recorremos los paquetes seleccionados
                    foreach($request->idpaquete_selecc as $key=> $idpaq){

                        //validamos los stock disponibles en farmacia de cada uno de los items de los paquetes
                        $validaPaq=$this->validaPaqueteCirugia($idpaq, $cant[$key]);
                       
                        //caso de error al consultar
                        if($validaPaq['error']==true){
                            DB::connection('pgsql')->rollback();
                            return (['mensaje'=>'No se pudo comprobar la existencia en stock, intentelo mas tarde','error'=>true]);
                        }else{
                            // recorremos cada uno de los items x paquete
                            foreach($validaPaq['resultado'] as $detalle){                               
                               
                                //el stock es menor a lo solicitado (x lote)
                                if($detalle->info=="N"){
                                    //insumos
                                    if($detalle->id_item >=30000){
                                        $stock_max=$detalle->stock_farm_ins;
                                        $nomb_item=$detalle->descripcion_ins;
                                    }else{ //medicina
                                        $stock_max=$detalle->stock_farm_med;
                                        $nomb_item=$detalle->descripcion_med;
                                    }
                                   
                                    // DB::connection('pgsql')->rollback();
                                    // return (['mensaje'=>'El stock del '.$request->nombrepaquete[$key].' del item '.$nomb_item.' es '.$stock_max.' y el solicitado es '.$cant[$key] * $detalle->cantidad,'error'=>true ,'idpaquete'=>$idpaq,'paquete'=>$request->nombrepaquete[$key]]);
                                
                                }else{

                                    if($detalle->id_item >=30000){
                                        //insumo gral
                                        $idbodega_sel=7;
                                    }else{
                                        //medicina gral
                                        $idbodega_sel=6;
                                    }

                                    if($detalle->id_item >=30000){
                                        $stock_max=$detalle->stock_farm_ins;
                                        $nomb_item=$detalle->descripcion_ins;
                                    }else{ //medicina
                                        $stock_max=$detalle->stock_farm_med;
                                        $nomb_item=$detalle->descripcion_med;
                                    }
                                    
                                    //registramos los detalles
                                    $ultimo=EntregaPaqueteCqCo::orderBy('identrega_paquete_co_cq','desc')->first();
                                    if(is_null($ultimo)){
                                        $suma=1;
                                    }else{
                                        $suma=$ultimo->identrega_paquete_co_cq+1;
                                    }
                                    //detalle de cada uno de los items x paquetes
                                    $entrega=new EntregaPaqueteCqCo();
                                    $entrega->identrega_paquete_co_cq=$suma;
                                    $entrega->idpaquete=$idpaq;
                                    $entrega->desc_paquete=$nomb_paq[$key];
                                    $entrega->iddetalle_paq=$detalle->id_detalle_paquetes_cirugia;
                                    $entrega->id_item=$detalle->id_item;
                                    $entrega->idbodega=$idbodega_sel;
                                    $entrega->nombre_item=$nomb_item;
                                    $entrega->tipo=$detalle->tipo;
                                    $entrega->cantidad=$detalle->cantidad_solic;
                                    $entrega->fecha_solicitud=date('Y-m-d H:i:s');
                                    $entrega->id_solicita=$creaUser["idusuario"];
                                    $entrega->cantidad_paquete=$cant[$key];
                                    $entrega->idcomprobante=$comprobante->idcomprobante;
                                    $entrega->lugar=$lugar;
                                    $entrega->save();
                                    
                                  
                                }

                            }
                        }
                       
                    }

                   
                    $tipocomp_old->numcom=$comprobante->secuencial;
                    $tipocomp_old->save();
                  
                    //si tofdo ok el comprobante se crea
                    $comprobante_crear=Comprobante::find($comprobante->idcomprobante);
                    $comprobante_crear->estado="Activo";
                    $comprobante_crear->codigo_old="PedidoAFarm";//pedido
                    $comprobante_crear->subtotal=number_format(($total_comprobante),2,'.', '');
                    $comprobante_crear->total=number_format(($total_comprobante),2,'.', '');
                    $comprobante_crear->save();

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

    public function validaPaqueteCirugia($id, $cantidad){
      
        $valida_paquetes=DB::table('bodega.detalle_paquetes_cirugia as de')
        ->leftJoin('bodega.medicamentos as m', 'm.coditem','de.id_item')
        ->leftJoin('bodega.insumo as i', 'i.codinsumo','de.id_item')
        ->where('id_paquete',$id)
        ->where('estado','A')
        ->select('de.id_detalle_paquetes_cirugia','de.id_item','de.tipo','m.stock as stock_farm_med','i.stock as stock_farm_ins',DB::raw("CONCAT(m.nombre,' - ', m.concentra,' - ', m.forma,' - ', m.presentacion) AS descripcion_med"),DB::raw("CONCAT(i.insumo) AS descripcion_ins"),'de.cantidad')
        ->orderBy('descripcion_ins','asc')
        ->orderBy('descripcion_med','asc')
        ->get();

        $detalle_incumple=[];
        foreach($valida_paquetes as $key => $data){
            $cantidad_solic_x_item=0;

            if($data->tipo=="Insumo"){
                $nombre_item=$data->descripcion_ins;
                $cantidad_item=$data->cantidad;

                $cantidad_stock_farmacia=$data->stock_farm_ins;

                $cantidad_solic_x_item=$cantidad * $data->cantidad;

            }else{
                $nombre_item=$data->descripcion_med;
                $cantidad_item=$data->cantidad;

                $cantidad_stock_farmacia=$data->stock_farm_med;

                $cantidad_solic_x_item=$cantidad * $data->cantidad;
               
            }
            
            if($cantidad_solic_x_item > $cantidad_stock_farmacia){
                $valida_paquetes[$key]->info="N";
            }else{
                $valida_paquetes[$key]->info="S";
            }

            $valida_paquetes[$key]->cantidad_solic=$cantidad_solic_x_item;
            $valida_paquetes[$key]->cantidad_stock_farmacia=$cantidad_stock_farmacia;
        }
      
        return [
            'error'=>false,
            'resultado'=>$valida_paquetes
        ];
        // echo json_encode($valida_paquetes, JSON_FORCE_OBJECT);
    }


    public function detallePedidoPaquete($id,$idbodega){
       
        try{
            
            $info= DB::connection('pgsql')->table('bodega.comprobante as comp')
            ->leftJoin('bodega.entrega_paquete_co_cq as ent', 'ent.idcomprobante','comp.idcomprobante')
            ->leftJoin('bodega.medicamentos as medi', 'medi.coditem','ent.id_item')
            ->leftJoin('bodega.insumo as i', 'i.codinsumo','ent.id_item')
            ->select('ent.nombre_item','ent.cantidad as cantidad_pedida','ent.id_item', 'medi.stock as stock_med','i.stock as stock_ins')
            ->where('comp.idcomprobante',$id)
            ->where('comp.estado','Activo')
            ->get();
    
            foreach($info as $key => $data){
        
                if($data->id_item>=30000){
                    $stock_item=$data->stock_ins;
                   
                }else{
                    $stock_item=$data->stock_med;
                }
                $info[$key]->stock_disp=$stock_item;
    
            }

            #agrupamos
            $lista_final_agrupada=[];
            foreach ($info as $key => $item){                
                if(!isset($lista_final_agrupada[$item->nombre_item])) {
                    $lista_final_agrupada[$item->nombre_item]=array($item);
            
                }else{
                    array_push($lista_final_agrupada[$item->nombre_item], $item);
                }
            }
            
            $datosPa=EntregaPaqueteCqCo::where("idcomprobante", $id)->get();
            $lista_final_agrupada_valida=[];
            foreach ($datosPa as $key => $item){                
                if(!isset($lista_final_agrupada_valida[$item->id_item])) {
                    $lista_final_agrupada_valida[$item->id_item]=array($item);
             
                }else{
                    array_push($lista_final_agrupada_valida[$item->id_item], $item);
                }
            }

            //eliminamos los detalles y pedidos antes de asignarle
            $detalleElimina=DetalleComprobante::where('idcomprobante', $id)
            ->get();
            foreach($detalleElimina as $data){
                $eliminaPedido=PedidoBodegaGral::where('iddetallecomprobante', $data->iddetalle_comprobante)
                ->first();
                $eliminaPedido->delete();
            }
            $EliminaDetalle=DetalleComprobante::where('idcomprobante', $id)
            ->delete();

            $datosReceta=DB::table('bodega.comprobante as comp')
            ->leftJoin('esq_pacientes.pacientes as per_pac', 'per_pac.id_paciente','comp.id_paciente')
            ->leftJoin('esq_rdacaa.cie10', 'cie10.cie10_id','comp.id_cie10')
         
            ->select(DB::raw("CONCAT(per_pac.apellido1,'  ', per_pac.apellido2,'  ', per_pac.nombre1,'  ', per_pac.nombre2) AS paciente"),
           'per_pac.documento as cedula_paciente',DB::raw("CONCAT(cie10_codigo,' -- ',cie10_descripcion) AS descripcion_cie_10"))
           ->where('comp.idcomprobante', $id)
            ->first();

            foreach($lista_final_agrupada_valida as $i=>  $dat){
                $paqueteLote=$this->ItemLote($i, $id);
                if($paqueteLote['error']==true){
                    DB::connection('pgsql')->rollback();
                   
                    return (['mensaje'=>$paqueteLote['mensaje'],'error'=>true]); 
                }
            }

        
            return response()->json([
                'error'=>false,
                'resultado'=>$lista_final_agrupada,
                'fecha'=>date('Y-m-d H:i:s'),
                'datosReceta'=>$datosReceta
            ]);

        }catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error'
            ]);
            
        }
    }

    public function ItemLote($identrega, $compr){

        $transaction=DB::connection('pgsql')->transaction(function() use ($identrega, $compr){
            try{

                $total_comprobante=0;
      
                $datosPa=EntregaPaqueteCqCo::where("id_item", $identrega)
                ->where("idcomprobante",$compr)
                ->select(DB::raw('sum("cantidad") as cantidad'),'id_item','idbodega')
                ->groupby('id_item','idbodega')
                ->first();  
               
                $cantidad=$datosPa->cantidad;
                $item=$datosPa->id_item;
                $idbodega=$datosPa->idbodega;
                $fecha_Actual=date('Y-m-d');

                $paquete=EntregaPaqueteCqCo::where("idcomprobante", $compr)->first();
                           
                // $StockPB=DB::table('bodega.prodxbod as pb')
                // ->leftJoin('bodega.existencia as e', 'e.idbodprod','pb.idbodprod')
                // ->where('pb.idbodega',$idbodega)
                // ->where('existencia','>',0)
                // ->whereDate('e.fecha_caducidad','>=',$fecha_Actual)
                // ->where('idprod',$item)
                // ->select('pb.existencia','pb.idbodprod','e.fecha_caducidad')       
                // ->distinct()
                // ->orderBy('e.fecha_caducidad','asc')
                // ->get();

                $StockPB=DB::table('bodega.prodxbod as pb')
                ->leftJoin('bodega.lotexprod as e', 'e.idbodp','pb.idbodprod')
                ->where('pb.idbodega',$idbodega)
                ->where('existencia','>',0)
                ->where('idprod',$item)
                ->whereDate('e.fcaduca','>=',$fecha_Actual)
                ->select('pb.existencia','pb.idbodprod','e.fcaduca')       
                ->distinct()
                ->orderBy('e.fcaduca','asc')
                ->get();
        
                
                if(sizeof($StockPB)==0){
                    // DB::connection('pgsql')->rollback();                                     
                    // return (['mensaje'=>'El stock insuficiente  ','error'=>true ]);
                }else{

                    $cantidad_item=0;                                
                    $cantidad_item=$cantidad;
                
                    foreach($StockPB as $data){
                    
                        if($cantidad_item>0){
                        
                            if($cantidad_item <= $data->existencia){//existencia_red

                                if($data->existencia>0){//existencia_red
                                    // $Prodbod =ProductoBodega::with('existencias')->where('idbodprod',$data->idbodprod)
                                    // ->where('idprod',$item)
                                    // ->first(); 
                                    
                                    $Prodbod =ProductoBodega::with('lote')->where('idbodprod',$data->idbodprod)
                                    ->where('idprod',$item)
                                    ->first();  
                                
                                    //registramos los detalles
                                    $ultimo=DetalleComprobante::orderBy('iddetalle_comprobante','desc')->first();
                                    if(is_null($ultimo)){
                                        $suma=1;
                                    }else{
                                        $suma=$ultimo->iddetalle_comprobante+1;
                                    }
                                
                                    $total_item=0;
                                    $total_item=$Prodbod->precio * $cantidad_item;
                                    $detalles=new DetalleComprobante();
                                    $detalles->iddetalle_comprobante=$suma;
                                    $detalles->idcomprobante=$compr;
                                    $detalles->id_item=$item;
                                    $detalles->id_bodega=$idbodega;
                                    $detalles->idbodprod=$Prodbod->idbodprod;
                                    $detalles->cantidad=$cantidad_item;
                                    $detalles->precio=number_format(($Prodbod->precio),4,'.', '');
                                    $detalles->descuento=0;
                                    $detalles->total=number_format(($total_item),4,'.', '');
                                    $detalles->iva=0;
                                    $detalles->fecha=date('Y-m-d H:i:s');
                                    $detalles->save(); 

                                    $ultimo=PedidoBodegaGral::orderBy('idpedido_bod_gral','desc')->first();
                                    if(is_null($ultimo)){
                                        $suma=1;
                                    }else{
                                        $suma=$ultimo->idpedido_bod_gral+1;
                                    }

                                    //registramos el pedido
                                    $pedido_temp=new PedidoBodegaGral();
                                    $pedido_temp->idpedido_bod_gral=$suma;
                                    $pedido_temp->iddetallecomprobante=$detalles->iddetalle_comprobante;

                                    $pedido_temp->lote=$Prodbod->lote->lote;
                                    $pedido_temp->fecha_elabora=$Prodbod->lote->felabora;
                                    $pedido_temp->fecha_caducidad=$Prodbod->lote->fcaduca;

                                    // $pedido_temp->lote=$Prodbod->existencias->lote;
                                    // $pedido_temp->fecha_caducidad=$Prodbod->existencias->fecha_caducidad;
                                    // $pedido_temp->fecha_elabora=$Prodbod->existencias->fecha_elaboracion;

                                    $pedido_temp->cantidad_pedida=$cantidad_item;
                                    $pedido_temp->idbodega=$idbodega;
                                    $pedido_temp->id_solicita=$paquete->id_solicita;
                                    $pedido_temp->fecha_solicita=date('Y-m-d H:i:s');
                                    $pedido_temp->idbodpro=$Prodbod->idbodprod;
                                    $pedido_temp->iditem=$item;
                                    $pedido_temp->id_paquete=$paquete->idpaquete;
                                    $pedido_temp->estado="Solicitado";
                                    $pedido_temp->save();  
                                    
                                    $total_comprobante=$total_comprobante +  $total_item;

                                    break;
                                }
            
                            }else{
                                
                                if($data->existencia_red>0){
                                    // $Prodbod =ProductoBodega::with('existencias')->where('idbodprod',$data->idbodprod)
                                    // ->where('idprod',$item)
                                    // ->first();

                                    $Prodbod =ProductoBodega::with('lote')->where('idbodprod',$data->idbodprod)
                                    ->where('idprod',$item)
                                    ->first();
                                
                                    $nuevoStock=$Prodbod->existencia;
                                    $nuevoStock_act=$nuevoStock - $cantidad_item;
                                    if($nuevoStock_act<0){
                                        $nuevoStock_act=0;
                                    }
                
                                    //registramos los detalles
                                    $ultimo=DetalleComprobante::orderBy('iddetalle_comprobante','desc')->first();
                                    if(is_null($ultimo)){
                                        $suma=1;
                                    }else{
                                        $suma=$ultimo->iddetalle_comprobante+1;
                                    }
                                
                                    $total_item=0;
                                    $total_item=$Prodbod->precio * $nuevoStock;
                                    $detalles=new DetalleComprobante();
                                    $detalles->iddetalle_comprobante=$suma;
                                    $detalles->idcomprobante=$compr;
                                    $detalles->id_item=$item;
                                    $detalles->id_bodega=$idbodega;
                                    $detalles->idbodprod=$Prodbod->idbodprod;
                                    $detalles->cantidad=$nuevoStock;
                                    $detalles->precio=number_format(($Prodbod->precio),4,'.', '');
                                    $detalles->descuento=0;
                                    $detalles->total=number_format(($total_item),4,'.', '');
                                    $detalles->iva=0;
                                    $detalles->fecha=date('Y-m-d H:i:s');
                                    $detalles->save(); 

                                    $ultimo=PedidoBodegaGral::orderBy('idpedido_bod_gral','desc')->first();
                                    if(is_null($ultimo)){
                                        $suma=1;
                                    }else{
                                        $suma=$ultimo->idpedido_bod_gral+1;
                                    }

                                    //registramos el pedido
                                    $pedido_temp=new PedidoBodegaGral();
                                    $pedido_temp->idpedido_bod_gral=$suma;
                                    $pedido_temp->iddetallecomprobante=$detalles->iddetalle_comprobante;

                                    $pedido_temp->lote=$Prodbod->lote->lote;
                                    $pedido_temp->fecha_elabora=$Prodbod->lote->felabora;
                                    $pedido_temp->fecha_caducidad=$Prodbod->lote->fcaduca;
                                    
                                    // $pedido_temp->lote=$Prodbod->existencias->lote;
                                    // $pedido_temp->fecha_caducidad=$Prodbod->existencias->fecha_caducidad;
                                    // $pedido_temp->fecha_elabora=$Prodbod->existencias->fecha_elaboracion;
                                    $pedido_temp->cantidad_pedida=$nuevoStock;
                                    $pedido_temp->idbodega=$detalles->id_bodega;
                                    $pedido_temp->id_solicita=$paquete->id_solicita;
                                    $pedido_temp->fecha_solicita=date('Y-m-d H:i:s');
                                    $pedido_temp->idbodpro=$Prodbod->idbodprod;
                                    $pedido_temp->iditem=$item;
                                    $pedido_temp->id_paquete=$paquete->idpaquete;
                                    $pedido_temp->estado="Solicitado";
                                    $pedido_temp->save();
                                    
                                    $cantidad_item=$cantidad_item - $Prodbod->existencia;

                                    $total_comprobante=$total_comprobante +  $total_item;
                                }
                            }
                        }

                    }
                    return (['total_comprobante'=>$total_comprobante,'error'=>false]); 
                }

            } catch (\Throwable $e) {
                DB::connection('pgsql')->rollback();
                Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
                return (['mensaje'=>'Ocurrió un error,intentelo más tarde','error'=>true]); 
            }
        });
        return ($transaction);
        
    }

    //ENTREGA PAQUETES DESDE CENTRO QUIRURGICO A FARMACIA
    public function validarEntrega(Request $request){
       
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
                        $ProductoBodegaDialisis->idbodega=32;
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
                        $existenciaDialisis->cod="IABCQ";
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