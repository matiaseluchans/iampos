<template>
  <v-menu
    v-model="menu"
    :close-on-content-click="false"
    transition="scale-transition"
    offset-y
    max-width="290px"
    min-width="auto"
  >
    <template v-slot:activator="{ props }">
      <v-text-field
        v-bind="props"
        :model-value="formattedDateTime"
        :label="label"
        :error-messages="errorMessages"
        variant="outlined"
        readonly
        :required="required"
        prepend-inner-icon="mdi-calendar"        
      ></v-text-field>
    </template>
    
    <v-card>
      <v-card-text class="pa-0">
        <v-tabs v-model="tab" grow>
          <v-tab value="date">
            <v-icon icon="mdi-calendar" class="mr-2"></v-icon>
            Fecha
          </v-tab>
          <v-tab value="time" :disabled="!date">
            <v-icon icon="mdi-clock" class="mr-2"></v-icon>
            Hora
          </v-tab>
        </v-tabs>

        <v-window v-model="tab">
          <v-window-item value="date">
            <v-date-picker
              v-model="date"
              :min="min"
              @update:model-value="onDateChange"
              full-width
            ></v-date-picker>
          </v-window-item>
          
          <v-window-item value="time">
            <v-time-picker
              v-model="time"
              format="24hr"
              @update:model-value="onTimeChange"
              full-width
            ></v-time-picker>
          </v-window-item>
        </v-window>
      </v-card-text>
      
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn variant="text" @click="menu = false">Cancelar</v-btn>
        <v-btn color="primary" variant="text" @click="saveDateTime">OK</v-btn>
      </v-card-actions>
    </v-card>
  </v-menu>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { format, parseISO } from 'date-fns'

const props = defineProps({
  modelValue: String,
  label: String,
  errorMessages: Array,
  required: Boolean,
  min: String
})

const emit = defineEmits(['update:model-value'])

const menu = ref(false)
const tab = ref('date')
const date = ref(null)
const time = ref(null)

const formattedDateTime = computed(() => {
  if (!props.modelValue) return ''
  try {
    return format(parseISO(props.modelValue), 'dd/MM/yyyy HH:mm')
  } catch {
    return props.modelValue
  }
})

watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    const dt = parseISO(newVal)
    date.value = format(dt, 'yyyy-MM-dd')
    time.value = format(dt, 'HH:mm')
  } else {
    date.value = null
    time.value = null
  }
}, { immediate: true })

const onDateChange = () => {
  if (date.value) {
    tab.value = 'time'
  }
}

const onTimeChange = () => {
  if (time.value) {
    saveDateTime()
  }
}

const saveDateTime = () => {
  if (date.value && time.value) {
    const dateTime = `${date.value}T${time.value}:00`
    emit('update:model-value', dateTime)
    menu.value = false
  }
}
</script>