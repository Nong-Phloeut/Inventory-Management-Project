import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useStockMovementStore = defineStore('stockMovement', () => {
  const stockMovements = ref([
    { id: 1, productName: 'Laptop', type: 'IN', quantity: 10, date: '2025-09-01' },
    { id: 2, productName: 'Mouse', type: 'OUT', quantity: 5, date: '2025-09-05' }
  ])

  const addMovement = (movement) => {
    movement.id = Date.now()
    stockMovements.value.push(movement)
  }

  const updateMovement = (movement) => {
    const index = stockMovements.value.findIndex(m => m.id === movement.id)
    if (index !== -1) stockMovements.value[index] = movement
  }

  const deleteMovement = (id) => {
    stockMovements.value = stockMovements.value.filter(m => m.id !== id)
  }

  return { stockMovements, addMovement, updateMovement, deleteMovement }
})
