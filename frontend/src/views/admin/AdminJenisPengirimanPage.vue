<template>
    <AdminLayout page-title="Jenis Pengiriman">
        <AdminTable title="Jenis Pengiriman" :columns="cols" :data="list" :loading="loading"
            :search-keys="['nama_ekspedisi', 'jenis_kirim']" :show-edit="false" @add="openAdd" @delete="confirmDelete">
            
            <template #cell-logo_ekspedisi="{ row }">
                <img v-if="row.full_url_logo" :src="row.full_url_logo" class="logo-thumb" />
                <span v-else>—</span>
            </template>
        </AdminTable>

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
            
            <AdminField label="Logo Ekspedisi" type="file" @update:modelValue="form.logo_ekspedisi = $event"
                required hint="JPG/PNG max 1MB" />
            
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
            list: [], 
            loading: true, 
            showModal: false, 
            saving: false, 
            formError: null,
            // Pastikan logo_ekspedisi default-nya null untuk menampung File object
            form: { jenis_kirim: '', nama_ekspedisi: '', logo_ekspedisi: null },
            toast: { show: false, message: '', type: 'success' },
            cols: [
                { key: 'logo_ekspedisi', label: 'Logo' }, // Urutan kolom bisa disesuaikan
                { key: 'nama_ekspedisi', label: 'Nama Ekspedisi' },
                { key: 'jenis_kirim', label: 'Jenis Kirim' },
            ],
        }
    },
    mounted() { 
        this.fetch() 
    },
    methods: {
        async fetch() {
            this.loading = true
            try {
                const res = await adminApi.get('/jenis-pengiriman')
                this.list = res.data
            } catch { 
                // Error handling silent
            } finally { 
                this.loading = false 
            }
        },

        openAdd() { 
            // Reset form saat buka modal tambah
            this.form = { jenis_kirim: '', nama_ekspedisi: '', logo_ekspedisi: null }; 
            this.formError = null; 
            this.showModal = true 
        },

        async save() {
            // Validasi input
            if (!this.form.jenis_kirim || !this.form.nama_ekspedisi || !this.form.logo_ekspedisi) {
                this.formError = 'Semua field wajib diisi.'; 
                return 
            }

            this.saving = true; 
            this.formError = null

            try {
                // WAJIB: Gunakan FormData untuk upload file
                const fd = new FormData()
                fd.append('jenis_kirim', this.form.jenis_kirim)
                fd.append('nama_ekspedisi', this.form.nama_ekspedisi)
                
                // Pastikan yang di-append adalah File dari input
                if (this.form.logo_ekspedisi) {
                    fd.append('logo_ekspedisi', this.form.logo_ekspedisi)
                }

                // Sertakan headers multipart/form-data
                await adminApi.post('/jenis-pengiriman', fd, { 
                    headers: { 'Content-Type': 'multipart/form-data' } 
                })

                this.showModal = false; 
                this.showT('Jenis pengiriman ditambahkan!'); 
                await this.fetch()
            } catch (e) { 
                this.formError = e.response?.data?.message || 'Gagal menyimpan data.' 
            } finally { 
                this.saving = false 
            }
        },

        async confirmDelete(r) {
            if (!confirm(`Hapus "${r.nama_ekspedisi}"?`)) return
            try {
                await adminApi.delete(`/jenis-pengiriman/${r.id}`)
                this.showT('Dihapus.'); 
                await this.fetch()
            } catch (e) { 
                this.showT(e.response?.data?.message || 'Gagal menghapus.', 'error') 
            }
        },
        showT(msg, type = 'success') { 
            this.toast = { show: true, message: msg, type }; 
            setTimeout(() => { this.toast.show = false }, 3500) 
        },
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