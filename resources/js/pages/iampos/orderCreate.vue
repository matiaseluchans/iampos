<template>
 <!--
  <VRow class="">
    <VCol cols="12" sm="12" md="12">
      <VCard>
        <VCardText>
          <VRow align="center" class="d-flex" >
            <VCol cols="12" md="2">
            <h3>
              Orden para:-->
              <!--<VChip color="warning">Pendiente</VChip>
              <VChip color="info">Listo para Retirar</VChip>-->
            <!--</h3>
            </VCol>
            <VCol cols="12" md="10" v-if="selectedCustomer">
                    <VAlert type="info" variant="tonal">
                      <strong>Dirección:</strong> {{ selectedCustomer.address }} 
                      <strong>Teléfono:</strong> {{ selectedCustomer.telephone }} 
                      <strong>Cliente:</strong> {{ selectedCustomer.name ?? "-"}} 
                      <strong>Email:</strong> {{ selectedCustomer.email ??"-"}} 
                    </VAlert>
                  </VCol>
            <div class="text-caption text-disabled">{{ formattedDate }}</div>
          </VRow>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>-->
  <VForm ref="orderForm" v-model="validOrder">
    <!-- Sección Cliente -->
    <VRow class="">
      <VCol cols="12" sm="12" md="12">
        <VCard >
          <div class="v-card-item"> 
            <div class="v-card-item__content">
              <div class="v-card-title"><h5 class="text-h5"><VAvatar   icon="ri-user-line" class="text-info mr-2" variant="tonal"/>Cliente</h5></div> 
            </div>
            <div class="v-card-item__append">
              <span class="text-primary cursor-pointer">
                <VBtn @click="openCustomerDialog" :color="$cv('principal')" title="nuevo cliente">
                  <VIcon icon="ri-user-add-line" class="mr-2" />
                </VBtn>
              </span>
            </div>
          </div> 
          <VCardText class="mb-0 pb-0">
            <VRow class="mb-0 pb-0">
              <VCol cols="12" md="4" class="mb-0 pb-0" >
                <VAutocomplete
                  v-model="order.customer_id"
                  :items="customers"
                  item-title="address"
                  item-value="id"
                  label="Seleccionar Cliente"
                  :rules="[(v) => !!v || 'Cliente es requerido']"
                  clearable
                  @update:model-value="onCustomerChange"
                  style="min-height: 90px;font-size:40px"
                  density="compact"
                  class="cliente mb-0 pb-0"
              
                >
                  <template v-slot:item="{ props, item }">
                    <v-sheet border="info md"> 
                    <VListItem v-bind="props">
                      <!--<VListItemTitle>{{ item.raw.address }}</VListItemTitle>-->
                      <VListItemSubtitle class="text-caption"><strong>localidad:</strong>{{ item.raw.locality_id}} - <strong>telefono:</strong>{{ item.raw.telephone }}</VListItemSubtitle>
                   
                    </VListItem>
                    </v-sheet>
                  </template>
                </VAutocomplete>
              </VCol>
              <VCol cols="12" md="8" v-if="selectedCustomer" class="mb-0 pb-0">
                    <VAlert type="info" variant="tonal" class="mb-0">
                      <strong>Dirección:</strong> {{ selectedCustomer.address }} 
                      <strong>Teléfono:</strong> {{ selectedCustomer.telephone }} 
                      <strong>Cliente:</strong> {{ selectedCustomer.name ?? "-"}} 
                      <strong>Email:</strong> {{ selectedCustomer.email ??"-"}} 
                    </VAlert>
                  </VCol>
            </VRow>
            <VRow> 
              <!-- Información del cliente seleccionado -->
              <!--<VCol cols="12" md="12" v-if="selectedCustomer">
                <VAlert type="info" variant="tonal">
                  <strong>Dirección:</strong> {{ selectedCustomer.address }} 
                  <strong>Teléfono:</strong> {{ selectedCustomer.telephone }} 
                  <strong>Cliente:</strong> {{ selectedCustomer.name ?? "-"}} 
                  <strong>Email:</strong> {{ selectedCustomer.email ??"-"}} 
                </VAlert>
              </VCol>-->
            </VRow>
          </VCardText>
        </VCard>
      </VCol>

       
    </VRow>
    <!-- Sección Productos -->
    <VRow>
      <VCol cols="12" sm="12" md="12">
        <VCard> 
          <div class="v-card-item"> 
            <div class="v-card-item__content">
              <div class="v-card-title"><h5 class="text-h5"><VAvatar   icon="ri-shopping-cart-line" class="text-info mr-2" variant="tonal"/>Productos</h5></div> 
            </div> 
          </div> 
          <VCardText>
            <!-- Agregar Producto -->
            <VRow class="">
              
              <VCol cols="12" md="5" xs="11">
                <VAutocomplete
                  v-model="newItem.product_id"
                  :items="products"
                  item-title="name"
                  item-value="id"
                  size="x-small"
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
              <VCol cols="12" md="1" xs="1" >
                <VTextField
                  v-model="newItem.quantity"
                  label="Cant"
                  type="number"
                  min="1"
                  :rules="[
                    (v) => !!v || 'Cantidad requerida',
                    (v) => v > 0 || 'Debe ser mayor a 0',
                    (v) => v <= getProductStock(newItem.product_id) || 'Stock insuficiente',
                  ]"
                  density="compact"
                />
              </VCol>
              <VCol cols="12" md="2" xs="6">
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
              <VCol cols="12" md="2" xs="6">
                <VTextField
                  :model-value="calculateItemTotal()"
                  label="Total"
                  prefix="$"
                  readonly
                  variant="outlined"
                  density="compact"
                  
                />
              </VCol>
              <VCol cols="12" md="1">
                <VBtn   color="primary" @click="addItem" :disabled="!canAddItem" block>
                  <VIcon icon="ri-add-line" />
                </VBtn>
              </VCol>
            </VRow>
            <br>

            <!-- Lista de Productos Agregados -->
            <VRow>
              <VCol cols="12" sm="12" md="9">
                <VCard> 
                  <div class="v-card-item"> 
                    <div class="v-card-item__content">
                      <div class="v-card-title"><h5 class="text-h5">
                        <VAvatar icon="ri-calculator-line" class="text-info mr-2" variant="tonal"/>Productos agregados</h5></div> 
                    </div> 
                  </div> 
                  <VCardText>
                    <div style="min-height: 300px;max-height: 280px; overflow-y: auto;">
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
                        />
                      </VCol>
                      <VCol cols="12" md="12">
                        <VTextField
                          :model-value="formatCurrency(order.discount_amount)"
                          label="Descuento"
                          readonly
                          variant="outlined"
                          density="compact"
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
                          class="text-h6"
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
    <!--
    <VRow class="">
      
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
                />
              </VCol>
              <VCol cols="12" md="12">
                <VTextField
                  :model-value="formatCurrency(order.discount_amount)"
                  label="Descuento"
                  readonly
                  variant="outlined"
                  density="compact"
                />
              </VCol>
              <VCol cols="12" md="12">
                <VTextField
                  :model-value="formatCurrency(order.total_amount)"
                  label="Total Final"
                  readonly
                  variant="outlined"
                  class="text-h6"
                  density="compact"
                  
                />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>-->

    <VRow class="">
       

      <VCol cols="12" sm="12" md="6">
        <!-- Sección Dirección de Entrega -->
        <VCard  >
          <div class="v-card-item">
            <div class="v-card-item__content">
              <div class="v-card-title"><h5 class="text-h5">
                <VAvatar   icon="ri-map-pin-line" class="text-info mr-2" variant="tonal"/>Dirección de Entrega</h5></div> 
            </div>
            <div class="v-card-item__append"><span class="text-primary cursor-pointer">
              <VBtn
                @click="useCustomerAddress"
                :color="$cv('principal')"
                title="Usar del cliente"
              >
                <VIcon icon="ri-refresh-line" class="mr-2" />
              </VBtn></span>
            </div>
          </div> 
          <VCardText>
            <VRow>
              <VCol cols="12" md="12">
                <VTextarea
                  v-model="order.shipping_address"
                  label="Dirección de Entrega"
                  :rules="[(v) => !!v || 'Dirección de entrega es requerida']"
                  rows="2"
                  />
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" sm="12"  md="6">
        <VCard>
          <!--<VCardTitle>
            <VIcon icon="ri-file-text-line" class="mr-2" />
            ssss
          </VCardTitle>-->
          <div class="v-card-item"> 
            <div class="v-card-item__content">
              <div class="v-card-title"><h5 class="text-h5">
                <VAvatar   icon="ri-file-text-line" class="text-info mr-2" variant="tonal"/>Notas Adicionales</h5></div> 
            </div> 
          </div> 
          <VCardText>
            <VTextarea v-model="order.notes" label="Notas de la orden" rows="2" />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
    <!-- Sección Totales -->

    <!-- Notas -->

    <!-- Botones de Acción -->
    <VRow>
      <VCol cols="12" class="text-center">
        <VBtn color="secondary" variant="outlined" class="mr-4" @click="$router.go(-1)">
          Cancelar
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
        <VAlert type="success" class="mb-4">
          ¡Orden creada exitosamente!<br />
          <strong>Número de Orden:</strong> {{ createdOrder?.order?.order_number }}<br />
          <strong>Total:</strong> {{ formatCurrency(createdOrder?.order?.total_amount) }}
        </VAlert>
        <p>¿Deseas registrar un pago para esta orden?</p>
      </VCardText>
      <VCardActions>
        <VSpacer />
        <VBtn variant="outlined" @click="finishOrder"> No, Finalizar </VBtn>
        <VBtn color="success" @click="openPaymentForm"> Sí, Registrar Pago </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>

  <!-- Dialog Registrar Pago -->
  <VDialog v-model="paymentFormDialog" max-width="600px">
    <VCard>
      <VToolbar color="success">
        <VBtn icon="ri-close-line" color="white" @click="closePaymentForm" />
        <VToolbarTitle>Registrar Pago</VToolbarTitle>
      </VToolbar>
      <VForm ref="paymentForm" v-model="validPayment">
        <VCardText>
          <VContainer>
            <VRow>
              <VCol cols="12">
                <VAlert type="info" class="mb-4">
                  <strong>Total de la Orden:</strong>
                  {{ formatCurrency(createdOrder?.total_amount) }}<br />
                  <strong>Total Pagado:</strong> {{ formatCurrency(totalPaid) }}<br />
                  <strong>Saldo Pendiente:</strong> {{ formatCurrency(pendingAmount) }}
                </VAlert>
              </VCol>
              <VCol cols="12" sm="6">
                <VTextField
                  v-model="payment.amount"
                  label="Monto del Pago"
                  type="number"
                  step="0.01"
                  prefix="$"
                  :rules="[
                    (v) => !!v || 'Monto es requerido',
                    (v) => v > 0 || 'Debe ser mayor a 0',
                    (v) => v <= pendingAmount || 'No puede ser mayor al saldo pendiente',
                  ]"
                  density="compact"
                />
              </VCol>
              <VCol cols="12" sm="6">
                <VAutocomplete
                  v-model="payment.payment_method_id"
                  :items="paymentMethods"
                  item-title="name"
                  item-value="id"
                  label="Método de Pago"
                  :rules="[(v) => !!v || 'Método de pago es requerido']"
                />
              </VCol>
              <VCol cols="12">
                <VTextarea v-model="payment.notes" label="Notas del Pago" rows="2" density="compact" />
              </VCol>
            </VRow>
          </VContainer>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="outlined" @click="closePaymentForm"> Cancelar </VBtn>
          <VBtn color="success" @click="savePayment" :loading="savingPayment">
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

export default {
  data() {
    return {
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
      paymentMethods: [],

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
      orderPayments: [],

      // Snackbar
      snackbar: false,
      snackbarText: "",
      snackbarColor: "success",

      // Table headers
      itemHeaders: [
        { title: "Producto", key: "product_name", width: "300px" },
        { title: "Cantidad", key: "quantity", width: "80px" },
        { title: "Precio Unit.", key: "unit_price", width: "150px" },
        { title: "Total", key: "total_price", width: "150px", align: "end" },
        { title: "Acciones", key: "actions", width: "100px", sortable: false },
      ],
      localities:[]
    };
  },

  computed: {
    selectedCustomer() {
      return this.customers.find((c) => c.id === this.order.customer_id);
    },

    canAddItem() {
      return (
        this.newItem.product_id &&
        this.newItem.quantity > 0 &&
        this.newItem.unit_price > 0 &&
        this.newItem.quantity <= this.getProductStock(this.newItem.product_id)
      );
    },

    canCreateOrder() {
      return (
        this.order.customer_id &&
        this.order.shipping_address &&
        this.order.items.length > 0
      );
    },

    totalPaid() {
      return this.orderPayments.reduce(
        (sum, payment) => sum + parseFloat(payment.amount),
        0
      );
    },

    pendingAmount() {
      return this.createdOrder ? this.createdOrder.total_amount - this.totalPaid : 0;
    },
  },

  async created() {
    await this.loadData();
  },

  methods: {
    async loadData() {
      try {
        const [
          customersRes,
          productsRes,
          stockRes,
          paymentMethodsRes,
          localitiesRes,
        ] = await Promise.all([
          this.$axios.get(this.$routes["customers"]),
          this.$axios.get(this.$routes["products"]),
          this.$axios.get(this.$routes["stocks"]),
          this.$axios.get(this.$routes["payment_methods"]),
          this.$axios.get(this.$routes["localities"]),
        ]);

        this.customers = customersRes.data.data || customersRes.data;
        this.products = productsRes.data.data || productsRes.data;
        this.stock = stockRes.data.data || stockRes.data;
        this.paymentMethods = paymentMethodsRes.data.data || paymentMethodsRes.data;
        this.localities = localitiesRes.data.data || localitiesRes.data;
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

        this.showSnackbar("Orden creada exitosamente", "success");
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
      this.payment.amount = this.pendingAmount;
      this.paymentFormDialog = true;
    },

    closePaymentForm() {
      this.paymentFormDialog = false;
      this.payment = {
        amount: 0,
        payment_method_id: null,
        notes: "",
      };
    },

    async savePayment() {
      const isValid = await this.$refs.paymentForm.validate();
      if (!isValid) return;

      this.savingPayment = true;
      try {
        const paymentData = {
          ...this.payment,
          order_id: this.createdOrder.id,
          payment_date: new Date().toISOString(),
        };

        const response = await this.$axios.post(this.$routes["payments"], paymentData);
        const newPayment = response.data.data || response.data;

        this.orderPayments.push(newPayment);

        // Check if order is fully paid
        if (this.pendingAmount <= 0) {
          await this.updateOrderStatus("paid");
          this.showSnackbar("Pago registrado. Orden completamente pagada!", "success");
        } else {
          this.showSnackbar("Pago registrado exitosamente", "success");
        }

        this.closePaymentForm();

        // Ask for another payment if there's still pending amount
        if (this.pendingAmount > 0) {
          setTimeout(() => {
            if (confirm("¿Deseas registrar otro pago?")) {
              this.openPaymentForm();
            } else {
              this.finishOrder();
            }
          }, 1000);
        } else {
          this.finishOrder();
        }
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
      this.paymentDialog = false;
      this.paymentFormDialog = false;
      this.$router.push({ name: "orders" });
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
    font-size: 23px;
    font-weight: bold;
    margin-top: 10px;
    margin-bottom: -10px;
}
</style>
