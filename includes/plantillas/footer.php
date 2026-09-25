<?php
// includes/plantillas/footer.php
// Cierra el <main> abierto en header.php.
// $scriptsPagina (opcional): arreglo con los JS propios de la vista, ej. ['inicio.js']
$rutaBase = $rutaBase ?? '';
?>
</main>

<footer class="pie-sitio mt-5 py-4">
  <div class="container">
    <div class="row gy-3">
      <div class="col-md-4">
        <p class="h5 mb-2">El Faro Literario</p>
        <p class="small mb-0">Tu librería en línea desde 2020: novelas, clásicos, ciencia ficción, fantasía y mucho más, con envíos a todo el país.</p>
      </div>
      <div class="col-md-4">
        <p class="fw-semibold mb-2">Explora</p>
        <ul class="list-unstyled small mb-0">
          <li><a href="<?= $rutaBase ?>public/index.php">Inicio</a></li>
          <li><a href="<?= $rutaBase ?>public/vistas/catalogo.php">Catálogo</a></li>
          <li><a href="<?= $rutaBase ?>public/vistas/carrito.php">Carrito</a></li>
        </ul>
      </div>
      <div class="col-md-4">
        <p class="fw-semibold mb-2">Visítanos</p>
        <ul class="list-unstyled small mb-0">
          <li>6ª Avenida 12-34, Zona 10, Ciudad de Guatemala</li>
          <li>Lunes a sábado, 9:00 a 19:00</li>
          <li>contacto@elfaroliterario.com</li>
        </ul>
      </div>
    </div>
    <hr class="border-secondary">
    <p class="small text-center mb-0">&copy; <?= date('Y') ?> El Faro Literario. Proyecto final INTECAP.</p>
  </div>
</footer>

<!-- Orden: Bootstrap -> api.js -> scripts de la vista -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $rutaBase ?>assets/js/api.js"></script>
<?php if (!empty($scriptsPagina)): ?>
  <?php foreach ($scriptsPagina as $script): ?>
<script src="<?= $rutaBase ?>assets/js/<?= htmlspecialchars($script) ?>"></script>
  <?php endforeach; ?>
<?php endif; ?>

<!--
  AVISO: descomentar esta línea cuando exista assets/js/auth.js (Feature/frontend-auth).
  Ese script es el que mostrará/ocultará #zonaSesionInvitado y #zonaSesionUsuario,
  y conectará el botón #btnCerrarSesion del header.
  <script src="<?= $rutaBase ?>assets/js/auth.js"></script>
-->
</body>
</html>