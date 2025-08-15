<template>
  <VCard>
    <VCardTitle class="d-flex flex-wrap align-center justify-space-between ga-2">
      <div class="d-flex flex-column" style="min-width: 250px; flex-grow: 1">
        <h2 class="text-h5 mb-1">Órdenes</h2>
        <div class="text-caption text-medium-emphasis d-flex align-center">
          <VIcon icon="ri-information-line" size="16" class="mr-1"/>
          <span style="white-space: normal;">
            Inicialmente se muestran las órdenes de los últimos 60 días
          </span>
        </div>
      </div>
    </VCardTitle>
    <VCardText class="d-flex px-2">   
      <VDataTable
        :headers="showHeaders"
        :items="orders.data || []"
        class="text-no-wrap striped-table border-0"
        :loading="loading"
        :items-per-page="pagination.itemsPerPage"
        :page="pagination.page"
        :items-length="orders.total || 0"
        @update:options="handlePaginationChange"        
      >
        <template v-slot:top>
          <VCard flat color="white">
            <VCardText class="mx-0 px-0">
              
              <VRow dense class="mx-0 px-0">
               
                <VCol cols="12" md="4" sm="12" class="pl-0 pt-0">
                  <VTextField
                    v-model="search"
                    label="Número de Orden"
                    class="mt-0"
                    density="compact"                            
                  />
                  <VAutocomplete
                    v-model="selectedCustomer"
                    :items="customers"
                    :item-title="customers.firstname ? 'firstname' : 'address'"
                    item-value="id"
                    label="Clientes"
                    multiple
                    clearable
                    class="mt-1"
                    density="compact"                            
                  />                          
                </VCol>
                <VCol cols="12" md="4" sm="12" class="pl-0 pt-0">
                  <VAutocomplete
                    v-model="selectedPaymentStatus"
                    :items="paymentStatuses"
                    item-title="name"
                    item-value="id"
                    label="Estado del pago"
                    clearable
                    class="mt-0"
                    density="compact"                            
                  />
                  <DateRangeField
                    class="mt-0"
                    ref="dateOrderRange"
                    v-model="dateOrderRange"
                    modelLabel="Fecha Orden"                            
                  />
                </VCol>
                       

                <VCol cols="12" md="4" sm="12" class="pl-0 pt-0 py-0">
                  <VAutocomplete
                    v-model="selectedShipmentStatus"
                    :items="shipmentStatuses"
                    item-title="name"
                    item-value="id"
                    label="Estado entrega"
                    clearable
                    class="mt-0"
                    density="compact"                            
                  />
                  <DateRangeField
                    class="mt-0 py-0"
                    ref="dateDeliveryRange"
                    v-model="dateRange"
                    modelLabel="Fecha entrega"                            
                  /> 
                </VCol>
              </VRow>
            </VCardText>
            <VCardActions class="justify-end">
              <VBtn variant="outlined" color="primary" @click="reset"> Reset </VBtn>
              <VBtn
                variant="outlined"
                color="white"
                @click="unsetInitialLoad(); fetchData()"
                :loading="loading"
                class="bg-primary"
              >
                Buscar
              </VBtn>
            </VCardActions>
          </VCard>
        </template>
        <template #item.order_number="{ item }">
          <div class="d-flex align-center mx-0 px-0" style="width: 100px">
            <!-- avatar -->

            <div class="d-flex flex-column text-start">
              <span class="d-block font-weight-medium text-high-emphasis text-truncate">{{
                item.order_number
              }}</span>
              <small>Productos: {{ item.quantity_products }}</small>              
            </div>
          </div>
        </template>
        <template #item.customer.firstname="{ item }">
          <span v-if="item.customer.firstname">{{
            item.customer.firstname + " " + item.customer.lastname
          }}</span>
        </template>
        <template #item.order_date="{ item }">
          <div class="d-flex flex-column">
            <span class="font-weight-medium" style="width: 80px">
              {{ formatDateGrid(item.order_date.split(" ")[0]) }}
            </span>
            <small class="text-disabled" style="width: 80px">
              {{ item.order_date.split(" ")[1] || "" }}
            </small>
          </div>
        </template>

        <template #item.delivery_date="{ item }">
          <div v-if="item.shipping && item.delivery_date">
            {{ getDate(item.delivery_date) }}
          </div>
          <!--<VRow v-if="item.shipping && item.delivery_date" class="d-flex align-center">
            <VCol cols="12" md="3" class="d-flex align-center">
              <VAvatar title="con envio" icon="ri-truck-line" class="text-info mr-2" variant="tonal"  size="40"/>
            </VCol>
            <VCol cols="12" md="3" class="d-flex align-center"><strong >{{ getDate(item.delivery_date) }}</strong>
            </VCol>
          </VRow>-->
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
        <template #item.total_paid="{ item }">                      
          <div :class=" (item.total_paid>0)? 'text-right text-success':'text-right text-error'">
            <strong>{{ formatCurrency(item.total_paid) }}</strong>
          </div>
        </template>
        <template #item.customer="{ item }">
          <strong>{{ getCustomer(item.customer) }}</strong>
        </template>
        <template #item.total_amount="{ item }">
          <div
            :class="
              item.total_profit > 0 ? 'text-right text-success' : 'text-right text-error'
            "
          >
            <strong>{{ formatCurrency(item.total_amount) }}</strong>
          </div>
        </template>
        <template #item.payment_status_id="{ item }">
          <div class="d-flex flex-column align-center gap-0">
            <!--
            <VChip
              :color="getStatusCodeColor(item.payment_status.code)"
              prepend-icon="ri-money-dollar-circle-line"
              density="comfortable"
              class="my-1"
              style="width: 140px;"
            >
              {{ item.payment_status.name }}
            </VChip>            
            -->
            <VMenu offset-y>
              <template v-slot:activator="{ props }">
                <VChip
                  v-bind="props"
                  :color="getStatusCodeColor(item.payment_status.code)"
                  prepend-icon="ri-money-dollar-circle-line"
                  density="comfortable"
                  class="my-1 status-chip"
                  style="width: 150px"
                >
                  {{ item.payment_status.name }}
                  <VIcon icon="ri-arrow-down-s-line" class="ml-1" />
                </VChip>
              </template>

              <VList density="compact" class="py-0">
                <VListItem
                  v-for="status in paymentStatuses"
                  :key="status.id"
                  @click="updatePaymentStatus(item, status)"
                  class="pa-0"
                >
                  <VChip
                    :color="getStatusCodeColor(status.code)"
                    prepend-icon="ri-money-dollar-circle-line"
                    density="comfortable"
                    class="ma-1 status-option"
                    style="width: 150px"
                    :class="`selected-status ${status.id === item.payment_status.id ? 'selected-status' : ''}`"
                    >
                    {{ status.name }}
                    <VIcon
                      v-if="status.id === item.payment_status.id"
                      icon="ri-check-line"
                      class="ml-1"/>
                  </VChip>
                </VListItem>
              </VList>
            </VMenu>

            <VMenu offset-y>
              <template v-slot:activator="{ props }">
                <VChip
                  v-bind="props"
                  :color="getStatusCodeColorShipping(item.shipment_status.code)"
                  prepend-icon="ri-truck-line"
                  density="comfortable"
                  class="my-1 status-chip"
                  style="width: 150px"
                >
                  {{ item.shipment_status.name }}
                  <VIcon icon="ri-arrow-down-s-line" class="ml-1" />
                </VChip>
              </template>

              <VList density="compact" class="py-0">
                <VListItem
                  v-for="status in shipmentStatuses"
                  :key="status.id"
                  @click="updateShippingStatus(item, status)"
                  class="pa-0"
                >
                  <VChip
                    :color="getStatusCodeColorShipping(status.code)"
                    prepend-icon="ri-truck-line"
                    density="comfortable"
                    class="ma-1 status-option"
                    style="width: 150px"
                    :class="`selected-status ${status.id === item.shipment_status.id ? 'selected-status' : ''}`"
                  >
                    {{ status.name }}
                    <VIcon
                      v-if="status.id === item.shipment_status.id"
                      icon="ri-check-line"
                      class="ml-1"
                    />
                  </VChip>
                </VListItem>
              </VList>
            </VMenu>
            <!--<VChip
              :color="getStatusCodeColorShipping(item.shipment_status.code)"
              prepend-icon="ri-truck-line"
              density="comfortable"
              class="my-1"
              style="width: 140px; "
            >
              <span style=" display: inline-block;  width: calc(100% - 24px);   text-align: center; ">
                {{ item.shipment_status.name }}
              </span>
            </VChip>-->
          </div>
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <div class="d-flex justify-center align-center gap-0 px-0 mx-0">
            <!-- Icono de hamburguesa para el menú de acciones -->
            <VMenu offset-y transition="scale-transition">
              <template #activator="{ props }">
                <IconBtn size="small" v-bind="props">
                  <VIcon icon="ri-more-2-fill" />
                </IconBtn>
              </template>

              <!-- Opciones del menú -->
              <VList>
                 <VListItem @click="editOrder(item)">
                  <VListItemTitle>
                    <IconBtn size="small" class="my-1" title="Editar">
                      <VIcon icon="ri-pencil-line" /> </IconBtn
                    >Editar
                  </VListItemTitle>
                </VListItem>
                <VListItem @click="showRemito(item)">
                  <VListItemTitle>
                    <IconBtn size="small" class="my-1" title="Remito">
                      <VIcon icon="ri-file-pdf-2-line" /> </IconBtn
                    >Remito
                  </VListItemTitle>
                </VListItem>

                <VListItem @click="showRemitoComanda(item)">
                  <VListItemTitle>
                    <IconBtn size="small" class="my-1" title="Comanda">
                      <VIcon icon="ri-file-pdf-2-line" /> </IconBtn
                    >Comanda
                  </VListItemTitle>
                </VListItem>
                <VListItem @click="openMovementDialog(item)">
                  <VListItemTitle>
                    <IconBtn
                      size="small"
                      class="my-1"
                      title="Cambiar el estado de la orden"
                      @click="openMovementDialog(item)"
                    >
                      <VIcon icon="ri-swap-box-line" /> </IconBtn
                    >Cambiar estado
                  </VListItemTitle>
                </VListItem>

                <VListItem v-if="!isPaid(item)" @click="openPaymentForm(item)">
                  <VListItemTitle>
                    <IconBtn
                      size="small"
                      class="my-1"
                      title="Registrar Pago"
                      @click="openPaymentForm(item)"
                    >
                      <VIcon icon="ri-money-dollar-circle-line" /> </IconBtn
                    >Registrar Pago
                  </VListItemTitle>
                </VListItem>
                <VListItem                   
                  @click="dialogCancelOrden(item)"
                >
                  <VListItemTitle>
                    <IconBtn
                      size="small"
                      class="my-1"
                      title="Cancelar Orden"
                      @click="dialogCancelOrden(item)">
                      <VIcon icon="ri-file-close-line" />
                    </IconBtn>Cancelar Orden
                  </VListItemTitle>                                                                
                </VListItem>
              </VList>
            </VMenu>
          </div>
        </template>
        <!--
          <template v-slot:footer>
            <div class="d-flex justify-end pa-4 font-weight-bold">
              <span class="mr-4">Total Amount:</span>
              <span>{{ calculateTotalAmount() }}</span>
            </div>
          </template>
        -->
        <template #tfoot>
          <tfoot>
            <tr class="bg-grey-lighten-4 font-weight-bold" style="background:#ccc">
              <td class="text-left">
                
              </td>
              <td :colspan="showHeaders.length - (this.isAdmin? 8:6)" class="text-right">
                <div class="d-flex align-center mx-0 px-0" style="width: 100px">                  
                  <div class="d-flex flex-column text-start">
                    <span class="d-block font-weight-medium text-high-emphasis text-truncate">Facturas Totales: {{
                      orders.data.length
                    }}</span>
                    <small>Productos Totales: {{ calculateTotalProducts() }}</small>              
                  </div>
                </div></td>              
              
              
              <td v-if="this.isAdmin" class="text-right">{{ formatCurrency(calculateTotalCost()) }}</td>
              <td v-if="this.isAdmin" class="text-right">{{ formatCurrency(calculateTotalProfit()) }}</td>
              <td class="text-right">{{ formatCurrency(calculateTotalAmount()) }}</td>
              <td class="text-right">{{ formatCurrency(calculateTotalPaid()) }}</td>
              <td class="text-right"></td>
              <td class="text-right"></td>
              <td class="text-right"></td>
            </tr>
          </tfoot>
        </template>
      </VDataTable>

      <VDialog v-model="movementDialog" max-width="800px" persistent>
        <VCard class="rounded-lg">
          <!-- Header con iconos Remix -->
          <VToolbar color="primary" density="compact">
            <VToolbarTitle class="text-white font-weight-medium">
              <VBtn icon variant="text" color="white" @click="closeMovementDialog">
              <VIcon>ri-close-line</VIcon>
            </VBtn>
              {{ movementFormTitle }}
            </VToolbarTitle>
            
          </VToolbar>

          <VForm ref="movementForm" v-model="validMovement">
            <VCardText class="px-4 pt-4 pb-2">
              <!-- Sección de orden con iconos Remix -->
              <VCard flat border class="mb-4" color="primary-lighten-5">
                <VCardItem class="py-0 mt-2 ">
                  <VCardTitle class="d-flex align-center">
                     <VAvatar
                            icon="ri-inbox-unarchive-line"
                            class="text-info mr-2"
                            variant="tonal"
                          />
                    <span>Detalles de la Orden</span>
                    <div class="d-flex flex-wrap gap-2 ml-3">
                      <VChip
                        :color="getStatusCodeColor(selectedOrder.payment_status.code)"
                        size="small"
                        class="font-weight-medium"
                      >
                        <VIcon  icon="ri-money-dollar-circle-line" class="mr-1"/>
                        {{ selectedOrder.payment_status.name }}
                      </VChip>
                      <VChip
                        :color="getStatusCodeColorShipping(selectedOrder.shipment_status.code)"
                        size="small"
                        class="font-weight-medium"
                      >
                        <VIcon  icon="ri-truck-line" class="mr-1"/>
                        {{ selectedOrder.shipment_status.name }}
                      </VChip>
                    </div>
                  </VCardTitle>
                </VCardItem>
                <VDivider class="my-1" />
                <VCardText>
                  <VRow dense>
                    <VCol cols="6" sm="3">
                      <div class="d-flex flex-column">
                        <span class="text-caption text-medium-emphasis">
                          <VIcon icon="ri-hashtag" size="14" class="mr-1"/>
                          Número
                        </span>
                        <span class="font-weight-medium">{{ selectedOrder.order_number }}</span>
                      </div>
                    </VCol>
                    <VCol cols="6" sm="3">
                      <div class="d-flex flex-column">
                        <span class="text-caption text-medium-emphasis">
                          <VIcon icon="ri-calendar-line" size="14" class="mr-1"/>
                          Fecha
                        </span>
                        <span class="font-weight-medium">{{ selectedOrder.order_date }}</span>
                      </div>
                    </VCol>
                    <VCol cols="6" sm="3">
                      <div class="d-flex flex-column">
                        <span class="text-caption text-medium-emphasis">
                          <VIcon icon="ri-archive-line" size="14" class="mr-1"/>
                          Productos
                        </span>
                        <span class="font-weight-medium">
                          {{ selectedOrder.quantity_products }} <small class="text-caption">items</small>
                        </span>
                      </div>
                    </VCol>
                    <VCol cols="6" sm="3">
                      <div class="d-flex flex-column">
                        <span class="text-caption text-medium-emphasis">
                          <VIcon icon="ri-money-dollar-circle-line" size="14" class="mr-1"/>
                          Total
                        </span>
                        <span class="font-weight-medium text-success">
                          {{ formatCurrency(selectedOrder.total_amount) }}
                        </span>
                      </div>
                    </VCol>
                  </VRow>
                </VCardText>
              </VCard>

              <!-- Sección de cliente con iconos Remix -->
              <VCard flat border class="mb-4" color="grey-lighten-4">
                <VCardItem class="py-0 mt-2">
                  <VCardTitle class="d-flex align-center">
                   
                      <VAvatar icon="ri-user-line" class="text-info mr-2" variant="tonal" />

                    <span>Información del Cliente</span>
                  </VCardTitle>
                </VCardItem>
                <VDivider class="my-1" />
                <VCardText>
                  <VRow dense>
                    <VCol cols="12" md="4">
                      <div class="d-flex flex-column">
                        <span class="text-caption text-medium-emphasis">
                          <VIcon icon="ri-user-3-line" size="14" class="mr-1"/>
                          Nombre
                        </span>
                        <span class="font-weight-medium">
                          {{ selectedOrder.customer.firstname }} {{ selectedOrder.customer.lastname }}
                        </span>
                      </div>
                    </VCol>
                    <VCol cols="12" md="4">
                      <div class="d-flex flex-column">
                        <span class="text-caption text-medium-emphasis">
                          <VIcon icon="ri-map-pin-line" size="14" class="mr-1"/>
                          Dirección
                        </span>
                        <span class="font-weight-medium">
                          {{ selectedOrder.customer.address || 'No especificada' }}
                        </span>
                      </div>
                    </VCol>
                    <VCol cols="12" md="4">
                      <div class="d-flex flex-column">
                        <span class="text-caption text-medium-emphasis">
                          <VIcon icon="ri-phone-line" size="14" class="mr-1"/>
                          Contacto
                        </span>
                        <span class="font-weight-medium">
                          {{ selectedOrder.customer.telephone || 'Sin teléfono' }}
                        </span>
                        <span v-if="selectedOrder.customer.email" class="text-primary text-caption">
                          <VIcon icon="ri-mail-line" size="12" class="mr-1"/>
                          {{ selectedOrder.customer.email }}
                        </span>
                      </div>
                    </VCol>
                  </VRow>
                </VCardText>
              </VCard>
        
              <VRow>
                <VCol cols="12" md="6" sm="6">
                  <VAutocomplete
                    v-model="movement.payment_status_id"
                    :items="paymentStatuses"
                    item-title="name"
                    item-value="id"
                    label="Estado del pago"
                    :rules="[validateStatusChange(movement.payment_status_id, 1)]"
                    variant="outlined"
                    class="mt-2"
                    clearable
                  />
                </VCol>
                <VCol cols="12" md="6" sm="6">
                  <VAutocomplete
                    v-model="movement.shipment_status_id"
                    :items="shipmentStatuses"
                    item-title="name"
                    item-value="id"
                    label="Estado de la entrega"
                    :rules="[validateStatusChange(movement.shipment_status_id, 2)]"
                    variant="outlined"
                    class="mt-2"
                    clearable
                  />
                </VCol>
              </VRow>
            </VCardText>

            <VCardActions>
              <VSpacer />
              <VBtn variant="outlined" color="primary" @click="closeMovementDialog">
                Cancelar
              </VBtn>
              <VBtn
                color="white"
                class="bg-primary"
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
      <VDialog v-model="paymentFormDialog" max-width="600px" persistent>
        <VCard class="rounded-lg">
          <!-- Header con gradiente y tipografía más limpia -->
          <VToolbar color="primary" density="compact" class="ml-0 pl-0">
            <VToolbarTitle class="text-h6 font-weight-medium text-white">
              <VBtn icon="ri-close-line" color="white" @click="closePaymentForm" />
              Registrar Pago
            </VToolbarTitle>
            <VSpacer />
            <VBtn icon variant="text" color="white" @click="closePaymentForm">
              <VIcon>mdi-close</VIcon>
            </VBtn>
          </VToolbar>

          <VForm ref="paymentForm" v-model="validPayment">
            <VCardText class="px-4 pt-4 pb-2">
              <!-- Resumen de pagos en tarjeta compacta -->
              <VCard flat class="border" color="surface">
                <VCardItem class="py-2">
                  <VCardTitle class="text-subtitle-1 font-weight-medium d-flex align-center">
                    
                    <VAvatar
                      icon="ri-money-dollar-circle-line"
                      class="text-primary mr-2"
                      variant="tonal"
                    />
                    Resumen de Pagos
                  </VCardTitle>
                </VCardItem>

                <VDivider />

                <VCardText class="px-2 py-3">
                  <!-- Diseño tipo tabla para mejor alineación -->
                  <div class="d-flex flex-column gap-2">
                    <div class="d-flex justify-space-between">
                      <span class="text-caption text-medium-emphasis">Total Orden:</span>
                      <span class="font-weight-medium">{{ formatCurrency(createdOrder?.order?.total_amount) }}</span>
                    </div>
                    <div class="d-flex justify-space-between">
                      <span class="text-caption text-medium-emphasis">Total Pagado:</span>
                      <span class="font-weight-medium text-primary">{{ formatCurrency(totalPaid) }}</span>
                    </div>
                    <div class="d-flex justify-space-between">
                      <span class="text-caption text-medium-emphasis">Saldo Pendiente:</span>
                      <span class="font-weight-medium" :class="pendingAmount > 0 ? 'text-error' : 'text-success'">
                        {{ formatCurrency(pendingAmount) }}
                      </span>
                    </div>
                    
                    <VDivider class="my-1" />
                    
                    <div class="d-flex justify-space-between">
                      <span class="text-caption text-medium-emphasis">Vuelto:</span>
                      <span class="font-weight-medium text-success">{{ formatCurrency(change) }}</span>
                    </div>
                  </div>
                </VCardText>
              </VCard>

              <!-- Componente de pagos con margen superior -->
              <div class="mt-4">
            
                <PaymentsRow
                  ref="paymentsRow"
                  :key="keyPayments"
                  :records="createdOrder.order.payment"
                  modulo="pagos"
                  @update-total="updateTotal"
                />
              </div>
            </VCardText>

            <!-- Acciones con diseño más moderno -->
            <VCardActions class="px-4 pb-4 pt-2">
              <VRow>
                <VCol cols="12" md="12" sm="12" class="pt-4 pb-0 my-0">
                  <VBtn
                    variant="outlined"
                    color="primary"
                    @click="closePaymentForm"
                    class="text-capitalize"
                  block
                  >
                    Cancelar
                  </VBtn> 
                </VCol>
                <VCol cols="12" md="12" sm="12" class="pt-1 my-0 pb-2">
                  <VBtn
                  
                    @click="savePayment"
                    :loading="savingPayment"
                    :disabled="!validPayment"
                    color="white"
                    class="bg-primary  text-capitalize"
                    block
                  >
                  
                    <template v-if="!savingPayment">
                      <VIcon icon="mdi-check-circle-outline" class="mr-1" />
                      Confirmar Pago
                    </template>
                    <template v-else>
                      Procesando...
                    </template>
                  </VBtn>
                </VCol>
              </VRow>
            </VCardActions>
          </VForm>
        </VCard>
      </VDialog>

      <DialogConfirmar
      v-if="dialogs['cancelOrden']"
        v-model="dialogs['cancelOrden']"
        @input="dialog = $event"
        title="Cancelar Orden"
        :info="'¿Estás seguro cancelar la orden <b>'+selectedOrder.order_number+'</b>? Esta acción no se puede deshacer.'"
        icon="ri-file-close-line"
        color="#F44336"
        @confirm="confirmCancelOrden()"
        @close="closeDialog('cancelOrden')"
      />    
      <v-snackbar v-model="snackbar" :color="snackbarColor" :timeout="3000">
        {{ snackbarText }}
        <template v-slot:action="{ attrs }">
          <VBtn text v-bind="attrs" @click="snackbar = false"> Cerrar </VBtn>
        </template>
      </v-snackbar>
    </VCardText>
    <!--
    <VCardActions class="justify-end custom-actions">      
      <VBtn variant="outlined" color="success" @click="getDeliveryReport(1)">
        Reporte de Entregas
      </VBtn>
      <VBtn variant="outlined" color="warning" @click="getDeliveryReport(2)">
        Reporte de Entregas Cliente
      </VBtn>
    </VCardActions>
    -->
  </VCard>
</template>

<script>
import DateRangeField from "@/components/DateRangeField.vue";
import { mapGetters } from "vuex";
import DialogConfirmar from "@/components/dialogs/Confirm.vue";
//import { debounce } from 'lodash';

export default {
  components: { DateRangeField, DialogConfirmar },
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
      defaultDateRange: {},  // Guardamos el rango inicial para poder resetearlo
      loading: false,
      search: "",
      customers: [],
      //orders: [],
      paymentStatuses: [],
      shipmentStatuses: [],
      selectedCustomer: null,
      selectedPaymentStatus: null,
      selectedShipmentStatus: null,
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
      snackbarText: "",
      snackbarColor: "success",

      // Headers
      headers: [
        { title: "", key: "actions", sortable: false, align: "left", width: "0px", cellProps: { 
          class: "actions-cell",  /* Clase aplicada vía cellProps*/
          /*style: { padding: "0", margin: "0" },*/  // Estilos inline
        } 
        },
        { title: "Orden", key: "order_number", width: "60px" },
        { title: "Fecha", key: "order_date", align: "center", width: "60px" },      
        { title: "Cliente", key: "customer", width: "70%" },
        { title: "Total", key: "total_amount", align: "end" },
        { title: "Pagado", key: "total_paid", align: "end" },
        { title: "Vendedor", key: "seller_name", width: "10%" },        

        { title: "Fecha Entrega", key: "delivery_date", align: "center", width: "100px" },
        { title: "Estados", key: "payment_status_id", align: "center" },
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
      isInitialLoad: true, // Bandera para identificar carga inicial
      dialogs: {
        cancelOrden: false,        
      },
    };
  },

  computed: {
    ...mapGetters({
      userIsAdmin: "isAdmin", // Mapea `isAdmin` del store a `userIsAdmin` en el componente
    }),
    showHeaders() {
      return this.headers;
    },

    pendingAmount() {
      return this.createdOrder
        ? this.totalPaid < this.createdOrder.order.total_amount
          ? this.createdOrder.order.total_amount - this.totalPaid
          : 0
        : 0;
    },

    movementFormTitle() {
      return this.selectedOrder
        ? `Cambio de estado de orden ${this.selectedOrder.order_number}`
        : "Cambio de estado de orden";
    },
  },
  async created() {
    this.checkAdmin();
    if (this.isAdmin) {
      this.headers.splice(
        4,
        0,
        { title: "Costo", key: "total_cost", align: "end" },
        { title: "Ganancia", key: "total_profit", align: "end" },        
      );
    }

    // Solo establecer fechas por defecto en la carga inicial
    if (this.isInitialLoad) {
      this.setDefaultDateRange();
    }
    await this.loadData();    
  },

  methods: {
    closeDialog(dialog){
      this.dialogs[dialog] = false   
       this.selectedOrder = null   
    },
    dialogCancelOrden(item) {
      if (!item) {
        this.showSnackbar("No hay orden seleccionada para cancelar", "error")
        return;
      }
      console.log("Abriendo diálogo de cancelación para la orden:", item);
      this.selectedOrder = item; // Asegurarse de que la orden seleccionada esté definida      
      this.dialogs['cancelOrden'] = true;
    },
    async confirmCancelOrden() {
      console.log("Confirmando cancelación de orden:", this.selectedOrder);

      try {
        this.loading = true;
        const response = await this.$axios.post(`${this.$routes["ordersCancel"]}/${this.selectedOrder.id}`, {
          data: {
            form: this.selectedOrder,
          },
          _method: 'PUT'
        });

        // Actualizar localmente para mejor experiencia de usuario        
        this.showSnackbar("Se ha cancelado la orden", "success");
      } catch (error) {
        console.error("Error al actualizar estado:", error);
        this.showSnackbar("Error al actualizar el estado", "error");
        // Forzar recarga para sincronizar con el servidor        
      } finally {        
        this.closeDialog('cancelOrden')
        this.loading = false;
        this.fetchData();
      }

      
      //this.openMovementDialog(this.selectedOrder, true); // true indica que es una cancelación
    },
    calculateTotalAmount() {
      if (!this.orders.data) return 0;

      return this.orders.data.reduce((total, item) => {
        return total + (parseFloat(item.total_amount) || 0);
      }, 0).toFixed(2); // Formats to 2 decimal places
    },
    calculateTotalPaid() {
      if (!this.orders.data) return 0;
      
      return this.orders.data.reduce((total, item) => {
        return total + (parseFloat(item.total_paid) || 0);
      }, 0).toFixed(2); // Formats to 2 decimal places
    },
    calculateTotalCost() {
      if (!this.orders.data) return 0;
      
      return this.orders.data.reduce((total, item) => {
        return total + (parseFloat(item.total_cost) || 0);
      }, 0).toFixed(2); // Formats to 2 decimal places
    },
    calculateTotalProfit() {
      if (!this.orders.data) return 0;
      
      return this.orders.data.reduce((total, item) => {
        return total + (parseFloat(item.total_profit) || 0);
      }, 0).toFixed(2); // Formats to 2 decimal places
    },
    calculateTotalProducts() {
      if (!this.orders.data) return 0;
      
      return this.orders.data.reduce((total, item) => {
        return total + (item.quantity_products || 0);
      }, 0);
    },
    unsetInitialLoad() {
      this.isInitialLoad = false;
    },
    setDefaultDateRange() {
      const endDate = new Date().toISOString().split("T")[0];
      const startDate = new Date();
      
      // Establecer el rango por defecto a 60 días atrás
      startDate.setDate(startDate.getDate() - 60);
      
      this.defaultDateRange = {
        start: startDate.toISOString().split("T")[0],
        end: endDate
      };
                  
    },   
    async updatePaymentStatus(item, newStatus) {
      // No hacer nada si es el mismo estado
      if (newStatus.id === item.payment_status.id) return;

      try {
        this.loading = true;
        const response = await this.$axios.post(`${this.$routes["orders"]}/${item.id}`, {
          data: {
            payment_status_id: newStatus.id,
          },
          _method: 'PUT'
        });

        // Actualizar localmente para mejor experiencia de usuario
        item.payment_status = newStatus;
        this.showSnackbar("Estado actualizado correctamente", "success");
      } catch (error) {
        console.error("Error al actualizar estado:", error);
        this.showSnackbar("Error al actualizar el estado", "error");
        // Forzar recarga para sincronizar con el servidor
        this.fetchData();
      } finally {
        this.loading = false;
      }
    },
    async updateShippingStatus(item, newStatus) {
      // No hacer nada si es el mismo estado
      if (newStatus.id === item.shipment_status.id) return;

      try {
        this.loading = true;
        const response = await this.$axios.post(`${this.$routes["orders"]}/${item.id}`, {
          data: {
            shipment_status_id: newStatus.id,
          },
          _method: 'PUT'
        });

        // Actualizar localmente para mejor experiencia de usuario
        item.shipment_status = newStatus;
        this.showSnackbar("Estado actualizado correctamente", "success");
      } catch (error) {
        console.error("Error al actualizar estado:", error);
        this.showSnackbar("Error al actualizar el estado", "error");
        // Forzar recarga para sincronizar con el servidor
        this.fetchData();
      } finally {
        this.loading = false;
      }
    },

    reset() {
      this.selectedCustomer = null;
      this.selectedPaymentStatus = null;
      this.selectedShipmentStatus = null;
      this.search = "";
      this.pagination.page = 1;
      this.dateRange = {
        start: null,
        end: null,
      };
      this.dateOrderRange = {
        start: null,
        end: null,
      };

      // 2. Resetear el componente DateRangeField
      this.$nextTick(() => {
        if (this.$refs.dateDeliveryRange && this.$refs.dateDeliveryRange.reset) {
          this.$refs.dateDeliveryRange.reset();
        }
        if (this.$refs.dateOrderRange && this.$refs.dateOrderRange.reset) {
          this.$refs.dateOrderRange.reset();
        }
      });
      this.fetchData();
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
        
        // Si es la primera carga, ya no lo será después de esta búsqueda
        
        const params = {
          order_number: this.search || undefined,
          customers: this.selectedCustomer || undefined,
          payment_status_id: this.selectedPaymentStatus || undefined,
          shipment_status_id: this.selectedShipmentStatus || undefined,
          page: this.pagination.page,
          per_page: this.pagination.itemsPerPage,
          sort_by: this.pagination.sortBy.length ? this.pagination.sortBy[0] : undefined,
          sort_order: this.pagination.sortDesc?.length
            ? this.pagination.sortDesc[0]
              ? "desc"
              : "asc"
            : undefined,
        };

        console.log("this.isInitialLoad")
        console.log(this.isInitialLoad)
        if (this.isInitialLoad) {          
          // Establecer el rango de fechas por defecto solo en la primera carga                    
          params.order_start_date = this.defaultDateRange.start;
          params.order_end_date = this.defaultDateRange.end;
        }

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
        Object.keys(params).forEach(
          (key) => params[key] === undefined && delete params[key]
        );

        const [ordersRes] = await Promise.all([
          this.$axios.get(this.$routes["ordersSearch"], { params }),
        ]);

        //this.orders = ordersRes.data.data;
        this.orders = {
          data: ordersRes.data.data,
          total: ordersRes.data.total,
          current_page: ordersRes.data.current_page,
          last_page: ordersRes.data.last_page,
        };

        // Eliminamos el filteredStock ya que ahora el filtrado lo hace el backend
      } catch (error) {
        console.error("Error fetching data:", error);
        this.showSnackbar("Error al cargar los datos", "error");
      } finally {
        this.loading = false;
      }
    },

    async loadData() {
      this.loading = true;
      try {
        const [
          customersRes,
          paymentStatusesRes,
          shipmentStatusesRes,
        ] = await Promise.all([
          this.$axios.get(this.$routes["customers"]),
          this.$axios.get(this.$routes["paymentStatuses"]),
          this.$axios.get(this.$routes["shipmentStatuses"]),
        ]);

        this.customers = customersRes.data.data;
        this.paymentStatuses = paymentStatusesRes.data.data;
        this.shipmentStatuses = shipmentStatusesRes.data.data;
      } catch (error) {
        console.error("Error fetching data:", error);
        this.showSnackbar("Error al cargar los datos", "error");
      } finally {
        this.loading = false;
      }
    },

    // Payment methods
    openPaymentForm(item) {
      this.keyPayments = +1;
      this.createdOrder.order = item;
      this.paymentFormDialog = true;
    },
    closePaymentForm() {
      this.createdOrder.order = {};
      this.paymentFormDialog = false;
    },
    updateTotal(newTotal) {
      this.totalPaid = newTotal;
      console.log("total pagado:",this.totalPaid);
      this.change =
        this.totalPaid > this.createdOrder.order.total_amount
          ? this.totalPaid - this.createdOrder.order.total_amount
          : 0;
    },
    async validatePayments(payments) {
      console.log(payments);
      if (payments.length <= 0) {
        return "Debe incluir al menos una modalidad de pago";
      }

      const paymentMethod = new Set();
      const total = payments.reduce(
        (acc, payment) => acc + parseFloat(payment.amount),
        0
      );
      /*if (total > this.pendingAmount) {        
        return "El total excede el saldo pendiente";
      }*/

      for (const payment of payments) {
        if (!payment.amount || !payment.payment_method_id) {
          return "Verifique la informacion de los pagos";
        }
        // 1. Verificar paymentMethod duplicados
        /*if (paymentMethod.has(payment.payment_method_id.id)) {
          return (
            "Ha seleccionado mas de una vez el mismo metodo de pago. Metodo de pago: " +
            payment.payment_method_id.name
          );
        }*/
        paymentMethod.add(payment.payment_method_id.id);
      }

      return true;
    },
    async savePayment() {
      const isValid = await this.validatePayments(this.$refs.paymentsRow.newPayments);

      if (isValid !== true) return this.showSnackbar(isValid, "error");

      this.savingPayment = true;
      try {
        const paymentData = {
          //...this.payment,
          order_id: this.createdOrder.order.id,
          payments: this.$refs.paymentsRow.newPayments,
        };
 
        const response = await this.$axios.post(this.$routes["payments"], paymentData);
        if (response.status == 201) {
          this.showSnackbar("Pago registrado exitosamente", "success");
          this.closePaymentForm();
          this.fetchData();
        }
      } catch (error) {
        console.error("Error saving payment:", error);
        this.showSnackbar("Error al registrar el pago", "error");
      } finally {
        this.savingPayment = false;
      }
    },
    //end payments methods
    checkAdmin() {
      this.isAdmin = this.userIsAdmin;
    },
    validateStatusChange(value, type) {
      if (!value) return "Debe seleccionar un estado";
      if (type == 1) {
        if (value === this.selectedOrder.payment_status_id)
          return "El estado de pago seleccionado es igual al estado de pago actual";
      } else {
        if (value === this.selectedOrder.shipment_status_id)
          return "El estado de envio seleccionado es igual al estado de envio actual";
      }

      return true;
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
      const datePart = dateString.split(" ")[0];
      const [day, month, year] = datePart.split("/").map(Number);

      // Valida que day, month y year sean números válidos
      if (isNaN(day) || isNaN(month) || isNaN(year)) return null;

      // Crea la fecha en UTC para evitar problemas de zona horaria
      return new Date(Date.UTC(year, month - 1, day)); // ¡Los meses en JS van de 0 a 11!
    },

    getCustomer(customer) {
      if (!customer) return "Cliente no disponible";

      return customer.firstname || customer.lastname
        ? `${customer.firstname || ""} ${customer.lastname || ""}`.trim()
        : customer.address || "Dirección no disponible";
    },
    formatCurrency(value) {
      return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
      }).format(value || 0);
    },

    // Movement Dialog Methods
    openMovementDialog(item) {
      if (item) {
        this.selectedOrder = item;
      } else {
        this.selectedOrder = null;
      }

      this.movementDialog = true;
    },

    closeMovementDialog() {
      this.movementDialog = false;
      this.$refs.movementForm?.reset();      
    },

    async saveMovement() {
      const isValid = await this.$refs.movementForm.validate();
      if (!isValid) return;

      this.savingMovement = true;
      try {
        let endpoint, data;

        // New stock - create or update
        endpoint = `${this.$routes["orders"]}/${this.selectedOrder.id}`;
        data = {
          data: {
            payment_status_id: this.movement.payment_status_id,
            shipment_status_id: this.movement.shipment_status_id,
          },
          _method: 'PUT'
        };

        await this.$axios.post(endpoint, data);
        this.showSnackbar("Estado actualizado correctamente", "success");
        this.closeMovementDialog();
        await this.fetchData();
      } catch (error) {
        console.error("Error saving movement:", error);
        this.showSnackbar(
          error.response?.data?.message || "Error al registrar el movimiento",
          "error"
        );
      } finally {
        this.savingMovement = false;
      }
    },

    // Utility Methods
    getStatusColor(item) {
      const statusColorMap = {
        [this.$paymentStatus.PENDING]: "warning",
        [this.$paymentStatus.PROCESS]: "warning",
        [this.$paymentStatus.PARTIAL_PAYMENT]: "warning",
        [this.$paymentStatus.PAID]: "success",
        [this.$paymentStatus.COMPLETED]: "success",
        [this.$paymentStatus.APPROVED]: "success",
      };

      return statusColorMap[item.status.code] || "error";
    },

    getStatusCodeColor(code) {
      const statusColorMap = {
        [this.$paymentStatus.PENDING]: "warning",
        [this.$paymentStatus.PROCESS]: "warning",
        [this.$paymentStatus.PARTIAL_PAYMENT]: "warning",
        [this.$paymentStatus.PAID]: "success",
        [this.$paymentStatus.COMPLETED]: "success",
        [this.$paymentStatus.APPROVED]: "success",
      };

      return statusColorMap[code] || "error";
    },
    
    getStatusCodeColorShipping(code) {
      const statusColorMap = {
        [this.$shipmentStatus.NOT_REQUIRED]: "success",
        [this.$shipmentStatus.DELIVERED]: "success",
        [this.$shipmentStatus.SHIPPED]: "success",
        [this.$shipmentStatus.READY_PICKUP]: "warning",
        [this.$shipmentStatus.PENDING]: "warning",        
        [this.$shipmentStatus.IN_TRANSIT]: "warning",        
        [this.$shipmentStatus.CANCEL]: "error",
        [this.$shipmentStatus.FAILED]: "error",
        [this.$shipmentStatus.RETURNED]: "error",
      };

      return statusColorMap[code] || "error";
    },

    isPaid(item) {
      const paids = [
        this.$paymentStatus.PAID,
        this.$paymentStatus.COMPLETED,
        this.$paymentStatus.REJECTED,
        this.$paymentStatus.CANCEL,
        this.$paymentStatus.REFUND,
        this.$paymentStatus.RETURNED,
      ];

      return paids.includes(item.status.code);
    },

    formatDate(date) {
      return new Date(date).toLocaleString();
    },
    formatDateGrid(dateStr) {
      if (!dateStr) return "";
      const [date, time] = dateStr.split(" ");
      return `${date} ${time || ""}`;
    },
    getDate(date) {
      const dateTime = date.toLocaleString(); // Ejemplo: "17/07/2025, 12:30:45"
      const onlyDate = dateTime.split(" ")[0]; // Obtiene todo antes de la coma

      return onlyDate; // "17/07/2025"
    },
    showSnackbar(text, color) {
      this.snackbarText = text;
      this.snackbarColor = color;
      this.snackbar = true;
    },
    async getDeliveryReport(type) {
      try {
        // Validar rango de fechas
        if (!this.dateRange.start || !this.dateRange.end) {
          this.showSnackbar("Debe seleccionar un rango de fechas de entrega", "error");

          return;
        }

        // Validar estado seleccionado
        const code = this.shipmentStatuses.find(
          (status) => status.id === this.selectedShipmentStatus
        )?.code;
        if (!code) {
          this.showSnackbar(
            "Debe seleccionar el estado de envio Pendiente para generar el reporte",
            "error"
          );

          return;
        }

        if (code != this.$shipmentStatus.PENDING) {
          this.showSnackbar(
            "Debe seleccionar el estado de envio Pendiente para generar el reporte",
            "error"
          );

          return;
        }

        // Mostrar loader mientras se genera el PDF
        this.loading = true;

        const urlReport =
          type == 1
            ? `${this.$routes["ordersDelivery"]}`
            : `${this.$routes["ordersCustomersDelivery"]}`;

        const params = {
          start_date: this.dateRange.start, // Formato: YYYY-MM-DD
          end_date: this.dateRange.end,
          shipment_status_id: this.selectedShipmentStatus,
          customers: this.selectedCustomer,
        };

        // Llamar al endpoint de Laravel que genera el PDF
        const response = await this.$axios.get(urlReport, {
          params,
          responseType: "blob",
        });

        var blob = new Blob([response.data], { type: "application/pdf" });
        const url = window.URL.createObjectURL(blob, { oneTimeOnly: true });
        const link = document.createElement("a");
        link.target = "_blank";

        link.href = url;

        document.body.appendChild(link);
        link.click();
      } catch (error) {
        console.error("Error generando reporte:", error);
        this.showSnackbar("Error al generar la reporte", "error");
      } finally {
        this.loading = false;
      }
    },

    async editOrder(item) {
      try {
        this.loading = true;
     
        this.$router.push({
          path: '/order-create',
          query: {
            edit: true,
            orderId: item.id,
       
          }
        });
        
      } catch (error) {
        console.error("Error al obtener datos de la orden:", error);
        this.showSnackbar("Error al cargar la orden para edición", "error");
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
            responseType: "blob",
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
        console.error("Error generando factura:", error);
        this.showSnackbar("Error al generar la factura", "error");
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
            responseType: "blob",
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
        console.error("Error generando factura:", error);
        this.showSnackbar("Error al generar la factura", "error");
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
.text-success {
  color: #4caf50;
}

.text-error {
  color: #f44336;
}

.text-disabled {
  color: #9e9e9e;
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
