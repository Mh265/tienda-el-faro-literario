<?php
// public/vistas/registro.php
$tituloPagina = 'Crear cuenta';
$rutaBase = '../../';
require_once __DIR__ . '/../../includes/plantillas/header.php';
?>

<div class="row justify-content-center">
  <div class="col-12 col-sm-10 col-md-8 col-lg-6">
    <div class="card tarjeta-auth p-4 p-sm-5">

      <h2 class="h4 text-center mb-4">Crear cuenta</h2>

      <form id="formRegistro" novalidate>
        <div class="row g-3">
          <div class="col-md-6">
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="nombreRegistro" name="nombre"
                     placeholder="Nombre" required autocomplete="given-name">
              <label for="nombreRegistro">Nombre</label>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="apellidoRegistro" name="apellido"
                     placeholder="Apellido" required autocomplete="family-name">
              <label for="apellidoRegistro">Apellido</label>
            </div>
          </div>
        </div>

        <div class="form-floating mb-3">
          <input type="email" class="form-control" id="correoRegistro" name="correo"
                 placeholder="nombre@correo.com" required autocomplete="email">
          <label for="correoRegistro">Correo electrónico</label>
        </div>

        <div class="form-floating mb-1">
          <input type="password" class="form-control" id="passwordRegistro" name="password"
                 placeholder="Contraseña" required minlength="6" autocomplete="new-password">
          <label for="passwordRegistro">Contraseña</label>
        </div>
        <p class="texto-auxiliar small mb-3">Mínimo 6 caracteres.</p>

        <div class="row g-3">
          <div class="col-md-6">
            <div class="form-floating mb-3">
              <input type="tel" class="form-control" id="telefonoRegistro" name="telefono"
                     placeholder="Teléfono" autocomplete="tel">
              <label for="telefonoRegistro">Teléfono <span class="texto-auxiliar">(opcional)</span></label>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-floating mb-3">
              <input type="text" class="form-control" id="direccionRegistro" name="direccion"
                     placeholder="Dirección" autocomplete="street-address">
              <label for="direccionRegistro">Dirección <span class="texto-auxiliar">(opcional)</span></label>
            </div>
          </div>
        </div>

        <button type="submit" class="btn btn-secondary w-100 mb-3">Crear cuenta</button>

        <p class="text-center mb-0">
          ¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a>
        </p>
      </form>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/plantillas/footer.php'; ?>