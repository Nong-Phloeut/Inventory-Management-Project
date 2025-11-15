import http from './api'

const API_URL = '/sales' // adjust if your backend is different

export default {
  async getAll() {
    const res = await http.get(API_URL)
    return res.data
  },

  async getById(id) {
    const res = await http.get(`${API_URL}/${id}`)
    return res.data
  },

  async create(sale) {
    const res = await http.post(API_URL, sale)
    return res.data
  }
}
