globalThis.UrlSistema=$('#url').val()
globalThis.DosisMed=500000
function medicamentoSeleccionado(){
    var idmedicamento=$('#cmb_medicamento').val()
    if(idmedicamento==""){
        return
    }

    $('#dosis').val('')
    $('#frecuencia').val('')


    var id_paciente=$('#id_paciente').val()
    vistacargando("m","Espere por favor");
    $.get(UrlSistema+'/medicamento-receta-seleccionado/'+id_paciente+'/'+idmedicamento, function(data){
        console.log(data)
        vistacargando("")
        if(data.error==true){
			alertNotificar(data.mensaje,"error");
			return;   
		}
        if(data.resultado != null){
            $('#dosis').val(data.resultado.dosis)
            $('#frecuencia').val(data.resultado.frec)
            $('#iddetalle').val(data.resultado.iddetalle)
            DosisMed=data.resultado.dosis
        }
        
        
    }).fail(function(){
        alertNotificar("Ocurrio un error","error");
        vistacargando("")
       
    }); 
}

function eliminar_material(id){
    $('#medicamentos_'+id).remove();
   
}

function agregarItem(){
    var id_detalle= $('#iddetalle').val()
    var adminis=$('#administracion').val()
    var hora=$('#hora').val()
    var observacion=$('#observacion').val()
    var dosis= $('#dosis').val()
    var medicamento=$('#cmb_medicamento option:selected').text();
    var idmedicina=$('#cmb_medicamento').val()
    var frecuencia=$('#frecuencia').val()

    if(idmedicina==""){
        alertNotificar("Seleccione un medicamento","error")
        return
    }

    if(dosis==""){
        alertNotificar("Ingrese la dosis","error")
        return
    }

    // if(dosis > DosisMed){
    //     alertNotificar("La dosis no puede ser mayor a "+DosisMed)
    //     $('#dosis').val(DosisMed)
    //     return
    // }

    if(adminis==""){
        alertNotificar("Ingrese la administracion","error")
        return
    }

    if(hora==""){
        alertNotificar("Seleccione la hora","error")
        return
    }

    

    var nfilas=$("#tb_listaMedicamento tr").length;
    var idfila=nfilas+1;

    $('#tb_listaMedicamento').append(`<tr id="medicamentos_${idfila}">

        <td width="5%" class="centrado"> 
            <button type="button" style="margin-right:1px !important" data-toggle="tooltip" data-original-title="Eliminar" class="btn btn-xs btn-danger marginB0" onClick="eliminar_material(${idfila})">
                <i class="fa fa-trash" >
                    
                </i> 
            </button>


        </td>   
     
        <td width="27%" class="centrado">
            <input type="hidden" name="idmedicina_selecc[]" id="idmedicina_selecc${idfila}" value="${idmedicina}">
            <input type="hidden" name="id_detalle_selecc[]" id="id_detalle_selecc${idfila}" value="${id_detalle}">
            <input type="hidden" name="med_selecc[]" id="med_selecc${idfila}" value="${medicamento}">
            <input type="hidden" name="frec_selecc[]" id="frec_selecc${idfila}" value="${frecuencia}">
           
           ${medicamento}
        </td> 
        <td width="8%" class="centrado">
            <input type="hidden" name="dosis_selecc[]" id="dosis${idfila}" value="${dosis}">
            ${dosis}
        </td> 

        <td width="8%" class="centrado">
            <input type="hidden" name="adminis_selecc[]" id="adminis${idfila}" value="${adminis}">
            ${adminis}
        </td>  

     
        <td width="12%" class="centrado">
            <input type="hidden" name="hora_selecc[]" id="hora${idfila}" value="${hora}">
            ${hora}
        </td>  

        <td width="12%" class="centrado">
            <input type="hidden" name="observacion_selecc[]" id="observacion${idfila}" value="${observacion}">
            ${observacion}
        </td>  

   

         
    </tr>`);

    limpiarMed()
}

function limpiarMed(){
    $('#iddetalle').val('')
    $('#administracion').val('')
    $('#hora').val('')
    $('#observacion').val('')
    $('#dosis').val('')
    $('#frecuencia').val('')
    $('#cmb_medicamento').val('').change()
    DosisMed=50000
}

function guardarMedicinaAdministrada(){
    
    var responsable=$("#id_responsable").val()
    var id_paciente=$("#id_paciente").val()
    var password=$('#password').val()
    var nfilas=$("#tb_listaMedicamento tr").length;
    
  
    if(id_paciente==""){
        alertNotificar("No existe el paciente ","error");
        return;
    }

    if(responsable==""){
        alertNotificar("No existe el responsable ","error");
        return;
    }

    if(password==""){
        alertNotificar("Ingrese la contraseña ","error");
        $("#password").focus()
        return;
    }
   
    if(nfilas<=0){
        alertNotificar("Debe agregar al menos un medicamento ","error");
        return;
    }
 
      
    swal({
        title: "¿Desea ingresar la administracion del medicamento?",
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

    tipo="POST"
    url_form=UrlSistema+"/guardar-medicacion-administrada"
    
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
                alertNotificar(data.mensaje,'error');
                return;                      
            }
           
            alertNotificar(data.mensaje,"success");
            swal(data.mensaje, "¡Operacion exitosa!", "success");
            cancelarSolicitud(2)
            
        }, error:function (data) {
            console.log(data)

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})

function cancelarSolicitud(tipo){
    
    if(tipo==1){
        window.close();
    }else{
        setTimeout(() => {
            window.close();     
        }, 3000);
    }
}