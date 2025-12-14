<template>
  <v-navigation-drawer
    :rail="rail"
    :model-value="drawer"
    permanent
    @click="$emit('update:rail', false)"
    @update:model-value="$emit('update:drawer', $event)"
  >
    <v-list class="pa-4 mb-4 mt-4">
      <v-list-item>
        <v-img src="logo.png" width="190" contain class="mx-auto" />
      </v-list-item>
    </v-list>
    <v-list
      v-model:opened="open"
      v-for="(link, i) in menu"
      :key="link.title"
      dense
      class="pa-0"
    >
      <v-list-item
        v-if="!link.subLinks"
        :key="i"
        :to="link.path"
        class="v-list-item"
        exact
      >
        <template v-slot:prepend>
          <v-icon :icon="link.icon"></v-icon>
        </template>
        <v-list-item-title>
          {{ link.title }}
        </v-list-item-title>
      </v-list-item>

      <v-list-group v-else :key="link.title" no-action class="pa-0">
        <template v-slot:activator="{ props }">
          <v-list-item v-bind="props">
            <template v-slot:prepend>
              <v-icon :icon="link.icon"></v-icon>
            </template>
            <v-list-item-title class="back-title">
              {{ link.title }}
            </v-list-item-title>
          </v-list-item>
        </template>

        <v-list-item
          v-for="sublink in link.subLinks"
          :to="sublink.path"
          :key="sublink.title"
          exact
        >
          <template v-slot:prepend>
            <v-icon :icon="sublink.icon"></v-icon>
          </template>
          <v-list-item-title>
            {{ sublink.title }}
          </v-list-item-title>
        </v-list-item>
      </v-list-group>
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
      rail: false,
      open: ['dashboard'],
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
          path: '/stocks',
          title: 'Stocks',
          icon: 'mdi-swap-horizontal'
        },
        {
          path: '/purchases',
          title: 'Purchases',
          icon: 'mdi-cart-arrow-down'
        },
        {
          title: 'Administration',
          icon: 'mdi-account-cog',
          subLinks: [
            {
              path: '/roles-management',
              title: 'Roles',
              icon: 'mdi-shield-account'
            },
            {
              path: '/users-management',
              title: 'Users',
              icon: 'mdi-account'
            }
          ]
        },
        {
          path: '/reports',
          title: 'Report',
          icon: 'mdi-chart-line'
        },
        {
          path: '/audit-logs',
          title: 'Audit logs',
          icon: 'mdi-timeline-clock-outline'
        }
      ]
    })
  }
</script>
<style>
  .v-list-group__items {
    margin-left: -35px;
  }
  .v-navigation-drawer__content {
    height: 100%;
    overflow-y: auto;
    overflow-x: hidden;
  }
  /* Hide scrollbar for Webkit browsers */
  .v-navigation-drawer__content::-webkit-scrollbar {
    display: none;
  }

  /* Hide scrollbar for Firefox */
  .v-navigation-drawer__content {
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none; /* Firefox */
  }
  .v-list-item-title .back-title {
    font-size: 15px !important;
  }
  .v-list-item__append {
    display: initial !important;
    align-items: unset !important;
  }
</style>
