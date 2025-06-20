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
                    <v-dialog v-model="dialog" max-width="50%" >
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
                          <v-btn icon="ri-close-line" color="white" @click="dialog = false" />
                          <v-toolbar-title>{{ formTitle }}</v-toolbar-title>
                          <v-spacer />
                        </v-toolbar>

                        <v-form ref="form" v-model="valid" >
                          <VCard-text >
                            <v-container class="py-0">
                              <VRow class="py-0">
                                <VCol cols="12" sm="6">
                                  <VTextField v-model="editedItem.nombre" label="Nombre" required />
                                </VCol>
                                <VCol cols="12" sm="6">
                                  <VTextField v-model="editedItem.codigo" label="Código" />
                                </VCol>
                                <VCol cols="12" sm="6">
                                  <VAutocomplete
                                    v-model="editedItem.productos_categorias_id"
                                    :items="categorias"
                                    item-title="nombre"
                                    item-value="id"
                                    label="Categoría"
                                    clearable 
                                  />
                                </VCol>
                                <VCol cols="12" sm="6">
                                  <VAutocomplete
                                    v-model="editedItem.marca_id"
                                    :items="marcas"
                                    item-title="nombre"
                                    item-value="id"
                                    label="Marca"
                                    clearable
                                  />
                                </VCol>
                                <VCol cols="12" sm="6">
                                  <VTextField
                                    v-model="editedItem.precio_compra"
                                    label="Precio de Compra"
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
                              </VRow>
                              <VRow class="pr-3">
                                <VCol cols="12" sm="11" class="pr-0">
                                  <VFileInput
                                    v-model="editedItem.imageFiles"
                                    label="Imagen del Producto"
                                    prepend-icon="ri-image-line"
                                    accept="image/*"
                                    @change="handleImageUpload"
                                    :clearable="true"
                                    :show-size="true"
                                    :truncate-length="15"
                                    :model-value="getImageInputValue()"
                                  >
                                    <template v-slot:selection="{ file }">
                                      <span v-if="file">{{ file.name }}</span>
                                      <span v-else-if="editedItem.image">
                                        {{ getImageName() }}
                                      </span>
                                    </template>
                                  </VFileInput>
                                </VCol>
                                <VCol cols="12" sm="1" class="pl-1">
                                  <VBtn
                                    v-if="editedItem.image && typeof editedItem.image === 'string'"
                                    color="error"
                                    size="x-large"
                                    @click="clearImage"
                                    class="mt-0"
                                  >
                                    <VIcon icon="ri-delete-bin-line" class="mr-1" />
                                  </VBtn>
                                </VCol>

                                <VCol cols="12" sm="12">
                                  <VImg
                                    v-if="imagePreview"
                                    :src="imagePreview"
                                    max-height="150"
                                    contain
                                    class="mt-2"
                                  />
                                  <VImg 
                                    v-if="editedItem.image"
                                    :src="'storage/productos/' + editedItem.image"
                                    max-height="150"
                                    contain
                                    class="mt-2"
                                  />
                                </VCol>
                              </VRow>
                            </v-container>
                          </VCard-text>
                        </v-form>
                        <VCardActions  >
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

          <template #item.image="{ item }">
            <VImg
              v-if="item.image"
              :src="'/storage/productos/' + item.image"
              max-height="50"
              max-width="50"
              contain
            />
            <span v-else>Sin imagen</span>
          </template>

          <template #item.precio_compra="{ item }">
            {{ formatCurrency(item.precio_compra) }}
          </template>

          <template #item.precio_venta="{ item }">
            {{ formatCurrency(item.precio_venta) }}
          </template>

          <template #item.activo="{ item }">
            <VChip :color="$resolveStatusVariant(item.activo).color" density="comfortable">
              {{ $resolveStatusVariant(item.activo).text }}
            </VChip>
          </template>

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
                title="Activo o Inactivo"
              >
              </VSwitch>
              <IconBtn size="small" @click="vista = false; $editItem(item.id)">
                <VIcon icon="ri-pencil-line" />
              </IconBtn>
              <IconBtn size="small" @click="vista = false; $deleteItem(item.id, item.nombre)">
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
      </v-container>
    </VCardText>
  </VCard>
</template>

<script>
export default {
  data: () => ({
    title: "Productos",
    route: "productos",
    dialog: false,
    snackbar: false,
    text: "Registro Insertado",
    color: "success",
    timeout: 4000,
    search: "",
    imagePreview: "",
    valid: true,
    headers: [
      { title: "ID", align: "start", sortable: false, key: "id" },
      { title: "Nombre", filterable: true, key: "nombre" },
      { title: "Código", filterable: true, key: "codigo" },
      { title: "Imagen", key: "image", sortable: false },
      { title: "Precio Compra", key: "precio_compra" },
      { title: "Precio Venta", key: "precio_venta" },
      { title: "Estado", key: "activo" },
      { title: "Acciones", key: "actions", sortable: false },
    ],
    desserts: [],
    editedIndex: -1,
    categorias: [],
    marcas: [],
    editedItem: {
      id: "",
      nombre: "",
      codigo: "",
      productos_categorias_id: null,
      marca_id: null,
      precio_compra: "",
      precio_venta: "",
      image: "",
      imageFiles: [],
      activo: 1,
    },
    defaultItem: {
      id: "",
      nombre: "",
      codigo: "",
      productos_categorias_id: null,
      marca_id: null,
      precio_compra: "",
      precio_venta: "",
      image: "",
      imageFiles: [],
      activo: 1,
    },
    selectedHeaders: [], 
  }),

  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "Registrar Producto" : "Editar Producto";
    },
    showHeaders() {
      return this.headers.filter((s) => this.selectedHeaders.includes(s));
    },
    filteredDesserts() {
      if (!this.search) return this.desserts;
      const searchTerm = this.search.toLowerCase();
      return this.desserts.filter(item => 
        item.nombre.toLowerCase().includes(searchTerm) ||
        item.codigo?.toLowerCase().includes(searchTerm)
      );
    },
  },

  created() {
    this.$initialize();
    this.selectedHeaders = this.headers;
    this.loadCategorias();
    this.loadMarcas();
  },

  methods: {

    getImageInputValue() {
      // Verificar si imageFiles existe y tiene elementos
      if (this.editedItem.imageFiles && this.editedItem.imageFiles.length > 0) {
        return this.editedItem.imageFiles;
      }
      if (this.editedItem.image) {
        // Crear un objeto File ficticio para mostrar en el input
        return [new File([], this.getImageName(), { type: 'image/*' })];
      }
      return [];
    },

    getImageName() {
      if (!this.editedItem.image) return '';
      // Extraer solo el nombre del archivo de la URL
      const parts = this.editedItem.image.split('/');
      return parts.length > 0 ? parts.pop() : '';
    },
    
         
    async loadCategorias() { 
      try {
        const response = await this.$axios.get(this.$routes["productosCategoria"]);
        this.categorias = response.data.data;
      } catch (error) {
        console.error("Error cargando categorías:", error);
      } finally {
        
      }
    },
    async loadMarcas() {
      try {
        const response = await this.$axios.get(this.$routes["marcas"]);
        this.marcas = response.data.data;
      } catch (error) {
        console.error("Error cargando marcas:", error);
      }
    },
    formatCurrency(value) {
      if (!value) return "$0.00";
      return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
      }).format(value);
    },
    handleImageUpload(event) {
      const file = event.target.files?.[0];
      this.imagePreview = file ? URL.createObjectURL(file) : "";
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
    }
  },
};
</script>
