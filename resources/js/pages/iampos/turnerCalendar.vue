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

    <VDialog v-model="dialog" max-width="600px">
      <VCard>
        <VToolbar color="primary" density="compact">
          <VToolbarTitle class="text-white">
            {{ editedEvent.id ? "Editar Turno" : "Nuevo Turno" }}
          </VToolbarTitle>
          <VSpacer />
          <VBtn icon color="white" @click="dialog = false">
            <VIcon>ri-close-line</VIcon>
          </VBtn>
        </VToolbar>

        <VCardText class="pt-4">
          <VRow dense>
            <VCol cols="12" sm="6">
              <VTextField v-model="editedEvent.client" label="Cliente" required />
            </VCol>
            <VCol cols="12" sm="6">
              <VTextField v-model="editedEvent.dog" label="Mascota" required />
            </VCol>
            <VCol cols="12" sm="6">
              <VSelect v-model="editedEvent.service" :items="services" label="Servicio" />
            </VCol>
            <VCol cols="12" sm="6">
              <VSelect v-model="editedEvent.status" :items="statuses" label="Estado" />
            </VCol>
            <VCol cols="12">
              <VTextField v-model="editedEvent.phone" label="Teléfono" />
            </VCol>
            <VCol cols="12">
              <VTextarea v-model="editedEvent.notes" label="Notas" rows="2" />
            </VCol>
          </VRow>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn variant="outlined" @click="dialog = false">Cancelar</VBtn>
          <VBtn color="primary" @click="saveEvent">Guardar</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <VSnackbar v-model="snackbar.show" :color="snackbar.color">
      {{ snackbar.text }}
      <template #actions>
        <VBtn variant="text" @click="snackbar.show = false">Cerrar</VBtn>
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

// Arreglos de datos de prueba
const services = ["Baño", "Corte de pelo", "Baño y corte", "Limpieza dental", "Corte de uñas", "Tratamiento antipulgas"]
const statuses = ["programado", "completado", "cancelado"]
const clientNames = ["María", "Juan", "Pedro", "Ana", "Luis", "Sofía", "Carlos", "Laura", "Diego", "Elena"]
const dogNames = ["Max", "Luna", "Rocky", "Bella", "Coco", "Duke", "Daisy", "Toby", "Milo", "Lola"]

const calendarRef = ref(null)
const dialog = ref(false)
const snackbar = ref({ show: false, text: "", color: "success" })

const editedEvent = ref({
  id: null,
  client: "",
  dog: "",
  service: "",
  status: "programado",
  phone: "",
  notes: "",
  start: "",
  end: ""
})

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
    editedEvent.value = { 
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
    editedEvent.value = {
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

function saveEvent() {
  if (!editedEvent.value.client || !editedEvent.value.dog || !editedEvent.value.service) {
    showSnackbar("Por favor complete los campos obligatorios.", "error")
    return
  }

  const newEvent = {
    id: editedEvent.value.id || Date.now().toString(),
    title: `${editedEvent.value.client} y ${editedEvent.value.dog}`,
    start: editedEvent.value.start,
    end: editedEvent.value.end,
    extendedProps: { ...editedEvent.value },
    color: statusColor(editedEvent.value.status)
  }

  if (editedEvent.value.id) {
    const index = events.value.findIndex((e) => e.id === editedEvent.value.id)
    if (index !== -1) {
      events.value[index] = newEvent
      showSnackbar("Turno actualizado correctamente", "success")
    }
  } else {
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
  editedEvent.value = {
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
