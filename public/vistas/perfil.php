<?php
// public/vistas/perfil.php
$tituloPagina = 'Mi perfil';
$rutaBase = '../../';

require_once __DIR__ . '/../../includes/ayudantes/AyudanteSesion.php';
require_once __DIR__ . '/../../includes/filtros/FiltroAutenticacion.php';
FiltroAutenticacion::protegerVista('login.php');

$scriptsPagina = ['perfil.js'];
require_once __DIR__ . '/../../includes/plantillas/header.php';
?>

<section class="mb-4">
  <h1 class="h3 mb-1">Mi perfil</h1>
  <p class="text-muted mb-0">Consulta y edita tus datos personales.</p>
</section>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="tarjeta-perfil p-4 text-center">
      <svg class="avatar-perfil mb-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <circle cx="12" cy="12" r="10"/>
        <circle cx="12" cy="9" r="3"/>
        <path d="M6 19c1.2-2.5 3.3-4 6-4s4.8 1.5 6 4"/>
      </svg>
      <p id="nombreCompletoPerfil" class="fw-semibold mb-1"></p>
      <p id="correoPerfil" class="small text-muted mb-4"></p>

      <a href="mis-pedidos.php" class="btn btn-outline-primary w-100 mb-2">Mis pedidos</a>
      <a href="wishlist.php" class="btn btn-outline-primary w-100">Lista de deseos</a>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="tarjeta-perfil p-4">
      <h2 class="h5 mb-3">Datos personales</h2>
      <form id="formPerfil">
        <div class="row g-3">
          <div class="col-md-6">
            <label for="nombrePerfil" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombrePerfil" name="nombre" maxlength="100" required>
          </div>
          <div class="col-md-6">
            <label for="apellidoPerfil" class="form-label">Apellido</label>
            <input type="text" class="form-control" id="apellidoPerfil" name="apellido" maxlength="100" required>
          </div>
          <div class="col-md-6">
            <label for="telefonoPerfil" class="form-label">Teléfono</label>
            <input type="tel" class="form-control" id="telefonoPerfil" name="telefono" maxlength="20">
          </div>
          <div class="col-md-6">
            <label for="direccionPerfil" class="form-label">Dirección</label>
            <input type="text" class="form-control" id="direccionPerfil" name="direccion" maxlength="255">
          </div>
        </div>
        <p class="small text-muted mt-3 mb-3">El correo no se puede modificar desde esta sección.</p>
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
        <div id="mensajePerfil" class="mt-3"></div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/plantillas/footer.php'; ?>