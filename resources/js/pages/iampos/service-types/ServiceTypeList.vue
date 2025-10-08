<template>
  <VCard :title="'Administración de Servicios'">
    <VCardText class="d-flex px-2"> 
        <VDataTable
          :headers="showHeaders"
          :items="filteredServiceTypes"
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
                      label="Búsqueda de Tipos de Servicio"
                    ></VTextField>
                  </VCol>
                  <VCol sm="3" class="pt-20 py-2">
                    <VSelect
                      v-model="filterRequiresResource"
                      :items="requiresResourceOptions"
                      label="Requiere Recurso"
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
                      title="Registrar Tipo de Servicio"
                    >
                      <VIcon size="large" icon="ri-add-circle-line" />
                    </VBtn>
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>
          </template>

          <!-- Columna: Precio Base -->
          <template #item.base_price="{ item }">
            <VChip
              color="primary"
              variant="outlined"
              density="comfortable"
            >
              ${{ formatPrice(item.base_price) }}
            </VChip>
          </template>

          <!-- Columna: Duración -->
          <template #item.duration_minutes="{ item }">
            <span>{{ item.duration_minutes }} min</span>
          </template>

          <!-- Columna: Requiere Recurso -->
          <template #item.requires_resource="{ item }">
            <VChip
              :color="getRequiresResourceColor(item.requires_resource)"
              density="comfortable"
            >
              {{ item.requires_resource ? 'Sí' : 'No' }}
            </VChip>
          </template>

          <!-- Columna: Unidades -->
          <template #item.units_range="{ item }">
            <span>{{ item.min_units }} - {{ item.max_units }}</span>
          </template>

          <!-- Columna: Tipo de Recurso -->
          <template #item.resource_type="{ item }">
            <VChip
              v-if="item.resource_type"
              color="info"
              density="comfortable"
            >
              {{ item.resource_type.name }}
            </VChip>
            <span v-else class="text-disabled">No aplica</span>
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

        <!-- Dialog para crear/editar -->
        <ServiceTypeForm
          v-model="formDialog"
          :service-type="selectedServiceType"
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
import { useServiceTypes } from '@/composables/useServiceTypes'
import ServiceTypeForm from './ServiceTypeForm.vue'

// Composables
const {
  serviceTypes,
  loading,
  error,
  fetchServiceTypes,
  updateServiceType,
  deleteServiceType
} = useServiceTypes()

// State
const search = ref('')
const filterRequiresResource = ref(null)
const formDialog = ref(false)
const selectedHeaders = ref([])
const formMode = ref('create')
const selectedServiceType = ref(null)

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
  /*{ title: 'Descripción', key: 'description' },*/
  { title: 'Precio Base', key: 'base_price' },
  { title: 'Duración', key: 'duration_minutes' },
  { title: 'Unidades', key: 'units_range' },
  { title: 'Máx. Participantes', key: 'max_participants' },
  { title: 'Requiere Recurso', key: 'requires_resource' },
  { title: 'Tipo Recurso', key: 'resource_type' },
  { title: 'Reservas', key: 'reservations_count' },
  { title: 'Estado', key: 'active' },
  
])

// Inicializar headers seleccionados (todos por defecto)
selectedHeaders.value = [...headers.value]

// Opciones para filtro
const requiresResourceOptions = ref([
  { title: 'Requiere Recurso', value: true },
  { title: 'No Requiere Recurso', value: false }
])

// Computed
const showHeaders = computed(() => {
  return selectedHeaders.value
})

const filteredServiceTypes = computed(() => {
  let filtered = serviceTypes.value

  // Filtrar por requiere recurso
  if (filterRequiresResource.value !== null) {
    filtered = filtered.filter(st => st.requires_resource === filterRequiresResource.value)
  }

  return filtered
})

// Métodos
const fetchData = async () => {
  try {
    await fetchServiceTypes()
  } catch (err) {
    showSnackbar('Error al cargar los tipos de servicio', 'error')
  }
}

const openCreateDialog = () => {
  selectedServiceType.value = null
  formMode.value = 'create'
  formDialog.value = true
}

const editItem = (item) => {
  selectedServiceType.value = { ...item }
  formMode.value = 'edit'
  formDialog.value = true
}

const deleteItem = async (item) => {
  const reservationsCount = item.reservations_count || 0
  const message = `¿Está seguro que desea eliminar el tipo de servicio "${item.name}"?${
    reservationsCount > 0 ? `\n\nEste tipo de servicio tiene ${reservationsCount} reservas asociadas.` : ''
  }`

  if (!confirm(message)) {
    return
  }

  try {
    await deleteServiceType(item.id)
    showSnackbar('Tipo de servicio eliminado exitosamente', 'success')
  } catch (err) {
    showSnackbar(error.value || 'Error al eliminar el tipo de servicio', 'error')
  }
}

const toggleActive = async (item) => {
  try {
    // Actualizar el estado activo del servicio
    const updatedItem = await updateServiceType(item.id, {
      ...item,
      active: item.active
    })
    showSnackbar(`Tipo de servicio ${item.active ? 'activado' : 'inactivado'} exitosamente`, 'success')
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
      ? 'Tipo de servicio creado exitosamente' 
      : 'Tipo de servicio actualizado exitosamente',
    'success'
  )
}

const formatPrice = (price) => {
  return parseFloat(price).toLocaleString('es-AR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

const getRequiresResourceColor = (requiresResource) => {
  return requiresResource ? 'warning' : 'success'
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