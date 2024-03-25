@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="{{asset('bower_components/sweetalert/sweetalert.css')}}">

    
    <section class="content-header">
        <h1>
            Mantenimiento de Items
        </h1>

    </section>

    <section class="content" id="content_form">

        <div class="box" id="listado_funcionario">
            <div class="box-header with-border">
                {{-- <h3 class="box-title">Formulario </h3> --}}
                <button type="button" onclick="listarMedGral(1)" class="btn btn-success btn-xs">Medicamentos</button>
                <button type="button" onclick="listarMedGral(2)" class="btn btn-danger btn-xs">Insumos</button>
                <button type="button" onclick="listarMedGral(13)" class="btn btn-warning btn-xs">Laboratorio Reactivo</button>
                <button type="button" onclick="listarMedGral(14)" class="btn btn-info btn-xs">Laboratorio Microb</button>
                <button type="button" onclick="listarMedGral(8)" class="btn btn-success btn-xs">Laboratorio Materiales</button>
                <button type="button" onclick="listarMedGral(3)" class="btn btn-danger btn-xs">Oficina</button>
                <button type="button" onclick="listarMedGral(4)" class="btn btn-warning btn-xs">Aseo y Limp</button>
                <button type="button" onclick="listarMedGral(5)" class="btn btn-info btn-xs">Herramienta</button>

                <button type="button" onclick="listarMedGral(9)" class="btn btn-success btn-xs">Tics</button>
                <button type="button" onclick="listarMedGral(10)" class="btn btn-danger btn-xs">Lenceria</button>
                {{-- <button type="button" onclick="listarMedGral(17)" class="btn btn-warning btn-xs">Medicamento Dialisis</button>
                <button type="button" onclick="listarMedGral(18)" class="btn btn-info btn-xs">Insumo Dialisis</button>
                <button type="button" onclick="listarMedGral(19)" class="btn btn-success btn-xs">Laboratorio Dialisis</button> --}}

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip"
                        title="Collapse">
                        <i class="fa fa-minus"></i>
                    </button>
                    
                </div> 

              
            </div>
            <div class="box-body">
                
                <div id="listado_global" style="display: none">

                    <div class="row">
                        <div class="col-md-3"></div>
                        <div class="col-md-6">
                            <ul class="nav nav-pills nav-stacked"style="margin-left:200px">
                                <li style="border-color: white"><a><i class="fa fa-building text-blue"></i> <b class="text-black" style="font-weight: 650 !important">Bodega</b>: <span  class="bodega_seleccionada"></span></a></li>
                                
                            </ul>
                            
                        </div>     
                        <div class="col-md-3"></div>
                        <div class="col-md-12">
                            <center><button type="button" onclick="nuevoItem()" class="btn btn-success btn-xs">Nuevo</button></center>
                        </div>
                       
                    </div>
                    <div class="table-responsive" style="margin-bottom:20px; margin-top:20px">

                        <table id="tabla_inventario_global" width="100%"class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Codigo</th>
                                    <th class="text-center">Descripcion</th>
                                  
                                    <th class="text-center"></th>
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

                <div id="listado_global_med" style="display: none">

                    <div class="row">
                        <div class="col-md-3"></div>
                        <div class="col-md-6">
                            <ul class="nav nav-pills nav-stacked"style="margin-left:200px">
                                <li style="border-color: white"><a><i class="fa fa-building text-blue"></i> <b class="text-black" style="font-weight: 650 !important">Bodega</b>: <span  class="bodega_seleccionada"></span></a></li>
                                
                            </ul>
                            
                        </div>     
                        <div class="col-md-3"></div>
                        <div class="col-md-12">
                            <center><button type="button" onclick="nuevoItem()" class="btn btn-success btn-xs">Nuevo</button></center>
                        </div>

                       
                       
                    </div>

                    <div class="col-md-12" style="margin-top:52px">
                        <form class="form-horizontal" id="form_Fitltra" autocomplete="off" method="post"
                        action="">
                            <div class="form-group">
                                <label for="inputPassword3" class="col-sm-3 control-label"></label>
                                <div class="col-sm-6">
                                    <input type="text"  autocomplete="of" name="buscaItem" class="form-control" id="buscaItem" placeholder="Busqueda por Nombre, Codigo Esbay o Cudim">
                                    
                                </div>                         
                            </div>
                        </form>
                    </div>
                    <div class="table-responsive col-md-12" style="margin-bottom:20px; margin-top:20px">

                        <table id="tabla_inventario_global_md" width="100%"class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Cudim</th>
                                    <th class="text-center">Cod Esbay</th>
                                    <th class="text-center">Descripcion</th>
                                    {{-- <th class="text-center">Stock</th>
                                    <th class="text-center">Stock Min</th>
                                    <th class="text-center">Stock Crit</th>
                                    <th class="text-center">Info</th> --}}
                                    <th class="text-center"></th>
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

                <div class="col-md-12" id="form_item" style="display: none">
                    <h4 class="text-center">BODEGA MEDICINA</h4>
                    <form class="form-horizontal" id="form_medicina_new" autocomplete="off" method="post"
                    action="">
                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-3 control-label">Cum</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="codigo" id="codigo"  placeholder="Cum">
                                
                            </div>                      
                        </div>

                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-3 control-label">Codigo Esbay</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="cod_esbay_med" id="cod_esbay_med" placeholder="Esbay" >
                                
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

                        {{-- <div class="form-group">
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
                        </div> --}}

                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-3 control-label"></label>
                            <div class="col-sm-6">
                                <button type="button" onclick="validaFormMedicina()" class="btn btn-success btn-sm">Guardar</button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="volverBusquedaMed()">Volver</button>

                            </div>                      
                        </div>

                    </form>


                </div>

                <div class="col-md-12" id="parametro_item" style="display: none">

                    <div class="table-responsive col-md-12" style="margin-bottom:20px; margin-top:20px; overflow:auto">

                        <table id="tabla_parametro" width="100%"class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Medica</th>
                                    <th class="text-center">Cardio</th>
                                    <th class="text-center">Ciru</th>
                                    <th class="text-center">Cobste</th>
                                    <th class="text-center">Cod_sisbo</th>
                                    <th class="text-center">cons_ext</th>

                                    <th class="text-center">covid_hosp</th>
                                    <th class="text-center">covid_tria</th>
                                    <th class="text-center">Cqui</th>
                                    <th class="text-center">derma</th>
                                    <th class="text-center">emerg</th>
                                    <th class="text-center">emerg_hosp</th>
                                    <th class="text-center">endocri</th>

                                    <th class="text-center">fisi</th>
                                    <th class="text-center">gastro</th>
                                    <th class="text-center">geriatra</th>
                                    <th class="text-center">gine</th>

                                    <th class="text-center">hos</th>

                                    <th class="text-center">infecto</th>
                                    <th class="text-center">medlab</th>

                                    <th class="text-center">mint</th>
                                    <th class="text-center">nefro</th>


                                    <th class="text-center">neo</th>
                                    <th class="text-center">neuro</th>
                                    <th class="text-center">nuemo</th>
                                    <th class="text-center">nutri</th>
                                    <th class="text-center">odon</th>
                                    <th class="text-center">otorrino</th>

                                    <th class="text-center">ped</th>
                                    <th class="text-center">psico</th>
                                    <th class="text-center">reuma</th>
                                    <th class="text-center">saludm</th>
                                    <th class="text-center">trauma</th>
                                    <th class="text-center"width="500px"> UCI </th>

                                    <th class="text-center">uci_covid</th>


                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="2"><center>No hay Datos Disponibles</td>
                                </tr>
                                
                            </tbody>
                        
                        </table>  
                    </div>    


                    <div class="col-md-12">
                        <center>
                            <button type="button" class="btn btn-danger btn-sm" onclick="volverBusqueda()">Volver</button>

                        </center>
                    </div>
                </div>

                <div class="col-md-12" id="parametro_insumo" style="display: none">

                    <div class="table-responsive col-md-12" style="margin-bottom:20px; margin-top:20px; overflow:auto">

                        <table id="tabla_parametro_ins" width="100%"class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Insumo</th>
                                   
                                    <th class="text-center">autorief</th>
                                    <th class="text-center">autorimed</th>
                                    <th class="text-center">Cardio</th>
                                    <th class="text-center">cons_ext</th>
                                    <th class="text-center">central</th>
                                    <th class="text-center">Ciru</th>

                                    <th class="text-center">Cobste</th>
                                    <th class="text-center">Cod_sisbo</th>


                                    <th class="text-center">cqui</th>
                                    <th class="text-center">derma</th>

                                    <th class="text-center">emerg</th>
                                    <th class="text-center">emerg_hosp</th>
                                    <th class="text-center">endocri</th>
                                    <th class="text-center">epp</th>

                                    <th class="text-center">fisi</th>
                                    <th class="text-center">gastro</th>
                                    <th class="text-center">geriatra</th>
                                    <th class="text-center">gine</th>

                                    <th class="text-center">hos</th>
                                    <th class="text-center">imagen</th>

                                    <th class="text-center">infecto</th>
                                    <th class="text-center">labo</th>
                                    <th class="text-center">medlab</th>

                                    <th class="text-center">mint</th>
                                    <th class="text-center">nefro</th>


                                    <th class="text-center">neo</th>
                                    <th class="text-center">nuemo</th>
                                    <th class="text-center">neuro</th>
                                  
                                    <th class="text-center">nutri</th>
                                    <th class="text-center">odon</th>
                                    <th class="text-center">otorrino</th>

                                    <th class="text-center">ped</th>
                                    <th class="text-center">psico</th>
                                    <th class="text-center">reuma</th>
                                    <th class="text-center">saludm</th>
                                    <th class="text-center">trauma</th>
                                    <th class="text-center"width="500px"> UCI </th>

                                    <th class="text-center">uro</th>


                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="2"><center>No hay Datos Disponibles</td>
                                </tr>
                                
                            </tbody>
                        
                        </table>  
                    </div>    


                    <div class="col-md-12">
                        <center>
                            <button type="button" class="btn btn-danger btn-sm" onclick="volverBusqueda()">Volver</button>

                        </center>
                    </div>
                </div>

                <div class="col-md-12" id="form_item_ins" style="display: none">
                    <h4 class="text-center">BODEGA INSUMO</h4>
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

                        
                        {{-- <div class="form-group">
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
                        </div> --}}

                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-3 control-label"></label>
                            <div class="col-sm-6">
                                <button type="button" onclick="validaFormInsumo()" class="btn btn-success btn-sm">Guardar</button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="volverBusquedaMed()">Volver</button>

                            </div>                      
                        </div>

                    </form>


                </div>

                <div class="col-md-12" id="form_item_lab" style="display: none">
                    <h4 class="text-center lab_form"></h4>
                    <form class="form-horizontal" id="form_lab_new" autocomplete="off" method="post"
                    action="">

                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-3 control-label">Cudim</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="cod_lab" id="cod_lab" placeholder="Cudim" >
                                
                            </div>                      
                        </div>

                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-3 control-label">Codigo Esbay</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="cod_esbay_lab" id="cod_esbay_lab" placeholder="Esbay" >
                                
                            </div>                      
                        </div>
                       
                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-3 control-label">Descripcion</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="desc_lab" id="desc_lab" placeholder="Descripcion" >

                                <input type="hidden" class="form-control" name="idbod" id="idbod">
                                
                            </div>                      
                        </div>

                        {{-- <div class="form-group">
                            <label for="inputPassword3" class="col-sm-3 control-label">Stock Min</label>
                            <div class="col-sm-6">
                                <input type="number" class="form-control" name="stock_min_lab" id="stock_min_lab"  placeholder="Stock Min">
                                
                            </div>                      
                        </div>

                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-3 control-label">Stock Critico</label>
                            <div class="col-sm-6">
                                <input type="number" class="form-control" name="stock_cri_lab" id="stock_cri_lab" placeholder="Stock Critico" >
                                
                            </div>                      
                        </div> --}}



                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-3 control-label"></label>
                            <div class="col-sm-6">
                                <button type="button" onclick="validaFormLab()" class="btn btn-success btn-sm">Guardar</button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="volverBusquedaMed()">Volver</button>

                            </div>                      
                        </div>

                    </form>


                </div>

                <div class="col-md-12" id="form_item_mat_of" style="display: none">
                    <h4 class="text-center item_form"></h4>
                    <form class="form-horizontal" id="form_mat_new" autocomplete="off" method="post"
                    action="">

                        <div class="form-group">
                            <label for="inputPassword3" class="col-sm-3 control-label">Codigo</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" autocomplete="of" name="codigo_item" id="codigo_item" placeholder="Codigo" >
                                
                            </div>                      
                        </div>

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
                                        <div class="col-sm-1">
                                            <button type="button" onclick="nuevoItem()" class="btn btn-sm btn-success" style="margin-top:5px">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                            
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
    <script src="{{ asset('js/gestionBodega/mantenimiento_item.js?v='.rand())}}"></script>
 

    <script>
        // llenar_tabla_especialidad()
        // limpiarCampos()
    </script>


@endsection
