// assets/js/carrito.js
/**
 * Manejo del carrito de compras. El carrito vive enteramente en el
 * navegador (localStorage): no hay tabla `carrito` en la base de datos.
 * Solo al hacer checkout se envían id_producto y cantidad al servidor.
 * Este archivo se carga en TODAS las páginas (ver footer.php) porque el
 * contador del header necesita estar siempre actualizado, como el
 * contador de un supermercado que muestra cuántos artículos llevas
 * aunque estés en cualquier pasillo, no solo en la caja.
 */
const CARRITO_STORAGE_KEY = 'elFaroCarrito';

function obtenerCarrito() {
  try {
    const datos = localStorage.getItem(CARRITO_STORAGE_KEY);
    return datos ? JSON.parse(datos) : [];
  } catch (error) {
    return [];
  }
}

function guardarCarrito(carrito) {
  localStorage.setItem(CARRITO_STORAGE_KEY, JSON.stringify(carrito));
  actualizarContadorCarrito();
}

// Si el stock se conoce (número), la cantidad nunca lo supera; si no se
// conoce (ej. libro agregado desde la wishlist), no se limita aquí y el
// servidor lo valida al crear el pedido.
function limitarPorStock(cantidad, stock) {
  return Number.isFinite(stock) ? Math.min(cantidad, stock) : cantidad;
}

// Agrega un libro al carrito, o suma la cantidad si ya estaba. `libro`
// trae los datos que necesitamos mostrar sin volver a pedirlos al
// servidor; el stock real se revalida siempre al hacer el pedido.
// `stock` es opcional: la vista de detalle lo envía (libro.cantidad) para
// que el carrito pueda poner un tope a la cantidad.
// Devuelve { cantidadEnCarrito, limitadoPorStock } para que quien llama
// pueda avisar al usuario si se ajustó la cantidad.
function agregarAlCarrito(libro, cantidad = 1, stock = null) {
  const carrito = obtenerCarrito();
  const idProducto = Number(libro.id_producto);
  const existente = carrito.find((item) => Number(item.id_producto) === idProducto);

  const stockNuevo = stock !== null ? Number(stock) : existente ? existente.stock : null;
  const stockConocido = Number.isFinite(stockNuevo) ? stockNuevo : null;

  const cantidadPrevia = existente ? existente.cantidad : 0;
  const cantidadDeseada = cantidadPrevia + cantidad;
  const cantidadFinal = limitarPorStock(cantidadDeseada, stockConocido);

  // Sin stock disponible: no se agrega nada.
  if (cantidadFinal < 1) {
    return { cantidadEnCarrito: cantidadPrevia, limitadoPorStock: true };
  }

  if (existente) {
    existente.cantidad = cantidadFinal;
    existente.stock = stockConocido;
  } else {
    carrito.push({
      id_producto: idProducto,
      nombre: libro.nombre,
      autor: libro.autor,
      precio: Number(libro.precio),
      imagen: libro.imagen || null,
      stock: stockConocido,
      cantidad: cantidadFinal
    });
  }

  guardarCarrito(carrito);
  return { cantidadEnCarrito: cantidadFinal, limitadoPorStock: cantidadFinal < cantidadDeseada };
}

function actualizarCantidadCarrito(id_producto, cantidad) {
  let carrito = obtenerCarrito();
  if (cantidad < 1) {
    carrito = carrito.filter((item) => Number(item.id_producto) !== id_producto);
  } else {
    carrito = carrito.map((item) =>
      Number(item.id_producto) === id_producto
        ? { ...item, cantidad: limitarPorStock(cantidad, item.stock) }
        : item
    );
  }
  guardarCarrito(carrito);
}

function eliminarDelCarrito(id_producto) {
  const carrito = obtenerCarrito().filter((item) => Number(item.id_producto) !== id_producto);
  guardarCarrito(carrito);
}

function vaciarCarrito() {
  localStorage.removeItem(CARRITO_STORAGE_KEY);
  actualizarContadorCarrito();
}

function calcularTotalCarrito(carrito = obtenerCarrito()) {
  return carrito.reduce((total, item) => total + item.precio * item.cantidad, 0);
}

function contarUnidadesCarrito(carrito = obtenerCarrito()) {
  return carrito.reduce((total, item) => total + item.cantidad, 0);
}

function actualizarContadorCarrito() {
  const contador = document.getElementById('contadorCarrito');
  if (contador) {
    contador.textContent = contarUnidadesCarrito();
  }
}

document.addEventListener('DOMContentLoaded', () => {
  actualizarContadorCarrito();

  // Esta comprobación evita que carrito.js (cargado en TODAS las páginas)
  // intente tocar elementos que solo existen en carrito.php.
  if (document.getElementById('listaCarrito')) {
    renderizarCarrito();
    document.getElementById('listaCarrito').addEventListener('change', manejarCambioCantidad);
    document.getElementById('listaCarrito').addEventListener('click', manejarClicEliminar);
  }
});

function renderizarCarrito() {
  const carrito = obtenerCarrito();
  const lista = document.getElementById('listaCarrito');
  const zonaVacio = document.getElementById('zonaCarritoVacio');
  const resumen = document.getElementById('resumenCarrito');

  lista.innerHTML = '';

  if (carrito.length === 0) {
    zonaVacio.classList.remove('d-none');
    resumen.classList.add('d-none');
    return;
  }

  zonaVacio.classList.add('d-none');
  resumen.classList.remove('d-none');

  const rutaBase = API_URL.replace(/api\/$/, '');
  carrito.forEach((item) => lista.appendChild(crearFilaCarrito(item, rutaBase)));
  actualizarResumenCarrito(carrito);
}

function crearFilaCarrito(item, rutaBase) {
  const fila = document.createElement('div');
  fila.className = 'row g-3 align-items-center fila-carrito py-3 border-bottom';
  fila.dataset.idProducto = item.id_producto;

  const portada = item.imagen
    ? `${rutaBase}assets/img/uploads/${item.imagen}`
    : `${rutaBase}assets/img/portada-defecto.svg`;

  const colImagen = document.createElement('div');
  colImagen.className = 'col-3 col-md-2';
  const img = document.createElement('img');
  img.src = portada;
  img.className = 'img-fluid rounded portada-carrito';
  img.alt = `Portada de ${item.nombre}`;
  colImagen.appendChild(img);

  const colTexto = document.createElement('div');
  colTexto.className = 'col-9 col-md-5';
  const titulo = document.createElement('p');
  titulo.className = 'mb-1 fw-semibold';
  titulo.textContent = item.nombre;
  const autor = document.createElement('p');
  autor.className = 'mb-0 small text-muted';
  autor.textContent = item.autor;
  colTexto.append(titulo, autor);

  const colCantidad = document.createElement('div');
  colCantidad.className = 'col-6 col-md-2';
  const idInput = `cantidad-${item.id_producto}`;
  const labelCantidad = document.createElement('label');
  labelCantidad.className = 'form-label small mb-1';
  labelCantidad.htmlFor = idInput;
  labelCantidad.textContent = 'Cantidad';
  const inputCantidad = document.createElement('input');
  inputCantidad.type = 'number';
  inputCantidad.id = idInput;
  inputCantidad.className = 'form-control form-control-sm input-cantidad';
  inputCantidad.min = '1';
  // Tope de stock, solo si se conoce (ver agregarAlCarrito).
  if (Number.isFinite(item.stock)) {
    inputCantidad.max = item.stock;
  }
  inputCantidad.value = item.cantidad;
  colCantidad.append(labelCantidad, inputCantidad);

  const colSubtotal = document.createElement('div');
  colSubtotal.className = 'col-4 col-md-2 text-md-end';
  const subtotal = document.createElement('span');
  subtotal.className = 'fw-semibold';
  subtotal.textContent = `Q${(item.precio * item.cantidad).toFixed(2)}`;
  colSubtotal.appendChild(subtotal);

  const colEliminar = document.createElement('div');
  colEliminar.className = 'col-2 col-md-1 text-end';
  const btnEliminar = document.createElement('button');
  btnEliminar.type = 'button';
  btnEliminar.className = 'btn btn-sm btn-outline-danger btn-eliminar-item';
  btnEliminar.setAttribute('aria-label', 'Quitar del carrito');
  btnEliminar.textContent = '✕';
  colEliminar.appendChild(btnEliminar);

  fila.append(colImagen, colTexto, colCantidad, colSubtotal, colEliminar);
  return fila;
}

function manejarCambioCantidad(evento) {
  if (!evento.target.classList.contains('input-cantidad')) return;

  const fila = evento.target.closest('.fila-carrito');
  const idProducto = Number(fila.dataset.idProducto);
  let cantidad = parseInt(evento.target.value, 10);

  if (isNaN(cantidad) || cantidad < 1) {
    cantidad = 1;
  }

  // actualizarCantidadCarrito aplica el tope de stock; renderizarCarrito
  // vuelve a pintar el input con el valor ya ajustado.
  actualizarCantidadCarrito(idProducto, cantidad);
  renderizarCarrito();
}

function manejarClicEliminar(evento) {
  if (!evento.target.classList.contains('btn-eliminar-item')) return;

  const fila = evento.target.closest('.fila-carrito');
  eliminarDelCarrito(Number(fila.dataset.idProducto));
  renderizarCarrito();
}

function actualizarResumenCarrito(carrito) {
  document.getElementById('totalCarrito').textContent = `Q${calcularTotalCarrito(carrito).toFixed(2)}`;
}
