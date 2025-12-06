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
      this.units.push(res.data)
    },
    async updateUnit(unit) {
      const res = await unitService.update(unit.id, unit)
      const idx = this.units.findIndex(s => s.id === unit.id)
      if (idx !== -1) this.units[idx] = res.data
    },
    async deleteUnit(id) {
      await unitService.delete(id)
      this.units = this.units.filter(s => s.id !== id)
    }
  }
})
