<template>
  <custom-title icon="mdi-truck-delivery">
    Supplier Management
    <template #right>
      <BaseButtonFilter class="me-4" @click="toggleFilterForm" />
      <BaseButton icon="mdi-plus" @click="openAddDialog">
        Add Supplier
      </BaseButton>
    </template>
  </custom-title>
  <v-card class="mb-4 pa-4 rounded-lg" elevation="0" v-show="showFilterForm">
    <v-row>
      <!-- Search -->
      <v-col cols="12" md="3">
        <v-text-field
          v-model="filters.keyword"
          label="Search (Name, Contact, Phone, Email)"
          prepend-inner-icon="mdi-magnify"
          hide-details
        />
      </v-col>

      <!-- Status -->
      <v-col cols="12" md="3">
        <v-select
          v-model="filters.status"
          :items="statusOptions"
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

  <v-data-table-server
    v-model:items-per-page="itemsPerPage"
    :items-length="supplierStore.suppliers.total || 0"
    :items="supplierStore.suppliers.data"
    :loading="supplierStore.loading"
    @update:options="loadItems"
    :headers="headers"
  >
    <template #item.actions="{ item }">
      <v-btn
        icon="mdi-pencil"
        color="primary"
        class="me-2"
        variant="text"
        @click="openEditDialog(item)"
      />
      <v-btn
        icon="mdi-delete"
        color="error"
        variant="text"
        @click="handleDelete(item.id)"
      />
    </template>
    <template #item.status="{ item }">
      <v-chip size="small" :color="item.status == '1' ? 'green' : 'red'">
        <v-icon
          :icon="item.status == '1' ? 'mdi-check-circle' : 'mdi-cancel'"
          start
        ></v-icon>
        {{ item.status == '1' ? 'Active' : 'Inactiv' }}
      </v-chip>
    </template>
  </v-data-table-server>

  <!-- Add / Edit Dialog -->
  <SupplierDialog
    v-model="isDialogOpen"
    :supplier="selectedSupplier"
    @save="handleSave"
  />
</template>

<script setup>
  import { ref, onMounted } from 'vue'
  import { useSupplierStore } from '@/stores/supplierStore'
  import SupplierDialog from '@/components/SupplierDialog.vue'
  import { useAppUtils } from '@/composables/useAppUtils'
  import { useI18n } from 'vue-i18n'
  const { t } = useI18n()

  const { confirm, notif } = useAppUtils()
  const supplierStore = useSupplierStore()

  // State
  const isDialogOpen = ref(false)
  const selectedSupplier = ref(null)
  const itemsPerPage = ref(10)
  const showFilterForm = ref(false)
  // Table headers
  const headers = [
    { title: 'Name', key: 'name' },
    { title: 'Contact', key: 'contact_name' },
    { title: 'Phone', key: 'phone' },
    { title: 'Email', key: 'email' },
    { title: 'Status', key: 'status' },
    { title: 'Actions', key: 'actions', sortable: false }
  ]

  // Load suppliers on mount
  onMounted(() => {
    supplierStore.fetchSuppliers()
  })
  const filters = ref({
    keyword: '',
    status: ''
  })

  const statusOptions = [
    { title: 'Active', value: 1 },
    { title: 'Inactive', value: 0 }
  ]

  const applyFilter = () => {
    supplierStore.fetchSuppliers(filters.value)
  }

  const resetFilter = () => {
    filters.value = {
      keyword: '',
      status: ''
    }

    supplierStore.fetchSuppliers()
  }
  const toggleFilterForm = () => {
    showFilterForm.value = !showFilterForm.value
  }
  // Open add dialog
  const openAddDialog = () => {
    selectedSupplier.value = null
    isDialogOpen.value = true
  }

  // Open edit dialog
  const openEditDialog = supplier => {
    selectedSupplier.value = { ...supplier }
    isDialogOpen.value = true
  }
  const loadItems = ({ page, itemsPerPage }) => {
    supplierStore.fetchSuppliers({
      page,
      per_page: itemsPerPage
    })
  }
  // Save (add or update)
  const handleSave = async supplier => {
    if (supplier.id) {
      await supplierStore.updateSupplier(supplier)
      notif(t('messages.updated_success'), {
        type: 'success',
        color: 'primary'
      })
    } else {
      await supplierStore.addSupplier(supplier)
      notif(t('messages.saved_success'), {
        type: 'success',
        color: 'primary'
      })
    }
    isDialogOpen.value = false
  }

  // Delete
  const handleDelete = async id => {
    confirm({
      title: 'Are you sure?',
      message: 'Are you sure you want to delete this supplier?',
      options: {
        type: 'error',
        color: 'error',
        width: 400
      },
      agree: async () => {
        await supplierStore.removeSupplier(id)
        notif(t('messages.deleted_success'), {
          type: 'success',
          color: 'primary'
        })
      }
    })
  }
</script>
