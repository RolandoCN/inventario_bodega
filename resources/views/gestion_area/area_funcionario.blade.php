@extends('layouts.app')

@section('content')

    
    <section class="content-header">
        <h1>
            Administración de Funcionarios Por Areas
        </h1>

    </section>

    <section class="content" id="content_form">

        <div class="box" id="listado_funcionario">
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
                <form class="form-horizontal" id="form_funcionario" autocomplete="off" method="post"
                action="">
                {{ csrf_field() }}
                    <div class="form-group">
                        <label for="inputPassword3" class="col-sm-3 control-label">Área</label>
                        <div class="col-sm-6">
                            <select data-placeholder="Seleccione Una Área" style="width: 100%;" class="form-control select2" name="id_area" id="id_area" onchange="FuncionariosArea()" >
                            
                                @foreach ($area as $dato)
                                    <option value=""></option>
                                    <option value="{{ $dato->id_area}}" >{{ $dato->descripcion }} </option>
                                @endforeach

                            </select>
                        </div>                           
                    </div>
                </form>
                <div class="table-responsive">
                    <table id="tabla_funcionario" width="100%"class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Cédula</th>
                                <th>Funcionario</th>
                               
                                <th style="min-width: 30%">Agregar/Quitar</th>
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



    </section>

@endsection
@section('scripts')

    <script src="{{ asset('js/gestionArea/area_funcionario.js?v='.rand())}}"></script>
 

    <script>
        // llenar_tabla_funcionario()
        // limpiarCampos()
    </script>


@endsection
