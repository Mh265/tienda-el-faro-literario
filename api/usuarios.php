<?php
// API de usuarios.
require_once __DIR__ . '/../includes/config/BaseDatos.php';
require_once __DIR__ . '/../includes/ayudantes/Respuesta.php';
require_once __DIR__ . '/../includes/ayudantes/AyudanteSesion.php';
require_once __DIR__ . '/../includes/filtros/FiltroAutenticacion.php';
require_once __DIR__ . '/../app/modelos/Usuario.php';
require_once __DIR__ . '/../app/controladores/UsuarioController.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$datos = json_decode(file_get_contents('php://input'), true) ?? [];
$accion = $_GET['accion'] ?? '';
$id = $_GET['id'] ?? null;

switch ($metodo) {
    case 'GET':
        if ($id !== null) {
            UsuarioController::detalleAdmin($id);
        } elseif ($accion === 'admin-listado') {
            UsuarioController::listarAdmin();
        } else {
            UsuarioController::perfilPropio();
        }
        break;

    case 'PUT':
        if ($id !== null) {
            UsuarioController::actualizarAdmin($id, $datos);
        } else {
            UsuarioController::actualizarPerfilPropio($datos);
        }
        break;

    case 'DELETE':
        if ($id === null) {
            Respuesta::error('Debe indicar el id del usuario a eliminar.', 400);
        }
        UsuarioController::eliminar($id);
        break;

    default:
        Respuesta::error('Método no soportado. Use GET, PUT o DELETE.', 405);
}
?>