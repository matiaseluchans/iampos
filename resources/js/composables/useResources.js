import { ref, computed } from 'vue'
import { apiRoute } from '@/helper/apiRoute'
import axios from '@/axios/axios'

export function useResources() {
  const resources = ref([])
  const resource = ref(null)
  const loading = ref(false)
  const error = ref(null)

  // GET ALL - Listar todos los recursos
  const fetchResources = async (params = {}) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.get(apiRoute.resources, { 
        params: {
          include: 'resourceType,reservations_count',
          ...params
        }
      })
      resources.value = response.data.data || response.data
      return resources.value
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar los recursos'
      throw err
    } finally {
      loading.value = false
    }
  }

  // GET BY ID - Obtener un recurso por ID
  const fetchResource = async (id) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.get(`${apiRoute.resources}/${id}`, {
        params: {
          include: 'resourceType,reservations_count'
        }
      })
      resource.value = response.data.data || response.data
      return resource.value
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar el recurso'
      throw err
    } finally {
      loading.value = false
    }
  }

  // CREATE - Crear nuevo recurso
  const createResource = async (resourceData) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.post(apiRoute.resources, resourceData)
      const newResource = response.data.data || response.data
      resources.value.push(newResource)
      return newResource
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear el recurso'
      throw err
    } finally {
      loading.value = false
    }
  }

  // UPDATE - Actualizar recurso
  const updateResource = async (id, resourceData) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.put(`${apiRoute.resources}/${id}`, resourceData)
      const updatedResource = response.data.data || response.data
      
      // Actualizar en la lista
      const index = resources.value.findIndex(r => r.id === id)
      if (index !== -1) {
        resources.value[index] = updatedResource
      }
      
      // Actualizar el resource actual si es el mismo
      if (resource.value && resource.value.id === id) {
        resource.value = updatedResource
      }
      
      return updatedResource
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar el recurso'
      throw err
    } finally {
      loading.value = false
    }
  }

  // DELETE - Eliminar recurso
  const deleteResource = async (id) => {
    loading.value = true
    error.value = null
    
    try {
      await axios.delete(`${apiRoute.resources}/${id}`)
      
      // Remover de la lista
      resources.value = resources.value.filter(r => r.id !== id)
      
      // Limpiar resource actual si es el mismo
      if (resource.value && resource.value.id === id) {
        resource.value = null
      }
      
      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar el recurso'
      throw err
    } finally {
      loading.value = false
    }
  }

  // SOFT DELETE - Eliminación suave
  const softDeleteResource = async (id) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.patch(`${apiRoute.resources}/${id}/soft-delete`)
      const deletedResource = response.data.data || response.data
      
      // Actualizar en la lista
      const index = resources.value.findIndex(r => r.id === id)
      if (index !== -1) {
        resources.value[index] = deletedResource
      }
      
      return deletedResource
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar el recurso'
      throw err
    } finally {
      loading.value = false
    }
  }

  // RESTORE - Restaurar recurso eliminado
  const restoreResource = async (id) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.patch(`${apiRoute.resources}/${id}/restore`)
      const restoredResource = response.data.data || response.data
      
      // Agregar a la lista
      resources.value.push(restoredResource)
      
      return restoredResource
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al restaurar el recurso'
      throw err
    } finally {
      loading.value = false
    }
  }

  // UPDATE USAGE - Actualizar uso actual
  const updateResourceUsage = async (id) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.patch(`${apiRoute.resources}/${id}/update-usage`)
      const updatedResource = response.data.data || response.data
      
      // Actualizar en la lista
      const index = resources.value.findIndex(r => r.id === id)
      if (index !== -1) {
        resources.value[index] = updatedResource
      }
      
      return updatedResource
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar el uso del recurso'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Computed para recursos activos
  const activeResources = computed(() => {
    return resources.value.filter(r => r.is_active && !r.deleted_at)
  })

  // Computed para recursos inactivos
  const inactiveResources = computed(() => {
    return resources.value.filter(r => !r.is_active && !r.deleted_at)
  })

  // Computed para recursos eliminados
  const deletedResources = computed(() => {
    return resources.value.filter(r => r.deleted_at)
  })

  // Computed para recursos compartidos
  const sharedResources = computed(() => {
    return resources.value.filter(r => r.is_shared)
  })

  // Computed para recursos exclusivos
  const exclusiveResources = computed(() => {
    return resources.value.filter(r => !r.is_shared)
  })

  // Resetear estado
  const reset = () => {
    resources.value = []
    resource.value = null
    error.value = null
    loading.value = false
  }

  return {
    // State
    resources,
    resource,
    loading,
    error,
    
    // Computed
    activeResources,
    inactiveResources,
    deletedResources,
    sharedResources,
    exclusiveResources,
    
    // Actions
    fetchResources,
    fetchResource,
    createResource,
    updateResource,
    deleteResource,
    softDeleteResource,
    restoreResource,
    updateResourceUsage,
    reset
  }
}