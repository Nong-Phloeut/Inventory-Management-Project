import http from './api'

export default {
  getAll(params) {
    return http.get('/stocks', { ...params })
  },
  create(data) {
    return http.post('/stocks', data)
  },
  update(id, data) {
    return http.put(`/stocks/${id}`, data)
  },
  delete(id) {
    return http.delete(`/stocks/${id}`)
  }
}
