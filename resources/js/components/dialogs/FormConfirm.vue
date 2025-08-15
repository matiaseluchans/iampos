<template>
  <v-dialog v-model="dialogConfirmar" max-width="500px" persistent>
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
        <p class="text-left" style="font-weight: 500; font-size: 18px;" v-html="title">


        </p>
      </v-card-title>

      <v-card-text>
        <p style="font-weight: 400; color:  #7D7D7D;" v-html="bodyText">

        </p>
      </v-card-text>

      <v-card-actions style="background: #F1F1F1;" class="pt-4 pb-4">

        <v-btn width="47%" color="white" @click="cancel" class="capitalize-first custom-btn">
          Cancelar
        </v-btn>
        <v-btn width="47%" :color="color" dark @click="confirm"  class="capitalize-first custom-btn">

          {{ textButton }}
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
      required: false,
      default:"mdi-alert-circle-outline"
    },

      title: {
      type: String,
      required: false,
      default:"¿Estás seguro de que deseas guardar los <br>cambios realizados?"
    },
    bodyText: {
      type: String,
      default: "",
    },
    textButton: {
      type: String,
      default: "Sí, estoy seguro",
    },
    color: {
      type: String,
      default: "#0078D4",
    },

  },
  data() {
    return {

      dialogConfirmar: this.value,
    };
  },
  watch: {
    value(val) {
      this.dialogConfirmar = val;
    },
    dialogConfirmar(val) {
      this.$emit('input', val);
    },
  },
  methods: {
    confirm() {
      this.$emit('close', { confirmed: true });
      this.dialogConfirmar= false;
    },
    cancel() {
      this.$emit('close', { confirmed: false });
      this.dialogConfirmar = false;
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
