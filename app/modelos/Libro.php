<?php
/**
 * Libro.php
 * Modelo para la tabla `productos` (libros del catálogo de "El Faro Literario").
 */
class Libro
{
    // Catálogo público: solo libros con estado = 'activo'. Todos los filtros
    // son opcionales, por eso el WHERE se arma dinámicamente según lo que
    // venga en $filtros (típicamente $_GET desde el Controlador).
    public static function obtenerCatalogo($filtros = [])
    {
        $conexion = BaseDatos::conectar();

        $condiciones = ["p.estado = 'activo'"];
        $parametros = [];

        if (!empty($filtros['q'])) {
            $condiciones[] = "(p.nombre LIKE :q OR p.autor LIKE :q)";
            $parametros[':q'] = '%' . $filtros['q'] . '%';
        }

        if (!empty($filtros['id_categoria'])) {
            $condiciones[] = "p.id_categoria = :id_categoria";
            $parametros[':id_categoria'] = (int) $filtros['id_categoria'];
        }

        if (isset($filtros['precio_min']) && $filtros['precio_min'] !== '') {
            $condiciones[] = "p.precio >= :precio_min";
            $parametros[':precio_min'] = $filtros['precio_min'];
        }

        if (isset($filtros['precio_max']) && $filtros['precio_max'] !== '') {
            $condiciones[] = "p.precio <= :precio_max";
            $parametros[':precio_max'] = $filtros['precio_max'];
        }

        // disponible=1 -> solo libros con stock; si no se envía, no se filtra.
        if (isset($filtros['disponible']) && $filtros['disponible'] == '1') {
            $condiciones[] = "p.cantidad > 0";
        }

        // Lista blanca de criterios de orden: el valor de $_GET['orden']
        // nunca se concatena directamente en el SQL.
        $ordenesPermitidos = [
            'precio_asc'  => 'p.precio ASC',
            'precio_desc' => 'p.precio DESC',
            'nombre_asc'  => 'p.nombre ASC',
            'recientes'   => 'p.fecha_creacion DESC'
        ];
        $orden = $ordenesPermitidos[$filtros['orden'] ?? ''] ?? 'p.fecha_creacion DESC';

        $sql = "SELECT p.*, c.nombre AS nombre_categoria
                FROM productos p
                INNER JOIN categorias c ON c.id_categoria = p.id_categoria
                WHERE " . implode(' AND ', $condiciones) . "
                ORDER BY $orden";

        $stmt = $conexion->prepare($sql);
        foreach ($parametros as $clave => $valor) {
            $stmt->bindValue($clave, $valor);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Listado completo para el panel de administración: incluye libros
    // activos e inactivos, sin los filtros del catálogo público.
    public static function obtenerTodosAdmin()
    {
        $conexion = BaseDatos::conectar();
        $sql = "SELECT p.*, c.nombre AS nombre_categoria
                FROM productos p
                INNER JOIN categorias c ON c.id_categoria = p.id_categoria
                ORDER BY p.fecha_creacion DESC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Detalle de un libro por su ID, sin filtrar por estado: el Controlador
    // decide si un libro inactivo puede mostrarse (por ejemplo, a un admin
    // que lo va a editar) o no (a un visitante).
    public static function obtenerPorId($id_producto, $conexion = null)
    {
        $conexion = $conexion ?? BaseDatos::conectar();
        $sql = "SELECT p.*, c.nombre AS nombre_categoria
                FROM productos p
                INNER JOIN categorias c ON c.id_categoria = p.id_categoria
                WHERE p.id_producto = :id_producto";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_producto", $id_producto, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Inserta un libro nuevo. estado nace siempre en 'activo'; darlo de baja
    // es una operación aparte (ver darDeBaja).
    public static function crear($id_categoria, $nombre, $autor, $editorial, $descripcion_corta, $descripcion_larga, $precio, $cantidad, $imagen, $fecha_publicacion)
    {
        $conexion = BaseDatos::conectar();
        $sql = "INSERT INTO productos
                    (id_categoria, nombre, autor, editorial, descripcion_corta, descripcion_larga, precio, cantidad, imagen, fecha_publicacion, estado)
                VALUES
                    (:id_categoria, :nombre, :autor, :editorial, :descripcion_corta, :descripcion_larga, :precio, :cantidad, :imagen, :fecha_publicacion, 'activo')";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_categoria", $id_categoria, PDO::PARAM_INT);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":autor", $autor);
        $stmt->bindParam(":editorial", $editorial);
        $stmt->bindParam(":descripcion_corta", $descripcion_corta);
        $stmt->bindParam(":descripcion_larga", $descripcion_larga);
        $stmt->bindParam(":precio", $precio);
        $stmt->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
        $stmt->bindParam(":imagen", $imagen);
        $stmt->bindParam(":fecha_publicacion", $fecha_publicacion);
        $stmt->execute();
        return $conexion->lastInsertId();
    }

    // Actualiza los datos editables de un libro. No toca `estado`: eso se
    // maneja aparte con darDeBaja()/reactivar(), para que quede explícito
    // en el código cuándo se está dando de baja algo (en vez de que ocurra
    // "de paso" dentro de una edición normal).
    public static function actualizar($id_producto, $id_categoria, $nombre, $autor, $editorial, $descripcion_corta, $descripcion_larga, $precio, $cantidad, $imagen, $fecha_publicacion)
    {
        $conexion = BaseDatos::conectar();
        $sql = "UPDATE productos
                SET id_categoria = :id_categoria,
                    nombre = :nombre,
                    autor = :autor,
                    editorial = :editorial,
                    descripcion_corta = :descripcion_corta,
                    descripcion_larga = :descripcion_larga,
                    precio = :precio,
                    cantidad = :cantidad,
                    imagen = :imagen,
                    fecha_publicacion = :fecha_publicacion
                WHERE id_producto = :id_producto";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_categoria", $id_categoria, PDO::PARAM_INT);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":autor", $autor);
        $stmt->bindParam(":editorial", $editorial);
        $stmt->bindParam(":descripcion_corta", $descripcion_corta);
        $stmt->bindParam(":descripcion_larga", $descripcion_larga);
        $stmt->bindParam(":precio", $precio);
        $stmt->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
        $stmt->bindParam(":imagen", $imagen);
        $stmt->bindParam(":fecha_publicacion", $fecha_publicacion);
        $stmt->bindParam(":id_producto", $id_producto, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // "Eliminar" un libro nunca es un DELETE físico: detalle_pedido tiene
    // FK hacia productos con ON DELETE RESTRICT, así que un libro con
    // ventas ya registradas ni siquiera se podría borrar. En vez de eso,
    // se marca estado = 'inactivo' y el catálogo público deja de mostrarlo.
    public static function darDeBaja($id_producto)
    {
        $conexion = BaseDatos::conectar();
        $sql = "UPDATE productos SET estado = 'inactivo' WHERE id_producto = :id_producto";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_producto", $id_producto, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Reactiva un libro dado de baja previamente.
    public static function reactivar($id_producto)
    {
        $conexion = BaseDatos::conectar();
        $sql = "UPDATE productos SET estado = 'activo' WHERE id_producto = :id_producto";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_producto", $id_producto, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // La condición "cantidad >= :cantidad_minima" en el propio UPDATE evita que el
    // stock quede negativo si dos pedidos concurrentes pasan la validación inicial
    // casi al mismo tiempo. Si no se actualizó ninguna fila (rowCount() === 0),
    // el Controller interpreta que el stock ya no alcanza y revierte el pedido.
    public static function descontarStock($id_producto, $cantidad, $conexion = null)
    {
        $conexion = $conexion ?? BaseDatos::conectar();
        $sql = "UPDATE productos
                SET cantidad = cantidad - :cantidad
                WHERE id_producto = :id_producto AND cantidad >= :cantidad_minima";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":cantidad", $cantidad, PDO::PARAM_INT);
        $stmt->bindParam(":id_producto", $id_producto, PDO::PARAM_INT);
        $stmt->bindParam(":cantidad_minima", $cantidad, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
?>