<template>
  <v-dialog v-model="modelValue" max-width="550">
    <v-card>
      <v-toolbar :title="editItem ? 'Edit Unit' : 'Create Unit'" class="bg-primary">
        <v-spacer />
        <v-btn icon="mdi-close" @click="close"></v-btn>
      </v-toolbar>

      <v-card-text>
        <v-row>
          <v-col>
            <v-text-field
              v-model="localItem.name"
              label="Unit Name"
              :error-messages="errors.name"
            />
          </v-col>

          <v-col>
            <v-text-field
              v-model="localItem.abbreviation"
              label="Abbreviation"
              :error-messages="errors.abbreviation"
            />
          </v-col>
        </v-row>
      </v-card-text>

      <v-card-actions class="justify-end">
        <v-btn variant="text" @click="close">Cancel</v-btn>
        <v-btn color="primary" @click="save">Save</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import axios from 'axios'
import { useAppUtils } from '@/composables/useAppUtils'

const { notif } = useAppUtils()

const props = defineProps({
  modelValue: Boolean,
  editItem: Object
})

const emit = defineEmits(['update:modelValue', 'saved'])

const localItem = ref({ name: '', abbreviation: '' })
const errors = ref({})

/* RESET FORM ON OPEN */
watch(
  () => props.editItem,
  (val) => {
    localItem.value = val ? { ...val } : { name: '', abbreviation: '' }
    errors.value = {}
  },
  { immediate: true }
)

/* RESET ANY MESSAGE WHEN OPEN */
watch(
  () => props.modelValue,
  (v) => {
    if (v) errors.value = {}
  }
)

const modelValue = computed({
  get: () => props.modelValue,
  set: v => emit('update:modelValue', v)
})

const close = () => {
  emit('update:modelValue', false)
  errors.value = {}
}

const save = async () => {
  errors.value = {}
  const apiUrl = 'http://localhost:8000/api/units'

  try {
    if (props.editItem?.id) {
      await axios.put(`${apiUrl}/${props.editItem.id}`, localItem.value)
      notif('Unit updated successfully!', { type: 'success', color: 'primary' })
    } else {
      await axios.post(apiUrl, localItem.value)
      notif('Unit created successfully!', { type: 'success', color: 'primary' })
    }

    emit('saved')
    close()

  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors
    } else {
      notif('Failed to save unit', { type: 'error', color: 'error' })
      console.error(err)
    }
  }
}
</script>
