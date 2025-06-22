import { createRouter, createWebHistory } from 'vue-router'
import { routes } from './routes'
import store from '@/store/modules'
import { initializeAuth } from "@/api/auth"

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

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
})

export default function (app) {
  app.use(router)
}
