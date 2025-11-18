<template>
  <custom-title icon="mdi-cart-arrow-down">
    <template #right>
      <BaseButton icon="mdi-plus" @click="openDialog">New Purchase</BaseButton>
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
        variant="tonal"
        @click="editPurchase(item)"
      />
      <!-- <v-btn
        icon="mdi-delete"
        size="small"
        color="error"
        @click="deleteProduct(item)"
      /> -->
    </template>
  </v-data-table>

  <PurchaseDialog
    v-if="isDialogOpen"
    :model-value="isDialogOpen"
    @update:model-value="val => (isDialogOpen = val)"
    :purchase="selectedPurchase"
    @save="savePurchase"
  />
</template>

<script setup>
  import { ref, onMounted } from 'vue'
  import { usePurchaseStore } from '@/stores/purchaseStore'
  import PurchaseDialog from '@/components/PurchaseDialog.vue'
  import { useAppUtils } from '@/composables/useAppUtils'
  import { useI18n } from 'vue-i18n'
  const { t } = useI18n()

  const { confirm, notif } = useAppUtils()
  const purchaseStore = usePurchaseStore()
  const isDialogOpen = ref(false)
  const selectedPurchase = ref(null)

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

  function openDialog() {
    selectedPurchase.value = { items: [] } // empty purchase for creation
    isDialogOpen.value = true
  }

  function editPurchase(purchase) {
    selectedPurchase.value = { ...purchase } // clone for editing
    isDialogOpen.value = true
  }

  function savePurchase(purchase) {
    if (purchase.id) {
      purchaseStore.updatePurchase(purchase.id, purchase)
    } else {
      purchaseStore.addPurchase(purchase)
    }
    isDialogOpen.value = false
    selectedPurchase.value = null
  }
</script>
