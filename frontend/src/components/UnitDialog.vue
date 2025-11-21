<template>
  <v-dialog v-model="modelValue"  max-width="550">
    <v-card>
      <v-toolbar
        :title="editItem ? 'Edit Unit' : 'Create Unit'"
        class="bg-primary"
      >
        <v-spacer />
        <v-btn icon="mdi-close" @click="close"></v-btn>
      </v-toolbar>
      <v-card-text>
        <v-row>
          <v-col>
            <v-text-field v-model="localItem.name" label="Unit Name" />
          </v-col>
          <v-col>
            <v-text-field
              v-model="localItem.abbreviation"
              label="Abbreviation"
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

  const props = defineProps({
    modelValue: Boolean,
    editItem: Object
  })

  const emit = defineEmits(['update:modelValue', 'saved'])

  const localItem = ref({
    name: '',
    abbreviation: ''
  })

  watch(
    () => props.editItem,
    val => {
      localItem.value = val ? { ...val } : { name: '', abbreviation: '' }
    },
    { immediate: true }
  )

  const modelValue = computed({
    get: () => props.modelValue,
    set: v => emit('update:modelValue', v)
  })

  const close = () => {
    emit('update:modelValue', false)
  }

  const save = () => {
    console.log('Saved:', localItem.value)

    emit('saved') // parent reload list
    close()
  }
</script>
