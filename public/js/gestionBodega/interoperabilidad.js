function Filtrados(){
    var filtro=$('#cmb_filtra').val()
    if(filtro==""){
        return
    }else{

        if(filtro=="M"){
            $('#tipo_item').html('Cum');
        }else{
            $('#tipo_item').html('Cudim');
        }

        $("#tabla_interoperabilidad tbody").html('');

        $('#tabla_interoperabilidad').DataTable().destroy();
        $('#tabla_interoperabilidad tbody').empty(); 
        
        var num_col = $("#tabla_interoperabilidad thead tr th").length; //obtenemos el numero de columnas de la tabla
        $("#tabla_interoperabilidad tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);
            
        
        $.get('filtra-interoperabilidad/'+filtro, function(data){
            
            if(data.error==true){
                $("#tabla_interoperabilidad tbody").html('');
                $("#tabla_interoperabilidad tbody").html(`<tr><td colspan="${num_col}" class="text-center">No existen registros</td></tr>`);
                alertNotificar(data.mensaje,"error");
                return;   
            }
            if(data.error==false){
                if(data.resultado.length==0){
                    $("#tabla_interoperabilidad tbody").html('');
                    $("#tabla_interoperabilidad tbody").html(`<tr><td colspan="${num_col}" class="text-center">No existen registros</td></tr>`);
                    alertNotificar("No se encontró información","error");
                    return;
                }
                
                $("#tabla_interoperabilidad tbody").html('');
                let clase=""
                $.each(data.resultado,function(i, item){
                    let lote=""
                    if(item.lote=="null" || item.lote==null){
                        lote=""
                    }else{
                        lote=item.lote
                    }
                    $('#tabla_interoperabilidad').append(`<tr class="${clase}">
                        <td style="width:10%; vertical-align:middle">
                            ${item.codigo_item} 
                            
                        </td>

                        <td style="width:45%;  text-align:left; vertical-align:middle">
                            ${item.detalle}
                        </td>

                        <td style="width:10%;  text-align:left; vertical-align:middle">
                            ${lote}
                        </td>
                    
                        <td style="width:10%; text-align:center">
                            ${item.existencia}
                        
                        
                        </td>
                        <td style="width:10%; text-align:center; vertical-align:middle">
                            ${item.felabora}
                        </td>
                    
                        <td style="width:10%; text-align:center; vertical-align:middle">
                            ${item.fcaduca}
                        </td>
                        <td style="width:15%; text-align:right; vertical-align:middle">
                            $ ${item.precio}
                        </td>
                        
                    
                    </tr>`);
                })

                cargar_estilos_datatable('tabla_interoperabilidad');
            
            }
        }).fail(function(){
           
            alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
            $("#tabla_interoperabilidad tbody").html('');
            $("#tabla_interoperabilidad tbody").html(`<tr><td colspan="${num_col}" class="text-center">Se produjo un error, por favor intentelo más tarde</td></tr>`);
        });
    }
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

function verReportesIindividual(){
    alertNotificar("Pendiente")
    return
}

function interOperar(){
    var filtro=$('#cmb_filtra').val()
   
        vistacargando("m","Espere por favor")
        $.get('enviar-nacional/'+filtro, function(data){
            vistacargando("")
            if(data.error==true){
                alertNotificar(data.mensaje,"error");
                return;   
            }
            if(data.error==false){
                if(data.resultado.length==0){
                  
                    alertNotificar("No se encontró información","error");
                    return;
                }
                
                
            }
        }).fail(function(){
            vistacargando("")
            alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
        });
    
}