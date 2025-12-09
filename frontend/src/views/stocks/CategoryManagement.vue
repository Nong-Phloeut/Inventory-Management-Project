<template>
  <custom-title icon="mdi-shape-outline">
    Category Management
    <template #right>
      <BaseButtonFilter class="me-4" @click="toggleFilterForm"/>
      <BaseButton icon="mdi-plus" @click="openAddDialog">
        Add Category
      </BaseButton>
    </template>
  </custom-title>
  <v-card class="mb-4 pa-4 rounded-lg" elevation="0" v-show="showFilterForm">
    <v-row>
      <v-col cols="12" md="3">
        <v-text-field
          v-model="formFilters.keyword"
          label="Search keyword"
          prepend-inner-icon="mdi-magnify"
          hide-details
        />
      </v-col>
      <v-col cols="12" md="3" class="d-flex align-center">
        <v-btn
          color="primary"
          prepend-icon="mdi-filter-outline"
          @click="applyFilter"
        >
          Apply Filter
        </v-btn>
        <v-btn
          class="ms-3"
          variant="outlined"
          @click="resetFilter"
          :disabled="!formFilters.keyword"
        >
          Reset
        </v-btn>
      </v-col>
    </v-row>
  </v-card>

  <v-data-table-server
    v-model:items-per-page="itemsPerPage"
    :items="categoryStore.categories.data"
    :loading="categoryStore.loading"
    :headers="headers"
    :items-length="categoryStore.categories.total || 0"
    @update:options="loadItems"
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
  </v-data-table-server>

  <!-- Add/Edit Dialog -->
  <CategoryDialog
    v-model="isDialogOpen"
    :category="selectedCategory"
    @save="handleSave"
  />
</template>

<script setup>
  import { ref, onMounted, reactive } from 'vue'
  import { useCategoryStore } from '@/stores/categoryStore'
  import CategoryDialog from '@/components/CategoryDialog.vue'
  import { useAppUtils } from '@/composables/useAppUtils'
  import { useI18n } from 'vue-i18n'

  const { confirm, notif } = useAppUtils()
  const categoryStore = useCategoryStore()
  const { t } = useI18n()
  const isDialogOpen = ref(false)
  const selectedCategory = ref(null)
  const formFilters = reactive({
    keyword: null
  })
  const itemsPerPage = ref(10)

  const headers = [
    { title: 'Name', key: 'name' },
    { title: 'Description', key: 'description' },
    { title: 'Actions', key: 'actions', sortable: false }
  ]
  const showFilterForm = ref(false)
  const showExportForm = ref(false)

  onMounted(() => {
    categoryStore.fetchCategories()
  })

  const toggleFilterForm = () => {
    showFilterForm.value = !showFilterForm.value
    showExportForm.value = false
  }
  const openAddDialog = () => {
    selectedCategory.value = null
    isDialogOpen.value = true
  }

  const openEditDialog = category => {
    selectedCategory.value = { ...category }
    isDialogOpen.value = true
  }
  const loadItems = ({ page, itemsPerPage }) => {
    categoryStore.fetchCategories({
      page,
      per_page: itemsPerPage
    })
  }
  const applyFilter = () => {
    categoryStore.fetchCategories({
      keyword: formFilters.keyword
    })
  }
  const resetFilter = () => {
    formFilters.keyword = null
    // reload default list
    categoryStore.fetchCategories()
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
