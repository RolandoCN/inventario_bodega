@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{asset('bower_components/sweetalert/sweetalert.css')}}">

    <section class="content-header" id="arriba">
        <h1>
            Listado Pedido Solicitados 
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
                                
                                    <button type="button" onclick="buscarPedidos()" class="btn btn-success btn-sm">
                                        Buscar
                                    </button>
                                  
                                </div>
                            </div>
                            
                        </div>
                    </form>
                </div>

                <div id="listado_permiso" >
                    <div class="table-responsive" style="margin-bottom:20px; margin-top:10px">
                        <table id="tabla_pedido" width="100%"class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Nº</th>
                                    <th class="text-center">Emisor</th>
                                    <th class="text-center">Area</th>
                                    <th class="text-center">Fecha-Hora</th>
                                    <th class="text-center">Bodega</th>
                                    <th class="text-center">Estado</th>
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

                <div class="col-md-12" id="actualiza_seccion" style="display: none">

                    <form class="form-horizontal" id="form_pedido_bodega" autocomplete="off" method="post"
                    action="">
                        {{ csrf_field() }}
    
                   
                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-3 control-label">Motivo</label>
                            <div class="col-sm-6">
                                <textarea name="motivo" id="motivo" class="form-control" ></textarea>
                            </div>                           
                        </div>

                      
                        <input type="hidden" name="fecha_actual" id="fecha_actual" value="{{date('Y-m-d')}}">
                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-3 control-label">Bodega</label>
                            <div class="col-sm-6">
                                <select data-placeholder="Seleccione Una Bodega" style="width: 100%;" class="form-control select2" name="cmb_bodega" id="cmb_bodega"  >
                                
                               
    
                                </select>
                            </div>   
                            <input type="hidden" name="bodega_seleccionda" id="bodega_seleccionda">                        
                        </div>
                       
                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-3 control-label">Item</label>
                            <div class="col-sm-6">
                                <select data-placeholder="Seleccione Un Item" style="width: 100%;" class="form-control select2" name="cmb_item" id="cmb_item" onchange="validaAgregaItem()" >
                                
    
                                </select>
                            </div>                           
                        </div>
    
                        <div id="tabla_detalle" class="row" >
                            <div class="table-responsive col-sm-12">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <table style="color: black"  id="TablaListaDetalle" class="table table-striped table-bordered dataTable no-footer" role="grid" aria-describedby="datatable_info">
                                            <thead>
                                                <tr role="row">      
                                                    <th style="text-align: center" class="sorting" tabindex="0" aria-controls="datatable" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 10px;"></th>
                                                    
                                                    <th style="text-align: center" class="sorting" tabindex="0" aria-controls="datatable" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 10px;">Item</th>
    
                                                    <th style="text-align: center" class="sorting" tabindex="0" aria-controls="datatable" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 10px;">Cantidad</th>
    
                                                       
                                                
                                                </tr>
                                            </thead>
                
                                            <tbody id="tb_listaMedicamento">
                                                {{-- <tr>
                                                    <td colspan="9"><center>Sin Registros</center></td>
                                                </tr> --}}
                                            </tbody>
                                          
                                        </table>
                                    </div>
                                </div>
                            </div>  
                        </div>
                        <hr>
                        <div class="form-group">
                            <div class="col-sm-12 text-center" >
                            
                                <button type="button" class="btn btn-success btn-sm" onclick="guardarEgresoBodega()">
                                    Actualizar
                                </button>
                                <button type="button" onclick="cancelarEgreso()" class="btn btn-danger btn-sm">Cancelar</button>
                            </div>
                        </div>
    
                    </form>
                </div>

               
            </div>

        </div>

        {{-- modal pedido --}}
        <div class="modal fade"  data-keyboard="false" data-backdrop="static" id="modal_detalle" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">

            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span></button>
                        <h5 class="modal-title" style="font-weight:550">DETALLE PEDIDO</h5>
                    </div>
                    <div class="modal-body">
                        <div class="row ">
                            
                            <div class="col-md-12 col-sm-12" >        
                            
                                <div id="div_infor_apr">
                                    
                                    <div class="row_" style="font-size: 13px">
                                        <div class="col-md-6">
                                            <ul class="nav nav-pills nav-stacked"style="margin-left:0px">
                                                <li style="border-color: white"><a><i class="fa fa-credit-card text-blue"></i> <b class="text-black" style="font-weight: 650 !important">Codigo</b>: <span  id="codigo_detalle">  </span></a></li>
                                            
                                            </ul>
                                            <ul class="nav nav-pills nav-stacked"style="margin-left:0px">
                                                <li style="border-color: white"><a><i class="fa fa-home text-blue"></i> <b class="text-black" style="font-weight: 650 !important"> Área</b>: <span  id="area_detalle"></span></a></li>
                                            
                                            </ul>

                                            <ul class="nav nav-pills nav-stacked anula_seccion" style="margin-left:0px">
                                                <li style="border-color: white"><a><i class="fa fa-user text-blue"></i> <b class="text-black" style="font-weight: 650 !important">Anulado Por:</b> <span  id="user_anula"> </span></a></li>
                                                
                                            </ul>

                                           

                                        </div>     
                                        <div class="col-md-6">
                                            <ul class="nav nav-pills nav-stacked" style="margin-left:0px">
                                                <li style="border-color: white"><a><i class="fa fa-user text-blue"></i> <b class="text-black" style="font-weight: 650 !important">Funcionario:</b> <span  id="funcionario_detalle"> </span></a></li>
                                                
                                            </ul>

                                            <ul class="nav nav-pills nav-stacked"style="margin-left:0px">
                                                <li style="border-color: white"><a><i class="fa fa-calendar text-blue"></i> <b class="text-black" style="font-weight: 650 !important"> Fecha</b>: <span  id="fecha_detalle"></span></a></li>
                                            
                                            </ul>

                                            <ul class="nav nav-pills nav-stacked anula_seccion"style="margin-left:0px">
                                                <li style="border-color: white"><a><i class="fa fa-pencil-square-o text-blue"></i> <b class="text-black" style="font-weight: 650 !important"> Detalle</b>: <span  id="detalle_anula"></span></a></li>
                                            
                                            </ul>

                                        </div>  

                                       

                                    </div>
                                </div> 

                                <div class="table-responsive" style="margin-bottom:20px; margin-top:10px">
                                    <table id="tabla_detalle_pedido" width="100%"class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Item</th>
                                                <th class="text-center">Lote</th>
                                                <th class="text-center">Cant. Solicitada</th>
                                                <th class="text-center">Cant. Entregada</th>
                                                <th class="text-center">Fecha Caduc</th>
                                               
                                                <th class="text-center">Stock</th>
                                               
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="6"><center>No hay Datos Disponibles</td>
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
        
                        
                        </div>
        
                    
                    </div>
                
                </div>
        
            </div>
        
        </div>
        {{-- fin modal pedido --}}

        <div class="modal fade_ detalle_class"  id="modal_busqueda" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">BUSQUEDA DE ITEM</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <form class="form-horizontal" id="form_medicina" autocomplete="off" method="post"
                            action="">
                                <div class="form-group">
                                    <label for="inputPassword3" class="col-sm-3 control-label">Item</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" name="item_txt" id="item_txt"  >
                                        
                                    </div>                           
                                </div>
                            </form>
                            
                            <div class="table-responsive col-md-12">
                                <table id="tabla_medicina" width="100%"class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Lote</th>
                                            <th>Descripcion</th>
                                            <th>F. Caducidad</th>
                                            <th>Existencia</th>
                                            <th style="min-width: 5%">Seleccionar</th>
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


    </section>

@endsection
@section('scripts')
    <script src="{{asset('bower_components/sweetalert/sweetalert.js')}}"></script>

    <script src="{{ asset('js/gestionBodega/listado_pedido_bodega_solic_new.js?v='.rand())}}"></script>
    <script>
        $('#tituloCabecera').html('Buscar')
    </script>

@endsection
