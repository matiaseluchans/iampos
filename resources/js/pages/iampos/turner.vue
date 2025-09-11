<template>
  <VCard :title="'Administración de ' + title">
    <VCardText class="d-flex px-2">
       
        <VDataTable
          :headers="showHeaders"
          :items="filteredDesserts"
          :search="search"
          class="text-no-wrap striped-table"
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
                    <v-dialog v-model="dialog" max-width="800px">
                      <template v-slot:activator="{ props: activatorProps }">
                        <VBtn
                          v-bind="activatorProps"
                          color="primary"
                          size="x-large"
                          :title="'Registrar ' + title"
                        >
                          <VIcon size="large" icon="ri-add-circle-line" />
                        </VBtn>
                      </template>
                      <VCard>
                        <v-toolbar color="primary">
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
                                <VCol cols="12">
                                  <VTextField
                                    v-model="editedItem.address"
                                    label="Dirección"
                                    required
                                  />
                                </VCol>
                                <VCol cols="12" sm="6">
                                  <VTextField
                                    v-model="editedItem.client_name"
                                    label="Nombre del Cliente"
                                    required
                                  />
                                </VCol>
                                <VCol cols="12" sm="6">
                                  <VTextField
                                    v-model="editedItem.phone"
                                    label="Teléfono"
                                    required
                                  />
                                </VCol>
                                
                                <VCol cols="12" sm="4">
                                  <VTextField
                                    v-model="editedItem.dog_name"
                                    label="Nombre del Perro"
                                    required
                                  />
                                </VCol>
                                <VCol cols="12" sm="4">
                                  <VSelect
                                    v-model="editedItem.dog_size"
                                    :items="dogSizes"
                                    label="Tamaño del Perro"
                                    required
                                  />
                                </VCol>
                                <VCol cols="12" sm="4">
                                  <VTextField
                                    v-model="editedItem.dog_breed"
                                    label="Raza del Perro"
                                  />
                                </VCol>
                                <VCol cols="12" sm="4">
                                  <VSelect
                                    v-model="editedItem.service"
                                    :items="services"
                                    label="Servicio"
                                    required
                                  />
                                </VCol>
                                <VCol cols="12" sm="4">
                                  <VMenu
                                    ref="dateMenu"
                                    v-model="dateMenu"
                                    :close-on-content-click="false"
                                    transition="scale-transition"
                                    offset-y
                                    min-width="auto"
                                  >
                                    <template v-slot:activator="{ on, attrs }">
                                      <VTextField
                                        v-model="editedItem.date"
                                        label="Fecha del Turno"
                                        prepend-icon="ri-calendar-event-line"
                                        readonly
                                        v-bind="attrs"
                                        v-on="on"
                                      ></VTextField>
                                    </template>
                                    <VDatePicker
                                      v-model="editedItem.date"
                                      no-title
                                      scrollable
                                      :min="new Date().toISOString().substr(0, 10)"
                                      @input="dateMenu = false"
                                    ></VDatePicker>
                                  </VMenu>
                                </VCol>
                                <VCol cols="12" sm="4">
                                  <VSelect
                                    v-model="editedItem.time"
                                    :items="availableTimes"
                                    label="Hora del Turno"
                                    required
                                  />
                                </VCol>
                                <VCol cols="12">
                                  <VTextarea
                                    v-model="editedItem.notes"
                                    label="Comentarios"
                                    rows="2"
                                  />
                                </VCol>
                              </VRow>
                            </v-container>
                          </VCard-text>
                        </v-form>
                        <VCardActions>
                          <VSpacer />
                          <VBtn variant="outlined" color="primary" @click="dialog = false">Cancelar</VBtn>
                          <VBtn class="bg-primary" color="white" @click="save">Guardar</VBtn>
                        </VCardActions>
                      </VCard>
                    </v-dialog>
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>
          </template>
          <!-- status -->
          <template #item.status="{ item }">
            <VChip
              :color="resolveStatusVariant(item.status).color"
              density="comfortable"
            >
              {{ resolveStatusVariant(item.status).text }}
            </VChip>
          </template>
          <!-- Actions -->
          <template #item.actions="{ item }">
            <div class="d-flex flex-wrap gap-1 align-center" style="min-width: 120px;">
              <IconBtn
                size="small"
                title="Editar"
                @click="editItem(item)"
              >
                <VIcon icon="ri-pencil-line" />
              </IconBtn>
              <IconBtn
                size="small"
                title="Cancelar Turno"
                @click="cancelItem(item)"
              >
                <VIcon icon="ri-close-circle-line" />
              </IconBtn>
              <IconBtn
                size="small"
                title="Eliminar"
                @click="deleteItem(item)"
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
  return "Turnos";
}

export default {
  data: (vm) => ({
    dessertName: "",
    valid: true,
    title: title(),
    dialog: false,
    dateMenu: false,
    snackbar: false,
    text: "Turno guardado correctamente",
    color: "success",
    timeout: 4000,
    search: "",
    headers: [
      { title: "Acciones", key: "actions", value: "actions", sortable: false, width: "150px" },
      { title: "Fecha", filterable: true, key: "date" },
      { title: "Hora", filterable: true, key: "time" },
      { title: "Direccion", filterable: true, key: "address" },
      
      { title: "Cliente", filterable: true, key: "client_name" },
      { title: "Teléfono", filterable: true, key: "phone" },
      { title: "Perro", filterable: true, key: "dog_name" },
      { title: "Raza", filterable: true, key: "dog_breed" },
      { title: "Tamaño", filterable: true, key: "dog_size" },
      { title: "Servicio", filterable: true, key: "service" },
      { title: "Estado", key: "status", width: "150px" },
    ],
    desserts: [],
    editedIndex: -1,
    editedItem: {
      id: "",
      client_name: "",
      phone: "",
      address: "",
      dog_name: "",
      dog_breed: "",
      dog_size: "",
      service: "",
      date: "",
      time: "",
      notes: "",
      status: "programado"
    },
    defaultItem: {
      id: "",
      client_name: "",
      phone: "",
      address: "",
      dog_name: "",
      dog_breed: "",
      dog_size: "",
      service: "",
      date: "",
      time: "",
      notes: "",
      status: "programado"
    },
    selectedHeaders: [],
    dogSizes: ["Pequeño", "Mediano", "Grande", "Gigante"],
    services: ["Baño", "Corte de pelo", "Baño y corte", "Limpieza dental", "Corte de uñas", "Tratamiento antipulgas"],
    availableTimes: [
      "08:00", "08:30", "09:00", "09:30", "10:00", "10:30", 
      "11:00", "11:30", "12:00", "12:30", "13:00", "13:30",
      "14:00", "14:30", "15:00", "15:30", "16:00", "16:30", "17:00","17:30",
      "18:00", "18:30", "19:00", "19:30", "20:00", "20:30", "21:00","21:30",
    ]
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "Registrar " + this.title : "Editar " + this.title;
    },
    showHeaders() {
      return this.headers.filter((s) => this.selectedHeaders.includes(s));
    },
    filteredDesserts() {
      if (this.search) {
        return this.desserts.filter(item => {
          const searchTerm = this.search.toLowerCase();
          return (
            item.client_name.toLowerCase().includes(searchTerm) ||
            item.phone.toLowerCase().includes(searchTerm) ||
            item.dog_name.toLowerCase().includes(searchTerm) ||
            item.dog_breed.toLowerCase().includes(searchTerm) ||
            item.service.toLowerCase().includes(searchTerm)
          );
        });
      }
      return this.desserts;
    }
  },

  watch: {
    dialog(val) {
      val || this.close();
    },
  },

  created() {
    this.initialize();
    this.selectedHeaders = this.headers;
  },

  methods: {
    initialize() {
      // Cargar datos de ejemplo
      this.desserts = [
        {
          id: 1,
          client_name: "María García",
          phone: "555-1234",
          address: "Calle Principal 123",
          dog_name: "Max",
          dog_breed: "Labrador",
          dog_size: "Grande",
          service: "Baño y corte",
          date: "2023-12-15",
          time: "10:00",
          notes: "Perro nervioso con secador",
          status: "programado"
        },
        {
          id: 2,
          client_name: "Juan Pérez",
          phone: "555-5678",
          address: "Avenida Central 456",
          dog_name: "Luna",
          dog_breed: "Caniche",
          dog_size: "Pequeño",
          service: "Corte de pelo",
          date: "2023-12-16",
          time: "11:30",
          notes: "",
          status: "completado"
        }
      ];
    },

    editItem(item) {
      this.editedIndex = this.desserts.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.dialog = true;
    },

    deleteItem(item) {
      const index = this.desserts.indexOf(item);
      if (confirm('¿Está seguro de que desea eliminar este turno?')) {
        this.desserts.splice(index, 1);
        this.showNotification("Turno eliminado correctamente", "success");
      }
    },

    cancelItem(item) {
      const index = this.desserts.indexOf(item);
      if (confirm('¿Está seguro de que desea cancelar este turno?')) {
        this.desserts[index].status = "cancelado";
        this.showNotification("Turno cancelado correctamente", "warning");
      }
    },

    close() {
      this.dialog = false;
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },

    save() {
      if (this.editedIndex > -1) {
        Object.assign(this.desserts[this.editedIndex], this.editedItem);
        this.showNotification("Turno actualizado correctamente", "success");
      } else {
        this.editedItem.id = Math.max(...this.desserts.map(t => t.id), 0) + 1;
        this.desserts.push(this.editedItem);
        this.showNotification("Turno creado correctamente", "success");
      }
      this.close();
    },

    showNotification(message, color) {
      this.text = message;
      this.color = color;
      this.snackbar = true;
    },

    resolveStatusVariant(status) {
      if (status === 'programado') return { color: 'primary', text: 'Programado' };
      if (status === 'completado') return { color: 'success', text: 'Completado' };
      if (status === 'cancelado') return { color: 'error', text: 'Cancelado' };
      return { color: 'secondary', text: 'Desconocido' };
    }
  },
  
  mounted() {
    console.log("Componente " + this.title + " creado");
  },
};
</script>

<style>
.striped-table .v-data-table__tr:nth-of-type(odd) {
  background-color: rgba(0, 0, 0, 0.02);
}
</style>
