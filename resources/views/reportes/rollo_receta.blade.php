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
            /* font-family: 'Times New Roman'; */
        }

        h1 {
            font-size: 12px;
        }

        h2 {
            font-size: 2px;
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
        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="ticket centrado">
      
        {{-- <h2 style="margin-top:5px">HOSPITAL GENERAL DE CHONE</h2> --}}
      

        {{-- @php    
            $farm="S";
            if($farm=="S"){
                // $tabla_uno="90%";
                // $tabla_dos="100%";
                // $mar_uno="10px";
                // $mar_dos="2px";
                // $mar_dos_derecha="10px";

                $tabla_uno="100%";
                $tabla_dos="100%";
                $mar_uno="15px";
                $mar_dos="1px";
                $mar_dos_derecha="15px";

            }else{
                $tabla_uno="70%";
                $tabla_dos="70%";
                $mar_uno="30px";
                $mar_dos="30px";
                $mar_dos_derecha="30px";
            }
        @endphp --}}


        @php    
        $farm="S";
        if($farm=="S"){
            // $tabla_uno="90%";
            // $tabla_dos="100%";
            // $mar_uno="10px";
            // $mar_dos="2px";
            // $mar_dos_derecha="10px";

            $tabla_uno="100%";
            $tabla_dos="100%";
            $mar_uno="20px";
            $mar_dos="20px";
            $mar_dos_derecha="15px";

        }else{
            $tabla_uno="70%";
            $tabla_dos="70%";
            $mar_uno="30px";
            $mar_dos="30px";
            $mar_dos_derecha="30px";
        }
    @endphp

      
        <table width="{{$tabla_dos}}" style="padding-bottom:2px !important; margin-left:{{$mar_dos}};margin-right:{{$mar_dos_derecha}};margin-top:2px">
            <thead>

                <tr class="centrado_" style="font-size:14px !important; line-height:40px !important">
                    <th><span style="color:white ">ccc</span></th>
                </tr>
               
                <tr class="centrado_" style="font-size:14px !important">
                    <th >HOSPITAL GENERAL DE CHONE</th>
                </tr>

                
                <tr class="centrado_" style="font-size:14px !important">
                    <th> {{$comprobante->documento}}</th>
                </tr>
                <tr class="centrado_" style="font-size:14px !important">
                    <th> 
                        {{$comprobante->paciente}}
                     
                    </th>
                </tr>
                <tr class="centrado_" style="font-size:14px !important">
                    <th ><b>AREA: </b> 
                        {{$comprobante->area_selec}}
                    </th>
                </tr>

                <tr class="centrado_" style="font-size:14px !important">
                    <th ><b>SERVICIO: </b> <span style="font-weight: 500  !important">{{$comprobante->servicio_selec}}</span></th>
                </tr>


            </thead>           
        </table>
           
     
    </div>
 

</body>

</html>