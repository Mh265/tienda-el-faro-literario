<?php
// Controlador del catálogo de libros.
class LibroController
{
    // Listado público.
    public static function listar($filtros)
    {
        $libros = Libro::obtenerCatalogo($filtros);
        Respuesta::exito('Listado de libros obtenido correctamente.', $libros);
    }

    // Listado administrativo.
    public static function listarAdmin()
    {
        if (!AyudanteSesion::esAdministrador()) {
            Respuesta::error('No tiene permisos para ver este listado.', 403);
        }

        $libros = Libro::obtenerTodosAdmin();
        Respuesta::exito('Listado administrativo de libros obtenido correctamente.', $libros);
    }

    // Detalle de libro.
    public static function detalle($id_producto)
    {
        if (!ctype_digit((string) $id_producto)) {
            Respuesta::error('El id del libro no es válido.', 400);
        }

        $libro = Libro::obtenerPorId($id_producto);

        if (!$libro) {
            Respuesta::error('Libro no encontrado.', 404);
        }

        if ($libro['estado'] !== 'activo' && !AyudanteSesion::esAdministrador()) {
            Respuesta::error('Libro no encontrado.', 404);
        }

        Respuesta::exito('Libro encontrado.', $libro);
    }

    // Crear libro.
    public static function crear($datos, $archivoImagen = null)
    {
        if (!AyudanteSesion::esAdministrador()) {
            Respuesta::error('No tiene permisos para crear libros.', 403);
        }

        $campos = self::validarDatos($datos);
        if ($campos['error']) {
            Respuesta::error($campos['error'], 400);
        }

        if (!Categoria::obtenerPorId($campos['id_categoria'])) {
            Respuesta::error('La categoría indicada no existe.', 400);
        }

        try {
            $campos['imagen'] = AyudanteArchivo::guardarPortada($archivoImagen);
        } catch (Exception $e) {
            Respuesta::error($e->getMessage(), 400);
        }

        $idProducto = Libro::crear(
            $campos['id_categoria'],
            $campos['nombre'],
            $campos['autor'],
            $campos['editorial'],
            $campos['descripcion_corta'],
            $campos['descripcion_larga'],
            $campos['precio'],
            $campos['cantidad'],
            $campos['imagen'],
            $campos['fecha_publicacion']
        );

        Respuesta::exito('Libro creado correctamente.', ['id_producto' => $idProducto], 201);
    }

    // Actualizar libro.
    public static function actualizar($id_producto, $datos)
    {
        if (!AyudanteSesion::esAdministrador()) {
            Respuesta::error('No tiene permisos para editar libros.', 403);
        }

        if (!ctype_digit((string) $id_producto)) {
            Respuesta::error('El id del libro no es válido.', 400);
        }

        if (!Libro::obtenerPorId($id_producto)) {
            Respuesta::error('Libro no encontrado.', 404);
        }

        $campos = self::validarDatos($datos);
        if ($campos['error']) {
            Respuesta::error($campos['error'], 400);
        }

        if (!Categoria::obtenerPorId($campos['id_categoria'])) {
            Respuesta::error('La categoría indicada no existe.', 400);
        }

        Libro::actualizar(
            $id_producto,
            $campos['id_categoria'],
            $campos['nombre'],
            $campos['autor'],
            $campos['editorial'],
            $campos['descripcion_corta'],
            $campos['descripcion_larga'],
            $campos['precio'],
            $campos['cantidad'],
            $campos['fecha_publicacion']
        );

        Respuesta::exito('Libro actualizado correctamente.');
    }

    // Cambia la portada de un libro existente (POST ?accion=portada&id=#).
    // Va aparte de actualizar() porque la portada viaja como archivo
    // (multipart/form-data) y actualizar() recibe JSON.
    public static function actualizarPortada($id_producto, $archivoImagen)
    {
        if (!AyudanteSesion::esAdministrador()) {
            Respuesta::error('No tiene permisos para cambiar la portada.', 403);
        }

        if (!ctype_digit((string) $id_producto)) {
            Respuesta::error('El id del libro no es válido.', 400);
        }

        if (!Libro::obtenerPorId($id_producto)) {
            Respuesta::error('Libro no encontrado.', 404);
        }

        try {
            $imagen = AyudanteArchivo::guardarPortada($archivoImagen);
        } catch (Exception $e) {
            Respuesta::error($e->getMessage(), 400);
        }

        // guardarPortada() devuelve null cuando no se envió ningún archivo.
        if ($imagen === null) {
            Respuesta::error('Debe seleccionar una imagen.', 400);
        }

        // app/modelos/Libro.php
        Libro::actualizarImagen($id_producto, $imagen);
        Respuesta::exito('Portada actualizada correctamente.', ['imagen' => $imagen]);
    }

    // Baja lógica del libro.
    public static function darDeBaja($id_producto)
    {
        if (!AyudanteSesion::esAdministrador()) {
            Respuesta::error('No tiene permisos para dar de baja libros.', 403);
        }

        if (!ctype_digit((string) $id_producto)) {
            Respuesta::error('El id del libro no es válido.', 400);
        }

        if (!Libro::obtenerPorId($id_producto)) {
            Respuesta::error('Libro no encontrado.', 404);
        }

        Libro::darDeBaja($id_producto);
        Respuesta::exito('Libro dado de baja correctamente.');
    }

    // Reactivar libro.
    public static function reactivar($id_producto)
    {
        if (!AyudanteSesion::esAdministrador()) {
            Respuesta::error('No tiene permisos para reactivar libros.', 403);
        }

        if (!ctype_digit((string) $id_producto)) {
            Respuesta::error('El id del libro no es válido.', 400);
        }

        if (!Libro::obtenerPorId($id_producto)) {
            Respuesta::error('Libro no encontrado.', 404);
        }

        Libro::reactivar($id_producto);
        Respuesta::exito('Libro reactivado correctamente.');
    }

    // Validación de datos del libro.
    private static function validarDatos($datos)
    {
        $idCategoria = $datos['id_categoria'] ?? null;
        $nombre = trim($datos['nombre'] ?? '');
        $autor = trim($datos['autor'] ?? '');
        $editorial = trim($datos['editorial'] ?? '') ?: null;
        $descripcionCorta = trim($datos['descripcion_corta'] ?? '') ?: null;
        $descripcionLarga = trim($datos['descripcion_larga'] ?? '') ?: null;
        $precio = $datos['precio'] ?? null;
        $cantidad = $datos['cantidad'] ?? 0;
        $fechaPublicacion = trim($datos['fecha_publicacion'] ?? '') ?: null;

        if (!$idCategoria || !ctype_digit((string) $idCategoria)) {
            return ['error' => 'La categoría es obligatoria.'];
        }

        if ($nombre === '' || $autor === '') {
            return ['error' => 'El título y el autor son obligatorios.'];
        }

        if (!is_numeric($precio) || $precio <= 0) {
            return ['error' => 'El precio debe ser un número mayor a 0.'];
        }

        if (!is_numeric($cantidad) || $cantidad < 0) {
            return ['error' => 'La cantidad en stock no puede ser negativa.'];
        }

        return [
            'error'              => null,
            'id_categoria'       => (int) $idCategoria,
            'nombre'             => $nombre,
            'autor'              => $autor,
            'editorial'          => $editorial,
            'descripcion_corta'  => $descripcionCorta,
            'descripcion_larga'  => $descripcionLarga,
            'precio'             => $precio,
            'cantidad'           => (int) $cantidad,
            'fecha_publicacion'  => $fechaPublicacion
        ];
    }
}
?>