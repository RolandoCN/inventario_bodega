
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>virtual keyboard</title>
    <link rel="stylesheet" href="style.css">
</head>

<link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">
<style>

.select2-container--default .select2-selection--multiple .select2-selection__choic {
            background-color: #337ab7 !important;
            border: 1px solid #aaa;
            border-radius: 4px;
            cursor: default;
            float: left;
            margin-right: 5px;
            margin-top: 5px;
            padding: 0 5px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #555;
            line-height: 28px;
            text-align: left;
            margin-left: -9px;
            /* border-color:#d2d6de */
        }

        .select2-container .select2-selection--single{
            height: 35px;
        }
        .select2-container--default .select2-selection--single {
            border: 1px solid #E3E3E3;
        }
       
        .skin-red-light .sidebar-menu .treeview-menu>li.active>a{
            color:#0a0aaa
        }
/* 
    * {
    margin: 0;
    padding: 0;
    -webkit-box-sizing: border-box;
            box-sizing: border-box;
    } */

    .container1 {
    width: 1100px;
    height: 385px;
    background-color: #5e5e5e;
    position: relative;
    margin: .5rem auto;
    display: -ms-grid;
    display: grid;
    padding: 20px;
    -ms-grid-rows: 25px 60px 60px 60px 60px 60px;
        grid-template-rows: 25px 60px 60px 60px 60px 60px;
    border-radius: 15px;
    opacity: 0;
    pointer-events: none;
    -webkit-transition: all .5s ease-in-out;
    transition: all .5s ease-in-out;
    -webkit-transform: translateY(-100%);
            transform: translateY(-100%);
    }

    .button-style {
    -webkit-box-shadow: 0 0 10px -5px black;
            box-shadow: 0 0 10px -5px black;
    background: #3e3e3e;
    height: 50px;
    color: white;
    font-size: 20px;
    text-align: center;
    cursor: pointer;
    border-radius: 5px;
    line-height: 45px;
    position: relative;
    }

    .esc.button-style {
    width: 50px;
    height: 25px;
    line-height: 0px;
    font-size: 15px;
    }

    .line {
    display: -ms-grid;
    display: grid;
    -ms-grid-columns: 50px 50px 50px 50px 50px 50px 50px 50px 50px 50px 50px 50px 50px 80px;
        grid-template-columns: 50px 50px 50px 50px 50px 50px 50px 50px 50px 50px 50px 50px 50px 80px;
    grid-column-gap: 20px;
    margin: 20px 10px 20px 10px;
    }

    .first-line {
    width: 85%;
    }

    .second-line {
    -ms-grid-columns: 60px 50px 50px 50px 50px 50px 50px 50px 50px 50px 50px 50px 50px 70px;
        grid-template-columns: 60px 50px 50px 50px 50px 50px 50px 50px 50px 50px 50px 50px 50px 70px;
    }

    .second-line .button {
    padding: 0;
    line-height: 45px;
    }

    .display {
    width: 700px;
    height: 200px;
    margin: 1rem auto;
    position: relative;
    background-color: #ccc;
    -webkit-box-shadow: 5px 5px 30px -10px black;
            box-shadow: 5px 5px 30px -10px black;
    border-radius: 5px;
    text-transform: lowercase;
    padding: 20px 20px;
    line-height: 30px;
    letter-spacing: 3px;
    overflow-y: scroll;
    }

    .display p {
    position: relative;
    max-width: 700px;
    }

    .display span {
    position: relative;
    max-width: 700px;
    overflow-x: hidden;
    text-overflow: clip;
    display: inline-block;
    }

    .third-line {
    -ms-grid-columns: 80px 50px 50px 50px 50px 50px 50px 50px 50px 50px 50px 50px 110px;
        grid-template-columns: 80px 50px 50px 50px 50px 50px 50px 50px 50px 50px 50px 50px 110px;
    }

    .capsLock .light {
    width: 5px;
    height: 5px;
    position: absolute;
    border-radius: 50%;
    top: 5px;
    right: 5px;
    background-color: #2e2e2e;
    }

    .capsLock-on .light {
    background-color: gold;
    -webkit-box-shadow: 0px 0px 5px gold;
            box-shadow: 0px 0px 5px gold;
    }

    .forth-line {
    -ms-grid-columns: 125px 50px 50px 50px 50px 50px 50px 50px 50px 50px 50px 125px;
        grid-template-columns: 125px 50px 50px 50px 50px 50px 50px 50px 50px 50px 50px 125px;
    }

    .shift {
    font-size: 15px;
    text-align: left;
    padding-left: 10px;
    line-height: 50px;
    }

    .fifth-line {
    -ms-grid-columns: 50px 50px 50px 50px 300px 50px 50px  auto;
        grid-template-columns: 50px 50px 50px 50px 300px 50px 50px  auto;
    }

    .arrows .button-style {
    -webkit-box-shadow: 0 0 10px -5px black;
            box-shadow: 0 0 10px -5px black;
    background: #3e3e3e;
    height: 50px;
    color: white;
    font-size: 20px;
    text-align: center;
    cursor: pointer;
    border-radius: 5px;
    line-height: 45px;
    position: relative;
    }

    .arrows {
    height: 50px;
    display: -ms-grid;
    display: grid;
    -ms-grid-columns: 60px 50px 60px;
        grid-template-columns: 60px 50px 60px;
    grid-column-gap: 10px;
    }

    .middle-arrows {
    width: 50px;
    height: 50px;
    display: -ms-grid;
    display: grid;
    -ms-grid-rows: 25px 25px;
        grid-template-rows: 25px 25px;
    }

    .up-arrow,
    .down-arrow {
    background: #3e3e3e;
    width: 50px;
    height: 25px;
    color: white;
    font-size: 20px;
    text-align: center;
    cursor: pointer;
    border-radius: 5px;
    line-height: 20px;
    position: relative;
    }

    .windows-icon {
    width: 20px;
    height: 50px;
    margin: auto;
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    fill: white;
    }

    .sound-button {
    padding: 10px 90px 10px 50px;
    width: 50px;
    background-color: lightgray;
    text-align: center;
    border-radius: 15px;
    cursor: pointer;
    }

    .container1 span:not(.arrows):not(.middle-arrows):active {
    -webkit-transform: scale(0.95);
            transform: scale(0.95);
    -webkit-box-shadow: none;
            box-shadow: none;
    }

    .toggle-keyboard {
    /* -webkit-box-sizing: border-box;
            box-sizing: border-box;
    padding: 10px 50px;
    width: 200px;
    background-color: gray;
    -webkit-text-fill-color: white;
    outline: 2px solid black;
    cursor: pointer; */
    }

    .container1.open {
    opacity: 1;
    -webkit-transform: translate(0);
            transform: translate(0);
    pointer-events: all;
    }

</style>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <link rel="stylesheet" href="{{ asset('bower_components/bootstrap/dist/css/bootstrap.min.css')}}">

    <link rel="stylesheet" href="{{ asset('bower_components/font-awesome/css/font-awesome.min.css')}}">

    <link rel="stylesheet" href="{{ asset('bower_components/Ionicons/css/ionicons.min.css')}}">

    <link rel="stylesheet" href="{{asset('bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css')}}">

    <link href="{{asset('bower_components/pnotify/dist/pnotify.css')}}" rel="stylesheet">
    <link href="{{asset('bower_components/pnotify/dist/pnotify.buttons.css')}}" rel="stylesheet">
    <link href="{{asset('bower_components/pnotify/dist/pnotify.nonblock.css')}}" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('dist/css/AdminLTE.min.css')}}">
    
    <link rel="icon" href="{{asset('logo_icono.png')}}" sizes="32x32" />

<body>
    <div class="displayx"></div>

    <div class="container">
       
        <div class="col-md-12 " style="margin-top: 80px">
            <div class="box box-primary">
                <div class="box-body box-profile">
                    <a href="{{asset('/')}}"><img class="profile-user-img img-responsive img-circle" src="{{ asset('dist/img/logomsp.png')}}" alt="User profile picture"></a>
                    <h3 class="profile-username text-center" style="font-weight:500">Hospital General Dr. Napoleón Dávila Córdova</h3>
                    <p class="text-muted text-center" style="color:black;font-weight:520">Confirmación Alimentación</p>
                    
                    <div class="" style="margin-bottom:12px;text-align:center" >
                       <input type="text" style="text-align:center;color:black; font-weight:600; font-size:17px"  name="reloj" id="reloj" size="20"  disabled>
                    </div>
                       
                    <ul class="list-group list-group-unbordered">
                        {{-- @foreach($alimento as $ali)                        
                            <li class="list-group-item">
                                <b style="margin-left:70px">{{$ali->descripcion}}</b> 
                                <b class="pull-right"  style="margin-right:70px">{{$ali->hora_min}} -- {{$ali->hora_max}}</b>
                            </li>
                        @endforeach --}}
                    </ul>

                    <div style="margin-top:12px; margin-bottom:15px "class="col-md-10 col-md-offset-1">
                        <form id="form_valida" autocomplete="off" method="post"
                        action="">
                            {{ csrf_field() }}
                            <div class="form-group has-feedback col-m-11">
                                {{-- <input id="cedula_func" type="text" class="form-control vv" name="cedula_func" minlength="1" maxlength="10" onKeyPress="if(this.value.length==10) return false;"  required autocomplete="tx_login" autofocus placeholder="Ingrese su número de cédula" onclick="openCloseKeyboard()">
                                <span class="glyphicon glyphicon-user form-control-feedback" ></span> --}}

                                <select data-placeholder="Busqueda por Cédula o Nombres de Persona" style="width: 100%;" class="form-control select2 vv" onchange="buscarPersona()" id="cedula_func" name="cedula_func" onclick="openCloseKeyboard()">
                                            
                                                
                                </select>
                              
                            </div>


                            <button type="submit" class="btn btn-primary btn-block" ><b>Consultar</b></button>
                            <button type="button" class="btn btn-primary btn-block" onclick="openCloseKeyboard()"><b>act</b></button>

                           
                        </form>
                    </div>

                    
                </div>
            </div>
        </div>
        
    
        <div class="toggle-keyboard"></div>
        <div class="container1">
            <span class="esc button-style" style="line-height: 25px;"></span>
            <div class="first-line line">
                <span class="button button-style" onclick="agregarValor(`)">`</span>
                <span class="button button-style"onclick="agregarValor(1)">1</span>
                <span class="button button-style"onclick="agregarValor(2)">2</span>
                <span class="button button-style"onclick="agregarValor(3)">3</span>
                <span class="button button-style" onclick="agregarValor(4)">4</span>
                <span class="button button-style"onclick="agregarValor(5)">5</span>
                <span class="button button-style"onclick="agregarValor(6)">6</span>
                <span class="button button-style"onclick="agregarValor(7)">7</span>
                <span class="button button-style"onclick="agregarValor(8)">8</span>
                <span class="button button-style"onclick="agregarValor(9)">9</span>
                <span class="button button-style"onclick="agregarValor(0)">0</span>
                <span class="button button-style"onclick="agregarValor(-)">-</span>
                <span class="button button-style"onclick="agregarValor(=)">=</span>
                <span class="button-style backspace" style="font-size: 15px;line-height: 48px">Backspace</span>
            </div>
            <div class="second-line line">
                <span class="button-style">Tab</span>
                <span class="button button-style"onclick="agregarValor('Q')">Q</span>
                <span class="button button-style"onclick="agregarValor('W')">W</span>
                <span class="button button-style"onclick="agregarValor('E')">E</span>
                <span class="button button-style"onclick="agregarValor('R')">R</span>
                <span class="button button-style"onclick="agregarValor('T')">T</span>
                <span class="button button-style"onclick="agregarValor('Y')">Y</span>
                <span class="button button-style"onclick="agregarValor('U')">U</span>
                <span class="button button-style"onclick="agregarValor('I')">I</span>
                <span class="button button-style"onclick="agregarValor('O')">O</span>
                <span class="button button-style"onclick="agregarValor('P')">P</span>
                <span class="button button-style"onclick="agregarValor('[')">[</span>
                <span class="button button-style"onclick="agregarValor(']')">]</span>
                <span class="button button-style"onclick="agregarValor('')">\</span>
            </div>

            <div class="third-line line">
                <span class="capsLock button-style" style="font-size: 15px; text-align: left; padding-left: 10px;">CapsLK
                    <span class="light"></span></span>
                <span class="button button-style"onclick="agregarValor('A')">A</span>
                <span class="button button-style"onclick="agregarValor('S')">S</span>
                <span class="button button-style"onclick="agregarValor('D')">D</span>
                <span class="button button-style"onclick="agregarValor('F')">F</span>
                <span class="button button-style"onclick="agregarValor('G')">G</span>
                <span class="button button-style"onclick="agregarValor('H')">H</span>
                <span class="button button-style"onclick="agregarValor('J')">J</span>
                <span class="button button-style"onclick="agregarValor('K')">K</span>
                <span class="button button-style"onclick="agregarValor('L')">L</span>
                <span class="button button-style"onclick="agregarValor(';')">;</span>
                <span class="button button-style"onclick="agregarValor('')">'</span>
                <span class="button-style enter"onclick="agregarValor('')">Enter</span>
            </div>

            <div class="forth-line line">
                <span class="button-style shift">Shift</span>
                <span class="button button-style"onclick="agregarValor('Z')">Z</span>
                <span class="button button-style"onclick="agregarValor('X')">X</span>
                <span class="button button-style"onclick="agregarValor('C')">C</span>
                <span class="button button-style"onclick="agregarValor('V')">V</span>
                <span class="button button-style"onclick="agregarValor('B')">B</span>
                <span class="button button-style"onclick="agregarValor('N')">N</span>
                <span class="button button-style"onclick="agregarValor('M')">M</span>
                <span class="button button-style"onclick="agregarValor(',')">,</span>
                <span class="button button-style"onclick="agregarValor('.')">.</span>
                <span class="button button-style">/</span>
                <span class="button-style shift">Shift</span>

            </div>

            <div class="fifth-line line">
                <span class="ctrl button-style">Ctrl</span>
                <span class="fn button-style">Fn</span>
                <span class="window button-style">
                
                    <?xml version="1.0" encoding="iso-8859-1"?>
                    <!-- Generator: Adobe Illustrator 19.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0)  -->
                    <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" class="windows-icon" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                        viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
                    <g>
                        <g>
                            <polygon points="0,80 0,240 224,240 224,52 		"/>
                        </g>
                    </g>
                    <g>
                        <g>
                            <polygon points="256,48 256,240 512,240 512,16 		"/>
                        </g>
                    </g>
                    <g>
                        <g>
                            <polygon points="256,272 256,464 512,496 512,272 		"/>
                        </g>
                    </g>
                    <g>
                        <g>
                            <polygon points="0,272 0,432 224,460 224,272 		"/>
                        </g>
                    </g>
                    <g>
                    </g>
                    <g>
                    </g>
                    <g>
                    </g>
                    <g>
                    </g>
                    <g>
                    </g>
                    <g>
                    </g>
                    <g>
                    </g>
                    <g>
                    </g>
                    <g>
                    </g>
                    <g>
                    </g>
                    <g>
                    </g>
                    <g>
                    </g>
                    <g>
                    </g>
                    <g>
                    </g>
                    <g>
                    </g>
                    </svg>
                    
                
                </span>
                <span class="alt button-style">Alt</span>
                <span class="space button-style"></span>
                <span class="ctrl button-style">Ctrl</span>
                <span class="alt button-style">Alt</span>
                <span class="arrows">
                    <span class="left-arrow button-style">&leftarrow;</span>
                    <span class="middle-arrows">
                        <span class="up-arrow">&uparrow;</span>
                        <span class="down-arrow">&downarrow;</span>
                    </span>
                    <span class="right-arrow button-style">&rightarrow;</span>
                </span>


            </div>
        </div>
    </div>

    
</body>

<script src="{{ asset('bower_components/jquery/dist/jquery.min.js') }}"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="{{ asset('bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <!-- SlimScroll -->
    <script src="{{ asset('bower_components/jquery-slimscroll/jquery.slimscroll.min.js') }}""></script>
    <!-- FastClick -->
    <script src="{{ asset('bower_components/fastclick/lib/fastclick.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="{{ asset('dist/js/demo.js') }}"></script>

    <script src="{{ asset('bower_components/select2/dist/js/select2.full.min.js') }}"></script>

<script>



    // openCloseKeyboard()
    let buttons = document.querySelectorAll('.button');
let buttonsAsl = buttons
let display = document.querySelector('.vv');
let para = document.createElement('p');
let span = document.createElement('span');
let shifts = document.querySelectorAll('.shift');

display.appendChild(para);
para.appendChild(span);
buttons.forEach((button) => {
    button.addEventListener('click', write);
    function write() {
        para.appendChild(span);
        span.textContent += button.innerHTML;

    }



});


let capsLock = document.querySelector('.capsLock');
capsLock.addEventListener('click', capLetter);
let light = document.querySelector('.light');
let isOn = true;
function capLetter() {
    capsLock.classList.toggle('capsLock-on');
    span = document.createElement('span');
    para.appendChild(span);

    if (isOn) {
        span.style.textTransform = 'uppercase';
        isOn = false;

    } else {
        span.style.textTransform = 'lowercase';
        isOn = true;
    }
}


let enterButton = document.querySelector('.enter');
enterButton.addEventListener('click', nextLine);
function nextLine() {
    para = document.createElement('p');
    span = document.createElement('span');
    display.appendChild(para);
    para.appendChild(span);
}

let space = document.querySelector('.space');
space.addEventListener('click', writeSpace);
function writeSpace() {
    span.innerHTML += '&nbsp;';
}


let openCloseKeyboardButton = document.querySelector('.toggle-keyboard');
openCloseKeyboardButton.addEventListener('click' , openCloseKeyboard);
let container1 = document.querySelector('.container1');
let openCloseButton = document.querySelector('.toggle-keyboard');
let isClosed = true;
function openCloseKeyboard(){
    alert("dsds")

    let openCloseKeyboardButton = document.querySelector('.toggle-keyboard');
openCloseKeyboardButton.addEventListener('click' , openCloseKeyboard);
let container1 = document.querySelector('.container1');
let openCloseButton = document.querySelector('.toggle-keyboard');
    // alert("dsd")
    container1.classList.toggle('open');
  if(isClosed){
    openCloseButton.innerHTML = '';
    isClosed = false;
  } else{
    openCloseButton.innerHTML = '';
    isClosed = true;
  }
}

function agregarValor(numero){
    alert(numero)
    document.getElementById("cedula_func").value += numero;
}

$('.select2').select2()
// $('#cedula_func').select2({
//     placeholder: 'Seleccione una opción',
//     ajax: {
//     url: 'buscar-persona',
//     dataType: 'json',
//     delay: 250,
//     processResults: function (data) {
//         return {
//         results:  $.map(data, function (item) {
//                 return {
//                     text: item.cedula+" - "+item.nombres,
//                     id: item.id_empleado
//                 }
//             })
//         };
//     },
//     cache: true
//     }
// });



</script>

</html>