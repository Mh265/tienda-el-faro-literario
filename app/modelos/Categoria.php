<?php
/**
 * Categoria.php
 * Modelo para la tabla `categorias` (géneros literarios del catálogo).
 */
class Categoria
{
    //Listar categorías
    public static function obtenerTodos()
    {
        $conexion = BaseDatos::conectar();
        $sql = "SELECT * FROM categorias ORDER BY nombre ASC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //Lista datos de una categoría específica por su ID
    public static function obtenerPorId($id_categoria)
    {
        $conexion = BaseDatos::conectar();
        $sql = "SELECT * FROM categorias WHERE id_categoria = :id_categoria";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_categoria", $id_categoria, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //Inserta una nueva categoría en la tabla
    public static function crear($nombre, $descripcion)
    {
        $conexion = BaseDatos::conectar();
        $sql = "INSERT INTO categorias (nombre, descripcion) VALUES (:nombre, :descripcion)";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":descripcion", $descripcion);
        $stmt->execute();
        return $conexion->lastInsertId();
    }

    //Edita una entrada específica dentro de la tabla de categoría
    public static function actualizar($id_categoria, $nombre, $descripcion)
    {
        $conexion = BaseDatos::conectar();
        $sql = "UPDATE categorias
                SET nombre = :nombre, descripcion = :descripcion
                WHERE id_categoria = :id_categoria";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":descripcion", $descripcion);
        $stmt->bindParam(":id_categoria", $id_categoria, PDO::PARAM_INT);
        return $stmt->execute();
    }

    //Elimina el registro de una categoría de la base de datos basándose en su ID.
    public static function eliminar($id_categoria)
    {
        $conexion = BaseDatos::conectar();
        $sql = "DELETE FROM categorias WHERE id_categoria = :id_categoria";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_categoria", $id_categoria, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>