<template>
  <v-container fluid class="pa-0">
    <!-- Title & Add Button -->
    <custom-title icon="mdi-account" class="mb-2">
      User Management
      <template #right>
        <BaseButtonFilter class="me-4" @click="toggleFilterForm" />
        <BaseButton icon="mdi-plus" @click="openAdd" small>Add User</BaseButton>
      </template>
    </custom-title>
    <v-card class="mb-4 pa-4 rounded-lg" elevation="0" v-show="showFilterForm">
      <v-row>
        <!-- Product Name / SKU -->
        <v-col cols="12" md="3">
          <v-text-field
            v-model="filters.keyword"
            label="Search (Username / Email)"
            prepend-inner-icon="mdi-magnify"
            hide-details
          />
        </v-col>

        <!-- Category -->
        <v-col cols="12" md="3">
          <v-select
            v-model="filters.roles"
            :items="rolesStore.roles.data"
            item-title="name"
            item-value="id"
            label="Roles"
            multiple
            hide-details
          >
            <template v-slot:selection="{ item, index }">
              <v-chip v-if="index < 2" :text="item.title" size="x-small" />

              <span
                v-if="index === 2"
                class="text-grey text-caption align-self-center"
              >
                (+{{ filters.roles.length - 2 }} others)
              </span>
            </template>
          </v-select>
        </v-col>
        <v-col cols="12" md="3">
          <v-select
            v-model="filters.status"
            :items="statusOptions"
            item-title="title"
            item-value="value"
            label="Status"
            hide-details
          />
        </v-col>

        <!-- Buttons -->
        <v-col cols="12" md="3" class="d-flex align-center">
          <v-btn class="me-3" variant="outlined" @click="resetFilter">
            Reset
          </v-btn>
          <v-btn
            color="primary"
            prepend-icon="mdi-filter-outline"
            @click="applyFilter"
          >
            Apply Filter
          </v-btn>
        </v-col>
      </v-row>
    </v-card>
    <!-- User Table -->
    <v-data-table
      :items="usersStore.users.data"
      :headers="headers"
      item-key="id"
      dense
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
  import { useRoleStore } from '@/stores/roleStore'

  const { t } = useI18n()
  const { confirm, notif } = useAppUtils()
  const usersStore = useUserStore()
  const rolesStore = useRoleStore()

  const dialog = ref(false)
  const selectedUser = ref({})
  const showFilterForm = ref(false)
  const filters = ref({
    keyword: '',
    roles: null,
    status: null
  })
  const statusOptions = [
    { title: 'Active', value: 1 },
    { title: 'Inactive', value: 0 }
  ]

  // Fetch users on mount
  onMounted(() => {
    usersStore.fetchUsers()
  })

  const ADMIN_ROLE_ID = 1

  function isAdmin(item) {
    return item.role_id === ADMIN_ROLE_ID
  }

  function canEdit(item) {
    return !isAdmin(item)
  }

  function canDelete(item) {
    return !isAdmin(item)
  }
  const toggleFilterForm = () => {
    showFilterForm.value = !showFilterForm.value
  }
  const applyFilter = () => {
    usersStore.fetchUsers({
      keyword: filters.value.keyword,
      roles: filters.value.roles?.join(',') ?? '',
      status: filters.value.status,
    })
  }
  const resetFilter = () => {
    filters.value = {
      keyword: '',
      roles: null,
      status: null
    }
    usersStore.fetchUsers()
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
