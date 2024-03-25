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
                    DESCARGO EN {{$bodega_selecc->nombre}} <br>DEL ITEM {{$item}}
                 
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
        @php
            $total_suma=0;
        @endphp
        @foreach($listar as $key=> $info)
            <div style="margin-top:12px;">
                
                <table class="ltable"  border="0" width="100%" style="padding-bottom:2px !important">
                    
                    <tr style="font-size: 10px !important; background-color:white;line-height:10px; "> 

                        <th width="100%" colspan="6" style="border: 0px; ; text-align: center; line-height:20px">     
                            {{$key}}
                        </th>
                    
                    </tr>
                    
                    <tr style="font-size: 10px !important; background-color: #D3D3D3;line-height:10px; "> 

                        <th width="50%" style="border: 0px; ;border-color: #D3D3D3; text-align: center; line-height:10px">RESPONSABLE</th>
                        
                        <th width="30%" style="border: 0px; ;border-color: #D3D3D3; text-align: center; line-height:10px">FECHA</th>


                        <th width="20%" style="border: 0px; ;border-color: #D3D3D3; text-align: center">CANTIDAD</th>

                        
                    
                    </tr>

                    <tbody>
                        
                       
                            @php
                                $total=0;
                                
                                
                            @endphp
                        
                            @foreach($info as $e=>$dato)
                                @if($dato->resta > 0)
                                    @php
                                       $total=$total + $dato->resta
                                           
                                    @endphp
                                    <tr style="font-size: 10px !important;line-height:10px; "> 
                                        <td align="left" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                            {{$dato->responsable}} 
                                        </td>

                                        <td align="center" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                            {{$dato->fecha_hora}} 
                                        </td>
                                        <td align="right" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                            {{$dato->resta}}
                                        </td>
                                        
                                    </tr>

                                @endif

                            @endforeach
                        
                    </tbody>
                    <tfoot >
                        <tr style="font-size:10px !important;line-height:5px" style="">
                            <td  colspan="2"style="font-size:9px;border: 0px; border-color: #D3D3D3;  text-align: right;">
                                <b>TOTAL</b>
                            </td>
                            <td style="border: 0px;border-color: #D3D3D3;  text-align: right; font-size:9px">
                            {{$total}} 
                            
                            </td>
                        
                        </tr>

                    </tfoot>
                    @php
                        $total_suma=$total_suma + $total;
                    @endphp
                </table>           
            </div>
        @endforeach

        <p style="font-size:12px; text-align:center; font-weight:600"><b>TOTAL: </b>{{$total_suma}}</p>

       
        
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