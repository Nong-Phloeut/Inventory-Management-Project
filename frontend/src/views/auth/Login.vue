<template>
  <v-container fluid class="pa-0 fill-height">
    <v-row no-gutters class="fill-height">
      <!-- Left: Image -->
      <v-col cols="12" md="6" class="d-flex align-center justify-center">
        <v-img
          src="https://www.kindpng.com/picc/m/567-5674919_inventory-management-inventory-management-system-png-transparent-png.png"
          alt="Login Illustration"
          cover
          class="h-100 w-100"
        />
      </v-col>

      <!-- Right: Form -->
      <v-col
        cols="12"
        md="6"
        class="d-flex align-center justify-center pa-8"
      >
        <v-card max-width="400" class="w-100 pa-6" elevation="6" rounded="xl">
          <div class="text-center mb-6">
            <v-icon size="48" color="primary">mdi-account-circle</v-icon>
            <h2 class="mt-2">Welcome Back {test}</h2>
            <p class="text-subtitle-2">Please login to continue</p>
          </div>

          <v-form @submit.prevent="login">
            <v-text-field
              v-model="email"
              label="Email"
              prepend-inner-icon="mdi-email-outline"
              type="email"
              required
            />

            <v-text-field
              v-model="password"
              label="Password"
              prepend-inner-icon="mdi-lock-outline"
              type="password"
              required
            />

            <v-btn
              type="submit"
              color="primary"
              block
              class="mt-4"
              size="large"
            >
              Login
            </v-btn>

            <v-btn
              variant="text"
              color="secondary"
              block
              class="mt-2"
              size="small"
            >
              Forgot password?
            </v-btn>
          </v-form>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref } from "vue"

const email = ref("")
const password = ref("")

function login() {
  console.log("Email:", email.value)
  console.log("Password:", password.value)
  // 👉 Replace with API call
}
</script>

<style scoped>
.fill-height {
  height: 100vh;
}
</style>
