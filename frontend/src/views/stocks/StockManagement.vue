<template>
  <custom-title icon="mdi-warehouse">Current Stock Levels</custom-title>

  <v-data-table :headers="headers" :items="stockStore.stocks.data">
    <template #item.product="{ item }">
      {{ item.product?.name }}
    </template>
    <template #item.updated_at="{ item }">
      {{ formatDate(item.product?.updated_at) }}
    </template>
    <template #item.stock_alert="{ item }">
      <v-chip :color="stockAlertChip(item).color" variant="flat" size="small">
        <v-icon :icon="stockAlertChip(item).icon" start></v-icon>
        {{ stockAlertChip(item).text }}
      </v-chip>
    </template>
    <template #item.actions="{ item }">
      <v-row dense>
        <!-- <v-tooltip location="top">
          <template #activator="{ props }">
            <v-btn
              v-bind="props"
              icon
              color="green"
              variant="text"
              @click="openDialog('return', item)"
            >
              <v-icon>mdi-backup-restore</v-icon>
            </v-btn>
          </template>
          <span>Return</span>
        </v-tooltip> -->

        <v-tooltip location="top">
          <template #activator="{ props }">
            <v-btn
              v-bind="props"
              icon
              color="orange"
              variant="text"
              @click="openDialog('adjustment', item)"
            >
              <v-icon>mdi-tune</v-icon>
            </v-btn>
          </template>
          <span>Adjustment</span>
        </v-tooltip>

        <v-tooltip location="top">
          <template #activator="{ props }">
            <v-btn
              v-bind="props"
              icon
              color="red"
              variant="text"
              @click="openDialog('loss', item)"
            >
              <v-icon>mdi-alert-circle</v-icon>
            </v-btn>
          </template>
          <span>Loss</span>
        </v-tooltip>

        <v-tooltip location="top">
          <template #activator="{ props }">
            <v-btn
              v-bind="props"
              icon
              variant="text"
              @click="openMovementDialog(item)"
            >
              <v-icon>mdi-history</v-icon>
            </v-btn>
          </template>
          <span>Stock Movements</span>
        </v-tooltip>
      </v-row>
    </template>
  </v-data-table>

  <MovementDialog v-model="isDialogOpen" :stock="selectedStock" />

  <!-- Stock Action Dialog -->
  <StockActionDialog
    v-model="dialogVisible"
    :actionType="dialogType"
    :stock="selectedStock"
    @submitAction="handleAction"
  />
</template>

<script setup>
  import { ref, onMounted } from 'vue'
  import { useStockStore } from '@/stores/stockStore'
  import MovementDialog from '@/components/MovementDialog.vue'
  import StockActionDialog from '@/components/stocks/StockActionDialog.vue'
  import { useStockMovementStore } from '@/stores/stockMovementStore'
  import { useDate } from '@/composables/useDate'
  // import { useAppUtils } from '@/composables/useAppUtils'

  // const { confirm, notif } = useAppUtils()
  const { formatDate, formatDateTime, addDays } = useDate()
  const stockMovementStore = useStockMovementStore()
  const stockStore = useStockStore()

  const headers = [
    { title: 'Product', key: 'product' },
    { title: 'SKU', key: 'product.sku' },
    { title: 'Quantity', key: 'quantity' },
    { title: 'Unit', key: 'product.unit' },
    { title: 'Stock Alert', key: 'product.low_stock_threshold' },
    { title: 'Status', key: 'stock_alert' },
    { title: 'Last Updated', key: 'updated_at' },
    { title: '', key: 'actions' }
  ]
  const form = ref({
    quantity: 0,
    note: ''
  })

  const isDialogOpen = ref(false)
  const selectedStock = ref(null)
  const dialogVisible = ref(false)
  const dialogType = ref('') // return | adjustment | loss

  onMounted(() => {
    stockStore.fetchStocks()
  })
  function openDialog(type, stock) {
    dialogType.value = type
    selectedStock.value = stock
    form.value = { quantity: 0, note: '' }
    dialogVisible.value = true
  }
  const openMovementDialog = item => {
    selectedStock.value = item
    isDialogOpen.value = true
  }
  const stockAlertChip = item => {
    const threshold = item.product?.low_stock_threshold ?? 0
    const qty = item.quantity

    if (qty <= 0) {
      return { text: 'Out of Stock', color: 'red', icon: 'mdi-close-circle' }
    }

    if (qty <= threshold) {
      return { text: 'Low Stock', color: 'warning', icon: 'mdi-alert' }
    }

    return { text: 'In Stock', color: 'green', icon: 'mdi-check-circle' }
  }

  async function handleAction(payload) {
    const { stock, ...data } = payload

    switch (dialogType.value) {
      case 'return':
        await stockMovementStore.returnStock({
          product_id: stock.product_id,
          ...data
        })
        break
      case 'adjustment':
        await stockMovementStore.adjustStock({
          product_id: stock.product_id,
          ...data
        })
        break
      case 'loss':
        await stockMovementStore.reportLoss({
          product_id: stock.product_id,
          ...data
        })
        break
    }

    // Refresh stock
    await stockStore.fetchStocks()
    // stocks.value = stockMovementStore.stocks
  }
</script>
