import { defineStore } from 'pinia'
import unitService from '../api/unit'

export const useUnitStore = defineStore('unit', {
  state: () => ({
    units: []
  }),
  actions: {
    async fetchUnits() {
      const res = await unitService.getAll()
      this.units = res.data
    },
    async addUnit(data) {
      const res = await unitService.create(data)
      return res
    },
    async updateUnit(unit) {
      const res = await unitService.update(unit.id, unit)
      return res
    },
    async deleteUnit(id) {
      await unitService.delete(id)
    }
  }
})
