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
use App\Models\Bodega\EntregaPaquete;
use App\Models\Personal\Funcionario;
use App\Models\Bodega\KardexCabDialisis;
use App\Models\Bodega\KardexDetDialisis;
use App\Models\User;
use App\Models\Personal\Area;
use Hash;
use App\Models\Personal\UsuarioPerfil;
use App\Models\Personal\Perfil;
use Illuminate\Support\Facades\Auth;

class SolicitaDialisisController extends Controller
{
    public function solicitarPaquete($idpersona, $idm){
      
        $paquetes=DB::connection('pgsql')->table('bodega.paquetes')
        ->where('estado','A')
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

        return view('gestion_dialisis.solicita_farmacia',[
            "paquetes"=>$paquetes,
            "paciente"=>$paciente,
            "responsable"=>$responsable,
            "url"=>$url,
            "area_"=>"D"
        ]);
    }

    public function solicitarPaqueteHD($idpersona, $idm){
      
        $paquetes=DB::connection('pgsql')->table('bodega.paquetes')
        ->where('estado','A')
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

        return view('gestion_dialisis.solicita_farmacia',[
            "paquetes"=>$paquetes,
            "paciente"=>$paciente,
            "responsable"=>$responsable,
            "url"=>$url,
            "area_"=>"HD"
        ]);
    }

    //carga combo de actividades
    public function cargaCie10(Request $request){
    
        $data = [];
        if($request->has('q')){
            $search = $request->q;
            $data=DB::connection('pgsql')->table('esq_rdacaa.cie10')
            ->where('cie10_descripcion', 'ilike', '%'.$search.'%')
            ->orWhere('cie10_codigo', 'ilike', '%'.$search.'%')
            ->take(10)->get();
        }
        
        return response()->json($data);
    }

    public function guardarUsuario($cedula, $password, $idbodega){
       
        $transaction=DB::transaction(function() use($cedula, $password, $idbodega){
            try{ 
                $dialisis_area=DB::table('bodega.area')
                ->where('descripcion','DIALISIS')
                ->where('estado','A')
                ->first();

                $area_func= Funcionario::where('ci',$cedula)
                ->where('estado',1)->first();
                $area_func->id_area=$dialisis_area->id_area;
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
                        if(is_null($bodega_user)){
                            DB::Rollback();
                            return [
                                "error"=>true,
                                "mensaje"=>"Usted no tiene acceso a realizar esta solicitud"
                            ];
                        }
                           
                        return [
                            "error"=>false,
                            "existe"=>"S",
                            "idusuario"=>$existe_user->id,
                            "id_area"=>$dialisis_area->id_area
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
                                if(is_null($bodega_user)){
                                    DB::Rollback();
                                    return [
                                        "error"=>true,
                                        "mensaje"=>"Usted no tiene acceso a realizar esta solicitud"
                                    ];
                                }

                                return [
                                    "error"=>false,
                                    "mensaje"=>"Cuenta creada exitosamente",
                                    "idusuario"=>$existe_user->id,
                                    "id_area"=>$dialisis_area->id_area
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
                                if(is_null($bodega_user)){
                                    DB::Rollback();
                                    return [
                                        "error"=>true,
                                        "mensaje"=>"Usted no tiene acceso a realizar esta solicitud"
                                    ];
                                }

                                return [
                                    "error"=>false,
                                    "mensaje"=>"Cuenta creada exitosamente",
                                    "idusuario"=>$existe_user->id,
                                    "id_area"=>$dialisis_area->id_area
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
                        if(is_null($bodega_user)){
                            DB::Rollback();
                            return [
                                "error"=>true,
                                "mensaje"=>"Usted no tiene acceso a realizar esta solicitud"
                            ];
                        }

                        return [
                            "error"=>false,
                            "mensaje"=>"Cuenta creada exitosamente",
                            "idusuario"=>$Usuario->id,
                            "id_area"=>$dialisis_area->id_area
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
                Log::error('SolicitaDialisisController => guardarUsuario => mensaje => '.$e->getMessage().' linea => '.$e->getLine());
                return [
                    'error'=>true,
                    'mensaje'=>'Ocurrió un error'
                ];
                
            }
        });
        return $transaction;
    }

    //SOLICITUD PAQUETES DESDE DIALISIS A FARMACIA
    public function guardarSolicitud(Request $request){
       
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
                //verificamos si esta creado el usuario
                $creaUser=$this->guardarUsuario($request->cedula_responsable, $request->password, 31);
                if($creaUser["error"]==true){
                    return (['mensaje'=>$creaUser["mensaje"],'error'=>true]);
                }

                //si existe un pedido pendiente no permitimos realizar la entrega
                $existe_pedido_pendiente=Comprobante::where('idtipo_comprobante',18)
                ->where('codigo_old','PedidoAFarm')
                ->where('estado','Activo')
                ->where('id_paciente',$request->id_paciente)
                ->first();
               
                if(!is_null($existe_pedido_pendiente)){
                    return (['mensaje'=>'Existe un pedido pendiente de retirar para el paciente seleccionado','error'=>true]);
                }

                //validamos que solo pueda solicitar un paquete en el dia
                $paquteDia=Comprobante::where('idtipo_comprobante',18)
                ->whereIn('codigo_old',['PedidoAFarm','Entregado'])
                ->where('estado','Activo')
                ->where('id_paciente',$request->id_paciente)
                ->whereDate('fecha_uso_item',$request->fecha_uso)
                ->first();
                
                if(!is_null($paquteDia)){
                    // return (['mensaje'=>'Ya existe un paquete en el dia para el paciente seleccionado','error'=>true]);
                }
              
                             
                $total_comprobante=0;
                $tipocomp_old= TipoComprobanteOld::where('idtipocom',18)//TRANSFERENCIA PAQUTE
                ->orderBy('idtipocom','desc')
                ->first();

                if($request->area_=="HD"){
                    $valida_area=Area::where('descripcion', 'HOSPITAL DEL DIA')
                    ->where('estado','A')
                    ->first();
                    if(is_null($valida_area)){
                        $guarda_area=new Area();
                        $guarda_area->descripcion="HOSPITAL DEL DIA";
                        $guarda_area->administrativo=$request->es_admin;
                        $guarda_area->estado="A";
                        $idarea_comp=$valida_area->id_area;
                    }else{
                        $idarea_comp=$valida_area->id_area;
                    }

                  
                }else{
                    $idarea_comp=$creaUser["id_area"];
                }

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
                $comprobante->idbodega=31;
                $comprobante->observacion=$request->motivo;             
                $comprobante->id_usuario_ingresa=$creaUser["idusuario"];
                $comprobante->area=$idarea_comp;
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
                        $validaPaq=$this->validaPaquete($idpaq, $cant[$key]);
                       
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
                                   
                                    DB::connection('pgsql')->rollback();
                                    return (['mensaje'=>'El stock del '.$request->nombrepaquete[$key].' del item '.$nomb_item.' es '.$stock_max.' y el solicitado es '.$cant[$key] * $detalle->cantidad,'error'=>true ,'idpaquete'=>$idpaq,'paquete'=>$request->nombrepaquete[$key]]);
                                
                                }else{

                                    if($detalle->id_item >=30000){
                                        //insumo dialisis
                                        $idbodega_sel=21;
                                    }else{
                                        //medicina dialisis
                                        $idbodega_sel=20;
                                    }

                                    if($detalle->id_item >=30000){
                                        $stock_max=$detalle->stock_farm_ins;
                                        $nomb_item=$detalle->descripcion_ins;
                                    }else{ //medicina
                                        $stock_max=$detalle->stock_farm_med;
                                        $nomb_item=$detalle->descripcion_med;
                                    }
                                    
                                    //registramos los detalles
                                    $ultimo=EntregaPaquete::orderBy('identrega_paquete','desc')->first();
                                    if(is_null($ultimo)){
                                        $suma=1;
                                    }else{
                                        $suma=$ultimo->identrega_paquete+1;
                                    }
                                    //detalle de cada uno de los items x paquetes
                                    $entrega=new EntregaPaquete();
                                    $entrega->identrega_paquete=$suma;
                                    $entrega->idpaquete=$idpaq;
                                    $entrega->desc_paquete=$nomb_paq[$key];
                                    $entrega->iddetalle_paq=$detalle->iddetalle_paq;
                                    $entrega->id_item=$detalle->id_item;
                                    $entrega->idbodega=$idbodega_sel;
                                    $entrega->nombre_item=$nomb_item;
                                    $entrega->tipo=$detalle->tipo;
                                    $entrega->cantidad=$detalle->cantidad_solic;
                                    $entrega->fecha_solicitud=date('Y-m-d H:i:s');
                                    $entrega->id_solicita=$creaUser["idusuario"];
                                    $entrega->cantidad_paquete=$cant[$key];
                                    $entrega->idcomprobante=$comprobante->idcomprobante;
                                    $entrega->save();
                                    
                                  
                                }

                            }
                        }
                       
                    }

                    $datosPa=EntregaPaquete::where("idcomprobante", $entrega->idcomprobante)->get();
                    $lista_final_agrupada=[];
                    foreach ($datosPa as $key => $item){                
                        if(!isset($lista_final_agrupada[$item->id_item])) {
                            $lista_final_agrupada[$item->id_item]=array($item);
                     
                        }else{
                            array_push($lista_final_agrupada[$item->id_item], $item);
                        }
                     }

                    foreach($lista_final_agrupada as $i=>  $dat){
                        $paqueteLote=$this->validaStock($i, $entrega->idcomprobante);
                        if($paqueteLote['error']==true){
                            DB::connection('pgsql')->rollback();
                           
                            return (['mensaje'=>$paqueteLote['mensaje'],'error'=>true]); 
                        }
                    }
                    
                    // $total_comprobante=$paqueteLote['total_comprobante'];

                   
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

    public function validaPaquete($id, $cantidad){
      
        $valida_paquetes=DB::table('bodega.detalle_paquetes as de')
        ->leftJoin('bodega.medicamentos as m', 'm.coditem','de.id_item')
        ->leftJoin('bodega.insumo as i', 'i.codinsumo','de.id_item')
        ->where('id_paquete',$id)
        ->where('estado','A')
        ->select('de.iddetalle_paq','de.id_item','de.tipo','m.stock_farm_dialisis as stock_farm_med','i.stock_farm_dialisis as stock_farm_ins',DB::raw("CONCAT(m.nombre,' - ', m.concentra,' - ', m.forma,' - ', m.presentacion) AS descripcion_med"),DB::raw("CONCAT(i.insumo) AS descripcion_ins"),'de.cantidad')
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

    public function validaStock($id_item, $compr){

        $datosPa=EntregaPaquete::where("id_item", $id_item)
        ->where("idcomprobante",$compr)
        ->select(DB::raw('sum("cantidad") as cantidad'),'id_item','idbodega')
        ->groupby('id_item','idbodega')
        ->first();  
                    
        
        $cantidad=$datosPa->cantidad;
        $item=$datosPa->id_item;
        $idbodega=$datosPa->idbodega;
    
        if($item>=30000){
            $stockInsumo=DB::table('bodega.insumo')
            ->where('codinsumo',$item)
            ->where('stock_farm_dialisis','<',$cantidad)
            ->select('insumo as detalle','stock_farm_dialisis')
            ->first();
        
            
            if(!is_null($stockInsumo)){
                DB::connection('pgsql')->rollback();
                return (['mensaje'=>'El stock  del item '.$stockInsumo->detalle.' es '.$stockInsumo->stock_farm_dialisis.' y el solicitado es '.$cantidad,'error'=>true]);
            }
        }else{
            $stockInsumo=DB::table('bodega.medicamentos')
            ->where('coditem',$item)
            ->where('stock_farm_dialisis','<',$cantidad)
            ->select(DB::raw("CONCAT(nombre,' - ', concentra,' - ', forma) AS detalle"),'stock_farm_dialisis')
            ->first();

            if(!is_null($stockInsumo)){
                DB::connection('pgsql')->rollback();
                return (['mensaje'=>'El stock  del item '.$stockInsumo->detalle.' es '.$stockInsumo->stock_farm_dialisis.' y el solicitado es '.$cantidad,'error'=>true]);
            }
        }
        $fecha_Actual=date('Y-m-d');
        // $StockPB=DB::table('bodega.prodxbod as pb')
        // ->leftJoin('bodega.existencia as e', 'e.idbodprod','pb.idbodprod')
        // ->where('pb.idbodega',$idbodega)
        // ->where('existencia','>',0)
        // ->whereDate('e.fecha_caducidad','>=',$fecha_Actual)
        // ->where('idprod',$item)
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
            DB::connection('pgsql')->rollback();                                     
        
            return (['mensaje'=>'El stock insuficiente  ','error'=>true ]);
        }
        return (['mensaje'=>'ok  ','error'=>false ]);
    }

    public function solicitarInsumo($idpersona, $idm){

       
        $bodega= DB::connection('pgsql')->table('bodega.bodega')       
       
        ->whereIn("idbodega",[7,21])
        ->where('estado',1)
        ->get();

        $paciente= DB::connection('pgsql')->table('esq_pacientes.pacientes')
        ->where('id_paciente', $idpersona)
        ->first();
       
        $responsable= DB::connection('pgsql')->table('esq_datos_personales.personal as dp')
        ->leftJoin('public.users as usu', 'usu.id_persona','dp.idpersonal')
        ->where('idpersonal', $idm)
        ->first();
           
        $user = Auth::loginUsingId($responsable->id);
               
        $url=DB::connection('pgsql')->table('bodega.parametro')
        ->where('estado', 'A')
        ->where('descripcion', 'Url')
        ->first();

        return view('gestion_dialisis.solicita_insumo',[
            "bodega"=>$bodega,
            "paciente"=>$paciente,
            "responsable"=>$responsable,
            "url"=>$url,
            "area_"=>"D"
        ]);
    }

    public function solicitarInsumoHD($idpersona, $idm){

       
        $bodega= DB::connection('pgsql')->table('bodega.bodega')       
       
        ->whereIn("idbodega",[7,21])
        ->where('estado',1)
        ->get();

        $paciente= DB::connection('pgsql')->table('esq_pacientes.pacientes')
        ->where('id_paciente', $idpersona)
        ->first();
       
        // $responsable= DB::connection('pgsql')->table('esq_datos_personales.personal')
        // ->where('idpersonal', $idm)
        // ->first();

        $responsable= DB::connection('pgsql')->table('esq_datos_personales.personal as dp')
        ->leftJoin('public.users as usu', 'usu.id_persona','dp.idpersonal')
        ->where('idpersonal', $idm)
        ->first();
           
        $user = Auth::loginUsingId($responsable->id);

        $url=DB::connection('pgsql')->table('bodega.parametro')
        ->where('estado', 'A')
        ->where('descripcion', 'Url')
        ->first();

        return view('gestion_dialisis.solicita_insumo',[
            "bodega"=>$bodega,
            "paciente"=>$paciente,
            "responsable"=>$responsable,
            "url"=>$url,
            "area_"=>"HD"
        ]);
    }

    

     //SOLICITUD INSUMO DESDE DIALISIS A FARMACIA
    public function guardarSolicitudInsumo(Request $request){
      
        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{
                //verificamos si esta creado el usuario
                $creaUser=$this->guardarUsuario($request->cedula_responsable, $request->password,$request->cmb_bodega);
                if($creaUser["error"]==true){
                    return (['mensaje'=>$creaUser["mensaje"],'error'=>true]);
                }

                if($request->cmb_bodega==21){
                    //bodega insumos dialisis
                    $tipo_comp=19;
                }else if($request->cmb_bodega==7){
                    //bodega ins gral
                    $tipo_comp=20;
                }else{
                    return (['mensaje'=>'No se encontro tipo comprobante para la bodega seleccionada','error'=>true]); 
                }
                             
                $total_comprobante=0;
                $tipocomp_old= TipoComprobanteOld::where('idtipocom',$tipo_comp)//TRANSFERENCIA PAQUTE
                ->orderBy('idtipocom','desc')
                ->first();

                //registramos la cabecera
                $ultimo=Comprobante::orderby('idcomprobante','desc')->first();
                if(is_null($ultimo)){
                    $suma=1;
                }else{
                    $suma=$ultimo->idcomprobante+1;
                }

                if($request->area_=="HD"){
                    $valida_area=Area::where('descripcion', 'HOSPITAL DEL DIA')
                    ->where('estado','A')
                    ->first();
                    if(is_null($valida_area)){
                        $guarda_area=new Area();
                        $guarda_area->descripcion="HOSPITAL DEL DIA";
                        $guarda_area->administrativo=$request->es_admin;
                        $guarda_area->estado="A";
                        $idarea_comp=$valida_area->id_area;
                    }else{
                        $idarea_comp=$valida_area->id_area;
                    }

                  
                }else{
                    $idarea_comp=$creaUser["id_area"];
                }
                $comprobante=new Comprobante();
                $comprobante->idcomprobante=$suma;
                $comprobante->idtipo_comprobante=$tipocomp_old->idtipocom;
                $comprobante->secuencial=$tipocomp_old->numcom+1;
                $comprobante->descripcion=$tipocomp_old->razoncom;
                $comprobante->fecha_hora=date('Y-m-d H:i:s');
                $comprobante->fecha=date('Y-m-d');
                $comprobante->idbodega=$request->cmb_bodega;
                $comprobante->observacion=$request->motivo;             
                $comprobante->id_usuario_ingresa=$creaUser["idusuario"];
                $comprobante->area=$idarea_comp;
                $comprobante->id_paciente=$request->id_paciente;
                $comprobante->id_cie10=$request->cie_10;
                $comprobante->fecha_uso_item=$request->fecha_uso;
                $comprobante->tipoarea="Hospitalizacion";

                if($comprobante->save()){

                    //datos detalle
                    $idmedicina_selecc=$request->idmedicina_selecc;
                    $idbodega_selecc=$request->idbodega_selecc; 
                    $cantidad=$request->cantidad;
                    $precio=$request->precio;
                   
                    $fecha_elab_=$request->fecha_elab_;
                    $fecha_caduc=$request->fecha_caduc;
                    $lote=$request->lote;
                    $reg_sani=$request->reg_sani;                    
                    $total=$request->total;         
                    
                    $idbodega_producto=$request->idbodega_producto;
                  
                    $cont=0;
                    $total_comp=0;
                  
                    //registramos los detalles localmente
                    while($cont < count($idmedicina_selecc)){
                        $nuevoStock=0;
                        $nuevoStock_act=0;

                        $ultimo=DetalleComprobante::orderBy('iddetalle_comprobante','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->iddetalle_comprobante+1;
                        }
                        $total_item=0;
                        $total_item=$precio[$cont] * $cantidad[$cont];
                        $detalles=new DetalleComprobante();
                        $detalles->iddetalle_comprobante=$suma;
                        $detalles->idcomprobante=$comprobante->idcomprobante;
                        $detalles->id_item=$idmedicina_selecc[$cont];
                        $detalles->id_bodega=$idbodega_selecc[$cont];
                        $detalles->cantidad=$cantidad[$cont];
                        $detalles->precio=number_format(($precio[$cont]),4,'.', '');
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
                        $pedido_temp=new PedidoBodegaGral();
                        $pedido_temp->idpedido_bod_gral=$suma;
                        $pedido_temp->iddetallecomprobante=$detalles->iddetalle_comprobante;
                        $pedido_temp->lote=$lote[$cont];
                        $pedido_temp->fecha_caducidad=$fecha_caduc[$cont];
                        $pedido_temp->fecha_elabora=$fecha_elab_[$cont];
                        $pedido_temp->cantidad_pedida=$cantidad[$cont];
                        $pedido_temp->idbodega=$detalles->id_bodega;
                        $pedido_temp->id_solicita=$comprobante->id_usuario_ingresa;
                        $pedido_temp->fecha_solicita=date('Y-m-d H:i:s');
                        $pedido_temp->idbodpro=$idbodega_producto[$cont];
                        $pedido_temp->estado="Solicitado";
                        $pedido_temp->save();                       
                        $cont=$cont+1;
                        $total_comp=$total_comp + $detalles->precio;
                    } 
                    
                  
                    $tipocomp_old->numcom=$comprobante->secuencial;
                    $tipocomp_old->save();

                    //si tofdo ok el comprobante se crea
                    $comprobante_crear=Comprobante::find($comprobante->idcomprobante);
                    $comprobante_crear->estado="Activo";
                    $comprobante_crear->subtotal=$total_comp;
                    $comprobante_crear->total=$total_comp;
                    $comprobante_crear->codigo_old="PedidoAFarm";//pedido
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

    public function vistaAdministracionMed($idpac, $idres){
       
        $paciente= DB::connection('pgsql')->table('esq_pacientes.pacientes')
        ->where('id_paciente', $idpac)
        ->first();
       
        $responsable= DB::connection('pgsql')->table('esq_datos_personales.personal')
        ->where('idpersonal', $idres)
        ->first();

        $fecha_actu=date('Y-m-d');
        
        $medicina=DB::table('bodega.detalle_comprobante as d')
		->leftJoin('bodega.comprobante as c', 'c.idcomprobante','d.idcomprobante')
        ->leftJoin('bodega.medicamentos as m', 'm.coditem','d.id_item')
		->select(DB::raw("CONCAT(m.nombre, ' ', m.concentra, ' ', m.forma, ' ', m.presentacion) as nombre_medicina"),'m.coditem as iditem')
		->where('c.id_paciente',$idpac)
        ->where('d.id_item','<', 30000)
        ->whereDate('c.fecha_uso_item', $fecha_actu)
        ->where(function($c){
            $c->where('c.codigo_old','EntregadoF')
            ->orwhere('c.codigo_old','Entregado');
        })
        ->where('c.area',40)
        ->distinct('m.coditem')
        ->get();
          
        if(sizeof($medicina)==0){
            $medicina=DB::table('inventario.prodnocb as p')
            ->leftJoin('inventario.detallecomprobante as dc', 'dc.idpncb','p.idpncb')
            ->leftJoin('inventario.comprobante as c', 'c.idcomprobante','dc.idcomprobante')
            ->select('p.prodncb as nombre_medicina','p.idpncb  as iditem')
            ->where('c.idtipocomprobante',2)
            ->where('c.idpaciente',$idpac)
            ->whereIn('c.estado',[2,9])
            ->whereDate('c.fechaing',$fecha_actu)
            ->distinct('p.prodncb')
            ->get();
           
        }else{
           
            $medicina=DB::table('bodega.detalle_comprobante as d')
            ->leftJoin('bodega.comprobante as c', 'c.idcomprobante','d.idcomprobante')
            ->leftJoin('bodega.medicamentos as m', 'm.coditem','d.id_item')
            ->select(DB::raw("CONCAT(m.nombre, ' ', m.concentra, ' ', m.forma, ' ', m.presentacion) as nombre_medicina"),'m.coditem as iditem')
            ->where('c.id_paciente',$idpac)
            ->where('d.id_item','<', 30000)
            ->whereDate('c.fecha_uso_item', $fecha_actu)
            ->where(function($c){
                $c->where('c.codigo_old','EntregadoF')
                ->orwhere('c.codigo_old','Entregado');
            })
            ->where('c.area',40)
            ->distinct('m.coditem');

            $medicina_fuera=DB::table('inventario.prodnocb as p')
            ->leftJoin('inventario.detallecomprobante as dc', 'dc.idpncb','p.idpncb')
            ->leftJoin('inventario.comprobante as c', 'c.idcomprobante','dc.idcomprobante')
            ->select('p.prodncb as nombre_medicina','p.idpncb  as iditem')
            ->where('c.idtipocomprobante',2)
            ->where('c.idpaciente',$idpac)
            ->whereIn('c.estado',[2,9])
            ->whereDate('c.fechaing',$fecha_actu)
            ->distinct('p.prodncb');
           
            $resultado = $medicina->union($medicina_fuera)->get();
            $medicina=$resultado;
            
        }
	
    

        $url=DB::connection('pgsql')->table('bodega.parametro')
        ->where('estado', 'A')
        ->where('descripcion', 'Url')
        ->first();

        return view('gestion_dialisis.administacion_medicina',[
            "paciente"=>$paciente,
            "medicina"=>$medicina,
            "responsable"=>$responsable,
            "url"=>$url
        ]);
    }

    public function infoMedicamento($id_paciente, $item){
        try{
            $fecha_actu=date('Y-m-d');
          
            $detalles = DB::table('inventario.detallecomprobante')->distinct('idbodprod')
            ->select('inventario.detallecomprobante.iddetalle', 'inventario.detallecomprobante.idbodprod', 'cantidad',
            DB::raw("CONCAT(nombre, ' ', concentra, ' ', forma, ' ', presentacion) as nombre_completo"),
            DB::raw("CASE
                WHEN frec = 0 THEN '------'
                WHEN frec = 1 THEN '24 HORAS'
                WHEN frec = 2 THEN '12 HORAS'
                WHEN frec = 3 THEN '8 HORAS'
                WHEN frec = 4 THEN '6 HORAS'
                WHEN frec = 6 THEN '4 HORAS'
                WHEN frec = 12 THEN '2 HORAS'
                WHEN frec = 7 THEN '7 DIAS'
                WHEN frec = 48 THEN '48 HORAS'
                WHEN frec = 72 THEN '72 HORAS'
                ELSE ''
            END as frec"),
            DB::raw("CASE
                WHEN duracion = 0 THEN '------'
                WHEN duracion = 1 THEN '1 DIA'
                ELSE CONCAT(duracion, ' DIAS')
            END as duracion"),
            'uso',
            'inventario.detallereceta.dosis'
            )
            ->join('bodega.medicamentos', 'inventario.detallecomprobante.idbodprod', '=', 'bodega.medicamentos.coditem')
            ->join('inventario.detallereceta', 'inventario.detallecomprobante.iddetalle', '=', 'inventario.detallereceta.iddetalle')
            ->whereIn('inventario.detallecomprobante.idcomprobante', function ($query) use ($id_paciente, $fecha_actu, $item) {
                $query->select('idcomprobante')
                    ->from('inventario.comprobante')
                    ->where('idtipocomprobante', 2)
                    // ->where('idpre', $id_internacion)
                    ->where('idpaciente',$id_paciente)
                    ->whereDate('fechaing', $fecha_actu)
                    ->where('idbodprod', $item)
                    ->where('estado', '9');
            })
            ->first();
            return (['resultado'=>$detalles,'error'=>false]);
        } catch (\Throwable $e) {
            Log::error(__CLASS__." => ".__FUNCTION__." => Mensaje =>".$e->getMessage()." Linea =>".$e->getLine());
            return (['mensaje'=>'Ocurrió un error,intentelo más tarde','error'=>true]); 
        }
    }

    public function guardarMedicacion(Request $request){
       
        $transaction=DB::connection('pgsql')->transaction(function() use ($request){
            try{

                $func= Funcionario::where('ci',$request->cedula_responsable)
                ->where('estado',1)->first();
               
                //valida contraseña
                $pass=md5($request->password);
               
                if($func->clave != $pass){
                    return [
                        "error"=>true,
                        "mensaje"=>"Contraseña incorrecta"
                    ];
                }

                $ultimo=KardexCabDialisis::orderBy('id_registro','desc')->first();
                if(is_null($ultimo)){
                    $suma=1;
                }else{
                    $suma=$ultimo->id_registro+1;
                }
                $guardarCabecera=new KardexCabDialisis();
                $guardarCabecera->id_registro=$suma;
                $guardarCabecera->fecha_registro=date('Y-m-d H:i:s');
                $guardarCabecera->id_responsable=$request->id_responsable;
                $guardarCabecera->id_paciente=$request->id_paciente;
                $guardarCabecera->eliminado=="N";

                if($guardarCabecera->save()){
                    //datos detalle
                    $idmedicina_selecc=$request->idmedicina_selecc;
                    $hora_selecc=$request->hora_selecc; 
                    $via_Adm=$request->adminis_selecc;
                    $dosis=$request->dosis_selecc;
                    $frecuencia=$request->frec_selecc;
                    $medicina=$request->med_selecc;
                    $observa=$request->observacion_selecc;
                    $cont=0;
                    while($cont < count($idmedicina_selecc)){
                       
                        $ultimo=KardexDetDialisis::orderBy('id_registro','desc')->first();
                        if(is_null($ultimo)){
                            $suma=1;
                        }else{
                            $suma=$ultimo->id_registro+1;
                        }
                       
                        $detalles= new KardexDetDialisis();
                        $detalles->id_registro=$suma;
                        $detalles->idcabecera=$guardarCabecera->id_registro;
                        $detalles->id_medicamento=$idmedicina_selecc[$cont];
                        $detalles->hora=$hora_selecc[$cont];
                        $detalles->check=1;
                        $detalles->via_admin=$via_Adm[$cont];
                        $detalles->dosis=$dosis[$cont];
                        $detalles->frecuencia=$frecuencia[$cont];
                        $detalles->nprod=$medicina[$cont];
                        $detalles->observacion=$observa[$cont];
                        $detalles->save(); 
                        
                        $cont=$cont+1;

                    } 
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