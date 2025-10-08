import { ref, computed } from 'vue'
import { apiRoute } from '@/helper/apiRoute'
import axios from '@/axios/axios'

export function useServiceTypes() {
  const serviceTypes = ref([])
  const serviceType = ref(null)
  const loading = ref(false)
  const error = ref(null)

  // GET ALL - Listar todos los tipos de servicio
  const fetchServiceTypes = async (params = {}) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.get(apiRoute.servicesTypes, { 
        params: {
          include: 'resourceType,reservations_count',
          ...params
        }
      })
      serviceTypes.value = response.data.data || response.data
      return serviceTypes.value
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar los tipos de servicio'
      throw err
    } finally {
      loading.value = false
    }
  }

  // GET BY ID - Obtener un tipo de servicio por ID
  const fetchServiceType = async (id) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.get(`${apiRoute.servicesTypes}/${id}`, {
        params: {
          include: 'resourceType,reservations_count'
        }
      })
      serviceType.value = response.data.data || response.data
      return serviceType.value
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar el tipo de servicio'
      throw err
    } finally {
      loading.value = false
    }
  }

  // CREATE - Crear nuevo tipo de servicio
  const createServiceType = async (serviceTypeData) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.post(apiRoute.servicesTypes, serviceTypeData)
      const newServiceType = response.data.data || response.data
      serviceTypes.value.push(newServiceType)
      return newServiceType
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear el tipo de servicio'
      throw err
    } finally {
      loading.value = false
    }
  }

  // UPDATE - Actualizar tipo de servicio
  const updateServiceType = async (id, serviceTypeData) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.put(`${apiRoute.servicesTypes}/${id}`, serviceTypeData)
      const updatedServiceType = response.data.data || response.data
      
      // Actualizar en la lista
      const index = serviceTypes.value.findIndex(st => st.id === id)
      if (index !== -1) {
        serviceTypes.value[index] = updatedServiceType
      }
      
      // Actualizar el serviceType actual si es el mismo
      if (serviceType.value && serviceType.value.id === id) {
        serviceType.value = updatedServiceType
      }
      
      return updatedServiceType
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar el tipo de servicio'
      throw err
    } finally {
      loading.value = false
    }
  }

  // DELETE - Eliminar tipo de servicio
  const deleteServiceType = async (id) => {
    loading.value = true
    error.value = null
    
    try {
      await axios.delete(`${apiRoute.servicesTypes}/${id}`)
      
      // Remover de la lista
      serviceTypes.value = serviceTypes.value.filter(st => st.id !== id)
      
      // Limpiar serviceType actual si es el mismo
      if (serviceType.value && serviceType.value.id === id) {
        serviceType.value = null
      }
      
      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar el tipo de servicio'
      throw err
    } finally {
      loading.value = false
    }
  }

  // SOFT DELETE - Eliminación suave
  const softDeleteServiceType = async (id) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.patch(`${apiRoute.servicesTypes}/${id}/soft-delete`)
      const deletedServiceType = response.data.data || response.data
      
      // Actualizar en la lista
      const index = serviceTypes.value.findIndex(st => st.id === id)
      if (index !== -1) {
        serviceTypes.value[index] = deletedServiceType
      }
      
      return deletedServiceType
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar el tipo de servicio'
      throw err
    } finally {
      loading.value = false
    }
  }

  // RESTORE - Restaurar tipo de servicio eliminado
  const restoreServiceType = async (id) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.patch(`${apiRoute.servicesTypes}/${id}/restore`)
      const restoredServiceType = response.data.data || response.data
      
      // Agregar a la lista
      serviceTypes.value.push(restoredServiceType)
      
      return restoredServiceType
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al restaurar el tipo de servicio'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Computed para tipos de servicio activos
  const activeServiceTypes = computed(() => {
    return serviceTypes.value.filter(st => !st.deleted_at)
  })

  // Computed para tipos de servicio eliminados
  const deletedServiceTypes = computed(() => {
    return serviceTypes.value.filter(st => st.deleted_at)
  })

  // Computed para tipos de servicio que requieren recursos
  const serviceTypesRequiringResources = computed(() => {
    return serviceTypes.value.filter(st => st.requires_resource)
  })

  // Computed para tipos de servicio que no requieren recursos
  const serviceTypesNotRequiringResources = computed(() => {
    return serviceTypes.value.filter(st => !st.requires_resource)
  })

  // Resetear estado
  const reset = () => {
    serviceTypes.value = []
    serviceType.value = null
    error.value = null
    loading.value = false
  }

  return {
    // State
    serviceTypes,
    serviceType,
    loading,
    error,
    
    // Computed
    activeServiceTypes,
    deletedServiceTypes,
    serviceTypesRequiringResources,
    serviceTypesNotRequiringResources,
    
    // Actions
    fetchServiceTypes,
    fetchServiceType,
    createServiceType,
    updateServiceType,
    deleteServiceType,
    softDeleteServiceType,
    restoreServiceType,
    reset
  }
}