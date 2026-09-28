# Feature/frontend-perfil

## Objetivo
Consultar y editar los datos personales (nombre, apellido, teléfono, dirección).

## Archivos
`public/vistas/perfil.php` (vista privada, en `public/vistas/`) · `assets/js/perfil.js`

## Endpoints consumidos
`GET api/usuarios.php` · `PUT api/usuarios.php`

## Decisiones
- Correo, contraseña y tipo de usuario no se editan (fuera de alcance junto con RF03).
- Se accede desde el menú de usuario del navbar ("Mi perfil").
