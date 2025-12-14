<template>
  <v-container fluid class="pa-0">
    <!-- Title & Add Button -->
    <custom-title icon="mdi-account" class="mb-2">
      User Management
      <template #right>
        <BaseButton icon="mdi-plus" @click="openAdd" small> Add User </BaseButton>
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
        <v-chip
          :color="roleColor(item.role?.slug)"
          variant="tonal"
          density="compact"
          x-small
        >
          <v-icon start x-small v-if="item.role?.slug === 'admin'">mdi-lock</v-icon>
          {{ item.role?.name || 'N/A' }}
        </v-chip>
      </template>

      <!-- Status chip -->
      <template #item.status="{ item }">
        <v-chip
          :color="statusColor(item.status)"
          variant="tonal"
          density="compact"
          x-small
        >
          <v-icon start x-small>
            {{ item.status === 'Active' ? 'mdi-check-circle' : 'mdi-close-circle' }}
          </v-icon>
          {{ item.status || 'Non' }}
        </v-chip>
      </template>

      <!-- Actions as icons only -->
      <template #item.actions="{ item }">
        <v-icon
          x-small
          color="primary"
          class="mr-1"
          v-if="item.role?.slug !== 'admin'"
          @click="openEdit(item)"
        >mdi-pencil</v-icon>

        <v-icon
          x-small
          color="red"
          v-if="item.role?.slug !== 'admin'"
          @click="onDeleteUser(item)"
        >mdi-delete</v-icon>

        <v-chip
          v-if="item.role?.slug === 'admin'"
          color="grey"
          variant="tonal"
          density="compact"
          x-small
        >
          <v-icon start x-small>mdi-lock</v-icon> Admin
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
      notif(t('messages.updated_success'), { type: 'success', color: 'primary' })
    } else {
      await usersStore.addUser(user)
      notif(t('messages.saved_success'), { type: 'success', color: 'primary' })
    }
    await usersStore.fetchUsers()
    dialog.value = false
  } catch (error) {
    notif(error.response?.data?.message || t('messages.save_failed'), { type: 'error', color: 'error' })
  }
}

// Delete User
function onDeleteUser(user) {
  confirm({
    title: 'Are you sure?',
    message: `Do you really want to delete user "${user.full_name}"?`,
    options: { type: 'error', color: 'error', width: 400 },
    agree: async () => {
      try {
        await usersStore.deleteUser(user.id)
        notif(t('messages.deleted_success'), { type: 'success', color: 'primary' })
        await usersStore.fetchUsers()
      } catch (err) {
        notif(err.response?.data?.message || t('messages.delete_failed'), { type: 'error', color: 'error' })
      }
    }
  })
}

// Table headers
const headers = [
  { title: 'No.', key: 'no', sortable: false },
  { title: 'Full Name', key: 'full_name' },
  { title: 'Email', key: 'email' },
  { title: 'Username', key: 'username' },
  { title: 'Role', key: 'role' },
  { title: 'Status', key: 'status' },
  { title: 'Actions', key: 'actions', sortable: false }
]

// Role color helper
function roleColor(slug) {
  switch (slug) {
    case 'admin': return 'blue'
    case 'manager': return 'yellow'
    case 'purchaser': return 'green'
    default: return 'grey'
  }
}

// Status color helper
function statusColor(status) {
  switch (status) {
    case 'Active': return 'success'
    case 'Inactive': return 'error'
    default: return 'grey'
  }
}
</script>

<style scoped>
.compact-table .v-data-table__wrapper {
  font-size: 12px;
}
.v-data-table .v-icon {
  cursor: pointer;
}
.v-data-table .v-chip {
  font-size: 11px;
  height: 20px;
}
</style>
