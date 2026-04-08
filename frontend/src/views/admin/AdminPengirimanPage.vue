<template>
    <AdminLayout page-title="Manajemen Pengiriman">
        <AdminTable title="Data Pengiriman" :columns="cols" :data="list" :loading="loading"
            :search-keys="['no_resi', 'nama_kurir']" @add="openAdd" @edit="openEdit" @delete="confirmDelete">
            <template #cell-status_kirim="{ row }">
                <select :value="row.status_kirim" class="status-select" @change="updateStatus(row, $event.target.value)">
                    <option class="option">Diproses</option>
                    <option class="option">Dikirim</option>
                    <option class="option">Diterima</option>
                </select>
            </template>
            <template #cell-tgl_kirim="{ value }">{{ fmtDate(value) }}</template>
            <template #cell-tgl_tiba="{ value }">{{ value ? fmtDate(value) : '—' }}</template>
        </AdminTable>

        <AdminModal :show="showModal" :title="isEdit ? 'Edit Pengiriman' : 'Input Pengiriman Baru'" :loading="saving"
            @close="showModal = false" @submit="save">
            <AdminField v-if="!isEdit" label="ID Penjualan" type="number" v-model="form.id_penjualan" placeholder="1"
                required />
            <AdminField label="No. Resi" v-model="form.no_resi" placeholder="JNE1234567890" required />
            <AdminField label="Nama Kurir" v-model="form.nama_kurir" placeholder="J&T Express" required />
            <AdminField label="Tanggal Kirim" type="date" v-model="form.tgl_kirim" required />
            <div v-if="formError" class="form-alert">{{ formError }}</div>
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
    name: 'AdminPengirimanPage',
    components: { AdminLayout, AdminTable, AdminModal, AdminField, ToastNotif },
    data() {
        return {
            list: [], loading: true, showModal: false, isEdit: false, saving: false, formError: null,
            form: { id: null, id_penjualan: '', no_resi: '', nama_kurir: '', tgl_kirim: '' },
            toast: { show: false, message: '', type: 'success' },
            cols: [
                { key: 'id_penjualan', label: 'ID Penjualan' },
                { key: 'no_resi', label: 'No. Resi' },
                { key: 'nama_kurir', label: 'Kurir' },
                { key: 'tgl_kirim', label: 'Tgl. Kirim' },
                { key: 'tgl_tiba', label: 'Tgl. Tiba' },
                { key: 'status_kirim', label: 'Status' },
            ],
        }
    },
    mounted() { this.fetch() },
    methods: {
        async fetch() { this.loading = true; try { this.list = (await adminApi.get('/pengiriman')).data } catch { } finally { this.loading = false } },
        openAdd() { this.isEdit = false; this.form = { id: null, id_penjualan: '', no_resi: '', nama_kurir: '', tgl_kirim: '' }; this.formError = null; this.showModal = true },
        openEdit(r) { this.isEdit = true; this.form = { id: r.id, id_penjualan: r.id_penjualan, no_resi: r.no_resi, nama_kurir: r.nama_kurir, tgl_kirim: r.tgl_kirim?.split('T')[0] || '' }; this.formError = null; this.showModal = true },
        async save() {
            if (!this.form.no_resi || !this.form.nama_kurir || !this.form.tgl_kirim) { this.formError = 'Field wajib belum lengkap.'; return }
            this.saving = true; this.formError = null
            try {
                if (this.isEdit) {
                    await adminApi.put(`/pengiriman/${this.form.id}`, { no_resi: this.form.no_resi, nama_kurir: this.form.nama_kurir, tgl_kirim: this.form.tgl_kirim })
                } else {
                    await adminApi.post('/pengiriman', { id_penjualan: this.form.id_penjualan, no_resi: this.form.no_resi, nama_kurir: this.form.nama_kurir, tgl_kirim: this.form.tgl_kirim })
                }
                this.showModal = false; this.showT('Pengiriman disimpan!'); await this.fetch()
            } catch (e) { this.formError = e.response?.data?.message || 'Gagal.' } finally { this.saving = false }
        },
        async updateStatus(row, newStatus) {
            try {
                await adminApi.put(`/pengiriman-update/${row.id}`, { status_kirim: newStatus })
                row.status_kirim = newStatus
                if (newStatus === 'Diterima') row.tgl_tiba = new Date().toISOString()
                this.showT('Status pengiriman diperbarui!')
            } catch (e) { this.showT(e.response?.data?.message || 'Gagal.', 'error') }
        },
        async confirmDelete(r) {
            if (!confirm(`Hapus pengiriman resi "${r.no_resi}"?`)) return
            try { await adminApi.delete(`/pengiriman/${r.id}`); this.showT('Dihapus.'); await this.fetch() }
            catch (e) { this.showT(e.response?.data?.message || 'Gagal.', 'error') }
        },
        fmtDate(d) { return d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '—' },
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

.option {
    color: black;
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