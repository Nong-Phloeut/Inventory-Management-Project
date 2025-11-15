import http from './api'

// Get all suppliers
export function getSuppliers() {
  return http.get('suppliers')
}

// Get a single supplier by ID
export function getSupplier(id) {
  return http.get(`suppliers/${id}`)
}

// Create a new supplier
export function createSupplier(supplier) {
  return http.post('suppliers', supplier)
}

// Update supplier by ID
export function updateSupplier(id, supplier) {
  return http.put(`suppliers/${id}`, supplier)
}

// Delete supplier by ID
export function deleteSupplier(id) {
  return http.delete(`suppliers/${id}`)
}
