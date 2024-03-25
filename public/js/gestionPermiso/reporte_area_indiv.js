function buscarPermisos(){
    let fecha_inicial=$('#bus_fecha_ini').val()
    let fecha_final=$('#bus_fecha_fin').val()
    let area=$('#area').val()
   
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

    if(area=="" || area==null){ 
        alertNotificar("Seleccione una área","error")
        return 
    }
    

    vistacargando("m", "Espere por favor")
    $.get('filtra-area-permiso-ind/'+fecha_inicial+'/'+fecha_final+'/'+area, function(data){
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
