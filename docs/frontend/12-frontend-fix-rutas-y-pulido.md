# Fix/frontend-rutas y Feature/frontend-pulido-final

## Correcciones (Fix/frontend-rutas)
| Cambio | Motivo |
|---|---|
| `public/wishlist.php` y `public/perfil.php` → `public/vistas/` | Sus rutas (`../../`) y los enlaces del navbar están pensados para `vistas/` |
| `assets/js/pefil.js` → `perfil.js` | `perfil.php` carga `perfil.js` |
| `catalogo.php`: comentario dentro del bloque PHP | Se imprimía como texto antes del `<!DOCTYPE>` |
| Navbar: enlace "Mi perfil" | `perfil.php` no era accesible |
| Logout redirige al inicio | En páginas privadas el usuario seguía viendo la vista |
| Login vuelve a la vista de origen (`?volver=`) | Desde checkout se perdía el contexto; el destino se valida contra una lista blanca |
| Mensaje de `wishlist.js` | Hacía referencia a una opción que ya existe |

## Pulido (Feature/frontend-pulido-final)
- Tope de stock en detalle y carrito.
- Reseñas: editar (formulario precargado) y eliminar.
- Admin: nombres en pedidos, portada no se borra al editar, contador de libros activos, modales sin mensajes viejos.
- Accesibilidad: `label` asociado a cada input de cantidad y `aria-label` en el selector de estado.

## Documentación
Los README de frontend viven en `docs/frontend/`; se eliminaron los placeholders (`RM-Vacio.MD`, `01-feature1ejemplo.MD`).
