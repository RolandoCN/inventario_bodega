<!DOCTYPE html>
<html>
<head>
  <title></title>

     <style type="text/css">
        @page {
            margin-top: 8em;
            margin-left: 3em;
            margin-right:3em;
            margin-bottom: 5em;
        }
        header { position: fixed;  top: -100px; left: 0px; right: 0px; background-color: white; height: 60px; margin-right: 99px}
       
       

        .ltable
        {
            border-collapse: collapse;
            font-family: sans-serif;
        }
        td, th /* Asigna un borde a las etiquetas td Y th */
        {
            border: 1px solid white;
        }

        .sinbordeencabezado /* Asigna un borde a las etiquetas td Y th */
        {
            border: 0px solid black;
        }
        .fuenteSubtitulo{
            font-size: 12px;
        }
        .pad{
            padding-left:5px;
            padding-right:5px;
        }

        
     </style>
      <style type="text/css">
        .preview_firma{
            width: 156px;
            border: solid 1px #000;
        }
        .img_firma{
            width: 80px;
        }
        .btn_azul{
            color: #fff;
            background-color: #337ab7;
            border-color: #2e6da4;
        }
        .hr{
        page-break-after: always;
        border: none;
        margin: 0;
        padding: 0;
        }
        .encabezado{
            /*border: 1px solid;*/
            text-align: center;
            font-size: 10px;

        }
        .encabezado1{
            text-align: center;
            padding-top: 1px;
            font-size: 10px;
        }
        .encabezado2{
            text-align: center;
            padding-top: 10px;
            padding-bottom: 10px;
            font-size: 10px;
        }
        p{
            line-height: 1px;
        }

    </style>

  
</head>

<body>

  <header>
    <table class="ltable " width="112.5%"  >                
            <tr>
                <td height="50px" colspan="3" style="border: 0px;" align="left" >
                    <img src="logo.jpg" width="300px" height="80px">
                </td>
                <td height="60px" colspan="2" style="border: 0px;" align="center" ></td>
               
            </tr>             
        </table>
  </header>

   
    <div style="margin-bottom:30px; margin-top:12px;">

        <table class="ltable" style="" border="0" width="100%" style="padding-bottom:2px !important">
          
            <tr style="font-size: 12px"  class="fuenteSubtitulo " style=""> 
                <th colspan="11" style="border-color:white;height:35px;text-align: center;border:0 px" width="100%"  >  
                    EGRESO POR AREA EN {{$bodega->nombre}}
                 
                </th>
            
            </tr>

            <tr style="font-size: 10px"  class="fuenteSubtitulo " style=""> 
                <td colspan="4" style="border-color:white;height:5px;text-align: left;border:0 px" width="100%"  >  
                    <b>GENERADO:</b> {{auth()->user()->persona->ape1}} {{auth()->user()->persona->ape2}} {{auth()->user()->persona->nom1}} {{auth()->user()->persona->nom2}}
               
                </td>

                <td colspan="4" style="border-color:white;height:5px;text-align: left;border:0 px" width="100%"  >  

                    <b>FECHA:</b> {{date('d-m-Y H:i:s')}}
                </td>
            
            </tr> 
            @if($tipo!="T")
                <tr style="font-size: 10px"  class="fuenteSubtitulo " style=""> 
                    <td colspan="4" style="border-color:white;height:5px;text-align: left;border:0 px" width="100%"  >  
                        <b>DESDE:</b> {{$fini}}
                
                    </td>

                    <td colspan="4" style="border-color:white;height:5px;text-align: left;border:0 px" width="100%"  >  

                        <b>HASTA:</b>  {{$ffin}}
                    </td>
                
                </tr>
            @endif 

          
        </table>
        @foreach($listar as $key=> $info)
            <div style="margin-top:12px;">
                
                <table class="ltable"  border="0" width="100%" style="padding-bottom:2px !important">
                    
                    <tr style="font-size: 10px !important; background-color:white;line-height:10px; "> 

                        <th width="100%" colspan="6" style="border: 0px; ; text-align: center; line-height:20px">     
                            {{$key}}
                        </th>
                    
                    </tr>
                    
                    <tr style="font-size: 10px !important; background-color: #D3D3D3;line-height:10px; "> 

                        <th width="10%" style="border: 0px; ;border-color: #D3D3D3; text-align: center; line-height:10px">CODIGO</th>
                        
                        <th width="40%" style="border: 0px; ;border-color: #D3D3D3; text-align: center; line-height:10px">ITEM</th>

                        <th width="10%" style="border: 0px; ;border-color: #D3D3D3; text-align: center">FECHA</th>

                        <th width="10%" style="border: 0px; ;border-color: #D3D3D3; text-align: center">CANTIDAD</th>

                        <th width="10%" style="border: 0px; ;border-color: #D3D3D3; text-align: center">PRECIO PROM</th>

                        <th width="10%" style="border: 0px; ;border-color: #D3D3D3; text-align: center">SUBTOTAL</th>

                    
                    </tr>

                    <tbody>
                        
                       
                            @php
                                $total=0;

                                
                            @endphp
                        
                            @foreach($info as $e=>$dato)
                                @if($dato->resta > 0)
                                    @php
                                        $precio_promedio=0;
                                        $subtotal=0;
                                       
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
                                           
                                    @endphp
                                    <tr style="font-size: 10px !important;line-height:10px; "> 
                                        <td align="center" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                            {{$codigo}} 
                                        </td>

                                        <td align="left" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                            {{$producto}} 
                                        </td>
                                        <td align="center" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                            {{$dato->fecha}}
                                        </td>
                                        <td align="right" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                            {{$dato->resta}}
                                        </td>

                                      

                                        <td align="right" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                            {{number_format($precio_promedio,4)}} 
                                        </td>
                                        <td align="right" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                            {{number_format($subtotal,2)}} 
                                        </td>

                                    </tr>

                                    @php
                                        $total=$total + $subtotal;

                                    @endphp
                                @endif

                            @endforeach
                        
                    </tbody>
                    <tfoot >
                        <tr style="font-size:10px !important;line-height:5px" style="">
                            <td  colspan="5"style="font-size:9px;border: 0px; border-color: #D3D3D3;  text-align: right;">
                                <b>TOTAL</b>
                            </td>
                            <td style="border: 0px;border-color: #D3D3D3;  text-align: right; font-size:9px">
                            {{number_format($total,2)}} 
                            
                            </td>
                        
                        </tr>

                    </tfoot>
                </table>           
            </div>
        @endforeach

       
        
        {{-- $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
        $pdf->text(490, 820, "Página $PAGE_NUM de $PAGE_COUNT", $font, 9); --}}
       
    </div>


   
  <script type="text/php">
    if ( isset($pdf) ) {
        $pdf->page_script('
            $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
            $pdf->text(490, 820, "Página $PAGE_NUM de $PAGE_COUNT", $font, 9); 
        ');
    }
</script>
</body>
</html>