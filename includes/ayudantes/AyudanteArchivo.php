<?php
// Ayudante para manejar portadas de libros.
class AyudanteArchivo
{
    private const EXTENSIONES_PERMITIDAS = ['jpg', 'jpeg', 'png', 'webp'];
    private const TAMANO_MAXIMO = 2 * 1024 * 1024; // 2 MB
    private const CARPETA_DESTINO = __DIR__ . '/../../assets/img/uploads/';

    // Guarda la portada si existe.
    public static function guardarPortada($archivo)
    {
        // Si no hay archivo, se acepta como null.
        if ($archivo === null || $archivo['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Ocurrió un error al subir la imagen.');
        }

        if ($archivo['size'] > self::TAMANO_MAXIMO) {
            throw new Exception('La imagen no debe superar los 2 MB.');
        }

        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::EXTENSIONES_PERMITIDAS, true)) {
            throw new Exception('Formato de imagen no permitido. Use JPG, PNG o WEBP.');
        }

        // La extensión se puede falsear: getimagesize() confirma que el contenido es una imagen.
        if (@getimagesize($archivo['tmp_name']) === false) {
            throw new Exception('El archivo no es una imagen válida.');
        }

        // Genera un nombre seguro para evitar colisiones.
        $nombreArchivo = uniqid('libro_') . '.' . $extension;
        $rutaDestino = self::CARPETA_DESTINO . $nombreArchivo;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            throw new Exception('No se pudo guardar la imagen en el servidor.');
        }

        return $nombreArchivo;
    }
}
?>