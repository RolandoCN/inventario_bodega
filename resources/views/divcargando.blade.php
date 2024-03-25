<style type="text/css">

	/*ESTILOS PARA CENTRAR UN DIV VERTICAL MENTE*/
		.page{
			height: 100%;
		}

		.container-1{
			display: table;
			width: 100%;
			height: 100%;
		}

		.container-2{
			display: table-cell;
			vertical-align: middle;
			color: #fff;
		}

		.imagen_nav{
			width: 48px;
			height: 48px;
			border-radius: 50%;
			margin-right: 10px;
		}
		.div_cargar{
			background-color:rgba(42, 63, 84, 0.5);
			position: fixed;
			top: 0;
			right: 0;
			bottom: 0;
			left: 0;
			z-index: 1050;
			display: none;
			overflow: hidden;
			-webkit-overflow-scrolling: touch;
			outline: 0;
		}

/* FIN DE ESTILOS PARA CENTRAR UN DIV VERTICAL MENTE*/

</style>


<div id="modal_cargando" class="page div_cargar">
	<div class="container-1">
		<div class="container-2">
			<center><h1 id="modal_cargando_title">Cargando</h1></center>
			{{-- <div class="loader3C" id="loader">Loading...</div> --}}

			{{-- <img src="{{ asset('dist/img/user2-160x160.jpg')}}" class="img-circle" alt="User Image"> --}}

			<div align="center" id="loader"><img width="7%" src="{{ asset('img/cargargif.gif')}}"></div>
		</div>
	</div>
</div>


