<template>
  <custom-title>
    <v-btn
      size="x-small"
      icon="mdi-arrow-left"
      class="white mr-2"
      @click="goBack"
      variant="tonal"
    ></v-btn>

    <strong>
      {{ isEdit ? 'Edit Purchase' : 'Add Purchase' }}
    </strong>
  </custom-title>

  <v-container fluid>
    <v-form ref="formRef">
      <v-row>
        <v-col cols="4">
          <v-select
            label="Supplier"
            :items="supplierStore.suppliers"
            v-model="purchase.supplier_id"
            item-title="name"
            item-value="id"
            :rules="[v => !!v || 'Supplier is required']"
          />
        </v-col>

        <v-col cols="4">
          <v-date-input
            v-model="purchase.purchase_date"
            format="YYYY-MM-DD"
            label="Purchase Date"
            :rules="[v => !!v || 'Date is required']"
          />
        </v-col>
      </v-row>

      <!-- STATUS, TAX, DISCOUNT -->
      <v-row>
        <v-col cols="3">
          <v-select
            label="Status"
            v-model="purchase.status"
            :items="['draft', 'ordered', 'received', 'cancelled']"
          />
        </v-col>

        <v-col cols="3">
          <v-select
            label="Payment Status"
            v-model="purchase.payment_status"
            :items="['unpaid', 'partial', 'paid']"
          />
        </v-col>

        <v-col cols="3">
          <v-text-field
            label="Tax"
            v-model.number="purchase.tax"
            type="number"
            min="0"
            density="compact"
          />
        </v-col>

        <v-col cols="3">
          <v-text-field
            label="Discount"
            v-model.number="purchase.discount"
            type="number"
            min="0"
            density="compact"
          />
        </v-col>
      </v-row>

      <v-divider class="my-3" />

      <!-- PURCHASE ITEMS -->
      <v-row v-for="(item, index) in purchase.items" :key="index" dense>
        <v-col cols="12" md="4">
          <v-select
            :items="productStore.products.data"
            v-model="item.product_id"
            item-title="name"
            item-value="id"
            label="Product"
            density="compact"
            :rules="[v => !!v || 'Product is required']"
          />
        </v-col>

        <v-col cols="12" md="3">
          <v-text-field
            v-model.number="item.quantity"
            type="number"
            label="Quantity"
            density="compact"
            min="1"
            :rules="[v => v > 0 || 'Quantity must be > 0']"
          />
        </v-col>

        <v-col cols="12" md="3">
          <v-text-field
            v-model.number="item.cost_price"
            type="number"
            label="Cost Price"
            density="compact"
            min="0"
            :rules="[v => v >= 0 || 'Price must be ≥ 0']"
          />
        </v-col>

        <v-col cols="12" md="2">
          <v-btn
            icon="mdi-delete"
            color="error"
            variant="text"
            @click="removeItem(index)"
          />
        </v-col>
      </v-row>

      <v-btn text color="primary" @click="addItem">+ Add Product</v-btn>

      <!-- TOTALS DISPLAY -->
      <v-row class="mt-5">
        <v-col class="text-end">
          <h3>Subtotal: {{ subtotal }}</h3>
          <h3>Tax: {{ purchase.tax }}</h3>
          <h3>Discount: {{ purchase.discount }}</h3>
          <h2 class="font-weight-bold">Total: {{ totalAmount }}</h2>
        </v-col>
      </v-row>

      <!-- ACTION BUTTONS -->
      <v-row class="mt-4">
        <v-col class="text-end">
          <v-btn variant="tonal" class="me-2" @click="goBack">Cancel</v-btn>

          <v-btn color="primary" @click="save">
            {{ isEdit ? 'Update Purchase' : 'Save Purchase' }}
          </v-btn>
        </v-col>
      </v-row>
    </v-form>
  </v-container>
</template>

<script setup>
  import { reactive, ref, computed, onMounted } from 'vue'
  import { useRoute, useRouter } from 'vue-router'
  import { useSupplierStore } from '@/stores/supplierStore'
  import { useProductStore } from '@/stores/productStore'
  import { usePurchaseStore } from '@/stores/purchaseStore'

  const route = useRoute()
  const router = useRouter()

  const supplierStore = useSupplierStore()
  const productStore = useProductStore()
  const purchaseStore = usePurchaseStore()

  const formRef = ref(null)

  const purchase = reactive({
    id: null,
    supplier_id: null,
    purchase_date: null,
    status: 'draft',
    payment_status: 'unpaid',
    tax: 0,
    discount: 0,
    items: []
  })

  const isEdit = computed(() => !!purchase.id)

  onMounted(async () => {
    await supplierStore.fetchSuppliers()
    await productStore.fetchProducts()

    if (route.params.id) {
      const data = await purchaseStore.fetchPurchaseById(route.params.id)
      Object.assign(purchase, JSON.parse(JSON.stringify(data)))
    } else {
      addItem()
    }
  })

  function addItem() {
    purchase.items.push({
      product_id: null,
      quantity: 1,
      cost_price: 0
    })
  }

  function removeItem(i) {
    purchase.items.splice(i, 1)
  }

  const subtotal = computed(() =>
    purchase.items.reduce((s, i) => s + i.quantity * i.cost_price, 0)
  )

  const totalAmount = computed(() =>
    (subtotal.value + Number(purchase.tax) - Number(purchase.discount)).toFixed(
      2
    )
  )

  async function save() {
    const { valid } = await formRef.value.validate()
    if (!valid) return

    const date =
      purchase.purchase_date instanceof Date
        ? purchase.purchase_date.toISOString().slice(0, 10)
        : purchase.purchase_date

    const payload = {
      ...purchase,
      subtotal: subtotal.value,
      total_amount: totalAmount.value,
      purchase_date: date
    }

    if (isEdit.value) {
      await purchaseStore.updatePurchase(purchase.id, payload)
    } else {
      await purchaseStore.addPurchase(payload)
    }

    router.push('/purchases')
  }

  function goBack() {
    router.push('/purchases')
  }
</script>
