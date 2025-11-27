<template>
  <v-app-bar scroll-behavior="inverted">
    <v-app-bar-nav-icon @click="togglerDrawer">
      <v-icon>mdi-menu</v-icon>
    </v-app-bar-nav-icon>
    <strong class="font-weight-bold d-none d-lg-block d-print-block">
      Inventory Management
    </strong>
    <template v-slot:append>
      <v-menu min-width="200px" rounded>
        <template v-slot:activator="{ props }">
          <v-btn icon v-bind="props" class="me-4">
            <v-avatar color="brown" size="large">
              <!-- <v-img :src="user.profile"></v-img> -->
              <span class="text-h6">{{ initials }}</span>
            </v-avatar>
          </v-btn>
        </template>
        <v-card>
          <v-card-text>
            <div class="mx-auto text-center">
              <!-- <v-avatar color="brown">
                <span class="text-h5">{{ initials }}</span>
              </v-avatar> -->
              <h3>{{ me.name }}</h3>
              <p class="text-caption mt-1">
                {{ me.email }}
              </p>
              <!-- {{ me }} -->
              <!-- <v-divider class="my-3"></v-divider> -->
              <!-- <v-btn variant="text" rounded>Edit Account</v-btn> -->
              <v-divider class="my-3"></v-divider>
              <v-btn variant="text" rounded="lg" @click="handleLogout">
                Disconnect
              </v-btn>
            </div>
          </v-card-text>
        </v-card>
      </v-menu>
    </template>
  </v-app-bar>
</template>


<script setup>
  import { ref, onMounted, computed } from 'vue'
  import { useAuthStore } from '@/stores/auth'
  import { useRouter } from 'vue-router'

  const authStore = useAuthStore()
  const router = useRouter()
  const me = ref({})

  // Drawer toggle event
  const togglerDrawer = () => {
    // emits event to parent
    emit('toggle')
  }

  const initials = computed(() => {
    if (!me.value.name) return ''
    const names = me.value.name.split(' ')
    return names.length >= 2
      ? names[0][0].toUpperCase() + names[1][0].toUpperCase()
      : names[0][0].toUpperCase()
  })

  onMounted(async () => {
    try {
      await authStore.me() // Make sure fetchUser() exists in your store
      me.value = authStore.me
    } catch (err) {
      await authStore.logout()
      router.push({ name: 'Login' })
    }
  })
  const handleLogout = async () => {
    await authStore.logout()
    router.push({ name: 'Login' })
  }
</script>
