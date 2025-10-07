<template>
  <v-dialog v-model="dialog" max-width="800" persistent>
    <VCard>
      <v-toolbar :color="mode === 'create' ? 'primary' : 'secondary'" density="compact">
        <v-toolbar-title class="text-white">
          {{ mode === 'create' ? 'Nuevo Tipo de Servicio' : 'Editar Tipo de Servicio' }}
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-btn icon color="white" @click="closeDialog">
          <v-icon>ri-close-line</v-icon>
        </v-btn>
      </v-toolbar>

      <VCardText class="pt-4">
        <v-form @submit.prevent="submitForm" ref="formRef">
          <VRow>
            <!-- Información Básica -->
            <VCol cols="12" md="6">
              <VTextField
                v-model="form.name"
                label="Nombre del Servicio *"
                variant="outlined"
                :rules="[rules.required]"
                :error-messages="errors.name"
                required
              />
            </VCol>

            <VCol cols="12" md="6">
              <VTextField
                v-model="form.base_price"
                label="Precio Base *"
                type="number"
                step="0.01"
                variant="outlined"
                :rules="[rules.required, rules.minValue(0)]"
                :error-messages="errors.base_price"
                prefix="$"
                required
              />
            </VCol>

            <VCol cols="12">
              <VTextarea
                v-model="form.description"
                label="Descripción del Servicio"
                variant="outlined"
                rows="3"
                :error-messages="errors.description"
                placeholder="Descripción detallada del servicio..."
              />
            </VCol>

            <!-- Configuración de Tiempo -->
            <VCol cols="12" md="4">
              <VTextField
                v-model="form.duration_minutes"
                label="Duración (minutos) *"
                type="number"
                variant="outlined"
                :rules="[rules.required, rules.minValue(1)]"
                :error-messages="errors.duration_minutes"
                required
              />
            </VCol>

            <VCol cols="12" md="4">
              <VTextField
                v-model="form.min_units"
                label="Unidades Mínimas *"
                type="number"
                variant="outlined"
                :rules="[rules.required, rules.minValue(1)]"
                :error-messages="errors.min_units"
                required
              />
            </VCol>

            <VCol cols="12" md="4">
              <VTextField
                v-model="form.max_units"
                label="Unidades Máximas *"
                type="number"
                variant="outlined"
                :rules="[rules.required, rules.minValue(1)]"
                :error-messages="errors.max_units"
                required
              />
            </VCol>

            <!-- Participantes -->
            <VCol cols="12" md="6">
              <VTextField
                v-model="form.max_participants"
                label="Máximo de Participantes *"
                type="number"
                variant="outlined"
                :rules="[rules.required, rules.minValue(1)]"
                :error-messages="errors.max_participants"
                required
              />
            </VCol>

            <!-- Configuración de Recursos -->
            <VCol cols="12" md="6">
              <VSwitch
                v-model="form.requires_resource"
                label="Requiere Recurso"
                color="primary"
                :messages="requiresResourceMessage"
                hide-details
                @change="onRequiresResourceChange"
              />
            </VCol>

            <!-- Tipo de Recurso (solo si requiere recurso) -->
            <VCol cols="12" md="6" v-if="form.requires_resource">
              <VAutocomplete
                v-model="form.resource_type_id"
                :items="resourceTypes"
                item-title="name"
                item-value="id"
                label="Tipo de Recurso *"
                variant="outlined"
                :rules="form.requires_resource ? [rules.required] : []"
                :error-messages="errors.resource_type_id"
                :loading="loadingResourceTypes"
                clearable
              >
                <template v-slot:item="{ props, item }">
                  <v-list-item v-bind="props">
                    <template v-slot:title>
                      <div class="d-flex justify-space-between">
                        <span>{{ item.raw.name }}</span>
                        <VChip size="small" :color="item.raw.is_shared_capacity ? 'success' : 'info'">
                          {{ item.raw.is_shared_capacity ? 'Compartida' : 'Individual' }}
                        </VChip>
                      </div>
                    </template>
                    <template v-slot:subtitle>
                      <span class="text-caption">{{ item.raw.description }}</span>
                    </template>
                  </v-list-item>
                </template>
              </VAutocomplete>
            </VCol>

            <!-- Configuración de Capacidad -->
            <VCol cols="12" md="6" v-if="form.requires_resource">
              <VSwitch
                v-model="form.requires_capacity"
                label="Requiere Control de Capacidad"
                color="primary"
                :messages="requiresCapacityMessage"
                hide-details
              />
            </VCol>

            <VCol cols="12" md="6" v-if="form.requires_capacity">
              <VTextField
                v-model="form.max_capacity_per_reservation"
                label="Capacidad Máxima por Reserva"
                type="number"
                variant="outlined"
                :min="1"
                :error-messages="errors.max_capacity_per_reservation"
                :hint="capacityHint"
                persistent-hint
              />
            </VCol>

            <!-- Información Adicional -->
            <VCol cols="12" v-if="form.requires_resource">
              <VAlert type="info" variant="tonal" density="compact">
                <div class="text-caption">
                  <strong>Servicio con Recurso:</strong> Este servicio requiere la asignación de un recurso específico 
                  (ej: habitación, puesto de trabajo, cancha) para poder ser reservado.
                </div>
              </VAlert>
            </VCol>

            <VCol cols="12" v-else>
              <VAlert type="info" variant="tonal" density="compact">
                <div class="text-caption">
                  <strong>Servicio sin Recurso:</strong> Este servicio no requiere la asignación de un recurso específico 
                  (ej: consulta médica, clase grupal, servicio a domicilio).
                </div>
              </VAlert>
            </VCol>
          </VRow>

          <VCardActions class="px-0">
            <VSpacer></VSpacer>
            <VBtn variant="outlined" @click="closeDialog" :disabled="loading">
              Cancelar
            </VBtn>
            <VBtn 
              color="primary" 
              type="submit" 
              :loading="loading"
              :disabled="!formIsValid"
            >
              {{ mode === 'create' ? 'Crear' : 'Actualizar' }}
            </VBtn>
          </VCardActions>
        </v-form>
      </VCardText>
    </VCard>
  </v-dialog>
</template>

<script setup>
import { ref, computed, watch, defineProps, defineEmits, onMounted } from 'vue'
import { useServiceTypes } from '@/composables/useServiceTypes'
import axios from '@/axios/axios'
import { apiRoute } from '@/helper/apiRoute'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  serviceType: {
    type: Object,
    default: null
  },
  mode: {
    type: String,
    default: 'create',
    validator: (value) => ['create', 'edit'].includes(value)
  }
})

const emit = defineEmits(['update:modelValue', 'saved', 'cancel'])

// Composables
const { createServiceType, updateServiceType, loading, error } = useServiceTypes()

// State
const form = ref({
  name: '',
  description: '',
  base_price: null,
  duration_minutes: 60,
  min_units: 1,
  max_units: 1,
  max_participants: 1,
  requires_resource: false,
  resource_type_id: null,
  requires_capacity: false,
  max_capacity_per_reservation: null
})

const errors = ref({})
const formRef = ref(null)
const resourceTypes = ref([])
const loadingResourceTypes = ref(false)

// Rules de validación
const rules = {
  required: value => !!value || 'Este campo es requerido',
  minValue: (min) => value => value >= min || `El valor debe ser mayor o igual a ${min}`
}

// Computed
const dialog = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const formIsValid = computed(() => {
  const basicFields = form.value.name && form.value.base_price && form.value.duration_minutes && 
                     form.value.min_units && form.value.max_units && form.value.max_participants
  
  if (form.value.requires_resource) {
    return basicFields && form.value.resource_type_id
  }
  
  return basicFields
})

const requiresResourceMessage = computed(() => {
  return form.value.requires_resource 
    ? 'Este servicio requiere un recurso específico'
    : 'Este servicio no requiere un recurso específico'
})

const requiresCapacityMessage = computed(() => {
  return form.value.requires_capacity 
    ? 'Se controlará la capacidad disponible del recurso'
    : 'No se controlará la capacidad del recurso'
})

const capacityHint = computed(() => {
  return form.value.max_capacity_per_reservation 
    ? `Máximo ${form.value.max_capacity_per_reservation} participantes por reserva en este recurso`
    : 'Sin límite de capacidad específico'
})

// Métodos
const loadResourceTypes = async () => {
  loadingResourceTypes.value = true
  try {
    const response = await axios.get(apiRoute.resourceTypes, {
      params: {
        active: true
      }
    })
    resourceTypes.value = response.data.data || response.data
  } catch (err) {
    console.error('Error cargando tipos de recurso:', err)
  } finally {
    loadingResourceTypes.value = false
  }
}

const onRequiresResourceChange = () => {
  if (!form.value.requires_resource) {
    form.value.resource_type_id = null
    form.value.requires_capacity = false
    form.value.max_capacity_per_reservation = null
  }
}

const resetForm = () => {
  form.value = {
    name: '',
    description: '',
    base_price: null,
    duration_minutes: 60,
    min_units: 1,
    max_units: 1,
    max_participants: 1,
    requires_resource: false,
    resource_type_id: null,
    requires_capacity: false,
    max_capacity_per_reservation: null
  }
  errors.value = {}
}

const closeDialog = () => {
  dialog.value = false
  emit('cancel')
}

const submitForm = async () => {
  if (!formIsValid.value) return

  errors.value = {}
  
  try {
    const formData = {
      ...form.value,
      // Asegurar que los valores numéricos sean correctos
      base_price: parseFloat(form.value.base_price),
      duration_minutes: parseInt(form.value.duration_minutes),
      min_units: parseInt(form.value.min_units),
      max_units: parseInt(form.value.max_units),
      max_participants: parseInt(form.value.max_participants),
      max_capacity_per_reservation: form.value.max_capacity_per_reservation 
        ? parseInt(form.value.max_capacity_per_reservation) 
        : null
    }

    if (props.mode === 'create') {
      await createServiceType(formData)
    } else {
      await updateServiceType(props.serviceType.id, formData)
    }

    emit('saved')
    closeDialog()
    
  } catch (err) {
    // Manejar errores de validación del backend
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors || {}
    } else {
      errors.value.general = [error.value || 'Error al guardar el tipo de servicio']
    }
  }
}

// Watchers
watch(() => props.serviceType, (newServiceType) => {
  if (newServiceType) {
    form.value = {
      name: newServiceType.name || '',
      description: newServiceType.description || '',
      base_price: newServiceType.base_price || null,
      duration_minutes: newServiceType.duration_minutes || 60,
      min_units: newServiceType.min_units || 1,
      max_units: newServiceType.max_units || 1,
      max_participants: newServiceType.max_participants || 1,
      requires_resource: newServiceType.requires_resource || false,
      resource_type_id: newServiceType.resource_type_id || null,
      requires_capacity: newServiceType.requires_capacity || false,
      max_capacity_per_reservation: newServiceType.max_capacity_per_reservation || null
    }
  } else {
    resetForm()
  }
})

watch(() => props.modelValue, (newValue) => {
  if (newValue && props.serviceType) {
    form.value = {
      name: props.serviceType.name || '',
      description: props.serviceType.description || '',
      base_price: props.serviceType.base_price || null,
      duration_minutes: props.serviceType.duration_minutes || 60,
      min_units: props.serviceType.min_units || 1,
      max_units: props.serviceType.max_units || 1,
      max_participants: props.serviceType.max_participants || 1,
      requires_resource: props.serviceType.requires_resource || false,
      resource_type_id: props.serviceType.resource_type_id || null,
      requires_capacity: props.serviceType.requires_capacity || false,
      max_capacity_per_reservation: props.serviceType.max_capacity_per_reservation || null
    }
  } else if (!newValue) {
    resetForm()
  }
})

// Lifecycle
onMounted(() => {
  loadResourceTypes()
})
</script>