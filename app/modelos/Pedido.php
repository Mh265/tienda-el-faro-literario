<?php
// Modelo de pedidos.
class Pedido
{
    // Historial global.
    public static function obtenerTodos()
    {
        $conexion = BaseDatos::conectar();
        $sql = "SELECT * FROM pedidos ORDER BY fecha DESC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener pedido por ID.
    public static function obtenerPorId($id_pedido)
    {
        $conexion = BaseDatos::conectar();
        $sql = "SELECT * FROM pedidos WHERE id_pedido = :id_pedido";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_pedido", $id_pedido, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Pedidos del usuario.
    public static function obtenerPorUsuario($id_usuario)
    {
        $conexion = BaseDatos::conectar();
        $sql = "SELECT * FROM pedidos WHERE id_usuario = :id_usuario ORDER BY fecha DESC";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Crear pedido.
    public static function crear($id_usuario, $total, $estado = 'pendiente', $conexion = null)
    {
        $conexion = $conexion ?? BaseDatos::conectar();
        $sql = "INSERT INTO pedidos (id_usuario, total, estado) VALUES (:id_usuario, :total, :estado)";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(":total", $total);
        $stmt->bindParam(":estado", $estado);
        $stmt->execute();
        return $conexion->lastInsertId();
    }

    // Actualizar pedido.
    public static function actualizar($id_pedido, $total, $estado)
    {
        $conexion = BaseDatos::conectar();
        $sql = "UPDATE pedidos SET total = :total, estado = :estado WHERE id_pedido = :id_pedido";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":total", $total);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":id_pedido", $id_pedido, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Eliminar pedido.
    public static function eliminar($id_pedido)
    {
        $conexion = BaseDatos::conectar();
        $sql = "DELETE FROM pedidos WHERE id_pedido = :id_pedido";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_pedido", $id_pedido, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
