import { defineStore } from 'pinia'
import { productService } from '../api/product'

export const useProductStore = defineStore('product', {
  state: () => ({
    products: [],
    loading: false,
    error: null
  }),

  actions: {
    async fetchProducts(filterParams = {}) {
      const res = await productService.getAll(filterParams)
      this.products = res.data
    },

    async addProduct(product) {
      try {
        await productService.create(product)
        this.fetchProducts()
      } catch (err) {
        this.error = err.response?.data?.message || err.message
        throw err
      }
    },

    async updateProduct(product) {
      try {
        await productService.update(product.id, product)
        this.fetchProducts()
      } catch (err) {
        this.error = err.response?.data?.message || err.message
        throw err
      }
    },

    async deleteProduct(id) {
      try {
        await productService.remove(id)
        this.fetchProducts()
      } catch (err) {
        this.error = err.response?.data?.message || err.message
        throw err
      }
    }
  }
})
