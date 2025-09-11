export const routes = [
  { path: '/', redirect: '/order-search' },
   
  {
    path: '/',
    component: () => import('@/layouts/default.vue'),
    children: [
      {
        path: '/dashboard',
        
        component: () => import('@/pages/dashboard.vue'),
        meta: { 
          title: "Dashboard",
          requiresAuth: true,
          allowedRoles: ['*'],
        },
      },
      {
        path: 'order-create',
        
        component: () => import('@/pages/iampos/orderCreate.vue'),
        
        meta: { 
          title: "Crear Orden",
          requiresAuth: true,
          allowedRoles: ['*'], 
        },
      },
      {
        path: 'order-list',
        
        component: () => import('@/pages/iampos/orderList.vue'),
        meta: { 
          title: "Ordenes",
          requiresAuth: true, 
          allowedRoles: ['*'], 
        },
      },
      {
        path: 'order-search',
        
        component: () => import('@/pages/iampos/orderSearch.vue'),
        meta: { 
          title: "Buscador de ordenes",
          requiresAuth: true, 
          allowedRoles: ['*'], 
        },
      },
      {
        path: 'order-pending-deliveries',
        
        component: () => import('@/pages/iampos/orderDelivery.vue'),
        meta: { 
          title: "Ordenes pendientes de entrega",
          requiresAuth: true, 
          allowedRoles: ['superadmin','bebidas-admin','petshop-admin'],
        },
      },      
      {
        path: 'customers',
        
        component: () => import('@/pages/iampos/customers.vue'),
        meta: { 
          title: "Clientes",
          requiresAuth: true,
          
        },
      }, 
      {
        path: 'localities',
        
        component: () => import('@/pages/iampos/localities.vue'),
        meta: { 
          title: "Localidades",
          requiresAuth: true, 
        },
      }, 
      {
        path: 'products',
        component: () => import('@/pages/iampos/products.vue'),
        meta: { 
          title: "Productos",
          requiresAuth: true, 
          allowedRoles: ['superadmin','bebidas-admin','petshop-admin']
        },
      }, 
      {
        path: 'stocks',
        
        component: () => import('@/pages/iampos/stocks.vue'),
        meta: { 
          title: "Stock",
          requiresAuth: true,
          allowedRoles: ['superadmin','bebidas-admin','petshop-admin']
        },
      },
      {
        path: 'warehouses',
        component: () => import('@/pages/iampos/warehouses.vue'),
        meta: { 
          title: "Depositos",
          requiresAuth: true, 
          allowedRoles: ['superadmin','bebidas-admin','petshop-admin']
        },
      }, 
      {
        path: 'categories',
        component: () => import('@/pages/iampos/categories.vue'),
        meta: { 
          title: "Categorias",
          requiresAuth: true ,
          allowedRoles: ['superadmin','bebidas-admin','petshop-admin']
        },
      }, 
      {
        path: 'brands',
        
        component: () => import('@/pages/iampos/brands.vue'),
        meta: { 
          title: "Marcas",
          requiresAuth: true,
          allowedRoles: ['superadmin','bebidas-admin','petshop-admin']
        },
      }, 
      {
        path: 'tenants',
        
        component: () => import('@/pages/iampos/tenants.vue'),
        meta: { 
          title: "Tenants",
          requiresAuth: true ,
          allowedRoles: ['superadmin'],
        },
      }, 

      {
        path: 'users',
        
        component: () => import('@/pages/iampos/users.vue'),
        meta: { 
          title: "Usuarios",
          requiresAuth: true,
          allowedRoles: ['superadmin'],
        },
      },
      
      {
        path: 'statuses',
        
        component: () => import('@/pages/iampos/statuses.vue'),
        meta: { 
          title: "Estados",
          requiresAuth: true,
          allowedRoles: ['superadmin' ]
       },
          
      },

      {
        path: 'paymentMethods',
        
        component: () => import('@/pages/iampos/paymentMethods.vue'),
        meta: { 
          title: "Métodos de pago",
          requiresAuth: true,
          allowedRoles: ['superadmin']
        },
      },

      {
        path: 'pricelist',
        
        component: () => import('@/pages/iampos/priceList.vue'),
        meta: { 
          title: "Lista de Precios",
          requiresAuth: true,
          allowedRoles: ['superadmin','bebidas-admin','petshop-admin']
        },
      },

      {
        path: 'turner-calendar',
        
        component: () => import('@/pages/iampos/turnerCalendar.vue'),
        meta: { 
          title: "Turnos",
          requiresAuth: true,
          allowedRoles: ['superadmin'],
        },
      },
      {
        path: 'turner-grid',
        
        component: () => import('@/pages/iampos/turner.vue'),
        meta: { 
          title: "Turnos Grilla",
          requiresAuth: true,
          allowedRoles: ['superadmin'],
        },
      },
      {
        path: 'account-settings',
        component: () => import('@/pages/account-settings.vue'),
      },
      {
        path: 'typography',
        component: () => import('@/pages/typography.vue'),
      },
      {
        path: 'icons',
        component: () => import('@/pages/icons.vue'),
      },
      {
        path: 'cards',
        component: () => import('@/pages/cards.vue'),
      },
      {
        path: 'tables',
        component: () => import('@/pages/tables.vue'),
      },
      {
        path: 'datatables',
        component: () => import('@/pages/datatables.vue'),
      },
      {
        path: 'form-layouts',
        component: () => import('@/pages/form-layouts.vue'),
      },
      {
        path: 'about',
        component: () => import('@/pages/about.vue'),
      },
      
    ],
  },
  {
    path: '/',
    component: () => import('@/layouts/blank.vue'),
    children: [
      {
        path: 'login',
        component: () => import('@/pages/login.vue'),
        meta: { guestOnly: true }
      },
      {
        path: 'register',
        component: () => import('@/pages/register.vue'),
      },
      {
        path: '/:pathMatch(.*)*',
        component: () => import('@/pages/[...error].vue'),
      },
    ],
  }, 
]
