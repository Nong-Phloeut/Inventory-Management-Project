<template>
  <v-container fluid class="pa-0">
    <!-- Title & Add Button -->
    <custom-title icon="mdi-account" class="mb-2">
      User Management
      <template #right>
        <BaseButton icon="mdi-plus" @click="openAdd" small>Add User</BaseButton>
      </template>
    </custom-title>

    <!-- User Table -->
    <v-data-table
      :items="usersStore.users.data"
      :headers="headers"
      item-key="id"
      dense
      hide-default-footer
      class="compact-table"
    >
      <!-- Row numbering -->
      <template #item.no="{ index }">{{ index + 1 }}</template>

      <!-- Role chip -->
      <template #item.role="{ item }">
        <v-chip color="darkcyan" variant="tonal" size="small">
          <v-icon start>mdi-lock</v-icon>
          {{ item.role?.name || 'N/A' }}
        </v-chip>
      </template>

      <!-- Status chip -->
      <template #item.status="{ item }">
        <v-chip
          :color="item.status === 1 ? 'success' : 'error'"
          variant="tonal"
          size="small"
        >
          <v-icon start>
            {{ item.status === 1 ? 'mdi-check-circle' : 'mdi-close-circle' }}
          </v-icon>
          {{ item.status === 1 ? 'Active' : 'Inactive' }}
        </v-chip>
      </template>

      <!-- Actions as icons only -->
      <template #item.actions="{ item }">
        <!-- Edit -->
        <v-btn
          v-if="canEdit(item)"
          icon
          variant="text"
          color="primary"
          @click="openEdit(item)"
        >
          <v-icon>mdi-pencil</v-icon>
        </v-btn>

        <!-- Delete -->
        <v-btn
          v-if="canDelete(item)"
          icon
          variant="text"
          color="red"
          @click="onDeleteUser(item)"
        >
          <v-icon>mdi-delete</v-icon>
        </v-btn>

        <!-- Admin Badge -->
        <v-chip
          v-if="isAdmin(item)"
          color="darkcyan"
          variant="tonal"
          size="small"
        >
          <v-icon start>mdi-lock</v-icon>
          Admin
        </v-chip>
      </template>
    </v-data-table>

    <!-- User Dialog -->
    <UserDialog v-model="dialog" :edited-user="selectedUser" @save="saveUser" />
  </v-container>
</template>

<script setup>
  import { ref, onMounted } from 'vue'
  import { useUserStore } from '@/stores/userStore'
  import { useAppUtils } from '@/composables/useAppUtils'
  import { useI18n } from 'vue-i18n'
  import UserDialog from '@/components/users/UserDialog.vue'

  const dialog = ref(false)
  const selectedUser = ref({})
  const { t } = useI18n()
  const { confirm, notif } = useAppUtils()
  const usersStore = useUserStore()

  // Fetch users on mount
  onMounted(() => {
    usersStore.fetchUsers()
  })

  const ADMIN_ROLE_ID = 1

  function isAdmin(item) {
    return item.id === ADMIN_ROLE_ID
  }

  function canEdit(item) {
    return !isAdmin(item)
  }

  function canDelete(item) {
    return !isAdmin(item)
  }
  // Open Add User dialog
  function openAdd() {
    selectedUser.value = {}
    dialog.value = true
  }

  // Open Edit User dialog
  function openEdit(user) {
    selectedUser.value = { ...user }
    dialog.value = true
  }

  // Save User (Add or Update)
  async function saveUser(user) {
    try {
      if (user.id) {
        await usersStore.updateUser(user)
        notif(t('messages.updated_success'), {
          type: 'success',
          color: 'primary'
        })
      } else {
        await usersStore.addUser(user)
        notif(t('messages.saved_success'), {
          type: 'success',
          color: 'primary'
        })
      }
      await usersStore.fetchUsers()
      dialog.value = false
    } catch (error) {
      notif(error.response?.data?.message || t('messages.save_failed'), {
        type: 'error',
        color: 'error'
      })
    }
  }

  // Delete User
  function onDeleteUser(user) {
    confirm({
      title: 'Are you sure?',
      message: `Do you really want to delete user "${user.name}"?`,
      options: { type: 'error', color: 'error', width: 500 },
      agree: async () => {
        try {
          await usersStore.deleteUser(user.id)
          notif(t('messages.deleted_success'), {
            type: 'success',
            color: 'primary'
          })
          await usersStore.fetchUsers()
        } catch (err) {
          notif(err.response?.data?.message || t('messages.delete_failed'), {
            type: 'error',
            color: 'error'
          })
        }
      }
    })
  }

  // Table headers
  const headers = [
    { title: 'No.', key: 'no', sortable: false },
    { title: 'Full Name', key: 'name' },
    { title: 'Email', key: 'email' },
    { title: 'Username', key: 'username' },
    { title: 'Role', key: 'role' },
    { title: 'Status', key: 'status' },
    { title: 'Actions', key: 'actions', sortable: false }
  ]

  // Status color helper
</script>

<style scoped>
  /* .compact-table .v-data-table__wrapper {
    font-size: 12px;
  } */
</style>
