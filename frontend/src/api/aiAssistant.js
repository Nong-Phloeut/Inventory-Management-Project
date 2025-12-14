import http from './api' // your axios instance

export default {
  // Get Low Stock AI recommendations
  getLowStockAI() {
    return http.get('/ai/low-stock')
  },

  // Get Purchase AI insights
  getPurchaseAI(params = {}) {
    return http.get('/ai/purchases', { params })
  },

  // Get Stock Movement AI insights
  getStockMovementAI(params = {}) {
    return http.get('/ai/stock-movement', { params })
  }
}
