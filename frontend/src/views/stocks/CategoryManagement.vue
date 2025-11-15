<template>
  <custom-title icon="mdi-shape-outline">
    Category Management
    <template #right>
      <BaseButton icon="mdi-plus" @click="openAddDialog">
        Add Category
      </BaseButton>
    </template>
  </custom-title>

  <v-data-table
    :items="categoryStore.categories"
    :loading="categoryStore.loading"
    :headers="headers"
  >
    <template #item.actions="{ item }">
      <v-btn
        size="small"
        class="me-2"
        color="primary"
        icon="mdi-pencil"
        variant="tonal"
        @click="openEditDialog(item)"
      ></v-btn>
      <v-btn
        size="small"
        icon="mdi-delete"
        color="error"
        variant="tonal"
        @click="openDeleteDialog(item)"
      ></v-btn>
    </template>
  </v-data-table>

  <!-- Add/Edit Dialog -->
  <CategoryDialog
    v-model="isDialogOpen"
    :category="selectedCategory"
    @save="handleSave"
  />

  <!-- Delete Confirmation -->
  <v-dialog v-model="isDeleteDialogOpen" max-width="400">
    <v-card>
      <v-card-title>Confirm Delete</v-card-title>
      <v-card-text>
        Are you sure you want to delete
        <strong>{{ selectedCategory?.name }}</strong>
        ?
      </v-card-text>
      <v-card-actions>
        <v-btn text @click="isDeleteDialogOpen = false">Cancel</v-btn>
        <v-btn color="error" @click="handleDelete">Delete</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
  import { ref, onMounted } from 'vue'
  import { useCategoryStore } from '@/stores/categoryStore'
  import CategoryDialog from '@/components/CategoryDialog.vue'

  const categoryStore = useCategoryStore()
  const isDialogOpen = ref(false)
  const isDeleteDialogOpen = ref(false)
  const selectedCategory = ref(null)

  const headers = [
    { title: 'Name', key: 'name' },
    { title: 'Description', key: 'description' },
    { title: 'Actions', key: 'actions', sortable: false }
  ]

  onMounted(() => {
    categoryStore.fetchCategories()
  })

  const openAddDialog = () => {
    selectedCategory.value = null
    isDialogOpen.value = true
  }

  const openEditDialog = category => {
    selectedCategory.value = { ...category }
    isDialogOpen.value = true
  }

  const openDeleteDialog = category => {
    selectedCategory.value = category
    isDeleteDialogOpen.value = true
  }

  const handleSave = async category => {
    if (category.id) {
      await categoryStore.updateCategory(category.id,category)
    } else {
      await categoryStore.addCategory(category)
    }
    isDialogOpen.value = false
  }

  const handleDelete = async () => {
    if (selectedCategory.value?.id) {
      await categoryStore.deleteCategory(selectedCategory.value.id)
      isDeleteDialogOpen.value = false
    }
  }
</script>
