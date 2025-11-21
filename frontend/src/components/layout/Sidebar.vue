<template>
  <v-navigation-drawer
    :rail="rail"
    :model-value="drawer"
    permanent
    @click="$emit('update:rail', false)"
    @update:model-value="$emit('update:drawer', $event)"
  >
    <v-list>
      <v-list-item>
        <v-img
          src="https://cdn-icons-png.flaticon.com/512/7527/7527289.png"
          width="90"
          contain
          class="mx-auto"
        />
      </v-list-item>
    </v-list>

    <v-list dense>
      <v-list-item
        v-for="(item, index) in menu"
        :key="index"
        link
        :to="item.path"
        exact
        color="primary"
      >
        <template v-slot:prepend>
          <v-icon :icon="item.icon"></v-icon>
        </template>
        <v-list-item-title>{{ item.title }}</v-list-item-title>
      </v-list-item>
    </v-list>

    <template v-slot:append>
      <div class="pa-2 text-center">
        <v-btn
          block
          class="text-none bg-primary"
          variant="tonal"
          prepend-icon="mdi-application-outline"
        >
          Version 0.0.1
        </v-btn>
      </div>
    </template>
  </v-navigation-drawer>
</template>

<script>
  export default {
    data: () => ({
      drawer: true,
      rail :false,
      menu: [
        {
          path: '/dashboard',
          title: 'Dashboard',
          icon: 'mdi-view-dashboard'
        },
        {
          path: '/categories',
          title: 'Categories',
          icon: 'mdi-shape-outline'
        },
        {
          path: '/units',
          title: 'Units',
          icon: 'mdi-counter'
        },
        {
          path: '/products',
          title: 'Products',
          icon: 'mdi-package-variant'
        },
        {
          path: '/suppliers',
          title: 'Suppliers',
          icon: 'mdi-truck-delivery'
        },
        {
          path: '/stock-movements',
          title: 'Stock Movements',
          icon: 'mdi-swap-horizontal'
        },
        {
          path: '/purchases',
          title: 'Purchases',
          icon: 'mdi-cart-arrow-down'
        },
        {
          path: '/list-users',
          title: 'User Management',
          icon: 'mdi-account'
        },
        {
          path: '/reports',
          title: 'Report',
          icon: 'mdi-chart-line'
        }
        // {
        //   path: '/sales',
        //   title: 'Sales',
        //   icon: 'mdi-chart-line'
        // }
      ]
    })
  }
</script>
