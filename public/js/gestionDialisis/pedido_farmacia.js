globalThis.UrlSistema=$('#url').val()
function carga_cie10(){
    $('#cie_10').select2({
        placeholder: 'Seleccione una opción',
        ajax: {
        url: UrlSistema+'/cargar-cie10',
        dataType: 'json',
        delay: 250,
        processResults: function (data) {
            return {
            results:  $.map(data, function (item) {
                    return {
                        text: item.cie10_codigo +" -- "+item.cie10_descripcion,
                        id: item.cie10_id
                    }
                })
            };
        },
        cache: true
        }
    });
}

//funcion para cuando selecciona un paquete del combo
function agg_paquete(){
    let paquete_cmb=$('#cmb_paquete').val()
    if(paquete_cmb==""){return}
    var paquete_txt=$('#cmb_paquete option:selected').text()

    let id_paquete=paquete_cmb
    var nueva_fila=id_paquete;
    var nfilas=$("#tb_listaMedicamento tr").length;
    if(nfilas>0){
        var dato=$('#idpaquete_selecc'+id_paquete).val();
       
        if(nueva_fila==dato){
            alertNotificar("El paquete ya está agregado a la lista","error");
            return;
        }

        alertNotificar("Solo se puede agregar un paquete por paciente","error");
        $('#cmb_paquete').val('').change()
        return;
    }


    $('#btn_cancelar').show();

    $('#btn_registrar').prop('disabled',false);  
    $('#tb_listaMedicamento').append(`<tr id="fila_paq_${id_paquete}">

        <td width="5%" class="centrado"> 
            <center><button type="button"  data-toggle="tooltip" data-original-title="Eliminar" class="btn btn-xs btn-danger marginB0" onClick="eliminar_paquete(${id_paquete})">
                <i class="fa fa-trash" >
                    
                </i> 
            </button></center>


        </td>   
     
        <td width="27%" class="centrado">
            <input type="hidden" name="idpaquete_selecc[]" id="idpaquete_selecc${id_paquete}" value="${id_paquete}">
            <input type="hidden" name="nombrepaquete[]" id="nombre_paquete_${id_paquete}" value='${paquete_txt}'>
            ${paquete_txt}
        </td> 
        <td width="8%" class="centrado">
            <input type="hidden"id="class_cantidad_${id_paquete}" style="width:100% !important;text-align:right" name="cantidad[]" onkeyup="tecla_cantidad(this,'${id_paquete}')"  onblur="validar_cantidad(this,'${id_paquete}')"  onclick="validar_cantidad(this,'${id_paquete}')" value="1">

            <input type="number"id="class_cantidad1_${id_paquete}" style="width:100% !important;text-align:right" name="cantidad[]" onkeyup="tecla_cantidad1(this,'${id_paquete}')"  onblur="validar_cantidad1(this,'${id_paquete}')"  onclick="validar_cantidad1(this,'${id_paquete}')" value="1" disabled>

        </td> 

        <td width="8%" class="centrado">
            <center><button type="button"  data-toggle="tooltip" data-original-title="Detalle" class="btn btn-xs btn-success marginB0" onClick="detalle_paquete('${id_paquete}','${paquete_txt}','S')">
                <i class="fa fa-eye" >
                    
                </i> 
            </button></center>
        </td>  

     

         
    </tr>`);

   
    $('[data-toggle="tooltip"]').tooltip();

    detalle_paquete(paquete_cmb,paquete_txt, 'N')
   
    
}

function eliminar_paquete(id){
    $('#fila_paq_'+id).remove();
    $('#fila_'+id).remove();
    var nfilas=$("#tb_listaMedicamento tr").length;

    if(nfilas==0){
        // TipoBod=0
    }
    
    
   
}

function detalle_paquete(id,descripcion,tipo){
  
    var cantidad_paq_ingr=$('#class_cantidad_'+id).val()
   
    var num_col = $("#tabla_detalle_paq thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_detalle_paq tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);
    
    globalThis.paqueteErrorGlobal="";
    globalThis.contadorPaqueteError=0;
    $('.guardar_btn').prop('disabled', true)

    //eliminamos la inconsistencia para q vuelva a verificar
    $('#fila_'+id).remove();

    vistacargando("m","Validando...")
    $.get(UrlSistema+"/valida-paquete/"+id+"/"+cantidad_paq_ingr, function(data){
        
        if(tipo=="S"){
            $('#modal_Paquete').modal({backdrop: 'static', keyboard: false})

        }
           
        if(data.error==true){
            vistacargando("")
            alertNotificar(data.mensaje,"error");
            $("#tabla_detalle_paq tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
            return;   
        }
        if(data.error==false){
            
            if(data.resultado.length <= 0){
                $("#tabla_detalle_paq tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
                
            }
                     
            $('#tabla_detalle_paq').DataTable({
                "destroy":true,
                pageLength: 10,
                autoWidth : true,
                order: [[ 1, "desc" ]],
                sInfoFiltered:false,
                language: {
                    url: UrlSistema+'/json/datatables/spanish.json',
                },
                columnDefs: [
                    { "width": "10%", "targets": 0 },
                    { "width": "60%", "targets": 1 },
                    { "width": "10%", "targets": 2,className: "text-center"  },
                    { "width": "10%", "targets": 3,className: "text-center"  },
                   
                   
                ],
                data: data.resultado,
                columns:[
                        {data: "iddetalle_paq"},
                        {data: "iddetalle_paq" },
                        {data: "cantidad" },
                        {data: "cantidad" },    
                      
                ],    
                "rowCallback": function( row, data, index ) {
                    $('td', row).eq(0).html(index+1)
                                       
                    if(data.id_item >=30000){
                        $('td', row).eq(1).html(data.descripcion_ins)
                       
                        $('td', row).eq(3).html(data.stock_farm_ins)
                    }else{
                        $('td', row).eq(1).html(data.descripcion_med)
                       
                        $('td', row).eq(3).html(data.stock_farm_med)
                    }
                    if(data.info=="N"){
                        $('td', row).eq(0).addClass('stock_no_cumple')
                        $('td', row).eq(1).addClass('stock_no_cumple')
                        $('td', row).eq(2).addClass('stock_no_cumple')
                        $('td', row).eq(3).addClass('stock_no_cumple')

                                           
                        if(data.id_item >=30000){
                            PaqueteInconsis=data.descripcion_ins
                        }else{
                            PaqueteInconsis=data.descripcion_med
                        }
                       
                        contadorPaqueteError=contadorPaqueteError+1;
                       
                    }
                  
                    $('td', row).eq(2).html(cantidad_paq_ingr * data.cantidad)
                }             
            });
           
            
            $('#paq_selecc').html(descripcion)
            $('#paq_selecc').addClass('mayusc')
            $('#idpaq').val(id)

            
            setTimeout(() => {
                aggError(id, descripcion)
                $('.guardar_btn').prop('disabled', false)
                vistacargando("")
            }, 2000);
            
            // $('#cmb_paquete').val('').change()
        }

     

       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
   
}

function aggError(idPaqSel, descripcion){
       
    // let descripcion= $('#paq_selecc').html()  
    // let idPaqSel=$('#idpaq').val() 
    if(contadorPaqueteError>0){
        
        $('#paquete_error').append(`
            
                <tr id="fila_${idPaqSel}">
                    <td>
                        <input type="hidden" name="idpaquete_inco[]" value="${idPaqSel}" id="id_paq-${idPaqSel}">
                        <input type="hidden" name="nombrepaquete_inco[]" value="${descripcion}" id="nomb_paq-${idPaqSel}">
                    </td>
                </tr>
        `)
    }
}

function tecla_cantidad(e, id){
    var valor_cantidad=$('#class_cantidad_'+id).val(); 
  
    if(valor_cantidad<=0){
        alertNotificar("La cantidad debe ser mayor o igual a 1","error");
        $('#class_cantidad_'+id).focus();
        $('#class_cantidad_'+id).val(1)
        return;
    }
   
}


function validar_cantidad(e, id){
    var valor_cantidad=$('#class_cantidad_'+id).val();   
    if(valor_cantidad<=0  && valor_cantidad!=""){
        alertNotificar("La cantidad debe ser mayor o igual a 1","error");
        $('#class_cantidad_'+id).focus();
        $('#class_cantidad_'+id).val(1)
        return;
    }
  
}

function guardarEgresoBodega(){

    btnGuardar="S"
    var cie10=$("#cie_10").val()
    var responsable=$("#id_responsable").val()
    var id_paciente=$("#id_paciente").val()
    var motivo=$("#motivo").val()
    var nfilas=$("#tb_listaMedicamento tr").length;
    var password=$('#password').val()
    var fecha_uso=$('#fecha_uso').val()
   
    if(password==""){
        alertNotificar("Ingrese la contraseña ","error");
        $("#password").focus()
        return;
    }
    if(cie10==""){
        alertNotificar("Debe ingresar un cie10 ","error");
        $("#cie_10").focus()
        return;
    }

    if(fecha_uso==""){
        alertNotificar("Debe ingresar la fecha uso del paquete ","error");
        $("#fecha_uso").focus()
        return;
    }

    if(id_paciente==""){
        alertNotificar("No existe el paciente ","error");
        return;
    }

    if(responsable==""){
        alertNotificar("No existe el responsable ","error");
        return;
    }

   

    if(motivo==""){
        alertNotificar("Debe ingresar un motivo ","error");
        $("#motivo").focus()
        return;
    }
   

    if(nfilas<=0){
        alertNotificar("Debe agregar al menos un paquete ","error");
        return;
    }
    var comprobar=0
    var val_id_input = []
    var paquete_incomp=[];
    $("input[name='nombrepaquete_inco[]']").each(function(indice, elemento) {
        if($(elemento).val()!=""){
            comprobar=comprobar+1
            paquete_incomp.push($(elemento).val());
            val_id_input.push($(this).attr('id'))

        }
    });
        
    var array_iddetalle=[]
    $.each(val_id_input,function(i, item){
        var valor=item.split('-')
        array_iddetalle.push(valor[1]);

    })

    // if(array_iddetalle.length>0){
    //     alertNotificar("El "+paquete_incomp[0]+" no cumple con la existencia en stock en farmacia ","error")
    //     detalle_paquete(array_iddetalle[0],paquete_incomp[0],'S')
    //     return
    // }
    
   
    globalThis.AccionForm="R"
   
    swal({
        title: "¿Desea realizar la solicitud?",
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
            $("#form_pedido_bodega").submit()
        }
        sweetAlert.close();   // ocultamos la ventana de pregunta
    }); 

}
 
$("#form_pedido_bodega").submit(function(e){
    e.preventDefault();
    vistacargando("m", "Espere por favor");  
    //comprobamos si es registro o edicion
    let tipo=""
    let url_form=""
    if(AccionForm=="R"){
        tipo="POST"
        url_form=UrlSistema+"/guardar-pedido-dial"
    }else{
        tipo="PUT"
        url_form="actualizar-pedido/"+idMenuEditar
    }
  
    var FrmData=$("#form_pedido_bodega").serialize();

    $.ajax({
            
        type: tipo,
        url: url_form,
        method: tipo,             
		data: FrmData,      
		
        processData:false, 

        success: function(data){
            vistacargando("");                
            if(data.error==true){
                if(data.idpaquete){
                    detalle_paquete(data.idpaquete,data.paquete,'S')
                }
                alertNotificar(data.mensaje,'error');
                return;                      
            }
         
            alertNotificar(data.mensaje,"success");
            swal(data.mensaje, "¡Operacion exitosa!", "success");

            cancelarEgreso(2)
            
        }, error:function (data) {
            console.log(data)

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})

function cancelarEgreso(tipo){
    IdMedicActual=0;
    $("#cie_10").val('').change()
    $("#cmb_paquete").val('').change()
    $("#id_responsable").val('')
    $("#cedula_responsable").val('')
    $("#nombre_responsable").val('')
    $('#password').val('')
    
    $("#id_paciente").val('')
    $("#nombre_paciente").val('')

    $("#motivo").val('')
    $("#tipo_ingreso_cmb").val('').change();
    $("#cmb_bodega").val('').change();
    $("#cmb_tipo_med").val('').change();
    $("#tb_listaMedicamento tr").html('');
    $('#tb_pie_TotalMedicamentos').html('');
    TipoBod=0
    if(tipo==1){
        window.close();
    }else{
        setTimeout(() => {
            window.close();     
        }, 3000);
    }
}