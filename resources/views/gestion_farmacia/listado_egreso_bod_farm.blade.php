@extends('layouts.app')

@section('content')

    <link rel="stylesheet" href="{{asset('bower_components/sweetalert/sweetalert.css')}}">
    <section class="content-header" id="arriba">
        <h1>
            Listado Egreso Bodega Farmacia
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
                    <form id="frm_buscar" class="form-horizontal" action="" autocomplete="off">
                        {{ csrf_field() }}
                        <div class="box-body">

                            <div class="form-group">
                                <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Buscar Por:</label>
                                
                                <div class="col-sm-10" style="font-weight: normal;">                     
                                    <select data-placeholder="Seleccione Una Opcion" style="width: 100%;" class="form-control select2" id="busqueda_egreso_cmb" name="busqueda_egreso_cmb" onchange="filtraEgreso()">
                                        <option value="" selected></option>   
                                        <option value="F">Fecha</option>   
                                        <option value="P">Paciente</option>                                       
                                    </select>
                                </div>
                                        
                            </div>

                            <div id="busqueda_fecha" style="display: none">    
                                <div class="form-group">
                                    <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Fecha Inicio:</label>
                                    
                                    <div class="col-sm-10" style="font-weight: normal;">                     
                                        <input type="date"  class="form-control" id="bus_fecha_ini"  name="bus_fecha_ini" >
                                    </div>
                                            
                                </div>

                                <div class="form-group">
                                    <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Fecha Fin:</label>
                                    
                                    <div class="col-sm-10" style="font-weight: normal;">                     
                                        <input type="date"  class="form-control" id="bus_fecha_fin"  name="bus_fecha_fin" >
                                    </div>
                                            
                                </div>
                            </div>

                            <div id="busqueda_paciente" style="display: none">    
                                <div class="form-group">
                                    <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Datos Paciente:</label>
                                    
                                    <div class="col-sm-10" style="font-weight: normal;">                     
                                        <select data-placeholder="Busqueda por Numero Documento o Nombres del Paciente" style="width: 100%;" class="form-control select2" id="paciente_cmb" name="paciente_cmb">
                                            <option value=""></option>
                                        </select>
                                    </div>
                                            
                                </div>

                            </div>

                            <div class="form-group">
                                <div class="col-sm-12 col-md-offset-2" >
                                
                                    <button type="button" onclick="buscarEgresos()" class="btn btn-success btn-sm">
                                        Buscar
                                    </button>
                                  
                                </div>
                            </div>
                            
                        </div>
                    </form>
                </div>

                <div id="listado_permiso" >
                    <div class="table-responsive" style="margin-bottom:20px; margin-top:10px">
                        <table id="tabla_egreso" width="100%"class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Comprobante</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Motivo</th>
                                    <th class="text-center">Responsable</th>
                                    <th class="text-center">Paciente</th>
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

        {{-- modal detalle aprobado --}}

        <div class="modal fade"  data-keyboard="false" data-backdrop="static" id="modal_detalle" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">

            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span></button>
                        <h5 class="modal-title" style="font-weight:550">DETALLE COMPROBANTE</h5>
                    </div>
                    <div class="modal-body">
                        <div class="row ">
                            
                            <div class="col-md-12 col-sm-12"  id="seccion_detalle">        
                            
                                <div id="div_infor_apr">
                                    
                                    <div class="row_" style="font-size: 13px">
                                        <div class="col-md-6">
                                            <ul class="nav nav-pills nav-stacked"style="margin-left:0px">
                                                <li style="border-color: white"><a><i class="fa fa-credit-card text-blue"></i> <b class="text-black" style="font-weight: 650 !important">Codigo</b>: <span  class="codigo_detalle">  </span></a></li>
                                            
                                            </ul>
                                            <ul class="nav nav-pills nav-stacked"style="margin-left:0px">
                                                <li style="border-color: white"><a><i class="fa fa-user text-blue"></i> <b class="text-black" style="font-weight: 650 !important"> Despacha</b>: <span  class="despacha_detalle"></span></a></li>
                                            
                                            </ul>

                                           

                                        </div>     
                                        <div class="col-md-6">
                                            <ul class="nav nav-pills nav-stacked" style="margin-left:0px">
                                                <li style="border-color: white"><a><i class="fa fa-user text-blue"></i> <b class="text-black" style="font-weight: 650 !important">Solicita:</b> <span  class="funcionario_detalle"> </span></a></li>
                                                
                                            </ul>

                                            <ul class="nav nav-pills nav-stacked"style="margin-left:0px">
                                                <li style="border-color: white"><a><i class="fa fa-calendar text-blue"></i> <b class="text-black" style="font-weight: 650 !important"> Fecha</b>: <span  class="fecha_detalle"></span></a></li>
                                            
                                            </ul>

                                        </div>  


                                    </div>
                                </div> 

                                <div class="table-responsive col-md-12" style="margin-bottom:20px; margin-top:10px" id="tabla_lab">
                                    <table id="tabla_detalle_pedido" width="100%"class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Item</th>
                                                <th class="text-center">Lote</th>
                                                <th class="text-center">Cant. Solicitada</th>
                                                <th class="text-center">Fecha Caduc</th>
                                                <th class="text-center">Cant Validada</th>
                                                <th class="text-center">Stock</th>
                                                <th class="text-center"></th>
                                               
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="7"><center>No hay Datos Disponibles</td>
                                            </tr>
                                            
                                        </tbody>
                                    
                                    </table>  
                                </div>    

                                <div class="col-md-12" style="margin-top: 15px !important">
                                    <center>

                                        {{-- <button onclick="revertir()"   type="button" class="btn btn-warning btn-xs btn_valida" ><span class="fa fa-check-circle-o"></span> Revertir</button> --}}

                                        <button onclick="cerrarRevertit()"  id="btn_cancelar_" type="button" class="btn btn-danger btn-xs" ><span class="fa fa-times"></span> Cerrar</button>

                                      
                                    
                                    </center>
                                </div>
                            
                            </div>

                            <div id="seccion_revertir" style="display: none" class="col-md-12">

                                <form id="frm_revertir" class="form-horizontal" action="" autocomplete="off">
                                    {{ csrf_field() }}
                                    <div class="box-body">
            
                                        <div class="form-group">
                                            <label for="inputEmail3" id="label_crit" class="col-sm-2 control-label" >Observacion</label>
                                            
                                            <div class="col-sm-10" style="font-weight: normal;">                     
                                                <textarea  class="form-control" id="motivo_rev"  name="motivo_rev" ></textarea >
                                            </div>
                                                    
                                        </div>
            
                                       
                                        <div class="form-group">
                                            <div class="col-sm-12 col-md-offset-2" >
                                            
                                                {{-- <button type="button" onclick="procesarReversion()" class="btn btn-success btn-xs">
                                                    Continuar
                                                </button> --}}

                                                <button onclick="cancelaRevertit()"  id="btn_cancelar_1" type="button" class="btn btn-danger btn-xs" >Cancelar</button>
                                              
                                            </div>
                                        </div>
                                        
                                    </div>
                                </form>

                            </div>

                            
        
                        
                        </div>
        
                    
                    </div>
                
                </div>
        
            </div>
        
        </div>

        {{-- fin modal detalle aprobado --}}
        @include('gestion_bodega.modal_doc')

    </section>

@endsection
@section('scripts')
    <script src="{{asset('bower_components/sweetalert/sweetalert.js')}}"></script>
    <script src="{{ asset('js/gestionFarmacia/listado_egreso_bodega.js?v='.rand())}}"></script>
    <script>
        $('#tituloCabecera').html('Buscar')
        buscarPaciente()
    </script>

@endsection
