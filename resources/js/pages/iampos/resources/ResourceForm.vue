<template>
  <v-dialog v-model="dialog" max-width="800" persistent>
    <VCard>
      <v-toolbar :color="mode === 'create' ? 'primary' : 'secondary'" density="compact">
        <v-toolbar-title class="text-white">
          {{ mode === 'create' ? 'Nuevo Recurso' : 'Editar Recurso' }}
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
                label="Nombre del Recurso *"
                variant="outlined"
                :rules="[rules.required]"
                :error-messages="errors.name"
                required
              />
            </VCol>

            <VCol cols="12" md="6">
              <VAutocomplete
                v-model="form.resource_type_id"
                :items="resourceTypes"
                item-title="name"
                item-value="id"
                label="Tipo de Recurso *"
                variant="outlined"
                :rules="[rules.required]"
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

            <VCol cols="12">
              <VTextarea
                v-model="form.description"
                label="Descripción del Recurso"
                variant="outlined"
                rows="3"
                :error-messages="errors.description"
                placeholder="Descripción detallada del recurso..."
              />
            </VCol>

            <!-- Configuración de Tipo de Uso -->
            <VCol cols="12" md="6">
              <VSwitch
                v-model="form.is_shared"
                label="Recurso Compartido"
                color="primary"
                :messages="sharedResourceMessage"
                hide-details
                @change="onSharedChange"
              />
            </VCol>

            <VCol cols="12" md="6">
              <VSwitch
                v-model="form.is_active"
                label="Recurso Activo"
                color="primary"
                :messages="activeResourceMessage"
                hide-details
              />
            </VCol>

            <!-- Configuración de Capacidad -->
            <VCol cols="12" md="6">
              <VTextField
                v-model="form.capacity"
                label="Capacidad *"
                type="number"
                variant="outlined"
                :rules="[rules.required, rules.minValue(1)]"
                :error-messages="errors.capacity"
                :hint="capacityHint"
                persistent-hint
                required
              />
            </VCol>

            <VCol cols="12" md="6">
              <VTextField
                v-model="form.current_usage"
                label="Uso Actual"
                type="number"
                variant="outlined"
                :min="0"
                :max="form.capacity"
                :error-messages="errors.current_usage"
                :hint="currentUsageHint"
                persistent-hint
                :disabled="!form.is_shared"
              />
            </VCol>

            <!-- Características Adicionales -->
            <VCol cols="12">
              <VTextarea
                v-model="form.features_text"
                label="Características (JSON)"
                variant="outlined"
                rows="3"
                :error-messages="errors.features"
                placeholder='Ej: {"wifi": true, "proyector": false, "aire_acondicionado": true}'
                :hint="featuresHint"
                persistent-hint
              />
            </VCol>

            <!-- Información Adicional -->
            <VCol cols="12" v-if="form.is_shared">
              <VAlert type="info" variant="tonal" density="compact">
                <div class="text-caption">
                  <strong>Recurso Compartido:</strong> Múltiples reservas pueden usar este recurso simultáneamente, 
                  compartiendo la capacidad total. Ej: Sala de conferencias, estacionamiento, área común.
                </div>
              </VAlert>
            </VCol>

            <VCol cols="12" v-else>
              <VAlert type="info" variant="tonal" density="compact">
                <div class="text-caption">
                  <strong>Recurso Exclusivo:</strong> Solo una reserva puede usar este recurso a la vez. 
                  Ej: Habitación de hotel, consultorio médico, puesto de trabajo individual.
                </div>
              </VAlert>
            </VCol>

            <!-- Información de Uso Actual -->
            <VCol cols="12" v-if="form.is_shared && form.capacity">
              <VAlert :type="getUsageAlertType()" variant="tonal" density="compact">
                <div class="text-caption">
                  <strong>Estado de Capacidad:</strong> 
                  {{ getUsageAlertMessage() }}
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
import { useResources } from '@/composables/useResources'
import axios from '@/axios/axios'
import { apiRoute } from '@/helper/apiRoute'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  resource: {
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
const { createResource, updateResource, loading, error } = useResources()

// State
const form = ref({
  name: '',
  description: '',
  resource_type_id: null,
  capacity: 1,
  current_usage: 0,
  is_shared: false,
  is_active: true,
  features: {},
  features_text: ''
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
  return form.value.name && form.value.name.trim() !== '' && 
         form.value.resource_type_id && 
         form.value.capacity >= 1
})

const sharedResourceMessage = computed(() => {
  return form.value.is_shared 
    ? 'Múltiples reservas pueden usar este recurso simultáneamente'
    : 'Solo una reserva puede usar este recurso a la vez'
})

const activeResourceMessage = computed(() => {
  return form.value.is_active 
    ? 'El recurso está disponible para reservas'
    : 'El recurso no está disponible para reservas'
})

const capacityHint = computed(() => {
  if (form.value.is_shared) {
    return 'Capacidad máxima total del recurso compartido'
  } else {
    return 'Para recursos exclusivos, la capacidad generalmente es 1'
  }
})

const currentUsageHint = computed(() => {
  if (!form.value.is_shared) {
    return 'No aplica para recursos exclusivos'
  }
  return `Uso actual: ${form.value.current_usage || 0} de ${form.value.capacity}`
})

const featuresHint = computed(() => {
  return 'Ingrese las características en formato JSON. Ej: {"wifi": true, "capacidad_maxima": 50}'
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

const onSharedChange = () => {
  if (!form.value.is_shared) {
    // Para recursos exclusivos, capacidad = 1 y uso actual = 0
    form.value.capacity = 1
    form.value.current_usage = 0
  }
}

const parseFeatures = (featuresText) => {
  if (!featuresText || featuresText.trim() === '') {
    return {}
  }
  
  try {
    return JSON.parse(featuresText)
  } catch (err) {
    errors.value.features = ['Formato JSON inválido en características']
    return {}
  }
}

const formatFeatures = (features) => {
  if (!features || Object.keys(features).length === 0) {
    return ''
  }
  
  return JSON.stringify(features, null, 2)
}

const getUsageAlertType = () => {
  if (!form.value.is_shared || !form.value.capacity) return 'info'
  
  const usage = ((form.value.current_usage || 0) / form.value.capacity) * 100
  if (usage >= 90) return 'error'
  if (usage >= 70) return 'warning'
  return 'success'
}

const getUsageAlertMessage = () => {
  if (!form.value.is_shared || !form.value.capacity) return ''
  
  const available = form.value.capacity - (form.value.current_usage || 0)
  const usagePercentage = ((form.value.current_usage || 0) / form.value.capacity) * 100
  
  if (usagePercentage >= 90) {
    return `Capacidad crítica: solo ${available} disponible(s) (${usagePercentage.toFixed(1)}% usado)`
  } else if (usagePercentage >= 70) {
    return `Capacidad moderada: ${available} disponible(s) (${usagePercentage.toFixed(1)}% usado)`
  } else {
    return `Capacidad buena: ${available} disponible(s) (${usagePercentage.toFixed(1)}% usado)`
  }
}

const resetForm = () => {
  form.value = {
    name: '',
    description: '',
    resource_type_id: null,
    capacity: 1,
    current_usage: 0,
    is_shared: false,
    is_active: true,
    features: {},
    features_text: ''
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
    // Parsear características JSON
    const features = parseFeatures(form.value.features_text)
    if (errors.value.features) {
      return
    }

    const formData = {
      ...form.value,
      // Asegurar que los valores numéricos sean correctos
      capacity: parseInt(form.value.capacity),
      current_usage: form.value.is_shared ? parseInt(form.value.current_usage || 0) : 0,
      features: features
    }

    // Remover el campo temporal de texto
    delete formData.features_text

    if (props.mode === 'create') {
      await createResource(formData)
    } else {
      await updateResource(props.resource.id, formData)
    }

    emit('saved')
    closeDialog()
    
  } catch (err) {
    // Manejar errores de validación del backend
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors || {}
    } else {
      errors.value.general = [error.value || 'Error al guardar el recurso']
    }
  }
}

// Watchers
watch(() => props.resource, (newResource) => {
  if (newResource) {
    form.value = {
      name: newResource.name || '',
      description: newResource.description || '',
      resource_type_id: newResource.resource_type_id || null,
      capacity: newResource.capacity || 1,
      current_usage: newResource.current_usage || 0,
      is_shared: newResource.is_shared || false,
      is_active: newResource.is_active !== undefined ? newResource.is_active : true,
      features: newResource.features || {},
      features_text: formatFeatures(newResource.features)
    }
  } else {
    resetForm()
  }
})

watch(() => props.modelValue, (newValue) => {
  if (newValue && props.resource) {
    form.value = {
      name: props.resource.name || '',
      description: props.resource.description || '',
      resource_type_id: props.resource.resource_type_id || null,
      capacity: props.resource.capacity || 1,
      current_usage: props.resource.current_usage || 0,
      is_shared: props.resource.is_shared || false,
      is_active: props.resource.is_active !== undefined ? props.resource.is_active : true,
      features: props.resource.features || {},
      features_text: formatFeatures(props.resource.features)
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