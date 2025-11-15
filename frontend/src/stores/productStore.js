import { defineStore } from 'pinia'
import { productService } from '../api/product'

export const useProductStore = defineStore('product', {
  state: () => ({
    products: [],
    loading: false,
    error: null
  }),

  actions: {
    async fetchProducts() {
      this.loading = true
      try {
        this.products = await productService.getAll()
      } catch (err) {
        this.error = err.response?.data?.message || err.message
      } finally {
        this.loading = false
      }
    },

    async addProduct(product) {
      try {
        const newProduct = await productService.create(product)
        this.products.push(newProduct)
      } catch (err) {
        this.error = err.response?.data?.message || err.message
        throw err
      }
    },

    async updateProduct(product) {
      try {
        const updated = await productService.update(product.id, product)
        const idx = this.products.findIndex(p => p.id === product.id)
        if (idx !== -1) this.products[idx] = updated
      } catch (err) {
        this.error = err.response?.data?.message || err.message
        throw err
      }
    },

    async deleteProduct(id) {
      try {
        await productService.remove(id)
        this.products = this.products.filter(p => p.id !== id)
      } catch (err) {
        this.error = err.response?.data?.message || err.message
        throw err
      }
    }
  }
})
