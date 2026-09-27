<?php
// public/vistas/carrito.php
$tituloPagina = 'Carrito de compras';
$rutaBase = '../../';
require_once __DIR__ . '/../../includes/plantillas/header.php';
?>

<section class="mb-4">
  <h1 class="h3 mb-1">Tu carrito</h1>
  <p class="text-muted mb-0">Revisa tus libros antes de continuar con la compra.</p>
</section>

<div id="zonaCarritoVacio" class="text-center my-5 d-none">
  <p class="text-muted mb-3">Tu carrito está vacío.</p>
  <a href="catalogo.php" class="btn btn-primary">Ver catálogo</a>
</div>

<div id="listaCarrito"></div>

<div id="resumenCarrito" class="row justify-content-end mt-4 d-none">
  <div class="col-12 col-md-5 col-lg-4">
    <div class="tarjeta-resumen p-3 p-md-4">
      <div class="d-flex justify-content-between mb-3">
        <span class="fw-semibold">Total</span>
        <span id="totalCarrito" class="fw-bold precio-libro">Q0.00</span>
      </div>
      <a href="checkout.php" class="btn btn-primary w-100">Continuar a checkout</a>
      <a href="catalogo.php" class="btn btn-outline-primary w-100 mt-2">Seguir comprando</a>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/plantillas/footer.php'; ?>