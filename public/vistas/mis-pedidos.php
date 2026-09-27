<?php
// public/vistas/mis-pedidos.php
$tituloPagina = 'Mis pedidos';
$rutaBase = '../../';

require_once __DIR__ . '/../../includes/ayudantes/AyudanteSesion.php';
require_once __DIR__ . '/../../includes/filtros/FiltroAutenticacion.php';
FiltroAutenticacion::protegerVista('login.php');

$scriptsPagina = ['mis-pedidos.js'];
require_once __DIR__ . '/../../includes/plantillas/header.php';
?>

<section class="mb-4">
  <h1 class="h3 mb-1">Mis pedidos</h1>
  <p class="text-muted mb-0">Historial de tus compras y su estado actual.</p>
</section>

<div id="zonaEstadoPedidos" class="text-center my-5 text-muted d-none" aria-live="polite"></div>
<div id="listaPedidos" class="d-flex flex-column gap-3"></div>

<?php require_once __DIR__ . '/../../includes/plantillas/footer.php'; ?>