import { createApp } from 'vue'
import App from '@/App.vue'
import { registerPlugins } from '@core/utils/plugins'
import router from './router'

// Styles
import '@core-scss/template/index.scss'
import '@layouts/styles/index.scss'
import swal from 'sweetalert2';
window.Swal = swal;
//import AxiosApi from "./plugins/axiosApi";
import ClientVar from "./utils/ClientVar";
import Can from "./utils/Can";

import Rules from './utils/Rules.js';
import CrudRequest from './utils/CrudRequest.js';
import { VueMaskDirective } from 'v-mask';
import moment from 'moment';


import axios from './axios/axios'

const vMaskV2 = VueMaskDirective;
const vMaskV3 = {
    beforeMount: vMaskV2.bind,
    updated: vMaskV2.componentUpdated,
    unmounted: vMaskV2.unbind
};


// Create vue app
const app = createApp(App)
app.directive('mask', vMaskV3);
app.use(ClientVar);
app.use(Can);
app.use(Rules);
app.use(CrudRequest);
app.config.globalProperties.moment = moment;
app.component('filter-component', './components/Filter.vue');
app.config.globalProperties.$axios = axios  

app.use(router)
// Register plugins
registerPlugins(app)

// Mount vue app
app.mount('#app')
