<template>
  <v-dialog v-model="model" max-width="500px">
    <v-card class="pa-0">
      <v-toolbar
        :title="isEdit ? 'Update Role' : 'Add New Role'"
        class="bg-primary"
      >
        <v-spacer />
        <v-btn icon="mdi-close" @click="closeDialog"></v-btn>
      </v-toolbar>
      <v-card-text>
        <v-form ref="formRef" v-model="valid">
          <v-text-field
            v-model="role.title"
            label="Role Title"
            :rules="[rules.required]"
            variant="outlined"
            density="comfortable"
          />

          <v-select
            v-model="role.scope"
            label="Scope"
            :items="['Organization', 'Team']"
            :rules="[rules.required]"
            variant="outlined"
            density="comfortable"
          />

          <v-textarea
            v-model="role.description"
            label="Description"
            auto-grow
            :rules="[rules.required]"
            rows="3"
            variant="outlined"
            density="comfortable"
          />
        </v-form>
      </v-card-text>

      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn variant="text" @click="closeDialog">Cancel</v-btn>

        <v-btn color="primary" @click="submitForm">
          {{ isEdit ? 'Update' : 'Create' }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
  import { ref, computed, watch } from 'vue'

  const props = defineProps({
    modelValue: Boolean,
    editedRole: { type: Object, default: () => ({}) }
  })

  const emit = defineEmits(['update:modelValue', 'save'])

  const model = computed({
    get: () => props.modelValue,
    set: val => emit('update:modelValue', val)
  })

  const isEdit = computed(() => !!props.editedRole?.id)

  const role = ref({
    id: null,
    title: '',
    scope: '',
    description: ''
  })

  watch(
    () => props.editedRole,
    val => {
      role.value = { ...val } // Fill form for edit mode
    },
    { immediate: true }
  )

  const formRef = ref(null)
  const valid = ref(true)

  const rules = {
    required: v => !!v || 'This field is required'
  }

  function submitForm() {
    formRef.value?.validate().then(success => {
      if (!success) return
      emit('save', { ...role.value })
      closeDialog()
    })
  }

  function closeDialog() {
    emit('update:modelValue', false)
  }
</script>
