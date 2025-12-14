import { defineStore } from 'pinia'
import reportService from '../api/report'

export const useReportStore = defineStore('report', {
  state: () => ({
    purchaseReport: [],
    inventoryReport: [],
    tableReport: [],
    kpisReport: {},
  }),
  actions: {
    async fetchReportsPurchases(params) {
      const res = await reportService.getReportsPurchases(params);
      this.purchaseReport = res.data
      this.tableReport = res.data.table
      this.kpisReport = res.data.kpis
    },
    async fetchReportsInventory(params) {
      const res = await reportService.getReportsInventory(params);
      console.log(res.data);
      this.inventoryReport = res.data
    }
  }
})
