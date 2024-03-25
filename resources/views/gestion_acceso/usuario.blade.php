@extends('layouts.app')

@section('content')

    <link rel="stylesheet" href="{{asset('bower_components/sweetalert/sweetalert.css')}}">
    <section class="content-header">
        <h1>
            Gestión Usuario
        </h1>

    </section>

    <section class="content" id="content_form">

        <div class="box" id="listado_user">
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
                    <table id="tabla_usuario" width="100%"class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombres</th>
                                <th>Usuario</th>
                                <th>Perfil</th>
                                <th style="min-width: 30%">Opciones</th>
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


        <div id="form_ing" style="display:none">
            <form class="form-horizontal" id="form_registro_user" autocomplete="off" method="post"
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
                            <label for="inputPassword3" class="col-sm-3 control-label">Persona</label>
                            <div class="col-sm-8">
                                <select data-placeholder="Seleccione Una Persona" style="width: 100%;" class="form-control select2" name="idpersona" id="idpersona" >
                                
                                    @foreach ($persona as $dato)
                                        <option value=""></option>
                                        <option value="{{ $dato->idpersona}}" >{{ $dato->nombres}} {{ $dato->apellidos }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                        </div>

                        <div class="form-group">

                            <label for="inputPassword3" class="col-sm-3 control-label">Perfil</label>
                            <div class="col-sm-8">
                                <select data-placeholder="Seleccione Una Perfil" style="width: 100%;" class="form-control select2" name="idperfil" id="idperfil" >
                                
                                    @foreach ($perfil as $dato)
                                        <option value=""></option>
                                        <option value="{{ $dato->idperfil}}" >{{ $dato->descripcion }} {{ $dato->apellidos }}</option>
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

        <div class="modal fade_ detalle_class"  id="modal_Bodega" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">INFORMACIÓN DE BODEGA POR USUARIO</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            
                            <div class="table-responsive col-md-12">
                                <table id="tabla_menu" width="100%"class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Bodega</th>
                                           
                                            <th style="min-width: 30%">Opciones</th>
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
        


    </section>


@endsection
@section('scripts')
    <script src="{{asset('bower_components/sweetalert/sweetalert.js')}}"></script>
    <script src="{{ asset('js/gestionAcceso/usuario.js?v='.rand())}}"></script>

    <script>
        llenar_tabla_usuario()
        // limpiarCampos()
    </script>


@endsection
