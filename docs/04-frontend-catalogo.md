# Feature/frontend-catalogo

## Objetivo
Implementar el catálogo de libros de "El Faro Literario" con búsqueda y
filtros combinables (RF04, RF05, RF06 salvo la opción de "popularidad",
que aún no existe en el backend), consumiendo `api/libros.php`
(`Feature/libro-api`, ya implementado por Milton).

## Archivos que agrega/modifica esta feature

| Archivo | Cambio |
|---|---|
| `public/vistas/catalogo.php` | Vista nueva: formulario de filtros + grid de tarjetas de libros |
| `assets/js/catalogo.js` | Nuevo: lógica de filtros, fetch al catálogo, mock de categorías, render de tarjetas |
| `assets/css/styles.css` | Nueva sección `CATÁLOGO`: estilos de `.tarjeta-filtros`, `.badge-categoria`, `.badge-sin-stock` |

## Endpoint consumido

`GET api/libros.php` → `app/controladores/LibroController.php` (ya documentado en `docs/API.md`).

Parámetros usados, todos opcionales y enviados solo si el usuario los llenó:
`q`, `id_categoria`, `precio_min`, `precio_max`, `disponible=1`, `orden`
(`recientes` por defecto, `precio_asc`, `precio_desc`, `nombre_asc`).

**No se incluyó una opción de orden por "popularidad"**: RF06 la menciona,
pero el backend no calcula esa métrica todavía (confirmado con Milton).
Queda pendiente para cuando exista esa funcionalidad.

## Mock de categorías

`api/categorias.php` sigue vacío (`Feature/categoria-api` pendiente). Mientras
tanto, `catalogo.js` usa `obtenerCategoriasMock()`, que devuelve las 12
categorías del seed de `bd/tienda_libros.sql` con sus `id_categoria` reales
(1 al 12).

**Cómo reemplazarlo cuando exista el endpoint:** cambiar el cuerpo de
`obtenerCategoriasMock()` por:

\`\`\`js
async function obtenerCategorias() {
  // api/categorias.php → app/controladores/CategoriaController.php
  const resultado = await llamarApi('categorias.php', 'GET');
  return resultado.exito ? resultado.datos : [];
}
\`\`\`

y ajustar `poblarSelectCategorias()` para que sea `async` y haga `await`
a esa función. El resto del archivo no cambia.

## Decisiones de arquitectura

- **Desviación del wireframe `wf-02`:** el wireframe mostraba 6 botones de
  acceso rápido además del formulario de filtros. Se fusionaron dentro del
  `<select>` de categoría del formulario, para no duplicar la selección de
  categoría en dos controles distintos (hay 12 categorías, no 6, así que
  los botones no alcanzaban de todas formas).
- **Filtros combinables con un solo `fetch`:** cada cambio de filtro (o el
  submit del formulario) reconstruye la query string completa desde el
  estado actual del formulario y pide el catálogo de nuevo. No se guarda
  estado de filtros en el cliente entre peticiones: el formulario mismo
  es la única fuente de verdad.
- **Categoría, orden y disponibilidad se aplican al cambiar (`change`)**;
  texto y precio esperan al botón "Aplicar filtros", para no lanzar una
  petición por cada tecla presionada.
- **El buscador del navbar precarga el campo de búsqueda del catálogo**
  leyendo `window.location.search` al cargar la página (no se usa
  `localStorage` ni `sessionStorage` para esto).
- **Sin paginación ni scroll infinito**, porque `api/libros.php` no la
  implementa: el catálogo entero (hasta 30 libros en el seed) se carga y
  se muestra de una vez.
- **`textContent` en todo dato proveniente de la API** (título, autor,
  categoría, mensajes de error), nunca `innerHTML`, para evitar XSS.

## Cómo probarlo

Ver checklist completa entregada en el chat de esta feature: catálogo sin
filtros, búsqueda desde el navbar, cada filtro por separado y combinado,
filtro imposible (catálogo vacío), backend caído, y responsivo en móvil,
tablet y escritorio.

## Pendiente para features futuras

- Reemplazar el mock de categorías cuando `Feature/categoria-api` esté
  lista (instrucciones arriba).
- El botón "Ver detalle" enlaza a `detalle-libro.php?id=...`, vista que se
  implementará en `Feature/frontend-detalle-libro`.
- Evaluar si conviene agregar orden por "popularidad" cuando el backend
  tenga esa métrica (RF06).