import {apiRoute} from "@/helper/apiRoute";

export default {

      install(Vue, options) {


          Vue.config.globalProperties.$cv = function ( value) {
            //let client = this.$keycloak.tokenParsed.foo_tenants[0];
            //let client = this.$keycloak.tokenParsed.tenantId;

          
            var vars = {
              primary:'white',
              color:"#6DB2D9",
              principal:"primary",
              btnEditar:"primary-2",
              btnReset:"orange",
              btnActivo:"blue",
              btnInactivo:"red",
              btnEliminar:"red",
              btnVista:'primary',
              titleActivar:"Activar",
              titleDesactivar:"Desactivar",
              filterColor:"grey-lighten-2"

          }

          this.$vuetify.theme.themes.dark.primary  = vars["primary"];
          this.$vuetify.theme.themes.light.primary = vars["primary"];
          this.$vuetify.theme.themes.dark.color    = vars["color"];
          this.$vuetify.theme.themes.light.color   = vars["color"];
          return vars[value];
          },

          Vue.config.globalProperties.$getPersonsTypes = function () {
            return [
              { id: 1, name: "FÍSICA" },
              { id: 2, name: "JURÍDICA" },
            ];
          },

          Vue.config.globalProperties.$appName = process.env.VUE_APP_NAME;

          Vue.config.globalProperties.$routes = apiRoute;

          Vue.config.globalProperties.$statusOrders = Object.freeze({
            PENDING: 'pending',
           APPROVED: 'approved',
           REJECTED: 'rejected',
           COMPLETED: 'completed',
           CONFIRM: 'confirm',
           PROCESS: 'process',
           PAID: 'paid',
           CANCEL: 'cancelled',
           PARTIAL_PAYMENT: 'partial_payment',
           REFUND: 'refund',
           NOT_REQUIRED: 'not_required',
           READY_PICKUP: 'ready_pickup',
           SHIPPED: 'shipped',
           IN_TRANSIT: 'in_transit',
           DELIVERED: 'delivered',
           FAILED: 'failed',
           RETURNED: 'returned',
          });

          

      }
  }


