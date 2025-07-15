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
                <VCol cols="12" sm="6" class="pl-0 pt-20 py-2">
                  <VAutocomplete
                    v-model="selectedCustomer"
                    :items="customers"
                    :item-title="(firstname)?'firstname':'address'"
                    item-value="id"
                    label="Clientes"
                    clearable
                  />
                </VCol>                
              </VRow>
              <VRow>                                
                <VCol cols="12" sm="6" class="pl-0 pt-20 py-2">
                  <VAutocomplete
                    v-model="selectedStatus"
                    :items="statuses"
                    item-title="name"
                    item-value="id"
                    label="Estado"
                    clearable
                  />
                </VCol>                
                <VCol cols="12" sm="6" class="pl-0 pt-20 py-2">
                  <DateRangeField v-model="dateRange" />
                  <!--
                  <div class="mt-4">
                    <strong>Rango seleccionado:</strong>
                    <pre>{{ dateRange }}</pre>
                  </div>
                  -->
                </VCol>
              </VRow>
            </VCardText>
          </VCard>
        </template>
        

        <template #item.order_number="{ item }">
          <div class="d-flex align-center">                
            <!-- avatar -->                                        
        
            <div class="d-flex flex-column text-start">
              <span class="d-block font-weight-medium text-high-emphasis text-truncate">{{ item.order_number }}</span>              
              <small>PRODUCTOS: {{ item.quantity_products }}</small>
              <div class="d-flex align-left gap-2">
                <VChip
                  color="primary"
                  density="comfortable"
                >
                  {{ item.order_type.name }}
                </VChip>            
              </div>
            </div>            
          </div>
        </template>

        <template #item.customer.firstname="{ item }">
          <span v-if="item.customer.firstname">{{ item.customer.firstname+' '+item.customer.lastname }}</span>          
        </template>
        <template #item.total_cost="{ item }">                      
          <div class="text-right text-error">
            <strong>${{ formatCurrency(item.total_cost) }}</strong>
          </div>
        </template>
        <template #item.total_profit="{ item }">                      
          <div class="text-right text-success">
            <strong>${{ formatCurrency(item.total_profit) }}</strong>
          </div>
        </template>
        <template #item.customer="{ item }">                      
          
            <strong>{{ getCustomer(item.customer) }}</strong>
          
        </template>
        <template #item.total_amount="{ item }">                      
          <div :class=" (item.total_profit>0)? 'text-right text-success':'text-right text-error'">
            <strong>${{ formatCurrency(item.total_amount) }}</strong>
          </div>
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
       <!-- Actions -->
        <template #item.actions="{ item }">
          <div class="d-flex justify-center align-center gap-1">              
            <!-- Icono de hamburguesa para el menú de acciones -->
            <VMenu
              offset-y
              transition="scale-transition"
            >
              <template #activator="{ props }">
                <IconBtn size="small" v-bind="props">
                  <VIcon icon="ri-more-2-fill" />
                </IconBtn>
              </template>

              <!-- Opciones del menú -->
              <VList>                
                <VListItem @click="showFactura2(item)">
                  <VListItemContent>                      
                      <VListItemTitle>
                        <IconBtn
                        size="small"
                        class="my-1"
                        title="Remito"
                        
                      >
                        <VIcon icon="ri-file-pdf-2-line" />
                      </IconBtn>
                      </VListItemTitle>                                              
                  </VListItemContent>
                </VListItem>
                
                <VListItem  @click="showFactura(item)">
                  <VListItemContent>                      
                      <VListItemTitle>
                        <IconBtn
                        size="small"
                        class="my-1"
                        title="Comanda"                        
                      >
                        <VIcon icon="ri-file-pdf-2-line" />
                      </IconBtn>
                      </VListItemTitle>                                              
                  </VListItemContent>
                </VListItem>
                <VListItem @click="openMovementDialog(item)">
                  <VListItemContent>                      
                      <VListItemTitle>
                        <IconBtn
                          size="small"
                          class="my-1"
                          title="Cambiar el estado de la orden"
                          @click="openMovementDialog(item)"
                        >
                          <VIcon icon="ri-swap-box-line" />
                        </IconBtn>
                      </VListItemTitle>                                              
                  </VListItemContent>
                </VListItem>
                
              </VList>
            </VMenu>
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

      <v-dialog v-model="movementDialog" max-width="45%">
        <VCard>
          <v-toolbar color="primary">
            <v-btn
              icon="ri-close-line"
              color="white"
              @click="closeMovementDialog"
            />
            <v-toolbar-title>{{ movementFormTitle }}</v-toolbar-title>
            <v-spacer />
          </v-toolbar>
          <VForm ref="movementForm" v-model="validMovement">
            <VCardText>
              <VContainer>
                <!-- Sección de información del cliente -->
                <VCard class="mb-6" color="grey-lighten-4">
                  <VCardTitle class="d-flex align-center">
                    <VIcon icon="mdi-account-circle" class="me-2" />
                    <span>Información del Cliente</span>
                  </VCardTitle>
                  <VDivider />
                  <VCardText>
                    <VRow>
                      <VCol cols="12" md="4">
                        <div class="info-field">
                          <div class="text-caption text-grey-darken-2">Nombre</div>
                          <span class="text-body-1 font-weight-bold">
                            {{ selectedOrder.customer.firstname }} {{ selectedOrder.customer.lastname }}
                          </span>
                        </div>
                      </VCol>
                      
                      <VCol cols="12" md="4">
                        <div class="info-field">
                          <div class="text-caption text-grey-darken-2">Dirección</div>
                          <span class="text-body-1 font-weight-bold">
                            {{ selectedOrder.customer.address }}
                          </span>
                        </div>
                      </VCol>
                      
                      <VCol cols="12" md="4">
                        <div class="info-field">
                          <div class="text-caption text-grey-darken-2">Contacto</div>
                          <span class="text-body-1 font-weight-bold">
                            {{ selectedOrder.customer.telephone || 'Sin teléfono' }}
                            <template v-if="selectedOrder.customer.email">
                              <br>{{ selectedOrder.customer.email }}
                            </template>
                          </span>
                        </div>
                      </VCol>
                    </VRow>
                  </VCardText>
                </VCard>

                <!-- Sección de información de la orden -->
                <VCard class="mb-6" color="primary-lighten-5">
                  <VCardTitle class="d-flex align-center">
                    <VIcon icon="mdi-package-variant-closed" class="me-2" />
                    <span>Detalles de la Orden</span>
                  </VCardTitle>
                  <VDivider />
                  <VCardText>
                    <VRow>
                      <VCol cols="12" md="3" class="d-flex align-center">
                        <div class="info-field">
                          <div class="text-caption text-primary">Número de Orden</div>
                          <span class="text-h6 font-weight-bold">{{ selectedOrder.order_number }}</span>
                        </div>
                      </VCol>
                      
                      <VCol cols="12" md="3" class="d-flex align-center">
                        <div class="info-field">
                          <div class="text-caption text-primary">Fecha</div>
                          <span class="text-h6 font-weight-bold">
                            {{ formatDate(selectedOrder.order_date) }}
                          </span>
                        </div>
                      </VCol>
                      
                      <VCol cols="12" md="2" class="d-flex align-center">
                        <div class="info-field">
                          <div class="text-caption text-primary">Estado Actual</div>
                          <VChip :color="getStatusColor(selectedOrder)" class="font-weight-bold">
                            {{ selectedOrder.status.name }}
                          </VChip>
                        </div>
                      </VCol>
                      
                      <VCol cols="12" md="2" class="d-flex align-center">
                        <div class="info-field">
                          <div class="text-caption text-primary">Importe Total</div>
                          <span class="text-h6 font-weight-bold text-success">
                            {{ formatCurrency(selectedOrder.total_amount) }}
                          </span>
                        </div>
                      </VCol>
                      
                      <VCol cols="12" md="2" class="d-flex align-center">
                        <div class="info-field">
                          <div class="text-caption text-primary">Productos</div>
                          <span class="text-h6 font-weight-bold">
                            {{ selectedOrder.quantity_products }} <small class="text-caption">items</small>
                          </span>
                        </div>
                      </VCol>
                    </VRow>
                  </VCardText>
                </VCard>

                <!-- Selector de estado -->
                <VRow>
                  <VCol cols="12" sm="12">
                    <VAutocomplete
                      v-model="movement.status_id"
                      :items="statuses"
                      item-title="name"
                      item-value="id"
                      label="Seleccionar nuevo estado"
                      :rules="[validateStatusChange]"
                      variant="outlined"
                      class="mt-2"
                    />
                  </VCol>
                </VRow>
              </VContainer>
            </VCardText>
            
            <VCardActions>
              <VSpacer />
              <VBtn variant="outlined" color="secondary" @click="closeMovementDialog">
                Cancelar
              </VBtn>
              <VBtn 
                color="primary"
                @click="saveMovement"
                :loading="savingMovement"
                :disabled="!validMovement"
              >
                Actualizar Estado
              </VBtn>
            </VCardActions>
          </VForm>
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
import DateRangeField from '@/components/DateRangeField.vue';


export default {
  components: { DateRangeField },
  data() {
   
    return {
      selectedDate: null,
      menu: false,      
      dateRange: {
        start: null,
        end: null,
      },            
      datesSalida: [],
      loading: false,
      search: '',
      customers: [],
      orders: [],
      stock: [],
      products: [],
      warehouses: [],
      statuses: [],
      selectedCustomer: null,
      selectedStatus: null,
      selectedWarehouse: null,
      selectedOrder: null,
      stockFilter: null,
      
      // Dialogs      
      historyDialog: false,
      summaryDialog: false,
      movementDialog: false,
      
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
        { title: 'Acciones', key: 'actions', sortable: false },
        { title: 'Orden', key: 'order_number'  }, 
        { title: 'Fecha', key: 'order_date'  },   
        /*{ title: 'Tipo de Orden', key: 'order_type.name', width: '250px' }, */
        { title: 'Cliente', key: 'customer' },                
        /*{ title: 'Productos', key: 'quantity_products', width: '80px' },*/        
        { title: 'Costo', key: 'total_cost',  align: 'end' },
        { title: 'Ganancia', key: 'total_profit', align: 'end' },
        { title: 'Total', key: 'total_amount', align: 'end' },
        { title: 'Envio', key: 'shipping', align: 'center' },
        { title: 'Estado', key: 'status.name' },                
        
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
      if (this.selectedStatus) {
        filtered = filtered.filter(item => item.status?.id === this.selectedStatus);
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

      if (this.dateRange && this.dateRange.start && this.dateRange.end) {
        const startDate = this.normalizeDateToStartOfDay(this.dateRange.start).getTime();
        const endDate = this.normalizeDateToEndOfDay(this.dateRange.end).getTime();
        
        
        filtered = filtered.filter(item => {
          const orderDate = this.parseDdMmYyyyToDate(item.order_date);
          if (!orderDate) return false;

          const orderTime = orderDate.getTime();
          const isInRange = orderTime >= startDate && orderTime <= endDate;

          // Debug opcional
           //console.log(`Order: ${item.order_date} | Timestamp: ${orderTime} | InRange: ${isInRange}`)

          return isInRange;
        });
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
      
      
      return filtered
    },

    movementFormTitle() {
      return this.selectedOrder 
        ? `Cambio de estado de orden ${this.selectedOrder.order_number}`
        : 'Cambio de estado de orden'
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
    validateStatusChange(value){
      if (!value) return 'Debe seleccionar un estado'
      if (value === this.selectedOrder.status.id) return 'El estado seleccionado es igual al estado actual'
      return true
    },
    normalizeDateToStartOfDay(date) {
      return new Date(date.getFullYear(), date.getMonth(), date.getDate(), 0, 0, 0)
    },
    normalizeDateToEndOfDay(date) {
      return new Date(date.getFullYear(), date.getMonth(), date.getDate()+1, 23, 59, 59)
    },
    parseDdMmYyyyToDate(str) {
      const [d, m, yAndTime] = str.split('/')
      const [y, time] = yAndTime.split(' ')
      const [hours, minutes, seconds] = time.split(':')
      return new Date(
        Number(y),
        Number(m) - 1,
        Number(d),
        Number(hours),
        Number(minutes),
        Number(seconds)
      )
    },    
    clearFechaSalida(){
      this.datesSalida = [];
    },
    getCustomer(customer) {
      if (!customer) return 'Cliente no disponible';
      
      return (customer.firstname || customer.lastname) 
        ? `${customer.firstname || ''} ${customer.lastname || ''}`.trim()
        : customer.address || 'Dirección no disponible';
    },
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

        const [ordersRes, customersRes, statusesRes] = await Promise.all([
          this.$axios.get(this.$routes["orders"]),          
          this.$axios.get(this.$routes["customers"]),          
          this.$axios.get(this.$routes["statuses"]), 
        ])
        
        this.orders = ordersRes.data.data;       
        this.customers = customersRes.data.data;
        this.statuses = statusesRes.data.data;
               

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
      console.log(item);
      if (item) {
        this.selectedOrder = item        
      } else {
        this.selectedOrder = null
        
      }
      
      this.movementDialog = true
    },

    closeMovementDialog() {
      this.movementDialog = false
      this.$refs.movementForm?.reset()
      /*this.$refs.movementForm?.reset()
      this.selectedOrder = null      */
    },

    async saveMovement() {
      const isValid = await this.$refs.movementForm.validate()
      if (!isValid) return
      
      this.savingMovement = true
      try {
        let endpoint, data    
        
        // New stock - create or update
        endpoint = `${this.$routes["orders"]}/${this.selectedOrder.id}`
        data = {
          data: {
            status_id: this.movement.status_id },          
        }
        
        
        await this.$axios.put(endpoint, data)
        this.showSnackbar('Estado actualizado correctamente', 'success')
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
      const statusColorMap = {
        [this.$statusOrders.PENDING]: 'primary',
        [this.$statusOrders.PROCESS]: 'warning',
        [this.$statusOrders.PARTIAL_PAYMENT]: 'warning',
        [this.$statusOrders.PAID]: 'success',
        [this.$statusOrders.COMPLETED]: 'success',
        [this.$statusOrders.APPROVED]: 'success'
      };
      
      return statusColorMap[item.status.code] || 'error';
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
        //link.download = `Factura_${item.order_number}.pdf`;

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
.info-field {
  padding: 8px 0;
}

.v-card-title {
  padding-bottom: 12px;
}
</style>
