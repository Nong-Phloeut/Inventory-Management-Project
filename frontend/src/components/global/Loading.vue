<template>
  <v-overlay
    :model-value="isLoading"
    class="align-center justify-center"
    persistent
  >
    <v-progress-circular
      color="primary"
      size="64"
      class="my-4"
      :indeterminate="true"
    />
  </v-overlay>
</template>

<script>
  import { mapState } from 'pinia'
  import { useLoadingStore } from '@/stores/loading'
  export default {
    name: 'CommonOverlay',
    computed: {
      ...mapState(useLoadingStore, ['isLoading'])
    }
  }
</script>
