<!-- AdminPembelianPage.vue -->
<template>
    <AdminLayout page-title="Pembelian (Stok Masuk)">
        <AdminTable title="Transaksi Pembelian" :columns="cols" :data="list" :loading="loading"
            :search-keys="['kode_masuk']" @add="openAdd" @edit="openEdit" @delete="confirmDelete">
            <template #cell-total_harga="{ value }"><span class="price">{{ fmtRp(value) }}</span></template>
            <template #cell-tanggal_masuk="{ value }">{{ fmtDate(value) }}</template>
        </AdminTable>

        <AdminModal :show="showModal" :title="isEdit ? 'Edit Pembelian' : 'Tambah Pembelian'" :loading="saving"
            @close="showModal = false" @submit="save">
            <AdminField label="Kode Masuk" v-model="form.kode_masuk" placeholder="PM-2024-001" required />
            <AdminField label="Tanggal Masuk" type="date" v-model="form.tanggal_masuk" required />
            <AdminField label="Distributor" type="select" v-model="form.id_distributor" :options="distOptions"
                required />
            <AdminField label="Total Item" type="number" v-model="form.total_item" placeholder="10" min="1" required />
            <AdminField label="Total Harga (Rp)" type="number" v-model="form.total_harga" placeholder="500000" min="0"
                required />
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
    name: 'AdminPembelianPage',
    components: { AdminLayout, AdminTable, AdminModal, AdminField, ToastNotif },
    data() {
        return {
            list: [], distList: [], loading: true, showModal: false, isEdit: false, saving: false, formError: null,
            form: { id: null, kode_masuk: '', tanggal_masuk: '', id_distributor: '', total_item: '', total_harga: '' },
            toast: { show: false, message: '', type: 'success' },
            cols: [
                { key: 'kode_masuk', label: 'Kode Masuk' },
                { key: 'tanggal_masuk', label: 'Tanggal' },
                { key: 'total_item', label: 'Total Item' },
                { key: 'total_harga', label: 'Total Harga' },
            ],
        }
    },
    computed: { distOptions() { return this.distList.map(d => ({ value: d.id, label: d.nama_distributor })) } },
    mounted() { this.fetch() },
    methods: {
        async fetch() {
            this.loading = true
            try {
                const [a, b] = await Promise.all([adminApi.get('/pembelian'), adminApi.get('/distributor')])
                this.list = a.data; this.distList = b.data
            } catch { } finally { this.loading = false }
        },
        openAdd() { this.isEdit = false; this.form = { id: null, kode_masuk: '', tanggal_masuk: '', id_distributor: '', total_item: '', total_harga: '' }; this.formError = null; this.showModal = true },
        openEdit(r) { this.isEdit = true; this.form = { id: r.id, kode_masuk: r.kode_masuk, tanggal_masuk: r.tanggal_masuk?.split('T')[0] || '', id_distributor: r.id_distributor, total_item: r.total_item, total_harga: r.total_harga }; this.formError = null; this.showModal = true },
        async save() {
            if (!this.form.kode_masuk || !this.form.tanggal_masuk || !this.form.id_distributor) { this.formError = 'Field wajib belum lengkap.'; return }
            this.saving = true; this.formError = null
            try {
                const d = { kode_masuk: this.form.kode_masuk, tanggal_masuk: this.form.tanggal_masuk, id_distributor: this.form.id_distributor, total_item: this.form.total_item, total_harga: this.form.total_harga }
                if (this.isEdit) await adminApi.put(`/pembelian/${this.form.id}`, d)
                else await adminApi.post('/pembelian', d)
                this.showModal = false; this.showT('Pembelian disimpan!'); await this.fetch()
            } catch (e) { this.formError = e.response?.data?.message || 'Gagal.' } finally { this.saving = false }
        },
        async confirmDelete(r) {
            if (!confirm(`Hapus pembelian "${r.kode_masuk}"?`)) return
            try { await adminApi.delete(`/pembelian/${r.id}`); this.showT('Dihapus.'); await this.fetch() }
            catch (e) { this.showT(e.response?.data?.message || 'Gagal.', 'error') }
        },
        fmtRp(v) { return 'Rp ' + Number(v || 0).toLocaleString('id-ID') },
        fmtDate(d) { return d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '—' },
        showT(msg, type = 'success') { this.toast = { show: true, message: msg, type }; setTimeout(() => { this.toast.show = false }, 3500) },
    },
}
</script>
<style scoped>
.price {
    font-family: 'Manrope', sans-serif;
    font-weight: 700;
    color: #4ade80;
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