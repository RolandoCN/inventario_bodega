@extends('layouts.app')

@section('content')

    <link rel="stylesheet" href="{{asset('bower_components/sweetalert/sweetalert.css')}}">

    <section class="content-header">
        <h1>
            Actualización de Pedido a Bodega 
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
                            <textarea name="motivo" id="motivo" class="form-control" >{{$comprobante->observacion}}</textarea>
                        </div>                           
                    </div>
                    <input type="hidden" name="fecha_actual" id="fecha_actual" value="{{date('Y-m-d')}}">
                    <div class="form-group">
                        <label for="inputPassword3" class="col-sm-3 control-label">Bodega</label>
                        <div class="col-sm-6">
                            <select data-placeholder="Seleccione Una Bodega" style="width: 100%;" class="form-control select2" name="cmb_bodega" id="cmb_bodega"  >
                            
                                @foreach ($bodega as $dato)
                                    {{-- <option value=""></option>
                                    <option value="{{ $dato->idbodega}}" >{{ $dato->nombre }} </option> --}}
                                    <option value="{{$dato->idbodega}}" {{$dato->idbodega == $comprobante->idbodega ? 'selected' : ''}} >{{$dato->nombre}}</option>

                                @endforeach

                            </select>
                        </div>                           
                    </div>
                   
                    <div class="form-group">
                        <label for="inputPassword3" class="col-sm-3 control-label">Item</label>
                        <div class="col-sm-5">
                            <select data-placeholder="Seleccione Un Tipo" style="width: 100%;" class="form-control select2" name="medi" id="medi"  disabled >
                            
                                <option value=""></option>
                                
                            </select>
                        </div> 
                        
                        <div class="col-sm-1" >
                            <button type="button" class="btn btn-sm btn-primary" style="margin-top: 5px" onclick="abrirModalMedicina()">Buscar</button>
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

                                                <th style="text-align: center" class="sorting" tabindex="0" aria-controls="datatable" rowspan="1" colspan="1"  aria-label="Office: activate to sort column ascending" style="width: 10px;"> Precio</th>

                                                <th style="text-align: center" class="sorting" tabindex="0" aria-controls="datatable" rowspan="1" colspan="1"  aria-label="Office: activate to sort column ascending" style="width: 10px;"> Fecha Elabo</th>

                                                <th style="text-align: center" class="sorting" tabindex="0" aria-controls="datatable" rowspan="1" colspan="1"  aria-label="Office: activate to sort column ascending" style="width: 10px;"> Fecha Caduc</th>

                                                <th style="text-align: center" class="sorting" tabindex="0" aria-controls="datatable" rowspan="1" colspan="1"  aria-label="Office: activate to sort column ascending" style="width: 10px;"> Lote</th>

                                                <th style="text-align: center" class="sorting" tabindex="0" aria-controls="datatable" rowspan="1" colspan="1"  aria-label="Office: activate to sort column ascending" style="width: 10px;"> Reg. San</th>

                                                <th style="text-align: center" class="sorting" tabindex="0" aria-controls="datatable" rowspan="1" colspan="1"  aria-label="Office: activate to sort column ascending" style="width: 10px;"> Total</th>

                                            
                                            </tr>
                                        </thead>
            
                                        <tbody id="tb_listaMedicamento">
                                            {{-- <tr>
                                                <td colspan="9"><center>Sin Registros</center></td>
                                            </tr> --}}
                                        </tbody>
                                        <tfoot id="tb_pie_TotalMedicamentos">                                                        
                                                    
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>  
                    </div>
                    <hr>
                    <div class="form-group">
                        <div class="col-sm-12 text-center" >
                        
                            <button type="button" class="btn btn-success btn-sm" onclick="guardarEgresoBodega()">
                                Guardar
                            </button>
                            <button type="button" onclick="cancelarEgreso()" class="btn btn-danger btn-sm">Cancelar</button>
                        </div>
                    </div>

                </form>
              

                
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

    <script src="{{ asset('js/gestionBodega/actualiza_pedido_bodega_gral.js?v='.rand())}}"></script>
 

    <script>
        // actualiza()
    </script>
@endsection
