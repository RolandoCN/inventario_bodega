@extends('layouts.app')

@section('content')

    
    <section class="content-header">
        <h1>
            Administración de Gestión Menú
        </h1>

    </section>

    <section class="content" id="content_form">

        <div class="box" id="listado_gestion_menu">
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
                    <table id="tabla_gestion_menu" width="100%"class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Gestión</th>
                                <th>Menú</th>
                                <th style="min-width: 30%">Opciones</th>
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


        <div id="form_ing" style="display:none">
            <form class="form-horizontal" id="form_gestion_menu" autocomplete="off" method="post"
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

                        <div class="form-group">

                            <label for="inputPassword3" class="col-sm-3 control-label">Gestión</label>
                            <div class="col-sm-8">
                                <select data-placeholder="Seleccione Una Gestión" style="width: 100%;" class="form-control select2" name="gestion" id="gestion" >
                                
                                    @foreach ($gestion as $dato)
                                        <option value=""></option>
                                        <option value="{{ $dato->id_gestion}}" >{{ $dato->descripcion }} </option>
                                    @endforeach
                                </select>
                               
                            </div>
                           
                        </div>


                        <div class="form-group">

                            <label for="inputPassword3" class="col-sm-3 control-label">Menú</label>
                            <div class="col-sm-8">
                                <select data-placeholder="Seleccione Un Menú" style="width: 100%;" class="form-control select2" name="menu" id="menu" >
                                
                                    @foreach ($menu as $dato)
                                        <option value=""></option>
                                        <option value="{{ $dato->id_menu}}" >{{ $dato->descripcion }} </option>
                                    @endforeach
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

    <script src="{{ asset('js/gestionMenu/gestionMenu.js?v='.rand())}}"></script>
 

    <script>
        llenar_tabla_gestion_menu()
        // limpiarCampos()
    </script>


@endsection
