<template>
  <v-dialog v-model="modelValue" max-width="550">
    <v-card>
      <v-toolbar
        :title="editItem ? 'Edit Unit' : 'Create Unit'"
        class="bg-primary"
      >
        <v-spacer />
        <v-btn icon="mdi-close" @click="close" />
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
              counter="10"
            />
          </v-col>
        </v-row>
        <v-row>
          <v-col>
            <v-text-field
              v-model="localItem.description"
              label="Description"
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
  import { ref, computed, watch } from 'vue'

  const props = defineProps({
    modelValue: Boolean,
    editItem: Object
  })

  const emit = defineEmits(['update:modelValue', 'saved'])

  const localItem = ref({ name: '', abbreviation: '', description: '' })
  const errors = ref({})

  /* RESET FORM ON OPEN */
  watch(
    () => props.editItem,
    val => {
      localItem.value = val
        ? { ...val }
        : { name: '', abbreviation: '', description: '' }
      errors.value = {}
    },
    { immediate: true }
  )

  /* RESET ERRORS WHEN OPEN */
  watch(
    () => props.modelValue,
    v => v && (errors.value = {})
  )

  const modelValue = computed({
    get: () => props.modelValue,
    set: v => emit('update:modelValue', v)
  })

  const close = () => {
    emit('update:modelValue', false)
    errors.value = {}
  }

  /* VALIDATION FUNCTION */
  const validate = () => {
    const newErrors = {}

    if (!localItem.value.name) {
      newErrors.name = ['Unit name is required']
    }

    if (!localItem.value.abbreviation) {
      newErrors.abbreviation = ['Abbreviation is required']
    } else if (localItem.value.abbreviation.length > 10) {
      newErrors.abbreviation = ['Abbreviation must not exceed 10 characters']
    }

    errors.value = newErrors
    return Object.keys(newErrors).length === 0
  }

  /* SAVE */
  const save = () => {
    if (!validate()) return

    emit('saved', { ...localItem.value })
    close()
  }
</script>
