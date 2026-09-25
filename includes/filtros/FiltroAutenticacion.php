<?php
/**
 * FiltroAutenticacion.php
 * Filtro central de control de acceso para "El Faro Literario". Usa
 * AyudanteSesion.php (ya cargado por quien invoque este filtro) para
 * distinguir dos niveles de protección:
 *   - "requiere sesión": basta con estar logueado (cliente o administrador).
 *   - "requiere administrador": además de estar logueado, el rol debe ser
 *     'administrador'; si no lo es, se corta con un 403.
 *
 * Se usa en dos contextos distintos, que necesitan responder diferente
 * cuando el acceso se rechaza:
 *   - Endpoints de la API (api/*.php, o el Controller que llaman): cortan
 *     con una respuesta JSON, usando Respuesta.php (ya cargado por el
 *     endpoint que invoca el filtro).
 *   - Vistas HTML (public/vistas/admin/*.php y otras vistas privadas):
 *     redirigen al login, porque una respuesta JSON no tiene sentido ahí.
 */
class FiltroAutenticacion
{
    // -----------------------------------------------------------------
    // Para ENDPOINTS DE LA API. Se llama al inicio del endpoint (api/*.php)
    // o del Controller, antes de tocar el Modelo.
    // -----------------------------------------------------------------

    // Exige que haya una sesión activa (cliente o administrador).
    public static function protegerApi()
    {
        if (!AyudanteSesion::estaAutenticado()) {
            Respuesta::error('Debe iniciar sesión para acceder a este recurso.', 401);
        }
    }

    // Exige que la sesión activa sea de tipo administrador.
    public static function protegerApiAdministrador()
    {
        self::protegerApi();
        if (!AyudanteSesion::esAdministrador()) {
            Respuesta::error('No tiene permisos de administrador para realizar esta acción.', 403);
        }
    }

    // -----------------------------------------------------------------
    // Para VISTAS HTML (public/vistas/admin/*.php, y cualquier otra vista
    // privada como checkout o mis-pedidos). Se llama al inicio de la
    // vista, antes de imprimir cualquier HTML.
    // $rutaLogin es la ruta relativa desde la vista hasta login.php
    // (ej. '../login.php' desde public/vistas/admin/).
    // -----------------------------------------------------------------

    // Exige que haya una sesión activa; si no, redirige al login.
    public static function protegerVista($rutaLogin)
    {
        if (!AyudanteSesion::estaAutenticado()) {
            header('Location: ' . $rutaLogin);
            exit;
        }
    }

    // Exige que la sesión activa sea de tipo administrador. Si hay sesión
    // pero el rol no corresponde, corta con 403 (no tiene sentido mandar
    // al login a alguien que ya inició sesión).
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