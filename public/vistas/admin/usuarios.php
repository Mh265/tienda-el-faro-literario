<?php
// public/vistas/admin/usuarios.php
$tituloPagina = 'Administrar usuarios';
$rutaBase = '../../../';

require_once __DIR__ . '/../../../includes/ayudantes/AyudanteSesion.php';
require_once __DIR__ . '/../../../includes/filtros/FiltroAutenticacion.php';
FiltroAutenticacion::protegerVistaAdministrador('../login.php');

$scriptsPagina = ['admin.js'];
require_once __DIR__ . '/../../../includes/plantillas/header.php';

$vistaActual = 'usuarios';
require_once __DIR__ . '/../../../includes/plantillas/admin-nav.php';
?>

<section class="mb-4">
  <h1 class="h3 mb-1">Administrar usuarios</h1>
  <p class="text-muted mb-0">Clientes y administradores registrados.</p>
</section>

<div id="zonaEstadoUsuariosAdmin" class="text-center my-4 text-muted d-none"></div>

<div class="table-responsive">
  <table class="table align-middle tabla-admin">
    <thead><tr><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Tipo</th><th>Registro</th><th class="text-end">Acciones</th></tr></thead>
    <tbody id="tablaUsuariosAdmin"></tbody>
  </table>
</div>

<div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formUsuario">
        <div class="modal-header">
          <h2 class="modal-title h5">Editar usuario</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="idUsuarioEditar" name="id_usuario">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="nombreUsuarioAdmin" class="form-label">Nombre</label>
              <input type="text" class="form-control" id="nombreUsuarioAdmin" name="nombre" maxlength="100" required>
            </div>
            <div class="col-md-6">
              <label for="apellidoUsuarioAdmin" class="form-label">Apellido</label>
              <input type="text" class="form-control" id="apellidoUsuarioAdmin" name="apellido" maxlength="100" required>
            </div>
            <div class="col-md-6">
              <label for="telefonoUsuarioAdmin" class="form-label">Teléfono</label>
              <input type="tel" class="form-control" id="telefonoUsuarioAdmin" name="telefono" maxlength="20">
            </div>
            <div class="col-md-6">
              <label for="direccionUsuarioAdmin" class="form-label">Dirección</label>
              <input type="text" class="form-control" id="direccionUsuarioAdmin" name="direccion" maxlength="255">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
        <div id="mensajeFormUsuario" class="px-3 pb-3"></div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../../includes/plantillas/footer.php'; ?>