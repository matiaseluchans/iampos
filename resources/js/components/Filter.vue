<template>
    <span>
    {{ header.title }}
    <v-menu offset-y :close-on-content-click="false">
      <template v-slot:activator="{ on, attrs }">
        <v-icon
          v-bind="attrs"
          v-on="on"
          small
          :color="filters[filterName] ? 'primary' : ''"
        >
          mdi-filter
        </v-icon>
      </template>
      <div style="background-color: white; width: 280px">
        <v-text-field
          v-model="filters[filterName]"
          class="pa-4"
          type="text"
          label="Ingrese datos para buscar"
          :autofocus="true"
        ></v-text-field>
        <v-btn
          @click="filters[filterName] = ''"
          small
          text
          :color="$cv('principal')"
          class="ml-2 mb-2"
          >Borrar</v-btn
        >
      </div>
    </v-menu>
  </span>
</template>

<script>
export default {
  props: {
    filters: "",
    header: { Object, default: null },
  },
  data: (vm) => ({
    filterName: "",
  }),

  computed: {
    dni() {
      //console.log("Hola");
    },
  },
  methods: {},
  mounted() {
    console.log("this.filters");
    console.log(this.filters);
    //console.log(this.header.header);
    this.filterName = this.header.header.value;
  },
};
</script>
