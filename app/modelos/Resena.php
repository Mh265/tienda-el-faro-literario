<?php
/**
 * Resena.php
 * Modelo para la tabla `resenas` (calificación 1-5 y comentario de un
 * usuario sobre un libro).
 */
class Resena
{
    // Trae las reseñas de UN libro (uso típico: detalle-libro.php),
    // con nombre y apellido del autor de la reseña vía JOIN a usuarios.
    public static function obtenerPorProducto($id_producto)
    {
        $conexion = BaseDatos::conectar();
        $sql = "SELECT r.*, u.nombre, u.apellido
                FROM resenas r
                INNER JOIN usuarios u ON u.id_usuario = r.id_usuario
                WHERE r.id_producto = :id_producto
                ORDER BY r.fecha DESC";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_producto", $id_producto, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    //Busca los datos de una sola reseña por su ID
    public static function obtenerPorId($id_resena)
    {
        $conexion = BaseDatos::conectar();
        $sql = "SELECT * FROM resenas WHERE id_resena = :id_resena";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_resena", $id_resena, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //Registra una nueva valoración asociada a un usuario y a un producto
    public static function crear($id_usuario, $id_producto, $calificacion, $comentario)
    {
        $conexion = BaseDatos::conectar();
        $sql = "INSERT INTO resenas (id_usuario, id_producto, calificacion, comentario)
                VALUES (:id_usuario, :id_producto, :calificacion, :comentario)";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(":id_producto", $id_producto, PDO::PARAM_INT);
        $stmt->bindParam(":calificacion", $calificacion, PDO::PARAM_INT);
        $stmt->bindParam(":comentario", $comentario);
        $stmt->execute();
        return $conexion->lastInsertId();
    }

    // Permite que el propio usuario edite su reseña (calificación/comentario).
    // La validación de que sea el dueño de la reseña quien la edite es del
    // Controlador (compara id_usuario de sesión contra la reseña).
    public static function actualizar($id_resena, $calificacion, $comentario)
    {
        $conexion = BaseDatos::conectar();
        $sql = "UPDATE resenas
                SET calificacion = :calificacion, comentario = :comentario
                WHERE id_resena = :id_resena";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":calificacion", $calificacion, PDO::PARAM_INT);
        $stmt->bindParam(":comentario", $comentario);
        $stmt->bindParam(":id_resena", $id_resena, PDO::PARAM_INT);
        return $stmt->execute();
    }
    //Borra una reseña de la base de datos
    public static function eliminar($id_resena)
    {
        $conexion = BaseDatos::conectar();
        $sql = "DELETE FROM resenas WHERE id_resena = :id_resena";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_resena", $id_resena, PDO::PARAM_INT);
        return $stmt->execute();
    }
}