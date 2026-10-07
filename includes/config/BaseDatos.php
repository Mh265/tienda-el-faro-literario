<?php
// Conexión a la base de datos.
class BaseDatos
{
    public static function conectar()
    {
        $host = "localhost";
        $dbname = "tienda_el_faro";
        $usuario = "root";
        $password = "";

        try {
            $conexion = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $usuario,
                $password
            );
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conexion;
        } catch (PDOException $e) {
            // El detalle técnico va al log del servidor, no a la pantalla del usuario.
            error_log("Error de conexión: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            die(json_encode([
                'exito'   => false,
                'mensaje' => 'No se pudo conectar con la base de datos.'
            ]));
        }
    }
}