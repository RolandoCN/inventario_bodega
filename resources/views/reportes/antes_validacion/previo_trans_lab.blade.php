<?php
$medidaTicket = 1;

?>
<!DOCTYPE html>
<html>

<head>

    <style>

        
        * {
            /* font-size: 12px; */
            font-family: 'DejaVu Sans', serif;
        }

        h1 {
            font-size: 12px;
        }

        h2 {
            font-size: 11px;
            line-height: 9px;
        }

        .ticket {
            margin: 2px;
        }

        td,
        th,
        tr,
        table {
           /* border-top: 1px solid black;*/
            border-collapse: collapse;
            margin: 0 auto;
        }

        td.precio {
            text-align: right;
            font-size: 11px;
        }

        td.cantidad {
            font-size: 11px;
        }

        td.producto {
            text-align: center;
        }

        th {
            text-align: center;
        }


        .centrado {
            text-align: center;
            align-content: center;
        }

        /* .ticket {
            width: <?php echo $medidaTicket ?>px;
            max-width: <?php echo $medidaTicket ?>px;
        } */

        img {
            max-width: inherit;
            width: inherit;
        }

        * {
            margin: 0;
            padding: 0;
        }

        .ticket {
            margin: 1;
            padding: 1;
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
            padding-top: 10px;
            font-size: 9px;
        }
        .encabezado2{
            text-align: center;
            padding-top: 10px;
            padding-bottom: 10px;
            font-size: 10px;
        }
        p{
            line-height: 5px;
            font-size: 10px;
        }

        body {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="ticket centrado">
        <h1 style="margin-top:5px !important">MINISTERIO DE SALUD PUBLICA</h1>
        <h2 style="margin-top:3px">HOSPITAL GENERAL</h2>
        <h2>DR NAPOLEÓN DÁVILA CÓRDOVA</h2>

        @php    
            if($farm=="S"){
                // $tabla_uno="90%";
                // $tabla_dos="100%";
                // $mar_uno="10px";
                // $mar_dos="2px";
                // $mar_dos_derecha="10px";

                $tabla_uno="95%";
                $tabla_dos="95%";
                $mar_uno="15px";
                $mar_dos="10px";
                $mar_dos_derecha="10px";

            }else{
                $tabla_uno="70%";
                $tabla_dos="70%";
                $mar_uno="30px";
                $mar_dos="30px";
                $mar_dos_derecha="30px";
            }
        @endphp

        
        <table width="{{$tabla_uno}}" style="padding-bottom:2px !important; margin-left:{{$mar_uno}};margin-right:{{$mar_uno}};margin-top:10px; border-top: 0px solid rgb(252, 24, 24) !important">

                <tr class="centrado" style="font-size: 10px !important">
                    <td width="100%" style="text-align: center"><b>SOLICITUD DE EGRESO EN {{$bodega_cons->nombre}} </b> </td>
                </tr>
                <tr class="centrado" style="font-size: 10px !important">
                    <td width="100%" style="text-align: center"><b>{{$comprobante->descripcion}} {{$comprobante->secuencial}}</b> </td>
                </tr>
           
                <tr class="centrado" style="font-size: 10px !important">
                    <td width="50%" style="text-align: left"><b>AREA:</b>{{$comprobante->nomarea->descripcion}} </td>
                </tr>

                <tr class="centrado" style="font-size: 10px !important">
                  
                    <td width="50%" style="text-align: left"><b>FECHA SOLIC:</b>{{$comprobante->fecha_hora}} </td>
                </tr>

           
        </table>


        <table width="{{$tabla_dos}}" style="padding-bottom:2px !important; margin-left:{{$mar_dos}};margin-right:{{$mar_dos_derecha}};margin-top:2px">
            <thead>
                <tr class="centrado" style="font-size: 10px !important">
                    <th >ITEM</th>
                    <th >CANT</th>
                    <th >LOTE</th>
                </tr>
            </thead>
            <tbody>

                @if(isset($comprobante))
                    @php
                        $total=0;
                    @endphp
                
                    @foreach($comprobante->detalle_lab as $e=>$dato)
                        @if($dato->pedido->cantidad_pedida>0)
                    
                            @php
                                $total_p=0;
                                $total_p=$dato->pedido->cantidad_pedida *$dato->precio;
                            @endphp
                    
                            
                            <tr style="font-size: 10px !important;border-top: 1px solid black">
                                <td width="60%"> 
                                    <p style="line-height:7px">{{$dato->laboratorio->descri}}</p>
                                 </td>
                                <td width="20%" style="text-align: center">
                                    {{$dato->pedido->cantidad_pedida == '0' ? '': $dato->pedido->cantidad_pedida}}
                                </td>
                                <td width="20%" >  
                                    @if(!is_null($dato->pedido))
                                        {{$dato->pedido->lote == 'null' ? '': $dato->pedido->lote}}
                                    @endif
                                </td>
                            </tr>
                                
                        @endif
                        @php
                            // $total=$total + $total_p;

                        @endphp

                    @endforeach
                @endif
            </tbody>

           
        </table>

     
    </div>
 

    <div style="margin-top:60px; margin-left:30px;margin-right:30px;">
       
        <p class="encabezado1_" style="line-height: 10px">
            Fecha y Hora de Impresión:
         
        </p>
        <p class="encabezado1_" style="line-height: 10px">
          
            {{date('d/m/Y H:i:s')}}
        </p>
      
    </div>

</body>

</html>