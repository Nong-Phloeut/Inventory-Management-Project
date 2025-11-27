import http from './api' // <-- your axios instance (with baseURL set)

/**
 * Category http service
 */
export default {
  // Get all categories
  getAll() {
    return http.get('/categories')
  },

  export(){
    
  }
}
