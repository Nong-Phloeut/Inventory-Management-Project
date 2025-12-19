<template>
  <custom-title icon="mdi-bell-outline">
    Notifications
    <template #right>
      <v-btn
        variant="tonal"
        color="primary"
        class="text-none me-3"
        @click="markAllAsRead"
      >
        Mark all as read
      </v-btn>
      <v-btn variant="outlined" color="grey" prepend-icon="mdi-filter-variant">
        Filter
      </v-btn>
    </template>
  </custom-title>
  <v-container fluid class="pa-0">
    <v-card variant="flat" border class="rounded-lg">
      <v-list lines="two" class="py-0">
        <v-list-item
          v-for="item in notifications"
          :key="item.id"
          :class="!item.read ? 'unread' : ''"
        >
          <template #prepend>
            <v-avatar size="44" :color="item.color" variant="tonal">
              <v-icon :icon="item.icon" />
            </v-avatar>
          </template>

          <v-list-item-title class="font-weight-medium">
            {{ item.title }}
          </v-list-item-title>
          <v-list-item-subtitle>
            {{ item.message }}
          </v-list-item-subtitle>

          <template #append>
            <div class="text-right">
              <div class="text-time text-medium-emphasis">
                {{ item.time }}
              </div>
              <v-menu>
                <template #activator="{ props }">
                  <v-btn
                    icon="mdi-dots-vertical"
                    variant="text"
                    size="small"
                    v-bind="props"
                  />
                </template>
                <v-list density="compact">
                  <v-list-item @click="markAsRead(item)">
                    <v-list-item-title>Mark as read</v-list-item-title>
                  </v-list-item>
                  <v-list-item @click="remove(item.id)">
                    <v-list-item-title class="text-error">
                      Delete
                    </v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>
            </div>
          </template>
        </v-list-item>
      </v-list>
    </v-card>
  </v-container>
</template>

<script setup>
  import { ref } from 'vue'

  const notifications = ref([
    {
      id: 1,
      title: 'Low Stock Alert',
      message: 'Product "Wireless Mouse" is below minimum stock.',
      time: '5 min ago',
      icon: 'mdi-alert-circle-outline',
      color: 'error',
      read: false
    },
    {
      id: 2,
      title: 'Purchase Completed',
      message: 'Purchase order #PO-2034 has been received.',
      time: '2 hours ago',
      icon: 'mdi-cart-check',
      color: 'success',
      read: false
    },
    {
      id: 3,
      title: 'New Supplier Added',
      message: 'Supplier "Tech Asia Co., Ltd" was added.',
      time: 'Yesterday',
      icon: 'mdi-account-plus-outline',
      color: 'primary',
      read: true
    }
  ])

  const markAsRead = item => {
    item.read = true
  }

  const markAllAsRead = () => {
    notifications.value.forEach(n => (n.read = true))
  }

  const remove = id => {
    notifications.value = notifications.value.filter(n => n.id !== id)
  }
</script>

<style scoped>
  .text-time {
    font-size: 12px;
  }
  .unread {
    background-color: rgba(33, 150, 243, 0.06);
  }
</style>
