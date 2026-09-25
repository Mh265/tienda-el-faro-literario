# API — El Faro Literario

Contrato entre Backend (Milton) y Frontend (Jenifer). Lo mantiene Milton y se actualiza en el mismo Pull Request de cada feature de backend.

**Última actualización:** 24/09/2026 · **Feature documentada:** `Feature/libro-api`

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
| Selección de operación | `auth.php` usa el parámetro `accion`. `libros.php` enruta principalmente por **método HTTP** (GET/POST/PUT/DELETE) y usa `accion` solo para los casos que no encajan en el CRUD estándar (`admin-listado`, `reactivar`) |

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
| 403 | Sesión activa pero sin permisos (no es administrador) |
| 404 | Acción no reconocida / recurso no encontrado |
| 405 | Método HTTP no soportado por el endpoint |
| 409 | Conflicto (correo ya registrado) |

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

**Nota sobre permisos:** como `Feature/filtro-autenticacion` todavía no se implementa, `LibroController` valida `AyudanteSesion::esAdministrador()` directamente en cada acción de escritura (crear, actualizar, dar de baja, reactivar, listado admin). Cuando se implemente el filtro de rutas, este chequeo puede quedarse igual (es una verificación de rol, no de rutas) o moverse — a decidir en esa feature.

| Método | `accion` | Acceso | Descripción |
|---|---|---|---|
| `GET` | *(ninguna)* | Público | Catálogo de libros con `estado='activo'`, con búsqueda y filtros |
| `GET` con `?id=` | *(ninguna)* | Público / admin | Detalle de un libro |
| `GET` | `admin-listado` | Administrador | Listado completo (activos e inactivos) para el panel admin |
| `POST` | *(ninguna)* | Administrador | Crea un libro nuevo |
| `PUT` con `?id=` | *(ninguna)* | Administrador | Actualiza los datos de un libro |
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

Requiere sesión de tipo `administrador`. Mismo cuerpo y mismas reglas que `crear`. No cambia `estado` (para eso están `reactivar` y el `DELETE`).

Respuesta `200`: `{ "exito": true, "mensaje": "Libro actualizado correctamente.", "datos": null }`

Mismos códigos de error que `crear`, más:

| Código | Mensaje |
|---|---|
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

## 3. Endpoints pendientes (📝 se especifican en su feature)

Cada uno se documenta aquí con parámetros, ejemplos y errores **antes** de implementarlo, para que el frontend pueda trabajar con datos simulados.

| Endpoint | Operaciones previstas | Acceso previsto | Feature backend |
|---|---|---|---|
| `api/categorias.php` | Listar, detalle, crear, actualizar, eliminar | Lectura pública · escritura administrador | `Feature/categoria-api` |
| `api/pedidos.php` | Crear desde el carrito, historial propio, todos (admin), detalle, cambiar estado | Cliente · administrador | `Feature/pedido-api` |
| `api/resenas.php` | Listar por libro, crear, editar, eliminar | Lectura pública · escritura cliente | `Feature/resena-api` |
| `api/wishlist.php` | Mi lista, agregar, quitar | Cliente | `Feature/wishlist-api` |
| `api/usuarios.php` | Perfil propio, listado y gestión (admin) | Cliente · administrador | `Feature/usuario-api` |

### Referencia: campos de un libro en la BD (tabla `productos`)

`id_producto`, `id_categoria`, `nombre` (título), `autor`, `editorial`, `descripcion_corta`, `descripcion_larga`, `precio`, `cantidad` (stock), `imagen`, `fecha_publicacion`, `estado` (`activo`/`inactivo`).

Regla acordada para el carrito: el frontend envía solo `id_producto` y `cantidad` al crear un pedido; el servidor calcula precios y total.

---

## 4. Registro de cambios

| Fecha | Endpoint | Cambio |
|---|---|---|
| 24/09/2026 | `api/auth.php` | Documentado (`registro`, `login`, `logout`, `verificar-sesion`) |
| 24/09/2026 | `api/libros.php` | Documentado: catálogo con búsqueda/filtros, detalle, crear, actualizar, dar de baja (soft delete), reactivar y listado admin |

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

| 24/09/2026 | *(general)* | Agregado `includes/filtros/FiltroAutenticacion.php`: convención de 401 (sin sesión) / 403 (sin rol admin) para futuros endpoints protegidos