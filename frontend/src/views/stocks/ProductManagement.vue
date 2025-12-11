<template>
  <custom-title icon="mdi-package-variant-closed">
    Products
    <template #right>
      <BaseButtonFilter class="me-4" @click="toggleFilterForm"/>
      <BaseButton icon="mdi-plus" @click="openAddDialog">
        Add Product
      </BaseButton>
    </template>
  </custom-title>
  <!-- FILTER FORM -->
  <v-card class="mb-4 rounded-lg" elevation="0" v-show="showFilterForm">
    <v-card-text class="py-0 mt-4">
      <v-row dense >
        <v-col cols="12" md="4">
          <v-text-field
            v-model="filters.keyword"
            label="Search (Name / SUK)"
            prepend-inner-icon="mdi-magnify"
            density="comfortable"
            clearable
            hide-details
          />
        </v-col>

        <!-- Category -->
        <v-col cols="12" md="4">
          <v-select
            v-model="filters.category_id"
            :items="categoryStore.categories.data"
            item-title="name"
            item-value="id"
            label="Category"
            multiple
          >
            <template v-slot:selection="{ item, index }">
              <v-chip v-if="index < 2" :text="item.title" size="x-small"/>

              <span
                v-if="index === 2"
                class="text-grey text-caption align-self-center"
              >
                (+{{ filters.category_id.length - 2 }} others)
              </span>
            </template>
          </v-select>
        </v-col>

        <!-- Stock Status -->
        <!-- <v-col cols="12" md="2">
          <v-select
            v-model="filters.stock_status"
            :items="stockStatusOptions"
            label="Stock Status"
            clearable
            hide-details
          />
        </v-col> -->

        <!-- Price Range -->
        <v-col cols="12" md="2">
          <v-text-field
            v-model="filters.min_price"
            type="number"
            label="Min Price"
            clearable
            hide-details
          />
        </v-col>

        <v-col cols="12" md="2">
          <v-text-field
            v-model="filters.max_price"
            type="number"
            label="Max Price"
            clearable
            hide-details
          />
        </v-col>
      </v-row>
    </v-card-text>

    <v-card-actions class="py-0">
      <v-spacer></v-spacer>
      <v-btn class="ms-3" variant="outlined" @click="resetFilter">Reset</v-btn>
      <v-btn
        class="bg-primary"
        elevation="1"
        prepend-icon="mdi-filter-outline"
        @click="applyFilter"
      >
        Apply Filter
      </v-btn>
    </v-card-actions>
  </v-card>
  <v-data-table-server
    :headers="headers"
    :items="productStore.products.data"
    v-model:items-per-page="itemsPerPage"
    :items-length="productStore.products.total || 0"
    @update:options="loadItems"
  >
    <template #item.no="{ index }">
      {{ index + 1 }}
    </template>
    <template #item.created_at="{ item }">
      {{ formatDate(item.created_at) }}
    </template>
    <template #item.price="{ item }">
      {{ formatCurrency(item.price) }}
    </template>
    <template #item.status="{ item }">
      <v-chip size="small" :color="item.status == 'active' ? 'green' : 'red'">
        <v-icon
          :icon="item.status == 'active' ? 'mdi-check-circle' : 'mdi-cancel'"
          start
        ></v-icon>
        {{ item.status.charAt(0).toUpperCase() + item.status.slice(1) }}
      </v-chip>
    </template>
    <template #item.actions="{ item }">
      <v-btn
        icon="mdi-pencil"
        class="me-2"
        color="primary"
        variant="text"
        @click="openEditDialog(item)"
      />
      <v-btn
        icon="mdi-delete"
        color="error"
        variant="text"
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
  import { ref, onMounted, reactive } from 'vue'
  import { useProductStore } from '@/stores/productStore'
  import { useCategoryStore } from '@/stores/categoryStore'
  import ProductDialog from '@/components/ProductDialog.vue'
  import { useDate } from '@/composables/useDate'
  import { useAppUtils } from '@/composables/useAppUtils'
  import { useI18n } from 'vue-i18n'
  import { useCurrency } from '@/composables/useCurrency.js'

  const { formatCurrency, formatKHR } = useCurrency()
  const { confirm, notif } = useAppUtils()
  const { formatDate, formatDateTime, addDays } = useDate()
  const { t } = useI18n()

  const productStore = useProductStore()
  const categoryStore = useCategoryStore()

  const itemsPerPage = ref(10)
  const headers = [
    { title: 'No', key: 'no' },
    { title: 'Name', key: 'name' },
    { title: 'Unit', key: 'unit.abbreviation' },
    { title: 'Category', key: 'category.name' },
    { title: 'SKU', key: 'sku' },
    { title: 'Price', key: 'price' },
    { title: 'Status', key: 'status' },
    { title: 'Created At', key: 'created_at' },
    { title: 'Actions', key: 'actions', sortable: false }
  ]

  const isDialogOpen = ref(false)
  const selectedProduct = ref(null)
  const showFilterForm = ref(false)

  const filters = ref({
    keyword: '',
    category_id: [],
    min_price: null,
    max_price: null
  })

  const toggleFilterForm = () => {
    showFilterForm.value = !showFilterForm.value
  }

  // fetch products when page loads
  onMounted(() => {
    productStore.fetchProducts()
    categoryStore.fetchCategories({
      per_page: -1
    })
  })

const applyFilter = () => {
  productStore.fetchProducts({
    keyword: filters.value.keyword,
    category_id: filters.value.category_id.join(','),
    min_price: filters.value.min_price,
    max_price: filters.value.max_price
  })
}


  const resetFilter = () => {
    filters.value = {
      keyword: '',
      category_id: null,
      min_price: null,
      max_price: null
    }

    productStore.fetchProducts()
  }
  const loadItems = ({ page, itemsPerPage }) => {
    productStore.fetchProducts({
      page,
      per_page: itemsPerPage
    })
  }
  const openAddDialog = () => {
    selectedProduct.value = null
    isDialogOpen.value = true
  }

  const openEditDialog = p => {
    selectedProduct.value = { ...p }
    isDialogOpen.value = true
  }

  const saveProduct = async p => {
    if (p.id) {
      await productStore.updateProduct(p)
      notif(t('messages.updated_success'), {
        type: 'success',
        color: 'primary'
      })
    } else {
      await productStore.addProduct(p)
      notif(t('messages.saved_success'), {
        type: 'success',
        color: 'primary'
      })
    }
    isDialogOpen.value = false
  }

  const deleteProduct = async p => {
    confirm({
      title: 'Are you sure?',
      message: 'Are you sure you want to delete this product?',
      options: {
        type: 'error',
        color: 'error',
        width: 400
      },
      agree: async () => {
        try {
          await productStore.deleteProduct(p.id)

          notif(t('messages.deleted_success'), {
            type: 'success',
            color: 'primary'
          })
        } catch (err) {
          notif(err.response.data.message, {
            type: 'error',
            color: 'error'
          })
        }
      }
    })
  }
</script>
