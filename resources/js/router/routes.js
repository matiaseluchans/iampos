export const routes = [
  { path: '/', redirect: '/dashboard' },
   
   {
    path: '/',
    component: () => import('@/layouts/default.vue'),
    children: [
      {
        path: 'dashboard',
        component: () => import('@/pages/dashboard.vue'),
        meta: { requiresAuth: true }
      },

      {
        path: 'clientes',
        component: () => import('@/pages/clientes.vue'),
        meta: { requiresAuth: true }
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
      },
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
