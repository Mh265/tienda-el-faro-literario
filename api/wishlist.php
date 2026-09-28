<?php
// API de wishlist.
require_once __DIR__ . '/../includes/config/BaseDatos.php';
require_once __DIR__ . '/../includes/ayudantes/Respuesta.php';
require_once __DIR__ . '/../includes/ayudantes/AyudanteSesion.php';
require_once __DIR__ . '/../includes/filtros/FiltroAutenticacion.php';
require_once __DIR__ . '/../app/modelos/Wishlist.php';
require_once __DIR__ . '/../app/modelos/Libro.php';
require_once __DIR__ . '/../app/controladores/WishlistController.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$datos = json_decode(file_get_contents('php://input'), true) ?? [];
$idProducto = $_GET['id_producto'] ?? null;

switch ($metodo) {
    case 'GET':
        WishlistController::listar();
        break;

    case 'POST':
        WishlistController::agregar($datos);
        break;

    case 'DELETE':
        if ($idProducto === null) {
            Respuesta::error('Debe indicar el id_producto a eliminar.', 400);
        }
        WishlistController::eliminar($idProducto);
        break;

    default:
        Respuesta::error('Método no soportado. Use GET, POST o DELETE.', 405);
}
?>