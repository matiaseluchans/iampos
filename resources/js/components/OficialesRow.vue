<template>
	<!-- <VCard> -->
		<VRow v-if="!personas.length" dense align="center" class="text-fields-row">
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
					:items="personas"					
					:key="keyTablePersonas"
					:hide-default-footer="true"											
				>
				
					<template v-slot:[`item.legajo`]="{ item }">
						
												       					
							<VTextField class="text-left"
								v-model="item.legajo"
								:label="item.legajo ? '' : 'Legajo'"
								:rules="[$rulesRequerido, $rulesLegajo]"								
								required
								@blur="getOficial(item)"
								
							></VTextField>
							
						
					</template>
					
					<template v-slot:[`item.documento`]="{ item }">
						<span class="text-h6 text-left">
							{{item.documento}}
						</span>						
					</template>
					
					<template v-slot:[`item.apellido`]="{ item }">
						
							<span class="text-h6 text-center">
							{{item.apellido}}
						</span>
						
					</template>
					
					<template v-slot:[`item.nombres`]="{ item }">
						<span class="text-h6 text-center">
							{{item.nombres}}
						</span>						
					</template>
					
					<template v-slot:[`item.funcion`]="{ item }" > 
						
														
							<VAutocomplete								
									:items="funciones"
									item-value="id"
									item-title="detalle"
									:label="item.funcion_id ? '' : 'Funcion'"
									v-model="item.funcion_id"
									:rules="[$rulesRequerido]"
									return-object														
								></VAutocomplete>																
							
									
					</template>

					<!-- <template v-slot:[`item.actions`]="{ item }"> -->
					<template #item.actions="{ item }">
						
							<v-btn
								color="warning"
								fab
								x-small
								dark
								title="Reset"
								@click="resetRow(item.index)"
								class="x-btn-grid"
							>
								<v-icon>ri-refresh-line</v-icon>
							</v-btn>
							
							<v-btn
								color="error"
								fab
								x-small
								dark
								title="Eliminar Persona"
								@click="remove(index)"
								class="x-btn-grid"
							>
								<v-icon>ri-user-unfollow-fill</v-icon>
							</v-btn>
							<v-btn
								:color="$cv('principal')"
								fab
								x-small
								dark
								title="Agregar Persona"
								@click="add"
								class="x-btn-grid"
							>
								<v-icon> ri-user-add-fill </v-icon>
							</v-btn>							
						
					</template>
				
					
                    
				</VDataTable>
			</VCol> 
		</VRow>		
	<!-- </VCard> -->
</template>




<script>
	export default {
		name: "OficialesRow",
		props: {
			modulo: String,
		},
		data: (vm) => ({		
			funciones: [],
			personas: [],
			personalPsa:[],
			route: "personalPsa",       
			headers: [				
				{ title: "Id", key: "index", align: " d-none" },
				{ title: "Legajo", key: "legajo", align: "center" },
				{ title: "Documento", key: "documento",  },
				{ title: "Apellido", key: "apellido",  },
				{ title: "Nombres", key: "nombres", align:"center" },
				{ title: "Funcion", key: "funcion", align: "center"  },
				{ title: "Acciones", key: "actions", align: "center"   },
			],			
			avatar: "/images/user_primary.png",
			avatarCondomino: "/images/user_primary_2.png",
			keyTablePersonas: 0,
		}),
		methods: {
			
			async getOficial(item){
				if(item.legajo){
					let index = item.index;
					let id = item.legajo;
					let record = await this.$getRecord(id);	
					if(record){
						this.personas[index].documento = record.data.documento;
						this.personas[index].apellido = record.data.apellido;
						this.personas[index].nombres = record.data.nombres;						
					}
				}
				
			},
			
			add() {
				var i = this.personas.length;
				this.personas.push({
                    legajo:"",
                    documento:"",
					apellido: "",
                    nombres: "",
                    funcion:"",					
					index: i,
				});
			},
			remove(index) {
				this.personas.splice(index, 1);
			},
			reset() {
				this.personas = [];
			},
			forceRerender(child) {
				this.componentKey += 1;
			},
			resetRow(index) {
				this.personas[index].legajo = "";
				this.personas[index].documento = "";
				this.personas[index].apellido = "";
				this.personas[index].nombres = "";				
			},
		},

		mounted() {
			console.log("Componente ContactosRow creado");
		},
		created() {			
			this.$getListForSelect("funciones");
			//this.$getListForSelect("personalPsa");			
		},
	};
</script>



<style scoped>
.hide-scrollbar .v-data-table__wrapper {
  overflow-y: hidden !important;
}
.text-fields-row {
	display: flex;
}
.x-btn-grid {
	flex: unset;
	background-color: bisque;
	margin: 0 0px;
}
.small-field .v-input__control {
  font-size: 0.8rem; /* Ajusta el tamaño de la fuente */
  height: 36px; /* Ajusta la altura del campo */
}
</style>
