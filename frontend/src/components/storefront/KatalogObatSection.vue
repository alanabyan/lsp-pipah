<template>
  <section id="katalog" class="section">
    <div class="container">
      <div class="section-header">
        <div>
          <p class="section-eyebrow">Produk</p>
          <h2 class="section-title">Katalog Obat</h2>
        </div>
        <div class="katalog-meta">
          <span class="total-label">Total: <strong>{{ obatList.length }} produk</strong></span>
          <select v-model="sortBy" class="sort-select">
            <option value="">Urutkan</option>
            <option value="nama_asc">Nama A-Z</option>
            <option value="harga_asc">Harga Terendah</option>
            <option value="harga_desc">Harga Tertinggi</option>
          </select>
        </div>
      </div>

      <!-- Loading skeleton -->
      <div v-if="loading" class="obat-grid">
        <div v-for="i in 8" :key="i" class="obat-skeleton"></div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="error-state">
        <span class="material-symbols-outlined">error_outline</span>
        <p>{{ error }}</p>
        <button class="btn-retry" @click="fetchObat">Coba Lagi</button>
      </div>

      <!-- Grid -->
      <div v-else class="obat-grid">
        <div v-for="obat in sortedObat" :key="obat.id" class="obat-card" @click="$router.push('/product/' + obat.id)">
          <div class="obat-img-wrap">
            <img v-if="obat.foto1" :src="imageUrl(obat.foto1)" :alt="obat.nama_obat" class="obat-img" />
            <div v-else class="obat-img-placeholder">
              <span class="material-symbols-outlined">medication</span>
            </div>
            <span v-if="obat.stok <= 5 && obat.stok > 0" class="stock-badge stock-badge--low">Stok Terbatas</span>
            <span v-if="obat.stok === 0" class="stock-badge stock-badge--empty">Habis</span>
          </div>
          <div class="obat-info">
            <p class="obat-name">{{ obat.nama_obat }}</p>
            <p class="obat-desc">{{ truncate(obat.deskripsi_obat, 55) }}</p>
            <p class="obat-price">{{ formatRp(obat.harga_jual) }}</p>
          </div>
          <div class="obat-actions">
            <button class="btn-cart" :disabled="obat.stok === 0 || loadingCart[obat.id]" @click.stop="addToCart(obat)">
              <span v-if="loadingCart[obat.id]" class="material-symbols-outlined spin">progress_activity</span>
              <span v-else class="material-symbols-outlined">add_shopping_cart</span>
              {{ obat.stok === 0 ? 'Stok Habis' : 'Tambah' }}
            </button>
          </div>
        </div>
      </div>

      <div v-if="!loading && !error && obatList.length > 0" class="load-more">
        <button class="btn-load-more">
          <span class="material-symbols-outlined">expand_more</span>
          Lihat Semua Produk
        </button>
      </div>
    </div>

    <transition name="toast">
      <div v-if="toast.show" class="toast" :class="'toast--' + toast.type">
        <span class="material-symbols-outlined">{{ toast.type === 'success' ? 'check_circle' : 'error' }}</span>
        {{ toast.message }}
      </div>
    </transition>
  </section>
</template>

<script>
import api from '@/services/api.js'

export default {
  name: 'KatalogObatSection',
  props: {
    filterJenisId: { type: Number, default: null },
  },
  emits: ['cart-updated'],
  data() {
    return {
      obatList: [], loading: true, error: null,
      sortBy: '', loadingCart: {},
      toast: { show: false, message: '', type: 'success' },
    }
  },
  computed: {
    sortedObat() {
      let list = [...this.obatList]
      if (this.filterJenisId) list = list.filter(o => o.idjenis === this.filterJenisId)
      if (this.sortBy === 'nama_asc') list.sort((a, b) => a.nama_obat.localeCompare(b.nama_obat))
      if (this.sortBy === 'harga_asc') list.sort((a, b) => a.harga_jual - b.harga_jual)
      if (this.sortBy === 'harga_desc') list.sort((a, b) => b.harga_jual - a.harga_jual)
      return list
    },
  },
  mounted() { this.fetchObat() },
  methods: {
    async fetchObat() {
      this.loading = true; this.error = null
      try {
        // GET /api/obat
        const res = await api.get('/obat')
        this.obatList = res.data
      } catch (err) {
        this.error = 'Gagal memuat katalog obat.'
      } finally { this.loading = false }
    },

    async addToCart(obat) {
      const token = localStorage.getItem('pelanggan_token')
      if (!token) {
        this.showToast('Silakan login terlebih dahulu!', 'error')
        this.$router.push('/login')
        return
      }
      this.loadingCart = { ...this.loadingCart, [obat.id]: true }
      try {
        // POST /api/pelanggan/tambah-keranjang
        await api.post('/pelanggan/tambah-keranjang', {
          id_obat: obat.id,
          jumlah_order: 1,
        })
        this.showToast(`${obat.nama_obat} ditambahkan ke keranjang!`, 'success')
        this.$emit('cart-updated')
      } catch (err) {
        const msg = err.response?.data?.message || 'Gagal menambahkan ke keranjang.'
        this.showToast(msg, 'error')
      } finally {
        this.loadingCart = { ...this.loadingCart, [obat.id]: false }
      }
    },

    imageUrl(filename) {
      const base = (import.meta.env.VITE_API_URL || 'http://localhost:8000/api').replace('/api', '')
      return `${base}/storage/obat/${filename}`
    },
    formatRp(val) { return 'Rp ' + Number(val).toLocaleString('id-ID') },
    truncate(str, len) { if (!str) return ''; return str.length > len ? str.slice(0, len) + '…' : str },
    showToast(message, type = 'success') {
      this.toast = { show: true, message, type }
      setTimeout(() => { this.toast.show = false }, 3000)
    },
  },
}
</script>

<style scoped>
.section {
  padding: 5rem 0;
  background: #f8fafb;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 2rem;
}

.section-eyebrow {
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: #059669;
}

.section-title {
  font-family: 'Manrope', sans-serif;
  font-size: 1.75rem;
  font-weight: 800;
  color: #064e3b;
  letter-spacing: -0.02em;
}

.katalog-meta {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.total-label {
  font-size: 0.8rem;
  color: #6b7280;
}

.sort-select {
  padding: 0.4rem 0.75rem;
  border: 1.5px solid #e5e7eb;
  border-radius: 0.5rem;
  font-size: 0.8rem;
  font-family: inherit;
  color: #374151;
  outline: none;
  background: #fff;
  cursor: pointer;
}

.sort-select:focus {
  border-color: #059669;
}

.obat-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1.25rem;
}

.obat-card {
  background: #fff;
  border-radius: 1rem;
  border: 1.5px solid #f0f4f8;
  overflow: hidden;
  transition: all 0.2s;
  display: flex;
  flex-direction: column;
  cursor: pointer;
}

.obat-card:hover {
  border-color: #a7f3d0;
  box-shadow: 0 12px 32px rgba(5, 150, 105, 0.1);
  transform: translateY(-4px);
}

.obat-img-wrap {
  position: relative;
  aspect-ratio: 1;
  overflow: hidden;
  background: #f0fdf4;
}

.obat-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s;
}

.obat-card:hover .obat-img {
  transform: scale(1.05);
}

.obat-img-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.obat-img-placeholder .material-symbols-outlined {
  font-size: 3rem;
  color: #a7f3d0;
}

.stock-badge {
  position: absolute;
  top: 0.5rem;
  left: 0.5rem;
  padding: 0.2rem 0.6rem;
  border-radius: 9999px;
  font-size: 0.65rem;
  font-weight: 700;
}

.stock-badge--low {
  background: #fef3c7;
  color: #92400e;
}

.stock-badge--empty {
  background: #fee2e2;
  color: #991b1b;
}

.obat-info {
  padding: 0.875rem;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.obat-name {
  font-size: 0.875rem;
  font-weight: 700;
  color: #171c1f;
  line-height: 1.3;
}

.obat-desc {
  font-size: 0.72rem;
  color: #9ca3af;
  line-height: 1.5;
  flex: 1;
}

.obat-price {
  font-family: 'Manrope', sans-serif;
  font-size: 1rem;
  font-weight: 800;
  color: #059669;
}

.obat-actions {
  padding: 0 0.875rem 0.875rem;
}

.btn-cart {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  padding: 0.55rem;
  border-radius: 0.625rem;
  border: none;
  background: #ecfdf5;
  color: #065f46;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s;
  font-family: inherit;
}

.btn-cart:hover:not(:disabled) {
  background: #059669;
  color: #fff;
}

.btn-cart:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-cart .material-symbols-outlined {
  font-size: 1rem;
}

.spin {
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.obat-skeleton {
  height: 18rem;
  border-radius: 1rem;
  background: linear-gradient(90deg, #f0f4f8 25%, #e5e7eb 50%, #f0f4f8 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
}

@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }

  100% {
    background-position: -200% 0;
  }
}

.load-more {
  text-align: center;
  margin-top: 2.5rem;
}

.btn-load-more {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.7rem 2rem;
  border-radius: 9999px;
  border: 1.5px solid #e5e7eb;
  background: #fff;
  font-size: 0.875rem;
  font-weight: 700;
  color: #374151;
  cursor: pointer;
  transition: all 0.15s;
  font-family: inherit;
}

.btn-load-more:hover {
  border-color: #059669;
  color: #059669;
  background: #ecfdf5;
}

.error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  padding: 3rem;
  color: #9ca3af;
}

.error-state .material-symbols-outlined {
  font-size: 2.5rem;
  color: #fca5a5;
}

.btn-retry {
  padding: 0.5rem 1.25rem;
  border: none;
  border-radius: 0.5rem;
  background: #059669;
  color: #fff;
  font-weight: 700;
  cursor: pointer;
  font-family: inherit;
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
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  z-index: 200;
  white-space: nowrap;
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
  font-size: 1.1rem;
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
</style>