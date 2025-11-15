import http from './api'

export const productService = {
  async getAll() {
    const res = await http.get('/products')
    return res.data
  },
  async getById(id) {
    const res = await http.get(`/products/${id}`)
    return res.data
  },
  async create(product) {
    const res = await http.post('/products', product)
    return res.data
  },
  async update(id, product) {
    const res = await http.put(`/products/${id}`, product)
    return res.data
  },
  async remove(id) {
    await http.delete(`/products/${id}`)
  },
}
