import axios from 'axios';
//import { useRouter } from "vue-router"


import { logout } from '@/api/auth'

const instance = axios.create({
  baseURL: import.meta.env.VITE_APP_URL,
})

const token = localStorage.getItem('token')
if (token) {
  instance.defaults.headers.common['Authorization'] = `Bearer ${token}`
}
//const router = useRouter()

// Interceptor para manejar respuestas con código 401
instance.interceptors.response.use(
  response => response,
  error => {
    
    
    if (error.response && error.response.status === 401) {
      // Redirigir usando Vue Router
      localStorage.removeItem('token');

      //router.push('/login');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default instance
