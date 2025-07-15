<template>
  <v-row dense>
    <v-col cols="6">
      <v-text-field
        v-model="start"
        label="Desde"
        placeholder="dd/mm/yyyy"
        v-mask="'##/##/####'"
        :error-messages="startError"
        @blur="validateStart"
        clearable
      />
    </v-col>

    <v-col cols="6">
      <v-text-field
        v-model="end"
        label="Hasta"
        placeholder="dd/mm/yyyy"
        v-mask="'##/##/####'"
        :error-messages="endError"
        @blur="validateEnd"
        clearable
      />
    </v-col>
  </v-row>
</template>

<script>
export default {
  name: 'DateRangeField',
  props: {
    modelValue: {
      type: Object,
      default: () => ({ start: null, end: null })
    }
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
        console.log(this.start);
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