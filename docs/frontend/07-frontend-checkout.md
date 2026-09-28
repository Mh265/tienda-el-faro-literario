# Feature/frontend-checkout

## Objetivo
Confirmar el pedido desde el carrito (RF11), con pago simulado (RF13) y confirmación en pantalla (RF20).

## Archivos
| Archivo | Rol |
|---|---|
| `public/vistas/checkout.php` | Vista privada (`protegerVista('login.php')`) |
| `assets/js/checkout.js` | Resumen, envío del pedido y modal de confirmación |

## Endpoint consumido
`POST api/pedidos.php` con `{ "items": [{ "id_producto", "cantidad" }] }` → `PedidoController::crear`.

## Decisiones
- **Solo `id_producto` y `cantidad`:** el servidor calcula precios y total.
- **Pago simulado:** el método elegido no viaja a la API. Con tarjeta hay una espera de menos de un segundo; con contra entrega no. No se procesa ningún cobro.
- Si la API responde error (ej. "Stock insuficiente…") se muestra el mensaje y el carrito se conserva. Si responde 401 se redirige a `login.php?volver=checkout.php`.
- Si tiene éxito: se llena el modal con `id_pedido` y `total` de la respuesta, se vacía el carrito y se deja el resumen vacío con el botón deshabilitado (evita duplicar el pedido).

## Cómo probarlo
Carrito vacío (botón deshabilitado); pedido correcto (aparece en Mis pedidos con estado pendiente y el stock baja); pedido con más unidades que el stock; sesión vencida; doble clic rápido en "Confirmar".
