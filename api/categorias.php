<?php
// API de categorías.
require_once __DIR__ . '/../includes/config/BaseDatos.php';
require_once __DIR__ . '/../includes/ayudantes/Respuesta.php';
require_once __DIR__ . '/../includes/ayudantes/AyudanteSesion.php';
require_once __DIR__ . '/../includes/filtros/FiltroAutenticacion.php';
require_once __DIR__ . '/../app/modelos/Categoria.php';
require_once __DIR__ . '/../app/controladores/CategoriaController.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$datos = json_decode(file_get_contents('php://input'), true) ?? [];
$id = $_GET['id'] ?? null;

switch ($metodo) {
    case 'GET':
        if ($id !== null) {
            CategoriaController::detalle($id);
        } else {
            CategoriaController::listar();
        }
        break;

    case 'POST':
        CategoriaController::crear($datos);
        break;

    case 'PUT':
        if ($id === null) {
            Respuesta::error('Debe indicar el id de la categoría a actualizar.', 400);
        }
        CategoriaController::actualizar($id, $datos);
        break;

    case 'DELETE':
        if ($id === null) {
            Respuesta::error('Debe indicar el id de la categoría a eliminar.', 400);
        }
        CategoriaController::eliminar($id);
        break;

    default:
        Respuesta::error('Método no soportado. Use GET, POST, PUT o DELETE.', 405);
}
?>