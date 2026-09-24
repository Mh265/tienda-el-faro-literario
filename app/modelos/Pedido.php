<?php
/**
 * Pedido.php
 * Modelo para la tabla `pedidos` (cabecera de cada compra).
 */
class Pedido
{
    //Devuelve el historial global de todas las compras de la tienda
    public static function obtenerTodos()
    {
        $conexion = BaseDatos::conectar();
        $sql = "SELECT * FROM pedidos ORDER BY fecha DESC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //Recupera la información general de una orden específica mediante su ID.
    public static function obtenerPorId($id_pedido)
    {
        $conexion = BaseDatos::conectar();
        $sql = "SELECT * FROM pedidos WHERE id_pedido = :id_pedido";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_pedido", $id_pedido, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Consulta adicional, útil para la vista "Mis pedidos" del cliente.
    public static function obtenerPorUsuario($id_usuario)
    {
        $conexion = BaseDatos::conectar();
        $sql = "SELECT * FROM pedidos WHERE id_usuario = :id_usuario ORDER BY fecha DESC";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    //Registra una nueva compra en la base de datos. Por defecto le asigna el estado 'pendiente'
    public static function crear($id_usuario, $total, $estado = 'pendiente')
    {
        $conexion = BaseDatos::conectar();
        $sql = "INSERT INTO pedidos (id_usuario, total, estado) VALUES (:id_usuario, :total, :estado)";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $stmt->bindParam(":total", $total);
        $stmt->bindParam(":estado", $estado);
        $stmt->execute();
        return $conexion->lastInsertId();
    }

    // Actualiza total y estado. El cambio de estado (pendiente -> pagado ->
    // enviado -> entregado / cancelado) se decide en el Controlador; aquí
    // solo se persiste el valor que ya viene validado.
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

    //Borra el registro del pedido de la base de datos según su ID.
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
