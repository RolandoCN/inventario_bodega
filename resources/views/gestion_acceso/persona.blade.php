@extends('layouts.app')

@section('content')

    
    <section class="content-header">
        <h1>
            Gestión Persona-Empresa
        </h1>

    </section>

    <section class="content" id="content_form">

        <div class="box" id="listado_persona">
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
                    <table id="tabla_persona" width="100%"class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Numero Ident</th>
                                <th>Nombres</th>
                                <th>Apellidos</th>
                                <th>Teléfono</th>
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
            <form class="form-horizontal" id="form_registro_persona" autocomplete="off" method="post"
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
                            <label for="inputPassword3" class="col-sm-3 control-label">Tipo Identificacion</label>
                            <div class="col-sm-8">
                                <select data-placeholder="Seleccione Una Tipo" style="width: 100%;" class="form-control select2" name="tipo_id" id="tipo_id" onchange="tipo_identificacion_selecc()">                                
                                   
                                    <option value="" selected></option>
                                    <option value="1" >Cedula</option>
                                    <option value="2" >Ruc</option>
                                                                     
                                </select>                               
                            </div>                           
                        </div>
                        
                        <div class="form-group seccion_cedula" style="display: none">
                            <label for="inputPassword3" class="col-sm-3 control-label">Cédula</label>
                            <div class="col-sm-8">
                                <input type="number" minlength="1" maxlength="10" onKeyPress="if(this.value.length==10) return false;"  class="form-control" id="cedula_persona" name="cedula_persona" placeholder="Cedula">
                                <span class="invalid-feedback" role="alert" style="color:red; display:none
                                " id="error_cedula">
                                    <strong id="txt_error_cedula"></strong>
                                </span>
                            </div>
                            
                        </div>

                        <div class="form-group seccion_ruc" style="display: none">
                            <label for="inputPassword3" class="col-sm-3 control-label">Ruc</label>
                            <div class="col-sm-8">
                                <input type="number" minlength="1" maxlength="13" onKeyPress="if(this.value.length==13) return false;"  class="form-control" id="ruc_persona" name="ruc_persona" placeholder="Ruc">
                                
                            </div>
                            
                        </div>

                        <div class="form-group">

                            <label for="inputPassword3" class="col-sm-3 control-label">Nombres</label>
                            <div class="col-sm-8">
                                <input type="text" minlength="1" maxlength="100" onKeyPress="if(this.value.length==100) return false;" class="form-control" id="nombres" name="nombres" placeholder="Nombres">
                                <span class="invalid-feedback" role="alert" style="color:red; display:none
                                " id="error_nombres">
                                    <strong id="txt_error_nombres"></strong>
                                </span>
                            </div>
                           
                        </div>


                        <div class="form-group">

                            <label for="inputPassword3" class="col-sm-3 control-label">Apellidos</label>
                            <div class="col-sm-8">
                                <input type="text" minlength="1" maxlength="100" onKeyPress="if(this.value.length==100) return false;"  class="form-control" id="apellidos" name="apellidos" placeholder="Apellidos">
                                <span class="invalid-feedback" role="alert" style="color:red; display:none
                                " id="error_apellidos">
                                    <strong id="txt_error_apellidos"></strong>
                                </span>
                            </div>
                           
                        </div>

                        <div class="form-group">

                            <label for="inputPassword3" class="col-sm-3 control-label">Teléfono</label>
                            <div class="col-sm-8">
                                <input type="number" minlength="1" maxlength="10" onKeyPress="if(this.value.length==10) return false;"  class="form-control" id="telefono" name="telefono" placeholder="Teléfono">
                                <span class="invalid-feedback" role="alert" style="color:red; display:none
                                " id="error_telefono">
                                    <strong id="txt_error_telefonos"></strong>
                                </span>
                            </div>
                           
                        </div>

                        <div class="form-group">

                            <label for="inputPassword3" class="col-sm-3 control-label">Correo</label>
                            <div class="col-sm-8">
                                <input type="text" minlength="1" maxlength="100" onKeyPress="if(this.value.length==100) return false;"  class="form-control" id="email" name="email" placeholder="Email">
                                
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

    <script src="{{ asset('js/gestionAcceso/persona.js?v='.rand())}}"></script>

    <script>
        llenar_tabla_persona()
        limpiarCampos()
    </script>


@endsection
