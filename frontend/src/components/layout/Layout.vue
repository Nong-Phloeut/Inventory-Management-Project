<template>
  <sidebar :rail="rail" :user="user" @update:rail="rail = $event" />
  <app-bar :user="user" @toggle="toggleRail" />
  <v-main>
    <v-container class="px-4" fluid>
      <router-view />
    </v-container>
  </v-main>
</template>

<script setup>
  import { ref, onMounted } from 'vue'
  import Sidebar from './Sidebar.vue'
  import AppBar from './AppBar.vue'
  import { useAuthStore } from '@/stores/auth'
  import { useRouter } from 'vue-router'

  const rail = ref(false)
  const user = ref(null)

  const authStore = useAuthStore()
  const router = useRouter()
  
  // Fetch logged-in user
  onMounted(async () => {
    try {
      await authStore.fetchMe()
      user.value = authStore.me
    } catch (error) {
      await authStore.logout()
      router.push({ name: 'Login' })
    }
  })

  // Toggle rail for sidebar
  function toggleRail() {
    rail.value = !rail.value
  }
</script>
