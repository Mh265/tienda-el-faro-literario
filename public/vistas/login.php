<?php
// public/vistas/login.php
$tituloPagina = 'Iniciar sesión';
$rutaBase = '../../';
require_once __DIR__ . '/../../includes/plantillas/header.php';
?>

<div class="row justify-content-center">
  <div class="col-12 col-sm-8 col-md-6 col-lg-4">
    <div class="card tarjeta-auth p-4 p-sm-5">

      <div class="text-center mb-4">
        <svg class="avatar-auth" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <circle cx="12" cy="12" r="10"/>
          <circle cx="12" cy="9" r="3"/>
          <path d="M6 19c1.2-2.5 3.3-4 6-4s4.8 1.5 6 4"/>
        </svg>
      </div>

      <form id="formLogin" novalidate>
        <div class="form-floating mb-3">
          <input type="email" class="form-control" id="correoLogin" name="correo"
                 placeholder="nombre@correo.com" required autocomplete="email">
          <label for="correoLogin">Correo electrónico</label>
        </div>

        <div class="form-floating mb-4">
          <input type="password" class="form-control" id="passwordLogin" name="password"
                 placeholder="Contraseña" required autocomplete="current-password">
          <label for="passwordLogin">Contraseña</label>
        </div>

        <button type="submit" class="btn btn-secondary w-100 mb-3">Iniciar sesión</button>

        <p class="text-center mb-0">
          ¿No tienes cuenta? <a href="registro.php">Regístrate</a>
        </p>
      </form>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/plantillas/footer.php'; ?>