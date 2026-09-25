<!-- docs/frontend/frontend-api-cliente.md -->
# Feature/frontend-api-cliente

## Objetivo
Crear el único punto por el que todas las vistas de "El Faro Literario"
hablan con la API PHP: una función central en `assets/js/api.js` que
envuelve `fetch()`, normaliza la respuesta y nunca lanza una excepción
hacia quien la llama. Cubre RF19 (consultar información vía API).

## Archivo que agrega esta feature

| Archivo | Rol |
|---|---|
| `assets/js/api.js` | Expone `llamarApi(recurso, metodo, cuerpo)`, la única forma en que el resto de scripts (`catalogo.js`, `carrito.js`, `checkout.js`, `auth.js`, `admin.js`, etc.) debe consumir la API |

No se tocó `includes/plantillas/footer.php`: ya cargaba los scripts en el
orden correcto (`bootstrap.bundle.min.js` → `assets/js/api.js` → scripts
propios de cada vista vía `$scriptsPagina`).

## Cómo usarla

`api.js` se carga automáticamente en cada vista a través de
`footer.php`, así que `llamarApi()` está disponible sin necesidad de
ningún `<script src="...">` adicional ni `import`.

```js
// Ejemplo de uso en cualquier script de vista, ej. futuro auth.js
async function iniciarSesion(correo, password) {
    // api/auth.php → app/controladores/AuthController.php
    const resultado = await llamarApi('auth.php?accion=login', 'POST', {
        correo: correo,
        password: password
    });

    if (!resultado.exito) {
        mostrarError(resultado.mensaje); // función propia de cada vista
        return;
    }

    // resultado.datos trae al usuario (sin password)
    console.log(resultado.datos);
}
```

### Forma del objeto que siempre devuelve

```js
{
  exito: true | false,
  mensaje: "texto para mostrar al usuario",
  datos: objeto | arreglo | null,
  estado: 200 // código HTTP recibido, o 0 si no hubo respuesta de red
}
```

`llamarApi()` **nunca lanza** (`throw`): cualquier script que la use no
necesita su propio `try/catch`.

## Decisiones de arquitectura

- **Un solo punto de entrada, sin módulos ES.** Se declaró como función
  global (`async function llamarApi(...)`) para que cualquier script
  cargado después vía `<script>` normal pueda usarla, sin `import`
  ni `export`, siguiendo el mismo estilo simple del resto del proyecto.
- **`credentials: 'same-origin'` en cada `fetch()`**, para que la
  cookie de sesión nativa de PHP viaje siempre, incluso aunque
  frontend y API compartan el mismo origen (Apache/XAMPP).
- **Dos niveles de manejo de error:**
  1. `fetch()` puede *rechazar* (sin conexión, DNS, CORS): se captura
     con `try/catch` y se devuelve `estado: 0`.
  2. `response.json()` puede fallar si el cuerpo no es JSON válido —
     esto pasa cuando `BaseDatos.php` truena con `die()` (imprime texto
     plano) o cuando el endpoint no existe (el servidor devuelve HTML
     de error). En ambos casos se devuelve el mensaje genérico
     `"No se pudo completar la operación, intenta de nuevo."`.
- **`verificar-sesion` con 401 no se trata distinto dentro de `api.js`.**
  Es un estado normal (visitante sin sesión), pero la decisión de
  mostrarlo o no en pantalla es de quien llama a `llamarApi()`, no de
  este archivo — `api.js` solo informa el `estado` y el `mensaje` tal
  cual vienen del backend.
- **`recurso` ya incluye la query string completa.** `llamarApi()` no
  arma parámetros por su cuenta (`?q=`, `?id=`, etc.); cada script de
  vista construye el string según lo que necesite. Esto mantiene a
  `api.js` genérico y sin conocimiento de la forma de cada endpoint.
- **Nota sobre `die()` en `BaseDatos.php`:** como ese `die()` no fija un
  código HTTP antes de imprimir, el navegador recibe **200** aunque el
  contenido sea un mensaje de error plano. `api.js` igual lo detecta
  correctamente porque el fallo real está en que `response.json()` no
  puede parsear ese texto — pero el campo `estado` en ese caso puede
  decir `200` en vez de un código de error. Queda documentado por si en
  el futuro se decide que `BaseDatos.php` fije un `http_response_code(500)`
  antes del `die()`.

## Cómo probarlo

Ver la checklist completa de pruebas manuales entregada en el chat de
esta feature (12 casos: login correcto/incorrecto, registro duplicado,
verificar-sesion con y sin sesión, catálogo con filtros, endpoint
inexistente, backend caído, sin conexión, permisos insuficientes y
persistencia de la cookie de sesión).

## Pendiente para features futuras

- Cuando se implemente `Feature/frontend-auth`, ese script decidirá qué
  hacer con el 401 de `verificar-sesion` (mostrar "Iniciar sesión" en
  vez de un mensaje de error).
- Si el equipo decide que `BaseDatos.php` debe fijar un código HTTP de
  servidor (500) antes del `die()`, `api.js` no necesita cambios: ya
  maneja cualquier `estado` que llegue junto con un cuerpo no-JSON.