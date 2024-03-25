<?php

namespace App\Http\Controllers\Nacional;
use App\Http\Controllers\Controller;
use App\Models\Personal\Especialidad;
use \Log;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator; 
use App\Models\Bodega\Comprobante; 
use App\Models\Bodega\DetalleComprobante; 
use App\Models\Bodega\Existencia; 
use App\Models\Bodega\Medicamento; 
use App\Models\Bodega\TipoComprobante;
use App\Models\Bodega\LoteProducto;
use App\Models\Bodega\ProductoBodega;
use App\Models\Bodega\Insumo;
use App\Models\Bodega\Item;
use App\Models\Bodega\Laboratorio;
use Storage;
use SplFileInfo;
use DateTime;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use DateTimeZone;

class InterOperabilidadController extends Controller
{   
    private $clientNacional = null;
    public function __construct(){
        try{
            $datosNacionalIp=DB::table('bodega.datos_endpoint')
            ->where('nombre', 'IP')
            ->select('valor')
            ->first();
        
            $this->clientNacional = new Client([
                'base_uri' =>$datosNacionalIp->valor,
                'verify' => false,
            ]);
        }catch(Exception $e){
            Log::error($e->getMessage());
        }
    }

    public function vistaOperabilidad(){

        $bodega= DB::connection('pgsql')->table('bodega.bodega')
        ->where('idtipobod',1)
        ->where('estado',1)
        ->get();

        return view('gestion_bodega.interoperabilidad',[
            "bodega"=>$bodega
        ]);
    }

    public function buscarStockBodega($bodega){
        try{
           
            $medicamentos=[];
            if($bodega=="M"){
                $medicamentos= DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
                ->leftJoin('bodega.medicamentos as med', 'proxbode.idprod','med.coditem')
                ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                ->where('bodega.idtipobod',1) // BODEGA 2
                ->where('proxbode.tipoprod',1) //MEDICAMENTOS
                ->whereIn('proxbode.idbodega',[1,17])
                ->where('proxbode.existencia','>',0)

                // ->where('med.cum','A11GA01LPR088A8')

                ->select('lot.lote', 'lot.fcaduca', DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion) AS detalle"), DB::raw("CONCAT(med.nombre,' ', med.concentra,' ', med.forma,' ', med.presentacion) AS detalle1"),'proxbode.idprod', 'proxbode.precio', 'lot.felabora','med.cum as codigo_item',DB::raw('sum("existencia") as existencia'))
                ->groupBy('lot.lote', 'lot.fcaduca','proxbode.idprod', 'proxbode.precio', 'lot.felabora','med.nombre', 'med.concentra','med.forma','med.presentacion','med.coditem','codigo_item','proxbode.idprod')
                ->distinct('codigo_item','fcaduca','felabora','precio','lote')             
                ->get();   


                 // $medicamentos= DB::connection('pgsql')->table('bodega.medicamentos as med')
                // ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','med.coditem')
                // ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                // ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                // ->where('bodega.idtipobod',1) // BODEGA 2
                // ->where('proxbode.tipoprod',1) //MEDICAMENTOS
                // ->whereIn('proxbode.idbodega',[1,17])
                // ->where('proxbode.existencia','>',0)
                // ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(med.nombre,' - ', med.concentra,' - ', med.forma,' - ', med.presentacion,' [', med.coditem,']',' [', proxbode.idbodprod,']') AS detalle"), DB::raw("CONCAT(med.nombre,' ', med.concentra,' ', med.forma,' ', med.presentacion) AS detalle1"),'proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','med.cum as codigo_item','proxbode.tipoprod' )

                // ->groupBy('lot.lote', 'lot.fcaduca','lot.regsan','proxbode.idprod', 'proxbode.precio', 'lot.felabora','med.nombre', 'med.concentra','med.forma','med.presentacion','med.coditem','codigo_item','proxbode.tipoprod')
                // ->distinct('codigo_item','fcaduca','felabora','precio','lote')
               
                // // ->take(3) 
                // ->get();   
             

            }elseif($bodega=="I"){

                $medicamentos= DB::connection('pgsql')->table('bodega.prodxbod as proxbode')
                ->leftJoin('bodega.insumo as insu', 'proxbode.idprod','insu.codinsumo')
                ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                ->where('bodega.idtipobod',1) // BODEGA 2
                ->where('proxbode.tipoprod',2) //INSUMPS
                ->whereIn('proxbode.idbodega',[2,18])
                ->where('proxbode.existencia','>',0)
                ->select('lot.lote', 'lot.fcaduca', DB::raw("CONCAT(insu.insumo) AS detalle"), DB::raw("CONCAT(insu.insumo) AS detalle1"),'proxbode.idprod', 'proxbode.precio', 'lot.felabora','insu.cudim as codigo_item',DB::raw('sum("existencia") as existencia'))
                ->groupBy('lot.lote', 'lot.fcaduca','proxbode.idprod', 'proxbode.precio', 'lot.felabora','insu.insumo','insu.codinsumo','codigo_item','proxbode.idprod')
                ->distinct('codigo_item','fcaduca','felabora','precio','lote')             
                ->get();   

               
                // $medicamentos= DB::connection('pgsql')->table('bodega.insumo as insu')
                // ->leftJoin('bodega.prodxbod as proxbode', 'proxbode.idprod','insu.codinsumo')
                // ->leftJoin('bodega.lotexprod as lot', 'lot.idbodp','proxbode.idbodprod')
                // ->leftJoin('bodega.bodega as bodega', 'bodega.idbodega','proxbode.idbodega')
                // ->where('bodega.idtipobod',1) // BODEGA 1
                // ->where('proxbode.tipoprod',2) //INSUMPS
                // ->whereIn('proxbode.idbodega',[2,18])
                // ->where('proxbode.existencia','>',0)
                // ->select('lot.lote', 'lot.fcaduca','lot.regsan', DB::raw("CONCAT(insu.insumo,' [', insu.codinsumo,']',' [', proxbode.idbodprod,']') AS detalle"), 'insu.insumo AS detalle1','proxbode.existencia','proxbode.idprod', 'proxbode.precio', 'lot.felabora','proxbode.idbodprod','insu.cudim as codigo_item','proxbode.tipoprod')  
                         
                // ->get();



               
            }

            return[
                'error'=>false,
                'resultado'=>$medicamentos
            ];
               
        }catch (\Throwable $e) {
            Log::error('InterOperabilidadController => buscarStockBodega => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return[
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ];
            
        }
    }

    public function credenciales(){
        try{
           
            $datosNacionalCredenciales=DB::table('bodega.datos_endpoint')
            ->whereIn('nombre', ['USUARIO','PASSWORD'])
            ->select('valor')
            ->get();
            $user=$datosNacionalCredenciales[0]->valor;            
            $password=$datosNacionalCredenciales[1]->valor;

            return [
                'error'=>false,
                'user'=>$user,
                'password'=>$password,
            ];
        }catch (\Throwable $e) {
            Log::error('InterOperabilidadController => credenciales => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return[
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ];
            
        }
    }

    //generar un token
    public function token(){
        $consultaCredenciales=$this->credenciales();
        if($consultaCredenciales['error']==true){
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
        }
       
        $user=$consultaCredenciales['user'];
        $password=$consultaCredenciales['password'];
        $dataCredenciales=["usuario"=>$user, "contraseña"=>$password];
    	$resultado = $this->clientNacional->request('POST', "auth/login",[
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($dataCredenciales)
        ]);

        $resultado=json_decode((string) $resultado->getBody());
    
        return $resultado->access_token;
    }
    
    public function saldoTarea($bodega){
        try{
            $consultaStock=$this->buscarStockBodega($bodega);
            if($consultaStock['error']==true){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'Ocurrió un error, intentelo más tarde'
                ]);
            }          
            if(sizeof($consultaStock['resultado'])==0){
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'No se encontro informacion'
                ]);
            }
            $productos_json=[];
            $json_lote=[];

           
            foreach($consultaStock['resultado'] as $data){
                $sku=$data->codigo_item;
                $cantidad=$data->existencia;
                $costo=number_format(($data->precio),2,'.', '');                
                $lotes=$data->lote;
                $felabora=$data->felabora;
                $fcaduca=$data->fcaduca;
                array_push($productos_json,["sku"=>$sku, "cantidad"=>$cantidad, "costo"=>$costo, "lotes"=>$lotes, "consumo"=>0, "fechaElaboracion"=>$felabora, "fechaCaducidad"=>$fcaduca]);               
            }

            $agrupadoPorSku = array_reduce($productos_json, function($resultado, $item) {
                $sku = $item['sku'];
                $resultado[$sku][] = $item;
                return $resultado;
            }, array());

           

            $agrupadoPorSkuYLote = array_map(function($sku) {
                $agrupado = array_reduce($sku, function($resultado, $item) {
                    $lotes = $item['lotes'];
                    $resultado[$lotes][] = $item;
                    return $resultado;
                }, array());
                return $agrupado;
            }, $agrupadoPorSku); 

          
            $lista=[];
            foreach($agrupadoPorSkuYLote as $datos){               
                $cantidad_item=0;
                $lote_json=[];
                $array=[];
                $sku_aux="";
                $suma=0;
              
                $cont=0;
                foreach($datos as $key => $valor){     
                   
                    foreach($valor as $item){
                       

                        $sku_aux=$item['sku']."-000".$suma+1;
                        $cantidad_item=$cantidad_item + $item['cantidad'];
                        $cantidad_item=intval($cantidad_item);
                        $costo=floatval($item['costo']);

                        // array_push($array, ["numero"=>$valor[0]['lotes'], "cantidad"=>intval($valor[0]['cantidad']), "fechaElaboracion"=>$valor[0]['fechaElaboracion'], "fechaCaducidad"=>$valor[0]['fechaCaducidad']]);

                        array_push($array, ["numero"=>$item['lotes'], "cantidad"=>intval($item['cantidad']), "fechaElaboracion"=>$item['fechaElaboracion'], "fechaCaducidad"=>$item['fechaCaducidad']]);


                        // $sku_aux=$valor[$cont]['sku']."-000".$suma+1;
                        // $cantidad_item=$cantidad_item + $valor[$cont]['cantidad'];
                        // $cantidad_item=intval($cantidad_item);
                        // $costo=floatval($valor[$cont]['costo']);

                        // // array_push($array, ["numero"=>$valor[0]['lotes'], "cantidad"=>intval($valor[0]['cantidad']), "fechaElaboracion"=>$valor[0]['fechaElaboracion'], "fechaCaducidad"=>$valor[0]['fechaCaducidad']]);

                        // array_push($array, ["numero"=>$valor[$cont]['lotes'], "cantidad"=>intval($valor[$cont]['cantidad']), "fechaElaboracion"=>$valor[$cont]['fechaElaboracion'], "fechaCaducidad"=>$valor[$cont]['fechaCaducidad']]);

                    }
                        
                       

                        // $cont++;
                }               
                array_push($lista,["sku"=>$sku_aux,"cantidad"=>$cantidad_item,"costo"=>$costo,"consumo"=>4,'lotes'=>$array]);
            }
            // dd($lista);
          
            // $lista=[];
            // foreach($agrupa_lote as $datos){
            //     $cantidad_item=0;
            //     $lote_json=[];
            //     $array=[];
            //     $sku_aux="";
            //     foreach($datos as $key => $valor){
            //         $sku_aux=$datos[0]['sku']."-000".$key+1;
            //         $cantidad_item=$cantidad_item + $valor['cantidad'];
            //         $costo=floatval($datos[0]['costo']);
            //         array_push($array, ["numero"=>$valor['lotes'], "cantidad"=>$valor['cantidad'], "fechaElaboracion"=>$valor['fechaElaboracion'], "fechaCaducidad"=>$valor['fechaCaducidad']]);
            //     }
            //     // array_push($lista,["sku"=>$datos[0]['sku'],"cantidad"=>$cantidad_item,"costo"=>$costo,"consumo"=>$datos[0]['consumo'],'lotes'=>$array]);

            //     array_push($lista,["sku"=>$sku_aux,"cantidad"=>$cantidad_item,"costo"=>$costo,"consumo"=>$datos[0]['consumo'],'lotes'=>$array]);
            // }

            // dd($lista);
            $unicodigo=1414;
            $fechaCorte=date('Y-m-d\TH:i:s.u\Z');
            if($bodega=="M"){
                $codigoTipoProducto="MD";
            }else if($bodega=="I"){
                $codigoTipoProducto="IM";
            }else{
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'Bodega no encontrada'
                ]);
            }
            
            $dataApi=["fechaCorte"=>$fechaCorte, "unicodigo"=>$unicodigo, "codigoTipoProducto"=>$codigoTipoProducto, "productos"=>$lista];
           
            $token=$this->token();
          
            $resultadoC = $this->clientNacional->request('POST', "saldo-tarea/create",[
                'headers' => [
                    'Authorization'=>'bearer '.$token,
                    'Accept' => 'application/json', 
                    'Content-Type' => 'application/json'
                ],
                    'body' => json_encode($dataApi)
            ]); 

            if($resultadoC->getstatusCode()==201){
                $resultado= json_decode((string) $resultadoC->getBody());
                log::info("dataApi=> ".json_encode($dataApi));  
                log::info("token=> ".$token); 
                log::info("uuid=> ".$resultado->uuid);  
                 
                sleep(10);
                $estadoTarea=$this->estadoTarea($resultado->uuid, $token);
                if($estadoTarea['error']==true){
                    return response()->json([
                        'error'=>true,
                        'mensaje'=>$estadoTarea['mensaje']
                    ]);
                }else{
                    return response()->json([
                        'error'=>false,
                        'mensaje'=>$estadoTarea['mensaje']
                    ]);
                }
               
            }else{
                return response()->json([
                    'error'=>true,
                    'mensaje'=>'Ocurrió un error, al enviar los datos a la api POST, saldo-tarea/create'
                ]);
            }
        }catch(\Throwable $e){
            Log::error('InterOperabilidadController => saldoTarea => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return response()->json([
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ]);
        }
    }

    public function estadoTarea($uuid, $token){
        try{
            // $uuid="f47102df-76e1-4251-a429-1fa1224a9a6b";
            // $token=$this->token();
          
            $resultadoC = $this->clientNacional->request('GET', "saldo-tarea/get-result-by-uuid/{$uuid}",[
                'headers' => [
                    'Authorization'=>'bearer '.$token,
                    'Content-Type' => 'application/json'
                ],
            ]); 
            
           
            if($resultadoC->getstatusCode()==200){
                $resultado= json_decode((string) $resultadoC->getBody());
                return[
                    'error'=>false,
                    'mensaje'=>'OK - La tarea se ha procesado exitosamente',
                    'resultado'=>$resultado
                ];
               
            }else if($resultadoC->getstatusCode()==201){
                $resultado= json_decode((string) $resultadoC->getBody());
                return[
                    'error'=>false,
                    'mensaje'=>'Created - La solicitud se ha creado pero no se ha procesado',
                    'resultado'=>$resultado
                ];
            }else if($resultadoC->getstatusCode()==400){
                $resultado= json_decode((string) $resultadoC->getBody());
                return[
                    'error'=>true,
                    'mensaje'=>'Bad Request - Algo está mal en la solicitud, posibles sku invalidos o saldos incorrectos',
                    'resultado'=>$resultado
                ];
            }else if($resultadoC->getstatusCode()==501){
                $resultado= json_decode((string) $resultadoC->getBody());
                return[
                    'error'=>true,
                    'mensaje'=>'Not-Implemented - La solicitud esta pendiente de procesar',
                    'resultado'=>$resultado
                ];
            }else{
                $resultado= json_decode((string) $resultadoC->getBody());
                return[
                    'error'=>true,
                    'mensaje'=>'Error 500 server API',
                    'resultado'=>$resultado
                ];
            }
        }catch(\Throwable $e){
            Log::error('InterOperabilidadController => saldoTarea => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return[
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde '
            ];
        }
    }

}