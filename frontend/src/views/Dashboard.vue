<template>
  <v-container fluid class="pa-0">
    <custom-title icon="mdi-view-dashboard">Inventory Dashboard</custom-title>

    <!-- Top Cards -->
    <v-row class="mb-6" dense>
      <v-col v-for="card in cards" :key="card.title" cols="12" sm="6" md="3">
        <v-card class="card pa-6" :elevation="4">
          <v-row align="center">
            <v-col cols="8">
              <div>{{ card.title }}</div>
              <div class="text-h4 font-weight-bold mt-1">{{ card.value }}</div>
            </v-col>
            <v-col cols="4" class="text-right">
              <v-icon size="48" :color="card.color">{{ card.icon }}</v-icon>
            </v-col>
          </v-row>
        </v-card>
      </v-col>
    </v-row>

    <!-- Charts & Tables -->
    <v-row dense>
      <!-- Stock by Category Chart -->
      <v-col cols="12" md="6">
        <v-card :elevation="4" class="pa-6">
          <h3 class="mb-4">Stock by Category</h3>
          <div style="height: 275px;">
            <canvas ref="barChartCanvas"></canvas>
          </div>
        </v-card>
      </v-col>

      <!-- Low Stock Table with Search -->
      <v-col cols="12" md="6">
        <v-card :elevation="4" class="pa-6">
          <h3 class="mb-4">Low Stock Items</h3>
          <v-text-field
            v-model="search"
            label="Search"
            prepend-inner-icon="mdi-magnify"
            single-line
            variant="solo"
            density="compact"
          ></v-text-field>
          <v-data-table
            :items="filteredLowStockItems"
            :headers="tableHeaders"
            :search="search"
            hide-default-footer
          ></v-data-table>
        </v-card>
      </v-col>
    </v-row>

    <!-- Quick Actions -->
    <v-row class="mt-6 mb-5" dense>
      <v-col cols="12">
        <v-card :elevation="4" class="pa-6">
          <h3 class="mb-4">Quick Actions</h3>
          <v-btn color="primary" class="mr-4" prepend-icon="mdi-plus-circle">
            Add New Product
          </v-btn>
          <v-btn color="success" class="mr-4" prepend-icon="mdi-truck-fast">
            Add Stock
          </v-btn>
          <v-btn color="info" prepend-icon="mdi-file-chart">
            Generate Report
          </v-btn>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import Chart from 'chart.js/auto'
import { useDashboardStore } from '@/stores/dashboardStore'

const dashboardStore = useDashboardStore()
const barChartCanvas = ref(null)
let barChartInstance = null

const search = ref('')

onMounted(async () => {
  await dashboardStore.fetchStats()
  renderBarChart()
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
    { title: 'Total Products', value: dashboardStore.stats.totalProducts, icon: 'mdi-cube-outline', color: 'blue-grey' },
    { title: 'In Stock', value: dashboardStore.stats.inStock, icon: 'mdi-warehouse', color: 'success' },
    { title: 'Low Stock', value: dashboardStore.stats.lowStock, icon: 'mdi-alert-circle-outline', color: 'warning' },
    { title: 'Suppliers', value: dashboardStore.stats.suppliers, icon: 'mdi-truck', color: 'purple' }
  ]
})

// Low stock items
const filteredLowStockItems = computed(() => {
  if (!dashboardStore.stats) return []
  if (!search.value) return dashboardStore.stats.lowStockItems
  const term = search.value.toLowerCase()
  return dashboardStore.stats.lowStockItems.filter(
    i => i.name.toLowerCase().includes(term) || i.category.toLowerCase().includes(term)
  )
})

const tableHeaders = [
  { title: 'Product', key: 'name' },
  { title: 'Stock', key: 'stock' },
  { title: 'Category', key: 'category' }
]
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
</style> 
