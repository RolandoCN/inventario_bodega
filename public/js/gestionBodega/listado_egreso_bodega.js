function buscarBodeguero(){
    $('#bodeguero_cmb').select2({
        placeholder: 'Seleccione una opción',
        ajax: {
        url: 'cargar-bodeguero',
        dataType: 'json',
        delay: 250,
        processResults: function (data) {
            return {
            results:  $.map(data, function (item) {
                    return {
                        text: item.documento +" -- "+item.nombre_bodeguero,
                        id: item.idper
                    }
                })
            };
        },
        cache: true
        }
    });
}

function fitroBusqueda(){
    let filtro=$('#busqueda_ingreso_cmb').val()
    
    if(filtro==""){ 
        return 
    }else{
        $('#bodeguero_cmb').val('').trigger('change.select2')
        $('#proveedor_cmb').val('').trigger('change.select2')

        if(filtro=="B"){
            $('#busqueda_bodeguero').show()
            $('#busqueda_proveedor').hide()
        }else if(filtro=="P"){
            $('#busqueda_bodeguero').hide()
            $('#busqueda_proveedor').show()
        }else{
            $('#busqueda_bodeguero').hide()
            $('#busqueda_proveedor').hide()
        }
    }
}

function buscarEgresos(){
    let fecha_inicial=$('#bus_fecha_ini').val()
    let fecha_final=$('#bus_fecha_fin').val()
    let filtro=$('#busqueda_ingreso_cmb').val()
    let id_bodeguero=$('#bodeguero_cmb').val()
    let id_persona=""
    if(filtro==""){ 
        alertNotificar("Seleccione un filtro de busqueda","error")
        return 
    }
    if(filtro=="B"){
        if(id_bodeguero==""){ 
            alertNotificar("Seleccione el bodeguero","error")
            return 
        }
        id_persona=id_bodeguero
    }

    if(filtro=="T"){
        id_persona=0
    }

   
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

    $("#tabla_egreso tbody").html('');

	$('#tabla_egreso').DataTable().destroy();
	$('#tabla_egreso tbody').empty(); 
    
    // limpiarCampos()
    var num_col = $("#tabla_egreso thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_egreso tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);


    $('#fecha_ini_rep').html('')
    $('#fecha_fin_rep').html('')

    $('#tituloCabecera').html(`<button type="buttton" onclick="cancelar()" class="btn btn-sm btn-danger">Atras</button> `)

    
    $.get('filtra-egreso-bod/'+fecha_inicial+'/'+fecha_final+'/'+filtro+'/'+id_persona, function(data){
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
              
				$('#tabla_egreso').append(`<tr>
                                                <td style="width:12%; vertical-align:middle">
                                                    ${item.descripcion} ${item.secuencial}
                                                    
                                                </td>

                                                <td style="width:12%;  text-align:center; vertical-align:middle">
                                                    ${item.fecha_hora}
                                                </td>
                                               
                                                <td style="width:20%; text-align:left">
                                                     ${item.observacion}
                                                  
                                                   
                                                </td>
                                                <td style="width:23%; text-align:left; vertical-align:middle">
                                                    ${item.responsable}
                                                </td>

                                                <td style="width:18%; text-align:center; vertical-align:middle">
                                                    ${item.nombre_bod}
                                                </td>
                                               
                                               

                                                <td style="width:7%; text-align:center; vertical-align:middle">

                                                    <button type="button" class="btn btn-xs btn-primary" onclick="imprimir('${item.idcomprobante}','${item.idbodega}','${item.idtipo_comprobante}')">Imprimir</button>

                                                   
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

function imprimir(id, bodega, idtipo){
    
    vistacargando("m","Espere por favor")
    var url_pdf=""
    if(idtipo=="4" || idtipo=="12"){
        url_pdf="reporte-egreso-bod-gral/"+id+"/"+bodega
    }else{
        url_pdf="reporte-transferencia-bod-farm/"+id+"/"+bodega
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







