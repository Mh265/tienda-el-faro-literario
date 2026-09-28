# Feature/frontend-admin (dashboard, libros, categorías, pedidos y usuarios)

## Objetivo
Panel de administración con CRUD completo (RF16, RF17, RF18) y gestión de pedidos.

## Archivos
| Archivo | Rol |
|---|---|
| `public/vistas/admin/index.php` | Dashboard (ingresos, libros activos, usuarios, pedidos recientes) |
| `public/vistas/admin/libros.php` | CRUD de libros con modal |
| `public/vistas/admin/categorias.php` | CRUD de categorías con modal |
| `public/vistas/admin/pedidos.php` | Listado y cambio de estado |
| `public/vistas/admin/usuarios.php` | Listado, edición y eliminación |
| `assets/js/admin.js` | Lógica de las 5 vistas (cada sección se activa si encuentra sus elementos) |

Todas las vistas usan `FiltroAutenticacion::protegerVistaAdministrador('../login.php')`.

## Decisiones
- **Nombres en pedidos:** `Pedido::obtenerTodos()` no hace JOIN, así que `admin.js` cruza `id_usuario` con `usuarios.php?accion=admin-listado`. Si ese listado falla se muestra `Usuario #id`.
- **Portada al editar un libro:** `PUT` no acepta archivo y el backend guarda `imagen = NULL` si no llega. El frontend reenvía el nombre de la portada actual para no borrarla. Solución definitiva (backend): que `Libro::actualizar()` no toque `imagen`.
- Dashboard cuenta como "libros en catálogo" solo los activos.
- Los mensajes de los modales se limpian al abrirlos.
- Baja de libros es soft delete; categorías y usuarios con dependencias devuelven 409 y se muestra el mensaje.

## Cómo probarlo
Crear, editar (verificar que la portada se conserva), dar de baja y reactivar un libro; crear, editar y eliminar una categoría (con y sin libros); cambiar estado de un pedido; editar y eliminar un usuario (con pedidos, sin pedidos y a uno mismo); entrar con un cliente a `admin/` (debe dar 403).
