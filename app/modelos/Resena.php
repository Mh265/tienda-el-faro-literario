<?php
// Modelo de reseñas.
class Resena
{
    // Reseñas de un libro.
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
    // Obtener reseña por ID.
    public static function obtenerPorId($id_resena)
    {
        $conexion = BaseDatos::conectar();
        $sql = "SELECT * FROM resenas WHERE id_resena = :id_resena";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_resena", $id_resena, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Verificar si el usuario ya reseñó este libro.
    public static function obtenerPorUsuarioYProducto($id_usuario, $id_producto)
    {
        $conexion = BaseDatos::conectar();
        $sql = "SELECT * FROM resenas
                WHERE id_usuario = :id_usuario AND id_producto = :id_producto";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(":id_producto", $id_producto, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear reseña.
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

    // Actualizar reseña.
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
    // Eliminar reseña.
    public static function eliminar($id_resena)
    {
        $conexion = BaseDatos::conectar();
        $sql = "DELETE FROM resenas WHERE id_resena = :id_resena";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_resena", $id_resena, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>