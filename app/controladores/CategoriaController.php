<?php
/**
 * Controlador de categorías (géneros literarios). Usa el Modelo
 * Categoria.php. Lectura pública; escritura solo administrador.
 */
class CategoriaController
{
    // GET /api/categorias.php -> listado público, ordenado por nombre.
    public static function listar()
    {
        // app/modelos/Categoria.php
        $categorias = Categoria::obtenerTodos();
        Respuesta::exito('Listado de categorías obtenido correctamente.', $categorias);
    }

    // GET /api/categorias.php?id=# -> detalle de una categoría.
    public static function detalle($id_categoria)
    {
        if (!ctype_digit((string) $id_categoria)) {
            Respuesta::error('El id de la categoría no es válido.', 400);
        }

        // app/modelos/Categoria.php
        $categoria = Categoria::obtenerPorId($id_categoria);

        if (!$categoria) {
            Respuesta::error('Categoría no encontrada.', 404);
        }

        Respuesta::exito('Categoría encontrada.', $categoria);
    }

    // POST /api/categorias.php -> crea una categoría nueva. Solo administrador.
    public static function crear($datos)
    {
        FiltroAutenticacion::protegerApiAdministrador();

        $campos = self::validarDatos($datos);
        if ($campos['error']) {
            Respuesta::error($campos['error'], 400);
        }

        try {
            // app/modelos/Categoria.php
            $idCategoria = Categoria::crear($campos['nombre'], $campos['descripcion']);
        } catch (PDOException $e) {
            // uq_categorias_nombre: nombre duplicado.
            Respuesta::error('Ya existe una categoría con ese nombre.', 409);
        }

        Respuesta::exito('Categoría creada correctamente.', ['id_categoria' => $idCategoria], 201);
    }

    // PUT /api/categorias.php?id=# -> actualiza nombre/descripción. Solo administrador.
    public static function actualizar($id_categoria, $datos)
    {
        FiltroAutenticacion::protegerApiAdministrador();

        if (!ctype_digit((string) $id_categoria)) {
            Respuesta::error('El id de la categoría no es válido.', 400);
        }

        // app/modelos/Categoria.php
        if (!Categoria::obtenerPorId($id_categoria)) {
            Respuesta::error('Categoría no encontrada.', 404);
        }

        $campos = self::validarDatos($datos);
        if ($campos['error']) {
            Respuesta::error($campos['error'], 400);
        }

        try {
            // app/modelos/Categoria.php
            Categoria::actualizar($id_categoria, $campos['nombre'], $campos['descripcion']);
        } catch (PDOException $e) {
            Respuesta::error('Ya existe una categoría con ese nombre.', 409);
        }

        Respuesta::exito('Categoría actualizada correctamente.');
    }

    // DELETE /api/categorias.php?id=# -> elimina la categoría. Solo administrador.
    // Si tiene libros asociados, la FK (productos.id_categoria ON DELETE RESTRICT) rechaza el borrado
    public static function eliminar($id_categoria)
    {
        FiltroAutenticacion::protegerApiAdministrador();

        if (!ctype_digit((string) $id_categoria)) {
            Respuesta::error('El id de la categoría no es válido.', 400);
        }

        // app/modelos/Categoria.php
        if (!Categoria::obtenerPorId($id_categoria)) {
            Respuesta::error('Categoría no encontrada.', 404);
        }

        try {
            // app/modelos/Categoria.php
            Categoria::eliminar($id_categoria);
        } catch (PDOException $e) {
            Respuesta::error('No se puede eliminar: hay libros asociados a esta categoría.', 409);
        }

        Respuesta::exito('Categoría eliminada correctamente.');
    }

    // Valida y normaliza nombre/descripción; lo usan crear() y actualizar().
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