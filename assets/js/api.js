// Cliente único para la API.
async function llamarApi(recurso, metodo = 'GET', cuerpo = null) {
  const opciones = {
    method: metodo,
    // Envía la cookie de sesión.
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