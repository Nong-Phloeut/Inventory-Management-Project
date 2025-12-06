<template>
  <custom-title icon="mdi-scale-balance">
    Unit Management
    <template #right>
      <BaseButton icon="mdi-plus" @click="openDialog">Add Unit</BaseButton>
    </template>
  </custom-title>

  <v-data-table :items="units" :headers="headers" class="mt-3">

    <!-- No -->
    <template #item.no="{ index }">
      {{ index + 1 }}
    </template>

    <!-- Created At datetime -->
    <template #item.created_at="{ item }">
      {{ formatDateTime(item.created_at) }}
    </template>

    <!-- Updated At datetime -->
    <template #item.updated_at="{ item }">
      {{ formatDateTime(item.updated_at) }}
    </template>

    <!-- Actions -->
    <template #item.actions="{ item }">
      <v-btn icon variant="text" color="primary" @click="openDialog(item)">
        <v-icon>mdi-pencil</v-icon>
      </v-btn>

      <v-btn icon variant="text" @click="deleteUnit(item)">
        <v-icon color="red">mdi-delete</v-icon>
      </v-btn>
    </template>

  </v-data-table>

  <UnitDialog v-model="dialog" :editItem="selectedItem" @saved="loadUnits" />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import UnitDialog from '@/components/UnitForm.vue'
import { useAppUtils } from '@/composables/useAppUtils'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const { confirm, notif } = useAppUtils()

const units = ref([])

/* Updated headers */
const headers = [
  { title: 'No.', key: 'no', sortable: false },
  { title: 'ID', key: 'id' },
  { title: 'Unit Name', key: 'name' },
  { title: 'Abbreviation', key: 'abbreviation' },
  { title: 'Created At', key: 'created_at' },
  { title: 'Updated At', key: 'updated_at' },
  { title: 'Actions', key: 'actions', sortable: false }
]

const dialog = ref(false)
const selectedItem = ref(null)
const apiUrl = 'http://localhost:8000/api/units'


/* Format full timestamp */
const formatDateTime = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false
  })
}

/* Load units */
const loadUnits = async () => {
  try {
    const res = await axios.get(apiUrl)
    units.value = res.data
  } catch (err) {
    console.error('Failed to load units:', err)
    notif(t('messages.load_failed'), { type: 'error', color: 'error' })
  }
}

const openDialog = (item = null) => {
  selectedItem.value = item
  dialog.value = true
}

/* Delete unit */
const deleteUnit = (item) => {
  confirm({
    title: 'Are you sure?',
    message: `Are you sure you want to delete unit "${item.name}"?`,
    options: { type: 'error', color: 'error', width: 400 },
    agree: async () => {
      try {
        await axios.delete(`${apiUrl}/${item.id}`)
        notif(t('messages.deleted_success'), { type: 'success', color: 'primary' })
        loadUnits()
      } catch (err) {
        console.error('Delete failed:', err)
        notif(
          err.response?.data?.error || t('messages.delete_failed'),
          { type: 'error', color: 'error' }
        )
      }
    }
  })
}

onMounted(() => {
  loadUnits()
})
</script>
