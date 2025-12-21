import { defineStore } from 'pinia'
import notificationService from '../api/notification'

export const useNotificationStore = defineStore('notification', {
  state: () => ({
    notifications: []
  }),

  actions: {
    async notificationsLink(userID) {
      await notificationService.linkTelegram(userID)
    },
    async fetchNotifications() {
      const res = await notificationService.notifications()
      this.notifications = res.data
    },
    async makeAsRead(readingId) {
      await notificationService.makeAsRead(readingId)
    },
    async remove(id) {
      await notificationService.remove(id)
    },
    async makeAsReadAll() {
      await notificationService.makeAsReadAll()
    }
  }
})
