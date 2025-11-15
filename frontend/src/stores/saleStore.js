import { defineStore } from 'pinia'
import salesApi from '../api/sales'

export const useSaleStore = defineStore('sales', {
  state: () => ({
    sales: [],
    selectedSale: null,
    loading: false,
    error: null
  }),

  actions: {
    async fetchSales() {
      this.loading = true
      try {
        this.sales = await salesApi.getAll()
      } catch (err) {
        this.error = err.response?.data?.message || err.message
      } finally {
        this.loading = false
      }
    },

    async fetchSale(id) {
      this.loading = true
      try {
        this.selectedSale = await salesApi.getById(id)
      } catch (err) {
        this.error = err.response?.data?.message || err.message
      } finally {
        this.loading = false
      }
    },

    async createSale(sale) {
      this.loading = true
      try {
        const newSale = await salesApi.create(sale)
        this.sales.push(newSale) // update local state
        return newSale
      } catch (err) {
        this.error = err.response?.data?.message || err.message
        throw err
      } finally {
        this.loading = false
      }
    }
  }
})
