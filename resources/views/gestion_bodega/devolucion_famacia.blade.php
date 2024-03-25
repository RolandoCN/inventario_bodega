@extends('layouts.app')

@section('content')
    <style>
        .ocultar_btn{
            display: none
        }
    </style>

    <link rel="stylesheet" href="{{asset('bower_components/sweetalert/sweetalert.css')}}">

    <section class="content-header" id="arriba">
        <h1>
            Listado Solicitud Devoluciones 
        </h1>

    </section>

    <section class="content" id="content_form">

        <div class="box" id="listado_persona">
            <div class="box-header with-border">
                <h3 class="box-title" id="tituloCabecera_">Pedidos Realizados </h3>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip"
                        title="Collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                    
                </div>

              
            </div>
            <div class="box-body">

                <div id="listado_permiso" >
                    
                    <div class="col-md-12">
                        <center>
                            <button type="button" class="btn btn-primary btn-xs" onclick="buscarPedidos()">Actualizar</button>
                        </center>
                    </div>
                   
                    <div class="table-responsive col-md-12" style="margin-bottom:20px; margin-top:10px">
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

               
            </div>

        </div>

          {{-- modal pedido --}}
          <div class="modal fade"  data-keyboard="false" data-backdrop="static" id="modal_detalle" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">

            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span></button>
                        <h5 class="modal-title" style="font-weight:550">DEVOLUCION DESDE FARMACIA A BODEGA</h5>
                    </div>
                    <div class="modal-body">
                        <div class="row ">
                            
                            <div class="col-md-12 col-sm-12"  id="seccion_detalle">        
                            
                                <div id="div_infor_apr">
                                    
                                    <div class="row_" style="font-size: 13px">
                                        <div class="col-md-6">
                                            <ul class="nav nav-pills nav-stacked"style="margin-left:0px">
                                                <li style="border-color: white"><a><i class="fa fa-credit-card text-blue"></i> <b class="text-black" style="font-weight: 650 !important">Codigo</b>: <span  id="codigo_detalle">  </span></a></li>
                                            
                                            </ul>
                                            <ul class="nav nav-pills nav-stacked"style="margin-left:0px">
                                                <li style="border-color: white"><a><i class="fa fa-home text-blue"></i> <b class="text-black" style="font-weight: 650 !important"> Área</b>: <span  id="area_detalle"></span></a></li>
                                            
                                            </ul>

                                           

                                        </div>     
                                        <div class="col-md-6">
                                            <ul class="nav nav-pills nav-stacked" style="margin-left:0px">
                                                <li style="border-color: white"><a><i class="fa fa-user text-blue"></i> <b class="text-black" style="font-weight: 650 !important">Funcionario:</b> <span  id="funcionario_detalle"> </span></a></li>
                                                
                                            </ul>

                                            <ul class="nav nav-pills nav-stacked"style="margin-left:0px">
                                                <li style="border-color: white"><a><i class="fa fa-calendar text-blue"></i> <b class="text-black" style="font-weight: 650 !important"> Fecha</b>: <span  id="fecha_detalle"></span></a></li>
                                            
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
                                                <th class="text-center">Fecha Caduc</th>
                                                <th class="text-center">Cant Validada</th>
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

                            
                                <div class="col-md-12" style="margin-top: 15px !important">
                                    <center>

                                        <button onclick="validar()" disabled  type="button" class="btn btn-success btn-xs btn_valida" ><span class="fa fa-check-circle-o"></span> Validar</button>

                                        {{-- <button onclick="anular()" id="btn_anular_"  type="button" class="btn btn-warning btn-xs ocultar_btn_" ><span class="fa fa-trash"></span> Anular</button> --}}

                                        <button onclick="cerrar()"  id="btn_cancelar" type="button" class="btn btn-danger btn-xs" ><span class="fa fa-times"></span> Cerrar</button>

                                      
                                    
                                    </center>
                                </div>

                               
                            
                            </div>

                            <div class="col-md-12" id="seccion_anular" style="display: none">
                                <form id="frm_anula_pedido" class="form-horizontal" action="" autocomplete="off">
                                    {{ csrf_field() }}
                                    <div class="box-body">
                            
                                        <div class="form-group">
                                            <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Motivo:</label>
                                            
                                            <div class="col-sm-10" style="font-weight: normal;">                     
                                                <textarea id="motivo_anulacion" name="motivo_anulacion" class="form-control" placeholder="Ingrese el motivo de anulacion"></textarea>
                                            </div>
                                                    
                                        </div>
            
                                        <div class="form-group">
                                            <div class="col-sm-12 col-md-offset-2" >
                                            
                                                <button type="button" onclick="anularPedidos()" class="btn btn-primary btn-xs">
                                                    <span class="fa fa-send"></span> Enviar
                                                </button>

                                                <button onclick="cerrar()"  id="btn_cancelar__" type="button" class="btn btn-danger btn-xs" ><span class="fa fa-times"></span> Cancelar</button>
                                              
                                            </div>
                                        </div>
                                        
                                    </div>
                                </form>
                            </div>

                            <div class="col-md-12 col-sm-12"  id="seccion_historial">     
                                
                                
                                <div id="div_infor_HIST">
                                    
                                    <div class="row_" style="font-size: 13px">
                                        <div class="col-m-12">
                                            <h4 class="text-center">HISTORIAL</h4>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="nav nav-pills nav-stacked"style="margin-left:0px">
                                                <li style="border-color: white"><a><i class="fa fa-credit-card text-blue"></i> <b class="text-black" style="font-weight: 650 !important">Item</b>: <span  id="item_historial">  </span></a></li>
                                            
                                            </ul>
                                          
                                           

                                        </div>     
                                        <div class="col-md-6">
                                            <ul class="nav nav-pills nav-stacked" style="margin-left:0px">
                                                <li style="border-color: white"><a><i class="fa fa-user text-blue"></i> <b class="text-black" style="font-weight: 650 !important">Funcionario:</b> <span  id="funcionario_historial"> </span></a></li>
                                                
                                            </ul>

                                        </div>  
                                    </div>
                                </div> 
                            
                                <div class="table-responsive" style="margin-bottom:20px; margin-top:10px">
                                    <table id="tabla_detalle_historial" width="100%"class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                                <th class="text-center">Pedido</th>
                                                <th class="text-center">Entregado</th>
                                               
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="3"><center>No hay Datos Disponibles</td>
                                            </tr>
                                            
                                        </tbody>
                                    
                                    </table>  
                                </div>    

                            
                                <div class="col-md-12" style="margin-top: 15px !important">
                                    <center>

                                        <button onclick="volver()"  id="btn_cancelar" type="button" class="btn btn-warning btn-xs" ><span class="fa fa-mail-reply"></span> Volver</button>

                                      
                                    
                                    </center>
                                </div>
                            
                            </div>
        
                        
                        </div>
        
                    
                    </div>
                
                </div>
        
            </div>
        
        </div>
        {{-- fin modal pedido --}}

       
    </section>
    @include('gestion_bodega.modal_doc')

@endsection
@section('scripts')
    <script src="{{asset('bower_components/sweetalert/sweetalert.js')}}"></script>

    <script src="{{ asset('js/gestionBodega/listado_devoluciones.js?v='.rand())}}"></script>
    <script>
        $('#tituloCabecera').html('Buscar')
        buscarPedidos()

       
    </script>

@endsection
