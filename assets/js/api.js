// assets/js/api.js
/**
 * Cliente único de la API REST de "El Faro Literario".
 * Todas las vistas deben usar llamarApi() en vez de fetch() directo,
 * para que el manejo de JSON, errores y la cookie de sesión viva en
 * un solo lugar del proyecto.
 */

/**
 * Llama a un endpoint dentro de /api y normaliza la respuesta.
 * Nunca lanza (throw): cualquier falla de red, de parseo de JSON o
 * del servidor se traduce siempre al mismo objeto de retorno, para
 * que quien llama (catalogo.js, carrito.js, checkout.js, etc.) no
 * necesite su propio try/catch.
 *
 * @param {string} recurso - archivo dentro de /api, con su query string si aplica.
 *   Ej: "auth.php?accion=login", "libros.php?id=5". Quien llama arma el string completo.
 * @param {string} [metodo='GET'] - método HTTP: GET, POST, PUT o DELETE.
 * @param {Object|null} [cuerpo=null] - objeto a enviar como JSON en el body.
 *   Los nombres de campo deben ser snake_case, igual que las columnas de la BD.
 * @returns {Promise<{exito: boolean, mensaje: string, datos: any, estado: number}>}
 *
 * Ejemplo:
 *   // api/auth.php → app/controladores/AuthController.php
 *   const resultado = await llamarApi('auth.php?accion=login', 'POST', {
 *       correo: correo,
 *       password: password
 *   });
 *   if (!resultado.exito) {
 *       mostrarError(resultado.mensaje);
 *       return;
 *   }
 *   console.log(resultado.datos);
 */
async function llamarApi(recurso, metodo = 'GET', cuerpo = null) {
  const opciones = {
    method: metodo,
    // Envía la cookie de sesión de PHP con cada petición, incluso
    // aunque el frontend y la API compartan el mismo origen.
    credentials: 'same-origin',
    headers: {}
  };

  if (cuerpo !== null) {
    opciones.headers['Content-Type'] = 'application/json';
    opciones.body = JSON.stringify(cuerpo);
  }

  let respuesta;
  try {
    // api/{recurso} → el Controlador correspondiente según el recurso pedido
    // (AuthController, LibroController, CategoriaController, etc.)
    respuesta = await fetch(API_URL + recurso, opciones);
  } catch (errorRed) {
    // Ni siquiera se pudo contactar al servidor (sin conexión, DNS, CORS...).
    return {
      exito: false,
      mensaje: 'No se pudo conectar con el servidor. Revisa tu conexión e intenta de nuevo.',
      datos: null,
      estado: 0
    };
  }

  let cuerpoJson;
  try {
    cuerpoJson = await respuesta.json();
  } catch (errorJson) {
    // La respuesta no es JSON: típicamente BaseDatos.php falló con die()
    // y devolvió texto/HTML plano, o hubo una excepción de PDO sin capturar.
    return {
      exito: false,
      mensaje: 'No se pudo completar la operación, intenta de nuevo.',
      datos: null,
      estado: respuesta.status
    };
  }

  // Formato ya validado en docs/API.md: { exito, mensaje, datos } en éxito,
  // { exito, mensaje } (sin datos) en error. Se normaliza por si algún
  // endpoint futuro olvida enviar alguna clave.
  return {
    exito: cuerpoJson.exito === true,
    mensaje: cuerpoJson.mensaje ?? '',
    datos: cuerpoJson.datos ?? null,
    estado: respuesta.status
  };
}