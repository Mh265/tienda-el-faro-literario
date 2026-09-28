<?php
// Ayudante para respuestas JSON.
class Respuesta
{
    // Respuesta exitosa.
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

    // Respuesta con error.
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