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
    hover
  >
    <template #item.actions="{ item }">
      <v-btn
        class="me-2"
        color="primary"
        icon="mdi-pencil"
        variant="text"
        @click="openEditDialog(item)"
      ></v-btn>
      <v-btn
        icon="mdi-delete"
        color="error"
        variant="text"
        @click="handleDelete(item.id)"
      ></v-btn>
    </template>
  </v-data-table>

  <!-- Add/Edit Dialog -->
  <CategoryDialog
    v-model="isDialogOpen"
    :category="selectedCategory"
    @save="handleSave"
  />
</template>

<script setup>
  import { ref, onMounted } from 'vue'
  import { useCategoryStore } from '@/stores/categoryStore'
  import CategoryDialog from '@/components/CategoryDialog.vue'
  import { useAppUtils } from '@/composables/useAppUtils'
  import { useI18n } from 'vue-i18n'

  const { confirm, notif } = useAppUtils()
  const categoryStore = useCategoryStore()
  const { t } = useI18n()
  const isDialogOpen = ref(false)
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

  const handleSave = async category => {
    if (category.id) {
      await categoryStore.updateCategory(category.id, category)
      notif(t('messages.updated_success'), {
        type: 'success',
        color: 'primary'
      })
    } else {
      await categoryStore.addCategory(category)
      notif(t('messages.saved_success'), {
        type: 'success',
        color: 'primary'
      })
    }
    isDialogOpen.value = false
  }

  const handleDelete = async id => {
    confirm({
      title: 'Are you sure?',
      message: 'Are you sure you want to delete this class?',
      options: {
        type: 'error',
        color: 'error',
        width: 400
      },
      agree: async () => {
        await categoryStore.deleteCategory(id)
        notif(t('messages.deleted_success'), {
          type: 'success',
          color: 'primary'
        })
      }
    })
  }
</script>
