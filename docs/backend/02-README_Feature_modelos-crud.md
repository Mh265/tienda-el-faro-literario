# Feature/modelos-crud

Implementa los Modelos restantes de la capa de datos (`app/modelos/`), completando la capa **M** del patrón MVC para "El Faro Literario". Cada Modelo es una clase con métodos **estáticos** que reciben la conexión desde `BaseDatos::conectar()` (sin singleton, igual que se definió en `Feature/conexion-base-datos`) y ejecutan consultas preparadas con parámetros nombrados.

Ningún Modelo contiene lógica de negocio entre tablas (validaciones cruzadas, reglas de flujo, hasheo de contraseñas, etc.) — eso se resuelve en los Controladores, que son quienes conocen el contexto de la petición (sesión, formulario, reglas del negocio).

## Categoria.php

Modelo de la tabla `categorias` (géneros literarios).

- `obtenerTodos()` — lista todas las categorías, ordenadas por nombre.
- `obtenerPorId($id_categoria)` — una categoría puntual.
- `crear($nombre, $descripcion)` — inserta y devuelve el id generado.
- `actualizar($id_categoria, $nombre, $descripcion)` — edita nombre/descripción.
- `eliminar($id_categoria)` — elimina la categoría. Si tiene productos asociados, la base de datos rechazará el borrado (FK `ON DELETE RESTRICT`); el Controlador debe capturar esa excepción y avisar al usuario.

## Usuario.php

Modelo de la tabla `usuarios` (clientes y administradores).

- `obtenerTodos()` / `obtenerPorId($id_usuario)` — **no** incluyen la columna `password` en el SELECT, para no exponer el hash en listados ni en el perfil.
- `obtenerPorCorreo($correo)` — sí trae la fila completa (incluyendo `password`); es la consulta que usará el login para comparar con `password_verify()`.
- `crear(...)` — inserta un usuario nuevo. **No hashea la contraseña**: guarda tal cual el valor de `$password` que recibe. El hasheo con `password_hash()` es responsabilidad del `AuthController` antes de llamar a este método — así el Modelo se mantiene simple y el Controlador es el único lugar donde vive la lógica de seguridad de contraseñas.
- `actualizar($id_usuario, $nombre, $apellido, $telefono, $direccion)` — solo edita datos de perfil. Cambiar `correo` (por la restricción UNIQUE) o `password` se deja fuera de este método genérico; si se necesitan, conviene métodos dedicados (`actualizarPassword`, etc.) más adelante.
- `eliminar($id_usuario)` — elimina el usuario. Pedidos, reseñas y wishlist asociados dependen de las FK definidas en la base de datos (pedidos usa `RESTRICT`, reseñas y wishlist usan `CASCADE`).

## Pedido.php

Modelo de la tabla `pedidos` (cabecera de una compra).

- `obtenerTodos()` — todos los pedidos (uso típico: panel de administración).
- `obtenerPorId($id_pedido)` — un pedido puntual.
- `obtenerPorUsuario($id_usuario)` — pedidos de un cliente (uso típico: "Mis pedidos"). Es una consulta filtrada simple, no lógica de negocio.
- `crear($id_usuario, $total, $estado = 'pendiente')` — crea la cabecera del pedido. El cálculo del `total` a partir del carrito es responsabilidad del Controlador; el Modelo solo persiste el valor recibido.
- `actualizar($id_pedido, $total, $estado)` — actualiza total/estado. Las reglas de qué transiciones de estado son válidas (`pendiente → pagado → enviado → entregado`, o `cancelado`) las decide el Controlador; el Modelo no las valida.
- `eliminar($id_pedido)` — elimina el pedido (y en cascada su `detalle_pedido`, según la FK).

## DetallePedido.php

Modelo de la tabla `detalle_pedido` (líneas de cada pedido: producto, cantidad y precio unitario al momento de la compra).

- `obtenerPorPedido($id_pedido)` — método de lectura principal: trae todas las líneas de un pedido con un `JOIN` a `productos` (nombre, autor, imagen) para poder pintarlas directamente en la vista de detalle de pedido.
- `obtenerPorId($id_detalle)` — utilidad puntual, por ejemplo para validar un id antes de editar/eliminar.
- `crear($id_pedido, $id_producto, $cantidad, $precio)` — el `$precio` se guarda tal como llega: es una copia del precio del libro al momento de la compra (no una referencia viva a `productos.precio`), para que el total del pedido no cambie si el precio del libro cambia después. Ese "tomar el precio actual y copiarlo" lo hace el Controlador al armar el pedido desde el carrito.
- `actualizar($id_detalle, $cantidad)` — solo permite corregir la cantidad. `id_pedido`, `id_producto` y `precio` no deberían cambiar una vez registrada la venta; si un administrador necesita corregir algo distinto, lo correcto es eliminar la línea y crear una nueva.
- `eliminar($id_detalle)` — elimina una línea del pedido.

## Resena.php

Modelo de la tabla `resenas` (calificación de 1 a 5 y comentario de un usuario sobre un libro).

- `obtenerPorProducto($id_producto)` — reseñas de un libro con nombre/apellido del autor de cada reseña (`JOIN` a `usuarios`), pensado para la vista de detalle del libro.
- `obtenerPorId($id_resena)` — una reseña puntual.
- `crear($id_usuario, $id_producto, $calificacion, $comentario)` — inserta una reseña nueva.
- `actualizar($id_resena, $calificacion, $comentario)` — permite que el autor edite su propia reseña. Validar que quien edita sea realmente el dueño de la reseña (comparando contra el usuario en sesión) es tarea del Controlador.
- `eliminar($id_resena)` — elimina la reseña.

## Wishlist.php

Modelo de la tabla `wishlist` (lista de deseos).

- No tiene método `actualizar()`: un renglón de wishlist solo relaciona usuario-producto, no tiene datos propios que editar. La única operación real es agregar o quitar un favorito.
- `obtenerPorUsuario($id_usuario)` — libros favoritos de un usuario, con datos básicos del producto (`JOIN`) para la vista de wishlist.
- `obtenerPorId($id_wishlist)` — un registro puntual.
- `crear($id_usuario, $id_producto)` — agrega un favorito. La restricción `UNIQUE (id_usuario, id_producto)` de la tabla evita duplicados a nivel de base de datos; si ya existe, PDO lanza una excepción que el Controlador debe capturar y convertir en un mensaje amigable.
- `eliminar($id_wishlist)` — quita un favorito por su id interno.
- `eliminarPorUsuarioYProducto($id_usuario, $id_producto)` — método de conveniencia para el botón "quitar de favoritos" en catálogo/detalle, donde el frontend normalmente conoce el `id_producto` pero no el `id_wishlist`.

## Convenciones aplicadas

- Nombres de clase en `PascalCase` en singular (`Categoria`, `Usuario`, `Pedido`, `DetallePedido`, `Resena`, `Wishlist`), igual que `Libro.php`.
- Métodos en `camelCase` con verbos en español (`obtenerTodos`, `obtenerPorId`, `crear`, `actualizar`, `eliminar`).
- Todas las consultas usan `prepare()` + parámetros nombrados (`:id_producto`, `:correo`, etc.) — nunca concatenación de variables en el SQL.
- Cada método abre su propia conexión con `BaseDatos::conectar()`, sin guardar el objeto PDO como propiedad de clase ni reutilizar conexiones entre llamadas, siguiendo el mismo estilo simple de `BaseDatos.php`.
