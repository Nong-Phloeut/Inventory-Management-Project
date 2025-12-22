<template>
  <custom-title icon="mdi-bell-outline">
    Notifications

    <template #right v-if="authStore.me.telegram_chat_id">
      <v-btn
        variant="flat"
        color="primary"
        class="text-none"
        prepend-icon="mdi-check-all"
        @click="markAllAsRead"
      >
        Mark all as read
      </v-btn>

      <!-- <v-btn
        variant="outlined"
        color="grey"
        prepend-icon="mdi-filter-variant"
        class="text-none ms-3"
      >
        Filter
      </v-btn> -->
    </template>
  </custom-title>

  <v-container fluid class="pa-0">
    <!-- Telegram not linked -->
    <v-card
      v-if="!authStore.me.telegram_chat_id"
      class="mx-auto rounded-xl text-center"
      elevation="0"
    >
      <v-card-text class="pa-10">
        <v-avatar color="blue-lighten-5" size="90" class="mb-4">
          <v-icon color="blue-darken-1" size="44">mdi-send</v-icon>
        </v-avatar>

        <h2 class="font-weight-bold mb-2">Stay Connected</h2>
        <p class="text-grey-darken-1 mb-6">
          Link your Telegram to receive
          <strong>real-time notifications</strong>
          about purchases, stock, and approvals.
        </p>

        <v-btn
          size="large"
          color="primary"
          class="text-none font-weight-bold rounded-lg"
          prepend-icon="mdi-send"
          @click="linkTelegram"
        >
          Connect Telegram Bot
        </v-btn>
      </v-card-text>
    </v-card>

    <!-- Notifications -->
    <template v-else>
      <v-card
        v-if="notifications.data && notifications.data.length"
        variant="flat"
      >
        <v-list lines="two" class="py-0">
          <v-list-item
            v-for="item in notifications.data"
            :key="item.id"
            class="notification-item"
            :class="{ unread: !item.read }"
            @click="!item.read && makeAsRead(item.id)"
          >
            <!-- Icon -->
            <template #prepend>
              <v-avatar
                size="44"
                :color="item.read ? 'grey-lighten-3' : item.color"
                variant="tonal"
              >
                <v-icon
                  :icon="item.icon"
                  :color="item.read ? 'grey' : item.color"
                />
              </v-avatar>
            </template>

            <!-- Content -->
            <v-list-item-title
              class="notification-title"
              :class="!item.read ? 'font-weight-bold' : 'text-grey-darken-1'"
            >
              {{ item.title }}
            </v-list-item-title>

            <v-list-item-subtitle class="notification-message">
              {{ item.message }}
            </v-list-item-subtitle>

            <!-- Meta -->
            <template #append>
              <div class="notification-meta">
                <span class="text-time">{{ item.time }}</span>

                <v-menu>
                  <template #activator="{ props }">
                    <v-btn
                      icon="mdi-dots-vertical"
                      variant="text"
                      size="small"
                      v-bind="props"
                      class="menu-btn"
                      @click.stop
                    />
                  </template>

                  <v-list density="compact" rounded="lg">
                    <v-list-item @click="makeAsRead(item.id)">
                      <v-icon start size="18">mdi-check</v-icon>
                      Mark as read
                    </v-list-item>

                    <v-list-item @click="remove(item.id)">
                      <v-icon start size="18" color="error">mdi-delete</v-icon>
                      <span class="text-error">Delete</span>
                    </v-list-item>
                  </v-list>
                </v-menu>
              </div>
            </template>
          </v-list-item>
        </v-list>
      </v-card>

      <!-- Empty state -->
      <div v-else class="text-center pa-10">
        <v-icon size="70" color="grey-lighten-2" class="mb-4">
          mdi-bell-off-outline
        </v-icon>
        <p class="text-grey-darken-1">
          You're all caught up 🎉<br />
          No notifications right now.
        </p>
      </div>
    </template>
  </v-container>
</template>

<script setup>
import { onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notificationStore'

const authStore = useAuthStore()
const notificationStore = useNotificationStore()

onMounted(() => {
  notificationStore.fetchNotifications()
})

const notifications = computed(() => notificationStore.notifications)

const linkTelegram = () => {
  const botUsername = 'IVMAlertBot'
  const token = authStore.me.name
  window.open(`https://t.me/${botUsername}?start=${token}`, '_blank')
}

const makeAsRead = id => {
  notificationStore.makeAsRead(id)
  notificationStore.fetchNotifications()
  authStore.fetchMe()
}

const markAllAsRead = () => {
  notificationStore.makeAsReadAll()
  notificationStore.fetchNotifications()
  authStore.fetchMe()
}

const remove = id => {
  notificationStore.remove(id)
  notificationStore.fetchNotifications()
  authStore.fetchMe()
}
</script>

<style scoped>
.notification-item {
  border-left: 4px solid transparent;
  cursor: pointer;
  transition: all 0.2s ease;
}

.notification-item:hover {
  background-color: rgba(33, 150, 243, 0.05);
}

.notification-item.unread {
  background-color: rgba(33, 150, 243, 0.08);
  border-left-color: #2196f3;
}

.notification-title {
  font-size: 15px;
}

.notification-message {
  font-size: 13px;
  opacity: 0.9;
}

.notification-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
}

.text-time {
  font-size: 11px;
  color: #9e9e9e;
}

.menu-btn {
  opacity: 0.5;
}

.notification-item:hover .menu-btn {
  opacity: 1;
}
</style>
