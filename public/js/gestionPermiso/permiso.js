
$('#cmb_persona').select2({
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

function limpiarCamposSinFuncionario(){
    limpiarCamposDesdeHasta()
    $('#fecha_hora_fin').val('')

    $('#div_dia').hide()
    $('#div_hora').hide()

    $('#area').val('')
    $('#ambito').val('')
    $('#ambito_ley').val('')
    $('#observacion').val('')
    $('#tipo_permiso').val('').trigger('change.select2')
    $('#tipo_justificacion').val('').trigger('change.select2')
    // $('#cmb_persona').val('').trigger('change.select2')
    $('#cant_dias_horas').val('')
}

function buscarFuncionario(){
    let idPers=$('#cmb_persona').val()
    if(idPers=="" || idPers==null){ return }

    limpiarCamposSinFuncionario()

    $.get('info-funcionario/'+idPers, function(data){
       
        if(data.error==true){
			
			alertNotificar(data.mensaje,"error");
			return;   
		}
		if(data.error==false){
			if(data.resultado==null){
				alertNotificar("No se encontró información","error");
				return;
			}
            
            $('#area').val(data.resultado.nombre_area)
            $('#ambito').val(data.resultado.nombre_amb)
            $('#ambito_ley').val(data.resultado.nombre_ley)

            globalThis.EsAdministrativo=data.resultado.administrativo
		
		}
    })  

}

function fecha_inicial(e){
    
    var fecha_i=e.target.value;
    $('#fecha_fin').val('');
    var fecha_fin = document.getElementById("fecha_fin");
    fecha_fin.setAttribute("min", fecha_i);
    $('#fecha_fin').prop('disabled',false)

}

function fecha_inicial_hora(e){
    
    var fecha_i=e.target.value;
   
    $('#fecha_hora_fin').val('');
    var fecha_fin = document.getElementById("fecha_hora_fin");
  
    fecha_fin.setAttribute("min", fecha_i);
    
    let max=fecha_i.split("T")
    let hora_max="23:59"
    fecha_fin.setAttribute("max", max[0]+'T'+hora_max);

    $('#fecha_hora_fin').prop('disabled',false)
}


function tipoPermiso(){
    //si es x hora mostramos el input date caso contratio input datetime
    let tipo_permiso=$('#tipo_permiso').val()
    if(tipo_permiso=="" || tipo_permiso==null){return}
    let idPers=$('#cmb_persona').val()
    if(idPers=="" || idPers==null){
        alertNotificar("Debe seleccionar primero al funcionario", "error")
        $('#tipo_permiso').val('').trigger('change.select2')
        return
    }

    if(tipo_permiso==2){
       
        limpiarCamposDesdeHasta()
        $('#div_dia').show()
        $('#div_hora').hide()

           
    }else if (tipo_permiso==1){
       
        limpiarCamposDesdeHasta()
        $('#div_dia').hide()
        $('#div_hora').show()
      
    }
}

function notificarPermiso(tipo_justificacion, idpers){
  
    $('#permiso_alerta').html('')
    $('#permiso_alerta').hide()

   
    //año inicio y año fin
    let fecha_ini=""
    let fecha_fin=""
    if($('#fecha_ini').val()!="" && $('#fecha_ini').val()!=""){
        fecha_ini=$('#fecha_ini').val()
        fecha_fin=$('#fecha_fin').val()
    }else{
        fecha_ini=$('#fecha_hora_ini').val()
        fecha_fin=$('#fecha_hora_fin').val()

        fecha_ini=fecha_ini.split("T");
        fecha_ini=fecha_ini[0]

        fecha_fin=fecha_fin.split("T");
        fecha_fin=fecha_fin[0]
    }
    
    if(fecha_ini=="" && fecha_fin==""){ 
        return
    }
   
    vistacargando("m","Espere por favor")
    $.get("notifica-just-funci/"+tipo_justificacion+"/"+idpers+"/"+fecha_ini+"/"+fecha_fin, function(data){
        vistacargando("")
        console.log(data)
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            return;   
        }
        if(data.mensaje.cantidadHoras!=null){

            
            let txt_just=$('#tipo_justificacion option:selected').text()

            //si es mayor a 64 horas es decir 8 dias empezamos a notificar cuando sea calamidad
            if(tipo_justificacion==2 || txt_just=='CALAMIDAD DOMÉSTICA'){
                if(data.mensaje.cantidadHoras>64){
                    let dias=data.mensaje.cantidadHoras/8
                    dias=Math.floor(dias)
                   
                    $('#permiso_alerta').show()
                    $('#permiso_alerta').append(`<div class="form-group">
                        <label for="inputPassword3" class="col-sm-3 control-label"></label>
                        <div class="col-sm-8">
                            <div class="alert alert-info alert-dismissible" style="margin-bottom:0px">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <span>
                                El funcionario ha solicitado ${dias} días de permiso en el año para el tipo de justificación ${txt_just}.</span>
                            </div>
                        </div>
                        
                    </div>`)
                    $('#tipo_justificacion').prop('disabled',true)
                    $('.btnguarda').prop('disabled',true)
                    setTimeout(() => {
                        $('#permiso_alerta').hide()
                        $('#tipo_justificacion').prop('disabled',false)
                        $('.btnguarda').prop('disabled',false)
                        // $('#tipo_justificacion').val('').trigger('change.select2')
                    }, 7000);

                
                    return
                }
            }    
            
            //si es mayor a 120 horas es decir 15 dias empezamos a notificar cuando sea descarga a vacaciones
            if(tipo_justificacion==7 || txt_just=='DESCARGO A VACACIONES'){
                if(data.mensaje.cantidadHoras>120){
                    let dias=data.mensaje.cantidadHoras/8
                    dias=Math.floor(dias)
                   
                    $('#permiso_alerta').show()
                    $('#permiso_alerta').append(`<div class="form-group">
                        <label for="inputPassword3" class="col-sm-3 control-label"></label>
                        <div class="col-sm-8">
                            <div class="alert alert-info alert-dismissible" style="margin-bottom:0px">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <span>
                                El funcionario ha solicitado ${dias} días de permiso en el año para el tipo de justificación ${txt_just}.</span>
                            </div>
                        </div>
                        
                    </div>`)
                    $('#tipo_justificacion').prop('disabled',true)
                    $('.btnguarda').prop('disabled',true)
                    setTimeout(() => {
                        $('#permiso_alerta').hide()
                        $('#tipo_justificacion').prop('disabled',false)
                        $('.btnguarda').prop('disabled',false)
                       
                    }, 7000);
                    return
                }
            }  
            
            //si es mayor a 720 horas es decir 90 dias empezamos a notificar cuando sea reposo medico
            if(tipo_justificacion==6 || txt_just=='REPOSO MEDICO IESS'){
                if(data.mensaje.cantidadHoras>720){
                    let dias=data.mensaje.cantidadHoras/8
                    dias=Math.floor(dias)
                    // alertNotificar("El funcionario ha solicitado "+dias+" días de permiso en el año para el tipo de justificación "+txt_just,"info");

                    $('#permiso_alerta').show()
                    $('#permiso_alerta').append(`<div class="form-group">
                        <label for="inputPassword3" class="col-sm-3 control-label"></label>
                        <div class="col-sm-8">
                            <div class="alert alert-info alert-dismissible" style="margin-bottom:0px">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <span>
                                El funcionario ha solicitado ${dias} días de permiso en el año para el tipo de justificación ${txt_just}.</span>
                            </div>
                        </div>
                        
                    </div>`)
                    $('#tipo_justificacion').prop('disabled',true)
                    $('.btnguarda').prop('disabled',true)
                    setTimeout(() => {
                        $('#permiso_alerta').hide()
                        $('#tipo_justificacion').prop('disabled',false)
                        $('.btnguarda').prop('disabled',false)
                       
                    }, 7000);
                    return
                }
            }  

            //si es mayor a 240 horas es decir 30 dias empezamos a notificar cuando sea certifiado medico laboral o particular
            if(tipo_justificacion==4 || tipo_justificacion==15){
                if(data.mensaje.cantidadHoras>240){
                    let dias=data.mensaje.cantidadHoras/8
                    dias=Math.floor(dias)
                    
                    $('#permiso_alerta').show()
                    $('#permiso_alerta').append(`<div class="form-group">
                        <label for="inputPassword3" class="col-sm-3 control-label"></label>
                        <div class="col-sm-8">
                            <div class="alert alert-info alert-dismissible" style="margin-bottom:0px">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <span>
                                El funcionario ha solicitado ${dias} días de permiso en el año para el tipo de justificación certificado médico.</span>
                            </div>
                        </div>
                        
                    </div>`)
                    $('#tipo_justificacion').prop('disabled',true)
                    $('.btnguarda').prop('disabled',true)
                    setTimeout(() => {
                        $('#permiso_alerta').hide()
                        $('#tipo_justificacion').prop('disabled',false)
                        $('.btnguarda').prop('disabled',false)
                       
                    }, 7000);
                    return
                }
            }  
           
        }
            
       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}

function seleccionJustificacion(){
    let tipo_justificacion=$('#tipo_justificacion').val()
    if(tipo_justificacion=="" || tipo_justificacion==null){return}
    let idPers=$('#cmb_persona').val()
    if(idPers=="" || idPers==null){
        alertNotificar("Debe seleccionar primero al funcionario", "error")
        $('#tipo_justificacion').val('').trigger('change.select2')
        return
    }

    notificarPermiso(tipo_justificacion,idPers)
}

function limpiarCamposDesdeHasta(){
    $('#fecha_ini').val('')
    $('#fecha_fin').val('')
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

function limpiarCampos(){
    limpiarCamposDesdeHasta()
    $('#fecha_hora_fin').val('')

    $('#div_dia').hide()
    $('#div_hora').hide()

    $('#area').val('')
    $('#ambito').val('')
    $('#ambito_ley').val('')
    $('#observacion').val('')
    $('#tipo_permiso').val('').trigger('change.select2')
    $('#tipo_justificacion').val('').trigger('change.select2')
    $('#cmb_persona').val('').trigger('change.select2')
}
function calcularHoras(){
    let h_ini=$('#fecha_hora_ini').val()
    let h_fin=$('#fecha_hora_fin').val()

    if(h_ini >= h_fin){
        $('#cant_horas').val('')
        alertNotificar("La hora final debe ser mayor a la hora inicial", "error")
        return
    }


    var h_ini_time = new Date(h_ini).getTime();
    var h_fin_time = new Date(h_fin).getTime();
    //La diferencia se da en milisegundos así que debes dividir entre 1000
    var calcula = h_fin_time-h_ini_time 
 
    let diferencia=calcula/(1000*60)
    let cant_horas=0;
   
    while(diferencia >=60){
        diferencia=diferencia-60
        cant_horas=cant_horas+1
    }

    let cant_minutos=0
    if(cant_horas>1){
        cant_minutos=diferencia
    }else{
        cant_minutos=diferencia
    }
    
    if(cant_horas<10){
        cant_horas="0"+cant_horas
    }

    if(cant_minutos<10){
        cant_minutos="0"+cant_minutos
    }

    $('#cant_horas').val(cant_horas+":"+cant_minutos)

    seleccionJustificacion()
}

function calcularDias(){
    let f_ini=$('#fecha_ini').val()
    let f_fin=$('#fecha_fin').val()

    if(f_ini > f_fin){
        alertNotificar("La fecha final debe ser mayor a la fecha inicial", "error")
        return
    }
   
    var fechaInicio = new Date(f_ini).getTime();
    var fechaFin    = new Date(f_fin).getTime();

    var diff = fechaFin - fechaInicio;
   
    $('#cant_dias').val(diff/(1000*60*60*24)+1)

    //si es administrativo son 8 horas x dias
    let hora_dia=(diff/(1000*60*60*24)+1)*8
    globalThis.HoraXDiasGlobal=hora_dia
    if(EsAdministrativo=="S"){
        $('#cant_dias_horas').val(hora_dia)
    }else{
        $('#cant_dias_horas').val(hora_dia)
    }

    seleccionJustificacion()
}

$("#form_registro_permiso").submit(function(e){
    e.preventDefault();
    
    //validamos los campos obligatorios
    let funcionario=$('#cmb_persona').val()
    let tipo_permiso=$('#tipo_permiso').val()
    let fecha_ini=$('#fecha_ini').val()
    let fecha_fin=$('#fecha_fin').val()
    let fecha_hora_ini=$('#fecha_hora_ini').val()
    let fecha_hora_fin=$('#fecha_hora_fin').val()
    let tipo_justificacion=$('#tipo_justificacion').val()
    let observacion=$('#observacion').val()
    let cant_dia_hora=$('#cant_dias_horas').val()
   
    if(funcionario=="" || funcionario==null){
        alertNotificar("Debe seleccionar el funcionario","error")
        return
    } 

    if(tipo_permiso=="" || tipo_permiso==null){
        alertNotificar("Debe seleccionar el tipo permiso","error")
        return
    } 

    //si es x dias
    if(tipo_permiso==2){
        if(fecha_ini=="" || fecha_ini==null){
            alertNotificar("Debe seleccionar la fecha inicial ","error")
            $('#fecha_ini').focus()
            return
        } 

        if(fecha_fin=="" || fecha_fin==null){
            alertNotificar("Debe seleccionar la fecha final ","error")
            $('#fecha_fin').focus()
            return
        } 

        
        if(fecha_ini > fecha_fin){
            alertNotificar("La fecha final debe ser mayor a la fecha inicial", "error")
            return
        }

        
        //si es administrativo solo permitimos 8 horas x dia
        let cant_dias=$('#cant_dias').val()
        if(EsAdministrativo=="S" ){
            if(cant_dia_hora!=HoraXDiasGlobal){
                alertNotificar("La cantidad de hora en "+cant_dias+ " dia(s) no puede diferente de " +HoraXDiasGlobal+" para el área administrativa", "error")
                return
            }
        }else{
           
            let min=cant_dias * 6
            let max=cant_dias * 24
            if(cant_dia_hora>max || cant_dia_hora<min){
                alertNotificar("La cantidad de hora no puede ser mayor a "+max+", ni menor a "+min+" en "+cant_dias+" dias", "error")
                return
            }
        }

    }
    //si es x horas
    if(tipo_permiso==1){
        if(fecha_hora_ini=="" || fecha_hora_ini==null){
            alertNotificar("Debe seleccionar la fecha hora inicial ","error")
            $('#fecha_hora_ini').focus()
            return
        } 

        if(fecha_hora_fin=="" || fecha_hora_fin==null){
            alertNotificar("Debe seleccionar la fecha hora final ","error")
            $('#fecha_ini').focus()
            return
        } 

        if(fecha_hora_ini >= fecha_hora_fin){
            $('#cant_horas').val('')
            alertNotificar("La hora final debe ser mayor a la hora inicial", "error")
            return
        }
    }

    if(tipo_justificacion=="" || tipo_justificacion==null){
        alertNotificar("Debe seleccionar el tipo justificación","error")
        return
    } 

    if(observacion=="" || observacion==null){
        alertNotificar("Debe ingresar la observacion ","error")
        $('#observacion').focus()
        return
    }
        
   
    vistacargando("m","Espere por favor")
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    //comprobamos si es registro o edicion
    let tipo="POST"
    let url_form="guardar-permiso"
     
    var FrmData=$("#form_registro_permiso").serialize();

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
            limpiarCampos()
            alertNotificar(data.mensaje,"success");
           
                            
        }, error:function (data) {
            console.log(data)

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})
