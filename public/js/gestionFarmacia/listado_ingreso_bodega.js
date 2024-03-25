function buscarIngresos(){
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

    $("#tabla_ingreso tbody").html('');

	$('#tabla_ingreso').DataTable().destroy();
	$('#tabla_ingreso tbody').empty(); 
    
    // limpiarCampos()
    var num_col = $("#tabla_ingreso thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_ingreso tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);


    $('#fecha_ini_rep').html('')
    $('#fecha_fin_rep').html('')

    $('#tituloCabecera').html(`<button type="buttton" onclick="cancelar()" class="btn btn-sm btn-danger">Atras</button> `)

    
    $.get('filtra-ingreso-bod-farmacia/'+fecha_inicial+'/'+fecha_final, function(data){
        console.log(data)
        
        if(data.error==true){
			$("#tabla_ingreso tbody").html('');
			$("#tabla_ingreso tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
			alertNotificar(data.mensaje,"error");
            cancelar()
			return;   
		}
		if(data.error==false){
			if(data.resultado.length==0){
				$("#tabla_ingreso tbody").html('');
				$("#tabla_ingreso tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
				alertNotificar("No se encontró información","error");
                cancelar()
				return;
			}
			
			$("#tabla_ingreso tbody").html('');
            $('#fecha_ini_rep').html(fecha_inicial)
            $('#fecha_fin_rep').html(fecha_final)
          
            
            let contador=0
			$.each(data.resultado,function(i, item){
              
				$('#tabla_ingreso').append(`<tr>
                                                <td style="width:10%; vertical-align:middle">
                                                    ${item.descripcion} ${item.secuencial}
                                                    
                                                </td>

                                                <td style="width:8%;  text-align:left; vertical-align:middle">
                                                    ${item.fecha_hora}
                                                </td>
                                               
                                                <td style="width:22%; text-align:left">
                                                    <li> <b>Ruc:</b> ${item.ruc}
                                                    <li> <b>Empresa:</b> ${item.empresa} 
                                                   
                                                </td>
                                                <td style="width:17%; text-align:left; vertical-align:middle">
                                                    ${item.responsable}
                                                </td>

                                                <td style="width:15%; text-align:left; vertical-align:middle">
                                                    ${item.nombre_bod}
                                                </td>

                                                <td style="width:8%; text-align:center; vertical-align:middle">
                                                    ${item.tipoIngreso}
                                                </td>
                                               
                                                <td style="width:10%; text-align:right; vertical-align:middle">
                                                    $ ${item.total}
                                                </td>

                                                <td style="width:5%; text-align:center; vertical-align:middle">

                                                    <button type="button" class="btn btn-xs btn-primary" onclick="imprimir('${item.idcomprobante}','${item.idbodega}')">Imprimir</button>

                                                   
                                                </td>

                                                
											
										</tr>`);
			})
            if(contador>0){
                $('.btn_aprobacion').hide()
            }else{
                $('.btn_aprobacion').show()
            }
		  
			cargar_estilos_datatable('tabla_ingreso');
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

function imprimir(id, bodega){
    vistacargando("m","Espere por favor")
    
    $.get("reporte-ingreso-bod-farmacia/"+id+"/"+bodega, function(data){
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


//cierra la modal detalle
function cerrar(){
    $('#modal_detalle').modal('hide')
}


function descargarAprobacion(){

    let fecha_inicial_rep=$('#fecha_ini_').val()
    let fecha_final_rep=$('#fecha_fin_').val()

    vistacargando("m","Espere por favor");           

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    $.ajax({
        type: "POST",
        url: 'reporte-detallado',
        data: { _token: $('meta[name="csrf-token"]').attr('content'),
        fecha_inicial_rep:fecha_inicial_rep, fecha_final_rep:fecha_final_rep },
        success: function(data){
           
            vistacargando("");                
            if(data.error==true){
                alertNotificar(data.mensaje,'error');
                return;                      
            }
            alertNotificar("El documento se descargará en unos segundos...","success");
            window.location.href="descargar-reporte/"+data.pdf
                            
        }, error:function (data) {
            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
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





function fecha_inicial(e){
    
    var fecha_i=e.target.value;
    $('#fecha_fin_').val('');
    var fecha_fin = document.getElementById("fecha_fin_");
    fecha_fin.setAttribute("min", fecha_i);
    $('#fecha_fin_').prop('disabled',false)

}

function fecha_inicial_hora(e){
    
    var fecha_i=e.target.value;
    console.log(e.target.value)
    $('#fecha_hora_fin').val('');
    var fecha_fin = document.getElementById("fecha_hora_fin");
  
    fecha_fin.setAttribute("min", fecha_i);
    
    let max=fecha_i.split("T")
    let hora_max="23:59"
    fecha_fin.setAttribute("max", max[0]+'T'+hora_max);

    $('#fecha_hora_fin').prop('disabled',false)
}



function limpiarCamposDesdeHasta(){
    $('#fecha_ini_').val('')
    $('#fecha_fin_').val('')
    $('#cant_dias').val('')
    $('#cant_dias_horas').val('')

    $('#fecha_hora_ini').val('')
    $('#fecha_hora_fin').val('')
    $('#cant_horas').val('')


    var fecha_fin = document.getElementById("fecha_hora_fin");
  
    fecha_fin.setAttribute("min", "");
    fecha_fin.setAttribute("max", "");

    $('#fecha_fin').prop('disabled',true)
    $('#fecha_hora_fin').prop('disabled',true)
}
