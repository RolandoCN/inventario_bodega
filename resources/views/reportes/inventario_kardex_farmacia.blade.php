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
                    KARDEX EN {{$bodega->nombre}} DEL ITEM <br> {{mb_strtoupper($nombre_item->detalle,'UTF-8')}} <br>
                    {{mb_strtoupper($texto,'UTF-8')}}
                 
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

            <tr style="font-size: 10px"  class="fuenteSubtitulo " style=""> 
                <td colspan="4" style="border-color:white;height:5px;text-align: left;border:0 px" width="100%"  >  
                    <b>DESDE:</b> {{$fini}}
               
                </td>

                <td colspan="4" style="border-color:white;height:5px;text-align: left;border:0 px" width="100%"  >  

                    <b>HASTA:</b>  {{$ffin}}
                </td>
            
            </tr> 

          
        </table>
        <div style="margin-top:12px;">

            <table class="ltable"  border="0" width="100%" style="padding-bottom:2px !important">
              
                
                <tr style="font-size: 10px !important; background-color: #D3D3D3;line-height:10px; "> 
                    
                    <th width="25%" style="border: 0px; ;border-color: #D3D3D3; text-align: center; line-height:10px">RESPONSABLDE</th>


                    <th width="23%" style="border: 0px; ;border-color: #D3D3D3; text-align: center">TIPO</th>

                    <th width="22%" style="border: 0px; ;border-color: #D3D3D3; text-align: center">DETALLE</th>

                    <th width="15%" style="border: 0px; ;border-color: #D3D3D3; text-align: center">FECHA</th>

                    <th width="10%" style="border: 0px; ;border-color: #D3D3D3; text-align: center">PRECIO</th>

                    <th width="10%" style="border: 0px; ;border-color: #D3D3D3; text-align: center">INGRESO</th>

                    <th width="10%" style="border: 0px; ;border-color: #D3D3D3; text-align: center">EGRESO</th>

                  
                </tr>

                <tbody>
                    
                    @if(isset($data))
                        @php
                            $total_ingreso=0;
                            $total_egreso=0;                            
                        @endphp
                     
                        @foreach($data as $e=>$dato)
                          
                                @php
                                    $precio_promedio=0;
                                @endphp
                                <tr style="font-size: 10px !important;line-height:10px; "> 

                                    <td align="left" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                        {{$dato->responsable}} 
                                    </td>

                                    <td align="left" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                        {{$dato->ntipo}} 
                                    </td>
                            
                                    <td align="left" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                        {{$dato->decri_comp}} {{$dato->secuencial}}
                                    </td>

                                    <td align="center" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                        {{$dato->fecha_hora}}
                                    </td>

                                    <td align="right" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                        {{number_format($dato->precio,4)}} 
                                    </td>

                                    <td align="right" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                        {{$dato->suma == null ? '0': $dato->suma}}
                                    </td>

                                    <td align="right" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                        {{$dato->resta == null ? '0': $dato->resta}}
                                    </td>

                                </tr>

                                @php
                                    $total_ingreso=$total_ingreso + $dato->suma;
                                    $total_egreso=$total_egreso + $dato->resta;
                                @endphp
                            

                        @endforeach
                    @endif
                </tbody>
                <tfoot >
                    <tr style="font-size:10px !important;line-height:5px" style="">
                        <td  colspan="5"style="font-size:9px;border: 0px; border-color: #D3D3D3;  text-align: right;">
                            <b>TOTAL</b>
                        </td>
                        <td style="border: 0px;border-color: #D3D3D3;  text-align: right; font-size:9px">
                           {{$total_ingreso}} 
                           
                        </td>

                        <td style="border: 0px;border-color: #D3D3D3;  text-align: right; font-size:9px">
                            {{$total_egreso}} 
                            
                         </td>
                      
                    </tr>

                </tfoot>
            </table>
            
           
        </div>

       
       
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