
import aiApi  from './aiApi'

export default {
  // Get Low Stock AI recommendations
  getLowStockAI() {
    return aiApi.get('/ai/low-stock')
  },

  // Get Purchase AI insights
  purchaseRecommendation() {
    return aiApi.get('/purchase-recommendation')
  },

  // Get Stock Movement AI insights
  getStockMovementAI(params = {}) {
    return aiApi.get('/ai/stock-movement', { params })
  }
}
