# Feature: Conexión a Base de Datos

**Rama:** `Feature/conexion-base-datos`
**Proyecto:** El Faro Literario — Tienda en Línea de Libros (Proyecto Final INTECAP)
**Responsable:** Milton (Backend)

## 1. Objetivo de la feature

Establecer el mecanismo base con el que **todos los Modelos** del proyecto se conectarán a la base de datos MySQL (`tienda_el_faro`), usando PDO. Esta feature no implementa CRUD ni lógica de negocio: es la capa de infraestructura sobre la que se construirán los Modelos (Libro, Usuario, Pedido, etc.).

## 2. Archivo agregado

```
includes/
└── config/
    └── BaseDatos.php
```

## 3. ¿Qué hace la clase `BaseDatos`?

Expone un único método estático, `BaseDatos::conectar()`, que:

1. Define en variables locales el host, el nombre de la base de datos, el usuario y la contraseña.
2. Arma el DSN (`Data Source Name`) de conexión a MySQL con esas variables y el charset `utf8mb4`.
3. Crea el objeto `PDO` y le activa `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION` con `setAttribute()`, para que cualquier error de la base de datos se convierta en una excepción (`PDOException`) en vez de fallar en silencio.
4. **Retorna la conexión PDO** ya configurada.
5. Si la conexión falla, el `catch` detiene la ejecución con `die()` mostrando el mensaje de error — el mismo comportamiento que se usó en el ejemplo de clase (proyecto Bosque Verde).

## 4. Decisiones de diseño (y por qué)

| Decisión | Razón |
|---|---|
| **Sin patrón singleton** | No se ha visto en clase todavía. Se prefirió una conexión directa y simple: cada llamada a `conectar()` abre una conexión nueva, sin lógica de "reutilizar si ya existe una abierta". |
| **Método estático** | Permite usar `BaseDatos::conectar()` desde cualquier Modelo sin necesidad de instanciar la clase (`new BaseDatos()`), reduciendo código repetido. |
| **Variables locales (no constantes de clase) para las credenciales** | Se siguió fielmente el ejemplo visto en clase (`conexion.php` del proyecto Bosque Verde), que usa variables simples en vez de constantes. |
| **`setAttribute()` después de crear el PDO (no arreglo de opciones en el constructor)** | Mismo motivo: es la forma en que se hizo en el ejemplo de clase, aunque el resultado (activar `ERRMODE_EXCEPTION`) es el mismo. |
| **`die()` en el `catch` (no `throw`)** | Se prioriza la fidelidad al nivel y metodología de clase sobre un manejo de errores más "profesional". Implica que, si la conexión falla, el script se detiene ahí mismo mostrando el error, en vez de dejar que el Controlador decida qué hacer con la excepción. |
| **Nombre `BaseDatos` (no `Database`)** | Se mantiene la convención de nombres en español que ya se usa en el resto del proyecto. |

## 5. Cómo se usa desde un Modelo

```php
require_once __DIR__ . '/../config/BaseDatos.php';

class LibroModelo
{
    public function obtenerTodos(): array
    {
        $pdo = BaseDatos::conectar();
        $consulta = $pdo->query('SELECT * FROM productos');
        return $consulta->fetchAll();
    }
}
```

## 6. Referencia de origen

El estilo de esta clase se tomó directamente del archivo `conexion.php` usado en el proyecto del colegio (Bosque Verde), solo envolviéndolo en un método estático de una clase para poder reutilizarlo como `BaseDatos::conectar()` desde los Modelos del proyecto.

## 7. Configuración pendiente antes de probar

Las credenciales en `BaseDatos.php` están puestas con valores típicos de un entorno local (XAMPP: usuario `root`, contraseña vacía). **Deben ajustarse** a las credenciales reales del entorno donde se ejecute el proyecto antes de hacer pruebas de conexión.

## 8. Alcance y lo que NO incluye esta feature

- No crea la base de datos ni sus tablas (eso corresponde a otra feature: modelo relacional / script SQL).
- No implementa ningún Modelo, Controlador ni endpoint de la API.
- No maneja pool de conexiones ni reconexión automática — queda fuera de alcance por decisión explícita del equipo.

## 9. Checklist de aceptación

- [x] Clase `BaseDatos` creada en `includes/config/BaseDatos.php`
- [x] Conexión vía PDO a `tienda_el_faro`
- [x] `PDO::ERRMODE_EXCEPTION` configurado con `setAttribute()`
- [x] Sin lógica de singleton
- [x] Estilo fiel al ejemplo de clase (Bosque Verde)
- [x] Convención de nombres en español respetada
