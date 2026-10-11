import { createRouter, createWebHistory } from 'vue-router'
import { routes } from './routes'
import store from '@/store/modules'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})



// Función para verificar si el usuario tiene acceso a la ruta
function hasAccess(to, user) {
  if (!to.meta) return true
  
  const tenantId = user?.data?.tenant?.id || null
  const userRoles = user?.data?.roles || []
  
  // Verificar tenant
  /*const allowedTenants = to.meta.allowedTenants || ['*']
  if (allowedTenants[0] !== '*' && !allowedTenants.includes(tenantId)) {
    return false
  }*/
  
  // Verificar roles
  const allowedRoles = to.meta.allowedRoles || ['*']
  if (allowedRoles[0] !== '*' && !userRoles.some(role => allowedRoles.includes(role))) {
    return false
  }
  
  return true
}




router.beforeEach((to, from, next) => {
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const isLoginRoute = to.path === '/login'

  // La autenticación ya se inicializa una única vez al bootstrapear la app (main.js).
  // Revalidar el token contra el backend en cada navegación agregaba latencia a cada
  // click del menú; el interceptor 401 de axios (axios/axios.js) ya cubre el caso
  // de token vencido durante la sesión.
  const currentUser = store.getters.currentUser
  
  //console.log(currentUser);
  // Lógica de redirección
  if (requiresAuth && !store.getters.isAuthenticated) {
    next('/login')
  } else if (isLoginRoute && store.getters.isAuthenticated) {
    next('/')
  } else if (requiresAuth && !hasAccess(to, currentUser)) {
    // Si no tiene acceso, redirigir al dashboard o mostrar error
    next('/') // o podrías usar next('/unauthorized')
  } else {
    next()
  }
})
 


export default function (app) {
  app.use(router)
}
