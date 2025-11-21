<template>
  <custom-title icon="mdi-scale-balance">
    Unit Management
    <template #right>
      <BaseButton icon="mdi-plus" @click="openDialog">Add Unit</BaseButton>
    </template>
  </custom-title>

  <v-data-table :items="units" :headers="headers" class="mt-3">
    <template #item.actions="{ item }">
      <v-btn icon variant="text" color='primary' @click="openDialog(item)">
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
  import UnitDialog from '@/components/UnitDialog.vue'
  import { useAppUtils } from '@/composables/useAppUtils'
  import { useI18n } from 'vue-i18n'
  const { t } = useI18n()

  const { confirm, notif } = useAppUtils()
  const units = ref([])

  const headers = [
    { title: 'ID', key: 'id' },
    { title: 'Unit Name', key: 'name' },
    { title: 'Abbreviation', key: 'abbreviation' },
    { title: 'Actions', key: 'actions', sortable: false }
  ]

  const dialog = ref(false)
  const selectedItem = ref(null)

  const loadUnits = () => {
    // Replace with your API
    units.value = [
      { id: 1, name: 'Kilogram', abbreviation: 'Lg' },
      { id: 2, name: 'Litre', abbreviation: 'Box' }
    ]
  }

  const openDialog = (item = null) => {
    selectedItem.value = item
    dialog.value = true
  }

  const deleteUnit = item => {
     confirm({
      title: 'Are you sure?',
      message: 'Are you sure you want to delete this supplier?',
      options: {
        type: 'error',
        color: 'error',
        width: 400
      },
      agree: async () => {
        // await supplierStore.removeSupplier(id)
        notif(t('messages.deleted_success'), {
          type: 'success',
          color: 'primary'
        })
      }
    })
    console.log('Delete:', item)
  }

  onMounted(() => {
    loadUnits()
  })
</script>
