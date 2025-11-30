<template>
  <v-dialog v-model="internalOpen" max-width="800">
    <v-card>
      <v-toolbar
        :title="form.id ? 'Edit Supplier' : 'Add Supplier'"
        class="bg-primary"
      >
        <v-spacer />
        <v-btn icon="mdi-close" @click="close"></v-btn>
      </v-toolbar>
      <v-card-text>
        <v-row>
          <v-col cols="12" md="6">
            <v-text-field v-model="form.name" label="Supplier Name" required />
          </v-col>
          <v-col cols="12" md="6">
            <v-text-field v-model="form.contact_name" label="Contact Person" />
          </v-col>
        </v-row>
        <v-row>
          <v-col cols="12" md="6">
            <v-text-field v-model="form.phone" label="Phone" />
          </v-col>
          <v-col cols="12" md="6">
            <v-text-field v-model="form.email" label="Email" type="email" />
          </v-col>
        </v-row>
        <v-row>
          <v-col>
            <v-text-field v-model="form.address" label="Address" />
          </v-col>
          <v-col>
            <v-select
              v-model="form.status"
              :items="statusOptions"
              item-title="name"
              item-value="id"
              label="Status"
            />
          </v-col>
        </v-row>
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn text @click="close">Cancel</v-btn>
        <v-btn color="primary" @click="save">Save</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
  import { ref, watch } from 'vue'

  const props = defineProps({
    modelValue: Boolean,
    supplier: { type: Object, default: null }
  })
  const emit = defineEmits(['update:modelValue', 'save'])

  const internalOpen = ref(false)

  const form = ref({
    id: null,
    name: '',
    contact_name: '',
    phone: '',
    email: '',
    status: 1
  })
  const statusOptions = ref([
    { id: 1, name: 'Active' },
    { id: 0, name: 'Inactive' }
  ])

  const resetForm = () => {
    form.value = {
      id: null,
      name: '',
      contact_name: '',
      phone: '',
      email: '',
      status: ''
    }
  }

  watch(
    () => props.modelValue,
    val => (internalOpen.value = val),
    { immediate: true }
  )

  watch(internalOpen, val => emit('update:modelValue', val))

  watch(
    () => props.supplier,
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
    emit('save', { ...form.value })
    resetForm()
    close()
  }
</script>
