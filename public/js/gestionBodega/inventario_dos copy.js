function buscarInventario(){
    
    let opcion=$('#cmb_opcion').val()
    
    if(opcion=="Individual"){
        dataIndividual()
    }else{
        dataGlobal()
    }

}

function dataGlobal(){
    let cmb_bodega=$('#cmb_bodega').val()
    let cmb_tipo=$('#cmb_tipo').val()
    let opcion=$('#cmb_opcion').val()
    if(cmb_bodega==""){ 
        alertNotificar("Seleccione una bodega","error")
        return 
    }

    if(cmb_tipo==""){ 
        alertNotificar("Seleccione un lugar","error")
        $('#bus_fecha_ini').focus()
        return 
    }

    if(opcion==""){ 
        alertNotificar("Seleccione un opcion","error")
        $('#bus_fecha_ini').focus()
        return 
    }


    $('#content_consulta').hide()
    $('#listado_global').show()
    
    $("#tabla_inventario_global tbody").html('');

	$('#tabla_inventario_global').DataTable().destroy();
	$('#tabla_inventario_global tbody').empty(); 
    
    var num_col = $("#tabla_inventario_global thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_inventario_global tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);


    $('#bodega_seleccionada').html('')
    $('#lugar_seleccionado').html('')

    $('#tituloCabecera').html(`<button type="buttton" onclick="cancelar()" class="btn btn-sm btn-danger">Atras</button> `)

    
    $.get('filtra-inventario/'+cmb_bodega+'/'+cmb_tipo, function(data){
        console.log(data)
        
        if(data.error==true){
			$("#tabla_inventario_global tbody").html('');
			$("#tabla_inventario_global tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
			alertNotificar(data.mensaje,"error");
            cancelar()
			return;   
		}
		if(data.error==false){
			if(data.resultado.length==0){
				$("#tabla_inventario_global tbody").html('');
				$("#tabla_inventario_global tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
				alertNotificar("No se encontró información","error");
                cancelar()
				return;
			}
			
			$("#tabla_inventario_global tbody").html('');

            var bodega_txt=$('#cmb_bodega option:selected').text()
            var lugar_txt=$('#cmb_tipo option:selected').text()

            $('#bodega_seleccionada').html(bodega_txt)
            $('#lugar_seleccionado').html(lugar_txt)
            let disabled=""
           
            if(cmb_bodega==1 || cmb_bodega==2 || cmb_bodega==8 || cmb_bodega==13 || cmb_bodega==14 ){
                disabled=""
                if(cmb_tipo=="FARMACIA"){
                    // disabled="disabled"
                }
            }else{
                // disabled="disabled"
            }
          
            
			$.each(data.resultado,function(i, item){
                if(item.existencia>=0){
                  
                    let codigo_item=""
                    if(item.codigo_item=="null" || item.codigo_item==null){
                        codigo_item=item.id_item
                    }else{
                        codigo_item=item.codigo_item
                    }
                    let precio=item.precio * 1
                    precio=precio.toFixed(2)

                    let estado=""
                    if(item.estado=="VERDADERO"){
                        estado="Activo" 
                        color_fila="color_activo"
                    }else{
                        estado="Inactivo"
                        color_fila="color_inactivo"
                    }

 				    $('#tabla_inventario_global').append(`<tr >
                                                <td style="width:10%; vertical-align:middle">
                                                    ${codigo_item} 
                                                    
                                                </td>

                                                <td style="width:50%;  text-align:left; vertical-align:middle">
                                                    ${item.detalle}
                                                </td>

                                                <td style="width:10%; text-align:center">
                                                     ${item.existencia}                                                  
                                                   
                                                </td>
                                               
                                               
                                                <td style="width:10%; text-align:right; vertical-align:middle">
                                                    $ ${item.precio}
                                                </td>

                                                <td style="width:10%; text-align:center; vertical-align:middle">
                                                    ${item.inconsis} 
                                                </td>

                                                <td style="width:10%; text-align:center; vertical-align:middle">
                                                    <button type="button" ${disabled} class="btn btn-primary btn-xs" onclick="verDetallado('${item.id_item}', '${cmb_bodega}','${cmb_tipo}','${item.existencia}')">Detalle</button>
                                                </td>
                                                
											
										</tr>`);
                }
			})
            
		  
			cargar_estilos_datatable('tabla_inventario_global');
		}
    }).fail(function(){
        cancelar()
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
        $("#tabla_inventario_global tbody").html('');
		$("#tabla_inventario_global tbody").html(`<tr><td colspan="${num_col}">Se produjo un error, por favor intentelo más tarde</td></tr>`);
    });  
}

function dataIndividual(){

    let cmb_bodega=$('#cmb_bodega').val()
    let cmb_tipo=$('#cmb_tipo').val()
    let opcion=$('#cmb_tcmb_opcionipo').val()
   
    if(cmb_bodega==""){ 
        alertNotificar("Seleccione una bodega","error")
        return 
    }

    if(cmb_tipo==""){ 
        alertNotificar("Seleccione un lugar","error")
        $('#bus_fecha_ini').focus()
        return 
    }

    if(opcion==""){ 
        alertNotificar("Seleccione un opcion","error")
        $('#bus_fecha_ini').focus()
        return 
    }

    $('#content_consulta').hide()
    $('#listado_individual').show()

    $("#tabla_inventario tbody").html('');

	$('#tabla_inventario').DataTable().destroy();
	$('#tabla_inventario tbody').empty(); 
    
    // limpiarCampos()
    var num_col = $("#tabla_inventario thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_inventario tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);


    $('#fecha_ini_rep').html('')
    $('#fecha_fin_rep').html('')

    $('#tituloCabecera').html(`<button type="buttton" onclick="cancelar()" class="btn btn-sm btn-danger">Atras</button> `)

    
    $.get('filtra-inventario2/'+cmb_bodega+'/'+cmb_tipo+'/'+opcion, function(data){
        console.log(data)
        
        if(data.error==true){
			$("#tabla_inventario tbody").html('');
			$("#tabla_inventario tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
			alertNotificar(data.mensaje,"error");
            cancelar()
			return;   
		}
		if(data.error==false){
			if(data.resultado.length==0){
				$("#tabla_inventario tbody").html('');
				$("#tabla_inventario tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
				alertNotificar("No se encontró información","error");
                cancelar()
				return;
			}
			
			$("#tabla_inventario tbody").html('');
            // $('#fecha_ini_rep').html(fecha_inicial)
            // $('#fecha_fin_rep').html(fecha_final)
          
            let fecha_actual=$('#fecha_actual').val()
           
            let contador=0
			$.each(data.resultado,function(i, item){
                // if(item.existencia>0){
                    let lote=""
                    if(item.lote=="null" || item.lote==null){
                        lote=""
                    }else{
                        lote=item.lote
                    }
                    
                    
                    // let fechaaux=item.fcaduca
                    // let fechaaux1=fechaaux.split('/')
                    // let fechaaux2=fechaaux1[2]+"-"+fechaaux1[1]+"-"+fechaaux1[0]
                    let fechaaux2=item.fcaduca
                    let fechaaux1=fechaaux2.split('-')
                  
                    console.log(fechaaux2)

                    let soloanio=fechaaux1[2]
                    let anioact=fecha_actual.split('/')

                    let caducado=""
                    let clase=""
                    if( (new Date(fecha_actual).getTime() >= new Date(fechaaux2).getTime())){
                        caducado="caducado"
                        clase="color_caducado"
                    }else{
                        if((new Date(anioact).getTime() >= new Date(soloanio).getTime()) && item.existencia >0)  {
                            
                            clase="color_x_caducar"
                        }
                        if( item.existencia <=0){
                            clase="color_rotura"
                        }
                    }

				    $('#tabla_inventario').append(`<tr class="${clase}">
                                                <td style="width:10%; vertical-align:middle">
                                                    ${item.codigo_item} 
                                                    
                                                </td>

                                                <td style="width:50%;  text-align:left; vertical-align:middle">
                                                    ${item.detalle}
                                                </td>

                                                <td style="width:10%;  text-align:left; vertical-align:middle">
                                                    ${lote}
                                                </td>
                                               
                                                <td style="width:10%; text-align:left">
                                                     ${item.existencia}
                                                  
                                                   
                                                </td>
                                                <td style="width:10%; text-align:left; vertical-align:middle">
                                                    ${item.fcaduca}
                                                </td>
                                               
                                                <td style="width:10%; text-align:right; vertical-align:middle">
                                                    $ ${item.precio}
                                                </td>
                                                
											
										</tr>`);
                // }
			})
            if(contador>0){
                $('.btn_aprobacion').hide()
            }else{
                $('.btn_aprobacion').show()
            }
		  
			cargar_estilos_datatable('tabla_inventario');
		}
    }).fail(function(){
        cancelar()
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
        $("#tabla_inventario tbody").html('');
		$("#tabla_inventario tbody").html(`<tr><td colspan="${num_col}">Se produjo un error, por favor intentelo más tarde</td></tr>`);
    });
}




//cierra la modal detalle
function cerrar(){
    $('#modal_detalle_producto').modal('hide')
}


function cargar_estilos_datatable(idtabla){
	$("#"+idtabla).DataTable({
		'paging'      : true,
		'searching'   : true,
		'ordering'    : true,
		'info'        : true,
		'autoWidth'   : true,
		"destroy":true,
        order: [[ 1, "asc" ]],
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
    $('#listado_individual').hide()
    $('#listado_global').hide()
    $('#form_actualiza').hide()
   
    $('html,body').animate({scrollTop:$('#arriba').offset().top},400);
    
}


function listado(){
    $('#tituloCabecera').html(`<button type="buttton" onclick="cancelar()" class="btn btn-sm btn-danger">Atras</button> `)
    $('#content_consulta').hide()
    $('#listado_individual').show()
    $('#form_actualiza').hide()
}

function verDetallado(iditem, bodega, tipo, total){

    if(bodega==9 || bodega==3 || bodega==4){

        $('#item_seleccionada').html('')
        $('#suma_seleccionada').html('')
        $('#total_seleccionado').html('')
        $('#resta_seleccionada').html('')

        $("#tabla_detallle_suma tbody").html('');

        $('#tabla_detallle_suma').DataTable().destroy();
        $('#tabla_detallle_suma tbody').empty(); 
        
        var num_col = $("#tabla_detallle_suma thead tr th").length; //obtenemos el numero de columnas de la tabla
        $("#tabla_detallle_suma tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);


        $('#tituloCabecera').html(`<button type="buttton" onclick="cancelar()" class="btn btn-sm btn-danger">Atras</button> `)

        $.get('detalle-inventario-item/'+bodega+'/'+tipo+'/'+iditem, function(data){
            console.log(data)
            
            if(data.tabla_detallle_suma==true){
                $("#tabla_detallle tbody").html('');
                $("#tabla_detallle_suma tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
                alertNotificar(data.mensaje,"error");
            
                return;   
            }
            if(data.error==false){
                if(data.resultado.length==0){
                    $("#tabla_detallle_suma tbody").html('');
                    $("#tabla_detallle_suma tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
                    alertNotificar("No se encontró información","error");
                    
                    return;
                }
                
                $("#tabla_detallle_suma tbody").html('');
               
            
                
                let total_item=0
                let total_suma=0
                let total_resta=0
                $.each(data.resultado,function(i, item){
                   
                        if(item.suma!="0"){
                            total_suma=Number(total_suma) + Number(item.suma)
                        }

                        if(item.resta!="0"){
                          
                            total_resta=Number(total_resta) + Number(item.resta)
                        }

                      

                        let precio=item.precio * 1
                        precio=precio.toFixed(2)
                        $('#tabla_detallle_suma').append(`<tr>
                                                    <td style="width:33%; vertical-align:middle;text-align:center">
                                                        ${item.fing} 
                                                        
                                                    </td>

                                                    <td style="width:33%;  text-align:center; vertical-align:middle">
                                                        ${item.suma}
                                                    </td>

                                                    <td style="width:33%;  text-align:center; vertical-align:middle">
                                                        ${item.resta}
                                                    </td>
                                                
                                                  
                                                
                                            </tr>`);
                    
                })
               
                $('#modal_detalle_producto_suma').modal('show')

                total_item= total_suma -total_resta

                $('#item_seleccionada').html(data.resultado[0].nombre_item)
                $('#suma_seleccionada').html(total_suma)
                $('#total_seleccionado').html(total_item)
                $('#resta_seleccionada').html(total_resta)
                
                cargar_estilos_datatable('tabla_detallle_suma');
            }
        }).fail(function(){
        
            vistacargando("")
            alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
            $("#tabla_detallle_suma tbody").html('');
            $("#tabla_detallle_suma tbody").html(`<tr><td colspan="${num_col}">Se produjo un error, por favor intentelo más tarde</td></tr>`);
        });   
    }

    else{
        $("#tabla_detallle tbody").html('');

        $('#tabla_detallle').DataTable().destroy();
        $('#tabla_detallle tbody').empty(); 
        
        var num_col = $("#tabla_detallle thead tr th").length; //obtenemos el numero de columnas de la tabla
        $("#tabla_detallle tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);


        $('#tituloCabecera').html(`<button type="buttton" onclick="cancelar()" class="btn btn-sm btn-danger">Atras</button> `)

        $.get('detalle-inventario-item/'+bodega+'/'+tipo+'/'+iditem, function(data){
            console.log(data)
            
            if(data.error==true){
                $("#tabla_detallle tbody").html('');
                $("#tabla_detallle tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
                alertNotificar(data.mensaje,"error");
            
                return;   
            }
            if(data.error==false){
                if(data.resultado.length==0){
                    $("#tabla_detallle tbody").html('');
                    $("#tabla_detallle tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
                    alertNotificar("No se encontró información","error");
                    
                    return;
                }
                
                $("#tabla_detallle tbody").html('');
               
                let contador=0
                let total_p=0
                $.each(data.resultado,function(i, item){
                    // if(item.existencia>=0){
                        let lote=""
                        if(item.lote=="null" || item.lote==null){
                            lote=""
                        }else{
                            lote=item.lote
                        }
                        let precio=item.precio * 1
                        precio=precio.toFixed(2)

                        total_p=Number(total_p)+ Number(item.existencia)
                        $('#tabla_detallle').append(`<tr>
                                                    <td style="width:10%; vertical-align:middle">
                                                        ${item.codigo_item} 
                                                        
                                                    </td>

                                                    <td style="width:50%;  text-align:left; vertical-align:middle">
                                                        ${item.detalle}
                                                    </td>

                                                    <td style="width:10%;  text-align:center; vertical-align:middle">
                                                        ${lote}
                                                    </td>
                                                
                                                    <td style="width:10%; text-align:right">
                                                      
                                                        <input type="number"id="class_valor_lote-${item.idbodprod}" step=""0.01" style="width:100% !important;text-align:right" name="valor_lote[]"   onblur="validar_lote(this,'${item.idbodprod}')" value="${item.existencia}" >
                                                    
                                                    
                                                    </td>
                                                    <td style="width:10%; text-align:center; vertical-align:middle">
                                                        ${item.fcaduca}
                                                    </td>
                                                
                                                    <td style="width:10%; text-align:right; vertical-align:middle">
                                                        $ ${precio}
                                                    </td>
                                                    
                                                
                                            </tr>`);
                    // }
                })

                var difer=total - total_p 
                var inco="No"
                if(total!=total_p){
                    inco="Si"
                }else{
                    inco="No"
                }
                $('#modal_detalle_producto').modal('show')

                $('#total_bodega').html(total)
                $('#inconsistencia').html(inco)
                $('#sumado').html(total_p)
                $('#diferencia').html(difer)

                globalThis.TotalItemSelecc=total

                cargar_estilos_datatable('tabla_detallle');
            }
        }).fail(function(){
        
            vistacargando("")
            alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
            $("#tabla_detallle tbody").html('');
            $("#tabla_detallle tbody").html(`<tr><td colspan="${num_col}">Se produjo un error, por favor intentelo más tarde</td></tr>`);
        });   
    }
    
}

globalThis.Actualizado=0
function validar_lote(){
    var valor_lote=[];
    var comprobar=0
    var retval = []
    var total_ingresado=0
    $("input[name='valor_lote[]']").each(function(indice, elemento) {
        
        if($(elemento).val()!=""){
            comprobar=comprobar+1
            valor_lote.push($(elemento).val());
            retval.push($(this).attr('id'))
            console.log($(elemento).val())
            total_ingresado=Number(total_ingresado) +Number ($(elemento).val())
           
        }
    });

   
    var array_bodprod=[]
    $.each(retval,function(i, item){
        console.log(item)
        var valor=item.split('-')
        array_bodprod.push(valor[1]);
    })

    console.log(array_bodprod)
    vistacargando("m", "Espere por favor")
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    $.ajax({
        type: "POST",
        url: 'actualiza-existencia-bodprod',
        data: { _token: $('meta[name="csrf-token"]').attr('content'),
        array_bodprod:array_bodprod,valor_lote:valor_lote},
        success: function(data){
            console.log(data)
            vistacargando("");                
            if(data.error==true){
                alertNotificar(data.mensaje,'error');
                return;                      
            }
            alertNotificar(data.mensaje,"success");

                var difer=TotalItemSelecc - total_ingresado
                var inco="No"
                if(TotalItemSelecc!=total_ingresado){
                    inco="Si"
                }else{
                    inco="No"
                }
                // $('#modal_detalle_producto').modal('show')
                Actualizado=1
                $('#total_bodega').html(TotalItemSelecc)
                $('#inconsistencia').html(inco)
                $('#sumado').html(total_ingresado)
                $('#diferencia').html(difer)
                   
        }, error:function (data) {
            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });

    // console.log(array_validado_1)
}


$('#modal_detalle_producto').on('hidden.bs.modal', function (event) {
    // do something...
        if(Actualizado==1){
            buscarInventario()
        }
            
    })
    