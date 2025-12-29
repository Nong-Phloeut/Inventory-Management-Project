<template>
  <v-app-bar color="surface-light" order="1" class="px-4">
    <template v-slot:prepend>
      <v-icon icon="mdi-storefront" color="primary" size="large" />
    </template>
    <v-app-bar-title class="font-weight-black">Quick POS</v-app-bar-title>
    <v-spacer></v-spacer>
    <v-text-field
      v-model="search"
      prepend-inner-icon="mdi-magnify"
      label="Search items..."
      hide-details
      density="compact"
      rounded="lg"
    ></v-text-field>
  </v-app-bar>

  <v-navigation-drawer location="end" name="drawer" permanent width="400">
    <template v-slot:prepend>
      <v-toolbar color="white" flat border="bottom">
        <v-toolbar-title class="font-weight-bold">
          Current Order
        </v-toolbar-title>
        <v-btn
          icon="mdi-delete-sweep-outline"
          color="error"
          variant="text"
          @click="cart = []"
          :disabled="!cart.length"
        ></v-btn>
      </v-toolbar>
    </template>

    <div class="flex-grow-1 overflow-y-auto pa-2">
      <v-empty-state
        v-if="cart.length === 0"
        icon="mdi-cart-outline"
        title="Your cart is empty"
        text="Tap on products to start an order"
        class="mt-10"
      ></v-empty-state>

      <v-list v-else lines="two">
        <v-list-item
          v-for="item in cart"
          :key="item.id"
          class="px-2 mb-2 border rounded-lg"
        >
          <template v-slot:prepend>
            <v-avatar rounded="lg" size="50" :image="item.image_url"></v-avatar>
          </template>

          <v-list-item-title>
            {{ item.name }}
          </v-list-item-title>
          <v-list-item-subtitle class="text-primary font-weight-bold">
            ${{ (item.price * item.qty).toFixed(2) }}
          </v-list-item-subtitle>

          <template v-slot:append>
            <div
              class="d-flex align-center bg-grey-lighten-4 rounded-pill px-1"
            >
              <v-btn
                icon="mdi-minus"
                size="x-small"
                variant="text"
                @click="decrease(item)"
              ></v-btn>
              <span class="mx-2 font-weight-bold text-caption">
                {{ item.qty }}
              </span>
              <v-btn
                icon="mdi-plus"
                size="x-small"
                variant="text"
                @click="increase(item)"
              ></v-btn>
              <v-btn
                icon="mdi-delete-outline"
                size="x-small"
                color="error"
                variant="text"
                @click="remove(item)"
              />
            </div>
          </template>
        </v-list-item>
      </v-list>
    </div>

    <template v-slot:append>
      <v-sheet class="pa-5" rounded="xl" elevation="3" border>
        <!-- Header -->
        <div class="d-flex align-center mb-4">
          <v-icon icon="mdi-cash-register" color="primary" class="mr-2" />
          <v-list-item-title class="text-h6 font-weight-bold">
            Payment Summary
          </v-list-item-title>
        </div>

        <!-- Payment Method -->
        <v-select
          v-model="paymentMethod"
          :items="[
            { title: 'Cash', value: 'cash' },
            { title: 'Card', value: 'card' },
            { title: 'QR Payment', value: 'qr' }
          ]"
          label="Payment Method"
          prepend-inner-icon="mdi-credit-card-outline"
          density="compact"
          variant="outlined"
          rounded="lg"
          hide-details
          class="mb-4"
        />

        <!-- Price Breakdown -->
        <v-sheet class="pa-3 bg-grey-lighten-4 rounded-lg">
          <div class="d-flex justify-space-between text-body-2 mb-2">
            <span class="text-grey-darken-1">Subtotal</span>
            <span>${{ subtotal.toFixed(2) }}</span>
          </div>

          <div class="d-flex justify-space-between text-body-2 mb-2">
            <span class="text-grey-darken-1">Tax (10%)</span>
            <span>${{ tax.toFixed(2) }}</span>
          </div>

          <div class="d-flex justify-space-between text-body-2">
            <span class="text-grey-darken-1">Discount</span>
            <span class="text-error">- ${{ discount.toFixed(2) }}</span>
          </div>
        </v-sheet>

        <v-divider class="my-4" />

        <!-- Total -->
        <div class="d-flex justify-space-between align-center mb-4">
          <span class="text-h6 font-weight-bold">Total</span>
          <span class="text-h5 font-weight-black text-primary">
            ${{ total.toFixed(2) }}
          </span>
        </div>

        <!-- Checkout Button -->
        <v-btn
          block
          size="large"
          color="primary"
          elevation="6"
          rounded="xl"
          prepend-icon="mdi-check-circle-outline"
          @click="handleCheckout"
          :disabled="cart.length === 0"
        >
          PROCESS PAYMENT
        </v-btn>
      </v-sheet>
    </template>
  </v-navigation-drawer>

  <v-main>
    <v-container>
      <v-row>
        <v-col
          v-for="product in filteredProducts"
          :key="product.id"
          cols="6"
          sm="4"
          lg="3"
        >
          <v-card
            hover
            class="product-card rounded-lg overflow-hidden"
            @click="addToCart(product)"
          >
            <v-img
              :src="product.image_url"
              height="200px"
              cover
              class="bg-grey-lighten-2"
            >
              <!-- <template v-slot:placeholder>
                <v-row class="fill-height ma-0" align="center" justify="center">
                  <v-progress-circular indeterminate color="grey-lighten-5" />
                </v-row>
              </template> -->
            </v-img>

            <v-card-text class="pa-3">
              <div class="text-subtitle-1 font-weight-bold text-truncate">
                {{ product.name }}
              </div>
              <div class="d-flex justify-space-between align-center mt-1">
                <span class="text-primary font-weight-black text-h6">
                  ${{ product.price }}
                </span>
                <v-chip
                  size="x-small"
                  :color="product.stock < 10 ? 'error' : 'grey'"
                >
                  Qty: {{ product.stock }}
                </v-chip>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </v-main>
  <v-dialog
    v-model="showQRDialog"
    fullscreen
    max-width="500px"
    transition="dialog-bottom-transition"
  >
    <v-card class="pa-4 pa-sm-8">
      <v-toolbar color="transparent" density="compact">
        <v-spacer />
        <v-btn
          icon="mdi-close"
          variant="text"
          @click="showQRDialog = false"
        ></v-btn>
      </v-toolbar>

      <v-card-item class="text-center pt-0">
        <v-card-title class="text-h5 font-weight-bold mb-2">
          Payment QR Code
        </v-card-title>
        <v-card-subtitle class="text-wrap">
          Scan this code with your banking app to pay
        </v-card-subtitle>
      </v-card-item>

      <v-card-text class="d-flex flex-column align-center mt-4">
        <div class="bg-primary-lighten-5 rounded-pill px-6 py-2 mb-6">
          <span class="text-subtitle-1 text-medium-emphasis">
            Total Amount:
          </span>
          <span class="text-h5 font-weight-black text-primary ml-2">
            ${{ total.toFixed(2) }}
          </span>
        </div>

        <v-sheet rounded="lg" class="pa-3 mb-6 bg-white" elevation="0">
          <v-img
            :src="
              qrCodeUrl ||
              'https://www.masskh.com/wp-content/uploads/2023/11/photo1700496472-568x800.jpeg'
            "
            width="400"
            height="400"
            aspect-ratio="1/1"
            class="rounded-md"
          >
            <template v-slot:placeholder>
              <v-row class="fill-height ma-0" align="center" justify="center">
                <v-progress-circular
                  indeterminate
                  color="primary"
                ></v-progress-circular>
              </v-row>
            </template>
          </v-img>
        </v-sheet>

        <!-- <div class="d-flex align-center mb-6">
          <v-progress-circular indeterminate size="18" width="2" color="primary" class="mr-3"></v-progress-circular>
          <span class="text-body-2 text-medium-emphasis">Waiting for payment confirmation...</span>
        </div> -->
      </v-card-text>

      <!-- <v-divider class="mb-4"></v-divider> -->

      <!-- <v-card-actions class="flex-column ga-2">
        <v-btn 
          color="primary" 
          variant="flat" 
          block 
          size="large" 
          rounded="lg" 
          @click="confirmPayment"
        >
          I have paid
        </v-btn>
        
        <v-btn 
          variant="text" 
          block 
          color="medium-emphasis" 
          @click="showQRDialog = false"
        >
          Cancel Transaction
        </v-btn>
      </v-card-actions> -->
    </v-card>
  </v-dialog>
</template>

<script setup>
  import { ref, computed, onMounted } from 'vue'
  import { useSaleStore } from '@/stores/salePOSStore'
  import { useProductStore } from '@/stores/productStore'

  const saleStore = useSaleStore()
  const productStore = useProductStore()
  const search = ref('')
  const paymentMethod = ref(null)

  const cart = ref([])

  const filteredProducts = computed(() => {
    if (productStore.products.data) {
      return productStore.products.data.filter(p =>
        p.name.toLowerCase().includes(search.value.toLowerCase())
      )
    }
  })

  const subtotal = computed(() =>
    cart.value.reduce((s, i) => s + i.price * i.qty, 0)
  )

  const tax = computed(() => subtotal.value * 0)
  const discount = computed(() => (subtotal.value > 500 ? 0 : 0))
  const total = computed(() => subtotal.value + tax.value - discount.value)

  function addToCart(product) {
    const item = cart.value.find(i => i.id === product.id)
    if (item) item.qty++
    else cart.value.push({ ...product, qty: 1 })
  }

  function decrease(item) {
    if (item.qty > 1) item.qty--
    else cart.value = cart.value.filter(i => i.id !== item.id)
  }

  function increase(item) {
    item.qty++
  }
  function remove(item) {
    cart.value = cart.value.filter(i => i.id !== item.id)
  }

  const showQRDialog = ref(false)
  const qrCodeUrl = ref('')

  async function handleQRPayment() {
    try {
      const saleData = {
        items: cart.value.map(i => ({
          product_id: i.id,
          qty: i.qty,
          price: i.price
        })),
        total: cart.value.reduce((s, i) => s + i.qty * i.price, 0),
        payment_method: 'qr'
      }

      // // Step 1: Request QR code from backend
      // const res = await saleStore.createQRPayment(saleData)
      // qrCodeUrl.value = res.qr_code_url

      // // Step 2: Show modal with QR
      showQRDialog.value = true

      // // Step 3: Poll backend to check if payment completed
      // const interval = setInterval(async () => {
      //   const status = await saleStore.checkQRStatus(res.payment_id)
      //   if (status.paid) {
      //     clearInterval(interval)
      //     showQRDialog.value = false
      //     alert(`Payment completed! Sale ID: ${status.sale_id}`)
      //     cart.value = []
      //     await saleStore.fetchProducts()
      //   }
      // }, 3000) // check every 3s
    } catch (err) {
      alert('QR Payment failed')
    }
  }

  async function handleCheckout() {
    if (!cart.value.length) return alert('Cart is empty!')

    if (paymentMethod.value === 'qr') {
      // Step 1: Generate QR payment
      await handleQRPayment()
      return
    }

    // Step 2: Normal checkout (Cash/Card)
    await handleNormalCheckout()
  }

  async function handleNormalCheckout() {
    try {
      const saleData = {
        items: cart.value.map(i => ({
          product_id: i.id,
          qty: i.qty,
          price: i.price
        })),
        total_amount: cart.value.reduce((s, i) => s + i.qty * i.price, 0),
        payment_method: paymentMethod.value
      }

      const res = await saleStore.checkout(saleData)
      cart.value = []
      await productStore.fetchProducts() // refresh stock
    } catch (err) {
      alert('Checkout failed')
    }
  }

  onMounted(async () => {
    await productStore.fetchProducts()
  })
</script>

<style>
  ::-webkit-scrollbar-thumb {
    background: transparent;
  }

  * {
    scrollbar-width: none;
  }
</style>
