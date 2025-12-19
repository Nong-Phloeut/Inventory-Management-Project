<template>
  <v-navigation-drawer
    :rail="rail"
    permanent
    @click="$emit('update:rail', false)"
  >
    <v-list class="pa-4 mb-4 mt-4">
      <v-list-item>
        <v-img src="logo.png" width="190" contain class="mx-auto" />
      </v-list-item>
    </v-list>
    <v-list
      v-model:opened="open"
      v-for="(link, i) in filteredMenu"
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

<script setup>
  import { ref, computed, watch  } from 'vue'

  const props = defineProps({
    user: Object // user will be passed from parent (Layout.vue)
  })

  const rail = ref(false)
  const open = ref(['dashboard'])

  const menu = ref([
    {
      path: '/dashboard',
      title: 'Dashboard',
      icon: 'mdi-view-dashboard',
      roles: [1, 2, 3]
    },
    {
      path: '/categories',
      title: 'Categories',
      icon: 'mdi-shape-outline',
      roles: [1]
    },
    { path: '/units', title: 'Units', icon: 'mdi-counter', roles: [1] },
    {
      path: '/products',
      title: 'Products',
      icon: 'mdi-package-variant',
      roles: [1, 2]
    },
    {
      path: '/suppliers',
      title: 'Suppliers',
      icon: 'mdi-truck-delivery',
      roles: [1]
    },
    {
      path: '/stocks',
      title: 'Stocks',
      icon: 'mdi-swap-horizontal',
      roles: [1, 2]
    },
    {
      path: '/purchases',
      title: 'Purchases',
      icon: 'mdi-cart-arrow-down',
      roles: [1, 2, 3]
    },
    {
      title: 'Administration',
      icon: 'mdi-account-cog',
      roles: [1],
      subLinks: [
        {
          path: '/roles-management',
          title: 'Roles',
          icon: 'mdi-shield-account',
          roles: [1]
        },
        {
          path: '/users-management',
          title: 'Users',
          icon: 'mdi-account',
          roles: [1]
        }
      ]
    },
    {
      title: 'Reports',
      icon: 'mdi-chart-line',
      roles: [1, 2],
      subLinks: [
        {
          path: '/inventory-reports',
          title: 'Inventory',
          icon: 'mdi-warehouse',
          roles: [1, 2]
        },
        {
          path: '/purchase-reports',
          title: 'Purchase',
          icon: 'mdi-cart-arrow-down',
          roles: [1, 2, 3]
        }
      ]
    },
    {
      path: '/audit-logs',
      title: 'Audit logs',
      icon: 'mdi-timeline-clock-outline',
      roles: [1]
    }
  ])

  // Filter menu based on user role
  const filteredMenu = computed(() => {
    if (!props.user) return []

    return menu.value
      .map(link => {
        // Check if main link is allowed
        if (!link.roles || link.roles.includes(props.user.role_id)) {
          // If it has subLinks, filter subLinks as well
          if (link.subLinks) {
            const filteredSubs = link.subLinks.filter(
              s => !s.roles || s.roles.includes(props.user.role_id)
            )
            return { ...link, subLinks: filteredSubs }
          }
          return link
        }
        return null
      })
      .filter(link => link !== null)
  })

  watch(rail, newVal => {
    if (newVal) {
      open.value = [] // close all groups
    } else {
      open.value = ['dashboard'] // optionally reopen default group
    }
  })
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
