import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

export function usePermission() {
  const authStore = useAuthStore()

  const hasRole = (...roles) => {
    return roles.includes(authStore.me?.role_id)
  }

  const isAdmin = computed(() => authStore.me?.role_id === 1)
  const isManager = computed(() => authStore.me?.role_id === 2)
  const isPurchaser = computed(() => authStore.me?.role_id === 3)

  return {
    hasRole,
    isAdmin,
    isManager,
    isPurchaser
  }
}

