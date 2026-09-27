<?php
// public/vistas/detalle-libro.php
$tituloPagina = 'Detalle del libro';
$rutaBase = '../../';
$scriptsPagina = ['detalle-libro.js'];
require_once __DIR__ . '/../../includes/plantillas/header.php';
?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb" id="migaDePan">
    <li class="breadcrumb-item"><a href="catalogo.php">Catálogo</a></li>
  </ol>
</nav>

<div id="zonaEstadoDetalle" class="text-center my-5 text-muted d-none" aria-live="polite"></div>

<div id="contenidoDetalle" class="d-none">

  <div class="row g-4 mb-4">
    <!-- Portada -->
    <div class="col-md-4">
      <img id="portadaDetalle" src="" alt="" class="img-fluid rounded shadow-sm w-100">
    </div>

    <!-- Información principal -->
    <div class="col-md-8">
      <span id="badgeCategoriaDetalle" class="badge badge-categoria mb-2"></span>
      <h1 id="tituloDetalle" class="h3 mb-1"></h1>
      <p id="autorDetalle" class="text-muted mb-3"></p>

      <p class="precio-libro h4 mb-2" id="precioDetalle"></p>
      <p class="mb-3">
        <span id="badgeStockDetalle" class="badge"></span>
      </p>

      <div class="row g-2 align-items-end mb-3">
        <div class="col-auto">
          <label for="cantidadDetalle" class="form-label small mb-1">Cantidad</label>
          <input type="number" class="form-control" id="cantidadDetalle" min="1" value="1" style="width: 90px;">
        </div>
        <div class="col-auto">
          <button type="button" id="btnAgregarCarritoDetalle" class="btn btn-primary">Agregar al carrito</button>
        </div>
        <div class="col-auto">
          <button type="button" id="btnWishlistDetalle" class="btn btn-outline-primary">♥ Agregar a lista de deseos</button>
        </div>
      </div>
      <div id="mensajeAccionesDetalle"></div>
    </div>
  </div>

  <!-- Descripción larga -->
  <section class="tarjeta-descripcion p-3 p-md-4 mb-4">
    <h2 class="h5 mb-3">Sinopsis</h2>
    <p id="descripcionLargaDetalle" class="mb-0"></p>
  </section>

  <!-- Ficha técnica: reemplaza los 5 recuadros del wireframe con datos reales -->
  <section class="row row-cols-2 row-cols-md-5 g-3 mb-5 text-center">
    <div class="col"><div class="chip-ficha p-2"><p class="small text-muted mb-1">Autor</p><p id="fichaAutor" class="fw-semibold mb-0 small"></p></div></div>
    <div class="col"><div class="chip-ficha p-2"><p class="small text-muted mb-1">Editorial</p><p id="fichaEditorial" class="fw-semibold mb-0 small"></p></div></div>
    <div class="col"><div class="chip-ficha p-2"><p class="small text-muted mb-1">Categoría</p><p id="fichaCategoria" class="fw-semibold mb-0 small"></p></div></div>
    <div class="col"><div class="chip-ficha p-2"><p class="small text-muted mb-1">Publicación</p><p id="fichaFecha" class="fw-semibold mb-0 small"></p></div></div>
    <div class="col"><div class="chip-ficha p-2"><p class="small text-muted mb-1">Stock</p><p id="fichaStock" class="fw-semibold mb-0 small"></p></div></div>
  </section>

  <hr class="mb-4">

  <!-- Reseñas (RF14) -->
  <section id="seccionResenas" class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="h5 mb-0">Reseñas</h2>
      <span id="promedioResenas" class="text-muted small"></span>
    </div>

    <div id="zonaEstadoResenas" class="text-muted small mb-3 d-none"></div>
    <div id="listaResenas" class="d-flex flex-column gap-3 mb-4"></div>

    <!-- Formulario: se muestra solo si hay sesión activa (lo decide detalle-libro.js) -->
    <div id="formularioResenaContenedor" class="tarjeta-descripcion p-3 p-md-4 d-none">
      <h3 class="h6 mb-3">Deja tu reseña</h3>
      <form id="formResena" novalidate>
        <div class="mb-3">
          <label for="calificacionResena" class="form-label">Calificación</label>
          <select class="form-select" id="calificacionResena" name="calificacion" required style="max-width: 150px;">
            <option value="5">5 - Excelente</option>
            <option value="4">4 - Muy bueno</option>
            <option value="3">3 - Bueno</option>
            <option value="2">2 - Regular</option>
            <option value="1">1 - Malo</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="comentarioResena" class="form-label">Comentario</label>
          <textarea class="form-control" id="comentarioResena" name="comentario" rows="3" maxlength="1000"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Publicar reseña</button>
      </form>
      <div id="mensajeResena" class="mt-3"></div>
    </div>

    <p id="avisoLoginResena" class="text-muted small d-none">
      <a href="login.php">Inicia sesión</a> para dejar tu reseña.
    </p>
  </section>

</div>

<?php require_once __DIR__ . '/../../includes/plantillas/footer.php'; ?>