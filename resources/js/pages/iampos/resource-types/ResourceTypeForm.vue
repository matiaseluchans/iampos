<template>
  <v-dialog v-model="dialog" max-width="600" persistent>
    <v-card>
      <v-toolbar :color="mode === 'create' ? 'primary' : 'secondary'" density="compact">
        <v-toolbar-title class="text-white">
          {{ mode === 'create' ? 'Nuevo Tipo de Recurso' : 'Editar Tipo de Recurso' }}
        </v-toolbar-title>
        <v-spacer></v-spacer>
        <v-btn icon color="white" @click="closeDialog">
          <v-icon>ri-close-line</v-icon>
        </v-btn>
      </v-toolbar>

      <v-card-text class="pt-4">
        <v-form @submit.prevent="submitForm" ref="formRef">
          <v-row>
            <v-col cols="12">
              <v-text-field
                v-model="form.name"
                label="Nombre *"
                variant="outlined"
                :rules="[rules.required]"
                :error-messages="errors.name"
              />
            </v-col>

            <v-col cols="12">
              <v-textarea
                v-model="form.description"
                label="Descripción"
                variant="outlined"
                rows="3"
                :error-messages="errors.description"
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-switch
                v-model="form.is_shared_capacity"
                label="Capacidad Compartida"
                color="primary"
                :messages="sharedCapacityMessage"
                hide-details
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.max_capacity_per_reservation"
                label="Capacidad Máxima por Reserva"
                type="number"
                variant="outlined"
                :min="1"
                :hint="capacityHint"
                persistent-hint
                :error-messages="errors.max_capacity_per_reservation"
              />
            </v-col>

            <!-- Información adicional para capacidad compartida -->
            <v-col cols="12" v-if="form.is_shared_capacity">
              <v-alert type="info" variant="tonal" density="compact">
                <div class="text-caption">
                  <strong>Capacidad Compartida:</strong> Múltiples reservas pueden usar el mismo recurso simultáneamente, 
                  compartiendo la capacidad total disponible.
                </div>
              </v-alert>
            </v-col>

            <!-- Información adicional para capacidad individual -->
            <v-col cols="12" v-else>
              <v-alert type="info" variant="tonal" density="compact">
                <div class="text-caption">
                  <strong>Capacidad Individual:</strong> Cada recurso solo puede ser usado por una reserva a la vez.
                </div>
              </v-alert>
            </v-col>
          </v-row>

          <v-card-actions class="px-0">
            <v-spacer></v-spacer>
            <v-btn variant="outlined" @click="closeDialog" :disabled="loading">
              Cancelar
            </v-btn>
            <v-btn 
              color="primary" 
              type="submit" 
              :loading="loading"
              :disabled="!formIsValid"
            >
              {{ mode === 'create' ? 'Crear' : 'Actualizar' }}
            </v-btn>
          </v-card-actions>
        </v-form>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, computed, watch, defineProps, defineEmits } from 'vue'
import { useResourceTypes } from '@/composables/useResourceTypes'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  resourceType: {
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
const { createResourceType, updateResourceType, loading, error } = useResourceTypes()

// State
const form = ref({
  name: '',
  description: '',
  is_shared_capacity: false,
  max_capacity_per_reservation: null
})

const errors = ref({})
const formRef = ref(null)

// Rules de validación
const rules = {
  required: value => !!value || 'Este campo es requerido'
}

// Computed
const dialog = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const formIsValid = computed(() => {
  return form.value.name && form.value.name.trim() !== ''
})

const sharedCapacityMessage = computed(() => {
  return form.value.is_shared_capacity 
    ? 'Múltiples reservas pueden compartir el mismo recurso'
    : 'Cada recurso es exclusivo para una reserva'
})

const capacityHint = computed(() => {
  if (!form.value.is_shared_capacity) {
    return 'Para capacidad individual, este campo generalmente es 1'
  }
  return form.value.max_capacity_per_reservation 
    ? `Máximo ${form.value.max_capacity_per_reservation} participantes por reserva`
    : 'Sin límite de capacidad por reserva'
})

// Watchers
watch(() => props.resourceType, (newResourceType) => {
  if (newResourceType) {
    form.value = {
      name: newResourceType.name || '',
      description: newResourceType.description || '',
      is_shared_capacity: newResourceType.is_shared_capacity || false,
      max_capacity_per_reservation: newResourceType.max_capacity_per_reservation || null
    }
  } else {
    resetForm()
  }
})

watch(() => props.modelValue, (newValue) => {
  if (newValue && props.resourceType) {
    form.value = {
      name: props.resourceType.name || '',
      description: props.resourceType.description || '',
      is_shared_capacity: props.resourceType.is_shared_capacity || false,
      max_capacity_per_reservation: props.resourceType.max_capacity_per_reservation || null
    }
  } else if (!newValue) {
    resetForm()
  }
})

// Métodos
const resetForm = () => {
  form.value = {
    name: '',
    description: '',
    is_shared_capacity: false,
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
      max_capacity_per_reservation: form.value.max_capacity_per_reservation 
        ? parseInt(form.value.max_capacity_per_reservation) 
        : null
    }

    if (props.mode === 'create') {
      await createResourceType(formData)
    } else {
      await updateResourceType(props.resourceType.id, formData)
    }

    emit('saved')
    closeDialog()
    
  } catch (err) {
    // Manejar errores de validación del backend
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors || {}
    } else {
      errors.value.general = [error.value || 'Error al guardar el tipo de recurso']
    }
  }
}
</script>