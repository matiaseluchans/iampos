<template>
  <VCard>
    <VCardTitle class="d-flex flex-wrap align-center justify-space-between ga-2">
      <div class="d-flex flex-column" style="min-width: 250px; flex-grow: 1">
        <h2 class="text-h5 mb-1">Resumen de Pagos</h2>
        <div class="text-caption text-medium-emphasis d-flex align-center">
          <VIcon icon="ri-information-line" size="16" class="mr-1" />
          <span style="white-space: normal">
            Ingresos de ventas y egresos de compras por día y método de pago
          </span>
        </div>
      </div>
    </VCardTitle>
    <VCardText class="d-flex px-2">
      <VDataTable
        :headers="headers"
        :items="paymentsSummary.data || []"
        class="text-no-wrap striped-table border-0"
        :loading="loading"
        :items-per-page="pagination.itemsPerPage"
        :page="pagination.page"
        :items-length="paymentsSummary.total || 0"
        @update:options="handlePaginationChange"
      >
        <template v-slot:top>
          <VCard flat color="white">
            <VCardText class="mx-0 px-0">
              <VRow dense class="mx-0 px-0">
                <VCol cols="12" md="6" sm="12" class="pl-0 pt-0">
                  <DateRangeField
                    class="mt-0"
                    ref="dateRangeField"
                    v-model="dateRange"
                    modelLabel="Fecha de Pago"
                  />
                </VCol>
                <VCol cols="12" md="3" sm="12" class="pl-0 pt-1">
                  <VSelect
                    v-model="origin"
                    :items="originOptions"
                    item-title="title"
                    item-value="value"
                    label="Origen"
                    class="mt-0"
                    density="compact"
                  />
                </VCol>
                <VCol cols="12" md="3" sm="12" class="pl-0 pt-1">
                  <VAutocomplete
                    v-model="selectedPaymentMethod"
                    :items="paymentMethods"
                    item-title="name"
                    item-value="id"
                    label="Método de Pago"
                    clearable
                    class="mt-0"
                    density="compact"
                    multiple
                    chips
                  />
                </VCol>
              </VRow>
            </VCardText>
            <VCardActions class="justify-end">
              <VBtn variant="outlined" color="primary" @click="resetFilters">
                Reset
              </VBtn>
              <VBtn
                variant="outlined"
                color="white"
                @click="fetchData"
                :loading="loading"
                class="bg-primary"
              >
                Buscar
              </VBtn>
              
              <VBtn
                  color="success"
                  variant="outlined"
                  @click="exportToExcel"
                  :loading="exportLoading"
                  :disabled="!paymentsSummary.data || paymentsSummary.data.length === 0"
                   
                >
                  <VIcon icon="ri-file-excel-2-line" class="mr-2" />
                  Exportar Excel
                </VBtn>
            </VCardActions>
          </VCard>
        </template>

        <template #item.date="{ item }">
          <div class="d-flex flex-column">
            <span class="font-weight-medium">
              {{ formatDateGrid(item.date) }}
            </span>
          </div>
        </template>

        <template #item.payment_method="{ item }">
          <div class="d-flex align-center">
            <VAvatar
              :color="getPaymentMethodColor(item.payment_method)"
              variant="tonal"
              size="32"
              class="mr-2"
            >
              <VIcon :icon="getPaymentMethodIcon(item.payment_method)" />
            </VAvatar>
            <span>{{ item.payment_method_name }}</span>
          </div>
        </template>

        <template #item.income="{ item }">
          <div class="text-right" :class="getColorAmount(item.income)">
            {{ formatCurrency(item.income) }}
          </div>
        </template>

        <template #item.expense="{ item }">
          <div class="text-right" :class="getColorAmount(item.expense)">
            {{ formatCurrency(item.expense) }}
          </div>
        </template>

        <template #item.net="{ item }">
          <div class="text-right" :class="getColorAmount(item.net)">
            <strong>{{ formatCurrency(item.net) }}</strong>
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <div class="d-flex justify-center align-center">
            <IconBtn size="small" @click="showPaymentDetails(item)" title="Ver detalle">
              <VIcon icon="ri-eye-line" />
            </IconBtn>
          </div>
        </template>

        <template #tfoot>
          <tfoot>
            <tr class="bg-grey-lighten-4 font-weight-bold" style="background: #ccc">
              <td class="text-left"></td>
              <td class="text-left">Total General</td>
              <td class="text-right">{{ formatCurrency(totals.income) }}</td>
              <td class="text-right">{{ formatCurrency(totals.expense) }}</td>
              <td class="text-right">{{ formatCurrency(totals.net) }}</td>
              <td class="text-right"></td>
            </tr>
          </tfoot>
        </template>
      </VDataTable>

      <!-- Modal de Detalle de Pagos -->
      <VDialog v-model="detailDialog" max-width="800px" persistent>
        <VCard class="rounded-lg">
          <VToolbar color="primary" density="compact">
            <VToolbarTitle class="text-white font-weight-medium">
              <VBtn icon variant="text" color="white" @click="closeDetailDialog">
                <VIcon>ri-close-line</VIcon>
              </VBtn>
              Detalle de Pagos - {{ selectedPayment.date }} -
              {{ selectedPayment.payment_method_name }}
            </VToolbarTitle>
          </VToolbar>

          <VCardText class="px-4 pt-4 pb-2">
            <!-- Resumen del grupo -->
            <VCard flat border class="mb-4" color="primary-lighten-5">
              <VCardItem class="py-0 mt-2">
                <VCardTitle class="d-flex align-center">
                  <VAvatar
                    icon="ri-money-dollar-circle-line"
                    class="text-info mr-2"
                    variant="tonal"
                  />
                  <span>Resumen del Grupo</span>
                </VCardTitle>
              </VCardItem>
              <VDivider class="my-1" />
              <VCardText>
                <VRow dense>
                  <VCol cols="6" sm="4">
                    <div class="d-flex flex-column">
                      <span class="text-caption text-medium-emphasis">
                        <VIcon icon="ri-calendar-line" size="14" class="mr-1" />
                        Fecha
                      </span>
                      <span class="font-weight-medium">{{
                        formatDateGrid(selectedPayment.date)
                      }}</span>
                    </div>
                  </VCol>
                  <VCol cols="6" sm="4">
                    <div class="d-flex flex-column">
                      <span class="text-caption text-medium-emphasis">
                        <VIcon icon="ri-bank-card-line" size="14" class="mr-1" />
                        Método de Pago
                      </span>
                      <span class="font-weight-medium">{{
                        selectedPayment.payment_method_name
                      }}</span>
                    </div>
                  </VCol>
                  <VCol cols="6" sm="4">
                    <div class="d-flex flex-column">
                      <span class="text-caption text-medium-emphasis">
                        <VIcon icon="ri-arrow-down-circle-line" size="14" class="mr-1" />
                        Ingresos
                      </span>
                      <span class="font-weight-medium" :class="getColorAmount(selectedPayment.income)">
                        {{ formatCurrency(selectedPayment.income) }}
                      </span>
                    </div>
                  </VCol>
                  <VCol cols="6" sm="4">
                    <div class="d-flex flex-column">
                      <span class="text-caption text-medium-emphasis">
                        <VIcon icon="ri-arrow-up-circle-line" size="14" class="mr-1" />
                        Egresos
                      </span>
                      <span class="font-weight-medium" :class="getColorAmount(selectedPayment.expense)">
                        {{ formatCurrency(selectedPayment.expense) }}
                      </span>
                    </div>
                  </VCol>
                  <VCol cols="6" sm="4">
                    <div class="d-flex flex-column">
                      <span class="text-caption text-medium-emphasis">
                        <VIcon
                          icon="ri-money-dollar-circle-line"
                          size="14"
                          class="mr-1"
                        />
                        Neto
                      </span>
                      <span class="font-weight-medium" :class="getColorAmount(selectedPayment.net)">
                        {{ formatCurrency(selectedPayment.net) }}
                      </span>
                    </div>
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>

            <!-- Tabla de detalles -->
            <VDataTable
              :headers="detailHeaders"
              :items="paymentDetails"
              class="text-no-wrap striped-table border"
              :loading="detailLoading"
              items-per-page="5"
              :page="pagination.page"
            >
              <template #item.payment_date="{ item }">
                <div class="d-flex flex-column">
                  <span class="font-weight-medium">
                    {{ formatDateTime(item.payment_date) }}
                  </span>
                </div>
              </template>

              <template #item.origin="{ item }">
                <VChip
                  size="small"
                  :color="item.origin === 'purchase' ? 'error' : 'success'"
                  variant="tonal"
                >
                  {{ item.origin === "purchase" ? "Compra" : "Venta" }}
                </VChip>
              </template>

              <template #item.reference_number="{ item }">
                <span class="font-weight-medium">#{{ item.reference_number }}</span>
              </template>

              <template #item.counterparty="{ item }">
                <div class="d-flex flex-column text-start">
                  <span
                    class="d-block font-weight-medium text-high-emphasis text-truncate"
                  >
                    {{ item.counterparty_name }}
                  </span>
                  <small class="text-disabled">{{ item.counterparty_contact }}</small>
                </div>
              </template>

              <template #item.amount="{ item }">
                <div class="text-right" :class="getColorAmount(item.amount)">
                  <strong>{{ formatCurrency(item.amount) }}</strong>
                </div>
              </template>
            </VDataTable>
          </VCardText>

          <VCardActions>
            <VSpacer />
            <VBtn variant="outlined" color="primary" @click="closeDetailDialog">
              Cerrar
            </VBtn>
          </VCardActions>
        </VCard>
      </VDialog>

      <v-snackbar v-model="snackbar" :color="snackbarColor" :timeout="3000">
        {{ snackbarText }}
        <template v-slot:action="{ attrs }">
          <VBtn text v-bind="attrs" @click="snackbar = false"> Cerrar </VBtn>
        </template>
      </v-snackbar>
    </VCardText>
  </VCard>
</template>

<script>
import { mapGetters } from "vuex";
import DateRangeField from "@/components/DateRangeField.vue";

export default {
  components: { DateRangeField },
  data() {
    return {
      loading: false,
      detailLoading: false,

      // Headers para la tabla principal
      headers: [
        { title: "Día", key: "date", align: "left", width: "120px" },
        { title: "Método de Pago", key: "payment_method", align: "left" },
        { title: "Ingresos", key: "income", align: "end" },
        { title: "Egresos", key: "expense", align: "end" },
        { title: "Neto", key: "net", align: "end" },
        {
          title: "Acciones",
          key: "actions",
          sortable: false,
          align: "center",
          width: "80px",
        },
      ],

      // Headers para el modal de detalle
      detailHeaders: [
        { title: "Fecha Pago", key: "payment_date", align: "left", width: "150px" },
        { title: "Origen", key: "origin", align: "left", width: "100px" },
        { title: "Referencia", key: "reference_number", align: "left", width: "120px" },
        { title: "Cliente / Proveedor", key: "counterparty", align: "left" },
        { title: "Monto", key: "amount", align: "end" },
      ],

      // Datos
      paymentsSummary: {
        data: [],
        total: 0,
        current_page: 1,
        last_page: 1,
      },

      paymentDetails: [],
      selectedPayment: {},
      paymentMethods: [],
      selectedPaymentMethod: null,
      origin: "all",
      appliedOrigin: "all", // origen con el que se calculó el resumen que se está viendo
      originOptions: [
        { title: "Todos", value: "all" },
        { title: "Ventas", value: "sales" },
        { title: "Compras", value: "purchases" },
      ],

      // Dialogs
      detailDialog: false,

      // Pagination
      pagination: {
        page: 1,
        itemsPerPage: 10,
        sortBy: [],
        sortDesc: [],
      },

      // Filtros
      dateRange: {
        start: null,
        end: null,
      },
      defaultDateRange: {},

      // Snackbar
      snackbar: false,
      snackbarText: "",
      snackbarColor: "success",

      isInitialLoad: true,
      exportLoading:false
      
    };
  },

  computed: {
    ...mapGetters({
      userIsAdmin: "isAdmin",
    }),
    totals() {
      const sum = (key) =>
        (this.paymentsSummary.data || []).reduce(
          (total, item) => total + (parseFloat(item[key]) || 0),
          0
        );
      return { income: sum("income"), expense: sum("expense"), net: sum("net") };
    },
  },

  async created() {
    // Establecer rango por defecto (últimos 30 días)
    this.setDefaultDateRange();
    await this.loadPaymentMethods();
    await this.fetchData();
  },

  methods: {

    async exportToExcel() {
      this.exportLoading = true;
      try {
        const params = {
          start_date: this.dateRange?.start,
          end_date: this.dateRange?.end,
          payment_methods: this.selectedPaymentMethod,
          origin: this.origin,
        };

        // Limpiar parámetros undefined
        Object.keys(params).forEach(
          (key) => params[key] === undefined && delete params[key]
        );

        // Hacer la petición para descargar el Excel
        const response = await this.$axios.get('/api/payments-summary/export-excel', {
          params,
          responseType: 'blob' // Importante para descargar archivos
        });

        // Crear el blob y descargar
        const blob = new Blob([response.data], {
          type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        });

        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        
        // Obtener el nombre del archivo del header o generar uno
        const contentDisposition = response.headers['content-disposition'];
        let fileName = 'resumen_pagos.xlsx';
        
        if (contentDisposition) {
          console.log('📁 Content-Disposition header:', contentDisposition);
          
          let fileName = 'resumen_pagos.xlsx';
          
          // Diferentes patrones para capturar el filename
          const patterns = [
            /filename="([^"]+)"/,      // filename="archivo.xlsx"
            /filename=([^;]+)/,         // filename=archivo.xlsx
            /filename\*=UTF-8''([^;]+)/ // filename*=UTF-8''archivo.xlsx (codificado)
          ];
          
          for (const pattern of patterns) {
            const match = contentDisposition.match(pattern);
            if (match && match[1]) {
              fileName = match[1];
              console.log('✅ Filename capturado con patrón:', pattern, '-', fileName);
              break;
            }
          }
          
          // Si no se capturó con los patrones, intentar extraer manualmente
          if (fileName === 'resumen_pagos.xlsx') {
            const parts = contentDisposition.split(';');
            for (const part of parts) {
              if (part.trim().startsWith('filename')) {
                const filenamePart = part.split('=')[1];
                if (filenamePart) {
                  fileName = filenamePart.trim().replace(/"/g, '');
                  console.log('✅ Filename capturado manualmente:', fileName);
                  break;
                }
              }
            }
          }
          
          // Decodificar si está en formato URL encoded
          try {
            if (fileName.includes('%')) {
              fileName = decodeURIComponent(fileName);
              console.log('✅ Filename decodificado:', fileName);
            }
          } catch (e) {
            console.warn('⚠️ No se pudo decodificar el filename:', fileName);
          }
          
          console.log('📄 Filename final:', fileName);
       
        
          link.setAttribute('download', fileName);
          document.body.appendChild(link);
          link.click();
          link.remove();
          window.URL.revokeObjectURL(url);
        }

        this.showSnackbar("Excel exportado exitosamente", "success");

      } catch (error) {
        console.error("Error exporting to Excel:", error);
        this.showSnackbar("Error al exportar el Excel", "error");
      } finally {
        this.exportLoading = false;
      }
    },
    setDefaultDateRange() {
      const endDate = new Date();
      const startDate = new Date();
      startDate.setDate(startDate.getDate() - 30);

      this.defaultDateRange = {
        start: startDate,
        end: endDate,
      };

      // Solo establecer en la carga inicial
      if (this.isInitialLoad) {
        this.dateRange = { ...this.defaultDateRange };
      }
    },

    async loadPaymentMethods() {
      try {
        const response = await this.$axios.get(this.$routes["paymentMethods"]);
        this.paymentMethods = response.data.data || [];
      } catch (error) {
        console.error("Error loading payment methods:", error);
        this.paymentMethods = [];
      }
    },

    async fetchData() {
      this.loading = true;
      try {
        const params = {
          page: this.pagination.page,
          per_page: this.pagination.itemsPerPage,
          start_date: this.dateRange?.start,
          end_date: this.dateRange?.end,
          payment_methods: this.selectedPaymentMethod,
          origin: this.origin,
        };

        // Eliminar parámetros undefined
        Object.keys(params).forEach(
          (key) => params[key] === undefined && delete params[key]
        );

        const response = await this.$axios.get(this.$routes["paymentsSummary"], {
          params,
        });

        this.appliedOrigin = params.origin || "all";
        this.paymentsSummary = {
          data: response.data.data,
          total: response.data.total,
          current_page: response.data.current_page,
          last_page: response.data.last_page,
        };

        // Marcar que ya no es la carga inicial
        this.isInitialLoad = false;
      } catch (error) {
        console.error("Error fetching payments summary:", error);
        this.showSnackbar("Error al cargar el resumen de pagos", "error");
      } finally {
        this.loading = false;
      }
    },

    resetFilters() {
      this.dateRange = {
        start: null,
        end: null,
      };
      this.selectedPaymentMethod = null;
      this.origin = "all";
      this.pagination.page = 1;

      // Resetear el componente DateRangeField
      this.$nextTick(() => {
        if (this.$refs.dateRangeField && this.$refs.dateRangeField.reset) {
          this.$refs.dateRangeField.reset();
        }
      });

      this.fetchData();
    },

    async showPaymentDetails(item) {
      this.selectedPayment = item;
      this.detailLoading = true;
      this.detailDialog = true;

      try {
        const params = {
          date: item.date,
          payment_method_id: item.payment_method_id,
          origin: this.appliedOrigin,
        };

        const response = await this.$axios.get(this.$routes["paymentsDetail"], {
          params,
        });
        this.paymentDetails = response.data.data;
      } catch (error) {
        console.error("Error fetching payment details:", error);
        this.showSnackbar("Error al cargar el detalle de pagos", "error");
      } finally {
        this.detailLoading = false;
      }
    },

    closeDetailDialog() {
      this.detailDialog = false;
      this.selectedPayment = {};
      this.paymentDetails = [];
    },

    handlePaginationChange(options) {
      this.pagination.page = options.page;
      this.pagination.itemsPerPage = options.itemsPerPage;
      this.pagination.sortBy = options.sortBy;
      this.pagination.sortDesc = options.sortDesc;
      this.fetchData();
    },

    // Utility Methods
    formatCurrency(value) {
      if (!value) return "$0";
      return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(value);
    },

    formatDateGrid(dateStr) {
      if (!dateStr) return "";
      const [year, month, day] = dateStr.split("-");
      return `${day}/${month}/${year}`;
    },

    formatDateTime(dateTimeStr) {
      if (!dateTimeStr) return "";
      const date = new Date(dateTimeStr);
      return date.toLocaleString("es-AR", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        hour12: false,
      });
    },

    getColorAmount(amount){

 
      const value = parseFloat(amount) || 0;
      if (value > 0) return "text-success";
      if (value < 0) return "text-error";
      return "";
    },

    getPaymentMethodColor(method) {
      const colorMap = {
        cash: "success",
        card: "primary",
        transfer: "info",
        digital: "warning",
        mercadopago: "primary",
        debit: "info",
        credit: "warning",
      };
      return colorMap[method] || "secondary";
    },

    getPaymentMethodIcon(method) {
      const iconMap = {
        cash: "ri-money-dollar-circle-line",
        card: "ri-bank-card-line",
        transfer: "ri-exchange-funds-line",
        digital: "ri-smartphone-line",
        mercadopago: "ri-smartphone-line",
        debit: "ri-bank-card-line",
        credit: "ri-bank-card-line",
      };
      return iconMap[method] || "ri-money-dollar-circle-line";
    },

    showSnackbar(text, color) {
      this.snackbarText = text;
      this.snackbarColor = color;
      this.snackbar = true;
    },
  },
};
</script>

<style scoped>
.text-success {
  color: #4caf50;
}

.text-disabled {
  color: #9e9e9e;
}

.v-card-title {
  padding-bottom: 12px;
}
</style>
