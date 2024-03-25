<?php

namespace App\Http\Controllers\Bodega;
use App\Http\Controllers\Controller;;
use App\Models\Producto;
use Log;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator;
use Storage;
use App\Http\Controllers\Bodega\InventarioController;
use App\Http\Controllers\Bodega\InventarioController2;
use App\Models\Bodega\Existencia;

// require_once 'localhost/sisInv/PHPExcel8/Classes/PHPExcel.php';
require_once __DIR__.'/PHPExcel8/Classes/PHPExcel.php';
// require_once __DIR__.'/../public/index.php';

class ReportesExcelController extends Controller
{
    private $objInventario = null;
    private $objInventario2 = null;
    private $objItems = null;
    private $objPersona = null;
    
    public function __construct(){
        try{
            $this->middleware('auth');
            $this->objInventario= new InventarioController();
            $this->objInventario2= new InventarioController2(); 
          
        }catch (\Throwable $e) {
            Log::error('ReportesExcelController => index => mensaje => '.$e->getMessage());
            return back();
        }
           
    }

    public function reporteExcelEgresoArea($idbodega, $lugar, $tipo,$fini, $ffin){
        $egreso=Existencia::with('solicita','prodbod')
        ->whereHas('prodbod', function($q) use($idbodega){
            $q->where('idbodega',$idbodega);
        })
        ->whereBetween('fecha', [$fini, $ffin])
        ->where(function($c) use($idbodega) {
            if($idbodega==20  || $idbodega==22  || $idbodega ==25 || $idbodega ==26 || $idbodega==27  || $idbodega ==28 || $idbodega ==29 || $idbodega ==21 || $idbodega ==6 || $idbodega ==7){
                $c->where('cod','EABFA'); //egreso bodega farmacia
            }else{
                $c->where('cod','EABA');
            }
            
        })
        ->get();
        // dd($egreso);
        #agrupamos por area
        $lista_final_agrupada=[];
        foreach ($egreso as $key => $item){                
            if(!isset($lista_final_agrupada[$item->solicita->persona->area->descripcion])) {
                $lista_final_agrupada[$item->solicita->persona->area->descripcion]=array($item);
        
            }else{
                array_push($lista_final_agrupada[$item->solicita->persona->area->descripcion], $item);
            }
        }

        $bodega=DB::connection('pgsql')->table('bodega.bodega')
        ->where('idbodega',$idbodega)->first();
        $fec_ini=date('d-m-Y', strtotime($fini));
        $fec_fin=date('d-m-Y', strtotime($ffin));
        if($tipo=='T'){
            $titulo_='EGRESO EN '.$bodega->nombre;
        }else{
            $titulo_='EGRESO EN '.$bodega->nombre. ' DESDE EL '.$fec_ini. ' HASTA EL '.$fec_fin;
        }

        $estiloTitulo = array(
            'font' => array(
                'name'      => 'Verdana',
                'bold'      => true,
                'italic'    => false,
                'strike'    => false,
                'size' =>10,
                'color'     => array(
                    'rgb' => '000000'
                )
            ),
            'fill' => array(
            'type'  => \PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array(
                    'rgb' => 'D3D3D3')
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_NONE
                )
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'rotation' => 0,
                'wrap' => TRUE
            )
        );

        $estiloCabecera = array(
            'font' => array(
                'name'      => 'Verdana',
                'bold'      => true,
                'italic'    => false,
                'strike'    => false,
                'size' =>10,
                'color'     => array(
                    'rgb' => '000000'
                )
            ),
            'fill' => array(
            'type'  => \PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array(
                    'rgb' => 'FFFFFF')
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_NONE
                )
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'rotation' => 0,
                'wrap' => TRUE
            )
        );

        $estiloTotal = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_JUSTIFY,
                'rotation' => 0,
                'wrap' => TRUE
            )
            
        );

        $estiloCodigo = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_JUSTIFY,
                'rotation' => 0,
                'wrap' => TRUE
            )
            
        );

        $estiloNumero = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_JUSTIFY,
                'rotation' => 0,
                'wrap' => TRUE
            )
            
        );

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
      
                    
        $objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue('HOSPITAL DR. NAPOLEON DAVILA CORDOVA')->getStyle('A1')->applyFromArray($estiloCabecera);

        $objPHPExcel->getActiveSheet()->mergeCells('A2:F2');
        $objPHPExcel->getActiveSheet()->getCell('A2')->setValue($titulo_)->getStyle('A2')->applyFromArray($estiloCabecera);

       
        $objPHPExcel->getActiveSheet()->mergeCells('A3:F3');
        $objPHPExcel->getActiveSheet()->getCell('A3')->setValue('')->getStyle('F3')->applyFromArray($estiloCabecera);

        $inicia_vacio=3;
        $inicia_titulo=4;

        $inicio_titulo2=4;

        $fila=6;
        foreach ($lista_final_agrupada as $key => $value) {
            
            $inicio_titulo2=$inicio_titulo2+1;
            $objPHPExcel->getActiveSheet()->mergeCells('A'.$inicia_titulo. ':F'.$inicia_titulo);
            $objPHPExcel->getActiveSheet()->getCell('A'.$inicia_titulo)->setValue($key)->getStyle('A'.$inicia_titulo)->applyFromArray($estiloCabecera);

            $objPHPExcel->getActiveSheet()->setCellValue('A'.$inicio_titulo2,'Codigo Esbay')
            ->getStyle('A'.$inicio_titulo2)->applyFromArray($estiloTitulo);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$inicio_titulo2,'Item')
            ->getStyle('B'.$inicio_titulo2)->applyFromArray($estiloTitulo);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$inicio_titulo2,'Fecha')
            ->getStyle('C'.$inicio_titulo2)->applyFromArray($estiloTitulo);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$inicio_titulo2,'Cantidad')
            ->getStyle('D'.$inicio_titulo2)->applyFromArray($estiloTitulo);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$inicio_titulo2,'Precio Promedio')
            ->getStyle('E'.$inicio_titulo2)->applyFromArray($estiloTitulo);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$inicio_titulo2,'Subtotal')
            ->getStyle('F'.$inicio_titulo2)->applyFromArray($estiloTitulo);
        
            if($fila>6){
                $con=$fila+3;
            }else{
                $con=$fila;
            }  
            
            $suma_subtotal=0;
            $suma_iva=0;
            $suma_total=0;
          
            foreach ($value as $e=>$dato) {

                         
                $precio_promedio=$dato->prodbod->precio;
                $subtotal=$precio_promedio * $dato->resta;
                
                $codigo="";
                $producto="";
                if($dato->prodbod->idbodega==30){ //protecc
                    // $producto=$dato->prodbod->itemproteccion->descri;
                    // $codigo=$dato->prodbod->itemproteccion->codigo;
                }else if($dato->prodbod->idbodega==1 || $dato->prodbod->idbodega==6 || $dato->prodbod->idbodega==17 || $dato->prodbod->idbodega==20){
                    //medicamentos
                    $producto=$dato->prodbod->medicamento->nombre." ".$dato->prodbod->medicamento->concentra." ".$dato->prodbod->medicamento->forma;
                    $codigo=$dato->prodbod->medicamento->cum;
                }else if($dato->prodbod->idbodega==2 || $dato->prodbod->idbodega==7 || $dato->prodbod->idbodega==18 || $dato->prodbod->idbodega==21){
                    //insumo
                    $producto=$dato->prodbod->insumo->insumo;
                    $codigo=$dato->prodbod->insumo->cudim;
                }else if($dato->prodbod->idbodega==8 || $dato->prodbod->idbodega==13 || $dato->prodbod->idbodega==14 || $dato->prodbod->idbodega==19 || $dato->prodbod->idbodega==23 || $dato->prodbod->idbodega==24  || $dato->prodbod->idbodega==22 || $dato->prodbod->idbodega==25 || $dato->prodbod->idbodega==26 || $dato->prodbod->idbodega==27 || $dato->prodbod->idbodega==28 || $dato->prodbod->idbodega==29){
                    //laboratio
                    $producto=$dato->prodbod->laboratorio->descri;
                    $codigo=$dato->prodbod->laboratorio->codigo;
                }else{
                    //items
                    $producto=$dato->prodbod->items->descri;
                    $codigo=$dato->prodbod->items->codigo;
                }
               
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$con,$codigo)->getStyle('A'.$con)->applyFromArray($estiloCodigo);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$con,$producto)->getStyle('B'.$con)->applyFromArray($estiloCodigo);

                $objPHPExcel->getActiveSheet()->setCellValue('C'.$con,$dato->fecha)->getStyle('C'.$con)->applyFromArray($estiloNumero);

                $objPHPExcel->getActiveSheet()->setCellValue('D'.$con,$dato->resta)->getStyle('D'.$con)->applyFromArray($estiloNumero);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$con,number_format(($precio_promedio),4,'.', ''))->getStyle('E'.$con)->applyFromArray($estiloNumero);
                $objPHPExcel->getActiveSheet()->setCellValue('F'.$con,number_format(($subtotal),2,'.', ''))->getStyle('F'.$con)->applyFromArray($estiloNumero);
            
                                
                $con=$con+1;  
                $suma_total= $suma_total + $subtotal;

                $inicia_titulo=$con;
                $fila=$con;

                $inicio_titulo2=$con;
               
            
            }  

            $objPHPExcel->getActiveSheet()->setCellValue('E'.$con,'TOTAL')->getStyle('E'.$con)->applyFromArray($estiloNumero);
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$con,number_format(($suma_total),2,'.', ''))->getStyle('F'.$con)->applyFromArray($estiloNumero);

            $inicio_titulo2=$con+1;
            $inicia_titulo=$con+1;
                
        }
        
        $objPHPExcel->getActiveSheet()->setTitle('ITEMS');
        $objPHPExcel->setActiveSheetIndex(0);
        $name="EGRESOSAREAS";
        $fecha= date("YmdHis");
        $extension="xls";
        $nombre_archivo=$name."".$fecha.".".$extension;
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        $objWriter->save('storage/app/public/'.$nombre_archivo);                
        $exists_destino = Storage::disk('public')->exists($nombre_archivo);        

        if($exists_destino){
            
            return response()->json([
                "error"=>false,
                "detalle"=>$nombre_archivo, 
                
            ]); 
        }else{
            Log::info("nombre_Excel ".$nombre_archivo);
            return response()->json([
                "error"=>true,
                "mensaje"=>'No se pudo crear el documento'
            ]);               
        }
    }

    //excel de egresos x bodega en rango de fecha
    public function reporteExcelEgreso($idbodega, $lugar, $tipo,$fini, $ffin){
      
        $obtenerData=$this->objInventario->buscarInventario($idbodega, $lugar, $tipo,$fini, $ffin, 'S');
        if($obtenerData['error']==true){
            return[
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ];
        }  
       
        $bodega=DB::connection('pgsql')->table('bodega.bodega')
        ->where('idbodega',$idbodega)->first();
        $fec_ini=date('d-m-Y', strtotime($fini));
        $fec_fin=date('d-m-Y', strtotime($ffin));
        if($tipo=='T'){
            $titulo_='EGRESO EN '.$bodega->nombre;
        }else{
            $titulo_='EGRESO EN '.$bodega->nombre. ' DESDE EL '.$fec_ini. ' HASTA EL '.$fec_fin;
        }
       

        $precio_min=1;
        $precio_max=1;

        $estiloTitulo = array(
            'font' => array(
                'name'      => 'Verdana',
                'bold'      => true,
                'italic'    => false,
                'strike'    => false,
                'size' =>10,
                'color'     => array(
                    'rgb' => '000000'
                )
            ),
            'fill' => array(
            'type'  => \PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array(
                    'rgb' => 'D3D3D3')
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_NONE
                )
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'rotation' => 0,
                'wrap' => TRUE
            )
        );

        $estiloCabecera = array(
            'font' => array(
                'name'      => 'Verdana',
                'bold'      => true,
                'italic'    => false,
                'strike'    => false,
                'size' =>10,
                'color'     => array(
                    'rgb' => '000000'
                )
            ),
            'fill' => array(
            'type'  => \PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array(
                    'rgb' => 'FFFFFF')
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_NONE
                )
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'rotation' => 0,
                'wrap' => TRUE
            )
        );

        $estiloTotal = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_JUSTIFY,
                'rotation' => 0,
                'wrap' => TRUE
            )
            
        );

        $estiloCodigo = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_JUSTIFY,
                'rotation' => 0,
                'wrap' => TRUE
            )
            
        );

        $estiloNumero = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_JUSTIFY,
                'rotation' => 0,
                'wrap' => TRUE
            )
            
        );

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
                    
        $objPHPExcel->getActiveSheet()->mergeCells('A1:E1');
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue('HOSPITAL DR. NAPOLEON DAVILA CORDOVA')->getStyle('A1')->applyFromArray($estiloCabecera);

        $objPHPExcel->getActiveSheet()->mergeCells('A2:E2');
        $objPHPExcel->getActiveSheet()->getCell('A2')->setValue($titulo_)->getStyle('A2')->applyFromArray($estiloCabecera);

       
        $objPHPExcel->getActiveSheet()->mergeCells('A3:E3');
        $objPHPExcel->getActiveSheet()->getCell('A3')->setValue('')->getStyle('A3')->applyFromArray($estiloCabecera);

        $inicia_vacio=3;
        $inicia_titulo=4;
        $inicio_cuerpo=5;

        $objPHPExcel->getActiveSheet()->setCellValue('A'.$inicia_titulo,'Codigo Esbay')
        ->getStyle('A'.$inicia_titulo)->applyFromArray($estiloTitulo);
        $objPHPExcel->getActiveSheet()->setCellValue('B'.$inicia_titulo,'Item')
        ->getStyle('B'.$inicia_titulo)->applyFromArray($estiloTitulo);
        $objPHPExcel->getActiveSheet()->setCellValue('C'.$inicia_titulo,'Cantidad')
        ->getStyle('C'.$inicia_titulo)->applyFromArray($estiloTitulo);
        $objPHPExcel->getActiveSheet()->setCellValue('D'.$inicia_titulo,'Precio Promedio')
        ->getStyle('D'.$inicia_titulo)->applyFromArray($estiloTitulo);
        $objPHPExcel->getActiveSheet()->setCellValue('E'.$inicia_titulo,'Subtotal')
        ->getStyle('E'.$inicia_titulo)->applyFromArray($estiloTitulo);
       
               
        $con=$inicio_cuerpo;
        $suma_subtotal=0;
        $suma_iva=0;
        $suma_total=0;
       
        foreach ($obtenerData['resultado'] as $key => $value) {

           
            $precio_promedio=0;
            $subtotal=0;
          
            if($value->egresado > 0){
                $precio_promedio=$value->precioegreso / $value->cantidadex;
                $subtotal=$precio_promedio * $value->egresado;
            
     
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$con,$value->codigo)->getStyle('A'.$con)->applyFromArray($estiloCodigo);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$con,$value->detalle)->getStyle('B'.$con)->applyFromArray($estiloCodigo);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$con,$value->egresado)->getStyle('C'.$con)->applyFromArray($estiloCodigo);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$con,number_format(($precio_promedio),4,'.', ''))->getStyle('C'.$con)->applyFromArray($estiloCodigo);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$con,number_format(($subtotal),2,'.', ''))->getStyle('C'.$con)->applyFromArray($estiloCodigo);
               
                                
                $con=$con+1;  
                $suma_total= $suma_total + $subtotal;
            }
        
        }  
        
        if($con>5){
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$con,'TOTAL')->getStyle('D'.$con)->applyFromArray($estiloNumero);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$con,number_format(($suma_total),2,'.', ''))->getStyle('E'.$con)->applyFromArray($estiloNumero);
          
        }

        
        $objPHPExcel->getActiveSheet()->setTitle('ITEMS');
        $objPHPExcel->setActiveSheetIndex(0);
        $name="EGRESOS";
        $fecha= date("YmdHis");
        $extension="xls";
        $nombre_archivo=$name."".$fecha.".".$extension;
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        $objWriter->save('storage/app/public/'.$nombre_archivo);                
        $exists_destino = Storage::disk('public')->exists($nombre_archivo);        

        if($exists_destino){
            
            return response()->json([
                "error"=>false,
                "detalle"=>$nombre_archivo, 
                
            ]); 
        }else{
            Log::info("nombre_Excel ".$nombre_archivo);
            return response()->json([
                "error"=>true,
                "mensaje"=>'No se pudo crear el documento'
            ]);               
        }
      
    }

    public function reporteExcelInvIndivFarma($idbodega, $filtro, $ini, $fin, $filt_fecha){
      
        $obtenerData=$this->objInventario2->buscarInventarioIndLote($idbodega, "FARMACIA", "Individual", $ini, $fin, $filt_fecha);
        if($obtenerData['error']==true){
            return[
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ];
        }  
       
       
        $bodega=DB::connection('pgsql')->table('bodega.bodega')
        ->where('idbodega',$idbodega)->first();
        $fec_ini=date('d-m-Y', strtotime($ini));
        $fec_fin=date('d-m-Y', strtotime($fin));

        if($filt_fecha!='Filtro'){
            $titulo_='INVENTARIO EN '.$bodega->nombre;
        }else{
            $titulo_='INVENTARIO EN '.$bodega->nombre. ' DESDE EL '.$fec_ini. ' HASTA EL '.$fec_fin;
        }

      
        $estiloTitulo = array(
            'font' => array(
                'name'      => 'Verdana',
                'bold'      => true,
                'italic'    => false,
                'strike'    => false,
                'size' =>10,
                'color'     => array(
                    'rgb' => '000000'
                )
            ),
            'fill' => array(
            'type'  => \PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array(
                    'rgb' => 'D3D3D3')
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_NONE
                )
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'rotation' => 0,
                'wrap' => TRUE
            )
        );

        $estiloCabecera = array(
            'font' => array(
                'name'      => 'Verdana',
                'bold'      => true,
                'italic'    => false,
                'strike'    => false,
                'size' =>10,
                'color'     => array(
                    'rgb' => '000000'
                )
            ),
            'fill' => array(
            'type'  => \PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array(
                    'rgb' => 'FFFFFF')
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_NONE
                )
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'rotation' => 0,
                'wrap' => TRUE
            )
        );

        $estiloTotal = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_JUSTIFY,
                'rotation' => 0,
                'wrap' => TRUE
            )
            
        );

        $estiloCodigo = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_JUSTIFY,
                'rotation' => 0,
                'wrap' => TRUE
            )
            
        );

        $estiloNumero = array(
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_JUSTIFY,
                'rotation' => 0,
                'wrap' => TRUE
            )
            
        );

        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
       
                    
        $objPHPExcel->getActiveSheet()->mergeCells('A1:E1');
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue('HOSPITAL DR. NAPOLEON DAVILA CORDOVA')->getStyle('A1')->applyFromArray($estiloCabecera);

        $objPHPExcel->getActiveSheet()->mergeCells('A2:E2');
        $objPHPExcel->getActiveSheet()->getCell('A2')->setValue($titulo_)->getStyle('A2')->applyFromArray($estiloCabecera);

       
        $objPHPExcel->getActiveSheet()->mergeCells('A3:E3');
        $objPHPExcel->getActiveSheet()->getCell('A3')->setValue('')->getStyle('A3')->applyFromArray($estiloCabecera);

        $inicia_vacio=3;
        $inicia_titulo=4;
        $inicio_cuerpo=5;

        $objPHPExcel->getActiveSheet()->setCellValue('A'.$inicia_titulo,'Codigo')
        ->getStyle('A'.$inicia_titulo)->applyFromArray($estiloTitulo);
        $objPHPExcel->getActiveSheet()->setCellValue('B'.$inicia_titulo,'Item')
        ->getStyle('B'.$inicia_titulo)->applyFromArray($estiloTitulo);
        $objPHPExcel->getActiveSheet()->setCellValue('C'.$inicia_titulo,'Lote')
        ->getStyle('C'.$inicia_titulo)->applyFromArray($estiloTitulo);
        $objPHPExcel->getActiveSheet()->setCellValue('D'.$inicia_titulo,'Stock')
        ->getStyle('D'.$inicia_titulo)->applyFromArray($estiloTitulo);
        $objPHPExcel->getActiveSheet()->setCellValue('E'.$inicia_titulo,'F. Caducidad')
        ->getStyle('E'.$inicia_titulo)->applyFromArray($estiloTitulo);
       
               
        $con=$inicio_cuerpo;
        $suma_subtotal=0;
        $suma_iva=0;
        $suma_total=0;
       
        foreach ($obtenerData['resultado'] as $key => $value) {
           
            // if($value->egresado > 0){
            $cantidad_lote=0;
            foreach ($value as $dato) {
                $totalAg=0;                                  
                if($filt_fecha=="Filtro"){
                    $totalAg=$dato->total;
                    $cantidad_lote=$cantidad_lote +$totalAg;
                }else{
                    $cantidad_lote=$cantidad_lote + $dato->existencia;
                }
            }
            if($cantidad_lote>0){
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$con,$value[0]->codigo_item)->getStyle('A'.$con)->applyFromArray($estiloCodigo);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$con,$value[0]->detalle)->getStyle('B'.$con)->applyFromArray($estiloCodigo);
                $objPHPExcel->getActiveSheet()->setCellValue('C'.$con,$value[0]->lote)->getStyle('C'.$con)->applyFromArray($estiloCodigo);
                $objPHPExcel->getActiveSheet()->setCellValue('D'.$con,$cantidad_lote)->getStyle('C'.$con)->applyFromArray($estiloCodigo);
                $objPHPExcel->getActiveSheet()->setCellValue('E'.$con,$value[0]->fcaduca)->getStyle('C'.$con)->applyFromArray($estiloCodigo);
               
                                
                $con=$con+1;  
                
            }
        
        }  
        
       
        
        $objPHPExcel->getActiveSheet()->setTitle('ITEMS');
        $objPHPExcel->setActiveSheetIndex(0);
        $name="INVENTARIO INDIV_";
        $fecha= date("YmdHis");
        $extension="xls";
        $nombre_archivo=$name."".$fecha.".".$extension;
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        $objWriter->save('storage/app/public/'.$nombre_archivo);                
        $exists_destino = Storage::disk('public')->exists($nombre_archivo);        

        if($exists_destino){
            
            return response()->json([
                "error"=>false,
                "detalle"=>$nombre_archivo, 
                
            ]); 
        }else{
            Log::info("nombre_Excel ".$nombre_archivo);
            return response()->json([
                "error"=>true,
                "mensaje"=>'No se pudo crear el documento'
            ]);               
        }
      
    }

    public function pdfInventarioIndExcel($idbodega, $filtro, $ini, $fin, $filt_fecha){
        try{
            set_time_limit(0);
            ini_set("memory_limit",-1);
            ini_set('max_execution_time', 0);

            // $consultaPdf=$this->buscarInventario($idbodega, "BODEGA", "Individual");
            $obtenerData=$this->objInventario2->buscarInventarioIndLote($idbodega, "BODEGA", "Individual", $ini, $fin, $filt_fecha);
           
            $bodega=DB::connection('pgsql')->table('bodega.bodega')
            ->where('idbodega',$idbodega)->first();
            $fec_ini=date('d-m-Y', strtotime($ini));
            $fec_fin=date('d-m-Y', strtotime($fin));
    
            if($filt_fecha!='Filtro'){
                $titulo_='INVENTARIO EN '.$bodega->nombre;
            }else{
                $titulo_='INVENTARIO EN '.$bodega->nombre. ' DESDE EL '.$fec_ini. ' HASTA EL '.$fec_fin;
            }
    
          
            $estiloTitulo = array(
                'font' => array(
                    'name'      => 'Verdana',
                    'bold'      => true,
                    'italic'    => false,
                    'strike'    => false,
                    'size' =>10,
                    'color'     => array(
                        'rgb' => '000000'
                    )
                ),
                'fill' => array(
                'type'  => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                        'rgb' => 'D3D3D3')
                ),
                'borders' => array(
                    'allborders' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_NONE
                    )
                ),
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    'rotation' => 0,
                    'wrap' => TRUE
                )
            );
    
            $estiloCabecera = array(
                'font' => array(
                    'name'      => 'Verdana',
                    'bold'      => true,
                    'italic'    => false,
                    'strike'    => false,
                    'size' =>10,
                    'color'     => array(
                        'rgb' => '000000'
                    )
                ),
                'fill' => array(
                'type'  => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                        'rgb' => 'FFFFFF')
                ),
                'borders' => array(
                    'allborders' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_NONE
                    )
                ),
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    'rotation' => 0,
                    'wrap' => TRUE
                )
            );
    
            $estiloTotal = array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'vertical' => \PHPExcel_Style_Alignment::VERTICAL_JUSTIFY,
                    'rotation' => 0,
                    'wrap' => TRUE
                )
                
            );
    
            $estiloCodigo = array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'vertical' => \PHPExcel_Style_Alignment::VERTICAL_JUSTIFY,
                    'rotation' => 0,
                    'wrap' => TRUE
                )
                
            );
    
            $estiloNumero = array(
                'alignment' => array(
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    'vertical' => \PHPExcel_Style_Alignment::VERTICAL_JUSTIFY,
                    'rotation' => 0,
                    'wrap' => TRUE
                )
                
            );
    
            $objPHPExcel = new \PHPExcel();
            $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
            $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
    
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
           
                        
            $objPHPExcel->getActiveSheet()->mergeCells('A1:E1');
            $objPHPExcel->getActiveSheet()->getCell('A1')->setValue('HOSPITAL DR. NAPOLEON DAVILA CORDOVA')->getStyle('A1')->applyFromArray($estiloCabecera);
    
            $objPHPExcel->getActiveSheet()->mergeCells('A2:E2');
            $objPHPExcel->getActiveSheet()->getCell('A2')->setValue($titulo_)->getStyle('A2')->applyFromArray($estiloCabecera);
    
           
            $objPHPExcel->getActiveSheet()->mergeCells('A3:E3');
            $objPHPExcel->getActiveSheet()->getCell('A3')->setValue('')->getStyle('A3')->applyFromArray($estiloCabecera);
    
            $inicia_vacio=3;
            $inicia_titulo=4;
            $inicio_cuerpo=5;
    
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$inicia_titulo,'Codigo')
            ->getStyle('A'.$inicia_titulo)->applyFromArray($estiloTitulo);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$inicia_titulo,'Item')
            ->getStyle('B'.$inicia_titulo)->applyFromArray($estiloTitulo);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$inicia_titulo,'Lote')
            ->getStyle('C'.$inicia_titulo)->applyFromArray($estiloTitulo);
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$inicia_titulo,'Stock')
            ->getStyle('D'.$inicia_titulo)->applyFromArray($estiloTitulo);
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$inicia_titulo,'F. Caducidad')
            ->getStyle('E'.$inicia_titulo)->applyFromArray($estiloTitulo);
           
                   
            $con=$inicio_cuerpo;
            $suma_subtotal=0;
            $suma_iva=0;
            $suma_total=0;
           
            foreach ($obtenerData['resultado'] as $key => $value) {
               
                // if($value->egresado > 0){
                $cantidad_lote=0;
                foreach ($value as $dato) {
                    $totalAg=0;                                  
                    if($filt_fecha=="Filtro"){
                        $totalAg=$dato->total;
                        $cantidad_lote=$cantidad_lote +$totalAg;
                    }else{
                        $cantidad_lote=$cantidad_lote + $dato->existencia;
                    }
                }
                if($cantidad_lote>0){
                    $objPHPExcel->getActiveSheet()->setCellValue('A'.$con,$value[0]->codigo_item)->getStyle('A'.$con)->applyFromArray($estiloCodigo);
                    $objPHPExcel->getActiveSheet()->setCellValue('B'.$con,$value[0]->detalle)->getStyle('B'.$con)->applyFromArray($estiloCodigo);
                    $objPHPExcel->getActiveSheet()->setCellValue('C'.$con,$value[0]->lote)->getStyle('C'.$con)->applyFromArray($estiloCodigo);
                    $objPHPExcel->getActiveSheet()->setCellValue('D'.$con,$cantidad_lote)->getStyle('C'.$con)->applyFromArray($estiloCodigo);
                    $objPHPExcel->getActiveSheet()->setCellValue('E'.$con,$value[0]->fcaduca)->getStyle('C'.$con)->applyFromArray($estiloCodigo);
                   
                                    
                    $con=$con+1;  
                    
                }
            
            }  
            
           
            
            $objPHPExcel->getActiveSheet()->setTitle('ITEMS');
            $objPHPExcel->setActiveSheetIndex(0);
            $name="INVENTARIO INDIV_";
            $fecha= date("YmdHis");
            $extension="xls";
            $nombre_archivo=$name."".$fecha.".".$extension;
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    
            $objWriter->save('storage/app/public/'.$nombre_archivo);                
            $exists_destino = Storage::disk('public')->exists($nombre_archivo);        
    
            if($exists_destino){
                
                return response()->json([
                    "error"=>false,
                    "detalle"=>$nombre_archivo, 
                    
                ]); 
            }else{
                Log::info("nombre_Excel ".$nombre_archivo);
                return response()->json([
                    "error"=>true,
                    "mensaje"=>'No se pudo crear el documento'
                ]);               
            }
          
          
                
        }catch (\Throwable $e) {
            Log::error('ReportesExcelController => pdfInventarioIndExcel => mensaje => '.$e->getMessage(). ' linea => ' .$e->getLine());
            return[
                'error'=>true,
                'mensaje'=>'Ocurrió un error, intentelo más tarde'
            ];
            
        }
    }


}