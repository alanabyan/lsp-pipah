<template>
    <AdminLayout page-title="Manajemen Penjualan">
        <AdminTable title="Data Penjualan" :columns="cols" :data="list" :loading="loading"
            :search-keys="['id', 'status_order']" :show-add="false" :show-edit="false" :show-delete="true"
            @delete="confirmDelete">
            <!-- Status Order -->
            <template #cell-status_order="{ row }">
                <select :value="row.status_order" class="status-select"
                    @change="updateStatus(row, $event.target.value)">
                    <option v-for="s in statusOptions" :key="s" :value="s" class="option-select">{{ s }}</option>
                </select>
            </template>
            <!-- Total Bayar -->
            <template #cell-total_bayar="{ value }">
                <span class="price">{{ fmtRp(value) }}</span>
            </template>
            <!-- Tanggal -->
            <template #cell-tgl_penjualan="{ value }">{{ fmtDate(value) }}</template>
            <!-- Konfirmasi Pembayaran -->
            <template #extra-actions="{ row }">
                <button v-if="row.pembayaran" class="action-btn action-btn--konfirmasi" @click="openKonfirmasi(row)"
                    title="Konfirmasi Pembayaran">
                    <span class="material-symbols-outlined">verified</span>
                </button>
            </template>
        </AdminTable>

        <!-- Modal Konfirmasi Pembayaran -->
        <AdminModal :show="showKonfirmasi" title="Konfirmasi Pembayaran" :loading="konfirmasiLoading"
            submit-label="Simpan Status" submit-icon="verified" @close="showKonfirmasi = false" @submit="saveKonfirmasi">
            <div v-if="selectedOrder" class="konfirmasi-info">
                <div class="info-row"><span>Order</span><span>#{{ pad(selectedOrder.id) }}</span></div>
                <div class="info-row"><span>Total Bayar</span><span>{{ fmtRp(selectedOrder.total_bayar) }}</span></div>
                <div class="info-row"><span>Jumlah Dibayar</span><span>{{ fmtRp(selectedOrder.pembayaran?.jumlah_bayar)
                        }}</span></div>
                <div v-if="selectedOrder.pembayaran?.bukti_bayar" class="bukti-wrap">
                    <p class="bukti-label">Bukti Bayar:</p>
                    <img :src="buktiUrl(selectedOrder.pembayaran.bukti_bayar)" class="bukti-img"
                        alt="Bukti Pembayaran" />
                </div>
                <AdminField label="Status Konfirmasi" type="select" v-model="konfirmasiStatus"
                    :options="[{ value: 'Menunggu', label: 'Menunggu' }, { value: 'Diterima', label: 'Diterima' }, { value: 'Ditolak', label: 'Ditolak' }]"
                    required />
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
    name: 'AdminPenjualanPage',
    components: { AdminLayout, AdminTable, AdminModal, AdminField, ToastNotif },
    data() {
        return {
            list: [], loading: true,
            showKonfirmasi: false, konfirmasiLoading: false,
            selectedOrder: null, konfirmasiStatus: 'Menunggu',
            toast: { show: false, message: '', type: 'success' },
            statusOptions: ['Menunggu Konfirmasi', 'Diproses', 'Menunggu Kurir', 'Dibatalkan Pembeli', 'Dibatalkan Penjual', 'Bermasalah', 'Selesai'],
            cols: [
                { key: 'id', label: 'ID' },
                { key: 'tgl_penjualan', label: 'Tanggal' },
                { key: 'status_order', label: 'Status Order' },
                { key: 'total_bayar', label: 'Total Bayar' },
            ],
        }
    },
    mounted() { this.fetch() },
    methods: {
        async fetch() { this.loading = true; try { this.list = (await adminApi.get('/penjualan')).data } catch { } finally { this.loading = false } },

        async updateStatus(row, newStatus) {
            try {
                await adminApi.put(`/penjualan/${row.id}`, { status_order: newStatus })
                row.status_order = newStatus
                this.showT('Status diperbarui!')
            } catch (e) { this.showT(e.response?.data?.message || 'Gagal update status.', 'error') }
        },

        openKonfirmasi(row) {
            this.selectedOrder = row
            this.konfirmasiStatus = row.pembayaran?.status_konfirmasi || 'Menunggu'
            this.showKonfirmasi = true
        },
        async saveKonfirmasi() {
            this.konfirmasiLoading = true
            try {
                await adminApi.patch(`/pembayaran/${this.selectedOrder.pembayaran.id}/status`, { status_konfirmasi: this.konfirmasiStatus })
                this.showKonfirmasi = false
                this.showT('Status pembayaran diperbarui!')
                await this.fetch()
            } catch (e) { this.showT(e.response?.data?.message || 'Gagal.', 'error') } finally { this.konfirmasiLoading = false }
        },

        async confirmDelete(r) {
            if (!confirm(`Hapus penjualan #${this.pad(r.id)}?`)) return
            try { await adminApi.delete(`/penjualan/${r.id}`); this.showT('Penjualan dihapus.'); await this.fetch() }
            catch (e) { this.showT(e.response?.data?.message || 'Gagal.', 'error') }
        },

        buktiUrl(f) { return `${(import.meta.env.VITE_API_URL || 'http://localhost:8000/api').replace('/api', '')}/storage/bukti_pembayaran/${f}` },
        fmtRp(v) { return 'Rp ' + Number(v || 0).toLocaleString('id-ID') },
        fmtDate(d) { return d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '—' },
        pad(id) { return '#' + String(id || 0).padStart(5, '0') },
        showT(msg, type = 'success') { this.toast = { show: true, message: msg, type }; setTimeout(() => { this.toast.show = false }, 3500) },
    },
}
</script>
<style scoped>
.status-select {
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 0.4rem;
    color: #fff;
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    cursor: pointer;
    outline: none;
    font-family: inherit;
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

.action-btn--konfirmasi {
    color: rgba(99, 102, 241, 0.6);
}

.action-btn--konfirmasi:hover {
    background: rgba(99, 102, 241, 0.12);
    border-color: rgba(99, 102, 241, 0.3);
    color: #a5b4fc;
}

.action-btn .material-symbols-outlined {
    font-size: 1rem;
}

.konfirmasi-info {
    display: flex;
    flex-direction: column;
    gap: 0.875rem;
}

.info-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.82rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.info-row span:first-child {
    color: rgba(255, 255, 255, 0.4);
}

.info-row span:last-child {
    color: #fff;
    font-weight: 700;
}

.bukti-wrap {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.bukti-label {
    font-size: 0.72rem;
    color: rgba(255, 255, 255, 0.35);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
}

.option-select {
    color: black;
}

.bukti-img {
    width: 100%;
    max-height: 14rem;
    object-fit: contain;
    border-radius: 0.625rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(255, 255, 255, 0.03);
}
</style>