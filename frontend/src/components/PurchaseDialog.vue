<template>
  <v-dialog
    :model-value="modelValue"
    @update:model-value="val => emit('update:modelValue', val)"
    max-width="900"
  >
    <v-card>
      <v-toolbar
        :title="isEdit ? 'Edit Purchase' : 'Add Purchase'"
        class="bg-primary"
      >
        <v-spacer />
        <v-btn icon="mdi-close" @click="close"></v-btn>
      </v-toolbar>

      <v-card-text>
        <v-form ref="formRef" v-model="valid">
          <v-row>
            <v-col cols="6">
              <v-select
                label="Supplier"
                :items="supplierStore.suppliers"
                v-model="purchase.supplier_id"
                item-title="name"
                item-value="id"
                :rules="[v => !!v || 'Supplier is required']"
              />
            </v-col>
            <v-col cols="6">
              <v-date-input
                v-model="purchase.purchase_date"
                format="YYYY-MM-DD"
                label="Purchase Date"
                :rules="[v => !!v || 'Date is required']"
              />
            </v-col>
          </v-row>

          <v-divider class="my-3" />

          <v-row v-for="(item, index) in purchase.items" :key="index">
            <v-col cols="12" md="4">
              <v-select
                :items="productStore.products"
                v-model="item.product_id"
                item-title="name"
                item-value="id"
                label="Product"
                class="me-2"
                :rules="[v => !!v || 'Product is required']"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="item.quantity"
                type="number"
                label="Quantity"
                class="me-2"
                min="1"
                :rules="[v => v > 0 || 'Quantity must be > 0']"
              />
            </v-col>
            <v-col cols="12" md="3">
              <v-text-field
                v-model.number="item.price"
                type="number"
                label="Price"
                class="me-2"
                min="0"
                :rules="[v => v >= 0 || 'Price must be ≥ 0']"
              />
            </v-col>
            <v-col cols="2" md="2">
              <v-btn icon color="red" @click="removeItem(index)">
                <v-icon>mdi-delete</v-icon>
              </v-btn>
            </v-col>
          </v-row>

          <v-btn text color="primary" @click="addItem">+ Add Product</v-btn>

          <v-row class="mt-3">
            <v-col class="text-end">
              <h3>Total: {{ totalAmount.toFixed(2) }}</h3>
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>

      <v-card-actions>
        <v-spacer />
        <v-btn text @click="close">Cancel</v-btn>
        <v-btn color="primary" @click="save" :disabled="!valid">
          {{ isEdit ? 'Update' : 'Save' }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
  import { reactive, computed, watch, ref, onMounted } from 'vue'
  import { useSupplierStore } from '@/stores/supplierStore'
  import { useProductStore } from '@/stores/productStore'
  import { defineProps, defineEmits } from 'vue'

  const props = defineProps({
    modelValue: Boolean,
    purchase: Object
  })

  const emit = defineEmits(['update:modelValue', 'save'])

  const formRef = ref(null)
  const valid = ref(false)

  // Default reactive purchase
  const localPurchase = reactive({
    id: null,
    supplier_id: null,
    purchase_date: null,
    items: []
  })

  // Pinia stores
  const supplierStore = useSupplierStore()
  const productStore = useProductStore()

  // Fetch suppliers and products from stores
  onMounted(() => {
    supplierStore.fetchSuppliers()
    productStore.fetchProducts()
  })
  // Watch for props changes
  watch(
    () => props.purchase,
    newVal => {
      if (newVal) {
        // Merge existing purchase for editing
        Object.assign(localPurchase, newVal)
        localPurchase.items = newVal.items?.length ? newVal.items : []
      } else {
        // Reset to empty for new purchase
        Object.assign(localPurchase, {
          id: null,
          supplier_id: null,
          purchase_date: null,
          items: []
        })
      }
    },
    { immediate: true, deep: true }
  )

  // Computed flags
  const isEdit = computed(() => !!localPurchase.id)

  // Total amount
  const totalAmount = computed(() =>
    localPurchase.items.reduce((sum, i) => sum + (i.quantity * i.price || 0), 0)
  )

  // Item management
  function addItem() {
    localPurchase.items.push({ product_id: null, quantity: 1, price: 0 })
  }

  function removeItem(index) {
    localPurchase.items.splice(index, 1)
  }

  // Save
  async function save() {
    const { valid: isValid } = await formRef.value.validate()
    if (!isValid) return

    // Ensure date format YYYY-MM-DD
    const formattedDate =
      localPurchase.purchase_date instanceof Date
        ? localPurchase.purchase_date.toISOString().slice(0, 10)
        : localPurchase.purchase_date.split('T')[0]

    emit('save', { ...localPurchase, purchase_date: formattedDate })
    close()
  }

  function close() {
    emit('update:modelValue', false)
  }

  const purchase = localPurchase
</script>
