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

   
    <div style="margin-bottom:30px; margin-top:10px;">

        <table class="ltable" style="" border="0" width="100%" style="padding-bottom:2px !important">
          
            <tr style="font-size: 10px"  class="fuenteSubtitulo " style=""> 
                <th colspan="11" style="border-color:white;height:35px;text-align: center;border:0 px" width="100%"  >  
                    EGRESO EN {{$bodega->nombre}}<br>
                                   
                </th>
            
            </tr>

            <tr style="font-size: 12px"  class="fuenteSubtitulo " style=""> 
                <td colspan="4" style="border-color:white;height:5px;text-align: left;border:0 px" width="100%"  >  
                    <b>GENERADO:</b> {{auth()->user()->persona->ape1}} {{auth()->user()->persona->ape2}} {{auth()->user()->persona->nom1}} {{auth()->user()->persona->nom2}}
               
                </td>

                <td colspan="4" style="border-color:white;height:5px;text-align: left;border:0 px" width="100%"  >  

                    <b>FECHA:</b> {{date('d-m-Y H:i:s')}}
                </td>
            
            </tr> 
        
            <tr style="font-size: 12px"  class="fuenteSubtitulo " style=""> 
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
                    
                    <th width="20%" style="border: 0px; ;border-color: #D3D3D3; text-align: center; line-height:10px">#</th>

                    <th width="60%" style="border: 0px; ;border-color: #D3D3D3; text-align: center; line-height:10px">AREA</th>

                    <th width="20%" style="border: 0px; ;border-color: #D3D3D3; text-align: center">CANTIDAD</th>


                
                </tr>

                <tbody>
                    
                    @if(isset($listar))

                        @php
                            $i=1;
                            $total=0
                        @endphp
                        @foreach($listar as $e=>$dato_agr)

                            @php
                                $total=$total+sizeof($dato_agr);
                            @endphp
                         
                            <tr style="font-size: 10px !important;line-height:10px; "> 
                                
                                <td align="center" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                    {{$i++}}
                                </td>

                                <td align="center" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                    {{-- {{$e}}  --}}
                                    {{$e == 'CE' ? 'CONSULTA EXTERNA': strtoupper($e)}}
                                </td>
                        
                                <td align="center" style="border-top: 0px;border-left: 0px; border-bottom: 0px;border-center:0px;border-right:0px;border-color: #D3D3D3">
                                    {{sizeof($dato_agr)}}
                                </td>

                            </tr> 
                           
                        @endforeach
                    @endif
                </tbody>

                <tfoot >
                    <tr style="font-size:10px !important;line-height:5px" style="">
                        <td  colspan="2"style="font-size:9px;border: 0px; border-color: #D3D3D3;  text-align: right;">
                            <b>TOTAL</b>
                        </td>
                        <td style="border: 0px;border-color: #D3D3D3;  text-align: center; font-size:9px">
                           {{$total}} 
                           
                        </td>
                      
                    </tr>

                </tfoot>
            
            </table>
            
           
        </div>

       
        
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