<?php
/**
 * AyudanteSesion.php
 */
class AyudanteSesion
{
    // session_start() no se puede llamar dos veces en la misma
    // petición sin generar un warning; por eso se valida el estado.
    public static function iniciar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Guarda en sesión solo los datos necesarios para identificar al
    // usuario y decidir permisos (nunca la contraseña, ni siquiera el
    // hash).
    public static function iniciarSesionUsuario($usuario)
    {
        self::iniciar();
        $_SESSION['id_usuario']   = $usuario['id_usuario'];
        $_SESSION['nombre']       = $usuario['nombre'];
        $_SESSION['apellido']     = $usuario['apellido'];
        $_SESSION['correo']       = $usuario['correo'];
        $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];
    }

    public static function estaAutenticado()
    {
        self::iniciar();
        return isset($_SESSION['id_usuario']);
    }

    // Pensado para que FiltroAutenticacion.php lo use
    // al proteger las rutas exclusivas de app/vistas/admin/.
    public static function esAdministrador()
    {
        self::iniciar();
        return self::estaAutenticado() && $_SESSION['tipo_usuario'] === 'administrador';
    }

    public static function obtenerUsuarioSesion()
    {
        self::iniciar();
        if (!self::estaAutenticado()) {
            return null;
        }
        return [
            'id_usuario'   => $_SESSION['id_usuario'],
            'nombre'       => $_SESSION['nombre'],
            'apellido'     => $_SESSION['apellido'],
            'correo'       => $_SESSION['correo'],
            'tipo_usuario' => $_SESSION['tipo_usuario']
        ];
    }

    // Vacía y destruye la sesión (logout).
    public static function cerrarSesion()
    {
        self::iniciar();
        $_SESSION = [];
        session_destroy();
    }
}