<template>
  <v-app-bar scroll-behavior="inverted">
    <v-app-bar-nav-icon @click="togglerDrawer">
      <v-icon>mdi-menu</v-icon>
    </v-app-bar-nav-icon>
    <strong class="font-weight-bold d-none d-lg-block d-print-block">
      Inventory Management
                 {{ authStore.user }}
    </strong>
    <template v-slot:append>
      <v-menu min-width="200px" rounded>
        <template v-slot:activator="{ props }">
          <v-btn icon v-bind="props" class="me-4">
            <v-avatar color="brown" size="large">
              <v-img :src="user.profile"></v-img>
            </v-avatar>
          </v-btn>
        </template>
        <v-card>
          <v-card-text>
            <div class="mx-auto text-center">
              <v-avatar color="brown">
                <span class="text-h5">{{ user.initials }}</span>
              </v-avatar>
              <h3>{{ user.fullName }}</h3>
              <p class="text-caption mt-1">
                {{ user.email }}
              </p>
   
              <v-divider class="my-3"></v-divider>
              <v-btn variant="text" rounded>Edit Account</v-btn>
              <v-divider class="my-3"></v-divider>
              <v-btn variant="text" rounded @click="handleLogout">Disconnect</v-btn>
            </div>
          </v-card-text>
        </v-card>
      </v-menu>
    </template>
  </v-app-bar>
</template>

<script>
  export default {
    emits: ['toggle'],
    data: () => ({
      user: {
        initials: 'JD',
        profile: 'https://cdn.vuetifyjs.com/images/john.jpg',
        fullName: 'John Doe',
        email: 'john.doe@doe.com'
      }
    }),
    methods: {
      togglerDrawer() {
        this.$emit('toggle')
      }
    }
  }
</script>

<script setup>
  import { useAuthStore } from '@/stores/auth'
  import { useRouter } from 'vue-router'

  const authStore = useAuthStore()
  const router = useRouter()

  const handleLogout = async () => {
    await authStore.logout()
    router.push({ name: 'Login' })
  }
</script>
