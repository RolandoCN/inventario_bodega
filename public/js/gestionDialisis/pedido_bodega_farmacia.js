globalThis.IdMedicActual=0
globalThis.btnGuardar="N"
globalThis.TipoBod=0
function abrirModalMedicina(){
    var tipo=$('#cmb_bodega').val()
    var nfilas=$("#tb_listaMedicamento tr").length;
    if(TipoBod>0 && TipoBod!=tipo && nfilas>0){
        alertNotificar("No se puede seleccionar bodegas con diferentes tipo de items","error")
        return
    }    
   
    TipoBod=tipo;
    if(TipoBod==0){
        alertNotificar("Debe seleccionar una bodega","error")
        return
    }
    if(IdMedicActual>0){
        btnGuardar="N"
        validaMedAgg()
    }else{
        $("#modal_busqueda").modal("show")
    }
   
}

$('#modal_busqueda').on('hidden.bs.modal', function (event) {
    limpiarInfoItem()

})
globalThis.UrlSistema=$('#url').val()
$("#form_medicina").submit(function(e){
    e.preventDefault();

    var tipo=$('#cmb_bodega').val()
    var nfilas=$("#tb_listaMedicamento tr").length;
    if(TipoBod>0 && TipoBod!=tipo && nfilas>0){
        alertNotificar("No se puede seleccionar bodegas con diferentes tipo de items","error")
        return
    }    
   
    TipoBod=tipo;
    if(TipoBod==0){
        alertNotificar("Debe seleccionar una bodega","error")
        return
    }

    var txt_item=$('#item_txt').val()
    var bodega_selecc=$('#cmb_bodega').val()
    
    if(txt_item===""){
        alertNotificar("Ingrese el nombre","error")
        return
    }
    //si es medicamento ña bodega buscamos en la tabla medicamentos
    var url_busqueda=""
    if(bodega_selecc==1 || bodega_selecc==17){ //medicamentos 
        url_busqueda="listado-medicamentos-lote/"+txt_item+"/"+bodega_selecc; 
    }else if(bodega_selecc==2 || bodega_selecc==18 || bodega_selecc==21 || bodega_selecc==7){ //insumos 
            url_busqueda=UrlSistema+"/listado-insumos-lote-far/"+txt_item+"/"+bodega_selecc; 
    
    }else if(bodega_selecc==8 || bodega_selecc==13 || bodega_selecc==14 || bodega_selecc==19 || bodega_selecc==23 || bodega_selecc==24 || bodega_selecc==22 || bodega_selecc==25 || bodega_selecc==26 || bodega_selecc==27 || bodega_selecc==28 || bodega_selecc==29){
        //laboratorio  
        url_busqueda="listado-lab-filtra/"+txt_item+"/"+bodega_selecc; 
     
    }else if(bodega_selecc==30){
        // proteccion 
        url_busqueda="listado-proteccion-stock/"+txt_item+"/"+bodega_selecc;  
    }else {
        // item 
        url_busqueda="listado-items-stock/"+txt_item+"/"+bodega_selecc;  
    }

    var num_col = $("#tabla_medicina thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_medicina tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);
   
    
    $.get(url_busqueda, function(data){
        console.log(data)
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            $("#tabla_medicina tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
            return;   
        }
        if(data.error==false){
            
            if(data.resultado.length <= 0){
                $("#tabla_medicina tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
                alertNotificar("No se encontró datos","error");
                return;  
            }

            datoItemArray=[]
            $.each(data.resultado,function(i, item){
                datoItemArray.push({'idprod_':item.idprod,'nombres_':item.detalle,'fcaduca_':item.fcaduca,'idbodprod_':item.idbodprod});
                globalThis.datosItem=datoItemArray;
            })
                     
            $('#tabla_medicina').DataTable({
                "destroy":true,
                pageLength: 10,
                autoWidth : true,
                order: [[ 2, "asc" ]],
                ordering:true,
                sInfoFiltered:false,
                language: {
                    url: 'json/datatables/spanish.json',
                },
                columnDefs: [
                    { "width": "10%", "targets": 0 },
                    { "width": "55%", "targets": 1 },
                    { "width": "15%", "targets": 2 },
                    { "width": "10%", "targets": 3 },
                    { "width": "10%", "targets": 4 },
                   
                ],
                data: data.resultado,
                columns:[
                        {data: "lote"},
                        {data: "detalle" },
                        {data: "fcaduca"},
                        {data: "existencia"},
                        {data: "existencia"},
                       
                ],    
                "rowCallback": function( row, data, index ) {
                    // if(data.lote=="null"){
                    //     $('td', row).eq(0).html(data.codi_it)
                    // }else{
                    //     $('td', row).eq(0).html(data.lote)
                    // }
                    $('td', row).eq(4).html(`
                                  
                                            <button type="button" class="btn btn-primary btn-xs" onclick="agg_medicamento('${data.idprod}', '${data.idprod}', '${bodega_selecc}', '${data.existencia}', '${data.precio}',  '${data.felabora}', '${data.fcaduca}', '${data.lote}', '${data.regsan}','${data.idbodprod}','${data.permitir}')"><i class="fa fa-check-circle-o"></i></button>
                                                                                
                                          
                                    
                    `); 
                }             
            });
        }
    }).fail(function(){
        $("#tabla_medicina tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
   
})

function limpiarInfoItem(){
    //cuando se cierra la modal limpiamos la tabla y el input de busqueda
   //cuando se cierra la modal limpiamos la tabla y el input de busqueda
   var num_col = $("#tabla_medicina thead tr th").length; //obtenemos el numero de columnas de la tabla
   $("#tabla_medicina tbody").html('')
   $("#tabla_medicina").DataTable().destroy();
   $('#tabla_medicina tbody').empty();
   $("#tabla_medicina tbody").html(`<tr><td colspan="${num_col}" style="padding:20px; 0px; font-size:18px;"><center>No se encontraron datos</center></td></tr>`);
   $('#item_txt').val('')
}

//funcion para cuando selecciona un material del combo
function agg_medicamento(id_item,nombrex, bodega, cantidad, precio, felab, fcad, lote, rsanitario,idbodprod, permitir){

    let filtrar_item = datosItem.filter(datos => datos.idprod_ == id_item );    
    let nombre=filtrar_item[0].nombres_
    console.log(filtrar_item)
    if(permitir=="No"){
        alertNotificar("No se puede solicitar este item porque presenta inconsistencia en su stock","error")
        return
    }else if(permitir=="X"){
        limpiarInfoItem()
        alertNotificar("Usted no tiene acceso al item "+nombre+",comuniquese con farmacia","error")
        swal("Usted no tiene acceso al item "+nombre+", comuniquese con farmacia", "¡Ocurrió un error!", "error");       
        return
    }else if(permitir=="XS"){
        limpiarInfoItem()
        alertNotificar("Usted no tiene acceso a esta bodega, comuniquese con TICS","error")
        swal("Usted no tiene acceso a esta bodega, comuniquese con TICS", "¡Ocurrió un error!", "error");       
        return
    }

    IdMedicActual=id_item;
    var nueva_fila=id_item;
    var nfilas=$("#tb_listaMedicamento tr").length;
    if(nfilas>0){
        var dato=$('#idmedicina_selecc'+id_item).val();
       
        if(nueva_fila==dato){
            alertNotificar("El item ya está agregado a la lista","error");
            return;
        }
    }
    if(fcad!='null'){
        if(filtrar_item.length>0){
            let fecha_mayor=filtrar_item[0].fcaduca_
            if(fecha_mayor !=  fcad){
                alertNotificar("Debe seleccionar el/la "+nombre+ " con la fecha de vencimiento mas proxima","error")
                return
            }
        }
    }

    if(felab!=""){
        // felab=felab.split('/')
        
        // felab=felab[2]+"-"+felab[1]+"-"+felab[0]
    }

    if(fcad!=""){
        // fcad=fcad.split('/')
        // fcad=fcad[2]+"-"+fcad[1]+"-"+fcad[0]
    
    }

    console.log(felab)
    globalThis.CantidadItem=cantidad
    globalThis.PrecioItem=precio

    if(rsanitario==null || rsanitario=="null"){
        rsanitario=""
    }

    $("#modal_busqueda").modal("hide")
   
    $('#seccion_materiales').show();
    $('#btn_cancelar').show();

    $('#btn_registrar').prop('disabled',false);  
    $('#tb_listaMedicamento').append(`<tr id="medicamentos_${id_item}">

        <td width="5%" class="centrado"> 
            <button type="button" style="margin-right:1px !important" data-toggle="tooltip" data-original-title="Eliminar" class="btn btn-xs btn-danger marginB0" onClick="eliminar_material(${id_item})">
                <i class="fa fa-trash" >
                    
                </i> 
            </button>


        </td>   
     
        <td width="27%" class="centrado">
            <input type="hidden" name="idmedicina_selecc[]" id="idmedicina_selecc${id_item}" value="${id_item}">
            <input type="hidden" name="idbodega_selecc[]" id="idbodega_selecc${id_item}" value="${bodega}">
            <input type="hidden" name="nombrematerial[]" id="nombre_medicina_${id_item}" value='${nombre}'>
            <input type="hidden" name="idbodega_producto[]" id="idbodega_producto${id_item}" value="${idbodprod}">
           ${nombre}
        </td> 
        <td width="8%" class="centrado">
            <input type="number"id="class_cantidad_${id_item}" style="width:100% !important;text-align:right" name="cantidad[]" onkeyup="tecla_cantidad(this,'${id_item}')" onclick="validar_cantidad(this,'${id_item}')" onblur="validar_cantidad(this,'${id_item}')" placeholder="${cantidad}">

            <input type="hidden"id="class_cantidadmax_${id_item}" style="width:100% !important;text-align:right" name="cantidadmax[]" value="${cantidad}">

        </td> 

        <td width="8%" class="centrado">
            <input type="number"id="class_precio_${id_item}" step=""0.01" style="width:100% !important;text-align:right" name="precio[]"  onblur="validar_precio(this,'${id_item}')" value="${precio}" readonly >
        </td>  

     
        <td width="12%" class="centrado">
            <input type="date"  id="class_fecha_elab_${id_item}" style="width:100% !important;text-align:right" name="fecha_elab_[]")  value="${felab}" readonly>
        </td>  

        <td width="12%" class="centrado">
            <input type="date"id="class_fecha_caduc_${id_item}"  style="width:100% !important;text-align:right" name="fecha_caduc[]" value="${fcad}" readonly>
        </td>  

   
        <td width="8%" class="centrado">
            <input type="text"id="class_lote_${id_item}"  style="width:100% !important;text-align:right" name="lote[]"  value="${lote}" readonly>
        </td>

        <td width="8%" class="centrado">
            <input type="text"id="class_reg_sani_${id_item}" style="width:100% !important;text-align:right" name="reg_sani[]" value="${rsanitario}" readonly>
        </td>

        <td width="9%" align="right" class="centrado">
            <input type="hidden" readonly id="class_total_${id_item}"  style="width:100% !important;text-align:right" name="total[]">
            <span style="text-align:right" id="total_span_id_${id_item}">0.00</span>
        </td>  

         
    </tr>`);

   
    $('[data-toggle="tooltip"]').tooltip();

   
    
}

function validar_precio(e, id){
    calcularTotalFila(id)
}
function calcularTotalFila(id){
    // var id=IdMedicActual;
    var valor_cantidad=$('#class_cantidad_'+id).val();   
    // var valor_precio=$('#class_precio_'+id).val(PrecioItem); 
    var valor_precio=PrecioItem; 
   
    if(valor_precio=="" || valor_cantidad==""){
        return
    }
   
    var total_fila=0
    CantidadItem=$("#class_cantidadmax_"+id).val()

    if(parseFloat(CantidadItem)< parseFloat(valor_cantidad)){
        alertNotificar("La cantidad a egresar no debe ser mayor a la cantidad en existencia "+CantidadItem,"error")
        $('#class_cantidad_'+id).val('')
        $('#class_total_'+id).val("")
        $('#total_span_id_'+id).html("")
        calculaTotalIngreso()
        return
    }

    if(valor_cantidad>0 && valor_precio>0 ){

        valor_precio=valor_precio*1;
        valor_precio=valor_precio.toFixed(2)

        $('#class_precio_'+id).val(valor_precio)
       
        total_fila= (valor_cantidad * valor_precio) ;

        total_fila=total_fila*1
        total_fila=total_fila.toFixed(2)
        

        $('#class_total_'+id).val(total_fila)
        $('#total_span_id_'+id).html(total_fila)

    }else{
        $('#class_total_'+id).val("")
        $('#total_span_id_'+id).html("")
    }
    calculaTotalIngreso()


}

function eliminar_material(id){
    $('#medicamentos_'+id).remove();
    calculaTotalIngreso();
    var nfilas=$("#tb_listaMedicamento tr").length;
    if(nfilas==0){
        TipoBod=0
    }
    
    
   
}

function calculaTotalIngreso(){
    $('#tb_pie_TotalMedicamentos').html('');
    var array_total_parcial=[];
    var total_final=0;
    $("input[name='total[]']").each(function(indice, elemento) {
        var tot_parcial=$(elemento).val();
        if(tot_parcial==""){
            tot_parcial=0;
        }
        array_total_parcial.push($(elemento).val());
        total_final=parseFloat(total_final)+parseFloat(tot_parcial);
    });
    if(array_total_parcial.length>0){

        $('#tb_pie_TotalMedicamentos').append(`<tr>
            <td colspan="8" align="right">TOTAL</td>
            <td align="right"><input type="hidden" value="${total_final.toFixed(2)}" readonly id="total_suma"  style="text-align:right" name="total_suma">${total_final.toFixed(2)}</td>  
           
        </tr>`);
    }
}



function tecla_cantidad(e, id){
    var valor_cantidad=$('#class_cantidad_'+id).val(); 
    calcularTotalFila(id)  
    if(valor_cantidad<=0){
        alertNotificar("La cantidad debe ser mayor que cero","error");
        $('#class_cantidad_'+id).focus();
        $('#class_cantidad_'+id).val('')
        return;
    }
    //calcularTotalFila()
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




function validaMedAgg(){
    var id=IdMedicActual;
    var valor_cantidad=$('#class_cantidad_'+id).val();  
    var valor_precio=$('#class_precio_'+id).val();  
   
    var todo_ok=1;

    if(valor_cantidad<=0 || valor_cantidad==""){
        alertNotificar("La cantidad debe ser mayor que cero","error");
        $('#class_cantidad_'+id).focus()
        $('#class_cantidad_'+id).val('')
        todo_ok=0
        return;
    }

    if(valor_precio<0 || valor_precio==""){
        alertNotificar("El precio debe ser mayor que cero","error");
        $('#class_precio_'+id).focus()
        $('#class_precio_'+id).val('')
        todo_ok=0
        return;
    }
  
 

    //si todo esta ok permitimos abrir modal
    if(todo_ok==1 && btnGuardar=="N"){
        $("#modal_busqueda").modal("show")
    }
   
    
}

function guardarEgresoBodega(){
    btnGuardar="S"
   
    var cie10=$("#cie_10").val()
    var responsable=$("#id_responsable").val()
    var id_paciente=$("#id_paciente").val()
    var cmb_bodega=$("#cmb_bodega").val()
    var motivo=$("#motivo").val()
    var nfilas=$("#tb_listaMedicamento tr").length;
    var password=$('#password').val()
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
 
    var id=IdMedicActual;
    var valor_cantidad=$('#class_cantidad_'+id).val();  
    var valor_precio=$('#class_precio_'+id).val();  
    
    var fecha_actual=$('#fecha_actual').val()
    var todo_ok=1;

    if(valor_cantidad<=0 || valor_cantidad==""){
        alertNotificar("La cantidad debe ser mayor que cero","error");
        $('#class_cantidad_'+id).focus()
        $('#class_cantidad_'+id).val('')
        todo_ok=0
        return;
    }

    if(valor_precio<0 || valor_precio==""){
        alertNotificar("El precio debe ser mayor que cero","error");
        $('#class_precio_'+id).focus()
        $('#class_precio_'+id).val('')
        todo_ok=0
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
        }
        sweetAlert.close();   // ocultamos la ventana de pregunta
    }); 

}

$("#form_pedido_bodega").submit(function(e){
    e.preventDefault();
    vistacargando("m", "Espere por favor");  
    //comprobamos si es registro o edicion

    tipo="POST"
    if(bod==8 || bod==13 || bod==14 || bod==19 || bod==23 || bod==24 || bod==18 || bod==17){
        //pedido a bod gral desde farmacia
        url_form="guardar-pedido-bodega-farmacia"
    }else if(bod==22 || bod==25 || bod==26 || bod==27 || bod==28 || bod==29){
        //pedidos a bod d farmacia desde lab
        url_form="guardar-pedido-bodega-farm-laborat"
    // }else if(bod==2 || bod==18 || bod==21){
    //     //pedidos a bod insumo d farmacia desde ...
    //     url_form="guardar-pedido-bodega-farm-laborat"
    // }else if(bod==1 || bod==17 || bod==20){
    //     //pedidos a bod medicina d farmacia desde ...
    //     url_form="guardar-pedido-bodega-farm-laborat"
    // 
    }else if(bod==21 || bod==7){
        url_form=UrlSistema+"/guardar-pedido-farm-insumo"
    }
    
    else{
        
        url_form="guardar-pedido-bodega-area"
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
  
    $("#id_responsable").val('')
    $("#cedula_responsable").val('')
    $("#nombre_responsable").val('')
    $('#password').val('')

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