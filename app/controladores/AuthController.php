<?php
/**
 * AuthController.php
 * Controlador de autenticación. Usa el Modelo Usuario.php
 */
class AuthController
{
    // Registra un nuevo cliente. tipo_usuario se fija en 'cliente' de
    // forma fija aquí: la creación de administradores no se expone por
    // esta vía pública, solo directamente en la base de datos (seed).
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

        // La tabla usuarios ya tiene UNIQUE(correo), pero se valida aquí
        // primero para poder devolver un mensaje claro en español en
        // lugar de dejar que PDO lance una excepción de duplicado.
        $existente = Usuario::obtenerPorCorreo($correo);
        if ($existente) {
            Respuesta::error('Ya existe una cuenta registrada con ese correo.', 409);
        }

        // El hash se genera aquí, no en el Modelo: Usuario.php solo
        // guarda el string que se le pasa, no decide cómo se protege
        // la contraseña.
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // app/modelos/Usuario.php
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

    // Verifica correo + password contra el hash guardado y, si es
    // válido, abre la sesión del usuario.
    public static function login($datos)
    {
        $correo   = trim($datos['correo'] ?? '');
        $password = $datos['password'] ?? '';

        if ($correo === '' || $password === '') {
            Respuesta::error('Correo y contraseña son obligatorios.', 400);
        }

        $usuario = Usuario::obtenerPorCorreo($correo);

        // Mismo mensaje genérico tanto si el correo no existe como si
        // la contraseña es incorrecta: no se debe revelar cuál de los
        // dos falló (buena práctica básica de autenticación).
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

    // Le permite al frontend (JS) preguntar si hay sesión activa -por
    // ejemplo al cargar cualquier página- para decidir si muestra
    // "Iniciar sesión" o "Mi cuenta / Cerrar sesión" en el navbar.
    public static function verificarSesion()
    {
        if (!AyudanteSesion::estaAutenticado()) {
            Respuesta::error('No hay una sesión activa.', 401);
        }
        Respuesta::exito('Sesión activa.', AyudanteSesion::obtenerUsuarioSesion());
    }
}