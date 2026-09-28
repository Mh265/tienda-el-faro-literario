// assets/js/mis-pedidos.js
document.addEventListener('DOMContentLoaded', () => {
  cargarMisPedidos();
});

const CLASES_ESTADO_PEDIDO = {
  pendiente: 'text-bg-secondary',
  pagado: 'text-bg-info',
  enviado: 'text-bg-warning',
  entregado: 'text-bg-success',
  cancelado: 'text-bg-danger'
};

// Carga los pedidos del usuario.
async function cargarMisPedidos() {
  const zonaEstado = document.getElementById('zonaEstadoPedidos');
  const lista = document.getElementById('listaPedidos');

  zonaEstado.classList.remove('d-none');
  zonaEstado.textContent = 'Cargando tus pedidos...';

  const resultado = await llamarApi('pedidos.php', 'GET');

  if (!resultado.exito) {
    zonaEstado.textContent = resultado.mensaje || 'No se pudo cargar tu historial.';
    return;
  }

  if (resultado.datos.length === 0) {
    zonaEstado.textContent = 'Todavía no tienes pedidos. ¡Explora el catálogo!';
    return;
  }

  zonaEstado.classList.add('d-none');
  resultado.datos.forEach((pedido) => lista.appendChild(crearTarjetaPedido(pedido)));
}

function crearTarjetaPedido(pedido) {
  const tarjeta = document.createElement('div');
  tarjeta.className = 'tarjeta-pedido p-3 p-md-4';

  const encabezado = document.createElement('div');
  encabezado.className = 'd-flex flex-wrap justify-content-between align-items-center gap-2';

  const info = document.createElement('div');
  const numero = document.createElement('p');
  numero.className = 'fw-semibold mb-1';
  numero.textContent = `Pedido #${pedido.id_pedido}`;
  const fecha = document.createElement('p');
  fecha.className = 'small text-muted mb-0';
  fecha.textContent = new Date(pedido.fecha).toLocaleDateString('es-GT', { year: 'numeric', month: 'long', day: 'numeric' });
  info.append(numero, fecha);

  const badgeEstado = document.createElement('span');
  badgeEstado.className = `badge ${CLASES_ESTADO_PEDIDO[pedido.estado] || 'text-bg-secondary'} text-capitalize`;
  badgeEstado.textContent = pedido.estado;

  const total = document.createElement('span');
  total.className = 'fw-bold precio-libro';
  total.textContent = `Q${Number(pedido.total).toFixed(2)}`;

  encabezado.append(info, badgeEstado, total);

  const btnDetalle = document.createElement('button');
  btnDetalle.type = 'button';
  btnDetalle.className = 'btn btn-sm btn-outline-primary mt-2';
  btnDetalle.textContent = 'Ver detalle';

  const zonaDetalle = document.createElement('div');
  zonaDetalle.className = 'mt-3 d-none';

  btnDetalle.addEventListener('click', () => alternarDetallePedido(pedido.id_pedido, zonaDetalle));

  tarjeta.append(encabezado, btnDetalle, zonaDetalle);
  return tarjeta;
}

// api/pedidos.php?id=# → app/controladores/PedidoController.php
async function alternarDetallePedido(idPedido, zonaDetalle) {
  if (zonaDetalle.dataset.cargado === '1') {
    zonaDetalle.classList.toggle('d-none');
    return;
  }

  const resultado = await llamarApi(`pedidos.php?id=${idPedido}`, 'GET');

  if (!resultado.exito) {
    zonaDetalle.textContent = resultado.mensaje;
    zonaDetalle.classList.remove('d-none');
    return;
  }

  resultado.datos.lineas.forEach((linea) => {
    const fila = document.createElement('div');
    fila.className = 'd-flex justify-content-between small border-top pt-2 mt-2';

    const texto = document.createElement('span');
    texto.textContent = `${linea.nombre} × ${linea.cantidad}`;

    const subtotal = document.createElement('span');
    subtotal.textContent = `Q${(linea.precio * linea.cantidad).toFixed(2)}`;

    fila.append(texto, subtotal);
    zonaDetalle.appendChild(fila);
  });

  zonaDetalle.dataset.cargado = '1';
  zonaDetalle.classList.remove('d-none');
}