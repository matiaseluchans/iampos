import { useSnackbar } from './useSnackbar'
import axios from 'axios'
import { apiRoute } from '@/helper/apiRoute'

export function useReservations() {
  const { showError } = useSnackbar()

  const handleError = (error) => {
    if (error.response?.status === 422) {
      // Validation errors are handled by the component
      throw error
    }
    
    const message = error.response?.data?.message || 'Error de conexión'
    showError(message)
    throw error
  }

  const get = async (url, config = {}) => {
    try {
      const response = await axios.get(url, config)
      return response
    } catch (error) {
      return handleError(error)
    }
  }

  const post = async (url, data = {}, config = {}) => {
    try {
      return await axios.post(`${apiRoute.reservations}`, data)

      /*const response = await axios.post(url, data, {
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        ...config
      })
      return response*/
    } catch (error) {
      return handleError(error)
    }
  }

  return {
    get,
    post
  }
}