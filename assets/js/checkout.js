/**
 * Checkout (RF11, RF13, RF20). El carrito vive en el navegador (carrito.js);
 * aquí solo se envían id_producto y cantidad.
 * El método de pago es simulado (RF13): no viaja a la API.
 */
document.addEventListener('DOMContentLoaded', () => {
  renderizarResumenCheckout();
  document.getElementById('btnConfirmarPedido').addEventListener('click', confirmarPedido);
});

function renderizarResumenCheckout() {
  const carrito = obtenerCarrito();
  const lista = document.getElementById('listaCheckout');
  const boton = document.getElementById('btnConfirmarPedido');

  lista.innerHTML = '';

  if (carrito.length === 0) {
    const vacio = document.createElement('p');
    vacio.className = 'text-muted mb-2';
    vacio.textContent = 'Tu carrito está vacío.';

    const enlace = document.createElement('a');
    enlace.href = 'catalogo.php';
    enlace.className = 'btn btn-outline-primary btn-sm';
    enlace.textContent = 'Ver catálogo';

    lista.append(vacio, enlace);
    boton.disabled = true;
  } else {
    carrito.forEach((item) => {
      const fila = document.createElement('div');
      fila.className = 'd-flex justify-content-between small py-2 border-bottom';

      const texto = document.createElement('span');
      texto.textContent = `${item.nombre} × ${item.cantidad}`;

      const subtotal = document.createElement('span');
      subtotal.textContent = `Q${(item.precio * item.cantidad).toFixed(2)}`;

      fila.append(texto, subtotal);
      lista.appendChild(fila);
    });
    boton.disabled = false;
  }

  document.getElementById('totalCheckout').textContent = `Q${calcularTotalCarrito(carrito).toFixed(2)}`;
}

// api/pedidos.php → app/controladores/PedidoController.php
async function confirmarPedido() {
  const carrito = obtenerCarrito();
  if (carrito.length === 0) return;

  const boton = document.getElementById('btnConfirmarPedido');
  const metodoPago = document.querySelector('input[name="metodoPago"]:checked').value;

  limpiarMensajeCheckout();
  boton.disabled = true;

  // Pago simulado (RF13): con tarjeta se muestra una breve espera, sin cobro real.
  if (metodoPago === 'tarjeta') {
    boton.textContent = 'Procesando pago...';
    await new Promise((resolver) => setTimeout(resolver, 800));
  }
  boton.textContent = 'Creando pedido...';

  // Solo id_producto y cantidad: el servidor toma el precio de la BD.
  const items = carrito.map((item) => ({ id_producto: item.id_producto, cantidad: item.cantidad }));
  const resultado = await llamarApi('pedidos.php', 'POST', { items });

  boton.textContent = 'Confirmar pedido';

  if (!resultado.exito) {
    boton.disabled = false;
    // Sesión vencida: se vuelve al login y luego a este checkout.
    if (resultado.estado === 401) {
      window.location.href = 'login.php?volver=checkout.php';
      return;
    }
    // Ej. "Stock insuficiente para X. Disponible: N."
    mostrarMensajeCheckout('danger', resultado.mensaje);
    return;
  }

  // Confirmación en pantalla (RF20): id_pedido y total ya vienen en la respuesta.
  document.getElementById('numeroPedidoConfirmado').textContent = `#${resultado.datos.id_pedido}`;
  document.getElementById('totalPedidoConfirmado').textContent = `Q${Number(resultado.datos.total).toFixed(2)}`;

  vaciarCarrito();
  renderizarResumenCheckout(); // deja el resumen vacío y el botón deshabilitado

  bootstrap.Modal.getOrCreateInstance(document.getElementById('modalConfirmacion')).show();
}

function limpiarMensajeCheckout() {
  document.getElementById('mensajeCheckout').innerHTML = '';
}

// El texto se asigna con textContent: el mensaje viene de la API.
function mostrarMensajeCheckout(tipo, texto) {
  const zona = document.getElementById('mensajeCheckout');
  zona.innerHTML = '';

  const alerta = document.createElement('div');
  alerta.className = `alert alert-${tipo} mb-0`;
  alerta.setAttribute('role', 'alert');
  alerta.textContent = texto;
  zona.appendChild(alerta);
}
