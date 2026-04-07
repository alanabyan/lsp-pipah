<template>
  <div class="product-page">
    <StorefrontNavbar :cart-count="cartCount" />

    <div class="product-wrap">
      <!-- Loading -->
      <div v-if="loading" class="container">
        <div class="product-skeleton-grid">
          <div class="skeleton skeleton--img"></div>
          <div class="skeleton-info">
            <div class="skeleton skeleton--title"></div>
            <div class="skeleton skeleton--text"></div>
            <div class="skeleton skeleton--text" style="width:60%"></div>
            <div class="skeleton skeleton--price"></div>
          </div>
        </div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="container error-state">
        <span class="material-symbols-outlined">error_outline</span>
        <p>{{ error }}</p>
        <button class="btn btn--green" @click="$router.back()">Kembali</button>
      </div>

      <!-- Product Detail -->
      <div v-else-if="obat" class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
          <router-link to="/" class="bc-link">Home</router-link>
          <span class="bc-sep">›</span>
          <router-link to="/" class="bc-link">Katalog</router-link>
          <span class="bc-sep">›</span>
          <span class="bc-current">{{ obat.nama_obat }}</span>
        </nav>

        <div class="product-grid">
          <!-- Images -->
          <div class="product-images">
            <div class="main-img-wrap">
              <img v-if="activeImg" :src="activeImg" :alt="obat.nama_obat" class="main-img" />
              <div v-else class="main-img-placeholder">
                <span class="material-symbols-outlined">medication</span>
              </div>
              <!-- Stock badge -->
              <span v-if="obat.stok === 0" class="stock-tag stock-tag--empty">Stok Habis</span>
              <span v-else-if="obat.stok <= 5" class="stock-tag stock-tag--low">Stok Terbatas</span>
            </div>

            <!-- Thumbnails -->
            <div v-if="images.length > 1" class="thumbs">
              <button
                v-for="(img, i) in images"
                :key="i"
                :class="['thumb', { 'thumb--active': activeImg === img }]"
                @click="activeImg = img"
              >
                <img :src="img" :alt="'foto ' + (i+1)" />
              </button>
            </div>
          </div>

          <!-- Info -->
          <div class="product-info">
            <div class="product-badges">
              <span class="badge badge--category">{{ obat.jenis_obat?.jenis || 'Obat' }}</span>
              <span v-if="obat.stok > 0" class="badge badge--stock">
                <span class="material-symbols-outlined">check_circle</span> In Stock ({{ obat.stok }})
              </span>
            </div>

            <h1 class="product-name">{{ obat.nama_obat }}</h1>
            <p class="product-desc">{{ obat.deskripsi_obat }}</p>

            <div class="product-price">
              <span class="price-val">{{ formatRp(obat.harga_jual) }}</span>
              <span class="price-unit">/ unit</span>
            </div>

            <!-- Quantity + Cart -->
            <div class="add-to-cart">
              <div class="qty-control">
                <button class="qty-btn" @click="qty > 1 && qty--">−</button>
                <span class="qty-val">{{ qty }}</span>
                <button class="qty-btn" @click="qty < obat.stok && qty++">+</button>
              </div>
              <button
                class="btn btn--add-cart"
                :disabled="obat.stok === 0 || adding"
                @click="addToCart"
              >
                <span v-if="adding" class="material-symbols-outlined spin">progress_activity</span>
                <span v-else class="material-symbols-outlined">add_shopping_cart</span>
                {{ adding ? 'Menambahkan...' : 'Tambah ke Keranjang' }}
              </button>
            </div>

            <router-link :disabled="obat.stok === 0 || adding" @click="addToCart" to="/checkout" class="btn btn--buy-now">
              <span class="material-symbols-outlined">bolt</span>
              Beli Sekarang
            </router-link>

            <!-- Meta info -->
            <div class="product-meta">
              <div class="meta-row">
                <span class="material-symbols-outlined meta-icon">inventory_2</span>
                <span class="meta-label">Stok tersedia:</span>
                <span class="meta-val">{{ obat.stok }} unit</span>
              </div>
              <div class="meta-row">
                <span class="material-symbols-outlined meta-icon">verified</span>
                <span class="meta-label">Terdaftar BPOM</span>
              </div>
              <div class="meta-row">
                <span class="material-symbols-outlined meta-icon">local_shipping</span>
                <span class="meta-label">Gratis ongkir untuk pembelian pertama</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Similar Products -->
        <div class="similar-section">
          <h2 class="similar-title">Produk Serupa</h2>
          <div class="similar-grid">
            <div
              v-for="item in similarProducts"
              :key="item.id"
              class="similar-card"
              @click="$router.push('/product/' + item.id)"
            >
              <div class="similar-img">
                <img v-if="item.foto1" :src="imageUrl(item.foto1)" :alt="item.nama_obat" />
                <span v-else class="material-symbols-outlined">medication</span>
              </div>
              <div class="similar-info">
                <p class="similar-name">{{ item.nama_obat }}</p>
                <p class="similar-price">{{ formatRp(item.harga_jual) }}</p>
                <button class="similar-btn" @click.stop="addSimilar(item)">+ Tambah</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast -->
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
  name: 'ProductDetailPage',
  components: { StorefrontNavbar },

  data() {
    return {
      obat: null, loading: true, error: null,
      activeImg: null, qty: 1, adding: false,
      similarProducts: [], cartCount: 0,
      toast: { show: false, message: '', type: 'success' },
    }
  },

  computed: {
    images() {
      if (!this.obat) return []
      return ['foto1','foto2','foto3']
        .map(k => this.obat[k] ? this.imageUrl(this.obat[k]) : null)
        .filter(Boolean)
    },
  },

  watch: {
    '$route.params.id': 'fetchObat',
  },

  mounted() {
    this.fetchObat()
    this.fetchCartCount()
  },

  methods: {
    async fetchObat() {
      this.loading = true; this.error = null
      try {
        const allRes = await api.get('/obat')
        const all = allRes.data
        const id = Number(this.$route.params.id)
        this.obat = all.find(o => o.id === id)
        if (!this.obat) { this.error = 'Obat tidak ditemukan.'; return }
        this.activeImg = this.images[0] || null
        this.similarProducts = all.filter(o => o.idjenis === this.obat.idjenis && o.id !== id).slice(0, 4)
      } catch { this.error = 'Gagal memuat detail produk.' }
      finally { this.loading = false }
    },

    async addToCart() {
      const token = localStorage.getItem('pelanggan_token')
      if (!token) { this.$router.push('/login'); return }
      this.adding = true
      try {
        await api.post('/pelanggan/tambah-keranjang', { id_obat: this.obat.id, jumlah_order: this.qty })
        this.showToast(`${this.obat.nama_obat} ditambahkan ke keranjang!`, 'success')
        await this.fetchCartCount()
      } catch (err) {
        this.showToast(err.response?.data?.message || 'Gagal menambah ke keranjang.', 'error')
      } finally { this.adding = false }
    },

    async addSimilar(item) {
      const token = localStorage.getItem('pelanggan_token')
      if (!token) { this.$router.push('/login'); return }
      try {
        await api.post('/keranjang', { id_obat: item.id, jumlah_order: 1 })
        this.showToast(`${item.nama_obat} ditambahkan!`, 'success')
        await this.fetchCartCount()
      } catch (err) {
        this.showToast(err.response?.data?.message || 'Gagal', 'error')
      }
    },

    async fetchCartCount() {
      const token = localStorage.getItem('pelanggan_token')
      if (!token) return
      try {
        const res = await api.get('/pelanggan/keranjang')
        this.cartCount = res.data?.length ?? 0
      } catch { this.cartCount = 0 }
    },

    imageUrl(f) {
      const base = import.meta.env.VITE_API_URL?.replace('/api', '') || 'http://localhost:8000'
      return `${base}/storage/obat/${f}`
    },
    formatRp(v) { return 'Rp ' + Number(v).toLocaleString('id-ID') },
    showToast(message, type = 'success') {
      this.toast = { show: true, message, type }
      setTimeout(() => { this.toast.show = false }, 3000)
    },
  },
}
</script>

<style scoped>
.product-page { font-family: 'Plus Jakarta Sans', sans-serif; background: #0a0a12; min-height: 100vh; color: #fff; }
.product-wrap { padding-top: 6rem; padding-bottom: 4rem; }
.container    { max-width: 1100px; margin: 0 auto; padding: 0 1.5rem; }

/* Breadcrumb */
.breadcrumb { display: flex; align-items: center; gap: 0.5rem; font-size: 0.78rem; margin-bottom: 2rem; }
.bc-link  { color: rgba(255,255,255,0.4); text-decoration: none; transition: color 0.15s; }
.bc-link:hover  { color: #4ade80; }
.bc-sep   { color: rgba(255,255,255,0.2); }
.bc-current { color: rgba(255,255,255,0.7); font-weight: 600; }

/* Product grid */
.product-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: start; margin-bottom: 4rem; }

/* Images */
.product-images { display: flex; flex-direction: column; gap: 0.75rem; }
.main-img-wrap {
  aspect-ratio: 1; border-radius: 1.25rem; overflow: hidden;
  background: #12151c; border: 1px solid rgba(255,255,255,0.07);
  position: relative; display: flex; align-items: center; justify-content: center;
}
.main-img { width: 100%; height: 100%; object-fit: cover; }
.main-img-placeholder .material-symbols-outlined { font-size: 5rem; color: rgba(255,255,255,0.1); }
.stock-tag {
  position: absolute; top: 1rem; left: 1rem;
  padding: 0.25rem 0.75rem; border-radius: 9999px;
  font-size: 0.72rem; font-weight: 700;
}
.stock-tag--low   { background: rgba(251,191,36,0.15); color: #fbbf24; border: 1px solid rgba(251,191,36,0.3); }
.stock-tag--empty { background: rgba(248,113,113,0.15); color: #f87171; border: 1px solid rgba(248,113,113,0.3); }

.thumbs { display: flex; gap: 0.5rem; }
.thumb {
  width: 4rem; height: 4rem; border-radius: 0.5rem;
  border: 2px solid rgba(255,255,255,0.07); overflow: hidden;
  cursor: pointer; transition: border-color 0.15s; background: #12151c; padding: 0;
}
.thumb--active { border-color: #4ade80; }
.thumb img { width: 100%; height: 100%; object-fit: cover; }

/* Info */
.product-info { display: flex; flex-direction: column; gap: 1.25rem; }
.product-badges { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.badge {
  display: inline-flex; align-items: center; gap: 0.3rem;
  padding: 0.25rem 0.75rem; border-radius: 9999px;
  font-size: 0.7rem; font-weight: 700;
}
.badge--category { background: rgba(74,222,128,0.12); color: #4ade80; border: 1px solid rgba(74,222,128,0.2); }
.badge--stock    { background: rgba(34,197,94,0.1); color: #86efac; }
.badge--stock .material-symbols-outlined { font-size: 0.8rem; }

.product-name {
  font-family: 'Manrope', sans-serif;
  font-size: 2rem; font-weight: 900; color: #fff; letter-spacing: -0.03em; line-height: 1.15;
}
.product-desc { font-size: 0.875rem; color: rgba(255,255,255,0.5); line-height: 1.7; }

.product-price { display: flex; align-items: baseline; gap: 0.4rem; }
.price-val { font-family: 'Manrope', sans-serif; font-size: 2rem; font-weight: 900; color: #4ade80; }
.price-unit { font-size: 0.8rem; color: rgba(255,255,255,0.35); }

/* Add to cart */
.add-to-cart { display: flex; gap: 0.75rem; align-items: center; }
.qty-control { display: flex; align-items: center; gap: 0.5rem; background: rgba(255,255,255,0.05); border: 1.5px solid rgba(255,255,255,0.1); border-radius: 0.625rem; padding: 0.4rem 0.75rem; }
.qty-btn { background: none; border: none; color: #fff; cursor: pointer; font-size: 1.2rem; line-height: 1; padding: 0; transition: color 0.15s; }
.qty-btn:hover { color: #4ade80; }
.qty-val { font-size: 0.9rem; font-weight: 700; color: #fff; min-width: 2rem; text-align: center; }

.btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; border: none; border-radius: 0.75rem; font-size: 0.875rem; font-weight: 700; cursor: pointer; transition: all 0.2s; text-decoration: none; font-family: inherit; }
.btn--add-cart { background: rgba(74,222,128,0.15); color: #4ade80; border: 1.5px solid rgba(74,222,128,0.3); flex: 1; justify-content: center; }
.btn--add-cart:hover:not(:disabled) { background: #4ade80; color: #052e16; }
.btn--add-cart:disabled { opacity: 0.4; cursor: not-allowed; }
.btn--buy-now { background: #4ade80; color: #052e16; width: 100%; justify-content: center; box-shadow: 0 4px 20px rgba(74,222,128,0.3); }
.btn--buy-now:hover { background: #22c55e; transform: translateY(-2px); }
.btn--green { background: #4ade80; color: #052e16; }
.btn .material-symbols-outlined { font-size: 1.1rem; }
.spin { animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Meta */
.product-meta { display: flex; flex-direction: column; gap: 0.6rem; padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 0.75rem; border: 1px solid rgba(255,255,255,0.07); }
.meta-row     { display: flex; align-items: center; gap: 0.6rem; font-size: 0.8rem; color: rgba(255,255,255,0.5); }
.meta-icon    { font-size: 0.95rem; color: #4ade80; }
.meta-label   { font-weight: 600; }
.meta-val     { color: #fff; }

/* Similar */
.similar-section { margin-top: 2rem; }
.similar-title { font-family: 'Manrope', sans-serif; font-size: 1.25rem; font-weight: 800; color: #fff; margin-bottom: 1.25rem; }
.similar-grid  { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
.similar-card  { background: #12151c; border: 1px solid rgba(255,255,255,0.07); border-radius: 0.875rem; overflow: hidden; cursor: pointer; transition: all 0.2s; }
.similar-card:hover { border-color: rgba(74,222,128,0.3); transform: translateY(-4px); }
.similar-img { aspect-ratio: 1; background: rgba(255,255,255,0.04); display: flex; align-items: center; justify-content: center; overflow: hidden; }
.similar-img img { width: 100%; height: 100%; object-fit: cover; }
.similar-img .material-symbols-outlined { font-size: 2rem; color: rgba(255,255,255,0.1); }
.similar-info { padding: 0.875rem; }
.similar-name  { font-size: 0.8rem; font-weight: 700; color: #fff; }
.similar-price { font-size: 0.875rem; font-weight: 800; color: #4ade80; margin: 0.25rem 0; }
.similar-btn   { font-size: 0.72rem; font-weight: 700; color: #4ade80; background: rgba(74,222,128,0.1); border: 1px solid rgba(74,222,128,0.2); padding: 0.3rem 0.7rem; border-radius: 0.4rem; cursor: pointer; transition: all 0.15s; font-family: inherit; }
.similar-btn:hover { background: #4ade80; color: #052e16; }

/* Skeletons */
.product-skeleton-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; padding: 2rem 0; }
.skeleton { border-radius: 0.75rem; background: linear-gradient(90deg, rgba(255,255,255,0.04) 25%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0.04) 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; }
.skeleton--img { aspect-ratio: 1; }
.skeleton-info { display: flex; flex-direction: column; gap: 1rem; padding-top: 1rem; }
.skeleton--title { height: 3rem; width: 80%; }
.skeleton--text  { height: 1rem; }
.skeleton--price { height: 2rem; width: 40%; }
@keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

/* Error */
.error-state { display: flex; flex-direction: column; align-items: center; gap: 1rem; padding: 5rem 0; color: rgba(255,255,255,0.3); }
.error-state .material-symbols-outlined { font-size: 3rem; color: #f87171; }

/* Toast */
.toast { position: fixed; bottom: 2rem; left: 50%; transform: translateX(-50%); padding: 0.75rem 1.5rem; border-radius: 0.75rem; display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; font-weight: 600; box-shadow: 0 8px 24px rgba(0,0,0,0.4); z-index: 200; white-space: nowrap; }
.toast--success { background: #064e3b; color: #fff; }
.toast--error   { background: #7f1d1d; color: #fff; }
.toast-enter-active, .toast-leave-active { transition: all 0.3s; }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(-50%) translateY(1rem); }

@media (max-width: 768px) {
  .product-grid  { grid-template-columns: 1fr; }
  .similar-grid  { grid-template-columns: repeat(2, 1fr); }
}
</style>