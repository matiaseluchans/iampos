<template>
  <VCard title="Importación y Actualización de Precios via Excel">
    <VCardText>
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
            @click="confirmUpdate"
            :loading="confirmLoading"
          >
            Confirmar Actualización ({{ previewData.length }} productos)
          </VBtn>
        </VCol>
      </VRow>

      <VDivider class="my-6" v-if="previewData.length > 0" />

      <!-- Vista previa de cambios antes de confirmar -->
      <div v-if="previewData.length > 0">
        <h3 class="text-h6 mb-4">Vista Previa de Cambios</h3>
        <VDataTable
          :headers="previewHeaders"
          :items="previewData"
          class="text-no-wrap striped-table pb-5"
          density="comfortable"
        >
          <template #item.old_purchase_price="{ item }">
            <span class="text-grey">{{ formatCurrency(item.old_purchase_price) }}</span>
          </template>
          
          <template #item.new_purchase_price="{ item }">
            <VChip
              v-if="item.new_purchase_price != item.old_purchase_price"
              color="info"
              size="small"
              class="font-weight-bold"
            >
              {{ formatCurrency(item.new_purchase_price) }}
            </VChip>
            <span v-else>{{ formatCurrency(item.new_purchase_price) }}</span>
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

      <!-- Historial de Importaciones -->
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
            <div class="d-flex align-center" style="min-width: 120px;">
              <VProgressLinear
                v-if="item.status === 'processing' || item.status === 'completed'"
                :model-value="(item.processed_rows / item.total_rows) * 100"
                color="primary"
                height="8"
                rounded
              />
              <span class="ml-2 text-caption">{{ item.processed_rows }}/{{ item.total_rows }}</span>
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
    previewKey: null,
    previewData: [],
    historyData: [],
    polling: null,
    snackbar: false,
    snackbarText: '',
    snackbarColor: 'success',
    previewHeaders: [
      { title: 'Código', key: 'code', sortable: true },
      { title: 'Producto', key: 'name', sortable: true },
      { title: 'P. Compra (Anterior)', key: 'old_purchase_price', sortable: false },
      { title: 'P. Compra (Nuevo)', key: 'new_purchase_price', sortable: false },
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
    // Iniciar polling para actualizar historial cada 5 segundos
    this.polling = setInterval(this.fetchHistory, 5000);
  },

  beforeUnmount() {
    clearInterval(this.polling);
  },

  methods: {
    async fetchHistory() {
      try {
        const response = await this.$axios.get('/api/products/import-excel-history');
        if (response.data.status === 'Success' || response.data.status === 'success') {
          this.historyData = response.data.data;
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

    async confirmUpdate() {
      if (!this.previewKey) return;

      this.confirmLoading = true;
      
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
          title: 'Importación Iniciada',
          text: 'El proceso se ejecutará en segundo plano. Puedes ver el progreso en el historial debajo.',
          confirmButtonColor: '#4472C4',
        });

        this.resetForm();
        this.fetchHistory();
      } catch (error) {
        console.error("Error confirming update:", error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Hubo un problema al iniciar la actualización.',
        });
      } finally {
        this.confirmLoading = false;
      }
    },

    resetForm() {
      this.excelFile = null;
      this.previewData = [];
      this.previewKey = null;
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
