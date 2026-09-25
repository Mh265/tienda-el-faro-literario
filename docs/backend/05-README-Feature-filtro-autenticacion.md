# Feature/filtro-autenticacion

## Objetivo
Centralizar el control de acceso (sesión y rol) de "El Faro Literario" en
una sola clase, para que los endpoints de la API que necesiten sesión o
rol de administrador, y las vistas privadas (empezando por
`public/vistas/admin/`), no repitan la validación de `AyudanteSesion` en
cada archivo.

## Archivo que agrega esta feature

| Archivo | Rol |
|---|---|
| `includes/filtros/FiltroAutenticacion.php` | Filtro de acceso: requiere sesión / requiere administrador, para API y para vistas |

## Decisiones de arquitectura

- **Cuatro métodos, no dos.** El filtro distingue "requiere sesión" de
  "requiere administrador" (como pide el proyecto), pero también distingue
  el contexto de uso: API (responde JSON) vs. vista HTML (redirige o
  imprime texto). Combinar ambas distinciones en un solo método habría
  requerido detectar "en qué contexto estoy" con lógica extra
  (`class_exists`, headers, etc.), que es justamente el tipo de patrón no
  visto en clase que el equipo decidió evitar. Cuatro métodos explícitos
  es más simple de leer y de usar.
- **API responde 401/403 en JSON, vía `Respuesta.php`** — igual que
  cualquier otro error de la API (`AuthController`, `LibroController`).
- **Vista sin sesión → redirige a `login.php`.** Vista con sesión pero sin
  rol de administrador → corta con `http_response_code(403)` y un mensaje
  en texto plano (no tiene sentido redirigir al login a alguien que ya
  inició sesión).
- **`$rutaLogin` es un parámetro explícito, sin valor por defecto.** La
  ruta relativa hacia `login.php` cambia según la profundidad de la vista
  (`public/vistas/admin/` vs. `public/vistas/`), así que es más simple
  pedirla explícita que adivinarla.
- **`LibroController.php` no se modifica.** Ya valida el rol directamente
  con `AyudanteSesion::esAdministrador()` (era una decisión pendiente de
  `Feature/libro-api`). Es funcionalmente equivalente a usar este filtro,
  así que se deja como está para no arriesgar una regresión en un
  endpoint ya probado y mergeado, a un día de la entrega. Queda como
  limpieza opcional para después de la entrega.
- **Ningún patrón fuera de lo visto en clase.** Solo métodos estáticos,
  `if` explícitos y `exit`, igual que el resto del proyecto.

## Cómo se usa

### Desde un endpoint de la API (Controller o `api/*.php`)

\`\`\`php
require_once __DIR__ . '/../includes/filtros/FiltroAutenticacion.php';

// Cualquier acción que solo requiera estar logueado (cliente o admin):
FiltroAutenticacion::protegerApi();

// Acciones exclusivas de administrador (crear/editar/eliminar/etc.):
FiltroAutenticacion::protegerApiAdministrador();
\`\`\`

### Desde una vista privada (ej. `public/vistas/admin/libros.php`)

\`\`\`php
require_once __DIR__ . '/../../../includes/ayudantes/AyudanteSesion.php';
require_once __DIR__ . '/../../../includes/filtros/FiltroAutenticacion.php';

FiltroAutenticacion::protegerVistaAdministrador('../login.php');
\`\`\`

## Códigos de respuesta

| Situación | API | Vista |
|---|---|---|
| Sin sesión | 401, mensaje JSON | Redirige a `login.php` |
| Con sesión, sin rol admin | 403, mensaje JSON | 403, mensaje en texto plano |

## Pendiente para features futuras

- Aplicar `protegerApi()` / `protegerApiAdministrador()` en
  `api/categorias.php`, `api/pedidos.php`, `api/resenas.php`,
  `api/wishlist.php`, `api/usuarios.php` cuando se implementen.
- Aplicar `protegerVistaAdministrador()` en `public/vistas/admin/*.php`
  (Jenifer) y `protegerVista()` en las vistas privadas de cliente
  (checkout, mis-pedidos, wishlist) cuando se implementen.
- Decidir, después de la entrega, si conviene refactorizar
  `LibroController.php` para usar este filtro en vez de la validación
  manual con `AyudanteSesion` (son funcionalmente equivalentes).