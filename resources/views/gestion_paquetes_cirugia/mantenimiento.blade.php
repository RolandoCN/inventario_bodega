@extends('layouts.app')

@section('content')

<style>
      
    .mayusc {
        text-transform: uppercase;
    }

   
</style>
    <section class="content-header">
        <h1>
            Gestión Paquete
        </h1>

    </section>

    <section class="content" id="content_form">

        <div class="box"id="listado_paquete" >
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
                    <button type="button" onclick="visualizarFormPrinc('N')" class="btn btn-primary btn-sm">Nuevo</button>
                </div>

                <div class="table-responsive">
                    <table id="tabla_paquete" width="100%"class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Descripcion</th>
                                <th>Area</th>
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
            <form class="form-horizontal" id="form_paquete" autocomplete="off" method="post"
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

                            <label for="inputPassword3" class="col-sm-3 control-label">Descripción</label>
                            <div class="col-sm-8">
                                <input type="text" minlength="1" maxlength="100" onKeyPress="if(this.value.length==100) return false;" class="form-control" id="descripcion" name="descripcion" placeholder="Descripción">
                               
                            </div>
                           
                        </div>

                        <div class="form-group">

                            <label for="inputPassword3" class="col-sm-3 control-label">Area</label>
                            <div class="col-sm-8">
                                <select data-placeholder="Seleccione Una Area" style="width: 100%;" class="form-control select2" name="area" id="area" >
                                
                                    <option value=""></option>
                                    <option value="CENTRO OBSTETRICO" >CENTRO OBSTETRICO </option>
                                    <option value="CENTRO QUIRURGICO" >CENTRO QUIRURGICO </option>
                                  
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

        <div class="modal fade_ detalle_class"  id="modal_Menu" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span></button>
                        <h4 class="modal-title"><span  id="paq_selecc" class="text-transform: uppercase !important"> </span> </h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                           
                            <div class="col-md-12">
                                <form class="form-horizontal" id="form_registro_detalle_paq" autocomplete="off" method="post"
                                    action="">
                                    {{ csrf_field() }}
                                    <div class="form-group">
        
                                        <label for="inputPassword3" class="col-sm-3 control-label"></label>
                                        <div class="col-sm-7">
                                            <input type="hidden" minlength="1" maxlength="100" onKeyPress="if(this.value.length==100) return false;" class="form-control" id="idpaquete_cab" name="idpaquete_cab" >

                                            <input type="hidden" minlength="1" maxlength="100" onKeyPress="if(this.value.length==100) return false;" class="form-control" id="tipo_item" name="tipo_item" >
                                           
                                        </div>
                                    
                                    </div>
                                    
                                    <div class="form-group">
        
                                        <label for="inputPassword3" class="col-sm-3 control-label">Descripción</label>
                                        <div class="col-sm-7">
                                          
                                            <select data-placeholder="Seleccione Un Item" style="width: 100%;" class="form-control select2" id="item_selecci" name="item_selecci" onchange="ItemSelecc()">
                                                <option value="" class="cmb_item_selecci"></option>
                                               
                                               
                                            </select>

                                        </div>
                                    
                                    </div>

                                    <div class="form-group">
        
                                        <label for="inputPassword3" class="col-sm-3 control-label">Cantidad</label>
                                        <div class="col-sm-7">
                                            <input type="number" minlength="1" maxlength="100" onKeyPress="if(this.value.length==100) return false;" class="form-control" id="cantidad_item" name="cantidad_item" >
        
                                        </div>
                                    
                                    </div>


                                    <div class="form-group">
                                        <div class="col-sm-12 col-md-offset-3 " >
                                        
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <span id="nombre_btn_form_detalle"></span>
                                            </button>
                                            <button type="button" onclick="cancelarDetalle()" class="btn btn-danger btn-sm">Cancelar</button>
                                        </div>
                                    </div>
                                    
                                </form>
                            </div>
                            
                            <div class="table-responsive col-md-12">
                                <table id="tabla_detalle_paq" width="100%"class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Descripción</th>
                                            <th>Cantidad</th>
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
                 
                </div>
        
            </div>
        
        </div>
        

    </section>

@endsection

@section('scripts')

   
    <script src="{{ asset('js/gestionPaqueteCirugia/paquete.js?v='.rand())}}"></script>

    <script>
        llenar_tabla_paquete()
        // limpiarCampos()
    </script>


@endsection
