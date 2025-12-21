import { defineStore } from 'pinia'
import inventoryService from '@/api/inventoryAI'

export const useInventoryAIStore = defineStore('inventoryAI', {
  state: () => ({
    products: [],
    forecast: null,
    reorderSuggestion: null,
    loading: false
  }),

  actions: {
    async fetchProducts() {
      this.products = (await inventoryService.getProducts()).data
    },

    async fetchAIData(productId) {
      this.loading = true
      try {
        const [forecast, reorder] = await Promise.all([
          inventoryService.getForecast(productId),
          inventoryService.getReorderSuggestion(productId)
        ])

        this.forecast = forecast.data
        this.reorderSuggestion = reorder.data
      } finally {
        this.loading = false
      }
    }
  }
})
