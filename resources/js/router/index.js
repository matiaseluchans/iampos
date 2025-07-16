import { createRouter, createWebHistory } from 'vue-router'
import { routes } from './routes'
import store from '@/store/modules'
import { initializeAuth } from "@/api/auth"

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




router.beforeEach(async (to, from, next) => {
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const isLoginRoute = to.path === '/login'
  
  // Intentar inicializar la autenticación si hay token
  const token = localStorage.getItem('token')
  if (token) {
    await initializeAuth()
  }

  const currentUser = store.getters.currentUser
  
  //console.log(currentUser);
  // Lógica de redirección
  if (requiresAuth && !store.getters.isAuthenticated) {
    next('/login')
  } else if (isLoginRoute && store.getters.isAuthenticated) {
    next('/dashboard')
  } else if (requiresAuth && !hasAccess(to, currentUser)) {
    // Si no tiene acceso, redirigir al dashboard o mostrar error
    next('/dashboard') // o podrías usar next('/unauthorized')
  } else {
    next()
  }
})

/*
// Guard de navegación global
router.beforeEach(async (to, from, next) => {
  // Verificar si la ruta requiere autenticación
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const isLoginRoute = to.path === '/login'
  
  // Intentar inicializar la autenticación si hay token
  const token = localStorage.getItem('token')
  if (token) {
    await initializeAuth()
  }
 

  // Lógica de redirección
  if (requiresAuth && !store.getters.isAuthenticated) {
    // Redirigir a login si no está autenticado y la ruta lo requiere
    next('/login')
  } else if (isLoginRoute && store.getters.isAuthenticated) {
    // Redirigir al dashboard si está autenticado y trata de acceder a login
    next('/dashboard')
  } else {
    // Continuar normalmente
    next()
  }
})*/



export default function (app) {
  app.use(router)
}
