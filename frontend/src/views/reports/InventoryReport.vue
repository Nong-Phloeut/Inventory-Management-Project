<template>
  <!-- PAGE TITLE -->
  <custom-title icon="mdi-warehouse">
    Inventory Report
    <template #right>
      <v-btn variant="outlined" prepend-icon="mdi-file-pdf-box">
        Export PDF
      </v-btn>
      <v-btn
        class="ms-2"
        variant="outlined"
        color="success"
        prepend-icon="mdi-file-excel"
      >
        Export Excel
      </v-btn>
    </template>
  </custom-title>

  <!-- FILTERS -->
  <v-card elevation="0" class="mb-6 pa-4 rounded-lg">
    <v-row dense>
      <v-col cols="12" md="3">
        <v-date-input
          v-model="filters.from"
          label="From Date"
          hide-details
        ></v-date-input>
      </v-col>
      <v-col cols="12" md="3">
        <v-date-input
          v-model="filters.to"
          label="To Date"
          hide-details
        ></v-date-input>
      </v-col>
      <v-col cols="12" md="3">
        <v-select
          label="Category"
          :items="categories"
          v-model="filters.category"
          hide-details
        />
      </v-col>
      <v-col cols="12" md="3" class="d-flex align-center">
        <v-btn color="primary" prepend-icon="mdi-filter">Apply Filter</v-btn>
      </v-col>
    </v-row>
  </v-card>

  <!-- KPI SUMMARY -->
  <v-row dense>
    <v-col v-for="kpi in kpis" :key="kpi.title" cols="12" sm="3" md="3">
      <v-card class="card pa-4" elevation="0" rounded="xl">
        <div class="d-flex justify-space-between align-center">
          <span class="text-kpi">{{ kpi.title }}</span>
          <v-icon :color="kpi.color" size="20">
            {{ kpi.icon }}
          </v-icon>
        </div>

        <div class="d-flex justify-space-between align-center mt-3">
          <h2 class="font-weight-bold">
            {{ kpi.value }}
          </h2>
        </div>
      </v-card>
    </v-col>
  </v-row>

  <!-- CHART PLACEHOLDERS -->
  <v-row dense class="mb-6 mt-4">
    <v-col cols="12" md="6">
      <v-card elevation="0" class="pa-4 rounded-lg">
        <div class="text-subtitle-1 font-weight-medium mb-2">
          Stock by Category
        </div>
        <div class="chart-placeholder">Bar Chart</div>
      </v-card>
    </v-col>
    <v-col cols="12" md="6">
      <v-card elevation="0" class="pa-4 rounded-lg">
        <div class="text-subtitle-1 font-weight-medium mb-2">
          Stock Value by Warehouse
        </div>
        <div class="chart-placeholder">Pie Chart</div>
      </v-card>
    </v-col>
  </v-row>

  <!-- INVENTORY TABLE -->
  <v-card elevation="0" class="rounded-lg">
    <v-data-table
      :headers="headers"
      :items="items"
      class="rounded-lg"
      density="comfortable"
    >
      <template #item.status="{ value }">
        <v-chip
          :color="
            value === 'Low' ? 'warning' : value === 'Out' ? 'error' : 'success'
          "
          size="small"
        >
          {{ value }}
        </v-chip>
      </template>
    </v-data-table>
  </v-card>
</template>

<script setup>
  import { ref } from 'vue'

  const filters = ref({
    from: null, // 1st day of current month
    to: null, // last day of current month
    category: null
  })

  const categories = ['Electronics', 'Office Supplies', 'Furniture']
  const warehouses = ['Main Warehouse', 'Branch A', 'Branch B']
  const stockStatuses = ['In Stock', 'Low', 'Out']

  const kpis = [
    { title: 'Total Products', value: 320, icon: 'mdi-package-variant-closed' },
    { title: 'Total Stock Qty', value: 12840, icon: 'mdi-counter' },
    { title: 'Low Stock Items', value: 24, icon: 'mdi-alert' },
    { title: 'Out of Stock', value: 8, icon: 'mdi-close-circle' }
  ]

  const headers = [
    { title: 'Product', value: 'name' },
    { title: 'Category', value: 'category' },
    { title: 'Warehouse', value: 'warehouse' },
    { title: 'Quantity', value: 'qty' },
    { title: 'Unit Price', value: 'price' },
    { title: 'Status', value: 'status' }
  ]

  const items = [
    {
      name: 'Laptop Dell XPS',
      category: 'Electronics',
      warehouse: 'Main Warehouse',
      qty: 12,
      price: '$1,200',
      status: 'Low'
    },
    {
      name: 'Office Chair',
      category: 'Furniture',
      warehouse: 'Branch A',
      qty: 0,
      price: '$85',
      status: 'Out'
    },
    {
      name: 'Printer HP 107A',
      category: 'Office Supplies',
      warehouse: 'Main Warehouse',
      qty: 56,
      price: '$140',
      status: 'In Stock'
    }
  ]
</script>

<style scoped>
  .chart-placeholder {
    height: 240px;
    border: 2px dashed rgba(0, 0, 0, 0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(0, 0, 0, 0.4);
    font-weight: 500;
  }
</style>
