@extends('layouts.app')

@section('content')

    <link rel="stylesheet" href="{{asset('bower_components/sweetalert/sweetalert.css')}}">
    <style>
        .stock_no_cumple{
            background-color:#ecd0d0;
        }

    </style>
    <section class="content-header">
        <h1>
            Solicitud Paquete a Farmacia
        </h1>

    </section>

    <section class="content" id="content_form">

        <div class="box" id="listado_funcionario">
            <div class="box-header with-border">
                <h3 class="box-title">Formulario </h3>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip"
                        title="Collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                    
                </div>

              
            </div>
            <div class="box-body">
                <form class="form-horizontal" id="form_pedido_bodega" autocomplete="off" method="post"
                action="">
                    {{ csrf_field() }}

               
                    <div class="form-group">
                        <label for="inputPassword3" class="col-sm-3 control-label">Motivo</label>
                        <div class="col-sm-6">
                            <textarea name="motivo" id="motivo" class="form-control" placeholder="Motivo"></textarea>
                           
                        </div>                           
                    </div>
                    <input type="hidden" name="fecha_actual" id="fecha_actual" value="{{date('Y-m-d')}}">
                    <div class="form-group">
                        <label for="inputPassword3" class="col-sm-3 control-label">Paquete</label>
                        <div class="col-sm-6">
                            <select data-placeholder="Seleccione Un Paquete" style="width: 100%;" class="form-control select2" name="cmb_paquete" id="cmb_paquete"  onchange="agg_paquete()" >
                            
                                @foreach ($paquetes as $dato)
                                    <option value=""></option>
                                    <option value="{{ $dato->id_paquete}}" >{{ $dato->descripcion }} </option>
                                @endforeach

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
                                                
                                                <th style="text-align: center" class="sorting" tabindex="0" aria-controls="datatable" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 10px;">Paquete</th>

                                                <th style="text-align: center" class="sorting" tabindex="0" aria-controls="datatable" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 10px;">Cantidad</th>

                                                <th style="text-align: center" class="sorting" tabindex="0" aria-controls="datatable" rowspan="1" colspan="1" aria-label="Office: activate to sort column ascending" style="width: 10px;">Detalle</th>

                                            
                                            </tr>
                                        </thead>
            
                                        <tbody id="tb_listaMedicamento">
                                           
                                        </tbody>
                                       
                                    </table>
                                </div>
                            </div>
                        </div>  
                    </div>
                    <hr>
                    <div class="form-group">
                        <div class="col-sm-12 text-center" >
                        
                            <button type="button" class="btn btn-success btn-sm guardar_btn" onclick="guardarEgresoBodega()">
                                Guardar
                            </button>
                            <button type="button" onclick="cancelarEgreso()" class="btn btn-danger btn-sm">Cancelar</button>
                        </div>
                    </div>

                </form>
              

                
            </div>

        </div>

        <div class="modal fade_ detalle_class"  id="modal_Paquete" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span></button>
                        <h4 class="modal-title"><span  id="paq_selecc" class="text-transform: uppercase !important"> </span> </h4>
                    </div>
                    <div class="modal-body">
                        {{-- <div id="paquete_error"></div> --}}
                        <table id="paquete_error">
                           
                        </table>
                        <input type="hidden" name="idpaq" id="idpaq">
                        <div class="row">
                           
                            
                            <div class="table-responsive col-md-12">
                                <table id="tabla_detalle_paq" width="100%"class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Descripción</th>
                                            <th>Cantidad</th>
                                            <th>Stock</th>
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
        
            </div>
        
        </div>


    </section>

@endsection
@section('scripts')
    <script src="{{asset('bower_components/sweetalert/sweetalert.js')}}"></script>

    <script src="{{ asset('js/gestionPaquete/pedido_farmacia.js?v='.rand())}}"></script>
 
@endsection
