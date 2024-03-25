function buscarPedidos(){
    let fecha_inicial=$('#bus_fecha_ini').val()
    let fecha_final=$('#bus_fecha_fin').val()
    
    fecha_inicial="01-01-2023"
    fecha_final="f"
   
    if(fecha_inicial==""){ 
        alertNotificar("Seleccione una fecha inicial","error")
        return 
    }

    if(fecha_final==""){ 
        alertNotificar("Seleccione una fecha final","error")
        $('#bus_fecha_ini').focus()
        return 
    }

    if(fecha_inicial > fecha_final){
        alertNotificar("La fecha de inicio debe ser menor a la fecha final","error")
        $('#bus_fecha_ini').focus()
        return
    }
   
    
    $('#content_consulta').hide()
    $('#listado_permiso').show()
    

    $("#tabla_pedido tbody").html('');

	$('#tabla_pedido').DataTable().destroy();
	$('#tabla_pedido tbody').empty(); 
    
    // limpiarCampos()
    var num_col = $("#tabla_pedido thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_pedido tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);


    $('#fecha_ini_rep').html('')
    $('#fecha_fin_rep').html('')

    $('#tituloCabecera').html(`<button type="buttton" onclick="cancelar()" class="btn btn-sm btn-danger">Atras</button> `)

    
    $.get('filtra-pedido-bod-gral/'+fecha_inicial+'/'+fecha_final, function(data){
        console.log(data)
        
        if(data.error==true){
			$("#tabla_pedido tbody").html('');
			$("#tabla_pedido tbody").html(`<tr><td colspan="${num_col}"  class="text-center">No existen registros</td></tr>`);
			alertNotificar(data.mensaje,"error");
            //cancelar()
			return;   
		}
		if(data.error==false){
			if(data.resultado.length==0){
				$("#tabla_pedido tbody").html('');
				$("#tabla_pedido tbody").html(`<tr><td colspan="${num_col}" class="text-center">No existen registros</td></tr>`);
				// alertNotificar("No se encontró información","error");
                //cancelar()
				return;
			}
			
			$("#tabla_pedido tbody").html('');
            $('#fecha_ini_rep').html(fecha_inicial)
            $('#fecha_fin_rep').html(fecha_final)
          
            
            let contador=0
			$.each(data.resultado,function(i, item){
                let estado=""
                if(item.codigo_old=="Pedido"){
                    estado="Pedido"
                }else{
                    estado="Entregado"
                }
              
				$('#tabla_pedido').append(`<tr>
                                                <td style="width:10%; vertical-align:middle">
                                                    ${item.descripcion} ${item.secuencial}
                                                    
                                                </td>

                                                <td style="width:25%;  text-align:left; vertical-align:middle">
                                                    ${item.solicita}
                                                </td>
                                               
                                                <td style="width:10%; text-align:left">
                                                     ${item.area}
                                                  
                                                   
                                                </td>
                                                <td style="width:10%; text-align:left; vertical-align:middle">
                                                    ${item.fecha_hora}
                                                </td>
                                               
                                                <td style="width:25%; text-align:left; vertical-align:middle">
                                                     ${item.nombre_bodega}
                                                </td>

                                                <td style="width:10%; text-align:left; vertical-align:middle">
                                                     ${estado}
                                                </td>

                                                <td style="width:10%; text-align:left; vertical-align:middle">

                                                    <button type="button" class="btn btn-xs btn-primary" onclick="Detalle('${item.idcomprobante}','${item.idbodega}','${item.descripcion}','${item.secuencial}','${item.solicita}','${item.area}','${item.fecha_hora}','${item.nombre_bodega}','${item.id_usuario_ingresa}')"><i class="fa fa-shopping-cart"></i></button>

                                                    <button type="button" data-toggle="tooltip" data-original-title="Anular" class="btn btn-xs btn-danger" onclick="Anular('${item.idcomprobante}')"><i class="fa fa-trash"></i></button>

                                                   
                                                </td>

                                                
											
										</tr>`);
			})
            if(contador>0){
                $('.btn_aprobacion').hide()
            }else{
                $('.btn_aprobacion').show()
            }
		  
			cargar_estilos_datatable('tabla_pedido');
		}
    })  

}


function Anular(idcomprobante){
    $('#motivo_anula').val('')
    $('#modal_anula_comprobante').modal('show')
    globalThis.IdComprobanteAnula=idcomprobante
}

function ProcesaAnulacion(){
    var motivo=$('#motivo_anula').val()
    if(motivo=="" || motivo==null){
        alertNotificar("Ingrese el motivo de anulacion","error")
        return
    }

    swal({
        title: "¿Desea anular el comprobante?",
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

            vistacargando("m","Espere por favor");           

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            $.ajax({
                type: "POST",
                url: "anular-comprobante-bodega",
                data: { _token: $('meta[name="csrf-token"]').attr('content'),
                idComprobanteAnula:IdComprobanteAnula,motivo_anulacion:motivo},
                success: function(data){
                    console.log(data)
                    vistacargando("");                
                    if(data.error==true){
                       
                        alertNotificar(data.mensaje,'error');
                        return;                      
                    }
                    alertNotificar(data.mensaje,"success");
                    cancelarAnulacion()
                    buscarPedidos()
                    
                                    
                }, error:function (data) {
                    vistacargando("");
                    alertNotificar('Ocurrió un error','error');
                }
            });

        }
        sweetAlert.close();   // ocultamos la ventana de pregunta
    }); 
}

function cancelarAnulacion(){
    $('#modal_anula_comprobante').modal('hide')
}


function verpdf(ruta){
    var iframe=$('#iframePdf');
    // iframe.attr("src", "visualizardoc/RP_504_64b35bd288536b87917f53da0019a319.pdf");
    iframe.attr("src", "visualizardoc/"+ruta);   
    $("#vinculo").attr("href", 'descargar-reporte/'+ruta);
    $("#documentopdf").modal("show");
}

$('#documentopdf').on('hidden.bs.modal', function (e) {
     
    var iframe=$('#iframePdf');
    iframe.attr("src", null);

});

$('#descargar').click(function(){
    $('#documentopdf').modal("hide");
});

function ImprimirPrevio(){
   
    vistacargando("m","Espere por favor")
    
    $.get("reporte-previo-transferencia/"+comprobanteSelecc+"/"+bodegaSelecc, function(data){
        console.log(data)
        vistacargando("")
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            return;   
        }
       
        verpdf(data.pdf)
        // window.location.href="descargar-reporte/"+data.pdf

       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}

function imprimir(id, bodega){
    vistacargando("m","Espere por favor")
    
    $.get("reporte-transferencia-bod-farm/"+id+"/"+bodega, function(data){
        console.log(data)
        vistacargando("")
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            return;   
        }
       
        verpdf(data.pdf)
        // window.location.href="descargar-reporte/"+data.pdf

       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}


function imprimiar(id){
    window.location.href="reporte-transferencia-bod-farm/"+id
}


globalThis.ContadorStockGlobal=0;
function Detalle(idcomprobante, idbodega, desc, secue, solicita, area, fecha, bodega, idusuaSoli){
    globalThis.bodegaSelecc=idbodega
    globalThis.comprobanteSelecc=idcomprobante
    globalThis.PermitirMasEntrega="N";
  

    $('#codigo_detalle').html('')
    $('#area_detalle').html('')
    $('#funcionario_detalle').html('')
    $('#fecha_detalle').html('')
    $('#fecha_Actual').val('')

    $("#tabla_detalle_pedido tbody").html('');
    vistacargando("m","Espere por favor")
    $.get("detalle-pedidos-new/"+idcomprobante+"/"+idbodega, function(data){
        console.log(data)
        vistacargando("")
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            var num_col = $("#tabla_detalle_pedido thead tr th").length; //obtenemos el numero de columnas de la tabla
            $("#tabla_detalle_pedido tbody").html('');
			$("#tabla_detalle_pedido tbody").html(`<tr><td colspan="${num_col}">Ocurrio un error</td></tr>`);
            return;   
        }
        if(data.resultado.length==0){
            alertNotificar("El pedido ya fue entregado","error");
            var num_col = $("#tabla_detalle_pedido thead tr th").length; //obtenemos el numero de columnas de la tabla
            $("#tabla_detalle_pedido tbody").html('');
			$("#tabla_detalle_pedido tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
            return;   
        }

        $("#tabla_detalle_pedido tbody").html('');
        let contador_stock=0
        $.each(data.resultado,function(i, item){
            
            if(item.stock){
                contador_stock=contador_stock+1;
            }

            let fecha_cad=""
            if(item.fecha_caducidad==null || item.fecha_caducidad=="null"){
                fecha_cad=""
            }else{
                fecha_cad=item.fecha_caducidad
            }

            let lote_=""
            if(item.lote==null || item.lote=="null"){
                lote_=""
            }else{
                lote_=item.lote
            }

            let stock=""
            let disabled=""
            if(item.stock==null || item.stock=="null"){
                stock=""
                disabled="disabled"
            }else{
                stock=item.stock
                disabled=""
            }

              
            $('#tabla_detalle_pedido').append(`<tr>
                                            <td style="width:35%; vertical-align:middle">
                                                ${item.nombre_item}

                                                <input type="hidden"id="iddetalle_comp${item.iddetalle}" style="width:100% !important;text-align:right" name="iddetalle_comp[]" value="${item.iddetalle}" >
                                                
                                            </td>

                                            <td style="width:10%;  text-align:center; vertical-align:middle">
                                                ${lote_}
                                            </td>
                                           
                                            <td style="width:15%; text-align:center; vertical-align:middle"">
                                                ${item.cantidad_pedida}

                                                <input type="hidden"id="class_cantidad_pedida${item.iddetalle}" style="width:100% !important;text-align:right" name="cantidad_pedida[]" value="${item.cantidad_pedida}" >
                                              
                                               
                                            </td>
                                            <td style="width:15%; text-align:center; vertical-align:middle">
                                                ${fecha_cad}
                                            </td>
                                           
                                            <td style="width:15%; text-align:left; vertical-align:middle">
                                               
                                                <input type="number"id="class_cantidad_validada-${item.iddetalle}" ${disabled} style="width:100% !important;text-align:right" name="cantidad_validada[]" onkeyup="validar_cantidad(this,${item.iddetalle})" onblur="validar_cantidad(this,${item.iddetalle})" onclick="validar_cantidad_click(this,${item.iddetalle})" >
                                            </td>

                                            <td style="width:10%; text-align:left; vertical-align:middle">
                                                ${stock}
                                                <input type="hidden"id="stock_item${item.iddetalle}"  style="width:100% !important;text-align:right" name="stock_item[]" value="${item.stock}" >
                                            </td>

                                            <td style="width:5%; text-align:left; vertical-align:middle">
                                               <button type="button" class="btn btn-xs btn-info" onclick="verHistorial('${idusuaSoli}','${item.iditem}','${item.nombre_item}','${solicita}')">
                                                    <i class="fa fa-search"></i>
                                                </button>
                                            </td>


                                            
                                        
                                    </tr>`);
        })
        volver()
       
        
        $('#codigo_detalle').html(desc +" "+ secue)
        $('#area_detalle').html(area)
        $('#funcionario_detalle').html(solicita)
        $('#fecha_detalle').html(fecha)
        if(bodegaSelecc==3 || bodegaSelecc==4 || bodegaSelecc==5 || bodegaSelecc==9 || bodegaSelecc==10 || bodegaSelecc==12 ){
            $('#btn_imprimir_previo').addClass('ocultar_btn')
        }else{
            $('#btn_imprimir_previo').removeClass('ocultar_btn')
            PermitirMasEntrega='S'
            if(data.validaParametro==null){
                PermitirMasEntrega="N"
            }
        } 
        ContadorStockGlobal=contador_stock
        if(contador_stock==0){
            $('#btn_anular').removeClass('ocultar_btn');
        }else{
            $('#btn_anular').addClass('ocultar_btn');
        }
       
        $('#fecha_Actual').val(data.fecha_Actual)
       
        $('#modal_detalle').modal('show')

       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}

function anular(){
    $('#motivo_anulacion').val('')
    $('#seccion_anular').show()
    $('#seccion_detalle').hide()
}

function verHistorial(idusuario, iditem, nombreitem, soli){
    $("#tabla_detalle_historial tbody").html('');
    $('#tabla_detalle_historial').DataTable().destroy();
	$('#tabla_detalle_historial tbody').empty(); 
    vistacargando("Espere por favor")
    $.get("historial-pedido/"+idusuario+"/"+iditem, function(data){
        console.log(data)
        vistacargando("")
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            var num_col = $("#tabla_detalle_historial thead tr th").length; //obtenemos el numero de columnas de la tabla
            $("#tabla_detalle_historial tbody").html('');
			$("#tabla_detalle_historial tbody").html(`<tr><td colspan="${num_col}">Ocurrio un error</td></tr>`);
            return;   
        }
        if(data.resultado.length==0){
            alertNotificar("No se encontro historial")
            var num_col = $("#tabla_detalle_historial thead tr th").length; //obtenemos el numero de columnas de la tabla
            $("#tabla_detalle_historial tbody").html('');
			$("#tabla_detalle_historial tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
            return;   
        }

        $("#tabla_detalle_historial tbody").html('');
        
        $.each(data.resultado,function(i, item){
          
            $('#tabla_detalle_historial').append(`<tr>
                                            <td style="width10%; vertical-align:middle">
                                                ${i+1}

                                            </td>

                                            <td style="width:45%;  text-align:left; vertical-align:middle">
                                                <li><b>Cantidad:</b>${item.cantidad_pedida}</li>
                                                <li><b>Fecha:</b>${item.fecha_solicita}</li>
                                            </td>

                                            <td style="width:45%;  text-align:left; vertical-align:middle">
                                                <li><b>Cantidad:</b>${item.cantidad_entregada}</li>
                                                <li><b>Fecha:</b>${item.fecha_aprueba}</li>
                                                <li><b>Respons:</b>${item.respo}</li>
                                            </td>
                                           
                                          
                                    </tr>`);
        })

        cargar_estilos_datatable2('tabla_detalle_historial');
        $('#seccion_detalle').hide()
        $('#seccion_historial').show()
        $('#item_historial').html(nombreitem)
        
        $('#funcionario_historial').html(soli)

    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
   
}

function cargar_estilos_datatable2(idtabla){
	$("#"+idtabla).DataTable({
		'paging'      : true,
		'searching'   : true,
		'ordering'    : true,
		'info'        : true,
		'autoWidth'   : true,
		"destroy":true,
        order: [[ 0, "asc" ]],
		pageLength: 10,
		sInfoFiltered:false,
		language: {
			url: 'json/datatables/spanish.json',
		},
	}); 
	$('.collapse-link').click();
	$('.datatable_wrapper').children('.row').css('overflow','inherit !important');

	$('.table-responsive').css({'padding-top':'12px','padding-bottom':'12px', 'border':'0', 'overflow-x':'inherit'});	
}


function volver(){
    $('#seccion_detalle').show()
    $('#seccion_historial').hide()
    $('#seccion_anular').hide()
}

function anularPedidos(){
    var motivo=$('#motivo_anulacion').val()
    if(motivo==""){
        alertNotificar("Debe ingresar el motivo","error")
        return
    }

    swal({
        title: "¿Desea anular el pedido?",
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
            vistacargando("m","Espere por favor");           

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            $.ajax({
                type: "POST",
                url: "anular-comprobante-bodega",
                data: { _token: $('meta[name="csrf-token"]').attr('content'),
                motivo_anulacion:motivo,idComprobanteAnula:comprobanteSelecc},
                success: function(data){
                    console.log(data)
                    vistacargando("");                
                    if(data.error==true){
                      
                        alertNotificar(data.mensaje,'error');
                        return;                      
                    }
                    alertNotificar(data.mensaje,"success");
                    $('#modal_detalle').modal('hide')
                   
                    buscarPedidos()
                    
                                    
                }, error:function (data) {
                    vistacargando("");
                    alertNotificar('Ocurrió un error','error');
                }
            });
            
        }
        sweetAlert.close();   // ocultamos la ventana de pregunta
    }); 
}

//cierra la modal detalle
function cerrar(){
    $('#modal_detalle').modal('hide')
}

function tecla_cantidad(e, id){
   
}

function validar_cantidad(e, id){
    var cant_max=$('#class_cantidad_pedida'+id).val()
    var cant_validada=$('#class_cantidad_validada-'+id).val()
    var stock=$('#stock_item'+id).val()
    
    if(cant_validada<=0 && cant_validada!=""){
        alertNotificar("La cantidad validada debe ser mayor a cero", "error")
        $('#class_cantidad_validada-'+id).val('')
        $('#class_cantidad_validada-'+id).focus()
        return
    }

    if(parseFloat(cant_max)< parseFloat(cant_validada) && PermitirMasEntrega=="N"){
        alertNotificar("La cantidad validada debe ser menor o igual a "+cant_max, "error")
        $('#class_cantidad_validada-'+id).val('')
        $('#class_cantidad_validada-'+id).focus()
        return
    }
  
    if(parseFloat(cant_validada) > parseFloat(stock)){
        alertNotificar("La cantidad validada debe ser menor a "+stock, "error")
        $('#class_cantidad_validada-'+id).val('')
        $('#class_cantidad_validada-'+id).focus()
        return
    }
    comprobar()
}

function validar_cantidad_click(e,id){
    var cant_max=$('#class_cantidad_pedida'+id).val()
    var cant_validada=$('#class_cantidad_validada-'+id).val()
    var stock=$('#stock_item'+id).val()
    
    if(cant_validada<=0 && cant_validada!=""){
        alertNotificar("La cantidad validada debe ser mayor a cero", "error")
        $('#class_cantidad_validada-'+id).val('')
        $('#class_cantidad_validada-'+id).focus()
        return
    }

    // if(parseFloat(cant_max)< parseFloat(cant_validada)){
    //     alertNotificar("La cantidad validada debe ser menor a4 "+cant_max, "error")
    //     $('#class_cantidad_validada-'+id).val('')
    //     $('#class_cantidad_validada-'+id).focus()
    //     return
    // }
    if(parseFloat(cant_validada) > parseFloat(stock)){
        alertNotificar("La cantidad validada debe ser menor a "+stock, "error")
        $('#class_cantidad_validada-'+id).val('')
        $('#class_cantidad_validada-'+id).focus()
        return
    }
    comprobar()
}

function comprobar(){
    //
    var array_validado=[];
    var comprobar=0
    $("input[name='cantidad_validada[]']").each(function(indice, elemento) {
        array_validado.push($(elemento).val());
        if($(elemento).val()!=""){
            comprobar=comprobar+1
        }
    });
    if(comprobar>0){
        $('.btn_valida').prop('disabled',false)
    }else{
        $('.btn_valida').prop('disabled',true)
    }

}


function validar(){
    //
    var array_cant_pedida=[]
    $("input[name='cantidad_pedida[]']").each(function(indice, elemento) {
        array_cant_pedida.push($(elemento).val());
    });

    var array_validado_1=[];
    var comprobar=0
   
    var retval = []
    $("input[name='cantidad_validada[]']").each(function(indice, elemento) {
        
        if($(elemento).val()!=""){
            comprobar=comprobar+1
            array_validado_1.push($(elemento).val());
            retval.push($(this).attr('id'))

           
        }
    });

    var iddetalle_comp=0
    var array_iddetalle=[]
    $.each(retval,function(i, item){
        var valor=item.split('-')
        array_iddetalle.push(valor[1]);
    })

    
    if(comprobar==0){
        alertNotificar("Debe por lo menos ingresar la cantidad validada de un item correctamente")
        return
    }
    let url_aprobar=""
    //4 =>aseo
    //3=>oficina
    //30=>proteccion
    //9=>tics
    //10=>lenceria
    if(bodegaSelecc==4 || bodegaSelecc==3 || bodegaSelecc==5 || bodegaSelecc==30 || bodegaSelecc==9 || bodegaSelecc==10){
        url_aprobar="validar-pedido-solicitado-area"
    }else{
        url_aprobar="validar-pedido-solicitado"
    }
    var fecha_Act=$('#fecha_Actual').val()
    
    swal({
        title: "¿Desea validar los pedidos solicitados?",
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

            vistacargando("m","Espere por favor");           

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            $.ajax({
                type: "POST",
                url: url_aprobar,
                data: { _token: $('meta[name="csrf-token"]').attr('content'),
                array_iddetalle:array_iddetalle,cantidad_validada:array_validado_1,array_cant_pedida:array_cant_pedida,fecha_Act:fecha_Act},
                success: function(data){
                    console.log(data)
                    vistacargando("");                
                    if(data.error==true){
                       
                        if(data.act){
                            $('#modal_detalle').modal('hide')
                            swal(data.mensaje, "¡Ocurrió un error!", "error");
                            buscarPedidos()
                        }
                        alertNotificar(data.mensaje,'error');
                        return;                      
                    }
                    alertNotificar(data.mensaje,"success");
                    $('#modal_detalle').modal('hide')
                   
                    imprimir(comprobanteSelecc, bodegaSelecc)

                    buscarPedidos()
                    
                                    
                }, error:function (data) {
                    vistacargando("");
                    alertNotificar('Ocurrió un error','error');
                }
            });

        }
        sweetAlert.close();   // ocultamos la ventana de pregunta
    }); 

}


function cargar_estilos_datatable(idtabla){
	$("#"+idtabla).DataTable({
		'paging'      : true,
		'searching'   : true,
		'ordering'    : true,
		'info'        : true,
		'autoWidth'   : true,
		"destroy":true,
        order: [[ 3, "desc" ]],
		pageLength: 10,
		sInfoFiltered:false,
		language: {
			url: 'json/datatables/spanish.json',
		},
	}); 
	$('.collapse-link').click();
	$('.datatable_wrapper').children('.row').css('overflow','inherit !important');

	$('.table-responsive').css({'padding-top':'12px','padding-bottom':'12px', 'border':'0', 'overflow-x':'inherit'});	
}


function cancelar(){
    $('#tituloCabecera').html('Buscar')
    $('#content_consulta').show()
    $('#listado_permiso').hide()
    $('#form_actualiza').hide()
   
    $('html,body').animate({scrollTop:$('#arriba').offset().top},400);
    
}


function listado(){
    $('#tituloCabecera').html(`<button type="buttton" onclick="cancelar()" class="btn btn-sm btn-danger">Atras</button> `)
    $('#content_consulta').hide()
    $('#listado_permiso').show()
    $('#form_actualiza').hide()
}







