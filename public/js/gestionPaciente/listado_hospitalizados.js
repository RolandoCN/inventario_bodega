function buscarPacientes(){
    let fecha=$('#fecha_hosp').val()
    let cmb_servicio=$('#cmb_servicio').val()

    // if(fecha==""){
    //     alertNotificar("Seleccione la fecha", "error")
    //     return
    // }
    fecha=0;
    if(cmb_servicio==""){
        alertNotificar("Seleccione el servicio", "error")
        return
    }

    vistacargando("m","Espere por favor")
    
    $.get("paciente-hospitalizados/"+fecha+"/"+cmb_servicio, function(data){
        console.log(data)
        vistacargando("")
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            return;   
        }
        alertNotificar("El documento se descargara en unos segundos...","success")
        // verpdf(data.pdf,'R')
        window.location.href="descargar-reporte/"+data.pdf

       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}