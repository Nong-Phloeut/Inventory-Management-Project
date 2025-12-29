import { defineStore } from 'pinia'
import salesApi from '../api/sales'

export const useSaleStore = defineStore('sales', {
  state: () => ({
    products: [],
  }),

  actions: {
   
  }
})
