function buscarPermisos(){
    let mes=$('#mes').val()
    
    if(mes==""){ 
        alertNotificar("Seleccione una fecha","error")
        return 
    }

    // window.location.href='filtra-gpr/'+mes
    // return

    vistacargando("m", "Espere por favor")
    $.get('filtra-gpr/'+mes, function(data){
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
