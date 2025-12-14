<template>
  <v-container fluid class="pa-0">
    <custom-title icon="mdi-account-group">
      Role Management
      <template #right>
        <BaseButton icon="mdi-plus" @click="openAdd">Add Role</BaseButton>
      </template>
    </custom-title>
    <v-row dense>
      <v-col
        v-for="(role, index) in rolesStore.roles.data"
        :key="index"
        cols="12"
        sm="6"
        md="6"
      >
        <v-card class="pa-4 rounded-lg" elevation="0">
          <div class="d-flex align-center mb-2">
            <h3 class="text-primary">
              {{ role.name }}
            </h3>

            <v-spacer></v-spacer>

            <v-chip
              :color="role.status === 1 ? 'success' : 'error'"
              variant="tonal"
              density="compact"
              class="font-weight-medium"
            >
              <v-icon start>
                {{
                  role.status === 1 ? 'mdi-check-circle' : 'mdi-close-circle'
                }}
              </v-icon>
              {{ role.status === 1 ? 'Active' : 'Inactive' }}
            </v-chip>
          </div>

          <p class="text-grey-darken-1 mb-4">
            {{ role.description || 'No description provided.' }}
          </p>

          
          <v-divider class="my-0"></v-divider>
          <v-card-actions class="px-0 py-0">
      
            <v-spacer></v-spacer>

            <v-btn
              color="error"
              variant="outlined"
              size="small"
              @click="onDeleteRole(role.id)"
            >
              <v-icon start>mdi-delete</v-icon>
              Delete
            </v-btn>

            <v-btn
              color="primary"
              variant="flat"
              size="small"
              @click="openEdit(role)"
            >
              <v-icon start>mdi-pencil</v-icon>
              Edit
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>
    <RoleDialog v-model="dialog" :editedRole="selectedRole" @save="saveRole" />
  </v-container>
</template>

<script setup>
  import { ref, onMounted } from 'vue'
  import { useRoleStore } from '@/stores/roleStore'
  import { useAppUtils } from '@/composables/useAppUtils'
  import { useI18n } from 'vue-i18n'
  import RoleDialog from '@/components/users/RoleDialog.vue'

  const dialog = ref(false)
  const selectedRole = ref({})
  const { t } = useI18n()

  const { confirm, notif } = useAppUtils()
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
  function onDeleteRole(role) {
    confirm({
      title: 'Are you sure?',
      message: 'Are you sure you want to delete this role?',
      options: {
        type: 'error',
        color: 'error',
        width: 400
      },
      agree: async () => {
        await rolesStore.deleteRole(role)
        notif(t('messages.deleted_success'), {
          type: 'success',
          color: 'primary'
        })
      }
    })
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
