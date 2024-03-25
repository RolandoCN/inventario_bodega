<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>SisInv</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">

    {{-- Plantilla --}}
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="{{ asset('bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('bower_components/font-awesome/css/font-awesome.min.css') }}">
    <!-- Ionicons -->
    <link rel="stylesheet" href="{{ asset('bower_components/Ionicons/css/ionicons.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('dist/css/AdminLTE.min.css') }}">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
        folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="{{ asset('dist/css/skins/_all-skins.min.css') }}">

    <link rel="stylesheet" href="{{ asset('bower_components/select2/dist/css/select2.min.css') }}">

    <!-- PNotify -->
    <link href="{{asset('bower_components/pnotify/dist/pnotify.css')}}" rel="stylesheet">
    <link href="{{asset('bower_components/pnotify/dist/pnotify.buttons.css')}}" rel="stylesheet">
    <link href="{{asset('bower_components/pnotify/dist/pnotify.nonblock.css')}}" rel="stylesheet">

    
    <link rel="stylesheet" href="{{asset('bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css')}}">


    <link rel="stylesheet" href="{{asset('css/spinners.css')}}">
    <link rel="icon" href="{{asset('logo_icono.png')}}" sizes="32x32" />

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
    </style>

    

    <!-- Scripts -->
    {{-- @vite(['resources/sass/app.scss', 'resources/js/app.js']) --}}

</head>

<body class="hold-transition sidebar-mini skin-red-light">

    <div class="wrapper">

        <header class="main-header">
            <!-- Logo -->
            <a href="{{asset('/')}}" class="logo">
                <!-- mini logo for sidebar mini 50x50 pixels -->
                <span class="logo-mini"><b>S</b>Inv</span>
                <!-- logo for regular state and mobile devices -->
                <span class="logo-lg"><b>Sis</b>Inv</span>
            </a>
            <!-- Header Navbar: style can be found in header.less -->
            <nav class="navbar navbar-static-top">
                <!-- Sidebar toggle button-->
                <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>

                <div class="navbar-custom-menu">
                    <ul class="nav navbar-nav">
                      
                        <!-- User Account: style can be found in dropdown.less --> 
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <img src="{{ asset('dist/img/use.png')}}" class="user-image" alt="User Image">
                                <span class="hidden-xs">
                                    @guest
                                    @else
                                        {{ Auth::user()->persona->nombres }}
                                    @endguest
                                </span>
                            </a>
                            <ul class="dropdown-menu">
                                <!-- User image -->
                                <li class="user-header">
                                    {{-- <img src="{{ asset('dist/img/user2-160x160.jpg')}}" class="img-circle" alt="User Image"> --}}
                                    <img src="{{ asset('dist/img/use.png')}}" class="img-circle" alt="User Image">
                                    <p>
                                        @guest
                                        @else
                                            {{ Auth::user()->persona->nombres }}
                                        @endguest
                                    </p>
                                </li>
                                <!-- Menu Body -->
                               
                                <!-- Menu Footer-->
                                <li class="user-footer">

                                    <div class="pull-left">
                                        <a onclick="modal_perfil()" class="btn btn-default btn-flat">Perfil</a>
                                    </div>
                                    <div class="pull-right">
                                        <a href="{{ route('logout') }}" class="btn btn-default btn-flat" id="cierra"
                                        onclick="event.preventDefault();document.getElementById('logout-form').submit();">Cerrar Sesión</a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </div>

                                    
                                </li>
                            </ul>
                        </li>
                       
                    </ul>
                </div> 
            </nav>
        </header>

        <!-- =============================================== -->

        <!-- Left side column. contains the sidebar -->
        <aside class="main-sidebar">
            <!-- sidebar: style can be found in sidebar.less -->
            <section class="sidebar">
                <!-- Sidebar user panel -->
                <div class="user-panel">
                    <div class="pull-left image">
                        <img src="{{ asset('dist/img/use.png')}}" class="img-circle" alt="User Image">
                    </div>
                    <div class="pull-left info">
                        <p> 
                            @guest
                            @else
                                {{ Auth::user()->persona->nombres }}
                            @endguest
                         </p>
                        <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                    </div>
                </div>
               
                <!-- sidebar menu: : style can be found in sidebar.less -->
                <ul class="sidebar-menu tree" data-widget="tree" style="margin-top: 12px">
                                  
                    @foreach(listarMenuSession() as $key=> $menu)
                        <li class="treeview" id="lista_gest_{{$key}}">
                    
                            <a href="">
                                <i class="{{$menu['icono'] <> null ? $menu['icono'] : "fa fa-desktop"}}"></i>
                                <span>{{$menu['gestion']}}</span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                                
                            
                            </a>
                            <ul class="treeview-menu">
                                @foreach($menu['rutas'] as $key2=> $gestion)

                                    @if ('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] == url($gestion->menu->url))
                                        <li class="active"><a href="{{url($gestion->menu->url)}}">
                                            <i class="fa fa-circle-o"></i> {{($gestion->menu->descripcion)}}</a>
                                        </li>
                                        <input type="hidden" name="url_" id="url_" value="{{$key}}">

                                    @else
                                        <li class=""><a href="{{url($gestion->menu->url)}}">
                                            <i class="fa fa-circle-o"></i> {{($gestion->menu->descripcion)}}</a>
                                        </li>
                                    @endif
                                    
                                <li>
                                    
                                @endforeach
                            </ul>
                        </li>
                    @endforeach 
                 
                </ul>
            </section>
            <!-- /.sidebar -->
        </aside>

        <!-- =============================================== -->

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            @yield('content')
        </div>
        <!-- /.content-wrapper -->

        <footer class="main-footer">
            <div class="pull-right hidden-xs">
                <b>Version</b> 1
            </div>
            <strong>TICS {{date('Y')}}.</strong> All rights
            reserved.
        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Create the tabs -->
            <ul class="nav nav-tabs nav-justified control-sidebar-tabs">
                <li><a href="#control-sidebar-home-tab" data-toggle="tab"><i class="fa fa-home"></i></a></li>

                <li><a href="#control-sidebar-settings-tab" data-toggle="tab"><i class="fa fa-gears"></i></a></li>
            </ul>
            <!-- Tab panes -->
            <div class="tab-content">
                <!-- Home tab content -->
                <div class="tab-pane" id="control-sidebar-home-tab">
                    <h3 class="control-sidebar-heading">Recent Activity</h3>
                    <ul class="control-sidebar-menu">
                        <li>
                            <a href="javascript:void(0)">
                                <i class="menu-icon fa fa-birthday-cake bg-red"></i>

                                <div class="menu-info">
                                    <h4 class="control-sidebar-subheading">Langdon's Birthday</h4>

                                    <p>Will be 23 on April 24th</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <i class="menu-icon fa fa-user bg-yellow"></i>

                                <div class="menu-info">
                                    <h4 class="control-sidebar-subheading">Frodo Updated His Profile</h4>

                                    <p>New phone +1(800)555-1234</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <i class="menu-icon fa fa-envelope-o bg-light-blue"></i>

                                <div class="menu-info">
                                    <h4 class="control-sidebar-subheading">Nora Joined Mailing List</h4>

                                    <p>nora@example.com</p>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <i class="menu-icon fa fa-file-code-o bg-green"></i>

                                <div class="menu-info">
                                    <h4 class="control-sidebar-subheading">Cron Job 254 Executed</h4>

                                    <p>Execution time 5 seconds</p>
                                </div>
                            </a>
                        </li>
                    </ul>
                    <!-- /.control-sidebar-menu -->

                    <h3 class="control-sidebar-heading">Tasks Progress</h3>
                    <ul class="control-sidebar-menu">
                        <li>
                            <a href="javascript:void(0)">
                                <h4 class="control-sidebar-subheading">
                                    Custom Template Design
                                    <span class="label label-danger pull-right">70%</span>
                                </h4>

                                <div class="progress progress-xxs">
                                    <div class="progress-bar progress-bar-danger" style="width: 70%"></div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <h4 class="control-sidebar-subheading">
                                    Update Resume
                                    <span class="label label-success pull-right">95%</span>
                                </h4>

                                <div class="progress progress-xxs">
                                    <div class="progress-bar progress-bar-success" style="width: 95%"></div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <h4 class="control-sidebar-subheading">
                                    Laravel Integration
                                    <span class="label label-warning pull-right">50%</span>
                                </h4>

                                <div class="progress progress-xxs">
                                    <div class="progress-bar progress-bar-warning" style="width: 50%"></div>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:void(0)">
                                <h4 class="control-sidebar-subheading">
                                    Back End Framework
                                    <span class="label label-primary pull-right">68%</span>
                                </h4>

                                <div class="progress progress-xxs">
                                    <div class="progress-bar progress-bar-primary" style="width: 68%"></div>
                                </div>
                            </a>
                        </li>
                    </ul>
                    <!-- /.control-sidebar-menu -->

                </div>
                <!-- /.tab-pane -->
                <!-- Stats tab content -->
                <div class="tab-pane" id="control-sidebar-stats-tab">Stats Tab Content</div>
                <!-- /.tab-pane -->
                <!-- Settings tab content -->
                <div class="tab-pane" id="control-sidebar-settings-tab">
                    <form method="post">
                        <h3 class="control-sidebar-heading">General Settings</h3>

                        <div class="form-group">
                            <label class="control-sidebar-subheading">
                                Report panel usage
                                <input type="checkbox" class="pull-right" checked>
                            </label>

                            <p>
                                Some information about this general settings option
                            </p>
                        </div>
                        <!-- /.form-group -->

                        <div class="form-group">
                            <label class="control-sidebar-subheading">
                                Allow mail redirect
                                <input type="checkbox" class="pull-right" checked>
                            </label>

                            <p>
                                Other sets of options are available
                            </p>
                        </div>
                        <!-- /.form-group -->

                        <div class="form-group">
                            <label class="control-sidebar-subheading">
                                Expose author name in posts
                                <input type="checkbox" class="pull-right" checked>
                            </label>

                            <p>
                                Allow the user to show his name in blog posts
                            </p>
                        </div>
                        <!-- /.form-group -->

                        <h3 class="control-sidebar-heading">Chat Settings</h3>

                        <div class="form-group">
                            <label class="control-sidebar-subheading">
                                Show me as online
                                <input type="checkbox" class="pull-right" checked>
                            </label>
                        </div>
                        <!-- /.form-group -->

                        <div class="form-group">
                            <label class="control-sidebar-subheading">
                                Turn off notifications
                                <input type="checkbox" class="pull-right">
                            </label>
                        </div>
                        <!-- /.form-group -->

                        <div class="form-group">
                            <label class="control-sidebar-subheading">
                                Delete chat history
                                <a href="javascript:void(0)" class="text-red pull-right"><i
                                        class="fa fa-trash-o"></i></a>
                            </label>
                        </div>
                        <!-- /.form-group -->
                    </form>
                </div>
                <!-- /.tab-pane -->
            </div>
        </aside>
        <!-- /.control-sidebar -->
        <!-- Add the sidebar's background. This div must be placed
             immediately after the control sidebar -->
        <div class="control-sidebar-bg"></div>
    </div>

    @guest
    @else
        @include('auth.modal_perfil')
        
    @endguest


    <!-- jQuery 3 -->
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

    {{-- PNotify --}}
    <script src="{{asset('bower_components/pnotify/dist/pnotify.js')}}"></script>
    <script src="{{asset('bower_components/pnotify/dist/pnotify.buttons.js')}}"></script>

    <script src="{{asset('bower_components/datatables.net/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js')}}"></script>
    <script src="{{asset('js/usuario/perfil.js?v='.rand())}}"></script>
    
    <script>
        
        function modal_perfil(){
            $('#modal_perfil').modal({backdrop: 'static', keyboard: false})
            // cargaInfoPerfil()
        }
        function mensaje(){
            alert("s")
        }
        $(document).ready(function() {
            // $('.sidebar-menu').tree()

            if (!$('body').hasClass('sidebar-collapse')){
                // $('[data-layout="sidebar-collapse"]').click()
            }
        
            var registrarInactividad = function () {
            var t;
            window.onload = reiniciarTiempo;
            // Eventos del DOM
            document.onmousemove = reiniciarTiempo;
            document.onkeypress = reiniciarTiempo;
            document.onload = reiniciarTiempo;
            document.onmousemove = reiniciarTiempo;
            document.onmousedown = reiniciarTiempo; // aplica para una pantalla touch
            document.ontouchstart = reiniciarTiempo;
            document.onclick = reiniciarTiempo;     // aplica para un clic del touchpad
            document.onscroll = reiniciarTiempo;    // navegando con flechas del teclado
            document.onkeypress = reiniciarTiempo;

            function tiempoExcedido() {
                alert("Su sesion ha expirado.")
                // $("#logout-form").submit()
                $('#cierra').click()
            }

            function reiniciarTiempo() {
                clearTimeout(t);
                // t = setTimeout(tiempoExcedido, 1800000)
                // t = setTimeout(tiempoExcedido, 7200000)
                // 1000 milisegundos = 1 segundo
            }
        };

        registrarInactividad(); //Esto activa el contador

           
        })
        $('.select2').select2()


        function alertNotificar(texto, tipo,time=7000){
            PNotify.removeAll()
            new PNotify({
                title: 'Mensaje de Información',
                text: texto,
                type: tipo,
                hide: true,
                delay: time,
                styling: 'bootstrap3',
                addclass: ''
            });
        }

        function vistacargando(estado){
            mostarOcultarVentanaCarga(estado,'');
        }

        function vistacargando(estado, mensaje){
            mostarOcultarVentanaCarga(estado, mensaje);
        }

        function mostarOcultarVentanaCarga(estado, mensaje){
            //estado --> M:mostrar, otra letra: Ocultamos la ventana
            // mensaje --> el texto que se carga al mostrar la ventana de carga
            if(estado=='M' || estado=='m'){
                // console.log(mensaje);
                $('#modal_cargando_title').html(mensaje);
                $('#modal_cargando').show();
                $('body').css('overflow', 'hidden');
            }else{
                $('#modal_cargando_title').html('Cargando');
                $('#modal_cargando').hide();
                $('body').css('overflow', '');
            }
        }

        let url_actual=$('#url_').val()
       
        if(url_actual){
            $('#lista_gest_'+url_actual).addClass('active menu-open')
            
           
        }

    </script>

    @yield('scripts')

    @include('divcargando')

</body>

</html>
