<template>
  <custom-title icon="mdi-package-variant-closed">
    Products
    <template #right>
      <BaseButtonFilter class="me-4" @click="toggleFilterForm" />
      <BaseButton icon="mdi-plus" @click="openAddDialog">
        Add Product
      </BaseButton>
    </template>
  </custom-title>

  <!-- FILTER FORM -->
  <v-card class="mb-4 rounded-lg" elevation="0" v-show="showFilterForm">
    <v-card-text class="py-0 mt-4">
      <v-row dense>
        <v-col cols="12" md="4">
          <v-text-field
            v-model="draftFilters.keyword"
            label="Search (Name / SKU)"
            prepend-inner-icon="mdi-magnify"
            clearable
            hide-details
          />
        </v-col>

        <v-col cols="12" md="4">
          <v-select
            v-model="draftFilters.category_id"
            :items="categoryStore.categories.data"
            item-title="name"
            item-value="id"
            label="Category"
            multiple
          >
            <template #selection="{ item, index }">
              <v-chip v-if="index < 2" :text="item.title" size="x-small" />
              <span v-if="index === 2" class="text-grey text-caption">
                (+{{ draftFilters.category_id.length - 2 }} others)
              </span>
            </template>
          </v-select>
        </v-col>

        <v-col cols="12" md="2">
          <v-text-field
            v-model="draftFilters.min_price"
            type="number"
            label="Min Price"
            clearable
            hide-details
          />
        </v-col>

        <v-col cols="12" md="2">
          <v-text-field
            v-model="draftFilters.max_price"
            type="number"
            label="Max Price"
            clearable
            hide-details
          />
        </v-col>
      </v-row>
    </v-card-text>

    <v-card-actions>
      <v-spacer />
      <v-btn
        variant="outlined"
        @click="resetFilter"
        :disabled="!isFilterActive"
      >
        Reset
      </v-btn>
      <v-btn
        class="bg-primary"
        prepend-icon="mdi-filter-outline"
        @click="applyFilter"
      >
        Apply Filter
      </v-btn>
    </v-card-actions>
  </v-card>

  <!-- DATA TABLE -->
  <v-data-table-server
    :headers="headers"
    :items="productStore.products.data"
    :items-length="productStore.products.total || 0"
    v-model:items-per-page="productStore.products.per_page"
    @update:options="loadItems"
    item-value="id"
    hover
  >
    <template #item.no="{ index }">
      {{ index + 1 + (tableOptions.page - 1) * tableOptions.itemsPerPage }}
    </template>

    <template #item.created_at="{ item }">
      {{ formatDate(item.created_at) }}
    </template>

    <template #item.price="{ item }">
      {{ formatCurrency(item.price) }}
    </template>

    <template #item.status="{ item }">
      <v-chip size="small" :color="item.status === 'active' ? 'green' : 'red'">
        <v-icon
          start
          :icon="item.status === 'active' ? 'mdi-check-circle' : 'mdi-cancel'"
        />
        {{ item.status }}
      </v-chip>
    </template>

    <template #item.actions="{ item }">
      <v-btn
        icon="mdi-pencil"
        variant="text"
        color="primary"
        @click="openEditDialog(item)"
      />
      <v-btn
        icon="mdi-delete"
        variant="text"
        color="error"
        @click="deleteProduct(item)"
      />
    </template>
  </v-data-table-server>

  <ProductDialog
    v-if="isDialogOpen"
    v-model:isOpen="isDialogOpen"
    :product="selectedProduct"
    @save="saveProduct"
  />
</template>

<script setup>
  import { ref, reactive, onMounted, computed } from 'vue'
  import { useProductStore } from '@/stores/productStore'
  import { useCategoryStore } from '@/stores/categoryStore'
  import ProductDialog from '@/components/ProductDialog.vue'
  import { useAppUtils } from '@/composables/useAppUtils'
  import { useDate } from '@/composables/useDate'
  import { useCurrency } from '@/composables/useCurrency'
  import { useI18n } from 'vue-i18n'

  const productStore = useProductStore()
  const categoryStore = useCategoryStore()
  const { confirm, notif } = useAppUtils()
  const { formatDate } = useDate()
  const { formatCurrency } = useCurrency()
  const { t } = useI18n()

  /* =====================
   TABLE + FILTER STATE
===================== */

  const tableOptions = reactive({
    page: 1,
    itemsPerPage: 10
  })

  const draftFilters = reactive({
    keyword: '',
    category_id: [],
    min_price: null,
    max_price: null
  })

  const appliedFilters = reactive({
    keyword: '',
    category_id: [],
    min_price: null,
    max_price: null
  })

  const showFilterForm = ref(false)
  const isDialogOpen = ref(false)
  const selectedProduct = ref(null)

  /* =====================
   HEADERS
===================== */

  const headers = [
    { title: 'No', key: 'no' },
    { title: 'Name', key: 'name' },
    { title: 'Unit', key: 'unit.abbreviation' },
    { title: 'Category', key: 'category.name' },
    { title: 'SKU', key: 'sku' },
    { title: 'Price', key: 'price' },
    { title: 'Status', key: 'status' },
    { title: 'Actions', key: 'actions', sortable: false }
  ]

  /* =====================
   FETCH DATA (ONE SOURCE)
===================== */

  const fetchData = () => {
    productStore.fetchProducts({
      page: tableOptions.page,
      per_page: tableOptions.itemsPerPage,
      keyword: appliedFilters.keyword,
      category_id: appliedFilters.category_id.length
        ? appliedFilters.category_id.join(',')
        : null,
      min_price: appliedFilters.min_price,
      max_price: appliedFilters.max_price
    })
  }

  /* =====================
   EVENTS
===================== */

  const loadItems = ({ page, itemsPerPage }) => {
    tableOptions.page = page
    tableOptions.itemsPerPage = itemsPerPage
    fetchData()
  }

  const applyFilter = () => {
    appliedFilters.keyword = draftFilters.keyword
    appliedFilters.category_id = [...draftFilters.category_id]
    appliedFilters.min_price = draftFilters.min_price
    appliedFilters.max_price = draftFilters.max_price

    tableOptions.page = 1
    fetchData()
  }

  const resetFilter = () => {
    draftFilters.keyword = ''
    draftFilters.category_id = []
    draftFilters.min_price = null
    draftFilters.max_price = null

    appliedFilters.keyword = ''
    appliedFilters.category_id = []
    appliedFilters.min_price = null
    appliedFilters.max_price = null
    tableOptions.page = 1
    fetchData()
  }

  const isFilterActive = computed(() => {
    return (
      draftFilters.keyword.trim() !== '' ||
      draftFilters.category_id.length > 0 ||
      draftFilters.min_price !== null ||
      draftFilters.max_price !== null
    )
  })
  const toggleFilterForm = () => {
    showFilterForm.value = !showFilterForm.value
  }

  /* =====================
   CRUD
===================== */

  const openAddDialog = () => {
    selectedProduct.value = null
    isDialogOpen.value = true
  }

  const openEditDialog = product => {
    selectedProduct.value = { ...product }
    isDialogOpen.value = true
  }

  const saveProduct = async product => {
    if (product.id) {
      await productStore.updateProduct(product)
      notif(t('messages.updated_success'), { type: 'success' })
    } else {
      await productStore.addProduct(product)
      notif(t('messages.saved_success'), { type: 'success' })
    }

    isDialogOpen.value = false
    fetchData()
  }

  const deleteProduct = product => {
    confirm({
      title: 'Delete Product',
      message: `Are you sure you want to delete ${product.name || 'this product'}?`,
      options: { type: 'error', width: 500 },
      agree: async () => {
        try {
          await productStore.deleteProduct(product.id)

          // Success handling
          notif(t('messages.deleted_success'), { type: 'success' })
          fetchData()
        } catch (error) {
          // Dynamic error handling
          const errorMessage =
            error.response?.data?.message || 'An unexpected error occurred.'
          notif(errorMessage, { type: 'error' }) // Changed to error type for visibility
        }
      }
    })
  }

  /* =====================
   INIT
===================== */

  onMounted(() => {
    fetchData()
    categoryStore.fetchCategories({ per_page: -1 })
  })
</script>
