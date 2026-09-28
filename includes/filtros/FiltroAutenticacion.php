<?php
/**
 * Valida acceso según la sesión del usuario.
 * - sesión activa: cliente o administrador
 * - administrador: además requiere rol administrador
 *
 * En API responde con JSON; en vistas redirige al login.
 */
class FiltroAutenticacion
{
    // API: requiere sesión activa.
    public static function protegerApi()
    {
        if (!AyudanteSesion::estaAutenticado()) {
            Respuesta::error('Debe iniciar sesión para acceder a este recurso.', 401);
        }
    }

    // API: requiere sesión activa y rol administrador.
    public static function protegerApiAdministrador()
    {
        self::protegerApi();
        if (!AyudanteSesion::esAdministrador()) {
            Respuesta::error('No tiene permisos de administrador para realizar esta acción.', 403);
        }
    }

    // Vista: requiere sesión activa. Si no hay sesión, redirige al login.
    public static function protegerVista($rutaLogin)
    {
        if (!AyudanteSesion::estaAutenticado()) {
            $destino = $rutaLogin;
            if ($rutaLogin === 'login.php') {
                $destino .= '?volver=' . urlencode(basename($_SERVER['SCRIPT_NAME']));
            }
            header('Location: ' . $destino);
            exit;
        }
    }

    // Vista: requiere sesión activa y rol administrador.
    public static function protegerVistaAdministrador($rutaLogin)
    {
        self::protegerVista($rutaLogin);
        if (!AyudanteSesion::esAdministrador()) {
            http_response_code(403);
            echo 'No tiene permisos de administrador para acceder a esta página.';
            exit;
        }
    }
}
?>
