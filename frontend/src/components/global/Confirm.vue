<template>
  <v-dialog v-model="dialog" :max-width="options.width" @keydown.esc="cancel">
    <v-card :width="options.width" rounded="lg">
      <v-card-title :class="`bg-${options.type}`">
        <strong>{{ title }}</strong>
      </v-card-title>
      <v-card-text
        v-show="!!message"
        class="capitalize-first-letter pt-6 pb-4"
        v-html="message"
      />
      <v-card-actions class="pa-4">
        <v-spacer />
        <v-btn elevation="0" ref="btnNo" @click.native="cancel" variant="tonal">
          {{ $t('btn.cancel') }}
        </v-btn>
        <v-btn elevation="0" class="bg-red" @click.native="agree">
          {{ $t('btn.yes') }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
  export default {
    name: 'ConfirmDialog',
    data() {
      return {
        dialog: false,
        agreeCallback: null,
        cancelCallback: null,
        message: null,
        title: null,
        options: {
          type: 'error',
          width: 290,
          agreeBtnText: this.$t('btn.delete'),
          denyBtnText: this.$t('btn.cancel')
        }
      }
    },
    methods: {
      bgColor() {
        const colors = {
          info: '#233F740F',
          error: '#FF52520F',
          warning: '#FFC1070F'
        }

        return colors[this.options.type || 'info']
      },
      open({ title, message, options, agree = () => {}, cancel = () => {} }) {
        this.dialog = true
        this.title = title
        this.message = message
        this.options = Object.assign(this.options, options)
        this.agreeCallback = agree
        this.cancelCallback = cancel
      },
      async agree() {
        await this.agreeCallback()
        this.dialog = false
      },
      async cancel() {
        await this.cancelCallback()
        this.dialog = false
      }
    }
  }
</script>
