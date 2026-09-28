// assets/js/wishlist.js
document.addEventListener('DOMContentLoaded', () => {
  cargarWishlist();
});

// Carga la wishlist del usuario.
async function cargarWishlist() {
  const zonaEstado = document.getElementById('zonaEstadoWishlist');
  const lista = document.getElementById('listaWishlist');

  zonaEstado.classList.remove('d-none');
  zonaEstado.textContent = 'Cargando tu lista de deseos...';

  const resultado = await llamarApi('wishlist.php', 'GET');

  if (!resultado.exito) {
    zonaEstado.textContent = resultado.mensaje || 'No se pudo cargar tu lista de deseos.';
    return;
  }

  if (resultado.datos.length === 0) {
    zonaEstado.textContent = 'Tu lista de deseos está vacía. Agrega libros desde el detalle de cada uno.';
    return;
  }

  zonaEstado.classList.add('d-none');
  const rutaBase = API_URL.replace(/api\/$/, '');
  resultado.datos.forEach((item) => lista.appendChild(crearFilaWishlist(item, rutaBase)));
}

function crearFilaWishlist(item, rutaBase) {
  const fila = document.createElement('div');
  fila.className = 'row g-3 align-items-center tarjeta-wishlist p-3';
  fila.dataset.idProducto = item.id_producto;

  const portada = item.imagen
    ? `${rutaBase}assets/img/uploads/${item.imagen}`
    : `${rutaBase}assets/img/portada-defecto.svg`;

  const colImagen = document.createElement('div');
  colImagen.className = 'col-3 col-md-2';
  const img = document.createElement('img');
  img.src = portada;
  img.className = 'img-fluid rounded';
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

  const colPrecio = document.createElement('div');
  colPrecio.className = 'col-4 col-md-2 fw-semibold';
  colPrecio.textContent = `Q${Number(item.precio).toFixed(2)}`;

  const colAgregar = document.createElement('div');
  colAgregar.className = 'col-4 col-md-2';
  const btnAgregar = document.createElement('button');
  btnAgregar.type = 'button';
  btnAgregar.className = 'btn btn-sm btn-primary w-100';
  btnAgregar.textContent = 'Agregar al carrito';
  btnAgregar.addEventListener('click', () => {
    agregarAlCarrito({
      id_producto: item.id_producto,
      nombre: item.nombre,
      autor: item.autor,
      precio: item.precio,
      imagen: item.imagen
    });
    btnAgregar.textContent = 'Agregado ✓';
    setTimeout(() => (btnAgregar.textContent = 'Agregar al carrito'), 1500);
  });
  colAgregar.appendChild(btnAgregar);

  const colQuitar = document.createElement('div');
  colQuitar.className = 'col-4 col-md-1 text-end';
  const btnQuitar = document.createElement('button');
  btnQuitar.type = 'button';
  btnQuitar.className = 'btn btn-sm btn-outline-danger';
  btnQuitar.setAttribute('aria-label', 'Quitar de la lista de deseos');
  btnQuitar.textContent = '♥';
  btnQuitar.addEventListener('click', () => quitarDeWishlist(item.id_producto, fila));
  colQuitar.appendChild(btnQuitar);

  fila.append(colImagen, colTexto, colPrecio, colAgregar, colQuitar);
  return fila;
}

// Quita un libro de la wishlist.
async function quitarDeWishlist(idProducto, fila) {
  const resultado = await llamarApi(`wishlist.php?id_producto=${idProducto}`, 'DELETE');
  if (resultado.exito) {
    fila.remove();
  }
}