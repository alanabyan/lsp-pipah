<template>
  <div class="tab-content">
    <div class="tab-header">
      <h1 class="tab-title">Order History</h1>
      <p class="tab-sub">Riwayat pesanan kamu. Klik "Lihat Detail" untuk melihat item, atau upload bukti bayar.</p>
    </div>
    <div v-if="loading" class="orders-list">
      <div v-for="i in 3" :key="i" class="skeleton skeleton--order"></div>
    </div>
    <div v-else-if="orders.length === 0" class="empty-state">
      <span class="material-symbols-outlined">receipt_long</span>
      <p>Belum ada pesanan.</p>
      <router-link to="/" class="btn btn--green">Mulai Belanja</router-link>
    </div>
    <div v-else class="orders-list">
      <div v-for="order in orders" :key="order.id" class="order-card">
        <div class="order-head">
          <div class="order-meta">
            <span class="order-id">#ORD-{{ pad(order.id) }}</span>
            <span class="order-date">{{ fmtDate(order.tgl_penjualan) }}</span>
          </div>
          <div class="order-badges">
            <span :class="['badge', statusCls(order.status_order)]">{{ order.status_order }}</span>
          </div>
          <span class="order-total">{{ fmtRp(order.total_bayar) }}</span>
          <div class="order-acts">
            <button v-if="order.status_order === 'Menunggu Konfirmasi'" class="btn btn--upload"
              @click="openUpload(order)">
              <span class="material-symbols-outlined">upload_file</span>
              Upload Bukti Bayar
            </button>
            <button v-if="order.status_order === 'Menunggu Konfirmasi'" class="btn btn--cancel"
              :disabled="cancelling === order.id" @click="cancelOrder(order)">{{ cancelling === order.id ? '...' :
              'Batalkan' }}</button>
            <button class="btn-link" @click="toggleDetail(order.id)">
              {{ expanded === order.id ? 'Sembunyikan ▲' : 'Lihat Detail ▼' }}
            </button>
          </div>
        </div>
        <transition name="slide">
          <div v-if="expanded === order.id" class="order-detail">
            <div v-if="detailLoading" class="detail-loading">
              <div v-for="i in 2" :key="i" class="skeleton skeleton--row"></div>
            </div>
            <div v-else-if="detailItems.length === 0" class="detail-empty">Tidak ada item.</div>
            <div v-else>
              <div class="detail-head"><span>Nama Obat</span><span>Jumlah</span><span>Harga</span><span>Subtotal</span>
              </div>
              <div v-for="d in detailItems" :key="d.id" class="detail-row">
                <span class="d-name">{{ d.nama_obat }}</span>
                <span class="d-qty">{{ d.jumlah_beli }}</span>
                <span class="d-price">{{ fmtRp(d.harga_beli) }}</span>
                <span class="d-sub">{{ fmtRp(d.subtotal) }}</span>
              </div>
            </div>
          </div>
        </transition>
      </div>
    </div>

    <div v-if="orders.length > 0" class="refill-banner">
      <span class="material-symbols-outlined refill-icon">local_pharmacy</span>
      <div>
        <p class="refill-title">Butuh obat rutin?</p>
        <p class="refill-sub">Kunjungi katalog dan tambahkan ke keranjang.</p>
      </div>
      <router-link to="/" class="btn btn--green btn--sm">Belanja Lagi</router-link>
    </div>

    <!-- Upload Modal -->
    <div v-if="showUploadModal" class="modal-overlay" @click.self="showUploadModal = false">
      <div class="modal">
        <div class="modal-header">
          <h3 class="modal-title">Upload Bukti Pembayaran</h3>
          <button class="icon-btn" @click="showUploadModal = false"><span
              class="material-symbols-outlined">close</span></button>
        </div>
        <div class="modal-body">
          <p class="modal-desc">Order: <strong>#ORD-{{ pad(uploadTarget?.id || 0) }}</strong> — Total: <strong>{{
            fmtRp(uploadTarget?.total_bayar) }}</strong></p>
          <div class="form-group">
            <label class="form-label">Jumlah yang Dibayarkan</label>
            <input v-model="uploadForm.jumlah_bayar" type="number" class="form-input"
              :placeholder="uploadTarget?.total_bayar" />
          </div>
          <div class="form-group">
            <label class="form-label">Foto Bukti Bayar (JPG/PNG, max 2MB)</label>
            <div class="file-zone" @click="$refs.fileInput.click()">
              <span class="material-symbols-outlined">cloud_upload</span>
              <p>{{ uploadForm.file ? uploadForm.file.name : 'Klik untuk pilih foto' }}</p>
              <input ref="fileInput" type="file" accept="image/jpeg,image/png" style="display:none" @change="onFile" />
            </div>
          </div>
          <div v-if="uploadErr" class="alert alert--error">{{ uploadErr }}</div>
        </div>
        <div class="modal-footer">
          <button class="btn btn--outline" @click="showUploadModal = false">Batal</button>
          <button class="btn btn--green" :disabled="uploading" @click="submitUpload">
            <span v-if="uploading" class="material-symbols-outlined spin">progress_activity</span>
            {{ uploading ? 'Mengupload...' : 'Upload Bukti' }}
          </button>
        </div>
      </div>
    </div>

    <transition name="toast">
      <div v-if="toast.show" :class="['toast', 'toast--' + toast.type]">
        <span class="material-symbols-outlined">{{ toast.type === 'success' ? 'check_circle' : 'error' }}</span>
        {{ toast.message }}
      </div>
    </transition>
  </div>
</template>

<script>
import api from '@/services/api.js'
export default {
  name: 'OrderHistoryTab',
  data() {
    return {
      orders: [], loading: true,
      expanded: null, detailItems: [], detailLoading: false,
      cancelling: null,
      showUploadModal: false, uploadTarget: null,
      uploading: false, uploadErr: null,
      uploadForm: { jumlah_bayar: '', file: null },
      toast: { show: false, message: '', type: 'success' },
    }
  },
  mounted() { this.fetchOrders() },
  methods: {
    async fetchOrders() {
  this.loading = true
  try {
    const res = await api.get('/penjualan')
    // Ambil data user dari localStorage
    const p = JSON.parse(localStorage.getItem('pelanggan_data') || '{}')
    
    // Debugging: Cek apakah ID pelanggan terbaca
    console.log("ID Pelanggan di LocalStorage:", p.id)
    console.log("Data dari API:", res.data)

    this.orders = (res.data || [])
      .filter(o => {
        // Pastikan kedua ID ada dan ubah ke Number untuk memastikan kecocokan
        return p.id && Number(o.id_pelanggan) === Number(p.id)
      }) 
      .sort((a, b) => new Date(b.tgl_penjualan) - new Date(a.tgl_penjualan))
  } catch (err) { 
    console.error("Gagal mengambil history:", err)
    this.orders = [] 
  } finally { 
    this.loading = false 
  }
},
    async toggleDetail(orderId) {
      if (this.expanded === orderId) { this.expanded = null; return }
      this.expanded = orderId; this.detailItems = []; this.detailLoading = true
      try {
        // Fetch detail penjualan + daftar obat secara paralel
        // GET /api/detail-penjualan  +  GET /api/obat
        const [detailRes, obatRes] = await Promise.all([
          api.get('/detail-penjualan'),
          api.get('/obat'),
        ])

        const allDetail = detailRes.data || []
        const allObat = obatRes.data || []

        // Filter detail milik order ini, lalu inject nama_obat dari daftar obat
        this.detailItems = allDetail
          .filter(d => d.id_penjualan === orderId)
          .map(d => ({
            ...d,
            nama_obat: allObat.find(o => o.id === d.id_obat)?.nama_obat || `Obat #${d.id_obat}`,
          }))
      } catch { this.detailItems = [] }
      finally { this.detailLoading = false }
    },
    async cancelOrder(order) {
  if (!confirm('Yakin ingin membatalkan pesanan ini? Stok obat akan dikembalikan.')) return
  
  this.cancelling = order.id
  try {
    // 1. Panggil API dengan method DELETE ke route pembatalan
    // Pastikan routenya sesuai dengan yang kamu buat di api.php nanti
    await api.delete(`/penjualan/${order.id}/batal`)
    
    // 2. Tampilkan notifikasi sukses
    this.showT('Pesanan dibatalkan dan stok telah dikembalikan.', 'success')
    
    // 3. Ambil ulang data agar daftar di layar langsung ter-update (hilang dari list)
    await this.fetchOrders()
  } catch (err) { 
    console.error("Gagal batal:", err)
    this.showT(err.response?.data?.message || 'Gagal membatalkan pesanan.', 'error') 
  } finally { 
    this.cancelling = null 
  }
},
    openUpload(order) {
      this.uploadTarget = order
      this.uploadForm = { jumlah_bayar: order.total_bayar, file: null }
      this.uploadErr = null; this.showUploadModal = true
    },
    onFile(e) { this.uploadForm.file = e.target.files[0] },
    async submitUpload() {
      if (!this.uploadForm.file) { this.uploadErr = 'Pilih foto bukti bayar.'; return }
      if (!this.uploadForm.jumlah_bayar) { this.uploadErr = 'Masukkan jumlah yang dibayarkan.'; return }
      this.uploading = true; this.uploadErr = null
      try {
        const fd = new FormData()
        fd.append('id_penjualan', this.uploadTarget.id)
        fd.append('jumlah_bayar', this.uploadForm.jumlah_bayar)
        fd.append('bukti_bayar', this.uploadForm.file)
        // POST /api/pembayaran
        await api.post('/pelanggan/bayar', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
        this.showUploadModal = false
        this.showT('Bukti pembayaran berhasil diupload! Menunggu konfirmasi admin.')
        await this.fetchOrders()
      } catch (err) { this.uploadErr = err.response?.data?.message || 'Gagal upload.' }
      finally { this.uploading = false }
    },
    pad(id) { return String(id || 0).padStart(5, '0') },
    fmtDate(d) { return d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '—' },
    fmtRp(v) { return 'Rp ' + Number(v || 0).toLocaleString('id-ID') },
    statusCls(s) {
      const m = { 'Menunggu Konfirmasi': 'badge--wait', 'Diproses': 'badge--process', 'Menunggu Kurir': 'badge--ship', 'Selesai': 'badge--done', 'Dibatalkan Pembeli': 'badge--cancel', 'Dibatalkan Penjual': 'badge--cancel' }
      return m[s] || 'badge--wait'
    },
    showT(msg, type = 'success') { this.toast = { show: true, message: msg, type }; setTimeout(() => { this.toast.show = false }, 3500) },
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

.orders-list {
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
}

.order-card {
  background: #12151c;
  border: 1px solid rgba(255, 255, 255, 0.07);
  border-radius: 1rem;
  overflow: hidden;
}

.order-head {
  display: grid;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  grid-template-columns: auto 1fr auto auto;
  align-items: center;
}

.order-meta {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.order-id {
  font-family: monospace;
  font-size: 0.9rem;
  font-weight: 800;
  color: #fff;
}

.order-date {
  font-size: 0.7rem;
  color: rgba(255, 255, 255, 0.35);
}

.order-badges {
  display: flex;
  gap: 0.35rem;
  flex-wrap: wrap;
}

.badge {
  padding: 0.18rem 0.55rem;
  border-radius: 9999px;
  font-size: 0.63rem;
  font-weight: 800;
}

.badge--wait {
  background: rgba(251, 191, 36, 0.12);
  color: #fbbf24;
}

.badge--process {
  background: rgba(99, 102, 241, 0.12);
  color: #a5b4fc;
}

.badge--ship {
  background: rgba(56, 189, 248, 0.12);
  color: #7dd3fc;
}

.badge--done {
  background: rgba(74, 222, 128, 0.12);
  color: #4ade80;
}

.badge--cancel {
  background: rgba(248, 113, 113, 0.12);
  color: #f87171;
}

.order-total {
  font-family: 'Manrope', sans-serif;
  font-size: 0.95rem;
  font-weight: 800;
  color: #fff;
  white-space: nowrap;
}

.order-acts {
  display: flex;
  gap: 0.4rem;
  align-items: center;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.4rem 0.875rem;
  border: none;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s;
  font-family: inherit;
  text-decoration: none;
}

.btn--green {
  background: #4ade80;
  color: #052e16;
}

.btn--green:hover {
  background: #22c55e;
}

.btn--upload {
  background: rgba(74, 222, 128, 0.12);
  color: #4ade80;
  border: 1px solid rgba(74, 222, 128, 0.25);
}

.btn--upload:hover {
  background: #4ade80;
  color: #052e16;
}

.btn--cancel {
  background: rgba(248, 113, 113, 0.1);
  color: #f87171;
  border: 1px solid rgba(248, 113, 113, 0.2);
}

.btn--cancel:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn--outline {
  background: rgba(255, 255, 255, 0.05);
  color: rgba(255, 255, 255, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.btn--sm {
  font-size: 0.72rem;
  padding: 0.35rem 0.75rem;
}

.btn .material-symbols-outlined {
  font-size: 0.9rem;
}

.btn-link {
  background: none;
  border: none;
  font-size: 0.75rem;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.35);
  cursor: pointer;
  font-family: inherit;
  white-space: nowrap;
  transition: color 0.15s;
}

.btn-link:hover {
  color: #4ade80;
}

.order-detail {
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  padding: 1rem 1.25rem;
}

.detail-head {
  display: grid;
  grid-template-columns: 1fr 80px 110px 110px;
  gap: 0.5rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.07em;
  color: rgba(255, 255, 255, 0.25);
}

.detail-row {
  display: grid;
  grid-template-columns: 1fr 80px 110px 110px;
  gap: 0.5rem;
  padding: 0.55rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
  font-size: 0.8rem;
}

.detail-row:last-child {
  border-bottom: none;
}

.d-name {
  color: rgba(255, 255, 255, 0.8);
  font-weight: 600;
}

.d-qty {
  color: rgba(255, 255, 255, 0.45);
  text-align: center;
}

.d-price {
  color: rgba(255, 255, 255, 0.45);
  text-align: right;
}

.d-sub {
  color: #4ade80;
  font-weight: 700;
  text-align: right;
}

.detail-empty {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.25);
  padding: 0.5rem 0;
}

.detail-loading {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.refill-banner {
  display: flex;
  align-items: center;
  gap: 1rem;
  background: rgba(74, 222, 128, 0.06);
  border: 1px solid rgba(74, 222, 128, 0.15);
  border-radius: 1rem;
  padding: 1.25rem;
}

.refill-icon {
  font-size: 2rem;
  color: #4ade80;
  flex-shrink: 0;
}

.refill-title {
  font-size: 0.875rem;
  font-weight: 700;
  color: #fff;
}

.refill-sub {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.4);
}

.refill-banner .btn {
  margin-left: auto;
  flex-shrink: 0;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.65);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 300;
  padding: 1rem;
}

.modal {
  background: #12151c;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 1rem;
  width: 100%;
  max-width: 26rem;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.07);
}

.modal-title {
  font-family: 'Manrope', sans-serif;
  font-size: 1rem;
  font-weight: 700;
  color: #fff;
}

.icon-btn {
  background: none;
  border: none;
  cursor: pointer;
  color: rgba(255, 255, 255, 0.4);
  display: flex;
  padding: 0.25rem;
  border-radius: 0.375rem;
}

.icon-btn:hover {
  color: #fff;
}

.modal-body {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.modal-desc {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.45);
  line-height: 1.5;
}

.modal-desc strong {
  color: rgba(255, 255, 255, 0.8);
}

.modal-footer {
  padding: 1rem 1.5rem;
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  border-top: 1px solid rgba(255, 255, 255, 0.07);
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.form-label {
  font-size: 0.7rem;
  font-weight: 700;
  color: rgba(255, 255, 255, 0.35);
  text-transform: uppercase;
  letter-spacing: 0.07em;
}

.form-input {
  padding: 0.6rem 0.875rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1.5px solid rgba(255, 255, 255, 0.08);
  border-radius: 0.5rem;
  color: #fff;
  font-size: 0.875rem;
  outline: none;
  font-family: inherit;
  transition: border 0.15s;
}

.form-input:focus {
  border-color: #4ade80;
}

.file-zone {
  border: 2px dashed rgba(255, 255, 255, 0.12);
  border-radius: 0.625rem;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: border 0.15s;
  color: rgba(255, 255, 255, 0.35);
}

.file-zone:hover {
  border-color: #4ade80;
  color: #4ade80;
}

.file-zone .material-symbols-outlined {
  font-size: 1.75rem;
}

.file-zone p {
  font-size: 0.78rem;
  font-weight: 600;
}

.alert {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 0.875rem;
  border-radius: 0.5rem;
  font-size: 0.78rem;
  font-weight: 600;
}

.alert--error {
  background: rgba(248, 113, 113, 0.1);
  color: #fca5a5;
  border: 1px solid rgba(248, 113, 113, 0.2);
}

.skeleton {
  background: linear-gradient(90deg, rgba(255, 255, 255, 0.04) 25%, rgba(255, 255, 255, 0.08) 50%, rgba(255, 255, 255, 0.04) 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
  border-radius: 0.875rem;
}

.skeleton--order {
  height: 5rem;
}

.skeleton--row {
  height: 2.5rem;
  border-radius: 0.5rem;
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
  gap: 1rem;
  padding: 3rem;
  color: rgba(255, 255, 255, 0.2);
}

.empty-state .material-symbols-outlined {
  font-size: 3rem;
}

.slide-enter-active,
.slide-leave-active {
  transition: all 0.2s;
  overflow: hidden;
  max-height: 40rem;
}

.slide-enter-from,
.slide-leave-to {
  max-height: 0;
  opacity: 0;
}

.spin {
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
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
  z-index: 400;
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
</style>