<?php
/**
 * Controlador del catálogo de libros. Usa los Modelos Libro.php y
 * Categoria.php (este último solo para validar que la categoría exista
 * antes de crear/editar un libro).
 */
class LibroController
{
    // GET /api/libros.php -> catálogo público (solo libros con estado 'activo').
    // $filtros llega directo desde $_GET: q, id_categoria, precio_min,
    // precio_max, disponible, orden (todos opcionales).
    public static function listar($filtros)
    {
        // app/modelos/Libro.php
        $libros = Libro::obtenerCatalogo($filtros);
        Respuesta::exito('Listado de libros obtenido correctamente.', $libros);
    }

    // GET /api/libros.php?accion=admin-listado -> listado completo (activos
    // e inactivos) para el panel de administración.
    public static function listarAdmin()
    {
        if (!AyudanteSesion::esAdministrador()) {
            Respuesta::error('No tiene permisos para ver este listado.', 403);
        }

        // app/modelos/Libro.php
        $libros = Libro::obtenerTodosAdmin();
        Respuesta::exito('Listado administrativo de libros obtenido correctamente.', $libros);
    }

    // GET /api/libros.php?id=# -> detalle de un libro.
    public static function detalle($id_producto)
    {
        if (!ctype_digit((string) $id_producto)) {
            Respuesta::error('El id del libro no es válido.', 400);
        }

        // app/modelos/Libro.php
        $libro = Libro::obtenerPorId($id_producto);

        if (!$libro) {
            Respuesta::error('Libro no encontrado.', 404);
        }

        // Un libro inactivo solo lo puede consultar un administrador (por
        // ejemplo para editarlo); un visitante o cliente recibe 404, como
        // si el libro no existiera, igual que hace el catálogo público.
        if ($libro['estado'] !== 'activo' && !AyudanteSesion::esAdministrador()) {
            Respuesta::error('Libro no encontrado.', 404);
        }

        Respuesta::exito('Libro encontrado.', $libro);
    }

        // POST /api/libros.php -> crea un libro nuevo. Solo administrador.
    // $archivoImagen es $_FILES['imagen'] (o null si no se envió portada).
    public static function crear($datos, $archivoImagen = null)
    {
        if (!AyudanteSesion::esAdministrador()) {
            Respuesta::error('No tiene permisos para crear libros.', 403);
        }

        $campos = self::validarDatos($datos);
        if ($campos['error']) {
            Respuesta::error($campos['error'], 400);
        }

        // app/modelos/Categoria.php
        if (!Categoria::obtenerPorId($campos['id_categoria'])) {
            Respuesta::error('La categoría indicada no existe.', 400);
        }

        // includes/ayudantes/AyudanteArchivo.php — la portada ahora llega
        // como archivo real, no como texto en el body; sustituye lo que
        // haya devuelto validarDatos() para 'imagen'.
        try {
            $campos['imagen'] = AyudanteArchivo::guardarPortada($archivoImagen);
        } catch (Exception $e) {
            Respuesta::error($e->getMessage(), 400);
        }

        // app/modelos/Libro.php
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

    // PUT /api/libros.php?id=# -> actualiza los datos de un libro existente.
    public static function actualizar($id_producto, $datos)
    {
        if (!AyudanteSesion::esAdministrador()) {
            Respuesta::error('No tiene permisos para editar libros.', 403);
        }

        if (!ctype_digit((string) $id_producto)) {
            Respuesta::error('El id del libro no es válido.', 400);
        }

        // app/modelos/Libro.php
        if (!Libro::obtenerPorId($id_producto)) {
            Respuesta::error('Libro no encontrado.', 404);
        }

        $campos = self::validarDatos($datos);
        if ($campos['error']) {
            Respuesta::error($campos['error'], 400);
        }

        // app/modelos/Categoria.php
        if (!Categoria::obtenerPorId($campos['id_categoria'])) {
            Respuesta::error('La categoría indicada no existe.', 400);
        }

        // app/modelos/Libro.php
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
            $campos['imagen'],
            $campos['fecha_publicacion']
        );

        Respuesta::exito('Libro actualizado correctamente.');
    }

    // DELETE /api/libros.php?id=# -> nunca borra físicamente: da de baja
    // (estado = 'inactivo'). Ver el comentario en Libro::darDeBaja() para
    // el porqué (FK RESTRICT de detalle_pedido hacia productos).
    public static function darDeBaja($id_producto)
    {
        if (!AyudanteSesion::esAdministrador()) {
            Respuesta::error('No tiene permisos para dar de baja libros.', 403);
        }

        if (!ctype_digit((string) $id_producto)) {
            Respuesta::error('El id del libro no es válido.', 400);
        }

        // app/modelos/Libro.php
        if (!Libro::obtenerPorId($id_producto)) {
            Respuesta::error('Libro no encontrado.', 404);
        }

        Libro::darDeBaja($id_producto);
        Respuesta::exito('Libro dado de baja correctamente.');
    }

    // PUT /api/libros.php?id=#&accion=reactivar -> vuelve a mostrar el libro
    // en el catálogo público.
    public static function reactivar($id_producto)
    {
        if (!AyudanteSesion::esAdministrador()) {
            Respuesta::error('No tiene permisos para reactivar libros.', 403);
        }

        if (!ctype_digit((string) $id_producto)) {
            Respuesta::error('El id del libro no es válido.', 400);
        }

        // app/modelos/Libro.php
        if (!Libro::obtenerPorId($id_producto)) {
            Respuesta::error('Libro no encontrado.', 404);
        }

        Libro::reactivar($id_producto);
        Respuesta::exito('Libro reactivado correctamente.');
    }

    // Valida y normaliza los campos de un libro; lo usan tanto crear() como
    // actualizar(). Devuelve un arreglo asociativo con los valores listos
    // para el Modelo y, si algo falla, la clave 'error' con el mensaje.
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
        $imagen = trim($datos['imagen'] ?? '') ?: null;
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
            'imagen'             => $imagen,
            'fecha_publicacion'  => $fechaPublicacion
        ];
    }
}
?>