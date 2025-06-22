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
          <v-btn dark text v-bind="attrs" @click="snackbar = false">Cerrar</v-btn>
        </template>
      </v-snackbar>
     
    </VCardText>
  </VCard>
</template>

<script>
export default {
  data: () => ({
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
      { title: "Nombre", filterable: true, key: "name" },
      { title: "Email", filterable: true, key: "email" },
      { title: "Tenant", key: "tenant.name" },
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
    },
    defaultItem: {
      id: "",
      name: "",
      email: "",
      password: "",
      tenant_id: null,
      roles: [],
      active: 1,
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
          return;
        }
        const response = await this.$axios.get(`${this.$routes['roles']}?tenant_id=${tenantId}`);
        this.roles = response.data.data;
      } catch (error) {
        console.error("Error cargando roles:", error);
      } finally {
        this.rolesLoading = false;
      }
    },

    editItem(item) {
      this.editedIndex = this.desserts.indexOf(item);
      this.editedItem = Object.assign({}, item);
      this.editedItem.roles = item.roles.map(role => role.id);
      this.dialog = true;
    },

    async save() {
      if (!this.$refs.form.validate()) return;

      try {
        const url = this.editedIndex === -1 
          ? this.$routes[this.route] 
          : `${this.$routes[this.route]}/${this.editedItem.id}`;

        const method = this.editedIndex === -1 ? 'post' : 'put';

        const response = await this.$axios[method](url, this.editedItem);

        if (this.editedIndex === -1) {
          this.desserts.push(response.data.data);
        } else {
          Object.assign(this.desserts[this.editedIndex], response.data.data);
        }

        this.showSnackbar('Usuario guardado exitosamente', 'success');
        this.close();
      } catch (error) {
        console.error("Error guardando usuario:", error);
        this.showSnackbar(error.response?.data?.message || 'Error al guardar', 'error');
      }
    },

    async deleteItem(item) {
      if (!confirm(`¿Estás seguro de eliminar al usuario ${item.name}?`)) return;

      try {
        await this.$axios.delete(`${this.$routes[this.route]}/${item.id}`);
        const index = this.desserts.indexOf(item);
        this.desserts.splice(index, 1);
        this.showSnackbar('Usuario eliminado exitosamente', 'success');
      } catch (error) {
        console.error("Error eliminando usuario:", error);
        this.showSnackbar(error.response?.data?.message || 'Error al eliminar', 'error');
      }
    },

    async toggleActive(item) {
      try {
        const response = await this.$axios.patch(`${this.$routes[this.route]}/${item.id}/toggle-active`);
        item.active = response.data.data.active;
        this.showSnackbar('Estado actualizado', 'success');
      } catch (error) {
        console.error("Error cambiando estado:", error);
        this.showSnackbar(error.response?.data?.message || 'Error al cambiar estado', 'error');
        // Revertir el cambio visual si falla
        item.active = !item.active;
      }
    },
    
    close() {
      this.dialog = false;
      this.$nextTick(() => {
        this.editedItem = Object.assign({}, this.defaultItem);
        this.editedIndex = -1;
      });
    },


    showSnackbar(text, color) {
      this.text = text;
      this.color = color;
      this.snackbar = true;
    },
    getTenantColor(item) {
      if (!item.tenant) return 'grey';
      // Ejemplo simple: color basado en el ID del tenant
      const colors = ['primary', 'secondary', 'success', 'info', 'warning', 'error'];
      
      return colors[item.tenant.id];
    },
    getRandomColor(item) {
      const colors = ['primary', 'secondary', 'success', 'info', 'warning', 'error'];
      //return colors[Math.floor(Math.random() * colors.length)];
      console.log(item.roles[0].id);
      return colors[item.roles[0].id];
    },
  },
};
</script>
