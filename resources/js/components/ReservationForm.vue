<template>
  <v-container class="reservation-form" max-width="800">
    <v-card elevation="2" class="pa-6">
      <v-card-title class="d-flex align-center">
        <v-icon icon="ri-calendar-line" class="mr-2" color="primary"></v-icon>
        <span class="text-h5">{{ title }}</span>
      </v-card-title>

      <v-card-text>
        <v-form @submit.prevent="submitReservation" ref="formRef">
          <!-- Selección de Servicio -->
          <v-row>
            <v-col cols="12" md="6" sm="6">
              <v-autocomplete
                v-model="form.customer_id"
                :items="customers"
                :item-title="(customers.firstname)?'firstname':'address'"
                item-value="id"
                label="Cliente *"
                :loading="loading.customers"
                :rules="[$rulesRequerido]"
                :error-messages="errors.customer_id"
                variant="outlined"
                clearable
                required
              />
            </v-col> 
            <v-col cols="12" md="6">
              <v-autocomplete
                v-model="form.service_type_id"
                :items="serviceTypes"
                item-title="name"
                item-value="id"
                label="Tipo de Servicio *"
                :loading="loading.serviceTypes"
                :error-messages="errors.service_type_id"
                @update:model-value="onServiceTypeChange"
                variant="outlined"
                clearable
                required
              >
                <template v-slot:item="{ props, item }">
                  <v-list-item v-bind="props">
                    <template v-slot:title>
                      <div class="d-flex justify-space-between">
                        <span>{{ item.raw.name }}</span>
                        <span class="text-primary font-weight-bold">${{ item.raw.base_price }}</span>
                      </div>
                    </template>
                    <template v-slot:subtitle>
                      <span class="text-caption">{{ item.raw.description }}</span>
                    </template>
                  </v-list-item>
                </template>
              </v-autocomplete>
            </v-col>

            <!-- Información del Servicio Seleccionado -->
            <v-col cols="12" md="6" v-if="selectedService">
              <v-card variant="outlined" color="info">
                <v-card-text class="pa-3">
                  <div class="text-caption text-info">
                    <div><strong>Duración:</strong> {{ selectedService.duration_minutes }} min/unidad</div>
                    <div><strong>Unidades:</strong> {{ selectedService.min_units }} - {{ selectedService.max_units }}</div>
                    <div><strong>Participantes:</strong> Máx. {{ selectedService.max_participants }}</div>
                  </div>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
          <v-row>
            <VCol cols="12" sm="6">
              <v-autocomplete
                v-model="form.status"
                :items="statuses"
                item-title="name"
                item-value="id"
                label="Estado *"                
                :rules="[$rulesRequerido]"
                :error-messages="errors.status"
                variant="outlined"
                clearable
                required
              />
              <!-- Mostrar estado original si es diferente al actual -->
              <v-alert 
                v-if="isExistingReservation && originalStatus && originalStatus !== form.status"
                type="info" 
                variant="tonal" 
                density="compact" 
                class="mt-2"
              >
                <div class="text-caption">
                  <v-icon icon="ri-history-line" size="small" class="mr-1"></v-icon>
                  Estado original: <strong>{{ getStatusText(originalStatus) }}</strong>
                </div>
              </v-alert>
            </VCol>
            
            <!-- Botón de cancelación -->
            <VCol cols="12" sm="6" v-if="canCancelReservation">
              <v-btn 
                color="error" 
                variant="outlined" 
                @click="openCancelDialog"
                :loading="loading.cancellation"
                class="mt-4"
              >
                <v-icon icon="ri-close-circle-line" class="mr-2"></v-icon>
                Cancelar Reserva
              </v-btn>
            </VCol>

            <!-- Mensaje cuando no se puede cancelar por estado original -->
            <VCol cols="12" sm="6" v-else-if="isExistingReservation && originalStatus && ['cancelled', 'completed', 'no_show'].includes(originalStatus)">
              <v-alert 
                type="info" 
                variant="tonal" 
                class="mt-4" 
                density="compact"
              >
                <div class="text-caption">
                  <v-icon icon="ri-information-line" size="small" class="mr-1"></v-icon>
                  Reserva originalmente <strong>{{ getStatusText(originalStatus) }}</strong> - No se puede cancelar
                </div>
              </v-alert>
            </VCol>

            <!-- Mensaje cuando no se puede cancelar por estado actual -->
            <VCol cols="12" sm="6" v-else-if="isExistingReservation && form.status && ['cancelled', 'completed', 'no_show'].includes(form.status) && !['cancelled', 'completed', 'no_show'].includes(originalStatus)">
              <v-alert 
                type="warning" 
                variant="tonal" 
                class="mt-4" 
                density="compact"
              >
                <div class="text-caption">
                  <v-icon icon="ri-error-warning-line" size="small" class="mr-1"></v-icon>
                  Estado actual: <strong>{{ getStatusText(form.status) }}</strong> - No se puede cancelar
                </div>
              </v-alert>
            </VCol>
          </v-row>

          <v-row>
            <v-col cols="12" md="6">
              <v-select
                v-model="form.time_units"
                :items="availableTimeUnits"
                label="Unidades de Tiempo *"
                variant="outlined"
                :error-messages="errors.time_units"
                @update:model-value="calculateEndTime"
                required
              >
                <template v-slot:item="{ item, props }">
                  <v-list-item v-bind="props" :title="getTimeUnitDisplay(item.value)"></v-list-item>
                </template>
                <template v-slot:selection="{ item }">
                  {{ getTimeUnitDisplay(item.value) }}
                </template>
              </v-select>
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model.number="form.participants_count"
                label="Número de Participantes *"
                type="number"
                variant="outlined"
                :min="1"
                :max="selectedService?.max_participants || 10"
                :error-messages="errors.participants_count"
                required
              />
            </v-col>
          </v-row>

          <!-- Sección Dinámica de Features - VERSION CORREGIDA -->
          <div v-if="shouldShowDynamicSection">
            <v-row>
              <v-col cols="12">
                <v-card variant="outlined" :color="sectionColor">
                  <v-card-title class="d-flex align-center">
                    <v-icon :icon="sectionIcon" class="mr-2" :color="sectionColor"></v-icon>
                    <span class="text-h6">{{ sectionTitle }}</span>
                  </v-card-title>
                  <v-card-text>
                    <v-row>
                      <v-col 
                        v-for="field in dynamicFields" 
                        :key="field.key"
                        :cols="field.cols || 12"
                        :md="field.md || 6"
                      >
                        <!-- Campo de texto -->
                        <v-text-field
                          v-if="field.type === 'text' || field.type === 'number'"
                          v-model="form.features[field.key]"
                          :label="field.label + (field.required ? ' *' : '')"
                          :type="field.inputType || 'text'"
                          variant="outlined"
                          :required="field.required"
                          :error-messages="getFieldError(field.key)"
                          :min="field.min"
                          :max="field.max"
                          :step="field.step"
                          :placeholder="field.placeholder"
                        />
                        
                        <!-- Select -->
                        <v-select
                          v-else-if="field.type === 'select'"
                          v-model="form.features[field.key]"
                          :label="field.label + (field.required ? ' *' : '')"
                          :items="field.options || []"
                          variant="outlined"
                          :required="field.required"
                          :error-messages="getFieldError(field.key)"
                          clearable
                        />
                        
                        <!-- Textarea -->
                        <v-textarea
                          v-else-if="field.type === 'textarea'"
                          v-model="form.features[field.key]"
                          :label="field.label + (field.required ? ' *' : '')"
                          variant="outlined"
                          :required="field.required"
                          :error-messages="getFieldError(field.key)"
                          :rows="field.rows || 3"
                          :placeholder="field.placeholder"
                        />
                      </v-col>
                    </v-row>
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>
          </div>

          <!-- Resto de campos del formulario -->
          <v-row>
            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.start_time"
                label="Fecha y Hora de Inicio *"
                type="datetime-local"
                :min="minStartDate"
                :error-messages="errors.start_time"
                @update:model-value="calculateEndTime"
                variant="outlined"
                required
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-text-field
                v-model="form.end_time"
                label="Fecha y Hora de Fin"
                type="datetime-local"
                variant="outlined"
                readonly
                :loading="loading.endTimeCalculation"
              />
            </v-col>
          </v-row>

          <!-- Selección de Recurso -->          
          <v-row v-if="selectedService?.requires_resource && availableResources.length > 0">
            <v-col cols="12">
              <v-card variant="outlined">
                <v-card-title class="d-flex align-center">
                  <v-icon icon="ri-map-pin-line" class="mr-2"></v-icon>
                  <span>Recursos Disponibles</span>
                  <v-spacer></v-spacer>
                  
                  <!-- Indicador de estado -->
                  <v-chip 
                    v-if="availabilityResult"
                    :color="availabilityResult.available ? 'success' : 'warning'"
                    size="small"
                  >
                    <v-icon start :icon="availabilityResult.available ? 'ri-check-line' : 'ri-alert-line'"></v-icon>
                    {{ availabilityResult.available_resources_count }} disponibles
                  </v-chip>
                  
                  <v-btn 
                    size="small" 
                    variant="text" 
                    @click="checkAvailability"
                    :loading="loading.availabilityCheck"
                    :disabled="!canCheckAvailability"
                    class="ml-2"
                  >
                    <v-icon icon="ri-refresh-line" class="mr-1"></v-icon>
                    Actualizar
                  </v-btn>
                </v-card-title>

                <v-card-text>
                  <v-alert
                    v-if="!form.start_time || !form.end_time"
                    type="info"
                    variant="tonal"
                    class="mb-4"
                  >
                    <v-icon icon="ri-information-line" class="mr-2"></v-icon>
                    Seleccione fecha y hora para ver la disponibilidad
                  </v-alert>

                  <v-radio-group v-model="form.resource_id">
                    <v-row>
                      <v-col 
                        v-for="resource in availableResources" 
                        :key="resource.id"
                        cols="12" 
                        md="6"
                      >
                        <v-card 
                          :variant="form.resource_id === resource.id ? 'elevated' : 'outlined'"
                          :color="getResourceCardColor(resource)"
                          class="pa-3 cursor-pointer"
                          @click="form.resource_id = resource.id"
                        >
                          <div class="d-flex align-center">
                            <v-radio :value="resource.id" class="mr-3"></v-radio>
                            <div class="flex-grow-1">
                              <div class="font-weight-bold">{{ resource.name }}</div>
                              <div class="text-caption text-medium-emphasis">
                                {{ resource.description }}
                              </div>
                              <div class="mt-1">
                                <v-chip 
                                  v-if="form.start_time && form.end_time"
                                  :color="resource.available ? 'success' : 'error'"
                                  size="small"
                                  class="mr-1"
                                >
                                  {{ resource.available ? 'Disponible' : 'No disponible' }}
                                </v-chip>
                                <v-chip 
                                  color="info"
                                  size="small"
                                >
                                  Cap: {{ resource.available_capacity }}/{{ resource.capacity }}
                                </v-chip>
                              </div>
                            </div>
                          </div>
                        </v-card>
                      </v-col>
                    </v-row>
                  </v-radio-group>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>

          <!-- Mensaje si no hay recursos -->
          <v-alert
            v-else-if="selectedService?.requires_resource"
            type="warning"
            variant="tonal"
          >
            No hay recursos configurados para este tipo de servicio.
          </v-alert>

          <!-- Campos de Texto -->
          <v-row>
            <v-col cols="12" md="6">
              <v-textarea
                v-model="form.special_requirements"
                label="Requisitos Especiales"
                variant="outlined"
                rows="3"
                :error-messages="errors.special_requirements"
                placeholder="Ej: Preferencias específicas, necesidades especiales..."
              />
            </v-col>

            <v-col cols="12" md="6">
              <v-textarea
                v-model="form.notes"
                label="Notas Adicionales"
                variant="outlined"
                rows="3"
                :error-messages="errors.notes"
                placeholder="Información adicional para la reserva..."
              />
            </v-col>
          </v-row>

          <!-- Resultado de Disponibilidad -->
          <v-alert
            v-if="availabilityResult"
            :type="availabilityResult.available ? 'success' : 'warning'"
            variant="tonal"
            class="my-4"
          >
            <template v-slot:prepend>
              <v-icon :icon="availabilityResult.available ? 'ri-checkbox-circle-line' : 'ri-alert-line'"></v-icon>
            </template>
            {{ availabilityMessage }}
            <div v-if="availabilityResult.available_resources_count !== undefined" class="text-caption mt-1">
              {{ availabilityResult.available_resources_count }} recursos disponibles
            </div>
          </v-alert>

          <!-- Acciones -->
          <v-card-actions class="px-0">
            <v-spacer></v-spacer>
            
            <v-btn 
              variant="outlined" 
              @click="handleCancel"
              :disabled="loading.submission || loading.cancellation"
            >
              <v-icon icon="ri-arrow-left-line" class="mr-2"></v-icon>
              {{ cancelButtonText }}
            </v-btn>

            <v-btn 
              type="submit" 
              color="primary" 
              :loading="loading.submission"
              :disabled="!formIsValid || loading.submission || loading.cancellation"
              size="large"
            >
              <v-icon icon="ri-save-line" class="mr-2"></v-icon>
              {{ loading.submission ? submitLoadingText : submitButtonText }}
            </v-btn>
          </v-card-actions>
        </v-form>
      </v-card-text>
    </v-card>

    <!-- Dialog para cancelación -->
    <VDialog v-model="cancelDialog" max-width="500px">
      <VCard>
        <VToolbar color="error" density="compact">
          <VToolbarTitle class="text-white">
            Cancelar Reserva
          </VToolbarTitle>
          <VSpacer />
          <VBtn icon color="white" @click="cancelDialog = false">
            <VIcon>ri-close-line</VIcon>
          </VBtn>
        </VToolbar>

        <VCardText class="pt-4">
          <v-alert type="warning" variant="tonal" class="mb-4">
            <strong>¿Está seguro que desea cancelar esta reserva?</strong>
            <div class="text-caption mt-1">Esta acción no se puede deshacer.</div>
          </v-alert>

          <v-textarea
            v-model="cancellationReason"
            label="Razón de cancelación *"
            variant="outlined"
            rows="3"
            :error-messages="errors.cancellation_reason"
            placeholder="Explique la razón de la cancelación..."
            required
          />

          <v-checkbox
            v-model="sendCancellationNotification"
            label="Enviar notificación al cliente"
            color="primary"
            hide-details
            class="mt-2"
          />
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn 
            variant="outlined" 
            @click="cancelDialog = false"
            :disabled="loading.cancellation"
          >
            Cancelar
          </VBtn>
          <VBtn 
            color="error" 
            @click="confirmCancelReservation"
            :loading="loading.cancellation"
            :disabled="!cancellationReason"
          >
            <v-icon icon="ri-close-circle-line" class="mr-2"></v-icon>
            Confirmar Cancelación
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Snackbar para errores -->
    <v-snackbar v-model="showError" color="error" timeout="5000">
      <div class="d-flex align-center">
        <v-icon icon="ri-error-warning-line" class="mr-2"></v-icon>
        {{ errorMessage }}
      </div>
      <template v-slot:actions>
        <v-btn variant="text" @click="showError = false">Cerrar</v-btn>
      </template>
    </v-snackbar>

    <!-- Snackbar para éxito -->
    <v-snackbar v-model="showSuccessSnackbar" color="success" timeout="3000">
      <div class="d-flex align-center">
        <v-icon icon="ri-checkbox-circle-line" class="mr-2"></v-icon>
        {{ successMessage }}
      </div>
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted, watch, defineProps, defineEmits } from 'vue'
import { useRouter } from 'vue-router'
import { apiRoute } from '@/helper/apiRoute'
import  axios  from '@/axios/axios'

// Definir props
const props = defineProps({
  // Datos iniciales del formulario
  initialData: {
    type: Object,
    default: () => ({
      id: null,
      customer_id: null,
      service_type_id: null,
      resource_id: null,
      status: 'pending',
      start_time: null,
      end_time: null,
      time_units: 1,
      required_capacity: 1,
      participants_count: 1,
      features: {},
      special_requirements: '',
      notes: '',
    })
  },
  
  // Datos pre-cargados
  preloadedData: {
    type: Object,
    default: () => ({
      serviceTypes: [],
      customers: [],
    })
  },
  
  // Configuración del componente
  title: {
    type: String,
    default: 'Nueva Reserva'
  },
  
  // Textos personalizables
  submitButtonText: {
    type: String,
    default: 'Crear Reserva'
  },
  
  submitLoadingText: {
    type: String,
    default: 'Creando...'
  },
  
  cancelButtonText: {
    type: String,
    default: 'Cancelar'
  },
  
  // Modo de funcionamiento
  mode: {
    type: String,
    default: 'create', // 'create' o 'edit'
    validator: (value) => ['create', 'edit'].includes(value)
  },
  
  // Si se debe cargar datos automáticamente
  autoLoad: {
    type: Boolean,
    default: true
  },
  
  // Callbacks personalizados
  onSuccess: {
    type: Function,
    default: null
  },
  
  onCancel: {
    type: Function,
    default: null
  },
  
  onSubmit: {
    type: Function,
    default: null
  }
})

// Agrega una ref para almacenar el estado original
const originalStatus = ref(null)

// Definir emits
const emit = defineEmits([
  'submit',
  'cancel',
  'success',
  'error',
  'update:form-data',
  'service-type-change',
  'availability-check',
  'reservation-cancelled',
  'reservation-updated'
])

const router = useRouter()

// 1. PRIMERO: Definir todas las refs básicas
const form = ref({
  id: props.initialData.id || null, // Asegurar que id se incluya
  customer_id: props.initialData.customer_id,
  service_type_id: props.initialData.service_type_id,
  resource_id: props.initialData.resource_id,
  status: props.initialData.status,
  start_time: props.initialData.start_time,
  end_time: props.initialData.end_time,
  time_units: props.initialData.time_units,
  required_capacity: props.initialData.required_capacity,
  participants_count: props.initialData.participants_count,
  features: { ...props.initialData.features },
  special_requirements: props.initialData.special_requirements,
  notes: props.initialData.notes
})

// Usar datos pre-cargados o cargarlos internamente
const serviceTypes = ref([...props.preloadedData.serviceTypes])
const customers = ref([...props.preloadedData.customers])

//const statuses = [{ id: "pending", name: "Pendiente" }, { id: "confirmed", name: "Confirmada" }, { id: "cancelled", name: "Cancelada" }, { id: "completed", name: "Completada" }, { id: "no_show", name: "No Show" }]

const statuses = [{ id: "pending", name: "Pendiente" }, { id: "confirmed", name: "Confirmada" }, { id: "cancelled", name: "Cancelada" }, { id: "completed", name: "Completada" }]
const selectedService = ref(null)
const availableResources = ref([])
const availabilityResult = ref(null)
const showError = ref(false)
const showSuccessSnackbar = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const errors = ref({})

// Nuevas refs para cancelación
const cancelDialog = ref(false)
const cancellationReason = ref('')
const sendCancellationNotification = ref(true)

const loading = ref({
  serviceTypes: false,
  submission: false,
  endTimeCalculation: false,
  availabilityCheck: false,
  customers: false,
  cancellation: false,
  timeUpdate: false
})

// Watcher para actualizar el form cuando cambien las props
watch(() => props.initialData, (newData) => {
  form.value = {
    id: newData.id,
    customer_id: newData.customer_id,
    service_type_id: newData.service_type_id,
    resource_id: newData.resource_id,
    status: newData.status,
    start_time: newData.start_time,
    end_time: newData.end_time,
    time_units: newData.time_units,
    required_capacity: newData.required_capacity,
    participants_count: newData.participants_count,
    features: { ...newData.features },
    special_requirements: newData.special_requirements,
    notes: newData.notes
  }

  // Guardar el estado original cuando lleguen los datos iniciales
  if (newData.status && originalStatus.value === null) {
    originalStatus.value = newData.status
    console.log('📋 Estado original guardado:', originalStatus.value)
  }
  
  // Si hay un service_type_id, cargar el servicio seleccionado
  if (newData.service_type_id) {
    onServiceTypeChange()
  }
}, { deep: true })

// Watcher para datos pre-cargados
watch(() => props.preloadedData, (newData) => {
  if (newData.serviceTypes) {
    serviceTypes.value = [...newData.serviceTypes]
  }
  if (newData.customers) {
    customers.value = [...newData.customers]
  }
}, { deep: true })

// Emitir cambios en el form
watch(form, (newForm) => {
  emit('update:form-data', newForm)
}, { deep: true })

// 2. SEGUNDO: Métodos simples y funciones de ayuda

// Método auxiliar para color de tarjeta
const getResourceCardColor = (resource) => {
  if (!form.value.start_time || !form.value.end_time) return undefined
  
  return resource.available ? 'success' : (form.value.resource_id === resource.id ? 'error' : undefined)
}

const getFieldError = (fieldKey) => {
  return errors.value[`features.${fieldKey}`] || []
}

const getServiceType = (serviceName) => {
  const name = serviceName.toLowerCase()
  if (name.includes('baño') || name.includes('canino') || name.includes('mascota') || name.includes('peluquería')) {
    return 'pet'
  }
  if (name.includes('estacionamiento') || name.includes('parking')) {
    return 'parking'
  }
  if (name.includes('habitación') || name.includes('hotel') || name.includes('suite')) {
    return 'hotel'
  }
  return 'other'
}


// 3. TERCERO: Computed properties en el orden correcto
const minStartDate = computed(() => {
  return new Date().toISOString().slice(0, 16)
})

const serviceType = computed(() => {
  if (!selectedService.value) return null

  return getServiceType(selectedService.value.name)
})

const sectionTitle = computed(() => {
  const type = serviceType.value
  const titles = {
    pet: 'Información de la Mascota',
    parking: 'Información del Vehículo',
    hotel: 'Información de Huéspedes',
    other: 'Información Adicional'
  }
  return titles[type] || 'Información Adicional'
})

const sectionIcon = computed(() => {
  const type = serviceType.value
  const icons = {
    pet: 'ri-heart-line',
    parking: 'ri-car-line',
    hotel: 'ri-hotel-line',
    other: 'ri-information-line'
  }
  return icons[type] || 'ri-information-line'
})

const sectionColor = computed(() => {
  const type = serviceType.value
  const colors = {
    pet: 'orange',
    parking: 'blue',
    hotel: 'green',
    other: 'grey'
  }
  return colors[type] || 'grey'
})

const dynamicFields = computed(() => {
  if (!selectedService.value) return []
  
  const type = serviceType.value
  const fieldsConfig = {
    pet: [
      { 
        key: 'pet_name', 
        label: 'Nombre de la Mascota', 
        type: 'text', 
        required: true,
        cols: 12,
        md: 6
      },
      { 
        key: 'pet_breed', 
        label: 'Raza', 
        type: 'text',
        cols: 12,
        md: 6
      },
      { 
        key: 'pet_weight', 
        label: 'Peso (kg)', 
        type: 'number',
        inputType: 'number',
        step: 0.1,
        min: 0.1,
        max: 100,
        cols: 12,
        md: 4
      },
      { 
        key: 'pet_age', 
        label: 'Edad (años)', 
        type: 'number',
        inputType: 'number',
        min: 0,
        max: 30,
        cols: 12,
        md: 4
      },
      { 
        key: 'pet_gender', 
        label: 'Género', 
        type: 'select',
        options: [
          { title: 'Macho', value: 'male' },
          { title: 'Hembra', value: 'female' },
          { title: 'Desconocido', value: 'unknown' }
        ],
        cols: 12,
        md: 4
      },
      { 
        key: 'pet_notes', 
        label: 'Notas sobre la Mascota', 
        type: 'textarea',
        cols: 12,
        rows: 3
      }
    ],
    parking: [
      { 
        key: 'vehicle_plate', 
        label: 'Placa del Vehículo', 
        type: 'text', 
        required: true,
        cols: 12,
        md: 6
      },
      { 
        key: 'vehicle_model', 
        label: 'Modelo', 
        type: 'text',
        cols: 12,
        md: 6
      },
      { 
        key: 'vehicle_color', 
        label: 'Color', 
        type: 'text',
        cols: 12,
        md: 6
      },
      { 
        key: 'vehicle_type', 
        label: 'Tipo de Vehículo', 
        type: 'select',
        options: [
          { title: 'Automóvil', value: 'car' },
          { title: 'SUV', value: 'suv' },
          { title: 'Van', value: 'van' },
          { title: 'Motocicleta', value: 'motorcycle' },
          { title: 'Camión', value: 'truck' }
        ],
        cols: 12,
        md: 6
      }
    ],
    hotel: [
      { 
        key: 'adults_count', 
        label: 'Número de Adultos', 
        type: 'number',
        inputType: 'number',
        required: true,
        min: 1,
        max: 10,
        cols: 12,
        md: 6
      },
      { 
        key: 'children_count', 
        label: 'Número de Niños', 
        type: 'number',
        inputType: 'number',
        min: 0,
        max: 10,
        cols: 12,
        md: 6
      },
      { 
        key: 'room_preference', 
        label: 'Preferencia de Habitación', 
        type: 'text',
        cols: 12
      }
    ],
    other: []
  }
  
  return fieldsConfig[type] || []
})

const shouldShowDynamicSection = computed(() => {
  return selectedService.value && dynamicFields.value.length > 0
})

const availabilityMessage = computed(() => {
  if (!availabilityResult.value) return ''
  
  if (availabilityResult.value.available) {
    return '✅ Disponible para reservar'
  } else {
    return '❌ No hay disponibilidad para las fechas seleccionadas'
  }
})

const canCheckAvailability = computed(() => 
  form.value.service_type_id && form.value.start_time && form.value.time_units
)

const formIsValid = computed(() => 
  form.value.customer_id &&
  form.value.service_type_id && 
  form.value.status &&
  form.value.start_time && 
  form.value.time_units && 
  form.value.participants_count > 0
)

const availableTimeUnits = computed(() => {
  if (!selectedService.value) return [{ title: '1 unidad', value: 1 }]
  
  const units = []
  for (let i = selectedService.value.min_units; i <= selectedService.value.max_units; i++) {
    units.push({
      title: getTimeUnitDisplay(i),
      value: i
    })
  }
  return units
})

const canCancelReservation = computed(() => {
  console.log('🔍 Verificando si se puede cancelar:', {
    estadoActual: form.value.status,
    estadoOriginal: originalStatus.value,
    esExistente: isExistingReservation.value
  })
  
  // Solo mostrar el botón si:
  // 1. Es una reserva existente (modo edición con ID)
  if (!isExistingReservation.value) {
    console.log('❌ No es reserva existente')
    return false
  }
  
  // 2. El estado original NO está en la lista de estados no cancelables
  const nonCancellableOriginalStatuses = ['cancelled', 'completed', 'no_show']
  
  // Si el estado original ya es no cancelable, NO mostrar el botón
  if (originalStatus.value && nonCancellableOriginalStatuses.includes(originalStatus.value)) {
    console.log(`❌ Estado original no cancelable: ${originalStatus.value}`)
    return false
  }
  
  // 3. El estado actual tampoco debe estar en la lista de no cancelables
  const nonCancellableCurrentStatuses = ['cancelled', 'completed', 'no_show']
  if (nonCancellableCurrentStatuses.includes(form.value.status)) {
    console.log(`❌ Estado actual no cancelable: ${form.value.status}`)
    return false
  }
  
  // 4. Opcional: No permitir cancelar si la reserva ya comenzó
  if (form.value.start_time) {
    const startTime = new Date(form.value.start_time)
    const now = new Date()
    if (startTime < now) {
      console.log('❌ La reserva ya comenzó')
      return false
    }
  }
  
  console.log('✅ Se puede cancelar')
  return true
})

const isExistingReservation = computed(() => {
  return props.mode === 'edit' && 
         form.value.id !== null && 
         form.value.id !== undefined &&
         form.value.id !== ''
})

// 4. CUARTO: Métodos asíncronos y de negocio - ACTUALIZADOS

const onServiceTypeChange = async () => {
  console.log('🎯 Servicio seleccionado cambiado a:', form.value.service_type_id)
  
  errors.value = {}
  availabilityResult.value = null
  
  // Actualizar el servicio seleccionado
  selectedService.value = serviceTypes.value.find(
    st => st.id === form.value.service_type_id
  ) || null
  
  // Limpiar features y resource_id anteriores
  if (props.mode === 'create') {
    form.value.features = {}
    form.value.resource_id = null
  }
  
  console.log('📋 Servicio seleccionado:', selectedService.value?.name)
  
  // Emitir evento
  emit('service-type-change', selectedService.value)
  
  if (selectedService.value) {
    // 1. PRIMERO: Cargar recursos compatibles con este servicio
    await loadAvailableResources()
    
    // 2. SI hay fecha seleccionada, verificar disponibilidad
    if (form.value.start_time && form.value.end_time) {
      await checkAvailability()
    }
  } else {
    availableResources.value = []
  }
  
  // Recalcular end_time
  calculateEndTime()
}

const loadAvailableResources = async () => {
  if (!selectedService.value || !selectedService.value.requires_resource) {
    availableResources.value = []
    return
  }
  
  try {
    console.log('🔄 Cargando recursos para servicio:', selectedService.value.name)
    
    const response = await axios.get('/api/resources', {
      params: {
        resource_type_id: selectedService.value.resource_type_id,
        active: true
      }
    })
    
    // Extraer el array de recursos de la respuesta
    let resourcesData = response.data.data || response.data
    
    availableResources.value = resourcesData.map(resource => ({
      ...resource,
      available: false, // Inicialmente no disponible (se verificará después)
      available_capacity: resource.capacity // Capacidad máxima inicial
    }))
    
    console.log(`✅ ${availableResources.value.length} recursos cargados`)
    
  } catch (error) {
    console.error('❌ Error cargando recursos:', error)
    availableResources.value = []
  }
}

const checkAvailability = async () => {
  if (!canCheckAvailability.value) {
    console.log('⚠️ No se puede verificar disponibilidad - faltan datos')
    return
  }

  loading.value.availabilityCheck = true
  
  try {
    console.log('🔍 Verificando disponibilidad...')
    
    const payload = {
      service_type_id: form.value.service_type_id,
      start_time: form.value.start_time,
      end_time: form.value.end_time,
      required_capacity: form.value.required_capacity || 1
    }

    const response = await axios.post('/api/reservations/check-availability', payload)
    availabilityResult.value = response.data

    console.log('📊 Resultado disponibilidad:', availabilityResult.value)

    // Emitir evento
    emit('availability-check', availabilityResult.value)

    // Actualizar estado de disponibilidad de cada recurso
    if (availabilityResult.value.available_resources) {
      availableResources.value = availableResources.value.map(resource => {
        const availableResource = availabilityResult.value.available_resources.find(
          ar => ar.resource.id === resource.id
        )
        
        return {
          ...resource,
          available: availableResource ? 
            availableResource.available_capacity >= (form.value.required_capacity || 1) : 
            false,
          available_capacity: availableResource?.available_capacity || 0
        }
      })
    }
    
  } catch (error) {
    console.error('❌ Error verificando disponibilidad:', error)
    showError.value = true
    errorMessage.value = 'Error al verificar la disponibilidad'
    emit('error', error)
  } finally {
    loading.value.availabilityCheck = false
  }
}

const calculateEndTime = () => {
  if (!form.value.service_type_id || !form.value.start_time || !form.value.time_units) {
    return;
  }

  loading.value.endTimeCalculation = true;

  try {
    const startDate = new Date(form.value.start_time);
    const serviceType = selectedService.value;
    
    if (serviceType) {
      const totalMinutes = form.value.time_units * serviceType.duration_minutes;
      const endDate = new Date(startDate.getTime() + (totalMinutes * 60 * 1000));
      
      // FORMATO CORRECTO - usar formateo manual
      const year = endDate.getFullYear();
      const month = String(endDate.getMonth() + 1).padStart(2, '0');
      const day = String(endDate.getDate()).padStart(2, '0');
      const hours = String(endDate.getHours()).padStart(2, '0');
      const minutes = String(endDate.getMinutes()).padStart(2, '0');
      
      form.value.end_time = `${year}-${month}-${day}T${hours}:${minutes}`;
      
      console.log('✅ Cálculo correcto:', {
        inicio: form.value.start_time,
        fin: form.value.end_time,
        unidades: form.value.time_units,
        minutosTotales: totalMinutes
      });
    }
  } catch (error) {
    console.error('❌ Error calculando fecha:', error);
  } finally {
    loading.value.endTimeCalculation = false;
  }
}

const getTimeUnitDisplay = (units) => {
  if (!selectedService.value) return `${units} unidad(es)`
  
  const totalMinutes = units * selectedService.value.duration_minutes
  const hours = Math.floor(totalMinutes / 60)
  const minutes = totalMinutes % 60
  
  if (hours > 0) {
    return `${units} unidad(es) - ${hours}h ${minutes > 0 ? `${minutes}min` : ''}`.trim()
  } else {
    return `${units} unidad(es) - ${minutes}min`
  }
}

const handleCancel = () => {
  // Resetear el estado original al cancelar
  originalStatus.value = null
  
  if (props.onCancel) {
    props.onCancel()
  } else {
    router.back()
  }
  emit('cancel')
}

// También al guardar exitosamente
const showSuccess = (message) => {
  successMessage.value = message
  showSuccessSnackbar.value = true
  
  // Resetear estado original después de guardar
  originalStatus.value = null
  emit('success', message)
}

// NUEVO: Método principal para enviar reserva (create/update)
const submitReservation = async () => {
  if (!formIsValid.value) return

  loading.value.submission = true
  errors.value = {}

  try {
    const payload = {
      customer_id: parseInt(form.value.customer_id),
      service_type_id: parseInt(form.value.service_type_id),
      resource_id: form.value.resource_id ? parseInt(form.value.resource_id) : null,
      status: form.value.status,
      start_time: form.value.start_time,
      end_time: form.value.end_time,
      time_units: parseInt(form.value.time_units),
      required_capacity: parseInt(form.value.required_capacity),
      participants_count: parseInt(form.value.participants_count),
      special_requirements: form.value.special_requirements,
      notes: form.value.notes,
      features: prepareFeaturesForApi(),
    }

    // Emitir evento submit
    //emit('submit', payload)

    // Si hay un callback personalizado, usarlo
    if (props.onSubmit) {
      const result = await props.onSubmit(payload)
      if (result.success) {
        emit('success', result)
        showSuccess(props.mode === 'edit' ? 'Reserva actualizada exitosamente' : 'Reserva creada exitosamente')
      } else {
        throw new Error(result.message || 'Error al procesar la reserva')
      }
    } else {
      // Comportamiento por defecto - usar métodos específicos
      let response
      
      if (props.mode === 'edit' && form.value.id) {
        // Usar update para modo edición
        response = await updateReservation(payload)
      } else {
        // Usar create para modo creación
        response = await axios.post(`${apiRoute.reservations}`, payload)
      }

      if (response.data.success) {
        emit('success', response.data)
        showSuccess(props.mode === 'edit' ? 'Reserva actualizada exitosamente' : 'Reserva creada exitosamente')
        
        // Emitir evento específico
        if (props.mode === 'edit') {
          emit('reservation-updated', response.data)
        }
      } else {
        throw new Error(response.data.message || `Error al ${props.mode === 'edit' ? 'actualizar' : 'crear'} la reserva`)
      }
    }

  } catch (error) {
    handleSubmissionError(error)
  } finally {
    loading.value.submission = false
  }
}

// NUEVO: Método para actualizar reserva
const updateReservation = async (payload) => {
  try {
    const response = await axios.put(`${apiRoute.reservations}/${form.value.id}`, payload)
    return response
  } catch (error) {
    // Manejar errores de validación del UpdateReservationRequest
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
      throw new Error('Error de validación en los datos')
    }
    throw error
  }
}

// NUEVO: Método para cancelar reserva
const openCancelDialog = () => {
  // Verificar que tenemos un ID antes de abrir el diálogo
  if (!form.value.id) {
    console.error('❌ No se puede abrir diálogo de cancelación: ID no disponible')
    showError.value = true
    errorMessage.value = 'No se puede cancelar: ID de reserva no disponible'
    return
  }
  
  cancellationReason.value = ''
  sendCancellationNotification.value = true
  cancelDialog.value = true
  
  console.log('📝 Abriendo diálogo de cancelación para reserva ID:', form.value.id)
}

const confirmCancelReservation = async () => {

  // Verificar que tenemos un ID válido
  if (!form.value.id) {
    console.error('❌ No se puede cancelar: ID de reserva no disponible')
    showError.value = true
    errorMessage.value = 'No se puede cancelar la reserva: ID no disponible'
    return
  }

  if (!cancellationReason.value.trim()) {
    errors.value.cancellation_reason = ['La razón de cancelación es obligatoria']
    return
  }

  loading.value.cancellation = true
  errors.value = {}

  try {
    const payload = {
      cancellation_reason: cancellationReason.value,
      send_notification: sendCancellationNotification.value,
    }

    const response = await axios.patch(`${apiRoute.reservations}/${form.value.id}/cancel`, payload)

    if (response.data.success) {
      showSuccess('Reserva cancelada exitosamente')
      cancelDialog.value = false
      
      // Actualizar el estado local
      form.value.status = 'cancelled'
      form.value.cancellation_reason = cancellationReason.value
      
      // Emitir eventos
      emit('success', response.data)
      emit('reservation-cancelled', response.data)
      emit('update:form-data', form.value)
    } else {
      throw new Error(response.data.message || 'Error al cancelar la reserva')
    }

  } catch (error) {
    handleSubmissionError(error)
  } finally {
    loading.value.cancellation = false
  }
}

// NUEVO: Método para actualizar solo el tiempo (desde calendario)
const updateReservationTime = async (startTime, endTime) => {
  loading.value.timeUpdate = true
  
  try {
    const payload = {
      start_time: startTime,
      end_time: endTime
    }

    const response = await axios.patch(`${apiRoute.reservations}/${form.value.id}/time`, payload)

    if (response.data.success) {
      // Actualizar el formulario localmente
      form.value.start_time = startTime
      form.value.end_time = endTime
      
      emit('reservation-updated', response.data)
      return { success: true }
    } else {
      throw new Error(response.data.message || 'Error al actualizar el horario')
    }

  } catch (error) {
    handleSubmissionError(error)
    return { success: false, error: error.message }
  } finally {
    loading.value.timeUpdate = false
  }
}

// NUEVO: Manejo centralizado de errores
const handleSubmissionError = (error) => {
  console.error('❌ Error en operación:', error)
  
  if (error.response?.status === 422) {
    errors.value = error.response.data.errors || {}
    showError.value = true
    errorMessage.value = 'Por favor, corrige los errores en el formulario'
  } else if (error.response?.data?.message) {
    showError.value = true
    errorMessage.value = error.response.data.message
  } else {
    showError.value = true
    errorMessage.value = error.message || 'Error al procesar la solicitud'
  }
  
  emit('error', error)
}

// Texto legible para los estados
const getStatusText = (status) => {
  const statusTexts = {
    pending: 'Pendiente',
    confirmed: 'Confirmada',
    cancelled: 'Cancelada',
    completed: 'Completada',
    no_show: 'No Show'
  }
  
  return statusTexts[status] || status
}

/**
 * Prepara los features para la API
 */
const prepareFeaturesForApi = () => {
  const features = { ...form.value.features }
  
  // Limpiar y transformar los features
  return Object.keys(features).reduce((acc, key) => {
    const value = features[key]
    
    // Solo incluir valores no vacíos
    if (value !== '' && value !== null && value !== undefined) {
      // Convertir números strings a números
      acc[key] = !isNaN(value) && value !== '' ? Number(value) : value
    }
    
    return acc
  }, {})
}

const loadData = async () => {
  // Solo cargar datos si no hay datos pre-cargados y autoLoad está activado
  if ((serviceTypes.value.length === 0 || customers.value.length === 0) && props.autoLoad) {
    try{
      const [servicesData, customersData] = await Promise.all([
        axios.get(`${apiRoute.servicesTypes}`),
        axios.get(`${apiRoute.customers}`),
      ])

      serviceTypes.value = servicesData.data.data || servicesData.data        
      customers.value = customersData.data.data || customersData.data
    } catch (error) {
      console.error("Error loading data:", error)
    }
  }
}

// Watcher para verificar disponibilidad automáticamente cuando cambian las fechas
watch([() => form.value.start_time, () => form.value.end_time], ([newStart, newEnd]) => {
  if (newStart && newEnd && selectedService.value?.requires_resource) {
    // Pequeño delay para evitar múltiples llamadas rápidas
    setTimeout(() => {
      checkAvailability()
    }, 500)
  }
})

// Watcher para required_capacity
watch(() => form.value.required_capacity, (newCapacity) => {
  if (newCapacity && selectedService.value?.requires_resource && 
      form.value.start_time && form.value.end_time) {
    checkAvailability()
  }
})

// 5. QUINTO: Lifecycle hooks
onMounted(() => {
  loadData()
  console.log('🚀 ReservationForm mounted')
  console.log('Modo:', props.mode)
  console.log('ID de reserva:', form.value.id)
  console.log('Datos iniciales:', props.initialData)
  
  // Inicializar estado original si hay datos iniciales
  if (props.initialData.status) {
    originalStatus.value = props.initialData.status
    console.log('📋 Estado original inicializado:', originalStatus.value)
  }
  
  // Si hay datos iniciales con service_type_id, cargar el servicio
  if (props.initialData.service_type_id) {
    onServiceTypeChange()
  }
})

// Exponer métodos para el componente padre
defineExpose({
  updateReservationTime,
  cancelReservation: confirmCancelReservation
})
</script>

<style scoped>
.reservation-form {
  padding: 20px 0;
}
</style>