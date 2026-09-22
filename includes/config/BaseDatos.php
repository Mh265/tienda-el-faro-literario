<?php
/**
 * BaseDatos.php
 * Clase encargada de abrir la conexión PDO hacia la base de datos
 * de "El Faro Literario".
 */
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
            die("Error de conexión: " . $e->getMessage());
        }
    }
}