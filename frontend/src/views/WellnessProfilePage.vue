<template>
  <div class="profile-page">
    <StorefrontNavbar :cart-count="cartCount" />

    <div class="profile-wrap">
      <div class="profile-layout">

        <!-- ── Sidebar ── -->
        <aside class="profile-sidebar">
          <div class="sidebar-user">
            <div class="user-avatar">
              <span class="material-symbols-outlined">account_circle</span>
            </div>
            <div class="user-info">
              <p class="user-greeting">Welcome back,</p>
              <p class="user-name">{{ pelanggan?.nama_pelanggan || '...' }}</p>
              <p class="user-id">Patient ID #{{ pad(pelanggan?.id) }}</p>
            </div>
          </div>

          <nav class="sidebar-nav">
            <button
              v-for="tab in tabs"
              :key="tab.key"
              :class="['nav-btn', { 'nav-btn--active': activeTab === tab.key }]"
              @click="activeTab = tab.key"
            >
              <span class="material-symbols-outlined">{{ tab.icon }}</span>
              {{ tab.label }}
            </button>
          </nav>

          <div class="sidebar-bottom">
            <router-link to="/checkout" class="nav-btn nav-btn--checkout">
              <span class="material-symbols-outlined">shopping_cart</span>
              Lanjut ke Checkout
            </router-link>
            <div class="divider-line"></div>
            <router-link to="/" class="nav-btn">
              <span class="material-symbols-outlined">storefront</span>
              Kembali ke Toko
            </router-link>
            <button class="nav-btn nav-btn--danger" @click="logout">
              <span class="material-symbols-outlined">logout</span>
              Log Out
            </button>
          </div>
        </aside>

        <!-- ── Main Content ── -->
        <main class="profile-content">

          <!-- TAB: Profile Overview -->
          <div v-if="activeTab === 'overview'" class="tab-wrap">
            <div class="tab-header">
              <h1 class="tab-title">My Wellness Profile</h1>
              <p class="tab-sub">Data akun dan informasi pelanggan kamu.</p>
            </div>

            <div class="overview-grid">
              <!-- Account Details Card — READ ONLY (tidak ada endpoint update profile) -->
              <div class="card">
                <div class="card-head">
                  <h3 class="card-title">Account Details</h3>
                  <!-- Tidak ada endpoint update profile di controller, hanya tampil -->
                </div>

                <div v-if="loading" class="loading-rows">
                  <div v-for="i in 5" :key="i" class="skeleton skeleton--row"></div>
                </div>
                <div v-else-if="pelanggan" class="account-fields">
                  <div class="field-item">
                    <span class="material-symbols-outlined field-icon">person</span>
                    <div>
                      <p class="field-label">Nama Lengkap</p>
                      <p class="field-val">{{ pelanggan.nama_pelanggan }}</p>
                    </div>
                  </div>
                  <div class="field-item">
                    <span class="material-symbols-outlined field-icon">mail</span>
                    <div>
                      <p class="field-label">Email</p>
                      <p class="field-val">{{ pelanggan.email }}</p>
                    </div>
                  </div>
                  <div class="field-item">
                    <span class="material-symbols-outlined field-icon">phone</span>
                    <div>
                      <p class="field-label">No. Telepon</p>
                      <p class="field-val">{{ pelanggan.no_telp || '—' }}</p>
                    </div>
                  </div>
                  <div class="field-item">
                    <span class="material-symbols-outlined field-icon">location_on</span>
                    <div>
                      <p class="field-label">Alamat</p>
                      <p class="field-val">{{ pelanggan.alamat1 || '—' }}</p>
                    </div>
                  </div>
                  <div v-if="pelanggan.kota1 || pelanggan.propinsi1" class="field-item">
                    <span class="material-symbols-outlined field-icon">apartment</span>
                    <div>
                      <p class="field-label">Kota / Provinsi</p>
                      <p class="field-val">{{ [pelanggan.kota1, pelanggan.propinsi1, pelanggan.kodepos1].filter(Boolean).join(', ') }}</p>
                    </div>
                  </div>
                </div>

                <!-- Info: tidak bisa edit karena tidak ada endpoint -->
                <div class="readonly-note">
                  <span class="material-symbols-outlined">info</span>
                  <p>Untuk mengubah data profil, hubungi apoteker kami.</p>
                </div>
              </div>

              <!-- Stats Card -->
              <div class="stats-col">
                <!-- Member Badge -->
                <div class="member-card">
                  <p class="member-tag">Status Akun</p>
                  <h3 class="member-title">Pharmacy+ Member</h3>
                  <p class="member-sub">Akses katalog lengkap, pengiriman cepat, dan layanan konsultasi apoteker.</p>
                  <div class="member-perks">
                    <span>✓ Akses Katalog Penuh</span>
                    <span>✓ Pengiriman ke Rumah</span>
                    <span>✓ Konsultasi Apoteker</span>
                  </div>
                </div>

                <!-- Quick stats dari orders -->
                <div class="quick-stats">
                  <div class="stat-item">
                    <p class="stat-val">{{ orderCount }}</p>
                    <p class="stat-label">Total Pesanan</p>
                  </div>
                  <div class="stat-item">
                    <p class="stat-val">{{ pendingCount }}</p>
                    <p class="stat-label">Menunggu</p>
                  </div>
                  <div class="stat-item">
                    <p class="stat-val">{{ doneCount }}</p>
                    <p class="stat-label">Selesai</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB: Order History -->
          <OrderHistoryTab v-if="activeTab === 'orders'" />

          <!-- TAB: Payment Methods -->
          <PaymentMethodsTab v-if="activeTab === 'payments'" />

        </main>
      </div>
    </div>
  </div>
</template>

<script>
import StorefrontNavbar  from '@/components/storefront/StorefrontNavbar.vue'
import OrderHistoryTab   from '@/components/profile/OrderHistoryTab.vue'
import PaymentMethodsTab from '@/components/profile/PaymentMethodsTab.vue'
import api from '@/services/api.js'

export default {
  name: 'WellnessProfilePage',
  components: { StorefrontNavbar, OrderHistoryTab, PaymentMethodsTab },

  data() {
    return {
      activeTab: 'overview',
      pelanggan: null,
      loading: true,
      cartCount: 0,
      // order stats
      orderCount: 0, pendingCount: 0, doneCount: 0,

      // Hanya 3 tab yang ada endpoint-nya:
      // - overview   → GET /api/pelanggan/me
      // - orders     → GET /api/penjualan + GET /api/detail-penjualan/nota/:id + POST /api/pembayaran + PUT /api/penjualan/:id
      // - payments   → GET /api/metode-pembayaran
      tabs: [
        { key: 'overview', label: 'Profile Overview', icon: 'person' },
        { key: 'orders',   label: 'Order History',    icon: 'receipt_long' },
        { key: 'payments', label: 'Metode Pembayaran',icon: 'credit_card' },
      ],
    }
  },

  mounted() {
    if (!localStorage.getItem('pelanggan_token')) {
      this.$router.push('/login'); return
    }
    // Buka tab dari query string
    if (this.$route.query.tab) this.activeTab = this.$route.query.tab
    this.loadPelanggan()
    this.loadOrderStats()
    this.loadCartCount()
  },

  methods: {
    async loadPelanggan() {
      this.loading = true
      try {
        // GET /api/pelanggan/me
        const res = await api.get('/pelanggan/me')
        this.pelanggan = res.data.pelanggan
        localStorage.setItem('pelanggan_data', JSON.stringify(res.data.pelanggan))
      } catch {
        // fallback ke localStorage
        this.pelanggan = JSON.parse(localStorage.getItem('pelanggan_data') || 'null')
      } finally { this.loading = false }
    },

    async loadOrderStats() {
      try {
        // GET /api/penjualan
        const res = await api.get('/penjualan')
        const p = JSON.parse(localStorage.getItem('pelanggan_data') || '{}')
        const myOrders = (res.data || []).filter(o => o.id_pelanggan === p.id)
        this.orderCount  = myOrders.length
        this.pendingCount = myOrders.filter(o => o.status_order === 'Menunggu Konfirmasi').length
        this.doneCount   = myOrders.filter(o => o.status_order === 'Selesai').length
      } catch { }
    },

    async loadCartCount() {
      try {
        // GET /api/keranjang
        const res = await api.get('/pelanggan/keranjang')
        this.cartCount = res.data?.length ?? 0
      } catch { this.cartCount = 0 }
    },

    async logout() {
      try { await api.post('/pelanggan/logout') } catch {}
      localStorage.removeItem('pelanggan_token')
      localStorage.removeItem('pelanggan_data')
      window.location.reload()
      this.$router.push('/')
    },

    pad(id) { return String(id || 0).padStart(5, '0') },
  },
}
</script>

<style scoped>
.profile-page { font-family: 'Plus Jakarta Sans', sans-serif; background: #0a0a12; min-height: 100vh; color: #fff; }
.profile-wrap { padding-top: 4.5rem; }
.profile-layout { display: grid; grid-template-columns: 15rem 1fr; min-height: calc(100vh - 4.5rem); }

/* Sidebar */
.profile-sidebar {
  background: #0e1117; border-right: 1px solid rgba(255,255,255,0.06);
  padding: 1.5rem 1rem; display: flex; flex-direction: column; gap: 1.25rem;
  position: sticky; top: 4.5rem; height: calc(100vh - 4.5rem); overflow-y: auto;
}
.sidebar-user { display: flex; align-items: center; gap: 0.75rem; padding: 0 0.25rem; }
.user-avatar .material-symbols-outlined { font-size: 2.75rem; color: #4ade80; }
.user-greeting { font-size: 0.63rem; color: rgba(255,255,255,0.3); }
.user-name { font-size: 0.875rem; font-weight: 700; color: #fff; line-height: 1.2; }
.user-id   { font-size: 0.63rem; color: #4ade80; font-weight: 600; margin-top: 1px; }

.sidebar-nav { display: flex; flex-direction: column; gap: 2px; flex: 1; }
.nav-btn {
  display: flex; align-items: center; gap: 0.65rem;
  padding: 0.55rem 0.75rem; border-radius: 0.625rem;
  background: none; border: none; color: rgba(255,255,255,0.4);
  font-size: 0.8rem; font-weight: 600; cursor: pointer;
  transition: all 0.15s; text-align: left; text-decoration: none;
  font-family: inherit; width: 100%;
}
.nav-btn:hover           { background: rgba(255,255,255,0.05); color: #fff; }
.nav-btn--active         { background: rgba(74,222,128,0.1); color: #4ade80; }
.nav-btn--checkout       { background: rgba(74,222,128,0.08); color: #4ade80; border: 1px solid rgba(74,222,128,0.2); }
.nav-btn--checkout:hover { background: rgba(74,222,128,0.15); }
.nav-btn--danger         { color: rgba(248,113,113,0.5); }
.nav-btn--danger:hover   { background: rgba(248,113,113,0.08); color: #f87171; }
.nav-btn .material-symbols-outlined { font-size: 1rem; }

.sidebar-bottom { display: flex; flex-direction: column; gap: 4px; margin-top: auto; }
.divider-line { height: 1px; background: rgba(255,255,255,0.06); margin: 0.4rem 0; }

/* Content */
.profile-content { padding: 2rem; overflow-y: auto; }
.tab-wrap  { display: flex; flex-direction: column; gap: 1.5rem; }
.tab-header{ }
.tab-title { font-family: 'Manrope', sans-serif; font-size: 1.75rem; font-weight: 900; color: #fff; letter-spacing: -0.03em; }
.tab-sub   { font-size: 0.8rem; color: rgba(255,255,255,0.35); margin-top: 0.3rem; line-height: 1.6; }

/* Overview grid */
.overview-grid { display: grid; grid-template-columns: 1fr 18rem; gap: 1.25rem; }

/* Card */
.card { background: #12151c; border: 1px solid rgba(255,255,255,0.07); border-radius: 1rem; padding: 1.5rem; }
.card-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
.card-title { font-family: 'Manrope', sans-serif; font-size: 1rem; font-weight: 700; color: #fff; }

/* Account fields */
.account-fields { display: flex; flex-direction: column; gap: 0; }
.field-item {
  display: flex; align-items: flex-start; gap: 0.875rem;
  padding: 0.875rem 0; border-bottom: 1px solid rgba(255,255,255,0.05);
}
.field-item:last-child { border-bottom: none; }
.field-icon  { font-size: 1rem; color: rgba(255,255,255,0.25); flex-shrink: 0; margin-top: 2px; }
.field-label { font-size: 0.67rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: rgba(255,255,255,0.3); }
.field-val   { font-size: 0.875rem; font-weight: 600; color: #fff; margin-top: 2px; }

.readonly-note {
  display: flex; align-items: center; gap: 0.5rem;
  margin-top: 1rem; padding: 0.75rem 1rem;
  background: rgba(255,255,255,0.03); border-radius: 0.625rem;
  font-size: 0.75rem; color: rgba(255,255,255,0.3);
}
.readonly-note .material-symbols-outlined { font-size: 0.9rem; flex-shrink: 0; }

/* Stats col */
.stats-col { display: flex; flex-direction: column; gap: 1rem; }
.member-card {
  background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
  border: 1px solid rgba(74,222,128,0.2); border-radius: 1rem;
  padding: 1.5rem; display: flex; flex-direction: column; gap: 0.75rem;
}
.member-tag   { font-size: 0.63rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.12em; color: #4ade80; }
.member-title { font-family: 'Manrope', sans-serif; font-size: 1.1rem; font-weight: 900; color: #fff; }
.member-sub   { font-size: 0.78rem; color: rgba(255,255,255,0.55); line-height: 1.6; }
.member-perks { display: flex; flex-direction: column; gap: 0.3rem; font-size: 0.72rem; font-weight: 700; color: #4ade80; }

.quick-stats {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem;
}
.stat-item {
  background: #12151c; border: 1px solid rgba(255,255,255,0.07);
  border-radius: 0.875rem; padding: 1rem; text-align: center;
}
.stat-val   { font-family: 'Manrope', sans-serif; font-size: 1.5rem; font-weight: 900; color: #4ade80; }
.stat-label { font-size: 0.65rem; color: rgba(255,255,255,0.35); font-weight: 600; margin-top: 2px; text-transform: uppercase; letter-spacing: 0.05em; }

/* Skeleton */
.loading-rows { display: flex; flex-direction: column; gap: 0.75rem; }
.skeleton { background: linear-gradient(90deg, rgba(255,255,255,0.04) 25%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0.04) 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; border-radius: 0.5rem; }
.skeleton--row { height: 3rem; }
@keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

@media (max-width: 768px) {
  .profile-layout { grid-template-columns: 1fr; }
  .profile-sidebar { position: static; height: auto; }
  .overview-grid  { grid-template-columns: 1fr; }
}
</style>