<template>
  <custom-title icon="mdi-cart-arrow-down">
    <template #right>
      <BaseButton icon="mdi-plus" @click="goToCreate">New Purchase</BaseButton>
    </template>
    Purchases
  </custom-title>

  <v-data-table
    :headers="headers"
    :items="purchaseStore.purchases"
    item-key="id"
    hover
  >
    <template #item.actions="{ item }">
      <v-btn
        icon="mdi-pencil"
        size="small"
        color="primary"
        variant="text"
        @click="goToEdit(item)"
      />
      <v-btn
        icon="mdi-eye"
        size="small"
        color="info"
        variant="text"
        @click="goToDetails(item)"
      />
      <!-- <v-btn
        icon="mdi-delete"
        size="small"
        color="error"
        @click="deleteProduct(item)"
      /> -->
    </template>
  </v-data-table>
</template>

<script setup>
  import { ref, onMounted } from 'vue'
  import { usePurchaseStore } from '@/stores/purchaseStore'
  import { useAppUtils } from '@/composables/useAppUtils'
  import { useRouter } from 'vue-router'
  import { useI18n } from 'vue-i18n'

  const { t } = useI18n()
  const router = useRouter()
  const { confirm, notif } = useAppUtils()
  const purchaseStore = usePurchaseStore()

  const headers = [
    { title: 'Invoice No', key: 'invoice_number' },
    { title: 'Supplier', key: 'supplier.name' },
    { title: 'Total', key: 'total_amount' },
    { title: 'Date', key: 'purchase_date' },
    { title: 'Actions', key: 'actions' }
  ]

  onMounted(() => {
    purchaseStore.fetchPurchases()
  })

  // Go to Create Page
  function goToCreate() {
    router.push('/purchase/create')
  }

  // Go to Edit Page
  function goToEdit(purchase) {
    router.push(`/purchase/${purchase.id}/edit`)
  }

  function goToDetails(item) {
  router.push(`/purchases/${item.id}/details`)
}
</script>
