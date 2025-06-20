import axios from '@/axios/axios'
import store from '@/store/modules'

export async function login(email, password) {
  try {
    const response = await axios.post('/api/login', { email, password })
    const token = response.data.access_token

    // Guardar token en store y localStorage
    store.commit('SET_TOKEN', token)
    localStorage.setItem('token', token)
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

    // Obtener y guardar datos del usuario
    const user = await fetchUser()
    return user
  } catch (error) {
    console.error('Login error:', error)
    store.commit('CLEAR_USER')
    throw error
  }
}

export async function fetchUser() {
  try {
    // Verificar si ya tenemos el usuario en el store
    if (store.getters.currentUser) {
      return store.getters.currentUser
    }

    const token = localStorage.getItem('token')
    
    if (!token) {
      throw new Error('No authentication token found')
    }
    
    const config = {
      headers: {
        Authorization: `Bearer ${token}`
      },
      withCredentials: true
    }
    
    const response = await axios.get('/api/user', config)
    
    // Guardar en el store
    store.commit('SET_USER', response.data)
    
    return response.data
  } catch (error) {
    console.error('Error fetching user data:', error)
    store.commit('CLEAR_USER')
    throw error
  }
}

// Alias para mantener compatibilidad
export const getUser = fetchUser

export async function logout() {
  try {
    await axios.post('/api/logout')
  } catch (error) {
    console.error('Logout error:', error)
  } finally {
    // Limpiar en cualquier caso
    store.commit('CLEAR_USER')
    localStorage.removeItem('token')
    delete axios.defaults.headers.common['Authorization']
  }
}

// Función para inicializar el usuario al cargar la app
export async function initializeAuth() {
  const token = localStorage.getItem('token')
  if (token) {
    try {
      // Restaurar token en axios y store
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
      store.commit('SET_TOKEN', token)
      
      // Obtener datos del usuario
      await fetchUser()
    } catch (error) {
      console.error('Error initializing auth:', error)
      store.commit('CLEAR_USER')
    }
  }
}
