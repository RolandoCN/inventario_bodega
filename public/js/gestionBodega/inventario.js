
function buscarInventario(){
    
    let opcion=$('#cmb_opcion').val()
    
    if(opcion=="Individual"){
        dataIndividual()
    }else{
        dataGlobal()
    }

}
globalThis.PrimeraVes=1
function dataIndividual(){

    let cmb_bodega=$('#cmb_bodega').val()
    let cmb_tipo=$('#cmb_tipo').val()
    let opcion=$('#cmb_opcion').val()
   
    if(cmb_bodega==""){ 
        alertNotificar("Seleccione una bodega","error")
        return 
    }

    if(cmb_tipo==""){ 
        alertNotificar("Seleccione un lugar","error")
      
        return 
    }

    if(opcion==""){ 
        alertNotificar("Seleccione un opcion","error")
       
        return 
    }

    $('#content_consulta').hide()
    $('#listado_individual').show()

    $("#tabla_inventario tbody").html('');

	$('#tabla_inventario').DataTable().destroy();
	$('#tabla_inventario tbody').empty(); 
    
    // limpiarCampos()
    var num_col = $("#tabla_inventario thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_inventario tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);


    $('#fecha_ini_rep').html('')
    $('#fecha_fin_rep').html('')

    $('#tituloCabecera').html(`<button type="buttton" onclick="cancelar()" class="btn btn-sm btn-danger">Atras</button> `)

    
    $.get('filtra-inventario2/'+cmb_bodega+'/'+cmb_tipo+'/'+opcion, function(data){
        
        if(data.error==true){
			$("#tabla_inventario tbody").html('');
			$("#tabla_inventario tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
			alertNotificar(data.mensaje,"error");
            cancelar()
			return;   
		}
		if(data.error==false){
			if(data.resultado.length==0){
				$("#tabla_inventario tbody").html('');
				$("#tabla_inventario tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
				alertNotificar("No se encontró información","error");
                cancelar()
				return;
			}
			
			$("#tabla_inventario tbody").html('');
            // $('#fecha_ini_rep').html(fecha_inicial)
            // $('#fecha_fin_rep').html(fecha_final)
          
            globalThis.DataResultado=data.resultado
            globalThis.Meses3=data.meses
		
           
            $('#cmb_filtra').html('');				
            $('#cmb_filtra').append(`<option value="Todos" selected>TODOS</option>
                                    <option value="Caducados">CADUCADOS</option>
                                    <option value="Porcaducar">POR CADUCAR</option>
                                    <option value="Rotura">ROTURA</option>`).change();
            $("#cmb_filtra").trigger("chosen:updated"); // actualizamos el combo 

            PrimeraVes=1

		}
    }).fail(function(){
        cancelar()
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
        $("#tabla_inventario tbody").html('');
		$("#tabla_inventario tbody").html(`<tr><td colspan="${num_col}">Se produjo un error, por favor intentelo más tarde</td></tr>`);
    });
}

function Filtrados(){
    var selecc=$('#cmb_filtra').val()
   

    $("#tabla_inventario tbody").html('');

	$('#tabla_inventario').DataTable().destroy();
	$('#tabla_inventario tbody').empty(); 
    
    // limpiarCampos()
    var num_col = $("#tabla_inventario thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_inventario tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);

   
    $("#tabla_inventario tbody").html('');
    
    let fecha_actual=$('#fecha_actual').val()
    let cmb_bodega=$('#cmb_bodega').val()
    let contador=0
    datoItemArray=[]
    $.each(DataResultado,function(i, item){
        console.log(item)
        let fechacad=item.fcaduca

        datoItemArray.push({'idprod_':item.idprod,'nombres_':item.detalle1});
        globalThis.datosItem=datoItemArray;
       
        let caducado=""
        let clase=""
        if(selecc=="Caducados"){
            if(fecha_actual > fechacad){
                let lote=""
                if(item.lote=="null" || item.lote==null){
                    lote=""
                }else{
                    lote=item.lote
                }
                if( fecha_actual >= fechacad){
                    caducado="caducado"
                    clase="color_caducado"
                }


                $('#tabla_inventario').append(`<tr class="${clase}">
                                            <td style="width:10%; vertical-align:middle">
                                                ${item.codigo_item} 
                                                
                                            </td>

                                            <td style="width:40%;  text-align:left; vertical-align:middle">
                                                ${item.detalle}
                                            </td>

                                            <td style="width:10%;  text-align:left; vertical-align:middle">
                                                ${lote}
                                            </td>
                                        
                                            <td style="width:10%; text-align:left">
                                                ${item.existencia}
                                            
                                            
                                            </td>
                                            <td style="width:10%; text-align:left; vertical-align:middle">
                                                ${item.fcaduca}
                                            </td>
                                        
                                            <td style="width:10%; text-align:right; vertical-align:middle">
                                                $ ${item.precio}
                                            </td>

                                            <td style="width:17%; text-align:center;">
                                                <button type="button" class="btn btn-xs btn-primary" onclick="actualizaProductoLote('${cmb_bodega}','${item.idbodprod}','${item.codigo_item}','${item.idprod}')">
                                                    <i class="fa fa-edit"></i>
                                                </button>

                                                <button type="button" class="btn btn-xs btn-success" onclick="kardexIndiv('${item.idbodprod}','BODEGA','${cmb_bodega}','${item.existencia}','${item.idprod}','${item.codigo_item}','${item.codigo_item}')">
                                                    <i class="fa fa-refresh"></i>
                                                </button>

                                            </td> 
                                            
                                        
                                    </tr>`);
            }
        }else if(selecc=="Todos"){

            let lote=""
            if(item.lote=="null" || item.lote==null){
                lote=""
            }else{
                lote=item.lote
            }
            
            if( fecha_actual > fechacad){
               
                caducado="caducado"
                clase="color_caducado"
            }else{
            
                if(fechacad < Meses3 && item.existencia >0)  {
                    
                    clase="color_x_caducar"
                }
                if( item.existencia <=0){
                    clase="color_rotura"
                }
            }

            $('#tabla_inventario').append(`<tr class="${clase}">
                                        <td style="width:10%; vertical-align:middle">
                                            ${item.codigo_item} 
                                            
                                        </td>

                                        <td style="width:40%;  text-align:left; vertical-align:middle">
                                            ${item.detalle}
                                        </td>

                                        <td style="width:10%;  text-align:left; vertical-align:middle">
                                            ${lote}
                                        </td>
                                    
                                        <td style="width:10%; text-align:left">
                                            ${item.existencia}
                                        
                                        
                                        </td>
                                        <td style="width:10%; text-align:left; vertical-align:middle">
                                            ${item.fcaduca}
                                        </td>
                                    
                                        <td style="width:10%; text-align:right; vertical-align:middle">
                                            $ ${item.precio}
                                        </td>

                                        <td style="width:17%; text-align:center;">
                                            <button type="button" class="btn btn-xs btn-primary" onclick="actualizaProductoLote('${cmb_bodega}','${item.idbodprod}','${item.codigo_item}','${item.idprod}')">
                                                <i class="fa fa-edit"></i>
                                            </button>

                                            <button type="button" class="btn btn-xs btn-success" onclick="kardexIndiv('${item.idbodprod}','BODEGA','${cmb_bodega}','${item.existencia}','${item.idprod}','${item.codigo_item}','${item.codigo_item}')">
                                                <i class="fa fa-refresh"></i>
                                            </button>

                                        </td> 
                                        
                                    
                                </tr>`);

        }else if(selecc=="Porcaducar" && fechacad >= fecha_actual){
          
            if(fechacad < Meses3 && item.existencia >0)  { 
                let lote=""
                if(item.lote=="null" || item.lote==null){
                    lote=""
                }else{
                    lote=item.lote
                }

                clase="color_x_caducar"
                

                $('#tabla_inventario').append(`<tr class="${clase}">
                                            <td style="width:10%; vertical-align:middle">
                                                ${item.codigo_item} 
                                                
                                            </td>

                                            <td style="width:5¡40%;  text-align:left; vertical-align:middle">
                                                ${item.detalle}
                                            </td>

                                            <td style="width:10%;  text-align:left; vertical-align:middle">
                                                ${lote}
                                            </td>
                                        
                                            <td style="width:10%; text-align:left">
                                                ${item.existencia}
                                            
                                            
                                            </td>
                                            <td style="width:10%; text-align:left; vertical-align:middle">
                                                ${item.fcaduca}
                                            </td>
                                        
                                            <td style="width:10%; text-align:right; vertical-align:middle">
                                                $ ${item.precio}
                                            </td>

                                            <td style="width:17%; text-align:center;">
                                                <button type="button" class="btn btn-xs btn-primary" onclick="actualizaProductoLote('${cmb_bodega}','${item.idbodprod}','${item.codigo_item}','${item.idprod}')">
                                                    <i class="fa fa-edit"></i>
                                                </button>

                                                <button type="button" class="btn btn-xs btn-success" onclick="kardexIndiv('${item.idbodprod}','BODEGA','${cmb_bodega}','${item.existencia}','${item.idprod}','${item.codigo_item}','${item.codigo_item}')">
                                                    <i class="fa fa-refresh"></i>
                                                </button>

                                            </td> 
                                            
                                        
                                    </tr>`);
            }

        }else if(selecc=="Rotura" && fechacad >= fecha_actual){
            let lote=""
            if(item.lote=="null" || item.lote==null){
                lote=""
            }else{
                lote=item.lote
            }

            if(item.existencia <=0){
                clase="color_rotura"
                
           
                $('#tabla_inventario').append(`<tr class="${clase}">
                                        <td style="width:10%; vertical-align:middle">
                                            ${item.codigo_item} 
                                            
                                        </td>

                                        <td style="width:40%;  text-align:left; vertical-align:middle">
                                            ${item.detalle}
                                        </td>

                                        <td style="width:10%;  text-align:left; vertical-align:middle">
                                            ${lote}
                                        </td>
                                    
                                        <td style="width:10%; text-align:left">
                                            ${item.existencia}
                                        
                                        
                                        </td>
                                        <td style="width:10%; text-align:left; vertical-align:middle">
                                            ${item.fcaduca}
                                        </td>
                                    
                                        <td style="width:10%; text-align:right; vertical-align:middle">
                                            $ ${item.precio}
                                        </td>

                                        <td style="width:17%; text-align:center;">
                                            <button type="button" class="btn btn-xs btn-primary" onclick="actualizaProductoLote('${cmb_bodega}','${item.idbodprod}','${item.codigo_item}','${item.idprod}')">
                                                <i class="fa fa-edit"></i>
                                            </button>

                                            <button type="button" class="btn btn-xs btn-success" onclick="kardexIndiv('${item.idbodprod}','BODEGA','${cmb_bodega}','${item.existencia}','${item.idprod}','${item.codigo_item}','${item.codigo_item}')">
                                                <i class="fa fa-refresh"></i>
                                            </button>

                                        </td> 
                                        
                                    
                                </tr>`);
            }
        }
    })
    if(contador>0){
        $('.btn_aprobacion').hide()
    }else{
        $('.btn_aprobacion').show()
    }
    
    cargar_estilos_datatable('tabla_inventario');
		
   
}

function dataGlobal(){
    let cmb_bodega=$('#cmb_bodega').val()
    let cmb_tipo=$('#cmb_tipo').val()
    let opcion=$('#cmb_opcion').val()
    let filtar_fecha=$("#cmb_filtra_fecha").val()
    let fecha_ini=$("#fecha_ini").val()
    let fecha_fin=$("#fecha_fin").val()
    let fecha_actual=$("#fecha_actual").val()

    if(cmb_bodega==""){ 
        alertNotificar("Seleccione una bodega","error")
        return 
    }

    if(cmb_tipo==""){ 
        alertNotificar("Seleccione un lugar","error")
        $('#bus_fecha_ini').focus()
        return 
    }

    if(opcion==""){ 
        alertNotificar("Seleccione un opcion","error")
       
        return 
    }

    if(opcion=="Agrupado"){
        //obligamos a que seleccione si es todo o filtro x fecha
        // if(filtar_fecha==""){
        //     alertNotificar("Seleccione si es por filtro o todos", "error")
        //     return
        // }
        // //si es filtro x fecha
        // if(filtar_fecha=="F"){
        //     //obligamos a seleccionar la fecha de inicio y fin
        //     if(fecha_ini==""){
        //         alertNotificar("Seleccione la fecha de inicio", "error")
        //         return
        //     }
        //     if(fecha_fin==""){
        //         alertNotificar("Seleccione la fecha de fin", "error")
        //         return
        //     }
        //     if(fecha_fin < fecha_ini){
        //         alertNotificar("La fecha de inicio debe ser mayor a la fecha final", "error")
        //         return
        //     }

        //     if(fecha_fin>fecha_actual){
        //         alertNotificar("La fecha de fin debe ser menor a la fecha actual", "error")
        //         return
        //     }
        // }

        // if(filtar_fecha=="T"){
        //     fecha_ini="2023-01-01"
        //     fecha_fin=fecha_actual
        // }

        filtar_fecha='t'
        fecha_ini="2023-01-01"
        fecha_fin=fecha_actual
    }
  

    $.get('filtra-inventario/'+cmb_bodega+'/'+cmb_tipo+'/'+filtar_fecha+'/'+fecha_ini+'/'+fecha_fin, function(data){

    // $.get('filtra-inventario/'+cmb_bodega+'/'+cmb_tipo, function(data){

        var num_col = $("#tabla_inventario_global thead tr th").length;
        if(data.error==true){
			$("#tabla_inventario_global tbody").html('');
			$("#tabla_inventario_global tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
			alertNotificar(data.mensaje,"error");
            cancelar()
			return;   
		}
		if(data.error==false){
			if(data.resultado.length==0){
               
				$("#tabla_inventario_global tbody").html('');
				$("#tabla_inventario_global tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
				alertNotificar("No se encontró información","error");
                cancelar()
				return;
			}
			
			$("#tabla_inventario_global tbody").html('');
            PrimeraVes=1

            globalThis.DataResultadoGLobal=data.resultado

            $('#cmb_filtra_glo').html('');				
            $('#cmb_filtra_glo').append(`<option value="Todos" selected>TODOS</option>
                                    <option value="Minimo">STOCK MINIMO</option>
                                    <option value="Critico">STOCK CRITICO</option>`).change();
            $("#cmb_filtra_glo").trigger("chosen:updated"); // actualizamos el combo 
           
            
            
		}
    }).fail(function(){
        cancelar()
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
        $("#tabla_inventario_global tbody").html('');
		$("#tabla_inventario_global tbody").html(`<tr><td colspan="${num_col}">Se produjo un error, por favor intentelo más tarde</td></tr>`);
    });  

    $('#content_consulta').hide()
    $('#listado_global').show()
    
    

}

function descargarPdfIndividual(){
    let cmb_bodega=$('#cmb_bodega').val()
    let tipo_filtro=$('#cmb_filtra').val()
    vistacargando("m", "Espere por favor")

    $.get('pdf-inventario-individual/'+cmb_bodega+'/'+tipo_filtro, function(data){
        vistacargando("")
        if(data.error==true){
			alertNotificar(data.mensaje,"error");        
			return;   
		}
		if(data.error==false){
			
            alertNotificar("El documento se descargará en unos segundos...","success");
            window.location.href="descargar-reporte/"+data.pdf
        }
    }).fail(function(){
       
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
     
    });  
}

function descargarPdf_(){
    vistacargando("m", "Espere por favor")
    let cmb_bodega=$('#cmb_bodega').val()
    let cmb_tipo=$('#cmb_tipo').val()
    let filtar_fecha=$("#cmb_filtra_fecha").val()
    let fecha_ini=$("#fecha_ini").val()
    let fecha_fin=$("#fecha_fin").val()
    let fecha_actual=$("#fecha_actual").val()

    if(filtar_fecha=="T"){
        fecha_ini="2023-01-01"
        fecha_fin=fecha_actual
    }
    $.get('pdf-inventario/'+cmb_bodega+'/'+cmb_tipo+'/'+filtar_fecha+'/'+fecha_ini+'/'+fecha_fin, function(data){
        vistacargando("")
        if(data.error==true){
			alertNotificar(data.mensaje,"error");        
			return;   
		}
		if(data.error==false){
			
            alertNotificar("El documento se descargará en unos segundos...","success");
            window.location.href="descargar-reporte/"+data.pdf
        }
    }).fail(function(){
       
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
     
    });  
}
function descargarEgresosAreaExcel_(){
    vistacargando("m", "Espere por favor")
    let cmb_bodega=$('#cmb_bodega').val()
    let cmb_tipo=$('#cmb_tipo').val()
    let filtar_fecha=$("#cmb_filtra_fecha").val()
    let fecha_ini=$("#fecha_ini").val()
    let fecha_fin=$("#fecha_fin").val()
    let fecha_actual=$("#fecha_actual").val()

    if(filtar_fecha=="T"){
        fecha_ini="2023-01-01"
        fecha_fin=fecha_actual
    }
    $.get('pdf-inventario-egreso-area-excel/'+cmb_bodega+'/'+cmb_tipo+'/'+filtar_fecha+'/'+fecha_ini+'/'+fecha_fin, function(data){
        vistacargando("")
        if(data.error==true){
			alertNotificar(data.mensaje,"error");        
			return;   
		}
		if(data.error==false){
			
            alertNotificar("El documento se descargará en unos segundos...","success");
            window.location.href="descargar-reporte/"+data.detalle
        }
    }).fail(function(){
       
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
     
    })
}

function descargarEgresosArea_(){
    vistacargando("m", "Espere por favor")
    let cmb_bodega=$('#cmb_bodega').val()
    let cmb_tipo=$('#cmb_tipo').val()
    let filtar_fecha=$("#cmb_filtra_fecha").val()
    let fecha_ini=$("#fecha_ini").val()
    let fecha_fin=$("#fecha_fin").val()
    let fecha_actual=$("#fecha_actual").val()

    if(filtar_fecha=="T"){
        fecha_ini="2023-01-01"
        fecha_fin=fecha_actual
    }
    $.get('pdf-inventario-egreso-area/'+cmb_bodega+'/'+cmb_tipo+'/'+filtar_fecha+'/'+fecha_ini+'/'+fecha_fin, function(data){
        vistacargando("")
        if(data.error==true){
			alertNotificar(data.mensaje,"error");        
			return;   
		}
		if(data.error==false){
			
            alertNotificar("El documento se descargará en unos segundos...","success");
            window.location.href="descargar-reporte/"+data.pdf
        }
    }).fail(function(){
       
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
     
    })
}

function descargarEgresosExcel_(){
    vistacargando("m", "Espere por favor")
    let cmb_bodega=$('#cmb_bodega').val()
    let cmb_tipo=$('#cmb_tipo').val()
    let filtar_fecha=$("#cmb_filtra_fecha").val()
    let fecha_ini=$("#fecha_ini").val()
    let fecha_fin=$("#fecha_fin").val()
    let fecha_actual=$("#fecha_actual").val()

    if(filtar_fecha=="T"){
        fecha_ini="2023-01-01"
        fecha_fin=fecha_actual
    }
    $.get('pdf-inventario-egreso-excel/'+cmb_bodega+'/'+cmb_tipo+'/'+filtar_fecha+'/'+fecha_ini+'/'+fecha_fin, function(data){
        vistacargando("")
        if(data.error==true){
			alertNotificar(data.mensaje,"error");        
			return;   
		}
		if(data.error==false){
			
            alertNotificar("El documento se descargará en unos segundos...","success");
            window.location.href="descargar-reporte/"+data.detalle
        }
    }).fail(function(){
       
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
     
    })
}

function descargarEgresos_(){
    vistacargando("m", "Espere por favor")
    let cmb_bodega=$('#cmb_bodega').val()
    let cmb_tipo=$('#cmb_tipo').val()
    let filtar_fecha=$("#cmb_filtra_fecha").val()
    let fecha_ini=$("#fecha_ini").val()
    let fecha_fin=$("#fecha_fin").val()
    let fecha_actual=$("#fecha_actual").val()

    if(filtar_fecha=="T"){
        fecha_ini="2023-01-01"
        fecha_fin=fecha_actual
    }
    $.get('pdf-inventario-egreso/'+cmb_bodega+'/'+cmb_tipo+'/'+filtar_fecha+'/'+fecha_ini+'/'+fecha_fin, function(data){
        vistacargando("")
        if(data.error==true){
			alertNotificar(data.mensaje,"error");        
			return;   
		}
		if(data.error==false){
			
            alertNotificar("El documento se descargará en unos segundos...","success");
            window.location.href="descargar-reporte/"+data.pdf
        }
    }).fail(function(){
       
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
     
    })
}

function FiltradosGLobal(){
    var selecc=$('#cmb_filtra_glo').val()

    $("#tabla_inventario_global tbody").html('');

	$('#tabla_inventario_global').DataTable().destroy();
	$('#tabla_inventario_global tbody').empty(); 
    
    var num_col = $("#tabla_inventario_global thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_inventario_global tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);


    $('#bodega_seleccionada').html('')
    $('#lugar_seleccionado').html('')

    $('#tituloCabecera').html(`<button type="buttton" onclick="cancelar()" class="btn btn-sm btn-danger">Atras</button> `)

    $("#tabla_inventario_global tbody").html('');

    var bodega_txt=$('#cmb_bodega option:selected').text()
    var lugar_txt=$('#cmb_tipo option:selected').text()
    var desde=$('#fecha_ini').val()
    var hasta=$('#fecha_fin').val()


    $('#bodega_seleccionada').html(bodega_txt)
    $('#lugar_seleccionado').html(lugar_txt)

    if(desde!=""){
        $('.fecha_fil').show()
        $('#desde_seleccionada').html(desde)
        $('#hasta_seleccionada').html(hasta)
    }else{
        $('.fecha_fil').hide()
    }
       
    let disabled=""

    let cmb_bodega=$('#cmb_bodega').val()
    let cmb_tipo=$('#cmb_tipo').val()
    datoItemArray=[]
    $.each(DataResultadoGLobal,function(i, item){
        datoItemArray.push({'idprod_':item.id_item,'nombres_':item.detalle});
        globalThis.datosItem=datoItemArray;
        if(selecc=="Todos"){
            let codigo_item=""
            if(item.codigo_item=="null" || item.codigo_item==null){
                codigo_item=item.id_item
            }else{
                codigo_item=item.codigo_item
            }
                      
            let cod=""
            
            if(item.codigo_item=='null' || item.codigo_item==null){
                cod=item.id_item
            }else{
                cod=item.codigo_item
            }
          
            let stock_min=""
            if(item.stock_min=='null' || item.stock_min==null || item.stock_min==undefined){
                stock_min=120
            }else{
                stock_min=item.stock_min
            }

            let stock_crit=""
            if(item.stock_cri=='null' || item.stock_cri==null || item.stock_cri==undefined){
                stock_crit=20
            }else{
                stock_crit=item.stock_cri
                
            }

            color_fila=""
            if(item.total <= stock_min  && item.total > stock_crit){
                color_fila="color_minimo"
               
            }
            else{
                if(item.total <= stock_min){
                    color_fila="color_critico"
                    
                  
                }
            }

            let precio_promedio=0;
            // if(item.existencia>0){
            if(item.total>0){
                // precio_promedio=Number(item.total_precio) / Number(item.cant_)
                // precio_promedio=precio_promedio.toFixed(2)
                precio_promedio=item.precio.toFixed(2)
            }

            let egresado=0;
            if(item.egresado>0){
                egresado=item.egresado
            }

            let egresadoMens=0;
            if(item.egresadoMens>0){
                egresadoMens=item.egresadoMens
            }

            let cantidadEgreso_=0;
            let cantidadAlta=0
            if(item.cantidadegreso>0){
                cantidadEgreso_=egresado/item.cantidadegreso
                cantidadEgreso_= Math.round(cantidadEgreso_)
                cantidadAlta=item.cantidadegresoAlto 
            }

            let total_=""
            if(item.total!=null){
                total_=item.total
            }
            $('#tabla_inventario_global').append(`<tr class="${color_fila}">
                                        <td style="width:10%; vertical-align:middle">
                                            ${cod}
                                            
                                        </td>

                                        <td style="width:25%;  text-align:left; vertical-align:middle">
                                            ${item.detalle}
                                        </td>

                                        <td style="width:8%; text-align:center; vertical-align:middle">
                                            ${item.total}                                                  
                                            
                                        </td>
                                        

                                        <td style="width:8%; text-align:center; vertical-align:middle">
                                            ${precio_promedio}
                                        </td>
                                       

                                        <td style="width:10%; text-align:center; vertical-align:middle">
                                            ${item.inconsis}
                                        </td>

                                        

                                        <td style="width:10%; text-align:center; vertical-align:middle">
                                            <button type="button" ${disabled} class="btn btn-primary btn-xs" onclick="verDetallado('${item.id_item}', '${cmb_bodega}','${cmb_tipo}','${item.total}','${cod}')">Detalle</button>
                                        </td>
                                        
                                    
                                </tr>`);

        }else if(selecc=="Minimo"){
          
            let cod=""
            
            if(item.codigo_item=='null' || item.codigo_item==null){
                cod=item.id_item
            }else{
                cod=item.codigo_item
            }
            
            if(item.codigo_item=='null' || item.codigo_item==null){
                cod=item.id_item
            }else{
                cod=item.codigo_item
            }

            let stock_min=""
            if(item.stock_min=='null' || item.stock_min==null || item.stock_min==undefined){
                stock_min=120
            }else{
                stock_min=item.stock_min
            }

            let stock_crit=""
            if(item.stock_cri=='null' || item.stock_cri==null || item.stock_cri==undefined){
                stock_crit=20
            }else{
                stock_crit=item.stock_cri
                
            }
            color_fila=""
            
            if(item.total <= stock_min  && item.total > stock_crit){
                color_fila="color_minimo"

                let precio_promedio=0;
                // if(item.existencia>0){
                if(item.total>0){
                    // precio_promedio=Number(item.total_precio) / Number(item.cant_)
                    // precio_promedio=precio_promedio.toFixed(2)
                    precio_promedio=item.precio.toFixed(2)
                }
    

                let egresado=0;
                if(item.egresado>0){
                    egresado=item.egresado
                }

                let egresadoMens=0;
                if(item.egresadoMens>0){
                    egresadoMens=item.egresadoMens
                }

                let cantidadEgreso=0;
                let cantidadAlta=0
                if(item.egresadoMens>0){
                    cantidadEgreso=egresado/item.cantidadegreso
                    cantidadEgreso= Math.round(cantidadEgreso)
                    cantidadAlta=item.cantidadegresoAlto
                }

                let total_=""
                if(item.total!=null){
                    total_=item.total
                }
                $('#tabla_inventario_global').append(`<tr class="${color_fila}">
                                                    <td style="width:10%; vertical-align:middle">
                                                        ${cod}
                                                        
                                                    </td>

                                                    <td style="width:25%;  text-align:left; vertical-align:middle">
                                                        ${item.detalle}
                                                    </td>

                                                    <td style="width:8%; text-align:center; vertical-align:middle">
                                                        ${item.total}                                                  
                                                        
                                                    </td>
                                                    

                                                    <td style="width:8%; text-align:center; vertical-align:middle">
                                                        ${precio_promedio}
                                                    </td>
                                                

                                                    <td style="width:10%; text-align:center; vertical-align:middle">
                                                        ${item.inconsis}
                                                    </td>

                                                    

                                                    <td style="width:10%; text-align:center; vertical-align:middle">
                                                        <button type="button" ${disabled} class="btn btn-primary btn-xs" onclick="verDetallado('${item.id_item}', '${cmb_bodega}','${cmb_tipo}','${item.total}','${cod}')">Detalle</button>
                                                    </td>
                                        
                                    
                                </tr>`);
                
            }

        }else if(selecc=="Critico"){
            let codigo_item=""
            if(item.codigo_item=="null" || item.codigo_item==null){
                codigo_item=item.id_item
            }else{
                codigo_item=item.codigo_item
            }
                      
            let cod=""
            
            if(item.codigo_item=='null' || item.codigo_item==null){
                cod=item.id_item
            }else{
                cod=item.codigo_item
            }
            
            if(item.codigo_item=='null' || item.codigo_item==null){
                cod=item.id_item
            }else{
                cod=item.codigo_item
            }

            let stock_min=""
            if(item.stock_min=='null' || item.stock_min==null || item.stock_min==undefined){
                stock_min=120
            }else{
                stock_min=item.stock_min
            }

            let stock_crit=""
            if(item.stock_cri=='null' || item.stock_cri==null || item.stock_cri==undefined){
                stock_crit=20
            }else{
                stock_crit=item.stock_cri
                
            }
            color_fila=""
            
            if(item.total <= stock_crit){
                color_fila="color_critico"
                
                let precio_promedio=0;
                // if(item.existencia>0){
                if(item.total>0){
                    // precio_promedio=Number(item.total_precio) / Number(item.cant_)
                    // precio_promedio=precio_promedio.toFixed(2)
                    precio_promedio=item.precio.toFixed(2)
                }
    

                let egresado=0;
                if(item.egresado>0){
                    egresado=item.egresado
                }

                let egresadoMens=0;
                if(item.egresadoMens>0){
                    egresadoMens=item.egresadoMens
                }

                let cantidadEgreso=0;
                let cantidadAlta=0
                if(item.egresadoMens>0){
                    cantidadEgreso=egresado/item.cantidadegreso
                    cantidadEgreso= Math.round(cantidadEgreso)
                    cantidadAlta=item.cantidadegresoAlto
                }
                let total_=""
                if(item.total!=null){
                    total_=item.total
                }
          
                $('#tabla_inventario_global').append(`<tr class="${color_fila}">
                                                        <td style="width:10%; vertical-align:middle">
                                                            ${cod}
                                                            
                                                        </td>

                                                        <td style="width:25%;  text-align:left; vertical-align:middle">
                                                            ${item.detalle}
                                                        </td>

                                                        <td style="width:8%; text-align:center; vertical-align:middle">
                                                            ${item.total}                                                  
                                                            
                                                        </td>
                                                        

                                                        <td style="width:8%; text-align:center; vertical-align:middle">
                                                            ${precio_promedio}
                                                        </td>
                                                    

                                                        <td style="width:10%; text-align:center; vertical-align:middle">
                                                            ${item.inconsis}
                                                        </td>

                                                        

                                                        <td style="width:10%; text-align:center; vertical-align:middle">
                                                            <button type="button" ${disabled} class="btn btn-primary btn-xs" onclick="verDetallado('${item.id_item}', '${cmb_bodega}','${cmb_tipo}','${item.total}','${cod}')">Detalle</button>
                                                        </td>
                                                                                    
                                    
                                </tr>`);
            }
        }
    })
    
		  
	cargar_estilos_datatable('tabla_inventario_global');
	
}

function verDetallado(iditem, bodega, tipo, total, codigo){
    let idprod=iditem
    let filtar_fecha=$("#cmb_filtra_fecha").val()
    let fecha_ini=$("#fecha_ini").val()
    let fecha_fin=$("#fecha_fin").val()
    let fecha_actual=$("#fecha_actual").val()

    $('#id_item_selecc').val('')
    $('#f_inicio_mov').val('')
    $('#f_fin_mov').val('')

    if(filtar_fecha=="T"){
        fecha_ini="2023-01-01"
        fecha_fin=fecha_actual
    }
    
    // if(bodega==9 || bodega==3 || bodega==4){
    if(bodega==50000){

        $('#item_seleccionada').html('')
        $('#suma_seleccionada').html('')
        $('#total_seleccionado').html('')
        $('#resta_seleccionada').html('')

        $("#tabla_detallle_suma tbody").html('');

        $('#tabla_detallle_suma').DataTable().destroy();
        $('#tabla_detallle_suma tbody').empty(); 
        
        var num_col = $("#tabla_detallle_suma thead tr th").length; //obtenemos el numero de columnas de la tabla
        $("#tabla_detallle_suma tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);


        $('#tituloCabecera').html(`<button type="buttton" onclick="cancelar()" class="btn btn-sm btn-danger">Atras</button> `)

        $.get('detalle-inventario-item/'+bodega+'/'+tipo+'/'+iditem+'/'+filtar_fecha+'/'+fecha_ini+'/'+fecha_fin, function(data){
            
            if(data.tabla_detallle_suma==true){
                $("#tabla_detallle tbody").html('');
                $("#tabla_detallle_suma tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
                alertNotificar(data.mensaje,"error");
            
                return;   
            }
            if(data.error==false){
                if(data.resultado.length==0){
                    $("#tabla_detallle_suma tbody").html('');
                    $("#tabla_detallle_suma tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
                    alertNotificar("No se encontró información","error");
                    
                    return;
                }
                
                $("#tabla_detallle_suma tbody").html('');
               
            
                
                let total_item=0
                let total_suma=0
                let total_resta=0
                $.each(data.resultado,function(i, item){
                   
                        if(item.suma!="0"){
                            total_suma=Number(total_suma) + Number(item.suma)
                        }

                        if(item.resta!="0"){
                          
                            total_resta=Number(total_resta) + Number(item.resta)
                        }

                      

                        let precio=item.precio * 1
                        precio=precio.toFixed(2)
                        $('#tabla_detallle_suma').append(`<tr>
                                                    <td style="width:33%; vertical-align:middle;text-align:center">
                                                        ${item.fing} 
                                                        
                                                    </td>

                                                    <td style="width:33%;  text-align:center; vertical-align:middle">
                                                        ${item.suma}
                                                    </td>

                                                    <td style="width:33%;  text-align:center; vertical-align:middle">
                                                        ${item.resta}
                                                    </td>
                                                
                                                  
                                                
                                            </tr>`);
                    
                })
               
                $('#modal_detalle_producto_suma').modal('show')

                total_item= total_suma -total_resta

                $('#item_seleccionada').html(data.resultado[0].nombre_item)
                $('#suma_seleccionada').html(total_suma)
                $('#total_seleccionado').html(total_item)
                $('#resta_seleccionada').html(total_resta)
                
                // cargar_estilos_datatable('tabla_detallle_suma');
            }
        }).fail(function(){
        
            vistacargando("")
            alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
            $("#tabla_detallle_suma tbody").html('');
            $("#tabla_detallle_suma tbody").html(`<tr><td colspan="${num_col}">Se produjo un error, por favor intentelo más tarde</td></tr>`);
        });   
    }

    else{
        $("#tabla_detallle tbody").html('');

        $('#tabla_detallle').DataTable().destroy();
        $('#tabla_detallle tbody').empty(); 
        
        var num_col = $("#tabla_detallle thead tr th").length; //obtenemos el numero de columnas de la tabla
        $("#tabla_detallle tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);


        $('#tituloCabecera').html(`<button type="buttton" onclick="cancelar()" class="btn btn-sm btn-danger">Atras</button> `)
        filtar_fecha="T"
        if(filtar_fecha=="T"){
            fecha_ini="2023-01-01"
            fecha_fin=fecha_actual
        }

        // $.get('detalle-inventario-item/'+bodega+'/'+tipo+'/'+iditem, function(data){
        $.get('detalle-inventario-item-fecha/'+bodega+'/'+tipo+'/'+iditem+'/'+filtar_fecha+'/'+fecha_ini+'/'+fecha_fin, function(data){
            globalThis.IdItemSeleccionado=iditem
            if(data.error==true){
                $("#tabla_detallle tbody").html('');
                $("#tabla_detallle tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
                alertNotificar(data.mensaje,"error");
            
                return;   
            }
            if(data.error==false){
                if(data.resultado.length==0){
                    $("#tabla_detallle tbody").html('');
                    $("#tabla_detallle tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
                    alertNotificar("No se encontró información","error");
                    
                    return;
                }
                
                $("#tabla_detallle tbody").html('');
                // $('#bodega_seleccionada').html(fecha_inicial)
                // $('#lugar_seleccionado').html(fecha_final)
            
              
                let contador=0
                // let total_p=0
                // $.each(data.resultado,function(i, item){
                //     // if(item.existencia>=0){
                //         let lote=""
                //         if(item.lote=="null" || item.lote==null){
                //             lote=""
                //         }else{
                //             lote=item.lote
                //         }
                //         let precio=item.precio * 1
                //         precio=precio.toFixed(2)
                //         total_p=Number(total_p)+ Number(item.existencia)
                //         $('#tabla_detallle').append(`<tr>
                //                                     <td style="width:10%; vertical-align:middle">
                //                                         ${item.codigo_item} 
                                                        
                //                                     </td>

                //                                     <td style="width:45%;  text-align:left; vertical-align:middle">
                //                                         ${item.detalle}
                //                                     </td>

                //                                     <td style="width:10%;  text-align:center; vertical-align:middle">
                //                                         ${lote}
                //                                     </td>
                                                
                //                                     <td style="width:10%; text-align:center; vertical-align:middle">
                                                      
                //                                         <input type="hidden"id="class_valor_lote-${item.idbodprod}" step=""0.01" style="width:100% !important;text-align:right" name="valor_lote[]"   onblur="validar_lote(this,'${item.idbodprod}')" value="${item.existencia}" >
                //                                         ${item.existencia}
                                                    
                //                                     </td>
                //                                     <td style="width:10%; text-align:center; vertical-align:middle">
                //                                         ${item.fcaduca}
                //                                     </td>
                                                
                //                                     <td style="width:10%; text-align:right; vertical-align:middle">
                //                                         $ ${precio}
                //                                     </td>

                //                                     <td style="width:5%; text-align:right; vertical-align:middle">
                //                                         <button class="btn btn-xs btn-primary"  onclick="verEx('${item.idbodprod}')">
                //                                             <i class="fa fa-search"></i>
                //                                         </button>
                //                                     </td>
                                                    
                                                
                //                             </tr>`);
                //     // }
                // })

                let total_p=0
                $.each(data.resultado,function(i, item){
                    total_p=Number(total_p)+ Number(item.existencia)
                })

                if(contador>0){
                    $('.btn_aprobacion').hide()
                }else{
                    $('.btn_aprobacion').show()
                }

                var difer=total - total_p
                var inco="No"
                if(total!=total_p){
                    inco="Si"
                }else{
                    inco="No"
                }

                $('#stock_selecc').val(total)
                $('#inconsistencia_selecc').val(inco)
                $('#suma_selecc').val(total_p)
                $('#diferencia_selecc').val(difer)

                // $('#total_bodega').html(total)
                // $('#inconsistencia').html(inco)
                // $('#sumado').html(total_p)
                // $('#diferencia').html(difer)

                globalThis.TotalItemSelecc=total


                // $('#modal_detalle_producto').modal('show')
                // $('#listado_detalle_egreso').hide()

                // $('#modal_detalle_producto').modal('show')
                // $('#listado_detalle_lote').show()
                // $('#listado_detalle_egreso').hide()
                // $('#listado_detalle_suma').hide()

                let filtrar_item = datosItem.filter(datos => datos.idprod_ == idprod );
   
                let nombre_item=filtrar_item[0].nombres_

                $("#tabla_detallle_suma tbody").html('');
        
                $('#tabla_detallle_suma').DataTable().destroy();
                $('#tabla_detallle_suma tbody').empty(); 

                $('#codigo_item_selecc').val(codigo)
                $('#item_selecc').val(nombre_item)
                $('#id_item_selecc').val(idprod)
                $('#id_bodega_selecc').val(bodega)

                $('#listado_detalle_lote').hide()
                $('#listado_detalle_suma').show()
            
                $('#modal_detalle_producto').modal('show')
                $('#listado_detalle_egreso').hide()

                cargar_estilos_datatable('tabla_detallle');
            }
        }).fail(function(){
        
            vistacargando("")
            alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
            $("#tabla_detallle tbody").html('');
            $("#tabla_detallle tbody").html(`<tr><td colspan="${num_col}">Se produjo un error, por favor intentelo más tarde</td></tr>`);
        });   
    }
    
}

function verEgreso(id_pedido){
    // if(id_pedido==null || id_pedido=='null'){
    //     alertNotificar('Es un egreso')
    // }
    
    $("#tabla_detallle_egreso tbody").html('');

    $('#tabla_detallle_egreso').DataTable().destroy();
    $('#tabla_detallle_egreso tbody').empty(); 
    
    var num_col = $("#tabla_detallle_egreso thead tr th").length; //obtenemos el numero de columnas de la tabla
    $("#tabla_detallle_egreso tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);


    $('#tituloCabecera').html(`<button type="buttton" onclick="cancelar()" class="btn btn-sm btn-danger">Atras</button> `)

    $.get('detalle-egreso/'+id_pedido, function(data){
        
        if(data.error==true){
            $("#tabla_detallle_egreso tbody").html('');
            $("#tabla_detallle_egreso tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
            alertNotificar(data.mensaje,"error");
        
            return;   
        }
        if(data.error==false){
            if(data.resultado.length==0){
                $("#tabla_detallle_egreso tbody").html('');
                $("#tabla_detallle_egreso tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
                alertNotificar("No se encontró información","error");
                
                return;
            }
            
            $("#tabla_detallle_egreso tbody").html('');
            
            let contador=0
            let total_p=0
            $.each(data.resultado,function(i, item){
                let suma=""
                let resta=""
                if(item.suma==null || item.suma=="null"){
                    suma=""
                }else{
                    suma=item.suma
                }
                let disabled=""
                if(item.resta==null || item.suma=="null"){
                    resta=""
                    disabled="disabled"
                }else{
                    resta=item.resta
                    disabled=''
                }
                $('#tabla_detallle_egreso').append(`<tr>
                                            <td style="width:40%; vertical-align:middle">
                                                ${item.solicitante} 
                                                
                                            </td>

                                            <td style="width:20%;  text-align:left; vertical-align:middle">
                                                ${item.area_nombre}
                                            </td>

                                            <td style="width:20%;  text-align:left; vertical-align:middle">
                                                ${item.fecha_hora}
                                            </td>

                                            <td style="width:20%;  text-align:center; vertical-align:middle">
                                                ${item.cantidad_entregada}
                                            </td>

                                            

                                    </tr>`);
                
            })
            
            cargar_estilos_datatable('tabla_detallle_egreso');
            $('#listado_detalle_egreso').show()
            $('#listado_detalle_suma').hide()

        }
        
    }).fail(function(){
    
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
        $("#tabla_detallle_egreso tbody").html('');
        $("#tabla_detallle_egreso tbody").html(`<tr><td colspan="${num_col}">Se produjo un error, por favor intentelo más tarde</td></tr>`);
    }); 
}

function atras(){
    $('#listado_detalle_lote').show()
    $('#listado_detalle_suma').hide()
}

function atrasDetalle(){
    $('#listado_detalle_egreso').hide()
    $('#listado_detalle_suma').show()

    
}

function imprimirEgresoItem(){
    let fecha_ini=$("#fecha_ini").val()
    let fecha_fin=$("#fecha_fin").val()
    let filtar_fecha=$("#cmb_filtra_fecha").val()
    let bodega_selecc=$("#cmb_bodega").val()
    let fecha_actual=$("#fecha_actual").val()
    if(filtar_fecha=="T"){
        fecha_ini="2023-01-01"
        fecha_fin=fecha_actual
    }
    vistacargando("m", "Espere por favor")
    $.get('egreso-item-pdf/'+IdItemSeleccionado+'/'+fecha_ini+'/'+fecha_fin+'/'+bodega_selecc, function(data){
        vistacargando("")
        if(data.error==true){
            
            alertNotificar(data.mensaje,"error");
        
            return;   
        }
        if(data.error==false){
           
            alertNotificar("El documento se descargará en unos segundos...","success");
            window.location.href="descargar-reporte/"+data.pdf
            
        }
        
    }).fail(function(){
    
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
      
    });  
}

function verEx(idbodpro){
    let fecha_ini=$("#fecha_ini").val()
    let fecha_fin=$("#fecha_fin").val()
    let filtar_fecha=$("#cmb_filtra_fecha").val()
    let fecha_actual=$("#fecha_actual").val()
    if(filtar_fecha=="T"){
        fecha_ini="2023-01-01"
        fecha_fin=fecha_actual
    }
    globalThis.BodProdGlobal=idbodpro

    $("#tabla_detallle_suma tbody").html('');

    $('#tabla_detallle_suma').DataTable().destroy();
    $('#tabla_detallle_suma tbody').empty(); 
    
    var num_col = $("#tabla_detallle_suma thead tr th").length; //obtenemos el numero de columnas de la tabla
    $("#tabla_detallle_suma tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);


    $('#tituloCabecera').html(`<button type="buttton" onclick="cancelar()" class="btn btn-sm btn-danger">Atras</button> `)

    $.get('detalle-suma-resta/'+idbodpro+'/'+fecha_ini+'/'+fecha_fin, function(data){
        
        if(data.error==true){
            $("#tabla_detallle_suma tbody").html('');
            $("#tabla_detallle_suma tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
            alertNotificar(data.mensaje,"error");
        
            return;   
        }
        if(data.error==false){
            if(data.resultado.length==0){
                $("#tabla_detallle_suma tbody").html('');
                $("#tabla_detallle_suma tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
                alertNotificar("No se encontró información","error");
                
                return;
            }
            
            $("#tabla_detallle_suma tbody").html('');
            
            let contador=0
            let total_p=0
            $.each(data.resultado,function(i, item){
                let suma=""
                let resta=""
                if(item.suma==null || item.suma=="null"){
                    suma=""
                }else{
                    suma=item.suma
                }
                let disabled=""
                if(item.resta==null || item.suma=="null"){
                    resta=""
                    disabled="disabled"
                }else{
                    resta=item.resta
                    disabled=''
                }

                if(item.id_pedido==null || item.suma=="null"){
                    disabled="disabled"
                }
                
                $('#tabla_detallle_suma').append(`<tr>
                                            <td style="width:40%; vertical-align:middle">
                                                ${item.responsable} 
                                                
                                            </td>

                                            <td style="width:20%;  text-align:left; vertical-align:middle">
                                                ${item.fecha_hora}
                                            </td>

                                            <td style="width:20%;  text-align:center; vertical-align:middle">
                                                ${suma}
                                            </td>

                                            <td style="width:20%;  text-align:center; vertical-align:middle">
                                                ${resta}
                                            </td>

                                            <td style="width:20%;  text-align:center; vertical-align:middle">
                                               <button type="button" ${disabled} class="btn btn-xs btn-primary" onclick="verEgreso('${item.id_pedido}')">
                                                    <i class="fa fa-sort-numeric-desc"></i>
                                                </button>
                                            </td>
                                          
                                            
                                        
                                    </tr>`);
                
            })
            
            cargar_estilos_datatable('tabla_detallle_suma');
            $('#listado_detalle_lote').hide()
            $('#listado_detalle_suma').show()
        }
        
    }).fail(function(){
    
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
        $("#tabla_detallle_suma tbody").html('');
        $("#tabla_detallle_suma tbody").html(`<tr><td colspan="${num_col}">Se produjo un error, por favor intentelo más tarde</td></tr>`);
    });   
   
}

function cerrar(){
    $('#modal_detalle_producto').modal('hide')
}


function cerrarSumaResta(){
    $('#modal_detalle_producto_suma').modal('hide')
}

function cargar_estilos_datatable(idtabla){
	var dataTable =$("#"+idtabla).DataTable({
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
    $('#listado_global').hide()
    $('#listado_individual').hide()
    $('#form_actualiza').hide()
   
    $('html,body').animate({scrollTop:$('#arriba').offset().top},400);
    
}


function listado(){
    $('#tituloCabecera').html(`<button type="buttton" onclick="cancelar()" class="btn btn-sm btn-danger">Atras</button> `)
    $('#content_consulta').hide()
    $('#listado_global').show()
    $('#form_actualiza').hide()
}
globalThis.Actualizado=0

function validar_lote(){
    var valor_lote=[];
    var comprobar=0
    var retval = []
    var total_ingresado=0
    $("input[name='valor_lote[]']").each(function(indice, elemento) {
        
        if($(elemento).val()!=""){
            comprobar=comprobar+1
            valor_lote.push($(elemento).val());
            retval.push($(this).attr('id'))

            total_ingresado=Number(total_ingresado) +Number ($(elemento).val())
           
        }
    });

   
    var array_bodprod=[]
    $.each(retval,function(i, item){
        var valor=item.split('-')
        array_bodprod.push(valor[1]);
    })

    vistacargando("m", "Espere por favor")
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    $.ajax({
        type: "POST",
        url: 'actualiza-existencia-bodprod',
        data: { _token: $('meta[name="csrf-token"]').attr('content'),
        array_bodprod:array_bodprod,valor_lote:valor_lote},
        success: function(data){
            vistacargando("");                
            if(data.error==true){
                alertNotificar(data.mensaje,'error');
                return;                      
            }
            alertNotificar(data.mensaje,"success");

                var difer=TotalItemSelecc - total_ingresado
                var inco="No"
                if(TotalItemSelecc!=total_ingresado){
                    inco="Si"
                }else{
                    inco="No"
                }
                // $('#modal_detalle_producto').modal('show')
                Actualizado=1
                $('#total_bodega').html(TotalItemSelecc)
                $('#inconsistencia').html(inco)
                $('#sumado').html(total_ingresado)
                $('#diferencia').html(difer)
                   
        }, error:function (data) {
            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });

}

$('#modal_detalle_producto').on('hidden.bs.modal', function (event) {
// do something...
    if(Actualizado==1){
        buscarInventario()
    }
        
})


function filtroOpcion(){
    var opcion=$('#cmb_opcion').val()
    if(opcion==""){return}
    if(opcion=="Agrupado"){
        //mostramos el combo para q muestro todo o x fecha
        $("#seccion_cmb_filtra").show()
    }else{
        $("#seccion_cmb_filtra").hide()
        limpiarCamposFiltro()
    }
}

function limpiarCamposFiltro(){
    $("#cmb_filtra_fecha").val('').change();
    $("#fecha_ini").val('')
    $("#fecha_fin").val('')
    $("#seccion_fecha").hide()
}

function filtraFecha(){
    var filtra=$("#cmb_filtra_fecha").val()
    if(filtra==""){return}
    if(filtra=="T"){
        $("#seccion_fecha").hide()
        $("#fecha_ini").val('')
        $("#fecha_fin").val('')

    }else{
        $("#seccion_fecha").show()
    }
}


function buscarMovimientos(){
    let idprod=$('#id_item_selecc').val()
    let f_inicio=$('#f_inicio_mov').val()
    let f_fin=$('#f_fin_mov').val()
    let fecha_actual=$("#fecha_actual").val()
    let bodega=$('#id_bodega_selecc').val()

    let tipo=$('#cmb_opcion').val()

    if(f_inicio==""){
        alertNotificar("Seleccione la fecha inicial","error")
        return
    }
    if(f_fin==""){
        alertNotificar("Seleccione la fecha final","error")
        return
    }

    if(f_fin < f_inicio){
        alertNotificar("La fecha de inicio debe ser menor a la fecha final","error")
        return
    }

    if(f_inicio > fecha_actual){
        alertNotificar("La fecha de inicio debe ser menor a la fecha actual","error")
        return
    }


    $("#tabla_detallle_suma tbody").html('');

    $('#tabla_detallle_suma').DataTable().destroy();
    $('#tabla_detallle_suma tbody').empty(); 

    let url_=""
    if(tipo=="Agrupado"){
        url_='kardex-bodega-item/'+idprod+'/'+f_inicio+'/'+f_fin+'/'+bodega
       
    }else{
        let lote=$('#lote_selecc').val()
        if(lote==""){
            alertNotificar("El item seleccionado no tiene lote asociado","error")
            return
        }else{
            
            url_='kardex-bodega-itemlote/'+idprod+'/'+f_inicio+'/'+f_fin+'/'+bodega+'/'+lote+'/'+IdBrodProdGlobal
        }
       
    }
    
    var num_col = $("#tabla_detallle_suma thead tr th").length; //obtenemos el numero de columnas de la tabla
    $("#tabla_detallle_suma tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);


    $('#tituloCabecera').html(`<button type="buttton" onclick="cancelar()" class="btn btn-sm btn-danger">Atras</button> `)

    $.get(url_, function(data){
    // $.get('kardex-bodega-item/'+idprod+'/'+f_inicio+'/'+f_fin+'/'+bodega, function(data){
        
        if(data.error==true){
            $("#tabla_detallle_suma tbody").html('');
            $("#tabla_detallle_suma tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
            alertNotificar(data.mensaje,"error");
        
            return;   
        }
        if(data.error==false){
            if(data.resultado.length==0){
                $("#tabla_detallle_suma tbody").html('');
                $("#tabla_detallle_suma tbody").html(`<tr><td colspan="${num_col}">No existen registros</td></tr>`);
                alertNotificar("No se encontró información","error");
                
                return;
            }
            
            $("#tabla_detallle_suma tbody").html('');
            
            let contador=0
            let total_p=0
            $.each(data.resultado,function(i, item){
                contador=contador+1
                let suma=""
                let resta=""
                if(item.suma==null || item.suma=="null"){
                    suma=""
                }else{
                    suma=item.suma
                }

                let disabled=""

                if(item.resta==null || item.suma=="null"){
                    resta=""
                    disabled="disabled"
                }else{
                    resta=item.resta
                    disabled=""
                }

                if(item.id_pedido==null || item.suma=="null"){
                    disabled="disabled"
                }

                $('#tabla_detallle_suma').append(`<tr>
                                            <td style="width:40%; vertical-align:middle">
                                                ${item.responsable} 
                                                
                                            </td>

                                            <td style="width:20%;  text-align:left; vertical-align:middle">
                                                ${item.ntipo}
                                            </td>

                                            <td style="width:20%;  text-align:left; vertical-align:middle">
                                                ${item.fecha_hora}
                                            </td>

                                            <td style="width:20%;  text-align:center; vertical-align:middle">
                                                ${suma}
                                            </td>

                                            <td style="width:20%;  text-align:center; vertical-align:middle">
                                                ${resta}
                                            </td>
                                        
                                          
                                            
                                        
                                    </tr>`);
                
            })
            $('.movimiento_item').prop('disabled',true)
            if(contador>0){
                $('.movimiento_item').prop('disabled',false)
            }
            
            cargar_estilos_datatable('tabla_detallle_suma');
            $('#listado_detalle_lote').hide()
            $('#listado_detalle_suma').show()
        }
        
    }).fail(function(){
    
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
        $("#tabla_detallle_suma tbody").html('');
        $("#tabla_detallle_suma tbody").html(`<tr><td colspan="${num_col}">Se produjo un error, por favor intentelo más tarde</td></tr>`);
    });   
   
}

function kardexItem(){
    let idprod=$('#id_item_selecc').val()
    let f_inicio=$('#f_inicio_mov').val()
    let f_fin=$('#f_fin_mov').val()
    let fecha_actual=$("#fecha_actual").val()
    let bodega=$('#id_bodega_selecc').val()
    let tipo=$('#cmb_opcion').val()

    if(f_inicio==""){
        alertNotificar("Seleccione la fecha inicial","error")
        return
    }
    if(f_fin==""){
        alertNotificar("Seleccione la fecha final","error")
        return
    }

    if(f_fin < f_inicio){
        alertNotificar("La fecha de inicio debe ser menor a la fecha final","error")
        return
    }

    if(f_inicio > fecha_actual){
        alertNotificar("La fecha de inicio debe ser menor a la fecha actual","error")
        return
    }

    let url_=""
    if(tipo=="Agrupado"){
        url_='kardex-bodega-item-reporte/'+idprod+'/'+f_inicio+'/'+f_fin+'/'+bodega
       
    }else{
        let lote=$('#lote_selecc').val()
        if(lote==""){
            alertNotificar("El item seleccionado no tiene lote asociado","error")
            return
        }else{
            
            url_='kardex-bodega-itemlote-reporte/'+idprod+'/'+f_inicio+'/'+f_fin+'/'+bodega+'/'+lote+'/'+IdBrodProdGlobal
        }
       
    }

    vistacargando("m", "Espere por favor")
    // $.get('kardex-bodega-item-reporte/'+idprod+'/'+f_inicio+'/'+f_fin+'/'+bodega, function(data){
    $.get(url_, function(data){
        vistacargando("")
        if(data.error==true){
            alertNotificar(data.mensaje,"error");        
            return;   
        }
        if(data.error==false){
            alertNotificar("El documento se descargará en unos segundos...","success");
            window.location.href="descargar-reporte/"+data.pdf
        }
        
    }).fail(function(){
    
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
       
    });  
}


function seccionReportes(){
    $('#modal_reporteria').modal('show')
    $('#f_inicio_reporte').val('')
    $('#f_fin_reporte').val('')
    $('#cmb_filtra_fecha_report').val('').trigger('change.select2')
    $('#filtra_fecha_reporteria').hide()
}

function FiltradosReporteria(){
    var filtra=$('#cmb_filtra_fecha_report').val()
    if(filtra==""){return}
    $('#f_inicio_reporte').val('')
    $('#f_fin_reporte').val('')
    if(filtra=="T"){
        $('#filtra_fecha_reporteria').hide()
    }else{
        $('#filtra_fecha_reporteria').show()
    }
}

function descargarPdf(){
    
    let cmb_bodega=$('#cmb_bodega').val()
    let cmb_tipo=$('#cmb_tipo').val()
    let filtar_fecha=$("#cmb_filtra_fecha_report").val()
    let fecha_ini=$("#f_inicio_reporte").val()
    let fecha_fin=$("#f_fin_reporte").val()
    let fecha_actual=$("#fecha_actual").val()

    if(filtar_fecha==""){
        alertNotificar("Debe seleccionar el tipo filtro", "error")
        return
    }
    if(filtar_fecha=="T"){
        fecha_ini="2023-01-01"
        fecha_fin=fecha_actual
    }

    if(filtar_fecha!="T"){
        if(fecha_ini==""){
            alertNotificar("Seleccione la fecha inicial", "error")
            return
        }
        if(fecha_fin==""){
            alertNotificar("Seleccione la fecha final", "error")
            return
        }

        if(fecha_fin < fecha_ini){
            alertNotificar("La fecha final debe ser mayor a la fecha inicial", "error")
            return
        }

        if(fecha_ini > fecha_actual){
            alertNotificar("La fecha inicial debe ser menor a la fecha actual", "error")
            return
        }


    }

    if(filtar_fecha=="T"){
        fecha_ini="2023-01-01"
        fecha_fin=fecha_actual
    }

    vistacargando("m", "Espere por favor")
    $.get('pdf-inventario/'+cmb_bodega+'/'+cmb_tipo+'/'+filtar_fecha+'/'+fecha_ini+'/'+fecha_fin, function(data){
        vistacargando("")
        if(data.error==true){
			alertNotificar(data.mensaje,"error");        
			return;   
		}
		if(data.error==false){
			
            alertNotificar("El documento se descargará en unos segundos...","success");
            window.location.href="descargar-reporte/"+data.pdf
        }
    }).fail(function(){
       
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
     
    });  
}

function descargarEgresos(){
    
    let cmb_bodega=$('#cmb_bodega').val()
    let cmb_tipo=$('#cmb_tipo').val()
    let filtar_fecha=$("#cmb_filtra_fecha_report").val()
    let fecha_ini=$("#f_inicio_reporte").val()
    let fecha_fin=$("#f_fin_reporte").val()
    let fecha_actual=$("#fecha_actual").val()
    
    if(filtar_fecha==""){
        alertNotificar("Debe seleccionar el tipo filtro", "error")
        return
    }

    if(filtar_fecha=="T"){
        fecha_ini="2023-01-01"
        fecha_fin=fecha_actual
    }


    if(filtar_fecha!="T"){
        if(fecha_ini==""){
            alertNotificar("Seleccione la fecha inicial", "error")
            return
        }
        if(fecha_fin==""){
            alertNotificar("Seleccione la fecha final", "error")
            return
        }

        if(fecha_fin < fecha_ini){
            alertNotificar("La fecha final debe ser mayor a la fecha inicial", "error")
            return
        }

        if(fecha_ini > fecha_actual){
            alertNotificar("La fecha inicial debe ser menor a la fecha actual", "error")
            return
        }


    }


    if(filtar_fecha=="T"){
        fecha_ini="2023-01-01"
        fecha_fin=fecha_actual
    }

    vistacargando("m", "Espere por favor")
    $.get('pdf-inventario-egreso/'+cmb_bodega+'/'+cmb_tipo+'/'+filtar_fecha+'/'+fecha_ini+'/'+fecha_fin, function(data){
        vistacargando("")
        if(data.error==true){
			alertNotificar(data.mensaje,"error");        
			return;   
		}
		if(data.error==false){
			
            alertNotificar("El documento se descargará en unos segundos...","success");
            window.location.href="descargar-reporte/"+data.pdf
        }
    }).fail(function(){
       
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
     
    })
}

function descargarEgresosExcel(){
   
    let cmb_bodega=$('#cmb_bodega').val()
    let cmb_tipo=$('#cmb_tipo').val()
    let filtar_fecha=$("#cmb_filtra_fecha_report").val()
    let fecha_ini=$("#f_inicio_reporte").val()
    let fecha_fin=$("#f_fin_reporte").val()
    let fecha_actual=$("#fecha_actual").val()

    if(filtar_fecha==""){
        alertNotificar("Debe seleccionar el tipo filtro", "error")
        return
    }

    if(filtar_fecha=="T"){
        fecha_ini="2023-01-01"
        fecha_fin=fecha_actual
    }


    if(filtar_fecha!="T"){
        if(fecha_ini==""){
            alertNotificar("Seleccione la fecha inicial", "error")
            return
        }
        if(fecha_fin==""){
            alertNotificar("Seleccione la fecha final", "error")
            return
        }

        if(fecha_fin < fecha_ini){
            alertNotificar("La fecha final debe ser mayor a la fecha inicial", "error")
            return
        }

        if(fecha_ini > fecha_actual){
            alertNotificar("La fecha inicial debe ser menor a la fecha actual", "error")
            return
        }


    }

    if(filtar_fecha=="T"){
        fecha_ini="2023-01-01"
        fecha_fin=fecha_actual
    }

    vistacargando("m", "Espere por favor")
    $.get('pdf-inventario-egreso-excel/'+cmb_bodega+'/'+cmb_tipo+'/'+filtar_fecha+'/'+fecha_ini+'/'+fecha_fin, function(data){
        vistacargando("")
        if(data.error==true){
			alertNotificar(data.mensaje,"error");        
			return;   
		}
		if(data.error==false){
			
            alertNotificar("El documento se descargará en unos segundos...","success");
            window.location.href="descargar-reporte/"+data.detalle
        }
    }).fail(function(){
       
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
     
    })
}

function descargarEgresosAreaExcel(){
    
    let cmb_bodega=$('#cmb_bodega').val()
    let cmb_tipo=$('#cmb_tipo').val()
    let filtar_fecha=$("#cmb_filtra_fecha_report").val()
    let fecha_ini=$("#f_inicio_reporte").val()
    let fecha_fin=$("#f_fin_reporte").val()
    let fecha_actual=$("#fecha_actual").val()

    
    if(filtar_fecha==""){
        alertNotificar("Debe seleccionar el tipo filtro", "error")
        return
    }

    if(filtar_fecha=="T"){
        fecha_ini="2023-01-01"
        fecha_fin=fecha_actual
    }


    if(filtar_fecha!="T"){
        if(fecha_ini==""){
            alertNotificar("Seleccione la fecha inicial", "error")
            return
        }
        if(fecha_fin==""){
            alertNotificar("Seleccione la fecha final", "error")
            return
        }

        if(fecha_fin < fecha_ini){
            alertNotificar("La fecha final debe ser mayor a la fecha inicial", "error")
            return
        }

        if(fecha_ini > fecha_actual){
            alertNotificar("La fecha inicial debe ser menor a la fecha actual", "error")
            return
        }


    }


    if(filtar_fecha=="T"){
        fecha_ini="2023-01-01"
        fecha_fin=fecha_actual
    }

    vistacargando("m", "Espere por favor")
    $.get('pdf-inventario-egreso-area-excel/'+cmb_bodega+'/'+cmb_tipo+'/'+filtar_fecha+'/'+fecha_ini+'/'+fecha_fin, function(data){
        vistacargando("")
        if(data.error==true){
			alertNotificar(data.mensaje,"error");        
			return;   
		}
		if(data.error==false){
			
            alertNotificar("El documento se descargará en unos segundos...","success");
            window.location.href="descargar-reporte/"+data.detalle
        }
    }).fail(function(){
       
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
     
    })
}

function descargarEgresosArea(){
   
    let cmb_bodega=$('#cmb_bodega').val()
    let cmb_tipo=$('#cmb_tipo').val()
    let filtar_fecha=$("#cmb_filtra_fecha_report").val()
    let fecha_ini=$("#f_inicio_reporte").val()
    let fecha_fin=$("#f_fin_reporte").val()
    let fecha_actual=$("#fecha_actual").val()

    if(filtar_fecha==""){
        alertNotificar("Debe seleccionar el tipo filtro", "error")
        return
    }

    if(filtar_fecha=="T"){
        fecha_ini="2023-01-01"
        fecha_fin=fecha_actual
    }


    if(filtar_fecha!="T"){
        if(fecha_ini==""){
            alertNotificar("Seleccione la fecha inicial", "error")
            return
        }
        if(fecha_fin==""){
            alertNotificar("Seleccione la fecha final", "error")
            return
        }

        if(fecha_fin < fecha_ini){
            alertNotificar("La fecha final debe ser mayor a la fecha inicial", "error")
            return
        }

        if(fecha_ini > fecha_actual){
            alertNotificar("La fecha inicial debe ser menor a la fecha actual", "error")
            return
        }


    }



    if(filtar_fecha=="T"){
        fecha_ini="2023-01-01"
        fecha_fin=fecha_actual
    }

    vistacargando("m", "Espere por favor")
    $.get('pdf-inventario-egreso-area/'+cmb_bodega+'/'+cmb_tipo+'/'+filtar_fecha+'/'+fecha_ini+'/'+fecha_fin, function(data){
        vistacargando("")
        if(data.error==true){
			alertNotificar(data.mensaje,"error");        
			return;   
		}
		if(data.error==false){
			
            alertNotificar("El documento se descargará en unos segundos...","success");
            window.location.href="descargar-reporte/"+data.pdf
        }
    }).fail(function(){
       
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
     
    })
}


function verReportesIindividual(){
    $('#modal_reporteria_indiv').modal('show')
    $('#f_inicio_reporte_ind').val('')
    $('#f_fin_reporte_ind').val('')
    $('#cmb_filtra_fecha_report_ind').val('').trigger('change.select2')
    $('#filtra_fecha_reporteria_ind').hide()
}

function FiltradosReporteriaInd(){
    var filtra=$('#cmb_filtra_fecha_report_ind').val()
    if(filtra==""){return}
    $('#f_inicio_reporte_ind').val('')
    $('#f_fin_reporte_ind').val('')
    if(filtra=="T"){
        $('#filtra_fecha_reporteria_ind').hide()
    }else{
        $('#filtra_fecha_reporteria_ind').show()
    }
}

function DescargarInventarioInd(){

    let cmb_bodega=$('#cmb_bodega').val()
    let filtar_fecha=$("#cmb_filtra_fecha_report_ind").val()
    let fecha_ini=$("#f_inicio_reporte_ind").val()
    let fecha_fin=$("#f_fin_reporte_ind").val()
    let fecha_actual=$("#fecha_actual").val()
    let tipo_filtro=$('#cmb_filtra').val()

    if(filtar_fecha==""){
        alertNotificar("Debe seleccionar el tipo filtro", "error")
        return
    }
    if(filtar_fecha=="T"){
        fecha_ini="2023-01-01"
        fecha_fin=fecha_actual
    }

    if(filtar_fecha!="T"){
        if(fecha_ini==""){
            alertNotificar("Seleccione la fecha inicial", "error")
            return
        }
        if(fecha_fin==""){
            alertNotificar("Seleccione la fecha final", "error")
            return
        }

        if(fecha_fin < fecha_ini){
            alertNotificar("La fecha final debe ser mayor a la fecha inicial", "error")
            return
        }

        if(fecha_ini > fecha_actual){
            alertNotificar("La fecha inicial debe ser menor a la fecha actual", "error")
            return
        }

    }
    vistacargando("m","Espere por favor")
    $.get('pdf-inventario-individual/'+cmb_bodega+'/'+tipo_filtro+'/'+fecha_ini+'/'+fecha_fin+'/'+filtar_fecha, function(data){
        vistacargando("")
        if(data.error==true){
			alertNotificar(data.mensaje,"error");        
			return;   
		}
		if(data.error==false){
			
            alertNotificar("El documento se descargará en unos segundos...","success");
            window.location.href="descargar-reporte/"+data.pdf
        }
    }).fail(function(){
       
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
     
    });  
}

function DescargarInventarioIndExcel(){

    let cmb_bodega=$('#cmb_bodega').val()
    let filtar_fecha=$("#cmb_filtra_fecha_report_ind").val()
    let fecha_ini=$("#f_inicio_reporte_ind").val()
    let fecha_fin=$("#f_fin_reporte_ind").val()
    let fecha_actual=$("#fecha_actual").val()
    let tipo_filtro=$('#cmb_filtra').val()

    if(filtar_fecha==""){
        alertNotificar("Debe seleccionar el tipo filtro", "error")
        return
    }
    if(filtar_fecha=="T"){
        fecha_ini="2023-01-01"
        fecha_fin=fecha_actual
    }

    if(filtar_fecha!="T"){
        if(fecha_ini==""){
            alertNotificar("Seleccione la fecha inicial", "error")
            return
        }
        if(fecha_fin==""){
            alertNotificar("Seleccione la fecha final", "error")
            return
        }

        if(fecha_fin < fecha_ini){
            alertNotificar("La fecha final debe ser mayor a la fecha inicial", "error")
            return
        }

        if(fecha_ini > fecha_actual){
            alertNotificar("La fecha inicial debe ser menor a la fecha actual", "error")
            return
        }

    }
    vistacargando("m","Espere por favor")
    $.get('pdf-inventario-individual-excel/'+cmb_bodega+'/'+tipo_filtro+'/'+fecha_ini+'/'+fecha_fin+'/'+filtar_fecha, function(data){
        vistacargando("")
        if(data.error==true){
			alertNotificar(data.mensaje,"error");        
			return;   
		}
		if(data.error==false){
			
            alertNotificar("El documento se descargará en unos segundos...","success");
            window.location.href="descargar-reporte/"+data.detalle
        }
    }).fail(function(){
       
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
     
    });  
}



function limpiarCamposActualizaPB(){
    $('#codigo_actualizar').val('')
    $('#lote_actualizar').val('')
    $('#felab_actualizar').val('')
    $('#fcad_actualizar').val('')
    $('#item_actualizar').val('')
    $('#precio_actualizar').val('')
    $('#id_prod_bod_actualizar').val('')
}
function actualizaProductoLote(bodega, idbodprod, codigo, idprod){
    limpiarCamposActualizaPB()

    let filtrar_item = datosItem.filter(datos => datos.idprod_ == idprod )   
    let nombre_item=filtrar_item[0].nombres_

    vistacargando("m", "Espere por favor")
    $.get('detalle-prodbod/'+bodega+'/'+idbodprod, function(data){
        console.log(data)
        vistacargando("")
        if(data.error==true){            
            alertNotificar(data.mensaje,"error");        
            return;   
        }
        $('#codigo_actualizar').val(codigo)
        $('#lote_actualizar').val(data.resultado.lote.lote)
        $('#felab_actualizar').val(data.resultado.lote.felabora)
        $('#fcad_actualizar').val(data.resultado.lote.fcaduca)
        $('#item_actualizar').val(nombre_item)
        $('#id_prod_bod_actualizar').val(idbodprod)
        $('#precio_actualizar').val(data.resultado.precio)
        $('#modal_editar_item').modal('show')
        
    }).fail(function(){
    
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
      
    }); 
    

}

function salirActualizacionPB(){
    limpiarCamposActualizaPB()
    $('#modal_editar_item').modal('hide')
}

function actualizarProdBodega(){
    var codigo_actualizar= $('#codigo_actualizar').val()
    var lote_actualizar= $('#lote_actualizar').val()
    var felab_actualizar= $('#felab_actualizar').val()
    var fcad_actualizar= $('#fcad_actualizar').val()
    var idbodprod =$('#id_prod_bod_actualizar').val()
    var precio=$('#precio_actualizar').val()
    if(codigo_actualizar==""){
        alertNotificar("Ingrese el codigo del item", "error")
        return
    }

    if(lote_actualizar==""){
        alertNotificar("Ingrese el lote del item", "error")
        return
    }

    if(felab_actualizar==""){
        alertNotificar("Ingrese la fecha de elaboracion del item", "error")
        return
    }

    if(fcad_actualizar==""){
        alertNotificar("Ingrese la fecha de caducidad del item", "error")
        return
    }

    if(idbodprod==""){
        alertNotificar("No se pudo obtener la informacion del producto", "error")
        return
    }
    if(precio==""){
        alertNotificar("Debe ingresar el precio", "error")
        return
    }

    if(precio<=0){
        alertNotificar("El precio debe ser mayor a cero", "error")
        return
    }

    swal({
        title: "¿Desea actualizar la informacion?",
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-danger",
        confirmButtonText: "Si, continuar",
        cancelButtonText: "No, cancelar",
        closeOnConfirm: false,
        closeOnCancel: false
    },
    function(isConfirm) {
        if (isConfirm) { 
            $("#frm_actualizar_pb").submit()
        }
        sweetAlert.close();   // ocultamos la ventana de pregunta
    });
   
}

$("#frm_actualizar_pb").submit(function(e){
    e.preventDefault();
    vistacargando("m", "Espere por favor");  
    //comprobamos si es registro o edicion
    let tipo="POST"
    let url_form="actualizar-prod-bodega"
      var FrmData=$("#frm_actualizar_pb").serialize();

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
            esActualizadoPB=1;
            salirActualizacionPB()
            buscarInventario()
            alertNotificar(data.mensaje,"success");
            
        }, error:function (data) {
            console.log(data)

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})

globalThis.IdBrodProdGlobal=0
function kardexIndiv(idbodprod, tipo,bodega,total, idprod, codigo){
    globalThis.IdBrodProdGlobal=idbodprod
    $('#f_inicio_mov').val('')
    $('#f_fin_mov').val('')

    let fecha_ini=$("#fecha_ini").val()
    let fecha_fin=$("#fecha_fin").val()
    let filtar_fecha="T";
    let fecha_actual=$("#fecha_actual").val()

    if(filtar_fecha=="T"){
        fecha_ini="2023-01-01"
        fecha_fin=fecha_actual
    }

    $.get('detalle-inventario-itemlote-fecha/'+idbodprod+'/'+tipo+'/'+filtar_fecha+'/'+fecha_ini+'/'+fecha_fin+'/'+bodega, function(data){
        console.log(data)
        if(data.error==true){
            alertNotificar(data.mensaje,"error");        
            return;   
        }
        if(data.error==false){
            if(data.resultado.length==0){
                alertNotificar("No se encontró información","error");                
                return;
            }
            
            let total_p=0
            $.each(data.resultado,function(i, item){
                total_p=Number(total_p)+ Number(item.existencia)
            })

            var difer=total - total_p 
            var inco="No"
            if(total!=total_p){
                inco="Si"
            }else{
                inco="No"
            }
            
            $('#stock_selecc').val(total)
            $('#inconsistencia_selecc').val(inco)
            $('#suma_selecc').val(total_p)
            $('#diferencia_selecc').val(difer)
            $('.global_info').hide()
            $('.lote_indiv').show()
            $('#lote_selecc').val(data.resultado[0].lote)

            let filtrar_item = datosItem.filter(datos => datos.idprod_ == idprod );
    
            let nombre_item=filtrar_item[0].nombres_
            $("#tabla_detallle_suma tbody").html('');
        
            $('#tabla_detallle_suma').DataTable().destroy();
            $('#tabla_detallle_suma tbody').empty(); 
        
            
        
            var num_col = $("#tabla_detallle_suma thead tr th").length; //obtenemos el numero de columnas de la tabla
            $("#tabla_detallle_suma tbody").html(`<tr><td colspan="${num_col}" style="padding:20px; 0px; font-size:20px;"><center><b> No hay datos disponibles</b></center></td></tr>`);
        
            $('#codigo_item_selecc').val(codigo)
            $('#item_selecc').val(nombre_item)
            $('#id_item_selecc').val(idprod)
            $('#id_bodega_selecc').val(bodega)
            
            $('#listado_detalle_lote').hide()
            $('#listado_detalle_suma').show()
        
            $('#modal_detalle_producto').modal('show')
            $('#listado_detalle_egreso').hide()


        }
    }).fail(function(){
    
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
        
    });   
    
}


