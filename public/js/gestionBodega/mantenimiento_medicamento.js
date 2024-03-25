
function cargar_estilos_datatable_detalle(idtabla){
	$("#"+idtabla).DataTable({
		'paging'      : false,
		'searching'   : false,
		'ordering'    : false,
		'info'        : false,
		'autoWidth'   : true,
		"destroy":true,
        // order: [[ 1, "asc" ]],
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

function cargar_estilos_datatable(idtabla){
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

function cargar_estilos_datatable_md(idtabla){
	$("#"+idtabla).DataTable({
		'paging'      : true,
		'searching'   : true,
		'ordering'    : true,
		'info'        : true,
		'autoWidth'   : true,
		"destroy":true,
        order: [[ 2, "asc" ]],
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

function cargar_estilos_datatable_md1(idtabla){
	$("#"+idtabla).DataTable({
        scrollCollapse: true,
        scroller: true,
        scrollY: 200
	}); 
	// $('.collapse-link').click();
	// $('.datatable_wrapper').children('.row').css('overflow','inherit !important');

	// $('.table-responsive').css({'padding-top':'12px','padding-bottom':'12px', 'border':'0', 'overflow-x':'inherit'});	
}

function listarMedGral(bodega=1){
    if(bodega==1 || bodega==2 ||bodega==8 || bodega==13 || bodega==14){
        var num_col = $("#tabla_inventario_global_md thead tr th").length; //obtenemos el numero de columnas de la tabla
        $("#tabla_inventario_global_md tbody").html('');

        $('#tabla_inventario_global_md').DataTable().destroy();
        $('#tabla_inventario_global_md tbody').empty(); 

        $("#tabla_inventario_global_md tbody").html('');
		$("#tabla_inventario_global_md tbody").html(`<tr><td colspan="${num_col}" class="text-center">No existen registros</td></tr>`);
        
        $('#content_consulta').hide()
        $('#listado_global').hide()
        $('#listado_global_med').show()
        globalThis.Bod_se=bodega
        $('#buscaItem').val('')

        let nombre_bod=""
        if(bodega==1){
            nombre_bod="Medicamentos"
        }else if(bodega==2){
            nombre_bod="Insumos"
        }else if(bodega==8){
            nombre_bod="Laboratorio Materiales"
        }else if(bodega==13){
            nombre_bod="Laboratorio Reactivo"
        }else if(bodega==14){
            nombre_bod="Laboratorio Microb"
        }

        BodeSelecc=bodega
        
        $('.bodega_seleccionada').html(nombre_bod)
        volverBusquedaMed()
        return
    }
    // if(bodega==8 || bodega==13 || bodega==14 || bodega==1 || bodega==2){
    // else if(bodega==8 || bodega==13 || bodega==14){
    //     dataGlobalMd(bodega,0)
    // }
    else{
        dataGlobal(bodega)
    }
    
}

$("#form_Fitltra").submit(function(e){
    e.preventDefault();
    Bod_se=1
    var txt_item=$('#buscaItem').val()
    var bodega_selecc=Bod_se
    
    if(txt_item===""){
        alertNotificar("Ingrese el nombre, codigo esbay o cudim del item","error")
        return
    }

    dataGlobalMd(bodega_selecc, txt_item)
})


globalThis.BodeSelecc=0
function dataGlobal(bodega){
    let cmb_bodega=bodega
    let cmb_tipo="BODEGA"
    BodeSelecc=bodega
    volverBusqueda()
 
    if(cmb_bodega==""){ 
        alertNotificar("Seleccione una bodega","error")
        return 
    }

    if(cmb_tipo==""){ 
        alertNotificar("Seleccione un lugar","error")
        $('#bus_fecha_ini').focus()
        return 
    }

    vistacargando("m","Espere por favor")
    $('#content_consulta').hide()
    $('#listado_global_med').hide()
    $('#listado_global').show()
    
    $("#tabla_inventario_global tbody").html('');

	$('#tabla_inventario_global').DataTable().destroy();
	$('#tabla_inventario_global tbody').empty(); 
    
    var num_col = $("#tabla_inventario_global thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_inventario_global tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);


    $('.bodega_seleccionada').html('')
    $('#lugar_seleccionado').html('')

    $('#tituloCabecera').html(`<button type="buttton" onclick="cancelar()" class="btn btn-sm btn-danger">Atras</button> `)

   
    $.get('listado-inventario/'+cmb_bodega+'/'+cmb_tipo, function(data){
       
        vistacargando("")
        let bod=""
        if(cmb_bodega==1){
            bod="Medicamento General"
        }else if(cmb_bodega==2){
            bod="Insumo General"
        }else if(cmb_bodega==13){
            bod="Laboratorio Reactivo"
        }else if(cmb_bodega==14){
            bod="Laboratorio Microb"
        }else if(cmb_bodega==8){
            bod="Laboratorio Materiales"
        }else if(cmb_bodega==3){
            bod="Oficina"
        }else if(cmb_bodega==4){
            bod="Aseo y Limp"
        }else if(cmb_bodega==5){
            bod="Herramienta"
        }else if(cmb_bodega==9){
            bod="Tics"
        }else if(cmb_bodega==10){
            bod="Lenceria"
        }else if(cmb_bodega==17){
            bod="Medicamento Dialisis"
        }else if(cmb_bodega==18){
            bod="Insumo Dialisis"
        }else if(cmb_bodega==19){
            bod="Laboratorio Dialisis"
        }else if(cmb_bodega==19){
            bod="Laboratorio Dialisis"
        }

        $('.bodega_seleccionada').html(bod)
       
        if(data.error==true){
			$("#tabla_inventario_global tbody").html('');
			$("#tabla_inventario_global tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
			alertNotificar(data.mensaje,"error");
         
			return;   
		}
		if(data.error==false){
			if(data.resultado.length==0){
				$("#tabla_inventario_global tbody").html('');
				$("#tabla_inventario_global tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
				alertNotificar("No se encontró información","error");
               
				return;
			}
			
			$("#tabla_inventario_global tbody").html('');
           
           
            let disabled=""
           
            if(cmb_bodega==1 || cmb_bodega==2 || cmb_bodega==8 || cmb_bodega==13 || cmb_bodega==14 ){
                disabled=""
                if(cmb_tipo=="FARMACIA"){
                    // disabled="disabled"
                }
            }else{
                //disabled="disabled"
            }
            
           
            datoItemArray=[]
			$.each(data.resultado,function(i, item){
                // if(item.existencia>=0){

                    datoItemArray.push({'idprod_':item.id_item,'nombres_':item.detalle});
                    globalThis.datosItem=datoItemArray;
                   
                    let cod=""
                  
                    if(item.codigo_item=='null' || item.codigo_item==null){
                        cod=item.id_item
                    }else{
                        cod=item.codigo_item
                    }
                   

 				    $('#tabla_inventario_global').append(`<tr >
                                                <td style="width:10%; vertical-align:middle">
                                                    ${cod}
                                                    
                                                </td>

                                                <td style="width:40%;  text-align:left; vertical-align:middle">
                                                    ${item.detalle}
                                                </td>

                                                <td style="width:10%; text-align:center; vertical-align:middle">
                                                    <button type="button" ${disabled} class="btn btn-primary btn-xs" onclick="verDetallado('${item.id_item}', '${cmb_bodega}','${cmb_tipo}','${item.existencia}')">Editar</button>

                                                    <button type="button" ${disabled} class="btn btn-info btn-xs" onclick="verAcceso('${item.id_item}', '${cmb_bodega}')">Parametros</button>
                                                </td>

                                                
											
										</tr>`);
                // }
			})
            
		  
			cargar_estilos_datatable('tabla_inventario_global');
		}
    }).fail(function(){
      
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
        $("#tabla_inventario_global tbody").html('');
		$("#tabla_inventario_global tbody").html(`<tr><td colspan="${num_col}">Se produjo un error, por favor intentelo más tarde</td></tr>`);
    });  

}



function dataGlobalMd(bodega=1,txt){
    let cmb_bodega=bodega

    let cmb_tipo="BODEGA"
    BodeSelecc=bodega
    volverBusqueda()
 
    if(cmb_bodega==""){ 
        alertNotificar("Seleccione una bodega","error")
        return 
    }

    if(cmb_tipo==""){ 
        alertNotificar("Seleccione un lugar","error")
        $('#bus_fecha_ini').focus()
        return 
    }

    vistacargando("m","Espere por favor")
    $('#content_consulta').hide()
    $('#listado_global').hide()
    $('#listado_global_med').show()
    
    $("#tabla_inventario_global_md tbody").html('');

	$('#tabla_inventario_global_md').DataTable().destroy();
	$('#tabla_inventario_global_md tbody').empty(); 
    
    var num_col = $("#tabla_inventario_global_md thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_inventario_global_md tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);


    $('.bodega_seleccionada').html('')
    $('#lugar_seleccionado').html('')

    $('#tituloCabecera').html(`<button type="buttton" onclick="cancelar()" class="btn btn-sm btn-danger">Atras</button> `)

    let url_bus=""
    if(txt==0){
        url_bus='listado-inventario/'+cmb_bodega+'/'+cmb_tipo
    }else{
        url_bus='filtra-listado-inventario/'+cmb_bodega+'/'+cmb_tipo+'/'+txt
    }
    $.get(url_bus, function(data){
       
        vistacargando("")
        let bod=""
        if(cmb_bodega==1){
            bod="Medicamento General"
        }else if(cmb_bodega==2){
            bod="Insumo General"
        }else if(cmb_bodega==13){
            bod="Laboratorio Reactivo"
        }else if(cmb_bodega==14){
            bod="Laboratorio Microb"
        }else if(cmb_bodega==8){
            bod="Laboratorio Materiales"
        }else if(cmb_bodega==3){
            bod="Oficina"
        }else if(cmb_bodega==4){
            bod="Aseo y Limp"
        }else if(cmb_bodega==5){
            bod="Herramienta"
        }else if(cmb_bodega==9){
            bod="Tics"
        }else if(cmb_bodega==10){
            bod="Lenceria"
        }else if(cmb_bodega==17){
            bod="Medicamento Dialisis"
        }else if(cmb_bodega==18){
            bod="Insumo Dialisis"
        }else if(cmb_bodega==19){
            bod="Laboratorio Dialisis"
        }else if(cmb_bodega==19){
            bod="Laboratorio Dialisis"
        }

        $('.bodega_seleccionada').html(bod)
       
        if(data.error==true){
			$("#tabla_inventario_global_md tbody").html('');
			$("#tabla_inventario_global_md tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
			alertNotificar(data.mensaje,"error");
			return;   
		}
		if(data.error==false){
			if(data.resultado.length==0){
				$("#tabla_inventario_global_md tbody").html('');
				$("#tabla_inventario_global_md tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
				alertNotificar("No se encontró información","error");
				return;
			}
			
			$("#tabla_inventario_global_md tbody").html('');
           
           
            let disabled=""
           
            if(cmb_bodega==1 || cmb_bodega==2 || cmb_bodega==8 || cmb_bodega==13 || cmb_bodega==14 ){
                disabled=""
                if(cmb_tipo=="FARMACIA"){
                    // disabled="disabled"
                }
            }else{
                //disabled="disabled"
            } 
          
            datoItemArray=[]
			$.each(data.resultado,function(i, item){
                let codi=""
                if(item.codigo_item){
                    codi=item.codigo_item
                }else{
                    codi=item.cudim
                }

                let codigo_esb=""
                if(item.codigo != null){
                    codigo_esb=item.codigo
                }else{
                    codigo_esb=""
                }

                datoItemArray.push({'idprod_':item.id_item,'nombres_':item.detalle});
                globalThis.datosItem=datoItemArray;

                $('#tabla_inventario_global_md').append(`<tr >
                                            <td style="width:10%; vertical-align:middle">
                                                ${codi}
                                                
                                            </td>

                                            <td style="width:10%; vertical-align:middle">
                                                ${codigo_esb} 
                                                
                                            </td>

                                            <td style="width:40%;  text-align:left; vertical-align:middle">
                                                ${item.detalle}
                                            </td>

                                            

                                            <td style="width:20%; text-align:center; vertical-align:middle">
                                               
                                                <button type="button" ${disabled} class="btn btn-success btn-xs" onclick="verAcceso('${item.id_item}', '${cmb_bodega}')">Parametros Med</button>

                                                <button type="button" ${disabled} class="btn btn-primary btn-xs" onclick="verAccesoEnf('${item.id_item}', '${cmb_bodega}')">Param Licen Lideres</button>
                                            </td>

                                            
                                        
                                    </tr>`);
                
			})
            
		  
			cargar_estilos_datatable_md('tabla_inventario_global_md');
		}
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
        $("#tabla_inventario_global_md tbody").html('');
		$("#tabla_inventario_global_md tbody").html(`<tr><td colspan="${num_col}">Se produjo un error, por favor intentelo más tarde</td></tr>`);
    });  

}


function verAccesoEnf(id, bodega, abiertaModal=null){

    $("#tabla_insumo_parametriza tbody").html('');

	$('#tabla_insumo_parametriza').DataTable().destroy();
	$('#tabla_insumo_parametriza tbody').empty(); 
    
    // limpiarCampos()
    var num_col = $("#tabla_insumo_parametriza thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_insumo_parametriza tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);


    $('#fecha_ini_rep').html('')
    $('#fecha_fin_rep').html('')

    $('#tituloCabecera').html(`<button type="buttton" onclick="cancelar()" class="btn btn-sm btn-danger">Atras</button> `)

    let filtrar_item = datosItem.filter(datos => datos.idprod_ == id );
   
    let nombre_insumo=filtrar_item[0].nombres_
   
    $.get('acceso-med-enf-lider/'+id+'/'+bodega, function(data){
       
        if(data.error==true){
			$("#tabla_insumo_parametriza tbody").html('');
			$("#tabla_insumo_parametriza tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
			alertNotificar(data.mensaje,"error");
            cancelar()
			return;   
		}
		if(data.error==false){
			if(data.resultado.length==0){
				$("#tabla_insumo_parametriza tbody").html('');
				$("#tabla_insumo_parametriza tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
				alertNotificar("No se encontró información","error");
                cancelar()
				return;
			}
			
			$("#tabla_insumo_parametriza tbody").html('');
        
          
            $('#tabla_insumo_parametriza').DataTable({
                "destroy":true,
                pageLength: 50,
                autoWidth : true,
                order: [[ 0, "asc" ]],
                sInfoFiltered:false,
                language: {
                    url: 'json/datatables/spanish.json',
                },
                columnDefs: [
                    { "width": "65%", "targets": 0 },
                    { "width": "35%", "targets": 1 },
                    
                   
                ],
                data: data.resultado,
                columns:[
                        {data: "descripcion" },
                        {data: "idarea_especialidad" },
                      
                ],    
                "rowCallback": function( row, data, index ) {
                   
                        let perm=""
                        if(data.accesoPerm=="S"){
                            perm="checked"
                        }else{
                            perm=""
                        }
                        $('td', row).eq(0).html(data.descripcion)
                        $('td', row).eq(1).html(`
                                    
                                                
                                                <input type="checkbox" onclick="accionAccesoEnf(${data.idarea_especialidad})"class="acces_check" id="check_${data.idarea_especialidad}" name="acces_check" value="${data.idarea_especialidad}"  ${perm}>
                                        
                                        
                        `); 
                    
                }             
            });
            globalThis.MedicnaSeleccionada=id
            if(abiertaModal!="S"){
                $('#modal_busqueda_acceso').modal('show')
                $('#insumo_parametro').html(nombre_insumo)
                $('.modal-title').html('ACCESO MEDICAMENTOS LIDERES AREA')
            }
		}
    })
       
}

function verAcceso(id, bodega, abiertaModal=null){

    $("#tabla_insumo_parametriza tbody").html('');

	$('#tabla_insumo_parametriza').DataTable().destroy();
	$('#tabla_insumo_parametriza tbody').empty(); 
    
    // limpiarCampos()
    var num_col = $("#tabla_insumo_parametriza thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_insumo_parametriza tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);


    $('#fecha_ini_rep').html('')
    $('#fecha_fin_rep').html('')

    $('#tituloCabecera').html(`<button type="buttton" onclick="cancelar()" class="btn btn-sm btn-danger">Atras</button> `)

    let filtrar_item = datosItem.filter(datos => datos.idprod_ == id );
   
    let nombre_insumo=filtrar_item[0].nombres_
   
    $.get('acceso-medicina/'+id+'/'+bodega, function(data){
      
        if(data.error==true){
			$("#tabla_insumo_parametriza tbody").html('');
			$("#tabla_insumo_parametriza tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
			alertNotificar(data.mensaje,"error");
            cancelar()
			return;   
		}
		if(data.error==false){
			if(data.resultado.length==0){
				$("#tabla_insumo_parametriza tbody").html('');
				$("#tabla_insumo_parametriza tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
				alertNotificar("No se encontró información","error");
                cancelar()
				return;
			}
			
			$("#tabla_insumo_parametriza tbody").html('');
        
          
            $('#tabla_insumo_parametriza').DataTable({
                "destroy":true,
                pageLength: 50,
                autoWidth : true,
                order: [[ 0, "asc" ]],
                sInfoFiltered:false,
                language: {
                    url: 'json/datatables/spanish.json',
                },
                columnDefs: [
                    { "width": "65%", "targets": 0 },
                    { "width": "35%", "targets": 1 },
                    
                   
                ],
                data: data.resultado,
                columns:[
                        {data: "descripcion" },
                        {data: "idarea_especialidad" },
                      
                ],    
                "rowCallback": function( row, data, index ) {
                    
                    let perm=""
                    if(data.accesoPerm=="S"){
                        perm="checked"
                    }else{
                        perm=""
                    }
                    $('td', row).eq(1).html(`
                                  
                                            
                                            <input type="checkbox" onclick="accionAcceso(${data.idarea_especialidad})"class="acces_check" id="check_${data.idarea_especialidad}" name="acces_check" value="${data.idarea_especialidad}"  ${perm}>
                                       
                                    
                    `); 
                }             
            });
            globalThis.MedicnaSeleccionada=id
            if(abiertaModal!="S"){
                $('#modal_busqueda_acceso').modal('show')
                $('#insumo_parametro').html(nombre_insumo)
                $('.modal-title').html('ACCESO MEDICAMENTOS DOCTORES')
            }
		}
    })
       
}

function accionAccesoEnf(id){
   
    if( $('#check_'+id).is(':checked') ){
        // mandamos a guardar ese menu al perfil
        AggQuitarAccesoMedicina(id,'A','LE')
    } else {
        // mandamos a quitar
        AggQuitarAccesoMedicina(id,'Q','LE')
    }
}

function accionAcceso(id){
   
    if( $('#check_'+id).is(':checked') ){
        // mandamos a guardar ese menu al perfil
        AggQuitarAccesoMedicina(id,'A','M')
    } else {
        // mandamos a quitar
        AggQuitarAccesoMedicina(id,'Q','M')
    }
}

function AggQuitarAccesoInsumo(idarea_esp, tipo, lugar){
    vistacargando("m","Espere por favor")
    $.get("insumo-por-area/"+idarea_esp+"/"+tipo+"/"+MedicnaSeleccionada, function(data){
        vistacargando("")
        if(data.error==true){
            if(tipo=='A'){
                $('#check_'+id).prop('checked',false)
            }else{
                $('#check_'+id).prop('checked',true)
            }
               
            alertNotificar(data.mensaje,"error");
            return;   
        }
       
        alertNotificar(data.mensaje,"success")
        if(lugar=='L'){
            verAccesoEnf(MedicnaSeleccionada, 2,'S')
        }else{
            verAcceso(MedicnaSeleccionada, 2,'S')
        }
        
       
    }).fail(function(){
        if(tipo=='A'){
            $('#check_'+id).prop('checked',false)
        }else{
            $('#check_'+id).prop('checked',true)
        }
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}

function AggQuitarAccesoMedicina(idarea_esp, tipo, lugar){
    vistacargando("m","Espere por favor")
    $.get("medicina-por-area/"+idarea_esp+"/"+tipo+"/"+MedicnaSeleccionada, function(data){
        vistacargando("")
        if(data.error==true){
            if(tipo=='A'){
                $('#check_'+id).prop('checked',false)
            }else{
                $('#check_'+id).prop('checked',true)
            }
               
            alertNotificar(data.mensaje,"error");
            return;   
        }
       
        alertNotificar(data.mensaje,"success")
        if(lugar=='L'){
            verAccesoEnf(MedicnaSeleccionada, 2,'S')
        }if(lugar=='LE'){
            verAccesoEnf(MedicnaSeleccionada, 2,'S')
        }else{
            verAcceso(MedicnaSeleccionada, 2,'S')
        }
        
       
    }).fail(function(){
        if(tipo=='A'){
            $('#check_'+id).prop('checked',false)
        }else{
            $('#check_'+id).prop('checked',true)
        }
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}
    

function verAcceso1(id, bodega){
    if(bodega==1 || bodega==2){

    }else{
        alertNotificar("Opcion no disponible para la bodega seleccionada")
        return
    }
    $("#tabla_parametro tbody").html('');
	$('#tabla_parametro').DataTable().destroy();
	$('#tabla_parametro tbody').empty(); 

    $("#tabla_parametro_ins tbody").html('');
	$('#tabla_parametro_ins').DataTable().destroy();
	$('#tabla_parametro_ins tbody').empty(); 


    vistacargando("m","Espere por favor");
    $.get('bloqueo-item/'+id+'/'+bodega, function(data){
       
        vistacargando("")
        if(data.error==true){
			alertNotificar(data.mensaje,"error");
			return;   
		}
        if(bodega==1){
                $('#tabla_parametro').append(`<tr >
                        
                        <td style="width:10%; vertical-align:middle">
                            ${data.resultado.nombre} ${data.resultado.presentacion}
                            
                        </td>

                        <td style="width:10%;  text-align:left; vertical-align:middle">
                           
                            <input type="number" id="cardio" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.cardio}','cardio')"  value="${data.resultado.cardio}">

                        </td>

                        <td style="width:10%;  text-align:left; vertical-align:middle">
                            
                            <input type="number" id="ciru" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.ciru}','ciru')"  value="${data.resultado.ciru}">
                            
                        </td>

                        <td style="width:10%;  text-align:left; vertical-align:middle">
                           
                            <input type="number" id="cobste" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.cobste}','cobste')"  value="${data.resultado.cobste}">

                        </td>

                        <td style="width:10%;  text-align:left; vertical-align:middle">
                           
                            <input type="number" id="cod_sisbo" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.cod_sisbo}','cod_sisbo')"  value="${data.resultado.cod_sisbo}">

                        </td>

                        <td style="width:10%;  text-align:left; vertical-align:middle">
                         
                            <input type="number" id="cons_ext" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.cons_ext}','cons_ext')"  value="${data.resultado.cons_ext}">
                        </td>



                        <td style="width:10%;  text-align:left; vertical-align:middle">
                           
                            <input type="number" id="covid_hosp" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.covid_hosp}','covid_hosp')"  value="${data.resultado.covid_hosp}">

                        </td>

                        <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                            <input type="number" id="covid_tria" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.covid_tria}','covid_tria')"  value="${data.resultado.covid_tria}">

                        </td>

                        <td style="width:10%;  text-align:left; vertical-align:middle">
                           
                            <input type="number" id="cqui" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.cqui}','cqui')"  value="${data.resultado.cqui}">


                        </td>

                        <td style="width:10%;  text-align:left; vertical-align:middle">
                          
                            <input type="number" id="derma" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.derma}','derma')"  value="${data.resultado.derma}">

                        </td>

                        <td style="width:10%;  text-align:left; vertical-align:middle">
                          
                            <input type="number" id="emerg" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.emerg}','emerg')"  value="${data.resultado.emerg}">

                        </td>

                        <td style="width:10%;  text-align:left; vertical-align:middle">
                          
                            <input type="number" id="emerg_hosp" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.emerg_hosp}','emerg_hosp')"  value="${data.resultado.emerg_hosp}">

                        </td>

                        <td style="width:10%;  text-align:left; vertical-align:middle">
                            <input type="number" id="endocri" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.endocri}','endocri')"  value="${data.resultado.endocri}">

                        </td>



                        <td style="width:10%;  text-align:left; vertical-align:middle">
                          
                            <input type="number" id="fisi" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.fisi}','fisi')"  value="${data.resultado.fisi}">

                        </td>

                        <td style="width:10%;  text-align:left; vertical-align:middle">
                          
                            <input type="number" id="gastro" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.gastro}','gastro')"  value="${data.resultado.gastro}">
                        </td>

                        <td style="width:10%;  text-align:left; vertical-align:middle">
                          
                            <input type="number" id="geriatra" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.geriatra}','geriatra')"  value="${data.resultado.geriatra}">
                            
                        </td>

                        <td style="width:10%;  text-align:left; vertical-align:middle">
                            
                            <input type="number" id="gine" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.gine}','gine')"  value="${data.resultado.gine}">
                        </td>

                        <td style="width:10%;  text-align:left; vertical-align:middle">
                            
                            <input type="number" id="hos" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.hos}','hos')"  value="${data.resultado.hos}">
                        </td>




                        <td style="width:10%;  text-align:left; vertical-align:middle">
                           
                            <input type="number" id="infecto" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.infecto}','infecto')"  value="${data.resultado.infecto}">
                        </td>
                        <td style="width:10%;  text-align:left; vertical-align:middle">
                           
                            <input type="number" id="medlab" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.medlab}','medlab')"  value="${data.resultado.medlab}">
                        </td>


                        <td style="width:10%;  text-align:left; vertical-align:middle">

                            <input type="number" id="mint" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.mint}','mint')"  value="${data.resultado.mint}">

                        </td>

                        <td style="width:10%;  text-align:left; vertical-align:middle">
                       
                            <input type="number" id="nefro" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.nefro}','nefro')"  value="${data.resultado.nefro}">

                        </td>


                        <td style="width:10%;  text-align:left; vertical-align:middle">
                           
                            <input type="number" id="neo" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.neo}','neo')"  value="${data.resultado.neo}">
                        </td>

                        <td style="width:10%;  text-align:left; vertical-align:middle">
                          
                            <input type="number" id="neuro" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.neuro}','neuro')"  value="${data.resultado.neuro}">
                        </td>

                        <td style="width:10%;  text-align:left; vertical-align:middle">
                           
                            <input type="number" id="nuemo" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.nuemo}','nuemo')"  value="${data.resultado.nuemo}">

                        </td>

                        <td style="width:10%;  text-align:left; vertical-align:middle">
                          
                            <input type="number" id="nutri" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.nutri}','nutri')"  value="${data.resultado.nutri}">

                        </td>

                        <td style="width:10%;  text-align:left; vertical-align:middle">
                           
                            <input type="number" id="odon" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.odon}','odon')"  value="${data.resultado.odon}">
                        </td>

                        <td style="width:10%;  text-align:left; vertical-align:middle">
                           
                            <input type="number" id="otorrino" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.otorrino}','otorrino')"  value="${data.resultado.otorrino}">
                            </td>
                        </td>



                        <td style="width:10%;  text-align:left; vertical-align:middle">
                           
                            <input type="number" id="ped" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.ped}','ped')"  value="${data.resultado.ped}">
                        </td>

                        <td style="width:10%;  text-align:left; vertical-align:middle">
                            
                            <input type="number" id="psico" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.psico}','psico')"  value="${data.resultado.psico}">
                        </td>

                        <td style="width:10%;  text-align:left; vertical-align:middle">
                         
                            <input type="number" id="reuma" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.reuma}','reuma')"  value="${data.resultado.reuma}">
                        </td>


                        <td style="width:10%;  text-align:left; vertical-align:middle">
                           
                            <input type="number" id="saludm" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.saludm}','saludm')"  value="${data.resultado.saludm}">
                        </td>

                        <td style="width:10%;  text-align:left; vertical-align:middle">
                          
                            <input type="number" id="trauma" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.trauma}','trauma')"  value="${data.resultado.trauma}">
                        </td>

                        <td style="width:50px;  text-align:left; vertical-align:middle">
                            
                            <input type="number"  id="uci" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.uci}','uci')"  value="${data.resultado.uci}">

                        </td>

                        <td style="width:10%;  text-align:left; vertical-align:middle">
                         
                            <input type="number" id="uci_covid" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.uci_covid}','uci_covid')"  value="${data.resultado.uci_covid}">
                        </td>
                     

                    
                </tr>`);

                $('#listado_global').hide()
                $('#listado_global_med').hide()
                $('#parametro_item').show()

                cargar_estilos_datatable_detalle('tabla_parametro');

        }
        else if(bodega==2){
            
            $('#tabla_parametro_ins').append(`<tr >
                    
                    <td style="width:10%; vertical-align:middle">
                        ${data.resultado.insumo}
                        
                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="autorief" style="width:50px !important;text-align:right" name="autorief[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.autorief}','autorief')"  value="${data.resultado.autorief}">

                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="autorimed" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.autorimed}','autorimed')"  value="${data.resultado.autorimed}">
                        
                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="cardio" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.cardio}','cardio')"  value="${data.resultado.cardio}">

                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="ce" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.ce}','ce')"  value="${data.resultado.ce}">

                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="ce" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.ce}','ce')"  value="${data.resultado.ce}">
                    </td>



                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="central" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.central}','central')"  value="${data.resultado.central}">

                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                    
                        <input type="number" id="ciru" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.ciru}','ciru')"  value="${data.resultado.ciru}">

                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="cobste" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.cobste}','cobste')"  value="${data.resultado.cobste}">


                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="cod_sisbo" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.cod_sisbo}','cod_sisbo')"  value="${data.resultado.cod_sisbo}">

                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="cqui" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.cqui}','cqui')"  value="${data.resultado.cqui}">

                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="derma" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.derma}','derma')"  value="${data.resultado.derma}">

                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        <input type="number" id="em" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.em}','em')"  value="${data.resultado.em}">

                    </td>



                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="emho" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.emho}','emho')"  value="${data.resultado.emho}">

                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="endocri" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.endocri}','endocri')"  value="${data.resultado.endocri}">
                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="epp" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.epp}','epp')"  value="${data.resultado.epp}">
                        
                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="fisi" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.fisi}','fisi')"  value="${data.resultado.fisi}">
                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="gastro" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.gastro}','gastro')"  value="${data.resultado.gastro}">
                    </td>




                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="geriatra" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.geriatra}','geriatra')"  value="${data.resultado.geriatra}">
                    </td>
                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="gine" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.gine}','gine')"  value="${data.resultado.gine}">
                    </td>


                    <td style="width:10%;  text-align:left; vertical-align:middle">

                        <input type="number" id="ho" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.ho}','ho')"  value="${data.resultado.ho}">

                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                    
                        <input type="number" id="imagen" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.imagen}','imagen')"  value="${data.resultado.imagen}">

                    </td>


                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="infecto" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.infecto}','infecto')"  value="${data.resultado.infecto}">
                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="labo" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.labo}','labo')"  value="${data.resultado.labo}">
                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="medlab" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.medlab}','medlab')"  value="${data.resultado.medlab}">

                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="mint" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.mint}','mint')"  value="${data.resultado.mint}">

                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="nefro" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.nefro}','nefro')"  value="${data.resultado.nefro}">
                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="neo" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.neo}','neo')"  value="${data.resultado.neo}">
                        </td>
                    </td>



                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="neumo" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.neumo}','neumo')"  value="${data.resultado.neumo}">
                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="neuro" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.neuro}','neuro')"  value="${data.resultado.neuro}">
                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="nutri" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.nutri}','nutri')"  value="${data.resultado.nutri}">
                    </td>


                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="odon" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.odon}','odon')"  value="${data.resultado.odon}">
                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="otorrino" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.otorrino}','otorrino')"  value="${data.resultado.otorrino}">
                    </td>

                    <td style="width:50px;  text-align:left; vertical-align:middle">
                        
                        <input type="number"  id="ped" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.ped}','ped')"  value="${data.resultado.ped}">

                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="psico" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.psico}','psico')"  value="${data.resultado.psico}">
                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="reuma" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.reuma}','reuma')"  value="${data.resultado.reuma}">
                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="trauma" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.trauma}','trauma')"  value="${data.resultado.trauma}">
                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="uci" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.uci}','uci')"  value="${data.resultado.uci}">
                    </td>

                    <td style="width:10%;  text-align:left; vertical-align:middle">
                        
                        <input type="number" id="uro" style="width:50px !important;text-align:right" name="cardio[]" onkeyup="tecla_cantidad(this,'${id}','${data.resultado.uro}','uro')"  value="${data.resultado.uro}">
                    </td>
                    

                
            </tr>`);

            $('#listado_global').hide()
            $('#listado_global_med').hide()
            $('#parametro_insumo').show()

            cargar_estilos_datatable_detalle('tabla_parametro_ins');

        

        }
            

      
       

       
       
        
    }).fail(function(){
        alertNotificar("Ocurrio un error","error");
        vistacargando("")
       
    });  
}

function tecla_cantidad(e, id, valoract,esp){
  
    var valor=$('#'+esp).val()
  
    if(valor==1 || valor==0){
        vistacargando("m", "Espere por favor")
        $.get('parametriza-item/'+id+"/"+esp+"/"+BodeSelecc+"/"+valor, function(data){
           
            vistacargando("")
            if(data.error==true){
                alertNotificar(data.mensaje,"error");
                return;   
            }
            alertNotificar(data.mensaje,"success");

        }).fail(function(){
            alertNotificar("Ocurrio un error","error");
            vistacargando("")
        
        }); 
        
    }else{
        alertNotificar("Solo se permite valores 1 y 0", "error")
        $('#'+esp).val(valoract)
        
        return
    }
    
}

globalThis.actualizarGlobal=0
function verDetallado(iditem, bodega, tipo){
    var bod=bodega
    if(bod==1){ btn="EM1"}
    else if(bod==2){ btn="EM2"}
    else if(bod==8){ btn="EM8"}
    else if(bod==13){ btn="EM13"}
    else if(bod==14){ btn="EM14"}
    else if(bod==3){ btn="EM3"}
    else if(bod==4){ btn="EM4"}
    else if(bod==5){ btn="EM5"}
    else if(bod==10){ btn="EM10"}
    else if(bod==9){ btn="EM9"}
    else if(bod==17){ btn="EM17"}
    else if(bod==18){ btn="EM18"}
    else if(bod==19){ btn="EM19"}
   
    vistacargando("m","Espere por favor");
    $.get('verifica-permiso', function(data){

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
            verDetallado_P(iditem, bodega, tipo)
        }
        
    }).fail(function(){
        alertNotificar("Ocurrio un error","error");
        vistacargando("")
       
    });  

}

function verDetallado_P(iditem, bodega, tipo){
    
   
    $.get('detalle-item-act/'+bodega+'/'+tipo+'/'+iditem, function(data){
     
        if(data.error==true){
			
			alertNotificar(data.mensaje,"error");
			return;   
		}
        actualizarGlobal=1
       
        globalThis.ItemActualiza=iditem
        if(bodega==1){
            $('#codigo').val(data.resultado.cum)
            $('#nombre_med').val(data.resultado.nombre)
            $('#cod_esbay_med').val(data.resultado.codigo)
            $('#concentracion_med').val(data.resultado.concentra)
            $('#forma_med').val(data.resultado.forma)
            $('#presentacion_med').val(data.resultado.presentacion)
            $('#stock_min').val(data.resultado.stock_min)
            $('#stock_cri').val(data.resultado.stock_cri)
            $('#form_item').show()
            $('#listado_global_med').hide()
          
            

        }else if(bodega==2){
           
            $('#cudim').val(data.resultado.cudim)
            $('#cod_esbay_ins').val(data.resultado.codigo)
            $('#insumo').val(data.resultado.insumo)
            $('#desc_ins').val(data.resultado.descrip)
            $('#espec_tecn').val(data.resultado.espetec)
            $('#stock_min_ins').val(data.resultado.stockmin)
            $('#stock_cri_ins').val(data.resultado.stockcri)
            var sele=""
            if(data.resultado.idtipoinsu==0){
                sele="selected"
            }else if(data.resultado.idtipoinsu==1){
                sele="selected"
            }else if(data.resultado.idtipoinsu==2){
                sele="selected"
            }else if(data.resultado.idtipoinsu==3){
                sele="selected"
            }else if(data.resultado.idtipoinsu==4){
                sele="selected"
            }else if(data.resultado.idtipoinsu==5){
                sele="selected"
            }else if(data.resultado.idtipoinsu==6){
                sele="selected"
            }else if(data.resultado.idtipoinsu==7){
                sele="selected"
            }

            $('#tipo_ins').html('');				
            $('#tipo_ins').append(` <option value=""></option>
            <option value="1" >DISPOSITIVOS MÉDICOS DE USO GENERAL </option>
            <option value="2">DISPOSITIVOS MÉDICOS DE ODONTOLOGÍA </option>
            <option value="3">DISPOSITIVOS MÉDICOS DE IMAGENOLOGÍA</option>
            <option value="4">DISPOSITIVOS MÉDICOS MATERIALES DE LABORATORIO</option>
            <option value="5">DISPOSITIVOS MÉDICOS REACTIVOS DE LABORATORIO</option>
            <option value="6">DISPOSITIVOS MÉDICOS DE MICROBIOLOGÍA</option>
            <option value="7">DESINFECTANTES</option>`).change();
            $("#tipo_ins").trigger("chosen:updated"); // actualizamos el combo 

            $('#form_item_ins').show()
            $('#listado_global_med').hide()
        }else if(bodega==8 || bodega==13 || bodega==14){ //laboratorio
           
            $('#cod_lab').val(data.resultado.codigo)
            $('#cod_esbay_lab').val(data.resultado.esbay)
            $('#desc_lab').val(data.resultado.descri)
            $('#stock_min_lab').val(data.resultado.stockmin)
            $('#stock_cri_lab').val(data.resultado.stockcri)

            $('#form_item_lab').show()
            $('#listado_global_med').hide()
        }else if(bodega==3 || bodega==4 || bodega==5 || bodega==9 || bodega==10){
           
            $('#mat_of').val(data.resultado.descri)
            $('#prese_of').val(data.resultado.presen)
            $('#codigo_item').val(data.resultado.codigo_sys)

            $('#form_item_mat_of').show()
            $('#listado_global').hide()
        }else if(bodega==17){

            $('#codigo_dialisis').val(data.resultado.cum)
            $('#nombre_med_dialisis').val(data.resultado.nombre)
            $('#concentracion_med_dialisis').val(data.resultado.concentra)
            $('#forma_med_dialisis').val(data.resultado.forma)
            $('#presentacion_med_dialisis').val(data.resultado.presentacion)
            $('#stock_min_dialisis').val(data.resultado.stock_min)
            $('#stock_cri_dialisis').val(data.resultado.stock_cri)

            $('#form_item_dialisis').show()
            $('#listado_global').hide()
        }else if(bodega==18){

            $('#cudim_dialisi').val(data.resultado.cudim)
            $('#insumo_dialisi').val(data.resultado.insumo)
            $('#desc_ins_dialisi').val(data.resultado.descrip)
            $('#espec_tecn').val(data.resultado.espetec)
            $('#stock_cri_ins_dialisi').val(data.resultado.stockmin)
            $('#stock_min_ins_dialisi').val(data.resultado.stockcri)

            $('#form_ins_dialisis').show()
            $('#listado_global').hide()
        }else if(bodega==19){
           

            // $('#mat_of').val(data.resultado.descri)
            // $('#prese_of').val(data.resultado.presen)

            $('#cod_lab_ins').val(data.resultado.codigo)
            $('#desc_lab_ins').val(data.resultado.descri)

            $('#form_lab_dialisis').show()
            $('#listado_global').hide()
        }
        
    }).fail(function(){
        alertNotificar("Ocurrio un error","error");
        vistacargando("")
       
    });   


}

function nuevoItem(){
    var bod=BodeSelecc
    var btn="";
    if(bod==1){ btn="NM1"}
    else if(bod==2){ btn="NM2"}
    else if(bod==8){ btn="NM8"}
    else if(bod==13){ btn="NM13"}
    else if(bod==14){ btn="NM14"}
    else if(bod==3){ btn="NM3"}
    else if(bod==4){ btn="NM4"}
    else if(bod==5){ btn="NM5"}
    else if(bod==10){ btn="NM10"}
    else if(bod==9){ btn="NM9"}
    else if(bod==17){ btn="NM17"}
    else if(bod==18){ btn="NM18"}
    else if(bod==19){ btn="NM19"}
    vistacargando("m","Espere");
    $.get('verifica-permiso', function(data){
       
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
   
    var bod=BodeSelecc
    actualizarGlobal=0
   
    if(bod==1){
        $('#form_item').show()
        $('#listado_global_med').hide()
    }else if(bod==2){
        $('#form_item_ins').show()
        $('#listado_global_med').hide()

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
        $('#listado_global_med').hide()
    }else if(bod==3 || bod==4 || bod==5 || bod==10 || bod==9){
        $('#form_item_mat_of').show()
        $('#listado_global').hide()
    }else if(bod==17){
        //med dialisis
        $('#form_item_dialisis').show()
        $('#listado_global').hide()

    }else if(bod==18){
        //ins dialisis
        $('#form_ins_dialisis').show()
        $('#listado_global').hide()

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
        $('#listado_global').hide()
    }
    
}

function volverBusquedaMed(){
    $('#form_item').hide()
    $('#form_item_ins').hide()
    $('#form_item_lab').hide()
    $('#form_item_mat_of').hide()
    $('#form_item_dialisis').hide()
    $('#form_ins_dialisis').hide()
    $('#form_lab_dialisis').hide()
    $('#parametro_item').hide()
    $('#parametro_insumo').hide()
    $('#listado_global_med').show()
    limpiarForm()
}

function volverBusqueda(){
    $('#form_item').hide()
    $('#form_item_ins').hide()
    $('#form_item_lab').hide()
    $('#form_item_mat_of').hide()
    $('#form_item_dialisis').hide()
    $('#form_ins_dialisis').hide()
    $('#form_lab_dialisis').hide()
    $('#parametro_item').hide()
    $('#parametro_insumo').hide()
    // $('#listado_global').show()
    $('#listado_global_med').show()
    limpiarForm()
}

function limpiarForm(){
    $('#codigo').val('')
    $('#nombre_med').val('')
    $('#cod_esbay_med').val('')
    $('#concentracion_med').val('')
    $('#forma_med').val('')
    $('#presentacion_med').val('')
    $('#stock_min').val('')
    $('#stock_cri').val('')

    $('#cudim').val('')
    $('#cod_esbay_ins').val('')
    $('#insumo').val('')
    $('#desc_ins').val('')
    $('#espec_tecn').val('')
    $('#stock_min_ins').val('')
    $('#stock_cri_ins').val('')

    $('#cod_lab').val('')
    $('#cod_esbay_lab').val('')
    $('#desc_lab').val('')
    $('#stock_min_lab').val('')
    $('#stock_cri_lab').val('')

    $('#mat_of').val('')
    $('#codigo_item').val('')
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
    var cod_esbay_med=$('#cod_esbay_med').val()
    var nombre_med=$('#nombre_med').val()
    var concentracion_med=$('#concentracion_med').val()
    var forma_med=$('#forma_med').val()
    var presentacion_med=$('#presentacion_med').val()
    var stock_min=$('#stock_min').val()
    var stock_cri=$('#stock_cri').val()
   
    if(codigo==""){
        alertNotificar("Ingrese el cum","error")
        $('#codigo').focus()
        return
    }

    if(cod_esbay_med==""){
        alertNotificar("Ingrese el codigo esbay","error")
        $('#cod_esbay_med').focus()
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
    var sms=""
    if(actualizarGlobal==0){
        sms="¿Desea ingresar el medicamento?"
    }else{
        sms="¿Desea actualizar el medicamento?"
    }
    swal({
        title:sms,
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

    if(actualizarGlobal==0){
        tipo="POST"
        url_form="guardar-medicina"
    }else{
        tipo="PUT"
        url_form="actualiza-medicina/"+ItemActualiza
    }
         
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
            volverBusquedaMed()
            listarMedGral(1)
            alertNotificar(data.mensaje,"success");
            
        }, error:function (data) {
            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})



function validaFormInsumo(){
    var cudim=$('#cudim').val()
    var insumo=$('#insumo').val()
    var esbay_insumo=$('#cod_esbay_ins').val()
    var stock_min_ins=$('#stock_min_ins').val()
    var stock_cri_ins=$('#stock_cri_ins').val()

    if(cudim==""){
        alertNotificar("Ingrese el cudim","error")
        $('#cudim').focus()
        return
    }

    if(esbay_insumo==""){
        alertNotificar("Ingrese el codigo esbay","error")
        $('#cod_esbay_ins').focus()
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

    var sms=""
    if(actualizarGlobal==0){
        sms="¿Desea ingresar el insumo?"
    }else{
        sms="¿Desea actualizar el insumo?"
    }
    swal({
        title:sms,
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

    if(actualizarGlobal==0){
        tipo="POST"
        url_form="guardar-insumo"
    }else{
        tipo="PUT"
        url_form="actualiza-insumo/"+ItemActualiza
    }  
   
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
            volverBusquedaMed()
            listarMedGral(2)
            alertNotificar(data.mensaje,"success");
            
        }, error:function (data) {
           
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

    $('#idbod').val(BodeSelecc)

    var sms=""
    if(actualizarGlobal==0){
        sms="¿Desea registrar la informacion?"
    }else{
        sms="¿Desea actualizar la informacion?"
    }
    swal({
        title:sms,
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
    if(actualizarGlobal==0){
        tipo="POST"
        url_form="guardar-lab"
    }else{
        tipo="PUT"
        url_form="actualiza-lab/"+ItemActualiza
    }  
   
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
            volverBusquedaMed()
            listarMedGral(BodeSelecc)
            alertNotificar(data.mensaje,"success");
            
        }, error:function (data) {
           
            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})


function validaFormItem(){
    var mat_of=$('#mat_of').val()
    var prese_of=$('#prese_of').val()
    var codigo=$('#codigo_item').val()
    
    if(codigo==""){
        alertNotificar("Ingrese el codigo","error")
        $('#codigo').focus()
        return
    }

    if(mat_of==""){
        alertNotificar("Ingrese la descripcion","error")
        $('#mat_of').focus()
        return
    }
    if(BodeSelecc==3 || BodeSelecc==4){
        if(prese_of==""){
            alertNotificar("Ingrese la presentacion","error")
            $('#prese_of').focus()
            return
        }
    }
    

    $('#idbodite').val(BodeSelecc)

    var sms=""
    if(actualizarGlobal==0){
        sms="¿Desea registrar la informacion?"
    }else{
        sms="¿Desea actualizar la informacion?"
    }
    swal({
        title:sms,
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
    if(actualizarGlobal==0){
        tipo="POST"
        url_form="guardar-item"
    }else{
        tipo="PUT"
        url_form="actualiza-item/"+ItemActualiza
    }  
    
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
            listarMedGral(BodeSelecc)
            alertNotificar(data.mensaje,"success");
            
        }, error:function (data) {
          
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

    var sms=""
    if(actualizarGlobal==0){
        sms="¿Desea ingresar la informacion?"
    }else{
        sms="¿Desea actualizar la informacion?"
    }
    swal({
        title:sms,
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
    if(actualizarGlobal==0){
        tipo="POST"
        url_form="guardar-med-dialisis"
    }else{
        tipo="PUT"
        url_form="actualiza-med-dialisis/"+ItemActualiza
    }  
  
    
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
            listarMedGral(BodeSelecc)
            alertNotificar(data.mensaje,"success");
            
        }, error:function (data) {
           

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})

function validaFormInsumoDialisi(){
    var cudim_dialisi=$('#cudim_dialisi').val()
    var insumo_dialisi=$('#insumo_dialisi').val()
    var desc_ins_dialisi=$('#desc_ins_dialisi').val()
    var stock_cri_ins_dialisi=$('#stock_cri_ins_dialisi').val()
    var stock_min_ins_dialisi=$('#stock_min_ins_dialisi').val()

    if(cudim_dialisi==""){
        alertNotificar("Ingrese el cudim","error")
        $('#cudim_dialisi').focus()
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
    var sms=""
    if(actualizarGlobal==0){
        sms="¿Desea ingresar la informacion?"
    }else{
        sms="¿Desea actualizar la informacion?"
    }
    swal({
        title:sms,
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
  
    if(actualizarGlobal==0){
        tipo="POST"
        url_form="guardar-ins-dialisis"
    }else{
        tipo="PUT"
        url_form="actualiza-ins-dialisis/"+ItemActualiza
    }  
  
  
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
            listarMedGral(BodeSelecc)
            alertNotificar(data.mensaje,"success");
            
        }, error:function (data) {
        
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

    $('#idbod_ins').val(BodeSelecc)

    var sms=""
    if(actualizarGlobal==0){
        sms="¿Desea ingresar la informacion?"
    }else{
        sms="¿Desea actualizar la informacion?"
    }
    swal({
        title:sms,
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
    if(actualizarGlobal==0){
        tipo="POST"
        url_form="guardar-lab-dialisis"
    }else{
        tipo="PUT"
        url_form="actualiza-lab-dialisis/"+ItemActualiza
    }  
   
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
            listarMedGral(BodeSelecc)
            alertNotificar(data.mensaje,"success");
            
        }, error:function (data) {
         
            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})


function marcarTodosMedicos(){
   
    vistacargando("m","Espere por favor")
    $.get("medicina-seleccionar-todos/"+MedicnaSeleccionada+"/medicos", function(data){
        vistacargando("")
        if(data.error==true){
            if(tipo=='A'){
                $('#check_'+id).prop('checked',false)
            }else{
                $('#check_'+id).prop('checked',true)
            }
               
            alertNotificar(data.mensaje,"error");
            return;   
        }
       
        alertNotificar(data.mensaje,"success")
        if(lugar=='L'){
            verAccesoEnf(MedicnaSeleccionada, 2,'S')
        }else{
            verAcceso(MedicnaSeleccionada, 2,'S')
        }
        
       
    }).fail(function(){
        if(tipo=='A'){
            $('#check_'+id).prop('checked',false)
        }else{
            $('#check_'+id).prop('checked',true)
        }
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}