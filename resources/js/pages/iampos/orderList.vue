<template>
  <VCard title="Gestión de Ordenes">
    <VCardText class="d-flex px-2">
      <VDataTable
        :headers="showHeaders"
        :items="filteredStock"
        :search="search"
        class="text-no-wrap"
        :loading="loading"
      >
        <template v-slot:top>
          <VCard flat color="white">
            <VCardText>
              <VRow>
                <VCol cols="12" sm="6" class="pl-0 pt-20 py-2">
                  <VTextField
                    v-model="search"
                     
                    label="Buscar en Ordenes"
                  />
                </VCol>
                
              </VRow>
              <VRow>
                <VCol cols="12" sm="6" class="pl-0 pt-20 py-2">
                  <VAutocomplete
                    v-model="selectedCustomer"
                    :items="customers"
                    item-title="firstname"
                    item-value="id"
                    label="Clientes"
                    clearable
                  />
                </VCol>
                <!--
                <VCol cols="12" sm="6" class="pt-20 py-2 d-flex gap-2">
                  <VBtn
                    color="primary"
                    size="large"
                    title="Registrar movimiento"
                    @click="openMovementDialog(null)"
                  >
                    <VIcon icon="ri-add-circle-line" class="mr-1" />
                    Movimiento
                  </VBtn>
                  <VBtn
                    color="secondary"
                    size="large"
                    title="Transferir stock"
                    @click="openTransferDialog()"
                  >
                    <VIcon icon="ri-arrow-left-right-line" class="mr-1" />
                    Transferir
                  </VBtn>
                  <VBtn
                    color="info"
                    size="large"
                    title="Ver resumen"
                    @click="showSummary()"
                  >
                    <VIcon icon="ri-pie-chart-line" />
                  </VBtn>
                </VCol>
                -->
              </VRow>
            </VCardText>
          </VCard>
        </template>
        

        <template #item.customer.firstname="{ item }">
          <span v-if="item.customer.firstname">{{ item.customer.firstname+' '+item.customer.lastname }}</span>          
        </template>

        <template #item.quantity_products="{ item }">
          <div class="d-flex align-center gap-2">
            <VChip
              :color="getStatusColor(item)"
              density="comfortable"
            >
              {{ item.quantity_products }}
            </VChip>            
          </div>
        </template>        

        <template #item.order_type.name="{ item }">
          <div class="d-flex align-center gap-2">
            <VChip
              :color="getStatusColor(item)"
              density="comfortable"
            >
              {{ item.order_type.name }}
            </VChip>            
          </div>
        </template>        

        <template #item.total_amount="{ item }">                      
              <strong>{{ formatCurrency(item.total_amount)  }}</strong>                      
        </template>

        <template #item.status.name="{ item }">    
          <div class="d-flex align-center gap-2">
            <VChip
              :color="getStatusColor(item)"
              density="comfortable"
            >
              {{ item.status.name }}
            </VChip>            
          </div>                                
        </template>

        <template #item.shipping_status.name="{ item }">    
          <div class="d-flex align-center gap-2">
            <VChip
              :color="getShippingStatusColor(item)"
              density="comfortable"
            >
              {{ item.shipping_status.name }}
            </VChip>            
          </div>                                
        </template>

        <template #item.payment_status.name="{ item }">    
          <div class="d-flex align-center gap-2">
            <VChip
              :color="getPaymentStatusColor(item)"
              density="comfortable"
            >
              {{ item.payment_status.name }}
            </VChip>            
          </div>                                
        </template>
       

        <template #item.actions="{ item }">
          <div class="d-flex flex-wrap gap-1 align-center" style="min-width: 120px">
            <IconBtn
              size="small"
              class="my-1"
              title="Registrar movimiento"
              @click="openMovementDialog(item)"
            >
              <VIcon icon="ri-arrow-up-down-line" />
            </IconBtn>
            
            <IconBtn
              size="small"
              class="my-1"
              title="Ver historial"
              @click="showHistory(item)"
            >
              <VIcon icon="ri-history-line" />
            </IconBtn>
            <IconBtn
              size="small"
              class="my-1"
              title="Ver Factura"
              @click="showFactura(item)"
            >
              <VIcon icon="ri-file-pdf-2-line" />
            </IconBtn>
            <IconBtn
              size="small"
              class="my-1"
              title="Ver Factura"
              @click="showFactura2(item)"
            >
              <VIcon icon="ri-file-pdf-2-line" />
            </IconBtn>
          </div>
        </template>
      </VDataTable>            

      <!-- Dialog para historial -->
      <v-dialog v-model="historyDialog" max-width="1000px">
        <VCard>
          <v-toolbar color="primary">
            <v-btn
              icon="ri-close-line"
              color="white"
              @click="historyDialog = false"
            />
            <v-toolbar-title>Historial de movimientos</v-toolbar-title>
            <v-spacer />
          </v-toolbar>
          <VCardText>
            <VDataTable
              :headers="historyHeaders"
              :items="movementHistory"
              :items-per-page="10"
            >
              <template #item.movement_type="{ item }">
                <VChip :color="getMovementTypeColor(item.movement_type)">
                  {{ formatMovementType(item.movement_type) }}
                </VChip>
              </template>
              <template #item.quantity="{ item }">
                <span :class="getQuantityClass(item.movement_type)">
                  {{ ['salida', 'transferencia'].includes(item.movement_type) && item.quantity > 0 ? '-' : '+' }}{{ Math.abs(item.quantity) }}
                </span>
              </template>
              <template #item.created_at="{ item }">
                {{ formatDate(item.created_at) }}
              </template>
            </VDataTable>
          </VCardText>
        </VCard>
      </v-dialog>
      

      <v-snackbar v-model="snackbar" :color="snackbarColor" :timeout="3000">
        {{ snackbarText }}
        <template v-slot:action="{ attrs }">
          <v-btn text v-bind="attrs" @click="snackbar = false">
            Cerrar
          </v-btn>
        </template>
      </v-snackbar>
    </VCardText>
  </VCard>
</template>

<script>
export default {
  data() {
    return {
      loading: false,
      search: '',
      orders: [],
      stock: [],
      products: [],
      warehouses: [],
      selectedCustomer: null,
      selectedWarehouse: null,
      selectedStockItem: null,
      stockFilter: null,
      
      // Dialogs      
      historyDialog: false,
      summaryDialog: false,
      
      // Form validations
      validMovement: false,
      validTransfer: false,
      validReserve: false,
      
      // Loading states
      savingMovement: false,
      savingTransfer: false,
      savingReserve: false,
      
      // Forms data
      movement: {
        product_id: null,
        warehouse_id: null,
        movement_type: 'entrada',
        quantity: 1,
        minimum_stock: 0,
        maximum_stock: 0,
        notes: ''
      },
      
      transfer: {
        product_id: null,
        from_warehouse_id: null,
        to_warehouse_id: null,
        quantity: 1,
        notes: ''
      },
      
      reserve: {
        quantity: 1
      },
            
      
      
      
      // Data
      movementHistory: [],
      stockSummary: {
        total_products: 0,
        total_stock_value: 0,
        low_stock_count: 0,
        out_of_stock_count: 0
      },
      
      // Snackbar
      snackbar: false,
      snackbarText: '',
      snackbarColor: 'success',
      
      // Headers
      headers: [
        { title: 'Acciones', key: 'actions', sortable: false, width: '100px' },
        { title: 'Orden', key: 'order_number', width: '250px' }, 
        { title: 'Fecha', key: 'order_date', width: '150px' },   
        { title: 'Tipo de Orden', key: 'order_type.name', width: '250px' }, 
        { title: 'Cliente', key: 'customer.firstname', width: '50px' },                
        { title: 'Productos', key: 'quantity_products', width: '80px' },
        { title: 'Total', key: 'total_amount', width: '150px' },
        { title: 'Estado', key: 'status.name', width: '50px' },        
        { title: 'Envio', key: 'shipping_status.name', width: '50px' },        
        { title: 'Pago', key: 'payment_status.name', width: '50px' },        
      ],
      
      historyHeaders: [
        { title: 'Fecha', key: 'created_at' },
        { title: 'Tipo', key: 'movement_type' },
        { title: 'Cantidad', key: 'quantity' },
        { title: 'Stock anterior', key: 'previous_quantity' },
        { title: 'Stock nuevo', key: 'new_quantity' },
        { title: 'Usuario', key: 'user.name' },
        { title: 'Notas', key: 'notes' },
      ]
    }
  },

  computed: {
    showHeaders() {
      return this.headers
    },

    filteredStock() {
      let filtered = this.orders;
      
      
      // Filtrar por producto seleccionado
      if (this.selectedCustomer) {
        filtered = filtered.filter(item => item.customer?.id === this.selectedCustomer);
      }
      
      // Filtrar por depósito seleccionado
      if (this.selectedWarehouse) {
        filtered = filtered.filter(item => item.warehouse?.id === this.selectedWarehouse);
      }
      
      // Filtrar por tipo de stock
      if (this.stockFilter === 'low') {
        filtered = filtered.filter(item => item.available < item.minimum_stock);
      } else if (this.stockFilter === 'empty') {
        filtered = filtered.filter(item => item.quantity <= 0);
      }
      
      // Filtrar por término de búsqueda
      /*if (this.search) {
        const searchTerm = this.search.toLowerCase();
        filtered = filtered.filter(item => {
          return (
            item.product?.name?.toLowerCase().includes(searchTerm) ||
            (item.product?.code && item.product.code.toLowerCase().includes(searchTerm)) ||
            (item.warehouse?.name && item.warehouse.name.toLowerCase().includes(searchTerm))
          );
        });
      }*/
      
      console.log("filtered");
      console.log(filtered);
      return filtered;
    },

    movementFormTitle() {
      return this.selectedStockItem 
        ? `Registrar movimiento para ${this.selectedStockItem.product.name}`
        : 'Registrar movimiento de stock'
    },

    quantityRules() {
      const rules = [
        v => !!v || 'Cantidad es requerida',
        v => v > 0 || 'Debe ser mayor a 0'
      ];

      if (this.movement.movement_type === 'salida' && this.selectedStockItem) {
        rules.push(v => v <= this.selectedStockItem.available || 'Stock insuficiente');
      }

      return rules;
    }
  },

  async created() {
    await this.fetchData()
  },

  methods: {
    formatCurrency(value) {
      return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
      }).format(value || 0);
    },
    async loadProducts() {
      try {
        const response = await this.$axios.get(this.$routes["products"]);
        this.products = response.data.data;
      } catch (error) {
        console.error("Error cargando categorías:", error);
      } finally {
      }
    },

    async fetchData() {
      this.loading = true;
      try {
    
        /*const [stockRes, productsRes, warehousesRes] = await Promise.all([
          this.$axios.get(this.$routes["stocks"]),
          this.$axios.get(this.$routes["products"]),
          this.$axios.get(this.$routes["warehouses"])
        ])*/

        const [ordersRes, customersRes] = await Promise.all([
          this.$axios.get(this.$routes["orders"]),          
          this.$axios.get(this.$routes["customers"]),          
        ])
        
        this.orders = ordersRes.data.data;       
        this.customers = customersRes.data.data;       

        /*this.products = productsRes.data.data
        this.warehouses = warehousesRes.data.data*/
 
      } catch (error) {
        console.error('Error fetching data:', error)
        this.showSnackbar('Error al cargar los datos', 'error')
      } finally {
        this.loading = false;
      }
    },

    // Movement Dialog Methods
    openMovementDialog(item) {
      if (item) {
        this.selectedStockItem = item
        this.movement.product_id = item.product.id
        this.movement.warehouse_id = item.warehouse ? item.warehouse.id : null
      } else {
        this.selectedStockItem = null
        this.movement.product_id = null
        this.movement.warehouse_id = null
      }
      
      this.movementDialog = true
    },

    closeMovementDialog() {
      this.movementDialog = false
      this.$refs.movementForm?.reset()
      this.selectedStockItem = null
      this.movement = {
        product_id: null,
        warehouse_id: null,
        movement_type: 'entrada',
        quantity: 1,
        minimum_stock: 0,
        maximum_stock:0,
        notes: ''
      }
    },

    async saveMovement() {
      const isValid = await this.$refs.movementForm.validate()
      if (!isValid) return
      
      this.savingMovement = true;
      try {
        let endpoint, data;
        
        if (this.selectedStockItem) {
          // Existing stock - record movement
          endpoint = `${this.$routes["stocks"]}/${this.selectedStockItem.id}/movements`
          data = {
            movement_type: this.movement.movement_type,
            quantity: this.movement.movement_type === 'salida' ? -Math.abs(this.movement.quantity) : Math.abs(this.movement.quantity),
            notes: this.movement.notes
          }
        } else {
          // New stock - create or update
          endpoint = `${this.$routes["stocks"]}/create-or-update`
          data = {
            product_id: this.movement.product_id,
            warehouse_id: this.movement.warehouse_id,
            quantity: this.movement.quantity,
            minimum_stock: this.movement.minimum_stock,
            maximum_stock: this.movement.maximum_stock
          }
        }
        
        await this.$axios.post(endpoint, data)
        this.showSnackbar('Movimiento registrado correctamente', 'success')
        this.closeMovementDialog()
        await this.fetchData()
      } catch (error) {
        console.error('Error saving movement:', error)
        this.showSnackbar(error.response?.data?.message || 'Error al registrar el movimiento', 'error')
      } finally {
        this.savingMovement = false;
      }
    },

    // Transfer Dialog Methods
    openTransferDialog() {
      this.transferDialog = true
    },

    closeTransferDialog() {
      this.transferDialog = false
      this.$refs.transferForm?.reset()
      this.transfer = {
        product_id: null,
        from_warehouse_id: null,
        to_warehouse_id: null,
        quantity: 1,
        notes: ''
      }
    },

    async saveTransfer() {
      const isValid = await this.$refs.transferForm.validate()
      if (!isValid) return
      
      this.savingTransfer = true;
      try {
        await this.$axios.post(`${this.$routes["stocks"]}/transfer`, this.transfer)
        this.showSnackbar('Transferencia realizada correctamente', 'success')
        this.closeTransferDialog()
        await this.fetchData()
      } catch (error) {
        console.error('Error saving transfer:', error)
        this.showSnackbar(error.response?.data?.message || 'Error al realizar la transferencia', 'error')
      } finally {
        this.savingTransfer = false;
      }
    },

    // Reserve Dialog Methods
    openReserveDialog(item) {
      console.log(item);
      this.selectedStockItem = item
      this.reserveDialog = true
    },

    closeReserveDialog() {
      this.reserveDialog = false
      this.$refs.reserveForm?.reset()
      this.selectedStockItem = null
      this.reserve = { quantity: 1 }
    },

    async saveReserve() {
      const isValid = await this.$refs.reserveForm.validate()
      if (!isValid) return
      
      this.savingReserve = true;
      try {
        await this.$axios.post(`${this.$routes["stocks"]}/${this.selectedStockItem.id}/reserve`, this.reserve)
        this.showSnackbar('Stock reservado correctamente', 'success')
        this.closeReserveDialog()
        await this.fetchData()
      } catch (error) {
        console.error('Error reserving stock:', error)
        this.showSnackbar(error.response?.data?.message || 'Error al reservar el stock', 'error')
      } finally {
        this.savingReserve = false;
      }
    },

    // History Methods
    async showHistory(item) {
      try {
        const res = await this.$axios.get(`${this.$routes["stocks"]}/${item.id}/movements?include=user`)
        this.movementHistory = res.data.data
        this.historyDialog = true
      } catch (error) {
        console.error('Error fetching history:', error)
        this.showSnackbar('Error al cargar el historial', 'error')
      }
    },

    // Summary Methods
    async showSummary() {
      try {
        const res = await this.$axios.get(`${this.$routes["stocks"]}/summary`)
        this.stockSummary = res.data.data
        this.summaryDialog = true
      } catch (error) {
        console.error('Error fetching summary:', error)
        this.showSnackbar('Error al cargar el resumen', 'error')
      }
    },

    // Utility Methods
    getStatusColor(item) {      
      switch(item.status_id){
      case 1: return 'primary'; break
      case 2: 
      case 3: 
        return 'warning'; break      
      case 4: return 'success'; break
      default: return 'error'; break
      }      
    },

    getPaymentStatusColor(item) {
      
      switch(item.payment_status_id){
      case 1: return 'warning'; break
      case 2: return 'success'; break
      case 3: return 'primary'; break      
      default: return 'error'; break
      }
      
    },

    getShippingStatusColor(item) {
      
      switch(item.shipping_status_id){
      case 1: return 'primary'; break
      case 2: 
      case 3: 
        return 'warning'; break      
      case 4: 
      case 5: 
      case 6: 
      case 7: 
        return 'success'; break      
      default: return 'error'; break
      }
      
    },
    getStockLevelColor(item) {
      if (!item.minimum_stock) return 'primary'
      
      if (item.available <= 0) return 'error'
      if (item.available < item.minimum_stock) return 'warning'
      return 'success'
    },

    calculateStockPercentage(item) {
      if (!item.maximum_stock || item.maximum_stock <= 0) return 0
      return (item.quantity / item.maximum_stock) * 100
    },
    calculateStockPercentageMin(item) {
      if (!item.minimum_stock || item.minimum_stock <= 0) return 0
      return (item.quantity / item.minimum_stock) * 100
    },

    formatMovementType(type) {
      const types = {
        entrada: 'Entrada',
        salida: 'Salida',
        ajuste: 'Ajuste',
        transferencia: 'Transferencia'
      }
      return types[type] || type
    },

    getMovementTypeColor(type) {
      const colors = {
        entrada: 'success',
        salida: 'error',
        ajuste: 'warning',
        transferencia: 'info'
      }
      return colors[type] || 'primary'
    },

    getQuantityClass(type) {
      return ['entrada', 'ajuste'].includes(type) ? 'text-success' : 'text-error'
    },

    formatDate(date) {
      return new Date(date).toLocaleString()
    },

    formatCurrency(value) {
      return new Intl.NumberFormat('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(value || 0)
    },

    showSnackbar(text, color) {
      this.snackbarText = text
      this.snackbarColor = color
      this.snackbar = true
    },
    async showFactura(item) {
      try {
        // Mostrar loader mientras se genera el PDF
        this.loading = true;
        
        // Llamar al endpoint de Laravel que genera el PDF
        const response = await this.$axios.get(
          `${this.$routes["orders"]}/invoice/${item.id}`,
          { 
            responseType: 'blob' 
          }
        );
        
         
        var blob = new Blob([response.data], { type: "application/pdf" });
        const url = window.URL.createObjectURL(blob, { oneTimeOnly: true });
        const link = document.createElement("a");
        link.target = "_blank";

        link.href = url;

        document.body.appendChild(link);
        link.click();
    
        
      } catch (error) {
        console.error('Error generando factura:', error);
        this.showSnackbar('Error al generar la factura', 'error');
      } finally {
        this.loading = false;
      }
    },
    async showFactura2(item) {
      try {
        // Mostrar loader mientras se genera el PDF
        this.loading = true;
        
        // Llamar al endpoint de Laravel que genera el PDF
        const response = await this.$axios.get(
          `${this.$routes["orders"]}/invoice2/${item.id}`,
          { 
            responseType: 'blob' 
          }
        );
        
         
        var blob = new Blob([response.data], { type: "application/pdf" });
        const url = window.URL.createObjectURL(blob, { oneTimeOnly: true });
        const link = document.createElement("a");
        link.target = "_blank";

        link.href = url;

        document.body.appendChild(link);
        link.click();
    
        
      } catch (error) {
        console.error('Error generando factura:', error);
        this.showSnackbar('Error al generar la factura', 'error');
      } finally {
        this.loading = false;
      }
    }

  }
}
</script>

<style scoped>
.text-success {
  color: #4CAF50;
}

.text-error {
  color: #F44336;
}

.text-disabled {
  color: #9E9E9E;
}
</style>
