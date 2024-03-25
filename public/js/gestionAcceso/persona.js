

$("#form_registro_persona").submit(function(e){
    e.preventDefault();
    
    //validamos los campos obligatorios
    let cedula=$('#cedula_persona').val()
    let nombres=$('#nombres').val()
    let apellidos=$('#apellidos').val()
    let telefono=$('#telefono').val()
        
    if(cedula=="" || cedula==null){
        alertNotificar("Debe ingresar la cédula","error")
        $('#cedula_persona').focus()
        return
    } 

    if(nombres=="" || nombres==null){
        alertNotificar("Ingrese los nombres","error")
        $('#nombres').focus()
        return
    } 

    if(apellidos=="" || apellidos==null){
        alertNotificar("Ingrese los apellidos","error")
        $('#apellidos').focus()
        return
    } 

    if(telefono=="" || telefono==null){
        alertNotificar("Ingrese el telefono","error")
        $('#telefono').focus()
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
        url_form="guardar-persona"
    }else{
        tipo="PUT"
        url_form="actualizar-persona/"+idPersonaEditar
    }
    
    var FrmData=$("#form_registro_persona").serialize();
   
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
            $('#listado_persona').show(200)
            llenar_tabla_persona()
                            
        }, error:function (data) {
            console.log(data)

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})

function limpiarCampos(){
    $('#cedula_persona').val('')
    $('#nombres').val('')
    $('#apellidos').val('')
    $('#telefono').val('')
    $('#email').val('')
}

function llenar_tabla_persona(){
    var num_col = $("#tabla_persona thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_persona tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);
   
    
    $.get("listado-persona/", function(data){
       
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            $("#tabla_persona tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
            return;   
        }
        if(data.error==false){
            
            if(data.resultado.length <= 0){
                $("#tabla_persona tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
                alertNotificar("No se encontró datos","error");
                return;  
            }
         
            $('#tabla_persona').DataTable({
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
                    { "width": "10%", "targets": 2 },
                    { "width": "25%", "targets": 3 },
                    { "width": "15%", "targets": 4 },
                   
                ],
                data: data.resultado,
                columns:[
                        {data: "cedula"},
                        {data: "nombres" },
                        {data: "apellidos"},
                        {data: "telefono"},
                        {data: "telefono"},
                ],    
                "rowCallback": function( row, data ) {
                    $('td', row).eq(4).html(`
                                  
                                            <button type="button" class="btn btn-primary btn-xs" onclick="editarPersona(${data.idpersona })">Editar</button>
                                                                                
                                            <a onclick="btn_eliminar_tarea(${data.idpersona })" class="btn btn-danger btn-xs"> Eliminar </a>
                                       
                                    
                    `); 
                }             
            });
        }
    }).fail(function(){
        $("#tabla_persona tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });


}

$('.collapse-link').click();
$('.datatable_wrapper').children('.row').css('overflow','inherit !important');

$('.table-responsive').css({'padding-top':'12px','padding-bottom':'12px', 'border':'0', 'overflow-x':'inherit'});



function editarPersona(idpersona){
    vistacargando("m","Espere por favor")
    $.get("editar-persona/"+idpersona, function(data){
        vistacargando("")    
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            return;   
        }
        if(data.resultado==null){
            alertNotificar("La persona ya no se puede editar","error");
            return;   
        }


        $('#cedula_persona').val(data.resultado.cedula)
        $('#nombres').val(data.resultado.nombres)
        $('#apellidos').val(data.resultado.apellidos)
        $('#telefono').val(data.resultado.telefono)
        $('#email').val(data.resultado.email)

        visualizarForm('E')
        globalThis.idPersonaEditar=idpersona



       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}

function visualizarForm(tipo){
    $('#form_ing').show(200)
    $('#listado_persona').hide(200)
    globalThis.AccionForm="";
    if(tipo=='N'){
        $('#titulo_form').html("Registro Persona")
        $('#nombre_btn_form').html('Registrar')
        AccionForm="R"
    }else{
        $('#titulo_form').html("Actualización Persona")
        $('#nombre_btn_form').html('Actualizar')
        AccionForm="E"
    }
}

function visualizarListado(){
    $('#form_ing').hide(200)
    $('#listado_persona').show(200)
    limpiarCampos()
}

function btn_eliminar_tarea(idpersona){
    if(confirm('¿Quiere eliminar el registro?')){
        vistacargando("m","Espere por favor")
        $.get("eliminar-persona/"+idpersona, function(data){
            vistacargando("")          
            if(data.error==true){
                alertNotificar(data.mensaje,"error");
                return;   
            }
    
            alertNotificar(data.mensaje,"success");
            llenar_tabla_persona()
           
        }).fail(function(){
            vistacargando("")
            alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
        });
    }
   
}