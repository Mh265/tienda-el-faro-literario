<?php
// Controlador de pedidos.
class PedidoController
{
    private static $estadosValidos = ['pendiente', 'pagado', 'enviado', 'entregado', 'cancelado'];

    // Crear pedido.
    public static function crear($datos)
    {
        FiltroAutenticacion::protegerApi();

        $items = $datos['items'] ?? [];
        if (!is_array($items) || count($items) === 0) {
            Respuesta::error('El pedido debe incluir al menos un libro.', 400);
        }

        foreach ($items as $item) {
            $idProducto = $item['id_producto'] ?? null;
            $cantidad = $item['cantidad'] ?? null;
            if (!ctype_digit((string) $idProducto) || !ctype_digit((string) $cantidad) || (int) $cantidad < 1) {
                Respuesta::error('Cada línea del pedido debe traer id_producto y cantidad (entero mayor a 0).', 400);
            }
        }

        $idUsuario = AyudanteSesion::obtenerUsuarioSesion()['id_usuario'];
        $conexion = BaseDatos::conectar();

        try {
            $conexion->beginTransaction();

            $total = 0;
            $lineas = [];

            foreach ($items as $item) {
                $idProducto = (int) $item['id_producto'];
                $cantidad = (int) $item['cantidad'];

                $libro = Libro::obtenerPorId($idProducto, $conexion);

                if (!$libro || $libro['estado'] !== 'activo') {
                    throw new Exception("El libro con id $idProducto no está disponible.");
                }

                if ($libro['cantidad'] < $cantidad) {
                    throw new Exception("Stock insuficiente para \"{$libro['nombre']}\". Disponible: {$libro['cantidad']}.");
                }

                $precioUnitario = $libro['precio'];
                $total += $precioUnitario * $cantidad;

                $lineas[] = [
                    'id_producto' => $idProducto,
                    'cantidad'    => $cantidad,
                    'precio'      => $precioUnitario
                ];
            }

            // Evita decimales sobrantes por la multiplicación de precios.
            $total = round($total, 2);

            $idPedido = Pedido::crear($idUsuario, $total, 'pendiente', $conexion);

            foreach ($lineas as $linea) {
                DetallePedido::crear($idPedido, $linea['id_producto'], $linea['cantidad'], $linea['precio'], $conexion);

                $descontado = Libro::descontarStock($linea['id_producto'], $linea['cantidad'], $conexion);
                if (!$descontado) {
                    throw new Exception('El stock cambió mientras se procesaba el pedido. Intenta de nuevo.');
                }
            }

            $conexion->commit();

            Respuesta::exito('Pedido creado correctamente.', [
                'id_pedido' => $idPedido,
                'total'     => $total
            ], 201);

        } catch (PDOException $e) {
            // Error de base de datos: se registra, pero no se muestra al usuario.
            // (PDOException va primero porque también es un Exception.)
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            error_log('Error al crear pedido: ' . $e->getMessage());
            Respuesta::error('No se pudo crear el pedido, intenta de nuevo.', 500);
        } catch (Exception $e) {
            // Reglas de negocio (libro no disponible, stock insuficiente...).
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            Respuesta::error($e->getMessage(), 400);
        }
    }

    // Historial del usuario autenticado.
    public static function misPedidos()
    {
        FiltroAutenticacion::protegerApi();
        $idUsuario = AyudanteSesion::obtenerUsuarioSesion()['id_usuario'];

        $pedidos = Pedido::obtenerPorUsuario($idUsuario);
        Respuesta::exito('Historial de pedidos obtenido correctamente.', $pedidos);
    }

    // Listado administrativo.
    public static function listarTodos()
    {
        FiltroAutenticacion::protegerApiAdministrador();

        $pedidos = Pedido::obtenerTodos();
        Respuesta::exito('Listado de pedidos obtenido correctamente.', $pedidos);
    }

    // Detalle del pedido.
    public static function detalle($id_pedido)
    {
        FiltroAutenticacion::protegerApi();

        if (!ctype_digit((string) $id_pedido)) {
            Respuesta::error('El id del pedido no es válido.', 400);
        }

        $pedido = Pedido::obtenerPorId($id_pedido);
        if (!$pedido) {
            Respuesta::error('Pedido no encontrado.', 404);
        }

        $usuarioSesion = AyudanteSesion::obtenerUsuarioSesion();
        $esDueno = (int) $pedido['id_usuario'] === (int) $usuarioSesion['id_usuario'];

        if (!$esDueno && !AyudanteSesion::esAdministrador()) {
            Respuesta::error('Pedido no encontrado.', 404);
        }

        $pedido['lineas'] = DetallePedido::obtenerPorPedido($id_pedido);

        Respuesta::exito('Pedido encontrado.', $pedido);
    }

    // Cambiar estado del pedido.
    public static function cambiarEstado($id_pedido, $datos)
    {
        FiltroAutenticacion::protegerApiAdministrador();

        if (!ctype_digit((string) $id_pedido)) {
            Respuesta::error('El id del pedido no es válido.', 400);
        }

        $estadoNuevo = $datos['estado'] ?? '';
        if (!in_array($estadoNuevo, self::$estadosValidos, true)) {
            Respuesta::error('Estado no válido. Use: ' . implode(', ', self::$estadosValidos) . '.', 400);
        }

        $pedido = Pedido::obtenerPorId($id_pedido);
        if (!$pedido) {
            Respuesta::error('Pedido no encontrado.', 404);
        }

        Pedido::actualizar($id_pedido, $pedido['total'], $estadoNuevo);

        Respuesta::exito('Estado del pedido actualizado correctamente.');
    }
}
?>