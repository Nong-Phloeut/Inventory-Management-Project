<template>
  <v-dialog v-model="internalOpen" max-width="600">
    <v-card>
      <v-toolbar
        :title="form.id ? 'Edit Category' : 'Add Category'"
        class="bg-primary"
      >
        <v-spacer />
        <v-btn icon="mdi-close" @click="close"></v-btn>
      </v-toolbar>

      <v-card-text>
        <v-form ref="formRef" v-model="isValid">
          <v-text-field
            v-model="form.name"
            label="Category Name"
            :rules="[rules.required]"
            required
            mandatory
          />
          <v-textarea v-model="form.description" label="Description" rows="3" />
        </v-form>
      </v-card-text>

      <v-card-actions>
        <v-spacer />
        <v-btn text @click="close">Cancel</v-btn>
        <v-btn color="primary" :disabled="!isValid" @click="save">Save</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
  import { ref, watch } from 'vue'

  const props = defineProps({
    modelValue: Boolean,
    category: { type: Object, default: null }
  })
  const emit = defineEmits(['update:modelValue', 'save'])

  const internalOpen = ref(false)
  const form = ref({ id: null, name: '', description: '' })
  const formRef = ref(null)
  const isValid = ref(false)

  const rules = {
    required: v => !!v || 'This field is required'
  }

  watch(
    () => props.modelValue,
    val => (internalOpen.value = val),
    { immediate: true }
  )

  watch(internalOpen, val => emit('update:modelValue', val))

  const resetForm = () => {
    form.value = { id: null, name: '', description: '' }
    isValid.value = false
  }

  watch(
    () => props.category,
    newVal => {
      if (newVal) {
        form.value = { ...newVal }
      } else {
        resetForm()
      }
    },
    { immediate: true }
  )

  const close = () => (internalOpen.value = false)

  const save = () => {
    formRef.value?.validate().then(success => {
      if (success) {
        emit('save', { ...form.value })
        resetForm()
        close()
      }
    })
  }
</script>
