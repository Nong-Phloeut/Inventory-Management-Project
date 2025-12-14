import { defineStore } from 'pinia'
import aiService from '@/api/aiAssistant'

export const useInventoryAIStore = defineStore('inventoryAI', {
  state: () => ({
    lowStock: '',
    purchase: '',
    movement: '',
    loading: {
      lowStock: false,
      purchase: false,
      movement: false
    }
  }),
  actions: {
    async fetchLowStock() {
      this.loading.lowStock = true
      try {
        const { data } = await aiService.getLowStockAI()
        this.lowStock = data.recommendations
      } catch (error) {
        console.error(error)
      } finally {
        this.loading.lowStock = false
      }
    },
    async fetchPurchase(params = {}) {
      this.loading.purchase = true
      try {
        const { data } = await aiService.getPurchaseAI(params)
        this.purchase = data.insights
      } catch (error) {
        console.error(error)
      } finally {
        this.loading.purchase = false
      }
    },
    async fetchMovement(params = {}) {
      this.loading.movement = true
      try {
        const { data } = await aiService.getStockMovementAI(params)
        this.movement = data.insights
      } catch (error) {
        console.error(error)
      } finally {
        this.loading.movement = false
      }
    }
  }
})
