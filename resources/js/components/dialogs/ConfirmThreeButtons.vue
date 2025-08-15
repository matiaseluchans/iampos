<template>
  <v-dialog v-model="dialog" max-width="500px" persistent>
    <v-card>
     <v-row>
      <v-col cols="12" md="9" sm="9">
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
      </v-col>
      <v-col cols="12" md="3" sm="3">
        <v-btn class="ml-6 mt-6"
          dark
          elevation="0"
          fab
          icon color="grey" large @click="closeDialog">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </v-col>
      
     </v-row>
      
      <v-card-title class="headline">
        <p class="text-left capitalize-first custom-btn" style="font-weight: 500; font-size: 18px;"  v-html="title">
        </p>
      </v-card-title>

      <v-card-text>
        <p style="font-weight: 400; color: #7D7D7D;" v-html="info">

        </p>
      </v-card-text>

      <v-card-actions style="background: #F1F1F1;" class="pt-4 pb-4">

        <v-btn width="47%" color="white" @click="cancel">
          <span class="capitalize-first custom-btn custom-text-black">{{ buttonCancelName }}</span>
        </v-btn>
        <v-btn width="47%" :color="color" dark @click="confirm"  class="capitalize-first custom-btn">
          {{ buttonConfirmName }}
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
    buttonConfirmName:{
      type: String,
      default: 'Guardar',
    },
    buttonCancelName:{
      type: String,
      default: 'Cancelar',
    }
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
      this.$emit('confirm');
      this.closeDialog();
    },
    cancel() {
      this.$emit('cancel');
      this.closeDialog();
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
.custom-text-black {
  color: black !important;
}
</style>
