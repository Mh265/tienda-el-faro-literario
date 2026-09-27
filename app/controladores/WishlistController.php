<?php
/**
 * Controlador de la lista de deseos. Usa los Modelos Wishlist.php y
 * Libro.php (para validar que el libro exista). Todas las acciones
 * requieren sesión activa (cliente o administrador).
 */
class WishlistController
{
    // GET /api/wishlist.php -> lista de deseos del usuario autenticado.
    public static function listar()
    {
        FiltroAutenticacion::protegerApi();
        $idUsuario = AyudanteSesion::obtenerUsuarioSesion()['id_usuario'];

        // app/modelos/Wishlist.php
        $lista = Wishlist::obtenerPorUsuario($idUsuario);
        Respuesta::exito('Lista de deseos obtenida correctamente.', $lista);
    }

    // POST /api/wishlist.php -> agrega un libro a la lista de deseos.
    public static function agregar($datos)
    {
        FiltroAutenticacion::protegerApi();

        $idProducto = $datos['id_producto'] ?? null;
        if (!ctype_digit((string) $idProducto)) {
            Respuesta::error('Debe indicar un id_producto válido.', 400);
        }

        // app/modelos/Libro.php
        $libro = Libro::obtenerPorId($idProducto);
        if (!$libro || $libro['estado'] !== 'activo') {
            Respuesta::error('El libro no está disponible.', 400);
        }

        $idUsuario = AyudanteSesion::obtenerUsuarioSesion()['id_usuario'];

        try {
            // app/modelos/Wishlist.php
            $idWishlist = Wishlist::crear($idUsuario, $idProducto);
        } catch (PDOException $e) {
            // uq_wishlist_usuario_producto: ya estaba en la lista.
            Respuesta::error('Este libro ya está en tu lista de deseos.', 409);
        }

        Respuesta::exito('Libro agregado a la lista de deseos.', ['id_wishlist' => $idWishlist], 201);
    }

    // DELETE /api/wishlist.php?id_producto=# -> quita un libro de la lista
    // de deseos del usuario autenticado. Se recibe id_producto (no id_wishlist)
    public static function eliminar($id_producto)
    {
        FiltroAutenticacion::protegerApi();

        if (!ctype_digit((string) $id_producto)) {
            Respuesta::error('Debe indicar un id_producto válido.', 400);
        }

        $idUsuario = AyudanteSesion::obtenerUsuarioSesion()['id_usuario'];

        // app/modelos/Wishlist.php
        Wishlist::eliminarPorUsuarioYProducto($idUsuario, $id_producto);
        Respuesta::exito('Libro eliminado de la lista de deseos.');
    }
}
?>