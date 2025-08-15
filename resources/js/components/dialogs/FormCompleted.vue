<template>
  <v-dialog v-model="dialog" max-width="500px" persistent>
    <v-card>
      <v-btn
        class="ml-6 mt-6"
        dark
        elevation="0"
        fab
        icon
        x-large
        :style="{ pointerEvents: 'none', background: `${color}45` }"
      >
        <v-icon

          :color="color"
          class="text-center"
          style="font-size: 55px;pointer-events: none;"
        >
          {{ icon }}
        </v-icon>
      </v-btn>
      <v-card-title class="headline">
        <p class="text-left capitalize-first custom-btn" style="font-weight: 500; font-size: 18px;"  v-html="title">
        </p>
      </v-card-title>

      <v-card-text>
        <p style="font-weight: 400; color: #7D7D7D;" v-html="info">

        </p>
      </v-card-text>

      <v-card-actions style="background: #F1F1F1;" class="pt-4 pb-4">
        <v-spacer></v-spacer>

        <v-btn width="47%" color="primary" @click="confirm" class="capitalize-first custom-btn">
          Aceptar
        </v-btn>

      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  props: {

    value: {
      type: Boolean,
      default: false,
    },
     icon: {
      type: String,
      required: true
    },
     color: {
      type: String,
      required: true
    },

     title: {
      type: String,
      required: true
    },
     info: {
      type: String,
      default: false,
    },
  },
  data() {
    return {
      dialog: this.value,
    };
  },
  watch: {
    value(val) {
      this.dialog = val;
    },
    dialog(val) {
      this.$emit('input', val);
    },
  },
  methods: {
    closeDialog() {
      this.dialog = false;
    },
    confirm() {
      if (this.$listeners['confirm']) {
        this.$emit('confirm');
        this.closeDialog();
      }
      else{
        this.closeDialog();
      }
      
    },
  },
};
</script>
<style scoped>
.custom-btn {
  text-transform: none; /* Elimina la transformación de mayúsculas */
  font-family: inherit; /* Hereda el tipo de letra por defecto */
}

.capitalize-first::first-letter {
  text-transform: capitalize; /* Solo la primera letra en mayúscula */
}
</style>
