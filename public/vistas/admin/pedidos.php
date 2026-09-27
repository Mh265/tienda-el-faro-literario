<?php
// public/vistas/admin/pedidos.php
$tituloPagina = 'Administrar pedidos';
$rutaBase = '../../../';

require_once __DIR__ . '/../../../includes/ayudantes/AyudanteSesion.php';
require_once __DIR__ . '/../../../includes/filtros/FiltroAutenticacion.php';
FiltroAutenticacion::protegerVistaAdministrador('../login.php');

$scriptsPagina = ['admin.js'];
require_once __DIR__ . '/../../../includes/plantillas/header.php';

$vistaActual = 'pedidos';
require_once __DIR__ . '/../../../includes/plantillas/admin-nav.php';
?>

<section class="mb-4">
  <h1 class="h3 mb-1">Administrar pedidos</h1>
  <p class="text-muted mb-0">Todos los pedidos de la tienda y su estado.</p>
</section>

<div id="zonaEstadoPedidosAdmin" class="text-center my-4 text-muted d-none"></div>

<div class="table-responsive">
  <table class="table align-middle tabla-admin">
    <thead><tr><th>#</th><th>Usuario</th><th>Fecha</th><th>Total</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
    <tbody id="tablaPedidosAdmin"></tbody>
  </table>
</div>

<?php require_once __DIR__ . '/../../../includes/plantillas/footer.php'; ?>