<template>  
<VCard :title="'Administración de ' + title">
	<VCardText class="d-flex">
		<v-container id="crud" fluid tag="section">
		
			<VDataTable
			:headers="showHeaders"
			:items="filteredDesserts"
			:search="search"
			class="text-no-wrap"
			>
			<template v-slot:top>
				<VCard flat color="white">
				<VCardText>
					<VRow>
					<VCol sm="6" class="pl-0 pt-20 py-2">
						<VTextField
						v-model="search"
						append-icon="ri-search-line"
						:label="'Busqueda de ' + title"
						></VTextField>
					</VCol>
					<VCol sm="2"></VCol>
					<VCol class="pt-20 py-2" sm="3">
						<VAutocomplete
						v-model="selectedHeaders"
						:items="headers"
						label="Columnas Visibles"
						multiple
						return-object
						>
						<template v-slot:selection="{ item, index }">
							<VChip v-if="index < 2">
							<span>{{ item.title }}</span>
							</VChip>
							<span
							v-if="index === 2"
							class="grey--text caption"
							>(otras {{ selectedHeaders.length - 2 }}+)</span
							>
						</template>
						</VAutocomplete>
					</VCol>
					<VCol sm="1" class="pt-20 py-2">
						<v-dialog v-model="dialog" max-width="50%">
						<template
							v-slot:activator="{ props: activatorProps }"
						>
							<VBtn
							v-bind="activatorProps"
							:color="$cv('principal')"
							size="x-large"
							:title="'Registrar nueva ' + title"
							>
							<VIcon size="large" icon="ri-add-circle-line" />
							</VBtn>
						</template>
						<VCard>
							<v-toolbar :color="$cv('principal')">
							<v-btn
								icon="ri-close-line"
								color="white"
								@click="dialog = false"
							></v-btn>

							<v-toolbar-title>{{
								formTitle
							}}</v-toolbar-title>

							<v-spacer></v-spacer>

							<v-toolbar-items>
								<v-btn
								text="Guardar"
								@click="$save()"
								variant="text"
								></v-btn>
							</v-toolbar-items>
							</v-toolbar>

							<v-form
							ref="form"
							v-model="valid"
							lazy-validation
							>
							<VCard-text>
								<v-container>
								<VRow>
									<VCol cols="12" md="12" sm="12">
									<VTextField
										v-model="editedItem.detalle"
										label="Detalle"
										:disabled="vista"
										:rules="[
										$rulesRequerido,
										$rulesAlfaNum,
										$rulesMax500,
										]"
									></VTextField>
									</VCol>
								</VRow>
								</v-container>
							</VCard-text>
							</v-form>
						</VCard>
						</v-dialog>
					</VCol>
					</VRow>
				</VCardText>
				</VCard>
			</template>
			<!-- status -->
			<template #item.activo="{ item }">
			<VChip
				:color="$resolveStatusVariant(item.activo).color"
				density="comfortable"
			>
				{{ $resolveStatusVariant(item.activo).text }}
			</VChip>
			</template>
			<!-- Actions -->
			<template #item.actions="{ item }">
				<div class="d-flex gap-1">
				<IconBtn
					size="small"
					@click="
					vista = false;
					$editItem(item.id);
					"
				>
					<VIcon icon="ri-pencil-line" />
				</IconBtn>
				<IconBtn
					size="small"
					@click="
					vista = false;
					$deleteItem(item.id, item.detalle);
					"
				>
					<VIcon icon="ri-delete-bin-line" />
				</IconBtn>
				</div>
			</template>
			</VDataTable>
		

		<v-snackbar
			v-model="snackbar"
			:bottom="true"
			:color="color"
			:timeout="timeout"
		>
			<div v-html="text"></div>

			<template v-slot:action="{ attrs }">
			<v-btn dark text v-bind="attrs" @click="snackbar = false">
				Cerrar
			</v-btn>
			</template>
		</v-snackbar>		
		</v-container>
	</VCardText>
</VCard>    
</template>

<script>

function title() {
  return "funciones";
}

export default {
  data: (vm) => ({
    dessertName: "",
    valid: true,            
    title: title(),    
    route: "funciones",       
    dialog: false,    
    snackbar: false,
    visible: true,
    text: "Registro Insertado",
    color: "success",
    timeout: 4000,    
    search: "",
    vista: false,    
    headers: [
      {
        title: "Id",
        align: "start",
        sortable: false,
        key: "id",
      },
      { title: "Detalle", filterable: true, key: "detalle" },
	  { title: "Creado por", key: "created_by" },
	  { title: "Modificado por", key: "last_modified_by" },
      { title: "Fecha Creacion", key: "created_at" },
	  { title: "Fecha Actualizacion", key: "updated_at" },
	  { title: "Estado", key: "activo" },
      { title: "Acciones", key: "actions", value: "actions", sortable: false },
    ],    
    desserts: [],
    editedIndex: -1,
    editedItem: {
      name: "",
      id: "",
    },
    defaultItem: {
      nombre: "",
      id: "",
    },
    filters: {
      id: "",
      name: "",
      address: "",
      created_at: "",
      updated_at: "",
    },
    filterKey: [],
    selectedHeaders: [],
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1
        ? "Registrar " + this.title
        : "Editar " + this.title;
    },

    filteredData() {
      return this.$filteredData().data !== undefined
        ? this.$filteredData().data
        : this.$filteredData();
    },
    showHeaders() {
      return this.headers.filter((s) => this.selectedHeaders.includes(s));
    },
    filteredDesserts() {
      let conditions = [];

      if (this.dessertName) {
        conditions.push(this.filterDessertName);
      }

      if (conditions.length > 0) {
        return this.desserts.filter((dessert) => {
          return conditions.every((condition) => {
            return condition(dessert);
          });
        });
      }

      return this.desserts;
    },
  },

  watch: {
    dialog(val) {
      val || this.$close();
    },
  },

  created() {
    this.$initialize();
    this.selectedHeaders = this.headers;
  },

  methods: {    
    filterDessertName(item) {
      return item.name.toLowerCase().includes(this.dessertName.toLowerCase());
    },
    filterByNombre(item) {
      return this.$filterBy(item, "nombre");
    },
    filterByActivo(item) {
      return this.$filterBy(item, "activo");
    },
  },
  mounted() {
    console.log("Componente " + this.title + " creado");
  },
};
</script>
<!--
<style>

.my-swal-container {
  z-index: 99999 !important;
}
</style>
-->
