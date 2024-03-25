$("#form_gestion_menu").submit(function(e){
    e.preventDefault();
    
    //validamos los campos obligatorios
    let gestion=$('#gestion').val()
    let menu=$('#menu').val()
   
    if(gestion=="" || gestion==null){
        alertNotificar("Debe seleccionar la gestion","error")
        return
    } 

    if(menu=="" || menu==null){
        alertNotificar("Debe seleccionar el menu","error")
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
        url_form="guardar-gestion-menu"
    }else{
        tipo="PUT"
        url_form="actualizar-gestion-menu/"+idGestionMenuEditar
    }
  
    var FrmData=$("#form_gestion_menu").serialize();

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
            $('#listado_gestion_menu').show(200)
            llenar_tabla_gestion_menu()
                            
        }, error:function (data) {
            console.log(data)

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})

function limpiarCampos(){
    $('#gestion').val('').trigger('change.select2')
    $('#menu').val('').trigger('change.select2')
}

function llenar_tabla_gestion_menu(){
    var num_col = $("#tabla_gestion_menu thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_gestion_menu tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);
   
    
    $.get("listado-gestion-menu/", function(data){
      
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            $("#tabla_gestion_menu tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
            return;   
        }
        if(data.error==false){
            
            if(data.resultado.length <= 0){
                $("#tabla_gestion_menu tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
                alertNotificar("No se encontró datos","error");
                return;  
            }
         
            $('#tabla_gestion_menu').DataTable({
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
                        {data: "id_gestion_menu"},
                        {data: "gestion.descripcion" },
                        {data: "menu.descripcion"},
                        {data: "id_gestion_menu"},
                ],    
                "rowCallback": function( row, data, index ) {
                    $('td', row).eq(0).html(index+1)
                    $('td', row).eq(3).html(`
                                  
                                            <button type="button" class="btn btn-primary btn-xs" onclick="editarGestionMenu(${data.id_gestion_menu })">Editar</button>
                                                                                
                                            <a onclick="eliminarGestionMenu(${data.id_gestion_menu })" class="btn btn-danger btn-xs"> Eliminar </a>
                                       
                                    
                    `); 
                }             
            });
        }
    }).fail(function(){
        $("#tabla_gestion_menu tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });


}

$('.collapse-link').click();
$('.datatable_wrapper').children('.row').css('overflow','inherit !important');

$('.table-responsive').css({'padding-top':'12px','padding-bottom':'12px', 'border':'0', 'overflow-x':'inherit'});



function editarGestionMenu(id_gestion_menu){
    vistacargando("m","Espere por favor")
    $.get("editar-gestion-menu/"+id_gestion_menu, function(data){
        vistacargando("")
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            return;   
        }
        if(data.resultado==null){
            alertNotificar("La persona ya no se puede editar","error");
            return;   
        }


        $('#gestion').val(data.resultado.id_gestion).trigger('change.select2')
        $('#menu').val(data.resultado.id_menu).trigger('change.select2')
      
        visualizarForm('E')
        globalThis.idGestionMenuEditar=id_gestion_menu

       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}

function visualizarForm(tipo){
    $('#form_ing').show(200)
    $('#listado_gestion_menu').hide(200)
    globalThis.AccionForm="";
    if(tipo=='N'){
        $('#titulo_form').html("Registro Gestión Menú")
        $('#nombre_btn_form').html('Registrar')
        AccionForm="R"
    }else{
        $('#titulo_form').html("Actualizar Gestión Menú")
        $('#nombre_btn_form').html('Actualizar')
        AccionForm="E"
    }
}

function visualizarListado(){
    $('#form_ing').hide(200)
    $('#listado_gestion_menu').show(200)
    limpiarCampos()
}

function eliminarGestionMenu(id_gestion_menu){
    if(confirm('¿Quiere eliminar el registro?')){
        vistacargando("m","Espere por favor")
        $.get("eliminar-gestion-menu/"+id_gestion_menu, function(data){
            vistacargando("")
            if(data.error==true){
                alertNotificar(data.mensaje,"error");
                return;   
            }
    
            alertNotificar(data.mensaje,"success");
            llenar_tabla_gestion_menu()
           
        }).fail(function(){
            vistacargando("")
            alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
        });
    }
   
}