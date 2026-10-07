# API — El Faro Literario

Contrato entre Backend (Milton) y Frontend (Jenifer). Lo mantiene Milton y se actualiza en el mismo Pull Request de cada feature de backend.

**Última actualización:** 06/10/2026 · **Features documentadas:** `Feature/categoria-api`, `Feature/wishlist-api`, `Feature/resena-api`, `Feature/usuario-api`

**Estados:** ✅ implementado · 🚧 en desarrollo · 📝 propuesto (aún no implementado)

---

## 1. Convenciones generales

| Aspecto | Detalle |
|---|---|
| URL base local | `http://localhost/<carpeta-del-proyecto>/api/` (ejemplo: `.../api/auth.php`) |
| Formato | JSON en el cuerpo de la petición y de la respuesta |
| Cabecera de petición | `Content-Type: application/json` |
| Cabecera de respuesta | `Content-Type: application/json; charset=utf-8` |
| Sesión | Cookie de sesión de PHP. Con `fetch()` en el mismo origen se envía sola |
| Nombres de campos | Iguales a las columnas de la BD (`snake_case`) |
| Selección de operación | `auth.php` usa el parámetro `accion`. `libros.php`, `pedidos.php`, `categorias.php`, `wishlist.php`, `resenas.php` y `usuarios.php` enrutan principalmente por **método HTTP** (GET/POST/PUT/DELETE) y usan `accion` solo para los casos que no encajan en el CRUD estándar (`admin-listado`, `reactivar`) |

### Formato de respuesta (`includes/ayudantes/Respuesta.php`)

Éxito:

```json
{ "exito": true, "mensaje": "Texto para el usuario", "datos": { } }
```

Error (no incluye `datos`):

```json
{ "exito": false, "mensaje": "Texto del error para mostrar al usuario" }
```

`datos` puede ser un objeto, una lista o `null` (por ejemplo en logout).

### Códigos HTTP usados hasta ahora

| Código | Significado |
|---|---|
| 200 | Operación correcta |
| 201 | Recurso creado |
| 400 | Datos faltantes o inválidos |
| 401 | Credenciales incorrectas o sin sesión activa |
| 403 | Sesión activa pero sin permisos (no es dueño del recurso o no es administrador) |
| 404 | Acción no reconocida / recurso no encontrado |
| 405 | Método HTTP no soportado por el endpoint |
| 409 | Conflicto (correo/nombre/registro ya existente, o restricción de integridad referencial) |

### Aviso para el frontend: errores que no vienen en JSON

Si falla la conexión a la base de datos (`BaseDatos.php` usa `die()`) o una excepción de PDO no se captura, la respuesta **no es JSON** (texto o HTML). `response.json()` lanzará un error: el cliente de API (`api.js`) debe envolverlo en `try/catch` y mostrar un mensaje genérico ("No se pudo completar la operación, intenta de nuevo").

### Ejemplo de consumo

```js
// api/auth.php → app/controladores/AuthController.php
const respuesta = await fetch(API_URL + 'auth.php?accion=login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ correo, password })
});
const json = await respuesta.json();
if (!json.exito) {
    mostrarError(json.mensaje);
    return;
}
// json.datos contiene el usuario
```

---

## 2. Endpoints implementados

### `api/auth.php` ✅

Controlador: `app/controladores/AuthController.php` · Modelo: `app/modelos/Usuario.php`

Acciones disponibles (enviar por `POST` con cuerpo JSON):

| `accion` | Acceso | Descripción |
|---|---|---|
| `registro` | Público | Crea una cuenta de cliente |
| `login` | Público | Valida credenciales y abre sesión |
| `logout` | Sesión | Cierra la sesión |
| `verificar-sesion` | Público | Consulta si hay una sesión activa |

Cualquier otro valor responde `404` con el mensaje *"Acción no reconocida. Use: registro, login, logout o verificar-sesion."*

#### `registro`

`POST api/auth.php?accion=registro`

| Campo | Tipo | Obligatorio | Regla |
|---|---|---|---|
| `nombre` | string | Sí | No vacío |
| `apellido` | string | Sí | No vacío |
| `correo` | string | Sí | Formato de correo válido; único |
| `password` | string | Sí | Mínimo 6 caracteres |
| `telefono` | string | No | Si no se llena, enviar `null` u omitir el campo (una cadena vacía se guarda como vacía) |
| `direccion` | string | No | Igual que `telefono` |

El tipo de usuario siempre se crea como `cliente`. **El registro no inicia sesión**: después hay que llamar a `login` (o redirigir a la vista de login).

Respuesta `201`:

```json
{ "exito": true, "mensaje": "Cuenta creada correctamente.", "datos": { "id_usuario": 7 } }
```

| Código | Mensaje |
|---|---|
| 400 | Nombre, apellido, correo y contraseña son obligatorios. |
| 400 | El correo no tiene un formato válido. |
| 400 | La contraseña debe tener al menos 6 caracteres. |
| 409 | Ya existe una cuenta registrada con ese correo. |

#### `login`

`POST api/auth.php?accion=login`

Cuerpo: `{ "correo": "ana.morales@correo.com", "password": "..." }`

Respuesta `200`:

```json
{
  "exito": true,
  "mensaje": "Inicio de sesión exitoso.",
  "datos": {
    "id_usuario": 2,
    "nombre": "Ana",
    "apellido": "Morales",
    "correo": "ana.morales@correo.com",
    "telefono": "5511-2233",
    "direccion": null,
    "tipo_usuario": "cliente",
    "fecha_registro": "2026-09-16 10:00:00"
  }
}
```

`tipo_usuario` es `cliente` o `administrador`; el frontend lo usa para mostrar u ocultar opciones (la seguridad real se valida en el servidor). La contraseña nunca se devuelve.

| Código | Mensaje |
|---|---|
| 400 | Correo y contraseña son obligatorios. |
| 401 | Correo o contraseña incorrectos. *(mismo mensaje si el correo no existe o la contraseña falla)* |

#### `logout`

`POST api/auth.php?accion=logout` (sin cuerpo)

Respuesta `200`: `{ "exito": true, "mensaje": "Sesión cerrada correctamente.", "datos": null }`

#### `verificar-sesion`

`POST api/auth.php?accion=verificar-sesion` (sin cuerpo)

Respuesta `200`:

```json
{
  "exito": true,
  "mensaje": "Sesión activa.",
  "datos": {
    "id_usuario": 2,
    "nombre": "Ana",
    "apellido": "Morales",
    "correo": "ana.morales@correo.com",
    "tipo_usuario": "cliente"
  }
}
```

Sin sesión responde `401` con *"No hay una sesión activa."*. Esto **no es una falla**: es el caso normal de un visitante; el frontend lo usa para decidir si el navbar muestra "Iniciar sesión" o "Mi cuenta / Cerrar sesión". Nota: esta respuesta trae menos campos que `login` (no incluye `telefono`, `direccion` ni `fecha_registro`).

---

### `api/libros.php` ✅

Controlador: `app/controladores/LibroController.php` · Modelos: `app/modelos/Libro.php` (principal) y `app/modelos/Categoria.php` (solo para validar que la categoría exista al crear/editar).

Enruta por **método HTTP**. `accion` solo se usa dentro de `GET` (`admin-listado`) y `PUT` (`reactivar`) para los dos casos que no son un CRUD estándar.

**Nota sobre permisos:** `LibroController` valida `AyudanteSesion::esAdministrador()` directamente en cada acción de escritura (crear, actualizar, dar de baja, reactivar, listado admin), por decisión explícita tomada al implementar `Feature/filtro-autenticacion` (para no arriesgar una regresión a un día de la entrega). Es funcionalmente equivalente a usar `FiltroAutenticacion::protegerApiAdministrador()`.

| Método | `accion` | Acceso | Descripción |
|---|---|---|---|
| `GET` | *(ninguna)* | Público | Catálogo de libros con `estado='activo'`, con búsqueda y filtros |
| `GET` con `?id=` | *(ninguna)* | Público / admin | Detalle de un libro |
| `GET` | `admin-listado` | Administrador | Listado completo (activos e inactivos) para el panel admin |
| `POST` | *(ninguna)* | Administrador | Crea un libro nuevo |
| `PUT` con `?id=` | *(ninguna)* | Administrador | Actualiza los datos de un libro |
| `POST` con `?id=` | `portada` | Administrador | Cambia la portada de un libro existente |
| `PUT` con `?id=` | `reactivar` | Administrador | Reactiva un libro dado de baja |
| `DELETE` con `?id=` | *(ninguna)* | Administrador | Da de baja el libro (`estado='inactivo'`); **nunca es un DELETE físico** |

#### Catálogo — `GET api/libros.php`

Todos los parámetros son opcionales y van en la query string:

| Parámetro | Tipo | Regla |
|---|---|---|
| `q` | string | Busca coincidencias en `nombre` o `autor` (`LIKE %q%`) |
| `id_categoria` | int | Filtra por una categoría exacta |
| `precio_min` | decimal | `precio >= precio_min` |
| `precio_max` | decimal | `precio <= precio_max` |
| `disponible` | `1` | Si se envía `1`, solo libros con `cantidad > 0` |
| `orden` | string | Uno de: `precio_asc`, `precio_desc`, `nombre_asc`, `recientes` (por defecto: `recientes`, es decir más nuevos primero) |

Ejemplo: `GET api/libros.php?q=dune&precio_max=200&orden=precio_asc`

Respuesta `200`:

```json
{
  "exito": true,
  "mensaje": "Listado de libros obtenido correctamente.",
  "datos": [
    {
      "id_producto": 5,
      "id_categoria": 2,
      "nombre": "Dune",
      "autor": "Frank Herbert",
      "editorial": "Chilton Books",
      "descripcion_corta": "Épica de ciencia ficción ambientada en el planeta Arrakis.",
      "descripcion_larga": "...",
      "precio": "210.00",
      "cantidad": 9,
      "imagen": null,
      "fecha_publicacion": null,
      "estado": "activo",
      "fecha_creacion": "2026-09-01 10:00:00",
      "nombre_categoria": "Ciencia Ficción"
    }
  ]
}
```

#### Listado administrativo — `GET api/libros.php?accion=admin-listado`

Igual forma de respuesta que el catálogo, pero incluye libros con `estado='inactivo'` y no aplica filtros. Requiere sesión de tipo `administrador`.

| Código | Mensaje |
|---|---|
| 403 | No tiene permisos para ver este listado. |

#### Detalle — `GET api/libros.php?id=5`

Un visitante o cliente que consulta un libro `inactivo` recibe `404`, como si no existiera (para no revelar libros dados de baja). Un administrador sí puede verlo (por ejemplo, para editarlo).

| Código | Mensaje |
|---|---|
| 400 | El id del libro no es válido. |
| 404 | Libro no encontrado. |

#### Crear — `POST api/libros.php`

Requiere sesión de tipo `administrador`. **Cuerpo: `multipart/form-data`**
(no JSON, por la portada). Campos de texto normales + un campo de archivo:

| Campo | Tipo | Obligatorio | Regla |
|---|---|---|---|
| `id_categoria` | int | Sí | Debe existir en `categorias` |
| `nombre` | string | Sí | Título del libro, no vacío |
| `autor` | string | Sí | No vacío |
| `editorial` | string | No | — |
| `descripcion_corta` | string | No | Para tarjetas del catálogo |
| `descripcion_larga` | string | No | Para la vista de detalle |
| `precio` | decimal | Sí | Mayor a 0 |
| `cantidad` | int | No (default 0) | No puede ser negativa |
| `imagen` | **archivo** | No | JPG/JPEG/PNG/WEBP, máximo 2 MB. Si no se envía, el libro queda con portada por defecto en el frontend |
| `fecha_publicacion` | date (`YYYY-MM-DD`) | No | — |

El servidor genera el nombre final del archivo (nunca se usa el nombre
original) y solo guarda ese nombre en la BD, no la ruta completa. El
frontend arma la URL como `assets/img/uploads/<nombre>`.

Respuesta `201`:

```json
{ "exito": true, "mensaje": "Libro creado correctamente.", "datos": { "id_producto": 31 } }
```

| Código | Mensaje |
|---|---|
| 400 | La categoría es obligatoria. |
| 400 | El título y el autor son obligatorios. |
| 400 | El precio debe ser un número mayor a 0. |
| 400 | La cantidad en stock no puede ser negativa. |
| 400 | La categoría indicada no existe. |
| 400 | La imagen no debe superar los 2 MB. |
| 400 | Formato de imagen no permitido. Use JPG, PNG o WEBP. |
| 403 | No tiene permisos para crear libros. |

**Nota para el frontend:** este endpoint espera `FormData`, no
`JSON.stringify`. No fijes manualmente el header `Content-Type`: el
navegador lo arma solo (con el boundary correcto) al usar `FormData`.

#### Actualizar — `PUT api/libros.php?id=5`

Requiere sesión de tipo `administrador`. Mismo cuerpo y mismas reglas que `crear`. No cambia `estado` (para eso están `reactivar` y el `DELETE`). No actualiza la portada (`imagen`): ese campo queda intacto y se ignora si viene en el cuerpo. Para cambiar la portada se usa `POST api/libros.php?accion=portada&id=#` (ver más abajo).

Respuesta `200`: `{ "exito": true, "mensaje": "Libro actualizado correctamente.", "datos": null }`

Mismos códigos de error que `crear`, más:

| Código | Mensaje |
|---|---|
| 404 | Libro no encontrado. |

#### Cambiar portada — `POST api/libros.php?accion=portada&id=5`

Requiere sesión de tipo `administrador`. **Cuerpo: `multipart/form-data`** con un solo campo de archivo:

| Campo | Tipo | Obligatorio | Regla |
|---|---|---|---|
| `imagen` | **archivo** | Sí | JPG/JPEG/PNG/WEBP, máximo 2 MB. Se valida la extensión y que el contenido sea realmente una imagen |

Igual que en `crear`, el servidor genera el nombre del archivo y solo guarda ese nombre en la BD. La portada anterior se reemplaza en la BD (el archivo viejo queda en `assets/img/uploads/`).

Respuesta `200`: `{ "exito": true, "mensaje": "Portada actualizada correctamente.", "datos": { "imagen": "libro_66f1a2b3c4d5e.jpg" } }`

| Código | Mensaje |
|---|---|
| 400 | El id del libro no es válido. / Debe indicar el id del libro para cambiar su portada. |
| 400 | Debe seleccionar una imagen. |
| 400 | La imagen no debe superar los 2 MB. |
| 400 | Formato de imagen no permitido. Use JPG, PNG o WEBP. |
| 400 | El archivo no es una imagen válida. |
| 403 | No tiene permisos para cambiar la portada. |
| 404 | Libro no encontrado. |

#### Dar de baja — `DELETE api/libros.php?id=5`

Requiere sesión de tipo `administrador`. Marca `estado='inactivo'`; el catálogo público deja de mostrar el libro. **No borra la fila**: un libro con ventas registradas no podría borrarse de todos modos por la FK `RESTRICT` de `detalle_pedido` hacia `productos`.

Respuesta `200`: `{ "exito": true, "mensaje": "Libro dado de baja correctamente.", "datos": null }`

| Código | Mensaje |
|---|---|
| 400 | El id del libro no es válido. |
| 403 | No tiene permisos para dar de baja libros. |
| 404 | Libro no encontrado. |

#### Reactivar — `PUT api/libros.php?id=5&accion=reactivar`

Requiere sesión de tipo `administrador`. Vuelve a poner `estado='activo'`. Mismos códigos de error que `darDeBaja`.

Respuesta `200`: `{ "exito": true, "mensaje": "Libro reactivado correctamente.", "datos": null }`

---

### `api/pedidos.php` ✅

Controlador: `app/controladores/PedidoController.php` · Modelos:
`app/modelos/Pedido.php` (cabecera), `app/modelos/DetallePedido.php`
(líneas) y `app/modelos/Libro.php` (precio/stock al momento de comprar).

Enruta por **método HTTP**, igual que `api/libros.php`. `accion` solo se
usa dentro de `GET` para `admin-listado`.

**Requiere sesión en todas sus acciones**, usando
`FiltroAutenticacion::protegerApi()` / `protegerApiAdministrador()` desde
el inicio de cada método del Controller.

| Método | `accion` | Acceso | Descripción |
|---|---|---|---|
| `GET` | *(ninguna)* | Cliente | Historial de pedidos propios |
| `GET` | `admin-listado` | Administrador | Todos los pedidos |
| `GET` con `?id=` | *(ninguna)* | Cliente (dueño) / admin | Detalle del pedido + sus líneas |
| `POST` | *(ninguna)* | Cliente | Crea un pedido desde el carrito (transacción) |
| `PUT` con `?id=` | *(ninguna)* | Administrador | Cambia el estado del pedido |

#### Crear pedido — `POST api/pedidos.php`

Cuerpo (JSON):

```json
{
  "items": [
    { "id_producto": 5, "cantidad": 1 },
    { "id_producto": 8, "cantidad": 2 }
  ]
}
```

El cliente **nunca** envía precio ni total — se calculan en el servidor con
`productos.precio` al momento de crear el pedido. Toda la operación
(validar stock, insertar `pedidos` + `detalle_pedido`, descontar
`productos.cantidad`) es una única transacción PDO: si cualquier línea
falla, no se guarda nada.

**Confirmación en pantalla (RF20):** la respuesta ya trae `id_pedido` y
`total`, suficiente para que el frontend arme una pantalla de confirmación
inmediatamente después de crear el pedido. El envío de un correo de
confirmación queda fuera de alcance del proyecto.

Respuesta `201`:

```json
{ "exito": true, "mensaje": "Pedido creado correctamente.", "datos": { "id_pedido": 12, "total": 375.00 } }
```

| Código | Mensaje |
|---|---|
| 400 | El pedido debe incluir al menos un libro. |
| 400 | Cada línea del pedido debe traer id_producto y cantidad (entero mayor a 0). |
| 400 | El libro con id # no está disponible. |
| 400 | Stock insuficiente para "Título". Disponible: N. |
| 400 | El stock cambió mientras se procesaba el pedido. Intenta de nuevo. |
| 401 | Debe iniciar sesión para acceder a este recurso. |

#### Historial propio — `GET api/pedidos.php`

Requiere sesión. Pedidos del usuario autenticado, sin sus líneas (para eso
está el detalle).

#### Listado administrativo — `GET api/pedidos.php?accion=admin-listado`

Requiere sesión de tipo `administrador`.

| Código | Mensaje |
|---|---|
| 403 | No tiene permisos de administrador para realizar esta acción. |

#### Detalle — `GET api/pedidos.php?id=12`

Requiere sesión. Devuelve el pedido con sus líneas en la clave `lineas`
(cada línea incluye `nombre`, `autor` e `imagen` del libro). Un cliente que
consulta un pedido ajeno recibe `404` (no `403`, para no revelar que
existe); un administrador puede ver cualquiera.

| Código | Mensaje |
|---|---|
| 400 | El id del pedido no es válido. |
| 404 | Pedido no encontrado. |

#### Cambiar estado — `PUT api/pedidos.php?id=12`

Requiere sesión de tipo `administrador`. Cuerpo: `{ "estado": "pagado" }`.
Valores permitidos: `pendiente`, `pagado`, `enviado`, `entregado`,
`cancelado`. No valida la secuencia entre estados, salvo estas reglas:

- **Cancelar devuelve el stock:** al pasar a `cancelado`, la cantidad de cada línea se suma de nuevo a `productos.cantidad`, todo en una transacción.
- **`cancelado` es definitivo:** un pedido cancelado no puede cambiar a otro estado (`409`), para no devolver el stock dos veces.
- Si el estado que se manda es el mismo que el pedido ya tiene, responde `200` sin hacer cambios.

Respuesta `200`: `{ "exito": true, "mensaje": "Estado del pedido actualizado correctamente.", "datos": null }`
(al cancelar: `"Pedido cancelado y stock devuelto correctamente."`)

| Código | Mensaje |
|---|---|
| 400 | El id del pedido no es válido. |
| 400 | Estado no válido. Use: pendiente, pagado, enviado, entregado, cancelado. |
| 403 | No tiene permisos de administrador para realizar esta acción. |
| 404 | Pedido no encontrado. |
| 409 | Un pedido cancelado no se puede cambiar a otro estado. |
| 500 | No se pudo cancelar el pedido, intenta de nuevo. |

---

### `api/categorias.php` ✅

Controlador: `app/controladores/CategoriaController.php` · Modelo: `app/modelos/Categoria.php`.

Enruta por **método HTTP**, sin acciones especiales.

| Método | Acceso | Descripción |
|---|---|---|
| `GET` | Público | Listado de categorías, ordenado por nombre |
| `GET` con `?id=` | Público | Detalle de una categoría |
| `POST` | Administrador | Crea una categoría nueva |
| `PUT` con `?id=` | Administrador | Actualiza nombre/descripción |
| `DELETE` con `?id=` | Administrador | Elimina la categoría |

#### Listado — `GET api/categorias.php`

Respuesta `200`:

```json
{
  "exito": true,
  "mensaje": "Listado de categorías obtenido correctamente.",
  "datos": [
    { "id_categoria": 6, "nombre": "Fantasía", "descripcion": "Mundos y criaturas imaginarias" }
  ]
}
```

#### Detalle — `GET api/categorias.php?id=6`

| Código | Mensaje |
|---|---|
| 400 | El id de la categoría no es válido. |
| 404 | Categoría no encontrada. |

#### Crear — `POST api/categorias.php`

Requiere sesión de tipo `administrador`. Cuerpo (JSON):

| Campo | Tipo | Obligatorio | Regla |
|---|---|---|---|
| `nombre` | string | Sí | No vacío; único (`uq_categorias_nombre`) |
| `descripcion` | string | No | — |

Respuesta `201`: `{ "exito": true, "mensaje": "Categoría creada correctamente.", "datos": { "id_categoria": 13 } }`

| Código | Mensaje |
|---|---|
| 400 | El nombre de la categoría es obligatorio. |
| 401/403 | Debe iniciar sesión / No tiene permisos de administrador para realizar esta acción. |
| 409 | Ya existe una categoría con ese nombre. |

#### Actualizar — `PUT api/categorias.php?id=6`

Requiere sesión de tipo `administrador`. Mismo cuerpo que `crear`.

Respuesta `200`: `{ "exito": true, "mensaje": "Categoría actualizada correctamente.", "datos": null }`

Mismos códigos que `crear`, más `404 Categoría no encontrada.`

#### Eliminar — `DELETE api/categorias.php?id=6`

Requiere sesión de tipo `administrador`. Si la categoría tiene libros asociados, la FK `RESTRICT` de `productos` impide el borrado.

Respuesta `200`: `{ "exito": true, "mensaje": "Categoría eliminada correctamente.", "datos": null }`

| Código | Mensaje |
|---|---|
| 400 | El id de la categoría no es válido. |
| 404 | Categoría no encontrada. |
| 409 | No se puede eliminar: hay libros asociados a esta categoría. |

---

### `api/wishlist.php` ✅

Controlador: `app/controladores/WishlistController.php` · Modelos: `app/modelos/Wishlist.php` y `app/modelos/Libro.php` (para validar el libro antes de agregarlo).

**Requiere sesión en todas sus acciones** (`FiltroAutenticacion::protegerApi()`).

| Método | Acceso | Descripción |
|---|---|---|
| `GET` | Cliente | Mi lista de deseos |
| `POST` | Cliente | Agregar un libro a la lista |
| `DELETE` con `?id_producto=` | Cliente | Quitar un libro de la lista |

#### Mi lista — `GET api/wishlist.php`

Respuesta `200`:

```json
{
  "exito": true,
  "mensaje": "Lista de deseos obtenida correctamente.",
  "datos": [
    { "id_wishlist": 4, "id_usuario": 2, "id_producto": 5, "fecha_agregado": "2026-09-20 12:00:00",
      "nombre": "Dune", "autor": "Frank Herbert", "precio": "210.00", "imagen": null, "cantidad": 9, "estado": "activo" }
  ]
}
```

#### Agregar — `POST api/wishlist.php`

Cuerpo: `{ "id_producto": 5 }`

Respuesta `201`: `{ "exito": true, "mensaje": "Libro agregado a la lista de deseos.", "datos": { "id_wishlist": 9 } }`

| Código | Mensaje |
|---|---|
| 400 | Debe indicar un id_producto válido. |
| 400 | El libro no está disponible. |
| 401 | Debe iniciar sesión para acceder a este recurso. |
| 409 | Este libro ya está en tu lista de deseos. |

#### Quitar — `DELETE api/wishlist.php?id_producto=5`

Se identifica por `id_producto` (no por `id_wishlist`), porque es el dato que el frontend normalmente tiene a mano en catálogo/detalle.

Respuesta `200`: `{ "exito": true, "mensaje": "Libro eliminado de la lista de deseos.", "datos": null }`

| Código | Mensaje |
|---|---|
| 400 | Debe indicar un id_producto válido. |
| 401 | Debe iniciar sesión para acceder a este recurso. |

---

### `api/resenas.php` ✅

Controlador: `app/controladores/ResenaController.php` · Modelos: `app/modelos/Resena.php` y `app/modelos/Libro.php`.

| Método | Acceso | Descripción |
|---|---|---|
| `GET` con `?id_producto=` | Público | Reseñas de un libro |
| `POST` | Cliente | Crea una reseña |
| `PUT` con `?id=` | Cliente (dueño) | Edita su propia reseña |
| `DELETE` con `?id=` | Cliente (dueño) / administrador | Elimina una reseña |

#### Listado por libro — `GET api/resenas.php?id_producto=5`

Respuesta `200`:

```json
{
  "exito": true,
  "mensaje": "Reseñas obtenidas correctamente.",
  "datos": [
    { "id_resena": 3, "id_usuario": 2, "id_producto": 5, "calificacion": 5,
      "comentario": "Excelente libro", "fecha": "2026-09-18 09:00:00",
      "nombre": "Ana", "apellido": "Morales" }
  ]
}
```

| Código | Mensaje |
|---|---|
| 400 | Debe indicar un id_producto válido. |

#### Crear — `POST api/resenas.php`

Requiere sesión. Cuerpo:

| Campo | Tipo | Obligatorio | Regla |
|---|---|---|---|
| `id_producto` | int | Sí | Debe existir y estar `activo` |
| `calificacion` | int | Sí | Entero entre 1 y 5 |
| `comentario` | string | No | — |

Respuesta `201`: `{ "exito": true, "mensaje": "Reseña creada correctamente.", "datos": { "id_resena": 11 } }`

| Código | Mensaje |
|---|---|
| 400 | Debe indicar un id_producto válido. |
| 400 | La calificación debe ser un número entero entre 1 y 5. |
| 400 | El libro no está disponible. |
| 401 | Debe iniciar sesión para acceder a este recurso. |

**Nota:** cada usuario puede tener una sola reseña por libro (restricción UNIQUE); para cambiarla se usa PUT. No se exige compra previa: regla abierta.

#### Actualizar — `PUT api/resenas.php?id=11`

Requiere sesión y ser el dueño de la reseña. Mismo cuerpo (`calificacion`, `comentario`) que `crear`, sin `id_producto`.

Respuesta `200`: `{ "exito": true, "mensaje": "Reseña actualizada correctamente.", "datos": null }`

| Código | Mensaje |
|---|---|
| 400 | El id de la reseña no es válido. |
| 400 | La calificación debe ser un número entero entre 1 y 5. |
| 403 | No tiene permisos para editar esta reseña. |
| 404 | Reseña no encontrada. |

#### Eliminar — `DELETE api/resenas.php?id=11`

Requiere sesión. El dueño puede eliminar la suya; un administrador puede eliminar cualquiera (moderación de contenido).

Respuesta `200`: `{ "exito": true, "mensaje": "Reseña eliminada correctamente.", "datos": null }`

| Código | Mensaje |
|---|---|
| 400 | El id de la reseña no es válido. |
| 403 | No tiene permisos para eliminar esta reseña. |
| 404 | Reseña no encontrada. |

---

### `api/usuarios.php` ✅

Controlador: `app/controladores/UsuarioController.php` · Modelo: `app/modelos/Usuario.php`.

**No maneja correo, contraseña ni `tipo_usuario`** — cambiarlos queda fuera de alcance (junto con RF03, recuperar contraseña).

| Método | `accion` | Acceso | Descripción |
|---|---|---|---|
| `GET` | *(ninguna)* | Cliente / admin | Perfil propio |
| `GET` | `admin-listado` | Administrador | Listado completo de usuarios |
| `GET` con `?id=` | *(ninguna)* | Administrador | Un usuario puntual |
| `PUT` | *(ninguna)* | Cliente / admin | Actualiza el perfil propio |
| `PUT` con `?id=` | *(ninguna)* | Administrador | Actualiza el perfil de cualquier usuario |
| `DELETE` con `?id=` | *(ninguna)* | Administrador | Elimina un usuario |

#### Perfil propio — `GET api/usuarios.php`

Requiere sesión. Respuesta `200`: el usuario (sin `password`), igual forma que `Usuario::obtenerPorId()`.

#### Actualizar perfil propio — `PUT api/usuarios.php`

Requiere sesión. Cuerpo:

| Campo | Tipo | Obligatorio | Regla |
|---|---|---|---|
| `nombre` | string | Sí | No vacío |
| `apellido` | string | Sí | No vacío |
| `telefono` | string | No | — |
| `direccion` | string | No | — |

Respuesta `200`: `{ "exito": true, "mensaje": "Perfil actualizado correctamente.", "datos": null }`

| Código | Mensaje |
|---|---|
| 400 | Nombre y apellido son obligatorios. |
| 401 | Debe iniciar sesión para acceder a este recurso. |

#### Listado administrativo — `GET api/usuarios.php?accion=admin-listado`

Requiere sesión de tipo `administrador`.

| Código | Mensaje |
|---|---|
| 403 | No tiene permisos de administrador para realizar esta acción. |

#### Detalle administrativo — `GET api/usuarios.php?id=3`

Requiere sesión de tipo `administrador`.

| Código | Mensaje |
|---|---|
| 400 | El id del usuario no es válido. |
| 404 | Usuario no encontrado. |

#### Actualizar (admin) — `PUT api/usuarios.php?id=3`

Requiere sesión de tipo `administrador`. Mismo cuerpo que la actualización de perfil propio.

Respuesta `200`: `{ "exito": true, "mensaje": "Usuario actualizado correctamente.", "datos": null }`

Mismos códigos que el perfil propio, más `404 Usuario no encontrado.`

#### Eliminar — `DELETE api/usuarios.php?id=3`

Requiere sesión de tipo `administrador`. No permite que un administrador elimine su propia cuenta. Un usuario con pedidos registrados no se puede eliminar (FK `RESTRICT` de `pedidos` hacia `usuarios`); sus reseñas y wishlist sí se eliminarían en cascada si la operación fuera posible.

Respuesta `200`: `{ "exito": true, "mensaje": "Usuario eliminado correctamente.", "datos": null }`

| Código | Mensaje |
|---|---|
| 400 | El id del usuario no es válido. |
| 400 | No puede eliminar su propia cuenta desde este panel. |
| 404 | Usuario no encontrado. |
| 409 | No se puede eliminar: el usuario tiene pedidos registrados. |

---

## 3. Fuera de alcance

| Funcionalidad | Motivo |
|---|---|
| RF03 — Recuperar contraseña | Decisión del equipo: queda fuera de alcance del proyecto |
| RF20 — Confirmación de pedido por correo | Decisión del equipo: solo se implementa la confirmación en pantalla (ya cubierta por la respuesta de `POST api/pedidos.php`); el envío de correo queda fuera de alcance |

Con esto, **todos los endpoints planeados originalmente en el Product Backlog están implementados**; no queda ningún endpoint en estado 📝.

---

## 4. Registro de cambios

| Fecha | Endpoint | Cambio |
|---|---|---|
| 24/09/2026 | `api/auth.php` | Documentado (`registro`, `login`, `logout`, `verificar-sesion`) |
| 24/09/2026 | `api/libros.php` | Documentado: catálogo con búsqueda/filtros, detalle, crear, actualizar, dar de baja (soft delete), reactivar y listado admin |
| 24/09/2026 | *(general)* | Agregado `includes/filtros/FiltroAutenticacion.php`: convención de 401 (sin sesión) / 403 (sin rol admin) para futuros endpoints protegidos |
| 25/09/2026 | `api/pedidos.php` | Documentado: creación transaccional desde el carrito, historial propio, listado admin, detalle con líneas, cambio de estado |
| 26/09/2026 | `api/categorias.php` | Documentado: listado y detalle públicos, CRUD administrador con manejo de FK `RESTRICT` |
| 26/09/2026 | `api/wishlist.php` | Documentado: mi lista, agregar, quitar por `id_producto` |
| 26/09/2026 | `api/resenas.php` | Documentado: listado por libro, crear, editar (dueño), eliminar (dueño o admin) |
| 26/09/2026 | `api/usuarios.php` | Documentado: perfil propio, gestión administrativa (listado, detalle, editar, eliminar) |
| 26/09/2026 | *(general)* | RF03 y confirmación de pedido por correo (parte de RF20) marcados como fuera de alcance del proyecto |
| 06/10/2026 | `api/libros.php` | Nuevo `POST ?accion=portada&id=#` para cambiar la portada; el orden `recientes` desempata por `id_producto`; se valida con `getimagesize()` que el archivo sea una imagen |
| 06/10/2026 | `api/wishlist.php` | `GET` ahora incluye `cantidad` y `estado` del libro; `POST` devuelve el `id_wishlist` real (antes devolvía `true`) |
| 06/10/2026 | `api/pedidos.php` | El `total` se redondea a 2 decimales; un error de base de datos responde `500` con mensaje genérico en lugar del texto de PDO |
| 06/10/2026 | `api/pedidos.php` | Cancelar un pedido devuelve el stock (transacción); un pedido `cancelado` ya no puede cambiar de estado (`409`) |
| 06/10/2026 | *(general)* | Si falla la conexión a la BD, ahora se responde JSON `500` ("No se pudo conectar con la base de datos.") y el detalle se registra con `error_log` |

---

## 5. Plantilla para documentar un endpoint nuevo

```markdown
### `METODO api/recurso.php`  Estado: 📝 / 🚧 / ✅
Acceso: público | cliente | administrador
Parámetros (query): ...
Cuerpo (JSON): campo (tipo, obligatorio, regla)
Respuesta 200/201: ejemplo real con datos del seed
Errores: código | mensaje
Notas: reglas de negocio relevantes para el frontend
```