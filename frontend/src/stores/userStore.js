import { defineStore } from 'pinia'
import userAPI from '@/api/user' // ✅ MUST be this file

export const useUserStore = defineStore('userStore', {
  state: () => ({
    users: {
      data: []
    },
    loading: false,
    error: null
  }),

  actions: {
    async fetchUsers(param) {
      this.loading = true
      try {
        const res = await userAPI.getAll(param)
        this.users.data = res
      } catch (err) {
        console.error('Fetch users failed:', err)
        this.error = err
      } finally {
        this.loading = false
      }
    },

    async addUser(user) {
      await userAPI.create(user)
    },

    async updateUser(user) {
      await userAPI.update(user.id, user)
    },

    async deleteUser(id) {
      await userAPI.remove(id)
    }
  }
})
