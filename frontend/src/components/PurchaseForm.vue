<template>
  <custom-title>
    <v-btn
      size="x-small"
      icon="mdi-arrow-left"
      class="white mr-2"
      @click="goBack"
      variant="tonal"
    ></v-btn>

    <strong>{{ isEdit ? 'Edit Purchase' : 'Add Purchase' }}</strong>
  </custom-title>

  <v-container fluid class="pa-0">
    <v-form ref="formRef" v-model="isValid">
      <!-- WRAP FORM IN CARD -->
      <v-card rounded="lg" elevation="0" class="pa-4 mb-4">
        <v-card-title class="text-h6 font-weight-bold pb-2">
          Purchase Information
        </v-card-title>

        <v-divider />
        <v-row class="mt-4">
          <v-col cols="12" sm="6" md="3">
            <v-select
              label="Supplier"
              :items="supplierStore.suppliers.data"
              v-model="purchase.supplier_id"
              item-title="name"
              item-value="id"
              :rules="[v => !!v || 'Supplier is required']"
            />
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <v-date-input
              v-model="purchase.purchase_date"
              label="Purchase Date"
              :rules="[v => !!v || 'Date is required']"
              :min="today"
            />
          </v-col>
          <!-- <v-col cols="12" sm="6" md="3">
            <v-select
              label="Purchase Status"
              v-model="purchase.status"
              :items="purchaseStore.statuses"
              item-title="label"
              item-value="code"
            />
          </v-col> -->

          <v-col cols="12" sm="6" md="3">
            <v-select
              label="Payment Status"
              v-model="purchase.payment_status"
              :items="['unpaid', 'partial', 'paid']"
            />
          </v-col>
        </v-row>
      </v-card>

      <!-- ITEMS CARD -->
      <v-card rounded="lg" elevation="0" class="pa-4">
        <v-card-title class="text-h6 font-weight-bold pb-2">
          Item List
        </v-card-title>

        <v-divider />
        <v-row class="mt-3" dense>
          <v-col cols="12">
            <v-row
              v-for="(item, index) in purchase.items"
              :key="index"
              dense
              class="mb-2"
            >
              <v-col cols="12" md="2">
                <v-select
                  :items="filteredProducts"
                  v-model="item.product_id"
                  item-title="name"
                  item-value="id"
                  label="Product"
                  density="compact"
                  :rules="[v => !!v || 'Product is required']"
                />
              </v-col>

              <v-col cols="12" md="1">
                <v-text-field
                  v-model.number="item.quantity"
                  type="number"
                  label="Quantity"
                  density="compact"
                  min="1"
                  :rules="[v => v > 0 || 'Quantity must be > 0']"
                />
              </v-col>

              <v-col cols="12" md="2">
                <v-text-field
                  v-model.number="item.cost_price"
                  type="number"
                  label="Cost Price"
                  density="compact"
                  min="0"
                  prefix="$"
                  :readonly="true"
                  :disabled="!item.product_id"
                  :rules="[v => v >= 0 || 'Price must be ≥ 0']"
                />
              </v-col>
              <v-col cols="12" md="2">
                <v-text-field
                  v-model.number="item.item_tax"
                  type="number"
                  suffix="%"
                  label="Tax"
                  density="compact"
                  min="0"
                  :rules="[v => v === null || v >= 0 || 'Tax must be ≥ 0']"
                />
              </v-col>

              <v-col cols="12" md="2">
                <v-text-field
                  v-model.number="item.item_discount"
                  type="number"
                  label="Discount"
                  suffix="%"
                  density="compact"
                  min="0"
                  :rules="[v => v === null || v >= 0 || 'Discount must be ≥ 0']"
                />
              </v-col>
              <v-col cols="12" md="2">
                <v-text-field
                  :model-value="getItemTotal(index)"
                  prefix="$"
                  type="text"
                  label="Total"
                  density="compact"
                  min="0"
                  :readonly="true"
                  :disabled="!item.product_id"
                />
              </v-col>

              <v-col cols="12" md="1">
                <v-btn
                  icon="mdi-delete"
                  color="error"
                  variant="text"
                  size="small"
                  @click="removeItem(index)"
                />
              </v-col>
            </v-row>
          </v-col>
        </v-row>
        <v-row>
          <v-col cols="12" sm="6" md="8">
            <v-textarea label="Note" v-model="purchase.note"></v-textarea>
          </v-col>
          <v-col cols="12" sm="8" md="4">
            <v-sheet border rounded="lg" class="pa-4">
              <div class="mb-2">Summary</div>

              <div class="d-flex justify-space-between mb-1">
                <span>Subtotal:</span>
                <strong>{{ formatCurrency(subtotal) }}</strong>
              </div>

              <div class="d-flex justify-space-between mb-1">
                <span>Total Discount Amount:</span>
                <strong>{{ formatCurrency(totalDiscount) }}</strong>
              </div>

              <div class="d-flex justify-space-between mb-1">
                <span>Total Tax Amount:</span>
                <strong>{{ formatCurrency(totalTax) }}</strong>
              </div>

              <v-divider class="my-2" />

              <div class="d-flex justify-space-between">
                <h3 class="font-weight-bold">Total Amount:</h3>
                <h3>{{ formatCurrency(totalAmount) }}</h3>
              </div>
            </v-sheet>
          </v-col>
        </v-row>

        <v-divider class="my-3" />

        <!-- ACTION BUTTONS -->
        <v-row>
          <v-col>
            <v-btn text color="primary" @click="addItem">+ Add Product</v-btn>
          </v-col>

          <v-col class="text-end">
            <v-btn
              v-if="hasRole(3)"
              color="primary"
              @click="savePurchase(false)"
              :disabled="purchase.items.length <= 0 || !isValid"
              class="me-2"
            >
              {{ isEdit ? 'Update Draft' : 'Save Draft' }}
            </v-btn>

            <v-btn
              v-if="hasRole(3)"
              color="success"
              @click="savePurchase(true)"
              :disabled="purchase.items.length <= 0 || !isValid"
            >
              Request Approval
            </v-btn>

            <!-- Manager buttons -->
            <v-btn
              v-if="hasRole(2)"
              color="primary"
              class="me-3"
              @click="approvePurchase"
            >
              Approve
            </v-btn>

            <v-btn v-if="hasRole(2)" color="error" @click="rejectPurchase">
              Reject
            </v-btn>
          </v-col>
        </v-row>
      </v-card>
    </v-form>
  </v-container>
</template>

<script setup>
  import { reactive, ref, computed, onMounted, watch } from 'vue'
  import { useRoute, useRouter } from 'vue-router'
  import { useSupplierStore } from '@/stores/supplierStore'
  import { useProductStore } from '@/stores/productStore'
  import { usePurchaseStore } from '@/stores/purchaseStore'
  import { useI18n } from 'vue-i18n'
  import { useAppUtils } from '@/composables/useAppUtils'
  import { useCurrency } from '@/composables/useCurrency.js'
  import { useDate } from '@/composables/useDate'
  import { usePurchaseCalculator } from '@/composables/usePurchaseCalculator'
  import { usePermission } from '@/composables/usePermission'
  import { PURCHASE_STATUSES } from '@/constants/purchaseStatuses.js'

  // ------------------------------
  // Composables & Utils
  // ------------------------------
  const { hasRole } = usePermission()
  const { formatDate, formatDateTime, formatLocalDate } = useDate()
  const { formatCurrency, formatCurrencyNoSymbol } = useCurrency()
  const { t } = useI18n()
  const { confirm, notif } = useAppUtils()

  const route = useRoute()
  const router = useRouter()

  const supplierStore = useSupplierStore()
  const productStore = useProductStore()
  const purchaseStore = usePurchaseStore()

  // ------------------------------
  // Refs & Reactive State
  // ------------------------------
  const formRef = ref(null)
  const isValid = ref(false)

  const purchase = reactive({
    id: null,
    supplier_id: null,
    purchase_date: new Date(),
    purchase_status_code: PURCHASE_STATUSES.DRAFT,
    payment_status: 'unpaid',
    note: '',
    tax: 0,
    discount: 0,
    items: [{ product_id: null, quantity: 1, cost_price: 0 }]
  })
  // today's date in YYYY-MM-DD format
  const today = new Date().toISOString().split('T')[0]

  const { itemTotals, subtotal, totalDiscount, totalTax, totalAmount } =
    usePurchaseCalculator(purchase)

  // ------------------------------
  // Computed Properties
  // ------------------------------
  const isEdit = computed(() => !!purchase.id)

  const filteredProducts = computed(() => {
    if (!purchase.supplier_id) return []

    return productStore.products.data.filter(p => {
      // include product if it belongs to selected supplier
      if (p.supplier_id === purchase.supplier_id) return true

      // or include if product is already selected in any item
      return purchase.items.some(item => item.product_id === p.id)
    })
  })

  // ------------------------------
  // Watchers
  // ------------------------------
  watch(
    () => purchase.items,
    items => {
      items.forEach(item => {
        if (item.product_id) {
          const product = productStore.products.data.find(
            p => p.id === item.product_id
          )
          if (product) item.cost_price = product.price
        }
      })
    },
    { deep: true }
  )

  watch(
    () => purchase.supplier_id,
    (newVal, oldVal) => {
      if (oldVal && newVal !== oldVal) {
        purchase.items.forEach(item => {
          // only reset if the product is not from the new supplier
          const product = productStore.products.data.find(
            p => p.id === item.product_id
          )
          if (product && product.supplier_id !== newVal) {
            item.product_id = null
          }
        })
      }
    }
  )

  // ------------------------------
  // Lifecycle Hooks
  // ------------------------------
  onMounted(async () => {
    await loadInitialData()
    if (route.params.id) await loadPurchase(route.params.id)
    else addItem()
  })

  // ------------------------------
  // Methods
  // ------------------------------

  async function loadInitialData() {
    await supplierStore.fetchSuppliers({ status: 1, per_page: -1 })
    await productStore.fetchProducts({ status: 'active', per_page: -1 })
    await purchaseStore.fetchStatuses()
  }

  async function loadPurchase(id) {
    await purchaseStore.fetchPurchaseById(id)
    Object.assign(purchase, purchaseStore.purchase)
  }

  function addItem() {
    purchase.items.push({
      product_id: null,
      quantity: 1,
      cost_price: 0,
      item_discount: 0,
      item_tax: 0
    })
  }

  function removeItem(index) {
    purchase.items.splice(index, 1)
  }

  function getItemTotal(index) {
    return formatCurrencyNoSymbol(itemTotals.value[index]?.total || 0)
  }

  async function savePurchase(requestApproval = false) {
    // 1️⃣ Validate form
    const { valid } = await formRef.value.validate()
    if (!valid) return

    // 5️⃣ Determine status
    const status = requestApproval
      ? PURCHASE_STATUSES.REQUEST
      : PURCHASE_STATUSES.DRAFT

    // 2️⃣ Format date
    const date =
      purchase.purchase_date instanceof Date
        ? formatLocalDate(purchase.purchase_date)
        : purchase.purchase_date

    // 3️⃣ Prepare payload
    const payload = {
      ...purchase,
      purchase_status_code: status,
      subtotal: subtotal.value,
      total_discount: totalDiscount.value,
      total_tax: totalTax.value,
      total_amount: totalAmount.value,
      purchase_date: date
    }

    // 4️⃣ Prevent editing received purchase
    if (isEdit.value && purchase.status === PURCHASE_STATUSES.RECEIVED) {
      return notif('Cannot edit a received purchase.', {
        type: 'error',
        color: 'error'
      })
    }

    // 6️⃣ Save purchase
    if (isEdit.value) {
      await purchaseStore.updatePurchase(purchase.id, payload)
      notif(
        requestApproval
          ? t('messages.saved_success') + ' & requested approval'
          : t('messages.updated_success'),
        { type: 'success', color: 'primary' }
      )
    } else {
      await purchaseStore.addPurchase(payload)
      notif(
        requestApproval
          ? t('messages.saved_success') + ' & requested approval'
          : t('messages.saved_success'),
        { type: 'success', color: 'primary' }
      )
    }

    router.push('/purchases')
  }

  const approvePurchase = async () => {
    await purchaseStore.updateStatus(purchase.id, 'approved')
    notif('Purchase approved', { type: 'success', color: 'primary' })
    router.push('/purchases')
  }

  const rejectPurchase = async () => {
    await purchaseStore.updateStatus(purchase.id, 'rejected')
    notif('Purchase rejected', { type: 'error', color: 'error' })
    router.push('/purchases')
  }

  function goBack() {
    router.push('/purchases')
  }
</script>
