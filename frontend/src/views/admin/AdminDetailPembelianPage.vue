<template>
    <AdminLayout page-title="Detail Pembelian">
        <div class="filter-bar">
            <AdminField label="Filter ID Pembelian" type="number" v-model="filterPembelianId"
                placeholder="Kosongkan = semua" hint="Masukkan ID nota pembelian untuk filter" />
            <button class="btn btn--indigo" @click="fetchDetail">Tampilkan</button>
        </div>

        <AdminTable title="Detail Item Pembelian" :columns="cols" :data="list" :loading="loading"
            :search-keys="['id_pembelian']" @add="openAdd" @delete="confirmDelete" :show-edit="false">
            <template #cell-subtotal="{ value }"><span class="price">{{ fmtRp(value) }}</span></template>
            <template #cell-harga_beli="{ value }">{{ fmtRp(value) }}</template>
        </AdminTable>

        <AdminModal :show="showModal" title="Tambah Item ke Nota Pembelian" :loading="saving"
            submit-label="Tambah & Update Stok" submit-icon="add_circle" @close="showModal = false" @submit="save">
            <div class="note-box">
                <span class="material-symbols-outlined">info</span>
                Stok obat akan otomatis <strong>bertambah</strong> setelah item disimpan.
            </div>
            <AdminField label="ID Nota Pembelian" type="number" v-model="form.id_pembelian" required
                hint="ID dari tabel pembelian" />
            <AdminField label="Obat" type="select" v-model="form.id_obat" :options="obatOptions" required />
            <AdminField label="Jumlah" type="number" v-model="form.jumlah" placeholder="10" min="1" required />
            <AdminField label="Harga Beli (Rp)" type="number" v-model="form.harga_beli" placeholder="40000" min="0"
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
    name: 'AdminDetailPembelianPage',
    components: { AdminLayout, AdminTable, AdminModal, AdminField, ToastNotif },
    data() {
        return {
            list: [], obatList: [], loading: false, filterPembelianId: '',
            showModal: false, saving: false, formError: null,
            form: { id_pembelian: '', id_obat: '', jumlah: '', harga_beli: '' },
            toast: { show: false, message: '', type: 'success' },
            cols: [
                { key: 'id_pembelian', label: 'ID Pembelian' },
                { key: 'id_obat', label: 'ID Obat' },
                { key: 'jumlah', label: 'Jumlah' },
                { key: 'harga_beli', label: 'Harga Beli' },
                { key: 'subtotal', label: 'Subtotal' },
            ],
        }
    },
    computed: {
        obatOptions() { return this.obatList.map(o => ({ value: o.id, label: o.nama_obat })) },
    },
    mounted() { this.fetchObat(); this.fetchDetail() },
    methods: {
        async fetchObat() { try { this.obatList = (await adminApi.get('/obat')).data } catch { } },
        async fetchDetail() {
            this.loading = true
            try {
                // GET /detail-pembelian/nota/:id atau ambil semua
                const url = this.filterPembelianId ? `/detail-pembelian/nota/${this.filterPembelianId}` : '/detail-pembelian'
                this.list = (await adminApi.get(url)).data
            } catch { this.list = [] } finally { this.loading = false }
        },
        openAdd() { this.form = { id_pembelian: this.filterPembelianId || '', id_obat: '', jumlah: '', harga_beli: '' }; this.formError = null; this.showModal = true },
        async save() {
            if (!this.form.id_pembelian || !this.form.id_obat || !this.form.jumlah || !this.form.harga_beli) { this.formError = 'Semua field wajib diisi.'; return }
            this.saving = true; this.formError = null
            try {
                // POST /detail-pembelian — controller store() → simpan detail & update stok obat
                await adminApi.post('/detail-pembelian', {
                    id_pembelian: this.form.id_pembelian,
                    id_obat: this.form.id_obat,
                    jumlah: this.form.jumlah,
                    harga_beli: this.form.harga_beli,
                })
                this.showModal = false; this.showT('Item ditambahkan & stok obat bertambah!'); await this.fetchDetail()
            } catch (e) { this.formError = e.response?.data?.message || 'Gagal.' } finally { this.saving = false }
        },
        async confirmDelete(r) {
            if (!confirm('Hapus item ini? Stok obat akan dikurangi kembali.')) return
            try {
                // DELETE /detail-pembelian/:id → controller destroy() → stok dikurangi
                await adminApi.delete(`/detail-pembelian/${r.id}`)
                this.showT('Item dihapus & stok dikembalikan.'); await this.fetchDetail()
            } catch (e) { this.showT(e.response?.data?.message || 'Gagal.', 'error') }
        },
        fmtRp(v) { return 'Rp ' + Number(v || 0).toLocaleString('id-ID') },
        showT(msg, type = 'success') { this.toast = { show: true, message: msg, type }; setTimeout(() => { this.toast.show = false }, 3500) },
    },
}
</script>
<style scoped>
.filter-bar {
    display: flex;
    align-items: flex-end;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.55rem 1.1rem;
    border: none;
    border-radius: 0.625rem;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s;
    font-family: inherit;
}

.btn--indigo {
    background: #6366f1;
    color: #fff;
}

.btn--indigo:hover {
    background: #4f46e5;
}

.price {
    font-family: 'Manrope', sans-serif;
    font-weight: 700;
    color: #4ade80;
}

.note-box {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    background: rgba(99, 102, 241, 0.08);
    border: 1px solid rgba(99, 102, 241, 0.2);
    border-radius: 0.5rem;
    font-size: 0.78rem;
    color: #a5b4fc;
}

.note-box .material-symbols-outlined {
    font-size: 1rem;
    flex-shrink: 0;
}

.note-box strong {
    color: #c7d2fe;
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