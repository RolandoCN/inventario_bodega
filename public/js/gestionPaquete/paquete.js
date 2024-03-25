
function visualizarFormPrinc(tipo){
    
    globalThis.AccionFormPaquete="";
    if(tipo=='N'){
        $('#titulo_form').html("Registro Paquete")
        $('#nombre_btn_form').html('Registrar')
        AccionFormPaquete="R"
    }else{
        $('#titulo_form').html("Actualizar Paquete")
        $('#nombre_btn_form').html('Actualizar')
        AccionFormPaquete="E"
    }
 
    $('#listado_paquete').hide(200)
    $('#form_ing').show(200)
}

function EditarPaquete(idpaq){
    vistacargando("m","Espere por favor")
    $.get("editar-paquete/"+idpaq, function(data){
        vistacargando("")
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            return;   
        }
        if(data.resultado==null){
            alertNotificar("La persona ya no se puede editar","error");
            return;   
        }

        $('#descripcion').val(data.resultado.descripcion)
        

        visualizarFormPrinc('E')
        globalThis.idPaqueteEditar=idpaq

       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}

$("#form_paquete").submit(function(e){
    e.preventDefault();
    
    //validamos los campos obligatorios
    let descripcion=$('#descripcion').val()
   
    if(descripcion=="" || descripcion==null){
        alertNotificar("Debe ingresar la descripcion","error")
        $('#descripcion').focus()
        return
    } 

    vistacargando("m","Espere por favor")
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    //comprobamos si es registro o edicion
    let tipo=""
    let url_form=""
    if(AccionFormPaquete=="R"){
        tipo="POST"
        url_form="guardar-paquete"
    }else{
        tipo="PUT"
        url_form="actualizar-paquete/"+idPaqueteEditar
    }
  
    var FrmData=$("#form_paquete").serialize();

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
            $('#descripcion').val('')
            alertNotificar(data.mensaje,"success");
            llenar_tabla_paquete()
            visualizarListado()
                            
        }, error:function (data) {
            console.log(data)

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})


function visualizarListado(){
    $('#form_ing').hide(200)
    $('#listado_paquete').show(200)

} 


function llenar_tabla_paquete(){
    var num_col = $("#tabla_paquete thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_paquete tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);
   
    
    $.get("listado-paquete/", function(data){
      
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            $("#tabla_paquete tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
            return;   
        }
        if(data.error==false){
            
            if(data.resultado.length <= 0){
                $("#tabla_paquete tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
                alertNotificar("No se encontró datos","error");
                return;  
            }
         
            $('#tabla_paquete').DataTable({
                "destroy":true,
                pageLength: 10,
                autoWidth : true,
                // order: [[ 1, "desc" ]],
                sInfoFiltered:false,
                language: {
                    url: 'json/datatables/spanish.json',
                },
                columnDefs: [
                    { "width": "10%", "targets": 0 },
                    { "width": "70%", "targets": 1 },
                    { "width": "20%", "targets": 2 },
                  
                   
                ],
                data: data.resultado,
                columns:[
                        {data: "descripcion"},
                        {data: "descripcion" },
                        {data: "descripcion" },
                       
                ],    
                "rowCallback": function( row, data, index ) {
                    $('td', row).eq(0).html(index+1)
                  
                    $('td', row).eq(2).html(`
                                            <button type="button" class="btn btn-primary btn-xs" onclick="EditarPaquete('${data.id_paquete }')">Editar</button>

                                            <button type="button" class="btn btn-success btn-xs" onclick="Detalle('${data.descripcion }','${data.id_paquete }')">Detalle</button>

                                            <button type="button" class="btn btn-danger btn-xs" onclick="EliminarPaquete('${data.id_paquete }')">Eliminar</button>
                                                                                
                                       
                                       
                                    
                    `); 
                }             
            });
        }
    }).fail(function(){
        $("#tabla_paquete tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });


}

$('.collapse-link').click();
$('.datatable_wrapper').children('.row').css('overflow','inherit !important');

$('.table-responsive').css({'padding-top':'12px','padding-bottom':'12px', 'border':'0', 'overflow-x':'inherit'});


function EliminarPaquete(id){
    if(confirm('¿Quiere eliminar el registro?')){
        vistacargando("")
        $.get("eliminar-paquete/"+id, function(data){
            vistacargando("")
            if(data.error==true){
                alertNotificar(data.mensaje,"error");
                return;   
            }
            $('#descripcion').val('')
            alertNotificar(data.mensaje,"success");
            llenar_tabla_paquete()
           
        }).fail(function(){
            vistacargando("")
            alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
        });
    }
   
}


function Detalle(descripcion,id_paqute, abiertaModal=null){
    visualizarForm('N')
    $('#idpaquete_cab').val()  
    $('#paq_selecc').html('')
    DetalleAccionForm="R"
    var num_col = $("#tabla_detalle_paq thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_detalle_paq tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);
   
    $.get("detalle-paquete/"+id_paqute, function(data){
        console.log(data)
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            $("#tabla_detalle_paq tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
            return;   
        }
        if(data.error==false){
            
            if(data.resultado.length <= 0){
                $("#tabla_detalle_paq tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
                // alertNotificar("No se encontró datos","error");
                // return;  
            }
                     
            $('#tabla_detalle_paq').DataTable({
                "destroy":true,
                pageLength: 10,
                autoWidth : true,
                order: [[ 1, "desc" ]],
                sInfoFiltered:false,
                language: {
                    url: 'json/datatables/spanish.json',
                },
                columnDefs: [
                    { "width": "10%", "targets": 0 },
                    { "width": "60%", "targets": 1 },
                    { "width": "20%", "targets": 2,className: "text-center"  },
                    { "width": "20%", "targets": 3 },
                 
                   
                ],
                data: data.resultado,
                columns:[
                        {data: "iddetalle_paq"},
                        {data: "iddetalle_paq" },
                        {data: "cantidad" },
                        {data: "iddetalle_paq" },
                       
                ],    
                "rowCallback": function( row, data, index ) {
                    $('td', row).eq(0).html(index+1)
                    
                    if(data.id_item >=30000){
                        $('td', row).eq(1).html(data.insumo.insumo)
                    }else{
                        $('td', row).eq(1).html(data.medicamento.nombre +" "+data.medicamento.concentra+" "+data.medicamento.forma+" "+data.medicamento.presentacion)
                    }
                  
                    $('td', row).eq(3).html(`
                                  
                        <button type="button" class="btn btn-primary btn-xs" onclick="editarDetallPaq(${data.iddetalle_paq })"><i class="fa fa-edit"></i></button>            
                                        
                        <a onclick="eliminarDetalle(${data.iddetalle_paq })" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i>  </a>
                                                  
                                    
                    `); 
                }             
            });
           
            
            if(abiertaModal!="S"){
                $('#modal_Menu').modal('show')
            }

            $('#paq_selecc').html(descripcion)
            $('#paq_selecc').addClass('mayusc')
            $('#idpaquete_cab').val(id_paqute)   

            $('#nombre_btn_form_detalle').html('Guardar')
            cargaItems()
        }

     

       
    }).fail(function(){
       
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}



$("#form_registro_detalle_paq").submit(function(e){
    
    e.preventDefault();
    
    //validamos los campos obligatorios
    let item_selecc=$('#item_selecci').val()
    let cantidad_selecc=$('#cantidad_item').val()
   
    if(item_selecc=="" || item_selecc==null){
        alertNotificar("Debe seleccionar un item","error")
        return
    } 

    if(cantidad_selecc=="" || cantidad_selecc==null){
        alertNotificar("Debe ingresar la cantidad","error")
        $('#cantidad_item').focus()
        return
    } 

    vistacargando("m","Espere por favor")
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    //comprobamos si es registro o edicion
    let tipo=""
    let url_form=""
    if(DetalleAccionForm=="R"){
        tipo="POST"
        url_form="guardar-detalle-paquete"
    }else{
        tipo="PUT"
        url_form="actualizar-detalle-paquete/"+idDetallePaqEditar
    }
 
    var FrmData=$("#form_registro_detalle_paq").serialize();

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
           
            alertNotificar(data.mensaje,"success");
           
            let paq_actual=$('#paq_selecc').html()
            let id_paqute_actual=$('#idpaquete_cab').val()
            Detalle(paq_actual,id_paqute_actual, null)
            visualizarForm('N')

            limpiarDetalleCampos()              
        }, error:function (data) {
            console.log(data)

            vistacargando("");
            alertNotificar('Ocurrió un error','error');
        }
    });
})

function cancelarDetalle(){
    DetalleAccionForm='R'
    limpiarDetalleCampos()
}
function limpiarDetalleCampos(){
    $('#tipo_item').val('')
    $('#item_selecci').val('').change()
    $('#cantidad_item').val('')
   
    $('#nombre_btn_form_detalle').html('Guardar')

}


function editarDetallPaq(iddetalle_paq){
    vistacargando("m","Espere por favor")
    $.get("editar-detalle-paq/"+iddetalle_paq, function(data){
        vistacargando("")
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            return;   
        }
        if(data.resultado==null){
            alertNotificar("La persona ya no se puede editar","error");
            return;   
        }

        $('#cantidad_item').val(data.resultado.cantidad)
        CargaItemEdit(data.resultado.id_item)
        visualizarForm('E')
        globalThis.idDetallePaqEditar=iddetalle_paq
        $('#nombre_btn_form_detalle').html('Actualizar')
       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}

function CargaItemEdit(idItemSel){
    $.get("item-seleccionado/"+idItemSel, function (data) {
      
        $('#item_selecci').html('');
        
        $.each(data.resultado,function(i,item){
          
            $('#item_selecci').append(`<option value="${item.iditem_}">${item.nombre_item}</option>`).change();
        })
        $("#item_selecci").trigger("chosen:updated"); // actualizamos el combo
    });
}

function visualizarForm(tipo){
   
    globalThis.AccionForm="";
    if(tipo=='N'){
      
        $('#nombre_btn_form').html('Registrar')
        DetalleAccionForm="R"
    }else{
      
        $('#nombre_btn_form').html('Actualizar')
        DetalleAccionForm="E"
    }
}



function eliminarDetalle(iddetalle){
    if(confirm('¿Quiere eliminar el registro?')){
        vistacargando("")
        $.get("eliminar-detalle-paq/"+iddetalle, function(data){
            vistacargando("")
            if(data.error==true){
                alertNotificar(data.mensaje,"error");
                return;   
            }
    
            alertNotificar(data.mensaje,"success");
            llenar_tabla_paquete()

            let paq_actual=$('#paq_selecc').html()
            let id_paqute_actual=$('#idpaquete_cab').val()
            Detalle(paq_actual,id_paqute_actual, null)
            visualizarForm('N')
           
        }).fail(function(){
            vistacargando("")
            alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
        });
    }
   
}

function cargaItems(){
    
    $('#item_selecci').select2({
        placeholder: 'Seleccione una opción',
        ajax: {
        url: 'carga-items',
        dataType: 'json',
        delay: 250,
        processResults: function (data) {
            return {
            results:  $.map(data, function (item) {

                    return {
                        text: item.descripcion,
                        id: item.id_item
                    }
                })
            };
        },
        cache: true
        }
    });
}

function ItemSelecc(){
    var tipo=$('#item_selecci').val()

    if(tipo>=30000){
        //insumo
        $('#tipo_item').val('Insumo')
    }else{
        //medicamento
        $('#tipo_item').val('Medicamento')
    }
}
