<template>
  <v-dialog v-model="internalOpen" max-width="500">
    <v-card>
      <v-card-title>
        {{ stock?.id ? 'Edit Stock' : 'Add Stock' }}
      </v-card-title>
      <v-card-text>
        <v-form ref="formRef" v-model="isValid">
          <v-select
            v-model="form.product_id"
            :items="products"
            item-title="name"
            item-value="id"
            label="Product"
            :disabled="!!stock?.id"
            :rules="[rules.required]"
          />
          <v-text-field
            v-model="form.quantity"
            type="number"
            label="Quantity"
            :rules="[rules.required]"
          />
        </v-form>
      </v-card-text>
      <v-card-actions>
        <v-spacer />
        <v-btn text @click="emit('update:isOpen', false)">Cancel</v-btn>
        <v-btn color="primary" :disabled="!isValid" @click="submit">Save</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  isOpen: Boolean,
  stock: Object,
  products: Array
})
const emit = defineEmits(['update:isOpen', 'save'])

const internalOpen = ref(false)
const isValid = ref(false)
const formRef = ref(null)
const form = ref({ product_id: null, quantity: 0 })

const rules = {
  required: v => !!v || 'Required'
}

watch(() => props.isOpen, val => {
  internalOpen.value = val
  if (val) {
    form.value = props.stock
      ? { ...props.stock }
      : { product_id: null, quantity: 0 }
  }
}, { immediate: true })

watch(internalOpen, val => emit('update:isOpen', val))

const submit = () => {
  if (!formRef.value.validate()) return
  emit('save', { ...form.value })
  emit('update:isOpen', false)
}
</script>
