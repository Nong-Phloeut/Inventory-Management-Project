<template>
  <v-dialog v-model="internalModel" max-width="500px">
    <v-card>
      <v-card-title>{{ actionTypeLabel }} Stock</v-card-title>
      <v-card-text>
        <v-text-field
          label="Quantity"
          v-model.number="form.quantity"
          type="number"
          :rules="[v => v > 0 || 'Quantity must be greater than 0']"
        />
        <v-textarea label="Note" v-model="form.note" />
      </v-card-text>

      <v-card-actions>
        <v-spacer />
        <v-btn text @click="close">Cancel</v-btn>
        <v-btn color="primary" @click="submit">Submit</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
  import { ref, watch, computed } from 'vue'

  const props = defineProps({
    modelValue: Boolean,
    actionType: { type: String, required: true },
    stock: Object
  })

  const emit = defineEmits(['update:modelValue', 'submitAction'])

  // ✅ Local ref for v-model
  const internalModel = ref(props.modelValue)
  watch(
    () => props.modelValue,
    val => (internalModel.value = val)
  )
//   watch(internalModel, val => emit('update:modelValue', val))
  watch(internalModel, val => {
    emit('update:modelValue', val)
    if (val && props.stock) {
      form.value.quantity = props.stock.quantity // initialize with current stock
      form.value.note = '' // optional: reset note
    }
  })

  const form = ref({ quantity: 0, note: '' })

  const actionTypeLabel = computed(
    () => props.actionType.charAt(0).toUpperCase() + props.actionType.slice(1)
  )

  function close() {
    internalModel.value = false
  }

  function submit() {
    if (form.value.quantity <= 0) return
    emit('submitAction', { ...form.value, stock: props.stock })
    close()
  }
</script>
