<template>
    <div class="cart-page">
        <div class="nav-spacer"></div>

        <div class="cart-container">
            <!-- ── Page Header ────────────────────────────────────── -->
            <div class="page-header">
                <button class="back-btn" @click="$router.push('/')">
                    <span>←</span> Lanjut Belanja
                </button>
                <div class="header-center">
                    <h1 class="page-title">Keranjang Belanja</h1>
                    <span class="item-count" v-if="!loading">{{ cartItems.length }} item</span>
                </div>
                <div class="header-right"></div>
            </div>

            <!-- ── Loading Skeleton ──────────────────────────────── -->
            <div v-if="loading" class="skeleton-list">
                <div v-for="i in 3" :key="i" class="skeleton-card">
                    <div class="skel skel-img"></div>
                    <div class="skel-info">
                        <div class="skel skel-title"></div>
                        <div class="skel skel-sub"></div>
                        <div class="skel skel-price"></div>
                    </div>
                </div>
            </div>

            <!-- ── Empty State ───────────────────────────────────── -->
            <div v-else-if="cartItems.length === 0" class="empty-state">
                <div class="empty-icon-wrap">
                    <span class="empty-icon">🛒</span>
                    <div class="empty-ring"></div>
                </div>
                <h2>Keranjang Masih Kosong</h2>
                <p>Yuk, tambahkan obat yang kamu butuhkan!</p>
                <button class="btn-shop" @click="$router.push('/')">Mulai Belanja</button>
            </div>

            <!-- ── Main Layout ───────────────────────────────────── -->
            <div v-else class="cart-layout">

                <!-- LEFT: Item List -->
                <div class="cart-items-col">

                    <!-- Select All Bar -->
                    <div class="select-all-bar">
                        <label class="check-label">
                            <input type="checkbox" class="custom-check" :checked="allSelected"
                                @change="toggleSelectAll" />
                            <span class="check-box"></span>
                            <span>Pilih Semua ({{ cartItems.length }})</span>
                        </label>
                        <button v-if="selectedIds.length > 0" class="btn-delete-selected" @click="deleteSelected">
                            Hapus ({{ selectedIds.length }})
                        </button>
                    </div>

                    <!-- Items -->
                    <div class="item-list">
                        <transition-group name="item">
                            <div v-for="item in cartItems" :key="item.id" class="item-card"
                                :class="{ 'item-selected': selectedIds.includes(item.id) }">
                                <!-- Checkbox -->
                                <label class="item-check">
                                    <input type="checkbox" class="custom-check" :value="item.id"
                                        v-model="selectedIds" />
                                    <span class="check-box"></span>
                                </label>

                                <!-- Image -->
                                <div class="item-img-wrap" @click="$router.push('/product/' + item.id_obat)">
                                    <img v-if="item.obat?.foto1" :src="imgUrl(item.obat.foto1)"
                                        :alt="item.obat?.nama_obat" class="item-img" @error="handleImgError" />
                                    <div v-else class="item-img-placeholder">💊</div>
                                    <div class="img-overlay">Lihat Detail</div>
                                </div>

                                <!-- Info -->
                                <div class="item-info">
                                    <div class="item-top">
                                        <div class="item-meta">
                                            <h3 class="item-name" @click="$router.push('/product/' + item.id_obat)">
                                                {{ item.obat?.nama_obat || 'Obat' }}
                                            </h3>
                                            <span v-if="item.obat?.jenis_obat?.jenis" class="item-jenis">
                                                {{ item.obat.jenis_obat.jenis }}
                                            </span>
                                        </div>
                                        <button class="btn-remove" @click="removeItem(item)"
                                            :disabled="deletingId === item.id">
                                            <span v-if="deletingId === item.id" class="mini-spin"></span>
                                            <span v-else>✕</span>
                                        </button>
                                    </div>

                                    <div class="item-bottom">
                                        <div class="price-group">
                                            <span class="item-price">Rp {{ formatPrice(item.harga) }}</span>
                                            <span class="item-subtotal">Subtotal: Rp {{ formatPrice(item.subtotal)
                                                }}</span>
                                        </div>

                                        <!-- Qty Control -->
                                        <div class="qty-control" :class="{ 'qty-updating': updatingId === item.id }">
                                            <button class="qty-btn" @click="decreaseQty(item)"
                                                :disabled="item.jumlah_order <= 1 || updatingId === item.id">−</button>
                                            <span class="qty-val">{{ item.jumlah_order }}</span>
                                            <button class="qty-btn" @click="increaseQty(item)"
                                                :disabled="updatingId === item.id">+</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </transition-group>
                    </div>
                </div>

                <!-- RIGHT: Summary -->
                <div class="summary-col">
                    <div class="summary-card">
                        <h2 class="summary-title">Ringkasan Pesanan</h2>

                        <!-- Selected notice -->
                        <div v-if="selectedIds.length > 0 && selectedIds.length < cartItems.length"
                            class="selected-notice">
                            <span class="notice-dot"></span>
                            {{ selectedIds.length }} dari {{ cartItems.length }} item dipilih
                        </div>

                        <!-- Item rows -->
                        <div class="summary-rows">
                            <div v-if="selectedItems.length === 0" class="summary-empty">
                                Belum ada item dipilih
                            </div>
                            <div v-for="item in selectedItems" :key="item.id" class="summary-row">
                                <span class="summary-name">
                                    {{ item.obat?.nama_obat || 'Obat' }} ×{{ item.jumlah_order }}
                                </span>
                                <span class="summary-price">Rp {{ formatPrice(item.subtotal) }}</span>
                            </div>
                        </div>

                        <div class="summary-divider"></div>

                        <!-- Pilih Pengiriman -->
                        <div class="section-label">Pilih Pengiriman</div>
                        <div v-if="loadingShipping" class="mini-loader">
                            <span class="mini-spin"></span> Memuat kurir...
                        </div>
                        <div v-else-if="shippingOptions.length === 0" class="mini-empty">
                            Tidak ada kurir tersedia
                        </div>
                        <div v-else class="option-list">
                            <label v-for="s in shippingOptions" :key="s.id" class="option-item"
                                :class="{ 'option-active': selectedShipping?.id === s.id }">
                                <input type="radio" :value="s" v-model="selectedShipping" hidden />
                                <div class="option-logo">
                                    <img v-if="s.logo_ekspedisi" :src="imgUrl(s.logo_ekspedisi)" :alt="s.nama_ekspedisi"
                                        class="option-img" @error="handleImgError" />
                                    <span v-else>🚚</span>
                                </div>
                                <div class="option-detail">
                                    <span class="option-name">{{ s.nama_ekspedisi }}</span>
                                    <span class="option-sub">{{ s.jenis_kirim }}</span>
                                </div>
                                <span class="option-check" v-if="selectedShipping?.id === s.id">✓</span>
                            </label>
                        </div>

                        <!-- Pilih Metode Bayar -->
                        <div class="section-label" style="margin-top:16px">Metode Pembayaran</div>
                        <div v-if="loadingPayment" class="mini-loader">
                            <span class="mini-spin"></span> Memuat metode...
                        </div>
                        <div v-else-if="paymentMethods.length === 0" class="mini-empty">
                            Tidak ada metode pembayaran
                        </div>
                        <div v-else class="option-list">
                            <label v-for="m in paymentMethods" :key="m.id" class="option-item"
                                :class="{ 'option-active': selectedPayment?.id === m.id }">
                                <input type="radio" :value="m" v-model="selectedPayment" hidden />
                                <div class="option-logo">
                                    <img v-if="m.url_logo" :src="imgUrl(m.url_logo)" :alt="m.metode_pembayaran"
                                        class="option-img" @error="handleImgError" />
                                    <span v-else>💳</span>
                                </div>
                                <div class="option-detail">
                                    <span class="option-name">{{ m.metode_pembayaran }}</span>
                                    <span class="option-sub">{{ m.tempat_bayar }} · {{ m.no_rekening }}</span>
                                </div>
                                <span class="option-check" v-if="selectedPayment?.id === m.id">✓</span>
                            </label>
                        </div>

                        <div class="summary-divider"></div>

                        <!-- Total -->
                        <div class="total-row">
                            <span class="total-label">Total Pembayaran</span>
                            <span class="total-val">Rp {{ formatPrice(selectedTotal) }}</span>
                        </div>

                        <!-- Checkout Button -->
                        <button class="btn-checkout" :disabled="!canCheckout || checkingOut" @click="proceedCheckout">
                            <span v-if="checkingOut" class="btn-spinner"></span>
                            <span v-else>🛒</span>
                            {{ checkingOut ? 'Memproses...' : 'Buat Pesanan' }}
                        </button>

                        <p v-if="selectedIds.length === 0" class="checkout-hint">Pilih item terlebih dahulu</p>
                        <p v-else-if="!selectedShipping" class="checkout-hint">Pilih metode pengiriman</p>
                        <p v-else-if="!selectedPayment" class="checkout-hint">Pilih metode pembayaran</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast -->
        <Teleport to="body">
            <transition name="toast">
                <div v-if="toast.show" class="toast" :class="'toast-' + toast.type">
                    {{ toast.message }}
                </div>
            </transition>
        </Teleport>
    </div>
</template>

<script>
import api from '@/services/api.js'

export default {
    name: 'CartPage',

    data() {
        return {
            cartItems: [],
            loading: true,
            updatingId: null,
            deletingId: null,
            selectedIds: [],
            shippingOptions: [],
            paymentMethods: [],
            loadingShipping: false,
            loadingPayment: false,
            selectedShipping: null,
            selectedPayment: null,
            checkingOut: false,
            toast: { show: false, message: '', type: 'success' },
        }
    },

    computed: {
        allSelected() {
            return this.cartItems.length > 0 &&
                this.selectedIds.length === this.cartItems.length
        },
        selectedItems() {
            return this.cartItems.filter(i => this.selectedIds.includes(i.id))
        },
        selectedTotal() {
            return this.selectedItems.reduce((sum, i) => sum + Number(i.subtotal || 0), 0)
        },
        canCheckout() {
            return this.selectedIds.length > 0 && !!this.selectedShipping && !!this.selectedPayment
        },
        // Ambil data pelanggan dari localStorage
        // Di LoginPage.vue Anda, pastikan simpan dengan key 'pelanggan_data'
        // contoh: localStorage.setItem('pelanggan_data', JSON.stringify(res.data.pelanggan))
        pelangganData() {
            try {
                return JSON.parse(localStorage.getItem('pelanggan_data') || 'null')
            } catch {
                return null
            }
        },
    },

    mounted() {
        this.fetchCart()
        this.fetchShipping()
        this.fetchPayment()
    },

    methods: {
        // ── Fetch Data ───────────────────────────────────────────────
        async fetchCart() {
            this.loading = true
            try {
                const res = await api.get('/pelanggan/keranjang')
                const raw = res.data
                if (Array.isArray(raw)) {
                    this.cartItems = raw
                } else if (Array.isArray(raw?.data)) {
                    this.cartItems = raw.data
                } else if (Array.isArray(raw?.keranjang)) {
                    this.cartItems = raw.keranjang
                } else {
                    this.cartItems = []
                }
                this.selectedIds = this.cartItems.map(i => i.id)
            } catch (e) {
                this.showToast('Gagal memuat keranjang', 'error')
                this.cartItems = []
            } finally {
                this.loading = false
            }
        },

        async fetchShipping() {
            this.loadingShipping = true
            try {
                // GET /jenis-pengiriman (public)
                const res = await api.get('/jenis-pengiriman')
                this.shippingOptions = res.data
            } catch {
                // silent
            } finally {
                this.loadingShipping = false
            }
        },

        async fetchPayment() {
            this.loadingPayment = true
            try {
                // GET /metode-bayar (public)
                const res = await api.get('/metode-bayar')
                this.paymentMethods = res.data
            } catch {
                // silent
            } finally {
                this.loadingPayment = false
            }
        },

        // ── Checkbox ─────────────────────────────────────────────────
        toggleSelectAll() {
            this.selectedIds = this.allSelected ? [] : this.cartItems.map(i => i.id)
        },

        // ── Qty ──────────────────────────────────────────────────────
        increaseQty(item) { this.updateQty(item, item.jumlah_order + 1) },
        decreaseQty(item) {
            if (item.jumlah_order <= 1) return
            this.updateQty(item, item.jumlah_order - 1)
        },
        async updateQty(item, newQty) {
            this.updatingId = item.id
            try {
                // POST /pelanggan/edit-keranjang/{id}
                const res = await api.post(`/pelanggan/edit-keranjang/${item.id}`, {
                    jumlah_order: newQty,
                })
                const idx = this.cartItems.findIndex(i => i.id === item.id)
                if (idx !== -1) {
                    this.cartItems[idx] = { ...this.cartItems[idx], ...res.data }
                }
            } catch (e) {
                this.showToast(e.response?.data?.message || 'Gagal update jumlah', 'error')
            } finally {
                this.updatingId = null
            }
        },

        // ── Remove ───────────────────────────────────────────────────
        async removeItem(item) {
            this.deletingId = item.id
            try {
                // DELETE /pelanggan/hapus-keranjang/{id}
                await api.delete(`/pelanggan/hapus-keranjang/${item.id}`)
                this.cartItems = this.cartItems.filter(i => i.id !== item.id)
                this.selectedIds = this.selectedIds.filter(id => id !== item.id)
                this.showToast('Item dihapus dari keranjang', 'success')
            } catch {
                this.showToast('Gagal menghapus item', 'error')
            } finally {
                this.deletingId = null
            }
        },

        async deleteSelected() {
            if (!confirm(`Hapus ${this.selectedIds.length} item dari keranjang?`)) return
            const toDelete = [...this.selectedIds]
            for (const id of toDelete) {
                try {
                    await api.delete(`/pelanggan/hapus-keranjang/${id}`)
                    this.cartItems = this.cartItems.filter(i => i.id !== id)
                } catch { /* skip */ }
            }
            this.selectedIds = []
            this.showToast('Item terpilih berhasil dihapus', 'success')
        },

        // ── Checkout ─────────────────────────────────────────────────
        async proceedCheckout() {
            if (!this.canCheckout) return
            this.checkingOut = true
            try {
                const pelanggan = this.pelangganData
                if (!pelanggan?.id) {
                    this.showToast('Sesi habis, silakan login ulang', 'error')
                    this.$router.push('/login')
                    return
                }

                // POST /penjualan
                await api.post('/penjualan', {
                    id_pelanggan: pelanggan.id,
                    id_metode_bayar: this.selectedPayment.id,
                    id_jenis_kirim: this.selectedShipping.id,
                    total_bayar: this.selectedTotal,
                })

                // Hapus item yang di-checkout dari keranjang
                for (const id of this.selectedIds) {
                    try { await api.delete(`/pelanggan/hapus-keranjang/${id}`) } catch { /* skip */ }
                }

                this.showToast('Pesanan berhasil dibuat!', 'success')
                setTimeout(() => this.$router.push('/profile?tab=orders'), 1500)
            } catch (e) {
                this.showToast(e.response?.data?.message || 'Gagal membuat pesanan', 'error')
            } finally {
                this.checkingOut = false
            }
        },

        // ── Helpers ──────────────────────────────────────────────────
        formatPrice(val) {
            return Number(val || 0).toLocaleString('id-ID')
        },
        imgUrl(filename) {
            if (!filename) return ''
            if (filename.startsWith('http')) return filename
            const base = (import.meta.env.VITE_API_URL || 'http://localhost:8000/api').replace('/api', '')
            return `${base}/storage/${filename}`
        },
        handleImgError(e) { e.target.style.display = 'none' },
        showToast(message, type = 'success') {
            this.toast = { show: true, message, type }
            setTimeout(() => { this.toast.show = false }, 3000)
        },
    },
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap');

.cart-page {
    min-height: 100vh;
    background: #f0f5f1;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.nav-spacer {
    height: 72px;
}

.cart-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 28px 24px 80px;
}

/* Header */
.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
    gap: 12px;
}

.back-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    background: white;
    border: 1.5px solid #e2ebe4;
    color: #2d6a4f;
    padding: 9px 16px;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    transition: all 0.15s;
    white-space: nowrap;
}

.back-btn:hover {
    border-color: #52b788;
    background: #fafffe;
}

.header-center {
    text-align: center;
    flex: 1;
}

.page-title {
    font-size: 22px;
    font-weight: 800;
    color: #1a3329;
    margin: 0 0 4px;
}

.item-count {
    font-size: 12px;
    font-weight: 600;
    color: #52b788;
    background: rgba(82, 183, 136, 0.1);
    padding: 2px 10px;
    border-radius: 99px;
}

.header-right {
    width: 130px;
}

/* Skeleton */
.skeleton-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.skeleton-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    display: flex;
    gap: 16px;
    border: 1.5px solid #e2ebe4;
}

.skel {
    background: linear-gradient(90deg, #e8f0ea 25%, #d4e6d8 50%, #e8f0ea 75%);
    background-size: 200% 100%;
    animation: shimmer 1.4s infinite;
    border-radius: 8px;
}

@keyframes shimmer {
    to {
        background-position: -200% 0;
    }
}

.skel-img {
    width: 90px;
    height: 90px;
    border-radius: 12px;
    flex-shrink: 0;
}

.skel-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 10px;
    padding-top: 4px;
}

.skel-title {
    height: 16px;
    width: 55%;
}

.skel-sub {
    height: 12px;
    width: 35%;
}

.skel-price {
    height: 20px;
    width: 40%;
    margin-top: 8px;
}

/* Empty */
.empty-state {
    text-align: center;
    padding: 80px 20px;
}

.empty-icon-wrap {
    position: relative;
    width: 100px;
    height: 100px;
    margin: 0 auto 28px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.empty-icon {
    font-size: 48px;
    position: relative;
    z-index: 1;
}

.empty-ring {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 2px dashed rgba(82, 183, 136, 0.35);
    animation: spin-slow 8s linear infinite;
}

@keyframes spin-slow {
    to {
        transform: rotate(360deg);
    }
}

.empty-state h2 {
    font-size: 22px;
    font-weight: 800;
    color: #1a3329;
    margin: 0 0 8px;
}

.empty-state p {
    font-size: 14px;
    color: #74a98a;
    margin: 0 0 28px;
}

.btn-shop {
    background: linear-gradient(135deg, #52b788, #2d6a4f);
    color: white;
    border: none;
    padding: 12px 32px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    font-family: inherit;
    box-shadow: 0 4px 20px rgba(82, 183, 136, 0.3);
}

/* Layout */
.cart-layout {
    display: grid;
    grid-template-columns: 1fr 370px;
    gap: 24px;
    align-items: start;
}

/* Select All */
.select-all-bar {
    background: white;
    border: 1.5px solid #e2ebe4;
    border-radius: 12px;
    padding: 13px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.check-label {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13.5px;
    font-weight: 600;
    color: #2d4a39;
    cursor: pointer;
}

.custom-check {
    display: none;
}

.check-box {
    width: 18px;
    height: 18px;
    border-radius: 5px;
    border: 2px solid #b7d9c4;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s;
    flex-shrink: 0;
}

.custom-check:checked+.check-box {
    background: #52b788;
    border-color: #52b788;
}

.custom-check:checked+.check-box::after {
    content: '✓';
    color: white;
    font-size: 11px;
    font-weight: 700;
}

.btn-delete-selected {
    font-size: 12.5px;
    font-weight: 600;
    color: #e05252;
    background: rgba(224, 82, 82, 0.08);
    border: 1px solid rgba(224, 82, 82, 0.2);
    padding: 5px 14px;
    border-radius: 7px;
    cursor: pointer;
    font-family: inherit;
    transition: all 0.15s;
}

.btn-delete-selected:hover {
    background: rgba(224, 82, 82, 0.15);
}

/* Item Cards */
.item-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.item-card {
    background: white;
    border: 1.5px solid #e2ebe4;
    border-radius: 16px;
    padding: 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    transition: all 0.2s;
}

.item-card:hover {
    border-color: #b7d9c4;
    box-shadow: 0 4px 20px rgba(82, 183, 136, 0.08);
}

.item-card.item-selected {
    border-color: #52b788;
    background: #fafffe;
}

.item-check {
    display: flex;
    align-items: center;
    flex-shrink: 0;
    cursor: pointer;
}

.item-img-wrap {
    width: 90px;
    height: 90px;
    border-radius: 12px;
    overflow: hidden;
    flex-shrink: 0;
    position: relative;
    cursor: pointer;
    background: #f0f5f1;
}

.item-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.item-img-wrap:hover .item-img {
    transform: scale(1.06);
}

.item-img-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
}

.img-overlay {
    position: absolute;
    inset: 0;
    background: rgba(45, 106, 79, 0.65);
    color: white;
    font-size: 11.5px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s;
}

.item-img-wrap:hover .img-overlay {
    opacity: 1;
}

.item-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.item-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
}

.item-meta {
    flex: 1;
    min-width: 0;
}

.item-name {
    font-size: 15px;
    font-weight: 700;
    color: #1a3329;
    margin: 0 0 4px;
    cursor: pointer;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.item-name:hover {
    color: #52b788;
}

.item-jenis {
    font-size: 11px;
    color: #74a98a;
    background: rgba(82, 183, 136, 0.08);
    padding: 2px 8px;
    border-radius: 99px;
    display: inline-block;
}

.btn-remove {
    width: 28px;
    height: 28px;
    border-radius: 7px;
    flex-shrink: 0;
    background: rgba(224, 82, 82, 0.07);
    border: 1px solid rgba(224, 82, 82, 0.15);
    color: #e05252;
    cursor: pointer;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s;
}

.btn-remove:hover {
    background: rgba(224, 82, 82, 0.15);
}

.btn-remove:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

.item-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.price-group {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.item-price {
    font-size: 15px;
    font-weight: 700;
    color: #2d6a4f;
    font-family: 'DM Mono', monospace;
}

.item-subtotal {
    font-size: 11.5px;
    color: #74a98a;
}

.qty-control {
    display: flex;
    align-items: center;
    gap: 4px;
    background: #f0f5f1;
    border-radius: 10px;
    padding: 4px;
    transition: opacity 0.2s;
}

.qty-control.qty-updating {
    opacity: 0.5;
    pointer-events: none;
}

.qty-btn {
    width: 30px;
    height: 30px;
    border-radius: 7px;
    background: white;
    border: 1.5px solid #e2ebe4;
    color: #2d6a4f;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s;
    line-height: 1;
    font-family: inherit;
}

.qty-btn:hover:not(:disabled) {
    border-color: #52b788;
    color: #52b788;
}

.qty-btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

.qty-val {
    min-width: 32px;
    text-align: center;
    font-size: 14px;
    font-weight: 700;
    color: #1a3329;
    font-family: 'DM Mono', monospace;
}

/* Summary */
.summary-col {
    position: sticky;
    top: 100px;
}

.summary-card {
    background: white;
    border: 1.5px solid #e2ebe4;
    border-radius: 20px;
    padding: 24px;
}

.summary-title {
    font-size: 17px;
    font-weight: 800;
    color: #1a3329;
    margin: 0 0 16px;
}

.selected-notice {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: #74a98a;
    background: rgba(82, 183, 136, 0.07);
    padding: 8px 12px;
    border-radius: 8px;
    margin-bottom: 14px;
}

.notice-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #52b788;
    flex-shrink: 0;
}

.summary-rows {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 150px;
    overflow-y: auto;
    padding-right: 2px;
}

.summary-empty {
    font-size: 12.5px;
    color: #b7d9c4;
    text-align: center;
    padding: 16px 0;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    align-items: flex-start;
}

.summary-name {
    font-size: 12.5px;
    color: #5a8070;
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.summary-price {
    font-size: 12.5px;
    color: #2d6a4f;
    font-weight: 600;
    white-space: nowrap;
    font-family: 'DM Mono', monospace;
}

.summary-divider {
    height: 1px;
    background: #e2ebe4;
    margin: 18px 0;
}

.section-label {
    font-size: 11px;
    font-weight: 700;
    color: #74a98a;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    margin-bottom: 10px;
}

.mini-loader {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12.5px;
    color: #74a98a;
    padding: 8px 0;
}

.mini-empty {
    font-size: 12.5px;
    color: #b7d9c4;
    padding: 8px 0;
}

.option-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 200px;
    overflow-y: auto;
}

.option-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1.5px solid #e2ebe4;
    cursor: pointer;
    transition: all 0.15s;
}

.option-item:hover {
    border-color: #b7d9c4;
}

.option-item.option-active {
    border-color: #52b788;
    background: rgba(82, 183, 136, 0.05);
}

.option-logo {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: #f0f5f1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
    overflow: hidden;
}

.option-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.option-detail {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 1px;
}

.option-name {
    font-size: 13px;
    font-weight: 700;
    color: #1a3329;
}

.option-sub {
    font-size: 11px;
    color: #74a98a;
    text-transform: capitalize;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.option-check {
    font-size: 13px;
    font-weight: 700;
    color: #52b788;
    width: 16px;
    text-align: center;
}

.total-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 18px;
}

.total-label {
    font-size: 14px;
    font-weight: 600;
    color: #3d6b50;
}

.total-val {
    font-size: 20px;
    font-weight: 800;
    color: #1a3329;
    font-family: 'DM Mono', monospace;
}

.btn-checkout {
    width: 100%;
    background: linear-gradient(135deg, #52b788, #2d6a4f);
    color: white;
    border: none;
    padding: 14px;
    border-radius: 13px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    font-family: inherit;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 4px 20px rgba(82, 183, 136, 0.3);
    transition: all 0.2s;
}

.btn-checkout:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 6px 28px rgba(82, 183, 136, 0.4);
}

.btn-checkout:disabled {
    opacity: 0.45;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.checkout-hint {
    text-align: center;
    font-size: 11.5px;
    color: #74a98a;
    margin: 8px 0 0;
}

.btn-spinner,
.mini-spin {
    width: 14px;
    height: 14px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
    display: inline-block;
    flex-shrink: 0;
}

.mini-spin {
    border-color: rgba(82, 183, 136, 0.2);
    border-top-color: #52b788;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Transitions */
.item-enter-active,
.item-leave-active {
    transition: all 0.25s ease;
}

.item-enter-from {
    opacity: 0;
    transform: translateX(-12px);
}

.item-leave-to {
    opacity: 0;
    transform: translateX(12px);
}

/* Toast */
.toast {
    position: fixed;
    bottom: 28px;
    right: 28px;
    padding: 12px 20px;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 600;
    z-index: 99999;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
    font-family: inherit;
}

.toast-success {
    background: rgba(82, 183, 136, 0.12);
    border: 1px solid rgba(82, 183, 136, 0.3);
    color: #2d6a4f;
}

.toast-error {
    background: rgba(224, 82, 82, 0.1);
    border: 1px solid rgba(224, 82, 82, 0.25);
    color: #c0392b;
}

.toast-enter-active,
.toast-leave-active {
    transition: all 0.25s ease;
}

.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateY(10px);
}

/* Responsive */
@media (max-width: 900px) {
    .cart-layout {
        grid-template-columns: 1fr;
    }

    .summary-col {
        position: static;
    }

    .page-header {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }

    .header-right {
        display: none;
    }
}
</style>