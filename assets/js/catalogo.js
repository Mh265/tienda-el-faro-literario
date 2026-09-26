// assets/js/catalogo.js

document.addEventListener('DOMContentLoaded', () => {
  poblarSelectCategorias();
  precargarFiltrosDesdeURL();
  cargarCatalogo();

  const formFiltros = document.getElementById('formFiltros');
  formFiltros.addEventListener('submit', (evento) => {
    evento.preventDefault(); // nunca recargamos la página completa
    cargarCatalogo();
  });

  // Categoría, orden y disponibilidad se recargan solos al cambiar (es
  // instantáneo, como prender un interruptor). El texto y los precios
  // esperan al botón "Aplicar filtros": si recargáramos en cada tecla que
  // el usuario escribe, saturaríamos de peticiones al servidor por nada.
  document.getElementById('filtroCategoria').addEventListener('change', cargarCatalogo);
  document.getElementById('filtroOrden').addEventListener('change', cargarCatalogo);
  document.getElementById('filtroDisponible').addEventListener('change', cargarCatalogo);
});

// TODO(mock): reemplazar por un fetch real a api/categorias.php cuando
// exista Feature/categoria-api. Mientras tanto, esta función devuelve las
// 12 categorías del seed de bd/tienda_libros.sql con sus id_categoria
// reales (1 al 12), para que el <select> funcione igual que cuando el
// endpoint exista. El día que Milton entregue el endpoint, solo hay que
// cambiar el cuerpo de esta función por un fetch — nadie más la toca.
function obtenerCategoriasMock() {
  return [
    { id_categoria: 1,  nombre: 'Novela' },
    { id_categoria: 2,  nombre: 'Ciencia Ficción' },
    { id_categoria: 3,  nombre: 'Infantil' },
    { id_categoria: 4,  nombre: 'Historia' },
    { id_categoria: 5,  nombre: 'Autoayuda' },
    { id_categoria: 6,  nombre: 'Fantasía' },
    { id_categoria: 7,  nombre: 'Terror y Misterio' },
    { id_categoria: 8,  nombre: 'Poesía' },
    { id_categoria: 9,  nombre: 'Biografía' },
    { id_categoria: 10, nombre: 'Negocios y Finanzas' },
    { id_categoria: 11, nombre: 'Clásicos' },
    { id_categoria: 12, nombre: 'Juvenil' }
  ];
}

function poblarSelectCategorias() {
  const select = document.getElementById('filtroCategoria');
  obtenerCategoriasMock().forEach((categoria) => {
    const opcion = document.createElement('option');
    opcion.value = categoria.id_categoria;
    opcion.textContent = categoria.nombre; // dato "de API" (aunque sea mock) -> textContent
    select.appendChild(opcion);
  });
}

// El buscador del navbar (includes/plantillas/header.php) manda un GET a
// catalogo.php?q=... . Aquí solo leemos ese parámetro para que el usuario
// vea su búsqueda ya escrita en el buscador propio del catálogo, como
// cuando llegás a una tienda ya diciendo qué buscás y el empleado no te
// pregunta de nuevo.
function precargarFiltrosDesdeURL() {
  const parametros = new URLSearchParams(window.location.search);
  const q = parametros.get('q');
  if (q) {
    document.getElementById('filtroQ').value = q;
  }
}

// Lee el estado actual del formulario.
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

// Arma la query string SOLO con los filtros que el usuario llenó, igual
// que LibroController arma el WHERE dinámico solo con lo que llega en
// $_GET: un filtro vacío no se manda, en vez de mandarlo vacío y que el
// backend tenga que decidir qué hacer con eso.
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
    // Cubre tanto un error normal de la API (exito:false en JSON) como el
    // caso "backend caído" que ya maneja llamarApi() con su mensaje
    // genérico cuando response.json() falla.
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

// Arma una tarjeta Bootstrap por libro. Todo lo que viene de la API se
// inserta con textContent (nunca innerHTML), para no abrir la puerta a
// XSS si algún día un título o descripción trae caracteres raros.
function crearTarjetaLibro(libro) {
  const columna = document.createElement('div');
  columna.className = 'col';

  const sinStock = Number(libro.cantidad) === 0;

  // API_URL ya viene declarada en header.php como "<rutaBase>api/";
  // le quitamos "api/" del final para reconstruir la ruta a la raíz del
  // proyecto y así armar la ruta a assets/img/ sin repetir $rutaBase acá.
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

  // Indicador de stock bajo/agotado en Terracota, como pide la identidad
  // visual del proyecto para "alerta / stock bajo".
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