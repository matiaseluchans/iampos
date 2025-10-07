<template>
  <VCard :title="'Administración de Recursos'">
    <VCardText class="d-flex px-2"> 
        <VDataTable
          :headers="showHeaders"
          :items="filteredResources"
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
                      label="Búsqueda de Recursos"
                    ></VTextField>
                  </VCol>
                  <VCol sm="3" class="pt-20 py-2">
                    <VSelect
                      v-model="filterType"
                      :items="typeOptions"
                      label="Tipo de Recurso"
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
                      title="Registrar Recurso"
                    >
                      <VIcon size="large" icon="ri-add-circle-line" />
                    </VBtn>
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>
          </template>

          <!-- Columna: Tipo de Recurso -->
          <template #item.resource_type="{ item }">
            <VChip
              v-if="item.resource_type"
              color="primary"
              density="comfortable"
            >
              {{ item.resource_type.name }}
            </VChip>
            <span v-else class="text-disabled">No asignado</span>
          </template>

          <!-- Columna: Tipo de Uso -->
          <template #item.is_shared="{ item }">
            <VChip
              :color="getUsageTypeColor(item.is_shared)"
              density="comfortable"
            >
              {{ item.is_shared ? 'Compartido' : 'Exclusivo' }}
            </VChip>
          </template>

          <!-- Columna: Capacidad -->
          <template #item.capacity="{ item }">
            <div class="d-flex align-center gap-2">
              <span>{{ item.capacity }}</span>
              <VProgressLinear
                v-if="item.is_shared"
                :model-value="getUsagePercentage(item)"
                :color="getUsageColor(item)"
                height="8"
                width="60"
                rounded
              />
            </div>
          </template>

          <!-- Columna: Uso Actual -->
          <template #item.current_usage="{ item }">
            <VChip
              :color="getUsageColor(item)"
              density="comfortable"
            >
              {{ item.current_usage || 0 }}/{{ item.capacity }}
            </VChip>
          </template>

          <!-- Columna: Disponibilidad -->
          <template #item.availability="{ item }">
            <VChip
              :color="getAvailabilityColor(item)"
              density="comfortable"
            >
              {{ getAvailabilityText(item) }}
            </VChip>
          </template>

          <!-- Columna: Reservas -->
          <template #item.reservations_count="{ item }">
            <VChip
              color="secondary"
              density="comfortable"
            >
              {{ item.reservations_count || 0 }}
            </VChip>
          </template>

          <!-- Columna: Estado -->
          <template #item.is_active="{ item }">
            <VChip
              :color="getStatusColor(item)"
              density="comfortable"
            >
              {{ getStatusText(item) }}
            </VChip>
          </template>

          <!-- Columna: Acciones -->
          <template #item.actions="{ item }">
            <div class="d-flex flex-wrap gap-1 align-center" style="min-width: 140px;">
              <IconBtn
                size="small"
                title="Editar"
                @click="editItem(item)"
              >
                <VIcon icon="ri-pencil-line" />
              </IconBtn>

              <IconBtn
                size="small"
                title="Actualizar Uso"
                @click="updateUsage(item)"
                :loading="loading"
              >
                <VIcon icon="ri-refresh-line" />
              </IconBtn>
              
              <VSwitch
                v-model="item.is_active"
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

        <!-- Dialog para crear/editar -->
        <ResourceForm
          v-model="formDialog"
          :resource="selectedResource"
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
import { useResources } from '@/composables/useResources'
import ResourceForm from './ResourceForm.vue'

// Composables
const {
  resources,
  loading,
  error,
  fetchResources,
  updateResource,
  deleteResource,
  updateResourceUsage
} = useResources()

// State
const search = ref('')
const filterType = ref(null)
const formDialog = ref(false)
const selectedHeaders = ref([])
const formMode = ref('create')
const selectedResource = ref(null)

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
  { title: 'Tipo', key: 'resource_type' },
  { title: 'Tipo de Uso', key: 'is_shared' },
  { title: 'Capacidad', key: 'capacity' },
  { title: 'Uso Actual', key: 'current_usage' },
  { title: 'Disponibilidad', key: 'availability' },
  { title: 'Reservas', key: 'reservations_count' },
  { title: 'Estado', key: 'is_active' },  
])

// Inicializar headers seleccionados (todos por defecto)
selectedHeaders.value = [...headers.value]

// Opciones para filtro
const typeOptions = ref([
  { title: 'Recursos Compartidos', value: 'shared' },
  { title: 'Recursos Exclusivos', value: 'exclusive' }
])

// Computed
const showHeaders = computed(() => {
  return selectedHeaders.value
})

const filteredResources = computed(() => {
  let filtered = resources.value

  // Filtrar por tipo de uso
  if (filterType.value === 'shared') {
    filtered = filtered.filter(r => r.is_shared)
  } else if (filterType.value === 'exclusive') {
    filtered = filtered.filter(r => !r.is_shared)
  }

  return filtered
})

// Métodos
const fetchData = async () => {
  try {
    await fetchResources()
  } catch (err) {
    showSnackbar('Error al cargar los recursos', 'error')
  }
}

const openCreateDialog = () => {
  selectedResource.value = null
  formMode.value = 'create'
  formDialog.value = true
}

const editItem = (item) => {
  selectedResource.value = { ...item }
  formMode.value = 'edit'
  formDialog.value = true
}

const deleteItem = async (item) => {
  const reservationsCount = item.reservations_count || 0
  const message = `¿Está seguro que desea eliminar el recurso "${item.name}"?${
    reservationsCount > 0 ? `\n\nEste recurso tiene ${reservationsCount} reservas asociadas.` : ''
  }`

  if (!confirm(message)) {
    return
  }

  try {
    await deleteResource(item.id)
    showSnackbar('Recurso eliminado exitosamente', 'success')
  } catch (err) {
    showSnackbar(error.value || 'Error al eliminar el recurso', 'error')
  }
}

const toggleActive = async (item) => {
  try {
    // Actualizar el estado activo del recurso
    const updatedItem = await updateResource(item.id, {
      ...item,
      is_active: item.is_active
    })
    showSnackbar(`Recurso ${item.is_active ? 'activado' : 'inactivado'} exitosamente`, 'success')
  } catch (err) {
    // Revertir el cambio en caso de error
    item.is_active = !item.is_active
    showSnackbar(error.value || 'Error al cambiar el estado', 'error')
  }
}

const updateUsage = async (item) => {
  try {
    await updateResourceUsage(item.id)
    showSnackbar('Uso del recurso actualizado exitosamente', 'success')
    // Recargar datos para reflejar cambios
    fetchData()
  } catch (err) {
    showSnackbar(error.value || 'Error al actualizar el uso del recurso', 'error')
  }
}

const handleSaved = () => {
  formDialog.value = false
  fetchData()
  showSnackbar(
    formMode.value === 'create' 
      ? 'Recurso creado exitosamente' 
      : 'Recurso actualizado exitosamente',
    'success'
  )
}

const getUsageTypeColor = (isShared) => {
  return isShared ? 'info' : 'primary'
}

const getUsagePercentage = (item) => {
  if (!item.is_shared || item.capacity === 0) return 0

  return ((item.current_usage || 0) / item.capacity) * 100
}

const getUsageColor = (item) => {
  if (!item.is_shared) return 'info'
  
  const usage = getUsagePercentage(item)
  if (usage >= 90) return 'error'
  if (usage >= 70) return 'warning'
  return 'success'
}

const getAvailabilityColor = (item) => {
  if (item.deleted_at) return 'error'
  if (!item.is_active) return 'warning'
  
  if (item.is_shared) {
    const available = item.capacity - (item.current_usage || 0)
    if (available <= 0) return 'error'
    if (available < (item.capacity * 0.3)) return 'warning'
    return 'success'
  } else {
    return item.is_active ? 'success' : 'warning'
  }
}

const getAvailabilityText = (item) => {
  if (item.deleted_at) return 'Eliminado'
  if (!item.is_active) return 'Inactivo'
  
  if (item.is_shared) {
    const available = item.capacity - (item.current_usage || 0)
    return `${available} disponible${available !== 1 ? 's' : ''}`
  } else {
    return 'Disponible'
  }
}

const getStatusColor = (item) => {
  if (item.deleted_at) return 'error'
  return item.is_active ? 'success' : 'warning'
}

const getStatusText = (item) => {
  if (item.deleted_at) return 'Eliminado'
  return item.is_active ? 'Activo' : 'Inactivo'
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