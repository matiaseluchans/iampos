<script setup>
import logo from "@images/logos/logo1.png";
</script>

<template>
  <v-dialog v-model="dialog" max-width="500px" persistent>
    <v-card class="pa-4" style="position: relative;">     
      <div class="container_header">
        <div class="one">
          <!-- Botón con ícono -->         
          <v-btn
            class="ml-3 mt-1"
            dark
            elevation="0"
            fab
            icon
            x-large
            :color="`${color}45`"
            :style="{ pointerEvents: 'none', background: `${color}45` }"
          >
            <v-icon
              :color="color"
              class="text-center"
              style="font-size: 30px; pointer-events: none;"
            >
              {{ icon }}
            </v-icon>
          </v-btn>        
        </div>
        <div class="two">
          <!-- Imagen de logo -->
          <v-img  
            max-height="100px" 
            max-width="100px"
            :src="logo"
            cover
          />
        </div>
      </div>

      <!-- Título -->
      <v-card-title class="headline">
        <p
          class="text-left capitalize-first custom-btn mt-2"
          style="font-weight: 500; font-size: 18px;"
          v-html="title"
        ></p>
      </v-card-title>

      <!-- Texto -->
      <v-card-text>
        <p
          style="font-weight: 400; color: #7D7D7D;"
          class="mt-4"
          v-html="info"
        ></p>
      </v-card-text>

      <!-- Botones de acción -->
      <v-card-actions style="background: #F1F1F1;" class="mt-4 pt-4 pb-4">
        <v-btn width="47%"  :style="{ backgroundColor: 'white' }" @click="closeDialog" class="capitalize-first custom-btn">
          Cancelar
        </v-btn>
        <v-btn width="51%" color="white" dark @click="confirm"  :style="{ backgroundColor: color }" class="capitalize-first custom-btn">
          Sí, estoy seguro
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
      this.$emit('close');
    },
    confirm() {
      this.$emit('confirm');
      this.closeDialog();
    },
  },
};
</script>
<style scoped>
.container_header {
      width: 100%;        
      }

      .one {
      width: 50%;        
      float: left;
      }

      .two {
      margin-left: 90%;        
      }

.custom-btn {
  text-transform: none; /* Elimina la transformación de mayúsculas */
  font-family: inherit; /* Hereda el tipo de letra por defecto */
}

.capitalize-first::first-letter {
  text-transform: capitalize; /* Solo la primera letra en mayúscula */
}
</style>
