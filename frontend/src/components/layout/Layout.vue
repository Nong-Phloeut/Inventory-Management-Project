<template>
  <sidebar v-model="drawer"/>
  <app-bar @toggle="toggleNav"/>
  <v-main>
    <v-container class="px-4" fluid>
      <router-view />
    </v-container>
  </v-main>
</template>
<script>
  import AppBar from './AppBar.vue'
  import Sidebar from './Sidebar.vue'

  export default {
    name: 'Layout',
    components: {
      AppBar,
      Sidebar
    },
    data: () => ({
      drawer: true
    }),

    methods: {
      toggleNav() {
        this.drawer = !this.drawer
      }
    }
  }
</script>
