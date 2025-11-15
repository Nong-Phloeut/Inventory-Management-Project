import { defineStore } from 'pinia'

import { userLogin } from '@/api/auth.js' // get form auth 

export const useAuthStore = defineStore('auth', {
  state: () => ({
    authUser: []
  }),
  actions: { //how to use it see in file Login.vue 
    async login({ email, password }) {
      const response = await userLogin(email, password)
      this.authUser = response.data // example
      return response
    }
  }
})
