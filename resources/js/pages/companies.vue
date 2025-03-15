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
										v-model="editedItem.name"
										label="Compañia seguro"
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
					$deleteItem(item.id, item.name);
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

		<!-- <vue-confirm-dialog></vue-confirm-dialog> -->
		</v-container>
	</VCardText>
</VCard>    
</template>

<script>
function title() {
  return "Companias";
}

export default {
  data: (vm) => ({
    dessertName: "",
    valid: true,
    nowDate: new Date().toISOString().slice(0, 10),
    _method: "PUT",
    autoGrow: true,
    rows: 1,
    title: title(),
    dessertActivo: "",
    route: "companies",
    menu: false,
    modal: false,
    menu2: false,
    dialog: false,
    isActive: { value: false },
    snackbar: false,
    visible: true,
    text: "Registro Insertado",
    color: "success",
    timeout: 4000,
    rules: [(v) => v.length <= 500 || "Max 500 caracteres"],
    search: "",
    vista: false,
    users: false,
    headers: [
      {
        title: "Id",
        align: "start",
        sortable: false,
        key: "id",
      },
      { title: "Nombre", filterable: true, key: "name" },
      { title: "Direccion", key: "address" },
      { title: "Creado", key: "created_at" },
      { title: "Actualizado", key: "updated_at" },
      { title: "Acciones", key: "actions", value: "actions", sortable: false },
    ],

    /*headers: [
          {
            align: 'start',
            key: 'name',
            sortable: false,
            title: 'Dessert (100g serving)',
          },
          { key: 'calories', title: 'Calories' },
          { key: 'fat', title: 'Fat (g)' },
          { key: 'carbs', title: 'Carbs (g)' },
          { key: 'protein', title: 'Protein (g)' },
          { key: 'iron', title: 'Iron (%)' },
        ],*/

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
    openSweetAlert() {
      // Ajustar z-index de SweetAlert2
      Swal.mixin({
        customClass: {
          container: "my-swal-container",
        },
      }).fire({
        title: "SweetAlert2",
        text: "Este es un mensaje de SweetAlert2",
        icon: "info",
      });
    },
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
