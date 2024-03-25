
$('.collapse-link').click();
$('.datatable_wrapper').children('.row').css('overflow','inherit !important');

$('.table-responsive').css({'padding-top':'12px','padding-bottom':'12px', 'border':'0', 'overflow-x':'inherit'});


function MedicinaEspecialida(){
    var idarea=$('#id_area').val()
    if(idarea==null || idarea==""){
        return
    }
    globalThis.InicioCmb=0;
    var num_col = $("#tabla_especialidad thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_especialidad tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);
    $.get("especialidad-por-medicina/"+idarea, function(data){
        console.log(data)
        
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            $("#tabla_especialidad tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
            return;   
        }
        if(data.error==false){
            
            if(data.resultado.length <= 0){
                $("#tabla_especialidad tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
                alertNotificar("No se encontró datos","error");
                return;  
            }
         
            $('#tabla_especialidad').DataTable({
                "destroy":true,
                pageLength: 10,
                autoWidth : true,
                order: [[ 2, "desc" ]],
                sInfoFiltered:false,
                language: {
                    url: 'json/datatables/spanish.json',
                },
                columnDefs: [
                    { "width": "10%", "targets": 0 },
                    { "width": "65%", "targets": 1 },
                    { "width": "25%", "targets": 2 },
                  
                                      
                ],
                data: data.resultado,
                columns:[
                        {data: "idprod"},
                        {data: "nprod" },
                        
                        {data: "pertenece"},
                      
                ],    
                "rowCallback": function( row, data, index ) {

                   
                    let perm=""
                    if(data.pertenece=="S"){
                        perm="checked"
                    }else{
                        perm=""
                    }
                    $('td', row).eq(2).html(`
                                  
                                            
                                            <input type="checkbox" onclick="accionMedicina(${data.idprod})"class="acces_check" id="check_${data.idprod}" name="acces_check" value="${data.idprod}"  ${perm}>
                                       
                                    
                    `);

                   
                }             
            });
        }
        
       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}

//paginacion
$('#tabla_especialidad').on( 'draw.dt', function () {
  
    setTimeout(function() {
        //para que no ejecute la funcion de chequear (paginacion la primera vez)
        if(InicioCmb==0){
            InicioCmb=1; //ejecute la function
        }else{
            // chequear();
        }
       
    }, 200);
});


function chequear(){

    var idarea=$('#id_area').val()
    if(idarea==null || idarea==""){
        return
    }
    vistacargando("m", "Espere por favor")
    $.get("especialidad-por-medicina/"+idarea, function(data){
        vistacargando("")
        $.each(data.resultado, function(i, item){
            
            if(item.pertenece=="S"){
                $('#check_'+item.idprod).prop('checked', true)
            } 
        });
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}

function accionMedicina(id){
   
    if( $('#check_'+id).is(':checked') ){
        // mandamos a guardar ese menu al perfil
        AggQuitarMedicinaFuncionario(id,'A')
    } else {
        // mandamos a quitar
        AggQuitarMedicinaFuncionario(id,'Q')
    }
}

function AggQuitarMedicinaFuncionario(id_medicina, tipo){
    
    var EspecialidadSeleccionada=$('#id_area').val()
    vistacargando("m","Espere por favor")
    $.get("especialidad-medicina-mant/"+id_medicina+"/"+tipo+"/"+EspecialidadSeleccionada, function(data){
        vistacargando("")
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            if(tipo=='A'){
                $('#check_'+id_medicina).prop('checked', false)
            }else{
                $('#check_'+id_medicina).prop('checked', true)
            }
                
            return;   
        }
       
        alertNotificar(data.mensaje,"success")

       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}
