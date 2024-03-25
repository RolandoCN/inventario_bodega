@extends('layouts.app')

@section('content')

    
    <section class="content-header" id="arriba">
        <h1>
            Listado Ingreso Bodega
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
                    <form id="frm_buscarAuditoria" class="form-horizontal" action="" autocomplete="off">
                        {{ csrf_field() }}
                        <div class="box-body">

                            <div class="form-group">
                                <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Buscar Por:</label>
                                
                                <div class="col-sm-10" style="font-weight: normal;">                     
                                    <select data-placeholder="Seleccione Una Opcion" style="width: 100%;" class="form-control select2" id="busqueda_ingreso_cmb" name="busqueda_ingreso_cmb" onchange="fitroBusqueda()">
                                        <option value="" selected></option>   
                                        <option value="B">Bodeguero</option>   
                                        <option value="P">Proveedor</option>  
                                        <option value="T">Todos</option>                                       
                                    </select>
                                </div>
                                        
                            </div>

                            <div id="busqueda_bodeguero" style="display: none">    
                                <div class="form-group">
                                    <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Datos Bodeguero:</label>
                                    
                                    <div class="col-sm-10" style="font-weight: normal;">                     
                                        <select data-placeholder="Busqueda por Numero Cedula o Nombres del Bodeguero" style="width: 100%;" class="form-control select2" id="bodeguero_cmb" name="bodeguero_cmb">
                                            <option value=""></option>
                                        </select>
                                    </div>
                                            
                                </div>

                            </div>

                            <div id="busqueda_proveedor" style="display: none">    
                                <div class="form-group">
                                    <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Datos Proveedor:</label>
                                    
                                    <div class="col-sm-10" style="font-weight: normal;">                     
                                        <select data-placeholder="Busqueda por RUC o Nombres de la Empresa" style="width: 100%;" class="form-control select2" id="proveedor_cmb" name="proveedor_cmb">
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
                                
                                    <button type="button" onclick="buscarIngresos()" class="btn btn-success btn-sm">
                                        Buscar
                                    </button>
                                  
                                </div>
                            </div>
                            
                        </div>
                    </form>
                </div>

                <div id="listado_permiso" >
                    <div class="table-responsive" style="margin-bottom:20px; margin-top:10px">
                        <table id="tabla_ingreso" width="100%"class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Comprobante</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Proveedor</th>
                                    <th class="text-center">Responsable</th>
                                    <th class="text-center">Bodega</th>
                                    <th class="text-center">Tipo</th>
                                    <th class="text-center">Total</th>
                                    <th style="min-width: 30%" class="text-center">Opciones</th>
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
        @include('gestion_bodega.modal_doc')
     
    </section>

@endsection
@section('scripts')

    <script src="{{ asset('js/gestionBodega/listado_ingreso_bodega.js?v='.rand())}}"></script>
    <script>
        $('#tituloCabecera').html('Buscar')
        buscarProveedor()
        buscarBodeguero()
    </script>

@endsection
