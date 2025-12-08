import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    name: 'Login',
    component: () => import('@/views/auth/Login.vue')
  },
  {
    path: '/layout',
    component: () => import('@/views/layout/Layout.vue'),
    children: [
      // User Management
      {
        path: '/dashboard',
        name: 'Dashboard',
        component: () => import('@/views/Dashboard.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: '/list-users',
        name: 'UserManagement',
        component: () => import('@/views/users/UserManagement.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: '/roles-management',
        name: 'RolesManagement',
        component: () => import('@/views/users/RolesManagement.vue'),
        meta: { requiresAuth: true }
      },

      // Stock Management Pages
      {
        path: '/products',
        name: 'Products',
        component: () => import('@/views/stocks/ProductManagement.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: '/categories',
        name: 'Categories',
        component: () => import('@/views/stocks/CategoryManagement.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: '/units',
        name: 'Units',
        component: () => import('@/views/UnitManagement.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: '/units/create',
        name: 'UnitsCreate',
        component: () => import('@/components/UnitForm.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: '/units/:id/edit',
        name: 'UnitsEdit',
        component: () => import('@/components/UnitForm.vue'),
        props: true, // Pass route param `id` as prop to component
        meta: { requiresAuth: true }
      },
      // {
      //   path: '/Unit/:id/details',
      //   name: 'purchase-details',
      //   component: () => import('@/views/purchases/UnitDetails.vue')
      // },
      {
        path: '/suppliers',
        name: 'Suppliers',
        component: () => import('@/views/stocks/SupplierManagement.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: '/stocks',
        name: 'Stocks',
        component: () => import('@/views/stocks/StockManagement.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: '/purchases',
        name: 'Purchases',
        component: () => import('@/views/stocks/PurchaseManagement.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: '/purchase/create',
        name: 'PurchaseCreate',
        component: () => import('@/components/PurchaseForm.vue')
      },
      {
        path: '/purchase/:id/edit',
        name: 'PurchaseEdit',
        component: () => import('@/components/PurchaseForm.vue')
      },
      {
        path: '/purchases/:id/details',
        name: 'purchase-details',
        component: () => import('@/views/purchases/PurchaseDetails.vue')
      },
      // {
      //   path: '/purchases/:id/invoice',
      //   name: 'purchase-invoice',
      //   component: () => import('@/pages/PurchaseInvoice.vue'),
      //   props: true
      // },
      {
        path: '/reports',
        name: 'Reports',
        component: () => import('@/views/stocks/ReportManagement.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: '/audit-logs',
        name: 'AuditLogs',
        component: () => import('@/views/auditLogs/AuditLogPage.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: '/audit-log/:id',
        name: 'audit-log-details',
        component: () => import('@/views/auditLogs/AuditLogDetails.vue'),
        props: true
      },
      {
        path: '/sales',
        name: 'Sales',
        component: () => import('@/views/stocks/SalesManagement.vue'),
        meta: { requiresAuth: true }
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Global navigation guard
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')

  // Redirect logged-in users away from Login page
  if (to.name === 'Login' && token) {
    return next({ name: 'Dashboard' }) // or any protected route
  }

  // Redirect unauthenticated users from protected pages
  if (to.meta.requiresAuth && !token) {
    return next({ name: 'Login' })
  }

  next()
})

export default router
