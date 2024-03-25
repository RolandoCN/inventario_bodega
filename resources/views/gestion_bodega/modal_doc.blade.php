<div class="modal fade" id="documentopdf" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
     
            <div class="modal-body">
            <span style="font-size: 150%; color: green" class="fa fa-file"></span> <label id="titulo" class="modal-title" style="font-size: 130%; color: black ;">DOCUMENTO</label>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span style="font-size: 35px"aria-hidden="true">&times;</span>
                </button>
                    <br><br>
                    <div class="row">
                        <div class="col-sm-12 col-xs-11 "style="height: auto ">
                        <iframe width="100%" height="500" frameborder="0"id="iframePdf"></iframe>
                                <p style="color: #747373;font-size:15px"></p>
                        </div>
                    </div>
                        
                
            </div>

            <div class="modal-footer"> 
                <center>
                    <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-mail-reply-all"></i> Salir</button>  
                    <a href=""id="vinculo"><button  type="button" id="descargar"class="btn btn-primary"><i class="fa fa-mail"></i> Descargar</button> </a>     
                    
                    {{-- <a href=""id="vinculo_rollo" style="display: none"><button  type="button" id="descargar_rollo"class="btn btn-success"><i class="fa fa-mail"></i> Descargar</button> </a>      --}}

                    <button  type="button" id="descargar_rollo" style="display: none" class="btn btn-success" onclick="descargarRollo()"><i class="fa fa-mail"></i> Descargar Etiqueta</button>     

                </center>               
            </div>



        </div>
    </div>
</div>

<script type="text/javascript">
 
</script>