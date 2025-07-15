 
  
export default {

  install(Vue, options) {


    Vue.config.globalProperties.can = function ( value) {
      return true;
    }
    Vue.config.globalProperties.$is = function(role) {
      // Obtener roles del store (ajusta según tu estructura)
      const userRoles = this.$store.getters.currentUser.data.roles || [];
      
      console.log(role, userRoles);
      // Si es array, verificar al menos un rol (OR)
      if (Array.isArray(role)) {
        return role.some(r => userRoles.includes(r));
      }
      
      // Si es string, verificar un solo rol
      return userRoles.includes(role);
    };

  }
}


