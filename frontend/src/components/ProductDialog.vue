<template>
  <v-dialog v-model="internalOpen" max-width="600">
    <v-card>
       <v-toolbar
        :title="form.id ? 'Edit Product' : 'Add Product'"
        class="bg-primary"
      >
        <v-spacer />
        <v-btn icon="mdi-close" @click="close"></v-btn>
      </v-toolbar>
      <v-card-text>
        <v-form ref="formRef" v-model="isValid">
          <v-text-field
            v-model="form.name"
            label="Name"
            :rules="[rules.required]"
            required
          />
          <v-select
            v-model="form.category_id"
            :items="categoryStore.categories"
            item-title="name"
            item-value="id"
            label="Category"
            :rules="[rules.required]"
          />
          <v-text-field v-model="form.sku" label="SKU" />
          <v-text-field v-model="form.price" label="Price" type="number" />
        </v-form>
      </v-card-text>

      <v-card-actions>
        <v-spacer />
        <v-btn text @click="close">Cancel</v-btn>
        <v-btn color="primary" :disabled="!isValid" @click="submit">Save</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
  import { ref, watch, onMounted } from 'vue'
  import { useCategoryStore } from '@/stores/categoryStore'
  const categoryStore = useCategoryStore()

  const props = defineProps({
    isOpen: Boolean,
    product: { type: Object, default: null }
  })
  const emit = defineEmits(['update:isOpen', 'save'])

  const internalOpen = ref(false)
  const formRef = ref(null)
  const isValid = ref(false)

  const form = ref({
    id: null,
    name: '',
    sku: '',
    price: 0
  })

  const rules = {
    required: v => !!v || 'This field is required'
  }
  // 🧹 Reset form
  const resetForm = () => {
    form.value = { id: null, name: '', sku: '', price: 0 }
    isValid.value = false
    formRef.value?.resetValidation()
  }
  // 🔄 Sync with parent
  watch(
    () => props.isOpen,
    val => {
      internalOpen.value = val
      if (val) {
        // opening dialog → load product if editing, otherwise reset
        if (props.product) {
          form.value = { ...props.product }
        } else {
          resetForm()
        }
      }
    },
    { immediate: true }
  )

  // 🔄 Emit back
  watch(internalOpen, val => emit('update:isOpen', val))

  const close = () => {
    internalOpen.value = false
    resetForm()
  }

  const submit = async () => {
    const ok = await formRef.value?.validate()
    if (ok) {
      emit('save', { ...form.value })
      close() // close & reset after successful save
    }
  }

  onMounted(() => categoryStore.fetchCategories())
</script>
