<template>
  <VCard variant="outlined" class="mb-4">
    <VCardTitle class="text-subtitle-1 pa-4 pb-0">
      {{ modelLabel || 'Rango de fechas' }}
    </VCardTitle>
    <VCardText class="pt-2">
      <VRow dense>
        <VCol cols="6">
          <VTextField
            v-model="start"
            label="Desde"
            placeholder="dd/mm/yyyy"
            v-mask="'##/##/####'"
            :error-messages="startError"
            @blur="validateStart"
            clearable
            density="compact"
          />
        </VCol>

        <VCol cols="6">
          <VTextField
            v-model="end"
            label="Hasta"
            placeholder="dd/mm/yyyy"
            v-mask="'##/##/####'"
            :error-messages="endError"
            @blur="validateEnd"
            clearable
            density="compact"
          />
        </VCol>
      </VRow>
    </VCardText>
  </VCard>
</template>

<script>
export default {
  name: 'DateRangeField',
  props: {
    modelValue: {
      type: Object,
      default: () => ({ start: null, end: null })
    },
    modelLabel: {
      type: String, default: ()=> '',
    },
  },
  data() {
    return {
      start: '',
      end: '',
      startError: '',
      endError: ''
    }
  },
  watch: {
    start(newVal) {
      this.validateStart()
      this.emitIfValid()
    },
    end(newVal) {
      this.validateEnd()
      this.emitIfValid()
    }
  },
  methods: {
    reset() {
      this.start = null
      this.end = null
      this.startError = null
      this.endError = null
      // Cualquier otra lógica de reset necesaria
    },
    isValidDate(str) {
        if (!/^\d{2}\/\d{2}\/\d{4}$/.test(str)) return false

        const [d, m, y] = str.split('/').map(Number)

        const date = new Date(y, m - 1, d)

        return (
            date.getFullYear() === y &&
            date.getMonth() === m - 1 &&
            date.getDate() === d
        )   
    },

    validateStart() {        
      if (this.start && !this.isValidDate(this.start)) {
        this.startError = 'Fecha inválida'
      } else {
        this.startError = ''
      }
    },
    validateEnd() {
      if (this.end && !this.isValidDate(this.end)) {
        this.endError = 'Fecha inválida'
      } else {
        this.endError = ''
      }
    },
    emitIfValid() {
      if (this.isValidDate(this.start) && this.isValidDate(this.end)) {
        const [sd, sm, sy] = this.start.split('/')
        const [ed, em, ey] = this.end.split('/')
        const startDate = new Date(`${sy}-${sm}-${sd}`)
        const endDate = new Date(`${ey}-${em}-${ed}`)
        this.$emit('update:modelValue', { start: startDate, end: endDate })
      } else {
        this.$emit('update:modelValue', { start: null, end: null })
      }
    }
  },
  mounted() {
    // Inicializa valores si vienen desde el padre
    if (this.modelValue.start instanceof Date) {
      const d = this.modelValue.start
      this.start = `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`
    }
    if (this.modelValue.end instanceof Date) {
      const d = this.modelValue.end
      this.end = `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')}/${d.getFullYear()}`
    }
  }
}
</script>
<style scoped>
/* Estilo para el grupo de fechas */
.date-range-group {
  border: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 6px;
  padding: 16px;
  margin-bottom: 16px;
}

.date-range-group__title {
  font-size: 0.875rem;
  font-weight: 500;
  margin-bottom: 8px;
  color: rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity));
}
</style>