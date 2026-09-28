# Feature/frontend-base-layout

## Objetivo
Sistema de estilos y layout compartido de "El Faro Literario": paleta, tipografías, encabezado, pie de página y página de inicio.

## Archivos
| Archivo | Rol |
|---|---|
| `assets/css/variables.css` | Variables de Bootstrap 5.3 (paleta y tipografías). Se carga **después** de `bootstrap.min.css` |
| `assets/css/styles.css` | Botones recoloreados y estilos por sección (navbar, pie, inicio, auth, catálogo, carrito, perfil, wishlist, pedidos, admin, detalle) |
| `includes/plantillas/header.php` / `footer.php` | Plantillas compartidas; cada vista define `$tituloPagina`, `$rutaBase` y opcionalmente `$scriptsPagina` |
| `includes/plantillas/admin-nav.php` | Navegación entre las 5 secciones del panel admin |
| `public/index.php` + `assets/js/inicio.js` | Inicio con hero y 4 libros destacados (`api/libros.php`) |

## Decisiones
- Todo el diseño se apoya en clases de Bootstrap; `styles.css` solo agrega lo que Bootstrap no cubre (colores de marca y tarjetas).
- `$rutaBase` es la ruta relativa de la vista a la raíz: así `assets/` y `api/` resuelven igual desde cualquier profundidad.
- `footer.php` carga los scripts en orden: Bootstrap → `api.js` → `auth.js` → `carrito.js` → scripts de la vista.
