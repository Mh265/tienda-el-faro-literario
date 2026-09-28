<?php
// Controlador de reseñas.
class ResenaController
{
    // Listado público por producto.
    public static function listarPorProducto($id_producto)
    {
        if (!ctype_digit((string) $id_producto)) {
            Respuesta::error('Debe indicar un id_producto válido.', 400);
        }

        // app/modelos/Resena.php
        $resenas = Resena::obtenerPorProducto($id_producto);
        Respuesta::exito('Reseñas obtenidas correctamente.', $resenas);
    }

    // POST /api/resenas.php -> crea una reseña nueva. Requiere sesión.
    public static function crear($datos)
    {
        FiltroAutenticacion::protegerApi();

        $idProducto = $datos['id_producto'] ?? null;
        $calificacion = $datos['calificacion'] ?? null;
        $comentario = trim($datos['comentario'] ?? '') ?: null;

        if (!ctype_digit((string) $idProducto)) {
            Respuesta::error('Debe indicar un id_producto válido.', 400);
        }

        if (!ctype_digit((string) $calificacion) || (int) $calificacion < 1 || (int) $calificacion > 5) {
            Respuesta::error('La calificación debe ser un número entero entre 1 y 5.', 400);
        }

        // app/modelos/Libro.php
        $libro = Libro::obtenerPorId($idProducto);
        if (!$libro || $libro['estado'] !== 'activo') {
            Respuesta::error('El libro no está disponible.', 400);
        }

            $idUsuario = AyudanteSesion::obtenerUsuarioSesion()['id_usuario'];

        // Una sola reseña por usuario y libro: si ya existe, se pide editarla.
        // app/modelos/Resena.php
        if (Resena::obtenerPorUsuarioYProducto($idUsuario, $idProducto)) {
            Respuesta::error('Ya escribiste una reseña para este libro. Edítala en lugar de crear otra.', 409);
        }

        try {
            // app/modelos/Resena.php
            $idResena = Resena::crear($idUsuario, $idProducto, (int) $calificacion, $comentario);
        } catch (PDOException $e) {
            // uq_resenas_usuario_producto: otra petición la creó justo antes.
            Respuesta::error('Ya escribiste una reseña para este libro. Edítala en lugar de crear otra.', 409);
        }

        Respuesta::exito('Reseña creada correctamente.', ['id_resena' => $idResena], 201);
    }

    // PUT /api/resenas.php?id=# -> edita una reseña. Solo el dueño puede editarla.
    public static function actualizar($id_resena, $datos)
    {
        FiltroAutenticacion::protegerApi();

        if (!ctype_digit((string) $id_resena)) {
            Respuesta::error('El id de la reseña no es válido.', 400);
        }

        // app/modelos/Resena.php
        $resena = Resena::obtenerPorId($id_resena);
        if (!$resena) {
            Respuesta::error('Reseña no encontrada.', 404);
        }

        $idUsuario = AyudanteSesion::obtenerUsuarioSesion()['id_usuario'];
        if ((int) $resena['id_usuario'] !== (int) $idUsuario) {
            Respuesta::error('No tiene permisos para editar esta reseña.', 403);
        }

        $calificacion = $datos['calificacion'] ?? null;
        $comentario = trim($datos['comentario'] ?? '') ?: null;

        if (!ctype_digit((string) $calificacion) || (int) $calificacion < 1 || (int) $calificacion > 5) {
            Respuesta::error('La calificación debe ser un número entero entre 1 y 5.', 400);
        }

        // app/modelos/Resena.php
        Resena::actualizar($id_resena, (int) $calificacion, $comentario);
        Respuesta::exito('Reseña actualizada correctamente.');
    }

    // DELETE /api/resenas.php?id=# -> elimina una reseña. El dueño puede
    // eliminar la suya; un administrador puede eliminar cualquiera
    public static function eliminar($id_resena)
    {
        FiltroAutenticacion::protegerApi();

        if (!ctype_digit((string) $id_resena)) {
            Respuesta::error('El id de la reseña no es válido.', 400);
        }

        // app/modelos/Resena.php
        $resena = Resena::obtenerPorId($id_resena);
        if (!$resena) {
            Respuesta::error('Reseña no encontrada.', 404);
        }

        $idUsuario = AyudanteSesion::obtenerUsuarioSesion()['id_usuario'];
        $esDueno = (int) $resena['id_usuario'] === (int) $idUsuario;

        if (!$esDueno && !AyudanteSesion::esAdministrador()) {
            Respuesta::error('No tiene permisos para eliminar esta reseña.', 403);
        }

        // app/modelos/Resena.php
        Resena::eliminar($id_resena);
        Respuesta::exito('Reseña eliminada correctamente.');
    }
}
?>