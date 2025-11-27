import { defineStore } from 'pinia'
import auditLogService from '../api/auditLog'

export const useAuditLogStore = defineStore('auditLogStore', {
  state: () => ({
    logs: [],
  }),

  actions: {
    getAllAuditLogs() {
     auditLogService.getAll()
    },
    exportCSV() {
     auditLogService.export()
    }
  }
})
