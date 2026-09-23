<?php
/**
 * Wishlist.php
 */
class Wishlist
{
    // Trae los libros que un usuario marcó como favoritos, con datos
    // básicos del producto (JOIN) para pintarlos directamente en la vista.
    public static function obtenerPorUsuario($id_usuario)
    {
        $conexion = BaseDatos::conectar();
        $sql = "SELECT w.*, p.nombre, p.autor, p.precio, p.imagen
                FROM wishlist w
                INNER JOIN productos p ON p.id_producto = w.id_producto
                WHERE w.id_usuario = :id_usuario
                ORDER BY w.fecha_agregado DESC";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //Busca un registro específico por su ID primario interno
    public static function obtenerPorId($id_wishlist)
    {
        $conexion = BaseDatos::conectar();
        $sql = "SELECT * FROM wishlist WHERE id_wishlist = :id_wishlist";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_wishlist", $id_wishlist, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // La restricción UNIQUE (id_usuario, id_producto) en la tabla es la
    // que evita duplicados; si ya existe, PDO lanzará una excepción que
    // el Controlador deberá capturar y traducir a un mensaje amigable
    // ("ya está en tu lista de deseos").
    public static function crear($id_usuario, $id_producto)
    {
        $conexion = BaseDatos::conectar();
        $sql = "INSERT INTO wishlist (id_usuario, id_producto) VALUES (:id_usuario, :id_producto)";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(":id_producto", $id_producto, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    //Elimina una entrada de la lista usando su clave primaria
    public static function eliminar($id_wishlist)
    {
        $conexion = BaseDatos::conectar();
        $sql = "DELETE FROM wishlist WHERE id_wishlist = :id_wishlist";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_wishlist", $id_wishlist, PDO::PARAM_INT);
        return $stmt->execute();
    }

    //Elimina el registro buscando directamente el par usuario-producto
    public static function eliminarPorUsuarioYProducto($id_usuario, $id_producto)
    {
        $conexion = BaseDatos::conectar();
        $sql = "DELETE FROM wishlist WHERE id_usuario = :id_usuario AND id_producto = :id_producto";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(":id_producto", $id_producto, PDO::PARAM_INT);
        return $stmt->execute();
    }
}