<template>
  <VCard title="Compras y Pagos a Proveedores">
    <VCardText>
      <!-- Filtros -->
      <VRow dense>
        <VCol cols="12" sm="6" md="3">
          <VAutocomplete
            v-model="filters.supplier_id"
            :items="suppliers"
            item-title="name"
            item-value="id"
            label="Proveedor"
            clearable
            density="compact"
          />
        </VCol>
        <VCol cols="6" sm="3" md="2">
          <VTextField
            v-model="filters.date_from"
            label="Desde"
            type="date"
            density="compact"
          />
        </VCol>
        <VCol cols="6" sm="3" md="2">
          <VTextField
            v-model="filters.date_to"
            label="Hasta"
            type="date"
            density="compact"
          />
        </VCol>
        <VCol cols="6" sm="4" md="2">
          <VSelect
            v-model="filters.payment_status"
            :items="paymentStatusOptions"
            label="Estado de pago"
            clearable
            density="compact"
          />
        </VCol>
        <VCol cols="6" sm="4" md="1">
          <VSelect
            v-model="filters.status"
            :items="statusOptions"
            label="Estado"
            clearable
            density="compact"
          />
        </VCol>
        <VCol cols="12" sm="4" md="2">
          <VTextField
            v-model="filters.purchase_number"
            label="N° compra"
            density="compact"
            clearable
            @keyup.enter="loadPurchases"
          />
        </VCol>
      </VRow>
      <VRow dense class="mb-2">
        <VCol cols="12" class="d-flex flex-wrap gap-2 align-center">
          <VBtn color="primary" :loading="loading" @click="loadPurchases">
            <VIcon icon="ri-search-line" class="mr-1" /> Buscar
          </VBtn>
          <VBtn variant="outlined" color="primary" @click="clearFilters">Limpiar</VBtn>
          <VBtn variant="tonal" color="warning" @click="showPending">
            <VIcon icon="ri-time-line" class="mr-1" /> Pendientes de pago
          </VBtn>
          <VSpacer />
          <VBtn color="primary" @click="$router.push({ path: 'purchase-create' })">
            <VIcon icon="ri-add-circle-line" class="mr-1" /> Registrar compra
          </VBtn>
        </VCol>
      </VRow>

      <!-- Resumen (solo compras recibidas del resultado) -->
      <VRow dense class="mb-2">
        <VCol cols="12" sm="4">
          <VCard flat border>
            <VCardText class="text-center py-2">
              <div class="text-caption text-medium-emphasis">Total comprado</div>
              <div class="text-h6">{{ formatCurrency(summary.total) }}</div>
            </VCardText>
          </VCard>
        </VCol>
        <VCol cols="12" sm="4">
          <VCard flat border>
            <VCardText class="text-center py-2">
              <div class="text-caption text-medium-emphasis">Pagado</div>
              <div class="text-h6 text-success">{{ formatCurrency(summary.paid) }}</div>
            </VCardText>
          </VCard>
        </VCol>
        <VCol cols="12" sm="4">
          <VCard flat border>
            <VCardText class="text-center py-2">
              <div class="text-caption text-medium-emphasis">Saldo a pagar</div>
              <div class="text-h6" :class="summary.balance > 0 ? 'text-error' : 'text-success'">
                {{ formatCurrency(summary.balance) }}
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>

      <VDataTable
        :headers="headers"
        :items="purchases"
        :loading="loading"
        class="text-no-wrap striped-table"
        density="compact"
        no-data-text="No hay compras para los filtros seleccionados"
        :items-per-page="25"
      >
        <template #item.actions="{ item }">
          <VMenu>
            <template #activator="{ props }">
              <IconBtn size="small" v-bind="props">
                <VIcon icon="ri-more-2-fill" />
              </IconBtn>
            </template>
            <VList density="compact">
              <VListItem @click="openDetail(item)">
                <VListItemTitle>
                  <VIcon icon="ri-eye-line" class="mr-2" />Ver detalle
                </VListItemTitle>
              </VListItem>
              <VListItem v-if="canPay(item)" @click="openPayment(item)">
                <VListItemTitle>
                  <VIcon icon="ri-money-dollar-circle-line" class="mr-2" />Registrar pago
                </VListItemTitle>
              </VListItem>
              <VListItem v-if="item.status === 'received'" @click="openCancel(item)">
                <VListItemTitle class="text-error">
                  <VIcon icon="ri-close-circle-line" class="mr-2" />Anular compra
                </VListItemTitle>
              </VListItem>
            </VList>
          </VMenu>
        </template>
        <template #item.supplier="{ item }">
          {{ item.supplier?.name }}
        </template>
        <template #item.purchase_date="{ item }">
          {{ formatDate(item.purchase_date) }}
        </template>
        <template #item.total_amount="{ item }">
          {{ formatCurrency(item.total_amount) }}
        </template>
        <template #item.total_paid="{ item }">
          {{ formatCurrency(item.total_paid) }}
        </template>
        <template #item.balance="{ item }">
          <span :class="item.status === 'received' && item.balance > 0 ? 'text-error' : ''">
            {{ item.status === "received" ? formatCurrency(item.balance) : "-" }}
          </span>
        </template>
        <template #item.due_date="{ item }">
          <span :class="isOverdue(item) ? 'text-error font-weight-bold' : ''">
            {{ item.due_date ? formatDate(item.due_date) : "-" }}
          </span>
          <VChip v-if="isOverdue(item)" color="error" size="x-small" class="ml-1"
            >Vencida</VChip
          >
        </template>
        <template #item.payment_status="{ item }">
          <VChip
            v-if="item.status === 'received'"
            :color="paymentStatusColor(item.payment_status)"
            size="small"
          >
            {{ paymentStatusLabel(item.payment_status) }}
          </VChip>
          <span v-else>-</span>
        </template>
        <template #item.status="{ item }">
          <VChip :color="item.status === 'received' ? 'success' : 'default'" size="small">
            {{ item.status === "received" ? "Recibida" : "Anulada" }}
          </VChip>
        </template>
      </VDataTable>
    </VCardText>
  </VCard>

  <!-- Detalle -->
  <VDialog v-model="detailDialog" max-width="1000px">
    <VCard v-if="detail">
      <VToolbar :color="$cv('principal')">
        <VBtn icon="ri-close-line" color="white" @click="detailDialog = false" />
        <VToolbarTitle>Compra #{{ detail.purchase_number }}</VToolbarTitle>
        <VChip :color="detail.status === 'received' ? 'success' : 'default'" class="mr-4">
          {{ detail.status === "received" ? "Recibida" : "Anulada" }}
        </VChip>
      </VToolbar>

      <VCardText class="pt-4">
        <VRow dense>
          <VCol cols="12" sm="6" md="3">
            <div class="text-caption text-medium-emphasis">Proveedor</div>
            <div class="font-weight-medium">{{ detail.supplier?.name }}</div>
          </VCol>
          <VCol cols="6" sm="3" md="2">
            <div class="text-caption text-medium-emphasis">Fecha</div>
            <div>{{ formatDate(detail.purchase_date) }}</div>
          </VCol>
          <VCol cols="6" sm="3" md="2">
            <div class="text-caption text-medium-emphasis">Vencimiento</div>
            <div>{{ detail.due_date ? formatDate(detail.due_date) : "-" }}</div>
          </VCol>
          <VCol cols="6" sm="6" md="3">
            <div class="text-caption text-medium-emphasis">Comprobante</div>
            <div>{{ detail.supplier_document || "-" }}</div>
          </VCol>
          <VCol cols="6" sm="6" md="2">
            <div class="text-caption text-medium-emphasis">Depósito</div>
            <div>{{ detail.warehouse?.name || "-" }}</div>
          </VCol>
        </VRow>

        <VAlert
          v-if="detail.status === 'cancelled'"
          type="warning"
          variant="tonal"
          class="my-3"
        >
          Compra anulada el {{ formatDate(detail.cancelled_at) }}. Motivo:
          {{ detail.cancel_reason }}
        </VAlert>

        <h4 class="mt-4 mb-2">Productos</h4>
        <VDataTable
          :headers="itemHeaders"
          :items="detail.items"
          density="compact"
          class="elevation-1"
          :items-per-page="1000"
        >
          <template #bottom></template>
          <template #item.product="{ item }">
            {{ item.product?.name }}
          </template>
          <template #item.unit_cost="{ item }">{{ formatCurrency(item.unit_cost) }}</template>
          <template #item.total_cost="{ item }">{{ formatCurrency(item.total_cost) }}</template>
        </VDataTable>

        <VRow class="mt-2" justify="end">
          <VCol cols="12" sm="5" md="4">
            <div class="d-flex justify-space-between">
              <span class="text-medium-emphasis">Subtotal</span>
              <span>{{ formatCurrency(detail.subtotal) }}</span>
            </div>
            <div class="d-flex justify-space-between">
              <span class="text-medium-emphasis">Descuento</span>
              <span>-{{ formatCurrency(detail.discount_amount) }}</span>
            </div>
            <div class="d-flex justify-space-between">
              <span class="text-medium-emphasis">Adicional</span>
              <span>{{ formatCurrency(detail.aditional) }}</span>
            </div>
            <VDivider class="my-1" />
            <div class="d-flex justify-space-between font-weight-bold">
              <span>Total</span>
              <span>{{ formatCurrency(detail.total_amount) }}</span>
            </div>
            <div class="d-flex justify-space-between text-success">
              <span>Pagado</span>
              <span>{{ formatCurrency(detail.total_paid) }}</span>
            </div>
            <div
              v-if="detail.status === 'received'"
              class="d-flex justify-space-between"
              :class="detail.balance > 0 ? 'text-error' : 'text-success'"
            >
              <span>Saldo</span>
              <span>{{ formatCurrency(detail.balance) }}</span>
            </div>
          </VCol>
        </VRow>

        <h4 class="mt-4 mb-2">Pagos</h4>
        <VDataTable
          :headers="paymentHeaders"
          :items="detail.payments"
          density="compact"
          class="elevation-1"
          no-data-text="Sin pagos registrados"
          :items-per-page="1000"
        >
          <template #bottom></template>
          <template #item.payment_date="{ item }">{{ formatDate(item.payment_date) }}</template>
          <template #item.payment_method="{ item }">
            {{ item.payment_method?.name }}
          </template>
          <template #item.amount="{ item }">{{ formatCurrency(item.amount) }}</template>
          <template #item.actions="{ item }">
            <IconBtn size="small" color="error" title="Anular pago" @click="voidPayment(item)">
              <VIcon icon="ri-delete-bin-line" />
            </IconBtn>
          </template>
        </VDataTable>

        <div v-if="detail.notes" class="mt-4">
          <div class="text-caption text-medium-emphasis">Notas</div>
          <div>{{ detail.notes }}</div>
        </div>
      </VCardText>

      <VCardActions class="px-4 pb-4">
        <VSpacer />
        <VBtn v-if="canPay(detail)" color="primary" @click="openPayment(detail)">
          <VIcon icon="ri-money-dollar-circle-line" class="mr-1" /> Registrar pago
        </VBtn>
        <VBtn variant="outlined" color="primary" @click="detailDialog = false">Cerrar</VBtn>
      </VCardActions>
    </VCard>
  </VDialog>

  <!-- Registrar pago -->
  <VDialog v-model="paymentDialog" max-width="900px" persistent>
    <VCard v-if="paymentTarget" class="rounded-lg">
      <VToolbar color="primary" density="compact" class="ml-0 pl-0">
        <VToolbarTitle class="text-h6 font-weight-medium text-white">
          <VBtn icon="ri-close-line" color="white" @click="closePayment" />
          Registrar Pago - Compra #{{ paymentTarget.purchase_number }}
        </VToolbarTitle>
      </VToolbar>

      <VForm ref="paymentForm" v-model="validPayment">
        <VCardText class="px-4 pt-4 pb-2">
          <VCard flat class="border" color="surface">
            <VCardText class="px-4 py-3">
              <div class="d-flex flex-column gap-2">
                <div class="d-flex justify-space-between">
                  <span class="text-caption text-medium-emphasis">Proveedor:</span>
                  <span class="font-weight-medium">{{ paymentTarget.supplier?.name }}</span>
                </div>
                <div class="d-flex justify-space-between">
                  <span class="text-caption text-medium-emphasis">Total compra:</span>
                  <span class="font-weight-medium">{{
                    formatCurrency(paymentTarget.total_amount)
                  }}</span>
                </div>
                <div class="d-flex justify-space-between">
                  <span class="text-caption text-medium-emphasis">Ya pagado:</span>
                  <span class="font-weight-medium text-success">{{
                    formatCurrency(paymentTarget.total_paid)
                  }}</span>
                </div>
                <div class="d-flex justify-space-between">
                  <span class="text-caption text-medium-emphasis">Saldo pendiente:</span>
                  <span class="font-weight-medium text-error">{{
                    formatCurrency(paymentTarget.balance)
                  }}</span>
                </div>
                <VDivider class="my-1" />
                <div class="d-flex justify-space-between">
                  <span class="text-caption text-medium-emphasis">Pago a registrar:</span>
                  <span class="font-weight-medium text-primary">{{
                    formatCurrency(totalPaid)
                  }}</span>
                </div>
                <div v-if="paymentExceeds" class="text-error text-caption">
                  El pago supera el saldo pendiente.
                </div>
              </div>
            </VCardText>
          </VCard>

          <div class="mt-4">
            <PaymentsRow
              ref="paymentsRow"
              :key="keyPayments"
              modulo="pagos"
              :pending-amount="paymentTarget.balance"
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
                @click="closePayment"
              >
                Cancelar
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
                <VIcon icon="mdi-check-circle-outline" class="mr-1" />
                Confirmar Pago
              </VBtn>
            </VCol>
          </VRow>
        </VCardActions>
      </VForm>
    </VCard>
  </VDialog>

  <!-- Anular compra -->
  <VDialog v-model="cancelDialog" max-width="520px" persistent>
    <VCard v-if="cancelTarget">
      <VToolbar color="error" density="compact">
        <VToolbarTitle class="text-white">
          Anular compra #{{ cancelTarget.purchase_number }}
        </VToolbarTitle>
      </VToolbar>
      <VCardText class="pt-4">
        <VAlert type="warning" variant="tonal" class="mb-4">
          Se va a descontar del stock la mercadería ingresada por esta compra.
          <span v-if="cancelTarget.total_paid > 0">
            <b>Esta compra tiene pagos registrados:</b> anulalos primero desde el detalle.
          </span>
        </VAlert>
        <VTextarea
          v-model="cancelReason"
          label="Motivo de la anulación *"
          rows="3"
          counter="500"
          maxlength="500"
          :rules="[(v) => !!(v && v.trim()) || 'El motivo es requerido']"
        />
      </VCardText>
      <VCardActions class="px-4 pb-4">
        <VSpacer />
        <VBtn variant="outlined" color="primary" @click="cancelDialog = false">Volver</VBtn>
        <VBtn
          color="error"
          :loading="cancelling"
          :disabled="!cancelReason || !cancelReason.trim() || cancelTarget.total_paid > 0"
          @click="confirmCancel"
        >
          Anular compra
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>

  <VSnackbar v-model="snackbar" :color="snackbarColor" :timeout="4000">
    {{ snackbarText }}
    <template v-slot:action="{ attrs }">
      <VBtn text v-bind="attrs" @click="snackbar = false"> Cerrar </VBtn>
    </template>
  </VSnackbar>
</template>

<script>
import PaymentsRow from "@/components/PaymentsRow.vue";

function toYmd(date) {
  const d = String(date.getDate()).padStart(2, "0");
  const m = String(date.getMonth() + 1).padStart(2, "0");
  return `${date.getFullYear()}-${m}-${d}`;
}

export default {
  components: { PaymentsRow },

  data() {
    const from = new Date();
    from.setDate(from.getDate() - 30);

    return {
      loading: false,
      purchases: [],
      suppliers: [],

      filters: {
        supplier_id: null,
        date_from: toYmd(from),
        date_to: "",
        payment_status: null,
        status: null,
        purchase_number: "",
      },
      paymentStatusOptions: [
        { title: "Pendiente", value: "pending" },
        { title: "Pago parcial", value: "partial" },
        { title: "Pagada", value: "paid" },
      ],
      statusOptions: [
        { title: "Recibida", value: "received" },
        { title: "Anulada", value: "cancelled" },
      ],

      headers: [
        { title: "", key: "actions", sortable: false, width: "40px" },
        { title: "N° Compra", key: "purchase_number" },
        { title: "Fecha", key: "purchase_date" },
        { title: "Proveedor", key: "supplier", sortable: false },
        { title: "Comprobante", key: "supplier_document" },
        { title: "Total", key: "total_amount", align: "end" },
        { title: "Pagado", key: "total_paid", align: "end" },
        { title: "Saldo", key: "balance", align: "end", sortable: false },
        { title: "Vencimiento", key: "due_date" },
        { title: "Pago", key: "payment_status" },
        { title: "Estado", key: "status" },
      ],
      itemHeaders: [
        { title: "Producto", key: "product", sortable: false },
        { title: "Cantidad", key: "quantity", align: "end" },
        { title: "Costo unit.", key: "unit_cost", align: "end" },
        { title: "Total", key: "total_cost", align: "end" },
      ],
      paymentHeaders: [
        { title: "Fecha", key: "payment_date" },
        { title: "Método", key: "payment_method", sortable: false },
        { title: "Referencia", key: "reference" },
        { title: "Importe", key: "amount", align: "end" },
        { title: "", key: "actions", sortable: false, width: "40px" },
      ],

      // Detalle
      detailDialog: false,
      detail: null,

      // Pago
      paymentDialog: false,
      paymentTarget: null,
      validPayment: false,
      savingPayment: false,
      keyPayments: 0,
      totalPaid: 0,

      // Anulación
      cancelDialog: false,
      cancelTarget: null,
      cancelReason: "",
      cancelling: false,

      snackbar: false,
      snackbarText: "",
      snackbarColor: "success",
    };
  },

  computed: {
    // Solo compras recibidas: las anuladas no son deuda ni gasto
    summary() {
      const received = this.purchases.filter((p) => p.status === "received");
      const total = received.reduce((s, p) => s + Number(p.total_amount || 0), 0);
      const paid = received.reduce((s, p) => s + Number(p.total_paid || 0), 0);
      return { total, paid, balance: Math.round((total - paid) * 100) / 100 };
    },

    paymentExceeds() {
      return (
        Math.round(this.totalPaid * 100) >
        Math.round(Number(this.paymentTarget?.balance || 0) * 100)
      );
    },
  },

  async created() {
    await Promise.all([this.loadSuppliers(), this.loadPurchases()]);
  },

  methods: {
    async loadSuppliers() {
      try {
        const res = await this.$axios.get(this.$routes["suppliers"]);
        this.suppliers = res.data.data || res.data;
      } catch (error) {
        console.error("Error cargando proveedores:", error);
      }
    },

    async loadPurchases() {
      this.loading = true;
      try {
        const params = {};
        Object.entries(this.filters).forEach(([key, value]) => {
          if (value !== null && value !== "" && value !== undefined) params[key] = value;
        });
        const res = await this.$axios.get(this.$routes["purchases"], { params });
        this.purchases = res.data.data || res.data;
      } catch (error) {
        this.showSnackbar(this.errorMessage(error, "Error al cargar las compras"), "error");
      } finally {
        this.loading = false;
      }
    },

    clearFilters() {
      this.filters = {
        supplier_id: null,
        date_from: "",
        date_to: "",
        payment_status: null,
        status: null,
        purchase_number: "",
      };
      this.loadPurchases();
    },

    showPending() {
      this.filters.payment_status = null;
      this.filters.status = "received";
      this.filters.date_from = "";
      this.filters.date_to = "";
      this.loadPurchases().then(() => {
        // pendientes y parciales
        this.purchases = this.purchases.filter((p) => p.payment_status !== "paid");
      });
    },

    // ---- Detalle
    async openDetail(item) {
      Swal.alertGetInfo();
      try {
        const res = await this.$axios.get(`${this.$routes["purchases"]}/${item.id}`);
        this.detail = res.data.data || res.data;
        this.detailDialog = true;
      } catch (error) {
        this.showSnackbar(this.errorMessage(error, "No se pudo cargar la compra"), "error");
      } finally {
        Swal.close();
      }
    },

    async refreshDetail() {
      if (!this.detail) return;
      const res = await this.$axios.get(`${this.$routes["purchases"]}/${this.detail.id}`);
      this.detail = res.data.data || res.data;
    },

    // ---- Pagos
    canPay(item) {
      return item.status === "received" && Number(item.balance) > 0;
    },

    openPayment(item) {
      this.paymentTarget = item;
      this.totalPaid = 0;
      this.keyPayments++;
      this.paymentDialog = true;
    },

    closePayment() {
      this.paymentDialog = false;
      this.paymentTarget = null;
    },

    updateTotal(total) {
      this.totalPaid = Math.round(Number(total || 0) * 100) / 100;
    },

    validatePayments(payments) {
      if (payments.length <= 0) return "Debe incluir al menos una modalidad de pago";

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
      if (this.savingPayment) return;

      const payments = this.$refs.paymentsRow.payments;
      const isValid = this.validatePayments(payments);
      if (isValid !== true) return this.showSnackbar(isValid, "error");
      if (this.paymentExceeds) {
        return this.showSnackbar("El pago supera el saldo pendiente", "error");
      }

      this.savingPayment = true;
      Swal.alertGetInfo();
      try {
        await this.$axios.post(this.$routes["supplierPayments"], {
          purchase_id: this.paymentTarget.id,
          payments,
        });
        this.showSnackbar("Pago registrado exitosamente", "success");
        this.closePayment();
        await this.loadPurchases();
        if (this.detailDialog) await this.refreshDetail();
      } catch (error) {
        this.showSnackbar(this.errorMessage(error, "Error al registrar el pago"), "error");
      } finally {
        this.savingPayment = false;
        Swal.close();
      }
    },

    voidPayment(payment) {
      Swal.fire({
        title: "Anular pago",
        text: `¿Desea anular el pago de ${this.formatCurrency(payment.amount)}?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Anular pago",
        cancelButtonText: "Cancelar",
      }).then(async (result) => {
        if (!result.isConfirmed) return;

        Swal.alertGetInfo();
        try {
          await this.$axios.delete(`${this.$routes["supplierPayments"]}/${payment.id}`);
          this.showSnackbar("Pago anulado", "success");
          await this.refreshDetail();
          await this.loadPurchases();
        } catch (error) {
          this.showSnackbar(this.errorMessage(error, "No se pudo anular el pago"), "error");
        } finally {
          Swal.close();
        }
      });
    },

    // ---- Anulación de compra
    openCancel(item) {
      this.cancelTarget = item;
      this.cancelReason = "";
      this.cancelDialog = true;
    },

    async confirmCancel() {
      if (this.cancelling) return;

      this.cancelling = true;
      Swal.alertGetInfo();
      try {
        await this.$axios.post(`${this.$routes["purchasesCancel"]}/${this.cancelTarget.id}`, {
          reason: this.cancelReason.trim(),
        });
        this.showSnackbar("Compra anulada y stock actualizado", "success");
        this.cancelDialog = false;
        this.cancelTarget = null;
        await this.loadPurchases();
      } catch (error) {
        this.showSnackbar(this.errorMessage(error, "No se pudo anular la compra"), "error");
      } finally {
        this.cancelling = false;
        Swal.close();
      }
    },

    // ---- Utilidades
    isOverdue(item) {
      if (item.status !== "received" || item.payment_status === "paid" || !item.due_date) {
        return false;
      }
      return String(item.due_date).substring(0, 10) < toYmd(new Date());
    },

    paymentStatusLabel(status) {
      return { pending: "Pendiente", partial: "Pago parcial", paid: "Pagada" }[status] || status;
    },

    paymentStatusColor(status) {
      return { pending: "error", partial: "warning", paid: "success" }[status] || "default";
    },

    // Acepta "d/m/Y H:i:s" (cast del modelo) o "Y-m-d"
    formatDate(value) {
      if (!value) return "";
      if (/^\d{4}-\d{2}-\d{2}/.test(value)) {
        const [y, m, d] = String(value).substring(0, 10).split("-");
        return `${d}/${m}/${y}`;
      }
      return String(value).substring(0, 10);
    },

    formatCurrency(value) {
      return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(Number(value) || 0);
    },

    errorMessage(error, fallback) {
      return error?.response?.data?.message || fallback;
    },

    showSnackbar(text, color) {
      this.snackbarText = text;
      this.snackbarColor = color;
      this.snackbar = true;
    },
  },
};
</script>
