<template> 
  <VForm :key="keyOrderForm" ref="orderForm" v-model="validOrder">
   
    <VRow>
      
        
      <VCol cols="12"  sm="12" md="6">
        <VCard>
          <VCardText class="mb-0 pb-2">
            <VRow class="mb-0 pb-0" >
                <VCol  v-if="isAdminBebidas" cols="1" md="1" sm="1"  class="mb-0 pt-3 d-none d-sm-flex">
                  <VAvatar icon="ri-user-line" class="text-error mr-2" variant="tonal" size="40"/> 
                </VCol>
                <VCol  v-if="isAdminBebidas" cols="9" sm="9" md="8" class="mb-0 pt-4 ml-3" >
                <VAutocomplete
                  v-model="order.seller_id"
                  :items="sellers"
                  item-title="name"
                  item-value="id"
                  label="Seleccionar Vendedor"
                  :rules="[(v) => !!v || 'Vendedor es requerido']"
                  clearable                                     
                  density="compact"
                  return-object
                 />
              </VCol>                            
            </VRow>            
              <VRow class="mb-0" :class="isAdminBebidas? 'pb-0':'py-8'">
                <VCol cols="1" md="1" sm="1"  class="mb-0 pt-0 d-none d-sm-flex">
                  <VAvatar icon="ri-user-line" class="text-info mr-2" variant="tonal" size="40"/> 
                </VCol>
                <VCol cols="9" sm="9" md="8" class="mb-0 pt-0 ml-3" >
                <VAutocomplete
                  v-model="order.customer_id"
                  :items="customers"
                  item-title="address"
                  item-value="id"
                  label="Seleccionar Cliente"
                  :rules="[(v) => !!v || 'Cliente es requerido']"
                  clearable
                  @update:model-value="onCustomerChange"
                   
                  density="compact"
                  >
                  <template v-slot:item="{ props, item }">
                    <v-sheet border="info md"> 
                    <VListItem v-bind="props">
                   
                      <VListItemSubtitle class="text-caption"><strong>localidad:</strong>{{ item.raw.locality_id}} - <strong>telefono:</strong>{{ item.raw.telephone }}</VListItemSubtitle>
                   
                    </VListItem>
                    </v-sheet>
                  </template>
                </VAutocomplete>
              </VCol>              
              <VCol cols="auto" class="mb-0 pt-0">
                <VBtn @click="openCustomerDialog" :color="$cv('principal')" title="nuevo cliente">
                  <VIcon icon="ri-user-add-line" class="mr" />
                </VBtn>
              </VCol>
            </VRow>
            
          </VCardText>
        </VCard>
      </VCol>


      <VCol cols="12" sm="12" md="6">
        <VCard>
          <VCardText class="mb-0 pb-2">
            <VRow class="mb-0 pb-0" >
              <VCol cols="1" md="1" class="mb-0 pb-0 pt-2 d-none d-sm-flex" >
                <VAvatar icon="ri-map-pin-line" class="text-info mr-2" variant="tonal"/> 
              </VCol>
              <VCol cols="1" md="1" class="mb-0 pt-5 ml-1">
                <VSwitch
                  v-model="order.shipping"
                  :true-value="1"
                  :false-value="0"
                  color="primary"
                  hide-details 
                  title="Requiere Envío" 
                  @click="toggleShipping(order)"
                />
              </VCol>
              <VCol cols="8" sm="8" md="8" class="mb-0 pt-3">
                <VTextField
                  v-model="order.shipping_address"
                  label="Dirección de Entrega"
                  :rules="[(v) => !!v || 'Dirección de entrega es requerida']"
                  :disabled="!order.shipping_address_status"
                  />
              </VCol>
              <VCol cols="auto" md="auto" class="mb-0" >
                <VBtn
                @click="useCustomerAddress"
                :color="$cv('principal')"
                title="Usar del cliente"
              >
                <VIcon icon="ri-refresh-line" class="mr-2" />
              </VBtn>
              </VCol>
               
            </VRow>
            <VRow class="mb-0 pb-2">
              <VCol cols="1" md="1" class="mb-0 pb-0 pt-0 d-none d-sm-flex" >
                <VAvatar icon="ri-truck-line" class="text-info mr-2" variant="tonal"/> 
              </VCol>
              <VCol cols="1" md="1" class=" ml-1">
                 
              </VCol>
              <VCol cols="8" sm="8" md="8" class="mb-0 pt-0">
               
      
                <VTextField
                  v-model="order.delivery_date"
                  label="Fecha de Entrega"
                  placeholder="dd/mm/yyyy"
                  v-mask="'##/##/####'"              
                  clearable
                  @focus="setDate(order.delivery_date)"
                  :disabled="!order.shipping_address_status"
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
            <!-- Agregar Producto -->
            <VRow class="mb-0 pb-0">
              <VCol  class="mb-0 pb-0 pt-4  d-none d-sm-flex flex-grow-0" >
                <VAvatar icon="ri-shopping-cart-line" class="text-info mr-2" variant="tonal" size="40" /> 
              </VCol>
              
              
              <VCol cols="8" md="5"  class="pt-5">
                <VAutocomplete
                  v-model="newItem.product_id"
                  :items="products"
                  item-title="name"
                  item-value="id"
                  
                  label="Seleccionar Producto"
                  clearable
                  @update:model-value="onProductChange"
                  density="compact"
                >
                  <template v-slot:item="{ props, item }">
                    <v-sheet border="info md"> 
                    <VListItem v-bind="props">
                      <!--<VListItemTitle>{{ item.raw.name }}</VListItemTitle>-->
            
                        <VListItemSubtitle class="text-caption" >
                       
                        <VChip :color="getProductStock(item.raw.id) >0 ?'success':'error'">Stock: {{ getProductStock(item.raw.id) }} </VChip>
                      
                        Código: {{ item.raw.code }}   
                        </VListItemSubtitle>
                    
                    </VListItem>
                  </v-sheet>
                      
                  </template>
                </VAutocomplete>
              </VCol>
              <VCol cols="4" md="1" class="pr-0" >
                <div :class="newItem.product_id && showStockWarning(newItem.product_id, newItem.quantity) ? 'bg-warning-2 rounded-lg':''" class="px-2 pt-2">
                  <VTextField
                    v-model="newItem.quantity"
                    label="Cant"
                    type="number"
                    min="1"
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
    
                <div  class="text-warning pt-6"  style="width:15px !important">
                     <VIcon v-if="newItem.product_id && showStockWarning(newItem.product_id, newItem.quantity)" icon="ri-error-warning-fill"  title="Stock Insuficiente"/>
                     
                  </div>
            
              <VCol cols="6" md="2" class="pt-5" >
                <VTextField
                  v-model="newItem.unit_price"
                  label="Precio Unitario"
                  type="number"
                  step="0.01"
                  prefix="$"
                  :rules="[
                    (v) => !!v || 'Precio requerido',
                    (v) => v > 0 || 'Debe ser mayor a 0',
                  ]"
                  density="compact"
                />
              </VCol>
              <VCol cols="6" md="2"  class="pt-5"  >
                <VTextField
                  :model-value="calculateItemTotal()"
                  label="Total"
                  prefix="$"
                  readonly
                  variant="outlined"
                  density="compact"
                  
                />
              </VCol>
              <VCol cols="12" md="1"  class="pt-5" >
                <VBtn   color="primary" @click="addItem" :disabled="!canAddItem" block>
                  <VIcon icon="ri-add-line" />
                </VBtn>
              </VCol>
            </VRow>
             
            <VRow class="my-0">
              <VCol cols="12" sm="12" md="9">
                <VCard> 
                  <div class="v-card-item"> 
                    <div class="v-card-item__content">
                      <div class="v-card-title"><h5 class="text-h5">
                        <VAvatar icon="ri-luggage-cart-line" class="text-info mr-2" variant="tonal"/>Productos agregados</h5></div> 
                    </div> 
                  </div> 
                  <VCardText>
                    <div style="min-height: 280px;max-height: 270px; overflow-y: auto;">
                      <VDataTable
                        :headers="itemHeaders"
                        :items="order.items"
                        class="elevation-1"
                        no-data-text="No hay productos agregados"
                        
                        density="compact"
                      >
                      <template #bottom v-if="!showFooter"></template>
                        <template #item.product_name="{ item }">
                          {{ getProductById(item.product_id)?.name }}
                        </template>
                        <template #item.unit_price="{ item, index }">
                          <VTextField
                            v-model="item.unit_price"
                            type="number"
                            step="0.01"
                            prefix="$"
                            density="compact"
                            hide-details
                            @input="updateItemTotal(index)"
                            class="ma-0 pa-0 text-sm"
                        
                          />
                        </template>
                        <template #item.quantity="{ item, index }">
                          <VTextField
                            v-model="item.quantity"
                            type="number"
                            min="1"
                            density="compact"
                            hide-details
                            @input="updateItemTotal(index)"
                            style="width: 80px;"
                          />
                        </template>
                        <template #item.total_price="{ item }">
                          ${{ formatNumber(item.total_price) }}
                        </template>
                        <template #item.actions="{ index }">
                          <IconBtn size="small" color="error" @click="removeItem(index)">
                            <VIcon icon="ri-delete-bin-line" />
                          </IconBtn>
                        </template>
                      </VDataTable>
                    </div>
                  </VCardText>
                </VCard>

              </VCol>

              <VCol cols="12" sm="12" md="3">
                <VCard> 
                  <div class="v-card-item"> 
                    <div class="v-card-item__content">
                      <div class="v-card-title"><h5 class="text-h5">
                        <VAvatar   icon="ri-calculator-line" class="text-info mr-2" variant="tonal"/>Totales</h5></div> 
                    </div> 
                  </div> 
                  <VCardText>
                    <VRow>
                      <VCol cols="12" md="12">
                        <VTextField
                          v-model="order.discount_amount"
                          label="Descuento"
                          type="number"
                          step="0.01"
                          prefix="$"
                          @input="calculateTotals"
                          density="compact"
                          class="number-end"
                        />
                      </VCol>
                      <VCol cols="12" md="12">
                        <VTextField
                          v-model="order.tax_amount"
                          label="Impuestos"
                          type="number"
                          step="0.01"
                          prefix="$"
                          @input="calculateTotals"
                          density="compact"
                          class="number-end"
                        />
                      </VCol>
                    </VRow>
                    <VDivider class="my-4" />
                    <VRow>
                      <VCol cols="12" md="12">
                        <VTextField
                          :model-value="formatCurrency(order.subtotal)"
                          label="Subtotal" 
                          readonly
                          variant="outlined"
                          density="compact" 
                          class="custom-bg-gray number-end"
                         
                        />
                      </VCol>
                      <VCol cols="12" md="12">
                        <VTextField
                          :model-value="formatCurrency(order.discount_amount)"
                          label="Descuento"
                          readonly
                          variant="outlined"
                          density="compact"
                          class="custom-bg-gray number-end"
                        >
                        <template #input="{ props }">
                          <input
                            v-bind="props"
                            class="text-right"
                            style="text-align: right;"
                          />
                        </template></VTextField>

                      </VCol>
                      <VCol cols="12" md="12">
                        <VTextField
                          :model-value="formatCurrency(order.total_amount)"
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
    
    <VRow>           
      <VCol cols="12" sm="12"  md="12">
        <VCard>          
          <div class="v-card-item"> 
            <div class="v-card-item__content">
              <div class="v-card-title"><h5 class="text-h5">
                <VAvatar   icon="ri-file-text-line" class="text-info mr-2" variant="tonal"/>Notas Adicionales</h5></div> 
            </div> 
          </div> 
          <VCardText>
            <VTextarea v-model="order.notes" label="Notas de la orden" rows="2" style="height:80px" />
          </VCardText>
        </VCard>
      </VCol>
      
    </VRow>
    <!-- Sección Totales -->

    <!-- Notas -->

    <!-- Botones de Acción -->
    <VRow>
      <VCol cols="12" class="text-center">
        <VBtn color="primary" variant="outlined" class="mr-4" @click="$router.go(-1)">
          Cancelar
        </VBtn>
        <VBtn color="primary"  class="mr-4" @click="$router.push({'path':'order-list'})">
          Ordenes
        </VBtn>
        <VBtn
          color="primary"
          @click="createOrder"
          :loading="creatingOrder"
          :disabled="!canCreateOrder"
        >
          <VIcon icon="ri-save-line" class="mr-2" />
          Crear Orden
        </VBtn>
      </VCol>
    </VRow>
  </VForm>

  <!-- Dialog Nuevo Cliente -->
  <VDialog v-model="customerDialog" max-width="600px">
    <VCard>
      <VToolbar color="primary">
        <VBtn icon="ri-close-line" color="white" @click="closeCustomerDialog" />
        <VToolbarTitle>Nuevo Cliente</VToolbarTitle>
      </VToolbar>
      <VForm ref="customerForm" v-model="validCustomer">
        <VCardText>
          <VContainer>
            <VRow>
              <VCol cols="12" sm="6">
                <VTextField
                  v-model="newCustomer.address"
                  label="Dirección"
                  :rules="[(v) => !!v || 'Dirección es requerida']"
                  density="compact"
                />
              </VCol>
              <VCol cols="12" sm="6">
                <VTextField
                  v-model="newCustomer.phone"
                  label="Teléfono"
                  :rules="[(v) => !!v || 'Teléfono es requerido']"
                  density="compact"
                />
              </VCol>

              <VCol cols="12" sm="6">
                <VTextField
                  v-model="newCustomer.name"
                  label="Nombre"
                  density="compact"
                />
              </VCol>
              <VCol cols="12" sm="6">
                <VAutocomplete
                      v-model="newCustomer.locality_id"
                      :items="localities"
                      item-title="name"
                      item-value="id"
                      label="Localidad"
                      clearable
                      density="compact"
                    />
              </VCol>
              
              
              <VCol cols="12" sm="6">
                <VTextField v-model="newCustomer.email" label="Email" density="compact"/>
              </VCol>
            </VRow>
          </VContainer>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="outlined" color="primary" @click="closeCustomerDialog">
            Cancelar
          </VBtn>
          <VBtn color="white" class="bg-primary" @click="saveCustomer" :loading="savingCustomer">
            Guardar Cliente
          </VBtn>
        </VCardActions>
      </VForm>
    </VCard>
  </VDialog>

  <!-- Dialog Confirmar Pago -->
  <VDialog v-model="paymentDialog" max-width="500px">
    <VCard>
      <VToolbar color="success">
        <VBtn icon="ri-close-line" color="white" @click="paymentDialog = false" />
        <VToolbarTitle>¿Registrar Pago?</VToolbarTitle>
      </VToolbar>
      <VCardText>
        <VAlert type="success" variant="tonal" class="mb-4 mt-4 text-center " :icon="false">
         <p class="text-h5 text-success my-0"> ¡Orden creada exitosamente!</p>
        </VAlert>
        <VAlert type="success" variant="tonal" class="mb-4 mt-4" :icon="false"> 
          <VRow class="py-0 my-0">
            <VCol cols="12" sm="6" class=" text-end text-h5 text-success" >
              Número de Orden:
            </VCol>

              <VCol cols="12" sm="6" class=" text-end text-h5 text-success">
                {{ createdOrder?.order?.order_number }}
              </VCol>
          </VRow>
          <VRow class="py-0 my-0">
            <VCol cols="12" sm="6" class=" text-end text-h5 text-success">
             Total:
            </VCol>

              <VCol cols="12" sm="6" class=" text-end text-h5 text-success">
               {{ formatCurrency(createdOrder?.order?.total_amount) }}
              </VCol>
          </VRow>
           
        </VAlert>
        <p class="text-center text-h5">¿Deseas registrar un pago para esta orden?</p>
      </VCardText>
      <VCardActions>
        <VSpacer />
        <VBtn variant="outlined" color="success" @click="finishOrder"> No, Finalizar </VBtn>
        <VBtn class="bg-success" color="white" @click="openPaymentForm"> Sí, Registrar Pago </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>

  <!-- Dialog Registrar Pago -->
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
                  <v-divider :thickness="3" color="success"></v-divider>
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
                          modulo="pagos"                          
                          :key="keyPayments"
                          @update-total="updateTotal"
                    ></PaymentsRow>
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

  <!-- Snackbar -->
  <VSnackbar v-model="snackbar" :color="snackbarColor" :timeout="4000">
    {{ snackbarText }}
    <template v-slot:action="{ attrs }">
      <VBtn text v-bind="attrs" @click="snackbar = false"> Cerrar </VBtn>
    </template>
  </VSnackbar>
</template>

<script>
import { VAvatar, VCardItem, VTextField } from "vuetify/lib/components/index.mjs";
import PaymentsRow from "@/components/PaymentsRow.vue";
import { mapGetters } from 'vuex';

export default {
  data() {
    return {
      keyOrderForm: 1,
      showFooter: false,
      validOrder: false,
      validCustomer: false,
      validPayment: false,

      // Loading states
      creatingOrder: false,
      savingCustomer: false,
      savingPayment: false,

      // Dialogs
      customerDialog: false,
      paymentDialog: false,
      paymentFormDialog: false,

      // Data
      customers: [],
      products: [],
      stock: [],
      sellers: [],
      //paymentMethods: [],

      // Order data
      order: {
        customer_id: null,
        shipping_address: "",
        subtotal: 0,
        tax_amount: 0,
        discount_amount: 0,
        total_amount: 0,
        notes: "",
        items: [],
        shipping:0,
        shipping_address_status:false
      },

      // New item being added
      newItem: {
        product_id: null,
        quantity: 1,
        unit_price: 0,
        unit_cost_price: 0,
      },

      // New customer
      newCustomer: {
        name: "",
        email: "",
        telephone: "",
        locality_id:"",
        address: "",
      },

      // Payment data
      payment: {
        amount: 0,
        payment_method_id: null,
        notes: "",
      },

      // Created order and payments
      createdOrder: null,      

      // Snackbar
      snackbar: false,
      snackbarText: "",
      snackbarColor: "success",

      // Table headers
      itemHeaders: [
        { title: "", key: "actions", width: "25px", sortable: false },
        { title: "Producto", key: "product_name", width: "300px" },
        { title: "Cantidad", key: "quantity", width: "80px" },
        { title: "Precio Unit.", key: "unit_price", width: "150px" },
        { title: "Total", key: "total_price", width: "150px", align: "end" },
       
      ],
      localities: [],
      keyPayments: 0,
      change: 0,
      totalPaid: 0,
      isAdmibBebidas: false,
    };
  },

  computed: {
    ...mapGetters({
      //userIsAdmin: 'isAdmin',  // Mapea `isAdmin` del store a `userIsAdmin` en el componente
    }),
    selectedCustomer() {
      return this.customers.find((c) => c.id === this.order.customer_id);
    },

    canAddItem() {
      return (
        this.newItem.product_id &&
        this.newItem.quantity > 0 &&
        this.newItem.unit_price > 0 /*&&
        this.newItem.quantity <= this.getProductStock(this.newItem.product_id)*/
      );
    },

    canCreateOrder() {
      return (
        this.order.customer_id &&
        this.order.shipping_address &&
        this.order.items.length > 0
      );
    },

    

    pendingAmount() {      
      return this.createdOrder ? (this.totalPaid<this.createdOrder.order.total_amount)? this.createdOrder.order.total_amount - this.totalPaid : 0:0;
    },   
  },

  async created() {
    await this.loadData();
  },

  methods: {
    setDate(value){
      if(value) return
      let tomorrow = this.getDateTimeTomorrow()

      this.order.delivery_date = tomorrow
    },  
    updateTotal(newTotal) {      
      this.totalPaid = newTotal
      this.change = (this.totalPaid>this.createdOrder.order.total_amount)? this.totalPaid - this.createdOrder.order.total_amount:0;
    },
    async loadData() {
      try {
        this.isAdminBebidas = this.$is(["bebidas-admin"]);
        //this.isAdmin = this.userIsAdmin;

        const [
          customersRes,
          productsRes,
          stockRes,                
          localitiesRes,
        ] = await Promise.all([
          this.$axios.get(this.$routes["customers"]),
          this.$axios.get(this.$routes["products"]),
          this.$axios.get(this.$routes["stocks"]),                    
          this.$axios.get(this.$routes["localities"]),
        ]);

        if(this.isAdminBebidas){
          const usersRes = await this.$axios.get(this.$routes["usersList"]);

          this.sellers = usersRes.data.data || usersRes.data; 
        }

        this.customers = customersRes.data.data || customersRes.data;
        this.products = productsRes.data.data || productsRes.data;
        this.stock = stockRes.data.data || stockRes.data;         
        this.localities = localitiesRes.data.data || localitiesRes.data;

        if(this.$is("bebidas-admin") || this.$is("bebidas-user")) 
        {
          this.order.shipping=1
        }
          this.setDate(this.order.delivery_date);

        
      } catch (error) {
        console.error("Error loading data:", error);
        this.showSnackbar("Error al cargar los datos", "error");
      }
    },

    onCustomerChange() {
      if (this.selectedCustomer) {
        this.order.shipping_address = this.selectedCustomer.address;
      }
    },

    useCustomerAddress() {
      if (this.selectedCustomer) {
        this.order.shipping_address = this.selectedCustomer.address;
      }
    },

    onProductChange() {
      if (this.newItem.product_id) {
        const product = this.getProductById(this.newItem.product_id);
        if (product) {
          this.newItem.unit_price = parseFloat(product.sale_price || 0);
          this.newItem.unit_cost_price = parseFloat(product.purchase_price || 0);
        }
      }
    },

    getProductById(productId) {
      return this.products.find((p) => p.id === productId);
    },

    getProductStock(productId) {
      const stockItem = this.stock.find((s) => s.product_id === productId);
      return stockItem
        ? parseFloat(stockItem.quantity) - parseFloat(stockItem.reserved_quantity)
        : 0;
    },
    showStockWarning(productId, quantity) {
      return quantity > this.getProductStock(productId);
    },

    calculateItemTotal() {
      return this.newItem.quantity * this.newItem.unit_price;
    },

    addItem() {
      if (!this.canAddItem) return;

      const product = this.getProductById(this.newItem.product_id);
      const totalPrice = this.newItem.quantity * this.newItem.unit_price;
      const totalCost = this.newItem.quantity * this.newItem.unit_cost_price;

      this.order.items.push({
        product_id: this.newItem.product_id,
        quantity: this.newItem.quantity,
        unit_price: this.newItem.unit_price,
        unit_cost_price: this.newItem.unit_cost_price,
        total_price: totalPrice,
        total_profit: totalPrice - totalCost,
      });

      // Reset new item
      this.newItem = {
        product_id: null,
        quantity: 1,
        unit_price: 0,
        unit_cost_price: 0,
      };

      this.calculateTotals();
    },

    removeItem(index) {
      this.order.items.splice(index, 1);
      this.calculateTotals();
    },

    updateItemTotal(index) {
      const item = this.order.items[index];
      item.total_price = item.quantity * item.unit_price;
      item.total_profit = item.total_price - item.quantity * item.unit_cost_price;
      this.calculateTotals();
    },

    calculateTotals() {
      this.order.subtotal = this.order.items.reduce(
        (sum, item) => sum + parseFloat(item.total_price),
        0
      );
      this.order.total_amount =
        this.order.subtotal +
        parseFloat(this.order.tax_amount || 0) -
        parseFloat(this.order.discount_amount || 0);
    },

    // Customer methods
    openCustomerDialog() {
      this.customerDialog = true;
    },

    closeCustomerDialog() {
      this.customerDialog = false;
      this.newCustomer = {
        name: "",
        email: "",
        phone: "",
        document: "",
        address: "",
      };
    },

    async saveCustomer() {
      const isValid = await this.$refs.customerForm.validate();
      if (!isValid) return;

      this.savingCustomer = true;
      try {
        const response = await this.$axios.post(
          this.$routes["customers"],
          this.newCustomer
        );
        const newCustomer = response.data.data || response.data;

        this.customers.push(newCustomer);
        this.order.customer_id = newCustomer.id;
        this.order.shipping_address = newCustomer.address;

        this.showSnackbar("Cliente creado exitosamente", "success");
        this.closeCustomerDialog();
      } catch (error) {
        console.error("Error saving customer:", error);
        this.showSnackbar("Error al crear el cliente", "error");
      } finally {
        this.savingCustomer = false;
      }
    },

    // Order methods
    async createOrder() {
      const isValid = await this.$refs.orderForm.validate();
      if (!isValid) return;

      this.creatingOrder = true;
      try {
        const orderData = {
          ...this.order,

          //order_date: new Date().toISOString(), //lo obtenemos en el back
          quantity_products: this.order.items.reduce(
            (sum, item) => sum + parseInt(item.quantity),
            0
          ),
          total_cost: this.order.items.reduce(
            (sum, item) => sum + item.quantity * item.unit_cost_price,
            0
          ),
          total_profit: this.order.items.reduce(
            (sum, item) => sum + parseFloat(item.total_profit),
            0
          ),
        };


        const response = await this.$axios.post(this.$routes["orders"], orderData);

        this.createdOrder = response.data.data || response.data;

        //this.showSnackbar("Orden creada exitosamente", "success");
        this.paymentDialog = true;
      } catch (error) {
        console.error("Error creating order:", error);
        this.showSnackbar("Error al crear la orden", "error");
      } finally {
        this.creatingOrder = false;
      }
    },

    // Payment methods
    openPaymentForm() {
      this.paymentDialog = false;
      
      this.paymentFormDialog = true;
    },

    closePaymentForm() {
      this.paymentFormDialog = false;
      this.clearForm();
      /*this.payment = {
        amount: 0,
        payment_method_id: null,
        notes: "",
      };*/
    },

    clearForm(){      
      this.order = {
        customer_id: null,
        shipping_address: '',
        subtotal: 0,
        tax_amount: 0,
        discount_amount: 0,
        total_amount: 0,
        notes: '',
        items: [],
      };
      this.order.shipping_address = '';

      // Paso 2: Forzar limpieza de validaciones
      this.$refs.orderForm.resetValidation();

      // Paso 3 (opcional): Retrasar un poco el reset para evitar que la validación se dispare
      // por el cambio reactivo inmediato
      this.$nextTick(() => {
        this.$refs.orderForm.resetValidation();
      });

      this.loadData();

      
      
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
      
      return true;
    },

    async savePayment() {
      //console.log("this.$refs.payments.personas");
      //console.log(this.$refs.paymentsRow.payments);
      const isValid = await this.validatePayments(this.$refs.paymentsRow.payments);      
          
      if (isValid !== true) return this.showSnackbar(isValid, "error");

      this.savingPayment = true;
      try {
        const paymentData = {
          //...this.payment,
          order_id: this.createdOrder.order.id,
          payments: this.$refs.paymentsRow.payments,
        };

        const response = await this.$axios.post(this.$routes["payments"], paymentData);
        console.log(response);
        if(response.status == 201){
          this.showSnackbar("Pago registrado exitosamente", "success");
          this.finishOrder();
        }
        /*
        const newPayment = response.data.data || response.data;

        this.orderPayments.push(newPayment);

        // Check if order is fully paid
        if (this.pendingAmount <= 0) {
          await this.updateOrderStatus("paid");
          this.showSnackbar("Pago registrado. Orden completamente pagada!", "success");
        } else {
          this.showSnackbar("Pago registrado exitosamente", "success");
        }

        this.closePaymentForm();*/

        // Ask for another payment if there's still pending amount
        /*if (this.pendingAmount > 0) {
          setTimeout(() => {
            if (confirm("¿Deseas registrar otro pago?")) {
              this.openPaymentForm();
            } else {
              this.finishOrder();
            }
          }, 1000);
        } else {
          this.finishOrder();
        }*/
      } catch (error) {
        console.error("Error saving payment:", error);
        this.showSnackbar("Error al registrar el pago", "error");
      } finally {
        this.savingPayment = false;
      }
    },

    async updateOrderStatus(status) {
      try {
        await this.$axios.patch(
          `${this.$routes["orders"]}/${this.createdOrder.id}/status`,
          {
            status: status,
          }
        );
      } catch (error) {
        console.error("Error updating order status:", error);
      }
    },

    finishOrder() {      
      this.clearForm();      
      this.paymentDialog = false;
      this.paymentFormDialog = false;      
      //this.$router.push({ path: "/order-create" });

    },

    // Utility methods
    formatNumber(value) {
      return new Intl.NumberFormat("es-AR", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      }).format(value || 0);
    },

    formatCurrency(value) {
      return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
      }).format(value || 0);
    },

    showSnackbar(text, color) {
      this.snackbarText = text;
      this.snackbarColor = color;
      this.snackbar = true;
    },

    toggleShipping(order){
      console.log(order.shipping);
      order.shipping_address_status = !order.shipping_address_status;
    }

  },
};
</script>

<style>


.v-card-title {
  font-weight: 600;
}

.v-data-table {
  border: 1px solid #e0e0e0;
  
}

tbody{
  font-size: 13px !important;
}
.text-h6 input {
  font-size: 1.25rem !important;
  font-weight: 600 !important;
}

.v-field__field {
    height: 35px !important;
    font-size: 13px !important;
    margin-top: 0px !important;
}
.v-field__input{
  min-block-size: 19px !important;
}
.v-data-table__tr{
  height: 43px !important;
}


.cliente .v-autocomplete__selection {
    /*font-size: 23px;
    font-weight: bold;
    margin-top: 10px;
    margin-bottom: -10px;*/
}
.custom-bg-gray .v-field__overlay {
  background-color: #ddd !important;
  
}
.number-end .v-field__input{
  text-align: end !important;
}

.bg-warning-2{
  background: #fdedb1;
}
</style>
