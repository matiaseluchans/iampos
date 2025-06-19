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
                    <v-dialog v-model="dialog" max-width="50%">
                      <template v-slot:activator="{ props: activatorProps }">
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

                          <v-toolbar-title>{{ formTitle }}</v-toolbar-title>

                          <v-spacer></v-spacer>

                          <v-toolbar-items> </v-toolbar-items>
                        </v-toolbar>

                        <v-form ref="form" v-model="valid" lazy-validation>
                          <VCard-text>
                            <v-container>
                              <VRow>
                                <VCol cols="12" sm="6">
                                  <VTextField
                                    v-model="editedItem.nombre"
                                    label="Nombre"
                                    required
                                  />
                                </VCol>
                                <VCol cols="12" sm="6">
                                  <VTextField
                                    v-model="editedItem.codigo"
                                    label="Código"
                                  />
                                </VCol>
                                <VCol cols="12" sm="6">
                                  <VTextField
                                    v-model="editedItem.precio_compra"
                                    label="Precio de Compra"
                                    clear-icon=""
                                    prefix="$"
                                  />
                                </VCol>
                                <VCol cols="12" sm="6">
                                  <VTextField
                                    v-model="editedItem.precio_venta"
                                    label="Precio de Venta"
                                    prefix="$"
                                  />
                                </VCol>
                                <VCol cols="12">
                                  <VFileInput
                                    v-model="editedItem.imageFiles"
                                    label="Imagen del Producto"
                                    prepend-icon="ri-image-line"
                                    accept="image/*"
                                    @change="handleImageUpload"
                                    :clearable="true"
                                    :show-size="true"
                                    :truncate-length="15"
                                    :placeholder="
                                      editedItem.image
                                        ? editedItem.image.split('/').pop()
                                        : ''
                                    "
                                  >
                                    <template v-slot:selection="{ file }">
                                      <span v-if="file">{{ file.name }}</span>
                                      <span v-else-if="editedItem.image">{{
                                        editedItem.image.split("/").pop()
                                      }}</span>
                                    </template>
                                  </VFileInput>

                                  <VBtn
                                    v-if="
                                      editedItem.image &&
                                      typeof editedItem.image === 'string'
                                    "
                                    color="error"
                                    small
                                    @click="clearImage"
                                    class="mt-2"
                                  >
                                    <VIcon icon="ri-delete-bin-line" class="mr-1" />
                                    Eliminar Imagen
                                  </VBtn>

                                  <VImg
                                    v-if="imagePreview"
                                    :src="imagePreview"
                                    max-height="150"
                                    contain
                                    class="mt-2"
                                  />
                                </VCol>
                                <!--
                                <VCol cols="12">
                                  <VFileInput
                                    v-model="editedItem.image"
                                    label="Imagen del Producto"
                                    prepend-icon="ri-image-line"
                                    accept="image/*"
                                    @change="handleImageUpload"
                                    :clearable="true"
                                    :show-size="true"
                                    :truncate-length="15"
                                    :placeholder="editedItem.image ? editedItem.image.split('/').pop() : ''"
                                  >
                                    <template v-slot:selection="{ file }">
                                      <span v-if="file">{{ file.name }}</span>
                                      <span v-else-if="editedItem.image">{{ editedItem.image.split('/').pop() }}</span>
                                    </template>
                                  </VFileInput>
                                  <VImg
                                    v-if="imagePreview"
                                    :src="imagePreview"
                                    max-height="150"
                                    contain
                                    class="mt-2"
                                  />
                                </VCol>-->
                                <!--<VCol cols="12">
                                  <VSwitch v-model="editedItem.activo" label="Activo" />
                                </VCol>-->
                              </VRow>
                            </v-container>
                          </VCard-text>
                        </v-form>
                        <VCardActions>
                          <VSpacer />
                          <VBtn text @click="dialog = false">Cancelar</VBtn>
                          <VBtn color="primary" @click="$saveWithFile()">Guardar</VBtn>
                        </VCardActions>
                      </VCard>
                    </v-dialog>
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>
          </template>

          <!-- Imagen del producto -->
          <template #item.image="{ item }">
            <VImg
              v-if="item.image"
              :src="'/storage/' + item.image"
              max-height="50"
              max-width="50"
              contain
            />
            <span v-else>Sin imagen</span>
          </template>

          <!-- Precios -->
          <template #item.precio_compra="{ item }">
            {{ formatCurrency(item.precio_compra) }}
          </template>

          <template #item.precio_venta="{ item }">
            {{ formatCurrency(item.precio_venta) }}
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
              <VSwitch
                v-model="item.activo"
                :true-value="1"
                :false-value="0"
                @click="$toggleActivo(item)"
                color="primary"
                hide-details
                class="pt-2 mt-0"
                title="Activar o Inactivar"
              >
              </VSwitch>
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
                  $deleteItem(item.id, item.nombre);
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
      </v-container>
    </VCardText>
  </VCard>
</template>

<script>
function title() {
  return "Productos";
}

export default {
  data: (vm) => ({
    dessertName: "",
    valid: true,
    title: title(),
    route: "productos",
    dialog: false,
    snackbar: false,
    visible: true,
    text: "Registro Insertado",
    color: "success",
    timeout: 4000,
    search: "",
    vista: false,
    imagePreview: "",
    headers: [
      {
        title: "ID",
        align: "start",
        sortable: false,
        key: "id",
      },
      { title: "Nombre", filterable: true, key: "nombre" },
      { title: "Código", filterable: true, key: "codigo" },
      { title: "Imagen", key: "image", sortable: false },
      { title: "Precio Compra", key: "precio_compra" },
      { title: "Precio Venta", key: "precio_venta" },
      { title: "Estado", key: "activo" },
      { title: "Acciones", key: "actions", value: "actions", sortable: false },
    ],
    desserts: [],
    editedIndex: -1,
    editedItem: {
      id: "",
      nombre: "",
      codigo: "",
      productos_categorias_id: null,
      precio_compra: "",
      precio_venta: "",
      image: "", // URL de la imagen (string)
      imageFiles: [], // Array de archivos (aunque solo usemos 1)
      activo: 1,
    },
    defaultItem: {
      id: "",
      nombre: "",
      codigo: "",
      productos_categorias_id: null,
      precio_compra: "",
      precio_venta: "",
      image: "", // URL de la imagen (string)
      imageFiles: [], // Array de archivos (aunque solo usemos 1)
      activo: 1,
    },
    filters: {
      id: "",
      nombre: "",
      codigo: "",
      precio_compra: "",
      precio_venta: "",
      created_at: "",
      updated_at: "",
    },
    filterKey: [],
    selectedHeaders: [],
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
  },

  methods: {
    formatCurrency(value) {
      if (!value) return "$0.00";
      return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
      }).format(value);
    },

    handleImageUpload(event) {
      const file = event.target.files?.[0]; // Si es un evento nativo
      // O bien (dependiendo de Vuetify):
      //const file = event; // Si Vuetify pasa el File directamente

      console.log(file instanceof File);
      if (file) {
        this.imagePreview = URL.createObjectURL(file);
      } else {
        this.imagePreview = ""; // Limpiar si no hay archivo
      }
    },
    clearImage() {
      this.editedItem.image = "";
      this.editedItem.imageFiles = [];
      this.imagePreview = "";
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
