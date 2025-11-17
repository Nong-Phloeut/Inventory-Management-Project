<template>
  <custom-title icon="mdi-package-variant-closed">
    Products
    <template #right>
      <BaseButton icon="mdi-plus" @click="openAddDialog">
        Add Product
      </BaseButton>
    </template>
  </custom-title>

  <v-data-table :headers="headers" :items="productStore.products">
    <template #item.created_at="{item}">
      {{ formatDate(item.created_at) }}
    </template>
    <template #item.actions="{ item }">
      <v-btn
        icon="mdi-pencil"
        size="small"
        class="me-2"
        color="primary"
        variant="tonal"
        @click="openEditDialog(item)"
      />
      <v-btn
        icon="mdi-delete"
        size="small"
        color="error"
        variant="tonal"
        @click="deleteProduct(item)"
      />
    </template>
  </v-data-table>

  <ProductDialog
    v-if="isDialogOpen"
    v-model:isOpen="isDialogOpen"
    :product="selectedProduct"
    @save="saveProduct"
  />
</template>

<script setup>
  import { ref, onMounted } from 'vue'
  import { useProductStore } from '@/stores/productStore'
  import ProductDialog from '@/components/ProductDialog.vue'
  import { useDate } from "@/composables/useDate";

  const { formatDate, formatDateTime, addDays } = useDate();

  const productStore = useProductStore()

  const headers = [
    { title: 'Name', key: 'name' },
    { title: 'SKU', key: 'sku' },
    { title: 'Price', key: 'price' },
    { title: 'Created At', key: 'created_at' },
    { title: 'Actions', key: 'actions', sortable: false }
  ]

  const isDialogOpen = ref(false)
  const selectedProduct = ref(null)

  // fetch products when page loads
  onMounted(() => {
    productStore.fetchProducts()
  })

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
    } else {
      await productStore.addProduct(p)
    }
    isDialogOpen.value = false
  }

  const deleteProduct = async p => {
    if (confirm(`Delete "${p.name}"?`)) {
      await productStore.deleteProduct(p.id)
    }
  }
</script>
