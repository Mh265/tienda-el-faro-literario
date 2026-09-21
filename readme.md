# 📚 Páginas & Tinta — Tienda en Línea de Libros

Proyecto final del curso de Desarrollo Full Stack (INTECAP).
Tienda de comercio electrónico especializada en libros, construida con
arquitectura MVC, PHP orientado a objetos, MySQL (PDO) y una API REST
consumida vía fetch() desde el frontend en Bootstrap.

## 🎨 Identidad Visual

### Paleta de colores

| Uso                        | Nombre        | HEX       |
|-----------------------------|---------------|-----------|
| Primario (marca, botones)   | Vino Editorial| `#6B2737` |
| Primario oscuro (hover)     | Vino Profundo | `#4A1B27` |
| Secundario (acentos, links) | Dorado Sello  | `#C9A227` |
| Fondo claro                 | Papel         | `#F8F5F0` |
| Texto principal             | Tinta         | `#2B2B2B` |
| Éxito / disponible          | Verde Hoja    | `#3F7D5C` |
| Alerta / stock bajo         | Terracota     | `#C1502E` |

### Sobrescritura de variables de Bootstrap 5

Agregar en `assets/css/variables.css` (importar **antes** que `bootstrap.min.css` no es necesario si usan Bootstrap compilado desde CDN; en ese caso estas variables sobrescriben las de Bootstrap 5.3+ que usa custom properties):

### Tipografías (Google Fonts)

- **Títulos / branding:** [Playfair Display](https://fonts.google.com/specimen/Playfair+Display) — serif elegante, evoca tipografía editorial.
- **Texto de interfaz:** [Inter](https://fonts.google.com/specimen/Inter) — sans-serif muy legible en pantalla, buen soporte de pesos.

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
```

## 🏗️ Arquitectura

- **Patrón:** MVC (Modelo-Vista-Controlador)
- **Backend:** PHP 8.x orientado a objetos, API REST, sesiones nativas de PHP
- **Base de datos:** MySQL 8.x, acceso vía PDO con consultas preparadas
- **Frontend:** HTML5 semántico + Bootstrap 5 + JavaScript (fetch + JSON)
- **Control de versiones:** Git / GitHub, flujo por ramas (ver sección de Roadmap)

## 👥 Equipo

| Rol      | Integrante | Responsabilidad                                              |
|----------|------------|----------------------------------------------------------------|
| Backend  | Milton     | Base de datos, estructura MVC, Modelos, Controladores, API PHP |
| Frontend | Jenifer    | Wireframes, mockups, maquetación Bootstrap, consumo de la API  |

## 📌 Estado actual

Versión **Alpha 0.1** — estructura base del proyecto y configuración inicial.