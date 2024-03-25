function buscarPedidos(){
    let fecha_inicial=$('#bus_fecha_ini').val()
    let fecha_final=$('#bus_fecha_fin').val()
    
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
    

    $('#pac_body').html('');

    $("#tabla_pedido tbody").html('');

	$('#tabla_pedido').DataTable().destroy();
	$('#tabla_pedido tbody').empty(); 
    
    // limpiarCampos()
    var num_col = $("#tabla_pedido thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_pedido tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);


    $('#fecha_ini_rep').html('')
    $('#fecha_fin_rep').html('')

    $('#tituloCabecera').html(`<button type="buttton" onclick="cancelar()" class="btn btn-sm btn-danger">Atras</button> `)

    
    $.get('filtra-pedido-paquete-sol/'+fecha_inicial+'/'+fecha_final, function(data){
        console.log(data)
        
        if(data.error==true){
			$("#tabla_pedido tbody").html('');
			$("#tabla_pedido tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
			alertNotificar(data.mensaje,"error");
            cancelar()
			return;   
		}
		if(data.error==false){
			if(data.resultado.length==0){
				$("#tabla_pedido tbody").html('');
				$("#tabla_pedido tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
				alertNotificar("No se encontró información","error");
                cancelar()
				return;
			}
			
			$("#tabla_pedido tbody").html('');
            $('#fecha_ini_rep').html(fecha_inicial)
            $('#fecha_fin_rep').html(fecha_final)
          
            
            let contador=0
			$.each(data.resultado,function(i, item){
                let estado=""
                if(item.codigo_old=="Pedido" || item.codigo_old=="PedidoAFarm"){
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

                                                    <button type="button" class="btn btn-xs btn-primary" onclick="Detalle('${item.idcomprobante}','${item.idbodega}','${item.descripcion}','${item.secuencial}','${item.solicita}','${item.area}','${item.fecha_hora}','${item.nombre_bodega}')">Detalle</button>

                                                    <button type="button" class="btn btn-xs btn-warning" onclick="Actualizar('${item.idcomprobante}','${item.idbodega}')">Actualizar</button>

                                                   
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

function regresarBusqueda(){
    $('#actualiza_seccion').hide()
    $('#listado_permiso').show()
    $('#tituloCabecera').html(`<button type="buttton" onclick="cancelar()" class="btn btn-sm btn-danger">Atras</button> `)
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


function Detalle(idcomprobante, idbodega, desc, secue, solicita, area, fecha, bodega){

    $('#codigo_detalle').html('')
    $('#area_detalle').html('')
    $('#funcionario_detalle').html('')
    $('#fecha_detalle').html('')

    $("#tabla_detalle_pedido tbody").html('');
    vistacargando("m","Espere por favor")
    $.get("paq-detalle-pedidos-sol/"+idcomprobante+"/"+idbodega, function(data){
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

            let cantidad_solicitada=0
            let cantidad_entregada=0
            let stock_=0
            $.each(item,function(i2, item2){
                cantidad_solicitada=Number(cantidad_solicitada) + Number(item2.cantidad_pedida);
                cantidad_entregada=Number(cantidad_entregada) + Number(item2.cantidad_entregada);
                stock_=item2.stock_disp
            })
           
            $('#tabla_detalle_pedido').append(`<tr>
                                          

                                            <td style="width:60%;  text-align:left; vertical-align:middle">
                                                ${i}
                                            </td>

                                            <td style="width:15%;  text-align:center; vertical-align:middle">
                                                ${cantidad_solicitada}
                                            </td>

                                            <td style="width:15%;  text-align:center; vertical-align:middle">
                                                ${cantidad_entregada}
                                            </td>

                                            <td style="width:10%;  text-align:center; vertical-align:middle">
                                                ${stock_}
                                            </td>
                                           
                                            
                                        
                                    </tr>`);
        })
        
        $('#modal_detalle').modal('show')
        
        $('#codigo_detalle').html(desc +" "+ secue)
        $('#area_detalle').html(area)
        $('#funcionario_detalle').html(solicita)
        $('#fecha_detalle').html(fecha)
       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}

function Actualizar(id, bodega){
   

    // window.location.href="actualizar-pedido/"+id+"/"+bodega;
    vistacargando("m","Espere por favor")
    $.get("actualizar-pedido-paquete/"+id+"/"+bodega, function(data){
        vistacargando("")     
        console.log(data)     
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            return;   
        }
        
    
        llenarTabla(data)
        $('#motivo').val(data.motivo)
       
        globalThis.idPaqueteEditar=id
       
        $('#actualiza_seccion').show()
        $('#listado_permiso').hide()
        $('#tituloCabecera').html('Actualizar')
      
      
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}

function cerrar(){
    $('#modal_detalle').modal('hide')
}

function llenarTabla(data){
    $('#tb_listaMedicamento').html('')
    let bodega=data.resultado.idbodega
    CantidadItem=1000

    // let cantida_paq=data.cantidad_pedida_paq
    let cantida_paq=""
    $.each(data.resultado,function(id_paquete,item){
        let paquete_txt=""
        $.each(item,function(i,item2){
            paquete_txt=item2.paquete.descripcion
            
          //  cantida_paq=item2.pedido.cant_x_paquete
            cantida_paq=item2.cantidad
        })
        

        let total_fila_=0
        total_fila_=item.cantidad *item.precio
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
            <input type="number"id="class_cantidad_${id_paquete}" style="width:100% !important;text-align:right" name="cantidad[]" onkeyup="tecla_cantidad(this,'${id_paquete}')"  onblur="validar_cantidad(this,'${id_paquete}')"  onclick="validar_cantidad(this,'${id_paquete}')" value="${cantida_paq}">
        </td> 

        <td width="8%" class="centrado">
            <center><button type="button"  data-toggle="tooltip" data-original-title="Detalle" class="btn btn-xs btn-success marginB0" onClick="detalle_paquete('${id_paquete}','${paquete_txt}','S')">
                <i class="fa fa-eye" >
                    
                </i> 
            </button></center>
        </td>  

     

         
    </tr>`);

   
    $('[data-toggle="tooltip"]').tooltip();
       
    })
       

}

function cancelarEgreso(){
    IdMedicActual=0;
    $("#motivo").val('')
    $("#tipo_ingreso_cmb").val('').change();
    $("#cmb_bodega").val('').change();
    $("#cmb_tipo_med").val('').change();
    $("#tb_listaMedicamento tr").html('');
    $('#tb_pie_TotalMedicamentos').html('');
    TipoBod=0
    regresarBusqueda()
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
    $.get("valida-paquete/"+id+"/"+cantidad_paq_ingr, function(data){
        
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
                    url: 'json/datatables/spanish.json',
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
            
            $('#cmb_paquete').val('').change()
        }

     

       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
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
            <input type="number"id="class_cantidad_${id_paquete}" style="width:100% !important;text-align:right" name="cantidad[]" onkeyup="tecla_cantidad(this,'${id_paquete}')"  onblur="validar_cantidad(this,'${id_paquete}')"  onclick="validar_cantidad(this,'${id_paquete}')" value="1">
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
   
    var motivo=$("#motivo").val()
    var nfilas=$("#tb_listaMedicamento tr").length;

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
        title: "¿Desea actualizar la solicitud?",
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
        tipo="PUT"
        url_form="actualizar-pedido-dialisis-farmacia/"+idPaqueteEditar
    }else{
        tipo="PUT"
        url_form="actualizar-pedido/"+idPaqueteEditar
    }
    alert(url_form)
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
            cancelarEgreso()
            alertNotificar(data.mensaje,"success");
            
        }, error:function (data) {
            console.log(data)

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})