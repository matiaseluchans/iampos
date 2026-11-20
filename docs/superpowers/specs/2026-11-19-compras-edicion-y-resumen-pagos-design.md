# Compras siempre editables + resumen de pagos unificado (ventas y compras)

Fecha: 2026-11-19 · Estado: borrador para aprobación

## Intención y por qué

Hoy una compra con cualquier pago registrado no se puede editar, y el usuario tiene que anular los pagos primero.
Se quiere que **siempre se pueda editar** (salvo anuladas), recalculando el total y el estado de pago.
Además, el dinero que sale por pagos a proveedores (`supplier_payments`) y el que entra por ventas (`payments`)
son de la **misma caja**, pero el "Resumen de Pagos" solo muestra `payments` de órdenes.
Se quiere ver ambos juntos, sin migrar ni modificar los pagos existentes.

## Objetivos

1. Editar compras recibidas con o sin pagos. Se mantienen los pagos; se recalculan `total_amount`, `total_paid` y `payment_status`.
2. El resumen de pagos (pantalla, detalle y Excel) incluye pagos de compras como egresos, junto a los ingresos de ventas.
3. Ver ingresos, egresos y neto por día y método de pago, y poder filtrar por origen (ventas / compras).

## No objetivos

- No mover ni copiar `supplier_payments` a `payments`. No cambiar el esquema de ninguna de las dos tablas.
- No editar compras anuladas (reabrirlas implicaría revertir la anulación y reponer stock).
- No permitir cambiar proveedor, depósito ni fecha de la compra (sin cambios respecto de hoy).
- No introducir "saldo a favor" de proveedores ni ajustar pagos automáticamente.

## Anti-objetivos (fracaso aunque "funcione")

- Que una compra editada quede con `total_paid > total_amount` (sobrepago).
- Que los egresos se mezclen y escondan dentro de un monto neto único.
- Que cambien los números de ventas que hoy muestra el resumen.

## Restricciones y hechos que condicionan

- `payments.order_id` es NOT NULL con FK a `orders`; por eso `supplier_payments` sigue separada.
- Convención existente: los reembolsos son `payments` con monto negativo, el frontend pinta rojo los negativos y el Excel rotula "Ingreso/Reembolso" por signo.
  Los reembolsos **siguen siendo ingreso negativo** (parte de ingresos netos de ventas); no se mezclan con egresos de compras.
- `Purchase::refreshPaymentStatus()` ya recalcula `total_paid` y `payment_status` a partir de los pagos activos; se reutiliza.
- `Payment` y `SupplierPayment` usan `TenantScope` y soft delete; ambos filtros deben seguir aplicando.
- Ambos modelos comparten `payment_method_id` contra la misma tabla `payment_methods`.

## Enfoques considerados

Para la edición (decidido por el usuario, opción A): bloquear solo el caso de total menor a lo pagado.
Descartadas: B (permitir saldo a favor: concepto nuevo en proveedores y reportes) y C (achicar pagos automáticamente: modifica dinero ya pagado, riesgo de auditoría).

Para el resumen unificado (elegido por el usuario: **B**):

| | A. UNION SQL | **B. Agrupar por fuente y unir en PHP (elegida)** | C. Vista de BD |
|---|---|---|---|
| Resumen | Una query con `UNION ALL` | Dos queries ya agrupadas por día y método, sumadas en PHP por `(día, método)` | `CREATE VIEW` |
| Descartada por | `UNION` con `TenantScope` y soft delete frágil | — | Scopes de Eloquent no aplican; filtro por tenant manual |

Se elige B porque el resultado agrupado es chico, y deja `TenantScope` y soft delete funcionando en cada modelo.

## Diseño

### 1. Edición de compras (backend)

En `PurchaseRepository::update()`:
- Se mantiene el bloqueo de compras anuladas.
- Se elimina el bloqueo por existencia de pagos.
- Tras calcular el nuevo `total_amount`, se calcula `paid = suma de pagos activos` (dentro de la transacción, con la compra ya bloqueada).
  Si `total_amount < paid`, error de dominio: `El total ($X) no puede ser menor a lo ya pagado ($Y). Anulá o reducí pagos primero.`
- Tras guardar la compra, se llama a `refreshPaymentStatus()` para recalcular `total_paid` y `payment_status`
  (ej.: `paid` pasa a `partial` si el total sube; con total igual a lo pagado queda `paid`).
- El ajuste de stock por diferencia no cambia.

En `SupplierPaymentRepository::delete()`: validar que la compra esté `received`; si está anulada, error de dominio.
(Hueco encontrado en la revisión; hoy se pueden anular pagos de compras anuladas.)

### 2. Edición de compras (frontend)

- `purchaseList.vue`: `canEdit(item)` pasa a `item.status === 'received'`.
  El botón de pagar y el de editar dejan de excluirse mutuamente (hoy `canEdit(detail) && !payments.length`).
- `purchaseCreate.vue`: `loadPurchaseForEdit()` deja de rechazar compras con pagos. El aviso de edición informa
  el monto ya pagado y que el nuevo total no puede quedar por debajo.
  Validación preventiva en cliente (el backend sigue siendo la fuente de verdad).

### 3. Resumen de pagos unificado

`PaymentService`:
- `getPaymentsSummaryQuery()` se mantiene para ventas (ingresos). Se agrega una query equivalente para `supplier_payments`
  (JOIN con `payment_methods` y `purchases`/`suppliers` solo cuando haga falta el detalle), agrupada por día y método.
- Un método de unión devuelve filas `{ date, payment_method_id, payment_method_name, income, expense, net, payment_count }`.
  `income` viene de `payments` (puede incluir reembolsos negativos, como hoy), `expense` de `supplier_payments` (positivo en BD, se muestra negativo).
  `net = income - expense`.
- Parámetro `origin`: `all` (default), `sales`, `purchases`. Filtro `payment_methods` aplica a ambas fuentes. Filtros de fecha idénticos para ambas.
- `getPaymentDetails(date, methodId, origin)` devuelve filas de ambas fuentes ordenadas por fecha desc con campos comunes:
  `id`, `origin` (`sale`|`purchase`), `payment_date`, `reference_number` (nº de orden o de compra), `counterparty_name`
  (cliente o proveedor), `counterparty_contact`, `amount` (con signo: egreso negativo), `payment_method`.

`PaymentSummaryController`: agrega el parámetro `origin` a `getSummary` y `getDetail`; el total de registros se calcula sobre la colección unida.

`PaymentsSummaryExport`:
- Hoja resumen: columnas Fecha, Método, Ingresos, Egresos, Neto, Cantidad de transacciones. Se corrigen las columnas que hoy salen vacías
  (`payment_method_code` y `transaction_count` no existen en la query).
- Hoja detalle: columna Origen y Referencia; se reemplaza el recorrido por grupo (N+1) por una sola consulta del rango.
  Se retira `Estado Orden` en filas de compra (queda vacío) o se reemplaza por `Estado de pago` de la compra.

`payments.vue`:
- Columnas: Día, Método de Pago, Ingresos, Egresos, Neto, Acciones. Pie con totales por columna.
- Filtro "Origen" (Todos / Ventas / Compras).
- Detalle: columnas Origen, Referencia, Contraparte, Monto; chip de origen.
- Rótulo de descripción actualizado: "Ingresos de ventas y egresos de compras por día y método de pago".

### 4. Tests

- Edición con pagos: total sube (paid→partial), total baja sin bajar de lo pagado (partial→paid si iguala), total menor a lo pagado (error), compra anulada (error).
- `SupplierPaymentRepository::delete()` sobre compra anulada (error).
- Resumen: combinación de ingresos y egresos en el mismo día y método, filtro por origen, filtro por método, aislamiento por tenant, pagos soft-deleted excluidos,
  reembolsos siguen en ingresos, y que los números de solo-ventas no cambian.
- Export: columnas completas y detalle con ambos orígenes.

## Supuestos (marcados como míos)

- "Misma caja" implica mismos `payment_methods` para ventas y compras.
- Los reembolsos de ventas se mantienen como ingreso negativo, no como egreso.
- El orden de las filas del resumen es el actual: fecha desc, luego monto desc (ahora por neto absoluto).
- Compras anuladas no aportan egresos nuevos; sus pagos ya deben estar anulados para poder anular la compra.

## Para el implementador

- Nombres exactos de columnas y copy de la UI.
- Cómo paginar el resumen (hoy el backend devuelve todo con `get()`; se puede mantener).
- Si la validación en cliente del total mínimo vale la pena o alcanza con el error del backend.
