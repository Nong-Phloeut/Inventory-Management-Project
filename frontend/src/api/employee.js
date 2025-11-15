import http from './api'

const API_URL = '/employees' // adjust to your backend

export default {
  async getAll() {
    const res = await http.get(API_URL)
    return res.data
  },

  async getById(id) {
    const res = await http.get(`${API_URL}/${id}`)
    return res.data
  },

  async create(employee) {
    const res = await http.post(API_URL, employee)
    return res.data
  },

  async update(id, employee) {
    const res = await http.put(`${API_URL}/${id}`, employee)
    return res.data
  },

  async remove(id) {
    await http.delete(`${API_URL}/${id}`)
    return true
  }
}
