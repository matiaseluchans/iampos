  export default {

    install(Vue, options) {


      Vue.config.globalProperties.can = function ( value) {
        return true;
          // let client = this.$keycloak.tokenParsed.foo_tenants[0];
          /*if (this.$keycloak.hasResourceRole(value)) {
            return true;
          }
          else
          return false;*/

        }
        Vue.config.globalProperties.is = function ( value) {
          return true;
          /*if (this.$keycloak.hasResourceRole(value)) {
            return true;
          }
          else
          return false;*/

        }

    }
  }


