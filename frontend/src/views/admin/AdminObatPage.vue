<template>
    <AdminLayout page-title="Manajemen Obat">
        <AdminTable title="Data Obat" :columns="cols" :data="obatList" :loading="loading"
            :search-keys="['nama_obat', 'deskripsi_obat']" @add="openAdd" @edit="openEdit" @delete="confirmDelete">
            <template #cell-foto1="{ row }">
                <img v-if="row.foto1" :src="imgUrl(row.foto1)" class="thumb" :alt="row.nama_obat" />
                <span v-else class="no-img">—</span>
            </template>
            <template #cell-harga_jual="{ value }">
                <span class="price-cell">{{ fmtRp(value) }}</span>
            </template>
            <template #cell-stok="{ value }">
                <span :class="['stock-badge', value <= 5 ? 'stock-badge--low' : 'stock-badge--ok']">{{ value }}</span>
            </template>
        </AdminTable>

        <AdminModal :show="showModal" :title="isEdit ? 'Edit Obat' : 'Tambah Obat Baru'" :loading="saving"
            :submit-label="isEdit ? 'Perbarui' : 'Simpan'" width="36rem" @close="closeModal" @submit="save">
            <div class="form-grid">
                <AdminField label="Nama Obat" v-model="form.nama_obat" placeholder="Amoxicillin 500mg" required />
                <AdminField label="Jenis Obat" type="select" v-model="form.idjenis" :options="jenisOptions" required />
                <AdminField label="Harga Jual (Rp)" type="number" v-model="form.harga_jual" placeholder="45000" min="0"
                    required />
                <AdminField label="Stok" type="number" v-model="form.stok" placeholder="100" min="0" required />
                <AdminField label="Deskripsi" type="textarea" v-model="form.deskripsi_obat"
                    placeholder="Keterangan obat..." :rows="3" />
                <div class="foto-row">
                    <AdminField label="Foto 1" type="file" accept="image/jpeg,image/png,image/jpg"
                        @update:modelValue="form.foto1 = $event" hint="JPG/PNG max 2MB" />
                    <AdminField label="Foto 2" type="file" accept="image/jpeg,image/png,image/jpg"
                        @update:modelValue="form.foto2 = $event" hint="Opsional" />
                    <AdminField label="Foto 3" type="file" accept="image/jpeg,image/png,image/jpg"
                        @update:modelValue="form.foto3 = $event" hint="Opsional" />
                </div>
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
    name: 'AdminObatPage',
    components: { AdminLayout, AdminTable, AdminModal, AdminField, ToastNotif },
    data() {
        return {
            obatList: [], jenisList: [], loading: true,
            showModal: false, isEdit: false, saving: false, formError: null,
            form: { id: null, idjenis: '', nama_obat: '', harga_jual: '', stok: '', deskripsi_obat: '', foto1: null, foto2: null, foto3: null },
            toast: { show: false, message: '', type: 'success' },
            cols: [
                { key: 'foto1', label: 'Foto' },
                { key: 'nama_obat', label: 'Nama Obat' },
                { key: 'harga_jual', label: 'Harga Jual' },
                { key: 'stok', label: 'Stok' },
                { key: 'deskripsi_obat', label: 'Deskripsi' },
            ],
        }
    },
    computed: {
        jenisOptions() { return this.jenisList.map(j => ({ value: j.id, label: j.jenis })) },
    },
    mounted() { this.fetchAll() },
    methods: {
        async fetchAll() {
            this.loading = true
            try {
                const [obatRes, jenisRes] = await Promise.all([
                    adminApi.get('/obat'),         // GET /api/obat
                    adminApi.get('/jenis-obat'),   // GET /api/jenis-obat
                ])
                this.obatList = obatRes.data
                this.jenisList = jenisRes.data
            } catch { this.showToast('Gagal memuat data obat.', 'error') }
            finally { this.loading = false }
        },

        openAdd() {
            this.isEdit = false
            this.form = { id: null, idjenis: '', nama_obat: '', harga_jual: '', stok: '', deskripsi_obat: '', foto1: null, foto2: null, foto3: null }
            this.formError = null; this.showModal = true
        },
        openEdit(row) {
            this.isEdit = true
            this.form = { id: row.id, idjenis: row.idjenis, nama_obat: row.nama_obat, harga_jual: row.harga_jual, stok: row.stok, deskripsi_obat: row.deskripsi_obat || '', foto1: null, foto2: null, foto3: null }
            this.formError = null; this.showModal = true
        },
        closeModal() { this.showModal = false },

        async save() {
            if (!this.form.nama_obat || !this.form.harga_jual || !this.form.stok || !this.form.idjenis) {
                this.formError = 'Nama, jenis, harga, dan stok wajib diisi.'; return
            }
            this.saving = true; this.formError = null
            try {
                const fd = new FormData()
                fd.append('idjenis', this.form.idjenis)
                fd.append('nama_obat', this.form.nama_obat)
                fd.append('harga_jual', this.form.harga_jual)
                fd.append('stok', this.form.stok)
                if (this.form.deskripsi_obat) fd.append('deskripsi_obat', this.form.deskripsi_obat)
                if (this.form.foto1) fd.append('foto1', this.form.foto1)
                if (this.form.foto2) fd.append('foto2', this.form.foto2)
                if (this.form.foto3) fd.append('foto3', this.form.foto3)

                if (this.isEdit) {
                    // POST /api/edit-obat/{id}
                    await adminApi.post(`/edit-obat/${this.form.id}`, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
                    this.showToast('Obat berhasil diperbarui!')
                } else {
                    // POST /api/tambah-obat
                    await adminApi.post('/tambah-obat', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
                    this.showToast('Obat berhasil ditambahkan!')
                }
                this.closeModal(); await this.fetchAll()
            } catch (err) { this.formError = err.response?.data?.message || 'Gagal menyimpan.' }
            finally { this.saving = false }
        },

        async confirmDelete(row) {
            if (!confirm(`Hapus obat "${row.nama_obat}"?`)) return
            try {
                // DELETE /api/hapus-obat/{id}
                await adminApi.delete(`/hapus-obat/${row.id}`)
                this.showToast('Obat berhasil dihapus.')
                await this.fetchAll()
            } catch (err) { this.showToast(err.response?.data?.message || 'Gagal menghapus.', 'error') }
        },

        imgUrl(f) { return `${(import.meta.env.VITE_API_URL || 'http://localhost:8000/api').replace('/api', '')}/storage/obat/${f}` },
        fmtRp(v) { return 'Rp ' + Number(v || 0).toLocaleString('id-ID') },
        showToast(message, type = 'success') { this.toast = { show: true, message, type }; setTimeout(() => { this.toast.show = false }, 3500) },
    },
}
</script>

<style scoped>
.form-grid {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.foto-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
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

.thumb {
    width: 2.5rem;
    height: 2.5rem;
    object-fit: cover;
    border-radius: 0.375rem;
}

.no-img {
    color: rgba(255, 255, 255, 0.2);
    font-size: 0.75rem;
}

.price-cell {
    font-family: 'Manrope', sans-serif;
    font-weight: 700;
    color: #4ade80;
    font-size: 0.875rem;
}

.stock-badge {
    padding: 0.18rem 0.55rem;
    border-radius: 9999px;
    font-size: 0.72rem;
    font-weight: 700;
}

.stock-badge--ok {
    background: rgba(74, 222, 128, 0.12);
    color: #4ade80;
}

.stock-badge--low {
    background: rgba(251, 191, 36, 0.12);
    color: #fbbf24;
}
</style>