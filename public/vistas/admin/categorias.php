<?php
// public/vistas/admin/categorias.php
$tituloPagina = 'Administrar categorías';
$rutaBase = '../../../';

require_once __DIR__ . '/../../../includes/ayudantes/AyudanteSesion.php';
require_once __DIR__ . '/../../../includes/filtros/FiltroAutenticacion.php';
FiltroAutenticacion::protegerVistaAdministrador('../login.php');

$scriptsPagina = ['admin.js'];
require_once __DIR__ . '/../../../includes/plantillas/header.php';

$vistaActual = 'categorias';
require_once __DIR__ . '/../../../includes/plantillas/admin-nav.php';
?>

<section class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
  <div>
    <h1 class="h3 mb-1">Administrar categorías</h1>
    <p class="text-muted mb-0">Géneros literarios del catálogo.</p>
  </div>
  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCategoria" id="btnNuevaCategoria">
    + Nueva categoría
  </button>
</section>

<div id="zonaEstadoCategoriasAdmin" class="text-center my-4 text-muted d-none" aria-live="polite"></div>

<div class="table-responsive">
  <table class="table align-middle tabla-admin">
    <thead>
      <tr><th>Nombre</th><th>Descripción</th><th class="text-end">Acciones</th></tr>
    </thead>
    <tbody id="tablaCategoriasAdmin"></tbody>
  </table>
</div>

<div class="modal fade" id="modalCategoria" tabindex="-1" aria-labelledby="tituloModalCategoria" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formCategoria">
        <div class="modal-header">
          <h2 class="modal-title h5" id="tituloModalCategoria">Nueva categoría</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="idCategoriaEditar" name="id_categoria">
          <div class="mb-3">
            <label for="nombreCategoria" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombreCategoria" name="nombre" maxlength="100" required>
          </div>
          <div class="mb-0">
            <label for="descripcionCategoria" class="form-label">Descripción</label>
            <input type="text" class="form-control" id="descripcionCategoria" name="descripcion" maxlength="255">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
        <div id="mensajeFormCategoria" class="px-3 pb-3"></div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../../includes/plantillas/footer.php'; ?>
