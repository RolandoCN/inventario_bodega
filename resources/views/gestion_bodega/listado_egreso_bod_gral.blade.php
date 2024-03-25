@extends('layouts.app')

@section('content')

    
    <section class="content-header" id="arriba">
        <h1>
            Listado Egreso Bodega
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

                <div class="col-md-12" id="content_consulta">
                    <form id="frm_buscar" class="form-horizontal" action="" autocomplete="off">
                        {{ csrf_field() }}
                        <div class="box-body">

                            <div class="form-group">
                                <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Buscar Por:</label>
                                
                                <div class="col-sm-10" style="font-weight: normal;">                     
                                    <select data-placeholder="Seleccione Una Opcion" style="width: 100%;" class="form-control select2" id="busqueda_ingreso_cmb" name="busqueda_ingreso_cmb" onchange="fitroBusqueda()">
                                        <option value="" selected></option>   
                                        <option value="B">Despachador</option>   
                                        <option value="T">Todos</option>                                       
                                    </select>
                                </div>
                                        
                            </div>

                            <div id="busqueda_bodeguero" style="display: none">    
                                <div class="form-group">
                                    <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Datos Despachador:</label>
                                    
                                    <div class="col-sm-10" style="font-weight: normal;">                     
                                        <select data-placeholder="Busqueda por Numero Cedula o Nombres del Despachador" style="width: 100%;" class="form-control select2" id="bodeguero_cmb" name="bodeguero_cmb">
                                            <option value=""></option>
                                        </select>
                                    </div>
                                            
                                </div>

                            </div>

                            <div class="form-group">
                                <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Fecha Inicio:</label>
                                
                                <div class="col-sm-10" style="font-weight: normal;">                     
                                    <input type="date"  class="form-control" id="bus_fecha_ini"  name="bus_fecha_ini" >
                                </div>
                                        
                            </div>

                            <div class="form-group">
                                <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Fecha Fin:</label>
                                
                                <div class="col-sm-10" style="font-weight: normal;">                     
                                    <input type="date"  class="form-control" id="bus_fecha_fin"  name="bus_fecha_fin" >
                                </div>
                                        
                            </div>

                            <div class="form-group">
                                <div class="col-sm-12 col-md-offset-2" >
                                
                                    <button type="button" onclick="buscarEgresos()" class="btn btn-success btn-sm">
                                        Buscar
                                    </button>
                                  
                                </div>
                            </div>
                            
                        </div>
                    </form>
                </div>

                <div id="listado_permiso" >
                    <div class="table-responsive" style="margin-bottom:20px; margin-top:10px">
                        <table id="tabla_egreso" width="100%"class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Comprobante</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Motivo</th>
                                    <th class="text-center">Responsable</th>
                                    <th class="text-center">Bodega</th>
                                    {{-- <th class="text-center">Total</th> --}}
                                    <th style="min-width: 30%" class="text-center">Opciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6"><center>No hay Datos Disponibles</td>
                                </tr>
                                
                            </tbody>
                        
                        </table>  
                    </div>    
                </div>

               
            </div>

        </div>
        @include('gestion_bodega.modal_doc')

    </section>

@endsection
@section('scripts')

    <script src="{{ asset('js/gestionBodega/listado_egreso_bodega.js?v='.rand())}}"></script>
    <script>
        $('#tituloCabecera').html('Buscar')
        buscarBodeguero()
    </script>

@endsection
