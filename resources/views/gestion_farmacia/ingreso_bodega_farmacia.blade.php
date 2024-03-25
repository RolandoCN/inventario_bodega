@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="{{asset('bower_components/sweetalert/sweetalert.css')}}">

    
    <section class="content-header">
        <h1>
            Administración de Ingreso a Farmacia
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
                <form class="form-horizontal" id="form_ingreso_bodega" autocomplete="off" method="post"
                action="">
                {{ csrf_field() }}

                    <div class="form-group">
                        <label for="inputPassword3" class="col-sm-3 control-label">Nº Factura/Guia</label>
                        <div class="col-sm-6">
                            <input type="text"  class="form-control" id="guia"  name="guia" placeholder="Factura/Guia">
                        </div>                           
                    </div>

                    {{-- <div class="form-group">
                        <label for="inputPassword3" class="col-sm-3 control-label">Proveedor</label>
                        <div class="col-sm-6">
                            <select data-placeholder="Seleccione Un Proveedor" style="width: 100%;" class="form-control select2" name="cmb_proveedor" id="cmb_proveedor">
                            
                                @foreach ($proveedor as $dato)
                                    <option value=""></option>
                                    <option value="{{ $dato->idprov}}" >{{ $dato->ruc }} -- {{ $dato->empresa }} </option>
                                @endforeach

                            </select>
                        </div>                           
                    </div> --}}
                    <input type="hidden" name="cmb_proveedor" id="cmb_proveedor" value="123">
                    <input type="hidden" name="fecha_actual" id="fecha_actual" value="{{date('Y-m-d')}}">

                    <div class="form-group">
                        <label for="inputPassword3" class="col-sm-3 control-label">Tipo Ingreso</label>
                        <div class="col-sm-6">
                            <select data-placeholder="Seleccione Un Tipo" style="width: 100%;" class="form-control select2" name="tipo_ingreso_cmb" id="tipo_ingreso_cmb" onchange="cambiaTipoIngeso()" >
                            
                                @foreach ($tipo_ingreso as $dato)
                                    <option value=""></option>
                                    <option value="{{ $dato->idtipo_ingreso}}" >{{ $dato->nombre }} </option>
                                @endforeach

                            </select>
                        </div>                           
                    </div>

                    <div class="form-group" id="seccio_dev" style="display: none">
                        <label for="inputPassword3" class="col-sm-3 control-label">Funcionario</label>
                        <div class="col-sm-6">
                            <select data-placeholder="Seleccione Una Opcion" style="width: 100%;" class="form-control select2" name="cmb_user_dev" id="cmb_user_dev"  >
                            
                                @foreach ($usuario as $dato)
                                    <option value=""></option>
                                    <option value="{{ $dato->idper}}" >{{ $dato->ci }} -- {{ $dato->ape1 }} {{ $dato->ape2 }} {{ $dato->nom1 }} {{ $dato->nom2 }}</option>
                                @endforeach

                            </select>
                        </div>                           
                    </div>

                    <div class="form-group">
                        <label for="inputPassword3" class="col-sm-3 control-label">Bodega</label>
                        <div class="col-sm-6">
                            <select data-placeholder="Seleccione Una Bodega" style="width: 100%;" class="form-control select2" name="cmb_bodega" id="cmb_bodega"  >
                            
                                @foreach ($bodega as $dato)
                                    <option value=""></option>
                                    <option value="{{ $dato->idbodega}}" >{{ $dato->nombre }} </option>
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

                    <div class="form-group">
                        <label for="inputPassword3" class="col-sm-3 control-label">Observacion</label>
                        <div class="col-sm-6">
                           <input type="text" name="observa" id="observa" class="form-control">
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
{{-- 
                                                <th style="text-align: center" class="sorting" tabindex="0" aria-controls="datatable" rowspan="1" colspan="1"  aria-label="Office: activate to sort column ascending" style="width: 10px;"> Descuento</th> --}}

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
                        
                            <button type="button" class="btn btn-success btn-sm" onclick="guardarIngresoBodega()">
                                Guardar
                            </button>
                            <button type="button" onclick="cancelarIngreso()" class="btn btn-danger btn-sm">Cancelar</button>
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
                            <div class="col-md-12" id="tabla_item">
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

                                <div class="table-responsive col-md-12">
                                    <table id="tabla_medicina_dev" width="100%"class="table table-bordered table-striped" style="display: none">
                                        <thead>
                                            <tr>
                                                <th>Lote</th>
                                                <th>Descripcion</th>
                                                <th>F. Caducidad</th>
                                                <th>Precio</th>
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

                            <div class="col-md-12" id="form_item" style="display: none">

                                <form class="form-horizontal" id="form_medicina_new" autocomplete="off" method="post"
                                action="">
                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Cum</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="codigo" id="codigo"  placeholder="Cum">
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Nombre</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="nombre_med" id="nombre_med" placeholder="Nombre" >
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Concentracion</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="concentracion_med" id="concentracion_med" placeholder="Concentracion"  >
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Forma</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="forma_med" id="forma_med" placeholder="Forma" >
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Presentacion</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="presentacion_med" id="presentacion_med" placeholder="Presentacion" >
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Stock Min</label>
                                        <div class="col-sm-6">
                                            <input type="number" class="form-control" name="stock_min" id="stock_min"  placeholder="Stock Min">
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Stock Critico</label>
                                        <div class="col-sm-6">
                                            <input type="number" class="form-control" name="stock_cri" id="stock_cri" placeholder="Stock Critico" >
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="button" onclick="validaFormMedicina()" class="btn btn-success btn-sm">Guardar</button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="volverBusqueda()">Volver</button>

                                        </div>                      
                                    </div>

                                </form>


                            </div>

                            <div class="col-md-12" id="form_item_ins" style="display: none">

                                <form class="form-horizontal" id="form_insumo_new" autocomplete="off" method="post"
                                action="">

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Cudim</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="cudim" id="cudim"  placeholder="Cudim">
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Codigo Esbay</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="cod_esbay_ins" id="cod_esbay_ins" placeholder="Esbay" >
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Insumo</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="insumo" id="insumo"  placeholder="Insumo">
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Descripcion</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="desc_ins" id="desc_ins" placeholder="Descripcion" >
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Espec Tecn</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="espec_tecn" id="espec_tecn" placeholder="Espec Tecn" >
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Tipo</label>
                                        <div class="col-sm-6">
                                            <select data-placeholder="Seleccione Un Tipo" style="width: 100%;" class="form-control select2" name="tipo_ins" id="tipo_ins" >
                            
                                               
                                                
                                            </select>
                                            
                                        </div>                      
                                    </div>

                                    
                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Stock Min</label>
                                        <div class="col-sm-6">
                                            <input type="number" class="form-control" name="stock_min_ins" id="stock_min_ins"  placeholder="Stock Min">
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Stock Critico</label>
                                        <div class="col-sm-6">
                                            <input type="number" class="form-control" name="stock_cri_ins" id="stock_cri_ins" placeholder="Stock Critico" >
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="button" onclick="validaFormInsumo()" class="btn btn-success btn-sm">Guardar</button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="volverBusqueda()">Volver</button>

                                        </div>                      
                                    </div>

                                </form>


                            </div>

                            <div class="col-md-12" id="form_item_lab" style="display: none">

                                <form class="form-horizontal" id="form_lab_new" autocomplete="off" method="post"
                                action="">

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Codigo</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="cod_lab" id="cod_lab" placeholder="Codigo" >
                                            
                                        </div>                      
                                    </div>
                                   
                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Descripcion</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="desc_lab" id="desc_lab" placeholder="Descripcion" >

                                            <input type="hidden" class="form-control" name="idbod" id="idbod">
                                            
                                        </div>                      
                                    </div>


                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="button" onclick="validaFormLab()" class="btn btn-success btn-sm">Guardar</button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="volverBusqueda()">Volver</button>

                                        </div>                      
                                    </div>

                                </form>


                            </div>

                            <div class="col-md-12" id="form_item_mat_of" style="display: none">

                                <form class="form-horizontal" id="form_mat_new" autocomplete="off" method="post"
                                action="">

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Descripcion</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="mat_of" id="mat_of" placeholder="Descripcion" >
                                            
                                        </div>                      
                                    </div>
                                   
                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Presentacion</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="prese_of" id="prese_of" placeholder="Presentacion" >

                                            <input type="hidden" class="form-control" name="idbodite" id="idbodite" placeholder="Presentacion" >
                                            
                                        </div>                      
                                    </div>


                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="button" onclick="validaFormItem()" class="btn btn-success btn-sm">Guardar</button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="volverBusqueda()">Volver</button>

                                        </div>                      
                                    </div>

                                </form>


                            </div>

                            <div class="col-md-12" id="form_item_dialisis" style="display: none">

                                <form class="form-horizontal" id="form_med_dialisis" autocomplete="off" method="post"
                                action="">
                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Cum</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="codigo_dialisis" id="codigo_dialisis"  placeholder="Cum">
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Nombre</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="nombre_med_dialisis" id="nombre_med_dialisis" placeholder="Nombre" >
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Concentracion</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="concentracion_med_dialisis" id="concentracion_med_dialisis" placeholder="Concentracion"  >
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Forma</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="forma_med_dialisis" id="forma_med_dialisis" placeholder="Forma" >
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Presentacion</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="presentacion_med_dialisis" id="presentacion_med_dialisis" placeholder="Presentacion" >
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Stock Min</label>
                                        <div class="col-sm-6">
                                            <input type="number" class="form-control" name="stock_min_dialisis" id="stock_min_dialisis"  placeholder="Stock Min">
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Stock Critico</label>
                                        <div class="col-sm-6">
                                            <input type="number" class="form-control" name="stock_cri_dialisis" id="stock_cri_dialisis" placeholder="Stock Critico" >
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="button" onclick="validaFormMedicinaDialisis()" class="btn btn-success btn-sm">Guardar</button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="volverBusqueda()">Volver</button>

                                        </div>                      
                                    </div>

                                </form>


                            </div>

                            <div class="col-md-12" id="form_ins_dialisis" style="display: none">

                                <form class="form-horizontal" id="form_insumo_new_dial" autocomplete="off" method="post"
                                action="">

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Cudim</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="cudim_dialisi" id="cudim_dialisi"  placeholder="Cudim">
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Codigo Esbay</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="cod_esbay_ins" id="cod_esbay_ins_dial" placeholder="Esbay" >
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Insumo</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="insumo_dialisi" id="insumo_dialisi"  placeholder="Insumo">
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Descripcion</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="desc_ins_dialisi" id="desc_ins_dialisi" placeholder="Descripcion" >
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Espec Tecn</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="espec_tecn_dialisi" id="espec_tecn_dialisi" placeholder="Espec Tecn" >
                                            
                                        </div>                      
                                    </div>

                                   
                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Stock Min</label>
                                        <div class="col-sm-6">
                                            <input type="number" class="form-control" name="stock_min_ins_dialisi" id="stock_min_ins_dialisi"  placeholder="Stock Min">
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Stock Critico</label>
                                        <div class="col-sm-6">
                                            <input type="number" class="form-control" name="stock_cri_ins_dialisi" id="stock_cri_ins_dialisi" placeholder="Stock Critico" >
                                            
                                        </div>                      
                                    </div>

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="button" onclick="validaFormInsumoDialisi()" class="btn btn-success btn-sm">Guardar</button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="volverBusqueda()">Volver</button>

                                        </div>                      
                                    </div>

                                </form>


                            </div>

                            <div class="col-md-12" id="form_lab_dialisis" style="display: none">

                                <form class="form-horizontal" id="form_lab_ins__new" autocomplete="off" method="post"
                                action="">

                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Codigo</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="cod_lab_ins" id="cod_lab_ins" placeholder="Codigo" >
                                            
                                        </div>                      
                                    </div>
                                   
                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label">Descripcion</label>
                                        <div class="col-sm-6">
                                            <input type="text" class="form-control" name="desc_lab_ins" id="desc_lab_ins" placeholder="Descripcion" >

                                            <input type="hidden" class="form-control" name="idbod_ins" id="idbod_ins">
                                            
                                        </div>                      
                                    </div>


                                    <div class="form-group">
                                        <label for="inputPassword3" class="col-sm-3 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="button" onclick="validaFormLabIns()" class="btn btn-success btn-sm">Guardar</button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="volverBusqueda()">Volver</button>

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
    <script src="{{asset('bower_components/sweetalert/sweetalert.js')}}"></script>
    <script src="{{ asset('js/gestionFarmacia/ingreso_medicina.js?v='.rand())}}"></script>
 

@endsection
