import { defineStore } from 'pinia'
import purchaseService from '../api/purchase'

export const usePurchaseStore = defineStore('purchase', {
  state: () => ({
    purchases: [],
    purchase: {}
  }),
  actions: {
    async fetchPurchases(ilterParams = {}) {
      this.purchases = await purchaseService.getAll(ilterParams)
    },
    async addPurchase(purchase) {
      await purchaseService.create(purchase)
    },
    async updatePurchase(id, purchase) {
      await purchaseService.update(id, purchase)
    },
    async deletePurchase(id) {
      await purchaseService.delete(id)
    },
    async fetchPurchaseById(id) {
      const response = await purchaseService.get(id)
      const { purchase_date } = response
      this.purchase = response
      this.purchase.purchase_date = new Date(purchase_date)
    }
  }
})
