import { defineStore } from 'pinia'
import employeeService from '../api/employee'

export const useEmployeeStore = defineStore('employee', {
  state: () => ({
    employees: [],
    loading: false,
    error: null
  }),

  actions: {
    async fetchEmployees() {
      const res = await employeeService.getAll()
      this.employees = res
    },

    async createEmployee(employee) {
      await employeeService.create(employee)
      this.fetchEmployees()
    },

    async updateEmployee(id, employee) {
      await employeeService.update(id, employee)
      this.fetchEmployees()
    },

    async deleteEmployee(id) {
      await employeeService.remove(id)
      this.fetchEmployees()
    }
  }
})
