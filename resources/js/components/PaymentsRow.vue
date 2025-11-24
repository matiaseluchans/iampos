<template>
 
  <VRow dense align="center">
    <VCol cols="12" md="12" sm="12" class="text-center">
       
      <VBtn
        v-if="(newPayments.length ==0)"
        color="primary"
        size="small"
        title="Agregar Pago"
        @click="add()"
      >
      Agregar Pago
      </VBtn>
      <VDataTable
        :headers="headers"
        :items="payments"
        :key="keyTablePayments"
        :hide-default-footer="true"
      >
        <template #bottom v-if="!showFooter"></template>
        
        <!-- Columna de acciones -->
        <template #item.actions="{ item }">
          <div class="d-flex gap-0 px-0 mx-0 justify-center">
            <VBtn
            v-if="!item.isExisting"
              color="success"
              size="small"
              title="Set Pago"
              @click="setImporte(item)"
              variant="tonal"
              icon="ri-money-dollar-circle-line"
            />
               
      
            <VBtn
              v-if="!item.isExisting"
              color="error"
              size="small"
              title="Eliminar Pago"
              @click="remove(item)"
              variant="tonal"
              icon="ri-subtract-line"
            >
              
            </VBtn>
            
            <VBtn
              v-if="!item.isExisting"
              color="warning"
              size="small"
              title="Reset"
              icon="ri-refresh-line"
              variant="tonal"
              @click="resetRow(item.index)"
            >
               
            </VBtn>
            
            
            <VBtn
              v-if="!item.isExisting"
              color="primary"
              size="small"
              title="Agregar Pago"
              @click="add()"
              variant="tonal"
              icon="ri-add-line"
            >
               
            </VBtn>
            

            <VChip v-if="item.isExisting"
              icon="ri-lock-line"
              color="primary" 
              class="ml-1"
            >
              <VIcon
              v-if="item.isExisting"
              icon="ri-lock-line"
              color="disabled"
              
              class="ml-1" 
            />
              </VChip> 
            
          </div>
        </template>
        
        <!-- Método de pago -->
        <template #item.payment_method_id="{ item }">
          <div class="d-flex justify-center">
            <VAutocomplete
              v-if="!item.isExisting"
              class="mx-auto"
              :items="paymentMethods"
              item-value="id"
              item-title="name"
              :label="item.payment_method_id ? '' : 'Método de Pago'"
              v-model="item.payment_method_id"
              :rules="[$rulesRequerido]"
              return-object
              density="compact"
              hide-details
              style="max-width: 350px;"
            />
            <span v-else class="text-medium-emphasis">
              {{ getPaymentMethodName(item.payment_method_id) }}
            </span>
          </div>
        </template>
        
        <!-- Importe -->
        <template #item.amount="{ item }">
          <VTextField
            v-if="!item.isExisting"
            class="text-left"
            v-model="item.amount"
            :label="item.amount ? '' : 'Importe'"
            :rules="[$rulesRequerido]"
            required
            density="compact"
            hide-details
            @keypress="onlyNumberInput"
          />
          <span v-else class="text-medium-emphasis">
            ${{ item.amount }}
          </span>
        </template>
        
        <!-- Fila con estilo diferente para pagos existentes -->
        <template #item.data-table-row="{ props }">
          <tr
            :class="props.item.isExisting ? 'existing-payment-row' : 'new-payment-row'"
            v-bind="props"
          ></tr>
        </template>
      </VDataTable>
    </VCol>
  </VRow>
</template>

<script>
export default {
  name: "PaymentsRow",
  props: {
    modulo: String,
    records: { type: Array, default() { return [] } },
    pendingAmount: Number

  },
  emits: ['update-total'],
  data: () => ({
    showFooter: false,
    paymentMethods: [],          
    newPayments: [],            // Solo para nuevos pagos
    existingPayments: [],       // Solo para pagos existentes
    route: "payments",       
    headers: [        
      { title: "", key: "actions", align: "center", width: "120px" },
      { title: "Id", key: "index", align: " d-none" },
      { title: "Método de pago", key: "payment_method_id", align: "center" },
      { title: "Importe", key: "amount", align: "center" },        
    ],            
    keyTablePayments: 0,
  }),
  computed: {
    // Combina pagos existentes y nuevos para mostrar en la tabla
    payments() {
      return [...this.existingPayments, ...this.newPayments];
    },
    // Calcula el total incluyendo ambos tipos de pagos
    totalLocal() {
      const existingTotal = this.existingPayments.reduce((sum, item) => sum + Number(item.amount || 0), 0);
      const newTotal = this.newPayments.reduce((sum, item) => sum + Number(item.amount || 0), 0);
      return existingTotal + newTotal;
    }
  },
  watch: {
    totalLocal(newVal) {
      this.$emit('update-total', newVal);
    },
    records: {
      immediate: true,
      handler(newRecords) {
        if (newRecords.length > 0) {
          this.existingPayments = newRecords.map((item, index) => ({
            ...item,
            index: index,
            isExisting: true  // Marcar como pago existente
          }));
        }
      }
    }
  },
  created() {      
    this.loadData();
    if (this.records.length === 0) {
      this.add();
    }
  },
  methods: {
    getPaymentMethodName(id) {
      const method = this.paymentMethods.find(m => m.id === id);
      return method ? method.name : 'Método no encontrado';
    },
    formatCurrency(value) {
      if (!value) return "$0";
      return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
      }).format(value);
    },
    onlyNumberInput(event) {
      const char = String.fromCharCode(event.keyCode);
      if (!/[0-9.-]/.test(char)) {
        event.preventDefault();
      }
    },  
    
    setImporte(item)
    {
      console.log(this.pendingAmount);
      item.amount = this.pendingAmount;
    },
    add() {
      const i = this.payments.length;
      this.newPayments.push({
        payment_method_id: "",
        amount: "",                  
        index: i,
        isExisting: false
      });
    },
    remove(item) {              
      if (item.isExisting) return; // No permitir eliminar pagos existentes
      
      let indexItem = item.index;              
      let index = this.newPayments.findIndex(obj => obj.index === indexItem);
      this.newPayments.splice(index, 1);
      
      // Reindexar
      this.newPayments.forEach((p, i) => {
        p.index = this.existingPayments.length + i;
      });
    },
    reset() {
      this.newPayments = [];
    },          
    resetRow(index) {
      const payment = this.newPayments.find(p => p.index === index);
      if (payment) {
        payment.payment_method_id = "";
        payment.amount = "";              
      }
    },
    async loadData() {
      try {
        const [paymentMethodsRes] = await Promise.all([
          this.$axios.get(this.$routes["paymentMethods"])
        ]);
        this.paymentMethods = paymentMethodsRes.data.data || paymentMethodsRes.data;          
      } catch (error) {
        console.error("Error loading data:", error);
        this.showSnackbar("Error al cargar los datos", "error");
      }
    },
  },        
};
</script>

<style scoped>
.existing-payment-row {
  background-color: rgba(0, 0, 0, 0.03); /* Fondo más claro para pagos existentes */
}

.existing-payment-row:hover {
  background-color: rgba(0, 0, 0, 0.05) !important;
}

.new-payment-row {
  background-color: rgba(255, 255, 255, 1); /* Fondo blanco para nuevos pagos */
}

.custom-input {
  max-width: 300px;
  font-size: 0.75rem;  
}

.custom-input .v-input__control {
  font-size: 0.75rem;
  min-height: 25px;
}

.custom-input input {
  font-size: 0.75rem;
  padding-top: 4px;
  padding-bottom: 4px;  
}

.custom-input .v-list-item-title {
  font-size: 0.75rem;  
}

::v-deep(.custom-input .v-field__input) {
  font-size: 0.75rem !important;
}
::v-deep(.custom-input .v-label) {
  font-size: 0.75rem !important;
}

::v-deep(.v-overlay-container .v-list-item-title) {
  font-size: 0.75rem !important;
}

::v-deep(.v-overlay-container .v-list-item) {
  padding-top: 4px !important;
  padding-bottom: 4px !important;
}
</style>
