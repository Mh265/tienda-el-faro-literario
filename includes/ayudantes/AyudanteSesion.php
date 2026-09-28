<?php
// Ayudante para la sesión.
class AyudanteSesion
{
    // Inicia la sesión si aún no existe.
    public static function iniciar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Guarda los datos mínimos del usuario en sesión.
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

    // Verifica si el usuario es administrador.
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

    // Cierra la sesión.
    public static function cerrarSesion()
    {
        self::iniciar();
        $_SESSION = [];
        session_destroy();
    }
}
?>