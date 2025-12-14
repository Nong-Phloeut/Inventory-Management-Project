import http from './api'

export default {
  getReportsPurchases(params) {
    return http.get('/reports/purchases', { params: params })
  },
  getReportsInventory(params) {
    return http.get('/reports/inventory', { params: params })
  }
}
