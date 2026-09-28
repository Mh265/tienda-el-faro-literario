<?php
// Controlador de categorías.
class CategoriaController
{
    // Listado público.
    public static function listar()
    {
        $categorias = Categoria::obtenerTodos();
        Respuesta::exito('Listado de categorías obtenido correctamente.', $categorias);
    }

    // Detalle por ID.
    public static function detalle($id_categoria)
    {
        if (!ctype_digit((string) $id_categoria)) {
            Respuesta::error('El id de la categoría no es válido.', 400);
        }

        $categoria = Categoria::obtenerPorId($id_categoria);

        if (!$categoria) {
            Respuesta::error('Categoría no encontrada.', 404);
        }

        Respuesta::exito('Categoría encontrada.', $categoria);
    }

    // Crear categoría.
    public static function crear($datos)
    {
        FiltroAutenticacion::protegerApiAdministrador();

        $campos = self::validarDatos($datos);
        if ($campos['error']) {
            Respuesta::error($campos['error'], 400);
        }

        try {
            $idCategoria = Categoria::crear($campos['nombre'], $campos['descripcion']);
        } catch (PDOException $e) {
            Respuesta::error('Ya existe una categoría con ese nombre.', 409);
        }

        Respuesta::exito('Categoría creada correctamente.', ['id_categoria' => $idCategoria], 201);
    }

    // Actualizar categoría.
    public static function actualizar($id_categoria, $datos)
    {
        FiltroAutenticacion::protegerApiAdministrador();

        if (!ctype_digit((string) $id_categoria)) {
            Respuesta::error('El id de la categoría no es válido.', 400);
        }

        if (!Categoria::obtenerPorId($id_categoria)) {
            Respuesta::error('Categoría no encontrada.', 404);
        }

        $campos = self::validarDatos($datos);
        if ($campos['error']) {
            Respuesta::error($campos['error'], 400);
        }

        try {
            Categoria::actualizar($id_categoria, $campos['nombre'], $campos['descripcion']);
        } catch (PDOException $e) {
            Respuesta::error('Ya existe una categoría con ese nombre.', 409);
        }

        Respuesta::exito('Categoría actualizada correctamente.');
    }

    // Eliminar categoría.
    public static function eliminar($id_categoria)
    {
        FiltroAutenticacion::protegerApiAdministrador();

        if (!ctype_digit((string) $id_categoria)) {
            Respuesta::error('El id de la categoría no es válido.', 400);
        }

        if (!Categoria::obtenerPorId($id_categoria)) {
            Respuesta::error('Categoría no encontrada.', 404);
        }

        try {
            Categoria::eliminar($id_categoria);
        } catch (PDOException $e) {
            Respuesta::error('No se puede eliminar: hay libros asociados a esta categoría.', 409);
        }

        Respuesta::exito('Categoría eliminada correctamente.');
    }

    // Validación de datos.
    private static function validarDatos($datos)
    {
        $nombre = trim($datos['nombre'] ?? '');
        $descripcion = trim($datos['descripcion'] ?? '') ?: null;

        if ($nombre === '') {
            return ['error' => 'El nombre de la categoría es obligatorio.'];
        }

        return [
            'error'       => null,
            'nombre'      => $nombre,
            'descripcion' => $descripcion
        ];
    }
}
?>