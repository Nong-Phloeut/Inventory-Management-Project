import http from './api'

export default {
  getAll() {
    return http.get('/roles')
  },
  create(data) {
    return http.post('/roles', data)
  },
  update(id, data) {
    return http.put(`/roles/${id}`, data)
  },
  delete(id) {
    return http.delete(`/roles/${id}`)
  }
}
