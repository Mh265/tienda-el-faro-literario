<?php
/**
 * Punto de entrada de la API para el catálogo de libros. Enruta por método
 * HTTP (GET/POST/PUT/DELETE) y, dentro de GET/PUT, por el parámetro
 * `accion` para los casos que no encajan en un CRUD simple
 * (admin-listado, reactivar).
 */
require_once __DIR__ . '/../includes/config/BaseDatos.php';
require_once __DIR__ . '/../includes/ayudantes/Respuesta.php';
require_once __DIR__ . '/../includes/ayudantes/AyudanteSesion.php';
require_once __DIR__ . '/../app/modelos/Libro.php';
require_once __DIR__ . '/../app/modelos/Categoria.php';
require_once __DIR__ . '/../app/controladores/LibroController.php';

$metodo = $_SERVER['REQUEST_METHOD'];
// El body JSON solo aplica a POST/PUT; en GET/DELETE puede venir vacío.
$datos = json_decode(file_get_contents('php://input'), true) ?? [];
$accion = $_GET['accion'] ?? ($datos['accion'] ?? '');
$id = $_GET['id'] ?? null;

switch ($metodo) {
    case 'GET':
        if ($id !== null) {
            LibroController::detalle($id);
        } elseif ($accion === 'admin-listado') {
            LibroController::listarAdmin();
        } else {
            // Filtros de catálogo, todos opcionales: q, id_categoria,
            // precio_min, precio_max, disponible, orden.
            LibroController::listar($_GET);
        }
        break;

    case 'POST':
        LibroController::crear($datos);
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
        // Nunca es un DELETE físico: internamente marca estado = 'inactivo'.
        LibroController::darDeBaja($id);
        break;

    default:
        Respuesta::error('Método no soportado. Use GET, POST, PUT o DELETE.', 405);
}
?>