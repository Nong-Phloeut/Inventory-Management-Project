
import { aiApi } from './aiApi'

export default {
  // AI
  getForecast(productId) {
    return aiApi.get(`/forecast/${productId}`)
  },

  getReorderSuggestion(productId) {
    return aiApi.get(`/reorder-suggestion/${productId}`)
  }
}
