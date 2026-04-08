<template>
  <div class="checkout-page">
    <StorefrontNavbar :cart-count="cartItems.length" />
    <div class="checkout-wrap">
      <div class="checkout-hero">
        <div class="container">
          <div class="checkout-hero-icon"><span class="material-symbols-outlined">shopping_bag</span></div>
          <div>
            <h1 class="checkout-title">Secure Checkout</h1>
            <p class="checkout-sub">Review pesanan dan selesaikan pembayaran.</p>
          </div>
        </div>
      </div>

      <div class="container">
        <div class="checkout-grid">
          <!-- LEFT -->
          <div class="checkout-left">
            <div class="card">
              <div class="card-header">
                <div class="card-header-left">
                  <span class="material-symbols-outlined card-icon">shopping_cart</span>
                  <h3 class="card-title">Keranjang Belanja</h3>
                </div>
                <span class="item-count">{{ cartItems.length }} item</span>
              </div>
              <div v-if="loadingCart" class="loading-state">
                <div v-for="i in 2" :key="i" class="cart-skeleton"></div>
              </div>
              <div v-else-if="cartItems.length === 0" class="empty-state">
                <span class="material-symbols-outlined">remove_shopping_cart</span>
                <p>Keranjang kamu kosong.</p>
                <router-link to="/" class="btn btn--green btn--sm">Belanja Sekarang</router-link>
              </div>
              <div v-else class="cart-items">
                <div v-for="item in cartItems" :key="item.id" class="cart-item">
                  <div class="item-img">
                    <img v-if="item.obat?.foto1" :src="imageUrl(item.obat.foto1)" :alt="item.obat?.nama_obat" />
                    <span v-else class="material-symbols-outlined">medication</span>
                  </div>
                  <div class="item-info">
                    <p class="item-name">{{ item.obat?.nama_obat }}</p>
                    <div class="item-qty">
                      <button class="qty-btn" @click="updateQty(item, item.jumlah_order - 1)">−</button>
                      <input type="number" class="qty-input" :value="item.jumlah_order" min="1"
                        @change="onQtyInput(item, $event.target.value)" @keyup.enter="$event.target.blur()" />
                      <button class="qty-btn" @click="updateQty(item, item.jumlah_order + 1)">+</button>
                    </div>
                  </div>
                  <div class="item-right">
                    <p class="item-price">{{ formatRp(item.subtotal) }}</p>
                    <button class="remove-btn" @click="removeItem(item.id)">
                      <span class="material-symbols-outlined">delete_outline</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="card">
              <div class="card-header">
                <div class="card-header-left">
                  <span class="material-symbols-outlined card-icon">local_shipping</span>
                  <h3 class="card-title">Jenis Pengiriman</h3>
                </div>
              </div>
              <div v-if="loadingShipping" class="cart-skeleton"></div>
              <div v-else class="shipping-options">
                <label v-for="ship in shippingMethods" :key="ship.id"
                  :class="['shipping-option', { 'shipping-option--active': selectedShipping === ship.id }]">
                  <input v-model="selectedShipping" type="radio" :value="ship.id" class="radio-input" />
                  <div class="shipping-info">
                    <p class="shipping-name">{{ ship.nama_ekspedisi }}</p>
                    <p class="shipping-type">{{ ship.jenis_kirim }}</p>
                  </div>
                  <span class="shipping-price">{{ ship.jenis_kirim === 'standar' ? 'Free' : formatRp(12000) }}</span>
                </label>
              </div>
            </div>
          </div>

          <!-- RIGHT -->
          <div class="checkout-right">
            <div class="summary-card">
              <h3 class="summary-title">Ringkasan Pesanan</h3>
              <div class="summary-rows">
                <div class="summary-row"><span>Subtotal</span><span>{{ formatRp(subtotal) }}</span></div>
                <div class="summary-row"><span>Ongkos Kirim</span><span :class="{ 'free-text': shippingCost === 0 }">{{
                  shippingCost === 0 ? 'Free' : formatRp(shippingCost) }}</span></div>
                <div class="summary-row"><span>Pajak (5%)</span><span>{{ formatRp(tax) }}</span></div>
              </div>
              <div class="summary-total">
                <span>Total</span>
                <span class="total-val">{{ formatRp(totalAmount) }}</span>
              </div>

              <!-- Metode Bayar -->
              <div class="payment-section">
                <p class="payment-label">Metode Pembayaran</p>
                <div v-if="loadingPayment" class="cart-skeleton"></div>
                <div v-else class="payment-options">
                  <label v-for="pm in paymentMethods" :key="pm.id"
                    :class="['payment-option', { 'payment-option--active': selectedPayment === pm.id }]">
                    <input v-model="selectedPayment" type="radio" :value="pm.id" class="radio-input" />
                    <span class="material-symbols-outlined pay-icon">payments</span>
                    <div>
                      <p class="pay-name">{{ pm.metode_pembayaran }}</p>
                      <p class="pay-sub">{{ pm.tempat_bayar }} · {{ pm.no_rekening }}</p>
                    </div>
                  </label>
                </div>
              </div>

              <button class="btn-confirm"
                :disabled="loadingOrder || cartItems.length === 0 || !selectedShipping || !selectedPayment"
                @click="confirmOrder">
                <span v-if="loadingOrder" class="material-symbols-outlined spin">progress_activity</span>
                <span v-else class="material-symbols-outlined">check_circle</span>
                {{ loadingOrder ? orderStepLabel : 'Konfirmasi Pesanan →' }}
              </button>

              <div class="guarantee-note">
                <span class="material-symbols-outlined">verified_user</span>
                <p>Setelah order dikonfirmasi, upload bukti pembayaran di halaman <strong>Order History</strong>.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <transition name="toast">
      <div v-if="toast.show" class="toast" :class="'toast--' + toast.type">
        <span class="material-symbols-outlined">{{ toast.type === 'success' ? 'check_circle' : 'error' }}</span>
        {{ toast.message }}
      </div>
    </transition>
  </div>
</template>

<script>
import StorefrontNavbar from '@/components/storefront/StorefrontNavbar.vue'
import api from '@/services/api.js'

export default {
  name: 'CheckoutPage',
  components: { StorefrontNavbar },
  data() {
    return {
      cartItems: [], shippingMethods: [], paymentMethods: [],
      selectedShipping: null, selectedPayment: null,
      loadingCart: true, loadingShipping: true, loadingPayment: true, loadingOrder: false,
      orderStep: '', // 'creating' | 'detail' | 'clearing'
      toast: { show: false, message: '', type: 'success' },
    }
  },
  computed: {
    subtotal() { return this.cartItems.reduce((s, i) => s + Number(i.subtotal), 0) },
    shippingCost() {
      if (!this.selectedShipping) return 0
      const m = this.shippingMethods.find(s => s.id === this.selectedShipping)
      return m?.jenis_kirim === 'standar' ? 0 : 12000
    },
    tax() { return Math.round(this.subtotal * 0.05) },
    totalAmount() { return this.subtotal + this.shippingCost + this.tax },
    orderStepLabel() {
      const map = {
        creating: '1/3 Membuat nota...',
        detail: '2/3 Menyimpan item...',
        clearing: '3/3 Membersihkan keranjang...',
      }
      return map[this.orderStep] || 'Memproses...'
    },
  },
  mounted() {
    this.fetchCart()
    this.fetchShipping()
    this.fetchPaymentMethods()
  },
  methods: {
    async fetchCart() {
      this.loadingCart = true
      try {
        const res = await api.get('/pelanggan/keranjang')

        // 🔥 FIX DI SINI
        this.cartItems = res.data.keranjang

      } catch (err) {
        console.log(err.response?.data)
        this.cartItems = []
      } finally {
        this.loadingCart = false
      }
    },
    async fetchShipping() {
      this.loadingShipping = true
      try {
        // GET /api/jenis-pengiriman
        const res = await api.get('/jenis-pengiriman')
        this.shippingMethods = res.data
        if (res.data.length > 0) this.selectedShipping = res.data[0].id
      } catch { }
      finally { this.loadingShipping = false }
    },
    async fetchPaymentMethods() {
      this.loadingPayment = true
      try {
        // GET /api/metode-bayar
        const res = await api.get('/metode-bayar')
        this.paymentMethods = res.data
        if (res.data.length > 0) this.selectedPayment = res.data[0].id
      } catch { }
      finally { this.loadingPayment = false }
    },
    async updateQty(item, newQty) {
      if (newQty < 1) return
      try {
        // POST /api/pelanggan/edit-keranjang/{id}
        await api.post(`/pelanggan/edit-keranjang/${item.id}`, { jumlah_order: newQty })
        await this.fetchCart()
      } catch (err) { this.showToast(err.response?.data?.message || 'Gagal update jumlah', 'error') }
    },

    async onQtyInput(item, rawValue) {
      const newQty = parseInt(rawValue, 10)
      if (!newQty || newQty < 1 || newQty === item.jumlah_order) return
      await this.updateQty(item, newQty)
    },
    async removeItem(id) {
      try {
        // DELETE /api/pelanggan/hapus-keranjang/{id}
        await api.delete(`/pelanggan/hapus-keranjang/${id}`)
        await this.fetchCart()
        this.showToast('Item dihapus dari keranjang', 'success')
      } catch { this.showToast('Gagal menghapus item', 'error') }
    },
    async confirmOrder() {
      if (!this.selectedShipping || !this.selectedPayment) return
      const pelanggan = JSON.parse(localStorage.getItem('pelanggan_data') || '{}')
      this.loadingOrder = true
      this.orderStep = 'creating'
      try {
        // STEP 1: Buat nota penjualan → POST /api/penjualan
        const penjualanRes = await api.post('/pelanggan/checkout-sekarang', {
          id_pelanggan: pelanggan.id,
          id_metode_bayar: this.selectedPayment,
          id_jenis_kirim: this.selectedShipping,
          total_bayar: this.totalAmount,
        })

        const idPenjualan = penjualanRes.data?.data?.id

        if (!idPenjualan) {
          throw new Error('ID penjualan tidak ditemukan dari response server.')
        }

        // STEP 2: Buat detail penjualan untuk setiap item keranjang
        // POST /api/detail-penjualan — controller akan otomatis kurangi stok obat
        this.orderStep = 'detail'
        const detailPromises = this.cartItems.map(item =>
          api.post('/pelanggan/detail-penjualan', {
            id_penjualan: idPenjualan,
            id_obat: item.id_obat,
            jumlah_beli: item.jumlah_order,
          })
        )
        await Promise.all(detailPromises)

        // STEP 3: Hapus semua item dari keranjang satu per satu
        // DELETE /api/pelanggan/hapus-keranjang/{id}
        this.orderStep = 'clearing'
        const deletePromises = this.cartItems.map(item =>
          api.delete(`/pelanggan/hapus-keranjang/${item.id}`)
        )
        await Promise.all(deletePromises)

        this.cartItems = []
        this.showToast('Pesanan berhasil dibuat! Silakan upload bukti pembayaran.', 'success')
        setTimeout(() => this.$router.push('/profile?tab=orders'), 1800)

      } catch (err) {
        const msg = err.response?.data?.message || err.message || 'Gagal membuat pesanan.'
        this.showToast(msg, 'error')
      } finally { this.loadingOrder = false; this.orderStep = '' }
    },
    imageUrl(f) { return `${(import.meta.env.VITE_API_URL || 'http://localhost:8000/api').replace('/api', '')}/storage/obat/${f}` },
    formatRp(v) { return 'Rp ' + Number(v).toLocaleString('id-ID') },
    showToast(message, type = 'success') { this.toast = { show: true, message, type }; setTimeout(() => { this.toast.show = false }, 3000) },
  },
}
</script>

<style scoped>
.checkout-page {
  font-family: 'Plus Jakarta Sans', sans-serif;
  background: #0a0a12;
  min-height: 100vh;
}

.checkout-wrap {
  padding-top: 4.5rem;
}

.checkout-hero {
  background: linear-gradient(135deg, #0d1117 0%, #0f2027 100%);
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  padding: 2rem 0;
}

.checkout-hero .container {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.checkout-hero-icon {
  width: 3rem;
  height: 3rem;
  border-radius: 0.75rem;
  background: rgba(74, 222, 128, 0.12);
  display: flex;
  align-items: center;
  justify-content: center;
}

.checkout-hero-icon .material-symbols-outlined {
  color: #4ade80;
  font-size: 1.5rem;
}

.checkout-title {
  font-family: 'Manrope', sans-serif;
  font-size: 1.75rem;
  font-weight: 900;
  color: #fff;
  letter-spacing: -0.03em;
}

.checkout-sub {
  font-size: 0.82rem;
  color: rgba(255, 255, 255, 0.4);
  margin-top: 2px;
}

.container {
  max-width: 1100px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

.checkout-grid {
  display: grid;
  grid-template-columns: 1fr 22rem;
  gap: 1.5rem;
  padding: 2rem 0 4rem;
}

.checkout-left {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.card {
  background: #12151c;
  border: 1px solid rgba(255, 255, 255, 0.07);
  border-radius: 1rem;
  padding: 1.5rem;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.25rem;
}

.card-header-left {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.card-icon {
  font-size: 1.1rem;
  color: #4ade80;
}

.card-title {
  font-family: 'Manrope', sans-serif;
  font-size: 1rem;
  font-weight: 700;
  color: #fff;
}

.item-count {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.35);
  background: rgba(255, 255, 255, 0.06);
  padding: 0.2rem 0.6rem;
  border-radius: 9999px;
}

.cart-items {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.cart-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.cart-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.item-img {
  width: 4rem;
  height: 4rem;
  border-radius: 0.625rem;
  background: rgba(255, 255, 255, 0.05);
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.item-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.item-img .material-symbols-outlined {
  color: rgba(255, 255, 255, 0.2);
  font-size: 1.5rem;
}

.item-info {
  flex: 1;
}

.item-name {
  font-size: 0.875rem;
  font-weight: 700;
  color: #fff;
}

.item-qty {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.qty-btn {
  width: 1.75rem;
  height: 1.75rem;
  border-radius: 50%;
  border: 1px solid rgba(255, 255, 255, 0.15);
  background: rgba(255, 255, 255, 0.05);
  color: #fff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  line-height: 1;
  transition: all 0.15s;
  flex-shrink: 0;
}

.qty-btn:hover {
  background: rgba(74, 222, 128, 0.15);
  border-color: #4ade80;
  color: #4ade80;
}

.qty-input {
  width: 3.5rem;
  height: 1.75rem;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 0.375rem;
  color: #fff;
  font-size: 0.85rem;
  font-weight: 700;
  text-align: center;
  outline: none;
  font-family: inherit;
  -moz-appearance: textfield;
  transition: border 0.15s;
}

.qty-input:focus {
  border-color: #4ade80;
  background: rgba(74, 222, 128, 0.08);
}

.qty-input::-webkit-outer-spin-button,
.qty-input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.item-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.5rem;
}

.item-price {
  font-family: 'Manrope', sans-serif;
  font-size: 0.95rem;
  font-weight: 800;
  color: #fff;
}

.remove-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: rgba(255, 255, 255, 0.2);
  transition: color 0.15s;
  display: flex;
}

.remove-btn:hover {
  color: #f87171;
}

.remove-btn .material-symbols-outlined {
  font-size: 1.1rem;
}

.shipping-options {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.shipping-option {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.875rem 1rem;
  border-radius: 0.75rem;
  border: 1.5px solid rgba(255, 255, 255, 0.07);
  background: rgba(255, 255, 255, 0.03);
  cursor: pointer;
  transition: all 0.15s;
}

.shipping-option--active {
  border-color: #4ade80;
  background: rgba(74, 222, 128, 0.06);
}

.radio-input {
  display: none;
}

.shipping-info {
  flex: 1;
}

.shipping-name {
  font-size: 0.875rem;
  font-weight: 700;
  color: #fff;
}

.shipping-type {
  font-size: 0.7rem;
  color: rgba(255, 255, 255, 0.4);
  text-transform: capitalize;
}

.shipping-price {
  font-size: 0.82rem;
  font-weight: 700;
  color: #4ade80;
}

.summary-card {
  background: #12151c;
  border: 1px solid rgba(255, 255, 255, 0.07);
  border-radius: 1rem;
  padding: 1.5rem;
  position: sticky;
  top: 6rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.summary-title {
  font-family: 'Manrope', sans-serif;
  font-size: 1rem;
  font-weight: 700;
  color: #fff;
}

.summary-rows {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.82rem;
  color: rgba(255, 255, 255, 0.5);
}

.summary-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.07);
  font-size: 0.875rem;
  font-weight: 700;
  color: #fff;
}

.total-val {
  font-family: 'Manrope', sans-serif;
  font-size: 1.4rem;
  font-weight: 900;
  color: #4ade80;
}

.free-text {
  color: #4ade80;
}

.payment-label {
  font-size: 0.72rem;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.4);
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.payment-options {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.payment-option {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 0.875rem;
  border-radius: 0.625rem;
  border: 1.5px solid rgba(255, 255, 255, 0.07);
  background: rgba(255, 255, 255, 0.03);
  cursor: pointer;
  transition: all 0.15s;
}

.payment-option--active {
  border-color: #4ade80;
  background: rgba(74, 222, 128, 0.05);
}

.pay-icon {
  font-size: 1.1rem;
  color: #4ade80;
}

.pay-name {
  font-size: 0.8rem;
  font-weight: 700;
  color: #fff;
}

.pay-sub {
  font-size: 0.68rem;
  color: rgba(255, 255, 255, 0.35);
}

.btn-confirm {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.875rem;
  border: none;
  border-radius: 0.75rem;
  background: #4ade80;
  color: #052e16;
  font-size: 0.9rem;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.2s;
  font-family: inherit;
  box-shadow: 0 4px 20px rgba(74, 222, 128, 0.3);
}

.btn-confirm:hover:not(:disabled) {
  background: #22c55e;
  transform: translateY(-2px);
}

.btn-confirm:disabled {
  opacity: 0.4;
  cursor: not-allowed;
  transform: none;
}

.btn-confirm .material-symbols-outlined {
  font-size: 1.1rem;
}

.guarantee-note {
  display: flex;
  align-items: flex-start;
  gap: 0.6rem;
  padding: 0.875rem;
  border-radius: 0.625rem;
  background: rgba(74, 222, 128, 0.06);
  border: 1px solid rgba(74, 222, 128, 0.15);
}

.guarantee-note .material-symbols-outlined {
  font-size: 1rem;
  color: #4ade80;
  flex-shrink: 0;
  margin-top: 1px;
}

.guarantee-note p {
  font-size: 0.72rem;
  color: rgba(255, 255, 255, 0.5);
  line-height: 1.5;
}

.guarantee-note strong {
  color: rgba(255, 255, 255, 0.7);
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.5rem 1.25rem;
  border: none;
  border-radius: 0.5rem;
  font-weight: 700;
  cursor: pointer;
  font-family: inherit;
  text-decoration: none;
  font-size: 0.8rem;
}

.btn--green {
  background: #4ade80;
  color: #052e16;
}

.cart-skeleton {
  height: 4rem;
  border-radius: 0.625rem;
  background: linear-gradient(90deg, rgba(255, 255, 255, 0.04) 25%, rgba(255, 255, 255, 0.08) 50%, rgba(255, 255, 255, 0.04) 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
  margin-bottom: 0.75rem;
}

@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }

  100% {
    background-position: -200% 0;
  }
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  padding: 2rem;
  color: rgba(255, 255, 255, 0.25);
}

.empty-state .material-symbols-outlined {
  font-size: 2.5rem;
}

.loading-state {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.toast {
  position: fixed;
  bottom: 2rem;
  left: 50%;
  transform: translateX(-50%);
  padding: 0.75rem 1.5rem;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  z-index: 200;
  white-space: nowrap;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
  font-family: 'Plus Jakarta Sans', sans-serif;
}

.toast--success {
  background: #064e3b;
  color: #fff;
}

.toast--error {
  background: #7f1d1d;
  color: #fff;
}

.toast .material-symbols-outlined {
  font-size: 1rem;
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(-50%) translateY(1rem);
}

.spin {
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 768px) {
  .checkout-grid {
    grid-template-columns: 1fr;
  }
}
</style>