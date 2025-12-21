import http from './api' // <-- your axios instance (with baseURL set)

/**
 * Category http service
 */
export default {
  // Get all categories
  linkTelegram(userId) {
    return http.post('/telegram/link', { user_id: userId })
  },
  notifications() {
    return http.get(`/notifications`)
  },
  makeAsRead(readingId) {
    return http.post(`/notifications/${readingId}/read`)
  },
  remove(id) {
    return http.delete(`/notifications/${id}`)
  },
  makeAsReadAll() {
    return http.post(`/notifications/read-all`)
  },
  confirmTelegram() {
    return http.post(`/telegram/link`)
  },
}
