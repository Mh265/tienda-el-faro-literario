<?php
// Modelo de usuarios.
class Usuario
{
    // Listar usuarios.
    public static function obtenerTodos()
    {
        $conexion = BaseDatos::conectar();
        $sql = "SELECT id_usuario, nombre, apellido, correo, telefono, direccion,
                       tipo_usuario, fecha_registro
                FROM usuarios
                ORDER BY fecha_registro DESC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener usuario por ID.
    public static function obtenerPorId($id_usuario)
    {
        $conexion = BaseDatos::conectar();
        $sql = "SELECT id_usuario, nombre, apellido, correo, telefono, direccion,
                       tipo_usuario, fecha_registro
                FROM usuarios
                WHERE id_usuario = :id_usuario";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener usuario por correo.
    public static function obtenerPorCorreo($correo)
    {
        $conexion = BaseDatos::conectar();
        $sql = "SELECT * FROM usuarios WHERE correo = :correo";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":correo", $correo);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear usuario.
    public static function crear($nombre, $apellido, $correo, $password, $telefono, $direccion, $tipo_usuario = 'cliente')
    {
        $conexion = BaseDatos::conectar();
        $sql = "INSERT INTO usuarios (nombre, apellido, correo, password, telefono, direccion, tipo_usuario)
                VALUES (:nombre, :apellido, :correo, :password, :telefono, :direccion, :tipo_usuario)";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":apellido", $apellido);
        $stmt->bindParam(":correo", $correo);
        $stmt->bindParam(":password", $password);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":direccion", $direccion);
        $stmt->bindParam(":tipo_usuario", $tipo_usuario);
        $stmt->execute();
        return $conexion->lastInsertId();
    }

    // Actualizar perfil del usuario.
    public static function actualizar($id_usuario, $nombre, $apellido, $telefono, $direccion)
    {
        $conexion = BaseDatos::conectar();
        $sql = "UPDATE usuarios
                SET nombre = :nombre, apellido = :apellido, telefono = :telefono, direccion = :direccion
                WHERE id_usuario = :id_usuario";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":apellido", $apellido);
        $stmt->bindParam(":telefono", $telefono);
        $stmt->bindParam(":direccion", $direccion);
        $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Eliminar usuario.
    public static function eliminar($id_usuario)
    {
        $conexion = BaseDatos::conectar();
        $sql = "DELETE FROM usuarios WHERE id_usuario = :id_usuario";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
