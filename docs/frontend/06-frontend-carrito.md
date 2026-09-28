# Feature/frontend-carrito

## Objetivo
Carrito de compras que vive en el navegador (RF08, RF09, RF10). No hay tabla `carrito`: solo al hacer checkout se envían `id_producto` y `cantidad`.

## Archivos
| Archivo | Rol |
|---|---|
| `public/vistas/carrito.php` | Vista pública del carrito |
| `assets/js/carrito.js` | Lógica del carrito y contador del navbar (se carga en todas las páginas) |

## Decisiones
- `localStorage` (clave `elFaroCarrito`) guarda id, nombre, autor, precio, imagen, stock (si se conoce) y cantidad.
- **Tope de stock opcional:** si el libro llega desde el detalle se conoce el stock y se limita la cantidad; si llega desde la wishlist no se conoce y lo valida el servidor al crear el pedido.
- `agregarAlCarrito(libro, cantidad, stock)` devuelve `{ cantidadEnCarrito, limitadoPorStock }` para que la vista pueda avisar al usuario.
- El precio guardado es solo informativo: el servidor recalcula con la BD.

## Cómo probarlo
Agregar, cambiar cantidad (incluyendo valores inválidos y mayores al stock), quitar, vaciar por completo, recargar la página (persistencia) y revisar el contador del navbar.
