<template>
  <v-container fluid class="pa-0">
    <custom-title icon="mdi-file-chart">Inventory Reports</custom-title>

    <!-- Filters -->
    <v-card class="pa-6 mb-6" elevation="4">
      <v-row dense>
        <v-col cols="12" md="3">
          <v-text-field
            v-model="filters.from"
            type="date"
            label="From Date"
            density="compact"
            hide-details
          ></v-text-field>
        </v-col>
        <v-col cols="12" md="3">
          <v-text-field
            v-model="filters.to"
            type="date"
            label="To Date"
            density="compact"
            hide-details
          ></v-text-field>
        </v-col>
        <v-col cols="12" md="3">
          <v-select
            v-model="filters.category"
            :items="categoryStore.categories.data"
            label="Category"
            item-title="name"
            item-value="id"
            density="compact"
            clearable
            hide-details
          ></v-select>
        </v-col>
        <v-col cols="12" md="3" class="d-flex align-center">
          <v-btn
            color="primary"
            prepend-icon="mdi-magnify"
            class="mr-4"
            @click="generateReport"
            hide-details
          >
            Generate
          </v-btn>
          <v-btn color="success" prepend-icon="mdi-file-excel">Export</v-btn>
        </v-col>
      </v-row>
    </v-card>

    <!-- Summary Cards -->
    <v-row class="mb-6" dense>
      <v-col v-for="card in cards" :key="card.title" cols="12" sm="6" md="3">
        <v-card class="pa-6" elevation="4">
          <v-row align="center">
            <v-col cols="8">
              <div class="text-subtitle-1">{{ card.title }}</div>
              <div class="text-h4 font-weight-bold mt-1">{{ card.value }}</div>
            </v-col>
            <v-col cols="4" class="text-right">
              <v-icon size="48" :color="card.color">{{ card.icon }}</v-icon>
            </v-col>
          </v-row>
        </v-card>
      </v-col>
    </v-row>

    <!-- Charts & Table -->
    <v-row dense>
      <v-col cols="12" md="6">
        <v-card class="pa-6" elevation="4">
          <h3 class="mb-4">Sales Trend</h3>
          <div style="height: 275px">
            <canvas ref="salesChart" height="220"></canvas>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" md="6">
        <v-card class="pa-6" elevation="4">
          <h3 class="mb-4">Stock Movement</h3>
          <div style="height: 275px">
            <canvas ref="stockChart" height="220"></canvas>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <!-- Detailed Report Table -->
    <v-card class="pa-6 mt-6" elevation="4">
      <h3 class="mb-4">Detailed Report</h3>
      <v-data-table
        :headers="tableHeaders"
        :items="reportItems"
        class="elevation-1"
      ></v-data-table>
    </v-card>
  </v-container>
</template>

<script setup>
  import { ref, onMounted } from 'vue'
  import Chart from 'chart.js/auto'
  import { useCategoryStore } from '@/stores/categoryStore'
  const categoryStore = useCategoryStore()

  const filters = ref({
    from: '',
    to: '',
    category: null
  })

  const categories = ['Electronics', 'Furniture', 'Stationery', 'Food']

  const cards = ref([
    {
      title: 'Total Sales',
      value: '$12,500',
      icon: 'mdi-cash-multiple',
      color: 'green'
    },
    {
      title: 'Stock In',
      value: '2,300',
      icon: 'mdi-database-import',
      color: 'blue'
    },
    {
      title: 'Stock Out',
      value: '1,950',
      icon: 'mdi-database-export',
      color: 'orange'
    },
    {
      title: 'Revenue',
      value: '$8,700',
      icon: 'mdi-chart-line',
      color: 'purple'
    }
  ])

  const tableHeaders = [
    { title: 'Date', value: 'date' },
    { title: 'Product', value: 'product' },
    { title: 'Category', value: 'category' },
    { title: 'Stock In', value: 'stockIn' },
    { title: 'Stock Out', value: 'stockOut' },
    { title: 'Sales ($)', value: 'sales' }
  ]

  const reportItems = ref([
    {
      date: '2025-09-01',
      product: 'Laptop',
      category: 'Electronics',
      stockIn: 20,
      stockOut: 15,
      sales: 1200
    },
    {
      date: '2025-09-02',
      product: 'Chair',
      category: 'Furniture',
      stockIn: 30,
      stockOut: 25,
      sales: 750
    },
    {
      date: '2025-09-03',
      product: 'Pen',
      category: 'Stationery',
      stockIn: 100,
      stockOut: 90,
      sales: 90
    }
  ])

  const salesChart = ref(null)
  const stockChart = ref(null)

  let salesChartInstance = null
  let stockChartInstance = null

  function renderCharts() {
    // Destroy previous charts if they exist
    if (salesChartInstance) salesChartInstance.destroy()
    if (stockChartInstance) stockChartInstance.destroy()

    // Get canvas contexts
    const salesCtx = salesChart.value.getContext('2d')
    const stockCtx = stockChart.value.getContext('2d')

    // Create sales chart
    salesChartInstance = new Chart(salesCtx, {
      type: 'line',
      data: {
        labels: ['Sep 1', 'Sep 2', 'Sep 3'],
        datasets: [
          {
            label: 'Sales ($)',
            data: [1200, 750, 90],
            borderColor: '#42A5F5',
            backgroundColor: '#42A5F5',
            fill: false
          }
        ]
      },
      options: { responsive: true, maintainAspectRatio: false }
    })

    // Create stock chart
    stockChartInstance = new Chart(stockCtx, {
      type: 'bar',
      data: {
        labels: ['Sep 1', 'Sep 2', 'Sep 3'],
        datasets: [
          {
            label: 'Stock In',
            data: [20, 30, 100],
            backgroundColor: '#66BB6A'
          },
          { label: 'Stock Out', data: [15, 25, 90], backgroundColor: '#EF5350' }
        ]
      },
      options: { responsive: true, maintainAspectRatio: false }
    })
  }

  function generateReport() {
    console.log('Filters:', filters.value)
    // Call API with filters.value to fetch new data
    renderCharts()
  }

  onMounted(() => {
    renderCharts()
    categoryStore.fetchCategories({
      per_page: -1
    })
  })
</script>

<style scoped>
  .v-card {
    border-radius: 12px;
  }
</style>
