export const routes = [
  { path: '/', redirect: '/dashboard' },
   
   {
    path: '/',
    component: () => import('@/layouts/default.vue'),
    children: [
      {
        path: '/dashboard',
        component: () => import('@/pages/dashboard.vue'),
        meta: { requiresAuth: true,
          allowedTenants: ['*'],
          allowedRoles: ['*']
          
         }
      },
      {
        path: 'order-create',
        component: () => import('@/pages/iampos/orderCreate.vue'),
        meta: { requiresAuth: true,
          allowedTenants: ['*'],  
          allowedRoles: ['*'] 
        }
      },
      {
        path: 'order-list',
        component: () => import('@/pages/iampos/orderList.vue'),
        meta: { requiresAuth: true, 
          allowedTenants: [2,3], // Solo tenant
          allowedRoles: ['*'] 
        }
      },      
      {
        path: 'customers',
        component: () => import('@/pages/iampos/customers.vue'),
        meta: { requiresAuth: true,
          allowedTenants: [2,3],  
          allowedRoles: ['admin'] 
        }
      }, 
      {
        path: 'localities',
        component: () => import('@/pages/iampos/localities.vue'),
        meta: { requiresAuth: true }
      }, 
      {
        path: 'products',
        component: () => import('@/pages/iampos/products.vue'),
        meta: { requiresAuth: true }
      }, 
      {
        path: 'stocks',
        component: () => import('@/pages/iampos/stocks.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'warehouses',
        component: () => import('@/pages/iampos/warehouses.vue'),
        meta: { requiresAuth: true }
      }, 
      {
        path: 'categories',
        component: () => import('@/pages/iampos/categories.vue'),
        meta: { requiresAuth: true }
      }, 
      {
        path: 'brands',
        component: () => import('@/pages/iampos/brands.vue'),
        meta: { requiresAuth: true }
      }, 
      {
        path: 'tenants',
        component: () => import('@/pages/iampos/tenants.vue'),
        meta: { requiresAuth: true ,
          allowedTenants: [1,2], // Solo para superadmin
          allowedRoles: ['superadmin']
        }
      }, 

      {
        path: 'users',
        component: () => import('@/pages/iampos/users.vue'),
        meta: { requiresAuth: true,
          allowedTenants: ['1'], // Solo para superadmin
          allowedRoles: ['superadmin']
        }
      },
      
      {
        path: 'statuses',
        component: () => import('@/pages/iampos/statuses.vue'),
        meta: { requiresAuth: true },
      },

      {
        path: 'paymentMethods',
        component: () => import('@/pages/iampos/paymentMethods.vue'),
        meta: { requiresAuth: true },
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
      /*
      {
        path: 'companies',
        component: () => import('@/pages/companies.vue'),
      },
      {
        path: 'clasificaciones',
        component: () => import('@/pages/clasificaciones.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'estados',
        component: () => import('@/pages/estados.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'funciones',
        component: () => import('@/pages/funciones.vue'),
        meta: { requiresAuth: true }
      },*/
      {
        path: 'partes',
        component: () => import('@/pages/partes.vue'),
      },
      {
        path: 'partes_status',
        component: () => import('@/pages/partes_status.vue'),
        props: { status: 'Recibidos' }         
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
