<?php
/**
 * Respuesta.php
 * Ayudante para estandarizar las respuestas JSON que devuelve la API.
 */
class Respuesta
{
    // Respuesta de éxito. $datos es opcional (ej. logout no necesita
    // devolver datos, solo confirmar el mensaje).
    public static function exito($mensaje, $datos = null, $codigo = 200)
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'exito'   => true,
            'mensaje' => $mensaje,
            'datos'   => $datos
        ]);
        exit;
    }

    // Respuesta de error. Se corta la ejecución con exit para que el
    // Controlador no siga corriendo código después de reportar el error.
    public static function error($mensaje, $codigo = 400)
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'exito'   => false,
            'mensaje' => $mensaje
        ]);
        exit;
    }
}
?>