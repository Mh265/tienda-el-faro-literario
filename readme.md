# 📚 El Faro Literario — Tienda en Línea de Libros

Proyecto final del curso de **Desarrollo Full Stack (INTECAP)**.
Aplicación web de comercio electrónico especializada en libros, construida con arquitectura **MVC**, **PHP orientado a objetos**, **MySQL (PDO)** y una **API REST** consumida con `fetch()` desde un frontend en **Bootstrap 5**.

> Basada en la plantilla de curso "Tienda en Línea de Productos", adaptada al rubro de libros.

**Leyenda de estado:** ✅ implementado · 🚧 en curso · 🔜 pendiente · 💡 opcional

---

## 1. Planteamiento del problema

Los procesos tradicionales de compra de libros (catálogo disperso, pedidos manuales, sin seguimiento) dificultan que el cliente consulte títulos, administre su compra y verifique el estado de sus pedidos. El administrador, a su vez, necesita una herramienta centralizada para gestionar libros, categorías, usuarios y pedidos.

**Solución:** una tienda en línea con catálogo, búsqueda y filtros, carrito, pedidos, reseñas, lista de deseos y un panel administrativo con CRUD completo, todo expuesto mediante una API REST en PHP.

---

## 2. Identidad visual

| Uso                         | Nombre         | HEX       |
|-----------------------------|----------------|-----------|
| Primario (marca, botones)   | Vino Editorial | `#6B2737` |
| Primario oscuro (hover)     | Vino Profundo  | `#4A1B27` |
| Secundario (acentos, links) | Dorado Sello   | `#C9A227` |
| Fondo claro                 | Papel          | `#F8F5F0` |
| Texto principal             | Tinta          | `#2B2B2B` |
| Éxito / disponible          | Verde Hoja     | `#3F7D5C` |
| Alerta / stock bajo         | Terracota      | `#C1502E` |

**Tipografías (Google Fonts):** Playfair Display (títulos y marca) · Inter (interfaz).

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
```

**Bootstrap 5.3:** las variables están en `assets/css/variables.css` y deben cargarse **después** de `bootstrap.min.css` para que sobrescriban las de `:root`. Ojo: `.btn-primary` define sus propias variables `--bs-btn-*`, así que los botones se recolorean en `assets/css/styles.css`, no solo en `variables.css`.

---

## 3. Arquitectura

**Patrón:** MVC · **Backend:** PHP 8.x POO + API REST + sesiones nativas · **BD:** MySQL 8.x con PDO y consultas preparadas · **Frontend:** HTML5 semántico + Bootstrap 5 + JavaScript (fetch + JSON) · **Versionado:** Git/GitHub por ramas.

```
Usuario → Vista (HTML/CSS/JS) → fetch() → API PHP (/api) → Controlador → Modelo → PDO → MySQL
                                                                                         │
Usuario ← Vista (actualiza DOM) ← Respuesta JSON ←──────────────────────────────────────┘
```

| Capa          | Responsabilidad                                                        | Ubicación                     |
|---------------|------------------------------------------------------------------------|-------------------------------|
| Vista         | Interfaz, formularios, consumo de la API                               | `public/`, `public/vistas/`, `assets/`      |
| API (rutas)   | Punto de entrada HTTP; enruta al controlador según método/acción       | `api/`                        |
| Controlador   | Validaciones, reglas de negocio, sesión/permisos, arma la respuesta    | `app/controladores/`          |
| Modelo        | Acceso a datos con PDO (una clase por tabla)                           | `app/modelos/`                |
| Núcleo        | Conexión, sesión, respuestas JSON, filtros de acceso, plantillas       | `includes/`                   |

---

## 4. Estructura de carpetas

```
tienda-el-faro-literario/
├── api/                        # Endpoints (auth, libros, categorias, pedidos, resenas, usuarios, wishlist)
├── app/
│   ├── controladores/          # AuthController, LibroController, CategoriaController,
│   │                           # PedidoController, ResenaController, WishlistController
│   └── modelos/                # Libro, Categoria, Usuario, Pedido, DetallePedido, Resena, Wishlist
├── assets/
│   ├── css/                    # variables.css, styles.css
│   ├── js/                     # api.js, catalogo.js, carrito.js, checkout.js, admin.js
│   └── img/uploads/            # portadas subidas (ignorado en git salvo .gitkeep)
├── bd/                         # tienda_libros.sql (estructura + datos iniciales)
├── docs/
│   ├── API.md                  # contrato de la API (lo mantiene Milton)
│   ├── backend/                # README de cada feature de backend
│   ├── frontend/               # README de cada feature de frontend
│   ├── wireframes/             # wireframes y mockups
│   └── pruebas/                # casos y rutinas de prueba
├── includes/
│   ├── ayudantes/              # AyudanteSesion, Respuesta
│   ├── config/                 # BaseDatos
│   ├── filtros/                # FiltroAutenticacion
│   └── plantillas/             # header.php y footer.php compartidos por todas las vistas
├── public/
│   ├── index.php               # página principal (inicio)
│   └── vistas/                 # login, registro, catalogo, detalle-libro, carrito, checkout,
│       │                       # mis-pedidos, wishlist
│       └── admin/              # libros, categorias, pedidos, usuarios
└── readme.md
```

---

## 5. Base de datos (`tienda_el_faro`)

Script completo en `bd/tienda_libros.sql` (estructura + seed: 12 categorías, 1 administrador, 5 clientes, 30 libros).

| Tabla            | Propósito                                                       | Relaciones clave                                  |
|------------------|-----------------------------------------------------------------|---------------------------------------------------|
| `usuarios`       | Clientes y administradores (`tipo_usuario`)                     | `correo` único                                    |
| `categorias`     | Géneros literarios                                              | 1:N con `productos`                               |
| `productos`      | **Libros** del catálogo (título en `nombre`, `autor`, `editorial`, `descripcion_corta`, `descripcion_larga`, `precio`, `cantidad` = stock, `imagen`, `fecha_publicacion`, `estado`) | FK → `categorias` (RESTRICT) |
| `pedidos`        | Cabecera de compra (`estado`: pendiente/pagado/enviado/entregado/cancelado) | FK → `usuarios`                       |
| `detalle_pedido` | Líneas del pedido; guarda el `precio` unitario al momento de comprar | FK → `pedidos` (CASCADE), `productos`        |
| `resenas`        | Calificación 1–5 y comentario                                   | FK → `usuarios`, `productos`                      |
| `wishlist`       | Lista de deseos; `UNIQUE (id_usuario, id_producto)`             | FK → `usuarios`, `productos`                      |

Decisiones: el campo **ISBN se excluye** del modelo; `descripcion` se dividió en corta (tarjetas) y larga (detalle); el carrito **no tiene tabla** (vive en el navegador hasta el checkout).

---

## 6. Módulos backend (Milton)

| Módulo                         | Archivos                                                                 | Estado |
|--------------------------------|--------------------------------------------------------------------------|--------|
| Conexión a BD                  | `includes/config/BaseDatos.php` (PDO directo, `ERRMODE_EXCEPTION`)       | ✅     |
| Modelos CRUD                   | `Categoria`, `Usuario`, `Pedido`, `DetallePedido`, `Resena`, `Wishlist`  | ✅     |
| Autenticación y sesión         | `AuthController`, `AyudanteSesion`, `Respuesta`, `api/auth.php`          | ✅     |
| Catálogo de libros             | `Libro.php`, `LibroController`, `api/libros.php` (búsqueda, filtros, CRUD, portada) | 🔜 |
| Categorías                     | `CategoriaController`, `api/categorias.php`                              | 🔜     |
| Control de acceso              | `FiltroAutenticacion` (requiere sesión / requiere administrador)         | 🔜     |
| Pedidos                        | `PedidoController`, `api/pedidos.php` (transacción + descuento de stock) | 🔜     |
| Reseñas                        | `ResenaController`, `api/resenas.php`                                    | 🔜     |
| Lista de deseos                | `WishlistController`, `api/wishlist.php`                                 | 🔜     |
| Usuarios (perfil y admin)      | `api/usuarios.php` + controlador                                         | 🔜     |
| Recuperar contraseña (RF03)    | Flujo de restablecimiento                                                | 💡     |
| Confirmación de pedido (RF20)  | Correo o comprobante en pantalla                                         | 💡     |

---

## 7. API REST (contrato propuesto)

Formato: JSON. Los contratos definitivos se documentan en `docs/API.md` y en el README de cada feature. Los nombres de campos coinciden con las columnas de la BD (`snake_case`).

| Endpoint            | Operaciones                                                                  | Acceso                              |
|---------------------|------------------------------------------------------------------------------|-------------------------------------|
| `api/auth.php`      | `accion=registro`, `login`, `logout`, `verificar-sesion`                     | Público / sesión                    |
| `api/libros.php`    | GET lista (`q`, `id_categoria`, `precio_min`, `precio_max`, `disponible`, `orden`), GET `?id=`, POST, PUT, DELETE | Lectura pública · escritura admin |
| `api/categorias.php`| GET lista / GET `?id=`, POST, PUT, DELETE                                    | Lectura pública · escritura admin   |
| `api/pedidos.php`   | POST (crear desde carrito), GET (mis pedidos / todos), GET `?id=`, PUT estado | Cliente · admin                    |
| `api/resenas.php`   | GET `?id_producto=`, POST, PUT, DELETE                                       | Lectura pública · escritura cliente |
| `api/wishlist.php`  | GET (mi lista), POST, DELETE                                                 | Cliente                             |
| `api/usuarios.php`  | GET / PUT perfil propio · GET lista, PUT, DELETE (admin)                     | Cliente · admin                     |

---

## 8. Módulos frontend (Jenifer)

| Vista / módulo                | Archivos                                              | Estado |
|-------------------------------|-------------------------------------------------------|--------|
| Sistema de estilos y layout   | `variables.css`, `styles.css`, `includes/plantillas/header.php` y `footer.php`, `public/index.php` | 🔜 |
| Cliente de API                | `assets/js/api.js`                                    | 🔜     |
| Registro e inicio de sesión   | `login.php`, `registro.php`, `auth.js`                | 🔜     |
| Catálogo (búsqueda y filtros) | `catalogo.php`, `catalogo.js`                         | 🔜     |
| Detalle de libro              | `detalle-libro.php`, `detalle-libro.js`               | 🔜     |
| Carrito                       | `carrito.php`, `carrito.js`                           | 🔜     |
| Checkout y confirmación       | `checkout.php`, `checkout.js`                         | 🔜     |
| Mis pedidos                   | `mis-pedidos.php`                                     | 🔜     |
| Reseñas                       | componente en detalle de libro                        | 🔜     |
| Lista de deseos               | `wishlist.php`                                        | 🔜     |
| Admin: libros                 | `admin/libros.php`, `admin.js`                        | 🔜     |
| Admin: categorías             | `admin/categorias.php`                                | 🔜     |
| Admin: pedidos                | `admin/pedidos.php`                                   | 🔜     |
| Admin: usuarios               | `admin/usuarios.php`                                  | 🔜     |

---

## 9. Funcionalidades por rol y trazabilidad con el Product Backlog

| ID   | Funcionalidad                          | Rol      | Prioridad |
|------|----------------------------------------|----------|-----------|
| RF01 | Registro de usuarios                   | Público  | Alta      |
| RF02 | Inicio y cierre de sesión              | Todos    | Alta      |
| RF03 | Recuperar contraseña                   | Todos    | Media     |
| RF04 | Catálogo de libros                     | Público  | Alta      |
| RF05 | Búsqueda (título, autor)               | Público  | Alta      |
| RF06 | Filtros (categoría, precio, disponibilidad, popularidad) | Público | Alta |
| RF07 | Detalle del libro                      | Público  | Alta      |
| RF08 | Agregar al carrito                     | Cliente  | Alta      |
| RF09 | Modificar cantidades                   | Cliente  | Alta      |
| RF10 | Eliminar del carrito                   | Cliente  | Alta      |
| RF11 | Registrar pedidos                      | Cliente  | Alta      |
| RF12 | Historial de pedidos                   | Cliente  | Alta      |
| RF13 | Proceso de pago (simulado)             | Cliente  | Alta      |
| RF14 | Reseñas y calificaciones               | Cliente  | Media     |
| RF15 | Lista de deseos                        | Cliente  | Media     |
| RF16 | Administrar libros (CRUD)              | Admin    | Alta      |
| RF17 | Administrar categorías (CRUD)          | Admin    | Alta      |
| RF18 | Administrar usuarios                   | Admin    | Alta      |
| RF19 | Consultar información vía API          | Sistema  | Alta      |
| RF20 | Confirmación del pedido                | Cliente  | Media     |

**Requerimientos no funcionales:** seguridad (hash, validación, consultas preparadas, sesiones, permisos por rol, HTTPS en producción) · usabilidad (intuitivo, móvil, mensajes claros) · rendimiento (imágenes optimizadas, consultas eficientes) · diseño (responsivo, contraste, tipografía legible, navegación consistente, tarjetas, formularios organizados).

---

## 10. Reglas de negocio y seguridad

- Contraseñas con `password_hash()` / `password_verify()`; el hash se genera en el controlador, nunca en el modelo.
- Solo consultas preparadas con parámetros nombrados.
- El cliente **nunca envía precios ni totales**: envía `id_producto` y `cantidad`; el servidor calcula con los precios de la BD.
- Crear un pedido es una **transacción**: valida stock, inserta `pedidos` + `detalle_pedido` y descuenta `cantidad`.
- Un libro con ventas no se elimina físicamente (FK `RESTRICT`): se da de baja con `estado = 'inactivo'`. El catálogo público solo muestra libros `activo`.
- Una categoría con libros no puede eliminarse (FK `RESTRICT`); el controlador devuelve un mensaje claro.
- Las rutas de administración exigen sesión de tipo `administrador`; el rol se valida en el servidor, no solo en la interfaz.
- Wishlist: el `UNIQUE` evita duplicados; el controlador traduce la excepción a un mensaje amigable.
- Reseñas: solo el dueño edita su reseña (RF07 sugiere que sea sobre libros adquiridos; regla por confirmar).
- Subida de portadas: validar tipo y tamaño, renombrar el archivo, guardar en `assets/img/uploads/`.
- Credenciales fuera del repositorio (`.gitignore`); HTTPS en producción.

---

## 11. Convenciones

| Elemento                | Convención                          | Ejemplo                          |
|-------------------------|-------------------------------------|----------------------------------|
| Tablas y columnas (BD)  | `snake_case`, español               | `id_producto`, `fecha_registro`  |
| Clases PHP              | `PascalCase`, singular en modelos   | `Libro`, `PedidoController`      |
| Métodos y variables     | `camelCase` (JS) · estilo del curso (PHP) | `obtenerPorId`, `agregarAlCarrito` |
| Archivos de vista y JS  | minúsculas con guion                | `detalle-libro.php`              |
| Ramas                   | `Feature/nombre-de-la-feature`      | `Feature/frontend-catalogo`      |
| Commits                 | Verbo en infinitivo + alcance       | `Agregar filtro por categoría`   |

---

## 12. Flujo de trabajo con Git

**Ramas:** `master` → `release/1.0` → `develop` → `Feature/*`. Cada feature nace de `develop`, se integra con Pull Request y se documenta con un README propio (backend en `docs/backend/`, frontend en `docs/frontend/`, nombrado como la feature, p. ej. `docs/frontend/frontend-catalogo.md`).

### Features de backend

| Rama                              | Contenido                                                | Estado |
|-----------------------------------|----------------------------------------------------------|--------|
| `Feature/conexion-base-datos`     | `BaseDatos.php`                                          | ✅     |
| `Feature/modelos-crud`            | Modelos con CRUD PDO                                     | ✅     |
| `Feature/auth-controller`         | Registro, login, logout, sesión                          | ✅     |
| `Feature/libro-api`               | Modelo `Libro`, controlador y endpoint, búsqueda y filtros | 🔜   |
| `Feature/filtro-autenticacion`    | Protección por sesión y rol                              | 🔜     |
| `Feature/categoria-api`           | Controlador y endpoint de categorías                     | 🔜     |
| `Feature/pedido-api`              | Creación transaccional, historial, cambio de estado      | 🔜     |
| `Feature/resena-api`              | Reseñas                                                  | 🔜     |
| `Feature/wishlist-api`            | Lista de deseos                                          | 🔜     |
| `Feature/usuario-api`             | Perfil y administración de usuarios                      | 🔜     |
| `Feature/recuperar-password`      | RF03                                                     | 💡     |
| `Feature/despliegue-pruebas`      | Hosting, HTTPS, casos de prueba                          | 🔜     |

### Features de frontend

| Rama                                  | Contenido                                        | Depende de                 |
|---------------------------------------|--------------------------------------------------|----------------------------|
| `Feature/frontend-base-layout`        | Estilos, encabezado/pie, página principal        | —                          |
| `Feature/frontend-api-cliente`        | `api.js`                                         | `auth-controller` ✅       |
| `Feature/frontend-auth`               | Login, registro, estado de sesión                | `auth-controller` ✅       |
| `Feature/frontend-catalogo`           | Tarjetas, búsqueda, filtros                      | `libro-api`                |
| `Feature/frontend-detalle-libro`      | Detalle del libro                                | `libro-api`                |
| `Feature/frontend-carrito`            | Carrito en el navegador                          | —                          |
| `Feature/frontend-checkout`           | Checkout y confirmación                          | `pedido-api`               |
| `Feature/frontend-mis-pedidos`        | Historial del cliente                            | `pedido-api`               |
| `Feature/frontend-admin-libros`       | CRUD de libros                                   | `libro-api`, `filtro-autenticacion` |
| `Feature/frontend-admin-categorias`   | CRUD de categorías                               | `categoria-api`            |
| `Feature/frontend-admin-pedidos`      | Gestión de pedidos                               | `pedido-api`               |
| `Feature/frontend-admin-usuarios`     | Gestión de usuarios                              | `usuario-api`              |
| `Feature/frontend-resenas`            | Reseñas y estrellas                              | `resena-api`               |
| `Feature/frontend-wishlist`           | Lista de deseos                                  | `wishlist-api`             |
| `Feature/frontend-recuperar-password` | RF03 (opcional)                                  | `recuperar-password`       |
| `Feature/frontend-pulido-final`       | Responsivo, accesibilidad, pruebas de interfaz   | Todas                      |

---

## 13. Entregables finales (Lineamientos de presentación)

- [ ] Descripción del problema
- [ ] Product Backlog (RF/RNF e historias de usuario)
- [ ] Wireframes / mockups
- [ ] Esquemas de BD (modelo relacional)
- [ ] `tienda_libros.sql`
- [ ] Descripción de tecnologías utilizadas
- [ ] Casos y rutinas de prueba
- [ ] Proyecto comprimido
- [ ] URL de GitHub
- [ ] URL del proyecto en producción (hosting)
- [ ] Demo: registro → login → catálogo/búsqueda/filtros → CRUD de libros → carrito → pedido en `pedidos` + `detalle_pedido`

---

## 14. Instalación local (XAMPP)

1. Clonar el repositorio en la carpeta `htdocs`.
2. Importar `bd/tienda_libros.sql` en MySQL (crea la BD `tienda_el_faro`).
3. Ajustar host, usuario y contraseña en `includes/config/BaseDatos.php` si difieren de los locales.
4. Abrir `http://localhost/<carpeta-del-proyecto>/`.

Usuario administrador de prueba: `admin@elfaroliterario.com` (la contraseña se entrega aparte del repositorio).

---

## 15. Decisiones tomadas y abiertas

**Tomadas**

- Acceso a las vistas por **acceso directo** (`public/vistas/*.php`), sin front controller, por simplicidad y tiempo.
- Cada vista es una página completa que incluye `includes/plantillas/header.php` y `footer.php` (con `__DIR__`) y define antes `$tituloPagina` y `$rutaBase` (ruta relativa desde la vista hasta la raíz del proyecto, para `assets/` y `api/`).
- Las vistas privadas (checkout, mis-pedidos, wishlist, admin) incluyen al inicio `includes/filtros/FiltroAutenticacion.php`, que valida sesión y rol en el servidor.

**Abiertas**

- Formato exacto de respuesta JSON y códigos HTTP (`Respuesta.php`) documentado en `docs/API.md`.
- Regla de reseñas: ¿solo compradores?
- Alcance del pago (RF13): simulado con método de pago seleccionable.

---

## 16. Equipo

| Rol      | Integrante | Responsabilidad                                                     |
|----------|------------|---------------------------------------------------------------------|
| Backend  | Milton     | Base de datos, estructura MVC, modelos, controladores, API PHP      |
| Frontend | Jenifer    | Wireframes, mockups, maquetación Bootstrap, consumo de la API       |