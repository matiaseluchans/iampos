import { createApp } from 'vue'
import App from '@/App.vue'
import { registerPlugins } from '@core/utils/plugins'
import store from './store/modules' 
import router from './router'
import { initializeAuth } from '@/api/auth' // Importa initializeAuth


// Styles
import '@core-scss/template/index.scss'
import '@layouts/styles/index.scss'
import swal from 'sweetalert2'

window.Swal = swal

import ClientVar from "./utils/ClientVar"
import Can from "./utils/Can"

import Rules from './utils/Rules'
import CrudRequest from './utils/CrudRequest'
import { VueMaskDirective } from 'v-mask'
import moment from 'moment'


import axios from './axios/axios'

import VCalendar from 'v-calendar';
import 'v-calendar/style.css';





const vMaskV2 = VueMaskDirective

const vMaskV3 = {
  beforeMount: vMaskV2.bind,
  updated: vMaskV2.componentUpdated,
  unmounted: vMaskV2.unbind,
}


// Create vue app
const app = createApp(App)

app.directive('mask', vMaskV3)
app.use(store)
app.use(ClientVar)
app.use(Can)
app.use(Rules)
app.use(CrudRequest)
app.use(VCalendar)
app.config.globalProperties.moment = moment
app.component('filter-component', './components/Filter.vue');
app.config.globalProperties.$axios = axios  

// Register plugins
registerPlugins(app)

// El router se instala recién cuando la auth ya está restaurada: app.use(router) dispara
// la navegación inicial, y si corre antes de que se cargue el usuario (roles), el guard
// rechaza las rutas con allowedRoles y manda a "/" (order-search) al hacer F5.
initializeAuth().finally(() => {
  app.use(router)

  // Mount vue app
  app.mount('#app')
})
