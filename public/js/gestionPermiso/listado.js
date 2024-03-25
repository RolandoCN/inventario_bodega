function buscarPermisos(){
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

    $("#tabla_permiso tbody").html('');

	$('#tabla_permiso').DataTable().destroy();
	$('#tabla_permiso tbody').empty(); 
    
    // limpiarCampos()
    var num_col = $("#tabla_permiso thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_permiso tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);


    $('#fecha_ini_rep').html('')
    $('#fecha_fin_rep').html('')

    $('#tituloCabecera').html(`<button type="buttton" onclick="cancelar()" class="btn btn-sm btn-danger">Atras</button> `)

    
    $.get('filtra-permiso/'+fecha_inicial+'/'+fecha_final, function(data){
        console.log(data)
        
        if(data.error==true){
			$("#tabla_permiso tbody").html('');
			$("#tabla_permiso tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
			alertNotificar(data.mensaje,"error");
            cancelar()
			return;   
		}
		if(data.error==false){
			if(data.resultado.length==0){
				$("#tabla_permiso tbody").html('');
				$("#tabla_permiso tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
				alertNotificar("No se encontró información","error");
                cancelar()
				return;
			}
			
			$("#tabla_permiso tbody").html('');
            $('#fecha_ini_rep').html(fecha_inicial)
            $('#fecha_fin_rep').html(fecha_final)
          
            
            let contador=0
			$.each(data.resultado,function(i, item){
                let fecha_hora_ini=""
                let fecha_hora_fin=""
                if(item.tipo=='POR DÍA'){
                    fecha_hora_ini=item.fecha_hora_ini.split(" ")
                    fecha_hora_ini=fecha_hora_ini[0]
                    fecha_hora_fin=item.fecha_hora_fin.split(" ")
                    fecha_hora_fin=fecha_hora_fin[0]
                }else{
                    fecha_hora_ini=item.fecha_hora_ini
                    fecha_hora_fin=item.fecha_hora_fin
                }  
                let des_area=""
                if(item.area_de!=null){
                    des_area=item.area_de
                }else{
                    des_area=""
                }

				$('#tabla_permiso').append(`<tr>
                                                <td style="width:15%; vertical-align:middle">
                                                    ${item.funcionario}
                                                    
                                                </td>

                                                <td style="width:15%;  text-align:left; vertical-align:middle">
                                                    ${des_area}
                                                </td>
                                               
                                                <td style="width:20%; text-align:left">
                                                    <li> <b>Tipo:</b> ${item.tipo}
                                                    <li> <b>Desde:</b> ${fecha_hora_ini} 
                                                    <li> <b>Hasta:</b>  ${fecha_hora_fin}
                                                </td>
                                                <td style="width:15%; text-align:left; vertical-align:middle">
                                                    ${item.just}
                                                </td>
                                               
                                                <td style="width:20%; text-align:left; vertical-align:middle">
                                                    ${item.observacion}
                                                </td>

                                                <td style="width:15%; text-align:left; vertical-align:middle">

                                                    <button type="button" class="btn btn-xs btn-primary" onclick="Detalle(${item.id_permiso})">Detalle</button>

                                                    <button type="button" class="btn btn-xs btn-info" onclick="editar(${item.id_permiso})">Editar</button>

                                                    <button type="button" class="btn btn-xs btn-danger" onclick="Eliminar(${item.id_permiso})">Eliminar</button>
                                                </td>

                                                
											
										</tr>`);
			})
            if(contador>0){
                $('.btn_aprobacion').hide()
            }else{
                $('.btn_aprobacion').show()
            }
		  
			cargar_estilos_datatable('tabla_permiso');
		}
    })  

}

function Eliminar(id_permiso){
    
    if(confirm('¿Quiere eliminar el registro?')){
        vistacargando("m","Espere por favor")
        $.get("eliminar-permiso/"+id_permiso, function(data){
            vistacargando("")          
            if(data.error==true){
                alertNotificar(data.mensaje,"error");
                return;   
            }
    
            alertNotificar(data.mensaje,"success");
            buscarPermisos()
            
        }).fail(function(){
            vistacargando("")
            alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
        });
    }
       
    
}

function Detalle(idpermiso){
    limpiarCampos()
    vistacargando("m","Espere por favor")
    $.get("detalle-permiso/"+idpermiso, function(data){
        console.log(data)
        vistacargando("")
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            return;   
        }
        if(data.resultado==null){
            alertNotificar("La persona ya no se puede editar","error");
            return;   
        }
        let func_ced=data.resultado.funcionario
        func_ced=func_ced.split(' - ')

        

        if(data.resultado.tipo=="POR DÍA"){
            $('#cant_hora_dia_txt').html('Cantidad Dias')
            $('#cant_dia_hora').html(data.resultado.cant_dia)

            //solo mostramos la fecha sin hora
            let separa_fecha_hora_ini=data.resultado.fecha_hora_ini
            separa_fecha_hora_ini=separa_fecha_hora_ini.split(" ")
            
            let separa_fecha_hora_fin=data.resultado.fecha_hora_fin
            separa_fecha_hora_fin=separa_fecha_hora_fin.split(" ")

            $('#desde_detalle').html(separa_fecha_hora_ini[0])
            $('#hasta_detalle').html(separa_fecha_hora_fin[0])

        }else{
            $('#cant_hora_dia_txt').html('Cantidad Horas')
            $('#cant_dia_hora').html(data.resultado.cant_hora)
            //fecha y hora
            $('#desde_detalle').html(data.resultado.fecha_hora_ini)
            $('#hasta_detalle').html(data.resultado.fecha_hora_fin)
        }
        
        $('#cedula_detalle').html(func_ced[0])
        $('#funcionario_detalle').html(func_ced[1])
        $('#area_detalle').html(data.resultado.area_de)
        $('#tipo_detalle').html(data.resultado.tipo)
        $('#justificacion_detalle').html(data.resultado.just)
        
        $('#motivo_detalle').html(data.resultado.observacion)
        $('#user_ingresa').html(data.resultado.usuario_ingresa)
        $('#fecha_ingresa').html(data.resultado.fecha_registro)

        if(data.resultado.usuario_actualiza!=null){
            $('.actualiza_detalle').show()
            $('#user_actualiza').html(data.resultado.usuario_actualiza)
            $('#fecha_actualiza').html(data.resultado.fecha_act)
        }else{
            $('.actualiza_detalle').hide()
            $('#user_actualiza').html('')
            $('#fecha_actualiza').html('')
        }

        $('#modal_detalle').modal('show')
        
       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}
//cierra la modal detalle
function cerrar(){
    $('#modal_detalle').modal('hide')
}

function editar(idpermiso){
    limpiarCampos()
    vistacargando("m","Espere por favor")
    $.get("detalle-permiso/"+idpermiso, function(data){
        console.log(data)
        vistacargando("")
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            return;   
        }
        if(data.resultado==null){
            alertNotificar("La persona ya no se puede editar","error");
            return;   
        }

       
        $('#cmb_persona').html('');				
		$('#cmb_persona').append(`<option value="${data.resultado.id_funcionario}">${data.resultado.funcionario}</option>`).change();
		$("#cmb_persona").trigger("chosen:updated"); // actualizamos el combo 

        $('#tipo_permiso').val(data.resultado.id_tipo_permiso).trigger('change.select2')
        $('#tipo_justificacion').val(data.resultado.id_justificacion).trigger('change.select2')

        $('#observacion').val(data.resultado.observacion)

        if(data.resultado.id_tipo_permiso==1){
            $('#fecha_hora_ini').val(data.resultado.fecha_hora_ini)
            $('#fecha_hora_fin').val(data.resultado.fecha_hora_fin)
            $('#cant_horas').val(data.resultado.cant_hora)
            $('#fecha_hora_fin').prop('disabled', false)
        }else{
            let solo_fecha_ini=data.resultado.fecha_hora_ini
            solo_fecha_ini=solo_fecha_ini.split(" ")
            
            let solo_fecha_fin=data.resultado.fecha_hora_fin
            solo_fecha_fin=solo_fecha_fin.split(" ")
            $('#fecha_ini_').val(solo_fecha_ini[0])
            $('#fecha_fin_').val(solo_fecha_fin[0])
            $('#cant_dias').val(data.resultado.cant_dia)
            $('#cant_dias_horas').val(data.resultado.cant_hora)
            $('#fecha_hora_fin').prop('disabled', false)
            $('#fecha_fin_').prop('disabled', false)
        }
        globalThis.HorasPerm=data.resultado.cant_hora
        globalThis.idPermisoEditar=idpermiso
        // calcularDias()
     
        $('#form_actualiza').show()
        $('#listado_permiso').hide()
        $('#tituloCabecera').html('Formulario Actualización')
       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}
globalThis.EsAdministrativo="N"

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
        console.log(data)
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

            EsAdministrativo=data.resultado.administrativo
            
            calcularDias()
		
		}
    })  

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
        fecha_ini=$('#fecha_ini_').val()
        fecha_fin=$('#fecha_fin_').val()
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
    
    let f_ini=$('#fecha_ini_').val()
    let f_fin=$('#fecha_fin_').val()

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
        // HorasPerm=HorasPerm.split(":") 
        // $('#cant_dias_horas').val(HorasPerm[0])
        $('#cant_dias_horas').val(hora_dia)
    }

    seleccionJustificacion()
}

//formulario de actalizacion
$("#form_actualiza").submit(function(e){
    e.preventDefault();
    
    //validamos los campos obligatorios
    let funcionario=$('#cmb_persona').val()
    let tipo_permiso=$('#tipo_permiso').val()
    let fecha_ini=$('#fecha_ini_').val()
    let fecha_fin=$('#fecha_fin_').val()
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
    let tipo="PUT"
    let url_form="actualizar-permiso/"+idPermisoEditar
     
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
            $('#form_actualiza').hide()
            buscarPermisos()
                            
        }, error:function (data) {
            console.log(data)

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})
