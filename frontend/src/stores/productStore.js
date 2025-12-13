import { defineStore } from 'pinia'
import { productService } from '../api/product'

export const useProductStore = defineStore('product', {
  state: () => ({
    products: []
  }),

  actions: {
    async fetchProducts(filterParams = {}) {
      const res = await productService.getAll(filterParams)
      this.products = res.data
    },
    async addProduct(product) {
      await productService.create(product)
    },

    async updateProduct(product) {
      await productService.update(product.id, product)
    },

    async deleteProduct(id) {
      await productService.remove(id)
    }
  }
})
