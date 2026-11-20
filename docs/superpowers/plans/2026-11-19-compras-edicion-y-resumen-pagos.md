# Compras siempre editables + resumen de pagos unificado: plan de implementación

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Permitir editar compras recibidas aunque tengan pagos (recalculando total y estado de pago) y mostrar ventas (ingresos) y pagos a proveedores (egresos) juntos en el Resumen de Pagos.

**Architecture:** La edición reutiliza `Purchase::refreshPaymentStatus()` y agrega la validación `total >= pagado`. El resumen agrupa cada fuente (`payments`, `supplier_payments`) en SQL por día y método y las une en PHP por `(día, método)`. Ninguna tabla cambia de esquema.

**Tech Stack:** Laravel (Eloquent, Sanctum, maatwebsite/excel), PHPUnit contra BD `testing`, Vue 3 + Vuetify.

**Spec:** `docs/superpowers/specs/2026-11-19-compras-edicion-y-resumen-pagos-design.md`

## Global Constraints

- No migrar ni modificar `supplier_payments` ni `payments`; sin migraciones nuevas.
- Compras anuladas no se editan. Proveedor, depósito y fecha de la compra no se editan.
- Error de edición: `El total ($X) no puede ser menor a lo ya pagado ($Y). Anulá o reducí pagos primero.` (HTTP 422, formato con `number_format($n, 2, ',', '.')` como el resto del módulo).
- Los reembolsos de ventas (`payments` con monto negativo) siguen en **ingresos**.
- Los números de solo-ventas del resumen no deben cambiar: no se toca `getPaymentsSummaryQuery()` ni sus joins.
- Mensajes y copy de UI en español rioplatense (voseo), sin emojis.
- Los tests corren contra la BD `testing` (`php artisan test --filter=<nombre>`); `DatabaseTransactions` revierte cada test.

## Decisiones del plan (no están en el spec)

- Contrato de la fila del resumen: `{ date: 'Y-m-d', payment_method_id: int, payment_method_name: string, income: float, expense: float, net: float, payment_count: int }`.
  `expense` es **negativo** (igual que los reembolsos y que `amount` en el detalle) y `net = income + expense`. Equivale a `income - |expense|` del spec.
- `delete()` de pagos devuelve 422 para la guarda nueva y mantiene 404 para "El pago no existe".
- En el detalle de ventas, `status` es el nombre de `Order::paymentStatus`; en compras, el valor de `Purchase.payment_status`.

## Review Focus

1. Editar con total **igual** a lo pagado: la compra queda `paid`, no `partial`.
2. Editar subiendo el total de una compra `paid`: pasa a `partial` y el saldo (`balance`) es la diferencia.
3. Un día con **solo** pagos de compras (sin ventas): aparece la fila con `income = 0` y `expense` negativo.
4. Pagos de compras de **otro tenant** no aparecen en resumen, detalle ni export.
5. Un pago de compra anulado (soft delete) y un pago de compra en el borde `fecha fin 23:59:59` se tratan bien: el anulado no suma, el del último segundo del día final sí.

## File Structure

| Archivo | Responsabilidad |
|---|---|
| `app/Repositories/PurchaseRepository.php` | `update()`: quita bloqueo por pagos, valida total mínimo, refresca estado |
| `app/Repositories/SupplierPaymentRepository.php` | `delete()`: guarda contra compra anulada |
| `app/Services/PaymentService.php` | Resumen y detalle unificados (ventas + compras) |
| `app/Http/Controllers/PaymentSummaryController.php` | Parámetro `origin` y delegación al servicio |
| `app/Exports/PaymentsSummaryExport.php` | Hojas con ingresos/egresos/neto y detalle con origen |
| `tests/Feature/PurchaseModuleTest.php` | Tests de edición y de `delete()` |
| `tests/Feature/PaymentsSummaryTest.php` (nuevo) | Tests del resumen, detalle, filtros y export |
| `resources/js/pages/iampos/purchaseList.vue`, `purchaseCreate.vue` | Habilitar edición con pagos |
| `resources/js/pages/iampos/payments.vue` | Columnas ingresos/egresos/neto, filtro origen, detalle |

---

### Task 1: Edición de compras con pagos (backend)

**Files:**
- Modify: `app/Repositories/PurchaseRepository.php` (`update()`, líneas ~168-267)
- Modify: `app/Repositories/SupplierPaymentRepository.php` (`delete()`, líneas ~97-120)
- Test: `tests/Feature/PurchaseModuleTest.php`

**Interfaces:**
- Consumes: `Purchase::refreshPaymentStatus(): void`, `Purchase::payments(): HasMany`, `BaseRepository::domainError(string $message, int $code = 422)`.
- Produces: `PUT /api/purchases/{id}` acepta compras con pagos; el response sigue siendo `Purchase` con `DETAIL_RELATIONS`.

- [ ] **Step 1: Reemplazar el test `test_edit_blocked_when_purchase_has_payments`** por tests nuevos (la compra base: 5 x 120 = 600, pago de 100 como en el helper `payload()`):
  - `test_edit_with_payments_recalculates_total_and_keeps_payments`: edita a 3 x 100 (total 300) → 200; `total_amount` 300, `total_paid` 100, `payment_status` `partial`, `payments` sigue con 1 pago, stock ajustado por diferencia (10 + 3 = 13).
  - `test_edit_total_equal_to_paid_marks_purchase_as_paid`: paga 300, edita a total 300 → `payment_status` `paid`.
  - `test_edit_raising_total_turns_paid_purchase_into_partial`: paga 600 (`paid`), edita a 5 x 200 (total 1000) → `partial`, `balance` 400.
  - `test_edit_total_below_paid_is_rejected_and_changes_nothing`: paga 500, edita a total 300 → 422, mensaje contiene `no puede ser menor a lo ya pagado`, `total_amount` sigue 600, stock sigue 15, ítems sin cambio.
  - `test_edit_discount_that_drops_total_below_paid_is_rejected`: paga 500, edita con `discount_amount` 200 sobre subtotal 600 → 422.
  - Mantener `test_can_edit_again_after_voiding_the_payment` y `test_cancelled_purchase_cannot_be_edited` sin cambios.

- [ ] **Step 2: Agregar `test_voiding_payment_on_cancelled_purchase_is_rejected`**: crea compra, paga 100, fuerza `Purchase::whereKey($id)->update(['status' => 'cancelled'])` por DB, `DELETE /api/supplier-payments/{id}` → 422 con mensaje que contiene `anulada`, y el pago sigue activo.

- [ ] **Step 3: Correr** `php artisan test --filter=PurchaseModuleTest` → los nuevos de edición con pagos y el de `delete()` fallan; el resto pasa.

- [ ] **Step 4: En `PurchaseRepository::update()`** eliminar el `if ($purchase->payments()->lockForUpdate()->exists())` y, después de calcular `$subtotal/$discount/$additional` y antes de tocar stock, calcular `$newTotal = round($subtotal - $discount + $additional, 2)` y `$paid = round((float) $purchase->payments()->sum('amount'), 2)`; si `$newTotal < $paid` lanzar `DomainException` con el texto exacto de Global Constraints. Al final, tras `$purchase->save()`, llamar `$purchase->refreshPaymentStatus()`. Actualizar el docblock del método (ya no dice "sin pagos").

- [ ] **Step 5: En `SupplierPaymentRepository::delete()`** después de bloquear la compra, si `$purchase->status !== Purchase::STATUS_RECEIVED` lanzar `DomainException('No se pueden anular pagos de una compra anulada')`. Cambiar el catch de `DomainException` para responder `$e->getCode() ?: 422`, y que el caso "El pago no existe" lance con código 404.

- [ ] **Step 6: Correr** `php artisan test --filter=PurchaseModuleTest` → todo PASS.

- [ ] **Step 7: Commit**

```bash
git add app/Repositories/PurchaseRepository.php app/Repositories/SupplierPaymentRepository.php tests/Feature/PurchaseModuleTest.php
git commit -m "feat(compras): permitir editar compras con pagos y validar total mínimo"
```

---

### Task 2: Edición de compras con pagos (frontend)

**Files:**
- Modify: `resources/js/pages/iampos/purchaseList.vue` (`canEdit` ~línea 660; botón "Editar compra" ~línea 321; menú ~línea 138)
- Modify: `resources/js/pages/iampos/purchaseCreate.vue` (`loadPurchaseForEdit` ~línea 869; alerta ~línea 3)

**Interfaces:**
- Consumes: Task 1 (el backend acepta la edición). `Purchase.total_paid` ya viene en el detalle.
- Produces: nada que consuma otra tarea.

- [ ] **Step 1: `purchaseList.vue`**: `canEdit(item)` devuelve `item.status === "received"`. Quitar `&& !(detail.payments || []).length` del `v-if` del botón "Editar compra" del detalle.

- [ ] **Step 2: `purchaseCreate.vue`**: en `loadPurchaseForEdit()` el rechazo solo aplica a `p.status !== "received"` (mensaje "La compra está anulada y no se puede editar"); guardar `this.alreadyPaid = Number(p.total_paid || 0)` en `data()`. En la `VAlert` de edición, si `alreadyPaid > 0` agregar: "Ya se pagaron $X: el nuevo total no puede ser menor." (formateado con el mismo helper de moneda del archivo).

- [ ] **Step 3: Validación preventiva en `updatePurchase()`**: antes de enviar, si `this.alreadyPaid > this.purchase.total_amount` mostrar snackbar de error con el mismo texto del backend y no enviar.

- [ ] **Step 4: Verificar a mano** (el repo no tiene tests de frontend): con `npm run dev`, editar una compra con un pago parcial, subir y bajar el total; con total menor al pagado debe mostrar el error y no guardar. Ver que el detalle muestre el estado de pago actualizado.

- [ ] **Step 5: Commit**

```bash
git add resources/js/pages/iampos/purchaseList.vue resources/js/pages/iampos/purchaseCreate.vue
git commit -m "feat(compras): habilitar edición en compras con pagos"
```

---

### Task 3: Servicio de resumen unificado

**Files:**
- Modify: `app/Services/PaymentService.php`
- Create: `tests/Feature/PaymentsSummaryTest.php`

**Interfaces:**
- Consumes: `getPaymentsSummaryQuery(array $filters)` (sin cambios), modelos `Payment`, `SupplierPayment`, `Purchase`, `Supplier`, `Order::paymentStatus()`.
- Produces (los usan Tasks 4 y 5):
  - `getUnifiedSummary(array $filters): Collection` — `$filters` admite `payment_methods` (array de ids|null), `start_date`, `end_date` (string|null), `origin` (`all`|`sales`|`purchases`, default `all`). Sin ambas fechas, aplica los últimos 30 días. Devuelve filas con el contrato de "Decisiones del plan", ordenadas por `date` desc y `abs(net)` desc.
  - `getPaymentDetailsRange(array $filters): Collection` — mismos filtros; filas `{ id, origin: 'sale'|'purchase', date: 'Y-m-d', payment_date, payment_method_id, payment_method, reference_number, counterparty_name, counterparty_contact, amount, status }`, ordenadas por `payment_date` desc. `amount` es positivo/negativo según el pago en ventas y **negativo** en compras.
  - `getPaymentDetails($date, $paymentMethodId, string $origin = 'all'): Collection` — igual que `getPaymentDetailsRange` con `start_date = end_date = $date` y `payment_methods = [$paymentMethodId]`.

- [ ] **Step 1: Crear `tests/Feature/PaymentsSummaryTest.php`** con el mismo `setUp` que `PurchaseModuleTest` (BD `testing`, dos tenants, `Sanctum::actingAs`). Helpers: `salePayment(float $amount, string $date, ?PaymentMethod $method = null, ?int $tenantId = null)` que inserta por `DB::table` un cliente, una orden (con `order_number` único y `order_date`) y un `payments`; `purchasePayment(float $amount, string $datetime, ?PaymentMethod $method = null, ?int $tenantId = null)` que crea proveedor, compra y `supplier_payments` por `DB::table`.

- [ ] **Step 2: Tests del servicio** (llaman al servicio directo con `app(PaymentService::class)`):
  - `test_summary_combines_income_and_expense_for_same_day_and_method`: venta 1000 y compra 400 el mismo día y método → una fila con `income` 1000, `expense` -400, `net` 600, `payment_count` 2.
  - `test_summary_keeps_refunds_as_negative_income`: venta 1000 y reembolso -200 → `income` 800, `expense` 0.
  - `test_summary_day_with_only_purchase_payments_has_zero_income` (Review Focus 3).
  - `test_summary_origin_filter`: con `origin` `sales` solo hay ingresos; con `purchases` solo egresos.
  - `test_summary_payment_method_filter_applies_to_both_sources`.
  - `test_summary_excludes_voided_payments_and_other_tenants` (Review Focus 4 y 5): un `supplier_payment` con `deleted_at` y otro de `otherUser`'s tenant no suman.
  - `test_summary_date_range_includes_last_second_of_end_date` (Review Focus 5): pago de compra a `23:59:59` del día fin se incluye; el del día siguiente a `00:00:00` no.
  - `test_summary_sales_only_numbers_match_legacy_query`: con solo ventas, `income` por fila coincide con `SUM(amount)` de `getPaymentsSummaryQuery()` para el mismo rango.
  - `test_details_return_both_origins_with_reference_and_counterparty`: en un día con una venta y una compra, `getPaymentDetails` devuelve 2 filas; la de venta con `origin` `sale`, `reference_number` = número de orden y `counterparty_name` = `"{address}, {firstname}"` (como hoy); la de compra con `origin` `purchase`, `reference_number` = número de compra, `counterparty_name` = nombre del proveedor y `amount` negativo.

- [ ] **Step 3: Correr** `php artisan test --filter=PaymentsSummaryTest` → FAIL (métodos no definidos).

- [ ] **Step 4: Implementar en `PaymentService`** los tres métodos de Interfaces más privados `supplierSummaryQuery`, `applyDateRange` y `salesDetailRows`/`purchaseDetailRows`. Enfoque: ventas con `getPaymentsSummaryQuery()` + filtro de fechas sobre `payments.payment_date`; compras con `SupplierPayment::join('payment_methods', ...)` agrupado por `DATE(supplier_payments.payment_date)` y método (el `TenantScope` ya califica `supplier_payments.tenant_id`); unión en PHP indexando por `"{date}|{payment_method_id}"`. El detalle de compras une `purchases` y `suppliers` (`suppliers.name`, `suppliers.telephone`) y el de ventas conserva el SELECT actual (`orders.order_number`, `customers.address`/`firstname`/`telephone`, `Order::paymentStatus`).

- [ ] **Step 5: Correr** `php artisan test --filter=PaymentsSummaryTest` → PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Services/PaymentService.php tests/Feature/PaymentsSummaryTest.php
git commit -m "feat(pagos): resumen y detalle unificados de ventas y compras"
```

---

### Task 4: Controller y endpoints del resumen

**Files:**
- Modify: `app/Http/Controllers/PaymentSummaryController.php` (`getSummary`, `getDetail`)
- Test: `tests/Feature/PaymentsSummaryTest.php`

**Interfaces:**
- Consumes: `PaymentService::getUnifiedSummary`, `getPaymentDetails` (Task 3).
- Produces (los usa Task 6): `GET /api/payments-summary?start_date&end_date&payment_methods[]&origin` → `{ success: true, data: [filas del contrato], total: int }`; `GET /api/payments-summary/detail?date&payment_method_id&origin` → `{ success: true, data: [filas de detalle] }`. `origin` inválido → 422 con `success: false`.

- [ ] **Step 1: Tests HTTP** en `PaymentsSummaryTest`: `test_summary_endpoint_returns_income_expense_net_and_total_count`, `test_summary_endpoint_origin_filter_and_invalid_origin_is_422`, `test_detail_endpoint_returns_rows_from_both_origins`, `test_detail_endpoint_requires_date_and_method` (400 como hoy).
- [ ] **Step 2: Correr** `php artisan test --filter=PaymentsSummaryTest` → los nuevos fallan.
- [ ] **Step 3: Implementar**: `getSummary` arma `$filters` con `payment_methods`, `start_date`, `end_date`, `origin` y responde con `getUnifiedSummary`; `total = $rows->count()`. `getDetail` pasa `origin`. Validar `origin` con `in:all,sales,purchases` (si falta, `all`). Se eliminan del controller las ramas de fecha duplicadas (ahora viven en el servicio).
- [ ] **Step 4: Correr** `php artisan test --filter=PaymentsSummaryTest` → PASS.
- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/PaymentSummaryController.php tests/Feature/PaymentsSummaryTest.php
git commit -m "feat(pagos): parámetro origin en resumen y detalle"
```

---

### Task 5: Export a Excel

**Files:**
- Modify: `app/Exports/PaymentsSummaryExport.php`; `PaymentSummaryController::exportExcel` (parámetro `origin`)
- Test: `tests/Feature/PaymentsSummaryTest.php`

**Interfaces:**
- Consumes: `getUnifiedSummary`, `getPaymentDetailsRange` (Task 3).
- Produces: `GET /api/payments-summary/export-excel?start_date&end_date&payment_methods[]&origin` con dos hojas.

- [ ] **Step 1: Tests con `Excel::fake()`/`Excel::matchByRegex`**, o instanciando las hojas y llamando `collection()`/`map()`: `test_export_summary_sheet_has_income_expense_net_columns` (headings: `Fecha`, `Método de Pago`, `Ingresos`, `Egresos`, `Neto`, `Cantidad de Transacciones`; la fila de un día con venta 1000 y compra 400 mapea `[date, método, 1000, -400, 600, 2]`) y `test_export_detail_sheet_includes_origin_and_both_sources` (headings: `Fecha Pago`, `Fecha Grupo`, `Método de Pago`, `Origen`, `Referencia`, `Contraparte`, `Contacto`, `Monto`, `Tipo Monto`, `Estado`; `Tipo Monto` es `Ingreso`/`Reembolso` para ventas y `Egreso` para compras; `Origen` es `Venta`/`Compra`).
- [ ] **Step 2: Correr** → FAIL.
- [ ] **Step 3: Implementar** las hojas sobre `getUnifiedSummary` y `getPaymentDetailsRange` (una sola consulta del rango, sin el recorrido por grupo). Pasar `origin` por el constructor del export y desde el controller. Los constructores tienen un parámetro opcional antes de uno obligatorio (`$paymentMethods = null, PaymentService $paymentService`): ordenar los argumentos para que no haya deprecation.
- [ ] **Step 4: Correr** `php artisan test --filter=PaymentsSummaryTest` → PASS.
- [ ] **Step 5: Commit**

```bash
git add app/Exports/PaymentsSummaryExport.php app/Http/Controllers/PaymentSummaryController.php tests/Feature/PaymentsSummaryTest.php
git commit -m "feat(pagos): excel con ingresos, egresos y origen"
```

---

### Task 6: Pantalla del resumen

**Files:**
- Modify: `resources/js/pages/iampos/payments.vue`

**Interfaces:**
- Consumes: contrato de la fila del resumen y del detalle (Tasks 3 y 4); `origin` en `exportToExcel`, `fetchData` y `showPaymentDetails`.
- Produces: nada.

- [ ] **Step 1: Headers de la tabla principal**: `Día`, `Método de Pago`, `Ingresos` (`income`), `Egresos` (`expense`), `Neto` (`net`), `Acciones`. Celdas de monto con `formatCurrency` y `getColorAmount` (los egresos ya son negativos y se pintan rojos). Corregir `calculateTotalAmount` para tres totales (`calculateTotals()` → `{ income, expense, net }`) y el `tfoot` con tres celdas.
- [ ] **Step 2: Filtro Origen**: `VSelect` con items `Todos` (`all`), `Ventas` (`sales`), `Compras` (`purchases`), default `all`, ubicado junto al filtro de método; se envía como `origin` en `fetchData`, `showPaymentDetails` y `exportToExcel`; `resetFilters` lo vuelve a `all`.
- [ ] **Step 3: Detalle**: headers `Fecha Pago`, `Origen` (chip: `Venta` en `success`, `Compra` en `error`), `Referencia` (`#reference_number`), `Contraparte` (nombre y contacto), `Monto`. Encabezado del diálogo "Resumen del Grupo": mostrar Ingresos, Egresos y Neto del grupo en lugar de un único Total.
- [ ] **Step 4: Texto descriptivo**: "Ingresos de ventas y egresos de compras por día y método de pago".
- [ ] **Step 5: Verificar a mano** con `npm run dev`, con datos de ventas y compras en el mismo día: una fila con ingresos, egresos y neto correctos, filtro Origen funciona, el detalle muestra ambos orígenes, el Excel descargado coincide con la pantalla.
- [ ] **Step 6: Commit**

```bash
git add resources/js/pages/iampos/payments.vue
git commit -m "feat(pagos): pantalla de resumen con ingresos, egresos y neto"
```

---

### Task 7: Verificación final

- [ ] **Step 1:** `php artisan test` completo → todo PASS (incluye `PurchaseModuleTest` y `PaymentsSummaryTest`).
- [ ] **Step 2:** Revisar con `git diff main --stat` que no cambió ninguna migración ni `getPaymentsSummaryQuery()`.
- [ ] **Step 3:** Si el repo despliega `public/assets` compilado (hoy está versionado), correr `npm run build` y commitear el build **solo si lo confirmás**.

## Self-review contra el spec

| Spec | Tarea |
|---|---|
| Editar compras con pagos, recalcular total y estado | 1, 2 |
| Total no menor a lo pagado; anuladas no editables | 1 (tests de rechazo) |
| Hueco de `SupplierPaymentRepository::delete()` | 1 |
| Resumen unificado con ingresos/egresos/neto y filtro de origen | 3, 4, 6 |
| Detalle con origen, referencia y contraparte | 3, 6 |
| Excel con columnas corregidas y detalle sin N+1 | 5 |
| No cambiar números de ventas; reembolsos como ingreso | 3 (tests) |
| Tests de aislamiento por tenant, soft delete y borde de fecha | 3 |
