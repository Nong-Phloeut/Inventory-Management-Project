<template>
  <v-navigation-drawer v-model="drawer">
    <template v-slot:prepend>
      <v-list>
        <v-list-item>
          <h1>Logo</h1>
          <v-img class="image" rounded></v-img>
        </v-list-item>
      </v-list>
    </template>

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
          title: 'Employees',
          icon: 'mdi-account'
        },
        {
          path: '/reports',
          title: 'Report',
          icon: 'mdi-chart-line'
        },
        {
          path: '/sales',
          title: 'Sales',
          icon: 'mdi-chart-line'
        }
      ]
    })
  }
</script>
