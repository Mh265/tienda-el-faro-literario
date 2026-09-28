<?php
// public/vistas/wishlist.php
$tituloPagina = 'Lista de deseos';
$rutaBase = '../../';

require_once __DIR__ . '/../../includes/ayudantes/AyudanteSesion.php';
require_once __DIR__ . '/../../includes/filtros/FiltroAutenticacion.php';
FiltroAutenticacion::protegerVista('login.php');

$scriptsPagina = ['wishlist.js'];
require_once __DIR__ . '/../../includes/plantillas/header.php';
?>

<section class="mb-4">
  <h1 class="h3 mb-1">Lista de deseos</h1>
  <p class="text-muted mb-0">Libros que guardaste para más adelante.</p>
</section>

<div id="zonaEstadoWishlist" class="text-center my-5 text-muted d-none" aria-live="polite"></div>
<div id="listaWishlist" class="d-flex flex-column gap-3"></div>

<?php require_once __DIR__ . '/../../includes/plantillas/footer.php'; ?>