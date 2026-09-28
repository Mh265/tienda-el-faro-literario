<?php
// API de autenticación.
require_once __DIR__ . '/../includes/config/BaseDatos.php';
require_once __DIR__ . '/../includes/ayudantes/Respuesta.php';
require_once __DIR__ . '/../includes/ayudantes/AyudanteSesion.php';
require_once __DIR__ . '/../app/modelos/Usuario.php';
require_once __DIR__ . '/../app/controladores/AuthController.php';

// El frontend envía JSON en el body.
$datos = json_decode(file_get_contents('php://input'), true) ?? [];
$accion = $_GET['accion'] ?? ($datos['accion'] ?? '');

switch ($accion) {
    case 'registro':
        AuthController::registrar($datos);
        break;

    case 'login':
        AuthController::login($datos);
        break;

    case 'logout':
        AuthController::logout();
        break;

    case 'verificar-sesion':
        AuthController::verificarSesion();
        break;

    default:
        Respuesta::error('Acción no reconocida. Use: registro, login, logout o verificar-sesion.', 404);
}
?>