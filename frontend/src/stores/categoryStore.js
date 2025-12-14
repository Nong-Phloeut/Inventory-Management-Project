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
      const res = await categoryService.getAll(filters)
      this.categories = res.data
    },

    async addCategory(data) {
      try {
        await categoryService.create(data)
        this.fetchCategories()
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
        await this.fetchCategories()
      } catch (err) {
        this.error = err.response?.data?.message || err.message
        throw err
      }
    }
  }
})
