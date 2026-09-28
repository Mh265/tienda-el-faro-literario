<?php
// Controlador de usuarios.
class UsuarioController
{
    // Perfil propio.
    public static function perfilPropio()
    {
        FiltroAutenticacion::protegerApi();
        $idUsuario = AyudanteSesion::obtenerUsuarioSesion()['id_usuario'];

        // app/modelos/Usuario.php
        $usuario = Usuario::obtenerPorId($idUsuario);
        Respuesta::exito('Perfil obtenido correctamente.', $usuario);
    }

    // PUT /api/usuarios.php -> actualiza datos del perfil propio
    // (nombre, apellido, telefono, direccion). No permite cambiar correo,
    // password ni tipo_usuario.
    public static function actualizarPerfilPropio($datos)
    {
        FiltroAutenticacion::protegerApi();
        $idUsuario = AyudanteSesion::obtenerUsuarioSesion()['id_usuario'];

        $campos = self::validarDatosPerfil($datos);
        if ($campos['error']) {
            Respuesta::error($campos['error'], 400);
        }

        // app/modelos/Usuario.php
        Usuario::actualizar($idUsuario, $campos['nombre'], $campos['apellido'], $campos['telefono'], $campos['direccion']);
        Respuesta::exito('Perfil actualizado correctamente.');
    }

    // GET /api/usuarios.php?accion=admin-listado -> listado completo. Solo administrador.
    public static function listarAdmin()
    {
        FiltroAutenticacion::protegerApiAdministrador();

        // app/modelos/Usuario.php
        $usuarios = Usuario::obtenerTodos();
        Respuesta::exito('Listado de usuarios obtenido correctamente.', $usuarios);
    }

    // GET /api/usuarios.php?id=# -> un usuario puntual. Solo administrador
    // (el propio usuario ya tiene su ruta dedicada sin id, arriba).
    public static function detalleAdmin($id_usuario)
    {
        FiltroAutenticacion::protegerApiAdministrador();

        if (!ctype_digit((string) $id_usuario)) {
            Respuesta::error('El id del usuario no es válido.', 400);
        }

        // app/modelos/Usuario.php
        $usuario = Usuario::obtenerPorId($id_usuario);
        if (!$usuario) {
            Respuesta::error('Usuario no encontrado.', 404);
        }

        Respuesta::exito('Usuario encontrado.', $usuario);
    }

    // PUT /api/usuarios.php?id=# -> edita el perfil de cualquier usuario.
    // Solo administrador. Mismos campos/reglas que el perfil propio.
    public static function actualizarAdmin($id_usuario, $datos)
    {
        FiltroAutenticacion::protegerApiAdministrador();

        if (!ctype_digit((string) $id_usuario)) {
            Respuesta::error('El id del usuario no es válido.', 400);
        }

        // app/modelos/Usuario.php
        if (!Usuario::obtenerPorId($id_usuario)) {
            Respuesta::error('Usuario no encontrado.', 404);
        }

        $campos = self::validarDatosPerfil($datos);
        if ($campos['error']) {
            Respuesta::error($campos['error'], 400);
        }

        // app/modelos/Usuario.php
        Usuario::actualizar($id_usuario, $campos['nombre'], $campos['apellido'], $campos['telefono'], $campos['direccion']);
        Respuesta::exito('Usuario actualizado correctamente.');
    }

    // DELETE /api/usuarios.php?id=# -> elimina un usuario. Solo administrador.
    // Un usuario con pedidos registrados no se puede borrar (FK RESTRICT de
    // pedidos hacia usuarios); reseñas y wishlist sí se borran en cascada.
    public static function eliminar($id_usuario)
    {
        FiltroAutenticacion::protegerApiAdministrador();

        if (!ctype_digit((string) $id_usuario)) {
            Respuesta::error('El id del usuario no es válido.', 400);
        }

        // app/modelos/Usuario.php
        if (!Usuario::obtenerPorId($id_usuario)) {
            Respuesta::error('Usuario no encontrado.', 404);
        }

        $idAdministradorEnSesion = AyudanteSesion::obtenerUsuarioSesion()['id_usuario'];
        if ((int) $idAdministradorEnSesion === (int) $id_usuario) {
            Respuesta::error('No puede eliminar su propia cuenta desde este panel.', 400);
        }

        try {
            // app/modelos/Usuario.php
            Usuario::eliminar($id_usuario);
        } catch (PDOException $e) {
            Respuesta::error('No se puede eliminar: el usuario tiene pedidos registrados.', 409);
        }

        Respuesta::exito('Usuario eliminado correctamente.');
    }

    // Valida y normaliza los campos editables de perfil; los usan
    // actualizarPerfilPropio() y actualizarAdmin().
    private static function validarDatosPerfil($datos)
    {
        $nombre = trim($datos['nombre'] ?? '');
        $apellido = trim($datos['apellido'] ?? '');
        $telefono = trim($datos['telefono'] ?? '') ?: null;
        $direccion = trim($datos['direccion'] ?? '') ?: null;

        if ($nombre === '' || $apellido === '') {
            return ['error' => 'Nombre y apellido son obligatorios.'];
        }

        return [
            'error'     => null,
            'nombre'    => $nombre,
            'apellido'  => $apellido,
            'telefono'  => $telefono,
            'direccion' => $direccion
        ];
    }
}
?>