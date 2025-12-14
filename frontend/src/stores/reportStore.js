import { defineStore } from 'pinia'
import reportService from '../api/report'

export const useReportStore = defineStore('report', {
  state: () => ({
    purchaseReport: [],
    tableReport: [],
    kpisReport: {},
  }),
  actions: {
    async fetchReportsPurchases(params) {
      const res = await reportService.getReportsPurchases(params);
      this.purchaseReport = res.data
      this.tableReport = res.data.table
      this.kpisReport = res.data.kpis
      this.kpisReport = res.data.byCategory
    }
  }
})
