import { defineStore } from 'pinia'
import inventoryService from '@/api/inventoryAI'

export const useInventoryAIStore = defineStore('inventoryAI', {
  state: () => ({
  }),

  actions: {
    async fetchPurchaseRecommendation() {
      return await inventoryService.purchaseRecommendation()
    }
  }
})
