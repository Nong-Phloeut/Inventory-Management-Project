import { defineStore } from 'pinia'
import productApi from '../api/salePOS'
import salesApi from '../api/sales'

export const useSaleStore = defineStore('sales', {
  state: () => ({
    products: [],
    loading: false,
    error: null
  }),

  actions: {
    async fetchProducts() {
      this.loading = true
      this.error = null
      try {
        const data = await productApi.listSaleProductPOS()
        this.products = data.data
      } catch (err) {
        this.error = err.message || 'Failed to fetch products'
      } finally {
        this.loading = false
      }
    },

    // optional: get product by id
    getProductById(id) {
      return this.products.find(p => p.id === id)
    },

    async checkout(saleData) {
      const res = await salesApi.create(saleData)
      return res
    }
  }
})
