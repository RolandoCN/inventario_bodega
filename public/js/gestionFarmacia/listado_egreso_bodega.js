function filtraEgreso(){
    let filtro_selecc=$('#busqueda_egreso_cmb').val()
    if(filtro_selecc==""){return}
    else if(filtro_selecc=="F"){
        $('#busqueda_fecha').show()
        $('#busqueda_paciente').hide()
        limpiarCampoBusqueda()
    }else{
        $('#busqueda_fecha').hide()
        $('#busqueda_paciente').show()
        limpiarCampoBusqueda()
    }
}

function limpiarCampoBusqueda(){
    $('#paciente_cmb').val('').trigger('change.select2')
    $('#bus_fecha_ini').val('')
    $('#bus_fecha_fin').val('')

}

function buscarPaciente(){
    $('#paciente_cmb').select2({
        placeholder: 'Seleccione una opción',
        ajax: {
        url: 'cargar-paciente',
        dataType: 'json',
        delay: 250,
        processResults: function (data) {
            return {
            results:  $.map(data, function (item) {
                    return {
                        text: item.documento +" -- "+item.nombre_paciente,
                        id: item.id_paciente
                    }
                })
            };
        },
        cache: true
        }
    });
}

function buscarEgresos(){

    let filtrar=$('#busqueda_egreso_cmb').val()
    if(filtrar==""){
        alertNotificar("Debe seleccionar una opcion", "error")
        return
    }

    let fecha_inicial=$('#bus_fecha_ini').val()
    let fecha_final=$('#bus_fecha_fin').val()
    let paciente_cmb=$('#paciente_cmb').val()
    
    if(filtrar=="F"){
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
        paciente_cmb=0
    }else if(filtrar=="P"){
        if(paciente_cmb==""){ 
            alertNotificar("Seleccione un paciente","error")
            return 
        }
        fecha_inicial=0
        fecha_final=0

    }
   
    
    $('#content_consulta').hide()
    $('#listado_permiso').show()
    

    $('#pac_body').html('');

    $("#tabla_egreso tbody").html('');

	$('#tabla_egreso').DataTable().destroy();
	$('#tabla_egreso tbody').empty(); 
    
    // limpiarCampos()
    var num_col = $("#tabla_egreso thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_egreso tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);


    $('#fecha_ini_rep').html('')
    $('#fecha_fin_rep').html('')

    $('#tituloCabecera').html(`<button type="buttton" onclick="cancelar()" class="btn btn-sm btn-danger">Atras</button> `)

    
    $.get('filtra-egreso-bod-farmacia/'+fecha_inicial+'/'+fecha_final+'/'+paciente_cmb, function(data){
        console.log(data)
        
        if(data.error==true){
			$("#tabla_egreso tbody").html('');
			$("#tabla_egreso tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
			alertNotificar(data.mensaje,"error");
            cancelar()
			return;   
		}
		if(data.error==false){
			if(data.resultado.length==0){
				$("#tabla_egreso tbody").html('');
				$("#tabla_egreso tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
				alertNotificar("No se encontró información","error");
                cancelar()
				return;
			}
			
			$("#tabla_egreso tbody").html('');
            $('#fecha_ini_rep').html(fecha_inicial)
            $('#fecha_fin_rep').html(fecha_final)
          
            
            let contador=0
			$.each(data.resultado,function(i, item){
                let disabled=""
                if(item.idbodega==6 || item.idbodega==7){
                    disabled=""
                }else{
                    disabled="disabled" 
                }

                let pac=item.documento+" "+ item.paciente                   
                if(item.documento==null){
                    pac=""
                }
				// $('#tabla_egreso').append(`<tr>
                //                                 <td style="width:15%; vertical-align:middle">
                //                                     ${item.descripcion} ${item.secuencial}
                                                    
                //                                 </td>

                //                                 <td style="width:10%;  text-align:left; vertical-align:middle">
                //                                     ${item.fecha_hora}
                //                                 </td>
                                               
                //                                 <td style="width:25%; text-align:left">
                //                                      ${item.observacion}
                                                  
                                                   
                //                                 </td>
                //                                 <td style="width:25%; text-align:left; vertical-align:middle">
                //                                     ${item.responsable}
                //                                 </td>
                                               
                //                                 <td style="width:10%; text-align:right; vertical-align:middle">
                //                                     $ ${item.total}
                //                                 </td>

                //                                 <td style="width:20%; text-align:center; vertical-align:middle">

                //                                     <button type="button" class="btn btn-xs btn-primary" onclick="imprimir('${item.idcomprobante}','${item.idbodega}','${item.idtipo_comprobante}')">Imprimir</button>

                //                                     <button type="button" ${disabled} class="btn btn-xs btn-success" onclick="descargarRollo('${item.idcomprobante}','${item.idbodega}')">Etiqueta</button>

                //                                    <button type="button" class="btn btn-xs btn-danger" onclick="DetalleOtros('${item.idcomprobante}','${item.idbodega}','${item.descripcion}','${item.secuencial}','${item.responsable}','${item.areasel}','${item.fecha_hora}','${item.nombre_bodega}','${item.id_usuario_ingresa}')">Revertir</button>

                                                   
                //                                 </td>

                                                
											
				// 						</tr>`);

                $('#tabla_egreso').append(`<tr>
                                                <td style="width:12%; vertical-align:middle">
                                                    ${item.descripcion} ${item.secuencial}
                                                    
                                                </td>

                                                <td style="width:7%;  text-align:left; vertical-align:middle">
                                                    ${item.fecha_hora}
                                                </td>
                                               
                                                <td style="width:20%; text-align:left">
                                                     ${item.observacion}
                                                  
                                                   
                                                </td>
                                                <td style="width:20%; text-align:left; vertical-align:middle">
                                                    ${item.responsable}
                                                </td>

                                                <td style="width:20%; text-align:left; vertical-align:middle">
                                                ${pac}
                                                </td>
                                               
                                                <td style="width:7%; text-align:right; vertical-align:middle">
                                                    $ ${item.total}
                                                </td>

                                                <td style="width:18%; text-align:center; vertical-align:middle">

                                                    <button type="button" class="btn btn-xs btn-primary" onclick="imprimir('${item.idcomprobante}','${item.idbodega}','${item.idtipo_comprobante}')">Imprimir</button>

                                                    <button type="button" ${disabled} class="btn btn-xs btn-success" onclick="descargarRollo('${item.idcomprobante}','${item.idbodega}')">Etiqueta</button>

                                                    <button type="button"  class="btn btn-xs btn-danger"onclick="DetalleOtros('${item.idcomprobante}','${item.idbodega}','${item.descripcion}','${item.secuencial}','${item.responsable}','${item.areasel}','${item.fecha_hora}','${item.nombre_bodega}','${item.id_usuario_ingresa}')">Ver</button>

                                                   
                                                </td>

                                                
											
										</tr>`);
			})
            if(contador>0){
                $('.btn_aprobacion').hide()
            }else{
                $('.btn_aprobacion').show()
            }
		  
			cargar_estilos_datatable('tabla_egreso');
		}
    })  

}

function descargarRollo(comprobanteSelecc,bodegaSelecc){
   
    vistacargando("m","Espere por favor")
    
    $.get("reporte-rollo/"+comprobanteSelecc+"/"+bodegaSelecc, function(data){
        console.log(data)
        vistacargando("")
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            return;   
        }
       
        // verpdf(data.pdf,'N')
        window.location.href="descargar-reporte/"+data.pdf

       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}


function verpdf(ruta){
    var iframe=$('#iframePdf');
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

function imprimir(id, bodega, tipo){
   
    vistacargando("m","Espere por favor")
      
    if(tipo=="16" || tipo=="17" || tipo=="18" || tipo=="8" || tipo=="19" || tipo=="20" || tipo=="23"){
       
        url_pdf="reporte-transferencia-bod-farm/"+id+"/"+bodega
    }else{
        url_pdf="reporte-egreso-bod-farmacia/"+id+"/"+bodega
        // url_pdf="reporte-transferencia-bod-farm/"+id+"/"+bodega
    }
    
    $.get(url_pdf, function(data){
        console.log(data)
        vistacargando("")
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            return;   
        }
        // alertNotificar("El documento se descargará en unos segundos...","success");
        // window.location.href="descargar-reporte/"+data.pdf
        verpdf(data.pdf)

       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
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
        order: [[ 1, "desc" ]],
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


function DetalleOtros(idcomprobante, idbodega, desc, secue, solicita, area, fecha, bodega, idusuaSoli){
    globalThis.bodegaSelecc=idbodega
    globalThis.comprobanteSelecc=idcomprobante

    $('.codigo_detalle').html('')
    $('.area_detalle').html('')
    $('.funcionario_detalle').html('')
    $('.fecha_detalle').html('')
    $('.despacha_detalle').html('')

    $("#tabla_detalle_pedido tbody").html('');
    vistacargando("m","Espere por favor") 
    $.get("detalle-pedidos-farm-todo/"+idcomprobante+"/"+idbodega, function(data){
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
        
        $.each(data.resultado,function(i, item){
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
                                           
                                            <td style="width:15%; text-align:center; vertical-align:middle">
                                            
                                                
                                                ${item.cantidad_entregada}
                                            </td>

                                            <td style="width:10%; text-align:left; vertical-align:middle">
                                                ${item.stock}
                                                <input type="hidden"id="stock_item${item.iddetalle}" style="width:100% !important;text-align:right" name="stock_item[]" value="${item.stock}" >
                                            </td>

                                            <td style="width:5%; text-align:left; vertical-align:middle">
                                               <button type="button" disabled class="btn btn-xs btn-info" onclick="verHistorial('${idusuaSoli}','${item.iditem}','${item.nombre_item}','${solicita}')">
                                                    <i class="fa fa-search"></i>
                                                </button>
                                            </td>


                                            
                                        
                                    </tr>`);
        })
        //volver()
        $('#modal_detalle').modal('show') 
        $('#seccion_revertir').hide()
        $('#seccion_detalle').show()
        
        $('.codigo_detalle').html(desc +" "+ secue)
        $('.area_detalle').html(area)
        $('.funcionario_detalle').html(solicita)
        $('.fecha_detalle').html(fecha)
        $('.despacha_detalle').html(data.info.responsable.persona.ape1 +" "+data.info.responsable.persona.ape2 +" "+data.info.responsable.persona.nom1 +" "+data.info.responsable.persona.nom2)

        $('#seccion_receta').hide()
        if(data.datosReceta!=0){
            $('#seccion_receta').show()
            $('.ci_paciente').html(data.datosReceta.cedula_paciente)
            
            $('.paciente_receta').html(data.datosReceta.paciente)

            if(data.datosReceta.descripcion_cie_10==" -- "){
                $('#cie_').hide()
            }else{
                $('#cie_').show()
                $('.cie_10_detalle').html(data.datosReceta.descripcion_cie_10)
            }
        }

        if(bodegaSelecc==3 || bodegaSelecc==4 || bodegaSelecc==5 || bodegaSelecc==9 || bodegaSelecc==10 || bodegaSelecc==12 ){
            $('#btn_imprimir_previo').addClass('ocultar_btn')
        }else{
            $('#btn_imprimir_previo').removeClass('ocultar_btn')
            PermitirMasEntrega='S'
            if(data.validaParametro==null){
                PermitirMasEntrega="N"
            }
        } 
       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}

function cerrarRevertit(){
    $('#modal_detalle').modal('hide') 
    cancelaRevertit()
}

function revertir(){
    $("#motivo_rev").val("")
    $('#seccion_detalle').hide()
    $('#seccion_revertir').show()
}

function cancelaRevertit(){
    $('#seccion_revertir').hide()
    $('#seccion_detalle').show()
}

function procesarReversion(){
    let mot=$("#motivo_rev").val()
    
    if(mot==""){
        alertNotificar("Ingrese una observacion","error")
        return
    }

    swal({
        title: "¿Desea revertir la solicitud?",
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
                url: "revertir-comprobante",
                data: { _token: $('meta[name="csrf-token"]').attr('content'),
                idcomp_rever:comprobanteSelecc,motivo:mot},
                success: function(data){
                    console.log(data)
                    vistacargando("");                
                    if(data.error==true){
                       
                        alertNotificar(data.mensaje,'error');
                        return;                      
                    }
                    alertNotificar(data.mensaje,"success");
                    $('#modal_detalle').modal('hide') 
                    buscarEgresos()
                    
                                    
                }, error:function (data) {
                    vistacargando("");
                    alertNotificar('Ocurrió un error','error');
                }
            });

        }
        sweetAlert.close();   // ocultamos la ventana de pregunta
    }); 
}   



