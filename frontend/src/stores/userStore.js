import { defineStore } from 'pinia'
import UserAPI from '@/api/user' // ✅ MUST be this file

export const useUserStore = defineStore('userStore', {
  state: () => ({
    users: {
      data: []
    },
    loading: false,
    error: null
  }),

  actions: {
    async fetchUsers() {
      this.loading = true
      try {
        const res = await UserAPI.getAll()

        // 🔥 IMPORTANT
        console.log('USERS FROM API:', res)

        this.users.data = res
      } catch (err) {
        console.error('Fetch users failed:', err)
        this.error = err
      } finally {
        this.loading = false
      }
    },

    async addUser(user) {
      await UserAPI.create(user)
    },

    async updateUser(user) {
      await UserAPI.update(user.id, user)
    },

    async deleteUser(id) {
      await UserAPI.remove(id)
    }
  }
})
