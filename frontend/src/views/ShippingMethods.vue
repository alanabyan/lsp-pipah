<template>
  <div class="app-layout">
    <AppSidebar active-item="Shipping Methods" />
    <AppTopbar
      user-name="Administrator"
      :avatar-src="adminAvatar"
      active-nav="Analytics"
    />

    <main class="main-content">

      <!-- ── Page Header ── -->
      <div class="page-header">
        <div>
          <p class="page-eyebrow">Manage courier services, internal logistics, and customer pickup options.</p>
          <h1 class="page-title">Shipping Methods</h1>
        </div>
        <button class="btn btn--primary" @click="openAddModal">
          <span class="material-symbols-outlined btn-icon">add</span>
          Tambah Metode
        </button>
      </div>

      <!-- ── Stats ── -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon-wrap stat-icon-wrap--green">
            <span class="material-symbols-outlined">local_shipping</span>
          </div>
          <div>
            <p class="stat-label">Total Kurir</p>
            <p class="stat-value">{{ loading ? '—' : totalKurir }}</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon-wrap stat-icon-wrap--blue">
            <span class="material-symbols-outlined">schedule</span>
          </div>
          <div>
            <p class="stat-label">Rata-rata Durasi</p>
            <p class="stat-value">1.4 <span class="stat-unit">hari</span></p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon-wrap stat-icon-wrap--orange">
            <span class="material-symbols-outlined">currency_exchange</span>
          </div>
          <div>
            <p class="stat-label">Estimasi Ongkir</p>
            <p class="stat-value">12k <span class="stat-unit">Rp</span></p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon-wrap stat-icon-wrap--purple">
            <span class="material-symbols-outlined">verified</span>
          </div>
          <div>
            <p class="stat-label">Aktif</p>
            <p class="stat-value">{{ loading ? '—' : totalAktif }}</p>
          </div>
        </div>
      </div>

      <!-- ── Table Card ── -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Daftar Metode Pengiriman</h3>
          <div class="search-wrap">
            <span class="material-symbols-outlined search-icon">search</span>
            <input
              v-model="searchQuery"
              class="search-input"
              placeholder="Cari..."
              @input="currentPage = 1"
            />
          </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="state-box">
          <span class="material-symbols-outlined spin">progress_activity</span>
          <p>Memuat data pengiriman...</p>
        </div>

        <!-- Error -->
        <div v-else-if="error" class="state-box state-box--error">
          <span class="material-symbols-outlined">error</span>
          <p>{{ error }}</p>
          <button class="btn btn--primary" @click="fetchMethods">Coba Lagi</button>
        </div>

        <!-- Empty -->
        <div v-else-if="filtered.length === 0" class="state-box">
          <span class="material-symbols-outlined">inbox</span>
          <p>{{ searchQuery ? 'Tidak ada hasil pencarian.' : 'Belum ada metode pengiriman.' }}</p>
        </div>

        <!-- Table -->
        <div v-else class="table-wrapper">
          <table class="methods-table">
            <thead>
              <tr>
                <th>Metode / Kurir</th>
                <th>Deskripsi Layanan</th>
                <th>Estimasi Durasi</th>
                <th>Estimasi Biaya</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="method in paginated"
                :key="method.id"
                class="table-row"
              >
                <td>
                  <div class="kurir-info">
                    <div class="kurir-icon" :style="iconStyle(method)">
                      <span class="material-symbols-outlined">{{ methodIcon(method) }}</span>
                    </div>
                    <div>
                      <p class="kurir-name">{{ method.nama_ekspedisi }}</p>
                      <p class="kurir-type">{{ method.jenis_kirim }}</p>
                    </div>
                  </div>
                </td>
                <td class="desc-cell">{{ deskripsi(method) }}</td>
                <td class="dur-cell">{{ durasi(method) }}</td>
                <td class="cost-cell">{{ biaya(method) }}</td>
                <td>
                  <span :class="['badge', isAktif(method) ? 'badge--aktif' : 'badge--nonaktif']">
                    {{ isAktif(method) ? 'AKTIF' : 'NON-AKTIF' }}
                  </span>
                </td>
                <td>
                  <div class="row-actions">
                    <button class="action-btn action-btn--edit" @click="openEditModal(method)" title="Edit">
                      <span class="material-symbols-outlined">edit</span>
                    </button>
                    <button
                      class="action-btn action-btn--delete"
                      @click="confirmDelete(method)"
                      :disabled="deletingId === method.id"
                      title="Hapus"
                    >
                      <span class="material-symbols-outlined">
                        {{ deletingId === method.id ? 'progress_activity' : 'delete' }}
                      </span>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="table-footer" v-if="!loading && !error && filtered.length > 0">
          <p class="table-count">
            Menampilkan {{ paginated.length }} dari {{ filtered.length }} metode pengiriman
          </p>
          <div class="pagination">
            <button
              class="page-btn"
              :disabled="currentPage === 1"
              @click="currentPage--"
            >Prev</button>
            <button
              v-for="p in totalPages"
              :key="p"
              :class="['page-btn', { 'page-btn--active': p === currentPage }]"
              @click="currentPage = p"
            >{{ p }}</button>
            <button
              class="page-btn"
              :disabled="currentPage === totalPages"
              @click="currentPage++"
            >Next</button>
          </div>
        </div>
      </div>

      <!-- ── Bottom Cards ── -->
      <div class="bottom-grid">
        <div class="bottom-card bottom-card--green">
          <div class="bottom-left">
            <div class="bottom-icon bottom-icon--green">
              <span class="material-symbols-outlined">hub</span>
            </div>
            <div>
              <h4 class="bottom-title">Automated Courier Sync</h4>
              <p class="bottom-sub">Integrasikan Apteki dengan API JNE/J&T untuk pembaruan resi dan pelacakan pesanan secara real-time langsung ke pelanggan.</p>
              <button class="bottom-btn bottom-btn--green">
                <span class="material-symbols-outlined btn-icon">api</span>
                Hubungkan API
              </button>
            </div>
          </div>
        </div>

        <div class="bottom-card bottom-card--white">
          <h4 class="bottom-title bottom-title--dark">Panduan Pengiriman</h4>
          <ul class="guide-list">
            <li>
              <span class="material-symbols-outlined guide-icon">check_circle</span>
              Gunakan "Ambil di Tempat" untuk pelanggan yang mengambil langsung ke apotek.
            </li>
            <li>
              <span class="material-symbols-outlined guide-icon">check_circle</span>
              Aplikasi kurir internal menggunakan bio-internal untuk menjaga stasilus atau situs aktif webmit.
            </li>
            <li>
              <span class="material-symbols-outlined guide-icon">check_circle</span>
              Aplikasikan estimasi biaya secara berkala sesuai dengan tabel ekspedisi webmit.
            </li>
          </ul>
        </div>
      </div>

    </main>

    <!-- ── Add / Edit Modal ── -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h3 class="modal-title">{{ isEditing ? 'Edit Metode Pengiriman' : 'Tambah Metode Pengiriman' }}</h3>
          <button class="icon-btn" @click="closeModal">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Jenis Kirim <span class="req">*</span></label>
              <select v-model="form.jenis_kirim" class="form-input">
                <option value="">-- Pilih --</option>
                <option value="ekonomi">Ekonomi</option>
                <option value="kargo">Kargo</option>
                <option value="regular">Regular</option>
                <option value="same day">Same Day</option>
                <option value="standar">Standar</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Nama Ekspedisi <span class="req">*</span></label>
              <input v-model="form.nama_ekspedisi" type="text" class="form-input" placeholder="e.g. JNE, J&T" />
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Logo Ekspedisi <span class="req">*</span></label>
            <input v-model="form.logo_ekspedisi" type="text" class="form-input" placeholder="URL logo atau nama file" />
          </div>
          <p v-if="formError" class="form-error">{{ formError }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn--outline" @click="closeModal" :disabled="saving">Batal</button>
          <button class="btn btn--primary" @click="saveMethod" :disabled="saving">
            <span v-if="saving" class="material-symbols-outlined spin btn-icon">progress_activity</span>
            {{ saving ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Tambah') }}
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
            Yakin hapus metode <strong>{{ deleteTarget.nama_ekspedisi }}</strong>
            ({{ deleteTarget.jenis_kirim }})? Tindakan ini tidak bisa dibatalkan.
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
import { JenisPengirimanService } from '@/services/api.js'

const PER_PAGE = 5

// Map jenis_kirim → icon & warna
const JENIS_STYLE = {
  'same day': { icon: 'bolt',           bg: '#ecfdf5', color: '#059669' },
  'standar':  { icon: 'local_shipping', bg: '#eff6ff', color: '#3b82f6' },
  'regular':  { icon: 'local_shipping', bg: '#fff7ed', color: '#f97316' },
  'ekonomi':  { icon: 'inventory_2',    bg: '#fdf4ff', color: '#a855f7' },
  'kargo':    { icon: 'forklift',       bg: '#fefce8', color: '#ca8a04' },
}

// Map jenis_kirim → estimasi durasi & biaya (dummy — ganti dari API kalau ada)
const JENIS_INFO = {
  'same day': { durasi: 'Instant',   biaya: 'Gratis' },
  'standar':  { durasi: '1 – 3 Jam', biaya: 'Rp 5.000' },
  'regular':  { durasi: '1 – 3 Hari', biaya: 'Rp 12.000+' },
  'ekonomi':  { durasi: '3 – 7 Hari', biaya: 'Calculated' },
  'kargo':    { durasi: '3 – 7 Hari', biaya: 'Calculated' },
}

export default {
  name: 'ShippingMethods',
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

      // Data
      allMethods: [],

      // Search & Pagination
      searchQuery: '',
      currentPage: 1,

      // Modal
      showModal:    false,
      isEditing:    false,
      editingId:    null,
      deleteTarget: null,

      // Form
      form: {
        jenis_kirim:    '',
        nama_ekspedisi: '',
        logo_ekspedisi: '',
      },
    }
  },

  computed: {
    totalKurir() { return this.allMethods.length },
    totalAktif() {
      // Semua dianggap aktif karena backend tidak punya field status
      return this.allMethods.length
    },

    filtered() {
      const q = this.searchQuery.toLowerCase().trim()
      if (!q) return this.allMethods
      return this.allMethods.filter(
        (m) =>
          m.nama_ekspedisi?.toLowerCase().includes(q) ||
          m.jenis_kirim?.toLowerCase().includes(q)
      )
    },

    totalPages() {
      return Math.max(1, Math.ceil(this.filtered.length / PER_PAGE))
    },

    paginated() {
      const start = (this.currentPage - 1) * PER_PAGE
      return this.filtered.slice(start, start + PER_PAGE)
    },
  },

  created() {
    this.fetchMethods()
  },

  methods: {
    // ── Fetch ────────────────────────────────────────────────
    async fetchMethods() {
      this.loading = true
      this.error   = null
      try {
        const res = await JenisPengirimanService.getAll()
        this.allMethods = res.data
      } catch (err) {
        this.error = err.response?.data?.message ?? 'Gagal memuat data. Periksa koneksi ke server.'
      } finally {
        this.loading = false
      }
    },

    // ── Save (Add / Edit) ────────────────────────────────────
    async saveMethod() {
      this.formError = null

      if (!this.form.jenis_kirim) {
        this.formError = 'Jenis kirim wajib dipilih.'
        return
      }
      if (!this.form.nama_ekspedisi.trim()) {
        this.formError = 'Nama ekspedisi wajib diisi.'
        return
      }
      if (!this.form.logo_ekspedisi.trim()) {
        this.formError = 'Logo ekspedisi wajib diisi.'
        return
      }

      this.saving = true
      try {
        if (this.isEditing) {
          // Backend belum punya PUT/PATCH jenis-pengiriman, pakai POST store lagi
          // atau tambahkan endpoint update di Laravel jika diperlukan
          // Untuk sementara, hapus lama lalu buat baru
          await JenisPengirimanService.delete(this.editingId)
          await JenisPengirimanService.create({ ...this.form })
        } else {
          await JenisPengirimanService.create({ ...this.form })
        }
        await this.fetchMethods()
        this.closeModal()
      } catch (err) {
        this.formError =
          err.response?.data?.message ??
          Object.values(err.response?.data?.errors ?? {})[0]?.[0] ??
          'Gagal menyimpan. Coba lagi.'
      } finally {
        this.saving = false
      }
    },

    // ── Delete ───────────────────────────────────────────────
    confirmDelete(method) {
      this.deleteTarget = method
    },

    async deleteMethod() {
      if (!this.deleteTarget) return
      this.deletingId = this.deleteTarget.id
      try {
        await JenisPengirimanService.delete(this.deleteTarget.id)
        this.allMethods  = this.allMethods.filter((m) => m.id !== this.deleteTarget.id)
        this.deleteTarget = null
        // Adjust page jika item terakhir di page ini habis
        if (this.currentPage > this.totalPages) this.currentPage = this.totalPages
      } catch (err) {
        alert(err.response?.data?.message ?? 'Gagal menghapus data.')
      } finally {
        this.deletingId = null
      }
    },

    // ── Modal helpers ────────────────────────────────────────
    openAddModal() {
      this.isEditing = false
      this.editingId = null
      this.form      = { jenis_kirim: '', nama_ekspedisi: '', logo_ekspedisi: '' }
      this.formError = null
      this.showModal = true
    },

    openEditModal(method) {
      this.isEditing = true
      this.editingId = method.id
      this.form      = {
        jenis_kirim:    method.jenis_kirim,
        nama_ekspedisi: method.nama_ekspedisi,
        logo_ekspedisi: method.logo_ekspedisi,
      }
      this.formError = null
      this.showModal = true
    },

    closeModal() {
      this.showModal = false
      this.formError = null
    },

    // ── Display helpers ──────────────────────────────────────
    iconStyle(method) {
      const s = JENIS_STYLE[method.jenis_kirim] ?? { bg: '#f0f4f8', color: '#6b7280' }
      return { background: s.bg, color: s.color }
    },

    methodIcon(method) {
      return JENIS_STYLE[method.jenis_kirim]?.icon ?? 'local_shipping'
    },

    deskripsi(method) {
      const map = {
        'same day': 'Pengiriman hari yang sama, tiba dalam hitungan jam.',
        'standar':  'Pengiriman khusus area kota (radius 5km) dan hari internal.',
        'regular':  'Pengiriman regular & kilat untuk kota/provinsi.',
        'ekonomi':  'Khusus untuk pemesanan grosir atau dari warehouse besar.',
        'kargo':    'Khusus untuk pemesanan grosir atau dari warehouse besar.',
      }
      return map[method.jenis_kirim] ?? method.nama_ekspedisi
    },

    durasi(method) {
      return JENIS_INFO[method.jenis_kirim]?.durasi ?? '-'
    },

    biaya(method) {
      return JENIS_INFO[method.jenis_kirim]?.biaya ?? 'Calculated'
    },

    isAktif() {
      // Backend tidak menyimpan field status, semua dianggap aktif
      return true
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

/* ── Header ── */
.page-header {
  display: flex; justify-content: space-between; align-items: flex-end;
}
.page-eyebrow { font-size: 0.78rem; color: #6b7280; margin-bottom: 0.25rem; }
.page-title {
  font-family: 'Manrope', sans-serif;
  font-size: 1.75rem; font-weight: 800;
  color: #064e3b; letter-spacing: -0.03em;
}

/* ── Buttons ── */
.btn {
  display: inline-flex; align-items: center; gap: 0.4rem;
  padding: 0.55rem 1.1rem; border: none; border-radius: 0.625rem;
  font-size: 0.8125rem; font-weight: 700; cursor: pointer;
  transition: all 0.15s; font-family: inherit;
}
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
.btn--primary { background: #059669; color: #fff; box-shadow: 0 2px 8px rgba(5,150,105,0.25); }
.btn--primary:hover:not(:disabled) { background: #047857; }
.btn--outline { background: #fff; color: #374151; border: 1px solid #e5e7eb; }
.btn--outline:hover:not(:disabled) { background: #f9fafb; }
.btn--danger  { background: #dc2626; color: #fff; }
.btn--danger:hover { background: #b91c1c; }
.btn-icon { font-size: 1rem; }

/* ── Stats ── */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}
.stat-card {
  background: #fff; border-radius: 0.875rem; padding: 1.25rem;
  box-shadow: 0 1px 4px rgba(0,0,0,0.05);
  display: flex; align-items: center; gap: 1rem;
}
.stat-icon-wrap {
  width: 3rem; height: 3rem; border-radius: 0.75rem; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
}
.stat-icon-wrap--green  { background: #ecfdf5; }
.stat-icon-wrap--green  .material-symbols-outlined { color: #059669; }
.stat-icon-wrap--blue   { background: #eff6ff; }
.stat-icon-wrap--blue   .material-symbols-outlined { color: #3b82f6; }
.stat-icon-wrap--orange { background: #fff7ed; }
.stat-icon-wrap--orange .material-symbols-outlined { color: #f97316; }
.stat-icon-wrap--purple { background: #fdf4ff; }
.stat-icon-wrap--purple .material-symbols-outlined { color: #a855f7; }
.stat-label { font-size: 0.72rem; color: #6b7280; font-weight: 500; }
.stat-value {
  font-family: 'Manrope', sans-serif;
  font-size: 1.5rem; font-weight: 900; color: #064e3b;
  line-height: 1.1;
}
.stat-unit { font-size: 0.875rem; font-weight: 500; color: #6b7280; }

/* ── Card / Table ── */
.card {
  background: #fff; border-radius: 0.875rem;
  padding: 1.5rem; box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}
.card-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 1.25rem;
}
.card-title {
  font-family: 'Manrope', sans-serif;
  font-size: 1rem; font-weight: 700; color: #064e3b;
}

/* Search */
.search-wrap { position: relative; }
.search-icon {
  position: absolute; left: 0.6rem; top: 50%; transform: translateY(-50%);
  font-size: 1rem; color: #9ca3af; pointer-events: none;
}
.search-input {
  padding: 0.45rem 0.75rem 0.45rem 2rem;
  border: 1px solid #e5e7eb; border-radius: 0.5rem;
  font-size: 0.8rem; outline: none; font-family: inherit;
  color: #171c1f; width: 14rem; transition: border 0.15s;
}
.search-input:focus { border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,0.1); }

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

/* Table */
.table-wrapper { overflow-x: auto; }
.methods-table { width: 100%; border-collapse: collapse; }
.methods-table thead tr { border-bottom: 2px solid #f0f4f8; }
.methods-table th {
  padding: 0 0.75rem 0.75rem;
  font-size: 0.68rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.08em;
  color: #9ca3af; text-align: left; white-space: nowrap;
}
.table-row { transition: background 0.12s; border-bottom: 1px solid #f9fafb; }
.table-row:last-child { border-bottom: none; }
.table-row:hover { background: rgba(236,253,245,0.4); }
.methods-table td { padding: 0.9rem 0.75rem; vertical-align: middle; }

/* Kurir cell */
.kurir-info { display: flex; align-items: center; gap: 0.75rem; }
.kurir-icon {
  width: 2.5rem; height: 2.5rem; border-radius: 0.625rem; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
}
.kurir-icon .material-symbols-outlined { font-size: 1.2rem; }
.kurir-name { font-size: 0.875rem; font-weight: 700; color: #171c1f; }
.kurir-type {
  font-size: 0.7rem; color: #9ca3af; margin-top: 1px;
  text-transform: capitalize;
}

.desc-cell  { font-size: 0.8rem; color: #6b7280; max-width: 18rem; line-height: 1.5; }
.dur-cell   { font-size: 0.8125rem; color: #374151; font-weight: 600; white-space: nowrap; }
.cost-cell  { font-size: 0.8125rem; color: #059669; font-weight: 700; white-space: nowrap; }

/* Badge */
.badge {
  display: inline-block; padding: 0.2rem 0.65rem;
  border-radius: 9999px; font-size: 0.68rem; font-weight: 700;
}
.badge--aktif    { background: #dcfce7; color: #15803d; }
.badge--nonaktif { background: #fee2e2; color: #b91c1c; }

/* Row actions */
.row-actions { display: flex; gap: 0.25rem; }
.action-btn {
  background: none; border: none; cursor: pointer;
  padding: 0.35rem; border-radius: 0.4rem;
  transition: all 0.15s; display: flex; color: #9ca3af;
}
.action-btn--edit:hover   { background: #eff6ff; color: #3b82f6; }
.action-btn--delete:hover:not(:disabled) { background: #fee2e2; color: #dc2626; }
.action-btn:disabled { opacity: 0.5; cursor: not-allowed; }

/* Pagination */
.table-footer {
  display: flex; justify-content: space-between; align-items: center;
  margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid #f0f4f8;
}
.table-count { font-size: 0.75rem; color: #9ca3af; }
.pagination { display: flex; gap: 0.25rem; }
.page-btn {
  padding: 0.3rem 0.65rem; border: 1px solid #e5e7eb;
  border-radius: 0.4rem; background: #fff; font-size: 0.78rem;
  cursor: pointer; font-family: inherit; color: #374151;
  transition: all 0.15s;
}
.page-btn:hover:not(:disabled) { background: #f0f9ff; border-color: #059669; color: #000; }
.page-btn--active { background: #059669; color: #fff; border-color: #059669; font-weight: 700; }
.page-btn:disabled { opacity: 0.45; cursor: not-allowed; }

/* ── Bottom Cards ── */
.bottom-grid {
  display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;
}
.bottom-card {
  border-radius: 0.875rem; padding: 1.5rem;
}
.bottom-card--green {
  background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
  color: #fff;
}
.bottom-card--white {
  background: #fff;
  box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}

.bottom-left { display: flex; gap: 1.25rem; align-items: flex-start; }
.bottom-icon {
  width: 3rem; height: 3rem; border-radius: 0.75rem; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
}
.bottom-icon--green {
  background: rgba(255,255,255,0.15);
}
.bottom-icon--green .material-symbols-outlined { color: #6ee7b7; font-size: 1.5rem; }

.bottom-title {
  font-family: 'Manrope', sans-serif;
  font-size: 0.9375rem; font-weight: 800;
  color: #fff; margin-bottom: 0.4rem;
}
.bottom-title--dark { color: #064e3b; }
.bottom-sub  { font-size: 0.78rem; color: rgba(255,255,255,0.7); line-height: 1.6; margin-bottom: 1rem; }

.bottom-btn {
  display: inline-flex; align-items: center; gap: 0.35rem;
  padding: 0.5rem 1rem; border: none; border-radius: 0.5rem;
  font-size: 0.78rem; font-weight: 700; cursor: pointer;
  transition: all 0.15s; font-family: inherit;
}
.bottom-btn--green {
  background: rgba(255,255,255,0.2);
  color: #fff; border: 1px solid rgba(255,255,255,0.3);
}
.bottom-btn--green:hover { background: rgba(255,255,255,0.3); }

.guide-list {
  list-style: none; padding: 0; margin: 0.75rem 0 0;
  display: flex; flex-direction: column; gap: 0.75rem;
}
.guide-list li {
  display: flex; gap: 0.6rem; align-items: flex-start;
  font-size: 0.78rem; color: #6b7280; line-height: 1.5;
}
.guide-icon { font-size: 1rem; color: #059669; flex-shrink: 0; margin-top: 1px; }

/* ── Modal ── */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.35);
  backdrop-filter: blur(4px);
  display: flex; align-items: center; justify-content: center; z-index: 100;
}
.modal {
  background: #fff; border-radius: 1rem;
  width: 100%; max-width: 28rem;
  box-shadow: 0 20px 60px rgba(0,0,0,0.15); overflow: hidden;
}
.modal--sm { max-width: 22rem; }
.modal-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 1.25rem 1.5rem; border-bottom: 1px solid #f0f4f8;
}
.modal-title {
  font-family: 'Manrope', sans-serif;
  font-size: 1rem; font-weight: 700; color: #064e3b;
}
.icon-btn {
  background: none; border: none; cursor: pointer;
  padding: 0.4rem; color: #6b7280; border-radius: 0.5rem;
  transition: background 0.15s; display: flex;
}
.icon-btn:hover { background: #f0f4f8; }
.modal-body { padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-group { display: flex; flex-direction: column; gap: 0.4rem; }
.form-label { font-size: 0.78rem; font-weight: 600; color: #374151; }
.req { color: #dc2626; }
.form-input {
  padding: 0.55rem 0.75rem; border: 1px solid #e5e7eb;
  border-radius: 0.5rem; font-size: 0.8125rem; outline: none;
  transition: border 0.15s; font-family: inherit; color: #171c1f;
  background: #fff;
}
.form-input:focus { border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,0.1); }
.form-error { font-size: 0.78rem; color: #dc2626; font-weight: 500; }
.confirm-text { font-size: 0.875rem; color: #374151; line-height: 1.6; }
.modal-footer {
  display: flex; justify-content: flex-end; gap: 0.75rem;
  padding: 1rem 1.5rem; border-top: 1px solid #f0f4f8;
}
</style>