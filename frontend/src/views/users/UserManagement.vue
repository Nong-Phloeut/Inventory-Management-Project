<template>
  <custom-title icon="mdi-account-group">
    User Management
    <template #right>
      <BaseButton icon="mdi-plus" @click="openDialog">Add Employee</BaseButton>
    </template>
  </custom-title>
  <v-container fluid class="pa-0">
    <v-data-table
      :items="employeeStore.employees"
      :headers="headers"
      :loading="employeeStore.loading"
      class="mt-4"
    >
      <template #item.name="{ item }">
        {{ item.first_name }} {{ item.last_name }}
      </template>

      <template #item.actions="{ item }">
        <v-btn size="small" variant="tonal" icon @click="edit(item)">
          <v-icon>mdi-pencil</v-icon>
        </v-btn>
        <v-btn
          size="small"
          variant="tonal"
          icon
          color="red"
          class="ms-2"
          @click="remove(item.id)"
        >
          <v-icon>mdi-delete</v-icon>
        </v-btn>
      </template>
    </v-data-table>

    <!-- Dialog for create/edit -->
    <employee-dialog
      v-model="dialog"
      :employee="selectedEmployee"
      @save="saveEmployee"
    />
  </v-container>
</template>

<script setup>
  import { ref, onMounted } from 'vue'
  import { useEmployeeStore } from '@/stores/employeeStore'
  import EmployeeDialog from '@/components/EmployeeDialog.vue'

  const employeeStore = useEmployeeStore()

  const dialog = ref(false)
  const selectedEmployee = ref(null)

  const headers = [
    { title: 'ID', key: 'employee_id' },
    { title: 'Name', key: 'name' },
    { title: 'Email', key: 'email' },
    { title: 'Phone', key: 'phone' },
    { title: 'Department', key: 'department' },
    { title: 'Job Title', key: 'job_title' },
    { title: 'Status', key: 'status' },
    { title: 'Actions', key: 'actions', sortable: false }
  ]

  onMounted(() => {
    employeeStore.fetchEmployees()
  })

  function openDialog() {
    selectedEmployee.value = null
    dialog.value = true
  }

  function edit(emp) {
    selectedEmployee.value = { ...emp }
    dialog.value = true
  }

  async function saveEmployee(employee) {
    if (employee.id) {
      await employeeStore.updateEmployee(employee.id, employee)
    } else {
      await employeeStore.createEmployee(employee)
    }
  }

  async function remove(id) {
    if (confirm('Are you sure you want to delete this employee?')) {
      await employeeStore.deleteEmployee(id)
    }
  }
</script>
