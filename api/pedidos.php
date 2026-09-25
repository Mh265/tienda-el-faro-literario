<?php
/**
 * Punto de entrada de la API para pedidos. Enruta por método HTTP, igual
 * que api/libros.php.
 */
require_once __DIR__ . '/../includes/config/BaseDatos.php';
require_once __DIR__ . '/../includes/ayudantes/Respuesta.php';
require_once __DIR__ . '/../includes/ayudantes/AyudanteSesion.php';
require_once __DIR__ . '/../includes/filtros/FiltroAutenticacion.php';
require_once __DIR__ . '/../app/modelos/Libro.php';
require_once __DIR__ . '/../app/modelos/Pedido.php';
require_once __DIR__ . '/../app/modelos/DetallePedido.php';
require_once __DIR__ . '/../app/controladores/PedidoController.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$datos = json_decode(file_get_contents('php://input'), true) ?? [];
$accion = $_GET['accion'] ?? '';
$id = $_GET['id'] ?? null;

switch ($metodo) {
    case 'GET':
        if ($id !== null) {
            PedidoController::detalle($id);
        } elseif ($accion === 'admin-listado') {
            PedidoController::listarTodos();
        } else {
            PedidoController::misPedidos();
        }
        break;

    case 'POST':
        PedidoController::crear($datos);
        break;

    case 'PUT':
        if ($id === null) {
            Respuesta::error('Debe indicar el id del pedido a actualizar.', 400);
        }
        PedidoController::cambiarEstado($id, $datos);
        break;

    default:
        Respuesta::error('Método no soportado. Use GET, POST o PUT.', 405);
}
?>