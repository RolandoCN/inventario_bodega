

$("#form_registro_user").submit(function(e){
    e.preventDefault();
    
    //validamos los campos obligatorios
    let idpersona=$('#idpersona').val()
    let idperfil=$('#idperfil').val()
      
    if(idpersona=="" || idpersona==null){
        alertNotificar("Debe seleccionar una persona","error")
        return
    } 

    if(idperfil=="" || idperfil==null){
        alertNotificar("Debe seleccionar un perfil","error")
        return
    } 

    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    //comprobamos si es registro o edicion
    let tipo=""
    let url_form=""
    if(AccionForm=="R"){
        tipo="POST"
        url_form="guardar-usuario"
    }else{
        tipo="PUT"
        url_form="actualizar-usuario/"+idUserEditar
    }
    
    vistacargando("m","Espere por favor")
    var FrmData=$("#form_registro_user").serialize();
   
    $.ajax({
            
        type: tipo,
        url: url_form,
        method: tipo,             
		data: FrmData,      
		
        processData:false, 

        success: function(data){
           
            vistacargando("");                
            if(data.error==true){
                alertNotificar(data.mensaje,'error');
                return;                      
            }
            limpiarCampos()
            alertNotificar(data.mensaje,"success");
            $('#form_ing').hide(200)
            $('#listado_user').show(200)
            llenar_tabla_usuario()
                            
        }, error:function (data) {
            console.log(data)

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})

function limpiarCampos(){
    $('#idpersona').prop('disabled', false)
    $('#idperfil').val('').trigger('change.select2')
    $('#idpersona').val('').trigger('change.select2')
    
}

function llenar_tabla_usuario(){
    var num_col = $("#tabla_usuario thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_usuario tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);
   
    
    $.get("listado-usuario/", function(data){
       
        console.log(data)
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            $("#tabla_usuario tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
            return;   
        }
        if(data.error==false){
            
            if(data.resultado.length <= 0){
                $("#tabla_usuario tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
                alertNotificar("No se encontró datos","error");
                return;  
            }
         
            $('#tabla_usuario').DataTable({
                "destroy":true,
                pageLength: 10,
                autoWidth : true,
                order: [[ 1, "desc" ]],
                sInfoFiltered:false,
                language: {
                    url: 'json/datatables/spanish.json',
                },
                columnDefs: [
                    { "width": "10%", "targets": 0 },
                    { "width": "30%", "targets": 1 },
                    { "width": "20%", "targets": 2 },
                    { "width": "20%", "targets": 3 },
                    { "width": "20%", "targets": 4 },                
                ],
                data: data.resultado,
                columns:[
                        {data: "persona.apellidos"},
                        {data: "persona.apellidos"},
                        {data: "persona.numero_doc"},
                        {data: "persona.nombres"},
                        {data: "persona.nombres"},
                ],    
                "rowCallback": function( row, data, index ) {
                    $('td', row).eq(0).html(index+1)
                    $('td', row).eq(1).html(data.persona.nombres+" "+data.persona.apellidos)
                    // $('td', row).eq(2).html(data.persona.ci)
                    $('td', row).eq(3).html(data.perfil.nombre_perfil.descripcion)
                    $('td', row).eq(4).html(`
                                  
                                            <button type="button" class="btn btn-primary btn-xs" onclick="editarUsuario(${data.id})">Editar</button>

                                            <button type="button" class="btn btn-warning btn-xs" onclick="resetearPassword(${data.id})">Resetear </button>

                                          
                                                                                
                                            <a onclick="eliminarUsuario(${data.id})" class="btn btn-danger btn-xs"> Eliminar </a>

                                    
                    `); 
                }             
            });
        }
    }).fail(function(){
        $("#tabla_usuario tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });


}

$('.collapse-link').click();
$('.datatable_wrapper').children('.row').css('overflow','inherit !important');

$('.table-responsive').css({'padding-top':'12px','padding-bottom':'12px', 'border':'0', 'overflow-x':'inherit'});

function Bodega1(bod){
   
    
}

function Bodega(iduser, abiertaModal=null){
    
    
    $.get("bodega-usuario/"+iduser, function(data){
        console.log(data)
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            $("#tabla_menu tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
            return;   
        }
        if(data.error==false){
            
            if(data.resultado.length <= 0){
                $("#tabla_menu tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
                alertNotificar("No se encontró datos","error");
                return;  
            }
         
            $('#tabla_menu').DataTable({
                "destroy":true,
                pageLength: 10,
                autoWidth : true,
                order: [[ 1, "desc" ]],
                sInfoFiltered:false,
                language: {
                    url: '/json/datatables/spanish.json',
                },
                columnDefs: [
                    { "width": "20%", "targets": 0 },
                    { "width": "35%", "targets": 1 },
                    { "width": "25%", "targets": 2 },
                
                   
                ],
                data: data.resultado,
                columns:[
                        {data: "idbodega"},
                        {data: "nombre" },
                     
                        {data: "idbodega"},
                ],    
                "rowCallback": function( row, data, index ) {
                    $('td', row).eq(0).html(index+1)
                    let perm=""
                    if(data.accesoPerm=="S"){
                        perm="checked"
                    }else{
                        perm=""
                    }
                    $('td', row).eq(2).html(`
                                  
                                            
                                            <input type="checkbox" onclick="accionAcceso(${data.idbodega})"class="acces_check" id="check_${data.idbodega}" name="acces_check" value="${data.idbodega}"  ${perm}>
                                       
                                    
                    `); 
                }             
            });
            globalThis.UsuarioSelecc=iduser
            if(abiertaModal!="S"){
                $('#modal_Bodega').modal("show")
            }
               
        }

     

       
    }).fail(function(){
       
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}

function accionAcceso(id){
   
    if( $('#check_'+id).is(':checked') ){
        // mandamos a guardar ese menu al perfil
        aggQuitarBodegaUser(id,'A')
    } else {
        // mandamos a quitar
        aggQuitarBodegaUser(id,'Q')
    }
}

function aggQuitarBodegaUser(idbodega, tipo){
    vistacargando("m","Espere por favor")
    $.get("bodega-por-usuario/"+idbodega+"/"+tipo+"/"+UsuarioSelecc, function(data){
        vistacargando("")
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            return;   
        }
       
        alertNotificar(data.mensaje,"success")
        accesos(UsuarioSelecc,'S')

       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}


function editarUsuario(idusuario){
    vistacargando("m","Espere por favor")
    $.get("editar-usuario/"+idusuario, function(data){
        console.log(data)
       
        vistacargando("")
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            return;   
        }
        if(data.resultado==null){
            alertNotificar("La persona ya no se puede editar","error");
            return;   
        }
        $('#idperfil').val(data.resultado.perfil.id_perfil).trigger('change.select2')
        $('#idpersona').val(data.resultado.id_persona).trigger('change.select2')
        $('#idpersona').prop('disabled', true)

        visualizarForm('E')
        globalThis.idUserEditar=idusuario



       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}

function visualizarForm(tipo){
    $('#form_ing').show(200)
    $('#listado_user').hide(200)
    globalThis.AccionForm="";
    if(tipo=='N'){
        $('#titulo_form').html("Registro Usuario")
        $('#nombre_btn_form').html('Registrar')
        AccionForm="R"
    }else{
        $('#titulo_form').html("Actualizar Usuario")
        $('#nombre_btn_form').html('Actualizar')
        AccionForm="E"
    }
}

function visualizarListado(){
    $('#form_ing').hide(200)
    $('#listado_user').show(200)
    limpiarCampos()
}

function eliminarUsuario(idusuario){
    vistacargando("m","Espere por favor")
    if(confirm('¿Quiere eliminar el registro?')){
        $.get("eliminar-usuario/"+idusuario, function(data){
            vistacargando("")
            if(data.error==true){
                alertNotificar(data.mensaje,"error");
                return;   
            }
    
            alertNotificar(data.mensaje,"success");
            if(data.desloguear=="S"){
                window.location.href="/"
            }
            llenar_tabla_usuario()
           
        }).fail(function(){
            vistacargando("")
            alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
        });
    }
   
}

function resetearPassword(idusuario){
    swal({
        title: "¿Desea resetear la contraseña del usuario?",
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-danger",
        confirmButtonText: "Si, continuar",
        cancelButtonText: "No, cancelar",
        closeOnConfirm: false,
        closeOnCancel: false
    },
    function(isConfirm) {
        if (isConfirm) { 
            //mmandamos a resetear
            vistacargando("m","Espere por favor")
            $.get("resetear-password/"+idusuario, function(data){
                vistacargando("")
                if(data.error==true){
                    alertNotificar(data.mensaje,"error");
                    return;   
                }
        
                alertNotificar(data.mensaje,"success");
                if(data.desloguear=="S"){
                    window.location.href="/"
                }
                llenar_tabla_usuario()
                
            }).fail(function(){
                vistacargando("")
                alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
            });
        }
        sweetAlert.close();   // ocultamos la ventana de pregunta
    }); 
}