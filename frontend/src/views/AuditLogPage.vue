<template>
  <v-container fluid class="pa-0">
    <!-- Title & Buttons -->
    <custom-title icon="mdi-timeline-clock-outline">
      Audit Log
      <template #right>
        <v-btn
          color="primary"
          prepend-icon="mdi-filter-outline"
          class="me-4"
          @click="toggleFilterForm"
        >
          Filter
        </v-btn>

        <v-btn
          color="green"
          prepend-icon="mdi-download"
          @click="toggleExportForm"
        >
          Export
        </v-btn>
      </template>
    </custom-title>

    <!-- FILTER FORM -->
    <v-card class="mb-4 pa-4" elevation="0" v-show="showFilterForm">
      <v-row>
        <v-col cols="12" md="3">
          <v-text-field
            v-model="formFilters.keyword"
            label="Search keyword"
            prepend-inner-icon="mdi-magnify"
            clearable
            hide-details
          />
        </v-col>

        <v-col cols="12" md="3">
          <v-date-input
            v-model="formFilters.startDate"
            label="Start Date"
            clearable
            hide-details="auto"
          />
        </v-col>

        <v-col cols="12" md="3">
          <v-date-input
            v-model="formFilters.endDate"
            label="End Date"
            clearable
            hide-details="auto"
          />
        </v-col>

        <v-col cols="12" md="3" class="d-flex align-center">
          <v-btn
            color="primary"
            prepend-icon="mdi-filter-outline"
            @click="applyFilter"
          >
            Apply Filter
          </v-btn>
          <v-btn class="ms-3" variant="outlined" @click="resetFilter">Reset</v-btn>
        </v-col>
      </v-row>
    </v-card>

    <!-- EXPORT FORM -->
    <v-card class="mb-4 pa-4" elevation="0" v-show="showExportForm">
      <v-row class="align-center">
        <v-col cols="12" md="4">
          <v-date-input
            v-model="exportDates.startDate"
            label="Start Date"
            :error="!!exportErrors.start"
            :error-messages="exportErrors.start"
            clearable
          />
        </v-col>

        <v-col cols="12" md="4">
          <v-date-input
            v-model="exportDates.endDate"
            label="End Date"
            :error="!!exportErrors.end"
            :error-messages="exportErrors.end"
            clearable
          />
        </v-col>

        <v-col cols="12" md="4" class="d-flex align-center mb-4">
          <v-btn
            color="green"
            prepend-icon="mdi-microsoft-excel"
            :disabled="!isDateRangeValid"
            @click="handleExport"
          >
            Generate
          </v-btn>

          <v-btn class="ms-3" variant="outlined" @click="closeExportForm">
            Cancel
          </v-btn>
        </v-col>
      </v-row>
    </v-card>

    <!-- Audit Table -->
    <v-data-table
      :headers="headers"
      :items="store.filteredLogs"
      class="elevation-0"
    ></v-data-table>
  </v-container>
</template>

<script setup>
  import { ref, reactive, computed, watch } from 'vue'
  import { useAuditLogStore } from '@/stores/auditLogStore'

  const store = useAuditLogStore()

  // UI state
  const showFilterForm = ref(false)
  const showExportForm = ref(false)

  // Filter Form
  const toggleFilterForm = () => {
    showFilterForm.value = !showFilterForm.value
    showExportForm.value = false
  }

  const formFilters = reactive({
    keyword: null,
    startDate: null,
    endDate: null
  })

  const applyFilter = () => {
    // todo call api end point from store
  }

  const resetFilter = () => {
    formFilters.keyword = ''
    formFilters.startDate = null
    formFilters.endDate = null
  }

  // Export Form
  const exportDates = reactive({
    startDate: store.filters.startDate || null,
    endDate: store.filters.endDate || null
  })

  const exportErrors = reactive({
    start: '',
    end: ''
  })

  // Show/hide export form
  const toggleExportForm = () => {
    showExportForm.value = !showExportForm.value
    showFilterForm.value = false
    clearExportErrors()
  }

  // Close export form
  const closeExportForm = () => {
    showExportForm.value = false
    clearExportErrors()
  }

  // Clear errors
  const clearExportErrors = () => {
    exportErrors.start = ''
    exportErrors.end = ''
  }

  // Computed: check date range validity live
  const isDateRangeValid = computed(() => {
    clearExportErrors()
    const start = exportDates.startDate
    const end = exportDates.endDate

    if (!start || !end) return false

    if (new Date(start) > new Date(end)) {
      exportErrors.end = 'End Date cannot be earlier than Start Date'
      return false
    }

    return true
  })

  // Handle export
  const handleExport = () => {
    if (!isDateRangeValid.value) return

    // Optionally update store.filters to export dates
    const prevStart = store.filters.startDate
    const prevEnd = store.filters.endDate

    store.filters.startDate = exportDates.startDate
    store.filters.endDate = exportDates.endDate

    store.exportCSV({
      startDate: exportDates.startDate,
      endDate: exportDates.endDate
    })

    // Restore previous filter state
    store.filters.startDate = prevStart
    store.filters.endDate = prevEnd

    closeExportForm()
  }

  // Table headers
  const headers = ref([
    { title: 'ID', key: 'id' },
    { title: 'Username', key: 'username' },
    { title: 'Action', key: 'action' },
    { title: 'Description', key: 'description' },
    { title: 'Date', key: 'created_at' }
  ])
</script>
