<template>
    <AdminLayout page-title="Jenis Pengiriman">
        <AdminTable title="Jenis Pengiriman" :columns="cols" :data="list" :loading="loading"
            :search-keys="['nama_ekspedisi', 'jenis_kirim']" :show-edit="false" @add="openAdd" @delete="confirmDelete" />

        <AdminModal :show="showModal" title="Tambah Jenis Pengiriman" :loading="saving" @close="showModal = false"
            @submit="save">
            <AdminField label="Jenis Kirim" type="select" v-model="form.jenis_kirim" :options="[
                { value: 'ekonomi', label: 'Ekonomi' },
                { value: 'kargo', label: 'Kargo' },
                { value: 'regular', label: 'Regular' },
                { value: 'same day', label: 'Same Day' },
                { value: 'standar', label: 'Standar' },
            ]" required />
            <AdminField label="Nama Ekspedisi" v-model="form.nama_ekspedisi" placeholder="J&T Express" required />
            <AdminField label="Logo Ekspedisi" v-model="form.logo_ekspedisi" placeholder="URL atau nama file logo"
                required hint="Masukkan URL logo atau nama file" />
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
    name: 'AdminJenisPengirimanPage',
    components: { AdminLayout, AdminTable, AdminModal, AdminField, ToastNotif },
    data() {
        return {
            list: [], loading: true, showModal: false, saving: false, formError: null,
            form: { jenis_kirim: '', nama_ekspedisi: '', logo_ekspedisi: '' },
            toast: { show: false, message: '', type: 'success' },
            cols: [
                { key: 'nama_ekspedisi', label: 'Nama Ekspedisi' },
                { key: 'jenis_kirim', label: 'Jenis Kirim' },
                { key: 'logo_ekspedisi', label: 'Logo' },
            ],
        }
    },
    mounted() { this.fetch() },
    methods: {
        async fetch() {
            this.loading = true
            try {
                // GET /api/jenis-pengiriman
                const res = await adminApi.get('/jenis-pengiriman')
                this.list = res.data
            } catch { }
            finally { this.loading = false }
        },
        openAdd() { this.form = { jenis_kirim: '', nama_ekspedisi: '', logo_ekspedisi: '' }; this.formError = null; this.showModal = true },
        async save() {
            if (!this.form.jenis_kirim || !this.form.nama_ekspedisi || !this.form.logo_ekspedisi) {
                this.formError = 'Semua field wajib diisi.'; return
            }
            this.saving = true; this.formError = null
            try {
                // POST /api/jenis-pengiriman
                await adminApi.post('/jenis-pengiriman', this.form)
                this.showModal = false; this.showT('Jenis pengiriman ditambahkan!'); await this.fetch()
            } catch (e) { this.formError = e.response?.data?.message || 'Gagal.' }
            finally { this.saving = false }
        },
        async confirmDelete(r) {
            if (!confirm(`Hapus "${r.nama_ekspedisi}"?`)) return
            try {
                // DELETE /api/jenis-pengiriman/{id}
                await adminApi.delete(`/jenis-pengiriman/${r.id}`)
                this.showT('Dihapus.'); await this.fetch()
            } catch (e) { this.showT(e.response?.data?.message || 'Gagal.', 'error') }
        },
        showT(msg, type = 'success') { this.toast = { show: true, message: msg, type }; setTimeout(() => { this.toast.show = false }, 3500) },
    },
}
</script>
<style scoped>
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