<template>
  <custom-title icon="mdi-truck-delivery">
    Supplier Management
    <template #right>
      <BaseButton icon="mdi-plus" @click="openAddDialog">
        Add Supplier
      </BaseButton>
    </template>
  </custom-title>

  <v-data-table
    :items="supplierStore.suppliers"
    :loading="supplierStore.loading"
    :headers="headers"
  >
    <template #item.actions="{ item }">
      <v-btn
        icon="mdi-pencil"
        size="small"
        color="primary"
        class="me-2"
        variant="tonal"
        @click="openEditDialog(item)"
      />
      <v-btn
        icon="mdi-delete"
        size="small"
        color="error"
        variant="tonal"
        @click="openDeleteDialog(item)"
      />
    </template>
  </v-data-table>

  <!-- Add / Edit Dialog -->
  <SupplierDialog
    v-model="isDialogOpen"
    :supplier="selectedSupplier"
    @save="handleSave"
  />

  <!-- Delete Confirmation Dialog -->
  <v-dialog v-model="isDeleteDialogOpen" max-width="400">
    <v-card>
      <v-card-title>Confirm Delete</v-card-title>
      <v-card-text>
        Are you sure you want to delete
        <strong>{{ selectedSupplier?.name }}</strong>
        ?
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn text @click="isDeleteDialogOpen = false">Cancel</v-btn>
        <v-btn color="error" @click="handleDelete(selectedSupplier?.id)">
          Delete
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
  import { ref, onMounted } from 'vue'
  import { useSupplierStore } from '@/stores/supplierStore'
  import SupplierDialog from '@/components/SupplierDialog.vue'

  const supplierStore = useSupplierStore()

  // State
  const isDialogOpen = ref(false)
  const isDeleteDialogOpen = ref(false)
  const selectedSupplier = ref(null)

  // Table headers
  const headers = [
    { title: 'Name', key: 'name' },
    { title: 'Contact', key: 'contact_name' },
    { title: 'Phone', key: 'phone' },
    { title: 'Email', key: 'email' },
    { title: 'Actions', key: 'actions', sortable: false }
  ]

  // Load suppliers on mount
  onMounted(() => {
    supplierStore.fetchSuppliers()
  })

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

  // Open delete dialog
  const openDeleteDialog = supplier => {
    selectedSupplier.value = supplier
    isDeleteDialogOpen.value = true
  }

  // Save (add or update)
  const handleSave = async supplier => {
    if (supplier.id) {
      await supplierStore.updateSupplier(supplier)
    } else {
      await supplierStore.addSupplier(supplier)
    }
    isDialogOpen.value = false
  }

  // Delete
  const handleDelete = async id => {
    await supplierStore.removeSupplier(id) // 🔹 use removeSupplier (store)
    isDeleteDialogOpen.value = false
  }
</script>
