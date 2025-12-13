<template>
  <v-container fluid class="pa-0">
    <custom-title icon="mdi-view-dashboard">Inventory Dashboard</custom-title>

    <v-row class="mb-6" dense>
      <!-- REVENUE -->
      <v-col cols="4" md="4">
        <v-card elevation="0" rounded="xl" class="pa-4 revenue-card">
          <span class="text-grey">Inventory Value</span>

          <div class="d-flex align-center">
            <h1 class="font-weight-bold me-3">
              {{ formatCurrency(inventoryValue) }}
            </h1>

            <v-chip size="small" color="red" label variant="tonal">
              <v-icon size="14" class="mr-1">mdi-arrow-up</v-icon>
              7.9%
            </v-chip>
          </div>
        </v-card>
      </v-col>

      <!-- KPI CARDS -->
      <v-col cols="12" md="8">
        <v-row dense>
          <v-col
            v-for="card in cards"
            :key="card.title"
            cols="12"
            sm="3"
            md="3"
          >
            <v-card
              class="card pa-4"
              elevation="0"
              rounded="xl"
              @click="handleCardClick(card)"
            >
              <div class="d-flex justify-space-between align-center">
                <span class="text-kpi">{{ card.title }}</span>
                <v-icon :color="card.color" size="20">
                  {{ card.icon }}
                </v-icon>
              </div>

              <div class="d-flex justify-space-between align-center mt-3">
                <h2 class="font-weight-bold">
                  {{ card.value }}
                </h2>

                <v-chip
                  v-if="card.trend !== undefined"
                  size="small"
                  label
                  :color="card.trend > 0 ? 'green' : 'red'"
                >
                  <v-icon size="14" class="mr-1">
                    {{ card.trend > 0 ? 'mdi-arrow-up' : 'mdi-arrow-down' }}
                  </v-icon>
                  {{ card.trend }}%
                </v-chip>
              </div>
            </v-card>
          </v-col>
        </v-row>
      </v-col>
    </v-row>

    <v-row dense>
      <v-col cols="12" md="6">
        <v-card :elevation="0" class="pa-6" rounded="xl">
          <v-toolbar class="bg-white">
            <h3>Stock by Category</h3>
            <v-spacer></v-spacer>
            <!-- <v-select
              v-model="selectedMonth"
              :items="monthsReport"
              label="Month"
              rounded="lg"
              density="compact"
              hide-details
              max-width="170"
            ></v-select> -->
            {{ formatDate(new Date()) }}
          </v-toolbar>

          <div style="height: 275px">
            <canvas ref="barChartCanvas"></canvas>
          </div>
        </v-card>
      </v-col>
      <v-col cols="12" md="6">
        <v-card :elevation="0" class="pa-6" rounded="xl">
          <v-toolbar class="bg-white">
            <h3>Monthly Purchase</h3>
            <!-- vs. Sales -->
            <v-spacer></v-spacer>
            <v-select
              v-model="selectedYear"
              :items="years"
              label="Year"
              rounded="lg"
              density="compact"
              hide-details
              max-width="170"
            ></v-select>
          </v-toolbar>
          <div style="height: 275px">
            <canvas ref="chartCanvas"></canvas>
          </div>
        </v-card>
      </v-col>
    </v-row>
    <!-- {{ dashboardStore.monthlyPurchases.data }} -->
  </v-container>
  <LowStockItemsDialog v-model="showLowStockDialog" />
</template>

<script setup>
  import { ref, computed, onMounted, watch } from 'vue'
  import Chart from 'chart.js/auto'
  import { useDashboardStore } from '@/stores/dashboardStore'
  import LowStockItemsDialog from '@/components/stocks/LowStockItemsDialog.vue'
  import { useCurrency } from '@/composables/useCurrency.js'
  import { useDate } from '@/composables/useDate'

  const { formatCurrency, formatKHR } = useCurrency()
  const { formatDate } = useDate()
  const dashboardStore = useDashboardStore()
  const barChartCanvas = ref(null)
  let barChartInstance = null

  const chartCanvas = ref(null)
  let chartInstance
  const showLowStockDialog = ref(false)

  function handleCardClick(card) {
    if (card.title === 'Low Stock') {
      showLowStockDialog.value = true
    }
  }

  const selectedMonth = ref(new Date().getMonth() + 1) // 1-indexed
  const selectedYear = ref(new Date().getFullYear())

  const monthsReport = [
    { title: 'January', value: 1 },
    { title: 'February', value: 2 },
    { title: 'March', value: 3 },
    { title: 'April', value: 4 },
    { title: 'May', value: 5 },
    { title: 'June', value: 6 },
    { title: 'July', value: 7 },
    { title: 'August', value: 8 },
    { title: 'September', value: 9 },
    { title: 'October', value: 10 },
    { title: 'November', value: 11 },
    { title: 'December', value: 12 }
  ]

  const currentYear = new Date().getFullYear()
  const years = computed(() => {
    const list = []
    for (let i = currentYear; i >= currentYear - 10; i--) {
      list.push(i)
    }
    return list
  })

  onMounted(async () => {
    await dashboardStore.fetchStats()
    await dashboardStore.fetchMonthlyPurchases(selectedYear.value)
    renderBarChart()

    const months = [
      'Jan',
      'Feb',
      'Mar',
      'Apr',
      'May',
      'Jun',
      'Jul',
      'Aug',
      'Sep',
      'Oct',
      'Nov',
      'Dec'
    ]

    chartInstance = new Chart(chartCanvas.value, {
      type: 'line',
      data: {
        labels: months,
        datasets: [
          {
            label: 'Purchases ($)',
            data: dashboardStore.monthlyPurchases.data,
            borderWidth: 2,
            tension: 0.4
          }
          // {
          //   label: 'Sales ($)',
          //   data: sales,
          //   borderWidth: 2,
          //   tension: 0.4
          // }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'top'
          }
        },
        scales: {
          y: {
            beginAtZero: false,
            title: {
              display: true,
              text: 'Amount ($)'
            }
          }
        }
      }
    })
  })

  // Watch for year changes
  watch(selectedYear, async newYear => {
    await dashboardStore.fetchMonthlyPurchases(newYear)

    if (chartInstance) {
      chartInstance.data.datasets[0].data = dashboardStore.monthlyPurchases.data
      chartInstance.update() // <-- important
    }
  })

  watch(
    () => dashboardStore.stats?.stockByCategory,
    () => renderBarChart()
  )

  function renderBarChart() {
    if (!barChartCanvas.value || !dashboardStore.stats?.stockByCategory) return
    if (barChartInstance) barChartInstance.destroy()

    const ctx = barChartCanvas.value.getContext('2d')
    barChartInstance = new Chart(ctx, {
      type: 'bar',
      data: dashboardStore.stats.stockByCategory,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true } }
      }
    })
  }
  const inventoryValue = computed(() => {
    return dashboardStore.stats?.inventoryValue ?? 0
  })
  // Cards
  const cards = computed(() => {
    if (!dashboardStore.stats) return []
    return [
      {
        title: 'Total Products',
        value: dashboardStore.stats.totalProducts,
        icon: 'mdi-cube-outline',
        color: 'blue-grey',
        trend: 12
      },
      {
        title: 'In Stock',
        value: dashboardStore.stats.inStock,
        icon: 'mdi-warehouse',
        color: 'success'
      },
      {
        title: 'Low Stock',
        value: dashboardStore.stats.lowStock,
        icon: 'mdi-alert-circle-outline',
        color: 'warning',
        trend: -20
      },
      {
        title: 'Out-of-Stock',
        value: dashboardStore.stats.suppliers,
        icon: 'mdi-cube-off-outline',
        color: 'red'
      }
    ]
  })
</script>

<style scoped>
  /* Custom CSS to improve spacing and typography */
  .card {
    border-radius: 12px;
    transition: all 0.3s ease-in-out;
  }
  .card:hover {
    transform: translateY(-5px);
    transition: 0.25s ease;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
  }
  .v-progress-linear {
    border-radius: 5px;
  }
  .v-data-table th {
    font-weight: bold !important;
  }
  .v-btn {
    text-transform: none;
    font-weight: 500;
  }

  .text-kpi {
    font-size: 17px;
  }
</style>
