<template>
  <custom-title icon="mdi-cart-arrow-down">
    <template #right>
      <BaseButton icon="mdi-plus" @click="goToCreate">New Purchase</BaseButton>
    </template>
    Purchase Orders
  </custom-title>

  <v-data-table
    :headers="headers"
    :items="purchaseStore.purchases"
    item-key="id"
    hover
  >
    <template #item.purchase_date="{ item }">
      {{ formatDate(item.purchase_date) }}
    </template>
    <template #item.status="{ item }">
      <v-chip
        :color="statusColor(item.status)"
        variant="tonal"
        size="small"
        class="font-weight-medium"
      >
        <v-icon :icon="statusIcon(item.status)" start></v-icon>
        {{ formatStatus(item.status) }}
      </v-chip>
    </template>

    <!-- PAYMENT STATUS -->
    <template #item.payment_status="{ item }">
      <v-chip
        :color="paymentColor(item.payment_status)"
        variant="tonal"
        size="small"
        class="font-weight-medium"
      >
        <v-icon :icon="paymentIcon(item.payment_status)" start></v-icon>
        {{ formatPayment(item.payment_status) }}
      </v-chip>
    </template>

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
  import { useDate } from '@/composables/useDate'

  const { formatDate, formatDateTime, addDays } = useDate()
  const { t } = useI18n()
  const router = useRouter()
  const { confirm, notif } = useAppUtils()
  const purchaseStore = usePurchaseStore()

  const headers = [
    { title: 'Invoice No', key: 'invoice_number' },
    { title: 'Po No', key: 'purchase_number' },
    { title: 'Supplier', key: 'supplier.name' },
    { title: 'Total', key: 'total_amount' },
    { title: 'Po Status', key: 'status' },
    { title: 'Payment Status', key: 'payment_status' },
    { title: 'Date', key: 'purchase_date' },
    { title: 'Actions', key: 'actions' }
  ]

  onMounted(() => {
    purchaseStore.fetchPurchases()
  })
  /* ---- COLOR HELPERS ---- */
  const statusColor = val => {
    switch (val) {
      case 'received':
        return 'green'
      case 'ordered':
        return 'blue'
      case 'ordered':
        return 'green-darken-1'
      case 'cancelled':
        return 'red'
      default:
        return 'grey'
    }
  }

  const paymentColor = val => {
    switch (val) {
      case 'paid':
        return 'green'
      case 'unpaid':
        return 'red'
      case 'partial':
        return 'orange'
      case 'refunded':
        return 'blue'
      default:
        return 'grey'
    }
  }

  /* ---- ICON HELPERS ---- */
  const statusIcon = val => {
    switch (val) {
      case 'received':
        return 'mdi-check-circle'
      case 'ordered':
        return 'mdi-progress-clock'
      case 'completed':
        return 'mdi-check-circle-outline'
      case 'cancelled':
        return 'mdi-close-circle'
      default:
        return 'mdi-information'
    }
  }

  const paymentIcon = val => {
    switch (val) {
      case 'paid':
        return 'mdi-cash-check'
      case 'unpaid':
        return 'mdi-cash-remove'
      case 'partial':
        return 'mdi-cash-clock'
      case 'refunded':
        return 'mdi-cash-refund'
      default:
        return 'mdi-cash'
    }
  }

  /* ---- FORMAT HELPERS ---- */
  const formatStatus = val => val.charAt(0).toUpperCase() + val.slice(1)

  const formatPayment = val => val.charAt(0).toUpperCase() + val.slice(1)
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
