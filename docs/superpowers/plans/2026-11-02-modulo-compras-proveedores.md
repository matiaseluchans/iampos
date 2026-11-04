# Módulo de Compras a Proveedores — Plan de implementación

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Registrar compras de mercadería a proveedores que suben el stock, y llevar el seguimiento de pagos (parciales, vencimientos, cuenta corriente) con una UX equivalente a `orderCreate.vue`.

**Architecture:** Espeja el módulo de órdenes: modelos con `TenantScope` + `AuditModelTrait`, repositorios que extienden `BaseRepository` (`PurchaseRepository` concentra la transacción de alta/anulación y el movimiento de stock), controladores delgados y páginas Vue (Options API, Vuetify) copiando patrones de `orderCreate.vue`, `orderList.vue` y `brands.vue`. Los pagos a proveedor son una tabla propia (`supplier_payments`), no se mezclan con `payments` (que cuelga de `orders`).

**Tech Stack:** Laravel (PHP), MySQL, Vue 3 Options API + Vuetify, axios, Vuex, `SweetAlert` (`Swal`) ya usado en las pantallas.

**Spec:** pedido del usuario (conversación 2026-11-02): "módulo que registre compras de mercadería a proveedores, para subir el stock, llevar el seguimiento de los pagos a proveedores; UX lo más parecida a order-create; proceso lo más completo posible; que quede ordenado y registrado todo". No hay documento de diseño aparte.

## Global Constraints

- Todas las tablas nuevas: `tenant_id` (FK a `tenants`, cascade), `created_by`, `last_modified_by`, `deleted_by` (string 1000), `timestamps`, `softDeletes`, igual que `orders`.
- Todos los modelos: `use HasFactory, SoftDeletes, AuditModelTrait;` y `static::addGlobalScope(new TenantScope)` en `booted()`.
- Fechas entran como `d/m/Y` (igual que `OrderRepository::save`); se guardan `Y-m-d H:i:s`.
- Respuestas con `successResponse`, `successResponseCreate`, `errorResponse` de `ApiController`; mensajes de validación con `getMsg("required")`.
- Movimientos de stock: `StockMovement` con `movement_type` `'entrada'` (compra) y `'ajuste'` (anulación), `quantity`, `previous_quantity`, `new_quantity`, `notes`, `tenant_id`, `user_id`, creados vía `$stock->movements()->create([...])` como hace `OrderRepository`.
- Roles permitidos en rutas y menú: `['superadmin', 'bebidas-admin', 'petshop-admin']`.
- Rutas API dentro del grupo `Route::middleware(['auth:sanctum'])` de `routes/api.php`.
- Respetar finales de línea CRLF de los archivos existentes que se modifiquen.
- Sin modificar el comportamiento del módulo de órdenes.

## Decisiones de diseño (supuestos a confirmar)

| #   | Decisión                                                                                                                                           | Por qué                                                                                                                |
| --- | -------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------- |
| D1  | La compra guardada = mercadería **recibida**: el stock sube al guardar. Estados de compra: `received`                                              |
| D2  | Estado de pago derivado: `pending` / `partial` / `paid`, calculado desde `total_paid` vs `total_amount`.                                           | Igual que `PaymentRepository::updateOrderPaymentStatus`.                                                               |
| D3  | Los pagos permiten parciales y **no** permiten superar el saldo pendiente.                                                                         | A proveedores no hay "vuelto".                                                                                         |
| D4  | **No hay edición** de compras en v1: se corrige con "Anular" + nueva compra. Anular se bloquea si hay pagos activos (hay que anularlos primero).   | Edición exige revertir/reaplicar stock y pagos; duplica el alcance.                                                    |
| D5  | Al guardar, opción `update_cost` (default sí) actualiza `products.purchase_price` con el costo de la compra. No toca `sale_price`.                 | Mantiene costo/ganancia de las órdenes coherentes.                                                                     |
| D6  | Stock por `(product_id, warehouse_id)`; la compra elige depósito (default: primer depósito activo del tenant). Si no existe fila de stock se crea. | `stocks` tiene `warehouse_id`; las órdenes hoy ignoran el depósito, así que se respeta la fila existente del producto. |
| D7  | Sin IVA desglosado en v1 (subtotal, descuento, adicional, total).                                                                                  | El detalle fiscal lo lleva Administración; se puede sumar después.                                                     |

## Review Focus

- Doble clic en "Registrar compra" → no duplicar compra ni sumar el stock dos veces (botón deshabilitado + transacción).
- Producto sin fila en `stocks` → se crea la fila en vez de fallar.
- Cantidad ≤ 0, costo < 0 o lista de ítems vacía → rechazada con mensaje, sin tocar stock.
- Pago mayor al saldo pendiente → rechazado; pago exactamente igual → `paid`.
- Anular compra con pagos activos → rechazada; sin pagos → stock vuelve a lo anterior y queda movimiento.
- Eliminar un proveedor con compras → bloqueado (no se borran historiales).
- Aislamiento por tenant: un usuario de otro tenant no ve ni paga compras ajenas.
- Número de compra correlativo por tenant sin colisiones en concurrencia.

---

## File Structure

**Backend (crear)**

- `database/migrations/2026_11_02_100000_create_suppliers_table.php`
- `database/migrations/2026_11_02_100100_create_purchases_tables.php` — `purchases`, `purchase_items`, `supplier_payments`
- `app/Models/Supplier.php`, `Purchase.php`, `PurchaseItem.php`, `SupplierPayment.php`
- `app/Repositories/SupplierRepository.php`, `PurchaseRepository.php`, `SupplierPaymentRepository.php`
- `app/Http/Controllers/SupplierController.php`, `PurchaseController.php`, `SupplierPaymentController.php`
- `tests/Feature/PurchaseModuleTest.php`

**Backend (modificar)**

- `routes/api.php` — rutas de suppliers, purchases, supplier-payments
- `app/Models/Product.php` — sin cambios (solo se usa `purchase_price`)

**Frontend (crear)**

- `resources/js/pages/iampos/suppliers.vue` — ABM + diálogo "Cuenta corriente"
- `resources/js/pages/iampos/purchaseCreate.vue` — alta de compra (modelo: `orderCreate.vue`)
- `resources/js/pages/iampos/purchaseList.vue` — listado, detalle, pagos, anulación (modelo: `orderList.vue`)

**Frontend (modificar)**

- `resources/js/helper/apiRoute.js`, `resources/js/router/routes.js`, `resources/js/layouts/components/NavItems.vue`

---

### Task 1: Base de datos y modelos

**Files:**

- Create: las dos migraciones y los cuatro modelos de la sección File Structure.

**Interfaces:**

- Produces: tablas `suppliers`, `purchases`, `purchase_items`, `supplier_payments`; modelos con relaciones `Supplier::purchases()`, `Purchase::supplier()/items()/payments()/warehouse()`, `PurchaseItem::product()`, `SupplierPayment::purchase()/paymentMethod()`.

- [ ] **Step 1: `suppliers`**: `name` (string, req), `business_name`, `tax_id` (CUIT, index), `contact_name`, `email`, `telephone`, `address`, `locality_id` (nullable FK `localities`, set null), `payment_term_days` (unsignedSmallInteger default 0), `notes` (text null), `active` (boolean default 1). Índice único `(tax_id, tenant_id)` solo si `tax_id` no es nulo (validar en repositorio, no en BD, por simplicidad con MySQL).
- [ ] **Step 2: `purchases`**: `purchase_date` (dateTime), `purchase_number` (string 50, único con `tenant_id`), `supplier_id` (FK, restrict), `warehouse_id` (nullable FK `warehouses`), `supplier_document` (string 100 null: nº factura/remito del proveedor), `due_date` (date null), `status` (string 20 default `received`), `payment_status` (string 20 default `pending`), `quantity_products` (int), `subtotal`, `discount_amount`, `aditional`, `total_amount`, `total_paid` (decimal 12,2 default 0), `notes` (text null), `cancelled_at` (dateTime null), `cancel_reason` (string 500 null). Índices `supplier_id`, `payment_status`, `status`.
- [ ] **Step 3: `purchase_items`**: `purchase_id` (FK cascade), `product_id` (nullable FK, set null), `quantity` (decimal 10,2), `unit_cost` (decimal 12,2), `total_cost` (decimal 12,2). `supplier_payments`: `purchase_id` (FK cascade), `payment_method_id` (nullable FK set null), `amount` (decimal 12,2), `payment_date` (dateTime), `reference` (string 100 null), `notes` (text null).
- [ ] **Step 4: Modelos** con `$fillable`, `$casts` (decimales, `purchase_date` datetime), `TenantScope`, `AuditModelTrait`. `Purchase::generateNumber(int $tenantId): string` replica `Order::generateOrderNumber` (10 dígitos, `lockForUpdate`) y se asigna en `creating`.
- [ ] **Step 5: Verificar**: `php artisan migrate --pretend` sin errores; `php artisan migrate` en BD local y `php artisan migrate:rollback --step=2` + re-migrar. Esperado: crea/dropea las 4 tablas.
- [ ] **Step 6: Commit** `feat(compras): migraciones y modelos de proveedores y compras`.

### Task 2: Proveedores (API)

**Files:** `SupplierRepository.php`, `SupplierController.php`, `routes/api.php`, `tests/Feature/PurchaseModuleTest.php`

**Interfaces:**

- Produces: `GET/POST api/suppliers`, `GET/PUT api/suppliers/{id}`, `DELETE api/suppliers/{id}`; `GET api/suppliers/{id}/statement`.
- `SupplierRepository::statement(int $id)`: `{supplier, total_purchased, total_paid, balance, purchases: [{id, purchase_number, purchase_date, due_date, total_amount, total_paid, payment_status, overdue: bool}]}` solo compras `received`.

- [ ] **Step 1: Tests** en `PurchaseModuleTest`: `test_supplier_requires_name`, `test_cannot_delete_supplier_with_purchases`, `test_statement_balance_equals_total_minus_paid`.
- [ ] **Step 2: Correr y ver fallar.** `php artisan test --filter PurchaseModuleTest`
- [ ] **Step 3: Implementar** `SupplierRepository` (extiende `BaseRepository`, con `$relations = ['locality']`): `save`/`update` validan `name` requerido y `tax_id` no repetido en el tenant; `delete` devuelve `errorResponse` si `purchases()->exists()`; `statement` como arriba (`overdue` = `due_date < hoy` y `payment_status != paid`).
- [ ] **Step 4: Controller + rutas** (`apiResource('suppliers', …)` + `Route::get('suppliers/{id}/statement', …)` + `Route::post('suppliers/{id}', update)` si el front usa POST como `brands`).
- [ ] **Step 5: Correr tests** hasta PASS. **Step 6: Commit.**

### Task 3: Alta de compra con ingreso de stock

**Files:** `PurchaseRepository.php`, `PurchaseController.php`, rutas, tests.

**Interfaces:**

- Consumes: modelos de Task 1, `Stock::movements()`.
- Produces: `POST api/purchases` con body `{supplier_id, purchase_date (d/m/Y), warehouse_id?, supplier_document?, due_date? (d/m/Y), discount_amount, aditional, notes?, update_cost: bool, items: [{product_id, quantity, unit_cost}]}`; responde `{purchase, items}`.
- `PurchaseRepository::save($request)`; helper privado `increaseStock(Purchase $purchase, array $items, int $warehouseId): void`.

- [ ] **Step 1: Tests**: `test_creating_purchase_increases_stock_and_records_movement` (stock 10 + compra 5 → 15, movimiento `entrada` con previous 10 / new 15); `test_creates_stock_row_when_product_has_none`; `test_rejects_empty_items_zero_quantity_or_negative_cost` (stock sin cambios); `test_update_cost_sets_product_purchase_price`; `test_purchase_numbers_are_sequential_per_tenant`.
- [ ] **Step 2: Correr y ver fallar.**
- [ ] **Step 3: Implementar `save`** en `DB::transaction`: validar (proveedor existe y activo, ítems no vacíos, `quantity > 0`, `unit_cost >= 0`); **recalcular totales en servidor** (`subtotal = Σ quantity*unit_cost`, `total = subtotal - discount + aditional`, ignorar los totales del cliente); crear `Purchase` (`status=received`, `payment_status=pending`, `total_paid=0`), ítems, y `increaseStock` (por ítem: buscar/crear `Stock` por `(product_id, warehouse_id)`, `quantity += qty`, movimiento `entrada` con nota `"Compra #{purchase_number}"`); si `update_cost`, `Product::where('id', …)->update(['purchase_price' => unit_cost])`.
- [ ] **Step 4: Controller + ruta.** **Step 5: tests PASS. Step 6: Commit.**

### Task 4: Pagos a proveedor y anulación

**Files:** `SupplierPaymentRepository.php`, `SupplierPaymentController.php`, `PurchaseRepository.php` (método `cancel`), rutas, tests.

**Interfaces:**

- Produces: `POST api/supplier-payments` body `{purchase_id, payments: [{payment_method_id: {id}, amount, payment_date (d/m/Y), reference?, notes?}]}`; `DELETE api/supplier-payments/{id}` (anula el pago y recalcula); `POST api/purchases-cancel/{id}` body `{reason}`.
- `Purchase::refreshPaymentStatus(): void` — recalcula `total_paid` (suma de pagos activos) y `payment_status` (`paid` si `total_paid >= total_amount`, `partial` si `> 0`, si no `pending`).

- [ ] **Step 1: Tests**: `test_partial_then_full_payment_updates_status` (pending → partial → paid); `test_payment_over_pending_balance_is_rejected`; `test_voiding_payment_recomputes_status`; `test_cancel_purchase_restores_stock_and_logs_movement`; `test_cancel_blocked_when_purchase_has_active_payments`; `test_cancelled_purchase_rejects_new_payments`.
- [ ] **Step 2: Correr y ver fallar.**
- [ ] **Step 3: Implementar** `SupplierPaymentRepository::save` (transacción; compra debe estar `received`; suma de pagos nuevos ≤ saldo; insertar; `refreshPaymentStatus`), `delete` (soft delete + refresh), `PurchaseRepository::cancel` (transacción; bloquear si hay pagos; descontar stock con movimiento `ajuste` "Anulación compra #…"; `status=cancelled`, `cancelled_at`, `cancel_reason`).
- [ ] **Step 4: Controllers + rutas. Step 5: tests PASS. Step 6: Commit.**

### Task 5: Listado y detalle de compras (API)

**Files:** `PurchaseRepository.php`, `PurchaseController.php`, rutas, tests.

**Interfaces:**

- Produces: `GET api/purchases?supplier_id=&date_from=&date_to=&payment_status=&status=&purchase_number=` → compras con `supplier`, `payments.paymentMethod`; `GET api/purchases/{id}` → compra con `items.product`, `supplier`, `payments.paymentMethod`, `warehouse`.

- [ ] **Step 1: Test** `test_list_filters_by_supplier_and_payment_status` y `test_other_tenant_cannot_see_purchase` (usuario de otro tenant → 404/vacío).
- [ ] **Step 2–3: Implementar** `all`/`get` con filtros (fechas `Y-m-d` como `orders-search`), orden `purchase_date desc`; relaciones indicadas.
- [ ] **Step 4: tests PASS. Step 5: Commit.**

### Task 6: Frontend base — rutas, API, menú y ABM de proveedores

**Files:** `apiRoute.js`, `routes.js`, `NavItems.vue`, `suppliers.vue`.

**Interfaces:**

- Produces: claves `$routes`: `suppliers: "api/suppliers"`, `purchases: "api/purchases"`, `purchasesCancel: "api/purchases-cancel"`, `supplierPayments: "api/supplier-payments"`; rutas `suppliers`, `purchase-create`, `purchase-list` con `allowedRoles` del Global Constraints; grupo de menú **Compras** (Registrar Compra, Compras, Proveedores) filtrado por `navFilter`.

- [ ] **Step 1: Registrar rutas y menú** (mismo estilo que `order-create` / `brands`).
- [ ] **Step 2: `suppliers.vue`** copiando la estructura de `brands.vue` (tabla, diálogo alta/edición con los campos de Task 1, activar/desactivar) + columna de acción **Cuenta corriente** que abre un diálogo con `GET suppliers/{id}/statement` (saldo, compras pendientes, vencidas en rojo).
- [ ] **Step 3: Verificar** (`npm run dev`): alta, edición, intento de borrar proveedor con compras muestra el mensaje del backend.
- [ ] **Step 4: Commit.**

### Task 7: Pantalla "Registrar Compra"

**Files:** `purchaseCreate.vue`

**Interfaces:**

- Consumes: `POST $routes.purchases` (Task 3), `$routes.suppliers`, `$routes.products`, `$routes.warehouses`, `$routes.paymentMethods`, `$routes.supplierPayments`.

- [ ] **Step 1: Estructura** copiando la de `orderCreate.vue`: tarjeta superior (Proveedor con autocomplete + botón "nuevo proveedor" en diálogo, Fecha de compra, N° comprobante del proveedor, Depósito, Fecha de vencimiento — default `hoy + payment_term_days` del proveedor); fila de agregar producto (producto, cantidad, **costo unitario** precargado con `purchase_price`, total, `+`), tabla "Productos agregados" con quitar ítem, tarjeta de totales (subtotal, descuento, adicional, total final), notas, checkbox "Actualizar costo del producto", botón **Registrar compra**.
- [ ] **Step 2: Reglas**: `canAddItem` (producto, cantidad > 0, costo ≥ 0), mostrar stock actual junto al producto (como `orderCreate`), deshabilitar el botón mientras `creatingPurchase` (evita doble envío).
- [ ] **Step 3: Post-guardado**: diálogo "Compra registrada" con opciones **Registrar pago ahora** (reusa el patrón del diálogo de pagos de `orderCreate`, sin vuelto, monto ≤ total) o **Finalizar** (queda `pending`); luego `clearForm()`.
- [ ] **Step 4: Verificar en navegador**: compra de 2 productos → stock sube en `stocks.vue`; pago parcial → estado `partial`.
- [ ] **Step 5: Commit.**

### Task 8: Listado de compras, pagos y anulación

**Files:** `purchaseList.vue`

- [ ] **Step 1: Listado** (modelo `orderList.vue`): filtros proveedor, rango de fechas, estado de pago, estado, N° compra; columnas fecha, número, proveedor, comprobante, total, pagado, saldo, vencimiento (rojo si vencida), estado de pago (chip), estado; header fijo no requerido.
- [ ] **Step 2: Acciones por fila**: **Ver detalle** (diálogo con ítems y pagos, con "anular pago"), **Registrar pago** (solo si saldo > 0 y `received`), **Anular compra** (confirmación con motivo; deshabilitada si hay pagos, con tooltip explicando).
- [ ] **Step 3: Verificar** flujo completo: pagar saldo → `paid`; anular pago → vuelve a `partial`; anular compra sin pagos → stock vuelve.
- [ ] **Step 4: Commit.**

### Task 9: Cierre

- [ ] **Step 1:** `php artisan test` completo y `npm run build` sin errores (el build va a `public/build`, no se commitea).
- [ ] **Step 2:** Revisar el `git diff` contra Global Constraints (CRLF, `tenant_id`, roles) y que el módulo de órdenes no cambió.
- [ ] **Step 3: Commit final** y nota de despliegue: correr `php artisan migrate` en producción y subir **toda** `public/build` (ver incidente del header de `/products`).

---

## Self-review

- **Cobertura del pedido:** compras a proveedores (T3, T7), subir stock (T3), seguimiento de pagos (T4, T8, cuenta corriente en T2/T6), UX tipo order-create (T7), "ordenado y registrado" (auditoría, movimientos de stock, número correlativo, anulaciones con motivo, soft deletes).
- **Consistencia de nombres:** `purchase_number`, `supplier_payments`, `refreshPaymentStatus`, `increaseStock`, claves `$routes` coinciden entre tareas.
- **Riesgo de tests:** el repo solo tiene `ExampleTest` y `phpunit.xml` apunta a la BD `testing`; si no existe una BD de pruebas con las migraciones, el Step 1 de las Tasks 2–5 debe empezar creándola (o usar `RefreshDatabase` sobre una BD dedicada). **Nunca** correr los tests contra la BD de desarrollo.
- **Fuera de alcance (v1):** edición de compras, IVA desglosado, órdenes de compra/recepción parcial, devoluciones a proveedor, exportación a Excel.
