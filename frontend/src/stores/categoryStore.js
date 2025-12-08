import { defineStore } from 'pinia'
import categoryService from '../api/category'

export const useCategoryStore = defineStore('category', {
  state: () => ({
    categories: [],
    loading: false,
    error: null
  }),

  actions: {
    async fetchCategories(filters) {
      this.loading = true
      this.error = null
      try {
        const res = await categoryService.getAll(filters)
        this.categories = res.data
      } catch (err) {
        this.error = err.response?.data?.message || err.message
      } finally {
        this.loading = false
      }
    },

    async addCategory(data) {
      try {
        const res = await categoryService.create(data)
        this.categories.push(res.data)
      } catch (err) {
        this.error = err.response?.data?.message || err.message
        throw err
      }
    },

    async updateCategory(id, data) {
      await categoryService.update(id, data)
      await this.fetchCategories()
      
    },

    async deleteCategory(id) {
      try {
        await categoryService.delete(id)
        this.categories = this.categories.filter(c => c.id !== id)
      } catch (err) {
        this.error = err.response?.data?.message || err.message
        throw err
      }
    }
  }
})
