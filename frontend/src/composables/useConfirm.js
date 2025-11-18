import { getCurrentInstance } from 'vue'

export function useConfirm() {
  const { proxy } = getCurrentInstance()
  return proxy.$confirm
}