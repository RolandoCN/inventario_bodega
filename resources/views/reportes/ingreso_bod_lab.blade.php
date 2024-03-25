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

                    {{$comprobante->descripcion}} {{$comprobante->secuencial}}
                </th>
            
            </tr>

            <tr style="font-size: 10px"  class="fuenteSubtitulo " style=""> 
                <td colspan="6" style="border-color:white;height:5px;text-align: left;border:0 px" width="100%"  >  
                    <b>RESPONSABLE:</b> {{$comprobante->entregado->persona->ape1}} {{$comprobante->entregado->persona->ape2}} {{$comprobante->entregado->persona->nom1}} {{$comprobante->entregado->persona->nom2}}
               
                </td>

                <td colspan="6" style="border-color:white;height:5px;text-align: left;border:0 px" width="100%"  >  

                    <b>FECHA:</b> {{$comprobante->fecha_hora}}
                </td>
            
            </tr>

            <tr style="font-size: 10px"  class="fuenteSubtitulo " style=""> 
                <td colspan="6" style="border-color:white;height:5px;text-align: left;border:0 px" width="100%"  >  
                    <b>PROVEEDOR :</b> 
                    
                    {{$comprobante->proveedor == null ? '': $comprobante->proveedor->empresa}}
               
                </td>

                <td colspan="6" style="border-color:white;height:5px;text-align: left;border:0 px" width="100%"  >  

                    <b>BODEGA:</b>{{$comprobante->bodega->nombre}}
                </td>
            
            </tr>
            @if(!is_null($comprobante->tipoIngreso) || !is_null($comprobante->guia))
                <tr style="font-size: 10px"  class="fuenteSubtitulo " style=""> 
                   

                    @if(!is_null($comprobante->guia))
                        <td colspan="6" style="border-color:white;height:5px;text-align: left;border:0 px" width="100%"  >  

                            <b>Nº FACTURA/GUIA:</b>{{strtoupper($comprobante->guia)}}
                        </td>
                    @endif
                    @if(!is_null($comprobante->tipoIngreso))
                        <td colspan="6" style="border-color:white;height:5px;text-align: left;border:0 px" width="100%"  >  

                            <b>TIPO:</b>{{strtoupper($comprobante->tipoIngreso->nombre)}}
                        </td>
                    @endif
                
                </tr>
            @endif

            @if(!is_null($comprobante->devolucion) || !is_null($comprobante->observacion))
                <tr style="font-size: 10px"  class="fuenteSubtitulo " style=""> 

                    @if(!is_null($comprobante->observacion))
                        <td colspan="6" style="border-color:white;height:5px;text-align: left;border:0 px" width="100%"  >  
                            <b>OBSERVACION :</b> {{strtoupper($comprobante->observacion)}} 
                    
                        </td>
                    @endif
                  
                    @if(!is_null($comprobante->devolucion))
                        <td colspan="6" style="border-color:white;height:5px;text-align: left;border:0 px" width="100%"  >  

                            <b>DEVUELTO POR:</b>{{$comprobante->devolucion->ape1}}
                            {{$comprobante->devolucion->ape2}}
                            {{$comprobante->devolucion->nom1}}
                            {{$comprobante->devolucion->nom2}}
                        </td>
                    @endif
                
                </tr>
            @endif

            
          
        </table>
        <div style="margin-top:12px;">

            <table class="ltable"  border="0" width="100%" style="padding-bottom:2px !important">
              
                
                <tr style="font-size: 10px !important; background-color: #D3D3D3;line-height:10px; "> 
                    
                    <th width="10%" style="border: 0px; ;border-color: #D3D3D3; text-align: center; line-height:10px">CODIGO</th>

                    <th width="40%" style="border: 0px; ;border-color: #D3D3D3; text-align: center; line-height:10px">ITEM</th>

                    <th width="10%" style="border: 0px; ;border-color: #D3D3D3; text-align: center">CANT </th>

                    <th width="10%" style="border: 0px; ;border-color: #D3D3D3; text-align: center">PRECIO</th>

                    <th width="10%" style="border: 0px; ;border-color: #D3D3D3; text-align: center">LOTE</th>

                    <th width="10%" style="border: 0px; ;border-color: #D3D3D3; text-align: center">F. ELAB</th>

                    <th width="10%" style="border: 0px; ;border-color: #D3D3D3; text-align: center">F. CADU</th>

                    <th width="10%" style="border: 0px; ;border-color: #D3D3D3; text-align: center">SUBTOTAL</th>

                  
                </tr>

                <tbody>
                    
                    @if(isset($comprobante))

                        @php
                            $total=0;
                        @endphp
                     
                        @foreach($comprobante->detalle_lab as $e=>$dato)
                            <tr style="font-size: 10px !important;line-height:10px; "> 

                                <td align="center" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                    {{$dato->laboratorio->codigo}}

                                <td align="left" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                    {{$dato->laboratorio->descri}} 
                                </td>
                        
                                <td align="center" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                    {{$dato->cantidad}}
                                </td>

                               
                                <td align="center" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                    {{$dato->precio}} 
                                </td>

                                <td align="center" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                    {{$dato->existencia->lote}}
                                </td>

                                <td align="center" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                    {{$dato->existencia->fecha_elaboracion}}
                                </td>

                                
                                <td align="center" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                    {{$dato->existencia->fecha_caducidad}}
                                </td>

                                <td align="right" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                  {{number_format($dato->total,2)}} 
                                </td>
                            </tr>

                            @php
                                $total=$total + $dato->total;

                            @endphp

                        @endforeach
                    @endif
                </tbody>
                <tfoot >
                    <tr style="font-size:10px !important;line-height:5px" style="">
                        <td  colspan="7"style="font-size:9px;border: 0px; border-color: #D3D3D3;  text-align: right;">
                            <b>TOTAL</b>
                        </td>
                        <td style="border: 0px;border-color: #D3D3D3;  text-align: right; font-size:9px">
                           {{number_format($total,2)}} 
                           
                        </td>
                      
                    </tr>

                </tfoot>
            </table>
            
           
        </div>

    </div>

    
    @if(!is_null($comprobante->retirado_nombre))
        <table width="100%">
            <tr>
            <td width="5%"></td>
            <td width="40%">
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <hr>
                <p class="encabezado1">RETIRADO(A)</p>
                <p class="encabezado1">{{$comprobante->retirado_nombre}}</p>
                <p class="encabezado1">{{$comprobante->retirado_cedula}}</p>
            </td>
            <td width="5%"></td>

            <td width="5%"></td>
            <td width="40%">
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <br>
                <hr>
                <p class="encabezado1">RESPONSABLE(A)</p>
                <p class="encabezado1">
                    {{$comprobante->entregado->persona->ape1}} {{$comprobante->entregado->persona->ape2}}
                    {{$comprobante->entregado->persona->nom1}} {{$comprobante->entregado->persona->nom2}}
                </p>
                <p class="encabezado1">{{$comprobante->entregado->persona->ci}}</p>
            </td>
            <td width="5%"></td>

            </tr>
        </table>
    @else
        <table width="100%">
            <tr>
                <td width="5%"></td>
                <td width="40%">
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <hr>
                    <p class="encabezado1">JEFE(A) BODEGA</p>
                    <p class="encabezado1">{{$jefa->jefabodega}}</p>
                    <p class="encabezado1">{{$jefa->cedula}}</p>
                </td>
                <td width="5%"></td>
                <td width="5%"></td>
                <td width="40%">
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <br>
                    <hr>
                    <p class="encabezado1">RESPONSABLE(A)</p>
                    <p class="encabezado1">
                        {{$comprobante->entregado->persona->ape1}} {{$comprobante->entregado->persona->ape2}}
                        {{$comprobante->entregado->persona->nom1}} {{$comprobante->entregado->persona->nom2}}
                    </p>
                    <p class="encabezado1">{{$comprobante->entregado->persona->ci}}</p>
                </td>
                <td width="5%"></td>

            </tr>
        </table>

    @endif

   
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