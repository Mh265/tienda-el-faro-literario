// assets/js/catalogo.js

document.addEventListener('DOMContentLoaded', () => {
  poblarSelectCategorias();
  precargarFiltrosDesdeURL();
  cargarCatalogo();

  const formFiltros = document.getElementById('formFiltros');
  formFiltros.addEventListener('submit', (evento) => {
    evento.preventDefault();
    cargarCatalogo();
  });

  document.getElementById('filtroCategoria').addEventListener('change', cargarCatalogo);
  document.getElementById('filtroOrden').addEventListener('change', cargarCatalogo);
  document.getElementById('filtroDisponible').addEventListener('change', cargarCatalogo);
});

// Carga las categorías para el filtro.
async function poblarSelectCategorias() {
  const select = document.getElementById('filtroCategoria');
  const resultado = await llamarApi('categorias.php', 'GET');

  if (!resultado.exito) {
    return;
  }

  resultado.datos.forEach((categoria) => {
    const opcion = document.createElement('option');
    opcion.value = categoria.id_categoria;
    opcion.textContent = categoria.nombre;
    select.appendChild(opcion);
  });
}

function precargarFiltrosDesdeURL() {
  const parametros = new URLSearchParams(window.location.search);
  const q = parametros.get('q');
  if (q) {
    document.getElementById('filtroQ').value = q;
  }
}

function leerFiltrosDelFormulario() {
  return {
    q: document.getElementById('filtroQ').value.trim(),
    id_categoria: document.getElementById('filtroCategoria').value,
    precio_min: document.getElementById('filtroPrecioMin').value,
    precio_max: document.getElementById('filtroPrecioMax').value,
    disponible: document.getElementById('filtroDisponible').checked ? '1' : '',
    orden: document.getElementById('filtroOrden').value
  };
}

function construirQueryString(filtros) {
  const parametros = new URLSearchParams();
  Object.entries(filtros).forEach(([clave, valor]) => {
    if (valor !== '' && valor !== null && valor !== undefined) {
      parametros.set(clave, valor);
    }
  });
  return parametros.toString();
}

// api/libros.php → app/controladores/LibroController.php
async function cargarCatalogo() {
  mostrarEstado('cargando', 'Cargando catálogo...');

  const filtros = leerFiltrosDelFormulario();
  const queryString = construirQueryString(filtros);
  const recurso = 'libros.php' + (queryString ? '?' + queryString : '');

  const resultado = await llamarApi(recurso, 'GET');

  if (!resultado.exito) {
    mostrarEstado('error', resultado.mensaje || 'No se pudo cargar el catálogo. Intenta de nuevo.');
    return;
  }

  const libros = resultado.datos || [];

  if (libros.length === 0) {
    mostrarEstado('vacio', 'No se encontraron libros con esos filtros.');
    return;
  }

  ocultarEstado();
  renderizarLibros(libros);
}

function mostrarEstado(tipo, mensaje) {
  const zonaEstado = document.getElementById('zonaEstadoCatalogo');
  const listaCatalogo = document.getElementById('listaCatalogo');
  const contador = document.getElementById('contadorResultados');

  zonaEstado.classList.remove('d-none', 'text-danger', 'text-muted');
  zonaEstado.classList.add(tipo === 'error' ? 'text-danger' : 'text-muted');
  zonaEstado.textContent = mensaje;

  listaCatalogo.innerHTML = '';
  contador.textContent = '';
}

function ocultarEstado() {
  document.getElementById('zonaEstadoCatalogo').classList.add('d-none');
}

function renderizarLibros(libros) {
  const listaCatalogo = document.getElementById('listaCatalogo');
  const contador = document.getElementById('contadorResultados');

  listaCatalogo.innerHTML = '';
  contador.textContent = libros.length === 1
    ? '1 libro encontrado'
    : `${libros.length} libros encontrados`;

  libros.forEach((libro) => {
    listaCatalogo.appendChild(crearTarjetaLibro(libro));
  });
}

function crearTarjetaLibro(libro) {
  const columna = document.createElement('div');
  columna.className = 'col';

  const sinStock = Number(libro.cantidad) === 0;

  const rutaBase = API_URL.replace(/api\/$/, '');
  const portada = libro.imagen
    ? `${rutaBase}assets/img/uploads/${libro.imagen}`
    : `${rutaBase}assets/img/portada-defecto.svg`;

  const tarjeta = document.createElement('div');
  tarjeta.className = 'card tarjeta-libro h-100';

  const img = document.createElement('img');
  img.src = portada;
  img.className = 'card-img-top';
  img.alt = `Portada de ${libro.nombre}`;

  const cuerpo = document.createElement('div');
  cuerpo.className = 'card-body d-flex flex-column';

  const badgeCategoria = document.createElement('span');
  badgeCategoria.className = 'badge badge-categoria mb-2 align-self-start';
  badgeCategoria.textContent = libro.nombre_categoria;

  const titulo = document.createElement('h3');
  titulo.className = 'h6 card-title';
  titulo.textContent = libro.nombre;

  const autor = document.createElement('p');
  autor.className = 'card-text small text-muted mb-2';
  autor.textContent = libro.autor;

  const precio = document.createElement('p');
  precio.className = 'precio-libro mb-2';
  precio.textContent = `Q${Number(libro.precio).toFixed(2)}`;

  cuerpo.append(badgeCategoria, titulo, autor, precio);

  if (sinStock) {
    const badgeStock = document.createElement('span');
    badgeStock.className = 'badge badge-sin-stock mb-2 align-self-start';
    badgeStock.textContent = 'Sin stock';
    cuerpo.appendChild(badgeStock);
  }

  const enlaceDetalle = document.createElement('a');
  enlaceDetalle.href = `detalle-libro.php?id=${libro.id_producto}`;
  enlaceDetalle.className = 'btn btn-outline-primary mt-auto btn-sm';
  enlaceDetalle.textContent = 'Ver detalle';
  cuerpo.appendChild(enlaceDetalle);

  tarjeta.append(img, cuerpo);
  columna.appendChild(tarjeta);
  return columna;
}