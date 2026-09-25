<?php
/**
 * Controlador de pedidos. Usa los Modelos Pedido, DetallePedido y Libro.
 */
class PedidoController
{
    // Lista blanca de estados válidos: el valor de $datos['estado'] nunca
    // se guarda tal cual sin pasar por aquí.
    private static $estadosValidos = ['pendiente', 'pagado', 'enviado', 'entregado', 'cancelado'];

    // POST /api/pedidos.php -> crea un pedido desde el carrito.
    // El cliente solo envía id_producto y cantidad por línea; el precio y
    // el total se calculan aquí con los datos actuales de productos, nunca
    // con lo que mande el frontend.
    public static function crear($datos)
    {
        FiltroAutenticacion::protegerApi();

        $items = $datos['items'] ?? [];
        if (!is_array($items) || count($items) === 0) {
            Respuesta::error('El pedido debe incluir al menos un libro.', 400);
        }

        // Validación de forma básica antes de abrir la transacción, para no
        // empezarla con datos claramente inválidos.
        foreach ($items as $item) {
            $idProducto = $item['id_producto'] ?? null;
            $cantidad = $item['cantidad'] ?? null;
            if (!ctype_digit((string) $idProducto) || !ctype_digit((string) $cantidad) || (int) $cantidad < 1) {
                Respuesta::error('Cada línea del pedido debe traer id_producto y cantidad (entero mayor a 0).', 400);
            }
        }

        $idUsuario = AyudanteSesion::obtenerUsuarioSesion()['id_usuario'];

        // Una sola conexión para toda la operación: se pasa explícitamente a
        // cada Modelo porque, tal como quedaron en Feature/modelos-crud,
        // cada método abre su propia conexión con BaseDatos::conectar().
        // Sin pasarla, el beginTransaction()/commit() de aquí no tendría
        // ningún efecto sobre los INSERT que hacen los Modelos.
        $conexion = BaseDatos::conectar();

        try {
            $conexion->beginTransaction();

            $total = 0;
            $lineas = [];

            foreach ($items as $item) {
                $idProducto = (int) $item['id_producto'];
                $cantidad = (int) $item['cantidad'];

                // app/modelos/Libro.php
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

            // app/modelos/Pedido.php
            $idPedido = Pedido::crear($idUsuario, $total, 'pendiente', $conexion);

            foreach ($lineas as $linea) {
                // app/modelos/DetallePedido.php
                DetallePedido::crear($idPedido, $linea['id_producto'], $linea['cantidad'], $linea['precio'], $conexion);

                // app/modelos/Libro.php — descuento atómico; si el stock ya
                // no alcanza (venta concurrente entre la validación de
                // arriba y este UPDATE), se aborta todo el pedido.
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

        } catch (Exception $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            Respuesta::error($e->getMessage(), 400);
        }
    }

    // GET /api/pedidos.php -> historial del cliente autenticado.
    public static function misPedidos()
    {
        FiltroAutenticacion::protegerApi();
        $idUsuario = AyudanteSesion::obtenerUsuarioSesion()['id_usuario'];

        // app/modelos/Pedido.php
        $pedidos = Pedido::obtenerPorUsuario($idUsuario);
        Respuesta::exito('Historial de pedidos obtenido correctamente.', $pedidos);
    }

    // GET /api/pedidos.php?accion=admin-listado -> todos los pedidos (admin).
    public static function listarTodos()
    {
        FiltroAutenticacion::protegerApiAdministrador();

        // app/modelos/Pedido.php
        $pedidos = Pedido::obtenerTodos();
        Respuesta::exito('Listado de pedidos obtenido correctamente.', $pedidos);
    }

    // GET /api/pedidos.php?id=# -> detalle de un pedido con sus líneas.
    // Un cliente solo puede ver su propio pedido; un administrador puede ver
    // cualquiera.
    public static function detalle($id_pedido)
    {
        FiltroAutenticacion::protegerApi();

        if (!ctype_digit((string) $id_pedido)) {
            Respuesta::error('El id del pedido no es válido.', 400);
        }

        // app/modelos/Pedido.php
        $pedido = Pedido::obtenerPorId($id_pedido);
        if (!$pedido) {
            Respuesta::error('Pedido no encontrado.', 404);
        }

        $usuarioSesion = AyudanteSesion::obtenerUsuarioSesion();
        $esDueno = (int) $pedido['id_usuario'] === (int) $usuarioSesion['id_usuario'];

        if (!$esDueno && !AyudanteSesion::esAdministrador()) {
            // Mismo criterio que LibroController con libros inactivos: no
            // se revela que el pedido existe si no es del usuario ni admin.
            Respuesta::error('Pedido no encontrado.', 404);
        }

        // app/modelos/DetallePedido.php
        $pedido['lineas'] = DetallePedido::obtenerPorPedido($id_pedido);

        Respuesta::exito('Pedido encontrado.', $pedido);
    }

    // PUT /api/pedidos.php?id=# -> cambia el estado de un pedido. Solo admin.
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

        // app/modelos/Pedido.php
        $pedido = Pedido::obtenerPorId($id_pedido);
        if (!$pedido) {
            Respuesta::error('Pedido no encontrado.', 404);
        }

        // El total no cambia al actualizar el estado; se reenvía el mismo
        // porque Pedido::actualizar() exige ambos campos.
        Pedido::actualizar($id_pedido, $pedido['total'], $estadoNuevo);

        Respuesta::exito('Estado del pedido actualizado correctamente.');
    }
}
?>