<template>
    <AdminLayout page-title="Jenis Obat">
        <AdminTable title="Jenis Obat" :columns="cols" :data="list" :loading="loading" :search-keys="['jenis']"
            @add="openAdd" @edit="openEdit" @delete="confirmDelete">
            <template #cell-image_url="{ row }">
                <img v-if="row.image_url" :src="imgUrl(row.image_url)" class="thumb" />
                <span v-else>—</span>
            </template>
        </AdminTable>

        <AdminModal :show="showModal" :title="isEdit ? 'Edit Jenis Obat' : 'Tambah Jenis Obat'" :loading="saving"
            @close="showModal = false" @submit="save">
            <AdminField label="Nama Jenis" v-model="form.jenis" placeholder="Antibiotik" required />
            <AdminField label="Deskripsi" type="textarea" v-model="form.deskripsi_jenis" placeholder="Keterangan..." />
            <AdminField label="Logo/Gambar" type="file" @update:modelValue="form.image_url = $event"
                hint="JPG/PNG max 1MB" />
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
    name: 'AdminJenisObatPage',
    components: { AdminLayout, AdminTable, AdminModal, AdminField, ToastNotif },
    data() {
        return {
            list: [], loading: true, showModal: false, isEdit: false, saving: false, formError: null,
            form: { id: null, jenis: '', deskripsi_jenis: '', image_url: null },
            toast: { show: false, message: '', type: 'success' },
            cols: [
                { key: 'image_url', label: 'Logo' },
                { key: 'jenis', label: 'Nama Jenis' },
                { key: 'deskripsi_jenis', label: 'Deskripsi' },
            ],
        }
    },
    mounted() { this.fetch() },
    methods: {
        async fetch() {
            this.loading = true
            try { this.list = (await adminApi.get('/jenis-obat')).data } catch { }
            finally { this.loading = false }
        },
        openAdd() { this.isEdit = false; this.form = { id: null, jenis: '', deskripsi_jenis: '', image_url: null }; this.formError = null; this.showModal = true },
        openEdit(r) { this.isEdit = true; this.form = { id: r.id, jenis: r.jenis, deskripsi_jenis: r.deskripsi_jenis || '', image_url: null }; this.formError = null; this.showModal = true },
        async save() {
            if (!this.form.jenis) { this.formError = 'Nama jenis wajib diisi.'; return }
            this.saving = true; this.formError = null
            try {
                const fd = new FormData()
                fd.append('jenis', this.form.jenis)
                if (this.form.deskripsi_jenis) fd.append('deskripsi_jenis', this.form.deskripsi_jenis)
                if (this.form.image_url) fd.append('image_url', this.form.image_url)
                if (this.isEdit) {
                    fd.append('_method', 'PUT')
                    await adminApi.post(`/jenis-obat/${this.form.id}`, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
                } else {
                    await adminApi.post('/jenis-obat', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
                }
                this.showModal = false; this.showT('Jenis obat berhasil disimpan!'); await this.fetch()
            } catch (e) { this.formError = e.response?.data?.message || 'Gagal.' } finally { this.saving = false }
        },
        async confirmDelete(r) {
            if (!confirm(`Hapus jenis "${r.jenis}"?`)) return
            try { await adminApi.delete(`/jenis-obat/${r.id}`); this.showT('Jenis obat dihapus.'); await this.fetch() }
            catch (e) { this.showT(e.response?.data?.message || 'Gagal.', 'error') }
        },
        imgUrl(f) { return `${(import.meta.env.VITE_API_URL || 'http://localhost:8000/api').replace('/api', '')}/storage/jenis_obat/${f}` },
        showT(msg, type = 'success') { this.toast = { show: true, message: msg, type }; setTimeout(() => { this.toast.show = false }, 3500) },
    },
}
</script>
<style scoped>
.thumb {
    width: 2.5rem;
    height: 2.5rem;
    object-fit: cover;
    border-radius: 0.375rem;
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