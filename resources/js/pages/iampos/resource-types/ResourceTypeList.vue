<template>
  <VCard :title="'Administración de Tipos de Recurso'">
    <VCardText class="d-flex px-2"> 
        <VDataTable
          :headers="showHeaders"
          :items="filteredResourceTypes"
          :search="search"
          :loading="loading"
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
                      label="Búsqueda de Tipos de Recurso"
                    ></VTextField>
                  </VCol>
                  <VCol sm="3" class="pt-20 py-2">
                    <VSelect
                      v-model="filterShared"
                      :items="sharedOptions"
                      label="Tipo de Capacidad"
                      variant="outlined"
                      clearable                      
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
                        <span v-if="index === 2" class="grey--text caption"
                          >(otras {{ selectedHeaders.length - 2 }}+)</span
                        >
                      </template>
                    </VAutocomplete>
                  </VCol>
                  <VCol sm="1" class="pt-20 py-2">
                    <VBtn
                      @click="openCreateDialog"
                      :color="$cv('principal')"
                      size="x-large"
                      title="Registrar Tipo de Recurso"
                    >
                      <VIcon size="large" icon="ri-add-circle-line" />
                    </VBtn>
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>
          </template>

          <!-- Columna: Tipo de Capacidad -->
          <template #item.is_shared_capacity="{ item }">
            <VChip
              :color="getCapacityTypeColor(item.is_shared_capacity)"
              density="comfortable"
            >
              {{ item.is_shared_capacity ? 'Compartida' : 'Individual' }}
            </VChip>
          </template>

          <!-- Columna: Capacidad Máxima -->
          <template #item.max_capacity_per_reservation="{ item }">
            <VChip
              v-if="item.max_capacity_per_reservation"
              color="primary"
              variant="outlined"
              density="comfortable"
            >
              {{ item.max_capacity_per_reservation }}
            </VChip>
            <span v-else class="text-disabled">Sin límite</span>
          </template>

          <!-- Columna: Recursos Asociados -->
          <template #item.resources_count="{ item }">
            <VChip
              color="info"
              density="comfortable"
            >
              {{ item.resources_count || 0 }}
            </VChip>
          </template>

          <!-- Columna: Estado -->
          <template #item.active="{ item }">
            <VChip
              :color="getStatusColor(item)"
              density="comfortable"
            >
              {{ getStatusText(item) }}
            </VChip>
          </template>

          <!-- Columna: Acciones -->
          <template #item.actions="{ item }">
            <div class="d-flex flex-wrap gap-1 align-center" style="min-width: 120px;">
              <IconBtn
                size="small"
                title="Editar"
                @click="editItem(item)"
              >
                <VIcon icon="ri-pencil-line" />
              </IconBtn>
              
              <VSwitch
                v-model="item.active"
                :true-value="true"
                :false-value="false"
                color="primary"
                hide-details 
                title="Activar o Inactivar"
                @change="toggleActive(item)"
              />
              
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

        <!-- Dialog para crear/editar (Componente Separado) -->
        <ResourceTypeForm
          v-model="formDialog"
          :resource-type="selectedResourceType"
          :mode="formMode"
          @saved="handleSaved"
          @cancel="formDialog = false"
        />

        <!-- Snackbar para mensajes -->
        <v-snackbar v-model="snackbar.show" :color="snackbar.color" :timeout="3000">
          <div v-html="snackbar.message"></div>
          <template v-slot:action="{ attrs }">
            <v-btn dark text v-bind="attrs" @click="snackbar.show = false"> 
              Cerrar 
            </v-btn>
          </template>
        </v-snackbar>
      
    </VCardText>
  </VCard>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useResourceTypes } from '@/composables/useResourceTypes'
import ResourceTypeForm from './ResourceTypeForm.vue'

// Composables
const {
  resourceTypes,
  loading,
  error,
  fetchResourceTypes,
  updateResourceType,
  deleteResourceType
} = useResourceTypes()

// State
const search = ref('')
const filterShared = ref(null)
const formDialog = ref(false)
const selectedHeaders = ref([])
const formMode = ref('create')
const selectedResourceType = ref(null)

// Snackbar
const snackbar = ref({
  show: false,
  message: '',
  color: 'success'
})

// Headers de la tabla
const headers = ref([
    { title: 'Acciones', key: 'actions', sortable: false },
    { title: 'Nombre', key: 'name' },
    { title: 'Descripción', key: 'description' },
    { title: 'Tipo Capacidad', key: 'is_shared_capacity' },
    { title: 'Capacidad Máx.', key: 'max_capacity_per_reservation' },
    { title: 'Recursos', key: 'resources_count' },
    { title: 'Estado', key: 'active' },  
])

// Inicializar headers seleccionados (todos por defecto)
selectedHeaders.value = [...headers.value]

// Opciones para filtro
const sharedOptions = ref([
  { title: 'Capacidad Compartida', value: true },
  { title: 'Capacidad Individual', value: false }
])

// Computed
const showHeaders = computed(() => {
  return selectedHeaders.value
})

const filteredResourceTypes = computed(() => {
  let filtered = resourceTypes.value

  // Filtrar por capacidad compartida
  if (filterShared.value !== null) {
    filtered = filtered.filter(rt => rt.is_shared_capacity === filterShared.value)
  }

  return filtered
})

// Métodos
const fetchData = async () => {
  try {
    await fetchResourceTypes({
      include: 'resources_count'
    })
  } catch (err) {
    showSnackbar('Error al cargar los tipos de recurso', 'error')
  }
}

const openCreateDialog = () => {
  selectedResourceType.value = null
  formMode.value = 'create'
  formDialog.value = true
}

const editItem = (item) => {
  selectedResourceType.value = { ...item }
  formMode.value = 'edit'
  formDialog.value = true
}

const deleteItem = async (item) => {
  if (!confirm(`¿Está seguro que desea eliminar el tipo de recurso "${item.name}"?${item.resources_count > 0 ? `\n\nEste tipo de recurso tiene ${item.resources_count} recursos asociados.` : ''}`)) {
    return
  }

  try {
    await deleteResourceType(item.id)
    showSnackbar('Tipo de recurso eliminado exitosamente', 'success')
  } catch (err) {
    showSnackbar(error.value || 'Error al eliminar el tipo de recurso', 'error')
  }
}

const toggleActive = async (item) => {
  try {
    // Actualizar el estado activo del recurso
    const updatedItem = await updateResourceType(item.id, {
      ...item,
      active: item.active
    })
    showSnackbar(`Tipo de recurso ${item.active ? 'activado' : 'inactivado'} exitosamente`, 'success')
  } catch (err) {
    // Revertir el cambio en caso de error
    item.active = !item.active
    showSnackbar(error.value || 'Error al cambiar el estado', 'error')
  }
}

const handleSaved = () => {
  formDialog.value = false
  fetchData()
  showSnackbar(
    formMode.value === 'create' 
      ? 'Tipo de recurso creado exitosamente' 
      : 'Tipo de recurso actualizado exitosamente',
    'success'
  )
}

const getCapacityTypeColor = (isShared) => {
  return isShared ? 'success' : 'info'
}

const getStatusColor = (item) => {
  if (item.deleted_at) return 'error'
  return item.active ? 'success' : 'warning'
}

const getStatusText = (item) => {
  if (item.deleted_at) return 'Eliminado'
  return item.active ? 'Activo' : 'Inactivo'
}

const showSnackbar = (message, color = 'success') => {
  snackbar.value = {
    show: true,
    message,
    color
  }
}

// Lifecycle
onMounted(() => {
  fetchData()
})
</script>

<style scoped>
.striped-table {
  width: 100%;
}
</style>