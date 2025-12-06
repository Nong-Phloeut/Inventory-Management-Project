import http from './api'

export default {
  getAll() {
    return http.get('/units')
  },
  create(data) {
    return http.post('/units', data)
  },
  update(id, data) {
    return http.put(`/units/${id}`, data)
  },
  delete(id) {
    return http.delete(`/units/${id}`)
  }
}
