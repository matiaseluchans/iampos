import { ref, computed } from 'vue'
import { apiRoute } from '@/helper/apiRoute'
import axios from '@/axios/axios'

export function useResourceTypes() {
  const resourceTypes = ref([])
  const resourceType = ref(null)
  const loading = ref(false)
  const error = ref(null)

  // GET ALL - Listar todos los tipos de recurso
  const fetchResourceTypes = async (params = {}) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.get(apiRoute.resourceTypes, { params })

      resourceTypes.value = response.data.data || response.data
      
      return resourceTypes.value
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar los tipos de recurso'
      throw err
    } finally {
      loading.value = false
    }
  }

  // GET BY ID - Obtener un tipo de recurso por ID
  const fetchResourceType = async (id) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.get(`${apiRoute.resourceTypes}/${id}`)
      resourceType.value = response.data.data || response.data
      return resourceType.value
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al cargar el tipo de recurso'
      throw err
    } finally {
      loading.value = false
    }
  }

  // CREATE - Crear nuevo tipo de recurso
  const createResourceType = async (resourceTypeData) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.post(apiRoute.resourceTypes, resourceTypeData)
      const newResourceType = response.data.data || response.data
      resourceTypes.value.push(newResourceType)
      return newResourceType
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al crear el tipo de recurso'
      throw err
    } finally {
      loading.value = false
    }
  }

  // UPDATE - Actualizar tipo de recurso
  const updateResourceType = async (id, resourceTypeData) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.put(`${apiRoute.resourceTypes}/${id}`, resourceTypeData)
      const updatedResourceType = response.data.data || response.data
      
      // Actualizar en la lista
      const index = resourceTypes.value.findIndex(rt => rt.id === id)
      if (index !== -1) {
        resourceTypes.value[index] = updatedResourceType
      }
      
      // Actualizar el resourceType actual si es el mismo
      if (resourceType.value && resourceType.value.id === id) {
        resourceType.value = updatedResourceType
      }
      
      return updatedResourceType
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al actualizar el tipo de recurso'
      throw err
    } finally {
      loading.value = false
    }
  }

  // DELETE - Eliminar tipo de recurso
  const deleteResourceType = async (id) => {
    loading.value = true
    error.value = null
    
    try {
      await axios.delete(`${apiRoute.resourceTypes}/${id}`)
      
      // Remover de la lista
      resourceTypes.value = resourceTypes.value.filter(rt => rt.id !== id)
      
      // Limpiar resourceType actual si es el mismo
      if (resourceType.value && resourceType.value.id === id) {
        resourceType.value = null
      }
      
      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar el tipo de recurso'
      throw err
    } finally {
      loading.value = false
    }
  }

  // SOFT DELETE - Eliminación suave
  const softDeleteResourceType = async (id) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.patch(`${apiRoute.resourceTypes}/${id}/soft-delete`)
      const deletedResourceType = response.data.data || response.data
      
      // Actualizar en la lista
      const index = resourceTypes.value.findIndex(rt => rt.id === id)
      if (index !== -1) {
        resourceTypes.value[index] = deletedResourceType
      }
      
      return deletedResourceType
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al eliminar el tipo de recurso'
      throw err
    } finally {
      loading.value = false
    }
  }

  // RESTORE - Restaurar tipo de recurso eliminado
  const restoreResourceType = async (id) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await axios.patch(`${apiRoute.resourceTypes}/${id}/restore`)
      const restoredResourceType = response.data.data || response.data
      
      // Agregar a la lista
      resourceTypes.value.push(restoredResourceType)
      
      return restoredResourceType
    } catch (err) {
      error.value = err.response?.data?.message || 'Error al restaurar el tipo de recurso'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Computed para tipos de recurso activos
  const activeResourceTypes = computed(() => {
    return resourceTypes.value.filter(rt => !rt.deleted_at)
  })

  // Computed para tipos de recurso eliminados
  const deletedResourceTypes = computed(() => {
    return resourceTypes.value.filter(rt => rt.deleted_at)
  })

  // Resetear estado
  const reset = () => {
    resourceTypes.value = []
    resourceType.value = null
    error.value = null
    loading.value = false
  }

  return {
    // State
    resourceTypes,
    resourceType,
    loading,
    error,
    
    // Computed
    activeResourceTypes,
    deletedResourceTypes,
    
    // Actions
    fetchResourceTypes,
    fetchResourceType,
    createResourceType,
    updateResourceType,
    deleteResourceType,
    softDeleteResourceType,
    restoreResourceType,
    reset
  }
}