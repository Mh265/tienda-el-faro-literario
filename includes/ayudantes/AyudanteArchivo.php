<?php
/**
 * Ayudante para validar y guardar portadas de libros subidas por el
 * administrador. Se mantiene separado del Controlador para no mezclar
 * lógica de manejo de archivos con las reglas de negocio de Libro.
 */
class AyudanteArchivo
{
    private const EXTENSIONES_PERMITIDAS = ['jpg', 'jpeg', 'png', 'webp'];
    private const TAMANO_MAXIMO = 2 * 1024 * 1024; // 2 MB
    private const CARPETA_DESTINO = __DIR__ . '/../../assets/img/uploads/';

    // Recibe el arreglo $_FILES['imagen'] (o null si no llegó nada en la
    // petición). Devuelve el nombre del archivo ya guardado en el servidor,
    // o null si no se subió ninguna imagen (la portada es opcional).
    // Lanza una excepción con un mensaje en español si el archivo no pasa
    // las validaciones, para que el Controlador la traduzca a un 400.
    public static function guardarPortada($archivo)
    {
        // No se subió ningún archivo: válido, imagen queda NULL.
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

        // Nombre generado por el servidor: nunca se usa el nombre original
        // del archivo (evita colisiones de nombres y archivos maliciosos
        // disfrazados de imagen).
        $nombreArchivo = uniqid('libro_') . '.' . $extension;
        $rutaDestino = self::CARPETA_DESTINO . $nombreArchivo;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            throw new Exception('No se pudo guardar la imagen en el servidor.');
        }

        return $nombreArchivo;
    }
}
?>