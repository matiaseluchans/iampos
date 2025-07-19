<template>
  <VCard title="Gestión de Stock">
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
                     
                    label="Buscar en stock"
                  />
                </VCol>
                
                <VCol cols="12" sm="3" class="pt-20 py-2">
                  <VAutocomplete
                    v-model="selectedWarehouse"
                    :items="warehouses"
                    item-title="name"
                    item-value="id"
                    label="Depósito"
                    clearable
                  />
                </VCol>
                <VCol cols="12" sm="3" class="pt-20 py-2">
                  <VSelect
                    v-model="stockFilter"
                    :items="stockFilterOptions"
                    label="Filtrar stock"
                    clearable
                  />
                </VCol>
              </VRow>
              <VRow>
                <VCol cols="12" sm="6" class="pl-0 pt-20 py-2">
                  <VAutocomplete
                    v-model="selectedProduct"
                    :items="products"
                    item-title="name"
                    item-value="id"
                    label="Producto"
                    clearable
                  />
                </VCol>
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
              </VRow>
            </VCardText>
          </VCard>
        </template>
        <!--

        <template #item.product.name="{ item }">
          <RouterLink :to="{ name: 'products-edit', params: { id: item.product.id } }">
            {{ item.product.name }}
          </RouterLink>
        </template>
        -->

        <template #item.warehouse.name="{ item }">
          <span v-if="item.warehouse">{{ item.warehouse.name }}</span>
          <span v-else class="text-disabled">Sin depósito</span>
        </template>

        <template #item.quantity="{ item }">
          <div class="d-flex align-center gap-2">
            <VChip
              :color="getStockLevelColor(item)"
              density="comfortable"
            >
              {{ item.quantity }}
            </VChip>
            <span v-if="item.reserved_quantity > 0" class="text-caption">
              (Reservado: {{ item.reserved_quantity }})
            </span>
            <VChip
              v-if="item.available < item.minimum_stock"
              color="error"
              size="small"
              variant="outlined"
            >
              Stock Bajo
            </VChip>
          </div>
        </template>

        <template #item.available="{ item }">
          <VChip
            :color="item.available > 0 ? 'success' : 'error'"
            density="comfortable"
          >
            {{ item.available }}
          </VChip>
        </template>

        <template #item.stock_level_min="{ item }">
          <VProgressLinear
            :model-value="calculateStockPercentageMin(item)"
            :color="getStockLevelColor(item)"
            height="20"
            rounded
          >
            <template v-slot:default="{ value }">
              <strong>{{ Math.ceil(value) }}%</strong>
            </template>
          </VProgressLinear>
        </template>
        <template #item.stock_level="{ item }">
          <VProgressLinear
            :model-value="calculateStockPercentage(item)"
            :color="getStockLevelColor(item)"
            height="20"
            rounded
          >
            <template v-slot:default="{ value }">
              <strong>{{ Math.ceil(value) }}%</strong>
            </template>
          </VProgressLinear>
        </template>

        <template #item.actions="{ item }">
          <div class="d-flex flex-wrap gap-1 align-center" style="min-width: 80px">
            <IconBtn
              size="small"
              class="my-1"
              title="Registrar movimiento"
              @click="openMovementDialog(item)"
            >
              <VIcon icon="ri-arrow-up-down-line" />
            </IconBtn>
            <!--<IconBtn
              size="small"
              class="my-1"
              title="Reservar stock"
              @click="openReserveDialog(item)"
              :disabled="item.available <= 0"
            >
              <VIcon icon="ri-bookmark-line" />
            </IconBtn>-->
            <IconBtn
              size="small"
              class="my-1"
              title="Ver historial"
              @click="showHistory(item)"
            >
              <VIcon icon="ri-history-line" />
            </IconBtn>
          </div>
        </template>
      </VDataTable>

      <!-- Dialog para registrar movimientos -->
      <v-dialog v-model="movementDialog" max-width="700px">
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
                <VRow>
                  <VCol cols="12" sm="12">
                    <VAutocomplete
                      v-model="movement.product_id"
                      :items="products"
                      item-title="name"
                      item-value="id"
                      label="Producto"
                      :rules="[v => !!v || 'Producto es requerido']"
                      :disabled="!!selectedStockItem"
                    />
                  </VCol>
                  <VCol cols="12" sm="6">
                    <VAutocomplete
                      v-model="movement.warehouse_id"
                      :items="warehouses"
                      item-title="name"
                      item-value="id"
                      label="Depósito"
                      :rules="[v => !!v || 'Depósito es requerido']"
                      :disabled="!!selectedStockItem"
                    />
                  </VCol>
                  <VCol cols="12" sm="6">
                    <VSelect
                      v-model="movement.movement_type"
                      :items="movementTypes"
                      item-title="text"
                      item-value="value"
                      label="Tipo de movimiento"
                      :rules="[v => !!v || 'Tipo es requerido']"
                    />
                  </VCol>
                  <VCol cols="12" sm="4">
                    <VTextField
                      v-model="movement.quantity"
                      label="Cantidad"
                      type="number"
                      step="1"
                      :rules="quantityRules"
                    />
                  </VCol>
                  <VCol cols="12" sm="4" v-if="!selectedStockItem">
                    <VTextField
                      v-model="movement.minimum_stock"
                      label="Stock Mínimo"
                      type="number"
                      step="1"
                      hint="Solo para productos nuevos"
                    />
                  </VCol>
                  <VCol cols="12" sm="4" v-if="!selectedStockItem">
                    <VTextField
                      v-model="movement.maximum_stock"
                      label="Stock Máximo"
                      type="number"
                      step="1"
                      hint="Solo para productos nuevos"
                    />
                  </VCol>
                  <VCol cols="12">
                    <VTextField
                      v-model="movement.notes"
                      label="Notas"
                      placeholder="Motivo del movimiento"
                    />
                  </VCol>
                  <VCol cols="12" v-if="movement.movement_type === 'salida' && selectedStockItem">
                    <VAlert
                      type="info"
                      variant="tonal"
                    >
                      Stock disponible: {{ selectedStockItem.available }}
                    </VAlert>
                  </VCol>
                </VRow>
              </VContainer>
            </VCardText>
            <VCardActions>
              <VSpacer />
              <VBtn variant="outlined" color="primary" @click="closeMovementDialog">
                Cancelar
              </VBtn>
              <VBtn 
                class="bg-primary" 
                color="white"
                @click="saveMovement"
                :loading="savingMovement"
              >
                Guardar movimiento
              </VBtn>
            </VCardActions>
          </VForm>
        </VCard>
      </v-dialog>

      <!-- Dialog para transferencias -->
      <v-dialog v-model="transferDialog" max-width="600px">
        <VCard>
          <v-toolbar color="secondary">
            <v-btn
              icon="ri-close-line"
              color="white"
              @click="closeTransferDialog"
            />
            <v-toolbar-title>Transferir Stock</v-toolbar-title>
            <v-spacer />
          </v-toolbar>
          <VForm ref="transferForm" v-model="validTransfer">
            <VCardText>
              <VContainer>
                <VRow>
                  <VCol cols="12">
                    <VAutocomplete
                      v-model="transfer.product_id"
                      :items="products"
                      item-title="name"
                      item-value="id"
                      label="Producto"
                      :rules="[v => !!v || 'Producto es requerido']"
                    />
                  </VCol>
                  <VCol cols="12" sm="6">
                    <VAutocomplete
                      v-model="transfer.from_warehouse_id"
                      :items="warehouses"
                      item-title="name"
                      item-value="id"
                      label="Desde depósito"
                      :rules="[v => !!v || 'Depósito origen es requerido']"
                    />
                  </VCol>
                  <VCol cols="12" sm="6">
                    <VAutocomplete
                      v-model="transfer.to_warehouse_id"
                      :items="warehouses"
                      item-title="name"
                      item-value="id"
                      label="Hacia depósito"
                      :rules="[
                        v => !!v || 'Depósito destino es requerido',
                        v => v !== transfer.from_warehouse_id || 'Debe ser diferente al origen'
                      ]"
                    />
                  </VCol>
                  <VCol cols="12" sm="6">
                    <VTextField
                      v-model="transfer.quantity"
                      label="Cantidad"
                      type="number"
                      step="1"
                      :rules="[
                        v => !!v || 'Cantidad es requerida',
                        v => v > 0 || 'Debe ser mayor a 0'
                      ]"
                    />
                  </VCol>
                  <VCol cols="12">
                    <VTextField
                      v-model="transfer.notes"
                      label="Notas"
                      placeholder="Motivo de la transferencia"
                    />
                  </VCol>
                </VRow>
              </VContainer>
            </VCardText>
            <VCardActions>
              <VSpacer />
              <VBtn variant="outlined" color="secondary" @click="closeTransferDialog">
                Cancelar
              </VBtn>
              <VBtn 
                class="bg-secondary" 
                color="white" 
                @click="saveTransfer"
                :loading="savingTransfer"
              >
                Transferir
              </VBtn>
            </VCardActions>
          </VForm>
        </VCard>
      </v-dialog>

      <!-- Dialog para reservas -->
      <v-dialog v-model="reserveDialog" max-width="500px">
        <VCard>
          <v-toolbar color="info">
            <v-btn
              icon="ri-close-line"
              color="white"
              @click="closeReserveDialog"
            />
            <v-toolbar-title>Reservar Stock</v-toolbar-title>
            <v-spacer />
          </v-toolbar>
          <VForm ref="reserveForm" v-model="validReserve">
            <VCardText>
              <VContainer>
                <VRow>
                  <VCol cols="12">
                    <VAlert type="info" variant="tonal">
                      Producto: {{ selectedStockItem?.product?.name }}<br>
                      Stock disponible: {{ selectedStockItem?.available }}
                    </VAlert>
                  </VCol>
                  <VCol cols="12">
                    <VTextField
                      v-model="reserve.quantity"
                      label="Cantidad a reservar"
                      type="number"
                      step="1"
                      :rules="[
                        v => !!v || 'Cantidad es requerida',
                        v => v > 0 || 'Debe ser mayor a 0',
                        v => v <= selectedStockItem?.available || 'No hay suficiente stock disponible'
                      ]"
                    />
                  </VCol>
                </VRow>
              </VContainer>
            </VCardText>
            <VCardActions>
              <VSpacer />
              <VBtn variant="outlined" color="info" @click="closeReserveDialog">
                Cancelar
              </VBtn>
              <VBtn 
              class="bg-info"
                color="white" 
                @click="saveReserve"
                :loading="savingReserve"
              >
                Reservar
              </VBtn>
            </VCardActions>
          </VForm>
        </VCard>
      </v-dialog>

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

      <!-- Dialog para resumen -->
      <v-dialog v-model="summaryDialog" max-width="800px">
        <VCard>
          <v-toolbar color="info">
            <v-btn
              icon="ri-close-line"
              color="white"
              @click="summaryDialog = false"
            />
            <v-toolbar-title>Resumen de Stock</v-toolbar-title>
            <v-spacer />
          </v-toolbar>
          <VCardText>

            <VRow class="pt-6">
              <VCol cols="12" sm="6" md="6">
                <VCard color="primary" variant="tonal">
                  <VCardText class="text-center">
                    <div class="text-h4">{{ stockSummary.total_products }}</div>
                    <div class="text-caption">Productos</div>
                  </VCardText>
                </VCard>
              </VCol>
              <VCol cols="12" sm="6" md="6">
                <VCard color="success" variant="tonal">
                  <VCardText class="text-center">
                    <div class="text-h4">${{ formatCurrency(stockSummary.total_stock_value) }}</div>
                    <div class="text-caption">Valor Total</div>
                  </VCardText>
                </VCard>
              </VCol>
            </VRow>
            <VRow>
              <VCol cols="12" sm="6" md="6">
                <VCard color="warning" variant="tonal">
                  <VCardText class="text-center">
                    <div class="text-h4">{{ stockSummary.low_stock_count }}</div>
                    <div class="text-caption">Stock Bajo</div>
                  </VCardText>
                </VCard>
              </VCol>
              <VCol cols="12" sm="6" md="6">
                <VCard color="error" variant="tonal">
                  <VCardText class="text-center">
                    <div class="text-h4">{{ stockSummary.out_of_stock_count }}</div>
                    <div class="text-caption">Sin Stock</div>
                  </VCardText>
                </VCard>
              </VCol>
            </VRow>
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
      stock: [],
      products: [],
      warehouses: [],
      selectedProduct: null,
      selectedWarehouse: null,
      selectedStockItem: null,
      stockFilter: null,
      
      // Dialogs
      movementDialog: false,
      transferDialog: false,
      reserveDialog: false,
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
      
      // Options
      movementTypes: [
        { text: 'Entrada', value: 'entrada' },
        { text: 'Salida', value: 'salida' },
        { text: 'Ajuste', value: 'ajuste' },
        { text: 'Transferencia', value: 'transferencia' }
      ],
      
      stockFilterOptions: [
        { title: 'Todos', value: null },
        { title: 'Stock Bajo', value: 'low' },
        { title: 'Sin Stock', value: 'empty' }
      ],
      
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
        { title: 'Producto', key: 'product.name', width: '250px' },
       /* { title: 'Código', key: 'product.code', width: '120px' },
        { title: 'Depósito', key: 'warehouse.name', width: '150px' },*/
        { title: 'Stock', key: 'quantity', width: '50px' },
        /*{ title: 'Disponible', key: 'available', width: '120px' },
        { title: 'Reservado', key: 'reserved_quantity', width: '120px' },*/
        { title: 'Mín', key: 'minimum_stock', width: '80px' },
        //{ title: 'Nivel min', key: 'stock_level_min', width: '150px' },
        //{ title: 'Máx', key: 'maximum_stock', width: '50px' },
        //{ title: 'Nivel Max', key: 'stock_level', width: '150px' }
      ],
      
      historyHeaders: [
        { title: 'Fecha', key: 'created_at' },
        { title: 'Tipo', key: 'movement_type' },
        { title: 'Cantidad', key: 'quantity' },
        { title: 'Stock anterior', key: 'previous_quantity' },
        { title: 'Stock nuevo', key: 'new_quantity' },
        { title: 'Usuario', key: 'user.name' },
        { title: 'Notas', key: 'notes' }
      ]
    }
  },

  computed: {
    showHeaders() {
      return this.headers
    },

    filteredStock() {
      let filtered = this.stock;
      
      
      // Filtrar por producto seleccionado
      if (this.selectedProduct) {
        filtered = filtered.filter(item => item.product?.id === this.selectedProduct);
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

    

        const [stockRes, productsRes, warehousesRes] = await Promise.all([
          this.$axios.get(this.$routes["stocks"]),
          this.$axios.get(this.$routes["products"]),
          this.$axios.get(this.$routes["warehouses"])
        ])
        
        this.stock = stockRes.data.data;

      
        this.stock = stockRes.data.data.map(item => ({
          ...item,
          available: item.quantity - item.reserved_quantity
        }))
       
        this.products = productsRes.data.data
        this.warehouses = warehousesRes.data.data
 
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
