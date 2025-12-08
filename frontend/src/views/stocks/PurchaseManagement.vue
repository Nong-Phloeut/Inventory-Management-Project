<template>
  <custom-title icon="mdi-cart-arrow-down">
    <template #right>
      <v-btn color="primary" class="me-4" @click="toggleFilter">
        <v-icon start>mdi-filter</v-icon>
        Filter
      </v-btn>
      <BaseButton icon="mdi-plus" @click="goToCreate">New Purchase</BaseButton>
    </template>
    Purchase Orders
  </custom-title>
  <v-card v-show="showFilter" elevation="0" class="mb-4 rounded-lg">
    <v-card-text class="py-0 mt-4">
      <v-row dense>
        <!-- Keyword -->
        <v-col cols="12" md="3">
          <v-text-field
            v-model="filters.keyword"
            label="Search (Invoice / Purchase)"
            clearable
          />
        </v-col>

        <!-- Status -->
        <v-col cols="12" md="3">
          <v-select
            v-model="filters.status"
            label="Status"
            :items="['draft', 'ordered', 'received', 'cancelled']"
            clearable
          />
        </v-col>

        <!-- Payment Status -->
        <v-col cols="12" md="3">
          <v-select
            v-model="filters.payment_status"
            label="Payment Status"
            :items="['unpaid', 'partial', 'paid']"
            clearable
          />
        </v-col>

        <!-- Supplier -->
        <v-col cols="12" md="3">
          <v-select
            v-model="filters.supplier_id"
            label="Supplier"
            :items="supplierStore.suppliers.data"
            item-title="name"
            item-value="id"
            clearable
          />
        </v-col>

        <!-- Date From -->
        <v-col cols="12" md="3">
          <v-text-field
            v-model="filters.date_from"
            type="date"
            label="From Date"
          />
        </v-col>

        <!-- Date To -->
        <v-col cols="12" md="3">
          <v-text-field v-model="filters.date_to" type="date" label="To Date" />
        </v-col>
      </v-row>
    </v-card-text>

    <!-- Buttons -->
    <v-card-actions class="py-0">
      <v-spacer></v-spacer>
      <v-btn variant="outlined" class="mr-2" @click="resetFilter">Reset</v-btn>
      <v-btn class="bg-primary" elevation="1" @click="applyFilter">Apply</v-btn>
    </v-card-actions>
  </v-card>

  <v-data-table
    :headers="headers"
    :items="purchaseStore.purchases.data"
    item-key="id"
    hover
  >
    <template #item.purchase_date="{ item }">
      {{ formatDate(item.purchase_date) }}
    </template>
    <template #item.total_amount="{ item }">
      {{ formatCurrency(item.total_amount) }}
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
  import { useSupplierStore } from '@/stores/supplierStore'

  import { useRouter } from 'vue-router'
  import { useI18n } from 'vue-i18n'
  import { useDate } from '@/composables/useDate'
  import { useCurrency } from '@/composables/useCurrency.js'

  const { formatCurrency, formatKHR } = useCurrency()
  const { formatDate, formatDateTime, addDays } = useDate()
  const { t } = useI18n()
  const router = useRouter()
  const { confirm, notif } = useAppUtils()
  const purchaseStore = usePurchaseStore()
  const supplierStore = useSupplierStore()

  const headers = [
    { title: 'Po No', key: 'purchase_number' },
    { title: 'Supplier', key: 'supplier.name' },
    { title: 'Total', key: 'total_amount' },
    { title: 'Po Status', key: 'status' },
    { title: 'Payment Status', key: 'payment_status' },
    { title: 'Date', key: 'purchase_date' },
    { title: 'Actions', key: 'actions' }
  ]

  const showFilter = ref(false)

  const filters = ref({
    keyword: '',
    status: null,
    payment_status: null,
    supplier_id: null,
    date_from: null,
    date_to: null
  })

  onMounted(() => {
    supplierStore.fetchSuppliers()
    purchaseStore.fetchPurchases()
  })

  const toggleFilter = () => {
    showFilter.value = !showFilter.value
  }

  const applyFilter = () => {
    purchaseStore.fetchPurchases({
      keyword: filters.value.keyword,
      status: filters.value.status,
      payment_status: filters.value.payment_status,
      supplier_id: filters.value.supplier_id,
      date_from: filters.value.date_from,
      date_to: filters.value.date_to
    })
  }

  const resetFilter = () => {
    filters.value = {
      keyword: '',
      status: null,
      payment_status: null,
      supplier_id: null,
      date_from: null,
      date_to: null
    }
    purchaseStore.fetchPurchases()
  }

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
