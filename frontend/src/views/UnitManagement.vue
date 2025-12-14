<template>
  <custom-title icon="mdi-scale-balance">
    Unit Management
    <template #right>
      <BaseButton icon="mdi-plus" @click="openDialog">Add Unit</BaseButton>
    </template>
  </custom-title>
  <v-data-table :items="unitStore.units" :headers="headers" class="mt-3">
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

  <UnitDialog v-model="dialog" :editItem="selectedItem" @saved="handleSave" />
</template>

<script setup>
  import { ref, onMounted } from 'vue'
  import UnitDialog from '@/components/UnitForm.vue'
  import { useAppUtils } from '@/composables/useAppUtils'
  import { useI18n } from 'vue-i18n'
  import { useUnitStore } from '@/stores/unitStore'

  const { t } = useI18n()
  const { confirm, notif } = useAppUtils()
  const unitStore = useUnitStore()

  /* Updated headers */
  const headers = [
    { title: 'ID', key: 'id' },
    { title: 'Unit Name', key: 'name' },
    { title: 'Abbreviation', key: 'abbreviation', sortable: false },
    { title: 'Description', key: 'description', sortable: false },
    { title: 'Actions', key: 'actions', sortable: false }
  ]

  const dialog = ref(false)
  const selectedItem = ref(null)

  const openDialog = (item = null) => {
    selectedItem.value = item
    dialog.value = true
  }

  // Save (add or update)
  const handleSave = async unit => {
    try {
      const res = unit.id
        ? await unitStore.updateUnit(unit)
        : await unitStore.addUnit(unit)
      console.log(res)

      if (res.status == 200 || res.status == 201) {
        notif(
          unit.id ? t('messages.updated_success') : t('messages.saved_success'),
          { type: 'success', color: 'primary' }
        )
      } else {
        notif(res.message, { type: 'error', color: 'primary' })
      }
      unitStore.fetchUnits()
      dialog.value = false
    } catch (error) {
      notif(t('messages.existName'), { type: 'error', color: 'primary' })
    }
  }

  /* Delete unit */
  const deleteUnit = item => {
    confirm({
      title: 'Are you sure?',
      message: `Are you sure you want to delete unit "${item.name}"?`,
      options: { type: 'error', color: 'error', width: 550 },
      agree: async () => {
        try {
          await unitStore.deleteUnit(item.id)
          notif(t('messages.deleted_success'), {
            type: 'success',
            color: 'primary'
          })
          unitStore.fetchUnits()
        } catch (err) {
          console.error('Delete failed:', err)
          notif(err.response?.data?.error || t('messages.delete_failed'), {
            type: 'error',
            color: 'error'
          })
        }
      }
    })
  }

  onMounted(() => {
    unitStore.fetchUnits()
  })
</script>
