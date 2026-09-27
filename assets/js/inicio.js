// assets/js/inicio.js
document.addEventListener('DOMContentLoaded', () => {
  cargarDestacados();
  actualizarContadorCarrito(); // definido en carrito.js, cargado globalmente desde footer.php
});

// api/libros.php → app/controladores/LibroController.php
async function cargarDestacados() {
  const zonaEstado = document.getElementById('zonaEstadoDestacados');
  const lista = document.getElementById('listaDestacados');

  const resultado = await llamarApi('libros.php?orden=recientes&disponible=1', 'GET');

  if (!resultado.exito || resultado.datos.length === 0) {
    zonaEstado.classList.remove('d-none');
    zonaEstado.textContent = 'No hay libros destacados por ahora.';
    return;
  }

  // Máximo 4 en inicio; el catálogo completo con filtros vive en catalogo.php.
  const destacados = resultado.datos.slice(0, 4);
  const rutaBase = API_URL.replace(/api\/$/, '');

  destacados.forEach((libro) => {
    lista.appendChild(crearTarjetaDestacado(libro, rutaBase));
  });
}

function crearTarjetaDestacado(libro, rutaBase) {
  const columna = document.createElement('div');
  columna.className = 'col';

  const portada = libro.imagen
    ? `${rutaBase}assets/img/uploads/${libro.imagen}`
    : `${rutaBase}assets/img/portada-defecto.svg`;

  const tarjeta = document.createElement('div');
  tarjeta.className = 'card tarjeta-libro';

  const img = document.createElement('img');
  img.src = portada;
  img.className = 'card-img-top';
  img.alt = `Portada de ${libro.nombre}`;

  const cuerpo = document.createElement('div');
  cuerpo.className = 'card-body d-flex flex-column';

  const titulo = document.createElement('h3');
  titulo.className = 'h6 card-title';
  titulo.textContent = libro.nombre;

  const autor = document.createElement('p');
  autor.className = 'card-text small text-muted mb-2';
  autor.textContent = libro.autor;

  const precio = document.createElement('p');
  precio.className = 'precio-libro mb-3';
  precio.textContent = `Q${Number(libro.precio).toFixed(2)}`;

  const enlace = document.createElement('a');
  enlace.href = `vistas/detalle-libro.php?id=${libro.id_producto}`;
  enlace.className = 'btn btn-outline-primary mt-auto btn-sm';
  enlace.textContent = 'Ver detalle';

  cuerpo.append(titulo, autor, precio, enlace);
  tarjeta.append(img, cuerpo);
  columna.appendChild(tarjeta);
  return columna;
}