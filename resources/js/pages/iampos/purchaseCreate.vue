<template>
  <VForm ref="purchaseForm" v-model="validPurchase">
    <VRow>
      <!-- Proveedor y fechas -->
      <VCol cols="12" sm="12" md="6">
        <VCard>
          <VCardText class="mb-0 pb-2">
            <VRow class="mb-0 pb-0">
              <VCol cols="1" md="1" class="mb-0 pt-3 d-none d-sm-flex">
                <VAvatar
                  icon="ri-truck-line"
                  class="text-error mr-2"
                  variant="tonal"
                  size="40"
                />
              </VCol>
              <VCol cols="12" sm="9" md="9" class="mb-0 pt-4 ml-0 pr-0 mr-0">
                <VAutocomplete
                  v-model="purchase.supplier_id"
                  :items="suppliers"
                  :item-title="supplierTitle"
                  item-value="id"
                  label="Proveedor"
                  :rules="[(v) => !!v || 'Proveedor es requerido']"
                  clearable
                  density="compact"
                  @update:model-value="onSupplierChange"
                />
              </VCol>
              <VCol cols="auto" md="auto" class="mb-0 pt-4">
                <VBtn
                  :color="$cv('principal')"
                  title="Nuevo proveedor"
                  size="small"
                  @click="supplierDialog = true"
                >
                  <VIcon icon="ri-add-line" />
                </VBtn>
              </VCol>
            </VRow>
            <VRow class="mb-0 pb-0">
              <VCol cols="12" md="1" class="ml-0"> </VCol>
              <VCol cols="12" sm="6" md="6" class="mb-0 pt-0">
                <VTextField
                  v-model="purchase.purchase_date"
                  label="Fecha de compra"
                  placeholder="dd/mm/yyyy"
                  v-mask="'##/##/####'"
                  :rules="[(v) => !!v || 'Fecha requerida']"
                  clearable
                  density="compact"
                  @update:model-value="onPurchaseDateChange"
                />
              </VCol>
              <VCol cols="12" sm="6" md="5" class="mb-0 pt-0">
                <VTextField
                  v-model="purchase.supplier_document"
                  label="N° comprobante del proveedor"
                  placeholder="Factura / Remito"
                  density="compact"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Depósito y vencimiento -->
      <VCol cols="12" sm="12" md="6">
        <VCard>
          <VCardText class="mb-0 pb-2">
            <VRow class="mb-0 pb-0">
              <VCol cols="1" md="1" class="mb-0 pb-0 pt-2 d-none d-sm-flex">
                <VAvatar icon="ri-store-2-line" class="text-info mr-2" variant="tonal" />
              </VCol>
              <VCol cols="12" sm="11" md="11" class="mb-0 pt-3">
                <VAutocomplete
                  v-model="purchase.warehouse_id"
                  :items="warehouses"
                  item-title="name"
                  item-value="id"
                  label="Depósito donde ingresa la mercadería"
                  :rules="[(v) => !!v || 'Depósito es requerido']"
                  density="compact"
                />
              </VCol>
            </VRow>
            <VRow class="mb-0 pb-2">
              <VCol cols="1" md="1" class="mb-0 pb-0 pt-0 d-none d-sm-flex">
                <VAvatar
                  icon="ri-calendar-event-line"
                  class="text-info mr-2"
                  variant="tonal"
                />
              </VCol>
              <VCol cols="12" sm="6" md="6" class="mb-0 pt-0">
                <VTextField
                  v-model="purchase.due_date"
                  label="Vencimiento del pago"
                  placeholder="dd/mm/yyyy"
                  v-mask="'##/##/####'"
                  clearable
                  density="compact"
                  :hint="dueDateHint"
                  persistent-hint
                  @update:model-value="dueDateTouched = true"
                />
              </VCol>
              <VCol cols="12" sm="5" md="5" class="mb-0 pt-0">
                <VSwitch
                  v-model="purchase.update_cost"
                  color="primary"
                  hide-details
                  label="Actualizar costo del producto"
                  title="Guarda el costo de esta compra como precio de compra del producto"
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VRow>
      <VCol cols="12" sm="12" md="12">
        <VCard>
          <VCardText class="mb-0 pb-3">
            <!-- Agregar producto -->
            <VRow class="mb-0 pb-0">
              <VCol class="mb-0 pb-0 pt-4 d-none d-sm-flex flex-grow-0">
                <VAvatar
                  icon="ri-shopping-cart-line"
                  class="text-info mr-2"
                  variant="tonal"
                  size="40"
                />
              </VCol>

              <VCol cols="8" md="5" class="pt-5">
                <VAutocomplete
                  v-model="newItem.product_id"
                  :items="products"
                  :item-title="productTitle"
                  item-value="id"
                  label="Seleccionar Producto"
                  clearable
                  density="compact"
                  @update:model-value="onProductChange"
                >
                  <template v-slot:item="{ props, item }">
                    <v-sheet border="info md">
                      <VListItem v-bind="props">
                        <VListItemSubtitle class="text-caption">
                          <VChip color="info" size="small"
                            >Stock: {{ getProductStock(item.raw.id) }}
                          </VChip>
                          <span class="ml-2 text-success font-weight-bold">
                            Costo: ${{ formatNumber(item.raw.purchase_price) }}
                          </span>
                        </VListItemSubtitle>
                      </VListItem>
                    </v-sheet>
                  </template>
                </VAutocomplete>
              </VCol>
              <VCol cols="4" md="1" class="pr-0">
                <div class="px-2 pt-2">
                  <VTextField
                    v-model.number="newItem.quantity"
                    label="Cant"
                    type="number"
                    min="0"
                    :rules="[
                      (v) => !!v || 'Cantidad requerida',
                      (v) => v > 0 || 'Debe ser mayor a 0',
                    ]"
                    density="compact"
                  />
                  <div class="text-caption">
                    En stock: {{ getProductStock(newItem.product_id) }}
                  </div>
                </div>
              </VCol>

              <VCol cols="6" md="2" class="pt-5">
                <VTextField
                  v-model="newItem.unit_cost"
                  label="Costo Unitario"
                  type="number"
                  step="0.01"
                  min="0"
                  prefix="$"
                  :rules="[(v) => v !== '' && v !== null && v >= 0 || 'Costo requerido']"
                  density="compact"
                />
              </VCol>
              <VCol cols="6" md="2" class="pt-5">
                <VTextField
                  :model-value="formatNumber(calculateItemTotal())"
                  label="Total"
                  prefix="$"
                  readonly
                  variant="outlined"
                  density="compact"
                />
              </VCol>
              <VCol cols="12" md="1" class="pt-5">
                <VBtn color="primary" :disabled="!canAddItem" block @click="addItem">
                  <VIcon icon="ri-add-line" />
                </VBtn>
              </VCol>
            </VRow>

            <VRow class="my-0">
              <VCol cols="12" sm="12" md="9">
                <VCard>
                  <div class="v-card-item">
                    <div class="v-card-item__content">
                      <div class="v-card-title">
                        <h5 class="text-h5">
                          <VAvatar
                            icon="ri-luggage-cart-line"
                            class="text-info mr-2"
                            variant="tonal"
                          />Productos a ingresar
                        </h5>
                      </div>
                    </div>
                  </div>
                  <VCardText>
                    <div style="min-height: 280px; max-height: 270px; overflow-y: auto">
                      <VDataTable
                        :headers="itemHeaders"
                        :items="purchase.items"
                        class="elevation-1"
                        no-data-text="No hay productos agregados"
                        density="compact"
                        :items-per-page="1000"
                      >
                        <template #bottom></template>
                        <template #item.product_name="{ item }">
                          {{ getProductById(item.product_id)?.name }}
                        </template>
                        <template #item.quantity="{ item }">
                          <VTextField
                            v-model.number="item.quantity"
                            type="number"
                            min="0"
                            density="compact"
                            hide-details
                            style="width: 90px"
                            @input="updateItemTotal(rowIndex(item))"
                          />
                        </template>
                        <template #item.unit_cost="{ item }">
                          <VTextField
                            v-model="item.unit_cost"
                            type="number"
                            step="0.01"
                            min="0"
                            prefix="$"
                            density="compact"
                            hide-details
                            class="ma-0 pa-0 text-sm"
                            @input="updateItemTotal(rowIndex(item))"
                          />
                        </template>
                        <template #item.total_cost="{ item }">
                          ${{ formatNumber(item.total_cost) }}
                        </template>
                        <template #item.actions="{ item }">
                          <IconBtn size="small" color="error" @click="removeItem(rowIndex(item))">
                            <VIcon icon="ri-delete-bin-line" />
                          </IconBtn>
                        </template>
                      </VDataTable>
                    </div>
                  </VCardText>
                </VCard>
              </VCol>

              <!-- Totales -->
              <VCol cols="12" sm="12" md="3">
                <VCard>
                  <div class="v-card-item">
                    <div class="v-card-item__content">
                      <div class="v-card-title">
                        <h5 class="text-h5">
                          <VAvatar
                            icon="ri-calculator-line"
                            class="text-info mr-2"
                            variant="tonal"
                          />Totales
                        </h5>
                      </div>
                    </div>
                  </div>
                  <VCardText>
                    <VRow>
                      <VCol cols="12" md="12">
                        <VTextField
                          :model-value="formatCurrency(purchase.subtotal)"
                          label="Subtotal"
                          readonly
                          variant="outlined"
                          density="compact"
                          class="custom-bg-gray number-end"
                        />
                      </VCol>
                      <VCol cols="12" md="12">
                        <VTextField
                          v-model="purchase.discount_amount"
                          label="Descuento"
                          type="number"
                          step="0.01"
                          min="0"
                          prefix="$"
                          density="compact"
                          class="number-end"
                          :rules="[
                            (v) =>
                              parseFloat(v || 0) <= purchase.subtotal ||
                              'No puede superar el subtotal',
                          ]"
                          @input="calculateTotals"
                        />
                      </VCol>
                      <VCol cols="12" md="12">
                        <VTextField
                          v-model="purchase.aditional"
                          label="Adicional"
                          type="number"
                          step="0.01"
                          min="0"
                          prefix="$"
                          density="compact"
                          class="number-end"
                          @input="calculateTotals"
                        />
                      </VCol>
                    </VRow>
                    <VDivider class="my-4" />
                    <VRow>
                      <VCol cols="12" md="12">
                        <VTextField
                          :model-value="formatCurrency(purchase.total_amount)"
                          label="Total Final"
                          readonly
                          variant="outlined"
                          class="text-h6 custom-bg-gray number-end"
                          density="compact"
                        />
                      </VCol>
                    </VRow>
                  </VCardText>
                </VCard>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Notas -->
    <VRow>
      <VCol cols="12" sm="12" md="12">
        <VCard>
          <div class="v-card-item">
            <div class="v-card-item__content">
              <div class="v-card-title">
                <h5 class="text-h5">
                  <VAvatar
                    icon="ri-file-text-line"
                    class="text-info mr-2"
                    variant="tonal"
                  />Notas Adicionales
                </h5>
              </div>
            </div>
          </div>
          <VCardText>
            <VTextarea
              v-model="purchase.notes"
              label="Notas de la compra"
              rows="2"
              style="height: 80px"
            />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Botones de acción -->
    <VRow>
      <VCol cols="12" class="text-center">
        <VBtn color="primary" variant="outlined" class="mr-4" @click="$router.go(-1)">
          <VIcon icon="ri-close-circle-line" class="mr-2" />
          Cancelar
        </VBtn>
        <VBtn color="primary" class="mr-4" @click="$router.push({ path: 'purchase-list' })">
          <VIcon icon="ri-file-list-3-line" class="mr-2" />
          Compras
        </VBtn>
        <VBtn
          color="primary"
          :loading="creatingPurchase"
          :disabled="!canCreatePurchase"
          @click="createPurchase"
        >
          <VIcon icon="ri-save-line" class="mr-2" />
          Registrar Compra
        </VBtn>
      </VCol>
    </VRow>
  </VForm>

  <!-- Dialog Nuevo Proveedor -->
  <VDialog v-model="supplierDialog" max-width="600px">
    <VCard>
      <VToolbar :color="$cv('principal')">
        <VBtn icon="ri-close-line" color="white" @click="supplierDialog = false" />
        <VToolbarTitle>Nuevo Proveedor</VToolbarTitle>
      </VToolbar>
      <VForm ref="supplierForm" v-model="validSupplier">
        <VCardText>
          <VRow>
            <VCol cols="12" sm="6">
              <VTextField
                v-model="newSupplier.name"
                label="Nombre *"
                :rules="[(v) => !!(v && v.trim()) || 'El nombre es requerido']"
                density="compact"
              />
            </VCol>
            <VCol cols="12" sm="6">
              <VTextField v-model="newSupplier.tax_id" label="CUIT" density="compact" />
            </VCol>
            <VCol cols="12" sm="6">
              <VTextField
                v-model="newSupplier.telephone"
                label="Teléfono"
                density="compact"
              />
            </VCol>
            <VCol cols="12" sm="6">
              <VTextField
                v-model.number="newSupplier.payment_term_days"
                label="Plazo de pago (días)"
                type="number"
                min="0"
                density="compact"
              />
            </VCol>
          </VRow>
        </VCardText>
      </VForm>
      <VCardActions>
        <VSpacer />
        <VBtn variant="outlined" color="primary" @click="supplierDialog = false"
          >Cancelar</VBtn
        >
        <VBtn
          class="bg-primary"
          color="white"
          :loading="savingSupplier"
          @click="saveSupplier"
          >Guardar</VBtn
        >
      </VCardActions>
    </VCard>
  </VDialog>

  <!-- Dialog compra registrada -->
  <VDialog v-model="paymentDialog" max-width="580px" persistent>
    <VCard class="rounded-lg">
      <VToolbar color="primary" density="compact">
        <VToolbarTitle class="text-white font-weight-medium">
          Compra registrada
        </VToolbarTitle>
      </VToolbar>

      <VCardText class="px-4 pt-5 pb-2">
        <div class="text-center mb-5">
          <VAvatar color="success-lighten-5" size="56" class="mb-3">
            <VIcon icon="ri-checkbox-circle-fill" color="success" size="80" />
          </VAvatar>
          <h3 class="text-h5 font-weight-medium text-success mb-2">
            ¡Compra registrada y stock actualizado!
          </h3>
          <p class="text-medium-emphasis mb-0">
            ¿Querés registrar un pago al proveedor ahora?
          </p>
        </div>

        <VCard flat border class="mb-4">
          <VCardText class="pa-4">
            <div class="d-flex justify-space-between align-center mb-3">
              <span class="text-medium-emphasis">N° de compra:</span>
              <span class="font-weight-medium">
                #{{ createdPurchase?.purchase?.purchase_number }}
              </span>
            </div>
            <div class="d-flex justify-space-between align-center">
              <span class="text-medium-emphasis">Total:</span>
              <span class="text-h5 font-weight-medium text-success">
                {{ formatCurrency(createdPurchase?.purchase?.total_amount) }}
              </span>
            </div>
          </VCardText>
        </VCard>
      </VCardText>

      <VCardActions class="px-4 pb-4 pt-0">
        <VRow>
          <VCol cols="12" class="pt-4 pb-0 my-0">
            <VBtn
              variant="outlined"
              color="primary"
              block
              class="text-capitalize"
              @click="finishPurchase"
            >
              <VIcon icon="mdi-check" class="mr-1" />
              Finalizar sin pago
            </VBtn>
          </VCol>
          <VCol cols="12" class="pt-1 pb-2 my-0">
            <VBtn
              color="white"
              block
              class="bg-primary text-capitalize"
              @click="openPaymentForm"
            >
              <VIcon icon="mdi-cash-plus" class="mr-1" />
              Registrar Pago
            </VBtn>
          </VCol>
        </VRow>
      </VCardActions>
    </VCard>
  </VDialog>

  <!-- Dialog registrar pago -->
  <VDialog v-model="paymentFormDialog" max-width="900px" persistent>
    <VCard class="rounded-lg">
      <VToolbar color="primary" density="compact" class="ml-0 pl-0">
        <VToolbarTitle class="text-h6 font-weight-medium text-white">
          <VBtn icon="ri-close-line" color="white" @click="closePaymentForm" />
          Registrar Pago a Proveedor
        </VToolbarTitle>
      </VToolbar>

      <VForm ref="paymentForm" v-model="validPayment">
        <VCardText class="px-4 pt-4 pb-2">
          <VCard flat class="border" color="surface">
            <VCardItem class="py-2">
              <VCardTitle class="text-subtitle-1 font-weight-medium d-flex align-center">
                <VAvatar
                  icon="ri-money-dollar-circle-line"
                  class="text-primary mr-2"
                  variant="tonal"
                />
                Resumen de Pagos
              </VCardTitle>
            </VCardItem>
            <VDivider />
            <VCardText class="px-2 py-3">
              <div class="d-flex flex-column gap-2">
                <div class="d-flex justify-space-between">
                  <span class="text-caption text-medium-emphasis">Total Compra:</span>
                  <span class="font-weight-medium">{{
                    formatCurrency(createdPurchase?.purchase?.total_amount)
                  }}</span>
                </div>
                <div class="d-flex justify-space-between">
                  <span class="text-caption text-medium-emphasis">Pago a registrar:</span>
                  <span class="font-weight-medium text-primary">{{
                    formatCurrency(totalPaid)
                  }}</span>
                </div>
                <div class="d-flex justify-space-between">
                  <span class="text-caption text-medium-emphasis">Saldo Pendiente:</span>
                  <span
                    class="font-weight-medium"
                    :class="pendingAfterPayment > 0 ? 'text-error' : 'text-success'"
                  >
                    {{ formatCurrency(pendingAfterPayment) }}
                  </span>
                </div>
                <div v-if="paymentExceeds" class="text-error text-caption">
                  El pago supera el saldo de la compra.
                </div>
              </div>
            </VCardText>
          </VCard>

          <div class="mt-4">
            <PaymentsRow
              ref="paymentsRow"
              :key="keyPayments"
              modulo="pagos"
              :pending-amount="pendingAmount"
              @update-total="updateTotal"
            />
          </div>
        </VCardText>

        <VCardActions class="px-4 pb-4 pt-2">
          <VRow>
            <VCol cols="12" class="pt-4 pb-0 my-0">
              <VBtn
                variant="outlined"
                color="primary"
                class="text-capitalize"
                block
                @click="closePaymentForm"
              >
                Pagar después
              </VBtn>
            </VCol>
            <VCol cols="12" class="pt-1 my-0 pb-2">
              <VBtn
                :loading="savingPayment"
                :disabled="!validPayment || paymentExceeds"
                color="white"
                class="bg-primary text-capitalize"
                block
                @click="savePayment"
              >
                <template v-if="!savingPayment">
                  <VIcon icon="mdi-check-circle-outline" class="mr-1" />
                  Confirmar Pago
                </template>
                <template v-else> Procesando... </template>
              </VBtn>
            </VCol>
          </VRow>
        </VCardActions>
      </VForm>
    </VCard>
  </VDialog>

  <!-- Snackbar -->
  <VSnackbar v-model="snackbar" :color="snackbarColor" :timeout="4000">
    {{ snackbarText }}
    <template v-slot:action="{ attrs }">
      <VBtn text v-bind="attrs" @click="snackbar = false"> Cerrar </VBtn>
    </template>
  </VSnackbar>
</template>

<script>
import PaymentsRow from "@/components/PaymentsRow.vue";

function emptyPurchase() {
  return {
    supplier_id: null,
    warehouse_id: null,
    purchase_date: "",
    supplier_document: "",
    due_date: "",
    update_cost: true,
    subtotal: 0,
    aditional: 0,
    discount_amount: 0,
    total_amount: 0,
    notes: "",
    items: [],
  };
}

function emptyItem() {
  return { product_id: null, quantity: 1, unit_cost: 0 };
}

export default {
  components: { PaymentsRow },

  data() {
    return {
      keyPurchaseForm: 1,
      validPurchase: false,
      validSupplier: false,
      validPayment: false,

      // Loading states
      creatingPurchase: false,
      savingSupplier: false,
      savingPayment: false,

      // Dialogs
      supplierDialog: false,
      paymentDialog: false,
      paymentFormDialog: false,

      // Data
      suppliers: [],
      products: [],
      warehouses: [],

      purchase: emptyPurchase(),
      newItem: emptyItem(),
      dueDateTouched: false,

      newSupplier: { name: "", tax_id: "", telephone: "", payment_term_days: 0 },

      // Compra creada y pagos
      createdPurchase: null,
      keyPayments: 0,
      totalPaid: 0,

      // Snackbar
      snackbar: false,
      snackbarText: "",
      snackbarColor: "success",

      itemHeaders: [
        { title: "", key: "actions", width: "25px", sortable: false },
        { title: "Producto", key: "product_name", width: "300px", sortable: false },
        { title: "Cantidad", key: "quantity", width: "90px", sortable: false },
        { title: "Costo Unit.", key: "unit_cost", width: "150px", sortable: false },
        { title: "Total", key: "total_cost", width: "150px", align: "end", sortable: false },
      ],
    };
  },

  computed: {
    selectedSupplier() {
      return this.suppliers.find((s) => s.id === this.purchase.supplier_id);
    },

    dueDateHint() {
      const days = this.selectedSupplier?.payment_term_days;
      return days ? `Plazo del proveedor: ${days} días` : "";
    },

    canAddItem() {
      return (
        this.newItem.product_id &&
        parseFloat(this.newItem.quantity) > 0 &&
        this.newItem.unit_cost !== "" &&
        this.newItem.unit_cost !== null &&
        parseFloat(this.newItem.unit_cost) >= 0
      );
    },

    canCreatePurchase() {
      return (
        this.purchase.supplier_id &&
        this.purchase.warehouse_id &&
        this.purchase.items.length > 0 &&
        this.purchase.items.every(
          (i) => parseFloat(i.quantity) > 0 && parseFloat(i.unit_cost) >= 0
        ) &&
        parseFloat(this.purchase.discount_amount || 0) <= this.purchase.subtotal &&
        !this.creatingPurchase
      );
    },

    pendingAmount() {
      const purchase = this.createdPurchase?.purchase;
      if (!purchase) return 0;
      return Math.max(0, purchase.total_amount - (purchase.total_paid || 0));
    },

    pendingAfterPayment() {
      return Math.max(0, this.round2(this.pendingAmount - this.totalPaid));
    },

    paymentExceeds() {
      return this.round2(this.totalPaid) > this.round2(this.pendingAmount);
    },
  },

  async created() {
    await this.loadData();
  },

  methods: {
    async loadData() {
      Swal.alertGetInfo();
      try {
        const [suppliersRes, productsRes, warehousesRes] = await Promise.all([
          this.$axios.get(this.$routes["suppliers"]),
          this.$axios.get(this.$routes["products"]),
          this.$axios.get(this.$routes["warehouses"]),
        ]);

        this.suppliers = (suppliersRes.data.data || suppliersRes.data).filter(
          (s) => s.active !== 0
        );
        this.products = (productsRes.data.data || productsRes.data).filter(
          (p) => p.active !== 0
        );
        this.warehouses = (warehousesRes.data.data || warehousesRes.data).filter(
          (w) => w.active !== 0
        );

        if (!this.purchase.warehouse_id && this.warehouses.length) {
          this.purchase.warehouse_id = this.warehouses[0].id;
        }
        if (!this.purchase.purchase_date) {
          this.purchase.purchase_date = this.todayString();
        }
      } catch (error) {
        console.error("Error loading data:", error);
        this.showSnackbar("Error al cargar los datos", "error");
      } finally {
        Swal.close();
      }
    },

    // ---- Fechas (dd/mm/aaaa)
    todayString() {
      return this.formatDateObj(new Date());
    },
    formatDateObj(date) {
      const d = String(date.getDate()).padStart(2, "0");
      const m = String(date.getMonth() + 1).padStart(2, "0");
      return `${d}/${m}/${date.getFullYear()}`;
    },
    parseDateString(value) {
      const match = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(value || "");
      if (!match) return null;
      const date = new Date(Number(match[3]), Number(match[2]) - 1, Number(match[1]));
      return isNaN(date.getTime()) ? null : date;
    },
    suggestDueDate() {
      if (this.dueDateTouched) return;
      const days = parseInt(this.selectedSupplier?.payment_term_days || 0, 10);
      const base = this.parseDateString(this.purchase.purchase_date);
      if (!days || !base) {
        this.purchase.due_date = "";
        return;
      }
      base.setDate(base.getDate() + days);
      this.purchase.due_date = this.formatDateObj(base);
    },
    onSupplierChange() {
      this.dueDateTouched = false;
      this.suggestDueDate();
    },
    onPurchaseDateChange() {
      this.suggestDueDate();
    },

    // ---- Productos
    supplierTitle(item) {
      return item.tax_id ? `${item.name} [${item.tax_id}]` : item.name;
    },
    productTitle(item) {
      return item.code ? `${item.name} [${item.code}]` : item.name;
    },
    getProductById(productId) {
      return this.products.find((p) => p.id === productId);
    },
    getProductStock(productId) {
      if (!productId) return 0;
      const product = this.getProductById(productId);
      if (!product) return 0;
      return parseFloat(product.total_stock || 0);
    },
    onProductChange() {
      const product = this.getProductById(this.newItem.product_id);
      if (product) {
        this.newItem.unit_cost = parseFloat(product.purchase_price || 0);
      }
    },
    calculateItemTotal() {
      return (parseFloat(this.newItem.quantity) || 0) * (parseFloat(this.newItem.unit_cost) || 0);
    },
    addItem() {
      if (!this.canAddItem) return;

      if (this.purchase.items.some((i) => i.product_id === this.newItem.product_id)) {
        this.showSnackbar(
          "El producto ya está en la compra. Editá su cantidad en la tabla.",
          "warning"
        );
        return;
      }

      const quantity = parseFloat(this.newItem.quantity);
      const unitCost = parseFloat(this.newItem.unit_cost);
      this.purchase.items.push({
        product_id: this.newItem.product_id,
        quantity,
        unit_cost: unitCost,
        total_cost: this.round2(quantity * unitCost),
      });

      this.newItem = emptyItem();
      this.calculateTotals();
    },
    // Índice real en purchase.items (el index del slot cambia si la tabla se reordena)
    rowIndex(item) {
      return this.purchase.items.indexOf(item);
    },
    removeItem(index) {
      this.purchase.items.splice(index, 1);
      this.calculateTotals();
    },
    updateItemTotal(index) {
      const item = this.purchase.items[index];
      item.total_cost = this.round2(
        (parseFloat(item.quantity) || 0) * (parseFloat(item.unit_cost) || 0)
      );
      this.calculateTotals();
    },
    calculateTotals() {
      this.purchase.subtotal = this.round2(
        this.purchase.items.reduce((sum, item) => sum + parseFloat(item.total_cost || 0), 0)
      );
      this.purchase.total_amount = this.round2(
        this.purchase.subtotal +
          parseFloat(this.purchase.aditional || 0) -
          parseFloat(this.purchase.discount_amount || 0)
      );
    },
    round2(value) {
      return Math.round((Number(value) || 0) * 100) / 100;
    },

    // ---- Proveedor rápido
    async saveSupplier() {
      const { valid } = await this.$refs.supplierForm.validate();
      if (!valid) return;

      this.savingSupplier = true;
      try {
        const response = await this.$axios.post(this.$routes["suppliers"], this.newSupplier);
        const created = response.data.data || response.data;
        this.suppliers.push(created);
        this.purchase.supplier_id = created.id;
        this.onSupplierChange();
        this.supplierDialog = false;
        this.newSupplier = { name: "", tax_id: "", telephone: "", payment_term_days: 0 };
        this.showSnackbar("Proveedor creado", "success");
      } catch (error) {
        this.showSnackbar(this.errorMessage(error, "Error al crear el proveedor"), "error");
      } finally {
        this.savingSupplier = false;
      }
    },

    // ---- Alta de compra
    async createPurchase() {
      if (this.creatingPurchase) return;

      const { valid } = await this.$refs.purchaseForm.validate();
      if (!valid) return;

      this.creatingPurchase = true;
      Swal.alertGetInfo();
      try {
        const payload = {
          ...this.purchase,
          items: this.purchase.items.map((i) => ({
            product_id: i.product_id,
            quantity: i.quantity,
            unit_cost: i.unit_cost,
          })),
        };
        const response = await this.$axios.post(this.$routes["purchases"], payload);

        this.createdPurchase = response.data.data || response.data;
        this.totalPaid = 0;

        if (this.createdPurchase.purchase.total_amount > 0) {
          this.paymentDialog = true;
        } else {
          this.showSnackbar("Compra registrada y stock actualizado", "success");
          this.finishPurchase();
        }
      } catch (error) {
        this.showSnackbar(this.errorMessage(error, "Error al registrar la compra"), "error");
      } finally {
        this.creatingPurchase = false;
        Swal.close();
      }
    },

    // ---- Pagos
    openPaymentForm() {
      this.paymentDialog = false;
      this.totalPaid = 0;
      this.keyPayments++;
      this.paymentFormDialog = true;
    },
    closePaymentForm() {
      this.paymentFormDialog = false;
      this.showSnackbar("Compra registrada. Queda pendiente de pago.", "success");
      this.finishPurchase();
    },
    updateTotal(newTotal) {
      this.totalPaid = this.round2(newTotal);
    },
    validatePayments(payments) {
      if (payments.length <= 0) {
        return "Debe incluir al menos una modalidad de pago";
      }

      const methods = new Set();
      for (const payment of payments) {
        if (!payment.amount || !payment.payment_method_id) {
          return "Verifique la información de los pagos";
        }
        if (parseFloat(payment.amount) <= 0) {
          return "El monto de cada pago debe ser mayor a 0";
        }
        if (methods.has(payment.payment_method_id.id)) {
          return (
            "Ha seleccionado más de una vez el mismo método de pago: " +
            payment.payment_method_id.name
          );
        }
        methods.add(payment.payment_method_id.id);
      }

      return true;
    },
    async savePayment() {
      const payments = this.$refs.paymentsRow.payments;
      const isValid = this.validatePayments(payments);
      if (isValid !== true) return this.showSnackbar(isValid, "error");
      if (this.paymentExceeds) {
        return this.showSnackbar("El pago supera el saldo de la compra", "error");
      }
      if (this.savingPayment) return;

      this.savingPayment = true;
      Swal.alertGetInfo();
      try {
        const response = await this.$axios.post(this.$routes["supplierPayments"], {
          purchase_id: this.createdPurchase.purchase.id,
          payments,
        });
        if (response.status == 201) {
          this.showSnackbar("Pago registrado exitosamente", "success");
          this.paymentFormDialog = false;
          this.finishPurchase();
        }
      } catch (error) {
        this.showSnackbar(this.errorMessage(error, "Error al registrar el pago"), "error");
      } finally {
        this.savingPayment = false;
        Swal.close();
      }
    },

    finishPurchase() {
      this.paymentDialog = false;
      this.paymentFormDialog = false;
      this.clearForm();
    },
    clearForm() {
      this.purchase = emptyPurchase();
      this.newItem = emptyItem();
      this.dueDateTouched = false;
      this.createdPurchase = null;
      this.totalPaid = 0;

      this.$nextTick(() => {
        this.$refs.purchaseForm?.resetValidation();
      });

      // recarga productos (stock actualizado) y valores por defecto
      this.loadData();
    },

    // ---- Utilidades
    errorMessage(error, fallback) {
      return error?.response?.data?.message || fallback;
    },
    formatNumber(value) {
      return new Intl.NumberFormat("es-AR", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(value || 0);
    },
    formatCurrency(value) {
      if (!value) return "$0";
      return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(value);
    },
    showSnackbar(text, color) {
      this.snackbarText = text;
      this.snackbarColor = color;
      this.snackbar = true;
    },
  },
};
</script>

<style>
.custom-bg-gray .v-field__overlay {
  background-color: #ddd !important;
}
.number-end .v-field__input {
  text-align: end !important;
}
.text-h6 input {
  font-size: 1.25rem !important;
  font-weight: 600 !important;
}
</style>
<style scoped>
tbody {
  font-size: 13px !important;
}
.v-card-title {
  font-weight: 600;
}
.v-data-table {
  border: 1px solid #e0e0e0;
}
.v-data-table__tr {
  height: 43px !important;
}
</style>
