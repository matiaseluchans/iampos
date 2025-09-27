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

    <VDialog v-model="dialog" max-width="90%">
      <VCard>
        <VToolbar color="primary" density="compact">
          <VToolbarTitle class="text-white">
            {{ reservationData.id ? "Editar Turno" : "Nuevo Turno" }}
          </VToolbarTitle>
          <VSpacer />
          <VBtn icon color="white" @click="dialog = false">
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
            :on-success="handleFormSuccess"
            :on-cancel="handleFormCancel"
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
      <template #actions>
        <VBtn 
          variant="text" 
          @click="snackbar.show = false"
        >
          Cerrar
        </VBtn>
      </template>
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

const calendarRef = ref(null)
const dialog = ref(false)
const snackbar = ref({ show: false, text: "", color: "success" })
const formKey = ref(0)
const calendarApi = ref(null)

// Datos para el formulario
const reservationData = ref({})
const reservationFormData = ref({})
const preloadedData = ref({
  serviceTypes: [],
  customers: [],
})

// Colores según el estado de la reserva
const statusColors = {
  pending: '#ff9800',     // Naranja
  confirmed: '#4caf50',   // Verde
  cancelled: '#f44336',   // Rojo
  completed: '#2196f3',   // Azul
  no_show: '#9e9e9e',     // Gris
}

const events = ref([])

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
    
    // Transformar las reservas a eventos del calendario
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
      
      console.log('📅 Evento creado:', event.title, event.start, event.end)
      return event
    })

    events.value = calendarEvents
    console.log(`✅ ${events.value.length} eventos transformados`)

    // Actualizar el calendario después de cargar los eventos
    updateCalendarEvents()
    
  } catch (error) {
    console.error('❌ Error cargando reservas:', error)
    showSnackbar('Error al cargar las reservas', 'error')
  }
}

// Actualizar eventos en el calendario
const updateCalendarEvents = () => {
  if (calendarApi.value) {
    // Limpiar eventos existentes
    calendarApi.value.removeAllEvents()
    
    // Agregar nuevos eventos
    events.value.forEach(event => {
      calendarApi.value.addEvent(event)
    })
    
    console.log('📅 Eventos actualizados en el calendario')
  } else {
    console.log('⚠️ Calendar API no está disponible aún')
  }
}

// Generar título del evento basado en la reserva
const generateEventTitle = (reservation) => {
  const customer = reservation.customer
  const serviceType = reservation.service_type
  
  // Nombre del cliente
  const customerName = customer ? 
    (customer.firstname && customer.lastname ? 
      `${customer.firstname} ${customer.lastname}` : 
      customer.business_name || 'Cliente') : 
    'Cliente'
  
  // Tipo de servicio
  const serviceName = serviceType ? serviceType.name : 'Servicio'
  
  // Información adicional basada en features
  let additionalInfo = ''
  
  if (reservation.features) {
    if (reservation.features.pet_name) {
      additionalInfo = ` - ${reservation.features.pet_name}`
    } else if (reservation.features.vehicle_plate) {
      additionalInfo = ` - ${reservation.features.vehicle_plate}`
    } else if (reservation.features.room_preference) {
      additionalInfo = ` - ${reservation.features.room_preference}`
    }
  }
  
  return `${customerName} - ${serviceName}${additionalInfo}`
}

// Método para transformar reservationData a reservationFormData
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

// Formatear fecha/hora para input datetime-local
const formatDateTimeForInput = (dateTimeString) => {
  if (!dateTimeString) return null
  const date = new Date(dateTimeString)
  return date.toISOString().slice(0, 16)
}

// Handlers del formulario
const handleFormSuccess = (message) => {
  showSnackbar(message, 'success')
  dialog.value = false
  // Recargar las reservas después de éxito
  setTimeout(() => {
    loadReservations()
  }, 500)
}

const handleFormCancel = () => {
  dialog.value = false
}

const handleFormSubmit = async (formData) => {
  try {
    if (reservationData.value.id) {
      // Editar reserva existente
      await axios.put(`${apiRoute.reservations}/${reservationData.value.id}`, formData)
    } else {
      // Crear nueva reserva
      await axios.post(`${apiRoute.reservations}`, formData)
    }
    
    return { success: true }
  } catch (error) {
    console.error('Error guardando reserva:', error)
    showSnackbar('Error al guardar la reserva', 'error')
    throw error
  }
}

const handleFormUpdate = (formData) => {
  // Actualizar datos locales si es necesario
  console.log('Formulario actualizado:', formData)
}

// Funciones del componente
const openDialog = (event = null) => {
  if (event) {
    // Editar reserva existente
    reservationData.value = {
      id: event.id,
      ...event.extendedProps.reservation
    }
  } else {
    // Nueva reserva
    reservationData.value = {
      id: null,
      start_time: null,
      end_time: null
    }
  }
  
  // Transformar datos para el formulario
  reservationFormData.value = transformToFormData(reservationData.value)
  
  // Forzar re-render del formulario
  formKey.value++
  
  dialog.value = true
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
    
    console.log('✅ Datos pre-cargados listos')
  } catch (error) {
    console.error('Error cargando datos pre-cargados:', error)
  }
}

onMounted(() => {  
  // Esperar a que el calendario se monte completamente
  nextTick(() => {
    if (calendarRef.value && calendarRef.value.getApi) {
      calendarApi.value = calendarRef.value.getApi()
      console.log('✅ Calendar API obtenida')
      
      // Cargar datos después de que el calendario esté listo
      Promise.all([loadPreloadedData(), loadReservations()]).then(() => {
        console.log('✅ Calendario completamente cargado')
      })
    }
  })
})

// Configuración de FullCalendar - USAR FUNCIÓN PARA events
const calendarOptions = ref({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: "timeGridWeek",

  // Configuración específica para Argentina
  locale: "es",
  firstDay: 1,
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
 
  // Usar función para events que retorne la referencia reactiva
  events: (info, successCallback, failureCallback) => {
    console.log('📅 Solicitando eventos para:', info.start, 'a', info.end)
    successCallback(events.value)
  },
  
  selectable: true,
  editable: true,
  
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay'
  },
  
  // Manejo de eventos
  eventClick: (info) => {
    openDialog(info.event)
  },
  
  select: (info) => {
    reservationData.value = {
      id: null,
      start_time: info.startStr,
      end_time: info.endStr,
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
  
  // Configuración de visualización
  eventDisplay: 'block',
  eventTimeFormat: {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false
  },
  
  // Tooltip personalizado para eventos
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
    
    // Revertir el cambio en el calendario
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
  
  if (reservation.features) {
    if (reservation.features.pet_name) {
      lines.push(`Mascota: ${reservation.features.pet_name}`)
    }
    if (reservation.features.vehicle_plate) {
      lines.push(`Vehículo: ${reservation.features.vehicle_plate}`)
    }
    if (reservation.features.room_preference) {
      lines.push(`Habitación: ${reservation.features.room_preference}`)
    }
  }
  
  if (reservation.notes) {
    lines.push(`Notas: ${reservation.notes}`)
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

// Método para forzar actualización del calendario
const refreshCalendar = () => {
  if (calendarApi.value) {
    calendarApi.value.refetchEvents()
  }
}
</script>

<style>
.calendar {
  height: 80vh;
}

/* Estilos adicionales para los estados */
.status-pending {
  font-weight: 600;
}

.status-confirmed {
  font-weight: 500;
}

.status-cancelled {
  opacity: 0.7;
  text-decoration: line-through;
}

.status-completed {
  font-style: italic;
}

.status-no_show {
  opacity: 0.5;
}

.fc-event {
  cursor: pointer;
}

.fc-event:hover {
  opacity: 0.9;
  transform: scale(1.02);
  transition: all 0.2s ease;
}
</style>