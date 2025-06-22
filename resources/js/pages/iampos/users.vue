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
                              <VCol cols="12">
                                <VCard  >
                                  <VCardText class="d-flex">
                                    <!-- 👉 Avatar -->
                                    <VAvatar
                                      v-if="!imagePreview && !editedItem.image"
                                      rounded="lg"
                                      size="100"
                                      class="me-6"
                                      :image="avatar"
                                    />
                                    
                                    <!-- Previsualización de nueva imagen -->
                                    <VAvatar
                                      v-else-if="imagePreview"
                                      rounded="lg"
                                      size="100"
                                      class="me-6"
                                      :image="imagePreview"
                                    />
                                    
                                    <!-- Imagen existente del usuario -->
                                    <VAvatar
                                      v-else-if="editedItem.image"
                                      rounded="lg"
                                      size="100"
                                      class="me-6"
                                      :image="getImageUrl(editedItem.image)"
                                    />
                                                            

                                                                    <!-- 👉 Upload Photo -->
                                                                    <!-- 👉 Upload Photo -->
                                    <div class="d-flex flex-column justify-center gap-5">
                                      <div class="d-flex flex-wrap gap-2">
                                        <!-- Botón para subir foto -->
                                        <VBtn
                                          color="primary"
                                          @click="$refs.fileInput.click()"
                                        >
                                          <VIcon icon="ri-upload-cloud-line" class="d-sm-none" />
                                          <span class="d-none d-sm-block">Subir foto</span>
                                        </VBtn>

                                        <!-- Input file oculto -->
                                        <input
                                          ref="fileInput"
                                          type="file"
                                          name="image"
                                          accept=".jpeg,.png,.jpg,.gif"
                                          hidden
                                          @change="handleFileUpload"
                                        >

                                        <!-- Botón para resetear -->
                                        <VBtn
                                          color="error"
                                          variant="outlined"
                                          @click="resetAvatar"
                                        >
                                          <span class="d-none d-sm-block">Restablecer</span>
                                          <VIcon icon="ri-refresh-line" class="d-sm-none" />
                                        </VBtn>
                                      </div>
                                      <p class="text-body-1 mb-0">
                                        Formatos permitidos: JPG, GIF o PNG. Tamaño máximo 800KB
                                      </p>
                                    </div> 
                                  </VCardText>

                                  <VDivider />
                          
                                </VCard>
                              </VCol>
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
                                      v-model="editedItem.email"
                                      label="Email"
                                      required
                                      :rules="emailRules"
                                    />
                                  </VCol> 
                                  <VCol cols="12" sm="12">
                                    <VAutocomplete
                                      v-model="editedItem.tenant_id"
                                      :items="tenants"
                                      item-title="name"
                                      item-value="id"
                                      label="Tenant"
                                      required
                                      :rules="[v => !!v || 'Tenant es requerido']"
                                    />
                                  </VCol>
                                
                                  <VCol cols="12"> 
                                    <VAutocomplete
                                      v-model="editedItem.roles"
                                      :items="roles"
                                      item-title="name"
                                      item-value="id"
                                      label="Rol"
                                      :disabled="!editedItem.tenant_id"
                                      :loading="rolesLoading"
                                      clearable
                                      :rules="[v => !!v || 'Rol es requerido']"
                                    >
                                      <template v-if="!editedItem.tenant_id" v-slot:prepend-inner>
                                        <VTooltip location="bottom">
                                          <template v-slot:activator="{ props }">
                                            <VIcon v-bind="props" icon="ri-information-line" />
                                          </template>
                                          <span>Seleccione un tenant primero</span>
                                        </VTooltip>
                                      </template>
                                    </VAutocomplete>
                                  </VCol>
                                
                                  <VCol cols="12" sm="12">
                                    <VTextField
                                      v-model="editedItem.password"
                                      label="Contraseña"
                                      :type="showPassword ? 'text' : 'password'"
                                      :append-icon="showPassword ? 'ri-eye-line' : 'ri-eye-off-line'"
                                      @click:append="showPassword = !showPassword"
                                      :rules="passwordRules"
                                      v-if="editedIndex === -1"
                                    />
                                    <VTextField
                                      v-model="editedItem.password"
                                      label="Nueva Contraseña (dejar en blanco para no cambiar)"
                                      :type="showPassword ? 'text' : 'password'"
                                      :append-icon="showPassword ? 'ri-eye-line' : 'ri-eye-off-line'"
                                      @click:append="showPassword = !showPassword"
                                      v-else
                                    />
                                  </VCol>
                                </VRow>
                              </VCol>
                            </VRow>
                            
                          </VContainer>
                        </VCardText>
                        <VCardActions>
                          <VSpacer />
                          <VBtn  variant="outlined" color="primary" @click="dialog = false">Cancelar</VBtn>
                          <VBtn class="bg-primary" color="white" @click="$saveUser()">Guardar</VBtn>
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
        

        <template #item.image="{ item }">
          <VAvatar
            v-if="item.image"
            :image="getImageUrl(item.image)"
           
            max-height="50"
            max-width="50"
            contain
          />
          <span v-else></span>
        </template>
        <template #item.tenant.name="{ item }">
          <VChip
            v-if="item.tenant"
            class="ma-1"
            :color="getTenantColor(item)"
          >
            {{ item.tenant.name }}
          </VChip>
        </template>
        <template #item.roles="{ item }">
          <VChip
            v-for="role in item.roles"
            :key="role.id"
            class="ma-1"
            :color="getRandomColor(item)"
          >
          
            {{ role.name }}
          </VChip>
        </template>

        <template #item.actions="{ item }">
          <div class="d-flex flex-wrap gap-1 align-center" style="min-width: 120px">
            <IconBtn
              size="small"
              class="my-1"
              title="Editar"
              @click="editItem(item)"
            >
              <VIcon icon="ri-pencil-line"  />
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
              @click="$deleteItem(item)"
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

import avatar1 from '@images/avatars/avatar-1.png'


export default {
  data: () => ({
    imagePreview: null,
    imageFile: null,
    avatar: avatar1,
    title: "Usuarios",
    route: "users",
    dialog: false,
    snackbar: false,
    text: "Registro Insertado",
    color: "success",
    timeout: 4000,
    search: "",
    valid: true,
    showPassword: false,
    headers: [
      {
        title: "Acciones",
        key: "actions",
        value: "actions",
        sortable: false,
        width: "150px",
      },
      { title: "Imagen", key: "image", sortable: false, width: "100px" },
      { title: "Nombre", filterable: true, key: "name" },
      { title: "Email", filterable: true, key: "email" },
      { title: "Tenant", key: "tenant.name", width: "100px", width: "100px" },
      { title: "Roles", key: "roles", sortable: false },
      { title: "Estado", key: "active", width: "150px" },
    ],
    desserts: [],
    editedIndex: -1,
    tenants: [],
    roles: [],
    editedItem: {
      id: "",
      name: "",
      email: "",
      password: "",
      tenant_id: null,
      roles: [],
      active: 1,
      image:"",
    },
    defaultItem: {
      id: "",
      name: "",
      email: "",
      password: "",
      tenant_id: null,
      roles: [],
      active: 1,
      image:"",
    },
    selectedHeaders: [],
    emailRules: [
      v => !!v || 'Email es requerido',
      v => /.+@.+\..+/.test(v) || 'Email debe ser válido',
    ],
    passwordRules: [
      v => !!v || 'Contraseña es requerida',
      v => (v && v.length >= 8) || 'Mínimo 8 caracteres',
    ],
    rolesLoading: false,
  }),

  watch: {
    dialog(val) {
      val || this.close();
    },
    'editedItem.tenant_id': {
      handler(newTenantId) {
        if (this.dialog) {
          this.loadRoles(newTenantId);
          // Resetear el rol seleccionado al cambiar tenant
          this.editedItem.roles = [];
        }
      },
      immediate: false
    }
  },

  
  created() {
    this.initialize();
    this.selectedHeaders = this.headers;
    this.loadTenants();
    //this.loadRoles();
  },
  computed: {
    formTitle() {
      return this.editedIndex === -1 ? "Registrar Usuario" : "Editar Usuario";
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
          item.email.toLowerCase().includes(searchTerm) ||
          (item.tenant && item.tenant.name.toLowerCase().includes(searchTerm))
      );
    },
  },


  methods: {
    async initialize() {
      try {
        const response = await this.$axios.get(this.$routes[this.route]);
        this.desserts = response.data.data;
      } catch (error) {
        console.error("Error cargando usuarios:", error);
      }
    },

    async loadTenants() {
      try {
        const response = await this.$axios.get(this.$routes['tenants']);
        this.tenants = response.data.data;
      } catch (error) {
        console.error("Error cargando tenants:", error);
      }
    }, 
    async loadRoles(tenantId) {
      this.rolesLoading = true;
      try {
        if (!tenantId) {
          this.roles = [];
          return Promise.resolve();
        }
        const response = await this.$axios.get(`${this.$routes['roles']}?tenant_id=${tenantId}`);
        this.roles = response.data.data;
        return Promise.resolve();
      } catch (error) {
        console.error("Error cargando roles:", error);
        return Promise.reject(error);
      } finally {
        this.rolesLoading = false;
      }
    },
 

    handleImageUpload(event) {
      const file = event.target.files?.[0];
      this.imagePreview = file ? URL.createObjectURL(file) : "";
    },

    handleFileUpload(event) {
      const file = event.target.files[0]
      if (!file) return
      
      // Validaciones
      const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg']
      if (!validTypes.includes(file.type)) {
        this.showSnackbar('Formato de imagen no válido. Use JPG, PNG o GIF.', 'error')
        return
      }

      if (file.size > 800 * 1024) {
        this.showSnackbar('La imagen es demasiado grande (máx. 800KB)', 'error')
        return
      }

      // Asignar el archivo a editedItem.imageFile para que $saveWithFile lo encuentre
      this.editedItem.imageFile = file
      
      // Crear previsualización
      const reader = new FileReader()
      reader.onload = (e) => {
        this.imagePreview = e.target.result
      }
      reader.readAsDataURL(file)
    },

    // Método para resetear la imagen
    resetAvatar() {
      this.imagePreview = null
      this.imageFile = null
      this.editedItem.image = null
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = ''
      }
    },

    // Método para construir la URL de la imagen
    getImageUrl(imagePath) {
      if (!imagePath) return ''
      if (imagePath.startsWith('http')) return imagePath
      return `${process.env.VUE_APP_API_URL || ''}/storage/users/${imagePath}`
    },

    // Modificar el método editItem
    async editItem(item) {
      this.editedIndex = item.id;
      this.editedItem = Object.assign({}, item)
      
      console.log( this.editedIndex);
      console.log( this.editedItem);
      // Cargar imagen existente
      if (item.image) {
        this.imagePreview = this.getImageUrl(item.image)
      } else {
        this.imagePreview = null
      }
      
      // Abrir diálogo primero
      this.dialog = true
      
      // Esperar a que el diálogo esté abierto
      await this.$nextTick()
      
      // Cargar roles del tenant
      this.loadRoles(item.tenant_id).then(() => {
        // Asignar los roles seleccionados (como objetos completos)
        this.editedItem.roles = this.roles.filter(role => 
          item.roles.some(userRole => userRole.id === role.id)
        )
      })
    },

    // Modificar el método close
    close() {
      this.dialog = false
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem)
        this.editedIndex = -1
        this.imagePreview = null
        this.resetAvatar()
      })
    }, 
     
  
    showSnackbar(text, color) {
      this.text = text;
      this.color = color;
      this.snackbar = true;
    },
    getTenantColor(item) {
      if (!item.tenant) return 'grey'; 
      const colors = ['primary', 'secondary', 'success', 'info', 'warning', 'error'];
      
      return colors[item.tenant.id];
    },
    getRandomColor(item) {
      const colors = ['primary', 'secondary', 'success', 'info', 'warning', 'error'];
     
      return colors[item.roles[0].id];
    },
  },
};
</script>
