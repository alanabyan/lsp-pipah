<template>
    <AdminLayout page-title="Konfirmasi Pembayaran">
        <AdminTable title="Data Pembayaran" :columns="cols" :data="list" :loading="loading"
            :search-keys="['id_penjualan']" :show-add="false" :show-edit="false" :show-delete="false">
            <template #cell-status_konfirmasi="{ row }">
                <span :class="['badge', statusCls(row.status_konfirmasi)]">
                    {{ row.status_konfirmasi || 'Menunggu' }}
                </span>
            </template>
            <template #cell-jumlah_bayar="{ value }">
                <span class="price">{{ fmtRp(value) }}</span>
            </template>
            <template #cell-tgl_bayar="{ value }">{{ fmtDate(value) }}</template>
            <template #extra-actions="{ row }">
                <button class="action-btn action-btn--view" @click="openDetail(row)" title="Lihat & Konfirmasi">
                    <span class="material-symbols-outlined">task_alt</span>
                </button>
            </template>
        </AdminTable>

        <!-- Detail & Konfirmasi Modal -->
        <AdminModal :show="showModal" title="Detail & Konfirmasi Pembayaran" :loading="saving"
            submit-label="Simpan Konfirmasi" submit-icon="verified" @close="showModal = false" @submit="saveKonfirmasi">
            <div v-if="selected" class="detail-wrap">
                <div class="info-grid">
                    <div class="info-row"><span class="info-key">ID Penjualan</span><span class="info-val">#{{
                        pad(selected.id_penjualan) }}</span></div>
                    <div class="info-row"><span class="info-key">Tanggal Bayar</span><span class="info-val">{{
                            fmtDate(selected.tgl_bayar) }}</span></div>
                    <div class="info-row"><span class="info-key">Jumlah Dibayar</span><span class="info-val price">{{
                        fmtRp(selected.jumlah_bayar) }}</span></div>
                    <div class="info-row">
                        <span class="info-key">Status Saat Ini</span>
                        <span :class="['badge', statusCls(selected.status_konfirmasi)]">{{ selected.status_konfirmasi ||
                            'Menunggu' }}</span>
                    </div>
                </div>

                <div v-if="selected.bukti_bayar" class="bukti-section">
                    <p class="bukti-label">Bukti Pembayaran</p>
                    <img :src="buktiUrl(selected.bukti_bayar)" alt="Bukti Pembayaran" class="bukti-img"
                        @click="openBukti(selected.bukti_bayar)" />
                    <p class="bukti-hint">Klik gambar untuk memperbesar</p>
                </div>
                <div v-else class="bukti-empty">
                    <span class="material-symbols-outlined">image_not_supported</span>
                    <p>Belum ada bukti pembayaran.</p>
                </div>

                <AdminField label="Ubah Status Konfirmasi" type="select" v-model="konfirmasiStatus" :options="[
                    { value: 'Menunggu', label: 'Menunggu' },
                    { value: 'Diterima', label: 'Diterima — Pembayaran Valid' },
                    { value: 'Ditolak', label: 'Ditolak — Pembayaran Tidak Valid' },
                ]" required />
                <div v-if="formError" class="form-alert">{{ formError }}</div>
            </div>
        </AdminModal>

        <ToastNotif :toast="toast" />
    </AdminLayout>
</template>

<script>
import AdminLayout from '@/layouts/AdminLayout.vue'
import AdminTable from '@/components/admin/AdminTable.vue'
import AdminModal from '@/components/admin/AdminModal.vue'
import AdminField from '@/components/admin/AdminField.vue'
import ToastNotif from '@/components/admin/ToastNotif.vue'
import { adminApi } from '@/stores/adminAuth.js'

export default {
    name: 'AdminPembayaranPage',
    components: { AdminLayout, AdminTable, AdminModal, AdminField, ToastNotif },
    data() {
        return {
            list: [], loading: true,
            showModal: false, selected: null,
            konfirmasiStatus: 'Menunggu',
            saving: false, formError: null,
            toast: { show: false, message: '', type: 'success' },
            cols: [
                { key: 'id', label: 'ID' },
                { key: 'id_penjualan', label: 'ID Penjualan' },
                { key: 'tgl_bayar', label: 'Tanggal Bayar' },
                { key: 'jumlah_bayar', label: 'Jumlah Bayar' },
                { key: 'status_konfirmasi', label: 'Status' },
            ],
        }
    },
    mounted() { this.fetch() },
    methods: {
        async fetch() {
            this.loading = true
            try {
                // GET /api/pembayaran
                const res = await adminApi.get('/pembayaran')
                this.list = res.data || []
            } catch { this.list = [] }
            finally { this.loading = false }
        },

        openDetail(row) {
            this.selected = row
            this.konfirmasiStatus = row.status_konfirmasi || 'Menunggu'
            this.formError = null
            this.showModal = true
        },

        async saveKonfirmasi() {
            this.saving = true; this.formError = null
            try {
                // POST /api/pembayaran-konfirmasi/{id}
                await adminApi.post(`/pembayaran-konfirmasi/${this.selected.id}`, {
                    status_konfirmasi: this.konfirmasiStatus,
                })
                this.showModal = false
                this.showT(`Pembayaran #${this.pad(this.selected.id_penjualan)} → ${this.konfirmasiStatus}`)
                await this.fetch()
            } catch (err) {
                this.formError = err.response?.data?.message || 'Gagal menyimpan konfirmasi.'
            } finally { this.saving = false }
        },

        openBukti(filename) {
            window.open(this.buktiUrl(filename), '_blank')
        },

        buktiUrl(filename) {
            return `${(import.meta.env.VITE_API_URL || 'http://localhost:8000/api').replace('/api', '')}/storage/bukti_pembayaran/${filename}`
        },
        fmtRp(v) { return 'Rp ' + Number(v || 0).toLocaleString('id-ID') },
        fmtDate(d) { return d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—' },
        pad(id) { return String(id || 0).padStart(5, '0') },
        statusCls(s) {
            if (!s || s === 'Menunggu') return 'badge--wait'
            if (s === 'Diterima') return 'badge--done'
            return 'badge--reject'
        },
        showT(msg, type = 'success') { this.toast = { show: true, message: msg, type }; setTimeout(() => { this.toast.show = false }, 3500) },
    },
}
</script>

<style scoped>
.badge {
    padding: 0.18rem 0.55rem;
    border-radius: 9999px;
    font-size: 0.68rem;
    font-weight: 800;
}

.badge--wait {
    background: rgba(251, 191, 36, 0.12);
    color: #fbbf24;
}

.badge--done {
    background: rgba(74, 222, 128, 0.12);
    color: #4ade80;
}

.badge--reject {
    background: rgba(248, 113, 113, 0.12);
    color: #f87171;
}

.price {
    font-family: 'Manrope', sans-serif;
    font-weight: 700;
    color: #4ade80;
}

.action-btn {
    padding: 0.35rem;
    background: none;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 0.4rem;
    cursor: pointer;
    display: flex;
    transition: all 0.15s;
}

.action-btn .material-symbols-outlined {
    font-size: 1rem;
}

.action-btn--view {
    color: rgba(99, 102, 241, 0.7);
}

.action-btn--view:hover {
    background: rgba(99, 102, 241, 0.12);
    border-color: rgba(99, 102, 241, 0.3);
    color: #a5b4fc;
}

.detail-wrap {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.info-grid {
    display: flex;
    flex-direction: column;
    gap: 0;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 0.75rem;
    overflow: hidden;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.info-row:last-child {
    border-bottom: none;
}

.info-key {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.35);
    font-weight: 600;
}

.info-val {
    font-size: 0.875rem;
    font-weight: 700;
    color: #fff;
}

.bukti-section {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.bukti-label {
    font-size: 0.72rem;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.35);
    text-transform: uppercase;
    letter-spacing: 0.07em;
}

.bukti-img {
    width: 100%;
    max-height: 16rem;
    object-fit: contain;
    border-radius: 0.75rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(255, 255, 255, 0.03);
    cursor: zoom-in;
    transition: opacity 0.15s;
}

.bukti-img:hover {
    opacity: 0.9;
}

.bukti-hint {
    font-size: 0.68rem;
    color: rgba(255, 255, 255, 0.2);
    text-align: center;
}

.bukti-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 2rem;
    color: rgba(255, 255, 255, 0.2);
    background: rgba(255, 255, 255, 0.02);
    border-radius: 0.75rem;
    border: 1px dashed rgba(255, 255, 255, 0.08);
}

.bukti-empty .material-symbols-outlined {
    font-size: 2rem;
}

.bukti-empty p {
    font-size: 0.78rem;
}

.form-alert {
    padding: 0.6rem 1rem;
    background: rgba(248, 113, 113, 0.1);
    border: 1px solid rgba(248, 113, 113, 0.2);
    border-radius: 0.5rem;
    font-size: 0.78rem;
    color: #fca5a5;
    font-weight: 600;
}
</style>