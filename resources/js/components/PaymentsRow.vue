<template>
	<!-- <VCard> -->

		<VRow v-if="!payments.length" dense align="center">
			<VCol cols="12" md="12" sm="12">
				<v-btn small color="primary" @click="add" dark>
					Agregar {{ this.modulo }}
				</v-btn>
			</VCol>
		</VRow>
		<VRow justify="center" v-else>						
			<VCol cols="12" md="12" sm="12" class="text-center">
				<VDataTable				
					:headers="headers"
					:items="payments"					
					:key="keyTablePayments"
					:hide-default-footer="true"											
				>				
															
					<template #bottom v-if="!showFooter"></template>
					<template v-slot:[`item.payment_method_id`]="{ item }" > 
						<div class="d-flex justify-center">																				
						<VAutocomplete
							class="mx-auto"
							:items="paymentMethods"
							item-value="id"
							item-title="name"
							:label="item.payment_method_id ? '' : 'Metodo de Pago'"
							v-model="item.payment_method_id"
							:rules="[$rulesRequerido]"
							return-object
							density="compact"
							hide-details
							style="max-width: 350px;"
						></VAutocomplete>
						</div>
					</template>
					<template v-slot:[`item.amount`]="{ item }">																		       					
						<VTextField 
							class="text-left"
							v-model="item.amount"
							:label="item.amount ? '' : 'Importe'"
							:rules="[$rulesRequerido, $rulesNumericos]"
							required							
							density="compact"
							hide-details
							
  							@keypress="onlyNumberInput"																				
						></VTextField>
					</template>
					<template #item.actions="{ item }">
						<!-- <div class="d-flex gap-1"> -->
							<IconBtn
								color="warning"
								size="x-small"
								title="Reset"
								@click="resetRow(item.index)"
							>
								<VIcon icon="ri-refresh-line" />
							</IconBtn>
							<IconBtn
								color="error"
								size="x-small"
								title="Eliminar Pago"
								@click="remove(item)"
							>
								<VIcon icon="ri-subtract-line" />
							</IconBtn>
							<IconBtn
								color="success"
								size="x-small"
								title="Agregar Pago"
								@click="add()"
							>
								<VIcon icon="ri-add-line" />
							</IconBtn>
						<!-- </div> -->
						</template>			                    
				</VDataTable>
			</VCol> 
		</VRow>		
	<!-- </VCard> -->
</template>

<script>
	export default {
		name: "PaymentsRow",
		props: {
			modulo: String,
			records: { type: Array, default(){return []} },
		},
		emits: ['update-total'],
		data: () => ({
			showFooter: false,
			paymentMethods: [],					
			payments: [],			
			route: "payments",       
			headers: [				
        		{ title: "", key: "actions", align: "center"   },
				{ title: "Id", key: "index", align: " d-none" },
				{ title: "Metodo de pago", key: "payment_method_id", align: "center" },
				{ title: "Importe", key: "amount", align: "center"  },				
				
			],						
			keyTablePayments: 0,			
		}),
		computed: {
			totalLocal() {
				console.log(this.payments.reduce((sum, item) => sum + Number(item.amount || 0), 0));
			return this.payments.reduce((sum, item) => sum + Number(item.amount || 0), 0)
			}
		},
		watch: {
			totalLocal(newVal) {
			this.$emit('update-total', newVal)
			}
		},
		created() {			
			this.loadData();		
			if(this.records.length>0){
				this.payments = this.records;
			}
			else{
				this.add();
			}			
		},
		methods: {	
			onlyNumberInput(event){
				const char = String.fromCharCode(event.keyCode);
				// Permitir solo números y punto decimal
				if (!/[0-9.]/.test(char)) {
					event.preventDefault();
				}
			},											
			add() {
				var i = this.payments.length;
				this.payments.push({
                    payment_method_id:"",
                    amount:"",									
					index: i,
				});
			},
			remove(item) {						
				let indexItem = item.index;								
				let index = this.payments.findIndex(obj => obj.index === indexItem);
				this.payments.splice(index, 1);							
			},
			reset() {
				this.payments = [];
			},			
			resetRow(index) {
				this.payments[index].payment_method_id = "";
				this.payments[index].amount = "";								
			},
			async loadData() {
				try {
					const [paymentMethodsRes] = await Promise.all([this.$axios.get(this.$routes["paymentMethods"])])

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

.custom-input {
  max-width: 300px;
  font-size: 0.75rem;  
}

.custom-input .v-input__control {
  font-size: 0.75rem; /* Tamaño de fuente más chico (12px) */
  min-height: 25px;    /* Altura más reducida del input */
}

.custom-input input {
  font-size: 0.75rem;
  padding-top: 4px;
  padding-bottom: 4px;  
}

/* Estilo para los ítems del dropdown del Autocomplete */
.custom-input .v-list-item-title {
  font-size: 0.75rem;  
}

::v-deep(.custom-input .v-field__input) {
  font-size: 0.75rem !important;
}
::v-deep(.custom-input .v-label) {
  font-size: 0.75rem !important;
}

/* Ítems del menú desplegable */
::v-deep(.v-overlay-container .v-list-item-title) {
  font-size: 0.75rem !important; /* Tamaño texto del dropdown */
}

::v-deep(.v-overlay-container .v-list-item) {
  padding-top: 4px !important;
  padding-bottom: 4px !important;
}

</style>
