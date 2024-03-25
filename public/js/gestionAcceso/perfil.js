

$("#form_registro_rol").submit(function(e){
    e.preventDefault();
    
    //validamos los campos obligatorios
    let descripcion=$('#descripcion').val()

    if(descripcion=="" || descripcion==null){
        alertNotificar("Debe ingresar la descripcion","error")
        $('#descripcion').focus()
        return
    } 
    vistacargando("m","Espere por favor")
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
        url_form="guardar-rol"
    }else{
        tipo="PUT"
        url_form="actualizar-rol/"+idRolEditar
    }
  
    var FrmData=$("#form_registro_rol").serialize();

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
            $('#listado_rol').show(200)
            llenar_tabla_rol()
                            
        }, error:function (data) {
            console.log(data)

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})

function limpiarCampos(){
    $('#descripcion').val('')
}

function llenar_tabla_rol(){
    var num_col = $("#tabla_rol thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_rol tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);
   
    
    $.get("listado-rol/", function(data){
      
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            $("#tabla_rol tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
            return;   
        }
        if(data.error==false){
            
            if(data.resultado.length <= 0){
                $("#tabla_rol tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
                alertNotificar("No se encontró datos","error");
                return;  
            }
         
            $('#tabla_rol').DataTable({
                "destroy":true,
                pageLength: 10,
                autoWidth : true,
                order: [[ 1, "desc" ]],
                sInfoFiltered:false,
                language: {
                    url: 'json/datatables/spanish.json',
                },
                columnDefs: [
                    { "width": "20%", "targets": 0 },
                    { "width": "60%", "targets": 1 },
                    { "width": "20%", "targets": 2 },
                   
                   
                ],
                data: data.resultado,
                columns:[
                        {data: "descripcion"},
                        {data: "descripcion" },
                        {data: "descripcion"},
                    
                ],    
                "rowCallback": function( row, data, index ) {
                    $('td', row).eq(0).html(index+1)
                    $('td', row).eq(2).html(`
                                  
                                            <button type="button" class="btn btn-primary btn-xs" onclick="editarRol(${data.id_perfil })">Editar</button>

                                            <button type="button" class="btn btn-success btn-xs" onclick="accesos(${data.id_perfil })">Accesos</button>
                                                                                
                                            <a onclick="eliminarRol(${data.id_perfil })" class="btn btn-danger btn-xs"> Eliminar </a>
                                       
                                    
                    `); 
                }             
            });
        }
    }).fail(function(){
        $("#tabla_rol tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });


}

$('.collapse-link').click();
$('.datatable_wrapper').children('.row').css('overflow','inherit !important');

$('.table-responsive').css({'padding-top':'12px','padding-bottom':'12px', 'border':'0', 'overflow-x':'inherit'});


function accesos(id_perfil, abiertaModal=null){
    
    
    $.get("acceso-perfil/"+id_perfil, function(data){
        
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
                    { "width": "20%", "targets": 3 },
                   
                ],
                data: data.resultado,
                columns:[
                        {data: "id_menu"},
                        {data: "descripcion" },
                        {data: "url" },
                        {data: "id_menu"},
                ],    
                "rowCallback": function( row, data, index ) {
                    $('td', row).eq(0).html(index+1)
                    let perm=""
                    if(data.accesoPerm=="S"){
                        perm="checked"
                    }else{
                        perm=""
                    }
                    $('td', row).eq(3).html(`
                                  
                                            
                                            <input type="checkbox" onclick="accionAcceso(${data.id_menu})"class="acces_check" id="check_${data.id_menu}" name="acces_check" value="${data.id_menu}"  ${perm}>
                                       
                                    
                    `); 
                }             
            });
            globalThis.PerfilSeleccionado=id_perfil
            if(abiertaModal!="S"){
                $('#modal_Acceso').modal('show')
            }
               
        }

     

       
    }).fail(function(){
       
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}

function accionAcceso(id){
   
    if( $('#check_'+id).is(':checked') ){
        // mandamos a guardar ese menu al perfil
        AggQuitarMenuPerfil(id,'A')
    } else {
        // mandamos a quitar
        AggQuitarMenuPerfil(id,'Q')
    }
}

function AggQuitarMenuPerfil(id_menu, tipo){
    vistacargando("m","Espere por favor")
    $.get("acceso-por-perfil/"+id_menu+"/"+tipo+"/"+PerfilSeleccionado, function(data){
        vistacargando("")
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            return;   
        }
       
        alertNotificar(data.mensaje,"success")
        accesos(PerfilSeleccionado,'S')

       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}


function editarRol(id_perfil){
    vistacargando("m","Espere por favor")
    $.get("editar-rol/"+id_perfil, function(data){
        vistacargando("")
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            return;   
        }
        if(data.resultado==null){
            alertNotificar("La persona ya no se puede editar","error");
            return;   
        }

        $('#descripcion').val(data.resultado.descripcion)

        visualizarForm('E')
        globalThis.idRolEditar=id_perfil

       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}

function visualizarForm(tipo){
    $('#form_ing').show(200)
    $('#listado_rol').hide(200)
    globalThis.AccionForm="";
    if(tipo=='N'){
        $('#titulo_form').html("Registro Perfil")
        $('#nombre_btn_form').html('Registrar')
        AccionForm="R"
    }else{
        $('#titulo_form').html("Actualizar Perfil")
        $('#nombre_btn_form').html('Actualizar')
        AccionForm="E"
    }
}

function visualizarListado(){
    $('#form_ing').hide(200)
    $('#listado_rol').show(200)
    limpiarCampos()
}

function eliminarRol(id_perfil){
    if(confirm('¿Quiere eliminar el registro?')){
        vistacargando("")
        $.get("eliminar-rol/"+id_perfil, function(data){
            vistacargando("")
            if(data.error==true){
                alertNotificar(data.mensaje,"error");
                return;   
            }
    
            alertNotificar(data.mensaje,"success");
            llenar_tabla_rol()
           
        }).fail(function(){
            vistacargando("")
            alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
        });
    }
   
}