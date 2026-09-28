<?php
// Controlador de autenticación.
class AuthController
{
    // Registro.
    public static function registrar($datos)
    {
        $nombre    = trim($datos['nombre'] ?? '');
        $apellido  = trim($datos['apellido'] ?? '');
        $correo    = trim($datos['correo'] ?? '');
        $password  = $datos['password'] ?? '';
        $telefono  = $datos['telefono'] ?? null;
        $direccion = $datos['direccion'] ?? null;

        if ($nombre === '' || $apellido === '' || $correo === '' || $password === '') {
            Respuesta::error('Nombre, apellido, correo y contraseña son obligatorios.', 400);
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            Respuesta::error('El correo no tiene un formato válido.', 400);
        }

        if (strlen($password) < 6) {
            Respuesta::error('La contraseña debe tener al menos 6 caracteres.', 400);
        }

        $existente = Usuario::obtenerPorCorreo($correo);
        if ($existente) {
            Respuesta::error('Ya existe una cuenta registrada con ese correo.', 409);
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $idUsuario = Usuario::crear(
            $nombre,
            $apellido,
            $correo,
            $passwordHash,
            $telefono,
            $direccion,
            'cliente'
        );

        Respuesta::exito('Cuenta creada correctamente.', ['id_usuario' => $idUsuario], 201);
    }

    // Login.
    public static function login($datos)
    {
        $correo   = trim($datos['correo'] ?? '');
        $password = $datos['password'] ?? '';

        if ($correo === '' || $password === '') {
            Respuesta::error('Correo y contraseña son obligatorios.', 400);
        }

        $usuario = Usuario::obtenerPorCorreo($correo);

        if (!$usuario || !password_verify($password, $usuario['password'])) {
            Respuesta::error('Correo o contraseña incorrectos.', 401);
        }

        AyudanteSesion::iniciarSesionUsuario($usuario);

        unset($usuario['password']);
        Respuesta::exito('Inicio de sesión exitoso.', $usuario, 200);
    }

    public static function logout()
    {
        AyudanteSesion::cerrarSesion();
        Respuesta::exito('Sesión cerrada correctamente.');
    }

    // Verifica si hay sesión activa.
    public static function verificarSesion()
    {
        if (!AyudanteSesion::estaAutenticado()) {
            Respuesta::error('No hay una sesión activa.', 401);
        }
        Respuesta::exito('Sesión activa.', AyudanteSesion::obtenerUsuarioSesion());
    }
}
?>