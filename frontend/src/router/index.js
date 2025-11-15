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
        path: '/suppliers',
        name: 'Suppliers',
        component: () => import('@/views/stocks/SupplierManagement.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: '/stock-movements',
        name: 'StockMovements',
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
        path: '/reports',
        name: 'Reports',
        component: () => import('@/views/stocks/ReportManagement.vue'),
        meta: { requiresAuth: true }
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

export default router
