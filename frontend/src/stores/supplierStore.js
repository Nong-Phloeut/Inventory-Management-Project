import { defineStore } from 'pinia'
import { ref } from 'vue'
import {
  getSuppliers,
  getSupplier,
  createSupplier,
  updateSupplier as updateSupplierApi,
  deleteSupplier
} from '../api/supplier' // adjust path if needed

export const useSupplierStore = defineStore('supplier', () => {
  const suppliers = ref([])
  const loading = ref(false)

  // Fetch suppliers
  const fetchSuppliers = async () => {
    loading.value = true
    try {
      const { data } = await getSuppliers()
      suppliers.value = data
    } finally {
      loading.value = false
    }
  }

  // Get single supplier (optional helper)
  const fetchSupplier = async (id) => {
    const { data } = await getSupplier(id)
    return data
  }

  // Create
  const addSupplier = async (supplier) => {
    const { data } = await createSupplier(supplier)
    suppliers.value.push(data) // push new supplier returned from API
  }

  // Update
  const updateSupplier = async (supplier) => {
    const { data } = await updateSupplierApi(supplier.id, supplier)
    const idx = suppliers.value.findIndex((s) => s.id === supplier.id)
    if (idx !== -1) suppliers.value[idx] = data
  }

  // Delete
  const removeSupplier = async (id) => {
    await deleteSupplier(id)
    suppliers.value = suppliers.value.filter((s) => s.id !== id)
  }

  return {
    suppliers,
    loading,
    fetchSuppliers,
    fetchSupplier,
    addSupplier,
    updateSupplier,
    removeSupplier
  }
})
