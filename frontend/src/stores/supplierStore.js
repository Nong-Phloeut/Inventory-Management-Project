import { defineStore } from 'pinia'
import { ref } from 'vue'
import {
  getSuppliers,
  getSupplier,
  createSupplier,
  updateSupplier as updateSupplierApi,
  deleteSupplier
} from '../api/supplier'

export const useSupplierStore = defineStore('supplier', {
  state: () => ({
    suppliers: []
  }),

  actions: {
    async fetchSuppliers(params) {
      const res = await getSuppliers(params)
      this.suppliers = res.data.data
    },
    async fetchSupplier(id) {
      const { data } = await getSupplier(id)
      return data
    },
    async addSupplier(supplier) {
      await createSupplier(supplier)
    },
    async updateSupplier(supplier) {
      await updateSupplierApi(supplier.id, supplier)
    },
    async removeSupplier(id) {
      await deleteSupplier(id)
    }
  }
})
