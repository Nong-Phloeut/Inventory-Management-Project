// composables/useNotif.js
import { getCurrentInstance } from 'vue'

export function useNotif() {
  const { proxy } = getCurrentInstance()
  return proxy.$notif
}