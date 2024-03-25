
$('#id_funcionario').select2({
    placeholder: 'Seleccione una opción',
    ajax: {
    url: 'buscar-funcionario',
    dataType: 'json',
    delay: 250,
    processResults: function (data) {
        
        return {
        results:  $.map(data, function (item) {
                return {
                    text: item.cedula+" - "+item.nombres+"  "+item.apellidos,
                    id: item.id_funcionario
                }
            })
        };
    },
    cache: true
    }
});


function buscarPermisos(){
    let fecha_inicial=$('#bus_fecha_ini').val()
    let fecha_final=$('#bus_fecha_fin').val()
    let id_funcionario=$('#id_funcionario').val()

    if(id_funcionario=="" || id_funcionario==null){ 
        alertNotificar("Seleccione un funcionario","error")
        return 
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
    
    vistacargando("m", "Espere por favor")
    $.get('filtra-funcionario/'+fecha_inicial+'/'+fecha_final+'/'+id_funcionario, function(data){
        console.log(data)
        vistacargando("")
        if(data.error==true){
            		
			alertNotificar(data.mensaje,"error");
			return;   
		}
		if(data.error==false){
						
			alertNotificar("El documento se descargará en unos segundos...", "success")
            window.location.href="descargar-pdf/"+data.pdf
		}
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });  

}
