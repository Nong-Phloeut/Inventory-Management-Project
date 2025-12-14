<template>
  <v-dialog v-model="model" max-width="600px">
    <v-card class="pa-0">
      <v-toolbar :title="isEdit ? 'Update User' : 'Add New User'" class="bg-primary">
        <v-spacer />
        <v-btn icon="mdi-close" @click="closeDialog"></v-btn>
      </v-toolbar>

      <v-card-text>
        <v-form ref="formRef" v-model="valid">
          <!-- Full Name -->
          <v-text-field
            v-model="user.name"
            label="Full Name"
            :rules="[rules.required]"
            variant="outlined"
            density="comfortable"
          />

          <!-- Email -->
          <v-text-field
            v-model="user.email"
            label="Email"
            :rules="[rules.required, rules.email]"
            variant="outlined"
            density="comfortable"
          />

          <!-- Username -->
          <v-text-field
            v-model="user.username"
            label="Username"
            :rules="[rules.required]"
            variant="outlined"
            density="comfortable"
          />

          <!-- Password -->
          <v-text-field
            v-model="user.password"
            label="Password"
            :rules="isEdit ? [] : [rules.required]"
            type="password"
            variant="outlined"
            density="comfortable"
          />

          <!-- Role -->
          <v-select
            v-model="user.role_id"
            label="Role"
            :items="roles"
            item-title="name"
            item-value="id"
            :rules="[rules.required]"
            variant="outlined"
            density="comfortable"
          />

          <!-- Status -->
          <v-select
            v-model="user.status"
            label="Status"
            :items="statusOptions"
            item-title="name"
            item-value="id"
            :rules="[rules.required]"
            variant="outlined"
            density="comfortable"
          />
        </v-form>
      </v-card-text>

      <v-card-actions>
        <v-spacer />
        <v-btn variant="text" @click="closeDialog">Cancel</v-btn>
        <v-btn color="primary" @click="submitForm">
          {{ isEdit ? 'Update' : 'Create' }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoleStore } from '@/stores/roleStore'

/* Props & Emits */
const props = defineProps({
  modelValue: Boolean,
  editedUser: { type: Object, default: () => ({}) }
})

const emit = defineEmits(['update:modelValue', 'save'])

/* Dialog Model */
const model = computed({
  get: () => props.modelValue,
  set: val => emit('update:modelValue', val)
})

/* Edit mode check */
const isEdit = computed(() => !!props.editedUser?.id)

/* User Form State */
const user = ref({
  id: null,
  name: '',
  email: '',
  username: '',
  password: '',
  role_id: null,
  status: null // Start empty
})

/* Roles from store */
const roleStore = useRoleStore()
const roles = computed(() => roleStore.roles.data || [])

/* Status Options (no default selection) */
const statusOptions = [
  { id: 'Active', name: 'Active' },
  { id: 'Inactive', name: 'Inactive' },
]

/* Watch editedUser to populate form */
watch(
  () => props.editedUser,
  val => {
    user.value = {
      id: val?.id || null,
      name: val?.name || '',
      email: val?.email || '',
      username: val?.username || '',
      password: '',
      role_id: val?.role_id || val?.role?.id || null,
      status: val?.status ?? null // empty for new user
    }
  },
  { immediate: true }
)

/* Form validation */
const formRef = ref(null)
const valid = ref(true)
const rules = {
  required: v => !!v || 'This field is required',
  email: v => /.+@.+\..+/.test(v) || 'Email must be valid'
}

/* Submit form (does not close dialog automatically) */
function submitForm() {
  formRef.value?.validate().then(success => {
    if (!success) return
    emit('save', { ...user.value }) // parent handles API
  })
}

/* Close dialog manually */
function closeDialog() {
  emit('update:modelValue', false)
}

/* Fetch roles on mounted */
onMounted(() => {
  roleStore.fetchRoles()
})
</script>
