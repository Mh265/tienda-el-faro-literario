# Feature/pedido-api

## Objetivo

Implementar la creación de pedidos desde el carrito, el historial de
compras del cliente, el listado administrativo, el detalle de un pedido y
el cambio de estado, sobre las tablas `pedidos` y `detalle_pedido`,
expuesto como `api/pedidos.php`.

## Archivos que agrega/completa esta feature

| Archivo | Rol |
|---|---|
| `app/controladores/PedidoController.php` | Validaciones, transacción de creación, control de acceso (cliente/admin) y armado de respuestas JSON |
| `api/pedidos.php` | Endpoint que enruta por método HTTP (y `accion=admin-listado` para el único caso que no encaja en el CRUD estándar) |
| `docs/API.md` | Actualizado con el contrato completo de `api/pedidos.php` |

## Cambios menores en Modelos ya existentes

`Feature/modelos-crud` dejó cada método de Modelo abriendo su propia
conexión con `BaseDatos::conectar()`. Eso rompía la atomicidad que pide
esta feature: si `Pedido::crear()` y `DetallePedido::crear()` abrieran cada
uno su propia conexión, un `beginTransaction()` hecho en el Controller no
tendría ningún efecto sobre esos INSERT (estarían en conexiones distintas).

Se agregó un parámetro opcional `$conexion = null` a los métodos que
participan en la transacción. Si no se pasa nada, el método se comporta
exactamente igual que antes — no rompe ningún código ya mergeado:

- `Libro::obtenerPorId($id_producto, $conexion = null)` — ahora puede leer
  el libro dentro de la misma transacción del pedido.
- `Libro::descontarStock($id_producto, $cantidad, $conexion = null)` —
  **método nuevo**. Hace `UPDATE productos SET cantidad = cantidad -
  :cantidad WHERE id_producto = :id_producto AND cantidad >=
  :cantidad_minima`. La condición en el propio UPDATE evita que el stock
  quede negativo si dos pedidos concurrentes pasan la validación inicial
  casi al mismo tiempo; si no actualiza ninguna fila, el Controller
  interpreta que el stock ya no alcanza y revierte todo el pedido.
- `Pedido::crear(..., $conexion = null)` y `DetallePedido::crear(...,
  $conexion = null)` — mismo patrón.

## Decisiones de arquitectura

- **El cliente nunca envía precios ni totales.** El body de `POST
  api/pedidos.php` solo trae `items: [{ id_producto, cantidad }]`; el
  precio unitario se toma de `productos.precio` en el momento de la compra
  y el total se calcula sumando en el servidor.
- **Toda la creación del pedido es una única transacción PDO**
  (`beginTransaction`/`commit`/`rollBack`) sobre una sola conexión, pasada
  explícitamente a los Modelos. Si cualquier línea falla (libro inactivo,
  stock insuficiente, o el descuento de stock no afecta ninguna fila), se
  hace `rollBack()` completo: no queda un pedido a medias ni stock
  descontado parcialmente.
- **La validación de stock ocurre dos veces, a propósito.** Primero como
  lectura normal, para dar un mensaje de error claro ("Stock insuficiente
  para X, disponible: N"); después, otra vez dentro del propio `UPDATE` de
  `descontarStock()`, que es la que realmente protege contra condiciones
  de carrera (dos clientes comprando el último ejemplar a la vez). No se
  usa `SELECT ... FOR UPDATE` por ser un patrón de bloqueo explícito no
  visto en clase; el `UPDATE` condicionado logra el mismo efecto de forma
  más simple.
- **`api/pedidos.php` enruta por método HTTP**, igual que `api/libros.php`.
  No hay `DELETE`: un pedido no se borra, se cancela cambiando su `estado`
  a `'cancelado'`.
- **`FiltroAutenticacion` se usa desde el día uno en esta feature**, a
  diferencia de `LibroController` (que quedó con su validación manual por
  decisión explícita de `Feature/filtro-autenticacion`, para no arriesgar
  una regresión a un día de la entrega). Toda feature nueva debe usar
  `protegerApi()` / `protegerApiAdministrador()` desde ahora.
- **El detalle de un pedido devuelve 404 (no 403) si el cliente no es el
  dueño.** Mismo criterio que `LibroController::detalle()` con libros
  inactivos: no se revela que el pedido existe si quien pregunta no tiene
  derecho a verlo.
- **`cambiarEstado()` valida el estado contra una lista blanca**
  (`pendiente`, `pagado`, `enviado`, `entregado`, `cancelado`), pero
  **no** valida que la transición sea "válida" en secuencia (por ejemplo,
  no impide pasar de `entregado` a `pendiente`). Queda como decisión
  abierta para después de la entrega.
- **`Pedido::actualizar()` exige `total` además de `estado`**, así que
  `cambiarEstado()` relee el pedido y reenvía su mismo `total`: el total
  de un pedido nunca cambia al actualizar su estado.

## Endpoints

Ver el detalle completo en `docs/API.md`. Resumen:

| Método | `accion` | Acceso | Descripción |
|---|---|---|---|
| `POST` | — | Cliente | Crea un pedido desde el carrito |
| `GET` | — | Cliente | Historial de pedidos propios |
| `GET` | `admin-listado` | Administrador | Todos los pedidos |
| `GET` con `?id=` | — | Cliente (dueño) / admin | Detalle del pedido con sus líneas |
| `PUT` con `?id=` | — | Administrador | Cambia el estado del pedido |

## Pendiente para features futuras

- Validar secuencia estricta de transición de estados, si el equipo decide
  que hace falta.
- `Feature/categoria-api`, `Feature/wishlist-api` y `Feature/resena-api`
  siguen sin implementar.
- ⚠️ **Hallazgo aparte, no de esta feature:** al verificar el repositorio se
  confirmó que `includes/ayudantes/AyudanteArchivo.php` **no existe**,
  aunque `api/libros.php` y `LibroController.php` ya lo requieren
  (`require_once`) desde `Feature/libro-api`. Tal como está hoy en
  `develop`, cualquier `POST` o `PUT` a `api/libros.php` truena con un
  error fatal de PHP (archivo no encontrado). Crearlo antes de la demo.