<template>
    <AdminLayout page-title="Distributor">
        <AdminTable title="Distributor" :columns="cols" :data="list" :loading="loading"
            :search-keys="['nama_distributor', 'telepon']" @add="openAdd" @edit="openEdit" @delete="confirmDelete" />

        <AdminModal :show="showModal" :title="isEdit ? 'Edit Distributor' : 'Tambah Distributor'" :loading="saving"
            @close="showModal = false" @submit="save">
            <AdminField label="Nama Distributor" v-model="form.nama_distributor" placeholder="PT Kimia Farma"
                required />
            <AdminField label="Telepon" v-model="form.telepon" placeholder="+62 21 xxxx xxxx" required />
            <AdminField label="Alamat" type="textarea" v-model="form.alamat" placeholder="Jalan, Kota..." required />
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
    name: 'AdminDistributorPage',
    components: { AdminLayout, AdminTable, AdminModal, AdminField, ToastNotif },
    data() {
        return {
            list: [], loading: true, showModal: false, isEdit: false, saving: false, formError: null,
            form: { id: null, nama_distributor: '', telepon: '', alamat: '' },
            toast: { show: false, message: '', type: 'success' },
            cols: [
                { key: 'nama_distributor', label: 'Nama Distributor' },
                { key: 'telepon', label: 'Telepon' },
                { key: 'alamat', label: 'Alamat' },
            ],
        }
    },
    mounted() { this.fetch() },
    methods: {
        async fetch() { this.loading = true; try { this.list = (await adminApi.get('/distributor')).data } catch { } finally { this.loading = false } },
        openAdd() { this.isEdit = false; this.form = { id: null, nama_distributor: '', telepon: '', alamat: '' }; this.formError = null; this.showModal = true },
        openEdit(r) { this.isEdit = true; this.form = { id: r.id, nama_distributor: r.nama_distributor, telepon: r.telepon, alamat: r.alamat }; this.formError = null; this.showModal = true },
        async save() {
            if (!this.form.nama_distributor || !this.form.telepon || !this.form.alamat) { this.formError = 'Semua field wajib diisi.'; return }
            this.saving = true; this.formError = null
            try {
                // Controller hanya punya store() dan destroy() — tidak ada update
                // Jika edit: hapus lama lalu buat baru (workaround)
                if (this.isEdit) { await adminApi.delete(`/distributor/${this.form.id}`) }
                await adminApi.post('/distributor', { nama_distributor: this.form.nama_distributor, telepon: this.form.telepon, alamat: this.form.alamat })
                this.showModal = false; this.showT('Distributor berhasil disimpan!'); await this.fetch()
            } catch (e) { this.formError = e.response?.data?.message || 'Gagal.' } finally { this.saving = false }
        },
        async confirmDelete(r) {
            if (!confirm(`Hapus distributor "${r.nama_distributor}"?`)) return
            try { await adminApi.delete(`/distributor/${r.id}`); this.showT('Distributor dihapus.'); await this.fetch() }
            catch (e) { this.showT(e.response?.data?.message || 'Gagal.', 'error') }
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