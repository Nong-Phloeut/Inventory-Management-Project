import { defineStore } from 'pinia'
import purchaseService from '../api/purchase'

export const usePurchaseStore = defineStore('purchase', {
  state: () => ({
    purchases: [],
    purchase: {},
    loading: false
  }),
  actions: {
    async fetchPurchases(ilterParams = {}) {
      this.loading = true
      try {
        this.purchases = await purchaseService.getAll(ilterParams)
      } finally {
        this.loading = false
      }
    },
    async addPurchase(purchase) {
      await purchaseService.create(purchase)
      await this.fetchPurchases()
    },
    async updatePurchase(id, purchase) {
      await purchaseService.update(id, purchase)
      await this.fetchPurchases()
    },
    async deletePurchase(id) {
      await purchaseService.delete(id)
      await this.fetchPurchases()
    },
    async fetchPurchaseById(id) {
      const response = await purchaseService.get(id)
      const { purchase_date } = response
      this.purchase = response
      this.purchase.purchase_date = new Date(purchase_date)
    }
  }
})
