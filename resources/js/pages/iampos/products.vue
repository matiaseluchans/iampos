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
                  <v-dialog v-model="dialog" max-width="50%">
                    <template v-slot:activator="{ props: activatorProps }">
                      <VBtn
                        v-bind="activatorProps"
                        :color="$cv('principal')"
                        size="x-large"
                        :title="'Registrar ' + title"
                        @click="openDialog"
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
                      </v-toolbar>

                      <v-form ref="form" v-model="valid">
                        <VCard-text>
                          <v-container class="py-0  my-0">
                            <VRow class="py-0  my-0">
                              <VCol cols="12" sm="6">
                                <VTextField
                                  v-model="editedItem.name"
                                  label="Nombre"
                                  required
                                  density="compact"
                                />
                              </VCol>
                              <VCol cols="12" sm="6">
                                <VTextField
                                  v-model="editedItem.code"
                                  label="Código"
                                  density="compact"
                                />
                              </VCol>
                              <VCol cols="12" sm="6">
                                <VAutocomplete
                                  v-model="editedItem.category_id"
                                  :items="categories"
                                  item-title="name"
                                  item-value="id"
                                  label="Categoría"
                                  density="compact"
                                  clearable
                                />
                              </VCol><VCol cols="12" sm="6">
                                <VAutocomplete
                                  v-model="editedItem.brand_id"
                                  :items="brands"
                                  item-title="name"
                                  item-value="id"
                                  label="Marca"
                                  density="compact"
                                  clearable
                                />
                              </VCol>
                            </VRow>
                             

                             
                            <VRow class="py-0  my-0">
                              <VCol cols="12" sm="6">
                                <VTextField
                                  v-model.number="editedItem.purchase_price"
                                  label="Precio de Compra"
                                  type="number"
                                  density="compact"
                                  prefix="$"
                                />
                              </VCol>
                              <VCol cols="12" sm="6">
                                <VTextField
                                  v-model.number="editedItem.order"
                                  label="Orden"
                                  type="number"
                                  density="compact"
                                  
                                />
                              </VCol>
                            </VRow>
                            <VRow class="py-0 my-0">
                              <VCol cols="12">
                                <VDataTable
                                  :headers="priceListHeaders"
                                  :items="priceListsWithPrices"
                                  :hide-default-footer="true"
                                  class="elevation-1">
                                  <template #item.name="{ item }">
                                    {{ item.name }}
                                  </template>
                                  <template #item.sale_price="{ item }">
                                    <VTextField
                                      v-model.number="editedItem.price_lists[item.id]"
                                      :label="'Precio'"
                                      type="number"
                                      density="compact"
                                      prefix="$"
                                      hide-details
                                    />
                                  </template>
                                  <template #bottom></template>
                                </VDataTable>
                              </VCol>
                            </VRow>
                            <VRow>
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
                                  :model-value="getImageInputValue()"
                                  density="compact"
                                >
                                  <template v-slot:selection="{ file }">
                                    <span v-if="file">{{ file.name }}</span>
                                    <span v-else-if="editedItem.image">
                                      {{ getImageName() }}
                                    </span>
                                  </template>
                                </VFileInput>
                                <VBtn
                                  v-if="editedItem.image && typeof editedItem.image === 'string'"
                                  color="error"
                                  size="x-large"
                                  @click="clearImage"
                                  class="mt-2"
                                >
                                  <VIcon icon="ri-delete-bin-line" class="mr-1" />
                                </VBtn>
                                <VImg
                                  v-if="imagePreview"
                                  :src="imagePreview"
                                  max-height="150"
                                  contain
                                  class="mt-2"
                                />
                                <VImg
                                  v-if="editedItem.image"
                                  :src="'storage/products/' + editedItem.image"
                                  max-height="150"
                                  contain
                                  class="mt-2"
                                />
                              </VCol>
                            </VRow>
                          </v-container>
                        </VCard-text>
                      </v-form>
                      <VCardActions>
                        <VSpacer />
                        <VBtn text @click="dialog = false">Cancelar</VBtn>
                        <VBtn class="bg-primary" color="white" @click="saveProduct">Guardar</VBtn>
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
            :src="'/storage/products/' + item.image"
            max-height="50"
            max-width="50"
            contain
          />
          <span v-else></span>
        </template>

        <template #item.purchase_price="{ item }">
           
          {{ formatCurrency(item.purchase_price) }}
        </template>

        <template #item.sale_price="{ item }">
          <span v-if="item.price_lists && item.price_lists.length > 0">
            {{ formatCurrency(getSalePriceForDefaultList(item)) }}
          </span>
          <span v-else>
            {{ formatCurrency(item.sale_price) }}
          </span>
        </template>
       
        <template v-slot:[`item.price_list_${priceList.id}`]="{ item }" v-for="priceList in priceLists">
          <span v-if="item.price_lists && item.price_lists.length > 0">
            <span v-for="productPriceList in item.price_lists" :key="productPriceList.id">
              <span v-if="productPriceList.id === priceList.id">
                {{ formatCurrency(productPriceList.pivot.sale_price) }}
              </span>
            </span>
          </span>
          <span v-else>
            $0.00
          </span>
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
              @click="editItem(item.id)"
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
    title: "Productos",
    route: "products",
    dialog: false,
    snackbar: false,
    text: "Registro Insertado",
    color: "success",
    timeout: 4000,
    search: "",
    imagePreview: "",
    valid: true,
    headers: [],
    desserts: [],
    categories: [],
    brands: [],
    priceLists: [],
    editedIndex: -1,
    vista: false,
    editedItem: {
      id: "",
      name: "",
      code: "",
      order:"",
      category_id: null,
      brand_id: null,
      purchase_price: "",
      sale_price: "",
      image: "",
      imageFiles: [],
      active: 1,
      price_lists: {}, // Nuevo objeto para almacenar los precios por lista
    },
    defaultItem: {
      id: "",
      name: "",
      code: "",
      order:"",
      category_id: null,
      brand_id: null,
      purchase_price: "",
      sale_price: "",
      image: "",
      imageFiles: [],
      active: 1,
      price_lists: {},
    },
    selectedHeaders: [], 
    priceListHeaders: [
      { title: 'Lista de Precios', key: 'name', sortable: false },
      { title: 'Precio de Venta', key: 'sale_price', sortable: false, width: '200px' },
    ],
  }),

  computed: {

     priceListsWithPrices() {
      return this.priceLists.map(list => {
        return {
          ...list,
          sale_price: this.editedItem.price_lists[list.id] || 0,
        };
      });
    },
    formTitle() {
      return this.editedIndex === -1 ? "Registrar Producto" : "Editar Producto";
    },
    showHeaders() {
      // Las cabeceras se construyen en 'created'
      return this.headers.filter((s) => this.selectedHeaders.includes(s));
    },
    filteredDesserts() {
      if (!this.search) return this.desserts;
      const searchTerm = this.search.toLowerCase();
      return this.desserts.filter(
        (item) =>
          item.name.toLowerCase().includes(searchTerm) ||
          item.code?.toLowerCase().includes(searchTerm)  
      );
    },
  },

  created() {
    this.$initialize();
    this.loadInitialData();
  },

  watch: {
    dialog(val) {
      val || this.closeDialog();
    },
  },

  methods: {

    getImageInputValue() {
      // Verificar si imageFiles existe y tiene elementos
      if (this.editedItem.imageFiles && this.editedItem.imageFiles.length > 0) {
        return this.editedItem.imageFiles;
      }
      if (this.editedItem.image) {
        // Crear un objeto File ficticio para mostrar en el input
        return [new File([], this.getImageName(), { type: "image/*" })];
      }
      return [];
    },

    getImageName() {
      if (!this.editedItem.image) return "";
      // Extraer solo el nombre del archivo de la URL
      const parts = this.editedItem.image.split("/");
      return parts.length > 0 ? parts.pop() : "";
    },

    async loadCategories() {
      try {
        const response = await this.$axios.get(this.$routes["categories"]);
        this.categories = response.data.data;
      } catch (error) {
        console.error("Error cargando categorías:", error);
      } finally {
      }
    },
    async loadBrands() {
      try {
        const response = await this.$axios.get(this.$routes["brands"]);
        this.brands = response.data.data;
      } catch (error) {
        console.error("Error cargando marcas:", error);
      }
    },

    async loadInitialData() {
      await this.loadPriceLists();
      this.buildHeaders();
      this.selectedHeaders = this.headers;
      this.loadCategories();
      this.loadBrands();
      // Asegúrate de llamar a la función que carga los productos
      this.$initialize();
    },

    buildHeaders() {
      const priceListHeaders = this.priceLists.map(list => ({
        title: `Lista ${list.name}`,
        key: `price_list_${list.id}`, // Usaremos esta key para la plantilla
        sortable: false,
      }));

      const baseHeaders = [
        {
          title: "Acciones",
          key: "actions",
          value: "actions",
          sortable: false,
          width: "150px",
        },
        {
          title: "Cód.",
          filterable: true,
          key: "code",
          width: "50px",
          class: "column-code",
        },
        { title: "Nombre", filterable: true, key: "name" },
        { title: "Orden", filterable: true, key: "order" },
        { title: "Imagen", key: "image", sortable: false },
        
      ];

      if (this.$is("superadmin")|| this.$is("bebidas-admin") || this.$is("petshop-admin")){
        baseHeaders.push({ title: "Precio Compra", key: "purchase_price" });
      }
      // Insertar los nuevos headers de listas de precios después de "Precio Compra"
      this.headers = [...baseHeaders, ...priceListHeaders, { title: "Estado", key: "active", width: "150px" }];
    },

    async loadPriceLists() {
      try {
        const response = await this.$axios.get(this.$routes["priceLists"]);
        this.priceLists = response.data.data;
      } catch (error) {
        console.error("Error loading price lists:", error);
      }
    },

    async editItem(id) {
      this.editedIndex = id;
      try {
        const response = await this.$axios.get(`api/${this.route}/${id}`);
        const item = response.data.data;

        // Limpiar el objeto de precios de listas
        this.editedItem.price_lists = {};

        // Mapear los precios de las listas existentes
        if(item.price_lists.length >0 ){
          item.price_lists.forEach(priceList => {
            this.editedItem.price_lists[priceList.id] = priceList.pivot.sale_price;
          });
        }

        // Copiar el resto de los datos del producto
        Object.assign(this.editedItem, {
          id: item.id,
          name: item.name,
          code: item.code,
          order: item.order,
          category_id: item.category_id,
          brand_id: item.brand_id,
          purchase_price: item.purchase_price,
          sale_price: item.sale_price, // Mantener el precio por defecto
          image: item.image,
          imageFiles: [],
          active: item.active,
        });

        // Asegurarse de que todos los precios de listas existan en el editedItem
        this.priceLists.forEach(list => {
          if (!this.editedItem.price_lists[list.id]) {
            this.editedItem.price_lists[list.id] = null;
          }
        });

        this.imagePreview = null;
        this.dialog = true;
      } catch (error) {
        console.error("Error al editar:", error);
      }
    },

    async saveProduct() {
      try {
        const data = new FormData();
        // Agregar campos de texto
        for (const key in this.editedItem) {
          if (this.editedItem[key] !== null && this.editedItem[key] !== '') {
            if (key === 'price_lists') {
              data.append(key, JSON.stringify(this.editedItem[key]));
            } else if (key === 'imageFiles' && this.editedItem.imageFiles[0]) {
              data.append('image', this.editedItem.imageFiles[0]);
            } else if (key !== 'id' && key !== 'image' && key !== 'imageFiles') {
              data.append(key, this.editedItem[key]);
            }
          }
        }
        // Manejar el caso de edición
        if (this.editedItem.id) {
          data.append('_method', 'PUT');
          await this.$axios.post(`api/${this.route}/${this.editedItem.id}`, data);
        } else {
          await this.$axios.post(`api/${this.route}`, data);
        }

        this.closeDialog();
        this.$initialize();
        this.showSnackbar("Registro guardado con éxito.");
      } catch (error) {
        console.error("Error al guardar:", error);
        this.showSnackbar("Error al guardar el registro.", "error");
      }
    },

    closeDialog() {
      this.dialog = false;
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
        this.imagePreview = "";
      });
    },

    getSalePriceForDefaultList(item) {
      const defaultList = item.price_lists.find(list => list.is_default);
      return defaultList ? defaultList.pivot.sale_price : item.sale_price;
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

    handleImageUpload(event) {
      const file = event.target.files?.[0];
      this.imagePreview = file ? URL.createObjectURL(file) : "";
    },

    clearImage() {
      this.editedItem.image = "";
      this.editedItem.imageFiles = [];
      this.imagePreview = "";
    },

    showSnackbar(message, color = 'success') {
      this.text = message;
      this.color = color;
      this.snackbar = true;
    },
    filterDessertName(item) {
      return item.name.toLowerCase().includes(this.dessertName.toLowerCase());
    },
    filterByName(item) {
      return this.$filterBy(item, "name");
    },
    filterByActive(item) {
      return this.$filterBy(item, "active");
    },
    filterByOrder(item) {
      return this.$filterBy(item, "order");
    },
  },
};
</script>
