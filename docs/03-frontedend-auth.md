# Feature/frontend-auth

## Objetivo
Implementar la interfaz de autenticación de "El Faro Literario": login,
registro y el estado de sesión reflejado en el navbar (RF01, RF02),
consumiendo `api/auth.php` (ya implementado en `Feature/auth-controller`).

## Archivos creados/modificados

| Archivo | Cambio |
|---|---|
| `public/vistas/login.php` | Vista pública nueva: formulario de correo/contraseña |
| `public/vistas/registro.php` | Vista pública nueva: formulario de registro (nombre, apellido, correo, password, teléfono y dirección opcionales) |
| `assets/js/auth.js` | Nuevo: `verificarSesion()`, manejo de submit de login/registro, `manejarLogout()`, `mostrarMensaje()` |
| `includes/plantillas/footer.php` | Se activa la carga de `auth.js` (antes comentada), justo después de `api.js` y antes de `$scriptsPagina` |
| `assets/css/styles.css` | Nueva sección `AUTH (login / registro)`: estilos de `.tarjeta-auth`, `.avatar-auth`, `.texto-auxiliar` |

## Endpoints consumidos

Todos en `api/auth.php` → `app/controladores/AuthController.php` (ya documentados en `docs/API.md`):

- `POST auth.php?accion=login`
- `POST auth.php?accion=registro`
- `POST auth.php?accion=logout`
- `POST auth.php?accion=verificar-sesion`

## Decisiones de arquitectura

- **Desviación del wireframe `wf-04`:** se reemplazó la fila de "Recordarme" +
  "¿Olvidaste tu contraseña?" por un enlace a `registro.php`, porque el
  proyecto no maneja "recordar sesión" (la sesión ya es una cookie nativa
  de PHP) y RF03 (recuperar contraseña) está marcado como opcional/pendiente
  en el backlog.
- **Sin wireframe dedicado para `registro.php`:** se reutilizó el mismo
  estilo de tarjeta oscura centrada de `login.php`, con más campos.
- **`verificarSesion()` se ejecuta en cada carga de página** (vía
  `footer.php`), nunca se guarda el usuario en `localStorage` ni
  `sessionStorage`: siempre se le pregunta al servidor.
- **Mensaje de éxito tras el registro vía query string (`?registrado=1`)**,
  no `sessionStorage`, para mantener el mecanismo lo más simple posible: la
  URL "recuerda" el aviso por una sola carga de página.
- **401 de `verificar-sesion` no se trata como error:** es el estado normal
  de un visitante; `auth.js` solo lo usa para decidir qué zona del navbar
  mostrar.
- **Mensajes de error/éxito con `textContent`**, nunca `innerHTML`, para no
  exponer el sitio a XSS con el texto que devuelve la API.

## Cómo probarlo

Ver la checklist completa de pruebas manuales entregada en el chat de esta
feature: casos de login correcto/incorrecto, registro con correo duplicado,
contraseña corta, logout, persistencia de sesión al recargar, visibilidad
de `#enlaceAdmin` según rol, responsivo en móvil y comportamiento con el
backend caído.

## Pendiente para features futuras

- `checkout.php`, `mis-pedidos.php`, `wishlist.php` y las vistas de
  `admin/` seguirán protegidas con `FiltroAutenticacion` en el servidor
  (ya implementado); este JS solo controla la parte cosmética del navbar.
- Si se implementa RF03 (recuperar contraseña), se puede reintroducir un
  enlace "¿Olvidaste tu contraseña?" en `login.php`.