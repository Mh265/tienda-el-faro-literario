# Feature/auth-controller

## Objetivo
Implementar la autenticación de usuarios de "El Faro Literario": registro
de clientes, inicio de sesión, cierre de sesión y verificación de sesión
activa, expuestas como endpoints de la API consumibles vía `fetch()`.

## Archivos que agrega/completa esta feature

| Archivo | Rol |
|---|---|
| `includes/ayudantes/Respuesta.php` | Estandariza el formato JSON de salida de la API (`exito`/`mensaje`/`datos`) |
| `includes/ayudantes/AyudanteSesion.php` | Centraliza el manejo de `$_SESSION` (iniciar, cerrar, consultar usuario autenticado) |
| `app/controladores/AuthController.php` | Lógica de registro, login, logout y verificación de sesión |
| `api/auth.php` | Endpoint público que enruta las peticiones al `AuthController` |

## Decisiones de arquitectura

- **Respuesta.php y AyudanteSesion.php se implementaron junto con el
  Controller.** Estaban vacíos en el repositorio pero son un
  prerrequisito directo del `AuthController`; sin ellos no había forma
  de responder en JSON ni de guardar la sesión. Si el equipo ya tenía
  otra implementación planeada para estos dos archivos, avisen antes
  de integrarlos para no pisar trabajo existente.
- **El hash de la contraseña se genera en el Controller, no en el
  Modelo.** `Usuario::crear()` solo guarda el string que recibe; quién
  decide *cómo* se protege la contraseña (`password_hash`) es
  responsabilidad del Controller. Esto es consistente con lo que ya
  dice el comentario de `Usuario.php`.
- **En sesión solo se guardan los datos que hacen falta para
  identificar al usuario** (`id_usuario`, `nombre`, `apellido`,
  `correo`, `tipo_usuario`) — nunca la contraseña ni su hash.
- **Mensaje de error genérico en login.** Si el correo no existe o la
  contraseña es incorrecta, se responde siempre "Correo o contraseña
  incorrectos", sin decir cuál de los dos falló. Es una práctica básica
  para no facilitarle a un atacante averiguar qué correos están
  registrados.
- **`api/auth.php` enruta "a mano" con un `switch(accion)`.**
  `public/index.php` sigue vacío (sin front controller), así que se
  siguió el mismo patrón simple de un archivo por recurso dentro de
  `/api`, leyendo la acción desde `?accion=` o del body JSON. Si más
  adelante se decide centralizar el enrutamiento en
  `public/index.php`, este archivo se ajusta fácilmente.
- **Ningún patrón fuera de lo visto en clase.** No se usó inyección de
  dependencias, interfaces, ni un enrutador con clases — solo métodos
  estáticos, `require_once` directos y un `switch`, igual que el resto
  del proyecto.

## Endpoints

Todas las peticiones son `POST` con body JSON, respondidas también en
JSON con la forma `{ "exito": bool, "mensaje": string, "datos": ... }`.

| Endpoint | Body esperado | Descripción |
|---|---|---|
| `POST /api/auth.php?accion=registro` | `nombre, apellido, correo, password, telefono?, direccion?` | Crea un cliente nuevo (password mínimo 6 caracteres) |
| `POST /api/auth.php?accion=login` | `correo, password` | Verifica credenciales y abre sesión |
| `POST /api/auth.php?accion=logout` | *(vacío)* | Cierra la sesión activa |
| `POST /api/auth.php?accion=verificar-sesion` | *(vacío)* | Devuelve los datos del usuario si hay sesión activa |

## Pendiente para features futuras
- `FiltroAutenticacion.php` sigue vacío: se implementará como feature
  aparte para proteger las rutas de `app/vistas/admin/` y las acciones
  que requieran sesión (ej. crear pedido, dejar reseña).
- No se valida aquí el rol `administrador` para ninguna acción — ese
  filtro también queda para `FiltroAutenticacion.php`.