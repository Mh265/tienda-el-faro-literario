<?php
// API de reseñas.
require_once __DIR__ . '/../includes/config/BaseDatos.php';
require_once __DIR__ . '/../includes/ayudantes/Respuesta.php';
require_once __DIR__ . '/../includes/ayudantes/AyudanteSesion.php';
require_once __DIR__ . '/../includes/filtros/FiltroAutenticacion.php';
require_once __DIR__ . '/../app/modelos/Resena.php';
require_once __DIR__ . '/../app/modelos/Libro.php';
require_once __DIR__ . '/../app/controladores/ResenaController.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$datos = json_decode(file_get_contents('php://input'), true) ?? [];
$id = $_GET['id'] ?? null;
$idProducto = $_GET['id_producto'] ?? null;

switch ($metodo) {
    case 'GET':
        if ($idProducto === null) {
            Respuesta::error('Debe indicar el id_producto para consultar sus reseñas.', 400);
        }
        ResenaController::listarPorProducto($idProducto);
        break;

    case 'POST':
        ResenaController::crear($datos);
        break;

    case 'PUT':
        if ($id === null) {
            Respuesta::error('Debe indicar el id de la reseña a actualizar.', 400);
        }
        ResenaController::actualizar($id, $datos);
        break;

    case 'DELETE':
        if ($id === null) {
            Respuesta::error('Debe indicar el id de la reseña a eliminar.', 400);
        }
        ResenaController::eliminar($id);
        break;

    default:
        Respuesta::error('Método no soportado. Use GET, POST, PUT o DELETE.', 405);
}
?>