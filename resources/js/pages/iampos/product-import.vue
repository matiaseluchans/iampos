<template>
  <VCard title="Importación y Actualización de Precios via Excel">
    <VCardText>
      <!-- ── Upload + Download Section ── -->
      <VRow>
        <VCol cols="12" md="8">
          <p class="text-body-1">
            Sube un archivo Excel con el mismo formato generado por la exportación para actualizar los precios de compra y venta.
          </p>
        </VCol>
        <VCol cols="12" md="4" class="d-flex justify-end">
          <VBtn
            color="success"
            prepend-icon="ri-file-excel-2-line"
            @click="downloadTemplate"
            :loading="exportLoading"
          >
            Descargar Formato
          </VBtn>
        </VCol>
      </VRow>

      <VDivider class="my-4" />

      <VRow align="center">
        <VCol cols="12" md="6">
          <VFileInput
            v-model="excelFile"
            label="Seleccionar archivo Excel"
            accept=".xlsx, .xls, .csv"
            prepend-icon="ri-upload-2-line"
            :loading="previewLoading"
            @change="generatePreview"
            clearable
          />
        </VCol>
        <VCol cols="12" md="6" v-if="previewData.length > 0">
          <VBtn
            color="primary"
            block
            size="large"
            @click="showConfirmDialog = true"
            :loading="confirmLoading"
            prepend-icon="ri-check-double-line"
          >
            Confirmar Actualización ({{ previewData.length }} productos)
          </VBtn>
        </VCol>
      </VRow>

      <!-- ── Summary Stats Cards ── -->
      <VRow v-if="previewStats" class="mt-4">
        <VCol cols="6" sm="3">
          <VCard variant="tonal" color="primary" class="text-center pa-3">
            <div class="text-h5 font-weight-bold">{{ previewStats.total_changes }}</div>
            <div class="text-caption text-medium-emphasis">Total Cambios</div>
          </VCard>
        </VCol>
        <VCol cols="6" sm="3">
          <VCard variant="tonal" color="info" class="text-center pa-3">
            <div class="text-h5 font-weight-bold">{{ previewStats.purchase_price_changes }}</div>
            <div class="text-caption text-medium-emphasis">Precio Compra</div>
          </VCard>
        </VCol>
        <VCol cols="6" sm="3">
          <VCard variant="tonal" color="success" class="text-center pa-3">
            <div class="text-h5 font-weight-bold">{{ previewStats.price_list_changes }}</div>
            <div class="text-caption text-medium-emphasis">Listas de Precio</div>
          </VCard>
        </VCol>
        <VCol cols="6" sm="3">
          <VCard variant="tonal" color="warning" class="text-center pa-3">
            <div class="text-h5 font-weight-bold">{{ previewStats.stock_changes }}</div>
            <div class="text-caption text-medium-emphasis">Stock</div>
          </VCard>
        </VCol>
      </VRow>

      <VDivider class="my-6" v-if="previewData.length > 0" />

      <!-- ── Preview Table with Pagination ── -->
      <div v-if="previewData.length > 0">
        <h3 class="text-h6 mb-4">Vista Previa de Cambios</h3>
        <VDataTable
          :headers="previewHeaders"
          :items="previewData"
          :items-per-page="50"
          :items-per-page-options="[25, 50, 100, { value: -1, title: 'Todos' }]"
          class="text-no-wrap striped-table pb-5"
          density="comfortable"
        >
          <template #item.purchase_price="{ item }">
            <div v-if="item.new_purchase_price != item.old_purchase_price">
              <span class="text-grey">{{ formatCurrency(item.old_purchase_price) }}</span>
              <VIcon icon="ri-arrow-right-line" size="small" color="grey" class="mx-1" />
              <VChip color="info" size="x-small" label class="font-weight-bold">
                {{ formatCurrency(item.new_purchase_price) }}
              </VChip>
            </div>
            <span v-else class="text-grey">{{ formatCurrency(item.old_purchase_price) }}</span>
          </template>

          <template #item.stock="{ item }">
            <div v-if="item.new_stock !== null && item.new_stock != item.old_stock">
              <span class="text-grey">{{ item.old_stock !== null ? item.old_stock : '-' }}</span>
              <VIcon icon="ri-arrow-right-line" size="small" color="grey" class="mx-1" />
              <VChip color="warning" size="x-small" label class="font-weight-bold">
                {{ item.new_stock }}
              </VChip>
            </div>
            <span v-else class="text-grey">{{ item.old_stock !== null ? item.old_stock : '-' }}</span>
          </template>

          <template #item.price_lists="{ item }">
            <div v-for="pl in item.price_lists" :key="pl.name" class="mb-1">
              <strong>{{ pl.name }}:</strong> 
              <span class="text-grey mx-1">{{ formatCurrency(pl.old_sale_price) }}</span>
              <VIcon icon="ri-arrow-right-line" size="small" color="grey" />
              <VChip color="success" size="x-small" label class="ml-1">
                {{ formatCurrency(pl.new_sale_price) }}
              </VChip>
            </div>
            <span v-if="item.price_lists.length === 0" class="text-grey italic">Sin cambios en listas</span>
          </template>
        </VDataTable>
      </div>

      <VDivider class="my-10" />

      <!-- ── Import History ── -->
      <div>
        <div class="d-flex justify-space-between align-center mb-4">
          <h3 class="text-h6">Historial de Importaciones</h3>
          <VBtn icon="ri-refresh-line" size="small" variant="text" @click="fetchHistory" :loading="historyLoading" />
        </div>
        
        <VDataTable
          :headers="historyHeaders"
          :items="historyData"
          class="text-no-wrap striped-table"
          density="comfortable"
        >
          <template #item.created_at="{ item }">
            {{ formatDate(item.created_at) }}
          </template>

          <template #item.status="{ item }">
            <VChip
              :color="getStatusColor(item.status)"
              size="small"
              label
              class="text-uppercase font-weight-bold"
            >
              <VIcon
                v-if="item.status === 'processing' || item.status === 'pending'"
                start
                size="14"
                icon="ri-loader-4-line"
                class="animate-spin"
              />
              {{ getStatusLabel(item.status) }}
            </VChip>
          </template>

          <template #item.progress="{ item }">
            <div class="d-flex align-center" style="min-width: 150px;">
              <VProgressLinear
                v-if="item.status === 'processing' || item.status === 'completed'"
                :model-value="item.total_rows > 0 ? (item.processed_rows / item.total_rows) * 100 : 0"
                :color="item.status === 'completed' ? 'success' : 'primary'"
                height="8"
                rounded
                class="flex-grow-1"
              />
              <span class="ml-2 text-caption font-weight-medium">
                {{ item.processed_rows }}/{{ item.total_rows }}
              </span>
            </div>
          </template>

          <template #item.error_message="{ item }">
            <VTooltip v-if="item.error_message" location="top">
              <template #activator="{ props }">
                <VIcon v-bind="props" color="error" icon="ri-error-warning-line" />
              </template>
              {{ item.error_message }}
            </VTooltip>
          </template>
        </VDataTable>
      </div>
    </VCardText>

    <!-- ── Confirmation Dialog ── -->
    <VDialog v-model="showConfirmDialog" max-width="520" persistent>
      <VCard>
        <VCardTitle class="text-h6 pa-5 pb-2">
          <VIcon icon="ri-error-warning-line" color="warning" class="mr-2" />
          Confirmar Actualización Masiva
        </VCardTitle>
        <VCardText class="px-5">
          <p class="mb-4">Estás a punto de actualizar <strong>{{ previewData.length }}</strong> productos. Este proceso no se puede deshacer.</p>

          <div v-if="previewStats" class="mb-4">
            <div v-if="previewStats.purchase_price_changes > 0" class="d-flex align-center mb-1">
              <VIcon icon="ri-price-tag-3-line" size="18" color="info" class="mr-2" />
              <span>{{ previewStats.purchase_price_changes }} precios de compra</span>
            </div>
            <div v-if="previewStats.price_list_changes > 0" class="d-flex align-center mb-1">
              <VIcon icon="ri-list-check-2" size="18" color="success" class="mr-2" />
              <span>{{ previewStats.price_list_changes }} listas de precio</span>
            </div>
            <div v-if="previewStats.stock_changes > 0" class="d-flex align-center mb-1">
              <VIcon icon="ri-stack-line" size="18" color="warning" class="mr-2" />
              <span>{{ previewStats.stock_changes }} stocks</span>
            </div>
          </div>

          <VAlert type="info" variant="tonal" density="compact" class="mt-2">
            El proceso puede tomar unos minutos dependiendo de la cantidad de registros.
          </VAlert>
        </VCardText>
        <VCardActions class="pa-5 pt-2">
          <VSpacer />
          <VBtn variant="text" @click="showConfirmDialog = false">Cancelar</VBtn>
          <VBtn color="primary" variant="elevated" @click="executeUpdate" :loading="confirmLoading">
            Sí, Actualizar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- ── Processing Overlay ── -->
    <VOverlay v-model="isProcessing" persistent contained class="align-center justify-center" scrim="rgba(0,0,0,0.7)">
      <VCard class="pa-8 text-center" min-width="320" elevation="24">
        <VProgressCircular indeterminate size="56" width="5" color="primary" />
        <p class="text-h6 mt-4 mb-1">Procesando Importación</p>
        <p class="text-body-2 text-medium-emphasis">
          Actualizando {{ previewData.length }} productos...
        </p>
        <p class="text-caption text-medium-emphasis mt-2">Por favor no cierres esta ventana</p>
      </VCard>
    </VOverlay>

    <v-snackbar v-model="snackbar" :color="snackbarColor" :timeout="4000">
      {{ snackbarText }}
      <template v-slot:actions>
        <v-btn text @click="snackbar = false">Cerrar</v-btn>
      </template>
    </v-snackbar>
  </VCard>
</template>

<script>
export default {
  name: 'ProductImport',
  data: () => ({
    excelFile: null,
    previewLoading: false,
    confirmLoading: false,
    exportLoading: false,
    historyLoading: false,
    isProcessing: false,
    showConfirmDialog: false,
    previewKey: null,
    previewData: [],
    previewStats: null,
    historyData: [],
    polling: null,
    snackbar: false,
    snackbarText: '',
    snackbarColor: 'success',
    previewHeaders: [
      { title: 'Código', key: 'code', sortable: true },
      { title: 'Producto', key: 'name', sortable: true },
      { title: 'Precio de Compra', key: 'purchase_price', sortable: false },
      { title: 'Stock', key: 'stock', sortable: false },
      { title: 'Listas de Precios (Cambios)', key: 'price_lists', sortable: false },
    ],
    historyHeaders: [
      { title: 'Fecha y Hora', key: 'created_at', sortable: true },
      { title: 'Archivo', key: 'file_name', sortable: true },
      { title: 'Tamaño', key: 'file_size', sortable: false },
      { title: 'Estado', key: 'status', sortable: true },
      { title: 'Progreso', key: 'progress', sortable: false },
      { title: '', key: 'error_message', sortable: false },
    ],
  }),

  mounted() {
    this.fetchHistory();
    this.startSmartPolling();
  },

  beforeUnmount() {
    this.stopPolling();
  },

  methods: {
    // ── Smart Polling: only poll when there are active imports ──
    startSmartPolling() {
      this.polling = setInterval(() => {
        const hasActive = this.historyData.some(
          h => h.status === 'pending' || h.status === 'processing'
        );
        if (hasActive) {
          this.fetchHistory();
        }
      }, 3000);
    },

    stopPolling() {
      if (this.polling) {
        clearInterval(this.polling);
        this.polling = null;
      }
    },

    async fetchHistory() {
      try {
        const response = await this.$axios.get('/api/products/import-excel-history');
        if (response.data.status === 'Success' || response.data.status === 'success') {
          const oldData = this.historyData;
          this.historyData = response.data.data;

          // Detect when a processing import just completed
          if (oldData.length > 0) {
            for (const item of this.historyData) {
              const oldItem = oldData.find(o => o.id === item.id);
              if (oldItem && (oldItem.status === 'processing' || oldItem.status === 'pending') && item.status === 'completed') {
                this.showSnackbar(`Importación "${item.file_name}" completada (${item.processed_rows} productos)`, 'success');
              } else if (oldItem && oldItem.status === 'processing' && item.status === 'failed') {
                this.showSnackbar(`Importación "${item.file_name}" falló`, 'error');
              }
            }
          }
        }
      } catch (error) {
        console.error("Error fetching history:", error);
      }
    },

    async downloadTemplate() {
      this.exportLoading = true;
      try {
        const response = await this.$axios.get("/api/listproduct/export-excel", {
          responseType: "blob",
        });

        const blob = new Blob([response.data], {
          type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        });

        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", "plantilla_importacion_precios.xlsx");
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
        
        this.showSnackbar("Plantilla descargada correctamente");
      } catch (error) {
        console.error("Error downloading template:", error);
        this.showSnackbar("Error al descargar la plantilla", "error");
      } finally {
        this.exportLoading = false;
      }
    },

    async generatePreview() {
      if (!this.excelFile) {
        this.previewData = [];
        this.previewKey = null;
        this.previewStats = null;
        return;
      }

      this.previewLoading = true;
      const formData = new FormData();
      
      const file = Array.isArray(this.excelFile) ? this.excelFile[0] : this.excelFile;
      if (!file) {
        this.previewLoading = false;
        return;
      }

      formData.append('file', file);

      try {
        const response = await this.$axios.post('/api/products/import-excel-preview', formData);

        this.previewData = response.data.data.changes;
        this.previewKey = response.data.data.preview_key;
        this.previewStats = response.data.data.stats || null;
        
        if (this.previewData.length === 0) {
          this.showSnackbar("No se encontraron cambios en el archivo", "info");
        } else {
          this.showSnackbar(`Se detectaron cambios en ${this.previewData.length} productos`);
        }
      } catch (error) {
        console.error("Error generating preview:", error);
        this.showSnackbar("Error al procesar el archivo Excel", "error");
        this.excelFile = null;
      } finally {
        this.previewLoading = false;
      }
    },

    // Opens the confirmation dialog — the actual button handler
    confirmUpdate() {
      if (!this.previewKey) return;
      this.showConfirmDialog = true;
    },

    // Executes the actual update after user confirms in dialog
    async executeUpdate() {
      this.showConfirmDialog = false;

      if (!this.previewKey) return;

      this.confirmLoading = true;
      this.isProcessing = true;
      
      const file = Array.isArray(this.excelFile) ? this.excelFile[0] : this.excelFile;
      const fileName = file ? file.name : 'Importación';
      const fileSize = file ? this.formatFileSize(file.size) : 'N/A';

      try {
        await this.$axios.post('/api/products/import-excel-confirm', {
          preview_key: this.previewKey,
          file_name: fileName,
          file_size: fileSize
        });

        Swal.fire({
          icon: 'success',
          title: 'Actualización Completada',
          text: `Se procesaron ${this.previewData.length} productos correctamente.`,
          confirmButtonColor: '#4472C4',
        });

        this.resetForm();
        this.fetchHistory();
      } catch (error) {
        console.error("Error confirming update:", error);
        const errorMsg = error.response?.data?.message || 'Hubo un problema al realizar la actualización.';
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: errorMsg,
        });
      } finally {
        this.confirmLoading = false;
        this.isProcessing = false;
      }
    },

    resetForm() {
      this.excelFile = null;
      this.previewData = [];
      this.previewKey = null;
      this.previewStats = null;
    },

    formatCurrency(value) {
      if (value === null || value === undefined) return "$0.00";
      return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(value);
    },

    formatDate(dateStr) {
      if (!dateStr) return '';
      return new Date(dateStr).toLocaleString('es-AR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },

    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },

    getStatusColor(status) {
      const colors = {
        pending: 'grey',
        processing: 'primary',
        completed: 'success',
        failed: 'error'
      };
      return colors[status] || 'grey';
    },

    getStatusLabel(status) {
      const labels = {
        pending: 'Pendiente',
        processing: 'Procesando',
        completed: 'Completado',
        failed: 'Fallido'
      };
      return labels[status] || status;
    },

    showSnackbar(text, color = 'success') {
      this.snackbarText = text;
      this.snackbarColor = color;
      this.snackbar = true;
    }
  }
}
</script>

<style scoped>
.striped-table :deep(tbody tr:nth-of-type(odd)) {
   background-color: rgba(0, 0, 0, 0.02);
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>
