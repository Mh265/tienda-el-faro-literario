# API — El Faro Literario

Contrato entre Backend (Milton) y Frontend (Jenifer). Lo mantiene Milton y se actualiza en el mismo Pull Request de cada feature de backend.

**Última actualización:** 23/09/2026 · **Feature documentada:** `Feature/auth-controller`

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
| Selección de operación | `auth.php` usa el parámetro `accion`, en la URL (`?accion=login`) o en el cuerpo JSON |

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
| 404 | Acción no reconocida |
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

## 3. Endpoints pendientes (📝 se especifican en su feature)

Cada uno se documenta aquí con parámetros, ejemplos y errores **antes** de implementarlo, para que el frontend pueda trabajar con datos simulados.

| Endpoint | Operaciones previstas | Acceso previsto | Feature backend |
|---|---|---|---|
| `api/libros.php` | Listar (búsqueda y filtros), detalle, crear, actualizar, eliminar/dar de baja | Lectura pública · escritura administrador | `Feature/libro-api` |
| `api/categorias.php` | Listar, detalle, crear, actualizar, eliminar | Lectura pública · escritura administrador | `Feature/categoria-api` |
| `api/pedidos.php` | Crear desde el carrito, historial propio, todos (admin), detalle, cambiar estado | Cliente · administrador | `Feature/pedido-api` |
| `api/resenas.php` | Listar por libro, crear, editar, eliminar | Lectura pública · escritura cliente | `Feature/resena-api` |
| `api/wishlist.php` | Mi lista, agregar, quitar | Cliente | `Feature/wishlist-api` |
| `api/usuarios.php` | Perfil propio, listado y gestión (admin) | Cliente · administrador | `Feature/usuario-api` |

### Referencia: campos de un libro en la BD (tabla `productos`)

El JSON final de `libros.php` se definirá en `Feature/libro-api`; mientras tanto el frontend puede simular datos con estos campos:

`id_producto`, `id_categoria`, `nombre` (título), `autor`, `editorial`, `descripcion_corta`, `descripcion_larga`, `precio`, `cantidad` (stock), `imagen`, `fecha_publicacion`, `estado` (`activo`/`inactivo`).

Regla acordada para el carrito: el frontend envía solo `id_producto` y `cantidad` al crear un pedido; el servidor calcula precios y total.

---

## 4. Registro de cambios

| Fecha | Endpoint | Cambio |
|---|---|---|
| 24/09/2026 | `api/auth.php` | Documentado (`registro`, `login`, `logout`, `verificar-sesion`) |

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