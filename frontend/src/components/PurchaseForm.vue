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
    <!-- WRAP FORM IN CARD -->
    <v-card rounded="lg" elevation="0" class="pa-4 mb-4">
      <v-card-title class="text-h6 font-weight-bold pb-2">
        Purchase Information
      </v-card-title>

      <v-divider />

      <v-form ref="formRef" v-model="isValid" class="mt-4">
        <v-row>
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
            />
          </v-col>
          <v-col cols="12" sm="6" md="3">
            <v-select
              label="Purchase Status"
              v-model="purchase.status"
              :items="['draft', 'ordered', 'received', 'cancelled']"
            />
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <v-select
              label="Payment Status"
              v-model="purchase.payment_status"
              :items="['unpaid', 'partial', 'paid']"
            />
          </v-col>
        </v-row>
      </v-form>
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
                :items="productStore.products.data"
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
          <v-btn variant="tonal" class="me-2" @click="goBack">Cancel</v-btn>
          <v-btn color="primary" @click="save" :disabled="purchase.items.length <= 0">
            {{ isEdit ? 'Update Purchase' : 'Save Purchase' }}
          </v-btn>
        </v-col>
      </v-row>
    </v-card>
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

  const { formatDate, formatDateTime, addDays, formatLocalDate } = useDate()
  const { formatCurrency, formatCurrencyNoSymbol } = useCurrency()
  const { t } = useI18n()
  const { confirm, notif } = useAppUtils()
  const route = useRoute()
  const router = useRouter()

  const supplierStore = useSupplierStore()
  const productStore = useProductStore()
  const purchaseStore = usePurchaseStore()

  const formRef = ref(null)
  const isValid = ref(false)

  const purchase = reactive({
    id: null,
    supplier_id: null,
    purchase_date: '',
    status: 'draft',
    payment_status: 'unpaid',
    note: '',
    tax: 0,
    discount: 0,
    items: []
  })
  const { itemTotals, subtotal, totalDiscount, totalTax, totalAmount } =
    usePurchaseCalculator(purchase)

  watch(
    () => purchase.items,
    items => {
      items.forEach(item => {
        if (item.product_id) {
          const product = productStore.products.data.find(
            p => p.id === item.product_id
          )
          if (product) {
            item.cost_price = product.price // auto-fill price
          }
        }
      })
    },
    { deep: true }
  )
  function getItemTotal(index) {
    const total = itemTotals.value[index]?.total || 0

    return formatCurrencyNoSymbol(total)
    // return total
  }

  const isEdit = computed(() => !!purchase.id)

  onMounted(async () => {
    await supplierStore.fetchSuppliers({ status: 1 })
    await productStore.fetchProducts({ status: 'active' })

    if (route.params.id) {
      await purchaseStore.fetchPurchaseById(route.params.id)
      Object.assign(purchase, purchaseStore.purchase)
    } else {
      addItem()
    }
  })

  function addItem() {
    purchase.items.push({
      product_id: null,
      quantity: 1,
      cost_price: 0,
      item_discount: 0,
      item_tax: 0
    })
  }

  function removeItem(i) {
    purchase.items.splice(i, 1)
  }

  async function save() {
    const { valid } = await formRef.value.validate()

    if (!valid) return

    const date =
      purchase.purchase_date instanceof Date
        ? formatLocalDate(purchase.purchase_date) // NO timezone shift
        : purchase.purchase_date

    const payload = {
      ...purchase,
      subtotal: subtotal.value,
      total_discount: totalDiscount.value,
      total_tax: totalTax.value,
      total_amount: totalAmount.value,
      purchase_date: date
    }

    if (isEdit.value) {
      if (purchase.status === 'received') {
        notif('Cannot edit a received purchase.', {
          type: 'error',
          color: 'error'
        })
        return
      }
      await purchaseStore.updatePurchase(purchase.id, payload)
      notif(t('messages.updated_success'), {
        type: 'success',
        color: 'primary'
      })
    } else {
      await purchaseStore.addPurchase(payload)
      notif(t('messages.saved_success'), {
        type: 'success',
        color: 'primary'
      })
    }

    router.push('/purchases')
  }

  function goBack() {
    router.push('/purchases')
  }
</script>
