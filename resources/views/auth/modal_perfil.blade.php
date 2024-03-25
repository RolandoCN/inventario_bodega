<style>
    /* estilos solo para telefonos */
   @media screen and (max-width: 767px){
       .list-group-item{
           width: 100% !important;
       }
   }
</style>

<div class="modal fade" id="modal_perfil" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">INFORMACIÓN PERSONAL</h4>
            </div>
            <div class="modal-body">
                <div class="row ">
                    
                    <div class="col-md-12 col-sm-12" id="seccion_perfil">

                        <form id="form_perfil_" class="form-horizontal" autocomplete="off"  enctype="multipart/form-data"> 
                            {{csrf_field()}}
                            <input type="hidden" name="_method" value="PUT">
                            <div id="div_infor_perfil">
                                <div class="row_">
                                    <div class="col-md-6">
                                        <ul class="nav nav-pills nav-stacked"style="margin-left:0px">
                                            <li style="border-color: white"><a><i class="fa fa-credit-card text-blue"></i> <b class="text-black" style="font-weight: 650 !important">Identificación</b>: <span  id="iden">
                                                 {{ Auth::user()->persona->ci }} </span></a></li>
                                           
                                        </ul>
                                        <ul class="nav nav-pills nav-stacked"style="margin-left:0px">
                                            <li style="border-color: white"><a><i class="fa fa-phone text-blue"></i> <b class="text-black" style="font-weight: 650 !important"> Teléfono</b>: <span  id="tele">
                                                {{ Auth::user()->persona->tel1 }}
                                            </span></a></li>
                                           
                                        </ul>
                                    </div>     
                                    <div class="col-md-6">
                                        <ul class="nav nav-pills nav-stacked" style="margin-left:12px">
                                            <li style="border-color: white"><a><i class="fa fa-user text-blue"></i> <b class="text-black" style="font-weight: 650 !important">Usuario:</b> <span  id="user"> 
                                                {{ Auth::user()->persona->nom1 }}  {{ Auth::user()->persona->ape1 }}
                                            </span></a></li>
                                            
                                        </ul>
                                        <ul class="nav nav-pills nav-stacked" style="margin-left:12px">
                                            <li style="border-color: white"><a><i class="fa fa-key text-blue"></i> <b class="text-black" style="font-weight: 650 !important">Perfil:</b> <span  id="perf">
                                                 {{ auth()->user()->perfil->nombre_perfil->descripcion}}  
                                                </span></a></li>
                                            
                                        </ul>
                                    </div>  
                                </div>
                            </div>           
                        
                            
                            <div style="display:none" id="div_edit_perfil">
                                <br>
                                <div class="row">
                                    <div class="col-md-2  col-sm-12 col-xs-12"></div>
                                    <div class="col-md-8  col-sm-12 col-xs-12">
                                        <div class="form-group">
                                        <label>Correo:</label>
                                            <input type="text" autocomplete="off" name="email" id="correo_perfil" class="form-control" required  >
                                        </div>
                                        <div class="form-group">
                                            <label>Dirección:</label>
                                            <input type="text" autocomplete="off" name="direccion_perfil" id="direccion_perfil" class="form-control" required >
                                        </div>
                                        <div class="form-group">
                                            <label>Celular:</label>
                                            <input type="text" autocomplete="off" name="celular_perfil" maxlength="13" id="celular_perfil" class="form-control" required  >
                                        </div>
                                        <div class="form-group">
                                            <label>Foto Perfil:</label>
                                            <input type="file" autocomplete="off" accept="image/x-png,image/jpeg" name="foto_perfil"id="foto_perfil" class="form-control"   >
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-sm-12 col-xs-12"></div>

                                </div>
                            </div>
                                    
                                
                            <div class="col-md-12" style="margin-top: 15px !important">
                                <center>
                                    <button onclick="cancelar_actualizacion_perf()" style="display:none" id="btn_cancelar" type="button" class="btn btn-danger" ><span class="fa fa-times"></span> Cancelar</button>
                                   

                                    <button id="btn_password" type="button" onclick="modal_cambio_clave()" class="btn btn-warning"><span class="fa fa-key"></span> Cambiar Clave</button>
                                    
                                    <button style="display:none" id="btn_submit" type="submit"  class="btn btn-success"><span class="fa fa-save"></span> Aceptar</button>

                                </center>
                            </div>
                        </form>
                    </div>

                    <div id="form_cambio_contra" style="display: none">
                        <div id="mensajes_cambio_pass"></div>
                        <form id="form_cambio_claves" class="form-horizontal" autocomplete="off"> 
                            {{ csrf_field() }}
                            <br>
                            <div class="row">
                                <div class="col-md-2  col-sm-12 col-xs-12"></div>
                                <div class="col-md-8  col-sm-12 col-xs-12">
                                    <div class="form-group col-md-12">
                                        <label>Contraseña actual:</label>
                                        <input type="password" id="clave_actual"  name="clave_actual" class="form-control" required="true">
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label>Nueva contraseña:</label>
                                        <input type="password" id="clave_nueva" name="clave_nueva" class="form-control" required="true">
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label>Confirmar contraseña:</label>
                                        <input type="password" id="clave_nueva_confirm"  name="clave_nueva_confirm" class="form-control" required="true">
                                    </div>
                                </div>
                                <div class="col-md-2 col-sm-12 col-xs-12"></div>

                            </div>
                                
                            <div class="col-md-12" style="margin-top: 15px !important">
                                <center>
                                    <button onclick="cancelar_cambio_pass()"  id="btn_cancelar_pass" type="button" class="btn btn-danger" ><span class="fa fa-times"></span> Cancelar</button>
                                    <button  type="button" onclick="cambioPass()" id="btn_cambia_pass" class="btn btn-success"><span class="fa fa-save"></span> Aceptar</button>

                                </center>
                            </div>
                        </form>
                    </div>

                </div>

               
            </div>
         
        </div>

    </div>

</div>

<script>
    function cambioPass(){
        let clave_actual=$('#clave_actual').val()
        let clave_nueva=$('#clave_nueva').val()
        let clave_nueva_confirm=$('#clave_nueva_confirm').val()

        if($('#clave_nueva').val()!=$('#clave_nueva_confirm').val()){
           
            alertNotificar("Contraseñas no coinciden","error")
            return;
        }
        vistacargando("m","Espere por favor")
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: "POST",
                url: 'cambiar-clave',
                data:{ _token: $('meta[name="csrf-token"]').attr('content'),
                clave_actual:clave_actual,clave_nueva:clave_nueva,clave_nueva_confirm:clave_nueva_confirm},
        
            success: function (data) {
                vistacargando("")

                if(data['error']==true){
                    
                    alertNotificar(data.detalle,"error")
                    
                    return;
                }
                alertNotificar(data.detalle,"success")
                cancelar_cambio_pass()
            },
            error: function(e){
            vistacargando("")
                alertNotificar(" Inconvenientes al procesar la solicitud intente nuevamente","error")
            }
        });
    }
</script>












