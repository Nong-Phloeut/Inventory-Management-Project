import http from './api'

const API_URL = '/purchases'

export default {
  // Get all purchases
  async getAll(params) {
    const response = await http.get(API_URL, { params })
    return response.data
  },

  // Get a single purchase by ID
  async get(id) {
    const response = await http.get(`${API_URL}/${id}`)
    return response.data
  },

  // Create a new purchase
  async create(data) {
    const response = await http.post(API_URL, data)
    return response.data
  },

  // Update an existing purchase
  async update(id, data) {
    const response = await http.put(`${API_URL}/${id}`, data)
    return response.data
  },

  // Delete a purchase
  async delete(id) {
    const response = await http.delete(`${API_URL}/${id}`)
    return response.data
  }
}
