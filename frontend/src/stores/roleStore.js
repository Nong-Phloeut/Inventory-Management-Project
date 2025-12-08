import { defineStore } from 'pinia'
import roleService from '../api/role' // Make sure you have this API service

export const useRoleStore = defineStore('role', {
  state: () => ({
    roles: []
  }),
  actions: {
    async fetchRoles() {
      const res = await roleService.getAll()
      this.roles = res.data
    },
    async addRole(data) {
      const res = await roleService.create(data)
      return res
    },
    async updateRole(role) {
      const res = await roleService.update(role.id, role)
      return res
    },
    async deleteRole(id) {
      await roleService.delete(id)
    }
  }
})
