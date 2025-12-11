<template>
  <v-container fluid class="pa-0">
    <custom-title icon="mdi-view-dashboard">
      Inventory Dashboard
    </custom-title>

    <v-row class="mb-6" dense>
      <v-col v-for="card in cards" :key="card.title" cols="12" sm="6" md="3">
        <v-card class="card pa-0" :elevation="4" @click="handleCardClick(card)">
          <template v-slot:title>
            <span class="text-kpi">{{ card.title }}</span>
          </template>
          <v-card-text class="d-flex justify-space-between align-center">
            <h1 class="font-weight-bold">{{ card.value }}</h1>

            <v-chip
              size="small"
              :color="card.trend > 0 ? 'green' : 'red'"
              text-color="white"
              class="mt-1"
              label
            >
              <v-icon
                size="14"
                class="mr-1"
                :color="
                  card.trend > 0 ? 'green' : card.trend < 0 ? 'red' : 'grey'
                "
              >
                {{
                  card.trend > 0
                    ? 'mdi-arrow-up'
                    : card.trend < 0
                      ? 'mdi-arrow-down'
                      : 'mdi-minus'
                }}
              </v-icon>

              {{ card.trend }}%
            </v-chip>
          </v-card-text>
          <template v-slot:append>
            <v-btn icon="" size="small" variant="tonal" :color="card.color">
              <v-icon :color="card.color" :icon="card.icon"></v-icon>
            </v-btn>
          </template>
        </v-card>
      </v-col>
    </v-row>

    <v-row dense>
      <v-col cols="12" md="6">
        <v-card :elevation="4" class="pa-6">
          <h3 class="mb-4">Stock by Category</h3>
          <div style="height: 275px">
            <canvas ref="barChartCanvas"></canvas>
          </div>
        </v-card>
      </v-col>
      <v-col cols="12" md="6">
        <v-card :elevation="4" class="pa-6">
          <h3 class="mb-4">Monthly Purchase vs. Sales</h3>
          <div style="height: 275px">
            <canvas ref="chartCanvas"></canvas>
          </div>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
  <LowStockItemsDialog v-model="showLowStockDialog" />
</template>

<script setup>
  import { ref, computed, onMounted, watch } from 'vue'
  import Chart from 'chart.js/auto'
  import { useDashboardStore } from '@/stores/dashboardStore'
  import LowStockItemsDialog from '@/components/stocks/LowStockItemsDialog.vue'
  import { useCurrency } from '@/composables/useCurrency.js'

  const { formatCurrency, formatKHR } = useCurrency()
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

  onMounted(async () => {
    await dashboardStore.fetchStats()
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

    const purchases = [
      1200, 1500, 1100, 1800, 1700, 2000, 2200, 2100, 1600, 1900, 2300, 2500
    ]
    const sales = [
      900, 1300, 1200, 1600, 1500, 1800, 2000, 1700, 1400, 1800, 2100, 2400
    ]

    chartInstance = new Chart(chartCanvas.value, {
      type: 'line',
      data: {
        labels: months,
        datasets: [
          {
            label: 'Purchases ($)',
            data: purchases,
            borderWidth: 2,
            tension: 0.4
          },
          {
            label: 'Sales ($)',
            data: sales,
            borderWidth: 2,
            tension: 0.4
          }
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
      // {
      //   title: 'Suppliers',
      //   value: dashboardStore.stats.suppliers,
      //   icon: 'mdi-truck',
      //   color: 'purple'
      // },
      // {
      //   title: 'Inventory Value',
      //   value: formatCurrency(dashboardStore.stats.inventoryValue),
      //   icon: 'mdi-currency-usd',
      //   color: 'blue'
      // }
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
  .dashboard-title {
    display: flex;
    align-items: center;
    margin-bottom: 24px;
    color: #333;
  }
  .dashboard-title .v-icon {
    margin-right: 8px;
  }
  .text-kpi {
    font-size: 17px;
  }
</style>
