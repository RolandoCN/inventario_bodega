
$("#form_funcionario").submit(function(e){
    e.preventDefault();
    
    //validamos los campos obligatorios
    let cedula=$('#cedula').val()
    let apellidos=$('#apellidos').val()
    let nombres=$('#nombres').val()
    let id_area=$('#id_area').val()
    let id_ambito=$('#id_ambito').val()
    let id_ambito_ley=$('#id_ambito_ley').val()
  
    if(cedula=="" || cedula==null){
        alertNotificar("Debe ingresar la cedula","error")
        $('#cedula').focus()
        return
    } 

    if(apellidos=="" || apellidos==null){
        alertNotificar("Debe ingresar los apellidos","error")
        $('#apellidos').focus()
        return
    } 

    if(nombres=="" || nombres==null){
        alertNotificar("Debe ingresar los nombres","error")
        $('#nombres').focus()
        return
    } 

    if(id_area=="" || id_area==null){
        alertNotificar("Debe seleccionar la área","error")
        return
    } 

    if(id_ambito=="" || id_ambito==null){
        alertNotificar("Debe seleccionar el ambito","error")
        return
    } 

    if(id_ambito_ley=="" || id_ambito_ley==null){
        alertNotificar("Debe seleccionar el ambito ley","error")
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
        url_form="guardar-funcionario"
    }else{
        tipo="PUT"
        url_form="actualizar-funcionario/"+idFuncionarioEditar
    }
  
    var FrmData=$("#form_funcionario").serialize();

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
            $('#listado_funcionario').show(200)
            llenar_tabla_funcionario()
                            
        }, error:function (data) {
            console.log(data)

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})

function limpiarCampos(){
    
    $('#cedula').val('')
    $('#apellidos').val('')
    $('#nombres').val('')
    $('#id_area').val('').trigger('change.select2')
    $('#id_ambito').val('').trigger('change.select2')
    $('#id_ambito_ley').val('').trigger('change.select2')
}

function llenar_tabla_funcionario(){
    var num_col = $("#tabla_funcionario thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_funcionario tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);
   
    
    $.get("listado-funcionario/", function(data){
      
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            $("#tabla_funcionario tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
            return;   
        }
        if(data.error==false){
            
            if(data.resultado.length <= 0){
                $("#tabla_funcionario tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
                alertNotificar("No se encontró datos","error");
                return;  
            }
         
            $('#tabla_funcionario').DataTable({
                "destroy":true,
                pageLength: 10,
                autoWidth : true,
                order: [[ 1, "asc" ]],
                sInfoFiltered:false,
                language: {
                    url: 'json/datatables/spanish.json',
                },
                columnDefs: [
                    { "width": "10%", "targets": 0 },
                    { "width": "30%", "targets": 1 },
                    { "width": "15%", "targets": 2 },
                    { "width": "15%", "targets": 3 },
                    { "width": "15%", "targets": 4 },
                    { "width": "15%", "targets": 5 },
                   
                ],
                data: data.resultado,
                columns:[
                        {data: "cedula"},
                        {data: "funcionario" },
                        {data: "area_de" },
                        {data: "ambito"},
                        {data: "ambitoley"},
                        {data: "cedula"},
                ],    
                "rowCallback": function( row, data, index ) {
                   
                    $('td', row).eq(5).html(`
                                  
                                            <button type="button" class="btn btn-primary btn-xs" onclick="editarFuncionario(${data.id_funcionario})">Editar</button>
                                                                                
                                            <a onclick="eliminarFuncionario(${data.id_funcionario})" class="btn btn-danger btn-xs"> Eliminar </a>
                                       
                                    
                    `); 
                }             
            });
        }
    }).fail(function(){
        $("#tabla_funcionario tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });


}

$('.collapse-link').click();
$('.datatable_wrapper').children('.row').css('overflow','inherit !important');

$('.table-responsive').css({'padding-top':'12px','padding-bottom':'12px', 'border':'0', 'overflow-x':'inherit'});



function editarFuncionario(id_funcionario){
    vistacargando("m","Espere por favor")
    $.get("editar-funcionario/"+id_funcionario, function(data){
        vistacargando("")
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            return;   
        }
        if(data.resultado==null){
            alertNotificar("La persona ya no se puede editar","error");
            return;   
        }

        $('#cedula').val(data.resultado.cedula)
        $('#apellidos').val(data.resultado.apellidos)
        $('#nombres').val(data.resultado.nombres)
        $('#id_area').val(data.resultado.id_area).trigger('change.select2')
        $('#id_ambito').val(data.resultado.id_ambito).trigger('change.select2')
        $('#id_ambito_ley').val(data.resultado.id_ambito_ley).trigger('change.select2')

        visualizarForm('E')
        globalThis.idFuncionarioEditar=id_funcionario

       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}

function visualizarForm(tipo){
    $('#form_ing').show(200)
    $('#listado_funcionario').hide(200)
    globalThis.AccionForm="";
    if(tipo=='N'){
        $('#titulo_form').html("Registro Funcionario")
        $('#nombre_btn_form').html('Registrar')
        AccionForm="R"
    }else{
        $('#titulo_form').html("Actualizar Funcionario")
        $('#nombre_btn_form').html('Actualizar')
        AccionForm="E"
    }
}

function visualizarListado(){
    $('#form_ing').hide(200)
    $('#listado_funcionario').show(200)
    limpiarCampos()
}

function eliminarFuncionario(id_funcionario){
    if(confirm('¿Quiere eliminar el registro?')){
        vistacargando("m","Espere por favor")
        $.get("eliminar-funcionario/"+id_funcionario, function(data){
            vistacargando("")
            if(data.error==true){
                alertNotificar(data.mensaje,"error");
                return;   
            }
    
            alertNotificar(data.mensaje,"success");
            llenar_tabla_funcionario()
           
        }).fail(function(){
            vistacargando("")
            alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
        });
    }
   
}