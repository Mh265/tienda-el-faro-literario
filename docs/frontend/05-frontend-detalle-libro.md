# Feature/frontend-detalle-libro (incluye reseñas y wishlist en el detalle)

## Objetivo
Vista de detalle de un libro (RF07) con agregar al carrito (RF08), lista de deseos (RF15) y reseñas (RF14). Usa el wireframe `wf-03` como guía.

## Archivos
| Archivo | Rol |
|---|---|
| `public/vistas/detalle-libro.php` | Vista pública |
| `assets/js/detalle-libro.js` | Carga del libro, carrito, wishlist y reseñas |

## Endpoints consumidos
`GET api/libros.php?id=` · `POST api/auth.php?accion=verificar-sesion` · `GET/POST/DELETE api/wishlist.php` · `GET/POST/PUT/DELETE api/resenas.php`

## Relación con el wireframe wf-03
| Wireframe | Implementación |
|---|---|
| Portada a la izquierda | `#portadaDetalle` (portada por defecto si no hay imagen) |
| Recuadro superior derecho | Categoría, título, autor, precio, stock, cantidad y botones de carrito / lista de deseos |
| Recuadro oscuro con líneas de texto | Sinopsis (`descripcion_larga`) |
| 5 recuadros bajo el marco | Ficha técnica: autor, editorial, categoría, publicación y stock |
| Línea punteada + sección inferior | Reseñas |
| Ícono de campana | No implementado: el backend no tiene alertas de disponibilidad |

## Decisiones
- **Tope de stock:** la cantidad se valida en JS (el atributo `max` no impide escribir un número mayor) y el carrito guarda el stock para ponerle tope.
- **Reseña única por usuario desde la interfaz:** si el usuario ya reseñó el libro, el formulario pasa a "Editar tu reseña" (PUT) en lugar de crear otra. El backend no prohíbe duplicados; queda como regla abierta.
- **Eliminar reseña:** el dueño elimina la suya y el administrador puede eliminar cualquiera (moderación).
- Comparaciones de id y calificación con `Number(...)`, para no depender de que PHP devuelva enteros.
- Todo texto de la API se asigna con `textContent`.

## Cómo probarlo
Libro con y sin stock; cantidad mayor al stock; agregar dos veces el mismo libro; wishlist con y sin sesión; reseña nueva, edición, eliminación propia y como administrador; libro inexistente (`?id=9999`) y sin `?id`.
