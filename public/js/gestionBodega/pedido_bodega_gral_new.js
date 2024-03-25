globalThis.BodegaSeleccionada=""
function bodegaSeleccionada(){
    var bodega=$('#cmb_bodega').val()
    if(bodega==null|| bodega==""){
        return
    }else{
        var hay_item_agg=$("#tb_listaMedicamento tr").length
        // alert(hay_item_agg)
        if(hay_item_agg>0){
            
            if(bodega != BodegaSeleccionada && BodegaSeleccionada!=""){
                $('#cmb_bodega').val(BodegaSeleccionada).trigger('change.select2')
                alertNotificar("Solo se puede realizar pedidos de una bodega a la vez", "error")
                return
            }
        }
        BodegaSeleccionada=bodega
      
    }
}

$('#cmb_item').select2({

    placeholder: 'Seleccione una opción',
    ajax: {
    url: "buscar-item",
    dataType: 'json',
    delay: 250,
    data: function (params) {
        return {
            q: params.term, // Search term
            param1:BodegaSeleccionada,
        };
    },
    processResults: function (data) {
        return {
        results:  $.map(data, function (item) {
                return {
                    text: item.detalle+" - ["+item.stock+"]",
                    id: item.idprod
                }
            })
        };
    },
    cache: true
    }
})

globalThis.IdtemIdActual=0
function validaAgregaItem(){
    var hay_item_agg=$("#tb_listaMedicamento tr").length
    if(hay_item_agg>0 && IdtemIdActual>0){
        var valor_cantidad=$('#class_cantidad_'+IdtemIdActual).val();   
       
        if(valor_cantidad<=0  && valor_cantidad!=""){
            alertNotificar("La cantidad debe ser mayor que cero","error");
            $('#class_cantidad_'+IdtemIdActual).focus();
            $('#class_cantidad_'+IdtemIdActual).val('')
            // $('#cmb_item').val('').change()
            return;
        }
        if(valor_cantidad==""){
            alertNotificar("La cantidad debe ser mayor que cero","error");
            
            $('#class_cantidad_'+IdtemIdActual).focus();
            $('#class_cantidad_'+IdtemIdActual).val('')
            return;
        }
    }

    var id_item=$('#cmb_item').val()
    if(id_item==null || id_item==""){
        return
    }
    IdtemIdActual=id_item
    $.get("valida-agregar-item/"+id_item+"/"+BodegaSeleccionada, function(data){
        console.log(data)
        
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
           
            return;   
        }
        if(data.error==false){
            $('#cmb_item').val('').change()
            var nueva_fila=id_item;
            var nfilas=$("#tb_listaMedicamento tr").length;
            if(nfilas>0){
                var dato=$('#idmedicina_selecc'+id_item).val();
               
                if(nueva_fila==dato){
                    alertNotificar("El item ya está agregado a la lista","error");
                    return;
                }

            }

            var nombre=data.resultado.detalle;
            var cantidad=data.resultado.stock

            $('#tb_listaMedicamento').append(`<tr id="medicamentos_${id_item}">

                <td width="5%" class="centrado"> 
                    <button type="button" style="margin-right:1px !important" data-toggle="tooltip" data-original-title="Eliminar" class="btn btn-xs btn-danger marginB0" onClick="eliminar_item(${id_item})">
                        <i class="fa fa-trash" >
                            
                        </i> 
                    </button>
        
        
                </td>   
            
                <td width="27%" class="centrado">
                    <input type="hidden" name="idmedicina_selecc[]" id="idmedicina_selecc${id_item}" value="${id_item}">
                    <input type="hidden" name="idbodega_selecc[]" id="idbodega_selecc${id_item}" value="${BodegaSeleccionada}">
                    <input type="hidden" name="nombrematerial[]" id="nombre_medicina_${id_item}" value='${nombre}'>
                    ${nombre}
                </td> 
                <td width="8%" class="centrado">
                    <input type="number"id="class_cantidad_${id_item}" style="width:100% !important;text-align:right" name="cantidad[]" onkeyup="tecla_cantidad(this,'${id_item}')"  onblur="validar_cantidad(this,'${id_item}')" placeholder="${cantidad}">
        
                    <input type="hidden"id="class_cantidadmax_${id_item}" style="width:100% !important;text-align:right" name="cantidadmax[]" value="${cantidad}">
        
                </td> 
        
               
                
            </tr>`);
            
        }
        
       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });

}

function tecla_cantidad(e, id){
    $('#cmb_item').val('').change()
    var valor_cantidad=$('#class_cantidad_'+id).val(); 
    calcularTotalFila(id)  
    if(valor_cantidad<=0){
        alertNotificar("La cantidad debe ser mayor que cero","error");
        $('#class_cantidad_'+id).focus();
        $('#class_cantidad_'+id).val('')
        return;
    }
   
}

function validar_cantidad(e, id){
    var valor_cantidad=$('#class_cantidad_'+id).val();   
    if(valor_cantidad<=0  && valor_cantidad!=""){
        alertNotificar("La cantidad debe ser mayor que cero","error");
        $('#class_cantidad_'+id).focus();
        $('#class_cantidad_'+id).val('')
        return;
    }
    calcularTotalFila(id)
}
function calcularTotalFila(id){
    var valor_cantidad=$('#class_cantidad_'+id).val();   
   
    CantidadItem=$("#class_cantidadmax_"+id).val()

    if(parseFloat(CantidadItem)< parseFloat(valor_cantidad)){
        alertNotificar("La cantidad a egresar no debe ser mayor a la cantidad en existencia "+CantidadItem,"error")
        $('#class_cantidad_'+id).val('')
        $('#class_total_'+id).val("")
        $('#total_span_id_'+id).html("")
        return
    }
}

function eliminar_item(id){
    $('#medicamentos_'+id).remove();
    var nfilas=$("#tb_listaMedicamento tr").length;
    $('#cmb_item').val('').change()
    // if(nfilas==0){
    //     TipoBod=0
    // }   
   
}

function guardarPedidoBodega(){   
    
    var cmb_bodega=$("#cmb_bodega").val()  
    var motivo=$("#motivo").val()
    var nfilas=$("#tb_listaMedicamento tr").length;

    if(motivo==""){
        alertNotificar("Debe ingresar un motivo ","error");
        return;
    }
   
    if(cmb_bodega==""){
        alertNotificar("Seleccione la bodega ","error");
        return;
    }

    if(nfilas<=0){
        alertNotificar("Debe agregar al menos un item ","error");
        return;
    }

    var id=IdtemIdActual;
    var valor_cantidad=$('#class_cantidad_'+id).val();  


    if(valor_cantidad<=0 || valor_cantidad==""){
        alertNotificar("La cantidad debe ser mayor que cero","error");
        $('#class_cantidad_'+id).focus()
        $('#class_cantidad_'+id).val('')
        return;
    }
    
      
    globalThis.AccionForm="R"
    globalThis.bod=cmb_bodega
   
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
            // alert("ok")
        }
        sweetAlert.close();   // ocultamos la ventana de pregunta
    }); 

}

$("#form_pedido_bodega").submit(function(e){
    e.preventDefault();
    vistacargando("m", "Espere por favor");  

    tipo="POST"
    if(bod==8 || bod==13 || bod==14 || bod==19 || bod==23 || bod==24 || bod==18 || bod==17 || bod==2  || bod==1){
        url_form="guardar-pedido-bodega-farmacia-new"
    }else if(bod==22 || bod==25 || bod==26 || bod==27 || bod==28 || bod==29){
        url_form="guardar-pedido-bodega-farm-laborat-new"    
    }else if(bod==21 || bod==7 || bod==6 ){
        url_form="guardar-pedido-bodega-farm-insumo-new"
    } else if(bod==0){
       alertNotificar("Bodega no permitida")
       return
    }    
    else{        
        url_form="guardar-pedido-bodega-area-new"
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
                alertNotificar(data.mensaje,'error');
                return;                      
            }
            cancelarEgreso()
            alertNotificar(data.mensaje,"success");
            
        }, error:function (data) {
            console.log(data)

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})

function cancelarEgreso(){
    IdMedicActual=0;
    $("#motivo").val('')
    $("#tipo_ingreso_cmb").val('').change();
    $("#cmb_bodega").val('').change();
    $("#cmb_tipo_med").val('').change();
    $("#tb_listaMedicamento ").html('');
    $('#tb_pie_TotalMedicamentos').html('');
    BodegaSeleccionada=""
    TipoBod=0
}