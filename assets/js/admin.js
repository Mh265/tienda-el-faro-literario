// assets/js/admin.js
/**
 * Lógica del panel de administración: dashboard, libros, categorías,
 * pedidos y usuarios. Este archivo se carga en las 5 vistas de
 * public/vistas/admin/; cada sección se activa sola si encuentra sus
 * elementos en la página actual — el mismo "guardia de puerta" que usa
 * carrito.js: revisa si le toca actuar antes de hacer nada.
 */
document.addEventListener('DOMContentLoaded', () => {
  if (document.getElementById('statLibros')) inicializarDashboard();
  if (document.getElementById('tablaLibrosAdmin')) inicializarLibrosAdmin();
  if (document.getElementById('tablaCategoriasAdmin')) inicializarCategoriasAdmin();
  if (document.getElementById('tablaPedidosAdmin')) inicializarPedidosAdmin();
  if (document.getElementById('tablaUsuariosAdmin')) inicializarUsuariosAdmin();
});

const CLASES_ESTADO_PEDIDO = {
  pendiente: 'text-bg-secondary',
  pagado: 'text-bg-info',
  enviado: 'text-bg-warning',
  entregado: 'text-bg-success',
  cancelado: 'text-bg-danger'
};

// Vacía la zona de mensajes de un formulario modal (el contenedor, no texto
// del usuario) para que un mensaje anterior no aparezca al reabrirlo.
function limpiarMensajeModal(idZona) {
  document.getElementById(idZona).innerHTML = '';
}

// ==========================================================
// DASHBOARD (admin/index.php)
// ==========================================================
async function inicializarDashboard() {
  const zonaEstado = document.getElementById('zonaEstadoDashboard');

  // api/libros.php?accion=admin-listado, api/pedidos.php?accion=admin-listado,
  // api/usuarios.php?accion=admin-listado → sus respectivos Controladores
  const [resLibros, resPedidos, resUsuarios] = await Promise.all([
    llamarApi('libros.php?accion=admin-listado', 'GET'),
    llamarApi('pedidos.php?accion=admin-listado', 'GET'),
    llamarApi('usuarios.php?accion=admin-listado', 'GET')
  ]);

  if (!resLibros.exito || !resPedidos.exito || !resUsuarios.exito) {
    zonaEstado.classList.remove('d-none');
    zonaEstado.textContent = 'No se pudo cargar el resumen. Intenta de nuevo.';
    return;
  }

  // "Libros en catálogo" = solo los activos (el listado admin trae también los dados de baja).
  document.getElementById('statLibros').textContent = resLibros.datos.filter((libro) => libro.estado === 'activo').length;
  document.getElementById('statUsuarios').textContent = resUsuarios.datos.length;

  // No hay endpoint de estadísticas: se calcula aquí con lo que ya
  // devuelve admin-listado, sin pedirle nada nuevo a Milton.
  const ingresos = resPedidos.datos
    .filter((pedido) => pedido.estado !== 'cancelado')
    .reduce((total, pedido) => total + Number(pedido.total), 0);
  document.getElementById('statIngresos').textContent = `Q${ingresos.toFixed(2)}`;

  const tabla = document.getElementById('tablaPedidosRecientes');
  resPedidos.datos.slice(0, 5).forEach((pedido) => {
    const fila = document.createElement('tr');

    const celdaId = document.createElement('td');
    celdaId.textContent = `#${pedido.id_pedido}`;

    const celdaFecha = document.createElement('td');
    celdaFecha.textContent = new Date(pedido.fecha).toLocaleDateString('es-GT');

    const celdaTotal = document.createElement('td');
    celdaTotal.textContent = `Q${Number(pedido.total).toFixed(2)}`;

    const celdaEstado = document.createElement('td');
    const badge = document.createElement('span');
    badge.className = `badge ${CLASES_ESTADO_PEDIDO[pedido.estado] || 'text-bg-secondary'} text-capitalize`;
    badge.textContent = pedido.estado;
    celdaEstado.appendChild(badge);

    fila.append(celdaId, celdaFecha, celdaTotal, celdaEstado);
    tabla.appendChild(fila);
  });
}

// ==========================================================
// LIBROS (admin/libros.php)
// ==========================================================
function inicializarLibrosAdmin() {
  cargarCategoriasParaSelect();
  cargarLibrosAdmin();

  document.getElementById('btnNuevoLibro').addEventListener('click', () => {
    document.getElementById('formLibro').reset();
    document.getElementById('idLibroEditar').value = '';
    document.getElementById('tituloModalLibro').textContent = 'Nuevo libro';
    limpiarMensajeModal('mensajeFormLibro');
  });

  document.getElementById('formLibro').addEventListener('submit', manejarSubmitLibro);
  document.getElementById('filtroBusquedaLibrosAdmin').addEventListener('input', filtrarTablaLibros);
}

// api/categorias.php → app/controladores/CategoriaController.php
async function cargarCategoriasParaSelect() {
  const resultado = await llamarApi('categorias.php', 'GET');
  const select = document.getElementById('categoriaLibro');
  if (!resultado.exito) return;

  select.innerHTML = '';
  resultado.datos.forEach((categoria) => {
    const opcion = document.createElement('option');
    opcion.value = categoria.id_categoria;
    opcion.textContent = categoria.nombre;
    select.appendChild(opcion);
  });
}

// api/libros.php?accion=admin-listado → app/controladores/LibroController.php
async function cargarLibrosAdmin() {
  const zonaEstado = document.getElementById('zonaEstadoLibrosAdmin');
  const tabla = document.getElementById('tablaLibrosAdmin');

  zonaEstado.classList.remove('d-none');
  zonaEstado.textContent = 'Cargando libros...';

  const resultado = await llamarApi('libros.php?accion=admin-listado', 'GET');

  if (!resultado.exito) {
    zonaEstado.textContent = resultado.mensaje || 'No se pudo cargar el listado.';
    return;
  }

  zonaEstado.classList.add('d-none');
  tabla.innerHTML = '';
  const rutaBase = API_URL.replace(/api\/$/, '');
  resultado.datos.forEach((libro) => tabla.appendChild(crearFilaLibroAdmin(libro, rutaBase)));
}

function crearFilaLibroAdmin(libro, rutaBase) {
  const fila = document.createElement('tr');
  fila.dataset.nombre = libro.nombre.toLowerCase();
  fila.dataset.autor = libro.autor.toLowerCase();

  const celdaPortada = document.createElement('td');
  const img = document.createElement('img');
  img.src = libro.imagen ? `${rutaBase}assets/img/uploads/${libro.imagen}` : `${rutaBase}assets/img/portada-defecto.svg`;
  img.alt = `Portada de ${libro.nombre}`;
  img.className = 'miniatura-admin';
  celdaPortada.appendChild(img);

  const celdaTitulo = document.createElement('td');
  celdaTitulo.textContent = libro.nombre;

  const celdaAutor = document.createElement('td');
  celdaAutor.textContent = libro.autor;

  const celdaCategoria = document.createElement('td');
  celdaCategoria.textContent = libro.nombre_categoria;

  const celdaPrecio = document.createElement('td');
  celdaPrecio.textContent = `Q${Number(libro.precio).toFixed(2)}`;

  const celdaStock = document.createElement('td');
  celdaStock.textContent = libro.cantidad;

  const celdaEstado = document.createElement('td');
  const badgeEstado = document.createElement('span');
  badgeEstado.className = `badge ${libro.estado === 'activo' ? 'text-bg-success' : 'text-bg-secondary'}`;
  badgeEstado.textContent = libro.estado;
  celdaEstado.appendChild(badgeEstado);

  const celdaAcciones = document.createElement('td');
  celdaAcciones.className = 'text-end';

  const btnEditar = document.createElement('button');
  btnEditar.type = 'button';
  btnEditar.className = 'btn btn-sm btn-outline-primary me-1';
  btnEditar.textContent = 'Editar';
  btnEditar.addEventListener('click', () => abrirModalEdicionLibro(libro));

  const btnEstado = document.createElement('button');
  btnEstado.type = 'button';
  btnEstado.className = `btn btn-sm ${libro.estado === 'activo' ? 'btn-outline-danger' : 'btn-outline-success'}`;
  btnEstado.textContent = libro.estado === 'activo' ? 'Dar de baja' : 'Reactivar';
  btnEstado.addEventListener('click', () => alternarEstadoLibro(libro));

  celdaAcciones.append(btnEditar, btnEstado);
  fila.append(celdaPortada, celdaTitulo, celdaAutor, celdaCategoria, celdaPrecio, celdaStock, celdaEstado, celdaAcciones);
  return fila;
}

function abrirModalEdicionLibro(libro) {
  limpiarMensajeModal('mensajeFormLibro');

  document.getElementById('tituloModalLibro').textContent = 'Editar libro';
  document.getElementById('idLibroEditar').value = libro.id_producto;
  document.getElementById('categoriaLibro').value = libro.id_categoria;
  document.getElementById('nombreLibro').value = libro.nombre;
  document.getElementById('autorLibro').value = libro.autor;
  document.getElementById('editorialLibro').value = libro.editorial || '';
  document.getElementById('precioLibro').value = libro.precio;
  document.getElementById('cantidadLibro').value = libro.cantidad;
  document.getElementById('descripcionCortaLibro').value = libro.descripcion_corta || '';
  document.getElementById('descripcionLargaLibro').value = libro.descripcion_larga || '';
  document.getElementById('fechaPublicacionLibro').value = libro.fecha_publicacion || '';
  document.getElementById('imagenLibro').value = '';

  new bootstrap.Modal(document.getElementById('modalLibro')).show();
}

// api/libros.php → app/controladores/LibroController.php
async function manejarSubmitLibro(evento) {
  evento.preventDefault();
  const formulario = evento.target;
  const idLibro = document.getElementById('idLibroEditar').value;
  const zonaMensaje = document.getElementById('mensajeFormLibro');
  let resultado;

  if (idLibro) {
    // PUT (actualizar) no acepta portada nueva, así que va como JSON normal.
    // Libro::actualizar() no toca la columna `imagen`: la portada se conserva.
    const datos = {
      id_categoria: formulario.id_categoria.value,
      nombre: formulario.nombre.value.trim(),
      autor: formulario.autor.value.trim(),
      editorial: formulario.editorial.value.trim(),
      descripcion_corta: formulario.descripcion_corta.value.trim(),
      descripcion_larga: formulario.descripcion_larga.value.trim(),
      precio: formulario.precio.value,
      cantidad: formulario.cantidad.value,
      fecha_publicacion: formulario.fecha_publicacion.value || null
    };
    resultado = await llamarApi(`libros.php?id=${idLibro}`, 'PUT', datos);
  } else {
    // POST (crear) va como FormData por la portada (docs/API.md): fetch
    // directo, sin fijar Content-Type, el navegador arma el boundary solo.
    const formData = new FormData(formulario);
    try {
      const respuesta = await fetch(API_URL + 'libros.php', {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
      });
      const cuerpo = await respuesta.json();
      resultado = { exito: cuerpo.exito, mensaje: cuerpo.mensaje, datos: cuerpo.datos };
    } catch (error) {
      resultado = { exito: false, mensaje: 'No se pudo completar la operación, intenta de nuevo.' };
    }
  }

  zonaMensaje.innerHTML = '';
  const alerta = document.createElement('div');
  alerta.className = `alert ${resultado.exito ? 'alert-success' : 'alert-danger'} mb-0`;
  alerta.textContent = resultado.mensaje;
  zonaMensaje.appendChild(alerta);

  if (resultado.exito) {
    bootstrap.Modal.getInstance(document.getElementById('modalLibro')).hide();
    cargarLibrosAdmin();
  }
}

// api/libros.php?id=# → app/controladores/LibroController.php (DELETE=baja, PUT accion=reactivar)
async function alternarEstadoLibro(libro) {
  const resultado = libro.estado === 'activo'
    ? await llamarApi(`libros.php?id=${libro.id_producto}`, 'DELETE')
    : await llamarApi(`libros.php?id=${libro.id_producto}&accion=reactivar`, 'PUT');

  if (resultado.exito) {
    cargarLibrosAdmin();
  } else {
    alert(resultado.mensaje);
  }
}

function filtrarTablaLibros(evento) {
  const termino = evento.target.value.trim().toLowerCase();
  document.querySelectorAll('#tablaLibrosAdmin tr').forEach((fila) => {
    const coincide = fila.dataset.nombre.includes(termino) || fila.dataset.autor.includes(termino);
    fila.classList.toggle('d-none', !coincide);
  });
}

// ==========================================================
// CATEGORÍAS (admin/categorias.php)
// ==========================================================
function inicializarCategoriasAdmin() {
  cargarCategoriasAdmin();

  document.getElementById('btnNuevaCategoria').addEventListener('click', () => {
    document.getElementById('formCategoria').reset();
    document.getElementById('idCategoriaEditar').value = '';
    document.getElementById('tituloModalCategoria').textContent = 'Nueva categoría';
    limpiarMensajeModal('mensajeFormCategoria');
  });

  document.getElementById('formCategoria').addEventListener('submit', manejarSubmitCategoria);
}

// api/categorias.php → app/controladores/CategoriaController.php
async function cargarCategoriasAdmin() {
  const zonaEstado = document.getElementById('zonaEstadoCategoriasAdmin');
  const tabla = document.getElementById('tablaCategoriasAdmin');

  zonaEstado.classList.remove('d-none');
  zonaEstado.textContent = 'Cargando categorías...';

  const resultado = await llamarApi('categorias.php', 'GET');

  if (!resultado.exito) {
    zonaEstado.textContent = resultado.mensaje;
    return;
  }

  zonaEstado.classList.add('d-none');
  tabla.innerHTML = '';
  resultado.datos.forEach((categoria) => tabla.appendChild(crearFilaCategoriaAdmin(categoria)));
}

function crearFilaCategoriaAdmin(categoria) {
  const fila = document.createElement('tr');

  const celdaNombre = document.createElement('td');
  celdaNombre.textContent = categoria.nombre;

  const celdaDescripcion = document.createElement('td');
  celdaDescripcion.textContent = categoria.descripcion || '—';

  const celdaAcciones = document.createElement('td');
  celdaAcciones.className = 'text-end';

  const btnEditar = document.createElement('button');
  btnEditar.type = 'button';
  btnEditar.className = 'btn btn-sm btn-outline-primary me-1';
  btnEditar.textContent = 'Editar';
  btnEditar.addEventListener('click', () => {
    limpiarMensajeModal('mensajeFormCategoria');
    document.getElementById('tituloModalCategoria').textContent = 'Editar categoría';
    document.getElementById('idCategoriaEditar').value = categoria.id_categoria;
    document.getElementById('nombreCategoria').value = categoria.nombre;
    document.getElementById('descripcionCategoria').value = categoria.descripcion || '';
    new bootstrap.Modal(document.getElementById('modalCategoria')).show();
  });

  const btnEliminar = document.createElement('button');
  btnEliminar.type = 'button';
  btnEliminar.className = 'btn btn-sm btn-outline-danger';
  btnEliminar.textContent = 'Eliminar';
  btnEliminar.addEventListener('click', () => eliminarCategoriaAdmin(categoria.id_categoria));

  celdaAcciones.append(btnEditar, btnEliminar);
  fila.append(celdaNombre, celdaDescripcion, celdaAcciones);
  return fila;
}

// api/categorias.php → app/controladores/CategoriaController.php
async function manejarSubmitCategoria(evento) {
  evento.preventDefault();
  const formulario = evento.target;
  const idCategoria = document.getElementById('idCategoriaEditar').value;

  const datos = {
    nombre: formulario.nombre.value.trim(),
    descripcion: formulario.descripcion.value.trim()
  };

  const resultado = idCategoria
    ? await llamarApi(`categorias.php?id=${idCategoria}`, 'PUT', datos)
    : await llamarApi('categorias.php', 'POST', datos);

  const zonaMensaje = document.getElementById('mensajeFormCategoria');
  zonaMensaje.innerHTML = '';
  const alerta = document.createElement('div');
  alerta.className = `alert ${resultado.exito ? 'alert-success' : 'alert-danger'} mb-0`;
  alerta.textContent = resultado.mensaje;
  zonaMensaje.appendChild(alerta);

  if (resultado.exito) {
    bootstrap.Modal.getInstance(document.getElementById('modalCategoria')).hide();
    cargarCategoriasAdmin();
  }
}

// api/categorias.php?id=# → app/controladores/CategoriaController.php
async function eliminarCategoriaAdmin(idCategoria) {
  if (!confirm('¿Eliminar esta categoría? Esto no se puede deshacer.')) return;

  const resultado = await llamarApi(`categorias.php?id=${idCategoria}`, 'DELETE');
  if (resultado.exito) {
    cargarCategoriasAdmin();
  } else {
    alert(resultado.mensaje); // ej. 409: hay libros asociados
  }
}

// ==========================================================
// PEDIDOS (admin/pedidos.php)
// ==========================================================
function inicializarPedidosAdmin() {
  cargarPedidosAdmin();
}

// api/pedidos.php?accion=admin-listado → app/controladores/PedidoController.php
// api/usuarios.php?accion=admin-listado → app/controladores/UsuarioController.php
async function cargarPedidosAdmin() {
  const zonaEstado = document.getElementById('zonaEstadoPedidosAdmin');
  const tabla = document.getElementById('tablaPedidosAdmin');

  zonaEstado.classList.remove('d-none');
  zonaEstado.textContent = 'Cargando pedidos...';

  // Pedido::obtenerTodos() no hace JOIN a usuarios (solo trae id_usuario), así
  // que los nombres se resuelven aquí con el listado de usuarios, sin pedirle
  // ningún cambio al backend.
  const [resultado, resUsuarios] = await Promise.all([
    llamarApi('pedidos.php?accion=admin-listado', 'GET'),
    llamarApi('usuarios.php?accion=admin-listado', 'GET')
  ]);

  if (!resultado.exito) {
    zonaEstado.textContent = resultado.mensaje;
    return;
  }

  const nombresPorId = {};
  if (resUsuarios.exito) {
    resUsuarios.datos.forEach((usuario) => {
      nombresPorId[usuario.id_usuario] = `${usuario.nombre} ${usuario.apellido}`;
    });
  }

  zonaEstado.classList.add('d-none');
  tabla.innerHTML = '';
  resultado.datos.forEach((pedido) => tabla.appendChild(crearFilaPedidoAdmin(pedido, nombresPorId)));
}

function crearFilaPedidoAdmin(pedido, nombresPorId) {
  const fila = document.createElement('tr');

  const celdaId = document.createElement('td');
  celdaId.textContent = `#${pedido.id_pedido}`;

  // Si el listado de usuarios no cargó, se muestra el id como respaldo.
  const celdaUsuario = document.createElement('td');
  celdaUsuario.textContent = nombresPorId[pedido.id_usuario] || `Usuario #${pedido.id_usuario}`;

  const celdaFecha = document.createElement('td');
  celdaFecha.textContent = new Date(pedido.fecha).toLocaleDateString('es-GT');

  const celdaTotal = document.createElement('td');
  celdaTotal.textContent = `Q${Number(pedido.total).toFixed(2)}`;

  const celdaEstado = document.createElement('td');
  const select = document.createElement('select');
  select.className = 'form-select form-select-sm';
  select.setAttribute('aria-label', `Estado del pedido ${pedido.id_pedido}`);
  Object.keys(CLASES_ESTADO_PEDIDO).forEach((estado) => {
    const opcion = document.createElement('option');
    opcion.value = estado;
    opcion.textContent = estado;
    opcion.selected = estado === pedido.estado;
    select.appendChild(opcion);
  });
  select.addEventListener('change', () => cambiarEstadoPedidoAdmin(pedido.id_pedido, select.value));
  celdaEstado.appendChild(select);

  const celdaAcciones = document.createElement('td');
  celdaAcciones.className = 'text-end';
  const btnVer = document.createElement('button');
  btnVer.type = 'button';
  btnVer.className = 'btn btn-sm btn-outline-primary';
  btnVer.textContent = 'Ver líneas';
  btnVer.addEventListener('click', () => verLineasPedidoAdmin(pedido.id_pedido, fila));
  celdaAcciones.appendChild(btnVer);

  fila.append(celdaId, celdaUsuario, celdaFecha, celdaTotal, celdaEstado, celdaAcciones);
  return fila;
}

// api/pedidos.php?id=# → app/controladores/PedidoController.php
async function cambiarEstadoPedidoAdmin(idPedido, estadoNuevo) {
  const resultado = await llamarApi(`pedidos.php?id=${idPedido}`, 'PUT', { estado: estadoNuevo });
  if (!resultado.exito) {
    alert(resultado.mensaje);
    cargarPedidosAdmin(); // revierte el select a su valor real
  }
}

// api/pedidos.php?id=# → app/controladores/PedidoController.php
async function verLineasPedidoAdmin(idPedido, filaPedido) {
  const filaExistente = filaPedido.nextElementSibling;
  if (filaExistente && filaExistente.classList.contains('fila-detalle-pedido')) {
    filaExistente.remove();
    return;
  }

  const resultado = await llamarApi(`pedidos.php?id=${idPedido}`, 'GET');
  if (!resultado.exito) return;

  const filaDetalle = document.createElement('tr');
  filaDetalle.className = 'fila-detalle-pedido';
  const celda = document.createElement('td');
  celda.colSpan = 6;

  resultado.datos.lineas.forEach((linea) => {
    const p = document.createElement('p');
    p.className = 'small mb-1';
    p.textContent = `${linea.nombre} × ${linea.cantidad} — Q${(linea.precio * linea.cantidad).toFixed(2)}`;
    celda.appendChild(p);
  });

  filaDetalle.appendChild(celda);
  filaPedido.after(filaDetalle);
}

// ==========================================================
// USUARIOS (admin/usuarios.php)
// ==========================================================
function inicializarUsuariosAdmin() {
  cargarUsuariosAdmin();
  document.getElementById('formUsuario').addEventListener('submit', manejarSubmitUsuarioAdmin);
}

// api/usuarios.php?accion=admin-listado → app/controladores/UsuarioController.php
async function cargarUsuariosAdmin() {
  const zonaEstado = document.getElementById('zonaEstadoUsuariosAdmin');
  const tabla = document.getElementById('tablaUsuariosAdmin');

  zonaEstado.classList.remove('d-none');
  zonaEstado.textContent = 'Cargando usuarios...';

  const resultado = await llamarApi('usuarios.php?accion=admin-listado', 'GET');

  if (!resultado.exito) {
    zonaEstado.textContent = resultado.mensaje;
    return;
  }

  zonaEstado.classList.add('d-none');
  tabla.innerHTML = '';
  resultado.datos.forEach((usuario) => tabla.appendChild(crearFilaUsuarioAdmin(usuario)));
}

function crearFilaUsuarioAdmin(usuario) {
  const fila = document.createElement('tr');

  const celdaNombre = document.createElement('td');
  celdaNombre.textContent = `${usuario.nombre} ${usuario.apellido}`;

  const celdaCorreo = document.createElement('td');
  celdaCorreo.textContent = usuario.correo;

  const celdaTelefono = document.createElement('td');
  celdaTelefono.textContent = usuario.telefono || '—';

  const celdaTipo = document.createElement('td');
  const badgeTipo = document.createElement('span');
  badgeTipo.className = `badge ${usuario.tipo_usuario === 'administrador' ? 'text-bg-primary' : 'text-bg-secondary'}`;
  badgeTipo.textContent = usuario.tipo_usuario;
  celdaTipo.appendChild(badgeTipo);

  const celdaRegistro = document.createElement('td');
  celdaRegistro.textContent = new Date(usuario.fecha_registro).toLocaleDateString('es-GT');

  const celdaAcciones = document.createElement('td');
  celdaAcciones.className = 'text-end';

  const btnEditar = document.createElement('button');
  btnEditar.type = 'button';
  btnEditar.className = 'btn btn-sm btn-outline-primary me-1';
  btnEditar.textContent = 'Editar';
  btnEditar.addEventListener('click', () => {
    limpiarMensajeModal('mensajeFormUsuario');
    document.getElementById('idUsuarioEditar').value = usuario.id_usuario;
    document.getElementById('nombreUsuarioAdmin').value = usuario.nombre;
    document.getElementById('apellidoUsuarioAdmin').value = usuario.apellido;
    document.getElementById('telefonoUsuarioAdmin').value = usuario.telefono || '';
    document.getElementById('direccionUsuarioAdmin').value = usuario.direccion || '';
    new bootstrap.Modal(document.getElementById('modalUsuario')).show();
  });

  const btnEliminar = document.createElement('button');
  btnEliminar.type = 'button';
  btnEliminar.className = 'btn btn-sm btn-outline-danger';
  btnEliminar.textContent = 'Eliminar';
  btnEliminar.addEventListener('click', () => eliminarUsuarioAdmin(usuario.id_usuario));

  celdaAcciones.append(btnEditar, btnEliminar);
  fila.append(celdaNombre, celdaCorreo, celdaTelefono, celdaTipo, celdaRegistro, celdaAcciones);
  return fila;
}

// api/usuarios.php?id=# → app/controladores/UsuarioController.php
async function manejarSubmitUsuarioAdmin(evento) {
  evento.preventDefault();
  const formulario = evento.target;
  const idUsuario = document.getElementById('idUsuarioEditar').value;

  const datos = {
    nombre: formulario.nombre.value.trim(),
    apellido: formulario.apellido.value.trim(),
    telefono: formulario.telefono.value.trim() || null,
    direccion: formulario.direccion.value.trim() || null
  };

  const resultado = await llamarApi(`usuarios.php?id=${idUsuario}`, 'PUT', datos);

  const zonaMensaje = document.getElementById('mensajeFormUsuario');
  zonaMensaje.innerHTML = '';
  const alerta = document.createElement('div');
  alerta.className = `alert ${resultado.exito ? 'alert-success' : 'alert-danger'} mb-0`;
  alerta.textContent = resultado.mensaje;
  zonaMensaje.appendChild(alerta);

  if (resultado.exito) {
    bootstrap.Modal.getInstance(document.getElementById('modalUsuario')).hide();
    cargarUsuariosAdmin();
  }
}

// api/usuarios.php?id=# → app/controladores/UsuarioController.php
async function eliminarUsuarioAdmin(idUsuario) {
  if (!confirm('¿Eliminar este usuario? Esto no se puede deshacer.')) return;

  const resultado = await llamarApi(`usuarios.php?id=${idUsuario}`, 'DELETE');
  if (resultado.exito) {
    cargarUsuariosAdmin();
  } else {
    alert(resultado.mensaje); // ej. 409: tiene pedidos registrados
  }
}
