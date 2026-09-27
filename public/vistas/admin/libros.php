<?php
// public/vistas/admin/libros.php
$tituloPagina = 'Administrar libros';
$rutaBase = '../../../';

require_once __DIR__ . '/../../../includes/ayudantes/AyudanteSesion.php';
require_once __DIR__ . '/../../../includes/filtros/FiltroAutenticacion.php';
FiltroAutenticacion::protegerVistaAdministrador('../login.php');

$scriptsPagina = ['admin.js'];
require_once __DIR__ . '/../../../includes/plantillas/header.php';

$vistaActual = 'libros';
require_once __DIR__ . '/../../../includes/plantillas/admin-nav.php';
?>

<section class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
  <div>
    <h1 class="h3 mb-1">Administrar libros</h1>
    <p class="text-muted mb-0">Catálogo completo (activos e inactivos).</p>
  </div>
  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalLibro" id="btnNuevoLibro">
    + Nuevo libro
  </button>
</section>

<div class="mb-3">
  <input type="search" class="form-control" id="filtroBusquedaLibrosAdmin" placeholder="Buscar por título o autor...">
</div>

<div id="zonaEstadoLibrosAdmin" class="text-center my-4 text-muted d-none"></div>

<div class="table-responsive">
  <table class="table align-middle tabla-admin">
    <thead>
      <tr>
        <th>Portada</th><th>Título</th><th>Autor</th><th>Categoría</th>
        <th>Precio</th><th>Stock</th><th>Estado</th><th class="text-end">Acciones</th>
      </tr>
    </thead>
    <tbody id="tablaLibrosAdmin"></tbody>
  </table>
</div>

<div class="modal fade" id="modalLibro" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="formLibro" novalidate>
        <div class="modal-header">
          <h2 class="modal-title h5" id="tituloModalLibro">Nuevo libro</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="idLibroEditar" name="id_producto">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="categoriaLibro" class="form-label">Categoría</label>
              <select class="form-select" id="categoriaLibro" name="id_categoria" required></select>
            </div>
            <div class="col-md-6">
              <label for="fechaPublicacionLibro" class="form-label">Fecha de publicación</label>
              <input type="date" class="form-control" id="fechaPublicacionLibro" name="fecha_publicacion">
            </div>
            <div class="col-md-6">
              <label for="nombreLibro" class="form-label">Título</label>
              <input type="text" class="form-control" id="nombreLibro" name="nombre" required>
            </div>
            <div class="col-md-6">
              <label for="autorLibro" class="form-label">Autor</label>
              <input type="text" class="form-control" id="autorLibro" name="autor" required>
            </div>
            <div class="col-md-6">
              <label for="editorialLibro" class="form-label">Editorial</label>
              <input type="text" class="form-control" id="editorialLibro" name="editorial">
            </div>
            <div class="col-md-3">
              <label for="precioLibro" class="form-label">Precio (Q)</label>
              <input type="number" class="form-control" id="precioLibro" name="precio" min="0.01" step="0.01" required>
            </div>
            <div class="col-md-3">
              <label for="cantidadLibro" class="form-label">Stock</label>
              <input type="number" class="form-control" id="cantidadLibro" name="cantidad" min="0" required>
            </div>
            <div class="col-12">
              <label for="descripcionCortaLibro" class="form-label">Descripción corta (catálogo)</label>
              <input type="text" class="form-control" id="descripcionCortaLibro" name="descripcion_corta" maxlength="255">
            </div>
            <div class="col-12">
              <label for="descripcionLargaLibro" class="form-label">Descripción larga (detalle)</label>
              <textarea class="form-control" id="descripcionLargaLibro" name="descripcion_larga" rows="3"></textarea>
            </div>
            <div class="col-12">
              <label for="imagenLibro" class="form-label">Portada</label>
              <input type="file" class="form-control" id="imagenLibro" name="imagen" accept=".jpg,.jpeg,.png,.webp">
              <p class="small text-muted mb-0">JPG, PNG o WEBP, máx. 2 MB. Al editar, dejar vacío conserva la portada actual.</p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
        <div id="mensajeFormLibro" class="px-3 pb-3"></div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../../includes/plantillas/footer.php'; ?>