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
        style="pointer-events: none;background: rgba(255, 82, 82, 0.15);"
      >
        <v-icon
          color="warning"
          class="text-center"
          style="font-size: 55px;pointer-events: none;"
        >
          {{ icon }}
        </v-icon>
      </v-btn>
      <v-card-title class="headline">
        <p class="text-left" style="font-weight: 500; font-size: 18px;">
          {{ title }}
        </p>
      </v-card-title>

      <v-card-text>
        <p style="font-weight: 400; color:  #7D7D7D;"  v-html="bodyText">


        </p>
      </v-card-text>

      <v-card-actions style="background: #F1F1F1;" class="pt-4 pb-4">
        <v-spacer></v-spacer>

        <v-btn width="47%" color="warning" @click="closeDialog" class="capitalize-first custom-btn">
          Volver a revisar
        </v-btn>

      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<style scoped>
.custom-btn {
  text-transform: none; /* Elimina la transformación de mayúsculas */
  font-family: inherit; /* Hereda el tipo de letra por defecto */
}

.capitalize-first::first-letter {
  text-transform: capitalize; /* Solo la primera letra en mayúscula */
}
</style>
<script>
export default {
  props: {

     icon: {
      type: String,
      required: false,
      default:"mdi-alert-circle-outline"
    },

    value: {
      type: Boolean,
      default: false,
    },
     title: {
      type: String,
      required: false,
      default:"Revisa tu formulario"
    },
    bodyText: {
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

  },
};
</script>
