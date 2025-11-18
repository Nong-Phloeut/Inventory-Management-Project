<template>
  <sidebar
    :drawer="drawer"
    @update:drawer="drawer = $event"
    :rail="rail"
    @update:rail="rail = $event"
  />
  <app-bar @toggle="toggleRail" />
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
      drawer: true,
       rail: false,
    }),

    methods: {
      toggleNav() {
        this.drawer = !this.drawer
      },
      toggleRail() {
        this.rail = !this.rail
      }
    }
  }
</script>
