<?php
// includes/plantillas/header.php
// Variables que define cada vista ANTES de incluir esta plantilla:
//   $tituloPagina -> texto para <title>
//   $rutaBase     -> ruta relativa hasta la raíz ('../', '../../', '../../../')
$tituloPagina = $tituloPagina ?? 'El Faro Literario';
$rutaBase     = $rutaBase ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($tituloPagina) ?> | El Faro Literario</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

  <!-- Orden importante: Bootstrap -> variables -> styles -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= $rutaBase ?>assets/css/variables.css">
  <link rel="stylesheet" href="<?= $rutaBase ?>assets/css/styles.css">

  <script>const API_URL = "<?= $rutaBase ?>api/";</script>
</head>
<body>
<header>
  <nav class="navbar navbar-expand-lg navbar-light navbar-faro" aria-label="Navegación principal">
    <div class="container">

      <a class="navbar-brand d-flex align-items-center gap-2" href="<?= $rutaBase ?>public/index.php">
        <img src="<?= $rutaBase ?>assets/img/logo.png" alt="Logo de El Faro Literario" class="logo-marca" height="40">
        <span>El Faro Literario</span>
      </a>

      <!-- Carrito fuera del menú colapsable: en móvil se ve siempre junto a la marca -->
      <a class="enlace-carrito ms-auto me-2 order-lg-last" href="<?= $rutaBase ?>public/vistas/carrito.php" aria-label="Ir al carrito de compras">
        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/>
          <path d="M2 3h3l2.7 12.4a1 1 0 0 0 1 .8h8.9a1 1 0 0 0 1-.8L20 8H6"/>
        </svg>
        <span id="contadorCarrito" class="badge rounded-pill contador-carrito">0</span>
        <span class="visually-hidden">artículos en el carrito</span>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
              aria-controls="menuPrincipal" aria-expanded="false" aria-label="Mostrar u ocultar el menú">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="menuPrincipal">
        <ul class="navbar-nav me-lg-3">
          <li class="nav-item"><a class="nav-link" href="<?= $rutaBase ?>public/index.php">Inicio</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= $rutaBase ?>public/vistas/catalogo.php">Catálogo</a></li>
        </ul>

        <!-- Buscador: GET hacia el catálogo; "q" es el mismo parámetro de api/libros.php -->
        <form class="d-flex flex-grow-1 my-2 my-lg-0" role="search"
              action="<?= $rutaBase ?>public/vistas/catalogo.php" method="get">
          <input class="form-control" type="search" name="q" maxlength="100"
                 placeholder="Buscar por título o autor" aria-label="Buscar libros">
          <button class="btn btn-primary ms-2" type="submit">Buscar</button>
        </form>

        <!-- Zona de sesión: la lógica real llega en Feature/frontend-auth -->
        <div class="ms-lg-3 my-2 my-lg-0">
          <div id="zonaSesionInvitado" class="d-flex gap-2">
            <a class="btn btn-outline-primary" href="<?= $rutaBase ?>public/vistas/login.php">Iniciar sesión</a>
            <a class="btn btn-primary" href="<?= $rutaBase ?>public/vistas/registro.php">Registrarse</a>
          </div>

          <div id="zonaSesionUsuario" class="dropdown d-none">
            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <span id="nombreUsuario">Mi cuenta</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <!-- mis-pedidos.php y wishlist.php aún no existen (llegan en sus features); se referencian ya para no reescribir el menú luego -->
              <li><a class="dropdown-item" href="<?= $rutaBase ?>public/vistas/mis-pedidos.php">Mis pedidos</a></li>
              <li><a class="dropdown-item" href="<?= $rutaBase ?>public/vistas/wishlist.php">Lista de deseos</a></li>
              <li id="enlaceAdmin" class="d-none"><a class="dropdown-item" href="<?= $rutaBase ?>public/vistas/admin/libros.php">Panel de administración</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><button id="btnCerrarSesion" class="dropdown-item" type="button">Cerrar sesión</button></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <!-- Aquí se insertan alertas/toasts de Bootstrap -->
  <div id="zonaMensajes" class="container mt-3" aria-live="polite"></div>
</header>

<main class="container py-4">