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
          <!-- Uso básico -->
          <ReservationForm />
          <!-- Uso con props personalizadas -->
          <!--
            <ReservationForm
              title="Editar Reserva"
              mode="edit"
              :initial-data="reservationData"
              :preloaded-data="preloadedData"
              submit-button-text="Actualizar Reserva"
              :on-success="handleSuccess"
              :on-cancel="handleCancel"
              @submit="handleSubmit"
              @update:form-data="handleFormUpdate"
            />
          -->
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn 
            variant="outlined" 
            @click="dialog = false">Cancelar</VBtn>
          <VBtn color="primary" @click="saveEvent">Guardar</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <VSnackbar 
      v-model="snackbar.show" 
      :color="snackbar.color"
    >
      {{ snackbar.text }}
      <template #actions>
        <!--
        <VBtn 
          variant="text" 
          @click="snackbar.show=false"
        />
          Cerrar
        </VBtn>
        -->
      </template>
    </VSnackbar>
  </VCard>
</template>

<script setup>
import { ref } from "vue"
import FullCalendar from "@fullcalendar/vue3"
import dayGridPlugin from "@fullcalendar/daygrid"
import timeGridPlugin from "@fullcalendar/timegrid"
import interactionPlugin from "@fullcalendar/interaction"
import { apiRoute } from '@/helper/apiRoute'
import  axios  from '@/axios/axios'
import ReservationForm from '@/components/ReservationForm.vue'




const services = ref([])
const customers = ref([])
const statuses = ["programado", "completado", "cancelado"]
const clientNames = ["María", "Juan", "Pedro", "Ana", "Luis", "Sofía", "Carlos", "Laura", "Diego", "Elena"]
const dogNames = ["Max", "Luna", "Rocky", "Bella", "Coco", "Duke", "Daisy", "Toby", "Milo", "Lola"]

const calendarRef = ref(null)
const dialog = ref(false)
const snackbar = ref({ show: false, text: "", color: "success" })

const loadData = async () => {
  try{
    const [servicesData, customersData] = await Promise.all([
      axios.get(`${apiRoute.servicesTypes}`),
      axios.get(`${apiRoute.customers}`),
    ])

    services.value = servicesData.data.data || servicesData.data        
    customers.value = customersData.data.data || customersData.data
  } catch (error) {
    console.error("Error loading data:", error)
  }
}

onMounted(() => {  
  //loadData()
})

/*const reservationData = ref({
  id: null,
  client: "",
  dog: "",
  service: "",
  status: "programado",
  phone: "",
  notes: "",
  start: "",
  end: ""
})*/

const reservationData = ref({
    /*customer_id: 1,
    service_type_id: 2,
    status: 'confirmed',
    start: "",
    end: "",*/
    
    // ... otros datos
  })

  const preloadedData = ref({
    serviceTypes: [],
    customers: [],
  })  

  const handleSuccess = (message) => {
    console.log('Éxito:', message)
  }

  const handleCancel = () => {
    console.log('Cancelado')
  }

  const handleSubmit = (formData) => {
    console.log('Datos del formulario:', formData)
    // Lógica personalizada de envío
  }

  const handleFormUpdate = (formData) => {
    console.log('Formulario actualizado:', formData)
  }

// Funciones de utilidad
function getRandomElement(arr) {
  return arr[Math.floor(Math.random() * arr.length)]
}

function getRandomDate() {
  const today = new Date()
  const randomDayOffset = Math.floor(Math.random() * 60) - 30 
  const randomHourOffset = Math.floor(Math.random() * 8) + 9 
  const randomMinuteOffset = Math.floor(Math.random() * 2) * 30 
  today.setDate(today.getDate() + randomDayOffset)
  today.setHours(randomHourOffset, randomMinuteOffset, 0)
  return today
}

function statusColor(status) {
  switch (status) {
    case "programado":
      return "#42a5f5"
    case "completado":
      return "#66bb6a"
    case "cancelado":
      return "#ef5350"
    default:
      return "#9e9e9e"
  }
}

// Generar eventos de prueba al inicio
const generateRandomEvents = (count) => {
  const generatedEvents = []
  for (let i = 0; i < count; i++) {
    const start = getRandomDate()
    const end = new Date(start.getTime() + 60 * 60 * 1000)
    const service = getRandomElement(services)
    const status = getRandomElement(statuses)
    const client = getRandomElement(clientNames)
    const dog = getRandomElement(dogNames)
    
    generatedEvents.push({
      id: `${i + 1}`,
      title: `${client} y ${dog}`,
      start: start.toISOString(),
      end: end.toISOString(),
      extendedProps: {
        client,
        dog,
        service,
        status,
        phone: `11-${Math.floor(10000000 + Math.random() * 90000000)}`,
        notes: Math.random() > 0.7 ? "Notas de prueba para el turno." : "",
      },
      color: statusColor(status),
    })
  }
  
  return generatedEvents
}

const events = ref(generateRandomEvents(50))

// Funciones del componente
function openDialog(event = null) {
  if (event) {
    reservationData.value = { 
      id: event.id, 
      client: event.extendedProps.client,
      dog: event.extendedProps.dog,
      service: event.extendedProps.service,
      status: event.extendedProps.status,
      phone: event.extendedProps.phone,
      notes: event.extendedProps.notes,
      start: event.startStr,
      end: event.endStr
    }
  } else {
    reservationData.value = {
      id: null,
      client: "",
      dog: "",
      service: "",
      status: "programado",
      phone: "",
      notes: "",
      start: "",
      end: ""
    }
  }
  dialog.value = true
}

const saveEvent = async () => {
  if (!reservationData.value.client || !reservationData.value.dog || !reservationData.value.service) {
    showSnackbar("Por favor complete los campos obligatorios.", "error")

    return
  }

  console.log("Guardando evento:", reservationData.value);

  const newEvent = {
    id: reservationData.value.id || Date.now().toString(),
    title: `${reservationData.value.client} y ${reservationData.value.dog}`,
    start: reservationData.value.start,
    end: reservationData.value.end,
    extendedProps: { ...reservationData.value },
    color: statusColor(reservationData.value.status),
  }

  if (reservationData.value.id) {
    const index = events.value.findIndex((e) => e.id === reservationData.value.id)
    if (index !== -1) {
      events.value[index] = newEvent
      showSnackbar("Turno actualizado correctamente", "success")
    }
  } else {

    //guardar en la base de datos
    const payload = {
      customer_id: 1,
      service_type_id: 3,
      resource_id: 5,
      start_time: "2025-09-24T10:00:00",
      time_units: 2,
      required_capacity: 3,
      participants_count: 1,
      special_requirements: "Necesito espacio para 3 vehículos de empresa",
      notes: "Cliente corporativo, facturación especial"
    };
    const response  = await axios.post(`${apiRoute.reservations}`, payload)

    console.log("Respuesta del servidor:", response.data)

    events.value.push(newEvent)
    showSnackbar("Turno creado correctamente", "success")
  }
  dialog.value = false
}

function showSnackbar(text, color) {
  snackbar.value = { show: true, text, color }
}

// Configuración de FullCalendar
const calendarOptions = {
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: "timeGridWeek",

  // Configuración específica para Argentina
  locale: "es",
  firstDay: 1, // Lunes como primer día de la semana, estándar en Argentina
  slotLabelFormat: {
    hour: '2-digit',
    minute: '2-digit',
    hour12: false // Formato de 24 horas
  },
 
  // Traducción de botones del encabezado
  buttonText: {
    today: 'Hoy',
    month: 'Mes',
    week: 'Semana',
    day: 'Día'
  },
 
  // Resto de tu configuración
  selectable: true,
  editable: true,
  events: events.value,
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay'
  },
 eventClick(info) {
  openDialog(info.event)
 },
 select(info) {
  reservationData.value = {
   id: null,
   client: "",
   dog: "",
   service: "",
   status: "programado",
   phone: "",
   notes: "",
   start: info.startStr,
   end: info.endStr
  }
  dialog.value = true
 },
 eventChange(info) {
  const updatedEvent = {
   id: info.event.id,
   title: info.event.title,
   start: info.event.startStr,
   end: info.event.endStr,
   extendedProps: { ...info.event.extendedProps },
   color: info.event.backgroundColor,
  }
  const index = events.value.findIndex(e => e.id === updatedEvent.id)
  if (index !== -1) {
   events.value[index] = updatedEvent
   showSnackbar("Turno reagendado correctamente", "success")
  }
 }
  
}
</script>

<style>
.calendar {
  height: 80vh;
}
</style>
