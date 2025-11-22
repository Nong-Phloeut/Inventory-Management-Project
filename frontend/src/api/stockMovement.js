import http from './api'

export default {
  getByProductId(productId) {
    return http.get('/stock-movements', {
      params: { product_id: productId }
    })
  },
  create(data) {
    return http.post('/stock-movements', data)
  },
  update(id, data) {
    return http.put(`/stock-movements/${id}`, data)
  },
  delete(id) {
    return http.delete(`/stock-movements/${id}`)
  },
  returnStock(payload) {
    return http.post('/stock/return', payload)
  },

  adjustStock(payload) {
    return http.post('/stock/adjust', payload)
  },

  reportLoss(payload) {
    return http.post('/stock/loss', payload)
  }
}
