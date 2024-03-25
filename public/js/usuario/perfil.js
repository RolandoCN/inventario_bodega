function cargaInfoPerfil(){

    cancelar_cambio_pass()

    $('#div_infor_perfil').show(200);
    $('#btn_password').show(200);
    $('#div_edit_perfil').hide(200);
    $('#btn_cancelar').hide();
    $('#btn_submit').hide();
    $("#btn_actualizar").show();

    spinner = '<span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>';
    $("#cedula_informacion").html(spinner+spinner+spinner);
    $("#correo_informacion").html(spinner+spinner+spinner);
    $("#celular_informacion").html(spinner+spinner+spinner);
    $("#direccion_informacion").html(spinner+spinner+spinner);
    $('#nombre_informacion').html(spinner+spinner+spinner);
    $('#foto_perfil').html(spinner+spinner+spinner);
    $('#user_informacion').html(spinner+spinner+spinner);
    $('#perfil_informacion').html(spinner+spinner+spinner);
    // cargaDato()
    $.get('dato-perfil',function(data){
        console.log(data)
       
        $('#cedula_informacion').html(data.data['persona'].tx_numident);
        $('#correo_informacion').html(data.data['persona'].tx_email);
        $('#celular_informacion').html(data.data['persona'].tx_telefono1);
        $('#direccion_informacion').html(data.data['persona'].tx_direccion);
        $('#nombre_informacion').html(data.data['persona'].tx_nombre +" "+ data.data['persona'].tx_apellido) ;
        $('#user_informacion').html(data.data['tx_login']);
        $('#perfil_informacion').html(data.data['perfil'].nombre_perfil.tx_descripcion) ;

        $('#foto_perfil').val('')

        var foto=$('#foto_perfil');
        foto.attr("src", null);

        var foto_plantilla=$('.img_perfil');
        foto_plantilla.attr("src",null)

        if(data.data.persona.tx_rutafoto!=null){
            //foto de la persona dentro de la modal
            var foto=$('#foto_perfil');
            foto.attr("src","/FotoPersona/"+data.data.persona.tx_rutafoto)

            //foto de la persona en la parte superior derecha del sistema (plantilla)
            var foto_plantilla=$('.img_perfil');
            foto_plantilla.attr("src","/FotoPersona/"+data.data.persona.tx_rutafoto)
            
        }else{
            var foto=$('#foto_perfil');
            foto.attr("src","/assets/images/profile.jpg")

            var foto_plantilla=$('.img_perfil');
            foto_plantilla.attr("src","/assets/images/profile.jpg")

        }
       
       
    }).fail(function(){
        alertNotificar('Incovenientes al obtener información, intente nuevamente','error');
        $("#btn_actualizar").html('<span class="fa fa-refresh"></span> Actualizar');
        $("#btn_actualizar").attr("disabled", false);
    });

    
}

function mostrar_actualizar(){
    
    spinner = '<span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>';

    $("#btn_actualizar").html(`Espere ${spinner}${spinner} `);
    $("#btn_actualizar").attr("disabled", true);
    $('#btn_password').attr("disabled", true);

    $('#correo_perfil').val('');
    $('#direccion_perfil').val('');
    $('#celular_perfil').val('');
    $('#foto_perfil').val('');

    $.get('dato-perfil',function(data){
        
        $('#correo_perfil').val(data.data['persona'].tx_email);
        $('#direccion_perfil').val(data.data['persona'].tx_direccion);
        $('#celular_perfil').val(data.data['persona'].tx_telefono1);
        $('#div_infor_perfil').hide(200);
        $('#btn_password').hide(200);
        $('#div_edit_perfil').show(200);
        $('#btn_cancelar').show();
        $('#btn_submit').show();
        $("#btn_actualizar").hide();
        $("#btn_actualizar").html('<span class="fa fa-refresh"></span> Actualizar');
        $("#btn_actualizar").attr("disabled", false);
        $('#btn_password').attr("disabled", false);
    }).fail(function(){
        alertNotificar('Incovenientes al obtener información, intente nuevamente','error');
        $("#btn_actualizar").html('<span class="fa fa-refresh"></span> Actualizar');
        $("#btn_actualizar").attr("disabled", false);
        $('#btn_password').attr("disabled", false);
    });
}

function cancelar_actualizacion_perf(){
   
//    $('#modal_perfil').modal('hide')
    cargaInfoPerfil()

}

function cancelar_cambio_pass(){
    $('#form_cambio_contra').hide();
    $('#seccion_perfil').show();
}

$('#form_perfil_').submit(function (e) {
    $('#mensajes_perfil').html('');
    e.preventDefault();
    
    if($('#correo_perfil').val()==''){
        alertNotificar('Ingrese el correo','error');
        return;
    }
    if($('#direccion_perfil').val()==''){
        alertNotificar('Ingrese la dirección','error');
        return;
    }
    if($('#celular_perfil').val()==''){
        alertNotificar('Ingrese el celular','error');
        return;
    }

    
    vistacargando("m", "Espere por favor")
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    $.ajax({
        url:'edit-perfil',
        method: 'POST',
        data: new FormData(this),
        contentType: false,
        cache: false,
        processData:false,

        success: function (data) {  
            vistacargando("")
        
            if(data['error']==true){
                alertNotificar(data.detalle)
                return;
            }
           
            alertNotificar(data.detalle,"success")
            var foto=$('#foto_perfil');
            foto.attr("src", null);
            // $('#modal_perfil').modal('hide')
            cancelar_actualizacion_perf()
       },
       error: function(e){
        vistacargando("")
        alertNotificar("Ocurrio un error");
       
       }      
   });
 
 });

var totalTime = 10;
function updateClock() {
    
    if(totalTime==0){
        window.location="/"; 
    }else{
        totalTime-=1;
    setTimeout("updateClock()",1000);
    }
}

$('#celular_perfil').on('input', function() {
    this.value = this.value.replace(/[^0-9]/g,'');
 });

$('#form_cambio_claves').submit(function (e) {
    vistacargando("m", "Espere por favor")
    e.preventDefault();
    if($('#clave_nueva').val()!=$('#clave_nueva_confirm').val()){
        vistacargando("")
        alertNotificar("Contraseñas no coinciden")

        return;
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        type: "POST",
            url: 'cambiar-clave',
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData:false,
        success: function (data) {
            vistacargando("")

            if(data['error']==true){
                
                alertNotificar(data.detalle)
                
                return;
            }
            alertNotificar(data.detalle,"success")
            cancelar_cambio_pass()
        },
        error: function(e){
        vistacargando("")
            alertNotificar(" Inconvenientes al procesar la solicitud intente nuevamente")
        }
    });

});

function modal_cambio_clave(){
    $('#clave_actual').val('');
    $('#clave_nueva').val('');
    $('#clave_nueva_confirm').val('');
    $('#form_cambio_contra').show();
    $('#seccion_perfil').hide();
}
