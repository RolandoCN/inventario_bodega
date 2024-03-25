@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="{{asset('bower_components/sweetalert/sweetalert.css')}}">

    
    <section class="content-header">
        <h1>
            Mantenimiento de Medicamentos
        </h1>

    </section>

    <section class="content" id="content_form">

        <div class="box" id="listado_funcionario">
            
            <div class="box-body">
                
                <div id="listado_global_med">

                    <div class="col-md-12" style="margin-top:52px">
                        <form class="form-horizontal" id="form_Fitltra" autocomplete="off" method="post"
                        action="">
                            <div class="form-group">
                                <label for="inputPassword3" class="col-sm-3 control-label"></label>
                                <div class="col-sm-6">
                                    <input type="text"  autocomplete="of" name="buscaItem" class="form-control" id="buscaItem" placeholder="Busqueda por Nombre, Codigo Esbay o Cudim">
                                    
                                </div>                         
                            </div>
                        </form>
                    </div>

                   
                    <div class="table-responsive col-md-12" style="margin-bottom:20px; margin-top:20px">

                        <table id="tabla_inventario_global_md" width="100%"class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Cudim</th>
                                    <th class="text-center">Cod Esbay</th>
                                    <th class="text-center">Descripcion</th>
                                 
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
                </div>

               
                
            </div>

        </div>

        
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
                            <div class="col-md-12" id="tabla_item">
                                <form class="form-horizontal" id="form_medicina" autocomplete="off" method="post"
                                action="">
                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Item</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="item_txt" id="item_txt"  >
                                            
                                        </div>  
                                        <div class="col-sm-1">
                                            <button type="button" onclick="nuevoItem()" class="btn btn-sm btn-success" style="margin-top:5px">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                            
                                        </div>                           
                                    </div>
                                </form>
                          
                                <div class="table-responsive col-md-12">
                                    <table id="tabla_medicina" width="100%"class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Codigo</th>
                                                <th>Descripcion</th>
                                            
                                                <th style="min-width: 5%">Seleccionar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="3"><center>No hay Datos Disponibles</td>
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

        <div class="modal fade_ detalle_class"  id="modal_busqueda_acceso" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span></button>
                            <h4 class="modal-title"></h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12" >
                                <h4 id="insumo_parametro" class="text-center"></h4>
                            </div>
                            {{-- <div class="col-md-12" class="text-center">
                                <center>
                                    <button type="button" onclick="marcarTodosMedicos()" class="btn btn-xs btn-success">Marcar Todos</button>
                                    <button type="button" onclick="desmarcarTodosMedicos()" class="btn btn-xs btn-danger">Desmarcar Todos</button>
                                </center>
                            </div> --}}
                            <div class="col-md-12" id="tabla_item">
                               
                          
                                <div class="table-responsive col-md-12">
                                    <table id="tabla_insumo_parametriza" width="100%"class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Descripcion</th>
                                                                                           
                                                <th style="min-width: 5%">Seleccionar</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="2"><center>No hay Datos Disponibles</td>
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



    </section>

@endsection
@section('scripts')
    <script src="{{asset('bower_components/sweetalert/sweetalert.js')}}"></script>
    <script src="{{ asset('js/gestionBodega/mantenimiento_medicamento.js?v='.rand())}}"></script>
@endsection
