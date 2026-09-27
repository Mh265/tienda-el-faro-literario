// assets/js/detalle-libro.js
let idProductoActual = null;
let usuarioEnSesion = null;

document.addEventListener('DOMContentLoaded', async () => {
  idProductoActual = new URLSearchParams(window.location.search).get('id');

  if (!idProductoActual) {
    mostrarErrorDetalle('No se indicó ningún libro.');
    return;
  }

  // api/auth.php?accion=verificar-sesion → app/controladores/AuthController.php
  const sesion = await llamarApi('auth.php?accion=verificar-sesion', 'POST');
  usuarioEnSesion = sesion.exito ? sesion.datos : null;

  await cargarDetalleLibro();
  await cargarResenas();

  document.getElementById('btnAgregarCarritoDetalle').addEventListener('click', manejarAgregarCarrito);
  document.getElementById('btnWishlistDetalle').addEventListener('click', manejarToggleWishlist);
  document.getElementById('formResena').addEventListener('submit', manejarSubmitResena);
});

// api/libros.php?id=# → app/controladores/LibroController.php
async function cargarDetalleLibro() {
  const zonaEstado = document.getElementById('zonaEstadoDetalle');
  const contenido = document.getElementById('contenidoDetalle');

  zonaEstado.classList.remove('d-none');
  zonaEstado.textContent = 'Cargando libro...';

  const resultado = await llamarApi(`libros.php?id=${idProductoActual}`, 'GET');

  if (!resultado.exito) {
    mostrarErrorDetalle(resultado.mensaje || 'No se pudo cargar el libro.');
    return;
  }

  const libro = resultado.datos;
  document.title = `${libro.nombre} | El Faro Literario`;
  window.libroActual = libro; // usado por manejarAgregarCarrito y manejarToggleWishlist

  const rutaBase = API_URL.replace(/api\/$/, '');
  const portada = libro.imagen
    ? `${rutaBase}assets/img/uploads/${libro.imagen}`
    : `${rutaBase}assets/img/portada-defecto.svg`;

  document.getElementById('portadaDetalle').src = portada;
  document.getElementById('portadaDetalle').alt = `Portada de ${libro.nombre}`;

  document.getElementById('badgeCategoriaDetalle').textContent = libro.nombre_categoria;
  document.getElementById('tituloDetalle').textContent = libro.nombre;
  document.getElementById('autorDetalle').textContent = libro.autor;
  document.getElementById('precioDetalle').textContent = `Q${Number(libro.precio).toFixed(2)}`;
  document.getElementById('descripcionLargaDetalle').textContent = libro.descripcion_larga || libro.descripcion_corta || 'Sin descripción disponible.';

  const sinStock = Number(libro.cantidad) === 0;
  const badgeStock = document.getElementById('badgeStockDetalle');
  badgeStock.textContent = sinStock ? 'Sin stock' : `${libro.cantidad} disponibles`;
  badgeStock.className = `badge ${sinStock ? 'badge-sin-stock' : 'text-bg-success'}`;

  const inputCantidad = document.getElementById('cantidadDetalle');
  inputCantidad.max = libro.cantidad;
  document.getElementById('btnAgregarCarritoDetalle').disabled = sinStock;

  // Ficha técnica
  document.getElementById('fichaAutor').textContent = libro.autor;
  document.getElementById('fichaEditorial').textContent = libro.editorial || '—';
  document.getElementById('fichaCategoria').textContent = libro.nombre_categoria;
  document.getElementById('fichaFecha').textContent = libro.fecha_publicacion
    ? new Date(libro.fecha_publicacion).toLocaleDateString('es-GT')
    : '—';
  document.getElementById('fichaStock').textContent = libro.cantidad;

  // Miga de pan
  const migaDePan = document.getElementById('migaDePan');
  const itemCategoria = document.createElement('li');
  itemCategoria.className = 'breadcrumb-item';
  itemCategoria.textContent = libro.nombre_categoria;
  const itemTitulo = document.createElement('li');
  itemTitulo.className = 'breadcrumb-item active';
  itemTitulo.setAttribute('aria-current', 'page');
  itemTitulo.textContent = libro.nombre;
  migaDePan.append(itemCategoria, itemTitulo);

  zonaEstado.classList.add('d-none');
  contenido.classList.remove('d-none');

  await sincronizarBotonWishlist();
}

function mostrarErrorDetalle(mensaje) {
  const zonaEstado = document.getElementById('zonaEstadoDetalle');
  zonaEstado.classList.remove('d-none');
  zonaEstado.textContent = mensaje;
}

function manejarAgregarCarrito() {
  const cantidad = parseInt(document.getElementById('cantidadDetalle').value, 10) || 1;
  agregarAlCarrito(window.libroActual, cantidad); // definido en carrito.js (global)

  const zonaMensaje = document.getElementById('mensajeAccionesDetalle');
  zonaMensaje.innerHTML = '';
  const alerta = document.createElement('div');
  alerta.className = 'alert alert-success mt-2 mb-0 py-2';
  alerta.textContent = 'Agregado al carrito.';
  zonaMensaje.appendChild(alerta);
}

// api/wishlist.php → app/controladores/WishlistController.php
// Comprueba si el libro ya está en la lista de deseos del usuario, para
// pintar el botón correctamente al cargar la página.
async function sincronizarBotonWishlist() {
  if (!usuarioEnSesion) return;

  const resultado = await llamarApi('wishlist.php', 'GET');
  if (!resultado.exito) return;

  const yaEstaEnWishlist = resultado.datos.some((item) => item.id_producto === Number(idProductoActual));
  actualizarTextoBotonWishlist(yaEstaEnWishlist);
}

function actualizarTextoBotonWishlist(activo) {
  const boton = document.getElementById('btnWishlistDetalle');
  boton.textContent = activo ? '♥ En tu lista de deseos' : '♥ Agregar a lista de deseos';
  boton.dataset.activo = activo ? '1' : '0';
}

// api/wishlist.php → app/controladores/WishlistController.php
async function manejarToggleWishlist() {
  if (!usuarioEnSesion) {
    window.location.href = 'login.php';
    return;
  }

  const boton = document.getElementById('btnWishlistDetalle');
  const yaActivo = boton.dataset.activo === '1';

  const resultado = yaActivo
    ? await llamarApi(`wishlist.php?id_producto=${idProductoActual}`, 'DELETE')
    : await llamarApi('wishlist.php', 'POST', { id_producto: Number(idProductoActual) });

  if (resultado.exito) {
    actualizarTextoBotonWishlist(!yaActivo);
  }
}

// api/resenas.php?id_producto=# → app/controladores/ResenaController.php
async function cargarResenas() {
  const zonaEstado = document.getElementById('zonaEstadoResenas');
  const lista = document.getElementById('listaResenas');

  const resultado = await llamarApi(`resenas.php?id_producto=${idProductoActual}`, 'GET');

  if (!resultado.exito) {
    zonaEstado.classList.remove('d-none');
    zonaEstado.textContent = resultado.mensaje;
    return;
  }

  const resenas = resultado.datos;

  if (resenas.length === 0) {
    zonaEstado.classList.remove('d-none');
    zonaEstado.textContent = 'Este libro todavía no tiene reseñas. ¡Sé el primero en opinar!';
  } else {
    zonaEstado.classList.add('d-none');
    const promedio = resenas.reduce((total, r) => total + r.calificacion, 0) / resenas.length;
    document.getElementById('promedioResenas').textContent = `${promedio.toFixed(1)} / 5 (${resenas.length} reseña${resenas.length === 1 ? '' : 's'})`;
    resenas.forEach((resena) => lista.appendChild(crearTarjetaResena(resena)));
  }

  // Mostrar el formulario solo si hay sesión activa.
  if (usuarioEnSesion) {
    document.getElementById('formularioResenaContenedor').classList.remove('d-none');
  } else {
    document.getElementById('avisoLoginResena').classList.remove('d-none');
  }
}

function crearTarjetaResena(resena) {
  const tarjeta = document.createElement('div');
  tarjeta.className = 'tarjeta-resena p-3';

  const encabezado = document.createElement('div');
  encabezado.className = 'd-flex justify-content-between align-items-center mb-1';

  const autor = document.createElement('span');
  autor.className = 'fw-semibold';
  autor.textContent = `${resena.nombre} ${resena.apellido}`;

  const estrellas = document.createElement('span');
  estrellas.className = 'texto-estrellas';
  estrellas.textContent = '★'.repeat(resena.calificacion) + '☆'.repeat(5 - resena.calificacion);
  estrellas.setAttribute('aria-label', `${resena.calificacion} de 5 estrellas`);

  encabezado.append(autor, estrellas);

  const fecha = document.createElement('p');
  fecha.className = 'small text-muted mb-2';
  fecha.textContent = new Date(resena.fecha).toLocaleDateString('es-GT');

  const comentario = document.createElement('p');
  comentario.className = 'mb-0';
  comentario.textContent = resena.comentario || '';

  tarjeta.append(encabezado, fecha, comentario);
  return tarjeta;
}

// api/resenas.php → app/controladores/ResenaController.php
async function manejarSubmitResena(evento) {
  evento.preventDefault();
  const formulario = evento.target;

  const datos = {
    id_producto: Number(idProductoActual),
    calificacion: Number(formulario.calificacion.value),
    comentario: formulario.comentario.value.trim() || null
  };

  const resultado = await llamarApi('resenas.php', 'POST', datos);

  const zonaMensaje = document.getElementById('mensajeResena');
  zonaMensaje.innerHTML = '';
  const alerta = document.createElement('div');
  alerta.className = `alert ${resultado.exito ? 'alert-success' : 'alert-danger'} mb-0`;
  alerta.textContent = resultado.mensaje;
  zonaMensaje.appendChild(alerta);

  if (resultado.exito) {
    formulario.reset();
    document.getElementById('listaResenas').innerHTML = '';
    cargarResenas();
  }
}