# Feature/libro-api

## Objetivo

Implementar el catálogo de libros de "El Faro Literario": listado con
búsqueda y filtros, detalle, y CRUD administrativo (crear, editar, dar de
baja, reactivar) sobre la tabla `productos`, expuesto como un endpoint de
la API consumible vía `fetch()`.

## Archivos que agrega/completa esta feature

| Archivo | Rol |
|---|---|
| `app/modelos/Libro.php` | Acceso a datos sobre `productos`: catálogo con filtros, listado admin, detalle, crear, actualizar, dar de baja, reactivar |
| `app/controladores/LibroController.php` | Validaciones, control de permisos (administrador) y armado de las respuestas JSON |
| `api/libros.php` | Endpoint público que enruta por método HTTP (y por `accion` en los casos que no son CRUD estándar) |
| `docs/API.md` | Actualizado con el contrato completo de `api/libros.php` |

## Decisiones de arquitectura

- **Nunca hay un `DELETE` físico sobre `productos`.** La tabla
  `detalle_pedido` tiene una FK hacia `productos` con `ON DELETE RESTRICT`,
  así que un libro con ventas ya registradas ni siquiera se podría borrar.
  En vez de eso, `Libro::darDeBaja()` marca `estado = 'inactivo'` y el
  catálogo público deja de mostrarlo. El método `DELETE` del endpoint sigue
  existiendo por prolijidad REST, pero internamente ejecuta ese soft
  delete, nunca un `DELETE FROM productos`.
- **`Libro.php` separa "listado del catálogo" de "listado admin".**
  `obtenerCatalogo($filtros)` siempre filtra `estado = 'activo'` y aplica
  los filtros de búsqueda; `obtenerTodosAdmin()` trae todo (activos e
  inactivos) sin filtros, pensado para el panel de administración. Se
  mantienen como dos métodos separados en vez de uno con banderas, para
  que quede explícito en el código cuál usa cada vista.
- **El WHERE del catálogo se arma dinámicamente.** Como `q`,
  `id_categoria`, `precio_min`, `precio_max`, `disponible` y `orden` son
  todos opcionales, `obtenerCatalogo()` arma un arreglo de condiciones y
  solo agrega la condición (con su parámetro nombrado) si el filtro viene
  en la petición. El criterio de `orden` usa una lista blanca de columnas
  permitidas — nunca se concatena el valor de `$_GET['orden']` directo en
  el SQL.
- **`api/libros.php` enruta principalmente por método HTTP**, no por
  `accion` como `auth.php`. Es más "REST" (GET = leer, POST = crear, PUT =
  editar, DELETE = dar de baja) y encaja mejor con un recurso con CRUD
  completo. `accion` solo se usa dentro de `GET` (`admin-listado`) y `PUT`
  (`reactivar`), que son los dos casos que no encajan en ese patrón.
- **El control de acceso de administrador se hace directo en el
  Controller con `AyudanteSesion::esAdministrador()`.**
  `FiltroAutenticacion.php` (la feature que protegerá rutas completas)
  todavía no existe, así que cada acción de escritura de
  `LibroController` (crear, actualizar, dar de baja, reactivar,
  admin-listado) valida el rol al inicio del método y responde `403` si
  no es administrador. Cuando se implemente el filtro de rutas, esta
  validación puede quedarse igual (es un chequeo de rol dentro del
  Controller, no de la ruta en sí) — se decide en esa feature.
- **`LibroController` valida que `id_categoria` exista antes de
  crear/editar**, usando `Categoria::obtenerPorId()` (ya implementado en
  `Feature/modelos-crud`). Así se devuelve un mensaje claro en vez de
  dejar que la FK `productos → categorias` lance una excepción de PDO sin
  capturar.
- **Ningún patrón fuera de lo visto en clase.** Igual que el resto del
  proyecto: solo métodos estáticos, `prepare()` + parámetros nombrados, y
  un `switch` para enrutar en el endpoint.

## Endpoints

Ver el detalle completo (parámetros, ejemplos de respuesta y códigos de
error) en `docs/API.md`, sección "`api/libros.php`". Resumen:

| Método | `accion` | Acceso | Descripción |
|---|---|---|---|
| `GET` | *(ninguna)* | Público | Catálogo con búsqueda y filtros (`q`, `id_categoria`, `precio_min`, `precio_max`, `disponible`, `orden`) |
| `GET` con `?id=` | *(ninguna)* | Público / admin | Detalle de un libro |
| `GET` | `admin-listado` | Administrador | Listado completo (activos e inactivos) |
| `POST` | *(ninguna)* | Administrador | Crear libro |
| `PUT` con `?id=` | *(ninguna)* | Administrador | Actualizar libro |
| `PUT` con `?id=` | `reactivar` | Administrador | Reactivar libro dado de baja |
| `DELETE` con `?id=` | *(ninguna)* | Administrador | Dar de baja (soft delete) |

## Pendiente para features futuras

- `FiltroAutenticacion.php` sigue vacío: cuando se implemente, se puede
  revisar si conviene mover a ese filtro la validación de rol que hoy vive
  directamente en `LibroController`, o dejarla como está.
- La subida real de portadas (`imagen`) a `assets/img/uploads/` no es
  parte de esta feature: por ahora `imagen` se guarda como el string que
  llegue en el cuerpo (por ejemplo, un nombre de archivo ya subido por
  otro medio, o `null`).
- `Feature/categoria-api` todavía no expone `api/categorias.php`; mientras
  tanto, el frontend puede usar un `SELECT` simulado o esperar esa feature
  para poblar el `<select>` de categorías en el formulario de libros.