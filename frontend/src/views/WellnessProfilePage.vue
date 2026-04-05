<template>
  <div class="profile-page">
    <StorefrontNavbar :cart-count="0" />

    <div class="profile-wrap">
      <div class="profile-layout">

        <!-- LEFT: Sidebar -->
        <aside class="profile-sidebar">
          <div class="sidebar-user">
            <div class="user-avatar">
              <span class="material-symbols-outlined">account_circle</span>
            </div>
            <div>
              <p class="user-greeting">Welcome back,</p>
              <p class="user-name">{{ pelanggan?.nama_pelanggan || '...' }}</p>
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

          <div class="sidebar-footer">
            <button class="nav-btn nav-btn--danger" @click="logout">
              <span class="material-symbols-outlined">logout</span>
              Log Out
            </button>
            <router-link to="/" class="nav-btn">
              <span class="material-symbols-outlined">arrow_back</span>
              Kembali ke Toko
            </router-link>
          </div>

          <!-- New Prescription FAB -->
          <button class="fab-btn">
            <span class="material-symbols-outlined">add</span>
            New Prescription
          </button>
        </aside>

        <!-- RIGHT: Content -->
        <main class="profile-content">

          <!-- Header -->
          <div class="content-header">
            <div>
              <h1 class="content-title">{{ currentTabLabel }}</h1>
              <p class="content-sub">Manage your health data, monitor active prescriptions, and track your medical orders with surgical precision.</p>
            </div>
          </div>

          <!-- ══ TAB: Profile Overview ══ -->
          <div v-if="activeTab === 'overview'">

            <div class="overview-grid">
              <!-- Account Details Card -->
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Account Details</h3>
                  <button class="btn-edit">
                    <span class="material-symbols-outlined">edit</span> Edit Profile
                  </button>
                </div>
                <div v-if="pelanggan" class="account-details">
                  <div class="detail-row">
                    <span class="detail-label">Full Name</span>
                    <span class="detail-val">{{ pelanggan.nama_pelanggan }}</span>
                  </div>
                  <div class="detail-row">
                    <span class="detail-label">Email</span>
                    <span class="detail-val">{{ pelanggan.email }}</span>
                  </div>
                  <div class="detail-row">
                    <span class="detail-label">Phone</span>
                    <span class="detail-val">{{ pelanggan.no_telp || '-' }}</span>
                  </div>
                  <div class="detail-row">
                    <span class="detail-label">Address</span>
                    <span class="detail-val">{{ fullAddress }}</span>
                  </div>
                </div>
                <div v-else class="loading-state">
                  <div v-for="i in 4" :key="i" class="skeleton skeleton--row"></div>
                </div>
              </div>

              <!-- Pharmacy Member Card -->
              <div class="member-card">
                <div class="member-tag">Exclusive Perk</div>
                <h3 class="member-title">Pharmacy+ Member</h3>
                <p class="member-sub">Your membership unlocks clinical delivery services and exclusive member discounts on all prescriptions.</p>
                <div class="member-perks">
                  <span>✓ Free Delivery</span>
                  <span>✓ Priority Consultation</span>
                  <span>✓ Member Discounts</span>
                </div>
              </div>
            </div>

          </div>

          <!-- ══ TAB: Order History ══ -->
          <div v-if="activeTab === 'orders'">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Recent Orders</h3>
                <div class="card-actions">
                  <button class="btn-filter">
                    <span class="material-symbols-outlined">filter_list</span> Filter Dates
                  </button>
                  <button class="btn-filter btn-filter--active">Selected (0)</button>
                </div>
              </div>

              <div v-if="loadingOrders" class="loading-state">
                <div v-for="i in 3" :key="i" class="skeleton skeleton--row"></div>
              </div>

              <div v-else-if="orders.length === 0" class="empty-state">
                <span class="material-symbols-outlined">receipt_long</span>
                <p>Belum ada pesanan.</p>
                <router-link to="/" class="btn btn--green btn--sm">Mulai Belanja</router-link>
              </div>

              <div v-else class="orders-table-wrap">
                <table class="orders-table">
                  <thead>
                    <tr>
                      <th>Invoice</th>
                      <th>Date</th>
                      <th>Total</th>
                      <th>Payment Status</th>
                      <th>Confirm Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="order in orders" :key="order.id" class="order-row">
                      <td class="order-id">#INV-{{ String(order.id).padStart(4,'0') }}</td>
                      <td class="order-date">{{ formatDate(order.tgl_penjualan) }}</td>
                      <td class="order-total">{{ formatRp(order.total_bayar) }}</td>
                      <td>
                        <span :class="['status-badge', statusClass(order.status_order)]">
                          {{ order.status_order }}
                        </span>
                      </td>
                      <td>
                        <span class="confirm-badge">
                          {{ order.pembayaran?.status_konfirmasi || 'Menunggu' }}
                        </span>
                      </td>
                      <td>
                        <button class="action-btn">View Detail</button>
                        <button v-if="order.status_order === 'Menunggu Konfirmasi'" class="action-btn action-btn--danger">Cancel</button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- ══ TAB: Active Prescriptions ══ -->
          <div v-if="activeTab === 'prescriptions'">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Active Prescriptions</h3>
                <button class="btn-edit">
                  <span class="material-symbols-outlined">add</span> Add Prescription
                </button>
              </div>

              <div class="prescriptions-grid">
                <div
                  v-for="presc in prescriptions"
                  :key="presc.id"
                  class="presc-card"
                  :class="'presc-card--' + presc.status"
                >
                  <div class="presc-header">
                    <span :class="['presc-status', 'presc-status--' + presc.status]">{{ presc.statusLabel }}</span>
                  </div>
                  <p class="presc-name">{{ presc.nama }}</p>
                  <p class="presc-dose">{{ presc.dosis }}</p>
                  <p class="presc-refill">Refill until: {{ presc.refillUntil }}</p>
                  <button class="presc-btn">Add Prescription</button>
                </div>
              </div>
            </div>
          </div>

          <!-- ══ TAB: Payment Methods ══ -->
          <div v-if="activeTab === 'payments'">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Payment Methods</h3>
              </div>
              <div v-if="loadingPaymentMethods" class="loading-state">
                <div v-for="i in 3" :key="i" class="skeleton skeleton--row"></div>
              </div>
              <div v-else class="payments-list">
                <div v-for="pm in paymentMethodsList" :key="pm.id" class="payment-item">
                  <div class="payment-icon-wrap">
                    <span class="material-symbols-outlined">
                      {{ pm.metode_pembayaran.toLowerCase().includes('wallet') ? 'account_balance_wallet' : 'credit_card' }}
                    </span>
                  </div>
                  <div class="payment-details">
                    <p class="payment-name">{{ pm.metode_pembayaran }}</p>
                    <p class="payment-place">{{ pm.tempat_bayar }} · {{ pm.no_rekening }}</p>
                  </div>
                  <span class="payment-active">Active</span>
                </div>
              </div>
            </div>
          </div>

          <!-- ══ TAB: Account Security ══ -->
          <div v-if="activeTab === 'security'">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Account Security</h3>
              </div>
              <div class="security-list">
                <div class="security-item">
                  <div class="security-icon">
                    <span class="material-symbols-outlined">lock</span>
                  </div>
                  <div>
                    <p class="security-title">Password</p>
                    <p class="security-sub">Last changed — Never</p>
                  </div>
                  <button class="btn-edit">Change Password</button>
                </div>
                <div class="security-item">
                  <div class="security-icon">
                    <span class="material-symbols-outlined">verified_user</span>
                  </div>
                  <div>
                    <p class="security-title">Two-Factor Authentication</p>
                    <p class="security-sub">Add an extra layer of security</p>
                  </div>
                  <button class="btn-edit">Enable</button>
                </div>
              </div>
            </div>
          </div>

        </main>
      </div>
    </div>
  </div>
</template>

<script>
import StorefrontNavbar from '@/components/storefront/StorefrontNavbar.vue'
import api from '@/services/api.js'

export default {
  name: 'WellnessProfilePage',
  components: { StorefrontNavbar },

  data() {
    return {
      activeTab: 'overview',
      pelanggan: null,
      orders: [],
      paymentMethodsList: [],
      loadingOrders: false,
      loadingPaymentMethods: false,

      tabs: [
        { key: 'overview',       label: 'Profile Overview',    icon: 'person' },
        { key: 'orders',         label: 'Order History',       icon: 'receipt_long' },
        { key: 'prescriptions',  label: 'Active Prescriptions',icon: 'medication' },
        { key: 'payments',       label: 'Payment Methods',     icon: 'payments' },
        { key: 'security',       label: 'Account Security',    icon: 'security' },
      ],

      // Mock prescriptions data (tidak ada di controller, jadi pakai mock)
      prescriptions: [
        { id: 1, nama: 'Amoxicillin', dosis: 'Existing until: Oct 25, 2025', refillUntil: 'Oct 25, 2025', status: 'active', statusLabel: '+ RENEWAL' },
        { id: 2, nama: 'Lisinopril', dosis: 'Existing until: Oct 28, 2024', refillUntil: 'Oct 28, 2024', status: 'inactive', statusLabel: 'PAUSED' },
      ],
    }
  },

  computed: {
    currentTabLabel() {
      return this.tabs.find(t => t.key === this.activeTab)?.label || 'My Wellness Profile'
    },
    fullAddress() {
      if (!this.pelanggan) return '-'
      const p = this.pelanggan
      return [p.alamat1, p.kota1, p.propinsi1, p.kodepos1].filter(Boolean).join(', ') || '-'
    },
  },

  mounted() {
    if (!localStorage.getItem('pelanggan_token')) {
      this.$router.push('/login'); return
    }
    this.loadPelanggan()
    this.loadOrders()
    this.loadPaymentMethods()
  },

  methods: {
    async loadPelanggan() {
      try {
        const res = await api.get('/pelanggan/me')
        this.pelanggan = res.data.pelanggan
        localStorage.setItem('pelanggan_data', JSON.stringify(res.data.pelanggan))
      } catch {
        this.pelanggan = JSON.parse(localStorage.getItem('pelanggan_data') || 'null')
      }
    },

    async loadOrders() {
      this.loadingOrders = true
      try {
        const res = await api.get('/penjualan')
        const pelanggan = JSON.parse(localStorage.getItem('pelanggan_data') || '{}')
        this.orders = res.data.filter(o => o.id_pelanggan === pelanggan.id)
      } catch { this.orders = [] }
      finally { this.loadingOrders = false }
    },

    async loadPaymentMethods() {
      this.loadingPaymentMethods = true
      try {
        const res = await api.get('/metode-pembayaran')
        this.paymentMethodsList = res.data
      } catch { this.paymentMethodsList = [] }
      finally { this.loadingPaymentMethods = false }
    },

    async logout() {
      try { await api.post('/pelanggan/logout') } catch {}
      localStorage.removeItem('pelanggan_token')
      localStorage.removeItem('pelanggan_data')
      this.$router.push('/')
    },

    formatRp(v) { return 'Rp ' + Number(v || 0).toLocaleString('id-ID') },
    formatDate(d) {
      if (!d) return '-'
      return new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
    },
    statusClass(s) {
      const map = {
        'Menunggu Konfirmasi': 'badge--waiting',
        'Diproses': 'badge--processing',
        'Selesai': 'badge--done',
        'Dibatalkan Pembeli': 'badge--cancelled',
        'Dibatalkan Penjual': 'badge--cancelled',
      }
      return map[s] || 'badge--waiting'
    },
  },
}
</script>

<style scoped>
.profile-page { font-family: 'Plus Jakarta Sans', sans-serif; background: #0a0a12; min-height: 100vh; color: #fff; }
.profile-wrap { padding-top: 4.5rem; }

/* Layout */
.profile-layout { display: grid; grid-template-columns: 15rem 1fr; min-height: calc(100vh - 4.5rem); }

/* Sidebar */
.profile-sidebar {
  background: #0e1117; border-right: 1px solid rgba(255,255,255,0.06);
  padding: 1.5rem 1rem; display: flex; flex-direction: column; gap: 1.5rem;
  position: sticky; top: 4.5rem; height: calc(100vh - 4.5rem); overflow-y: auto;
}
.sidebar-user { display: flex; align-items: center; gap: 0.75rem; padding: 0 0.5rem; }
.user-avatar .material-symbols-outlined { font-size: 2.5rem; color: #4ade80; }
.user-greeting { font-size: 0.68rem; color: rgba(255,255,255,0.35); }
.user-name { font-size: 0.875rem; font-weight: 700; color: #fff; }

.sidebar-nav { display: flex; flex-direction: column; gap: 2px; flex: 1; }
.nav-btn {
  display: flex; align-items: center; gap: 0.6rem;
  padding: 0.55rem 0.75rem; border-radius: 0.625rem;
  background: none; border: none; color: rgba(255,255,255,0.45);
  font-size: 0.8rem; font-weight: 600; cursor: pointer;
  transition: all 0.15s; text-align: left; text-decoration: none;
  font-family: inherit; width: 100%;
}
.nav-btn:hover { background: rgba(255,255,255,0.05); color: #fff; }
.nav-btn--active { background: rgba(74,222,128,0.1); color: #4ade80; }
.nav-btn--danger { color: rgba(248,113,113,0.6); }
.nav-btn--danger:hover { background: rgba(248,113,113,0.1); color: #f87171; }
.nav-btn .material-symbols-outlined { font-size: 1rem; }

.sidebar-footer { display: flex; flex-direction: column; gap: 2px; padding-top: 0.75rem; border-top: 1px solid rgba(255,255,255,0.06); }

.fab-btn {
  display: flex; align-items: center; justify-content: center; gap: 0.5rem;
  padding: 0.65rem; border: none; border-radius: 0.625rem;
  background: rgba(74,222,128,0.15); color: #4ade80;
  font-size: 0.8rem; font-weight: 700; cursor: pointer;
  border: 1px solid rgba(74,222,128,0.25); transition: all 0.15s; font-family: inherit;
}
.fab-btn:hover { background: #4ade80; color: #052e16; }

/* Content */
.profile-content { padding: 2rem; display: flex; flex-direction: column; gap: 1.5rem; }
.content-header { margin-bottom: 0.5rem; }
.content-title  { font-family: 'Manrope', sans-serif; font-size: 1.75rem; font-weight: 900; color: #fff; letter-spacing: -0.03em; }
.content-sub    { font-size: 0.8rem; color: rgba(255,255,255,0.35); margin-top: 0.3rem; line-height: 1.6; }

/* Card */
.card { background: #12151c; border: 1px solid rgba(255,255,255,0.07); border-radius: 1rem; padding: 1.5rem; }
.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
.card-title  { font-family: 'Manrope', sans-serif; font-size: 1rem; font-weight: 700; color: #fff; }
.card-actions { display: flex; gap: 0.5rem; }
.btn-edit {
  display: inline-flex; align-items: center; gap: 0.35rem;
  padding: 0.4rem 0.875rem; border: 1px solid rgba(255,255,255,0.1);
  background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.6);
  border-radius: 0.5rem; font-size: 0.75rem; font-weight: 600;
  cursor: pointer; transition: all 0.15s; font-family: inherit;
}
.btn-edit:hover { background: rgba(74,222,128,0.1); color: #4ade80; border-color: rgba(74,222,128,0.3); }
.btn-edit .material-symbols-outlined { font-size: 0.9rem; }
.btn-filter {
  display: inline-flex; align-items: center; gap: 0.35rem;
  padding: 0.35rem 0.75rem; border: 1px solid rgba(255,255,255,0.1);
  background: rgba(255,255,255,0.04); color: rgba(255,255,255,0.5);
  border-radius: 0.5rem; font-size: 0.72rem; font-weight: 600; cursor: pointer;
  transition: all 0.15s; font-family: inherit;
}
.btn-filter--active { background: rgba(74,222,128,0.1); color: #4ade80; border-color: rgba(74,222,128,0.25); }
.btn-filter .material-symbols-outlined { font-size: 0.85rem; }

/* Overview Grid */
.overview-grid { display: grid; grid-template-columns: 1fr 18rem; gap: 1.25rem; }

/* Account Details */
.account-details { display: flex; flex-direction: column; gap: 0.875rem; }
.detail-row   { display: flex; flex-direction: column; gap: 0.2rem; padding-bottom: 0.875rem; border-bottom: 1px solid rgba(255,255,255,0.05); }
.detail-row:last-child { border-bottom: none; padding-bottom: 0; }
.detail-label { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(255,255,255,0.3); }
.detail-val   { font-size: 0.875rem; font-weight: 600; color: #fff; }

/* Member Card */
.member-card {
  background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
  border-radius: 1rem; padding: 1.5rem;
  border: 1px solid rgba(74,222,128,0.2);
  display: flex; flex-direction: column; gap: 0.875rem;
}
.member-tag { font-size: 0.68rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: #4ade80; }
.member-title { font-family: 'Manrope', sans-serif; font-size: 1.1rem; font-weight: 900; color: #fff; }
.member-sub   { font-size: 0.78rem; color: rgba(255,255,255,0.6); line-height: 1.6; }
.member-perks { display: flex; flex-direction: column; gap: 0.35rem; font-size: 0.75rem; font-weight: 700; color: #4ade80; }

/* Orders Table */
.orders-table-wrap { overflow-x: auto; }
.orders-table { width: 100%; border-collapse: collapse; }
.orders-table thead tr { border-bottom: 1px solid rgba(255,255,255,0.07); }
.orders-table th { padding: 0.5rem 0.75rem; font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: rgba(255,255,255,0.3); text-align: left; }
.order-row { border-bottom: 1px solid rgba(255,255,255,0.04); transition: background 0.12s; }
.order-row:hover { background: rgba(255,255,255,0.02); }
.order-row td { padding: 0.875rem 0.75rem; vertical-align: middle; }
.order-id    { font-size: 0.8rem; font-weight: 700; color: #fff; font-family: monospace; }
.order-date  { font-size: 0.78rem; color: rgba(255,255,255,0.45); }
.order-total { font-size: 0.875rem; font-weight: 700; color: #4ade80; }

.status-badge { padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.68rem; font-weight: 700; }
.badge--waiting    { background: rgba(251,191,36,0.15); color: #fbbf24; }
.badge--processing { background: rgba(59,130,246,0.15); color: #93c5fd; }
.badge--done       { background: rgba(74,222,128,0.15); color: #4ade80; }
.badge--cancelled  { background: rgba(248,113,113,0.15); color: #f87171; }

.confirm-badge { font-size: 0.75rem; color: rgba(255,255,255,0.45); font-weight: 600; }

.action-btn { padding: 0.3rem 0.7rem; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.6); border-radius: 0.4rem; font-size: 0.72rem; font-weight: 700; cursor: pointer; transition: all 0.15s; font-family: inherit; margin-right: 0.35rem; }
.action-btn:hover { background: rgba(74,222,128,0.1); color: #4ade80; border-color: rgba(74,222,128,0.25); }
.action-btn--danger:hover { background: rgba(248,113,113,0.1); color: #f87171; border-color: rgba(248,113,113,0.25); }

/* Prescriptions */
.prescriptions-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; }
.presc-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 0.875rem; padding: 1.25rem; display: flex; flex-direction: column; gap: 0.6rem; }
.presc-card--active   { border-color: rgba(74,222,128,0.2); }
.presc-card--inactive { border-color: rgba(248,113,113,0.15); }
.presc-status { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 9999px; font-size: 0.65rem; font-weight: 800; }
.presc-status--active   { background: rgba(74,222,128,0.15); color: #4ade80; }
.presc-status--inactive { background: rgba(248,113,113,0.1); color: #f87171; }
.presc-name   { font-size: 0.9rem; font-weight: 700; color: #fff; }
.presc-dose   { font-size: 0.72rem; color: rgba(255,255,255,0.4); }
.presc-refill { font-size: 0.7rem; color: rgba(255,255,255,0.3); }
.presc-btn { padding: 0.4rem; border: 1px dashed rgba(255,255,255,0.15); background: none; color: rgba(255,255,255,0.4); border-radius: 0.5rem; font-size: 0.72rem; cursor: pointer; transition: all 0.15s; font-family: inherit; margin-top: 0.25rem; }
.presc-btn:hover { border-color: #4ade80; color: #4ade80; }

/* Payment list */
.payments-list { display: flex; flex-direction: column; gap: 0.75rem; }
.payment-item { display: flex; align-items: center; gap: 1rem; padding: 0.875rem; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 0.75rem; }
.payment-icon-wrap { width: 2.5rem; height: 2.5rem; border-radius: 0.5rem; background: rgba(74,222,128,0.1); display: flex; align-items: center; justify-content: center; }
.payment-icon-wrap .material-symbols-outlined { color: #4ade80; font-size: 1.2rem; }
.payment-name  { font-size: 0.875rem; font-weight: 700; color: #fff; }
.payment-place { font-size: 0.72rem; color: rgba(255,255,255,0.35); }
.payment-active { margin-left: auto; padding: 0.2rem 0.6rem; background: rgba(74,222,128,0.12); color: #4ade80; border-radius: 9999px; font-size: 0.68rem; font-weight: 700; }

/* Security */
.security-list { display: flex; flex-direction: column; gap: 1rem; }
.security-item { display: flex; align-items: center; gap: 1rem; padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 0.75rem; border: 1px solid rgba(255,255,255,0.06); }
.security-icon { width: 2.5rem; height: 2.5rem; border-radius: 0.5rem; background: rgba(74,222,128,0.1); display: flex; align-items: center; justify-content: center; }
.security-icon .material-symbols-outlined { color: #4ade80; font-size: 1.2rem; }
.security-title { font-size: 0.875rem; font-weight: 700; color: #fff; }
.security-sub   { font-size: 0.72rem; color: rgba(255,255,255,0.35); margin-top: 2px; }

/* Loading */
.loading-state { display: flex; flex-direction: column; gap: 0.75rem; }
.skeleton { border-radius: 0.5rem; background: linear-gradient(90deg, rgba(255,255,255,0.04) 25%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0.04) 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; }
.skeleton--row { height: 3rem; }
@keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

/* Empty */
.empty-state { display: flex; flex-direction: column; align-items: center; gap: 0.75rem; padding: 3rem; color: rgba(255,255,255,0.2); }
.empty-state .material-symbols-outlined { font-size: 2.5rem; }
.btn { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.5rem 1.25rem; border: none; border-radius: 0.5rem; font-weight: 700; cursor: pointer; font-family: inherit; text-decoration: none; }
.btn--green { background: #4ade80; color: #052e16; }
.btn--sm    { font-size: 0.8rem; }

@media (max-width: 768px) {
  .profile-layout { grid-template-columns: 1fr; }
  .profile-sidebar { position: static; height: auto; }
  .overview-grid   { grid-template-columns: 1fr; }
}
</style>