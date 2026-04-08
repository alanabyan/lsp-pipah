<template>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-brand">
                <div class="brand-icon">
                    <span class="material-symbols-outlined">admin_panel_settings</span>
                </div>
                <div>
                    <p class="brand-name">Apotek Online</p>
                    <p class="brand-role">Admin Panel</p>
                </div>
            </div>

            <nav class="sidebar-nav">
                <p class="nav-section-label">Manajemen</p>
                <router-link v-for="item in filteredNavItems" :key="item.to" :to="item.to" class="nav-item"
                active-class="nav-item--active">
                <span class="material-symbols-outlined">{{ item.icon }}</span>
                {{ item.label }}</router-link>
            </nav>

            <div class="sidebar-footer">
                <div class="admin-info">
                    <span class="material-symbols-outlined admin-avatar">manage_accounts</span>
                    <div>
                        <p class="admin-name">{{ adminData?.name || 'Administrator' }}</p>
                        <p class="admin-email" style="text-transform: capitalize;">{{ adminData?.jabatan }}</p>
                    </div>
                </div>
                <button class="btn-logout" @click="logout">
                    <span class="material-symbols-outlined">logout</span> Logout
                </button>
            </div>
        </aside>

        <!-- Main -->
        <div class="admin-main">
            <!-- Topbar -->
            <header class="admin-topbar">
                <div class="topbar-left">
                    <h2 class="page-title">{{ pageTitle }}</h2>
                </div>
                <div class="topbar-right">
                    <router-link to="/" class="topbar-btn" title="Lihat Toko">
                        <span class="material-symbols-outlined">storefront</span>
                        Lihat Toko
                    </router-link>
                    <div class="admin-badge">
                        <span class="material-symbols-outlined">verified_user</span>
                        {{ displayJabatan }}
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="admin-content">
                <slot />
            </main>
        </div>
    </div>
</template>

<script>
import { adminAuth, adminApi } from '@/stores/adminAuth.js'

export default {
    name: 'AdminLayout',
    props: {
        pageTitle: { type: String, default: 'Dashboard' },
    },
    data() {
        return {
            adminData: adminAuth.getUser(), 
            allNavItems: [
    { label: 'Dashboard', icon: 'dashboard', to: '/admin/dashboard', roles: ['admin','pemilik'] },
    { label: 'Jenis Obat', icon: 'category', to: '/admin/jenis-obat', roles: ['admin', 'apoteker'] },
    { label: 'Obat', icon: 'medication', to: '/admin/obat', roles: ['admin'] },
    { label: 'Distributor', icon: 'local_shipping', to: '/admin/distributor', roles: ['admin', 'karyawan'] },
    { label: 'Pembelian', icon: 'shopping_cart', to: '/admin/pembelian', roles: ['kasir', 'karyawan'] },
    { label: 'Penjualan', icon: 'point_of_sale', to: '/admin/penjualan', roles: ['kasir',] },
    { label: 'Pembayaran', icon: 'payments', to: '/admin/pembayaran', roles: ['kasir'] },
    { label: 'Pengiriman', icon: 'local_shipping', to: '/admin/pengiriman', roles: ['karyawan'] },
    { label: 'Metode Pembayaran', icon: 'credit_card', to: '/admin/metode-pembayaran', roles: ['kasir'] },
    { label: 'Jenis Pengiriman', icon: 'conveyor_belt', to: '/admin/jenis-pengiriman', roles: ['karyawan'] },
],
        }
    },
    computed: {
        // Menu yang akan tampil di sidebar sesuai jabatan
        filteredNavItems() {
            return this.allNavItems.filter(item => adminAuth.hasRole(item.roles));
        },
        // Judul jabatan untuk tampilan badge (Admin, Apoteker, dll)
        displayJabatan() {
            return this.adminData?.jabatan?.toUpperCase() || 'STAFF';
        }
    },
    methods: {
        async logout() {
            try { await adminApi.post('/admin/logout') } catch { }
            adminAuth.clearSession()
            this.$router.push('/admin/login')
        },
    },
}
</script>

<style scoped>
.admin-layout {
    display: grid;
    grid-template-columns: 15rem 1fr;
    min-height: 100vh;
    background: #070b10;
    font-family: 'Plus Jakarta Sans', sans-serif;
    color: #fff;
}

/* Sidebar */
.admin-sidebar {
    background: #0d1117;
    border-right: 1px solid rgba(255, 255, 255, 0.06);
    display: flex;
    flex-direction: column;
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
}

.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1.5rem 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.brand-icon {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.625rem;
    background: rgba(99, 102, 241, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.brand-icon .material-symbols-outlined {
    color: #a5b4fc;
    font-size: 1.25rem;
}

.brand-name {
    font-family: 'Manrope', sans-serif;
    font-size: 0.875rem;
    font-weight: 800;
    color: #fff;
}

.brand-role {
    font-size: 0.65rem;
    color: rgba(255, 255, 255, 0.3);
    font-weight: 600;
}

.sidebar-nav {
    flex: 1;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.nav-section-label {
    font-size: 0.62rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: rgba(255, 255, 255, 0.2);
    padding: 0 0.5rem;
    margin-bottom: 0.35rem;
    margin-top: 0.25rem;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.55rem 0.75rem;
    border-radius: 0.625rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.45);
    text-decoration: none;
    transition: all 0.15s;
    border-left: 3px solid transparent;
}

.nav-item:hover {
    background: rgba(255, 255, 255, 0.05);
    color: #fff;
}

.nav-item--active {
    background: rgba(99, 102, 241, 0.12);
    color: #a5b4fc;
    border-left-color: #6366f1;
}

.nav-item .material-symbols-outlined {
    font-size: 1rem;
}

.sidebar-footer {
    padding: 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.admin-info {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.5rem;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 0.625rem;
}

.admin-avatar {
    font-size: 1.75rem;
    color: #a5b4fc;
}

.admin-name {
    font-size: 0.78rem;
    font-weight: 700;
    color: #fff;
}

.admin-email {
    font-size: 0.65rem;
    color: rgba(255, 255, 255, 0.3);
}

.btn-logout {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.5rem;
    border: 1px solid rgba(248, 113, 113, 0.15);
    background: rgba(248, 113, 113, 0.06);
    border-radius: 0.5rem;
    color: rgba(248, 113, 113, 0.6);
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s;
    font-family: inherit;
}

.btn-logout:hover {
    background: rgba(248, 113, 113, 0.12);
    color: #f87171;
}

.btn-logout .material-symbols-outlined {
    font-size: 0.9rem;
}

/* Main */
.admin-main {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.admin-topbar {
    position: sticky;
    top: 0;
    z-index: 30;
    background: rgba(7, 11, 16, 0.95);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 2rem;
    height: 3.75rem;
}

.page-title {
    font-family: 'Manrope', sans-serif;
    font-size: 1rem;
    font-weight: 800;
    color: #fff;
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.topbar-btn {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.4rem 0.875rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(255, 255, 255, 0.04);
    color: rgba(255, 255, 255, 0.55);
    border-radius: 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.15s;
}

.topbar-btn:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.08);
}

.topbar-btn .material-symbols-outlined {
    font-size: 0.9rem;
}

.admin-badge {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.35rem 0.75rem;
    background: rgba(99, 102, 241, 0.12);
    border: 1px solid rgba(99, 102, 241, 0.2);
    border-radius: 9999px;
    font-size: 0.72rem;
    font-weight: 800;
    color: #a5b4fc;
}

.admin-badge .material-symbols-outlined {
    font-size: 0.85rem;
}

.admin-content {
    padding: 2rem;
    flex: 1;
}
</style>