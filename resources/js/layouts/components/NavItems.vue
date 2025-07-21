<script setup>
import VerticalNavSectionTitle from '@/@layouts/components/VerticalNavSectionTitle.vue'
import VerticalNavGroup from '@layouts/components/VerticalNavGroup.vue'
import VerticalNavLink from '@layouts/components/VerticalNavLink.vue'
import { filterNavItems } from '@/helper/navFilter'
import { computed } from 'vue'
import { useStore } from 'vuex'

const store = useStore()
const user = computed(() => store.getters.currentUser)

// Definimos toda la estructura de navegación como un array de objetos
const navItems = [
  {
    component: 'VerticalNavLink',
    item: {
      title: 'Dashboard',
      icon: 'ri-home-smile-line',
      to: '/dashboard'
    }
  },
  {
    component: 'VerticalNavLink',
    item: {
      title: 'Registrar Orden',
      to: '/order-create',
      icon: 'ri-draft-line'
    }
  },
  {
    component: 'VerticalNavLink',
    item: {
      title: 'Ordenes',
      to: '/order-list',
      icon: 'ri-shopping-bag-line'
    }
  },
  {
    component: 'VerticalNavLink',
    item: {
      title: 'Buscador Ordenes',
      to: '/order-search',
      icon: 'ri-search-line'
    }
  },
  {
    component: 'VerticalNavLink',
    item: {
      title: 'Clientes',
      to: '/customers',
      icon: 'ri-group-line'
    }
  },
  {
    component: 'VerticalNavGroup',
    item: {
      title: 'Productos',
      icon: 'ri-stock-line'
    },
    children: [
      {
        component: 'VerticalNavLink',
        item: {
          title: 'Stock',
          to: '/stocks',
          icon: 'ri-stock-line'
        }
      },
      {
        component: 'VerticalNavLink',
        item: {
          title: 'Productos',
          to: '/products',
          icon: 'ri-stock-line'
        }
      }
    ]
  },
  {
    component: 'VerticalNavGroup',
    item: {
      title: 'Administración',
      icon: 'ri-tools-line'
    },
    children: [
      {
        component: 'VerticalNavLink',
        item: {
          title: 'Depositos',
          to: '/warehouses'
        }
      },
      {
        component: 'VerticalNavLink',
        item: {
          title: 'Categorias',
          to: '/categories',
          icon: 'ri-bookmark-line'
        }
      },
      {
        component: 'VerticalNavLink',
        item: {
          title: 'Marcas',
          to: '/brands',
          icon: 'ri-bookmark-line'
        }
      },
      {
        component: 'VerticalNavLink',
        item: {
          title: 'Estados',
          to: '/statuses',
          icon: 'ri-bookmark-line'
        }
      },
      {
        component: 'VerticalNavLink',
        item: {
          title: 'Metodos de Pago',
          to: '/paymentMethods',
          icon: 'ri-wallet-3-line'
        }
      },
      {
        component: 'VerticalNavLink',
        item: {
          title: 'Localidades',
          to: '/localities',
          icon: 'ri-map-pin-line'
        }
      }
    ]
  },
  {
    component: 'VerticalNavGroup',
    item: {
      title: 'Configuración',
      icon: 'ri-settings-5-line'
    },
    children: [
      {
        component: 'VerticalNavLink',
        item: {
          title: 'Tenants',
          to: '/tenants',
          icon: 'ri-database-2-line'
        }
      },
      {
        component: 'VerticalNavLink',
        item: {
          title: 'Usuarios',
          to: '/users',
          icon: 'ri-user-line'
        }
      }
    ]
  } ,
  /*{
    component: 'VerticalNavSectionTitle',
    item: {
      heading: 'Apps & Pages'
    },
  },
    
  /*{
    component: 'VerticalNavLink',
    item: {
      title: 'Tenants',
      to: '/tenants',
      icon: 'ri-database-2-line'
    }
  }*/
       
  
  // ... otros items de navegación
]

// Filtramos los items según los permisos del usuario
const filteredNavItems = computed(() => filterNavItems(navItems, user.value))

// Para depuración
console.log('Usuario actual:', user.value)
console.log('Menú filtrado:', filteredNavItems.value)
</script>

<template>
  <div>
    <template v-for="(navItem, index) in filteredNavItems" :key="index">
      <!-- LINKS DIRECTOS -->
      <VerticalNavLink
        v-if="navItem.component === 'VerticalNavLink'"
        :item="{
          title: navItem.item.title,
          to: navItem.item.to,
          icon: navItem.item.icon,
          // otras props si existen
        }"
      />
      
      <!-- GRUPOS -->
      <VerticalNavGroup
        v-else-if="navItem.component === 'VerticalNavGroup'"
        :item="{
          title: navItem.item.title,
          icon: navItem.item.icon
        }"
      >
        <VerticalNavLink
          v-for="(child, childIndex) in navItem.children"
          :key="childIndex"
          :item="{
            title: child.item.title,
            to: child.item.to,
            icon: child.item.icon
          }"
        />
      </VerticalNavGroup>
      
      <!-- TÍTULOS DE SECCIÓN -->
      <VerticalNavSectionTitle
        v-else-if="navItem.component === 'VerticalNavSectionTitle'"
        :item="navItem.item"
      />
    </template>
  </div>
</template>
