<?php
// public/vistas/admin/index.php
$tituloPagina = 'Panel de administración';
$rutaBase = '../../../';

require_once __DIR__ . '/../../../includes/ayudantes/AyudanteSesion.php';
require_once __DIR__ . '/../../../includes/filtros/FiltroAutenticacion.php';
FiltroAutenticacion::protegerVistaAdministrador('../login.php');

$scriptsPagina = ['admin.js'];
require_once __DIR__ . '/../../../includes/plantillas/header.php';

$vistaActual = 'index';
require_once __DIR__ . '/../../../includes/plantillas/admin-nav.php';
?>

<section class="mb-4">
  <h1 class="h3 mb-1">Panel de administración</h1>
  <p class="text-muted mb-0">Resumen general de la tienda.</p>
</section>

<div class="row g-4 mb-4">
  <div class="col-md-4">
    <div class="tarjeta-stat p-4">
      <p class="text-muted small mb-1">Ingresos totales</p>
      <p id="statIngresos" class="h3 mb-0">Q0.00</p>
    </div>
  </div>
  <div class="col-md-4">
    <div class="tarjeta-stat p-4">
      <p class="text-muted small mb-1">Libros en catálogo</p>
      <p id="statLibros" class="h3 mb-0">0</p>
    </div>
  </div>
  <div class="col-md-4">
    <div class="tarjeta-stat p-4">
      <p class="text-muted small mb-1">Usuarios registrados</p>
      <p id="statUsuarios" class="h3 mb-0">0</p>
    </div>
  </div>
</div>

<div class="tarjeta-resumen p-3 p-md-4">
  <h2 class="h5 mb-3">Pedidos recientes</h2>
  <div id="zonaEstadoDashboard" class="text-muted small d-none"></div>
  <table class="table align-middle">
    <thead>
      <tr><th>#</th><th>Fecha</th><th>Total</th><th>Estado</th></tr>
    </thead>
    <tbody id="tablaPedidosRecientes"></tbody>
  </table>
  <a href="pedidos.php" class="btn btn-outline-primary btn-sm">Ver todos los pedidos</a>
</div>

<?php require_once __DIR__ . '/../../../includes/plantillas/footer.php'; ?>