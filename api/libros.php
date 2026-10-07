<?php
require_once __DIR__ . '/../includes/config/BaseDatos.php';
require_once __DIR__ . '/../includes/ayudantes/Respuesta.php';
require_once __DIR__ . '/../includes/ayudantes/AyudanteSesion.php';
require_once __DIR__ . '/../includes/ayudantes/AyudanteArchivo.php';
require_once __DIR__ . '/../app/modelos/Libro.php';
require_once __DIR__ . '/../app/modelos/Categoria.php';
require_once __DIR__ . '/../app/controladores/LibroController.php';

$metodo = $_SERVER['REQUEST_METHOD'];

// POST usa multipart/form-data para recibir la portada.
$datos = ($metodo === 'POST') ? [] : (json_decode(file_get_contents('php://input'), true) ?? []);
$accion = $_GET['accion'] ?? ($datos['accion'] ?? '');
$id = $_GET['id'] ?? null;

switch ($metodo) {
    case 'GET':
        if ($id !== null) {
            LibroController::detalle($id);
        } elseif ($accion === 'admin-listado') {
            LibroController::listarAdmin();
        } else {
            LibroController::listar($_GET);
        }
        break;

    case 'POST':
        // $_FILES['imagen'] no existe si el form no incluyó el campo file;
        // se pasa null en ese caso y AyudanteArchivo lo trata como "sin portada".
        if ($accion === 'portada') {
            if ($id === null) {
                Respuesta::error('Debe indicar el id del libro para cambiar su portada.', 400);
            }
            LibroController::actualizarPortada($id, $_FILES['imagen'] ?? null);
        } else {
            LibroController::crear($_POST, $_FILES['imagen'] ?? null);
        }
        break;

    case 'PUT':
        if ($id === null) {
            Respuesta::error('Debe indicar el id del libro a actualizar.', 400);
        }

        if ($accion === 'reactivar') {
            LibroController::reactivar($id);
        } else {
            LibroController::actualizar($id, $datos);
        }
        break;

    case 'DELETE':
        if ($id === null) {
            Respuesta::error('Debe indicar el id del libro a dar de baja.', 400);
        }
        LibroController::darDeBaja($id);
        break;

    default:
        Respuesta::error('Método no soportado. Use GET, POST, PUT o DELETE.', 405);
}
?>