import { defineStore } from 'pinia'

export const useAuditLogStore = defineStore('auditLogStore', {
  state: () => ({
    logs: [
      {
        id: 1,
        username: 'alexis',
        action: 'LOGIN',
        description: 'User logged in',
        created_at: '2025-11-25 10:25'
      },
      {
        id: 2,
        username: 'admin',
        action: 'DELETE',
        description: 'Removed a product',
        created_at: '2025-11-25 11:10'
      },
      {
        id: 3,
        username: 'teacher',
        action: 'UPDATE',
        description: 'Updated classroom data',
        created_at: '2025-11-26 09:20'
      }
    ],
    filters: {
      keyword: '',
      startDate: null,
      endDate: null
    }
  }),

  getters: {
    filteredLogs(state) {
      return state.logs.filter(log => {
        const matchKeyword =
          state.filters.keyword === '' ||
          log.username.toLowerCase().includes(state.filters.keyword.toLowerCase()) ||
          log.action.toLowerCase().includes(state.filters.keyword.toLowerCase()) ||
          log.description.toLowerCase().includes(state.filters.keyword.toLowerCase())

        const created = new Date(log.created_at)
        const start = state.filters.startDate ? new Date(state.filters.startDate) : null
        const end = state.filters.endDate ? new Date(state.filters.endDate) : null

        const matchDate =
          (!start || created >= start) &&
          (!end || created <= end)

        return matchKeyword && matchDate
      })
    }
  },

  actions: {
    setFilter(key, value) {
      this.filters[key] = value
    },

    exportCSV() {
      const headers = ['ID', 'Username', 'Action', 'Description', 'Date']
      const rows = this.filteredLogs.map(log => [
        log.id,
        log.username,
        log.action,
        log.description,
        log.created_at
      ])

      let csvContent =
        'data:text/csv;charset=utf-8,' +
        [headers.join(','), ...rows.map(row => row.join(','))].join('\n')

      const link = document.createElement('a')
      link.href = encodeURI(csvContent)
      link.download = 'AuditLogs.csv'
      link.click()
    }
  }
})
