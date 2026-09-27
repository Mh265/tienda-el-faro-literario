<?php
// includes/plantillas/admin-nav.php
// $rutaBase y $vistaActual los define la vista admin que incluye este
// parcial, justo después de requerir header.php.
$vistaActual = $vistaActual ?? '';
$enlacesAdmin = [
    'index'      => ['texto' => 'Panel',      'archivo' => 'index.php'],
    'libros'     => ['texto' => 'Libros',     'archivo' => 'libros.php'],
    'categorias' => ['texto' => 'Categorías', 'archivo' => 'categorias.php'],
    'pedidos'    => ['texto' => 'Pedidos',    'archivo' => 'pedidos.php'],
    'usuarios'   => ['texto' => 'Usuarios',   'archivo' => 'usuarios.php'],
];
?>
<nav class="nav nav-pills nav-admin mb-4" aria-label="Secciones de administración">
  <?php foreach ($enlacesAdmin as $clave => $enlace): ?>
    <a class="nav-link <?= $vistaActual === $clave ? 'active' : '' ?>"
       href="<?= $rutaBase ?>public/vistas/admin/<?= $enlace['archivo'] ?>">
      <?= htmlspecialchars($enlace['texto']) ?>
    </a>
  <?php endforeach; ?>
</nav>