import { defineStore } from 'pinia'
import stockService from '../api/stock'

export const useStockStore = defineStore('stock', {
  state: () => ({
    stocks: []
  }),
  actions: {
    async fetchStocks() {
      const res = await stockService.getAll()
      this.stocks = res.data
    },
    async addStock(data) {
      const res = await stockService.create(data)
      this.stocks.push(res.data)
    },
    async updateStock(stock) {
      const res = await stockService.update(stock.id, stock)
      const idx = this.stocks.findIndex(s => s.id === stock.id)
      if (idx !== -1) this.stocks[idx] = res.data
    },
    async deleteStock(id) {
      await stockService.delete(id)
      this.stocks = this.stocks.filter(s => s.id !== id)
    }
  }
})
