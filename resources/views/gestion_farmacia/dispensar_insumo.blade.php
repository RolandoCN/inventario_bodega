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
            Dispensacion de Insumos
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
                                    <th class="text-center">Paciente</th>
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
                        <h5 class="modal-title" style="font-weight:550">DETALLE PEDIDO</h5>
                    </div>
                    <div class="modal-body">
                        <div class="row ">
                            
                            <div class="col-md-12 col-sm-12"  id="seccion_detalle">        
                            
                                <div id="div_infor_apr">
                                    
                                    <div class="row_" style="font-size: 13px">
                                        <div class="col-md-6">
                                            <ul class="nav nav-pills nav-stacked"style="margin-left:0px">
                                                <li style="border-color: white"><a><i class="fa fa-credit-card text-blue"></i> <b class="text-black" style="font-weight: 650 !important">Codigo</b>: <span  class="codigo_detalle">  </span></a></li>
                                            
                                            </ul>
                                            <ul class="nav nav-pills nav-stacked"style="margin-left:0px">
                                                <li style="border-color: white"><a><i class="fa fa-home text-blue"></i> <b class="text-black" style="font-weight: 650 !important"> Área</b>: <span  class="area_detalle"></span></a></li>
                                            
                                            </ul>

                                           

                                        </div>     
                                        <div class="col-md-6">
                                            <ul class="nav nav-pills nav-stacked" style="margin-left:0px">
                                                <li style="border-color: white"><a><i class="fa fa-user text-blue"></i> <b class="text-black" style="font-weight: 650 !important">Solicitante:</b> <span  class="funcionario_detalle"> </span></a></li>
                                                
                                            </ul>

                                            <ul class="nav nav-pills nav-stacked"style="margin-left:0px">
                                                <li style="border-color: white"><a><i class="fa fa-calendar text-blue"></i> <b class="text-black" style="font-weight: 650 !important"> Fecha</b>: <span  class="fecha_detalle"></span></a></li>
                                            
                                            </ul>

                                        </div>  

                                        <div id="seccion_receta">
                                            <div class="col-md-6">
                                                <ul class="nav nav-pills nav-stacked" style="margin-left:0px">
                                                    <li style="border-color: white"><a><i class="fa fa-credit-card text-blue"></i> <b class="text-black" style="font-weight: 650 !important">C.I. Paciente:</b> <span  class="ci_paciente"> </span></a></li>
                                                    
                                                </ul>

                                                <ul class="nav nav-pills nav-stacked" style="margin-left:0px">
                                                    <li style="border-color: white"><a><i class="fa fa-user-md  text-blue"></i> <b class="text-black" style="font-weight: 650 !important">CIE 10:</b> <span  class="cie_10_detalle"> </span></a></li>
                                                    
                                                </ul>

                                               

                                            </div>  

                                            <div class="col-md-6">
                                              
                                                <ul class="nav nav-pills nav-stacked"style="margin-left:0px">
                                                    <li style="border-color: white"><a><i class="fa fa-frown-o text-blue"></i> <b class="text-black" style="font-weight: 650 !important"> Paciente</b>: <span  class="paciente_receta"></span></a></li>
                                                
                                                </ul>
                                            
                                            </div>

                                            <div class="col-md-6" style="display: none" id="secc_acomp">
                                              
                                                <ul class="nav nav-pills nav-stacked"style="margin-left:0px">
                                                    <li style="border-color: white"><a><i class="fa fa-users text-blue"></i> <b class="text-black" style="font-weight: 650 !important"> Acompañante</b>: <span  class="acompanante_receta"></span></a></li>
                                                
                                                </ul>
                                            
                                            </div>

                                        </div> 


                                        <div class="col-md-6">
                                            <ul class="nav nav-pills nav-stacked" style="margin-left:0px">
                                                <li style="border-color: white"><a><i class="fa fa-building text-blue"></i> <b class="text-black" style="font-weight: 650 !important">Bodega:</b> <span  class="bodegas_detalle"> </span></a></li>
                                                
                                            </ul>

                                        </div>  

                                      

                                      

                                    </div>
                                </div> 

                                <div class="table-responsive col-md-12" style="margin-bottom:20px; margin-top:10px" id="tabla_lab">
                                    <table id="tabla_detalle_pedido" width="100%"class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Item</th>
                                                <th class="text-center">Lote</th>
                                                <th class="text-center">Cant. Solicitada</th>
                                                <th class="text-center">Fecha Caduc</th>
                                                <th class="text-center">Cant Validada</th>
                                                <th class="text-center">Stock</th>
                                                <th class="text-center"></th>
                                               
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="7"><center>No hay Datos Disponibles</td>
                                            </tr>
                                            
                                        </tbody>
                                    
                                    </table>  
                                </div>    

                                <div class="col-md-12" style="margin-top: 15px !important">
                                    <center>

                                        <button onclick="validar()" disabled  type="button" class="btn btn-success btn-xs btn_valida" ><span class="fa fa-check-circle-o"></span> Validar</button>

                                        <button onclick="ImprimirPrevio()" id="btn_imprimir_previo" type="button" class="btn btn-primary btn-xs ocultar_btn" ><span class="fa fa-print"></span> Imprimir</button>

                                        <button onclick="ImprimirRollo()" id="btn_rollo_impresion" type="button" class="btn btn-info btn-xs " ><span class="fa fa-print"></span> Rollo</button>

                                        <button onclick="cerrar()"  id="btn_cancelar" type="button" class="btn btn-danger btn-xs" ><span class="fa fa-times"></span> Cerrar</button>

                                      
                                    
                                    </center>
                                </div>
                            
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

                                        <button onclick="volver()"  id="btn_cancelar2" type="button" class="btn btn-warning btn-xs" ><span class="fa fa-mail-reply"></span> Volver</button>

                                      
                                    
                                    </center>
                                </div>
                            
                            </div>
        
                        
                        </div>
        
                    
                    </div>
                
                </div>
        
            </div>
        
        </div>
        {{-- fin modal pedido --}}

        <div class="modal fade"  data-keyboard="false" data-backdrop="static" id="modal_detalle_paquete" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">

            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span></button>
                        <h5 class="modal-title" style="font-weight:550">DETALLE PEDIDO</h5>
                    </div>
                    <div class="modal-body">
                        <div class="row ">
                            
                            <div class="col-md-12 col-sm-12"  id="seccion_detalle_paquete">        
                            
                                <div id="div_infor_aprx">
                                    
                                    <div class="row_" style="font-size: 13px">
                                        <div class="col-md-6">
                                            <ul class="nav nav-pills nav-stacked"style="margin-left:0px">
                                                <li style="border-color: white"><a><i class="fa fa-credit-card text-blue"></i> <b class="text-black" style="font-weight: 650 !important">Codigo</b>: <span  class="codigo_detalle">  </span></a></li>
                                            
                                            </ul>
                                            <ul class="nav nav-pills nav-stacked"style="margin-left:0px">
                                                <li style="border-color: white"><a><i class="fa fa-home text-blue"></i> <b class="text-black" style="font-weight: 650 !important"> Área</b>: <span  class="area_detalle"></span></a></li>
                                            
                                            </ul>

                                           

                                        </div>     
                                        <div class="col-md-6">
                                            <ul class="nav nav-pills nav-stacked" style="margin-left:0px">
                                                <li style="border-color: white"><a><i class="fa fa-user text-blue"></i> <b class="text-black" style="font-weight: 650 !important">Funcionario:</b> <span  class="funcionario_detalle"> </span></a></li>
                                                
                                            </ul>

                                            <ul class="nav nav-pills nav-stacked"style="margin-left:0px">
                                                <li style="border-color: white"><a><i class="fa fa-calendar text-blue"></i> <b class="text-black" style="font-weight: 650 !important"> Fecha</b>: <span  class="fecha_detalle"></span></a></li>
                                            
                                            </ul>

                                         

                                        </div>  
                                        <div id="seccion_receta">
                                            <div class="col-md-6">
                                                <ul class="nav nav-pills nav-stacked" style="margin-left:0px">
                                                    <li style="border-color: white"><a><i class="fa fa-credit-card text-blue"></i> <b class="text-black" style="font-weight: 650 !important">C.I. Paciente:</b> <span  class="ci_paciente"> </span></a></li>
                                                    
                                                </ul>

                                                <ul class="nav nav-pills nav-stacked" style="margin-left:0px">
                                                    <li style="border-color: white"><a><i class="fa fa-user-md  text-blue"></i> <b class="text-black" style="font-weight: 650 !important">CIE 10:</b> <span  class="cie_10_detalle"> </span></a></li>
                                                    
                                                </ul>

                                               

                                            </div>  

                                            <div class="col-md-6">
                                              
                                                <ul class="nav nav-pills nav-stacked"style="margin-left:0px">
                                                    <li style="border-color: white"><a><i class="fa fa-frown-o text-blue"></i> <b class="text-black" style="font-weight: 650 !important"> Paciente</b>: <span  class="paciente_receta"></span></a></li>
                                                
                                                </ul>
                                            
                                            </div>
                                        </div> 
                                    </div>
                                </div> 

                            
                                <div class="table-responsive col-md-12" style="margin-bottom:20px; margin-top:10px" id="tabla_paq">
                                    <table id="Paquete_tabla_detalle_pedido" width="100%"class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Item</th>
                                                <th class="text-center">Cant. Solicitada</th>
                                                <th class="text-center">Cant A Entregar</th>
                                               
                                                <th class="text-center">Stock</th>
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

                                        <button onclick="validarPaquete()"  type="button" class="btn btn-primary btn-xs btn_valida" ><span class="fa fa-check-circle-o"></span> Validar</button>

                                        <button onclick="ImprimirPrevio()" id="btn_imprimir_previo1" type="button" class="btn btn-success btn-xs " ><span class="fa fa-print"></span> Imprimir</button>

                                        <button onclick="cerrarPedidoPaquete()"  type="button" class="btn btn-danger btn-xs" ><span class="fa fa-times"></span> Cerrar</button>

                                      
                                    
                                    </center>
                                </div>
                            
                            </div>

                            <div class="col-md-12 col-sm-12"  id="seccion_detalle_item_paquete" style="display: none">     
                                
                                
                                <div id="div_infor_HIST">
                                    
                                    <div class="row_" style="font-size: 13px">
                                        <div class="col-m-12">
                                            <h4 class="text-center" id="titulo_modal_paquete">DETALLE</h4>
                                        </div>
                                       
                                    </div>
                                </div> 
                            
                                <div class="table-responsive" style="margin-bottom:20px; margin-top:10px">
                                    <table id="Paquete_tabla_detalle_historial" width="100%"class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center">#</th>
                                               
                                                <th class="text-center">Cantidad</th>
                                                <th class="text-center">Lote</th>
                                                <th class="text-center">F. Caduca</th>
                                                <th class="text-center">Stock</th>
                                               
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="4"><center>No hay Datos Disponibles</td>
                                            </tr>
                                            
                                        </tbody>
                                    
                                    </table>  
                                </div>    

                            
                                <div class="col-md-12" style="margin-top: 15px !important">
                                    <center>

                                        <button onclick="volverlistaPaquete()"type="button" class="btn btn-warning btn-xs" ><span class="fa fa-mail-reply"></span> Volver</button>

                                      
                                    
                                    </center>
                                </div>
                            
                            </div>

                           
                        </div>
        
                    
                    </div>
                
                </div>
        
            </div>
        
        </div>

        <div class="modal fade"  data-keyboard="false" data-backdrop="static" id="modal_anula_comprobante" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">

            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span></button>
                        <h5 class="modal-title" style="font-weight:550">ANULA COMPROBANTE</h5>
                    </div>
                    <div class="modal-body">
                        <div class="row ">
                            
                            <form class="form-horizontal" id="form_registro_persona" autocomplete="off" method="post"
                            action="">
                                {{ csrf_field() }}                               
                                        
                                <div class="form-group">
                                    <label for="inputPassword3" class="col-sm-2 control-label">Motivo</label>
                                    <div class="col-sm-8">
                                        <textarea minlength="1" maxlength="200" onKeyPress="if(this.value.length==200) return false;"  class="form-control" id="motivo_anula" name="motivo_anula" placeholder="Ingrese el motivo de anulacion"></textarea>
                                       
                                    </div>
                                    
                                </div>
        
                                <div class="form-group">
                                    <div class="col-sm-12 text-center" >
                                    
                                        <button type="button" onclick="ProcesaAnulacion()" class="btn btn-success btn-sm">
                                           Anular
                                        </button>
                                        <button type="button" onclick="cancelarAnulacion()" class="btn btn-danger btn-sm">Cancelar</button>
                                    </div>
                                </div>
                            
                            </form>
                        
                        </div>
        
                    
                    </div>
                
                </div>
        
            </div>
        
        </div>

       
    </section>
    @include('gestion_bodega.modal_doc')

@endsection
@section('scripts')
    <script src="{{asset('bower_components/sweetalert/sweetalert.js')}}"></script>

    <script src="{{ asset('js/gestionFarmacia/listado_insumo.js?v='.rand())}}"></script>
    <script>
        $('#tituloCabecera').html('Buscar')
        buscarPedidos()

       
    </script>

@endsection
