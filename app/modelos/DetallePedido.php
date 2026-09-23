<?php
/**
 * DetallePedido.php
 * Modelo para la tabla `detalle_pedido` (líneas / renglones de un pedido:
 * qué producto, cuántas unidades y a qué precio se vendió).
 */
class DetallePedido
{   
    //Recupera todas las líneas de producto asociadas a un pedido específico,
    public static function obtenerPorPedido($id_pedido)
    {
        $conexion = BaseDatos::conectar();
        $sql = "SELECT dp.*, p.nombre, p.autor, p.imagen
                FROM detalle_pedido dp
                INNER JOIN productos p ON p.id_producto = dp.id_producto
                WHERE dp.id_pedido = :id_pedido";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_pedido", $id_pedido, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //Busca una línea específica por su ID único
    public static function obtenerPorId($id_detalle)
    {
        $conexion = BaseDatos::conectar();
        $sql = "SELECT * FROM detalle_pedido WHERE id_detalle = :id_detalle";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_detalle", $id_detalle, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //Inserta un nuevo renglón de producto asociado a la cabecera del pedido
    public static function crear($id_pedido, $id_producto, $cantidad, $precio)
    {
        $conexion = BaseDatos::conectar();
        $sql = "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio)
                VALUES (:id_pedido, :id_producto, :cantidad, :precio)";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_pedido", $id_pedido, PDO::PARAM_INT);
        $stmt->bindParam(":id_producto", $id_producto, PDO::PARAM_INT);
        $stmt->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
        $stmt->bindParam(":precio", $precio);
        $stmt->execute();
        return $conexion->lastInsertId();
    }

    //Elimina una línea específica del pedido
    public static function actualizar($id_detalle, $cantidad)
    {
        $conexion = BaseDatos::conectar();
        $sql = "UPDATE detalle_pedido SET cantidad = :cantidad WHERE id_detalle = :id_detalle";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
        $stmt->bindParam(":id_detalle", $id_detalle, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public static function eliminar($id_detalle)
    {
        $conexion = BaseDatos::conectar();
        $sql = "DELETE FROM detalle_pedido WHERE id_detalle = :id_detalle";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_detalle", $id_detalle, PDO::PARAM_INT);
        return $stmt->execute();
    }
}