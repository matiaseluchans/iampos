// helpers/navFilter.js
import { routes } from '@/router/routes'

export function filterNavItems(navItems, user) {
    if (!user?.data) return []
  
    return navItems.filter(item => {
      // 1. LINKS DIRECTOS (como Dashboard)
      if (item.component === 'VerticalNavLink' && item.item?.to) {
        const route = findRouteByPath(routes, item.item.to)
        if (!route) {
          console.warn(`Ruta no encontrada: ${item.item.to}`)
          return false
        }
        return hasAccess(route, user)
      }
  
      // 2. GRUPOS (como "Productos")
      if (item.component === 'VerticalNavGroup' && item.children) {
        item.children = filterNavItems(item.children, user)
        return item.children.length > 0
      }
  
      // 3. TÍTULOS (si los usas)
      if (item.component === 'VerticalNavSectionTitle') return true
  
      return false
    })
  }

function findRouteByPath(routes, path) {
    const cleanPath = path.replace(/^\//, '') // Elimina el slash inicial
    
    
    for (const route of routes) {
      // Compara con o sin slash
      if (route.path === cleanPath || route.path === `/${cleanPath}`) {
        return route
      }
      
      // Busca en rutas anidadas (como las de /layouts/default.vue)
      if (route.children) {
        const found = findRouteByPath(route.children, cleanPath)
        if (found) return found
      }
    }
    return null
  }
 

function hasAccess(route, user) {
    if (!route.meta) return true
    
    const tenantId = String(user?.data?.tenant?.id) // Asegurar string
    const userRoles = user?.data?.roles || []
  
    // Verificar tenant
    /*const allowedTenants = route.meta.allowedTenants?.map(String) || ['*']
    if (allowedTenants[0] !== '*' && !allowedTenants.includes(tenantId)) {
      return false
    }*/
  
    // Verificar roles
    const allowedRoles = route.meta.allowedRoles || ['*']
    if (allowedRoles[0] !== '*' && !userRoles.some(role => allowedRoles.includes(role))) {
      return false
    }
  
    return true
  }
