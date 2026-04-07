<template>
    <AdminLayout page-title="Metode Pembayaran">
        <AdminTable title="Metode Pembayaran" :columns="cols" :data="list" :loading="loading"
            :search-keys="['metode_pembayaran', 'tempat_bayar']" :show-edit="false" @add="openAdd"
            @delete="confirmDelete">
            <template #cell-url_logo="{ row }">
                <img v-if="row.url_logo" :src="logoUrl(row.url_logo)" class="logo-thumb" />
                <span v-else>—</span>
            </template>
        </AdminTable>

        <AdminModal :show="showModal" title="Tambah Metode Pembayaran" :loading="saving" @close="showModal = false"
            @submit="save">
            <AdminField label="Metode Pembayaran" v-model="form.metode_pembayaran" placeholder="Digital Wallet (OVO)"
                required hint="Maks 30 karakter" />
            <AdminField label="Tempat Bayar" v-model="form.tempat_bayar" placeholder="OVO App" required
                hint="Maks 50 karakter" />
            <AdminField label="No. Rekening" v-model="form.no_rekening" placeholder="08123456789" required
                hint="Maks 25 karakter" />
            <AdminField label="Logo" type="file" @update:modelValue="form.url_logo = $event"
                hint="JPG/PNG max 1MB (opsional)" />
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
    name: 'AdminMetodePembayaranPage',
    components: { AdminLayout, AdminTable, AdminModal, AdminField, ToastNotif },
    data() {
        return {
            list: [], loading: true, showModal: false, saving: false, formError: null,
            form: { metode_pembayaran: '', tempat_bayar: '', no_rekening: '', url_logo: null },
            toast: { show: false, message: '', type: 'success' },
            cols: [
                { key: 'url_logo', label: 'Logo' },
                { key: 'metode_pembayaran', label: 'Metode' },
                { key: 'tempat_bayar', label: 'Tempat Bayar' },
                { key: 'no_rekening', label: 'No. Rekening' },
            ],
        }
    },
    mounted() { this.fetch() },
    methods: {
        async fetch() { this.loading = true; try { this.list = (await adminApi.get('/metode-bayar')).data } catch { } finally { this.loading = false } },
        openAdd() { this.form = { metode_pembayaran: '', tempat_bayar: '', no_rekening: '', url_logo: null }; this.formError = null; this.showModal = true },
        async save() {
            if (!this.form.metode_pembayaran || !this.form.tempat_bayar || !this.form.no_rekening) { this.formError = 'Semua field wajib diisi.'; return }
            this.saving = true; this.formError = null
            try {
                const fd = new FormData()
                fd.append('metode_pembayaran', this.form.metode_pembayaran)
                fd.append('tempat_bayar', this.form.tempat_bayar)
                fd.append('no_rekening', this.form.no_rekening)
                if (this.form.url_logo) fd.append('url_logo', this.form.url_logo)
                await adminApi.post('/metode-bayar', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
                this.showModal = false; this.showT('Metode pembayaran ditambahkan!'); await this.fetch()
            } catch (e) { this.formError = e.response?.data?.message || 'Gagal.' } finally { this.saving = false }
        },
        async confirmDelete(r) {
            if (!confirm(`Hapus metode "${r.metode_pembayaran}"?`)) return
            try { await adminApi.delete(`/metode-bayar/${r.id}`); this.showT('Dihapus.'); await this.fetch() }
            catch (e) { this.showT(e.response?.data?.message || 'Gagal.', 'error') }
        },
        logoUrl(f) { return `${(import.meta.env.VITE_API_URL || 'http://localhost:8000/api').replace('/api', '')}/storage/metode_bayar/${f}` },
        showT(msg, type = 'success') { this.toast = { show: true, message: msg, type }; setTimeout(() => { this.toast.show = false }, 3500) },
    },
}
</script>
<style scoped>
.logo-thumb {
    width: 2.5rem;
    height: 2.5rem;
    object-fit: contain;
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