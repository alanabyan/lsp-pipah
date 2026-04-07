<template>
  <div class="tab-content">
    <div class="tab-header">
      <h1 class="tab-title">Metode Pembayaran</h1>
      <p class="tab-sub">Pilihan metode pembayaran yang tersedia. Gunakan saat checkout.</p>
    </div>
    <div v-if="loading" class="methods-grid">
      <div v-for="i in 3" :key="i" class="skeleton skeleton--method"></div>
    </div>
    <div v-else-if="methods.length === 0" class="empty-state">
      <span class="material-symbols-outlined">credit_card_off</span>
      <p>Belum ada metode pembayaran tersedia.</p>
    </div>
    <div v-else class="methods-grid">
      <div v-for="m in methods" :key="m.id" class="method-card">
        <div class="method-icon-wrap">
          <img v-if="m.url_logo" :src="logoUrl(m.url_logo)" :alt="m.metode_pembayaran" class="method-logo" />
          <span v-else class="material-symbols-outlined method-icon-fallback">{{ iconFor(m.metode_pembayaran) }}</span>
        </div>
        <div class="method-info">
          <p class="method-name">{{ m.metode_pembayaran }}</p>
          <p class="method-place">{{ m.tempat_bayar }}</p>
          <p class="method-norek">No. Rekening: <strong>{{ m.no_rekening }}</strong></p>
        </div>
        <span class="badge badge--active">Tersedia</span>
      </div>
    </div>
    <div class="info-note">
      <span class="material-symbols-outlined">info</span>
      <p>Metode pembayaran dipilih saat <strong>checkout</strong>. Setelah pesan, upload bukti bayar di <strong>Order
          History</strong>.</p>
    </div>
  </div>
</template>

<script>
import api from '@/services/api.js'
export default {
  name: 'PaymentMethodsTab',
  data() { return { methods: [], loading: true } },
  mounted() { this.fetchMethods() },
  methods: {
    async fetchMethods() {
      this.loading = true
      try {
        // GET /api/metode-bayar
        const res = await api.get('/metode-bayar')
        this.methods = res.data || []
      } catch { this.methods = [] }
      finally { this.loading = false }
    },
    logoUrl(f) { return `${(import.meta.env.VITE_API_URL || 'http://localhost:8000/api').replace('/api', '')}/storage/metode_bayar/${f}` },
    iconFor(name) {
      const n = (name || '').toLowerCase()
      if (n.includes('wallet') || n.includes('ovo') || n.includes('gopay')) return 'account_balance_wallet'
      if (n.includes('transfer') || n.includes('bank')) return 'account_balance'
      if (n.includes('kredit') || n.includes('kartu')) return 'credit_card'
      if (n.includes('tunai') || n.includes('cash')) return 'payments'
      if (n.includes('qris') || n.includes('qr')) return 'qr_code_2'
      return 'payment'
    },
  },
}
</script>

<style scoped>
.tab-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.tab-title {
  font-family: 'Manrope', sans-serif;
  font-size: 1.75rem;
  font-weight: 900;
  color: #fff;
  letter-spacing: -0.03em;
}

.tab-sub {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.35);
  margin-top: 0.3rem;
  line-height: 1.6;
}

.methods-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(18rem, 1fr));
  gap: 1rem;
}

.method-card {
  background: #12151c;
  border: 1px solid rgba(255, 255, 255, 0.07);
  border-radius: 1rem;
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: all 0.2s;
}

.method-card:hover {
  border-color: rgba(74, 222, 128, 0.25);
  transform: translateY(-2px);
}

.method-icon-wrap {
  width: 3.5rem;
  height: 3.5rem;
  border-radius: 0.75rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.07);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  overflow: hidden;
}

.method-logo {
  width: 100%;
  height: 100%;
  object-fit: contain;
  padding: 0.4rem;
}

.method-icon-fallback {
  font-size: 1.5rem;
  color: #4ade80;
}

.method-info {
  flex: 1;
}

.method-name {
  font-size: 0.9rem;
  font-weight: 700;
  color: #fff;
}

.method-place {
  font-size: 0.72rem;
  color: rgba(255, 255, 255, 0.4);
  margin-top: 2px;
}

.method-norek {
  font-size: 0.72rem;
  color: rgba(255, 255, 255, 0.35);
  margin-top: 4px;
}

.method-norek strong {
  color: rgba(255, 255, 255, 0.65);
}

.badge {
  padding: 0.18rem 0.55rem;
  border-radius: 9999px;
  font-size: 0.62rem;
  font-weight: 800;
}

.badge--active {
  background: rgba(74, 222, 128, 0.12);
  color: #4ade80;
}

.info-note {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  background: rgba(99, 102, 241, 0.06);
  border: 1px solid rgba(99, 102, 241, 0.15);
  border-radius: 0.875rem;
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.5);
  line-height: 1.6;
}

.info-note .material-symbols-outlined {
  font-size: 1rem;
  color: #a5b4fc;
  flex-shrink: 0;
  margin-top: 1px;
}

.info-note strong {
  color: rgba(255, 255, 255, 0.75);
}

.skeleton {
  background: linear-gradient(90deg, rgba(255, 255, 255, 0.04) 25%, rgba(255, 255, 255, 0.08) 50%, rgba(255, 255, 255, 0.04) 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
  border-radius: 1rem;
}

.skeleton--method {
  height: 6rem;
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
  padding: 3rem;
  color: rgba(255, 255, 255, 0.2);
}

.empty-state .material-symbols-outlined {
  font-size: 3rem;
}
</style>