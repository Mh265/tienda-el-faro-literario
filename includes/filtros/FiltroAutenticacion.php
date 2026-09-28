<?php
// Filtro central de acceso para API y vistas.
class FiltroAutenticacion
{
    // Requiere sesión activa.
    public static function protegerApi()
    {
        if (!AyudanteSesion::estaAutenticado()) {
            Respuesta::error('Debe iniciar sesión para acceder a este recurso.', 401);
        }
    }

    // Requiere sesión de administrador.
    public static function protegerApiAdministrador()
    {
        self::protegerApi();
        if (!AyudanteSesion::esAdministrador()) {
            Respuesta::error('No tiene permisos de administrador para realizar esta acción.', 403);
        }
    }

    // Requiere sesión activa para vista privada.
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

    // Requiere sesión de administrador para vista privada.
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
