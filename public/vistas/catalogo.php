// public/vistas/catalogo.php
<?php
$tituloPagina = 'Catálogo';
$rutaBase = '../../';
$scriptsPagina = ['catalogo.js'];
require_once __DIR__ . '/../../includes/plantillas/header.php';
?>

<section class="mb-4">
  <h1 class="h3 mb-1">Catálogo de libros</h1>
  <p class="text-muted mb-0">Explora nuestra colección y filtra por lo que buscas.</p>
</section>

<!-- Barra de filtros: un solo <form>, se envía por JS (fetch), nunca recarga la página -->
<section class="tarjeta-filtros p-3 p-md-4 mb-4">
  <form id="formFiltros" class="row g-3 align-items-end" role="search">

    <div class="col-12 col-md-4">
      <label for="filtroQ" class="form-label">Buscar</label>
      <input type="search" class="form-control" id="filtroQ" name="q"
             placeholder="Título o autor" maxlength="100">
    </div>

    <div class="col-6 col-md-2">
      <label for="filtroCategoria" class="form-label">Categoría</label>
      <select class="form-select" id="filtroCategoria" name="id_categoria">
        <option value="">Todas</option>
        <!-- Las <option> de categoría se agregan por JS (catalogo.js) -->
      </select>
    </div>

    <div class="col-6 col-md-2">
      <label for="filtroOrden" class="form-label">Ordenar por</label>
      <select class="form-select" id="filtroOrden" name="orden">
        <option value="recientes">Más recientes</option>
        <option value="precio_asc">Precio: menor a mayor</option>
        <option value="precio_desc">Precio: mayor a menor</option>
        <option value="nombre_asc">Título A-Z</option>
      </select>
    </div>

    <div class="col-6 col-md-2">
      <label for="filtroPrecioMin" class="form-label">Precio mín.</label>
      <input type="number" class="form-control" id="filtroPrecioMin" name="precio_min"
             min="0" step="0.01" placeholder="Q0">
    </div>

    <div class="col-6 col-md-2">
      <label for="filtroPrecioMax" class="form-label">Precio máx.</label>
      <input type="number" class="form-control" id="filtroPrecioMax" name="precio_max"
             min="0" step="0.01" placeholder="Q999">
    </div>

    <div class="col-12 col-md-8">
      <div class="form-check">
        <input type="checkbox" class="form-check-input" id="filtroDisponible" name="disponible">
        <label class="form-check-label" for="filtroDisponible">Solo libros disponibles</label>
      </div>
    </div>

    <div class="col-12 col-md-4 text-md-end">
      <button type="submit" class="btn btn-primary w-100 w-md-auto">Aplicar filtros</button>
    </div>

  </form>
</section>

<!-- Mensaje de estado: cargando / vacío / error. Oculto mientras hay resultados -->
<div id="zonaEstadoCatalogo" class="text-center my-5 text-muted d-none" aria-live="polite"></div>

<section>
  <p id="contadorResultados" class="text-muted small mb-3"></p>
  <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4" id="listaCatalogo"></div>
</section>

<?php require_once __DIR__ . '/../../includes/plantillas/footer.php'; ?>