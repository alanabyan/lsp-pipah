<template>
  <div class="app-layout">
    <AppSidebar />
    <AppTopbar
      user-name="Administrator"
      :avatar-src="adminAvatar"
      active-nav="Analytics"
    />

    <main class="main-content">

      <div class="page-header">
        <div>
          <p class="page-eyebrow">Konfigurasi dan kelola gateway pembayaran transaksi apotek.</p>
          <h1 class="page-title">Metode Pembayaran</h1>
        </div>
        <div class="page-actions">
          <button class="btn btn--outline" @click="fetchMethods" :disabled="loading">
            <span class="material-symbols-outlined btn-icon" :class="{ spin: loading }">sync</span>
            Sinkronisasi
          </button>
          <button class="btn btn--primary" @click="showAddModal = true">
            <span class="material-symbols-outlined btn-icon">add</span>
            Tambah Metode
          </button>
        </div>
      </div>

      <!-- Stats -->
      <div class="stats-grid">
        <div
          v-for="stat in stats"
          :key="stat.label"
          class="stat-card"
          :style="{ '--accent': stat.accent }"
        >
          <div class="stat-icon-wrap" :style="{ background: stat.iconBg }">
            <span class="material-symbols-outlined" :style="{ color: stat.accent }">{{ stat.icon }}</span>
          </div>
          <div class="stat-body">
            <p class="stat-label">{{ stat.label }}</p>
            <p class="stat-value">{{ stat.value }}</p>
          </div>
          <div class="stat-change" :class="stat.changeType === 'up' ? 'change--up' : 'change--warn'">
            <span class="material-symbols-outlined change-icon">
              {{ stat.changeType === 'up' ? 'trending_up' : 'warning' }}
            </span>
            {{ stat.change }}
          </div>
        </div>
      </div>

      <!-- Table Card -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Daftar Opsi Pembayaran</h3>
          <div class="table-actions">
            <button class="icon-btn" title="Filter">
              <span class="material-symbols-outlined">filter_list</span>
            </button>
            <button class="icon-btn" title="Sort">
              <span class="material-symbols-outlined">sort</span>
            </button>
          </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="state-box">
          <span class="material-symbols-outlined spin">progress_activity</span>
          <p>Memuat data...</p>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="state-box state-box--error">
          <span class="material-symbols-outlined">error</span>
          <p>{{ error }}</p>
          <button class="btn btn--primary" @click="fetchMethods">Coba Lagi</button>
        </div>

        <!-- Empty -->
        <div v-else-if="paymentMethods.length === 0" class="state-box">
          <span class="material-symbols-outlined">inbox</span>
          <p>Belum ada metode pembayaran.</p>
        </div>

        <!-- Table -->
        <div v-else class="table-wrapper">
          <table class="methods-table">
            <thead>
              <tr>
                <th></th>
                <th>Pay Method</th>
                <th>No. Rekening</th>
                <th>Tgl. Ditambahkan</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="method in paymentMethods"
                :key="method.id"
                class="table-row"
              >
                <td>
                  <div class="method-icon" :style="{ background: iconStyle(method).bg }">
                    <span class="material-symbols-outlined" :style="{ color: iconStyle(method).color }">
                      {{ iconStyle(method).icon }}
                    </span>
                  </div>
                </td>
                <td>
                  <p class="method-name">{{ method.metode_pembayaran }}</p>
                  <p class="method-sub">{{ method.tempat_bayar }}</p>
                </td>
                <td class="provider-cell">{{ method.no_rekening }}</td>
                <td class="date-cell">{{ formatDate(method.created_at) }}</td>
                <td>
                  <button
                    class="action-btn action-btn--delete"
                    @click="confirmDelete(method)"
                    title="Hapus"
                    :disabled="deletingId === method.id"
                  >
                    <span class="material-symbols-outlined">
                      {{ deletingId === method.id ? 'progress_activity' : 'delete' }}
                    </span>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <p class="table-footer-note" v-if="!loading && !error">
          <span class="material-symbols-outlined" style="font-size:0.9rem;vertical-align:middle;">info</span>
          Menampilkan {{ paymentMethods.length }} metode pembayaran terdaftar.
        </p>
      </div>

      <!-- Bottom Cards -->
      <div class="bottom-grid">
        <div class="bottom-card bottom-card--primary">
          <div class="bottom-card-icon">
            <span class="material-symbols-outlined">account_balance</span>
          </div>
          <div>
            <h4 class="bottom-card-title">Automated Reconciliation</h4>
            <p class="bottom-card-sub">Rekonsiliasi otomatis setiap hari pukul 23:59</p>
          </div>
          <button class="bottom-card-btn">Konfigurasi</button>
        </div>
        <div class="bottom-card bottom-card--secondary">
          <div class="bottom-card-icon bottom-card-icon--blue">
            <span class="material-symbols-outlined">security</span>
          </div>
          <div>
            <h4 class="bottom-card-title">Security Protocol</h4>
            <p class="bottom-card-sub">SSL & enkripsi AES-256 aktif pada semua gateway</p>
          </div>
          <button class="bottom-card-btn bottom-card-btn--blue">Lihat Detail</button>
        </div>
      </div>

    </main>

    <!-- ── Add Modal ── -->
    <div v-if="showAddModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h3 class="modal-title">Tambah Metode Pembayaran</h3>
          <button class="icon-btn" @click="closeModal">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Nama Metode <span class="required">*</span></label>
            <input
              v-model="newMethod.metode_pembayaran"
              type="text"
              class="form-input"
              placeholder="e.g. GoPay"
              maxlength="30"
            />
          </div>
          <div class="form-group">
            <label class="form-label">Tempat Bayar <span class="required">*</span></label>
            <input
              v-model="newMethod.tempat_bayar"
              type="text"
              class="form-input"
              placeholder="e.g. Gojek"
              maxlength="50"
            />
          </div>
          <div class="form-group">
            <label class="form-label">No. Rekening <span class="required">*</span></label>
            <input
              v-model="newMethod.no_rekening"
              type="text"
              class="form-input"
              placeholder="e.g. 1234567890"
              maxlength="25"
            />
          </div>
          <div class="form-group">
            <label class="form-label">Logo <span class="form-hint">(opsional, jpg/png, maks 1MB)</span></label>
            <input
              type="file"
              class="form-input"
              accept="image/jpg,image/jpeg,image/png"
              @change="onFileChange"
            />
          </div>
          <p v-if="formError" class="form-error">{{ formError }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn--outline" @click="closeModal" :disabled="saving">Batal</button>
          <button class="btn btn--primary" @click="addMethod" :disabled="saving">
            <span v-if="saving" class="material-symbols-outlined spin btn-icon">progress_activity</span>
            {{ saving ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ── Delete Confirm Modal ── -->
    <div v-if="deleteTarget" class="modal-overlay" @click.self="deleteTarget = null">
      <div class="modal modal--sm">
        <div class="modal-header">
          <h3 class="modal-title">Hapus Metode?</h3>
          <button class="icon-btn" @click="deleteTarget = null">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <div class="modal-body">
          <p class="confirm-text">
            Yakin ingin menghapus metode
            <strong>{{ deleteTarget.metode_pembayaran }}</strong>?
            Tindakan ini tidak bisa dibatalkan.
          </p>
        </div>
        <div class="modal-footer">
          <button class="btn btn--outline" @click="deleteTarget = null">Batal</button>
          <button class="btn btn--danger" @click="deleteMethod">Ya, Hapus</button>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import AppSidebar from '@/layouts/AppSidebar.vue'
import AppTopbar  from '@/layouts/AppTopbar.vue'
import { MetodeBayarService } from '@/services/api.js'

// Palet icon otomatis berdasarkan index
const ICON_PALETTE = [
  { icon: 'payments',        bg: '#ecfdf5', color: '#059669' },
  { icon: 'qr_code_2',       bg: '#eff6ff', color: '#3b82f6' },
  { icon: 'account_balance', bg: '#fff7ed', color: '#f97316' },
  { icon: 'credit_card',     bg: '#fdf4ff', color: '#a855f7' },
  { icon: 'wallet',          bg: '#fefce8', color: '#ca8a04' },
  { icon: 'smartphone',      bg: '#f0fdf4', color: '#16a34a' },
]

export default {
  name: 'PaymentMethods',
  components: { AppSidebar, AppTopbar },

  data() {
    return {
      adminAvatar: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCUFa9tr34dUoJFF2I_UADU-EZmG8Xs40HsxVEaRqVLSnOarJ4PvDmSNz3OW0PhVYM6YDe69edcdgGYptt7DxHELYfqKow8p2GNdnVInGCyca4FpTCKMcxGtvQWRYwPGOcHJ-pg2NrogB-FfRCBZmun9kqyLyMFX739yD_3cgAf_mLCM0Dg_QXNxph_gE-BjdcgVGF4vjHCTfFdfB9RHjDbGtGiB7Zz3WsP7zkW2IbDuT2hn9UZHquTs8upmFkLnVr8V_m2uV08IJQ',

      // State
      loading:    false,
      error:      null,
      saving:     false,
      deletingId: null,
      formError:  null,

      // Data dari API
      paymentMethods: [],

      // Modal
      showAddModal: false,
      deleteTarget: null,

      // Form tambah
      newMethod: {
        metode_pembayaran: '',
        tempat_bayar: '',
        no_rekening: '',
        url_logo: null,   // File object
      },

      // Stats (statis — bisa dihubungkan ke API analytics nanti)
      stats: [
        {
          label: 'Total Transaksi Tunai',
          value: 'Rp 45.2M',
          change: '+9%',
          changeType: 'up',
          icon: 'payments',
          iconBg: '#ecfdf5',
          accent: '#059669',
        },
        {
          label: 'Pertumbuhan QRIS',
          value: 'Rp 12.8M',
          change: '+48%',
          changeType: 'up',
          icon: 'qr_code_2',
          iconBg: '#eff6ff',
          accent: '#3b82f6',
        },
        {
          label: 'Rata-rata Merchant Fee',
          value: '1.85%',
          change: 'Stabil',
          changeType: 'up',
          icon: 'percent',
          iconBg: '#fdf4ff',
          accent: '#a855f7',
        },
        {
          label: 'Total Metode Aktif',
          value: this.totalAktif ?? '-',
          change: 'Dari database',
          changeType: 'up',
          icon: 'account_balance',
          iconBg: '#fff7ed',
          accent: '#f97316',
        },
      ],
    }
  },

  computed: {
    totalAktif() {
      return this.paymentMethods.length
    },
  },

  created() {
    this.fetchMethods()
  },

  methods: {
    // ── Fetch semua metode bayar ──────────────────────────────
    async fetchMethods() {
      this.loading = true
      this.error   = null
      try {
        const res = await MetodeBayarService.getAll()
        this.paymentMethods = res.data
        // Update stat "Total Metode Aktif" secara dinamis
        this.stats[3].value = String(res.data.length)
      } catch (err) {
        this.error = err.response?.data?.message ?? 'Gagal memuat data. Periksa koneksi ke server.'
      } finally {
        this.loading = false
      }
    },

    // ── Simpan metode baru ────────────────────────────────────
    async addMethod() {
      this.formError = null

      // Validasi
      if (!this.newMethod.metode_pembayaran.trim()) {
        this.formError = 'Nama metode wajib diisi.'
        return
      }
      if (!this.newMethod.tempat_bayar.trim()) {
        this.formError = 'Tempat bayar wajib diisi.'
        return
      }
      if (!this.newMethod.no_rekening.trim()) {
        this.formError = 'No. rekening wajib diisi.'
        return
      }

      this.saving = true
      try {
        // Pakai FormData karena ada upload file (opsional)
        const formData = new FormData()
        formData.append('metode_pembayaran', this.newMethod.metode_pembayaran)
        formData.append('tempat_bayar',      this.newMethod.tempat_bayar)
        formData.append('no_rekening',       this.newMethod.no_rekening)
        if (this.newMethod.url_logo) {
          formData.append('url_logo', this.newMethod.url_logo)
        }

        await MetodeBayarService.create(formData)
        await this.fetchMethods()   // Refresh list dari server
        this.closeModal()
      } catch (err) {
        this.formError = err.response?.data?.message
          ?? Object.values(err.response?.data?.errors ?? {})[0]?.[0]
          ?? 'Gagal menyimpan. Coba lagi.'
      } finally {
        this.saving = false
      }
    },

    // ── Konfirmasi hapus ──────────────────────────────────────
    confirmDelete(method) {
      this.deleteTarget = method
    },

    // ── Hapus metode ──────────────────────────────────────────
    async deleteMethod() {
      if (!this.deleteTarget) return
      this.deletingId = this.deleteTarget.id
      try {
        await MetodeBayarService.delete(this.deleteTarget.id)
        this.paymentMethods = this.paymentMethods.filter(
          (m) => m.id !== this.deleteTarget.id
        )
        this.stats[3].value = String(this.paymentMethods.length)
      } catch (err) {
        alert(err.response?.data?.message ?? 'Gagal menghapus data.')
      } finally {
        this.deletingId  = null
        this.deleteTarget = null
      }
    },

    // ── Handle file input ─────────────────────────────────────
    onFileChange(e) {
      this.newMethod.url_logo = e.target.files[0] ?? null
    },

    // ── Reset & tutup modal ───────────────────────────────────
    closeModal() {
      this.showAddModal = false
      this.formError    = null
      this.newMethod    = {
        metode_pembayaran: '',
        tempat_bayar: '',
        no_rekening: '',
        url_logo: null,
      }
    },

    // ── Helper: icon & warna otomatis berdasarkan index ───────
    iconStyle(method) {
      const idx = this.paymentMethods.indexOf(method) % ICON_PALETTE.length
      return ICON_PALETTE[idx]
    },

    // ── Format tanggal dari Laravel timestamp ─────────────────
    formatDate(dateStr) {
      if (!dateStr) return '-'
      return new Date(dateStr).toLocaleDateString('id-ID', {
        day: '2-digit', month: 'short', year: 'numeric',
      })
    },
  },
}
</script>

<style scoped>
.app-layout {
  display: grid;
  grid-template-columns: 16rem 1fr;
  min-height: 100vh;
  background: #f0f4f8;
  font-family: 'Plus Jakarta Sans', sans-serif;
  color: #171c1f;
}
.main-content {
  grid-column: 2;
  margin-top: 4rem;
  padding: 2rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.page-header { display: flex; justify-content: space-between; align-items: flex-end; }
.page-eyebrow { font-size: 0.78rem; color: #6b7280; margin-bottom: 0.25rem; }
.page-title {
  font-family: 'Manrope', sans-serif;
  font-size: 1.75rem; font-weight: 800;
  color: #064e3b; letter-spacing: -0.03em;
}
.page-actions { display: flex; gap: 0.75rem; }

.btn {
  display: inline-flex; align-items: center; gap: 0.4rem;
  padding: 0.55rem 1.1rem; border: none; border-radius: 0.625rem;
  font-size: 0.8125rem; font-weight: 700; cursor: pointer; transition: all 0.15s;
  font-family: inherit;
}
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
.btn--primary { background: #059669; color: #fff; box-shadow: 0 2px 8px rgba(5,150,105,0.25); }
.btn--primary:hover:not(:disabled) { background: #047857; }
.btn--outline { background: #fff; color: #374151; border: 1px solid #e5e7eb; }
.btn--outline:hover:not(:disabled) { background: #f9fafb; }
.btn--danger  { background: #dc2626; color: #fff; }
.btn--danger:hover { background: #b91c1c; }
.btn-icon { font-size: 1rem; }

/* Stats */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}
.stat-card {
  background: #fff; border-radius: 0.875rem; padding: 1.25rem;
  box-shadow: 0 1px 4px rgba(0,0,0,0.05);
  display: flex; flex-direction: column; gap: 0.75rem;
  border-top: 3px solid var(--accent);
}
.stat-icon-wrap {
  width: 2.5rem; height: 2.5rem; border-radius: 0.625rem;
  display: flex; align-items: center; justify-content: center;
}
.stat-icon-wrap .material-symbols-outlined { font-size: 1.3rem; }
.stat-label { font-size: 0.72rem; color: #6b7280; font-weight: 500; }
.stat-value { font-family: 'Manrope', sans-serif; font-size: 1.3rem; font-weight: 800; color: #171c1f; }
.stat-change {
  display: inline-flex; align-items: center; gap: 0.25rem;
  font-size: 0.7rem; font-weight: 700; padding: 0.15rem 0.5rem;
  border-radius: 9999px; width: fit-content;
}
.change--up   { background: #ecfdf5; color: #059669; }
.change--warn { background: #fff7ed; color: #f97316; }
.change-icon  { font-size: 0.8rem; }

/* Card / Table */
.card {
  background: #fff; border-radius: 0.875rem;
  padding: 1.5rem; box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}
.card-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 1.25rem;
}
.card-title { font-family: 'Manrope', sans-serif; font-size: 1rem; font-weight: 700; color: #064e3b; }
.table-actions { display: flex; gap: 0.25rem; }

.icon-btn {
  background: none; border: none; cursor: pointer;
  padding: 0.4rem; color: #6b7280; border-radius: 0.5rem;
  transition: background 0.15s; display: flex;
}
.icon-btn:hover { background: #f0f4f8; color: #374151; }

/* State boxes */
.state-box {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; gap: 0.75rem;
  padding: 3rem; color: #6b7280; font-size: 0.875rem;
}
.state-box .material-symbols-outlined { font-size: 2rem; color: #059669; }
.state-box--error .material-symbols-outlined { color: #dc2626; }
.state-box--error { color: #dc2626; }

@keyframes spin { to { transform: rotate(360deg); } }
.spin { display: inline-block; animation: spin 1s linear infinite; }

.table-wrapper { overflow-x: auto; }
.methods-table { width: 100%; border-collapse: collapse; }
.methods-table thead tr { border-bottom: 1px solid #f0f4f8; }
.methods-table th {
  padding: 0 0.75rem 0.75rem;
  font-size: 0.7rem; font-weight: 600;
  text-transform: uppercase; letter-spacing: 0.07em; color: #9ca3af;
  text-align: left;
}
.table-row { transition: background 0.12s; }
.table-row:hover { background: #f9fafb; }
.methods-table td { padding: 0.85rem 0.75rem; vertical-align: middle; }

.method-icon {
  width: 2.25rem; height: 2.25rem; border-radius: 0.5rem;
  display: flex; align-items: center; justify-content: center;
}
.method-icon .material-symbols-outlined { font-size: 1.15rem; }
.method-name { font-size: 0.875rem; font-weight: 700; color: #171c1f; }
.method-sub  { font-size: 0.7rem; color: #9ca3af; margin-top: 1px; }
.provider-cell { font-size: 0.8125rem; color: #6b7280; }
.date-cell     { font-size: 0.8rem; color: #6b7280; }

.action-btn {
  background: none; border: none; cursor: pointer;
  padding: 0.35rem; color: #9ca3af; border-radius: 0.4rem;
  transition: all 0.15s; display: flex;
}
.action-btn--delete:hover:not(:disabled) { background: #fee2e2; color: #dc2626; }
.action-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.table-footer-note {
  margin-top: 1.25rem; font-size: 0.75rem; color: #9ca3af;
  padding-top: 1rem; border-top: 1px solid #f0f4f8;
  display: flex; align-items: center; gap: 0.35rem;
}

/* Bottom grid */
.bottom-grid {
  display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;
}
.bottom-card {
  border-radius: 0.875rem; padding: 1.25rem;
  display: flex; align-items: center; gap: 1rem;
}
.bottom-card--primary  { background: #ecfdf5; border: 1px solid #a7f3d0; }
.bottom-card--secondary{ background: #eff6ff; border: 1px solid #bfdbfe; }
.bottom-card-icon {
  width: 3rem; height: 3rem; border-radius: 0.75rem;
  background: #fff; display: flex; align-items: center; justify-content: center;
  box-shadow: 0 1px 4px rgba(0,0,0,0.07); flex-shrink: 0;
}
.bottom-card-icon .material-symbols-outlined { color: #059669; font-size: 1.4rem; }
.bottom-card-icon--blue .material-symbols-outlined { color: #3b82f6; }
.bottom-card-title { font-family: 'Manrope', sans-serif; font-size: 0.9rem; font-weight: 700; color: #064e3b; }
.bottom-card--secondary .bottom-card-title { color: #1e40af; }
.bottom-card-sub { font-size: 0.72rem; color: #6b7280; margin-top: 2px; }
.bottom-card-btn {
  margin-left: auto; flex-shrink: 0;
  padding: 0.45rem 0.9rem; border: none; border-radius: 0.5rem;
  font-size: 0.75rem; font-weight: 700; cursor: pointer; transition: all 0.15s;
  background: #059669; color: #fff; font-family: inherit;
}
.bottom-card-btn:hover { background: #047857; }
.bottom-card-btn--blue { background: #3b82f6; }
.bottom-card-btn--blue:hover { background: #2563eb; }

/* Modal */
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.35);
  backdrop-filter: blur(4px);
  display: flex; align-items: center; justify-content: center;
  z-index: 100;
}
.modal {
  background: #fff; border-radius: 1rem;
  width: 100%; max-width: 26rem;
  box-shadow: 0 20px 60px rgba(0,0,0,0.15);
  overflow: hidden;
}
.modal--sm { max-width: 22rem; }
.modal-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 1.25rem 1.5rem; border-bottom: 1px solid #f0f4f8;
}
.modal-title { font-family: 'Manrope', sans-serif; font-size: 1rem; font-weight: 700; color: #064e3b; }
.modal-body  { padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
.form-group  { display: flex; flex-direction: column; gap: 0.4rem; }
.form-label  { font-size: 0.78rem; font-weight: 600; color: #374151; }
.form-hint   { font-weight: 400; color: #9ca3af; }
.required    { color: #dc2626; }
.form-input  {
  padding: 0.55rem 0.75rem; border: 1px solid #e5e7eb;
  border-radius: 0.5rem; font-size: 0.8125rem; outline: none;
  transition: border 0.15s; font-family: inherit; color: #171c1f;
}
.form-input:focus { border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,0.1); }
.form-error { font-size: 0.78rem; color: #dc2626; font-weight: 500; }
.confirm-text { font-size: 0.875rem; color: #374151; line-height: 1.6; }
.modal-footer {
  display: flex; justify-content: flex-end; gap: 0.75rem;
  padding: 1rem 1.5rem; border-top: 1px solid #f0f4f8;
}
</style>