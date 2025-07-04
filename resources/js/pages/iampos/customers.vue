<template>
  <VCard :title="'Administración de ' + title">
    <VCardText class="d-flex px-2">
       
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
                  <VCol sm="4" class="pl-0 pt-20 py-2">
                    <VTextField
                      v-model="search"
                      append-icon="ri-search-line"
                      :label="'Busqueda de ' + title"
                    ></VTextField>
                  </VCol>
                  <VCol sm="3"></VCol>
                  <VCol sm="4" class="pt-20 py-2">
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
                        <span v-if="index === 2" class="grey--text caption"
                          >(otras {{ selectedHeaders.length - 2 }}+)</span
                        >
                      </template>
                    </VAutocomplete>
                  </VCol>
                  <VCol sm="1" class="pt-20 py-2">
                    <v-dialog v-model="dialog" max-width="600px">
                      <template v-slot:activator="{ props: activatorProps }">
                        <VBtn
                          v-bind="activatorProps"
                          :color="$cv('principal')"
                          size="x-large"
                          :title="'Registrar ' + title"
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

                          <v-toolbar-title>{{ formTitle }}</v-toolbar-title>
 
                           
                        </v-toolbar>

                        <v-form ref="form" v-model="valid" lazy-validation>
                          <VCard-text>
                            <v-container>
                              <VRow>
                                <VCol cols="12" sm="6">
                                  <VTextField
                                    v-model="editedItem.address"
                                    label="Direccion"
                                    required
                                  />
                                </VCol>
                                <VCol cols="12" sm="6">
                                  <VTextField
                                    v-model="editedItem.telephone"
                                    label="Telefono"
                                    required
                                  />
                                </VCol>
                                <VCol cols="12" sm="6">
                                  <VTextField
                                    v-model="editedItem.firstname"
                                    label="Nombre"
                                  />
                                </VCol>
                                <VCol cols="12" sm="6">
                                  <VAutocomplete
                                        v-model="editedItem.locality_id"
                                        :items="localities"
                                        item-title="name"
                                        item-value="id"
                                        label="Localidad"
                                        clearable
                                      />
                                </VCol>
                                
                                
                                <VCol cols="12" sm="6">
                                  <VTextField v-model="editedItem.email" label="Email" />
                                </VCol>
                              </VRow>
                            </v-container>
                          </VCard-text>
                        </v-form>
                        <VCardActions>
                        <VSpacer />
                        <VBtn  variant="outlined" color="primary" @click="dialog = false">Cancelar</VBtn>
                        <VBtn class="bg-primary" color="white" @click="$save()">Guardar</VBtn>
                      </VCardActions>
                      </VCard>
                    </v-dialog>
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>
          </template>
          <!-- status -->
          <template #item.active="{ item }">
            <VChip
              :color="$resolveStatusVariant(item.active).color"
              density="comfortable"
            >
              {{ $resolveStatusVariant(item.active).text }}
            </VChip>
          </template>
          <!-- Actions -->
          <template #item.actions="{ item }">
            <div class="d-flex flex-wrap gap-1 align-center" style="min-width: 120px;">
              <IconBtn
                size="small"
                title="Editar"
                @click="
                  vista = false;
                  $editItem(item.id);
                "
              >
                <VIcon icon="ri-pencil-line" />
              </IconBtn>
              <VSwitch
                v-model="item.active"
                :true-value="1"
                :false-value="0"
                color="primary"
                hide-details 
                title="Activar o Inactivar"
                @click="$toggleActive(item)"
              /> 
              
              <IconBtn
                size="small"
                title="Eliminar"
                @click="
                  vista = false;
                  $deleteItem(item.id, item.address);
                "
              >
                <VIcon icon="ri-delete-bin-line" />
              </IconBtn>

              
            </div>
          </template>
        </VDataTable>

        <v-snackbar v-model="snackbar" :bottom="true" :color="color" :timeout="timeout">
          <div v-html="text"></div>

          <template v-slot:action="{ attrs }">
            <v-btn dark text v-bind="attrs" @click="snackbar = false"> Cerrar </v-btn>
          </template>
        </v-snackbar>
    
    </VCardText>
  </VCard>
</template>

<script>
function title() {
  return "Clientes";
}

export default {
  data: (vm) => ({
    dessertName: "",
    valid: true,
    title: title(),
    route: "customers",
    dialog: false,
    snackbar: false,
    visible: true,
    text: "Registro Insertado",
    color: "success",
    timeout: 4000,
    search: "",
    vista: false,
    headers: [
      { title: "Acciones", key: "actions", value: "actions", sortable: false, width:"150px" },
      { title: "Direccion", filterable: true, key: "address" },
      { title: "Localidad", filterable: true, key: "locality" },
      { title: "Telefono", filterable: true, key: "telephone" },
      { title: "Nombre", filterable: true, key: "firstname" },
      { title: "Estado", key: "active", width:"150px" },
    ],
    desserts: [],
    editedIndex: -1,
    editedItem: {
      id: "",
      address: "",
      locality_id: "",
      telephone: "",
      email: "",
      firstname: "",
      active: 1,
    },
    defaultItem: {
      id: "",
      address: "",
      locality_id: "",
      telephone: "",
      email: "",
      firstname: "",
      active: 1,
    },
    filters: {
      id: "",
      address: "",
      locality_id: "",
      telephone: "",
      email: "",
      firstname: "",
      created_at: "",
      updated_at: "",
    },
    filterKey: [],
    selectedHeaders: [],
    localities:[]
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "Registrar " + this.title : "Editar " + this.title;
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
    this.loadLocalities();
  },

  methods: {
    async loadLocalities() {
      try {
        const response =await this.$axios.get( this.$routes["localities"])
        this.localities = response.data.data;
      } catch (error) {
        console.error("Error al cargar localidades:", error);
      }
    },
    filterDessertName(item) {
      return item.name.toLowerCase().includes(this.dessertName.toLowerCase());
    },
    filterByName(item) {
      return this.$filterBy(item, "firstname");
    },
    filterByActive(item) {
      return this.$filterBy(item, "active");
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
