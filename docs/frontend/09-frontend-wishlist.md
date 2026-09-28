# Feature/frontend-wishlist

## Objetivo
Lista de deseos del cliente (RF15).

## Archivos
`public/vistas/wishlist.php` (vista privada, debe estar en `public/vistas/`) · `assets/js/wishlist.js`

## Endpoints consumidos
`GET api/wishlist.php` · `DELETE api/wishlist.php?id_producto=`. Agregar se hace desde el detalle del libro (`POST`).

## Decisiones
- "Agregar al carrito" desde la wishlist no conoce el stock (el endpoint no lo devuelve); el servidor lo valida al crear el pedido.
- Mensaje de lista vacía: indica agregar libros desde el detalle de cada uno.
