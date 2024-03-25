@extends('layouts.app')

@section('content')

    
    <section class="content-header">
        <h1>
            Administración de Medicinas por Especialidad
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
                        <label for="inputPassword3" class="col-sm-3 control-label">Especialidad</label>
                        <div class="col-sm-6">
                            <select data-placeholder="Seleccione Una Especialidad" style="width: 100%;" class="form-control select2" name="id_area" id="id_area" onchange="MedicinaEspecialida()" >
                            
                                @foreach ($especialidad as $dato)
                                    <option value=""></option>
                                    <option value="{{ $dato->idespecialidad}}" >{{ $dato->especialidad }} </option>
                                @endforeach

                            </select>
                        </div>                           
                    </div>
                </form>
                <div class="table-responsive">
                    <table id="tabla_especialidad" width="100%"class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>idprod</th>
                                <th>Producto</th>
                               
                                <th style="min-width: 30%">Agregar/Quitar</th>
                            </tr>
                        </thead>
                        <tbody id="listaMedicinas">
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

    <script src="{{ asset('js/gestionBodega/medicina_funcionario.js?v='.rand())}}"></script>
 

    <script>
        // llenar_tabla_especialidad()
        // limpiarCampos()
    </script>


@endsection
