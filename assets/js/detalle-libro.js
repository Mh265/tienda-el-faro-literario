// assets/js/detalle-libro.js
let idProductoActual = null;
let usuarioEnSesion = null;
let libroActual = null;
let miResenaActual = null; // reseña del usuario en sesión para este libro (si ya escribió una)

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
  libroActual = libro; // usado por manejarAgregarCarrito
  document.title = `${libro.nombre} | El Faro Literario`;

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
  inputCantidad.disabled = sinStock;
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

// Pinta una alerta bajo los botones de acción. Texto siempre con textContent.
function mostrarMensajeAcciones(tipo, texto) {
  const zonaMensaje = document.getElementById('mensajeAccionesDetalle');
  zonaMensaje.innerHTML = '';
  const alerta = document.createElement('div');
  alerta.className = `alert alert-${tipo} mt-2 mb-0 py-2`;
  alerta.textContent = texto;
  zonaMensaje.appendChild(alerta);
}

function manejarAgregarCarrito() {
  const inputCantidad = document.getElementById('cantidadDetalle');
  const stock = Number(libroActual.cantidad);

  // El atributo max del input no impide escribir un número mayor: se valida aquí.
  let cantidad = parseInt(inputCantidad.value, 10);
  if (isNaN(cantidad) || cantidad < 1) cantidad = 1;
  if (cantidad > stock) cantidad = stock;
  inputCantidad.value = cantidad;

  // agregarAlCarrito está en carrito.js (global). El tercer parámetro es el
  // stock, para que el carrito también pueda poner su tope.
  const resultado = agregarAlCarrito(libroActual, cantidad, stock);

  if (resultado.limitadoPorStock) {
    mostrarMensajeAcciones('warning', `Solo hay ${stock} disponibles: tu carrito quedó con ${resultado.cantidadEnCarrito}.`);
  } else {
    mostrarMensajeAcciones('success', 'Agregado al carrito.');
  }
}

// api/wishlist.php → app/controladores/WishlistController.php
// Comprueba si el libro ya está en la lista de deseos del usuario, para
// pintar el botón correctamente al cargar la página.
async function sincronizarBotonWishlist() {
  if (!usuarioEnSesion) return;

  const resultado = await llamarApi('wishlist.php', 'GET');
  if (!resultado.exito) return;

  const yaEstaEnWishlist = resultado.datos.some((item) => Number(item.id_producto) === Number(idProductoActual));
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
  } else {
    mostrarMensajeAcciones('danger', resultado.mensaje);
  }
}

// api/resenas.php?id_producto=# → app/controladores/ResenaController.php
async function cargarResenas() {
  const zonaEstado = document.getElementById('zonaEstadoResenas');
  const lista = document.getElementById('listaResenas');
  const promedio = document.getElementById('promedioResenas');

  // Siempre se repinta desde cero: así sirve tanto la carga inicial como
  // después de publicar, editar o eliminar una reseña.
  lista.innerHTML = '';
  promedio.textContent = '';

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
    const suma = resenas.reduce((total, r) => total + Number(r.calificacion), 0);
    promedio.textContent = `${(suma / resenas.length).toFixed(1)} / 5 (${resenas.length} reseña${resenas.length === 1 ? '' : 's'})`;
    resenas.forEach((resena) => lista.appendChild(crearTarjetaResena(resena)));
  }

  prepararFormularioResena(resenas);
}

function crearTarjetaResena(resena) {
  const calificacion = Number(resena.calificacion);
  const esDuena = usuarioEnSesion && Number(resena.id_usuario) === Number(usuarioEnSesion.id_usuario);
  const esAdmin = usuarioEnSesion && usuarioEnSesion.tipo_usuario === 'administrador';

  const tarjeta = document.createElement('div');
  tarjeta.className = 'tarjeta-resena p-3';

  const encabezado = document.createElement('div');
  encabezado.className = 'd-flex justify-content-between align-items-center mb-1';

  const autor = document.createElement('span');
  autor.className = 'fw-semibold';
  autor.textContent = `${resena.nombre} ${resena.apellido}`;

  const estrellas = document.createElement('span');
  estrellas.className = 'texto-estrellas';
  estrellas.textContent = '★'.repeat(calificacion) + '☆'.repeat(5 - calificacion);
  estrellas.setAttribute('aria-label', `${calificacion} de 5 estrellas`);

  encabezado.append(autor, estrellas);

  const fecha = document.createElement('p');
  fecha.className = 'small text-muted mb-2';
  fecha.textContent = new Date(resena.fecha).toLocaleDateString('es-GT');

  const comentario = document.createElement('p');
  comentario.className = 'mb-0';
  comentario.textContent = resena.comentario || '';

  tarjeta.append(encabezado, fecha, comentario);

  // El dueño elimina la suya; un administrador puede eliminar cualquiera
  // (moderación). El servidor lo valida igual: esto es solo lo visible.
  if (esDuena || esAdmin) {
    const btnEliminar = document.createElement('button');
    btnEliminar.type = 'button';
    btnEliminar.className = 'btn btn-sm btn-outline-danger mt-2';
    btnEliminar.textContent = esDuena ? 'Eliminar mi reseña' : 'Eliminar (moderación)';
    btnEliminar.addEventListener('click', () => eliminarResena(resena.id_resena));
    tarjeta.appendChild(btnEliminar);
  }

  return tarjeta;
}

// Muestra el formulario solo con sesión activa. Si el usuario ya reseñó este
// libro, el mismo formulario pasa a "editar" (precargado) en vez de crear otra.
function prepararFormularioResena(resenas) {
  const contenedor = document.getElementById('formularioResenaContenedor');
  const aviso = document.getElementById('avisoLoginResena');

  if (!usuarioEnSesion) {
    contenedor.classList.add('d-none');
    aviso.classList.remove('d-none');
    return;
  }

  aviso.classList.add('d-none');
  contenedor.classList.remove('d-none');

  miResenaActual = resenas.find((r) => Number(r.id_usuario) === Number(usuarioEnSesion.id_usuario)) || null;

  const formulario = document.getElementById('formResena');
  const titulo = document.getElementById('tituloFormResena');
  const boton = document.getElementById('btnPublicarResena');

  if (miResenaActual) {
    titulo.textContent = 'Editar tu reseña';
    boton.textContent = 'Guardar cambios';
    formulario.calificacion.value = miResenaActual.calificacion;
    formulario.comentario.value = miResenaActual.comentario || '';
  } else {
    titulo.textContent = 'Deja tu reseña';
    boton.textContent = 'Publicar reseña';
    formulario.reset();
  }
}

// api/resenas.php → app/controladores/ResenaController.php (POST crea, PUT edita)
async function manejarSubmitResena(evento) {
  evento.preventDefault();
  const formulario = evento.target;

  const calificacion = Number(formulario.calificacion.value);
  const comentario = formulario.comentario.value.trim() || null;

  const resultado = miResenaActual
    ? await llamarApi(`resenas.php?id=${miResenaActual.id_resena}`, 'PUT', { calificacion, comentario })
    : await llamarApi('resenas.php', 'POST', { id_producto: Number(idProductoActual), calificacion, comentario });

  const zonaMensaje = document.getElementById('mensajeResena');
  zonaMensaje.innerHTML = '';
  const alerta = document.createElement('div');
  alerta.className = `alert ${resultado.exito ? 'alert-success' : 'alert-danger'} mb-0`;
  alerta.textContent = resultado.mensaje;
  zonaMensaje.appendChild(alerta);

  if (resultado.exito) {
    await cargarResenas();
  }
}

// api/resenas.php?id=# → app/controladores/ResenaController.php
async function eliminarResena(idResena) {
  if (!confirm('¿Eliminar esta reseña? Esto no se puede deshacer.')) return;

  const resultado = await llamarApi(`resenas.php?id=${idResena}`, 'DELETE');

  const zonaMensaje = document.getElementById('mensajeResena');
  zonaMensaje.innerHTML = '';
  const alerta = document.createElement('div');
  alerta.className = `alert ${resultado.exito ? 'alert-success' : 'alert-danger'} mb-0`;
  alerta.textContent = resultado.mensaje;
  zonaMensaje.appendChild(alerta);

  if (resultado.exito) {
    await cargarResenas();
  }
}
