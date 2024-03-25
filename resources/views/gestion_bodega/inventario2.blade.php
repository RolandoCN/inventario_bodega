@extends('layouts.app')

@section('content')

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
    </style>

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
           Inventario Farmacia
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
                                <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Busqueda:</label>
                                
                                <div class="col-sm-10" style="font-weight: normal;">                     
                                    <select data-placeholder="Seleccione Una Bodega" style="width: 100%;" class="form-control select2" name="cmb_bodega" id="cmb_bodega"  >
                            
                                        @foreach ($bodega as $dato)
                                            <option value=""></option>
                                            <option value="{{ $dato->idbodega}}" >{{ $dato->nombre }} </option>
                                        @endforeach
        
                                    </select>
                                </div>
                                        
                            </div>

                            <div class="form-group">
                                <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Lugar:</label>
                                
                                <div class="col-sm-10" style="font-weight: normal;">                     
                                    <select data-placeholder="Seleccione Un Lugar" style="width: 100%;" class="form-control select2" name="cmb_tipo" id="cmb_tipo"  >
                            
                                        <option value=""></option>
                                        {{-- <option value="BODEGA" >BODEGA</option> --}}
                                        <option value="FARMACIA" >FARMACIA</option>
                                      
                                    </select>
                                </div>
                                        
                            </div>

                            <div class="form-group">
                                <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Tipo:</label>
                                
                                <div class="col-sm-10" style="font-weight: normal;">                     
                                    <select data-placeholder="Seleccione Una Opcion" style="width: 100%;" class="form-control select2" name="cmb_opcion" id="cmb_opcion" onchange="filtroOpcion()" >
                                    {{-- <select data-placeholder="Seleccione Una Opcion" style="width: 100%;" class="form-control select2" name="cmb_opcion" id="cmb_opcion"> --}}
                            
                                        <option value=""></option>
                                        <option value="Individual" >INDIVIDUAL</option>
                                        <option value="Agrupado" >AGRUPADO</option>
                                      
                                    </select>
                                </div>
                                <input type="hidden" name="fecha_actual" id="fecha_actual" value="{{date('Y-m-d')}}">
                                        
                            </div>

                            <div class="form-group" id="seccion_cmb_filtra_" style="display: none">
                                <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Filtra Fecha:</label>
                                
                                <div class="col-sm-10" style="font-weight: normal;">                     
                                    <select data-placeholder="Seleccione Una Opcion" style="width: 100%;" class="form-control select2" name="cmb_filtra_fecha" id="cmb_filtra_fecha" onchange="filtraFecha()">
                            
                                        <option value=""></option>
                                        <option value="T" >TODOS</option>
                                        <option value="F" >FILTRO</option>
                                      
                                    </select>
                                </div>
                                
                            </div>

                            <div id="seccion_fecha" style="display: none">
                                <div class="form-group">
                                    <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Fecha Inicio:</label>
                                    
                                    <div class="col-sm-10" style="font-weight: normal;">                     
                                    <input type="date" class="form-control" name="fecha_ini" id="fecha_ini" >
                                    </div>
                                    
                                </div>

                                <div class="form-group">
                                    <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Fecha Fin:</label>
                                    
                                    <div class="col-sm-10" style="font-weight: normal;">                     
                                    <input type="date" class="form-control" name="fecha_fin" id="fecha_fin" >
                                    </div>
                                    
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-12 col-md-offset-2" >
                                
                                    <button type="button" onclick="buscarInventario()" class="btn btn-success btn-sm">
                                        Buscar
                                    </button>
                                  
                                </div>
                            </div>
                            
                        </div>
                    </form>
                </div>

                <div id="listado_individual" style="display: none" >
                    <div class="col-md-12">
                        <form id="frm_buscar_estad" class="form-horizontal" action="" autocomplete="off">
                            <div class="form-group">
                                <label for="inputEmail3" id="label_crit" class="col-sm-3 control-label" >Filtrar:</label>
                                
                                <div class="col-sm-7" style="font-weight: normal;">                     
                                    <select data-placeholder="Seleccione Una Opcion" style="width: 100%;" class="form-control select2" name="cmb_filtra" id="cmb_filtra" onchange="Filtrados()" >
                            
                                        <option value="Todos" selected>TODOS</option>
                                        <option value="Caducados" >CADUCADOS</option>
                                        <option value="Porcaducar" >POR CADUCAR</option>
                                        <option value="Rotura" >ROTURA</option>
                                    
                                    </select>
                                </div>
                                        
                            </div>
                        </form>

                    </div>
                    <div class="col-md-12">
                        <center>
                            {{-- <button type="button" class="btn btn-xs btn-success" onclick="DescargarInventarioInd()">Descargar</button> --}}
                            <button type="button" class="btn btn-xs btn-primary" onclick="verReportesIindividual()">Reportes</button>
                           
                        </center>
                    </div>
                    <div class="table-responsive" style="margin-bottom:20px; margin-top:10px">
                        <table id="tabla_inventario" width="100%"class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Cum-Cudim-Codigo</th>
                                    <th>Nombre</th>
                                    <th>Lote</th>
                                    <th>Stock</th>
                                    <th>F. Caducidad</th>
                                    <th>Precio</th>
                                    <th></th>
                                   
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

                <div id="listado_global" style="display: none">

                    <div class="row">
                        <div class="col-md-6">
                            <ul class="nav nav-pills nav-stacked"style="margin-left:80px">
                                <li style="border-color: white"><a><i class="fa fa-building text-blue"></i> <b class="text-black" style="font-weight: 650 !important">Bodega</b>: <span  id="bodega_seleccionada"></span></a></li>
                                
                            </ul>
                            
                        </div>     
                        <div class="col-md-6">
                            <ul class="nav nav-pills nav-stacked" style="margin-left:22px">
                                <li style="border-color: white"><a><i class="fa fa-home text-blue"></i> <b class="text-black" style="font-weight: 650 !important">Lugar:</b> <span  id="lugar_seleccionado"> </span></a></li>
                                
                            </ul>
                            
                        </div>  
                    </div>

                    <div class="col-md-12">
                        <form id="frm_buscar_estad2" class="form-horizontal" action="" autocomplete="off">
                            <div class="form-group">
                                <label for="inputEmail3" id="label_crit" class="col-sm-4 control-label" >Filtrar:</label>
                                
                                <div class="col-sm-5" style="font-weight: normal;">                     
                                    <select data-placeholder="Seleccione Una Opcion" style="width: 100%;" class="form-control select2" name="cmb_filtra_glo" id="cmb_filtra_glo" onchange="FiltradosGLobal()" >
                            
                                        <option value="Todos" selected>TODOS</option>
                                        <option value="Minimo">STOCK MINIMO</option>
                                        <option value="Critico">STOCK CRITICO</option>
                                      
                                    
                                    </select>
                                </div>
                                        
                            </div>
                        </form>

                    </div>
                    <div class="col-md-12">
                        <center>
                          
                            {{-- <button type="button" class="btn btn-xs btn-success" onclick="descargarPdf()">Inventario</button>
                            <button type="button" class="btn btn-xs btn-primary" onclick="descargarEgresos()">Egresos PDF</button>
                            <button type="button" class="btn btn-xs btn-info" onclick="descargarEgresosExcel()">Egresos Excel</button>
                            <button type="button" class="btn btn-xs btn-warning" onclick="descargarEgresosArea()">Egresos Area PDF</button>
                            <button type="button" class="btn btn-xs btn-danger" onclick="descargarEgresosAreaExcel()">Egresos Area Excel</button> --}}
                            
                            <button type="button" class="btn btn-xs btn-primary" onclick="seccionReportes()">Reportes</button>
                            <button type="button" class="btn btn-xs btn-success" onclick="verEgresoArea()">Egreso Area</button>

                        </center>
                    </div>

                    <div class="table-responsive" style="margin-bottom:20px; margin-top:20px">

                        <table id="tabla_inventario_global" width="100%"class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Codigo</th>
                                    <th class="text-center">Descripcion</th>
                                    <th class="text-center">Stock</th>
                                    <th class="text-center">Precio Prom</th>
                                    <th class="text-center">Info</th>
                                    <th class="text-center"></th>
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

        <div class="modal fade_ detalle_class"  id="modal_detalle_producto" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">DETALLE</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div id="listado_detalle_lote" >
                                <div class="row1">
                                    <div class="col-md-6">
                                        <ul class="nav nav-pills nav-stacked"style="margin-left:80px">
                                            <li style="border-color: white"><a><i class="fa fa-sort-numeric-asc text-blue"></i> <b class="text-black" style="font-weight: 650 !important">Stock</b>: <span  id="total_bodega"></span></a></li>
                                        
                                        </ul>

                                        <ul class="nav nav-pills nav-stacked"style="margin-left:80px">
                                            <li style="border-color: white"><a><i class="fa fa-sort-numeric-asc text-blue"></i> <b class="text-black" style="font-weight: 650 !important">Inconsistencia</b>: <span  id="inconsistencia"></span></a></li>
                                        
                                        </ul>
                                    
                                    </div>     
                                    <div class="col-md-6">
                                        <ul class="nav nav-pills nav-stacked" style="margin-left:22px">
                                            <li style="border-color: white"><a><i class="fa fa-sort-numeric-asc text-blue"></i> <b class="text-black" style="font-weight: 650 !important">Suma:</b> <span  id="sumado"> </span></a></li>
                                            
                                        </ul>

                                        <ul class="nav nav-pills nav-stacked" style="margin-left:22px">
                                            <li style="border-color: white"><a><i class="fa fa-sort-numeric-asc text-blue"></i> <b class="text-black" style="font-weight: 650 !important">Diferencia:</b> <span  id="diferencia"> </span></a></li>
                                            
                                        </ul>
                                    
                                    </div>  

                                    <center>
                                        <button type="button" onclick="imprimirEgresoItem()" class="btn btn-success btn-xs">
                                            Imprimir Descargos
                                        </button>
                                    </center>
                                </div>

                                                      
                           
                                <div class="table-responsive col-md-12" style="margin-bottom:20px; margin-top:10px">
                                    <table id="tabla_detallle" width="100%"class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Cum-Cudim-Codigo</th>
                                                <th class="text-center">Nombre</th>
                                                <th class="text-center">Stock</th>
                                                <th class="text-center">Precio</th>
                                                <th class="text-center"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="5"><center>No hay Datos Disponibles</td>
                                            </tr>
                                            
                                        </tbody>
                                    
                                    </table>  
                                </div>    

                                <div class="col-md-12" style="margin-top: 15px !important">
                                    <center>
                                        <button onclick="cerrar()"  id="btn_cancelar" type="button" class="btn btn-danger btn-xs" ><span class="fa fa-times"></span> Cerrar</button>
    
                                    </center>
                                </div>
                            </div>

                            <div id="listado_detalle_suma" style="display: none">
                                <div class="col-md-12">
                                    <form id="frm_buscar_movimientos" class="form-horizontal" action="" autocomplete="off">
                                        {{ csrf_field() }}
                                        <div class="box-body">
                
                                            <div class="form-group">
                                                <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Codigo:</label>
                                                
                                                <div class="col-sm-9" style="font-weight: normal;">                     
                                                    <input type="text" readonly class="form-control" name="codigo_item_selecc" id="codigo_item_selecc">

                                                    <input type="hidden" readonly class="form-control" name="id_item_selecc" id="id_item_selecc">

                                                    <input type="hidden" readonly class="form-control" name="id_bodega_selecc" id="id_bodega_selecc">
                                                </div>
                                                        
                                            </div>

                                            <div class="form-group">
                                                <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Item:</label>
                                                
                                                <div class="col-sm-9" style="font-weight: normal;">                     
                                                    <input type="text" readonly class="form-control" name="item_selecc" id="item_selecc">
                                                </div>
                                                        
                                            </div>

                                            <div class="form-group lote_indiv" style="display: none" >
                                                <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Lote:</label>
                                                
                                                <div class="col-sm-9" style="font-weight: normal;">                     
                                                    <input type="text" readonly class="form-control" name="lote_selecc" id="lote_selecc">
                                                </div>
                                                        
                                            </div>

                                            <div class="global_info">
                                                <div class="form-group">
                                                    <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Stock:</label>
                                                    
                                                    <div class="col-sm-3" style="font-weight: normal;">                     
                                                        <input type="text" readonly class="form-control" name="stock_selecc" id="stock_selecc">
                                                    </div>

                                                    <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Suma:</label>
                                                    
                                                    <div class="col-sm-4" style="font-weight: normal;">                     
                                                        <input type="text" readonly class="form-control" name="suma_selecc" id="suma_selecc">
                                                    </div>
                                                            
                                                </div>

                                                <div class="form-group">
                                                    <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Inconsistencia:</label>
                                                    
                                                    <div class="col-sm-3" style="font-weight: normal;">                     
                                                        <input type="text" readonly class="form-control" name="inconsistencia_selecc" id="inconsistencia_selecc">
                                                    </div>

                                                    <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Diferencia:</label>
                                                    
                                                    <div class="col-sm-4" style="font-weight: normal;">                     
                                                        <input type="text" readonly class="form-control" name="diferencia_selecc" id="diferencia_selecc">
                                                    </div>
                                                            
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >F Inicio:</label>
                                                
                                                <div class="col-sm-3" style="font-weight: normal;">                     
                                                    <input type="date"  class="form-control" name="f_inicio_mov" id="f_inicio_mov">
                                                </div>

                                                <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >F Fin:</label>
                                                
                                                <div class="col-sm-4" style="font-weight: normal;">                     
                                                    <input type="date"  class="form-control" name="f_fin_mov" id="f_fin_mov">
                                                </div>
                                                        
                                            </div>


                                            <div class="form-group">
                                                <div class="col-sm-12 " >
                                                
                                                    <center><button type="button" onclick="buscarMovimientos()" class="btn btn-success btn-sm">
                                                        Buscar
                                                    </button></center>
                                                  
                                                </div>
                                            </div>


                                        </div>
                                    </form>
                                </div>
                                <div class="table-responsive col-md-12" style="margin-bottom:20px; margin-top:10px">
                                    <table id="tabla_detallle_suma" width="100%"class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Responsable</th>
                                                <th class="text-center">Detalle</th>
                                                <th class="text-center">Fecha</th>
                                                <th class="text-center">Ingreso</th>
                                                <th class="text-center">Egreso</th>
                                                
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="5"><center>No hay Datos Disponibles</td>
                                            </tr>
                                            
                                        </tbody>
                                    
                                    </table>  
                                </div>    
                                <div class="col-md-12" style="margin-top: 15px !important">
                                    <center>
                                        {{-- <button onclick="atras()"   type="button" class="btn btn-danger btn-xs" ><span class="fa fa-print"></span> Imprimir Kardex</button> --}}

                                        <button onclick="kardexItem()" disabled  type="button" class="btn btn-danger btn-xs movimiento_item" ><span class="fa fa-print"></span> Imprimir Kardex</button>

    
                                    </center>
                                </div>
                            </div>

                            <div id="listado_detalle_egreso" style="display: none">
                                <div class="table-responsive col-md-12" style="margin-bottom:20px; margin-top:10px">
                                    <table id="tabla_detallle_egreso" width="100%"class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Funcionario</th>
                                                <th class="text-center">Area</th>
                                                <th class="text-center">Fecha</th>
                                                <th class="text-center">Cantidad</th>
                                              
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="4"><center>No hay Datos Disponibles</td>
                                            </tr>
                                            
                                        </tbody>
                                    
                                    </table>  
                                </div>    
                                {{-- <div class="col-md-12" style="margin-top: 15px !important">
                                    <center>
                                        <button onclick="atrasDetalle()"   type="button" class="btn btn-danger btn-xs" ><span class="fa fa-times"></span> Regresar</button>

                                    </center>
                                </div> --}}
                            </div>

                        </div>

                      

                    
                    </div>
                
                </div>

            </div>

        </div>


        <div class="modal fade_ detalle_class"  id="modal_reporteria" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">REPORTERIA</h4>
                       
                    </div>
                    <div class="modal-body">
                        <div class="row">
                           
                                <div class="col-md-12">
                                    <form id="frm_buscar_reporteria" class="form-horizontal" action="" autocomplete="off">
                                        {{ csrf_field() }}
                                        <div class="box-body">

                                            <div class="form-group">
                                                <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Fitrar Fecha:</label>
                                                
                                                <div class="col-sm-9" style="font-weight: normal;">                     
                                                    <select data-placeholder="Seleccione Una Opcion" style="width: 100%;" class="form-control select2" name="cmb_filtra_fecha_report" id="cmb_filtra_fecha_report" onchange="FiltradosReporteria()" >
                                                        <option value="" selected></option>
                                                        <option value="T">TODOS</option>
                                                        <option value="Filtro">FILTRAR</option>
                                                       
                                                    </select>
                                                </div>
                                                        
                                            </div>
                                            
                                            <div id="filtra_fecha_reporteria" style="display: none">
                                                <div class="form-group">
                                                    <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >F Inicio:</label>
                                                    
                                                    <div class="col-sm-9" style="font-weight: normal;">                     
                                                        <input type="date"  class="form-control" name="f_inicio_reporte" id="f_inicio_reporte">
                                                    </div>
                                                            
                                                </div>

                                                <div class="form-group">

                                                    <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >F Fin:</label>
                                                    
                                                    <div class="col-sm-9" style="font-weight: normal;">                     
                                                        <input type="date"  class="form-control" name="f_fin_reporte" id="f_fin_reporte">
                                                    </div>
                                                            
                                                </div>
                                            </div>


                                            <div class="form-group">
                                                <div class="col-sm-12 " >
                                                
                                                    <center>
                                                      
                                                        <button type="button" class="btn btn-xs btn-success" onclick="descargarPdf()">Inventario</button>
                                                        <button type="button" class="btn btn-xs btn-primary" onclick="descargarEgresos()">Egresos PDF</button>
                                                        <button type="button" class="btn btn-xs btn-info" onclick="descargarEgresosExcel()">Egresos Excel</button>
                                                        {{-- <button type="button" class="btn btn-xs btn-warning" onclick="descargarEgresosArea()">Egresos Area PDF</button> --}}
                                                        {{-- <button type="button" class="btn btn-xs btn-danger" onclick="descargarEgresosAreaExcel()">Egresos Area Excel</button> --}}
                                                    </center>
                                                  
                                                </div>
                                            </div>


                                        </div>
                                    </form>
                                </div>
                            
                            </div>

                        </div>
                    </div>
                
                </div>

            </div>

        </div>

        <div class="modal fade_ detalle_class"  id="modal_reporteria_indiv" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">REPORTERIA</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                           
                                <div class="col-md-12">
                                    <form id="frm_buscar_reporteria_ind" class="form-horizontal" action="" autocomplete="off">
                                        {{ csrf_field() }}
                                        <div class="box-body">

                                            <div class="form-group">
                                                <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Fitrar Fecha:</label>
                                                
                                                <div class="col-sm-9" style="font-weight: normal;">                     
                                                    <select data-placeholder="Seleccione Una Opcion" style="width: 100%;" class="form-control select2" name="cmb_filtra_fecha_report_ind" id="cmb_filtra_fecha_report_ind" onchange="FiltradosReporteriaInd()" >
                                                        <option value="" selected></option>
                                                        <option value="T">TODOS</option>
                                                        <option value="Filtro">FILTRAR</option>
                                                       
                                                    </select>
                                                </div>
                                                        
                                            </div>
                                            
                                            <div id="filtra_fecha_reporteria_ind" style="display: none">
                                                <div class="form-group">
                                                    <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >F Inicio:</label>
                                                    
                                                    <div class="col-sm-9" style="font-weight: normal;">                     
                                                        <input type="date"  class="form-control" name="f_inicio_reporte_ind" id="f_inicio_reporte_ind">
                                                    </div>
                                                            
                                                </div>

                                                <div class="form-group">

                                                    <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >F Fin:</label>
                                                    
                                                    <div class="col-sm-9" style="font-weight: normal;">                     
                                                        <input type="date"  class="form-control" name="f_fin_reporte_ind" id="f_fin_reporte_ind">
                                                    </div>
                                                            
                                                </div>
                                            </div>


                                            <div class="form-group">
                                                <div class="col-sm-12 " >
                                                
                                                    <center>
                                                        <button type="button" class="btn btn-xs btn-success" onclick="DescargarInventarioInd()">Descargar PDF</button>

                                                        <button type="button" class="btn btn-xs btn-primary" onclick="DescargarInventarioIndExcel()">Descargar Excel</button>
                                                    </center>
                                                  
                                                </div>
                                            </div>


                                        </div>
                                    </form>
                                </div>
                            
                            </div>

                        </div>
                    </div>
                
                </div>

            </div>

        </div>


        <div class="modal fade_ detalle_class"  id="modal_reporteria_egreso_area" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">EGRESO AREA</h4>
                       
                    </div>
                    <div class="modal-body">
                        <div class="row">

                                <div id="buscaEgresoSeccion">
                                    <div class="col-md-12">
                                        <form id="frm_buscar_reporteria_ea" class="form-horizontal" action="" autocomplete="off">
                                            {{ csrf_field() }}
                                            <div class="box-body">
                                                
                                                <div class="form-group">
                                                    <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >F Inicio:</label>
                                                    
                                                    <div class="col-sm-9" style="font-weight: normal;">                     
                                                        <input type="date"  class="form-control" name="f_inicio_reporte_ea" id="f_inicio_reporte_ea">
                                                    </div>
                                                            
                                                </div>

                                                <div class="form-group">

                                                    <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >F Fin:</label>
                                                    
                                                    <div class="col-sm-9" style="font-weight: normal;">                     
                                                        <input type="date"  class="form-control" name="f_fin_reporte_ea" id="f_fin_reporte_ea">
                                                    </div>
                                                            
                                                </div>

                                                <div class="form-group">
                                                    <div class="col-sm-12 col-md-offset-2" >
                                                                                                                                            
                                                        <button type="button" class="btn btn-xs btn-success" onclick="buscarEgresoAreaFarm()">Buscar</button>                                                       
                                                    
                                                    </div>
                                                </div>


                                            </div>
                                        </form>
                                    </div>

                                    <div class="col-md-12">
                                        
                                        <div class="table-responsive col-md-12" style="margin-bottom:20px; margin-top:10px">
                                            <table id="tabla_egreso_area" width="100%"class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th class="text-center">#</th>
                                                        <th class="text-center">Area</th>
                                                        <th class="text-center">Cantidad</th>
                                                        <th class="text-center"></th>
                                                    
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td colspan="4"><center>No hay Datos Disponibles</td>
                                                    </tr>
                                                    
                                                </tbody>
                                            
                                            </table>  
                                        </div>  

                                        <center>
                                            <button type="button" disabled id="btn_descarga_egreso" onclick="descargarEgresoFarm()" class="btn btn-warning btn-xs">Descargar</button>
                                        </center>

                                    </div>
                                </div>

                                <div class="col-md-12" id="detalle_egreso_area" style="display: none">

                                    <div class="col-md-12">
                                        <h3 id="area_egres_selecc" class="text-center"></h3>
                                        <h4 id="cant_area_egres_selecc" class="text-center"></h4>
                                        <input type="hidden" name="area_cod_selecc" id="area_cod_selecc">
                                        <center>
                                            <button type="button" class="btn btn-danger btn-xs" onclick="regresarBuscaEgresoSeccion()">Atras</button>
                                            <button type="button" class="btn btn-success btn-xs" onclick="pdfAreaEgresoFarm()">Descargar</button>
                                        </center>
                                    </div>

                                    <div class="table-responsive col-md-12" style="margin-bottom:20px; margin-top:10px">
                                        <table id="tabla_egreso_area_detalle" width="100%"class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">Solicita</th>
                                                    <th class="text-center">Comprobante</th>
                                                    <th class="text-center">Fecha Despacho</th>
                                                    <th class="text-center">Despacha</th>
                                                    <th class="text-center"></th>
                                                  
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="5"><center>No hay Datos Disponibles</td>
                                                </tr>
                                                
                                            </tbody>
                                        
                                        </table>  
                                    </div> 
                                    
                                 

                                </div>
                            
                            </div>

                        </div>
                    </div>
                
                </div>

            </div>

        </div>


        <div class="modal fade_ detalle_class"  id="modal_editar_item" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">ACTUALIZAR ITEM</h4>
                       
                    </div>
                    <div class="modal-body">
                        <div class="row">

                            <div class="col-md-12">
                                <form id="frm_actualizar_pb" class="form-horizontal" action="" autocomplete="off">
                                    {{ csrf_field() }}
                                    <div class="box-body">
                                        
                                        <div class="form-group">
                                            <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Codigo:</label>
                                            
                                            <div class="col-sm-9" style="font-weight: normal;">  
                                                <input type="hidden"  class="form-control" name="id_prod_bod_actualizar" id="id_prod_bod_actualizar">                   
                                                <input type="text"  class="form-control" name="codigo_actualizar" id="codigo_actualizar">
                                            </div>
                                                    
                                        </div>

                                        <div class="form-group">
                                            <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Codigo:</label>
                                            
                                            <div class="col-sm-9" style="font-weight: normal;">                     
                                                <input type="text" readonly  class="form-control" name="item_actualizar" id="item_actualizar">
                                            </div>
                                                    
                                        </div>

                                        <div class="form-group">

                                            <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Lote:</label>
                                            
                                            <div class="col-sm-9" style="font-weight: normal;">                     
                                                <input type="text"  class="form-control" name="lote_actualizar" id="lote_actualizar">
                                            </div>
                                                    
                                        </div>

                                        <div class="form-group">

                                            <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >F. Elaboracion:</label>
                                            
                                            <div class="col-sm-9" style="font-weight: normal;">                     
                                                <input type="date"  class="form-control" name="felab_actualizar" id="felab_actualizar">
                                            </div>
                                                    
                                        </div>

                                        <div class="form-group">

                                            <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >F. Caducidad:</label>
                                            
                                            <div class="col-sm-9" style="font-weight: normal;">                     
                                                <input type="date"  class="form-control" name="fcad_actualizar" id="fcad_actualizar">
                                            </div>
                                                    
                                        </div>

                                        <div class="form-group">

                                            <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Precio:</label>
                                            
                                            <div class="col-sm-9" style="font-weight: normal;">                     
                                                <input type="number"  class="form-control" name="precio_actualizar" id="precio_actualizar">
                                            </div>
                                                    
                                        </div>

                                        <div class="form-group">
                                            <div class="col-sm-12 col-md-offset-2" >
                                                                                                                                    
                                                <button type="button" onclick="actualizarProdBodega()" class="btn btn-sm btn-success">Actualizar</button>   
                                                
                                                <button type="button" class="btn btn-sm btn-danger" onclick="salirActualizacionPB()">Salir</button>   
                                            
                                            </div>
                                        </div>


                                    </div>
                                </form>
                            </div>

                           
                        </div>
                    </div>
                
                </div>

            </div>

        </div>

    </section>

@endsection
@section('scripts')

    <script src="{{ asset('js/gestionBodega/inventario_dos.js?v='.rand())}}"></script>
    <script src="{{asset('bower_components/sweetalert/sweetalert.js')}}"></script>

    <script>
        $('#tituloCabecera').html('Buscar')
    </script>

@endsection 
