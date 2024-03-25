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
                    <b>SOLICITADO:</b> {{$comprobante->entregado->persona->ape1}} {{$comprobante->entregado->persona->ape2}} {{$comprobante->entregado->persona->nom1}} {{$comprobante->entregado->persona->nom2}}
               
                </td>

                <td colspan="6" style="border-color:white;height:5px;text-align: left;border:0 px" width="100%"  >  

                    <b>FECHA:</b> {{$comprobante->fecha_hora}}
                </td>
            
            </tr> 

            <tr style="font-size: 10px"  class="fuenteSubtitulo " style=""> 
                <td colspan="6" style="border-color:white;height:5px;text-align: left;border:0 px" width="100%"  >  
                    <b>APROBADO:</b> {{$comprobante->responsable->persona->ape1}} {{$comprobante->responsable->persona->ape2}} {{$comprobante->responsable->persona->nom1}} {{$comprobante->responsable->persona->nom2}}
               
                </td>

                <td colspan="6" style="border-color:white;height:5px;text-align: left;border:0 px" width="100%"  >  

                    <b>FECHA:</b>{{$comprobante->fecha_aprobacion}}
                </td>
            
            </tr>

            <tr style="font-size: 10px"  class="fuenteSubtitulo " style=""> 
                <td colspan="6" style="border-color:white;height:5px;text-align: left;border:0 px" width="100%"  >  
                    <b>ÁREA:</b> {{$comprobante->nomarea->descripcion}} 
               
                </td>

            </tr>

            
          
        </table>
        <div style="margin-top:12px;">

            <table class="ltable"  border="0" width="100%" style="padding-bottom:2px !important">
              
                
                <tr style="font-size: 10px !important; background-color: #D3D3D3;line-height:10px; "> 
                    
                    <th width="40%" style="border: 0px; ;border-color: #D3D3D3; text-align: center; line-height:10px">ITEM</th>

                    <th width="10%" style="border: 0px; ;border-color: #D3D3D3; text-align: center">CANT PED</th>

                    <th width="10%" style="border: 0px; ;border-color: #D3D3D3; text-align: center">CANT ENT</th>

                    <th width="10%" style="border: 0px; ;border-color: #D3D3D3; text-align: center">PRECIO</th>


                    <th width="10%" style="border: 0px; ;border-color: #D3D3D3; text-align: center">F. CADU</th>

                    <th width="10%" style="border: 0px; ;border-color: #D3D3D3; text-align: center">LOTE</th>

                    <th width="10%" style="border: 0px; ;border-color: #D3D3D3; text-align: center">SUBTOTAL</th>

                  
                </tr>

                <tbody>
                    
                    @if(isset($comprobante))
                        @php
                            $total=0;
                        @endphp
                     
                        @foreach($comprobante->detalle_item as $e=>$dato)
                            @if($dato->pedido->cantidad_entregada>0)
                           
                                @php
                                    $total_p=0;
                                    $total_p=$dato->pedido->cantidad_entregada *$dato->precio;
                                @endphp
                                <tr style="font-size: 10px !important;line-height:10px; "> 
                                    <td align="left" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                        {{$dato->itemlab->descri}} 
                                    </td>
                            
                                    <td align="center" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                        {{$dato->pedido->cantidad_pedida}}
                                    </td>

                                    <td align="center" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                        
                                        {{$dato->pedido->cantidad_entregada == '0' ? '': $dato->pedido->cantidad_entregada}}
                                    </td>
                                    <td align="center" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                        {{$dato->precio}} 
                                    </td>

                                

                                    <td align="center" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                        {{$dato->pedido->fecha_caducidad}}
                                        
                                    </td>

                                    <td align="center" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                        @if(!is_null($dato->existencia))
                                            {{$dato->existencia->lote == 'null' ? '': $dato->existencia->lote}}
                                        @endif
                                    </td>

                                    <td align="right" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                    {{number_format($total_p,2)}} 
                                    </td>
                                </tr>
                            @endif  

                            @php
                                $total=$total + $total_p;

                            @endphp

                        @endforeach
                    @endif
                </tbody>
                <tfoot >
                    <tr style="font-size:10px !important;line-height:5px" style="">
                        <td  colspan="6"style="font-size:9px;border: 0px; border-color: #D3D3D3;  text-align: right;">
                            <b>TOTAL</b>
                        </td>
                        <td style="border: 0px;border-color: #D3D3D3;  text-align: right; font-size:9px">
                           {{number_format($total,2)}} 
                           
                        </td>
                      
                    </tr>

                </tfoot>
            </table>
            
           
        </div>

       
        
        {{-- $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
        $pdf->text(490, 820, "Página $PAGE_NUM de $PAGE_COUNT", $font, 9); --}}
       
    </div>

    @if(!is_null($comprobante->entregado))
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
                <p class="encabezado1">RECIBI CONFORME
                    
                </p>
                <p class="encabezado1">
                    {{$comprobante->entregado->persona->ape1}} {{$comprobante->entregado->persona->ape2}}
                    {{$comprobante->entregado->persona->nom1}} {{$comprobante->entregado->persona->nom2}}
                </p>
                <p class="encabezado1">
                    {{$comprobante->entregado->persona->ci}}
                </p>
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
                <p class="encabezado1">ENTREGUE CONFORME</p>
                <p class="encabezado1">
                    

                    {{$comprobante->responsable->persona->ape1}} {{$comprobante->responsable->persona->ape2}} {{$comprobante->responsable->persona->nom1}} {{$comprobante->responsable->persona->nom2}}

                </p>
                <p class="encabezado1">{{$comprobante->responsable->persona->ci}}</p>
            </td>
            <td width="5%"></td>

            </tr>
        </table>
    @else
        <table width="100%">
            <tr>
            <td width="25%"></td>
            <td width="50%">
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
            <td width="25%"></td>

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