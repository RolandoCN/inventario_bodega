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
        $("#modal_busqueda").modal({backdrop: 'static', keyboard: false})
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

    var num_col_ = $("#tabla_medicina_dev thead tr th").length; //obtenemos el numero de columnas de la tabla
    $("#tabla_medicina_dev tbody").html('')
    $("#tabla_medicina_dev").DataTable().destroy();
    $('#tabla_medicina_dev tbody').empty();
    $("#tabla_medicina_dev tbody").html(`<tr><td colspan="${num_col_}" style="padding:20px; 0px; font-size:18px;"><center>No se encontraron datos</center></td></tr>`);

    volverBusqueda()

})

$("#form_medicina").submit(function(e){
    e.preventDefault();

    var txt_item=$('#item_txt').val()
    var bodega_selecc=$('#cmb_bodega').val()
    
    if(txt_item===""){
        alertNotificar("Ingrese el nombre","error")
        return
    }

    var tipo_ingreso=$('#tipo_ingreso_cmb').val()
    if(tipo_ingreso==4){
        //si es medicamento ña bodega buscamos en la tabla medicamentos 
        var url_busqueda=""
        if(bodega_selecc==1 || bodega_selecc==17){
            url_busqueda="listado-medicamentos-filtra-dev/"+txt_item+"/"+bodega_selecc;
        }else if(bodega_selecc==2 || bodega_selecc==18){
            url_busqueda="listado-insumos-filtra-dev/"+txt_item+"/"+bodega_selecc;
        }else if(bodega_selecc==8 || bodega_selecc==19){
            url_busqueda="listado-lab-mat-filtra-dev/"+txt_item+"/"+bodega_selecc;
        }else if(bodega_selecc==13 || bodega_selecc==23){
            url_busqueda="listado-lab-mat-filtra-dev/"+txt_item+"/"+bodega_selecc;
        }else if(bodega_selecc==14 || bodega_selecc==24){
            url_busqueda="listado-lab-mat-filtra-dev/"+txt_item+"/"+bodega_selecc;
        }else if(bodega_selecc==173){
            // url_busqueda="listado-medicamentos-dial-filtra/"+txt_item; //14
        }else if(bodega_selecc==1833){
            url_busqueda="listado-insumo-dial-filtra/"+txt_item; //14
        }else if(bodega_selecc==0){
            url_busqueda="listado-lab-ins-filtra/"+txt_item; //14
        }else if(bodega_selecc==30){//bodega proteccion
            url_busqueda="listado-proteccion-filtra-dev/"+txt_item+"/"+bodega_selecc;
        }else {
            url_busqueda="listado-item-filtra-dev/"+txt_item+"/"+bodega_selecc; //otros
        }

        $('#tabla_medicina_dev').show()
        $('#tabla_medicina').hide()
        
        var num_col = $("#tabla_medicina_dev thead tr th").length; //obtenemos el numero de columnas de la tabla
        $("#tabla_medicina_dev tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);
    
        
        $.get(url_busqueda, function(data){
            console.log(data)
            if(data.error==true){
                alertNotificar(data.mensaje,"error");
                $("#tabla_medicina_dev tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
                return;   
            }
            if(data.error==false){
                
                if(data.resultado.length <= 0){
                    $("#tabla_medicina_dev tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
                    alertNotificar("No se encontró datos","error");
                    return;  
                }

                datoItemArray=[]
                $.each(data.resultado,function(i, item){
                    datoItemArray.push({'idprod_':item.codigo_item,'nombres_':item.detalle});
                    globalThis.datosItem=datoItemArray;
                })
            
                $('#tabla_medicina_dev').DataTable({
                    "destroy":true,
                    pageLength: 10,
                    autoWidth : true,
                    order: [[ 2, "asc" ]],
                    // ordering:false,
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
                            {data: "precio"},
                            {data: "existencia"},
                           
                    ],    
                    "rowCallback": function( row, data, index ) {
                        $('td', row).eq(4).html(`
                                      
                                                <button type="button" class="btn btn-primary btn-xs" onclick="agg_medicamentodev('${data.idprod}', '${data.idprod}', '${bodega_selecc}', '${data.existencia}', '${data.precio}',  '${data.felabora}', '${data.fcaduca}', '${data.lote}', '${data.regsan}','${data.idbodprod}','${data.permitir}')"><i class="fa fa-check-circle-o"></i></button>
                                                                                    
                                              
                                        
                        `); 
                    }             
                });
            }
        }).fail(function(){
            $("#tabla_medicina_dev tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
            alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
        });

    }else{   
        //si es medicamento ña bodega buscamos en la tabla medicamentos
        var url_busqueda=""
        if(bodega_selecc==1 || bodega_selecc==17){
            url_busqueda="listado-medicamentos-filtra/"+txt_item;
        }else if(bodega_selecc==2 || bodega_selecc==18){
            url_busqueda="listado-insumos-filtra/"+txt_item;
        }else if(bodega_selecc==8 || bodega_selecc==19){
            url_busqueda="listado-lab-mat-filtra/"+txt_item;
        }else if(bodega_selecc==13 || bodega_selecc==23){
            url_busqueda="listado-lab-react-filtra/"+txt_item;
        }else if(bodega_selecc==14 || bodega_selecc==24){
            url_busqueda="listado-lab-microb-filtra/"+txt_item; //14
        }else if(bodega_selecc==173){
            // url_busqueda="listado-medicamentos-dial-filtra/"+txt_item; //14
        }else if(bodega_selecc==1833){
            url_busqueda="listado-insumo-dial-filtra/"+txt_item; //14
        }else if(bodega_selecc==0){
            url_busqueda="listado-lab-ins-filtra/"+txt_item; //14
        }else if(bodega_selecc==30){//bodega proteccion
            url_busqueda="listado-proteccion-filtra/"+txt_item; //14
        }else {
            url_busqueda="listado-item-filtra/"+txt_item+"/"+bodega_selecc; //otros
        }

        $('#tabla_medicina_dev').hide()
        $('#tabla_medicina').show()
        
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
                    datoItemArray.push({'idprod_':item.codigo_item,'nombres_':item.detalle});
                    globalThis.datosItem=datoItemArray;
                })
            
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
                        if(data.codi==null){
                            $('td', row).eq(0).html(data.codigo_item)
                        }else{
                            $('td', row).eq(0).html(data.codi)
                        }
                        $('td', row).eq(2).html(`
                                    
                                                <button type="button" class="btn btn-primary btn-xs" onclick="agg_medicamento('${data.codigo_item}', '${data.codigo_item}', '${bodega_selecc}')"><i class="fa fa-check-circle-o"></i></button>
                                                                                    
                                            
                                        
                        `); 
                    }             
                });
            }
        }).fail(function(){
            $("#tabla_medicina tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
            alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
        });
    }



   
})

//funcion para cuando selecciona un material del combo
function agg_medicamento1(id_item,nombrex, bodega){

    let filtrar_item = datosItem.filter(datos => datos.idprod_ == id_item );
    console.log(filtrar_item)

    let nombre=filtrar_item[0].nombres_

    IdMedicActual=id_item;
    var nueva_fila=id_item;
    var nfilas=$("#tb_listaMedicamento tr").length;
    if(nfilas>0){
        var dato=$('#idmedicina_selecc'+id_item).val();
       
        if(nueva_fila==dato){
            // alertNotificar("El item ya está agregado a la lista","error");
            // return;
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
            <input type="number"id="class_precio_${id_item}" step="any" style="width:100% !important;text-align:right" name="precio[]" onkeyup="tecla_precio(this,'${id_item}')"  onblur="validar_precio(this,'${id_item}')" >
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
globalThis.IdFila="";
function agg_medicamento(id_itemx,nombrex, bodega){

    let filtrar_item = datosItem.filter(datos => datos.idprod_ == id_itemx );
    console.log(filtrar_item)

    let nombre=filtrar_item[0].nombres_

    var nfilas=$("#tb_listaMedicamento tr").length;
    var id_item=nfilas
    
    IdMedicActual=id_itemx;
    var nueva_fila=id_item;
    // globalThis.IdFila=id_item
    IdFila=id_item
    var nfilas=$("#tb_listaMedicamento tr").length;
    if(nfilas>0){
        var dato=$('#idmedicina_selecc'+id_item).val();
       
        if(nueva_fila==dato){
            // alertNotificar("El item ya está agregado a la lista","error");
            // return;
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
            <input type="hidden" name="idmedicina_selecc[]" id="idmedicina_selecc${id_item}" value="${IdMedicActual}">
            <input type="hidden" name="idbodega_selecc[]" id="idbodega_selecc${id_item}" value="${bodega}">
            <input type="hidden" name="nombrematerial[]" id="nombre_medicina_${id_item}" value='${nombre}'>
           ${nombre}
        </td> 
        <td width="8%" class="centrado">
            <input type="number"id="class_cantidad_${id_item}" style="width:100% !important;text-align:right" name="cantidad[]" onkeyup="tecla_cantidad(this,'${id_item}')"  onblur="validar_cantidad(this,'${id_item}')" >
        </td> 

        <td width="8%" class="centrado">
            <input type="number"id="class_precio_${id_item}" step="any" style="width:100% !important;text-align:right" name="precio[]" onkeyup="tecla_precio(this,'${id_item}')"  onblur="validar_precio(this,'${id_item}')" >
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

//funcion para cuando selecciona un material del combo
function agg_medicamentodev(id_item,nombrex, bodega, cantidad, precio, felab, fcad, lote, rsanitario,idbodprod, permitir){

    if(permitir=="No"){
        alertNotificar("No se puede egresar este item porque presenta inconsistencia","error")
        return
    }

    let filtrar_item = datosItem.filter(datos => datos.idprod_ == id_item );
    console.log(filtrar_item)

    let nombre=filtrar_item[0].nombres_
    if(fcad!='null'){
        if(filtrar_item.length>0){
            let fecha_mayor=filtrar_item[0].fcaduca_
            // if(fecha_mayor !=  fcad){
            //     alertNotificar("Debe seleccionar el/la "+nombre+ " con la fecha de vencimiento mas proxima","error")
            //     return
            // }
        }
    }


    IdMedicActual=id_item;
    var nueva_fila=id_item;
    var nfilas=$("#tb_listaMedicamento tr").length;
    
    IdFila=id_item
    if(nfilas>0){
        var dato=$('#idmedicina_selecc'+id_item).val();
       
        if(nueva_fila==dato){
            alertNotificar("El item ya está agregado a la lista","error");
            return;
        }
    }

    if(felab!=""){
        felab=felab.split('/')
       
    }

    if(fcad!=""){
        fcad=fcad.split('/')
       
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
            <input type="number"id="class_cantidad_${id_item}" style="width:100% !important;text-align:right" name="cantidad[]" onkeyup="tecla_cantidad(this,'${id_item}')"  onblur="validar_cantidad(this,'${id_item}')" placeholder="${cantidad}">
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

function cambiaTipoIngeso(){
    var tipo=$('#tipo_ingreso_cmb').val()
    if(tipo==""){return}
    if(tipo==4){
        //devolucion
        $('#cmb_user_dev').val('').change();
        $('#seccio_dev').show()
    }else{
        $('#seccio_dev').hide() 
        $('#cmb_user_dev').val('').change();   
    }
}

function calcularTotalFila(op, id){
    // var id=IdFila;
    var valor_cantidad=$('#class_cantidad_'+id).val();   
    var valor_precio=$('#class_precio_'+id).val(); 

    if(valor_precio==""){
        // return
    }
   
    var total_fila=0

    if(valor_cantidad>0 && valor_precio>0 ){

        valor_precio=valor_precio*1;
        valor_precio=valor_precio.toFixed(4)
        if(op=="E"){
            $('#class_precio_'+id).val(valor_precio)
       
        }
            
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
    calcularTotalFila('I',id)
}

function tecla_precio(e, id){
    var valor_precio=$('#class_precio_'+id).val();   
  

    if(valor_precio<0){
        alertNotificar("El precio debe ser mayor que cero","error");
        $('#class_precio_'+id).focus();
        $('#class_precio_'+id).val('')
        return;
    }
    calcularTotalFila('I', id)
   // 
}



function validar_cantidad(e, id){
    var valor_cantidad=$('#class_cantidad_'+id).val();   
    if(valor_cantidad<=0  && valor_cantidad!=""){
        alertNotificar("La cantidad debe ser mayor que cero","error");
        $('#class_cantidad_'+id).focus();
        $('#class_cantidad_'+id).val('')
        return;
    }
    calcularTotalFila('E', id)
}

function validar_precio(e, id){
    var valor_precio=$('#class_precio_'+id).val();   
    if(valor_precio<0 && valor_precio!=""){
        alertNotificar("El precio debe ser mayor que cero","error");
        $('#class_precio_'+id).focus();
        $('#class_precio_'+id).val('')
        return;
    }
    calcularTotalFila('E', id)
}


function validaMedAgg(){
    var id=IdFila;
   
    var valor_cantidad=$('#class_cantidad_'+id).val();  
    var valor_precio=$('#class_precio_'+id).val();  
    var valor_fe=$('#class_fecha_elab_'+id).val();   
    var valor_fc=$('#class_fecha_caduc_'+id).val();   
    var lote=$('#class_lote_'+id).val();   
    var rs=$('#class_reg_sani_'+id).val();  
    var fecha_actual=$('#fecha_actual').val()
    var todo_ok=1;
    var bodega_selecc=$('#cmb_bodega').val()

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
    
    //solo cuando sea medic, ins, lab mat y lab react validamos estos campos
    if(bodega_selecc==1 || bodega_selecc==2 || bodega_selecc==17 || bodega_selecc==18 || bodega_selecc==8 || bodega_selecc==13 || bodega_selecc==19 || bodega_selecc==23 ){
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
    }

 

    //si todo esta ok permitimos abrir modal
    if(todo_ok==1 && btnGuardar=="N"){
        $("#modal_busqueda").modal("show")
    }
   
    
}

function guardarIngresoBodega(){
    btnGuardar="S"
    var guia=$("#guia").val()
    var proveedor=$("#cmb_proveedor").val()
    var tipo_ingreso_cmb=$("#tipo_ingreso_cmb").val()
    var cmb_bodega=$("#cmb_bodega").val()
    var nfilas=$("#tb_listaMedicamento tr").length;
    var cmb_user_dev=$('#cmb_user_dev').val()

    if(guia==""){
        alertNotificar("Ingrese el numero de factura ","error");
        $("#guia").focus()
        return;
    }

    if(proveedor==""){
        alertNotificar("Seleccione el proveedor ","error");
        return;
    }

    if(tipo_ingreso_cmb==""){
        alertNotificar("Seleccione el tipo ingreso ","error");
        return;
    }

    if(tipo_ingreso_cmb==4){
        if(cmb_user_dev==""){
            alertNotificar("Seleccione el funcionario","error")
            return
        }
    }

    if(cmb_bodega==""){
        alertNotificar("Seleccione la bodega ","error");
        return;
    }

    if(nfilas<=0){
        alertNotificar("Debe agregar al menos un item ","error");
        return;
    }

    

    var id=IdFila;
    var valor_cantidad=$('#class_cantidad_'+id).val();  
    var valor_precio=$('#class_precio_'+id).val();  
    var valor_fe=$('#class_fecha_elab_'+id).val();   
    var valor_fc=$('#class_fecha_caduc_'+id).val();   
    var lote=$('#class_lote_'+id).val();   
    var rs=$('#class_reg_sani_'+id).val();  
    var fecha_actual=$('#fecha_actual').val()
    var todo_ok=1;
    var bodega_selecc=$('#cmb_bodega').val()

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
  
    //solo cuando sea medic, ins, lab mat y lab react validamos estos campos
    if(bodega_selecc==1 || bodega_selecc==2 || bodega_selecc==8 || bodega_selecc==13  || bodega_selecc==17 || bodega_selecc==18 || bodega_selecc==19 || bodega_selecc==23){
            
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
    }

 
    
    globalThis.AccionForm="R"

    swal({
        title: "¿Desea ingresar la  informacion?",
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
            $("#form_ingreso_bodega").submit()
        }
        sweetAlert.close();   // ocultamos la ventana de pregunta
    }); 
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
    IdFila=""
    $("#cmb_proveedor").val('').change();
    $("#tipo_ingreso_cmb").val('').change();
    $("#cmb_bodega").val('').change();
    $("#cmb_tipo_med").val('').change();

    $("#tb_listaMedicamento tr").html('');
    $('#tb_pie_TotalMedicamentos').html('');
    $("#guia").val('')
    $("#observa").val('')

    $("#TablaListaDetalle tbody").html('')
    $("#TablaListaDetalle").DataTable().destroy();
    $('#TablaListaDetalle tbody').empty();

    $('#seccio_dev').hide() 
    $('#cmb_user_dev').val('').change();   
    
    TipoBod=0

}

function nuevoItem(){
    var bod=$('#cmb_bodega').val()
    var btn="";
    if(bod==1){ btn="NM1"}
    else if(bod==2){ btn="NM2"}
    else if(bod==8){ btn="NM8"}
    else if(bod==13){ btn="NM13"}
    else if(bod==14){ btn="NM14"}
    else if(bod==3){ btn="NM3"}
    else if(bod==4){ btn="NM2"}
    else if(bod==5){ btn="NM5"}
    else if(bod==10){ btn="NM10"}
    else if(bod==9){ btn="NM9"}
    else if(bod==17){ btn="NM17"}
    else if(bod==18){ btn="NM18"}
    else if(bod==19){ btn="NM19"}
    vistacargando("m","s");
    $.get('verifica-permiso', function(data){
        console.log(data)
        vistacargando("")
        if(data.error==true){
			alertNotificar(data.mensaje,"error");
			return;   
		}
        var ok=0;
      
        $.each(data.resultado, function(i,item){
            if(item.codigo==btn){
                ok=1
            }
        })
        if(ok==0){
            alertNotificar("Usted no tiene permisos, para realizar esta accion", "error")
            return
        }else{
            nuevoItem_p()
        }
        
    }).fail(function(){
        alertNotificar("Ocurrio un error","error");
        vistacargando("")
       
    });  
}
function nuevoItem_p(){
    var bod=$('#cmb_bodega').val()
    if(bod==1){
        $('#form_item').show()
        $('#tabla_item').hide()
    }else if(bod==2){
        $('#form_item_ins').show()
        $('#tabla_item').hide()

        $('#tipo_ins').html('');				
        $('#tipo_ins').append(` <option value=""></option>
        <option value="1">DISPOSITIVOS MÉDICOS DE USO GENERAL </option>
        <option value="2">DISPOSITIVOS MÉDICOS DE ODONTOLOGÍA </option>
        <option value="3">DISPOSITIVOS MÉDICOS DE IMAGENOLOGÍA</option>
        <option value="4">DISPOSITIVOS MÉDICOS MATERIALES DE LABORATORIO</option>
        <option value="5">DISPOSITIVOS MÉDICOS REACTIVOS DE LABORATORIO</option>
        <option value="6">DISPOSITIVOS MÉDICOS DE MICROBIOLOGÍA</option>
        <option value="7">DESINFECTANTES</option>`).change();
        $("#tipo_ins").trigger("chosen:updated"); // actualizamos el combo 

    }else if(bod==8 || bod==13 || bod==14){
        $('#form_item_lab').show()
        $('#tabla_item').hide()
    }else if(bod==3 || bod==4 || bod==5 || bod==10 || bod==9){
        $('#form_item_mat_of').show()
        $('#tabla_item').hide()
    }else if(bod==17){
        //med dialisis
        $('#form_item_dialisis').show()
        $('#tabla_item').hide()

    }else if(bod==18){
        //ins dialisis
        $('#form_ins_dialisis').show()
        $('#tabla_item').hide()

        $('#tipo_ins_dialisi').html('');				
        $('#tipo_ins_dialisi').append(` <option value=""></option>
        <option value="1">DISPOSITIVOS MÉDICOS DE USO GENERAL </option>
        <option value="2">DISPOSITIVOS MÉDICOS DE ODONTOLOGÍA </option>
        <option value="3">DISPOSITIVOS MÉDICOS DE IMAGENOLOGÍA</option>
        <option value="4">DISPOSITIVOS MÉDICOS MATERIALES DE LABORATORIO</option>
        <option value="5">DISPOSITIVOS MÉDICOS REACTIVOS DE LABORATORIO</option>
        <option value="6">DISPOSITIVOS MÉDICOS DE MICROBIOLOGÍA</option>
        <option value="7">DESINFECTANTES</option>`).change();
        $("#tipo_ins_dialisi").trigger("chosen:updated"); // actualizamos el combo 

    }else if(bod==19){
        $('#form_lab_dialisis').show()
        $('#tabla_item').hide()
    }
    
}

function volverBusqueda(){
    $('#form_item').hide()
    $('#form_item_ins').hide()
    $('#form_item_lab').hide()
    $('#form_item_mat_of').hide()
    $('#form_item_dialisis').hide()
    $('#form_ins_dialisis').hide()
    $('#form_lab_dialisis').hide()
    $('#tabla_item').show()
    limpiarForm()
}

function limpiarForm(){
    $('#codigo').val('')
    $('#nombre_med').val('')
    $('#concentracion_med').val('')
    $('#forma_med').val('')
    $('#presentacion_med').val('')
    $('#stock_min').val('')
    $('#stock_cri').val('')

    $('#cudim').val('')
    $('#insumo').val('')
    $('#cod_esbay_ins').val('')
    $('#desc_ins').val('')
    $('#espec_tecn').val('')
    $('#stock_min_ins').val('')
    $('#stock_cri_ins').val('')

    $('#cod_lab').val('')
    $('#desc_lab').val('')

    $('#mat_of').val('')
    $('#prese_of').val('')


    $('#codigo_dialisis').val('')
    $('#nombre_med_dialisis').val('')
    $('#concentracion_med_dialisis').val('')
    $('#forma_med_dialisis').val('')
    $('#presentacion_med_dialisis').val('')
    $('#stock_min_dialisis').val('')
    $('#stock_cri_dialisis').val('')

    $('#cudim_dialisi').val('')
    $('#insumo_dialisi').val('')
    $('#cod_esbay_ins_dial').val('')
    $('#desc_ins_dialisi').val('')
    $('#stock_cri_ins_dialisi').val('')
    $('#stock_min_ins_dialisi').val('')

    $('#cod_lab_ins').val('')
    $('#desc_lab_ins').val('')

    var num_col = $("#tabla_medicina thead tr th").length; //obtenemos el numero de columnas de la tabla
    $("#tabla_medicina tbody").html('')
    $("#tabla_medicina").DataTable().destroy();
    $('#tabla_medicina tbody').empty();
    $("#tabla_medicina tbody").html(`<tr><td colspan="${num_col}" style="padding:20px; 0px; font-size:18px;"><center>No se encontraron datos</center></td></tr>`);
    $('#item_txt').val('')


   
}

function validaFormMedicina(){
    var codigo=$('#codigo').val()
    var nombre_med=$('#nombre_med').val()
    var concentracion_med=$('#concentracion_med').val()
    var forma_med=$('#forma_med').val()
    var presentacion_med=$('#presentacion_med').val()
    var stock_min=$('#stock_min').val()
    var stock_cri=$('#stock_cri').val()
   
    if(codigo==""){
        alertNotificar("Ingrese el codigo","error")
        $('#codigo').focus()
        return
    }


    if(nombre_med==""){
        alertNotificar("Ingrese el nombre","error")
        $('#nombre_med').focus()
        return
    }

    if(concentracion_med==""){
        alertNotificar("Ingrese la concentracion","error")
        $('#concentracion_med').focus()
        return
    }

    if(forma_med==""){
        alertNotificar("Ingrese la forma","error")
        $('#forma_med').focus()
        return
    }
    if(presentacion_med==""){
        alertNotificar("Ingrese la presentacion","error")
        $('#presentacion_med').focus()
        return
    }

    if(stock_min==""){
        alertNotificar("Ingrese el stock minimo","error")
        $('#stock_min').focus()
        return
    }

    if(stock_cri==""){
        alertNotificar("Ingrese el stock critico","error")
        $('#stock_cri').focus()
        return
    }

    swal({
        title: "¿Desea ingresar el medicamento?",
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
            $("#form_medicina_new").submit()
        }
        sweetAlert.close();   // ocultamos la ventana de pregunta
    });
    
}


$("#form_medicina_new").submit(function(e){
    e.preventDefault();
    vistacargando("m", "Espere por favor");  
    //comprobamos si es registro o edicion
  
    tipo="POST"
    url_form="guardar-medicina"
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var FrmData=$("#form_medicina_new").serialize();

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
            volverBusqueda()
            alertNotificar(data.mensaje,"success");
            
        }, error:function (data) {
            console.log(data)

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})



function validaFormInsumo(){
    var cudim=$('#cudim').val()
    var insumo=$('#insumo').val()
    var esbay=$('#cod_esbay_ins').val()
    var stock_min_ins=$('#stock_min_ins').val()
    var stock_cri_ins=$('#stock_cri_ins').val()

    if(cudim==""){
        alertNotificar("Ingrese el cudim","error")
        $('#cudim').focus()
        return
    }

    if(esbay==""){
        alertNotificar("Ingrese el codigo esbay","error")
        $('#cudesbayim').focus()
        return
    }
    
    if(insumo==""){
        alertNotificar("Ingrese el insumo","error")
        $('#insumo').focus()
        return
    }

    if(stock_min_ins==""){
        alertNotificar("Ingrese el stock minimo","error")
        $('#stock_min_ins').focus()
        return
    }

    if(stock_cri_ins==""){
        alertNotificar("Ingrese el stock critico","error")
        $('#stock_cri_ins').focus()
        return
    }

    swal({
        title: "¿Desea ingresar el insumo?",
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
            $("#form_insumo_new").submit()
        }
        sweetAlert.close();   // ocultamos la ventana de pregunta
    });
    
}


$("#form_insumo_new").submit(function(e){
    e.preventDefault();
    vistacargando("m", "Espere por favor");  
    //comprobamos si es registro o edicion
  
    tipo="POST"
    url_form="guardar-insumo"
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var FrmData=$("#form_insumo_new").serialize();

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
            volverBusqueda()
            alertNotificar(data.mensaje,"success");
            
        }, error:function (data) {
            console.log(data)

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})


function validaFormLab(){
    var cod_lab=$('#cod_lab').val()
    var desc_lab=$('#desc_lab').val()
   
    if(cod_lab==""){
        alertNotificar("Ingrese el codigo","error")
        $('#cod_lab').focus()
        return
    }
    
    if(desc_lab==""){
        alertNotificar("Ingrese la descripcion","error")
        $('#desc_lab').focus()
        return
    }

    $('#idbod').val($('#cmb_bodega').val())

    swal({
        title: "¿Desea ingresar la informacion?",
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
            $("#form_lab_new").submit()
        }
        sweetAlert.close();   // ocultamos la ventana de pregunta
    });
    
}


$("#form_lab_new").submit(function(e){
    e.preventDefault();
    vistacargando("m", "Espere por favor");  
    //comprobamos si es registro o edicion
  
    tipo="POST"
    url_form="guardar-lab"
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var FrmData=$("#form_lab_new").serialize();

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
            volverBusqueda()
            alertNotificar(data.mensaje,"success");
            
        }, error:function (data) {
            console.log(data)

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})


function validaFormItem(){
    var mat_of=$('#mat_of').val()
    var prese_of=$('#prese_of').val()
   
    if(mat_of==""){
        alertNotificar("Ingrese la descripcion","error")
        $('#mat_of').focus()
        return
    }
    if($('#cmb_bodega').val()==3 || $('#cmb_bodega').val()==4){
        if(prese_of==""){
            alertNotificar("Ingrese la presentacion","error")
            $('#prese_of').focus()
            return
        }
    }
    

    $('#idbodite').val($('#cmb_bodega').val())

    swal({
        title: "¿Desea ingresar la informacion?",
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
            $("#form_mat_new").submit()
        }
        sweetAlert.close();   // ocultamos la ventana de pregunta
    });
    
}


$("#form_mat_new").submit(function(e){
    e.preventDefault();
    vistacargando("m", "Espere por favor");  
    //comprobamos si es registro o edicion
  
    tipo="POST"
    url_form="guardar-item"
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var FrmData=$("#form_mat_new").serialize();

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
            volverBusqueda()
            alertNotificar(data.mensaje,"success");
            
        }, error:function (data) {
            console.log(data)

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})


function validaFormMedicinaDialisis(){
    var codigo_dialisis=$('#codigo_dialisis').val()
    var nombre_med_dialisis=$('#nombre_med_dialisis').val()
    var concentracion_med_dialisis=$('#concentracion_med_dialisis').val()
    var forma_med_dialisis=$('#forma_med_dialisis').val()
    var presentacion_med_dialisis=$('#presentacion_med_dialisis').val()
    var stock_min_dialisis=$('#stock_min_dialisis').val()
    var stock_cri_dialisis=$('#stock_cri_dialisis').val()
   
    if(codigo_dialisis==""){
        alertNotificar("Ingrese el codigo","error")
        $('#codigo_dialisis').focus()
        return
    }


    if(nombre_med_dialisis==""){
        alertNotificar("Ingrese el nombre","error")
        $('#nombre_med_dialisis').focus()
        return
    }

    if(concentracion_med_dialisis==""){
        alertNotificar("Ingrese la concentracion","error")
        $('#concentracion_med_dialisis').focus()
        return
    }

    if(forma_med_dialisis==""){
        alertNotificar("Ingrese la forma","error")
        $('#forma_med_dialisis').focus()
        return
    }
    if(presentacion_med_dialisis==""){
        alertNotificar("Ingrese la presentacion","error")
        $('#presentacion_med_dialisis').focus()
        return
    }

    if(stock_min_dialisis==""){
        alertNotificar("Ingrese el stock minimo","error")
        $('#stock_min_dialisis').focus()
        return
    }

    if(stock_cri_dialisis==""){
        alertNotificar("Ingrese el stock critico","error")
        $('#stock_cri_dialisis').focus()
        return
    }

    swal({
        title: "¿Desea ingresar el medicamento?",
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
            $("#form_med_dialisis").submit()
        }
        sweetAlert.close();   // ocultamos la ventana de pregunta
    });
    
}

$("#form_med_dialisis").submit(function(e){
    e.preventDefault();
    vistacargando("m", "Espere por favor");  
    //comprobamos si es registro o edicion
  
    tipo="POST"
    url_form="guardar-med-dialisis"
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var FrmData=$("#form_med_dialisis").serialize();

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
            volverBusqueda()
            alertNotificar(data.mensaje,"success");
            
        }, error:function (data) {
            console.log(data)

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})

function validaFormInsumoDialisi(){
    var cudim_dialisi=$('#cudim_dialisi').val()
    var esvay_ins_dia=$('#cod_esbay_ins_dial').val()
    var insumo_dialisi=$('#insumo_dialisi').val()
    var desc_ins_dialisi=$('#desc_ins_dialisi').val()
    var stock_cri_ins_dialisi=$('#stock_cri_ins_dialisi').val()
    var stock_min_ins_dialisi=$('#stock_min_ins_dialisi').val()

    if(cudim_dialisi==""){
        alertNotificar("Ingrese el cudim","error")
        $('#cudim_dialisi').focus()
        return
    }

    if(esvay_ins_dia==""){
        alertNotificar("Ingrese el codigo esbay","error")
        $('#cod_esbay_ins_dial').focus()
        return
    }
    
    if(insumo_dialisi==""){
        alertNotificar("Ingrese el insumo","error")
        $('#insumo_dialisi').focus()
        return
    }

    if(desc_ins_dialisi==""){
        alertNotificar("Ingrese el stock minimo","error")
        $('#desc_ins_dialisi').focus()
        return
    }

    if(stock_min_ins_dialisi==""){
        alertNotificar("Ingrese el stock minimo","error")
        $('#stock_min_ins_dialisi').focus()
        return
    }

    if(stock_cri_ins_dialisi==""){
        alertNotificar("Ingrese el stock critico","error")
        $('#stock_cri_ins_dialisi').focus()
        return
    }

    swal({
        title: "¿Desea ingresar el insumo dialisis?",
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
            $("#form_insumo_new_dial").submit()
        }
        sweetAlert.close();   // ocultamos la ventana de pregunta
    });
    
}

$("#form_insumo_new_dial").submit(function(e){
    e.preventDefault();
    vistacargando("m", "Espere por favor");  
    //comprobamos si es registro o edicion
  
    tipo="POST"
    url_form="guardar-ins-dialisis"
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var FrmData=$("#form_insumo_new_dial").serialize();

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
            volverBusqueda()
            alertNotificar(data.mensaje,"success");
            
        }, error:function (data) {
            console.log(data)

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})


function validaFormLabIns(){
    var cod_lab_ins=$('#cod_lab_ins').val()
    var desc_lab_ins=$('#desc_lab_ins').val()
   
    if(cod_lab_ins==""){
        alertNotificar("Ingrese el codigo","error")
        $('#cod_lab_lab').focus()
        return
    }
    
    if(desc_lab_ins==""){
        alertNotificar("Ingrese la descripcion","error")
        $('#desc_lab_ins').focus()
        return
    }

    $('#idbod_ins').val($('#cmb_bodega').val())

    swal({
        title: "¿Desea ingresar la informacion del laboratorio de dialisis?",
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
            $("#form_lab_ins__new").submit()
        }
        sweetAlert.close();   // ocultamos la ventana de pregunta
    });
    
}


$("#form_lab_ins__new").submit(function(e){
    e.preventDefault();
    vistacargando("m", "Espere por favor");  
    //comprobamos si es registro o edicion
  
    tipo="POST"
    url_form="guardar-lab-dialisis"
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var FrmData=$("#form_lab_ins__new").serialize();

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
            volverBusqueda()
            alertNotificar(data.mensaje,"success");
            
        }, error:function (data) {
            console.log(data)

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})

function nuevoProveedor(){
    limpiaProveedor()
    $('#modal_proveedor').modal('show')
}

function limpiaProveedor(){
    $('#ruc').val('')
    $('#contacto').val('')
    $('#empresa').val('')
    $('#telefono').val('')
    $('#email').val('')
}

function cerrarNuevoProveedor(){
    limpiaProveedor()
    $('#modal_proveedor').modal('hide')
}

function guardaProveedor(){
    var ruc=$('#ruc').val()
    var contacto=$('#contacto').val()
    var empresa=$('#empresa').val()
    
    if(ruc==""){
        alertNotificar("Ingrese el ruc","error")
        $('#ruc').focus()
        return
    }
    
    if(contacto==""){
        alertNotificar("Ingrese el contacto","error")
        $('#contacto').focus()
        return
    }

    if(empresa==""){
        alertNotificar("Ingrese la empresa","error")
        $('#empresa').focus()
        return
    }


    swal({
        title: "¿Desea ingresar la informacion del proveedor?",
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
            $("#form_new_proveedor").submit()
        }
        sweetAlert.close();   // ocultamos la ventana de pregunta
    });
    
}

$("#form_new_proveedor").submit(function(e){
    e.preventDefault();
    vistacargando("m", "Espere por favor");  
    //comprobamos si es registro o edicion
  
    tipo="POST"
    url_form="guardar-proveedor"
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var FrmData=$("#form_new_proveedor").serialize();

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
            cerrarNuevoProveedor()
            alertNotificar(data.mensaje,"success");
            cargaCombo()
            
        }, error:function (data) {
            console.log(data)

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})


function cargaCombo(){
    vistacargando("m","");
    $.get('carga-combo-bodega', function(data){
        console.log(data)
        vistacargando("")
        if(data.error==true){
			alertNotificar(data.mensaje,"error");
			return;   
		}
        $('#cmb_proveedor').html('');	
        $('#cmb_proveedor').find('option').remove().end();
        $('#cmb_proveedor').append('<option value="">Selecccione un  tipo</option>');
        $.each(data.resultado, function(i,item){
          			
            $('#cmb_proveedor').append('<option class="" value="'+item.idprov+'">'+item.ruc+' -- '+item.empresa+'</option>');
           
        })
         $("#cmb_proveedor").trigger("chosen:updated"); // actualizamos el combo 
          
        
    }).fail(function(){
        alertNotificar("Ocurrio un error","error");
        vistacargando("")
       
    }); 
}