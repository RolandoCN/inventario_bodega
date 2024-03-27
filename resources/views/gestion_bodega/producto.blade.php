@extends('layouts.app')

@section('content')

    
    <section class="content-header">
        <h1>
            Gestión Producto
        </h1>

    </section>

    <section class="content" id="content_form">

        <div class="box" id="listado_producto">
            <div class="box-header with-border">
                <h3 class="box-title">Listado </h3>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip"
                        title="Collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                    
                </div>

              
            </div>
            <div class="box-body">

                <div class="col-md-12" style="text-align:right; margin-bottom:20px; margin-top:10px">
                    <button type="button" onclick="visualizarForm('N')" class="btn btn-primary btn-sm">Nuevo</button>
                </div>

                <div class="table-responsive">
                    <table id="tabla_producto" width="100%"class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Codigo</th>
                                <th>Producto</th>
                                <th>Marca-Modelo</th>
                                <th>Subtotal</th>
                                <th>Iva</th>
                                <th>Total</th>
                                <th style="min-width: 30%">Opciones</th>
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


        <div id="form_ing" style="display:none">
            <form class="form-horizontal" id="form_registro_producto" autocomplete="off" method="post"
                action="">
                {{ csrf_field() }}
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title" id="titulo_form"> </h3>

                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip"
                                title="Collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                            
                        </div>
                    </div>
                    <div class="box-body">
                        

                        
                        <div class="form-group " >
                            <label for="inputPassword3" class="col-sm-3 control-label">Codigo</label>
                            <div class="col-sm-8">
                                <input type="text" minlength="1" maxlength="30" onKeyPress="if(this.value.length==30) return false;"  class="form-control" id="codigo" name="codigo" placeholder="Codigo">
                                <span class="invalid-feedback" role="alert" style="color:red; display:none
                                " id="error_cedula">
                                    <strong id="txt_error_cedula"></strong>
                                </span>
                            </div>
                            
                        </div>


                        <div class="form-group">

                            <label for="inputPassword3" class="col-sm-3 control-label">Descripcion</label>
                            <div class="col-sm-8">
                                <input type="text" minlength="1" maxlength="100" onKeyPress="if(this.value.length==100) return false;" class="form-control" id="descripcion" name="descripcion" placeholder="Descripcion">
                                <span class="invalid-feedback" role="alert" style="color:red; display:none
                                " id="error_nombres">
                                    <strong id="txt_error_nombres"></strong>
                                </span>
                            </div>
                           
                        </div>

                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-3 control-label">Marca</label>
                            <div class="col-sm-8">
                                <select data-placeholder="Seleccione Una Marca" style="width: 100%;" class="form-control select2" name="cmb_marca" id="cmb_marca" >
                                
                                    @foreach ($marca as $dato)
                                        <option value=""></option>
                                        <option value="{{ $dato->idmarca}}" >{{ $dato->descripcion }} </option>
                                    @endforeach
    
                                </select>
                            </div>                           
                        </div>

                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-3 control-label">Modelo</label>
                            <div class="col-sm-8">
                                <select data-placeholder="Seleccione Un Modelo" style="width: 100%;" class="form-control select2" name="cmb_modelo" id="cmb_modelo" >
                                
                                    @foreach ($modelo as $dato)
                                        <option value=""></option>
                                        <option value="{{ $dato->idmodelo}}" >{{ $dato->descripcion }} </option>
                                    @endforeach
    
                                </select>
                            </div>                           
                        </div>


                        <div class="form-group">

                            <label for="inputPassword3" class="col-sm-3 control-label">Detalle</label>
                            <div class="col-sm-8">
                                <input type="text" minlength="1" maxlength="100" onKeyPress="if(this.value.length==100) return false;"  class="form-control" id="detalle" name="detalle" placeholder="Detalle">
                            
                            </div>
                           
                        </div>

                        <div class="form-group">

                            <label for="inputPassword3" class="col-sm-3 control-label">Precio Venta</label>
                            <div class="col-sm-8">
                                <input type="number" minlength="1" maxlength="100" onKeyPress="if(this.value.length==100) return false;"  class="form-control" id="precio" name="precio" placeholder="Precio Venta">
                            
                            </div>
                           
                        </div>

                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-3 control-label">Grava IVA</label>
                            <div class="col-sm-8">
                                <select data-placeholder="Seleccione Una Opcion" style="width: 100%;" class="form-control select2" name="cmb_iva" id="cmb_iva" >
                                    <option value=""></option>
                                    <option value="Si" >Si </option>
                                    <option value="No" >No </option>
                                </select>
                            </div>                           
                        </div>

                        <hr>
                        <div class="form-group">
                            <div class="col-sm-12 text-center" >
                            
                                <button type="submit" class="btn btn-success btn-sm">
                                    <span id="nombre_btn_form"></span>
                                </button>
                                <button type="button" onclick="visualizarListado()" class="btn btn-danger btn-sm">Cancelar</button>
                            </div>
                        </div>
                        
                    </div>

                </div>
            
            </form>
        </div>


    </section>

@endsection
@section('scripts')

    <script src="{{ asset('js/gestionProducto/producto.js?v='.rand())}}"></script>

    <script>
        llenar_tabla_producto()
        limpiarCampos()
    </script>


@endsection
