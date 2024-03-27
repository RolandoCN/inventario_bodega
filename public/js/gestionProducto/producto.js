
$("#form_registro_producto").submit(function(e){
    e.preventDefault();
    
    //validamos los campos obligatorios
    let codigo=$('#codigo').val()
    let descripcion=$('#descripcion').val()
    var cmb_marca=$('#cmb_marca').val()
    let cmb_modelo=$('#cmb_modelo').val()
    let precio=$('#precio').val()
    let cmb_iva=$('#cmb_iva').val()

    if(codigo==""){
        alertNotificar("Debe ingresar el codigo del producto","error")
        $('#codigo').focus()
        return
    }
            
    if(descripcion=="" || descripcion==null){
        alertNotificar("Ingrese la descripcion","error")
        $('#descripcion').focus()
        return
    } 

    if(cmb_marca=="" || cmb_marca==null){
        alertNotificar("Seleccione la marca","error")
        return
    } 

    if(cmb_modelo=="" || cmb_modelo==null){
        alertNotificar("Seleccione el modelo","error")
        return
    } 

    if(precio==""){
        alertNotificar("Debe ingresar el precio","error")
        $('#precio').focus()
        return
    }

    if(precio<=0){
        alertNotificar("El precio debe ser mayor a cero","error")
        $('#precio').focus()
        return
    }

    if(cmb_iva==""){
        alertNotificar("Debe seleccionar si grava IVA o no","error")
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
        url_form="guardar-producto"
    }else{
        tipo="PUT"
        url_form="actualizar-producto/"+idProductoEditar
    }
    
    var FrmData=$("#form_registro_producto").serialize();
   
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
            $('#listado_producto').show(200)
            llenar_tabla_producto()
                            
        }, error:function (data) {
            console.log(data)

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})

function limpiarCampos(){

    $('#codigo').val('')
    $('#descripcion').val('')
    $('#precio').val('')
   
    $('#cmb_marca').val('').change()
    $('#cmb_modelo').val('').change()
    $('#cmb_iva').val('').change()
}

function llenar_tabla_producto(){
    var num_col = $("#tabla_producto thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_producto tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);
   
    
    $.get("listado-producto/", function(data){
       
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            $("#tabla_producto tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
            return;   
        }
        if(data.error==false){
            
            if(data.resultado.length <= 0){
                $("#tabla_producto tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
                alertNotificar("No se encontró datos","error");
                return;  
            }
         
            $('#tabla_producto').DataTable({
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
                    { "width": "25%", "targets": 1 },
                    { "width": "25%", "targets": 2 },
                    { "width": "10%", "targets": 3 },
                    { "width": "10%", "targets": 4 },
                    { "width": "10%", "targets": 5 },
                    { "width": "10%", "targets": 6 },
                   
                ],
                data: data.resultado,
                columns:[
                        {data: "codigo"},
                        {data: "descripcion" },
                        {data: "precio"},
                        {data: "precio"},
                        {data: "iva"},
                        {data: "valor_venta"},
                        {data: "valor_venta"},
                ],    
                "rowCallback": function( row, data ) {

                    $('td', row).eq(1).html(`<li>${data.descripcion}</li>
                        <li>${data.detalle}</li>
                    `)

                    $('td', row).eq(2).html(`${data.marca.descripcion} - ${data.modelo.descripcion}`)

                    $('td', row).eq(6).html(`
                                  
                                            <button type="button" class="btn btn-primary btn-xs" onclick="editarPersona(${data.idpersona })">Editar</button>
                                                                                
                                            <a onclick="btn_eliminar_tarea(${data.idpersona })" class="btn btn-danger btn-xs"> Eliminar </a>
                                       
                                    
                    `); 
                }             
            });
        }
    }).fail(function(){
        $("#tabla_producto tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });


}

$('.collapse-link').click();
$('.datatable_wrapper').children('.row').css('overflow','inherit !important');

$('.table-responsive').css({'padding-top':'12px','padding-bottom':'12px', 'border':'0', 'overflow-x':'inherit'});



function editarPersona(idpersona){
    vistacargando("m","Espere por favor")
    $.get("editar-persona/"+idpersona, function(data){
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


        $('#cedula_persona').val(data.resultado.cedula)
        $('#nombres').val(data.resultado.nombres)
        $('#apellidos').val(data.resultado.apellidos)
        $('#telefono').val(data.resultado.telefono)
        $('#email').val(data.resultado.email)
        $('#tipo_id').val(data.resultado.tipo_doc).trigger('change.select2')
        if(data.resultado.tipo_doc==1){
            $('#cedula_persona').val(data.resultado.numero_doc)
        }else{
            $('#ruc_persona').val(data.resultado.numero_doc)
        }
        
        visualizarForm('E')
        globalThis.idProductoEditar=idpersona



       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}

function visualizarForm(tipo){
    $('#form_ing').show(200)
    $('#listado_producto').hide(200)
    globalThis.AccionForm="";
    if(tipo=='N'){
        $('#titulo_form').html("Registro Persona-Empresa")
        $('#nombre_btn_form').html('Registrar')
        AccionForm="R"
    }else{
        $('#titulo_form').html("Actualización Persona-Empresa")
        $('#nombre_btn_form').html('Actualizar')
        AccionForm="E"
    }
}

function visualizarListado(){
    $('#form_ing').hide(200)
    $('#listado_producto').show(200)
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
            llenar_tabla_producto()
           
        }).fail(function(){
            vistacargando("")
            alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
        });
    }
   
}