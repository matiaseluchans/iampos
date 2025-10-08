<template>
  <VCard>
    <VCardTitle class="d-flex justify-space-between align-center">
      <span>Agenda de Turnos</span>
      <VBtn color="primary" prepend-icon="ri-add-line" @click="openDialog()">Nuevo turno</VBtn>
    </VCardTitle>

    <VCardText>
      <FullCalendar
        ref="calendarRef"
        :options="calendarOptions"
        class="calendar"
      />
    </VCardText>

    <VDialog v-model="dialog" max-width="90%" persistent>
      <VCard>
        <VToolbar color="primary" density="compact">
          <VToolbarTitle class="text-white">
            {{ reservationData.id ? "Editar Turno" : "Nuevo Turno" }}
          </VToolbarTitle>
          <VSpacer />
          <VBtn icon color="white" @click="closeDialog">
            <VIcon>ri-close-line</VIcon>
          </VBtn>
        </VToolbar>

        <VCardText class="pt-4">
          <ReservationForm
            :key="formKey"
            :title="reservationData.id ? 'Editar Reserva' : 'Nueva Reserva'"
            :mode="reservationData.id ? 'edit' : 'create'"
            :initial-data="reservationFormData"
            :preloaded-data="preloadedData"
            :submit-button-text="reservationData.id ? 'Actualizar Reserva' : 'Crear Reserva'"
            @success="handleFormSuccess"
            @cancel="handleFormCancel"
            @submit="handleFormSubmit"
            @update:form-data="handleFormUpdate"
          />
        </VCardText>
      </VCard>
    </VDialog>

    <VSnackbar 
      v-model="snackbar.show" 
      :color="snackbar.color"
    >
      {{ snackbar.text }}
      <!--
      <template #actions>
        <VBtn 
          variant="text" 
          @click="snackbar.show = false"
        >
          Cerrar
        </VBtn>
      </template>
      -->
    </VSnackbar>
  </VCard>
</template>

<script setup>
import { ref, onMounted, nextTick } from "vue"
import FullCalendar from "@fullcalendar/vue3"
import dayGridPlugin from "@fullcalendar/daygrid"
import timeGridPlugin from "@fullcalendar/timegrid"
import interactionPlugin from "@fullcalendar/interaction"
import { apiRoute } from '@/helper/apiRoute'
import axios from '@/axios/axios'
import ReservationForm from '@/components/ReservationForm.vue'
import { id } from "vuetify/lib/locale/index.mjs"

const calendarRef = ref(null)
const dialog = ref(false)
const snackbar = ref({ show: false, text: "", color: "success" })
const formKey = ref(0)
const calendarApi = ref(null)
const isLoading = ref(false)

// Datos para el formulario
const reservationData = ref({})
const reservationFormData = ref({})
const preloadedData = ref({
  serviceTypes: [],
  customers: [],
})

// Colores según el estado de la reserva
const statusColors = {
  pending: '#ff9800',
  confirmed: '#4caf50',
  cancelled: '#f44336',
  completed: '#2196f3',
  no_show: '#9e9e9e',
}

const events = ref([])

// Función para convertir fecha a formato local sin ajuste de zona horaria
const formatDateForInput = (date) => {
  if (!date) return null
  
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  const hours = String(date.getHours()).padStart(2, '0')
  const minutes = String(date.getMinutes()).padStart(2, '0')
  
  return `${year}-${month}-${day}T${hours}:${minutes}`
}

// Función para parsear fechas del formulario al calendario
const parseDateFromInput = (dateString) => {
  if (!dateString) return null
  
  // Asumir que la fecha del formulario está en hora local
  const date = new Date(dateString + ':00')
  return date
}

// Cerrar diálogo y limpiar datos
const closeDialog = () => {
  dialog.value = false
  setTimeout(() => {
    reservationData.value = {}
    reservationFormData.value = transformToFormData(null)
    formKey.value++
  }, 300)
}

// Manejar éxito del formulario
const handleFormSuccess = (result) => {
  console.log('✅ Reserva guardada exitosamente:', result)
  showSnackbar(
    reservationData.value.id ? 'Reserva actualizada exitosamente' : 'Reserva creada exitosamente', 
    'success'
  )
  closeDialog()
  setTimeout(() => {
    loadReservations()
  }, 500)
}

// Manejar cancelación del formulario
const handleFormCancel = () => {
  console.log('❌ Formulario cancelado')
  closeDialog()
}

// Manejar envío del formulario
const handleFormSubmit = async (formData) => {
  try {
    isLoading.value = true
    
    // Ajustar las fechas antes de enviar (si es necesario)
    const adjustedFormData = {
      ...formData,
      start_time: formData.start_time ? formData.start_time + ':00' : null,
      end_time: formData.end_time ? formData.end_time + ':00' : null
    }
    
    if (reservationData.value.id) {
      const response = await axios.put(`${apiRoute.reservations}/${reservationData.value.id}`, adjustedFormData)
      return { success: true, data: response.data }
    } else {
      const response = await axios.post(`${apiRoute.reservations}`, adjustedFormData)
      return { success: true, data: response.data }
    }
  } catch (error) {
    console.error('❌ Error guardando reserva:', error)
    showSnackbar('Error al guardar la reserva', 'error')
    throw error
  } finally {
    isLoading.value = false
  }
}

const handleFormUpdate = (formData) => {
  // Actualización en tiempo real si es necesario
}

// Método para cargar las reservas desde la API
const loadReservations = async () => {
  try {
    console.log('🔄 Cargando reservas...')
    
    const response = await axios.get(`${apiRoute.reservations}`, {
      params: {
        include: 'customer,serviceType,resource',
        per_page: 1000
      }
    })

    const reservations = response.data.data || response.data
    console.log(`📊 ${reservations.length} reservas obtenidas de la API`)
    
    const calendarEvents = reservations.map(reservation => {
      const event = {
        id: reservation.id.toString(),
        title: generateEventTitle(reservation),
        start: reservation.start_time,
        end: reservation.end_time,
        extendedProps: {
          reservation: reservation,
          customer: reservation.customer,
          serviceType: reservation.service_type,
          resource: reservation.resource,
          status: reservation.status,
          participants_count: reservation.participants_count,
          features: reservation.features || {}
        },
        backgroundColor: statusColors[reservation.status] || '#757575',
        borderColor: statusColors[reservation.status] || '#757575',
        textColor: '#ffffff',
        classNames: [`status-${reservation.status}`],
        editable: reservation.status === 'pending' || reservation.status === 'confirmed'
      }
      
      return event
    })

    events.value = calendarEvents
    updateCalendarEvents()
    
  } catch (error) {
    console.error('❌ Error cargando reservas:', error)
    showSnackbar('Error al cargar las reservas', 'error')
  }
}

// Actualizar eventos en el calendario
const updateCalendarEvents = () => {
  if (calendarApi.value) {
    calendarApi.value.removeAllEvents()
    events.value.forEach(event => {
      calendarApi.value.addEvent(event)
    })
    calendarApi.value.render()
  }
}

// Generar título del evento
const generateEventTitle = (reservation) => {
  const customer = reservation.customer
  const serviceType = reservation.service_type
  
  const customerName = customer ? 
    (customer.firstname && customer.lastname ? 
      `${customer.firstname} ${customer.lastname}` : 
      customer.business_name || 'Cliente') : 
    'Cliente'
  
  const serviceName = serviceType ? serviceType.name : 'Servicio'
  
  let additionalInfo = ''
  if (reservation.features) {
    if (reservation.features.pet_name) {
      additionalInfo = ` - ${reservation.features.pet_name}`
    } else if (reservation.features.vehicle_plate) {
      additionalInfo = ` - ${reservation.features.vehicle_plate}`
    }
  }
  
  return `${customerName} - ${serviceName}${additionalInfo}`
}

// Transformar datos para el formulario
const transformToFormData = (reservation) => {
  if (!reservation) {
    return {
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
      notes: ''
    }
  }

  return {
    id: reservation.id || null,
    customer_id: reservation.customer_id,
    service_type_id: reservation.service_type_id,
    resource_id: reservation.resource_id,
    status: reservation.status,
    start_time: reservation.start_time ? formatDateTimeForInput(reservation.start_time) : null,
    end_time: reservation.end_time ? formatDateTimeForInput(reservation.end_time) : null,
    time_units: reservation.time_units || 1,
    required_capacity: reservation.required_capacity || 1,
    participants_count: reservation.participants_count || 1,
    features: reservation.features || {},
    special_requirements: reservation.special_requirements || '',
    notes: reservation.notes || ''
  }
}

// Formatear fecha/hora para input (corregido)
const formatDateTimeForInput = (dateTimeString) => {
  if (!dateTimeString) return null
  
  // Si ya está en el formato correcto, retornarlo directamente
  if (dateTimeString.includes('T') && dateTimeString.length === 16) {
    return dateTimeString
  }
  
  // Parsear la fecha y formatear manualmente
  const date = new Date(dateTimeString)
  
  // Usar formato manual para evitar problemas de zona horaria
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  const hours = String(date.getHours()).padStart(2, '0')
  const minutes = String(date.getMinutes()).padStart(2, '0')
  
  return `${year}-${month}-${day}T${hours}:${minutes}`
}

// Funciones del componente
const openDialog = (event = null) => {
  
  if (event) {
    //console.log('📝 Abriendo diálogo para:', event ? `Editar reserva ID ${event.id}` : 'Nueva reserva')
    // Editar reserva existente - asegúrate de pasar el ID
    reservationData.value = {
      id: event.id, // Esto debe ser el ID numérico, no string
      ...event.extendedProps.reservation,
    }
  } else {
    // Nueva reserva
    reservationData.value = {
      id: null, // Explícitamente null para nuevas reservas
      start_time: null,
      end_time: null
    }
  }

  // Transformar datos para el formulario
  reservationFormData.value = transformToFormData(reservationData.value)
  
  // Forzar re-render del formulario
  formKey.value++
  
  dialog.value = true
  
  console.log('📋 Datos pasados al formulario:', reservationData.value)
}

const showSnackbar = (text, color) => {
  snackbar.value = { show: true, text, color }
  setTimeout(() => {
    snackbar.value.show = false
  }, 3000)
}

// Cargar datos pre-cargados
const loadPreloadedData = async () => {
  try {
    const [servicesResponse, customersResponse] = await Promise.all([
      axios.get(`${apiRoute.servicesTypes}`),
      axios.get(`${apiRoute.customers}`),
    ])

    preloadedData.value.serviceTypes = servicesResponse.data.data || servicesResponse.data
    preloadedData.value.customers = customersResponse.data.data || customersResponse.data
  } catch (error) {
    console.error('Error cargando datos pre-cargados:', error)
  }
}

// Para actualizar solo el tiempo desde el calendario
const handleTimeUpdate = async (reservationId, startTime, endTime) => {
  const result = await reservationFormComponent.updateReservationTime(startTime, endTime)
  if (result.success) {
    // Actualizar calendario
  }
}

// Escuchar eventos
const handleReservationCancelled = (data) => {
  console.log('Reserva cancelada:', data)
  // Actualizar UI
}

const handleReservationUpdated = (data) => {
  console.log('Reserva actualizada:', data)
  // Actualizar UI
}

onMounted(() => {  
  nextTick(() => {
    if (calendarRef.value && calendarRef.value.getApi) {
      calendarApi.value = calendarRef.value.getApi()
      
      Promise.all([loadPreloadedData(), loadReservations()]).then(() => {
        console.log('✅ Calendario completamente cargado')
      })
    }
  })
})

// Configuración de FullCalendar (CORREGIDA)
const calendarOptions = ref({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: "timeGridWeek",
  locale: "es",
  firstDay: 1,
  timeZone: 'local', // Usar zona horaria local
  slotLabelFormat: {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false
  },
  buttonText: {
    today: 'Hoy',
    month: 'Mes',
    week: 'Semana',
    day: 'Día'
  },
  events: (info, successCallback, failureCallback) => {
    successCallback(events.value)
  },
  selectable: true,
  editable: true,
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay'
  },
  
  // CORRECCIÓN: Manejo de selección de fechas
  eventClick: (info) => {
    openDialog(info.event)
  },
  
  select: (info) => {
    console.log('📅 Selección en calendario:', info.start, info.end)
    
    // Usar formato manual para evitar problemas de zona horaria
    const startFormatted = formatDateForInput(info.start)
    const endFormatted = formatDateForInput(info.end)
    
    console.log('📅 Fecha formateada para formulario:', startFormatted)
    
    reservationData.value = {
      id: null,
      start_time: startFormatted,
      end_time: endFormatted,
    }
    reservationFormData.value = transformToFormData(reservationData.value)
    formKey.value++
    dialog.value = true
  },
  
  eventDrop: (info) => {
    updateReservationTime(info.event)
  },
  
  eventResize: (info) => {
    updateReservationTime(info.event)
  },
  
  eventDisplay: 'block',
  eventTimeFormat: {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false
  },
  
  eventDidMount: (info) => {
    const reservation = info.event.extendedProps.reservation
    if (reservation) {
      info.el.title = generateEventTooltip(reservation)
    }
  }
})

// Método para actualizar el tiempo de la reserva
const updateReservationTime = async (event) => {
  try {
    const reservationId = event.id
    const updateData = {
      start_time: event.startStr,
      end_time: event.endStr
    }

    await axios.put(`${apiRoute.reservations}/${reservationId}`, updateData)
    showSnackbar("Turno reagendado correctamente", "success")
  } catch (error) {
    console.error('Error actualizando reserva:', error)
    showSnackbar("Error al reagendar el turno", "error")
    event.revert()
  }
}

// Generar tooltip para el evento
const generateEventTooltip = (reservation) => {
  const lines = []
  
  if (reservation.customer) {
    const customer = reservation.customer
    const customerName = customer.firstname && customer.lastname ? 
      `${customer.firstname} ${customer.lastname}` : 
      customer.business_name || 'Cliente'
    lines.push(`Cliente: ${customerName}`)
  }
  
  if (reservation.service_type) {
    lines.push(`Servicio: ${reservation.service_type.name}`)
  }
  
  lines.push(`Estado: ${getStatusText(reservation.status)}`)
  
  if (reservation.participants_count > 1) {
    lines.push(`Participantes: ${reservation.participants_count}`)
  }
  
  return lines.join('\n')
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
</script>

<style>
.calendar {
  height: 80vh;
}

.status-pending { font-weight: 600; }
.status-confirmed { font-weight: 500; }
.status-cancelled { opacity: 0.7; text-decoration: line-through; }
.status-completed { font-style: italic; }
.status-no_show { opacity: 0.5; }

.fc-event {
  cursor: pointer;
}
.fc-event:hover {
  opacity: 0.9;
  transform: scale(1.02);
  transition: all 0.2s ease;
}
</style>