<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Personal\GestionController;
use App\Http\Controllers\Personal\MenuController;
use App\Http\Controllers\Personal\GestionMenuController;
use App\Http\Controllers\Personal\PersonaController;
use App\Http\Controllers\Personal\PerfilController;
use App\Http\Controllers\Personal\UsuarioController;
use App\Http\Controllers\Personal\PermisoController;
use App\Http\Controllers\Personal\ReportePermisoController;
use App\Http\Controllers\Personal\AreaController;
use App\Http\Controllers\Personal\FuncionarioController;
use App\Http\Controllers\Bodega\EspecialidadMedicinaController;
use App\Http\Controllers\Bodega\BodegaPrincipalController;
use App\Http\Controllers\Bodega\InventarioController;
use App\Http\Controllers\Bodega\BodegaFarmaciaController;
use App\Http\Controllers\Bodega\MedicinaController;
use App\Http\Controllers\Bodega\InventarioController2;
use App\Http\Controllers\Bodega\SolicitarController;
use App\Http\Controllers\Bodega\ProveedorController;
use App\Http\Controllers\Bodega\PaqueteDialisisController;
use App\Http\Controllers\Bodega\SolicitudPaqueteDialController;
use App\Http\Controllers\Bodega\SolicitudPaqueteCirugiaController;
use App\Http\Controllers\Bodega\PaqueteCirugiaController;
use App\Http\Controllers\Bodega\ReportesExcelController;
use App\Http\Controllers\Bodega\SolicitaDialisisController;
use App\Http\Controllers\Bodega\SolicitudPaqueteCentroObstController;
use App\Http\Controllers\Api\TurneroController;
use App\Http\Controllers\Nacional\InterOperabilidadController;
use App\Http\Controllers\Bodega\PedidoBodegaController;
use App\Http\Controllers\Personal\ItemController;


Route::get('/', [App\Http\Controllers\HomeController::class, 'index']);

Auth::routes();

Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::get('/usuario', [UsuarioController::class, 'index']);
Route::get('/listado-usuario', [UsuarioController::class, 'listar']);
Route::post('/guardar-usuario', [UsuarioController::class, 'guardar']);
Route::get('/editar-usuario/{id}', [UsuarioController::class, 'editar']);
Route::put('/actualizar-usuario/{id}', [UsuarioController::class, 'actualizar']);
Route::get('/eliminar-usuario/{id}', [UsuarioController::class, 'eliminar']);
Route::post('/cambiar-clave', [UsuarioController::class, 'cambiarClave']);
Route::get('/resetear-password/{id}', [UsuarioController::class, 'resetearPassword']);
Route::get('/bodega-usuario/{id}', [UsuarioController::class, 'bodegaUsuario']);
Route::get('/bodega-por-usuario/{bod}/{tipo}/{usuario}', [UsuarioController::class, 'mantenimientoBodegaUser']);

//PERSONA
Route::get('/persona', [PersonaController::class, 'index']);
Route::get('/listado-persona', [PersonaController::class, 'listar']);
Route::post('/guardar-persona', [PersonaController::class, 'guardar']);
Route::get('/editar-persona/{id}', [PersonaController::class, 'editar']);
Route::put('/actualizar-persona/{id}', [PersonaController::class, 'actualizar']);
Route::get('/eliminar-persona/{id}', [PersonaController::class, 'eliminar']);

//ITEM
Route::get('/producto', [ItemController::class, 'index']);
Route::get('/listado-producto', [ItemController::class, 'listar']);
Route::post('/guardar-producto', [ItemController::class, 'guardar']);
Route::get('/editar-producto/{id}', [ItemController::class, 'editar']);
Route::put('/actualizar-producto/{id}', [ItemController::class, 'actualizar']);
Route::get('/eliminar-producto/{id}', [ItemController::class, 'eliminar']);

Route::get('/ingreso-bodega', [BodegaPrincipalController::class, 'index']);



Route::middleware(['auth'])->group(function() { //middleware autenticacion

    // //PERSONA
    // Route::get('/persona', [PersonaController::class, 'index'])->middleware('validarRuta');
    // Route::get('/listado-persona', [PersonaController::class, 'listar']);
    // Route::post('/guardar-persona', [PersonaController::class, 'guardar']);
    // Route::get('/editar-persona/{id}', [PersonaController::class, 'editar']);
    // Route::put('/actualizar-persona/{id}', [PersonaController::class, 'actualizar']);
    // Route::get('/eliminar-persona/{id}', [PersonaController::class, 'eliminar']);


    //PERFILES
    Route::get('/perfil', [PerfilController::class, 'index'])->middleware('validarRuta');
    Route::get('/listado-rol', [PerfilController::class, 'listar']);
    Route::post('/guardar-rol', [PerfilController::class, 'guardar']);
    Route::get('/editar-rol/{id}', [PerfilController::class, 'editar']);
    Route::put('/actualizar-rol/{id}', [PerfilController::class, 'actualizar']);
    Route::get('/eliminar-rol/{id}', [PerfilController::class, 'eliminar']);
    Route::get('/acceso-perfil/{id}', [PerfilController::class, 'accesoPerfil']);
    Route::get('/acceso-por-perfil/{menu}/{tipo}/{perfil}', [PerfilController::class, 'mantenimientoAccesoPerfil']);
    Route::get('/dato-perfil', [PerfilController::class, 'datoPerfil']);

    //USUARIO
    // Route::get('/usuario', [UsuarioController::class, 'index'])->middleware('validarRuta');
    // Route::get('/listado-usuario', [UsuarioController::class, 'listar']);
    // Route::post('/guardar-usuario', [UsuarioController::class, 'guardar']);
    // Route::get('/editar-usuario/{id}', [UsuarioController::class, 'editar']);
    // Route::put('/actualizar-usuario/{id}', [UsuarioController::class, 'actualizar']);
    // Route::get('/eliminar-usuario/{id}', [UsuarioController::class, 'eliminar']);
    // Route::post('/cambiar-clave', [UsuarioController::class, 'cambiarClave']);
    // Route::get('/resetear-password/{id}', [UsuarioController::class, 'resetearPassword']);
    // Route::get('/bodega-usuario/{id}', [UsuarioController::class, 'bodegaUsuario']);
    // Route::get('/bodega-por-usuario/{bod}/{tipo}/{usuario}', [UsuarioController::class, 'mantenimientoBodegaUser']);

    //GESTION
    Route::get('/gestion', [GestionController::class, 'index'])->middleware('validarRuta');
    Route::get('/listado-gestion', [GestionController::class, 'listar']);
    Route::post('/guardar-gestion', [GestionController::class, 'guardar']);
    Route::get('/editar-gestion/{id}', [GestionController::class, 'editar']);
    Route::put('/actualizar-gestion/{id}', [GestionController::class, 'actualizar']);
    Route::get('/eliminar-gestion/{id}', [GestionController::class, 'eliminar']);

    //MENU
    Route::get('/menu', [MenuController::class, 'index'])->middleware('validarRuta'); 
    Route::get('/listado-menu', [MenuController::class, 'listar']);
    Route::post('/guardar-menu', [MenuController::class, 'guardar']);
    Route::get('/editar-menu/{id}', [MenuController::class, 'editar']);
    Route::put('/actualizar-menu/{id}', [MenuController::class, 'actualizar']);
    Route::get('/eliminar-menu/{id}', [MenuController::class, 'eliminar']);
    // GESTION-MENU
    Route::get('/gestion-menu', [GestionMenuController::class, 'index'])->middleware('validarRuta');
    Route::get('/listado-gestion-menu', [GestionMenuController::class, 'listar']);
    Route::post('/guardar-gestion-menu', [GestionMenuController::class, 'guardar']);
    Route::get('/editar-gestion-menu/{id}', [GestionMenuController::class, 'editar']);
    Route::put('/actualizar-gestion-menu/{id}', [GestionMenuController::class, 'actualizar']);
    Route::get('/eliminar-gestion-menu/{id}', [GestionMenuController::class, 'eliminar']);


    //FUNCIONARIO
    Route::get('/funcionario', [FuncionarioController::class, 'index'])->middleware('validarRuta');
    Route::get('/listado-funcionario', [FuncionarioController::class, 'listar']);
    Route::post('/guardar-funcionario', [FuncionarioController::class, 'guardar']);
    Route::get('/editar-funcionario/{id}', [FuncionarioController::class, 'editar']);
    Route::put('/actualizar-funcionario/{id}', [FuncionarioController::class, 'actualizar']);
    Route::get('/eliminar-funcionario/{id}', [FuncionarioController::class, 'eliminar']);

    //AREA FUNCIONARIO
    Route::get('/area-funcionario', [AreaController::class, 'areaFuncionario'])->middleware('auth');
    Route::get('/funcionarios-por-area/{idarea}', [AreaController::class, 'listaFuncArea']);
    Route::get('/funcionarios-areas-mant/{idfunc}/{tipo}/{idarea}', [AreaController::class, 'aggQuitarFuncionario']);


    //MEDICINA-ESPECIALIDAD
    Route::get('/especialidad-medicina', [EspecialidadMedicinaController::class, 'especialidadMedicinas'])->middleware('auth');
    Route::get('/especialidad-por-medicina/{id}', [EspecialidadMedicinaController::class, 'listaMedicinasEspecialidad']);
    Route::get('/especialidad-medicina-mant/{idmed}/{tipo}/{idesp}', [EspecialidadMedicinaController::class, 'aggQuitarMedicina']);


    //INGRESO BODEGA
    // Route::get('/ingreso-bodega', [BodegaPrincipalController::class, 'index'])->middleware('validarRuta');
    Route::get('/listado-medicamentos-filtra/{text}', [BodegaPrincipalController::class, 'buscarMedicamentos']);
    Route::get('/listado-insumos-filtra/{text}', [BodegaPrincipalController::class, 'buscarInsumos']);
    Route::get('/listado-lab-mat-filtra/{text}', [BodegaPrincipalController::class, 'buscarLaboratorioMat']);
    Route::get('/listado-lab-react-filtra/{text}', [BodegaPrincipalController::class, 'buscarLaboratorioReact']);
    Route::get('/listado-lab-microb-filtra/{text}', [BodegaPrincipalController::class, 'buscarLaboratorioMicrob']);
    Route::get('/listado-item-filtra/{text}/{bodega}', [BodegaPrincipalController::class, 'buscarItem']);
    Route::get('/listado-medicamentos-dial-filtra/{text}', [BodegaPrincipalController::class, 'buscarMedicDialisis']);
    Route::get('/listado-insumo-dial-filtra/{text}', [BodegaPrincipalController::class, 'buscarInsumosDialisis']);
    Route::get('/listado-lab-ins-filtra/{text}', [BodegaPrincipalController::class, 'buscarLabDialisis']);
    Route::get('/listado-proteccion-filtra/{text}', [BodegaPrincipalController::class, 'buscarProteccion']);
    Route::post('/guardar-ingreso-bodega', [BodegaPrincipalController::class, 'guardarIngreso']);
    Route::get('/descargar-reporte/{nombre}', [BodegaPrincipalController::class, 'descargarPdf']);

    Route::get('/listado-item-filtra-dev/{text}/{bodega}', [BodegaPrincipalController::class, 'buscarItemDevolucion']);
    Route::get('/listado-medicamentos-filtra-dev/{text}/{bodega}', [BodegaPrincipalController::class, 'buscarMedicamentosDevolucion']);
    Route::get('/listado-insumos-filtra-dev/{text}/{bodega}', [BodegaPrincipalController::class, 'buscarInsumosDevolucion']);
    Route::get('/listado-proteccion-filtra-dev/{text}/{bodega}', [BodegaPrincipalController::class, 'buscarProteccionDevolucion']);
    Route::get('/listado-lab-mat-filtra-dev/{text}/{bodega}', [BodegaPrincipalController::class, 'buscarLaboratorioMatDevolucion']);
    Route::get('/listado-lab-react-filtra-dev/{text}', [BodegaPrincipalController::class, 'buscarLaboratorioReactDevolucion']);

    //guardar medicima
    Route::get('/mantenimiento-item', [MedicinaController::class, 'index'])->middleware('validarRuta');
    Route::post('/guardar-medicina', [MedicinaController::class, 'guardaMedicina']);
    Route::put('/actualiza-medicina/{id}', [MedicinaController::class, 'actualizaMedicina']);
    Route::post('/guardar-insumo', [MedicinaController::class, 'guardaInsumo']);
    Route::put('/actualiza-insumo/{id}', [MedicinaController::class, 'actualizaInsumo']);
    Route::post('/guardar-lab', [MedicinaController::class, 'guardaLaboratorio']);
    Route::put('/actualiza-lab/{id}', [MedicinaController::class, 'actualizaLaboratorio']);
    Route::post('/guardar-item', [MedicinaController::class, 'guardaItem']);
    Route::put('/actualiza-item/{id}', [MedicinaController::class, 'actualizaItem']);
    Route::post('/guardar-med-dialisis', [MedicinaController::class, 'guardaMedicinaDialisis']);
    Route::put('/actualiza-med-dialisis/{id}', [MedicinaController::class, 'actualizaMedicinaDialisis']);
    Route::post('/guardar-ins-dialisis', [MedicinaController::class, 'guardaInsumoDialisis']);
    Route::put('/actualiza-ins-dialisis/{id}', [MedicinaController::class, 'actualizaInsumoDialisis']);
    Route::post('/guardar-lab-dialisis', [MedicinaController::class, 'guardaLabDialisis']);
    Route::put('/actualiza-lab-dialisis/{id}', [MedicinaController::class, 'actualizaLabDialisis']);
    Route::get('/detalle-item-act/{bodega}/{tipo}/{item}', [MedicinaController::class, 'DetalleItem']);

    Route::get('/bloqueo-item/{id}/{bodega}', [MedicinaController::class, 'verAcceso']);
    Route::get('/parametriza-item/{id}/{esp}/{bod}/{valor}', [MedicinaController::class, 'parametriza']);

    Route::get('/parametriza-insumo', [MedicinaController::class, 'vistaInsumo'])->middleware('validarRuta');
    Route::get('/acceso-item/{id}/{bodega}', [MedicinaController::class, 'accesoInsumo']);
    Route::get('/acceso-item-enf/{id}/{bodega}', [MedicinaController::class, 'accesoInsumoEnf']);
    Route::get('/acceso-med-enf-lider/{id}/{bodega}', [MedicinaController::class, 'accesoMedicinaEnfLider']);
    Route::get('/insumo-por-area/{id}/{tipo}/{bodega}', [MedicinaController::class, 'mantenimientoAccesoInsumo']);


    Route::get('/parametriza-medicina', [MedicinaController::class, 'vistaMedicamentos'])->middleware('validarRuta');
    Route::get('/acceso-medicina/{id}/{bodega}', [MedicinaController::class, 'accesoMedicamentos']);
    Route::get('/medicina-por-area/{id}/{tipo}/{bodega}', [MedicinaController::class, 'mantenimientoAccesoMedicamentos']);
    Route::get('/medicina-seleccionar-todos/{id}/{tipo}', [MedicinaController::class, 'agregarMedicamentosTodos']);


    Route::get('/consulta-stock-medicamentos', [BodegaPrincipalController::class, 'stockMedicamentoLote']);

    //LISTADO INGRESO BODEGA
    Route::get('/listar-ingreso-bodega', [BodegaPrincipalController::class, 'listado'])->middleware('validarRuta');
    Route::get('/filtra-ingreso-bod/{ini}/{fin}/{tipo}/{perso}', [BodegaPrincipalController::class, 'filtrarIngreso']);
    Route::get('/cargar-proveedor', [BodegaPrincipalController::class, 'cargaProveedor']);
    Route::get('/cargar-bodeguero', [BodegaPrincipalController::class, 'cargaBodeguero']);
    Route::get('/reporte-ingreso-bod-gral/{idcomproba}/{bodega}', [BodegaPrincipalController::class, 'reporteIngresoBodGral']);

    //LISTADO EGRESO BODEGA
    Route::get('/listar-egreso-bodega', [BodegaPrincipalController::class, 'listadoEgreso'])->middleware('validarRuta');
    Route::get('/filtra-egreso-bod/{ini}/{fin}/{tipo}/{perso}', [BodegaPrincipalController::class, 'filtrarEgresoBodega']);
    Route::get('/reporte-egreso-bod-gral/{idcomproba}/{bodega}', [BodegaPrincipalController::class, 'reporteEgresoBodGral']);

    //EGRESO BODEGA
    Route::get('/egreso-bodega', [BodegaPrincipalController::class, 'vistaEgreso'])->middleware('validarRuta');
    Route::get('/listado-medicamentos-lote/{text}/{bodega}', [BodegaPrincipalController::class, 'buscarMedicamentosLote']);
    Route::get('/listado-insumos-lote/{text}/{bodega}', [BodegaPrincipalController::class, 'buscarInsumosLote']);
    Route::get('/listado-lab-mat-lote/{text}', [BodegaPrincipalController::class, 'buscarLaboratorioMatLote']);
    Route::get('/listado-lab-react-lote/{text}', [BodegaPrincipalController::class, 'buscarLaboratorioReactLote']);
    Route::get('/listado-lab-microb-lote/{text}', [BodegaPrincipalController::class, 'buscarLaboratorioMicroLote']);
    Route::get('/listado-items-lote/{text}/{iditem}', [BodegaPrincipalController::class, 'buscarItemsLote']);
    Route::get('/listado-proteccion-lote/{text}/{iditem}', [BodegaPrincipalController::class, 'buscarProteccionLote']);
    Route::get('/listado-lab-filtra/{text}/{iditem}', [BodegaPrincipalController::class, 'buscarLaboratorioLote']);
    Route::post('/guardar-egreso-bodega', [BodegaPrincipalController::class, 'guardarEgreso']);
    Route::get('/reporte-egreso-bod-farmacia/{idcomproba}/{bodega}', [BodegaPrincipalController::class, 'reporteEgresoBodFarm']);

    //LISTO PEDIDOS A BODEGA
    Route::get('/listado-pedido', [BodegaPrincipalController::class, 'listaPedidoVista'])->middleware('validarRuta');
    Route::get('/filtra-pedido-bod-gral/{ini}/{fin}', [BodegaPrincipalController::class, 'filtrarPedidoBodega']); 
    Route::get('/detalle-pedidos/{idcomprobante}/{idbodega}', [BodegaPrincipalController::class, 'detallePedidoBodega']);
    Route::post('/validar-pedido-solicitado', [BodegaPrincipalController::class, 'validaPedido']);
    Route::get('visualizardoc/{documentName}', [BodegaPrincipalController::class, 'visualizarDoc']);
    Route::post('/anular-comprobante-bodega', [BodegaPrincipalController::class, 'anulaPedido']);

    //LISTO PEDIDOS A BODEGA (detalle usuario logueado)
    Route::get('/detalle-pedido', [BodegaPrincipalController::class, 'listaPedidoVistaSolicitante'])->middleware('validarRuta');
    Route::get('/detalle-pedidos-sol/{idcomprobante}/{idbodega}', [BodegaPrincipalController::class, 'detallePedidoBodegaSoli']);
    Route::get('/filtra-pedido-bod-gral-sol/{ini}/{fin}', [BodegaPrincipalController::class, 'filtrarPedidoBodegaSol']);
    Route::get('/actualizar-pedido/{id}/{bodega}', [BodegaPrincipalController::class, 'editarPedido']);
   

    //INVENTARIO
    Route::get('/inventario-consultar', [InventarioController::class, 'inventarioVista'])->middleware('validarRuta');
    Route::get('/filtra-inventario/{idbodega}/{lugar}/{f}/{fini}/{ffin}', [InventarioController::class, 'buscarInventario']);
    // Route::get('/filtra-inventario/{idbodega}/{lugar}', [InventarioController::class, 'buscarInventario']);
    Route::get('/listado-inventario/{idbodega}/{lugar}', [InventarioController::class, 'listarInventario']);
    Route::get('/filtra-listado-inventario/{idbodega}/{lugar}/{txt}', [InventarioController::class, 'FiltralistarInventario']);
    Route::get('/detalle-inventario-item/{idbodega}/{lugar}/{item}', [InventarioController::class, 'buscarDetalleItemBod']);
    Route::get('/detalle-inventario-item-fecha/{idbodega}/{lugar}/{item}/{f}/{fini}/{ffin}', [InventarioController::class, 'buscarDetalleItemBodFecha']);
    Route::get('/detalle-suma-resta/{idbodpr}/{ini}/{fin}', [InventarioController::class, 'detalleMovimiento']);
    Route::get('/egreso-item-pdf/{idbodpr}/{ini}/{fin}/{bodega}', [InventarioController::class, 'reporteItemEgreso']);
    Route::get('/pdf-inventario/{idbodega}/{lugar}/{f}/{fini}/{ffin}', [InventarioController::class, 'pdfInventario']);

    Route::get('/detalle-inventario-itemlote-fecha/{idbodprod}/{lugar}/{f}/{fini}/{ffin}/{bode}', [InventarioController::class, 'buscarDetalleItemLoteFecha']);
    
    Route::get('/pdf-inventario-egreso/{idbodega}/{lugar}/{f}/{fini}/{ffin}', [InventarioController::class, 'pdfInventarioEgreso']);
    Route::get('/egreso-area-farmacia/{fini}/{ffin}/{bodega}', [InventarioController::class, 'EgresoAreaFarmacia']);
    Route::get('/egreso-area-farma-detalle/{fini}/{ffin}/{bodega}/{area}', [InventarioController::class, 'EgresoAreaFarmaciaDetalle']);
    Route::get('/pdf-egreso-area-farma-detalle/{fini}/{ffin}/{bodega}/{area}', [InventarioController::class, 'pdfEgresoAreaFarmaciaDetalle']);
    Route::get('/pdf-inventario-egreso-area/{idbodega}/{lugar}/{f}/{fini}/{ffin}', [InventarioController::class, 'pdfInventarioEgresoArea']);
    Route::get('pdf-egreso-area-farma-bodega/{fini}/{ffin}/{idbodega}', [InventarioController::class, 'pdfInventarioEgresoAreaBodega']);
    Route::get('/detalle-egreso/{id_pedido}', [InventarioController::class, 'detalleEgreso']);
    Route::get('/kardex-farmacia-item/{idpr}/{ini}/{fin}/{bodega}', [InventarioController::class, 'kardexItemFarmacia']);
    Route::get('/kardex-farmacia-item-reporte/{idpr}/{ini}/{fin}/{bodega}', [InventarioController::class, 'kardexItemFarmaciaPdf']);

    Route::get('/kardex-farmacia-itemlote/{idpr}/{ini}/{fin}/{bodega}/{lote}/{Idbodprod}', [InventarioController::class, 'kardexItemLoteFarmacia']);
    Route::get('/kardex-farmacia-itemlote-reporte/{idpr}/{ini}/{fin}/{bodega}/{lote}/{Idbodprod}', [InventarioController::class, 'kardexItemLoteFarmaciaPdf']);
    
    Route::get('/kardex-bodega-item/{idpr}/{ini}/{fin}/{bodega}', [InventarioController::class, 'kardexItemBodega']);
    Route::get('/kardex-bodega-itemlote/{idpr}/{ini}/{fin}/{bodega}/{lote}/{Idbodprod}', [InventarioController::class, 'kardexItemLoteBodega']);
    Route::get('/kardex-bodega-item-reporte/{idpr}/{ini}/{fin}/{bodega}', [InventarioController::class, 'kardexItemBodegaPdf']);
    Route::get('/kardex-bodega-itemlote-reporte/{idpr}/{ini}/{fin}/{bodega}/{lote}/{Idbodprod}', [InventarioController::class, 'kardexItemLoteBodegaPdf']);

    Route::get('/pdf-inventario-egreso-excel/{idbodega}/{lugar}/{f}/{fini}/{ffin}', [ReportesExcelController::class, 'reporteExcelEgreso']);
    Route::get('/pdf-inventario-egreso-area-excel/{idbodega}/{lugar}/{f}/{fini}/{ffin}', [ReportesExcelController::class, 'reporteExcelEgresoArea']);

    Route::get('/excel-inventario-indiv-farm/{idbodega}/{filtro}/{ini}/{fin}/{ff}', [ReportesExcelController::class, 'reporteExcelInvIndivFarma']);
    Route::get('/pdf-inventario-individual-excel/{idbodega}/{filtro}/{ini}/{fin}/{ff}', [ReportesExcelController::class, 'pdfInventarioIndExcel']);


    //INVENTARIO FARMACIA
    Route::get('/inventario-farmacia', [InventarioController2::class, 'inventarioVista'])->middleware('validarRuta');
    Route::get('/filtra-inventario2/{idbodega}/{lugar}/{f}', [InventarioController2::class, 'buscarInventario']);
    Route::get('/detalle-inventario-item2/{idbodega}/{lugar}/{item}', [InventarioController2::class, 'buscarDetalleItemBod']);
    Route::post('/actualiza-existencia-bodprod', [InventarioController2::class, 'actualizaExistencia']);
    Route::get('/pdf-inventario-individual/{idbodega}/{filtro}/{ini}/{fin}/{ff}', [InventarioController2::class, 'pdfInventarioIndividual']);
   
    Route::get('/pdf-inventario-indiv-farm/{idbodega}/{filtro}/{ini}/{fin}/{ff}', [InventarioController2::class, 'pdfInventarioIndividualFarmacia']);

    Route::get('/detalle-prodbod/{idbodega}/{idprodbod}', [InventarioController2::class, 'verProdBodega']);
    Route::post('/actualizar-prod-bodega', [InventarioController2::class, 'actualizaDetallePB']);


    //INTEROPERABILIDAD
    Route::get('/interoperabilidad', [InterOperabilidadController::class, 'vistaOperabilidad']);
    Route::get('/filtra-interoperabilidad/{bodega}', [InterOperabilidadController::class, 'buscarStockBodega']);
    Route::get('/enviar-nacional/{bodega}', [InterOperabilidadController::class, 'saldoTarea']);
    Route::get('/estado-tarea/{uuid}', [InterOperabilidadController::class, 'estadoTarea']);


    //PEDIDO BODEGA DESDE FARMACIA
    Route::get('/pedido-bodega-farmacia', [BodegaFarmaciaController::class, 'index'])->middleware('validarRuta');
    Route::post('/guardar-pedido-bodega-farmacia', [BodegaFarmaciaController::class, 'guardarPedidoBodega']);

    //PEDIDO DESDE LABORATORIO A FARMACIA
    Route::post('/guardar-pedido-bodega-farm-laborat', [BodegaFarmaciaController::class, 'guardarPedidoBodegaFarm']);

    //PEDIDO DESDE INSUMO A FARMACIA
    Route::post('/guardar-pedido-bodega-farm-insumo', [BodegaFarmaciaController::class, 'guardarPedidoBodegaFarmInsumo']);

    

    //EGRESO FARMACIA
    Route::get('/egreso-medicamentos', [BodegaFarmaciaController::class, 'vistaEgreso'])->middleware('validarRuta');
    Route::get('/listado-medicamentos-lote-farmacia/{text}/{bodega}', [BodegaFarmaciaController::class, 'buscarMedicamentosLote']);
    Route::get('/listado-insumos-lote-farmacia/{text}/{bodega}', [BodegaFarmaciaController::class, 'buscarInsumosLote']);
    // Route::get('/listado-lab-mat-lote-farmacia/{text}', [BodegaFarmaciaController::class, 'buscarLaboratorioMatLote']);
    // Route::get('/listado-lab-react-lote/{text}', [BodegaFarmaciaController::class, 'buscarLaboratorioReactLote']);
    // Route::get('/listado-lab-microb-lote/{text}', [BodegaFarmaciaController::class, 'buscarLaboratorioMicroLote']);

    Route::get('/listado-lab-lote-farmacia/{text}/{bodega}', [BodegaFarmaciaController::class, 'buscarLaboratorioFarmLote']);
    Route::post('/guardar-egreso-bodega-farma', [BodegaFarmaciaController::class, 'guardarEgreso']);
    Route::put('/actualizar-pedido-bodega-farm/{idcomp}', [BodegaFarmaciaController::class, 'actualizaPedidoLab']);
    Route::put('/actualizar-pedido-bodega-farm-laborat/{idcomp}', [BodegaFarmaciaController::class, 'actualizaPedidoLabDialisis']);
    


    //DEVOLUCION FARMACIA-BODEGA
    Route::get('/devolucion-medicamentos', [BodegaFarmaciaController::class, 'vistaDevolucion']);
    Route::post('/guardar-devolucion-farma-bodega', [BodegaFarmaciaController::class, 'guardarDevolucionFarmBodega']);

    //LISTADO EGRESO BODEGA
    Route::get('/listado-egreso-farmacia', [BodegaFarmaciaController::class, 'listadoEgreso'])->middleware('validarRuta');
    Route::get('/filtra-egreso-bod-farmacia/{ini}/{fin}/{paci}', [BodegaFarmaciaController::class, 'filtrarEgresoBodega']);
    Route::get('/cargar-paciente', [BodegaFarmaciaController::class, 'cargaPaciente']);

    


    //INGRESO FARMACIA
    Route::get('/ingreso-farmacia', [BodegaFarmaciaController::class, 'vistaIngreso'])->middleware('validarRuta');
    Route::post('/guardar-ingreso-bodega-farmacia', [BodegaFarmaciaController::class, 'guardarIngreso']);
    Route::get('/reporte-transferencia-bod-farm/{idcomproba}/{bodega}', [BodegaPrincipalController::class, 'reporteTransfBodGral']);
    Route::get('/reporte-previo-transferencia/{idcomproba}/{bodega}', [BodegaPrincipalController::class, 'reporteAntesTransferencia']);
    Route::get('/reporte-rollo/{idcomproba}/{bodega}', [BodegaPrincipalController::class, 'reporteRolloFarmacia']);
    Route::get('/descargar-reporte/{nombre}', [BodegaPrincipalController::class, 'descargarPdf']);
   
    

    //LISTADO INGRESO DIRECTO FARMACIA
    Route::get('/listar-ingreso-farmacia', [BodegaFarmaciaController::class, 'listadoIngresos'])->middleware('validarRuta');
    Route::get('/filtra-ingreso-bod-farmacia/{ini}/{fin}', [BodegaFarmaciaController::class, 'filtrarIngresoDirecto']);
    Route::get('/reporte-ingreso-bod-farmacia/{idcomproba}/{bodega}', [BodegaFarmaciaController::class, 'reporteIngresoBodFarmacia']);

    Route::get('/verifica-permiso', [FuncionarioController::class, 'verPermisos']);


    //HOSPITALIZADOS
    Route::get('/listar-hospitalizados', [BodegaFarmaciaController::class, 'vistaPacientes'])->middleware('validarRuta');
    Route::get('/paciente-hospitalizados/{fecha}/{servicion}', [BodegaFarmaciaController::class, 'generarReportePaciente']);


    //VISTA PARA SOLICITAR
    Route::get('/solicita-item', [SolicitarController::class, 'index'])->middleware('validarRuta');
    Route::post('/guardar-pedido-bodega-area', [SolicitarController::class, 'guardarPedidoArea']);
    Route::post('/validar-pedido-solicitado-area', [SolicitarController::class, 'validaPedidoArea']);
    Route::get('/listado-items-stock/{text}/{iditem}', [SolicitarController::class, 'buscarItemsStock']);
   
    Route::get('/listado-proteccion-stock/{text}/{iditem}', [SolicitarController::class, 'buscarProteccionStock']);
    Route::get('/historial-pedido/{usuario}/{bodega}', [SolicitarController::class, 'historialPedido']);
    Route::put('/actualizar-pedido-bodega-area/{idcomp}', [SolicitarController::class, 'actualizaPedidoArea']);

    Route::get('/listar-pedidos', [SolicitarController::class, 'misPedidos']);

    //LISTO PEDIDOS A BODEGA FARMACIA
    Route::get('/listado-pedido-farmacia', [BodegaFarmaciaController::class, 'listaPedidoVista'])->middleware('validarRuta');
    Route::get('/filtra-pedido-bod-farmacia/{ini}/{fin}', [BodegaFarmaciaController::class, 'filtrarPedidoBodegaFarm']); 
    Route::get('/detalle-pedidos-farm/{idcomprobante}/{idbodega}', [BodegaFarmaciaController::class, 'detallePedidoBodegaFarm']);
    Route::post('/validar-pedido-solicitud-farm', [BodegaFarmaciaController::class, 'validaPedidoAfarm']);
    Route::get('/paquete-item-detalle/{idcomprobante}/{item}', [BodegaFarmaciaController::class, 'detalleItemPaquete']);
    Route::post('/anular-comprobante', [BodegaFarmaciaController::class, 'anularPedido']);
    Route::get('/detalle-pedidos-farm-todo/{idcomprobante}/{idbodega}', [BodegaFarmaciaController::class, 'detallePedidoBodegaFarmTodo']);
    Route::post('/revertir-comprobante', [BodegaFarmaciaController::class, 'revertirPedido']);

    //SOLICITUDES DE DEVOLUCIONES
    Route::get('/listado-devoluciones-farmacia', [BodegaFarmaciaController::class, 'listaDevolver'])->middleware('auth');
    Route::get('/filtra-devolver-farmacia-bod/{ini}/{fin}', [BodegaFarmaciaController::class, 'filtrarDevolverBodega']); 
    Route::post('/validar-devolucion-farmacia', [BodegaFarmaciaController::class, 'validarDevolucion']);

    //RECETAS
    Route::get('/dispensar-receta', [BodegaFarmaciaController::class, 'dispensarMedicamentos'])->middleware('validarRuta');
    Route::get('/filtra-pedido-receta/{ini}/{fin}', [BodegaFarmaciaController::class, 'filtrarReceta']); 

    //INSUMOS
    Route::get('/dispensar-insumo', [BodegaFarmaciaController::class, 'dispensarInsumos'])->middleware('validarRuta');
    Route::get('/filtra-pedido-insumo/{ini}/{fin}', [BodegaFarmaciaController::class, 'filtrarInsumo']); 
    Route::get('/aprobarInsumoDespachado', [BodegaFarmaciaController::class, 'aprobarInsumoEntregado']); 

    //PROVEEDOR
    Route::post('/guardar-proveedor', [ProveedorController::class, 'guardar']);
    Route::get('/carga-combo-bodega', [ProveedorController::class, 'cargaComboBodega']);


    //PAQUETES
    Route::get('/paquete-dialisis', [PaqueteDialisisController::class, 'vistaIngreso'])->middleware('validarRuta');
    Route::get('/listado-paquete', [PaqueteDialisisController::class, 'listar']);
    Route::post('/guardar-paquete', [PaqueteDialisisController::class, 'guardarIngresoPaquete']);
    Route::get('/editar-paquete/{id}', [PaqueteDialisisController::class, 'editar']);
    Route::put('/actualizar-paquete/{id}', [PaqueteDialisisController::class, 'actualizarPaquete']);
    Route::get('/eliminar-paquete/{id}', [PaqueteDialisisController::class, 'eliminarPaquete']);
    Route::post('/guardar-ingreso-bodega-farmacia11', [PaqueteDialisisController::class, 'guardarIngreso']);
    Route::get('/detalle-paquete/{id}', [PaqueteDialisisController::class, 'detallePaquete']);
    Route::get('/carga-items', [PaqueteDialisisController::class, 'cargarItems']);
    Route::post('/guardar-detalle-paquete', [PaqueteDialisisController::class, 'guardarDetallePaquete']);
    Route::get('/editar-detalle-paq/{id}', [PaqueteDialisisController::class, 'editarDetalle']);
    Route::get('/item-seleccionado/{id}', [PaqueteDialisisController::class, 'itemSeleccionado']);
    Route::put('/actualizar-detalle-paquete/{id}', [PaqueteDialisisController::class, 'actualizarDetallePaquete']);
    Route::get('/eliminar-detalle-paq/{id}', [PaqueteDialisisController::class, 'eliminarDetallePaquete']);

    //SOLICITA PAQUETE FARMACIA
    Route::get('/solicitud-paquete', [SolicitudPaqueteDialController::class, 'index'])->middleware('validarRuta');   
    Route::post('/valida-paquete', [SolicitudPaqueteDialController::class, 'validaPaqueteApi']);
    Route::post('/guardar-pedido-dialisis-farmacia', [SolicitudPaqueteDialController::class, 'guardarSolicitud']);

    //LISTA PAQUETE FARMACIA
    Route::get('/listado-solicitud', [SolicitudPaqueteDialController::class, 'listado'])->middleware('validarRuta');
    Route::get('/filtra-pedido-paquete-sol/{ini}/{fin}', [SolicitudPaqueteDialController::class, 'listadoPedido']);
    Route::get('/paq-detalle-pedidos-sol/{idcomprobante}/{idbodega}', [SolicitudPaqueteDialController::class, 'detallePedidoPaquete']);
    Route::get('/actualizar-pedido-paquete/{id}/{bodega}', [SolicitudPaqueteDialController::class, 'editarPedido']);
    Route::put('/actualizar-pedido-dialisis-farmacia/{id}', [SolicitudPaqueteDialController::class, 'actualizarSolicitud']);
    Route::post('/valida-entrega-paquete', [SolicitudPaqueteDialController::class, 'validarEntrega']);


    //PAQUETES CIRUGIA
    Route::get('/paquetes-listar', [PaqueteCirugiaController::class, 'vistaIngreso'])->middleware('auth');
    Route::get('/listado-paquete-cirugia', [PaqueteCirugiaController::class, 'listar']);
    Route::post('/guardar-paquete-cirugia', [PaqueteCirugiaController::class, 'guardarIngresoPaqueteCirugia']);
    Route::get('/editar-paquete-cirugia/{id}', [PaqueteCirugiaController::class, 'editar']);
    Route::put('/actualizar-paquete-cirugia/{id}', [PaqueteCirugiaController::class, 'actualizarPaquete']);
    Route::get('/eliminar-paquete-cirugia/{id}', [PaqueteCirugiaController::class, 'eliminarPaquete']);
    Route::post('/guardar-ingreso-bodega-farmacia-cirugia', [PaqueteCirugiaController::class, 'guardarIngreso']);
    Route::get('/detalle-paquete-cirugia/{id}', [PaqueteCirugiaController::class, 'detallePaquete']);
    Route::post('/guardar-detalle-paquete-cirugia', [PaqueteCirugiaController::class, 'guardarDetallePaquete']);
    Route::get('/editar-detalle-paq-cirugia/{id}', [PaqueteCirugiaController::class, 'editarDetalle']);
    Route::get('/item-seleccionado-cirugia/{id}', [PaqueteCirugiaController::class, 'itemSeleccionado']);
    Route::put('/actualizar-detalle-paquete-cirugia/{id}', [PaqueteCirugiaController::class, 'actualizarDetallePaquete']);
    Route::get('/eliminar-detalle-paq-cirugia/{id}', [PaqueteCirugiaController::class, 'eliminarDetallePaquete']);
    Route::get('/carga-items-paquete-ciru', [PaqueteCirugiaController::class, 'cargarItems']);

    Route::post('/valida-entrega-paquete-cq', [SolicitudPaqueteCirugiaController::class, 'validarEntrega']);
    Route::get('/paq-cq-detalle-pedidos-sol/{idcomprobante}/{idbodega}', [SolicitudPaqueteCirugiaController::class, 'detallePedidoPaquete']);
    
    
});

    //LISTA PAQUETE FARMACIA
    Route::get('/solicitaInsumo/{persona}/{cedula}', [SolicitaDialisisController::class, 'solicitarPaquete']);
    Route::get('/valida-paquete/{id}/{cant}', [SolicitudPaqueteDialController::class, 'validaPaquete']);
    Route::get('/cargar-cie10', [SolicitaDialisisController::class, 'cargaCie10']);
    Route::post('/guardar-pedido-dial', [SolicitaDialisisController::class, 'guardarSolicitud']);


    //LISTA PAQUETE FARMACIA
    Route::get('/pedirInsumo/{persona}/{cedula}', [SolicitaDialisisController::class, 'solicitarInsumo']);
    Route::get('/listado-insumos-lote-far/{text}/{bodega}', [BodegaPrincipalController::class, 'buscarInsumosLoteDialisis']);
    Route::post('/guardar-pedido-farm-insumo', [SolicitaDialisisController::class, 'guardarSolicitudInsumo']);

    //insumo hosp dia
    Route::get('/pedirInsumoHD/{persona}/{cedula}', [SolicitaDialisisController::class, 'solicitarInsumoHD']);
    //LISTA PAQUETE FARMACIA HOS DIA
    Route::get('/solicitaInsumoHD/{persona}/{cedula}', [SolicitaDialisisController::class, 'solicitarPaqueteHD']);

    //LISTA PAQUETE FARMACIA
    Route::get('/medicina-administrada/{idpac}/{idresp}', [SolicitaDialisisController::class, 'vistaAdministracionMed']);
    Route::get('/ medicamento-receta-seleccionado/{paciente}/{iditem}', [SolicitaDialisisController::class, 'infoMedicamento']);
    Route::post('/guardar-medicacion-administrada', [SolicitaDialisisController::class, 'guardarMedicacion']);


    //SOLICITA PAQUETE FARMACIA CIRUGIA
    Route::get('/solicitudPaquetesCQ/{persona}/{cedula}', [SolicitudPaqueteCirugiaController::class, 'index']); 
    Route::get('/valida-paquete-cirugia/{id}/{cant}', [SolicitudPaqueteCirugiaController::class, 'validaPaqueteCirugia']);
    Route::post('/guardar-pedido-cirugia', [SolicitudPaqueteCirugiaController::class, 'guardarSolicitudCirugia']); 

    //SOLICITA PAQUETE FARMACIA CENTRO OBSTETRICO
    Route::get('/solicitudPaquetesCO/{persona}/{cedula}', [SolicitudPaqueteCentroObstController::class, 'index']); 
    Route::post('/valida-entrega-paquete-co', [SolicitudPaqueteCentroObstController::class, 'validarEntregaPaquete']);
    
    //NEW PEDIDOS BODEGA
    Route::get('/buscar-item', [PedidoBodegaController::class, 'buscarStockItem']);
    Route::get('/valida-agregar-item/{item}/{bod}', [PedidoBodegaController::class, 'validaItemSeleccionado']);
    Route::post('/guardar-pedido-bodega-farmacia-new', [PedidoBodegaController::class, 'guardarPedidoBodegaDesdeFarmacia']);
    Route::get('/detalle-pedidos-new/{idcomprobante}/{idbodega}', [PedidoBodegaController::class, 'detallePedidoBodega']);
    Route::get('/actualizar-pedido-new/{id}/{bodega}', [PedidoBodegaController::class, 'editarPedido']);
    Route::put('/actualizar-pedido-bodega-farm-laborat-new/{idcomp}', [PedidoBodegaController::class, 'actualizaPedidoBodega']);
    Route::get('/detalle-pedidos-sol-new/{idcomprobante}/{idbodega}', [PedidoBodegaController::class, 'detallePedidoBod']);
    Route::post('/guardar-pedido-bodega-area-new', [PedidoBodegaController::class, 'guardarPedidoArea']);
    Route::post('/guardar-pedido-bodega-farm-insumo-new', [PedidoBodegaController::class, 'guardarPedidoInsumo']);
    Route::post('/guardar-pedido-bodega-farm-laborat-new', [PedidoBodegaController::class, 'guardarPedidoBodegaFarm']);
    
   
    
Route::get('/clear', function() {

    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
 
    return "Cleared!";
 
 });

 
Route::get('/turnero', [TurneroController::class, 'index']);

