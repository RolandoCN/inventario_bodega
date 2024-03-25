@extends('layouts.app')

@section('content')

    
    <section class="content-header" id="arriba">
        <h1>
            Listado Pacientes
        </h1>

    </section>

    <section class="content" id="content_form">

        <div class="box" id="listado_persona">
            <div class="box-header with-border">
                <h3 class="box-title" id="tituloCabecera"> </h3>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip"
                        title="Collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                    
                </div>

              
            </div>
            <div class="box-body">

                <div class="col-md-12" id="content_consulta">
                    <form id="frm_buscarAuditoria" class="form-horizontal" action="" autocomplete="off">
                        {{ csrf_field() }}
                        <div class="box-body">

                            {{-- <div class="form-group">
                                <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Fecha:</label>
                                
                                <div class="col-sm-10" style="font-weight: normal;">                     
                                    <input type="date"  class="form-control" id="fecha_hosp"  name="fecha_hosp" >
                                </div>
                                        
                            </div> --}}

                            <div class="form-group">
                                <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Servicio:</label>
                                
                                <div class="col-sm-10" style="font-weight: normal;">                     
                                    <select data-placeholder="Seleccione Un Servicio" style="width: 100%;" class="form-control select2" name="cmb_servicio" id="cmb_servicio"  >
                                        <option value=""></option>
                                        <option value="UCI">UCI</option>
                                        <option value="CENTRO OBSTETRICO" >CENTRO OBSTETRICO </option>
                                        <option value="CIRUGIA" >CIRUGIA</option>
                                        <option value="GINECOLOGIA" >GINECOLOGIA</option>
                                        <option value="MEDICINA INTERNA" >MEDICINA INTERNA</option>
                                        <option value="NEONATOLOGIA" >NEONATOLOGIA</option>
                                        <option value="PEDIATRIA" >PEDIATRIA</option>
                                        <option value="EMERGENCIA" >EMERGENCIA</option>
                                        <option value="TODAS" >TODAS</option>
                                       
                                    </select>
                                </div>
                                        
                            </div>

                            <div class="form-group">
                                <div class="col-sm-12 col-md-offset-2" >
                                
                                    <button type="button" onclick="buscarPacientes()" class="btn btn-success btn-sm">
                                        Buscar
                                    </button>
                                  
                                </div>
                            </div>
                            
                        </div>
                    </form>
                </div>

                <div id="listado_permiso" >
                    <div class="table-responsive" style="margin-bottom:20px; margin-top:10px">
                        <table id="tabla_ingreso" width="100%"class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Comprobante</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Proveedor</th>
                                    <th class="text-center">Responsable</th>
                                    <th class="text-center">Bodega</th>
                                    <th class="text-center">Tipo</th>
                                    <th class="text-center">Total</th>
                                    <th style="min-width: 30%" class="text-center">Opciones</th>
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

        </div>

        @include('gestion_bodega.modal_doc')
    </section>

@endsection
@section('scripts')

    <script src="{{ asset('js/gestionPaciente/listado_hospitalizados.js?v='.rand())}}"></script>
    <script>
        $('#tituloCabecera').html('Buscar')
    </script>

@endsection
