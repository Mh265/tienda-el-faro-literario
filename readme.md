# 📚 El Faro Literario — Tienda en Línea de Libros

Proyecto final del curso de **Desarrollo Full Stack (INTECAP)**.

Aplicación web de comercio electrónico especializada en libros. Los clientes pueden explorar el catálogo, buscar y filtrar, guardar libros en una lista de deseos, armar un carrito, generar pedidos y dejar reseñas. Los administradores gestionan libros, categorías, pedidos y usuarios desde un panel propio.

---

## 1. Tecnologías utilizadas

| Capa | Tecnología | Uso en el proyecto |
|---|---|---|
| Frontend | HTML5 semántico | Estructura de las vistas (`header`, `nav`, `main`, `section`, `footer`, formularios, tablas) |
| Frontend | CSS3 + Bootstrap 5.3 (CDN) | Diseño responsivo, cuadrícula, tarjetas, modales, formularios y componentes |
| Frontend | JavaScript (vanilla) | Eventos, manipulación del DOM, carrito en `localStorage`, consumo de la API con `fetch()` y JSON |
| Frontend | Google Fonts | Playfair Display (títulos y marca) e Inter (interfaz) |
| Backend | PHP 8.x orientado a objetos | Controladores, modelos, validaciones, reglas de negocio y sesiones nativas |
| Backend | API REST (JSON) | Comunicación entre el frontend y el backend |
| Base de datos | MySQL 8.x + PDO | Consultas preparadas con parámetros nombrados y transacciones |
| Servidor local | XAMPP (Apache + PHP + MySQL) | Entorno de desarrollo |
| Control de versiones | Git y GitHub | Trabajo por ramas (`master`, `develop`, `Feature/*`) y Pull Requests |

---

## 2. Identidad visual y estilos

| Uso | Nombre | HEX |
|---|---|---|
| Primario (marca, botones) | Vino Editorial | `#6B2737` |
| Primario oscuro (hover, pie de página) | Vino Profundo | `#4A1B27` |
| Secundario (acentos, insignias) | Dorado Sello | `#C9A227` |
| Fondo | Papel | `#F8F5F0` |
| Texto principal | Tinta | `#2B2B2B` |
| Éxito / disponible | Verde Hoja | `#3F7D5C` |
| Alerta / sin stock | Terracota | `#C1502E` |

- **Bootstrap 5.3** se personaliza con dos archivos que se cargan **después** de `bootstrap.min.css`:
  - `assets/css/variables.css`: sobrescribe las variables `--bs-*` de `:root` (colores, fuentes).
  - `assets/css/styles.css`: recolorea los botones (`.btn-primary`, `.btn-outline-primary`, `.btn-secondary` definen sus propias variables `--bs-btn-*`) y contiene los estilos propios, organizados en secciones por módulo (navbar, inicio, catálogo, carrito, perfil, admin, etc.).
- Diseño **responsivo** (móvil primero), tarjetas para los libros, formularios organizados y navegación consistente mediante un encabezado y pie de página compartidos.
- Controles HTML acordes a cada dato: `email`, `password`, `tel`, `number`, `date`, `select`, `textarea`, `file` y `search`.

---

## 3. Arquitectura

El proyecto sigue el patrón **MVC**. El flujo de una operación es:

```
Vista (HTML + JS) → fetch() → api/*.php → Controlador → Modelo → PDO → MySQL
                                                                      ↓
Vista (actualiza el DOM) ← respuesta JSON { exito, mensaje, datos } ←┘
```

Las vistas se abren directamente por su URL (sin front controller). Cada una define `$tituloPagina` y `$rutaBase` e incluye el encabezado y pie compartidos (`includes/plantillas/`).

```
tienda-el-faro-literario/
├── api/                      # Endpoints REST (auth, libros, categorias, pedidos, resenas, usuarios, wishlist)
├── app/
│   ├── controladores/        # Lógica de cada recurso y validaciones
│   └── modelos/              # Acceso a datos con PDO (Libro, Categoria, Usuario, Pedido, DetallePedido, Resena, Wishlist)
├── assets/
│   ├── css/                  # variables.css, styles.css
│   ├── js/                   # api.js, auth.js, carrito.js, catalogo.js, detalle-libro.js, checkout.js, ...
│   └── img/                  # logo, portada por defecto y uploads/ (portadas subidas)
├── bd/                       # tienda_libros.sql (estructura + datos iniciales)
├── includes/
│   ├── ayudantes/            # AyudanteSesion, AyudanteArchivo, Respuesta
│   ├── config/               # BaseDatos (conexión PDO)
│   ├── filtros/              # FiltroAutenticacion (acceso por sesión y rol)
│   └── plantillas/           # header.php, footer.php, admin-nav.php
├── public/
│   ├── index.php             # Página de inicio
│   └── vistas/               # Vistas del cliente
│       └── admin/            # Vistas del panel de administración
└── readme.md
```

---

## 4. Base de datos (`tienda_el_faro`)

El script `bd/tienda_libros.sql` crea la estructura y carga datos iniciales (12 categorías, 1 administrador, 5 clientes y 30 libros).

| Tabla | Propósito |
|---|---|
| `usuarios` | Clientes y administradores (`tipo_usuario`); `correo` único |
| `categorias` | Géneros literarios |
| `productos` | Libros del catálogo (título en `nombre`, `autor`, `editorial`, descripciones corta y larga, `precio`, `cantidad` como stock, `imagen`, `estado`) |
| `pedidos` | Cabecera de cada compra y su estado |
| `detalle_pedido` | Líneas del pedido con el precio unitario al momento de comprar |
| `resenas` | Calificación (1 a 5) y comentario; una reseña por usuario y libro |
| `wishlist` | Lista de deseos; sin duplicados por usuario y libro |

El carrito no tiene tabla: vive en el navegador (`localStorage`) hasta que se confirma el pedido.

---

## 5. API REST

Formato de respuesta: `{ "exito": true|false, "mensaje": "...", "datos": ... }`. Los nombres de campos coinciden con las columnas de la base de datos. El contrato detallado de cada endpoint está en `docs/API.md`.

| Endpoint | Operaciones | Acceso |
|---|---|---|
| `api/auth.php` | Registro, inicio y cierre de sesión, verificación de sesión | Público / sesión |
| `api/libros.php` | Catálogo con búsqueda y filtros, detalle, crear, editar, dar de baja y reactivar | Lectura pública · escritura administrador |
| `api/categorias.php` | Listar, detalle, crear, editar, eliminar | Lectura pública · escritura administrador |
| `api/pedidos.php` | Crear pedido, historial propio, listado general, detalle, cambio de estado | Cliente · administrador |
| `api/resenas.php` | Listar por libro, crear, editar, eliminar | Lectura pública · escritura cliente |
| `api/wishlist.php` | Ver, agregar y quitar libros | Cliente |
| `api/usuarios.php` | Perfil propio y gestión de usuarios | Cliente · administrador |

---

## 6. Funcionalidades

**Cliente:** registro e inicio de sesión · catálogo con búsqueda por título o autor y filtros por categoría, precio y disponibilidad · detalle del libro con reseñas · carrito (agregar, modificar cantidades, eliminar) · checkout con pago simulado y confirmación en pantalla · historial de pedidos · lista de deseos · perfil editable.

**Administrador:** panel con resumen de la tienda · CRUD de libros (con portada) y categorías · gestión de pedidos y sus estados · gestión de usuarios.

**Fuera de alcance:** recuperación de contraseña y envío de correos de confirmación.

---

## 7. Seguridad

- Contraseñas con `password_hash()` / `password_verify()`.
- Consultas preparadas en todos los modelos.
- El cliente nunca envía precios ni totales: envía `id_producto` y `cantidad`, y el servidor calcula con los precios de la base de datos.
- Crear un pedido es una **transacción**: valida stock, inserta `pedidos` y `detalle_pedido` y descuenta el inventario.
- Permisos por rol validados en el servidor (`FiltroAutenticacion`); el frontend solo muestra u oculta opciones.
- Textos de la API insertados en el DOM con `textContent` para evitar XSS.
- Subida de portadas con validación de tipo y tamaño, y archivos renombrados.
- Baja lógica de libros (`estado = 'inactivo'`) para conservar el historial de ventas; llaves foráneas con `RESTRICT` para proteger la integridad de los datos.

---

## 8. Instalación local (XAMPP)

1. Clonar el repositorio dentro de la carpeta `htdocs`.
2. Importar `bd/tienda_libros.sql` en MySQL (crea la base `tienda_el_faro`).
3. Ajustar host, usuario y contraseña en `includes/config/BaseDatos.php` si difieren de los locales.
4. Abrir `http://localhost/<carpeta-del-proyecto>/public/index.php`.

Usuario administrador de prueba: `admin@elfaroliterario.com` (la contraseña se entrega aparte del repositorio).

---

## 9. Equipo

| Rol | Integrante | Responsabilidad |
|---|---|---|
| Backend | Milton | Base de datos, estructura MVC, modelos, controladores y API PHP |
| Frontend | Jenifer | Wireframes y mockups, maquetación con Bootstrap y consumo de la API con JavaScript |