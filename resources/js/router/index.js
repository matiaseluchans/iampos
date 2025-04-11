// router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import { routes } from './routes'
import axios from '@/axios/axios' // Importá tu instancia de Axios

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

// Guard de navegación
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')

  if (to.meta.requiresAuth && !token) {
    next('/login')
  } else if (to.path === '/login' && token) {
    next('/dashboard')
  } else {
    next()
  }
})

//export default router

export default function (app) {
  app.use(router)
} 
//export { router }

//export default router
