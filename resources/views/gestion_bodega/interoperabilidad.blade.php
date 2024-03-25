@extends('layouts.app')

@section('content')

    <style>
        .color_inactivo{
            background: #f3d5d5  !important
        }

        .color_activo{
            background:#ffff  !important
        }
    </style>

    <style>
        .color_caducado{
            background: #ee6767  !important
        }

        .color_x_caducar{
            background:#f7fa65  !important
        }

        .color_rotura{
            background:#87f17d  !important
        }

        .color_minimo{
            background: #eeeabb  !important
        }

        .color_critico{
            background:#f7cdc8  !important
            /* background:yellow  !important */
        }

    </style>
    <link rel="stylesheet" href="{{asset('bower_components/sweetalert/sweetalert.css')}}">
    
    <section class="content-header" id="arriba">
        <h1>
           Interoperabilidad Nacional
        </h1>

    </section>

    <section class="content" id="content_form">

        <div class="box" id="listado_persona">
            <div class="box-header with-border">
                <h3 class="box-title" id="tituloCabecera"> </h3>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip"
                        title="Collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                    
                </div>

              
            </div>
            <div class="box-body">

              

                <div id="listado_individual_"  >
                    <div class="col-md-12">
                        <form id="frm_buscar_estad" class="form-horizontal" action="" autocomplete="off">
                            <div class="form-group">
                                <label for="inputEmail3" id="label_crit" class="col-sm-3 control-label" >Filtrar:</label>
                                
                                <div class="col-sm-7" style="font-weight: normal;">                     
                                    <select data-placeholder="Seleccione Una Opcion" style="width: 100%;" class="form-control select2" name="cmb_filtra" id="cmb_filtra" onchange="Filtrados()" >
                            
                                        <option value="M" selected>Medicamentos</option>
                                        <option value="I" >Insumos</option>
                                        <option value="D" >Dispositivos</option>
                                     
                                    </select>
                                </div>
                                        
                            </div>
                        </form>

                    </div>

                    <div class="col-md-12">
                        <center>
                            <button type="button" class="btn btn-xs btn-primary" onclick="verReportesIindividual()">Descargar</button>
                            <button type="button" class="btn btn-xs btn-success" onclick="interOperar()">Interoperar</button>
                        </center>
                    </div>

                    
                    <div class="table-responsive col-md-12" style="margin-bottom:20px; margin-top:20px">

                        <table id="tabla_interoperabilidad" width="100%"class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th id="tipo_item">Cum</th>
                                    <th>Nombre</th>
                                    <th>Lote</th>
                                    <th>Stock</th>
                                    <th>F. Elaboracion</th>
                                    <th>F. Caducidad</th>
                                    <th>Precio</th>
                                 
                                   
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="7"><center>No hay Datos Disponibles</td>
                                </tr>
                                
                            </tbody>
                        
                        </table>  
                    </div>    

                </div>

                

            </div>

        </div>


    </section>

@endsection
@section('scripts')

    <script src="{{ asset('js/gestionBodega/interoperabilidad.js?v='.rand())}}"></script>
    <script src="{{asset('bower_components/sweetalert/sweetalert.js')}}"></script>
    <script>
        $('#tituloCabecera').html('Listado')
        Filtrados()
    </script>

@endsection
