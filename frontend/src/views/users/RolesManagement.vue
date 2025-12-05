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
        v-for="(role, index) in roles"
        :key="index"
        cols="12"
        sm="6"
        md="6"
      >
        <v-card class="pa-0 rounded-lg" elevation="0">
          <v-card-text>
            <div class="d-flex justify-space-between align-center">
              <h3 class="font-weight-medium">{{ role.title }}</h3>
              <v-chip size="small" color="primary" class="font-weight-medium">
                {{ role.managers }} Manager
              </v-chip>
            </div>

            <p class="text-grey-darken-1 mt-2">
              Scope:
              <strong>{{ role.scope }}</strong>
            </p>

            <p class="mt-2 mb-4">
              {{ role.description }}
            </p>
          </v-card-text>

          <v-divider class="my-2"></v-divider>
          <v-card-actions class="py-0">
            <v-spacer></v-spacer>
            <v-btn variant="outlined" size="small" elevation="0">
              View List
            </v-btn>

            <v-btn color="primary" variant="outlined" size="small">Edit</v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
    <RoleDialog v-model="dialog" :editedRole="selectedRole" @save="saveRole" />
  </v-container>
</template>

<script setup>
  import { ref, computed, watch } from 'vue'
  import RoleDialog from '@/components/users/RoleDialog.vue'

  const dialog = ref(false)
  const selectedRole = ref({})

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
  function saveRole(role) {
    if (role.id) {
      console.log('Updating role:', role)
    } else {
      console.log('Creating new role:', role)
    }
  }

  const roles = [
    {
      title: 'Organization Admin',
      scope: 'Organization',
      description:
        'Full access to manage members, billing, and organization-wide settings.',
      managers: 3
    },
    {
      title: 'IT Developer Admin',
      scope: 'Organization',
      description:
        'Manages system configurations, integrations, and IT-related settings.',
      managers: 2
    }
  ]
</script>

<style scoped>
  .border-dashed {
    border-style: dashed !important;
  }
</style>
