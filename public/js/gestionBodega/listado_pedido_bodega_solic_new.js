globalThis.PrecioItem=0
globalThis.CantidadItem=0
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

    
    $.get('filtra-pedido-bod-gral-sol/'+fecha_inicial+'/'+fecha_final, function(data){
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
                }else if(item.codigo_old=="Anulado"){
                    estado="Anulado"
                }
                else{
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

                                                    <button type="button" class="btn btn-xs btn-warning" onclick="Actualizar('${item.idcomprobante}','${item.idbodega}','${item.guarda_detalle_pedido}')">Actualizar</button>

                                                   
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

function cargaCombo(data, bode){
    
    $('#cmb_bodega').html('');	
    $('#cmb_bodega').find('option').remove().end();
    $('#cmb_bodega').append('<option value="">Selecccione un  tipo</option>');
    $.each(data, function(i,item){
        console.log(item)
        if(bode==item.idbodega){
            $('#cmb_bodega').append('<option class="" selected value="'+item.idbodega+'">'+item.nombre+'</option>');
        }else{
            $('#cmb_bodega').append('<option class=""  value="'+item.idbodega+'">'+item.nombre+'</option>');
        }		
        
        
    })
    $("#cmb_bodega").trigger("chosen:updated"); // actualizamos el combo 
    $("#cmb_bodega").prop('disabled', true)
        
    $("#bodega_seleccionda").val(bode)
   
}
globalThis.BodegaSeleccionada=""
function Actualizar(id, bodega, info_pedido){
   
    BodegaSeleccionada=bodega
   
  
    vistacargando("m","Espere por favor")
    $.get("actualizar-pedido-new/"+id+"/"+bodega, function(data){
        vistacargando("")     
        console.log(data)     
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            return;   
        }
        if(info_pedido!="S"){
            alertNotificar("No se puede actualizar la informacion","error")
            return
        }
        //laboratorio
        if(bodega==8 || bodega==13 || bodega==14 || bodega==19 || bodega==23 || bodega==24 || bodega==22 || bodega==25 || bodega==26 || bodega==27 || bodega==28 || bodega==29) {
            llenarTablaLab(data)
        }else if(bodega==30){
            llenarTablaProtecc(data)
        }else if(bodega==2 || bodega==7|| bodega==18 || bodega==21){
            llenarTablaInsumo(data)
        }else if(bodega==1 || bodega==17 || bodega==20 || bodega==6){
            llenarTablaMedicamentos(data)
        }else{
            llenarTabla(data)
        }
        $('#motivo').val(data.resultado.observacion)
       
        cargaCombo(data.bodega, bodega)

        globalThis.idComprobanteActualizar=id
       
        $('#actualiza_seccion').show()
        $('#listado_permiso').hide()
        $('#tituloCabecera').html('Actualizar')
      
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}

function Eliminar(id_permiso){
    
    if(confirm('¿Quiere eliminar el registro?')){
        vistacargando("m","Espere por favor")
        $.get("eliminar-permiso/"+id_permiso, function(data){
            vistacargando("")          
            if(data.error==true){
                alertNotificar(data.mensaje,"error");
                return;   
            }
    
            alertNotificar(data.mensaje,"success");
            buscarPedidos()
            
        }).fail(function(){
            vistacargando("")
            alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
        });
    }
       
    
}
function imprimir(){

}


function Detalle(idcomprobante, idbodega, desc, secue, solicita, area, fecha, bodega){
   
    $('#codigo_detalle').html('')
    $('#area_detalle').html('')
    $('#funcionario_detalle').html('')
    $('#fecha_detalle').html('')
    $('#user_anula').html('')
    $('#detalle_anula').html('')

    $("#tabla_detalle_pedido tbody").html('');
    vistacargando("m","Espere por favor")
    $.get("detalle-pedidos-sol-new/"+idcomprobante+"/"+idbodega, function(data){
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
        var anulado=0;
      
        $.each(data.resultado,function(i, item){
            var cant_ent=""
            if(item.cantidad_entregada==null){
                cant_ent=""
            }else{
                cant_ent=item.cantidad_entregada
            }

            let lote_=""
            if(item.lote==null || item.lote=="null"){
                lote_=""
            }else{
                lote_=item.lote
            }

            let fecha_cad_=""
            if(item.fecha_caducidad==null || item.fecha_caducidad=="null"){
                fecha_cad_=""
            }else{
                fecha_cad_=item.fecha_caducidad
            }

            if(item.detalle_anula!=null){
                anulado=anulado+1;                
            }

            var stock=""
            if(item.stock==null){
                stock=""
            }else{
                stock=item.stock
            }

            $('#tabla_detalle_pedido').append(`<tr>
                                            <td style="width:35%; vertical-align:middle">
                                                ${item.nombre_item}

                                                <input type="hidden"id="iddetalle_comp${item.iddetalle}" style="width:100% !important;text-align:right" name="iddetalle_comp[]" value="${item.iddetalle}" >
                                                
                                            </td>

                                            <td style="width:10%;  text-align:center; vertical-align:middle">
                                                ${lote_}
                                            </td>
                                           
                                            <td style="width:15%; text-align:center; vertical-align:middle">
                                                ${item.cantidad_pedida}

                                               
                                            </td>

                                            <td style="width:15%; text-align:center; vertical-align:middle">
                                                ${cant_ent}

                                            
                                            </td>
                                            <td style="width:15%; text-align:center; vertical-align:middle">
                                                ${fecha_cad_}
                                            </td>
                                           

                                            <td style="width:10%; text-align:left; vertical-align:middle">
                                                ${stock}
                                                <input type="hidden"id="stock_item${item.iddetalle}" style="width:100% !important;text-align:right" name="stock_item[]" value="${item.stock}" >
                                            </td>


                                            
                                        
                                    </tr>`);
        })
        
        $('#modal_detalle').modal('show')
        
        $('#codigo_detalle').html(desc +" "+ secue)
        $('#area_detalle').html(area)
        $('#funcionario_detalle').html(solicita)
        $('#fecha_detalle').html(fecha)
        $('.anula_seccion').hide()
        if(anulado>0){
            $('.anula_seccion').show()
            $('#user_anula').html(data.resultado[0].anulador)
            $('#detalle_anula').html(data.resultado[0].detalle_anula)
        }
       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
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


//cierra la modal detalle
function cerrar(){
    $('#modal_detalle').modal('hide')
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
    
    console.log("sas")
    console.log(retval)

    console.log(array_validado_1)

    var iddetalle_comp=0
    var array_iddetalle=[]
    $.each(retval,function(i, item){
        console.log(item)
        var valor=item.split('-')
        array_iddetalle.push(valor[1]);
    })

    console.log(array_iddetalle)
    
    

    console.log(array_validado_1)
    if(comprobar==0){
        alertNotificar("Debe por lo menos ingresar la cantidad validada de un item correctamente")
        return
    }

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
                url: 'validar-pedido-solicitado',
                data: { _token: $('meta[name="csrf-token"]').attr('content'),
                array_iddetalle:array_iddetalle,cantidad_validada:array_validado_1},
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

//items
function llenarTabla(data){
    $('#tb_listaMedicamento').html('')
    let bodega=data.resultado.idbodega
    CantidadItem=1000
    $.each(data.resultado.detalle_pedido,function(i,item){
        
       
        $('#tb_listaMedicamento').append(`<tr id="medicamentos_${item.id_item}">

            <td width="5%" class="centrado"> 
                <button type="button" style="margin-right:1px !important" data-toggle="tooltip" data-original-title="Eliminar" class="btn btn-xs btn-danger marginB0" onClick="eliminar_material(${item.id_item})">
                    <i class="fa fa-trash" >
                        
                    </i> 
                </button>


            </td>   

            <td width="27%" class="centrado">
                <input type="hidden" name="idmedicina_selecc[]" id="idmedicina_selecc${item.id_item}" value="${item.id_item}">
                <input type="hidden" name="idbodega_selecc[]" id="idbodega_selecc${item.id_item}" value="${bodega}">
                <input type="hidden" name="nombrematerial[]" id="nombre_medicina_${item.id_item}" value='${item.itemlab.descri}'>
            
                ${item.itemlab.descri}
            </td> 
            <td width="8%" class="centrado">
                <input type="number"id="class_cantidad_${item.id_item}" style="width:100% !important;text-align:right" name="cantidad[]" onkeyup="tecla_cantidad(this,'${item.id_item}')"  onblur="validar_cantidad(this,'${item.id_item}')" placeholder="${item.cantidad}" value="${item.cantidad}">
            </td> 

            
        </tr>`);
       
    })
       
   
    calculaTotalIngreso()




}
//proteccion
function llenarTablaProtecc(data){
    $('#tb_listaMedicamento').html('')
    let bodega=data.resultado.idbodega
    CantidadItem=1000
    $.each(data.resultado.detalle_pedido,function(i,item){
        console.log(item.id_item)
        $('#tb_listaMedicamento').append(`<tr id="medicamentos_${item.id_item}">

            <td width="5%" class="centrado"> 
                <button type="button" style="margin-right:1px !important" data-toggle="tooltip" data-original-title="Eliminar" class="btn btn-xs btn-danger marginB0" onClick="eliminar_material(${item.id_item})">
                    <i class="fa fa-trash" >
                        
                    </i> 
                </button>


            </td>   

            <td width="27%" class="centrado">
                <input type="hidden" name="idmedicina_selecc[]" id="idmedicina_selecc${item.id_item}" value="${item.id_item}">
                <input type="hidden" name="idbodega_selecc[]" id="idbodega_selecc${item.id_item}" value="${bodega}">
                <input type="hidden" name="nombrematerial[]" id="nombre_medicina_${item.id_item}" value='${item.itemproteccion.descri}'>
            
                ${item.itemproteccion.descri} 
            </td> 
            <td width="8%" class="centrado">
                <input type="number"id="class_cantidad_${item.id_item}" style="width:100% !important;text-align:right" name="cantidad[]" onkeyup="tecla_cantidad(this,'${item.id_item}')"  onblur="validar_cantidad(this,'${item.id_item}')" placeholder="${item.cantidad}" value="${item.cantidad}">
            </td> 


          
            
        </tr>`);
       
    })
       
   
    calculaTotalIngreso()




}

//laboratio
function llenarTablaLab(data){
    $('#tb_listaMedicamento').html('')
    let bodega=data.resultado.idbodega
    CantidadItem=1000
    $.each(data.resultado.detalle_pedido,function(i,item){
       
        $('#tb_listaMedicamento').append(`<tr id="medicamentos_${item.id_item}">

            <td width="5%" class="centrado"> 
                <button type="button" style="margin-right:1px !important" data-toggle="tooltip" data-original-title="Eliminar" class="btn btn-xs btn-danger marginB0" onClick="eliminar_material(${item.id_item})">
                    <i class="fa fa-trash" >
                        
                    </i> 
                </button>


            </td>   

            <td width="27%" class="centrado">
                <input type="hidden" name="idmedicina_selecc[]" id="idmedicina_selecc${item.id_item}" value="${item.id_item}">
                <input type="hidden" name="idbodega_selecc[]" id="idbodega_selecc${item.id_item}" value="${bodega}">
                <input type="hidden" name="nombrematerial[]" id="nombre_medicina_${item.id_item}" value='${item.laboratorio.descri}'>
            
                ${item.laboratorio.descri} 
            </td> 
            <td width="8%" class="centrado">
                <input type="number"id="class_cantidad_${item.id_item}" style="width:100% !important;text-align:right" name="cantidad[]" onkeyup="tecla_cantidad(this,'${item.id_item}')"  onblur="validar_cantidad(this,'${item.id_item}')" placeholder="${item.cantidad}" value="${item.cantidad}">
            </td> 

            
        </tr>`);
       
    })
       
   
    calculaTotalIngreso()




}

function llenarTablaInsumo(data){
    if(data.resultado.paciente){
        $('#paciente').val(data.resultado.paciente.apellido1+" "+data.resultado.paciente.apellido2+" "+data.resultado.paciente.nombre1+" "+data.resultado.paciente.nombre2)
    }

    $('#tb_listaMedicamento').html('')
    let bodega=data.resultado.idbodega
    CantidadItem=1000
    $.each(data.resultado.detalle_pedido,function(i,item){
        

        $('#tb_listaMedicamento').append(`<tr id="medicamentos_${item.id_item}">

            <td width="5%" class="centrado"> 
                <button type="button" style="margin-right:1px !important" data-toggle="tooltip" data-original-title="Eliminar" class="btn btn-xs btn-danger marginB0" onClick="eliminar_material(${item.id_item})">
                    <i class="fa fa-trash" >
                        
                    </i> 
                </button>


            </td>   

            <td width="27%" class="centrado">
                <input type="hidden" name="idmedicina_selecc[]" id="idmedicina_selecc${item.id_item}" value="${item.id_item}">
                <input type="hidden" name="idbodega_selecc[]" id="idbodega_selecc${item.id_item}" value="${bodega}">
                <input type="hidden" name="nombrematerial[]" id="nombre_medicina_${item.id_item}" value='${item.insumo.insumo}'>
            
                ${item.insumo.insumo}
            </td> 
            <td width="8%" class="centrado">
                <input type="number"id="class_cantidad_${item.id_item}" style="width:100% !important;text-align:right" name="cantidad[]" onkeyup="tecla_cantidad(this,'${item.id_item}')"  onblur="validar_cantidad(this,'${item.id_item}')" placeholder="${item.cantidad}" value="${item.cantidad}">
            </td> 

           
            
        </tr>`);
       
    })
       
   
    calculaTotalIngreso()




}


function llenarTablaMedicamentos(data){
  
    $('#tb_listaMedicamento').html('')
    let bodega=data.resultado.idbodega
    CantidadItem=1000
    $.each(data.resultado.detalle_pedido,function(i,item){
       
        $('#tb_listaMedicamento').append(`<tr id="medicamentos_${item.id_item}">


            <td width="5%" class="centrado"> 
                <button type="button" style="margin-right:1px !important" data-toggle="tooltip" data-original-title="Eliminar" class="btn btn-xs btn-danger marginB0" onClick="eliminar_material(${item.id_item})">
                    <i class="fa fa-trash" >
                        
                    </i> 
                </button>


            </td>   
        
            <td width="27%" class="centrado">
                <input type="hidden" name="idmedicina_selecc[]" id="idmedicina_selecc${item.id_item}" value="${item.id_item}">
                <input type="hidden" name="idbodega_selecc[]" id="idbodega_selecc${item.id_item}" value="${bodega}">
                <input type="hidden" name="nombrematerial[]" id="nombre_medicina_${item.id_item}" value='${item.item.nombre}'>
               
                ${item.item.nombre} ${item.item.concentra} ${item.item.forma} ${item.item.presentacion}
            </td> 
            <td width="8%" class="centrado">
                <input type="number"id="class_cantidad_${item.id_item}" style="width:100% !important;text-align:right" name="cantidad[]" onkeyup="tecla_cantidad(this,'${item.id_item}')"  onblur="validar_cantidad(this,'${item.id_item}')" placeholder="${item.cantidad}" value="${item.cantidad}">
            </td> 

           
        </tr>`);
       
    })
       
   
    calculaTotalIngreso()




}

function validar_precio(e, id){
    calcularTotalFila(id)
}
function calcularTotalFila(id){
   
    var valor_cantidad=$('#class_cantidad_'+id).val();   
    
    var valor_precio=$('#class_precio_'+id).val(); 
  
    if(valor_precio=="" || valor_cantidad==""){
        return
    }
   
    var total_fila=0

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

    var id=IdMedicActual;
    var valor_cantidad=$('#class_cantidad_'+id).val();  
    var valor_precio=$('#class_precio_'+id).val();  
  

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
      
    globalThis.AccionForm="A"
    globalThis.bod=cmb_bodega
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
        tipo="POST"
        url_form="guardar-pedido-bodega-area"
    }else{
        tipo="PUT"
        if(bod==8 || bod==13 || bod==14 || bod==19 || bod==23 || bod==24){
            url_form="actualizar-pedido-bodega-farm-laborat-new/"+idComprobanteActualizar
        }else if(bod==22 || bod==25 || bod==26 || bod==27 || bod==28 || bod==29){
           
            url_form="actualizar-pedido-bodega-farm-laborat-new/"+idComprobanteActualizar
        }else if(bod==18 || bod==21 || bod==7 || bod==2 ){
           
            url_form="actualizar-pedido-bodega-farm-laborat-new/"+idComprobanteActualizar
        }else if(bod==1 || bod==17 || bod==20 || bod==6){
          
            url_form="actualizar-pedido-bodega-farm-laborat-new/"+idComprobanteActualizar
        }else{
            url_form="actualizar-pedido-bodega-farm-laborat-new/"+idComprobanteActualizar
        }
       
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
                if(data.ent){
                    buscarPedidos()
                    cancelarEgreso()

                }
                return;                      
            }
           
            alertNotificar(data.mensaje,"success");
            buscarPedidos()
            cancelarEgreso()
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
    $("#tb_listaMedicamento tr").html('');
    $('#tb_pie_TotalMedicamentos').html('');
    TipoBod=0
    regresarBusqueda()
}

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
    
    if(bodega_selecc==1 || bodega_selecc==17 || bodega_selecc==6){
        url_busqueda="listado-medicamentos-lote/"+txt_item+"/"+bodega_selecc; 
    }else if(bodega_selecc==2 || bodega_selecc==18 || bodega_selecc==21 || bodega_selecc==7){
        url_busqueda="listado-insumos-lote/"+txt_item+"/"+bodega_selecc; 
    
    }else if(bodega_selecc==8 || bodega_selecc==13 || bodega_selecc==14 || bodega_selecc==19 || bodega_selecc==23 || bodega_selecc==24){
        url_busqueda="listado-lab-filtra/"+txt_item+"/"+bodega_selecc; 
    
    }else {
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
                datoItemArray.push({'idprod_':item.idprod,'nombres_':item.detalle});
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


//funcion para cuando selecciona un material del combo
function agg_medicamento(id_item,nombrex, bodega, cantidad, precio, felab, fcad, lote, rsanitario,idbodprod, permitir){

    if(permitir=="No"){
        alertNotificar("No se puede solicitar este item porque presenta inconsistencia en su stock","error")
        return
    }

    let filtrar_item = datosItem.filter(datos => datos.idprod_ == id_item );
    console.log(filtrar_item)

    let nombre=filtrar_item[0].nombres_

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

    if(felab!=""){
        // felab=felab.split('/')
        
        // felab=felab[2]+"-"+felab[1]+"-"+felab[0]
    }

    if(fcad!=""){
        // fcad=fcad.split('/')
        // fcad=fcad[2]+"-"+fcad[1]+"-"+fcad[0]
    
    }

    console.log(felab)
    CantidadItem=cantidad
    PrecioItem=precio

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
    IdMedicActual=id_item;
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


function validar_cantidad_click(e,id){
    var cant_max=$('#class_cantidad_pedida'+id).val()
    var cant_validada=$('#class_cantidad_validada-'+id).val()
    var stock=$("#class_cantidadmax_"+id).val()
    
    if(cant_validada<=0 && cant_validada!=""){
        alertNotificar("La cantidad validada debe ser mayor a cero", "error")
        $('#class_cantidad_validada-'+id).val('')
        $('#class_cantidad_validada-'+id).focus()
        return
    }

    if(parseFloat(cant_max)< parseFloat(cant_validada)){
        alertNotificar("La cantidad validada debe ser menor a "+cant_max, "error")
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

function eliminar_item(id){
    $('#medicamentos_'+id).remove();
    var nfilas=$("#tb_listaMedicamento tr").length;
    $('#cmb_item').val('').change()
    // if(nfilas==0){
    //     TipoBod=0
    // }   
   
}