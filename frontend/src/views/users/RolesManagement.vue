<template>
  <v-container fluid class="pa-0">
    <custom-title icon="mdi-account-group">
      Role Management
      <template #right>
        <BaseButton icon="mdi-plus" @click="openAdd">
          Add Role
        </BaseButton>
      </template>
    </custom-title>

    <v-row dense>
      <v-col
        v-for="(role, index) in rolesStore.roles"
        :key="index"
        cols="12"
        sm="6"
        md="6"
      >
        <v-card class="pa-0 rounded-lg" elevation="0">
          <v-card-text>
            <div class="d-flex justify-space-between align-center">
              <h3 class="font-weight-medium">{{ role.name }}</h3>
              <!-- <v-chip size="small" color="primary" class="font-weight-medium">
                {{ role.managers || 0 }} Manager
              </v-chip> -->
            </div>

            <p class="text-grey-darken-1 mt-2">
              Status:
              <strong>{{ role.status || '-' }}</strong>
            </p>

            <p class="mt-2 mb-4">
              {{ role.description || '-' }}
            </p>
          </v-card-text>

          <v-divider class="my-2"></v-divider>
          <v-card-actions class="py-0">
            <v-spacer></v-spacer>
            <v-btn variant="outlined" size="small" elevation="0">
              View List
            </v-btn>

            <v-btn
              color="primary"
              variant="outlined"
              size="small"
              @click="openEdit(role)"
            >
              Edit
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

    <RoleDialog
      v-model="dialog"
      :editedRole="selectedRole"
      @save="saveRole"
    />
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoleStore } from '@/stores/roleStore'
import RoleDialog from '@/components/users/RoleDialog.vue'

const dialog = ref(false)
const selectedRole = ref({})

// Pinia store
const rolesStore = useRoleStore()

// Fetch roles on mount
onMounted(() => {
  rolesStore.fetchRoles()
})

// Open add dialog
function openAdd() {
  selectedRole.value = {}
  dialog.value = true
}

// Open update dialog
function openEdit(role) {
  selectedRole.value = { ...role }
  dialog.value = true
}

// Handle submit
async function saveRole(role) {
  if (role.id) {
    await rolesStore.updateRole(role)
  } else {
    await rolesStore.addRole(role)
  }
  await rolesStore.fetchRoles()
  dialog.value = false
}
</script>

<style scoped>
.border-dashed {
  border-style: dashed !important;
}
</style>
