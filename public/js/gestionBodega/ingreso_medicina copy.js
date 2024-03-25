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
    //cuando se cierra la modal limpiamos la tabla y el input de busqueda
    var num_col = $("#tabla_medicina thead tr th").length; //obtenemos el numero de columnas de la tabla
    $("#tabla_medicina tbody").html('')
    $("#tabla_medicina").DataTable().destroy();
    $('#tabla_medicina tbody').empty();
    $("#tabla_medicina tbody").html(`<tr><td colspan="${num_col}" style="padding:20px; 0px; font-size:18px;"><center>No se encontraron datos</center></td></tr>`);
    $('#item_txt').val('')

})

$("#form_medicina").submit(function(e){
    e.preventDefault();

    var txt_item=$('#item_txt').val()
    var bodega_selecc=$('#cmb_bodega').val()
    
    if(txt_item===""){
        alertNotificar("Ingrese el nombre","error")
        return
    }
   
    //si es medicamento ña bodega buscamos en la tabla medicamentos
    var url_busqueda=""
    if(bodega_selecc==1){
        url_busqueda="listado-medicamentos-filtra/"+txt_item;
    }else if(bodega_selecc==2){
        url_busqueda="listado-insumos-filtra/"+txt_item;
    }else if(bodega_selecc==8){
        url_busqueda="listado-lab-mat-filtra/"+txt_item;
    }else if(bodega_selecc==13){
        url_busqueda="listado-lab-react-filtra/"+txt_item;
    }else{
        url_busqueda="listado-lab-microb-filtra/"+txt_item; //14
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
         
            $('#tabla_medicina').DataTable({
                "destroy":true,
                pageLength: 10,
                autoWidth : true,
                // order: [[ 1, "desc" ]],
                ordering:false,
                sInfoFiltered:false,
                language: {
                    url: 'json/datatables/spanish.json',
                },
                columnDefs: [
                    { "width": "20%", "targets": 0 },
                    { "width": "60%", "targets": 1 },
                    { "width": "20%", "targets": 2 },
                   
                   
                ],
                data: data.resultado,
                columns:[
                        {data: "codi"},
                        {data: "detalle" },
                        {data: "detalle"},
                       
                ],    
                "rowCallback": function( row, data, index ) {
                    $('td', row).eq(2).html(`
                                  
                                            <button type="button" class="btn btn-primary btn-xs" onclick="agg_medicamento('${data.codigo_item}', '${data.detalle}', '${bodega_selecc}')"><i class="fa fa-check-circle-o"></i></button>
                                                                                
                                          
                                    
                    `); 
                }             
            });
        }
    }).fail(function(){
        $("#tabla_medicina tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
   
})

//funcion para cuando selecciona un material del combo
function agg_medicamento(id_item,nombre, bodega){
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
           ${nombre}
        </td> 
        <td width="8%" class="centrado">
            <input type="number"id="class_cantidad_${id_item}" style="width:100% !important;text-align:right" name="cantidad[]" onkeyup="tecla_cantidad(this,'${id_item}')"  onblur="validar_cantidad(this,'${id_item}')" >
        </td> 

        <td width="8%" class="centrado">
            <input type="number"id="class_precio_${id_item}" step=""0.01" style="width:100% !important;text-align:right" name="precio[]" onkeyup="tecla_precio(this,'${id_item}')"  onblur="validar_precio(this,'${id_item}')" >
        </td>  


        <td width="12%" class="centrado">
            <input type="date"  id="class_fecha_elab_${id_item}" style="width:100% !important;text-align:right" name="fecha_elab_[]")   >
        </td>  

        <td width="12%" class="centrado">
            <input type="date"id="class_fecha_caduc_${id_item}"  style="width:100% !important;text-align:right" name="fecha_caduc[]">
        </td>  

   
        <td width="8%" class="centrado">
            <input type="text"id="class_lote_${id_item}"  style="width:100% !important;text-align:right" name="lote[]" >
        </td>

        <td width="8%" class="centrado">
            <input type="text"id="class_reg_sani_${id_item}" style="width:100% !important;text-align:right" name="reg_sani[]" >
        </td>

        <td width="8%" align="right" class="centrado">
            <input type="hidden" readonly id="class_total_${id_item}"  style="width:100% !important;text-align:right" name="total[]">
            <span style="text-align:right" id="total_span_id_${id_item}">0.00</span>
        </td>  

         
    </tr>`);

   
    $('[data-toggle="tooltip"]').tooltip();

   
    
}

function calcularTotalFila(){
    var id=IdMedicActual;
    var valor_cantidad=$('#class_cantidad_'+id).val();   
    var valor_precio=$('#class_precio_'+id).val(); 

    if(valor_precio==""){
        return
    }
   
    var total_fila=0

    if(valor_cantidad>0 && valor_precio>0 ){

        valor_precio=valor_precio*1;
        valor_precio=valor_precio.toFixed(2)
        
        $('#class_precio_'+id).val(valor_precio)
       
        total_fila= (valor_cantidad * valor_precio);

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
    if(valor_cantidad<=0){
        alertNotificar("La cantidad debe ser mayor que cero","error");
        $('#class_cantidad_'+id).focus();
        $('#class_cantidad_'+id).val('')
        return;
    }
    //calcularTotalFila()
}

function tecla_precio(e, id){
    var valor_precio=$('#class_precio_'+id).val();   
    if(valor_precio<0){
        alertNotificar("El precio debe ser mayor que cero","error");
        $('#class_precio_'+id).focus();
        $('#class_precio_'+id).val('')
        return;
    }
   // calcularTotalFila()
}



function validar_cantidad(e, id){
    var valor_cantidad=$('#class_cantidad_'+id).val();   
    if(valor_cantidad<=0  && valor_cantidad!=""){
        alertNotificar("La cantidad debe ser mayor que cero","error");
        $('#class_cantidad_'+id).focus();
        $('#class_cantidad_'+id).val('')
        return;
    }
    calcularTotalFila()
}

function validar_precio(e, id){
    var valor_precio=$('#class_precio_'+id).val();   
    if(valor_precio<0 && valor_precio!=""){
        alertNotificar("El precio debe ser mayor que cero","error");
        $('#class_precio_'+id).focus();
        $('#class_precio_'+id).val('')
        return;
    }
    calcularTotalFila()
}




function validaMedAgg(){
    var id=IdMedicActual;
    var valor_cantidad=$('#class_cantidad_'+id).val();  
    var valor_precio=$('#class_precio_'+id).val();  
    var valor_fe=$('#class_fecha_elab_'+id).val();   
    var valor_fc=$('#class_fecha_caduc_'+id).val();   
    var lote=$('#class_lote_'+id).val();   
    var rs=$('#class_reg_sani_'+id).val();  
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
  

          
    if( (new Date(fecha_actual).getTime() <= new Date(valor_fe).getTime())){
        alertNotificar("La fecha de elaboracion debe ser menor a la fecha actual","error");
        $('#class_fecha_elab_'+id).focus()
        $('#class_fecha_elab_'+id).val('')
        todo_ok=0
        return;
    }

    if(valor_fe==""){
        alertNotificar("Ingrese la fecha de elaboracion ","error");
        $('#class_fecha_elab_'+id).focus()
        $('#class_fecha_elab_'+id).val('')
        todo_ok=0
        return;
    }


    if(valor_fc==""){
        alertNotificar("Ingrese la fecha de caducidad ","error");
        $('#class_fecha_caduc_'+id).focus()
        $('#class_fecha_caduc_'+id).val('')
        todo_ok=0
        return;
    }
 
    if(valor_fe!="" && valor_fc!=""){
        
        if( (new Date(valor_fc).getTime() < new Date(valor_fe).getTime())){
            alertNotificar("La fecha de caducidad debe ser mayor a la fecha de elaboracion","error");
            $('#class_fecha_caduc_'+id).focus()
            $('#class_fecha_caduc_'+id).val('')
            todo_ok=0
            return;
        }
    }

    if(lote==""){
        alertNotificar("Ingrese el lote ","error");
        $('#class_lote_'+id).focus()
        $('#class_lote_'+id).val('')
        todo_ok=0
        return;
    }

 
    if(rs==""){
        alertNotificar("Ingrese el registro sanitario ","error");
        $('#class_reg_sani_'+id).focus()
        $('#class_reg_sani_'+id).val('')
        todo_ok=0
        return;
    }

    //si todo esta ok permitimos abrir modal
    if(todo_ok==1 && btnGuardar=="N"){
        $("#modal_busqueda").modal("show")
    }
   
    
}

function guardarIngresoBodega(){
    btnGuardar="S"
   
    var proveedor=$("#cmb_proveedor").val()
    var tipo_ingreso_cmb=$("#tipo_ingreso_cmb").val()
    var cmb_bodega=$("#cmb_bodega").val()
    var nfilas=$("#tb_listaMedicamento tr").length;

    if(proveedor==""){
        alertNotificar("Seleccione el proveedor ","error");
        return;
    }

    if(tipo_ingreso_cmb==""){
        alertNotificar("Seleccione el tipo ingreso ","error");
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
    var valor_fe=$('#class_fecha_elab_'+id).val();   
    var valor_fc=$('#class_fecha_caduc_'+id).val();   
    var lote=$('#class_lote_'+id).val();   
    var rs=$('#class_reg_sani_'+id).val();  
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
  

          
    if( (new Date(fecha_actual).getTime() <= new Date(valor_fe).getTime())){
        alertNotificar("La fecha de elaboracion debe ser menor a la fecha actual","error");
        $('#class_fecha_elab_'+id).focus()
        $('#class_fecha_elab_'+id).val('')
        todo_ok=0
        return;
    }

    if(valor_fe==""){
        alertNotificar("Ingrese la fecha de elaboracion ","error");
        $('#class_fecha_elab_'+id).focus()
        $('#class_fecha_elab_'+id).val('')
        todo_ok=0
        return;
    }


    if(valor_fc==""){
        alertNotificar("Ingrese la fecha de caducidad ","error");
        $('#class_fecha_caduc_'+id).focus()
        $('#class_fecha_caduc_'+id).val('')
        todo_ok=0
        return;
    }
 
    if(valor_fe!="" && valor_fc!=""){
        
        if( (new Date(valor_fc).getTime() < new Date(valor_fe).getTime())){
            alertNotificar("La fecha de caducidad debe ser mayor a la fecha de elaboracion","error");
            $('#class_fecha_caduc_'+id).focus()
            $('#class_fecha_caduc_'+id).val('')
            todo_ok=0
            return;
        }
    }

    if(lote==""){
        alertNotificar("Ingrese el lote ","error");
        $('#class_lote_'+id).focus()
        $('#class_lote_'+id).val('')
        todo_ok=0
        return;
    }

 
    if(rs==""){
        alertNotificar("Ingrese el registro sanitario ","error");
        $('#class_reg_sani_'+id).focus()
        $('#class_reg_sani_'+id).val('')
        todo_ok=0
        return;
    }
    
    globalThis.AccionForm="R"
    $("#form_ingreso_bodega").submit()
}

$("#form_ingreso_bodega").submit(function(e){
    e.preventDefault();
    vistacargando("m", "Espere por favor");  
    //comprobamos si es registro o edicion
    let tipo=""
    let url_form=""
    if(AccionForm=="R"){
        tipo="POST"
        url_form="guardar-ingreso-bodega"
    }else{
        tipo="PUT"
        url_form="actualizar-menu/"+idMenuEditar
    }
  
    var FrmData=$("#form_ingreso_bodega").serialize();

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
            cancelarIngreso()
            alertNotificar(data.mensaje,"success");
            
        }, error:function (data) {
            console.log(data)

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})

function cancelarIngreso(){
    IdMedicActual=0;
    $("#cmb_proveedor").val('').change();
    $("#tipo_ingreso_cmb").val('').change();
    $("#cmb_bodega").val('').change();
    $("#cmb_tipo_med").val('').change();
    $("#tb_listaMedicamento tr").html('');
    $('#tb_pie_TotalMedicamentos').html('');

}