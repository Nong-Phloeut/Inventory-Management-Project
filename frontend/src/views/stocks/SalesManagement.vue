<template>
  <v-container fluid class="pa-0">
    <custom-title icon="mdi-cart">
      Sales Management
      <template #right>
        <v-btn color="primary" @click="openDialog">+ New Sale</v-btn>
      </template>
    </custom-title>

    <v-data-table
      :items="saleStore.sales"
      :headers="headers"
      :loading="saleStore.loading"
      class="mt-4"
    >
      <template #item.total_amount="{ item }">
        {{ item.total_amount.toFixed(2) }}
      </template>
    </v-data-table>

    <!-- Sale Dialog -->
    <SaleDialog v-model="dialog" @save="createSale" />
  </v-container>
</template>

<script setup>
  import { ref, onMounted } from 'vue'
  import { useSaleStore } from '@/stores/saleStore'
  import SaleDialog from '@/components/SaleDialog.vue'

  const saleStore = useSaleStore()
  const dialog = ref(false)

  const headers = [
    { text: 'ID', value: 'id' },
    { text: 'Date', value: 'sale_date' },
    { text: 'Total Amount', value: 'total_amount' },
    { text: 'Created At', value: 'created_at' }
  ]

  onMounted(() => {
    saleStore.fetchSales()
  })

  function openDialog() {
    dialog.value = true
  }

  async function createSale(sale) {
    await saleStore.createSale(sale)
  }
</script>
