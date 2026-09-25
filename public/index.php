<?php
// public/index.php
$tituloPagina = 'Inicio';
$rutaBase = '../';
$scriptsPagina = ['inicio.js'];
require_once __DIR__ . '/../includes/plantillas/header.php';

// TODO(mock): reemplazar por fetch a api/libros.php (LibroController) cuando exista Feature/libro-api.
// Campos tomados de bd/tienda_libros.sql (tabla productos), imagen=NULL en el seed -> se usa portada-defecto.svg.
$librosDestacados = [
    ['id_producto' => 1,  'nombre' => 'Cien años de soledad',     'autor' => 'Gabriel García Márquez', 'precio' => 180.00],
    ['id_producto' => 5,  'nombre' => 'Dune',                     'autor' => 'Frank Herbert',           'precio' => 210.00],
    ['id_producto' => 19, 'nombre' => 'Harry Potter y la piedra filosofal', 'autor' => 'J. K. Rowling',  'precio' => 165.00],
    ['id_producto' => 29, 'nombre' => 'Don Quijote de la Mancha',  'autor' => 'Miguel de Cervantes',     'precio' => 210.00],
];
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

<!-- Libros destacados -->
<section class="mb-5">
  <h2 class="h3 mb-4">Destacados</h2>
  <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4" id="listaDestacados">
    <?php foreach ($librosDestacados as $libro): ?>
    <div class="col">
      <div class="card tarjeta-libro">
        <img src="<?= $rutaBase ?>assets/img/portada-defecto.svg" class="card-img-top" alt="Portada de <?= htmlspecialchars($libro['nombre']) ?>">
        <div class="card-body d-flex flex-column">
          <h3 class="h6 card-title"><?= htmlspecialchars($libro['nombre']) ?></h3>
          <p class="card-text small text-muted mb-2"><?= htmlspecialchars($libro['autor']) ?></p>
          <p class="precio-libro mb-3">Q<?= number_format($libro['precio'], 2) ?></p>
          <a href="<?= $rutaBase ?>public/vistas/detalle-libro.php?id=<?= $libro['id_producto'] ?>" class="btn btn-outline-primary mt-auto btn-sm">Ver detalle</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/plantillas/footer.php'; ?>