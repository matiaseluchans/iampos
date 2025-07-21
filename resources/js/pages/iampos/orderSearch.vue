<template>
  <VCard title="Buscador de Ordenes">
    <VCardText class="d-flex px-2">
      <VDataTable
        :headers="showHeaders"
        :items="orders.data || []"        
        class="text-no-wrap"
        :loading="loading"
        :items-per-page="pagination.itemsPerPage"
        :page="pagination.page"
        :items-length="orders.total || 0"
        @update:options="handlePaginationChange"
      >
        <template v-slot:top>
          <VCard flat color="white">
            <VCardText>
              <VRow>
                <VCol cols="12" md="4" sm="4" class="pl-0 pt-20 py-2">
                  <VTextField
                    v-model="search"                     
                    label="Número de Orden"
                  />
                </VCol>
                <VCol cols="12" md="4" sm="4" class="pl-0 pt-20 py-2">
                  <VAutocomplete
                    v-model="selectedCustomer"
                    :items="customers"
                    :item-title="(customers.firstname)?'firstname':'address'"
                    item-value="id"
                    label="Clientes"                    
                    multiple
                    clearable
                  />
                </VCol>
                <VCol 
                  cols="12" 
                  md="4"
                  sm="4"
                  class="pl-0 pt-20 py-2">
                  <VAutocomplete
                    v-model="selectedStatus"
                    :items="statuses"
                    item-title="name"
                    item-value="id"
                    label="Estado"
                    clearable
                  />
                </VCol>                
              </VRow>
              <VRow>                                                
                <VCol cols="12" sm="6" class="pl-0 pt-20 py-2">
                  <DateRangeField ref="dateOrderRange" v-model="dateOrderRange" modelLabel='Fecha Orden' />
                </VCol>
                <VCol cols="12" sm="6" class="pl-0 pt-20 py-2">
                  <DateRangeField ref="dateDeliveryRange" v-model="dateRange" modelLabel='Fecha Entrega' />                  
                </VCol>
              </VRow>
            </VCardText>
            <VCardActions class="justify-end">
              <VBtn 
                variant="outlined" 
                color="warning" 
                @click="reset"
              >
                Reset
              </VBtn>
              <VBtn 
                variant="outlined"
                color="primary"
                @click="fetchData"
                :loading="loading"                
              >                
                Buscar
              </VBtn>
            </VCardActions>
          </VCard>
          
        </template>      
        <template #item.order_number="{ item }">
          <div class="d-flex align-center">                
            <!-- avatar -->                                        
        
            <div class="d-flex flex-column text-start">            
              <span class="d-block font-weight-medium text-high-emphasis text-truncate">{{ item.order_number }}</span>     
              <small>Productos: {{ item.quantity_products }}</small>                                                                    
              <!--<div class="d-flex align-left gap-2">
                <VChip
                  color="primary"
                  density="comfortable"
                >
                  {{ item.order_type.name }}
                </VChip>           
              </div> -->
            </div>            
          </div>
        </template>
        <template #item.customer.firstname="{ item }">
          <span v-if="item.customer.firstname">{{ item.customer.firstname+' '+item.customer.lastname }}</span>          
        </template>
        <template #item.order_date="{ item }">
          <div class="d-flex flex-column">
            <span class="font-weight-medium">
              {{ formatDateGrid(item.order_date.split(' ')[0]) }}
            </span>
            <small class="text-disabled">
              {{ item.order_date.split(' ')[1] || '' }}
            </small>
          </div>
        </template>
        <template #item.delivery_date="{ item }">
          <VRow v-if="item.shipping">
            <VCol cols="12" md="3" class="d-flex align-center">
              <VAvatar title="con envio" icon="ri-truck-line" class="text-info mr-2" variant="tonal"  size="40"/>
            </VCol>
            <VCol cols="12" md="3" class="d-flex align-center"><strong >{{ getDate(item.delivery_date) }}</strong>
            </VCol>
          </VRow>
        </template>
        <template #item.total_cost="{ item }">                      
          <div class="text-right text-error">
            <strong>{{ formatCurrency(item.total_cost) }}</strong>
          </div>
        </template>
        <template #item.total_profit="{ item }">                      
          <div class="text-right text-success">
            <strong>{{ formatCurrency(item.total_profit) }}</strong>
          </div>
        </template>
        <template #item.customer="{ item }">                                
          <strong>{{ getCustomer(item.customer) }}</strong>          
        </template>
        <template #item.total_amount="{ item }">                      
          <div :class=" (item.total_profit>0)? 'text-right text-success':'text-right text-error'">
            <strong>{{ formatCurrency(item.total_amount) }}</strong>
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
                <VListItem @click="showRemito(item)">
                  <VListItemTitle>
                    <IconBtn
                      size="small"
                      class="my-1"
                      title="Remito">
                      <VIcon icon="ri-file-pdf-2-line" />
                    </IconBtn>Remito
                  </VListItemTitle>
                </VListItem>
                
                <VListItem @click="showRemitoComanda(item)">                  
                  <VListItemTitle>
                    <IconBtn
                      size="small"
                      class="my-1"
                      title="Comanda">
                      <VIcon icon="ri-file-pdf-2-line" />
                    </IconBtn>Comanda
                  </VListItemTitle>                  
                </VListItem>
                <VListItem @click="openMovementDialog(item)">                  
                  <VListItemTitle>
                    <IconBtn
                      size="small"
                      class="my-1"
                      title="Cambiar el estado de la orden"
                      @click="openMovementDialog(item)">
                      <VIcon icon="ri-swap-box-line" />
                    </IconBtn>Estado
                  </VListItemTitle>                                                                
                </VListItem>
                
                <VListItem 
                  v-if="!isPaid(item)"
                  @click="openPaymentForm(item)"
                >
                  <VListItemTitle>
                    <IconBtn
                      size="small"
                      class="my-1"
                      title="Registrar Pago"
                      @click="openPaymentForm(item)">
                      <VIcon icon="ri-money-dollar-circle-line" />
                    </IconBtn>Registrar Pago
                  </VListItemTitle>                                                                
                </VListItem>                
              </VList>
            </VMenu>
          </div>
        </template>        
      </VDataTable>
      
      

      <VDialog v-model="movementDialog" max-width="80%">
        <VCard>
          <VToolbar color="primary">
            <VBtn
              icon="ri-close-line"
              color="white"
              @click="closeMovementDialog"
            />
            <VToolbarTitle>{{ movementFormTitle }}</VToolbarTitle>
            <VSpacer />
          </VToolbar>
          <VForm ref="movementForm" v-model="validMovement">
            <VCardText>
              <VContainer>
                <!-- Sección de información del cliente -->
                <VCard class="mb-6" color="grey-lighten-4">
                  <VCardTitle class="d-flex align-center">
                    <VIcon icon="ri-user-line" class="me-2" />
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
                    <VIcon icon="ri-inbox-unarchive-line" class="me-2" />
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
                            {{ (selectedOrder.order_date) }}
                          </span>
                          
                          <!--
                          <div class="d-flex flex-column">
                            <span class="font-weight-medium">
                              {{ formatDateGrid(selectedOrder.order_date.split(' ')[0]) }}
                            </span>
                            <small class="text-disabled">
                              {{ selectedOrder.order_date.split(' ')[1] || '' }}
                            </small>
                          </div>
                          -->
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
      </VDialog>
      
      <!-- Dialog Registrar pagos -->
      <VDialog v-model="paymentFormDialog" max-width="1000px">
        <VCard>
          <VToolbar color="success">
            <VBtn icon="ri-close-line" color="white" @click="closePaymentForm" />
            <VToolbarTitle>Registrar Pago</VToolbarTitle>
          </VToolbar>
          <VForm ref="paymentForm" v-model="validPayment">
            <VCardText>                                            
              <!-- aca debe ir la linea de pagos -->
              <VRow class="justify-center">                
                <VCol cols="12" md="12" sm="12">
                  <VCard>     
                    <div class="v-card-item"> 
                      <div class="v-card-item__content">
                        <div class="v-card-title">
                          <h5 class="text-h5">
                            <VAvatar icon="ri-money-dollar-circle-line" class="text-success mr-2" variant="tonal"/>Pagos
                          </h5>
                        </div> 
                      </div> 
                    </div>                    
                    <VCardText>     
                      <VRow>
                        <VCol cols="12">
                          <VAlert type="success" variant="tonal" class="mb-4" :icon="false">
                            <VRow>
                              <VCol cols="12" sm="9" class=" text-end text-h5 text-success">
                                Total de la Orden: 
                              </VCol>
                              <VCol cols="12" sm="3" class=" text-end text-h5 text-success">
                                {{ formatCurrency(createdOrder?.order?.total_amount) }}
                              </VCol>
                            </VRow>
                            <VRow>
                              <VCol cols="12" sm="9" class=" text-end pt-0 text-h5 text-success">
                                Total Pagado:
                              </VCol>
                              <VCol cols="12" sm="3" class=" text-end pt-0 text-h5 text-success">
                                {{ formatCurrency(totalPaid) }}
                              </VCol>
                            </VRow>
                            <VRow class="pb-2">
                              <VCol cols="12" sm="9" class=" text-end pt-0 text-h5 text-success">
                                Saldo Pendiente:
                              </VCol>

                              <VCol cols="12" sm="3" class=" text-end pt-0  text-h5 text-success">
                                {{ formatCurrency(pendingAmount) }}
                              </VCol>
                            </VRow>                            
                            <VDivider 
                              :thickness="3" 
                              color="success" 
                            />                            
                            <VRow class="pt-4">
                              <VCol cols="12" sm="9" class=" text-end pt-0 text-h5 text-success">
                                Vuelto:
                              </VCol>

                              <VCol cols="12" sm="3" class=" text-end pt-0  text-h5 text-success">
                                {{ formatCurrency(change) }}
                              </VCol>
                            </VRow>
                          </VAlert>
                        </VCol>
                      </VRow>
                      <PaymentsRow                        
                        ref="paymentsRow"
                        :key="keyPayments"
                        modulo="pagos"                        
                        @update-total="updateTotal"
                      />
                    </VCardText>
                  </VCard>               
                </VCol>
              </VRow>
            </VCardText>
            <VCardActions>
              <VSpacer />
              <VBtn variant="outlined" color="success" @click="closePaymentForm"> Cancelar </VBtn>
              <VBtn class="bg-success" color="white" @click="savePayment" :loading="savingPayment">
                Registrar Pago
              </VBtn>
            </VCardActions>
          </VForm>
        </VCard>
      </VDialog>
      

      <v-snackbar v-model="snackbar" :color="snackbarColor" :timeout="3000">
        {{ snackbarText }}
        <template v-slot:action="{ attrs }">
          <VBtn text v-bind="attrs" @click="snackbar = false">
            Cerrar
          </VBtn>
        </template>
      </v-snackbar>
    </VCardText>
    <VCardActions class="justify-end  custom-actions"> <!-- Clase clave -->
      <VBtn variant="outlined" color="success" @click="getDeliveryReport(1)">
        Reporte de Entregas
      </VBtn>
      <VBtn variant="outlined" color="warning" @click="getDeliveryReport(2)">
        Reporte de Entregas Cliente
      </VBtn>
    </VCardActions>    
  </VCard>
</template>

<script>
import DateRangeField from '@/components/DateRangeField.vue';
import { mapGetters } from 'vuex';
//import { debounce } from 'lodash';

export default {
  components: { DateRangeField },
  data() {   
    return {
      keyPayments: 1,
      validPayment: false,
      savingPayment: false,
      change: 0,
      totalPaid: 0,
      
      // Payment data
      payment: {
        amount: 0,
        payment_method_id: null,
        notes: "",
      },      
      dateRange: {
        start: null,
        end: null,
      },
      dateOrderRange: {
        start: null,
        end: null,
      },                  
      loading: false,
      search: '',
      customers: [],
      //orders: [],                  
      statuses: [],
      selectedCustomer: null,
      selectedStatus: null,      
      selectedOrder: null,            

      // Dialogs            
      movementDialog: false,      
      paymentFormDialog: false,
      
      // Form validations
      validMovement: false,      
      
      // Loading states
      savingMovement: false,      
      
      // Forms data
      movement: {
        status_id: null,
      }, 

      // Snackbar
      snackbar: false,
      snackbarText: '',
      snackbarColor: 'success',
      
      // Headers
      headers: [
        { title: 'Acciones', key: 'actions', sortable: false, align: 'center' },
        { title: 'Orden', key: 'order_number' }, 
        { title: 'Fecha', key: 'order_date', align: 'center' },           
        { title: 'Fecha Entrega', key: 'delivery_date', align: 'center'  },           
        { title: 'Cliente', key: 'customer' },         
        { title: 'Total', key: 'total_amount', align: 'end' },
         
        { title: 'Estado', key: 'status.name' },                        
      ],            
      isAdmin: false,
      createdOrder: { order: {} },
      pagination: {
        page: 1,
        itemsPerPage: 10,
        sortBy: [],
        sortDesc: [],
      },
      orders: {
        data: [],
        total: 0,
        current_page: 1,
        last_page: 1,
      },
      //searchDebounce: null, // Para el debounce de búsqueda
    }
  },

  computed: {
    ...mapGetters({
      userIsAdmin: 'isAdmin',  // Mapea `isAdmin` del store a `userIsAdmin` en el componente
    }),
    showHeaders() {
      return this.headers
    },         

    pendingAmount()
    {      
      return this.createdOrder ? (this.totalPaid<this.createdOrder.order.total_amount)? this.createdOrder.order.total_amount - this.totalPaid : 0:0
    },

    movementFormTitle() {
      return this.selectedOrder 
        ? `Cambio de estado de orden ${this.selectedOrder.order_number}`
        : 'Cambio de estado de orden'
    },   
  },
  /*
  watch: {
    // Observadores para los filtros
    search(newVal) {
      this.pagination.page = 1; // Resetear a primera página al buscar
      this.debouncedFetchData();
    },
    selectedCustomer(newVal) {
      this.pagination.page = 1;
      this.fetchData();
    },
    selectedStatus(newVal) {
      this.pagination.page = 1;
      this.fetchData();
    },
    dateRange: {
      handler(newVal) {
        if(newVal.start && newVal.end){
          this.pagination.page = 1;
          this.fetchData()
        }
      },
      deep: true,
    },
    dateOrderRange: {
      
      handler(newVal) {
        if(newVal.start && newVal.end){
          this.pagination.page = 1;
          this.fetchData()
        }        
      },
      deep: true,
    }
  },*/

  async created() {
    this.checkAdmin()  
    if(this.isAdmin){
      this.headers.splice(4, 0, 
        { title: 'Costo', key: 'total_cost', align: 'end' },
        { title: 'Ganancia', key: 'total_profit', align: 'end' }
      )
    }
    // Configurar debounce para la búsqueda (300ms de espera)
    //this.debouncedFetchData = debounce(this.fetchData, 300);
    
    await this.loadData();    
  },

  methods:{
    reset(){
      this.selectedCustomer = null
      this.selectedStatus = null
      this.search = ''
      this.pagination.page = 1
      this.dateRange = {
        start: null,
        end: null,
      }
      this.dateOrderRange = {
        start: null,
        end: null,
      }

      // 2. Resetear el componente DateRangeField
      this.$nextTick(() => {
        if (this.$refs.dateDeliveryRange && this.$refs.dateDeliveryRange.reset) {
          this.$refs.dateDeliveryRange.reset()
        }
        if (this.$refs.dateOrderRange && this.$refs.dateOrderRange.reset) {
          this.$refs.dateOrderRange.reset()
        }
      })
      this.fetchData()
    },
    handlePaginationChange(options) {
      this.pagination.page = options.page;
      this.pagination.itemsPerPage = options.itemsPerPage;
      this.pagination.sortBy = options.sortBy;
      this.pagination.sortDesc = options.sortDesc;
      this.fetchData();
    },

    async fetchData() {
      this.loading = true;
      try {
        console.log("this.selectedCustomer");
        console.log(this.selectedCustomer);
        const params = {
          order_number: this.search || undefined,
          customers: this.selectedCustomer || undefined,
          status_id: this.selectedStatus || undefined,
          page: this.pagination.page,
          per_page: this.pagination.itemsPerPage,
          sort_by: this.pagination.sortBy.length ? this.pagination.sortBy[0] : undefined,
          sort_order: this.pagination.sortDesc?.length ? (this.pagination.sortDesc[0] ? 'desc' : 'asc') : undefined,
        };
        
        // Añadir fechas si están definidas
        if (this.dateRange?.start && this.dateRange?.end) {
          params.delivery_start_date = this.dateRange.start;
          params.delivery_end_date = this.dateRange.end;
        }
        // Añadir fechas si están definidas
        if (this.dateOrderRange?.start && this.dateOrderRange?.end) {
          params.order_start_date = this.dateOrderRange.start;
          params.order_end_date = this.dateOrderRange.end;
        }

        
        
        // Eliminar parámetros undefined
        Object.keys(params).forEach(key => params[key] === undefined && delete params[key]);
        
        const [ordersRes] = await Promise.all([
          this.$axios.get(this.$routes["ordersSearch"], { params }),                    
        ]);
        
        //this.orders = ordersRes.data.data; 
        this.orders = {
          data: ordersRes.data.data,
          total: ordersRes.data.total,
          current_page: ordersRes.data.current_page,
          last_page: ordersRes.data.last_page
        };
        
        // Eliminamos el filteredStock ya que ahora el filtrado lo hace el backend
      } catch (error) {
        console.error('Error fetching data:', error);
        this.showSnackbar('Error al cargar los datos', 'error');
      } finally {
        this.loading = false;
      }
    },

    async loadData() {
      this.loading = true;
      try {                
        const [customersRes, statusesRes] = await Promise.all([          
          this.$axios.get(this.$routes["customers"]),          
          this.$axios.get(this.$routes["statuses"]), 
        ]);
                
        this.customers = customersRes.data.data;
        this.statuses = statusesRes.data.data;
                
      } catch (error) {
        console.error('Error fetching data:', error);
        this.showSnackbar('Error al cargar los datos', 'error');
      } finally {
        this.loading = false;
      }
    },

    // Payment methods
    openPaymentForm(item) {
      this.keyPayments=+1
      this.createdOrder.order = item        
      this.paymentFormDialog = true
    },
    closePaymentForm() {
      this.createdOrder.order = {}
      this.paymentFormDialog = false      
    },
    updateTotal(newTotal) {      
      this.totalPaid = newTotal
      this.change = (this.totalPaid>this.createdOrder.order.total_amount)? this.totalPaid - this.createdOrder.order.total_amount:0
    },
    async  validatePayments(payments) {                        
      if(payments.length<=0){
        return "Debe incluir al menos una modalidad de pago";        
      }

      const paymentMethod = new Set();
      const total = payments.reduce((acc, payment) => acc + parseFloat(payment.amount), 0);
      /*if (total > this.pendingAmount) {        
        return "El total excede el saldo pendiente";
      }*/

      for (const payment of payments) {                
        if((!payment.amount)||(!payment.payment_method_id)){
          return "Verifique la informacion de los pagos";
        }
        // 1. Verificar paymentMethod duplicados
        if (paymentMethod.has(payment.payment_method_id.id)) {
          return 'Ha seleccionado mas de una vez el mismo metodo de pago. Metodo de pago: '+payment.payment_method_id.name;          
        }
        paymentMethod.add(payment.payment_method_id.id);        
      }       
      
      return true
    },
    async savePayment() {      
      const isValid = await this.validatePayments(this.$refs.paymentsRow.payments);      
          
      if (isValid !== true) return this.showSnackbar(isValid, "error");

      this.savingPayment = true
      try {
        const paymentData = {
          //...this.payment,
          order_id: this.createdOrder.order.id,
          payments: this.$refs.paymentsRow.payments,
        }

        const response = await this.$axios.post(this.$routes["payments"], paymentData);        
        if(response.status == 201){
          this.showSnackbar("Pago registrado exitosamente", "success");     
          this.closePaymentForm();
          this.fetchData();
        }        
      } catch (error) {
        console.error("Error saving payment:", error);
        this.showSnackbar("Error al registrar el pago", "error");
      } finally {
        this.savingPayment = false
      }
    },
    //end payments methods
    checkAdmin() {
      this.isAdmin = this.userIsAdmin      
    },
    validateStatusChange(value){
      if (!value) return 'Debe seleccionar un estado'
      if (value === this.selectedOrder.status.id) return 'El estado seleccionado es igual al estado actual'

      return true
    },
    
    // Convierte una fecha (string o Date) al inicio del día (00:00:00)
    normalizeDateToStartOfDay(date) {
      const d = new Date(date);
      d.setHours(0, 0, 0, 0); // Asegura 00:00:00.000

      return d;
    },

    // Convierte una fecha (string o Date) al final del día (23:59:59.999)
    normalizeDateToEndOfDay(date) {
      const d = new Date(date);
      d.setHours(23, 59, 59, 999); // Asegura 23:59:59.999

      return d;
    },

    // Parsea "dd/mm/yyyy" a objeto Date válido
    parseDdMmYyyyToDate(dateString) {
      if (!dateString) return null;

      // Elimina la parte de la hora si existe (ej: "16/07/2025 00:00:00" => "16/07/2025")
      const datePart = dateString.split(' ')[0]; 
      const [day, month, year] = datePart.split('/').map(Number);

      // Valida que day, month y year sean números válidos
      if (isNaN(day) || isNaN(month) || isNaN(year)) return null;

      // Crea la fecha en UTC para evitar problemas de zona horaria
      return new Date(Date.UTC(year, month - 1, day)); // ¡Los meses en JS van de 0 a 11!
    },
        
    getCustomer(customer) {
      if (!customer) return 'Cliente no disponible'
      
      return (customer.firstname || customer.lastname) 
        ? `${customer.firstname || ''} ${customer.lastname || ''}`.trim()
        : customer.address || 'Dirección no disponible'
    },
    formatCurrency(value) {
      return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
      }).format(value || 0)
    },    
    

    // Movement Dialog Methods
    openMovementDialog(item) {      
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

    // Utility Methods
    getStatusColor(item) {      
      const statusColorMap = {
        [this.$statusOrders.PENDING]: 'primary',
        [this.$statusOrders.PROCESS]: 'warning',
        [this.$statusOrders.PARTIAL_PAYMENT]: 'warning',
        [this.$statusOrders.PAID]: 'success',
        [this.$statusOrders.COMPLETED]: 'success',
        [this.$statusOrders.APPROVED]: 'success',
      }
      
      return statusColorMap[item.status.code] || 'error'
    },  
    
    isPaid(item) {             
      const paids = [this.$statusOrders.PAID, 
        this.$statusOrders.COMPLETED, 
        this.$statusOrders.REJECTED,
        this.$statusOrders.CANCEL,
        this.$statusOrders.REFUND,
        this.$statusOrders.RETURNED]
      
      return paids.includes(item.status.code)            
    },  
    

    formatDate(date) {
      return new Date(date).toLocaleString()
    },
    formatDateGrid(dateStr){
      if (!dateStr) return ''
      const [date, time] = dateStr.split(' ')
      return `${date} ${time || ''}`
    },
    getDate(date){      
      const dateTime = date.toLocaleString(); // Ejemplo: "17/07/2025, 12:30:45"
      const onlyDate = dateTime.split(' ')[0]; // Obtiene todo antes de la coma

      return onlyDate; // "17/07/2025"    
    },
    showSnackbar(text, color) {
      this.snackbarText = text
      this.snackbarColor = color
      this.snackbar = true
    },
    async getDeliveryReport(type) {
      try {        
        // Mostrar loader mientras se genera el PDF
        this.loading = true

        const urlReport = (type == 1)?`${this.$routes["ordersDelivery"]}`:`${this.$routes["ordersCustomersDelivery"]}`

        const params = {
          start_date: this.dateRange.start, // Formato: YYYY-MM-DD
          end_date: this.dateRange.end,
          status_id: this.selectedStatus,          
          customers: this.selectedCustomer,          
        }
        
        // Llamar al endpoint de Laravel que genera el PDF
        const response = await this.$axios.get(
         urlReport,
          { params,
            responseType: 'blob',
          },
        )
                 
        var blob = new Blob([response.data], { type: "application/pdf" })
        const url = window.URL.createObjectURL(blob, { oneTimeOnly: true })
        const link = document.createElement("a")
        link.target = "_blank"

        link.href = url

        document.body.appendChild(link);
        link.click();
    
        
      } catch (error) {
        console.error('Error generando reporte:', error	);
        this.showSnackbar('Error al generar la reporte', 'error');
      } finally {
        this.loading = false;
      }
    },
    async showRemito(item) {
      try {
        // Mostrar loader mientras se genera el PDF
        this.loading = true;
        
        // Llamar al endpoint de Laravel que genera el PDF
        const response = await this.$axios.get(
          `${this.$routes["orders"]}/remito/${item.id}`,
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
    async showRemitoComanda(item) {
      try {
        // Mostrar loader mientras se genera el PDF
        this.loading = true;
        
        // Llamar al endpoint de Laravel que genera el PDF
        const response = await this.$axios.get(
          `${this.$routes["orders"]}/remito-comanda/${item.id}`,
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
.custom-actions {
  padding: 8px 16px; /* Ajusta el espaciado interno si es necesario */
}
</style>
