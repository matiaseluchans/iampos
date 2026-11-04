<template>
  <VCard :title="'Administración de ' + title">
    <VCardText class="d-flex px-2">
      <VDataTable
        :headers="showHeaders"
        :items="filteredDesserts"
        :search="search"
        class="text-no-wrap striped-table"
      >
        <template v-slot:top>
          <VCard flat color="white">
            <VCardText>
              <VRow>
                <VCol sm="4" class="pl-0 pt-20 py-2">
                  <VTextField
                    v-model="search"
                    append-icon="ri-search-line"
                    :label="'Busqueda de ' + title"
                  ></VTextField>
                </VCol>
                <VCol sm="3"></VCol>
                <VCol sm="4" class="pt-20 py-2">
                  <VAutocomplete
                    v-model="selectedHeaders"
                    :items="headers"
                    label="Columnas Visibles"
                    multiple
                    return-object
                  >
                    <template v-slot:selection="{ item, index }">
                      <VChip v-if="index < 2">
                        <span>{{ item.title }}</span>
                      </VChip>
                      <span v-if="index === 2" class="grey--text caption"
                        >(otras {{ selectedHeaders.length - 2 }}+)</span
                      >
                    </template>
                  </VAutocomplete>
                </VCol>
                <VCol sm="1" class="pt-20 py-2">
                  <v-dialog v-model="dialog" max-width="760px">
                    <template v-slot:activator="{ props: activatorProps }">
                      <VBtn
                        v-bind="activatorProps"
                        :color="$cv('principal')"
                        size="x-large"
                        :title="'Registrar proveedor'"
                      >
                        <VIcon size="large" icon="ri-add-circle-line" />
                      </VBtn>
                    </template>
                    <VCard>
                      <v-toolbar :color="$cv('principal')">
                        <v-btn
                          icon="ri-close-line"
                          color="white"
                          @click="dialog = false"
                        ></v-btn>

                        <v-toolbar-title>{{ formTitle }}</v-toolbar-title>
                      </v-toolbar>

                      <v-form ref="form" v-model="valid" lazy-validation>
                        <VCardText>
                          <v-container>
                            <VRow>
                              <VCol cols="12" sm="6">
                                <VTextField
                                  v-model="editedItem.name"
                                  label="Nombre *"
                                  :rules="[(v) => !!(v && v.trim()) || 'El nombre es requerido']"
                                />
                              </VCol>
                              <VCol cols="12" sm="6">
                                <VTextField
                                  v-model="editedItem.business_name"
                                  label="Razón social"
                                />
                              </VCol>
                              <VCol cols="12" sm="4">
                                <VTextField
                                  v-model="editedItem.tax_id"
                                  label="CUIT"
                                  placeholder="30-12345678-9"
                                />
                              </VCol>
                              <VCol cols="12" sm="4">
                                <VTextField
                                  v-model="editedItem.contact_name"
                                  label="Contacto"
                                />
                              </VCol>
                              <VCol cols="12" sm="4">
                                <VTextField
                                  v-model="editedItem.telephone"
                                  label="Teléfono"
                                />
                              </VCol>
                              <VCol cols="12" sm="6">
                                <VTextField
                                  v-model="editedItem.email"
                                  label="Email"
                                  type="email"
                                />
                              </VCol>
                              <VCol cols="12" sm="6">
                                <VTextField
                                  v-model.number="editedItem.payment_term_days"
                                  label="Plazo de pago (días)"
                                  type="number"
                                  min="0"
                                  hint="Se usa para sugerir el vencimiento de cada compra"
                                  persistent-hint
                                />
                              </VCol>
                              <VCol cols="12" sm="8">
                                <VTextField v-model="editedItem.address" label="Dirección" />
                              </VCol>
                              <VCol cols="12" sm="4">
                                <VAutocomplete
                                  v-model="editedItem.locality_id"
                                  :items="localities"
                                  item-title="name"
                                  item-value="id"
                                  label="Localidad"
                                  clearable
                                />
                              </VCol>
                              <VCol cols="12">
                                <VTextarea
                                  v-model="editedItem.notes"
                                  label="Notas"
                                  rows="2"
                                />
                              </VCol>
                            </VRow>
                          </v-container>
                        </VCardText>
                      </v-form>
                      <VCardActions>
                        <VSpacer />
                        <VBtn variant="outlined" color="primary" @click="dialog = false"
                          >Cancelar</VBtn
                        >
                        <VBtn class="bg-primary" color="white" @click="$save()"
                          >Guardar</VBtn
                        >
                      </VCardActions>
                    </VCard>
                  </v-dialog>
                </VCol>
              </VRow>
            </VCardText>
          </VCard>
        </template>

        <!-- plazo -->
        <template #item.payment_term_days="{ item }">
          {{ item.payment_term_days ? item.payment_term_days + " días" : "Contado" }}
        </template>

        <!-- status -->
        <template #item.active="{ item }">
          <VChip :color="$resolveStatusVariant(item.active).color" density="comfortable">
            {{ $resolveStatusVariant(item.active).text }}
          </VChip>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <div class="d-flex flex-wrap gap-1 align-center" style="min-width: 170px">
            <IconBtn
              size="small"
              title="Editar"
              @click="
                vista = false;
                $editItem(item.id);
              "
            >
              <VIcon icon="ri-pencil-line" />
            </IconBtn>
            <IconBtn size="small" title="Cuenta corriente" @click="openStatement(item)">
              <VIcon icon="ri-bill-line" />
            </IconBtn>
            <VSwitch
              v-model="item.active"
              :true-value="1"
              :false-value="0"
              color="primary"
              hide-details
              title="Activar o Inactivar"
              @click="$toggleActive(item)"
            />

            <IconBtn
              size="small"
              title="Eliminar"
              @click="
                vista = false;
                $deleteItem(item.id, item.name);
              "
            >
              <VIcon icon="ri-delete-bin-line" />
            </IconBtn>
          </div>
        </template>
      </VDataTable>

      <!-- Cuenta corriente -->
      <VDialog v-model="statementDialog" max-width="900px">
        <VCard>
          <VToolbar :color="$cv('principal')">
            <VBtn icon="ri-close-line" color="white" @click="statementDialog = false" />
            <VToolbarTitle>Cuenta corriente - {{ statement?.supplier?.name }}</VToolbarTitle>
          </VToolbar>
          <VCardText class="pt-4">
            <VRow v-if="statement">
              <VCol cols="12" sm="4">
                <VCard flat border>
                  <VCardText class="text-center">
                    <div class="text-caption text-medium-emphasis">Total comprado</div>
                    <div class="text-h6">{{ formatCurrency(statement.total_purchased) }}</div>
                  </VCardText>
                </VCard>
              </VCol>
              <VCol cols="12" sm="4">
                <VCard flat border>
                  <VCardText class="text-center">
                    <div class="text-caption text-medium-emphasis">Total pagado</div>
                    <div class="text-h6 text-success">{{ formatCurrency(statement.total_paid) }}</div>
                  </VCardText>
                </VCard>
              </VCol>
              <VCol cols="12" sm="4">
                <VCard flat border>
                  <VCardText class="text-center">
                    <div class="text-caption text-medium-emphasis">Saldo pendiente</div>
                    <div
                      class="text-h6"
                      :class="statement.balance > 0 ? 'text-error' : 'text-success'"
                    >
                      {{ formatCurrency(statement.balance) }}
                    </div>
                  </VCardText>
                </VCard>
              </VCol>
            </VRow>

            <VDataTable
              v-if="statement"
              :headers="statementHeaders"
              :items="statement.purchases"
              density="compact"
              class="mt-4 elevation-1"
              no-data-text="El proveedor no tiene compras registradas"
              :items-per-page="25"
            >
              <template #item.purchase_date="{ item }">
                {{ formatDate(item.purchase_date) }}
              </template>
              <template #item.due_date="{ item }">
                <span :class="item.overdue ? 'text-error font-weight-bold' : ''">
                  {{ item.due_date ? formatDate(item.due_date) : "-" }}
                </span>
                <VChip v-if="item.overdue" color="error" size="x-small" class="ml-1"
                  >Vencida</VChip
                >
              </template>
              <template #item.total_amount="{ item }">
                {{ formatCurrency(item.total_amount) }}
              </template>
              <template #item.total_paid="{ item }">
                {{ formatCurrency(item.total_paid) }}
              </template>
              <template #item.balance="{ item }">
                {{ formatCurrency(item.total_amount - item.total_paid) }}
              </template>
              <template #item.payment_status="{ item }">
                <VChip :color="paymentStatusColor(item.payment_status)" size="small">
                  {{ paymentStatusLabel(item.payment_status) }}
                </VChip>
              </template>
            </VDataTable>
          </VCardText>
        </VCard>
      </VDialog>

      <v-snackbar v-model="snackbar" :bottom="true" :color="color" :timeout="timeout">
        <div v-html="text"></div>

        <template v-slot:action="{ attrs }">
          <v-btn dark text v-bind="attrs" @click="snackbar = false"> Cerrar </v-btn>
        </template>
      </v-snackbar>
    </VCardText>
  </VCard>
</template>

<script>
function title() {
  return "Proveedores";
}

export default {
  data: (vm) => ({
    dessertName: "",
    valid: true,
    title: title(),
    route: "suppliers",
    dialog: false,
    snackbar: false,
    visible: true,
    text: "Registro Insertado",
    color: "success",
    timeout: 4000,
    search: "",
    vista: false,
    headers: [
      {
        title: "Acciones",
        key: "actions",
        value: "actions",
        sortable: false,
        width: "190px",
      },
      { title: "Nombre", filterable: true, key: "name" },
      { title: "Razón social", filterable: true, key: "business_name" },
      { title: "CUIT", filterable: true, key: "tax_id" },
      { title: "Contacto", filterable: true, key: "contact_name" },
      { title: "Teléfono", key: "telephone" },
      { title: "Plazo de pago", key: "payment_term_days" },
      { title: "Estado", key: "active", width: "150px" },
    ],
    desserts: [],
    editedIndex: -1,
    editedItem: {
      id: "",
      name: "",
      business_name: "",
      tax_id: "",
      contact_name: "",
      email: "",
      telephone: "",
      address: "",
      locality_id: null,
      payment_term_days: 0,
      notes: "",
      active: 1,
    },
    defaultItem: {
      id: "",
      name: "",
      business_name: "",
      tax_id: "",
      contact_name: "",
      email: "",
      telephone: "",
      address: "",
      locality_id: null,
      payment_term_days: 0,
      notes: "",
      active: 1,
    },
    filters: {
      id: "",
      name: "",
      created_at: "",
      updated_at: "",
    },
    filterKey: [],
    selectedHeaders: [],
    localities: [],

    // Cuenta corriente
    statementDialog: false,
    statement: null,
    statementHeaders: [
      { title: "N° Compra", key: "purchase_number" },
      { title: "Fecha", key: "purchase_date" },
      { title: "Comprobante", key: "supplier_document" },
      { title: "Vencimiento", key: "due_date" },
      { title: "Total", key: "total_amount", align: "end" },
      { title: "Pagado", key: "total_paid", align: "end" },
      { title: "Saldo", key: "balance", align: "end", sortable: false },
      { title: "Estado", key: "payment_status" },
    ],
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1
        ? "Registrar proveedor"
        : "Editar proveedor";
    },

    showHeaders() {
      return this.headers.filter((s) => this.selectedHeaders.includes(s));
    },
    filteredDesserts() {
      return this.desserts;
    },
  },

  watch: {
    dialog(val) {
      val || this.$close();
    },
  },

  // eslint-disable-next-line vue/component-api-style
  created() {
    this.$initialize();
    this.selectedHeaders = this.headers;
    this.loadLocalities();
  },

  methods: {
    async loadLocalities() {
      try {
        const res = await this.$axios.get(this.$routes["localities"]);
        this.localities = res.data.data || res.data;
      } catch (error) {
        console.error("Error cargando localidades:", error);
      }
    },

    async openStatement(item) {
      Swal.alertGetInfo("Obteniendo cuenta corriente");
      try {
        const res = await this.$axios.get(
          `${this.$routes["suppliers"]}/${item.id}/statement`
        );
        this.statement = res.data.data;
        this.statementDialog = true;
      } catch (error) {
        this.snackbar = true;
        this.color = "error";
        this.text = "No se pudo obtener la cuenta corriente";
      } finally {
        Swal.close();
      }
    },

    formatCurrency(value) {
      return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(value || 0);
    },

    // Acepta "d/m/Y H:i:s" (cast del modelo) o "Y-m-d"
    formatDate(value) {
      if (!value) return "";
      if (/^\d{4}-\d{2}-\d{2}/.test(value)) {
        const [y, m, d] = value.substring(0, 10).split("-");
        return `${d}/${m}/${y}`;
      }
      return String(value).substring(0, 10);
    },

    paymentStatusLabel(status) {
      return { pending: "Pendiente", partial: "Pago parcial", paid: "Pagada" }[status] || status;
    },

    paymentStatusColor(status) {
      return { pending: "error", partial: "warning", paid: "success" }[status] || "default";
    },
  },
};
</script>
