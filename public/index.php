<?php
// public/index.php
$tituloPagina = 'Inicio';
$rutaBase = '../';
$scriptsPagina = ['inicio.js'];
require_once __DIR__ . '/../includes/plantillas/header.php';
?>

<!-- Hero -->
<section class="hero-inicio row align-items-center g-4 mb-5">
  <div class="col-lg-6">
    <h1 class="display-5 fw-bold">Encuentra tu próxima gran lectura</h1>
    <p class="lead">Novelas, clásicos, ciencia ficción, fantasía y mucho más, con entrega a todo el país.</p>
    <a href="<?= $rutaBase ?>public/vistas/catalogo.php" class="btn btn-primary btn-lg">Ver catálogo</a>
  </div>
  <div class="col-lg-6">
    <div class="hero-portada-generica">
      <img src="<?= $rutaBase ?>assets/img/portada-defecto.svg" alt="Portadas de libros" height="180">
    </div>
  </div>
</section>

<!-- Libros destacados: se cargan por fetch en inicio.js (api/libros.php ya disponible) -->
<section class="mb-5">
  <h2 class="h3 mb-4">Destacados</h2>
  <div id="zonaEstadoDestacados" class="text-center my-4 text-muted d-none" aria-live="polite"></div>
  <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4" id="listaDestacados"></div>
</section>

<?php require_once __DIR__ . '/../includes/plantillas/footer.php'; ?>