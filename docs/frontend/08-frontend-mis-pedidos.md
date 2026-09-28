# Feature/frontend-mis-pedidos

## Objetivo
Historial de compras del cliente (RF12).

## Archivos
`public/vistas/mis-pedidos.php` (vista privada) · `assets/js/mis-pedidos.js`

## Endpoints consumidos
`GET api/pedidos.php` (historial propio) · `GET api/pedidos.php?id=` (líneas, se cargan al pulsar "Ver detalle" y se guardan en la tarjeta).

## Decisiones
- Estado del pedido con badge de Bootstrap según `CLASES_ESTADO_PEDIDO`.
- Fecha con `toLocaleDateString('es-GT')`; totales con formato `Q0.00`.
- Estados vacío y de error visibles en `#zonaEstadoPedidos`.
