
$("#form_area").submit(function(e){
    e.preventDefault();
    
    //validamos los campos obligatorios
    let descripcion=$('#descripcion').val()
    let es_admin=$('#es_admin').val()
   
    if(descripcion=="" || descripcion==null){
        alertNotificar("Debe ingresar la descripcion","error")
        $('#descripcion').focus()
        return
    } 

    if(es_admin=="" || es_admin==null){
        alertNotificar("Debe seleccionar si es horario administrativo","error")
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
        url_form="guardar-area"
    }else{
        tipo="PUT"
        url_form="actualizar-area/"+idAreaEditar
    }
  
    var FrmData=$("#form_area").serialize();

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
            $('#listado_area').show(200)
            llenar_tabla_area()
                            
        }, error:function (data) {
            console.log(data)

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})

function limpiarCampos(){
    $('#descripcion').val('')
    $('#es_admin').val('').trigger('change.select2')
}

function llenar_tabla_area(){
    var num_col = $("#tabla_area thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_area tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);
   
    
    $.get("listado-area/", function(data){
      
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            $("#tabla_area tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
            return;   
        }
        if(data.error==false){
            
            if(data.resultado.length <= 0){
                $("#tabla_area tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
                alertNotificar("No se encontró datos","error");
                return;  
            }
         
            $('#tabla_area').DataTable({
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
                    { "width": "45%", "targets": 1 },
                    { "width": "25%", "targets": 2 },
                    { "width": "20%", "targets": 3 },
                   
                ],
                data: data.resultado,
                columns:[
                        {data: "id_area"},
                        {data: "descripcion" },
                        {data: "administrativo" },
                        {data: "id_area"},
                ],    
                "rowCallback": function( row, data, index ) {
                    $('td', row).eq(0).html(index+1)
                    if(data.administrativo=="S"){
                        $('td', row).eq(2).html('Si')
                    }else{
                        $('td', row).eq(2).html('No')
                    }
                    $('td', row).eq(3).html(`
                                  
                                            <button type="button" class="btn btn-primary btn-xs" onclick="editarArea(${data.id_area})">Editar</button>
                                                                                
                                            <a onclick="eliminarArea(${data.id_area})" class="btn btn-danger btn-xs"> Eliminar </a>
                                       
                                    
                    `); 
                }             
            });
        }
    }).fail(function(){
        $("#tabla_area tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });


}

$('.collapse-link').click();
$('.datatable_wrapper').children('.row').css('overflow','inherit !important');

$('.table-responsive').css({'padding-top':'12px','padding-bottom':'12px', 'border':'0', 'overflow-x':'inherit'});



function editarArea(id_area){
    vistacargando("m","Espere por favor")
    $.get("editar-area/"+id_area, function(data){
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
        $('#es_admin').val(data.resultado.administrativo).trigger('change.select2')

        visualizarForm('E')
        globalThis.idAreaEditar=id_area

       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}

function visualizarForm(tipo){
    $('#form_ing').show(200)
    $('#listado_area').hide(200)
    globalThis.AccionForm="";
    if(tipo=='N'){
        $('#titulo_form').html("Registro Área")
        $('#nombre_btn_form').html('Registrar')
        AccionForm="R"
    }else{
        $('#titulo_form').html("Actualizar Área")
        $('#nombre_btn_form').html('Actualizar')
        AccionForm="E"
    }
}

function visualizarListado(){
    $('#form_ing').hide(200)
    $('#listado_area').show(200)
    limpiarCampos()
}

function eliminarArea(id_area){
    if(confirm('¿Quiere eliminar el registro?')){
        vistacargando("m","Espere por favor")
        $.get("eliminar-area/"+id_area, function(data){
            vistacargando("")
            if(data.error==true){
                alertNotificar(data.mensaje,"error");
                return;   
            }
    
            alertNotificar(data.mensaje,"success");
            llenar_tabla_area()
           
        }).fail(function(){
            vistacargando("")
            alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
        });
    }
   
}