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
                  />
                </VCol>
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
                      <span v-if="index === 2" class="grey--text caption">
                        (otras {{ selectedHeaders.length - 2 }}+)
                      </span>
                    </template>
                  </VAutocomplete>
                </VCol>
                <VCol sm="1" class="pt-20 py-2">
                  <v-dialog v-model="dialog" max-width="500px">
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
                        />
                        <v-toolbar-title>{{ formTitle }}</v-toolbar-title>
                        <v-spacer />
                      </v-toolbar>

                      <VForm ref="form" v-model="valid">
                        <VCardText>
                          <VContainer class="py-0">
                            <VRow class="py-0">
                              <VCol cols="12" sm="12">
                                <VRow class="py-0">
                                  <VCol cols="12" sm="12">
                                    <VTextField
                                      v-model="editedItem.name"
                                      label="Nombre"
                                      required
                                      :rules="[v => !!v || 'Nombre es requerido']"
                                    />
                                  </VCol>
                                  <VCol cols="12" sm="12">
                                    <VTextField
                                      v-model="editedItem.code"
                                      label="Código"
                                    />
                                  </VCol>
                                  <VCol cols="12" sm="12">
                                    <VTextField
                                      v-model="editedItem.location"
                                      label="Ubicación"
                                    />
                                  </VCol>
                                   
                                </VRow>
                              </VCol>
                            </VRow>
                          </VContainer>
                        </VCardText>
                        <VCardActions>
                          <VSpacer />
                          <VBtn variant="outlined" color="primary" @click="dialog = false">Cancelar</VBtn>
                          <VBtn class="bg-primary" color="white" @click="$save()">Guardar</VBtn>
                        </VCardActions>
                      </VForm>
                    </VCard>
                  </v-dialog>
                </VCol>
              </VRow>
            </VCardText>
          </VCard>
        </template>

        <template #item.active="{ item }">
          <VChip
            :color="$resolveStatusVariant(item.active).color"
            density="comfortable"
          >
            {{ $resolveStatusVariant(item.active).text }}
          </VChip>
        </template>

        <template #item.actions="{ item }">
          <div class="d-flex flex-wrap gap-1 align-center" style="min-width: 120px">
            <IconBtn
              size="small"
              class="my-1"
              title="Editar"
              @click="$editItem(item.id)"
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
              class="my-1"
              @click="$deleteItem(item.id, item.name)"
            >
              <VIcon icon="ri-delete-bin-line" />
            </IconBtn>
          </div>
        </template>
      </VDataTable>

      <v-snackbar v-model="snackbar" :bottom="true" :color="color" :timeout="timeout">
        <div v-html="text"></div>
        <template v-slot:action="{ attrs }">
          <v-btn dark text v-bind="attrs" @click="snackbar = false">Cerrar</v-btn>
        </template>
      </v-snackbar>
    </VCardText>
  </VCard>
</template>

<script>
export default {
  data: () => ({
    title: "Depositos",
    route: "warehouses",
    dialog: false,
    snackbar: false,
    text: "Registro Insertado",
    color: "success",
    timeout: 4000,
    search: "",
    valid: true,
    headers: [
      {
        title: "Acciones",
        key: "actions",
        value: "actions",
        sortable: false,
        width: "150px",
      },
      { title: "Código", key: "code", width: "100px" },
      { title: "Nombre", filterable: true, key: "name" },
      { title: "Ubicación", key: "location" },
      { title: "Estado", key: "active", width: "150px" },
    ],
    desserts: [],
    editedIndex: -1,
    editedItem: {
      id: "",
      name: "",
      code: "",
      location: "",
      active: 1,
    },
    defaultItem: {
      id: "",
      name: "",
      code: "",
      location: "",
      active: 1,
    },
    selectedHeaders: [],
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "Registrar Deposito" : "Editar Deposito";
    },
    showHeaders() {
      return this.headers.filter((s) => this.selectedHeaders.includes(s));
    },
    filteredDesserts() {
      if (!this.search) return this.desserts;
      const searchTerm = this.search.toLowerCase();
      return this.desserts.filter(
        (item) =>
          item.name.toLowerCase().includes(searchTerm) ||
          item.code?.toLowerCase().includes(searchTerm) ||
          item.location?.toLowerCase().includes(searchTerm)
      );
    },
  },

  created() {
    this.initialize();
    this.selectedHeaders = this.headers;
  },

  methods: {
    async initialize() {
      try {
        const response = await this.$axios.get(this.$routes[this.route]);
        this.desserts = response.data.data;
      } catch (error) {
        console.error("Error cargando depositos:", error);
      }
    },

     

     

      
      

    showSnackbar(text, color) {
      this.text = text;
      this.color = color;
      this.snackbar = true;
    },
  },

  watch: {
    dialog(val) {
      val || this.$close();
    },
  },
};
</script>
