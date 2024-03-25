
$('.collapse-link').click();
$('.datatable_wrapper').children('.row').css('overflow','inherit !important');

$('.table-responsive').css({'padding-top':'12px','padding-bottom':'12px', 'border':'0', 'overflow-x':'inherit'});

function FuncionariosArea(){
    var idarea=$('#id_area').val()
    if(idarea==null || idarea==""){
        return
    }
    var num_col = $("#tabla_funcionario thead tr th").length; //obtenemos el numero de columnas de la tabla
	$("#tabla_funcionario tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center><span class="spinner-border" role="status" aria-hidden="true"></span><b> Obteniendo información</b></center></td></tr>`);
    $.get("funcionarios-por-area/"+idarea, function(data){
        console.log(data)
        
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            $("#tabla_funcionario tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
            return;   
        }
        if(data.error==false){
            
            if(data.resultado.length <= 0){
                $("#tabla_funcionario tbody").html(`<tr><td colspan="${num_col}" style="padding:40px; 0px; font-size:20px;"><center>No se encontraron datos</center></td></tr>`);
                alertNotificar("No se encontró datos","error");
                return;  
            }
         
            $('#tabla_funcionario').DataTable({
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
                    { "width": "35%", "targets": 1 },
                    { "width": "20%", "targets": 2 },
                   
                ],
                data: data.resultado,
                columns:[
                        {data: "cedula"},
                        {data: "funcionario" },
                       
                        {data: "pertenece" },
                        
                ],    
                "rowCallback": function( row, data, index ) {

                   
                    let perm=""
                    if(data.pertenece=="S"){
                        perm="checked"
                    }else{
                        perm=""
                    }
                    $('td', row).eq(2).html(`
                                  
                                            
                                            <input type="checkbox" onclick="accioArea(${data.idper})"class="acces_check" id="check_${data.idper}" name="acces_check" value="${data.idper}"  ${perm}>
                                       
                                    
                    `);

                   
                }             
            });
        }
        
       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}

function accioArea(id){
   
    if( $('#check_'+id).is(':checked') ){
        // mandamos a guardar ese menu al perfil
        AggQuitarAreaFunc(id,'A')
    } else {
        // mandamos a quitar
        AggQuitarAreaFunc(id,'Q')
    }
}

function AggQuitarAreaFunc(idper, tipo){
    var AreaSeleccionada=$('#id_area').val()
    vistacargando("m","Espere por favor")
    $.get("funcionarios-areas-mant/"+idper+"/"+tipo+"/"+AreaSeleccionada, function(data){
        vistacargando("")
        if(data.error==true){
            alertNotificar(data.mensaje,"error");
            if(tipo=='A'){
                $('#check_'+idper).prop('checked', false)
            }else{
                $('#check_'+idper).prop('checked', true)
            }
                
            return;   
        }
       
        alertNotificar(data.mensaje,"success")
        // FuncionariosArea()

       
    }).fail(function(){
        vistacargando("")
        alertNotificar("Se produjo un error, por favor intentelo más tarde","error");  
    });
}
