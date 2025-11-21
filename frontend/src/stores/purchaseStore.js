import { defineStore } from 'pinia';
import purchaseService from '../api/purchase';

export const usePurchaseStore = defineStore('purchase', {
  state: () => ({
    purchases: [],
    loading: false,
  }),
  actions: {
    async fetchPurchases() {
      this.loading = true;
      try {
        this.purchases = await purchaseService.getAll();
      } finally {
        this.loading = false;
      }
    },
    async addPurchase(purchase) {
      await purchaseService.create(purchase);
      // await this.fetchPurchases();
    },
    async updatePurchase(id, purchase) {
      await purchaseService.update(id, purchase);
      await this.fetchPurchases();
    },
    async deletePurchase(id) {
      await purchaseService.delete(id);
      await this.fetchPurchases();
    },
    async fetchPurchaseById(id) {
      return await purchaseService.get(id);
    },
  },
});
