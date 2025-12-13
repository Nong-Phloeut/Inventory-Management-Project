import api from './api'

export default {
  getStats() {
    return api.get('/dashboard')
  },
  getMonthlyPurchases(year) {
    return api.get('/monthly-purchases', {
      params: { year }
    })
  }
}
