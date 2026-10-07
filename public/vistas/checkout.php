<?php
// public/vistas/checkout.php
$tituloPagina = 'Checkout';
$rutaBase = '../../';

require_once __DIR__ . '/../../includes/ayudantes/AyudanteSesion.php';
require_once __DIR__ . '/../../includes/filtros/FiltroAutenticacion.php';
FiltroAutenticacion::protegerVista('login.php');

$scriptsPagina = ['checkout.js'];
require_once __DIR__ . '/../../includes/plantillas/header.php';
?>

<section class="mb-4">
  <h1 class="h3 mb-1">Confirmar pedido</h1>
  <p class="text-muted mb-0">Revisa tu pedido, elige un método de pago y confirma la compra.</p>
</section>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="tarjeta-resumen p-3 p-md-4 mb-4">
      <h2 class="h5 mb-3">Resumen del pedido</h2>
      <div id="listaCheckout"></div>
      <hr>
      <div class="d-flex justify-content-between">
        <span class="fw-semibold">Total</span>
        <span id="totalCheckout" class="fw-bold precio-libro">Q0.00</span>
      </div>
    </div>

    <div class="tarjeta-resumen p-3 p-md-4">
      <h2 class="h5 mb-3">Método de pago (simulado)</h2>
      <div class="form-check mb-2">
        <input class="form-check-input" type="radio" name="metodoPago" id="pagoTarjeta" value="tarjeta" checked>
        <label class="form-check-label" for="pagoTarjeta">Tarjeta de crédito/débito</label>
      </div>
      <div class="form-check mb-2">
        <input class="form-check-input" type="radio" name="metodoPago" id="pagoContraEntrega" value="contra_entrega">
        <label class="form-check-label" for="pagoContraEntrega">Pago contra entrega</label>
      </div>
      <p class="small text-muted mb-0">Este es un pago simulado: no se realiza ningún cobro real.</p>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="tarjeta-resumen p-3 p-md-4">
      <h2 class="h5 mb-3">Confirmación</h2>
      <p class="small text-muted">Al confirmar, se descuenta el stock y se crea tu pedido con estado <strong>pendiente</strong>.</p>
      <button id="btnConfirmarPedido" type="button" class="btn btn-primary w-100">Confirmar pedido</button>
      <div id="mensajeCheckout" class="mt-3"></div>
    </div>
  </div>
</div>

<!-- Confirmación en pantalla (RF20) -->
<div id="modalConfirmacion" class="modal fade" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center p-4">
      <div class="modal-body">
        <h2 class="h4 mb-3">¡Pedido creado!</h2>
        <p class="mb-1">Número de pedido: <strong id="numeroPedidoConfirmado"></strong></p>
        <p class="mb-4">Total: <strong id="totalPedidoConfirmado"></strong></p>
        <a href="mis-pedidos.php" class="btn btn-primary w-100">Ver mis pedidos</a>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/plantillas/footer.php'; ?>