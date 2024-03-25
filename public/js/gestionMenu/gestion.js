

$("#form_registro_gestion").submit(function(e){
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
        url_form="guardar-gestion"
    }else{
        tipo="PUT"
        url_form="actualizar-gestion/"+idGestionEditar
    }
  
    var FrmData=$("#form_registro_gestion").serialize();

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
            $('#listado_gestion').show(200)
            llenar_tabla_gestion()
                            
        }, error:function (data) {
            console.log(data)

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})

function limpiarCampos(){
    $('#descripcion').val('')
    $('#icono').val('')
}

function llenar_tabla_gestion(){
    var num_col = $("#tabla_gestion thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_gestion tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);
   
    
    $.get("listado-gestion/", function(data){
      
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            $("#tabla_gestion tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
            return;   
        }
        if(data.error==false){
            
            if(data.resultado.length <= 0){
                $("#tabla_gestion tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
                alertNotificar("No se encontró datos","error");
                return;  
            }
         
            $('#tabla_gestion').DataTable({
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
                    { "width": "30%", "targets": 1 },
                    { "width": "30%", "targets": 2 },
                    { "width": "20%", "targets": 3 },
                   
                ],
                data: data.resultado,
                columns:[
                        {data: "id_gestion"},
                        {data: "descripcion" },
                        {data: "icono"},
                        {data: "id_gestion"},
                ],    
                "rowCallback": function( row, data, index ) {
                    $('td', row).eq(0).html(index+1)
                    $('td', row).eq(3).html(`
                                  
                                            <button type="button" class="btn btn-primary btn-xs" onclick="editarGestion(${data.id_gestion })">Editar</button>
                                                                                
                                            <a onclick="eliminarGestion(${data.id_gestion })" class="btn btn-danger btn-xs"> Eliminar </a>
                                       
                                    
                    `); 
                }             
            });
        }
    }).fail(function(){
        $("#tabla_gestion tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });


}

$('.collapse-link').click();
$('.datatable_wrapper').children('.row').css('overflow','inherit !important');

$('.table-responsive').css({'padding-top':'12px','padding-bottom':'12px', 'border':'0', 'overflow-x':'inherit'});



function editarGestion(id_gestion){
    vistacargando("m","Espere por favor")
    $.get("editar-gestion/"+id_gestion, function(data){
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
        $('#icono').val(data.resultado.icono)
      
        visualizarForm('E')
        globalThis.idGestionEditar=id_gestion

       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}

function visualizarForm(tipo){
    $('#form_ing').show(200)
    $('#listado_gestion').hide(200)
    globalThis.AccionForm="";
    if(tipo=='N'){
        $('#titulo_form').html("Registro Gestión")
        $('#nombre_btn_form').html('Registrar')
        AccionForm="R"
    }else{
        $('#titulo_form').html("Actualizar Gestión")
        $('#nombre_btn_form').html('Actualizar')
        AccionForm="E"
    }
}

function visualizarListado(){
    $('#form_ing').hide(200)
    $('#listado_gestion').show(200)
    limpiarCampos()
}

function eliminarGestion(id_gestion){
    if(confirm('¿Quiere eliminar el registro?')){
        vistacargando("m","Espere por favor")
        $.get("eliminar-gestion/"+id_gestion, function(data){
            vistacargando("")
            if(data.error==true){
                alertNotificar(data.mensaje,"error");
                return;   
            }
    
            alertNotificar(data.mensaje,"success");
            llenar_tabla_gestion()
           
        }).fail(function(){
            vistacargando("")
            alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
        });
    }
   
}