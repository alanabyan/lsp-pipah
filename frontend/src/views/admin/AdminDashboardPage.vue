<template>
    <AdminLayout page-title="Dashboard">
        <div class="dashboard">
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div v-for="stat in stats" :key="stat.label" class="stat-card" :style="{ '--c': stat.color }">
                    <div class="stat-icon"><span class="material-symbols-outlined">{{ stat.icon }}</span></div>
                    <div class="stat-body">
                        <p class="stat-val">{{ loading ? '...' : stat.value }}</p>
                        <p class="stat-label">{{ stat.label }}</p>
                    </div>
                </div>
            </div>

            <!-- Recent Penjualan -->
            <div class="section-card">
                <div class="section-head">
                    <h3 class="section-title">Penjualan Terbaru</h3>
                    <router-link to="/admin/penjualan" class="section-link">Lihat Semua →</router-link>
                </div>
                <div v-if="loadingOrders" class="loading-rows">
                    <div v-for="i in 5" :key="i" class="skeleton"></div>
                </div>
                <table v-else class="mini-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="o in recentOrders" :key="o.id">
                            <td class="mono">#{{ pad(o.id) }}</td>
                            <td>{{ fmtDate(o.tgl_penjualan) }}</td>
                            <td><span :class="['sbadge', 'sbadge--' + sClass(o.status_order)]">{{ o.status_order }}</span>
                            </td>
                            <td class="price">{{ fmtRp(o.total_bayar) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Low Stock Warning -->
            <div class="section-card">
                <div class="section-head">
                    <h3 class="section-title">
                        <span class="material-symbols-outlined warn-icon">warning</span>
                        Stok Obat Hampir Habis
                    </h3>
                    <router-link to="/admin/obat" class="section-link">Kelola Stok →</router-link>
                </div>
                <div v-if="lowStockObat.length === 0 && !loading" class="no-warn">
                    <span class="material-symbols-outlined">check_circle</span> Semua stok aman.
                </div>
                <div v-else class="low-stock-list">
                    <div v-for="o in lowStockObat" :key="o.id" class="low-stock-item">
                        <span class="low-stock-name">{{ o.nama_obat }}</span>
                        <span class="low-stock-qty" :class="o.stok === 0 ? 'qty--empty' : 'qty--low'">
                            Stok: {{ o.stok }} {{ o.stok === 0 ? '(HABIS)' : '' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script>
import AdminLayout from '@/layouts/AdminLayout.vue'
import { adminApi } from '@/stores/adminAuth.js'
export default {
    name: 'AdminDashboardPage',
    components: { AdminLayout },
    data() {
        return {
            loading: true, loadingOrders: true,
            recentOrders: [], lowStockObat: [],
            stats: [
                { label: 'Total Obat', icon: 'medication', color: '#4ade80', value: 0 },
                { label: 'Total Penjualan', icon: 'point_of_sale', color: '#818cf8', value: 0 },
                { label: 'Menunggu Konfirmasi', icon: 'pending', color: '#fbbf24', value: 0 },
                { label: 'Pengiriman Aktif', icon: 'local_shipping', color: '#60a5fa', value: 0 },
            ],
        }
    },
    mounted() { this.fetchStats() },
    methods: {
        async fetchStats() {
            this.loading = true; this.loadingOrders = true
            try {
                const [obatRes, penjualanRes, pengirimanRes] = await Promise.all([
                    adminApi.get('/obat'),
                    adminApi.get('/penjualan'),
                    adminApi.get('/pengiriman'),
                ])
                const obat = obatRes.data || []
                const penjualan = penjualanRes.data || []
                const pengiriman = pengirimanRes.data || []

                this.stats[0].value = obat.length
                this.stats[1].value = penjualan.length
                this.stats[2].value = penjualan.filter(o => o.status_order === 'Menunggu Konfirmasi').length
                this.stats[3].value = pengiriman.filter(p => p.status_kirim !== 'Diterima').length

                this.recentOrders = penjualan.slice(-10).reverse()
                this.lowStockObat = obat.filter(o => o.stok <= 10).sort((a, b) => a.stok - b.stok)
            } catch { }
            finally { this.loading = false; this.loadingOrders = false }
        },
        pad(id) { return String(id || 0).padStart(5, '0') },
        fmtRp(v) { return 'Rp ' + Number(v || 0).toLocaleString('id-ID') },
        fmtDate(d) { return d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '—' },
        sClass(s) {
            const m = { 'Menunggu Konfirmasi': 'wait', 'Diproses': 'process', 'Menunggu Kurir': 'ship', 'Selesai': 'done', 'Dibatalkan Pembeli': 'cancel', 'Dibatalkan Penjual': 'cancel' }
            return m[s] || 'wait'
        },
    },
}
</script>

<style scoped>
.dashboard {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Stats */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
}

.stat-card {
    background: #0f1319;
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 1rem;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    border-top: 3px solid var(--c, #6366f1);
}

.stat-icon {
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 0.75rem;
    background: rgba(255, 255, 255, 0.04);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-icon .material-symbols-outlined {
    font-size: 1.3rem;
    color: var(--c, #a5b4fc);
}

.stat-val {
    font-family: 'Manrope', sans-serif;
    font-size: 1.75rem;
    font-weight: 900;
    color: #fff;
    line-height: 1;
}

.stat-label {
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.35);
    font-weight: 600;
    margin-top: 3px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

/* Section Cards */
.section-card {
    background: #0f1319;
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 1rem;
    padding: 1.5rem;
}

.section-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.25rem;
}

.section-title {
    font-family: 'Manrope', sans-serif;
    font-size: 0.95rem;
    font-weight: 700;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.section-link {
    font-size: 0.75rem;
    font-weight: 700;
    color: #818cf8;
    text-decoration: none;
    transition: color 0.15s;
}

.section-link:hover {
    color: #a5b4fc;
}

.warn-icon {
    font-size: 1rem;
    color: #fbbf24;
}

/* Mini table */
.mini-table {
    width: 100%;
    border-collapse: collapse;
}

.mini-table thead tr {
    border-bottom: 1px solid rgba(255, 255, 255, 0.07);
}

.mini-table th {
    padding: 0.5rem 0.75rem;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: rgba(255, 255, 255, 0.25);
    text-align: left;
}

.mini-table td {
    padding: 0.75rem 0.75rem;
    font-size: 0.82rem;
    color: rgba(255, 255, 255, 0.65);
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
}

.mini-table tr:last-child td {
    border-bottom: none;
}

.mono {
    font-family: monospace;
    color: rgba(255, 255, 255, 0.8);
    font-weight: 700;
}

.price {
    font-family: 'Manrope', sans-serif;
    font-weight: 700;
    color: #4ade80;
}

.sbadge {
    padding: 0.15rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.63rem;
    font-weight: 800;
}

.sbadge--wait {
    background: rgba(251, 191, 36, 0.12);
    color: #fbbf24;
}

.sbadge--process {
    background: rgba(99, 102, 241, 0.12);
    color: #a5b4fc;
}

.sbadge--ship {
    background: rgba(56, 189, 248, 0.12);
    color: #7dd3fc;
}

.sbadge--done {
    background: rgba(74, 222, 128, 0.12);
    color: #4ade80;
}

.sbadge--cancel {
    background: rgba(248, 113, 113, 0.12);
    color: #f87171;
}

/* Low Stock */
.no-warn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.82rem;
    color: rgba(74, 222, 128, 0.7);
}

.no-warn .material-symbols-outlined {
    font-size: 1rem;
    color: #4ade80;
}

.low-stock-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.low-stock-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.6rem 0.875rem;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 0.5rem;
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.low-stock-name {
    font-size: 0.82rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.7);
}

.low-stock-qty {
    font-size: 0.75rem;
    font-weight: 800;
}

.qty--low {
    color: #fbbf24;
}

.qty--empty {
    color: #f87171;
}

/* Skeleton */
.loading-rows {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.skeleton {
    height: 2.5rem;
    border-radius: 0.5rem;
    background: linear-gradient(90deg, rgba(255, 255, 255, 0.04) 25%, rgba(255, 255, 255, 0.08) 50%, rgba(255, 255, 255, 0.04) 75%);
    background-size: 200% 100%;
    animation: shimmer 1.4s infinite;
}

@keyframes shimmer {
    0% {
        background-position: 200% 0
    }

    100% {
        background-position: -200% 0
    }
}

@media(max-width:768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>