import { createRouter, createWebHistory } from 'vue-router'
import { adminAuth } from '@/stores/adminAuth.js'

// ── Storefront ──────────────────────────────
import HomePage from '@/views/HomePage.vue'
import LoginPage from '@/views/LoginPage.vue'
import RegisterPage from '@/views/RegisterPage.vue'
import ProductDetailPage from '@/views/ProductDetailPage.vue'
import CheckoutPage from '@/views/CheckoutPage.vue'
import WellnessProfilePage from '@/views/WellnessProfilePage.vue'

// ── Admin ───────────────────────────────────
import AdminLoginPage from '@/views/admin/AdminLoginPage.vue'
import AdminDashboardPage from '@/views/admin/AdminDashboardPage.vue'
import AdminObatPage from '@/views/admin/AdminObatPage.vue'
import AdminJenisObatPage from '@/views/admin/AdminJenisObatPage.vue'
import AdminDistributorPage from '@/views/admin/AdminDistributorPage.vue'
import AdminPembelianPage from '@/views/admin/AdminPembelianPage.vue'
import AdminDetailPembelianPage from '@/views/admin/AdminDetailPembelianPage.vue'
import AdminPenjualanPage from '@/views/admin/AdminPenjualanPage.vue'
import AdminPembayaranPage from '@/views/admin/AdminPembayaranPage.vue'
import AdminPengirimanPage from '@/views/admin/AdminPengirimanPage.vue'
import AdminMetodePembayaranPage from '@/views/admin/AdminMetodePembayaranPage.vue'
import AdminJenisPengirimanPage from '@/views/admin/AdminJenisPengirimanPage.vue'
import CartPage from '../views/CartPage.vue'

// ── Guards ──────────────────────────────────
const requirePelanggan = (to, from, next) => {
    localStorage.getItem('pelanggan_token') ? next() : next('/login')
}
const redirectIfPelangganLoggedIn = (to, from, next) => {
    localStorage.getItem('pelanggan_token') ? next('/') : next()
}
const requireAdmin = (to, from, next) => {
    const ok = adminAuth.isLoggedIn() && adminAuth.isAdminEmail(adminAuth.getData()?.email)
    if (!ok) { adminAuth.clearSession(); next('/admin/login') } else next()
}
const redirectIfAdminLoggedIn = (to, from, next) => {
    const ok = adminAuth.isLoggedIn() && adminAuth.isAdminEmail(adminAuth.getData()?.email)
    ok ? next('/admin/dashboard') : next()
}

const routes = [
    // ── Storefront ────────────────────────────
    { path: '/', name: 'Home', component: HomePage },
    { path: '/login', name: 'Login', component: LoginPage, beforeEnter: redirectIfPelangganLoggedIn },
    { path: '/register', name: 'Register', component: RegisterPage, beforeEnter: redirectIfPelangganLoggedIn },
    { path: '/product/:id', name: 'ProductDetail', component: ProductDetailPage },
    { path: '/checkout', name: 'Checkout', component: CheckoutPage, beforeEnter: requirePelanggan },
    { path: '/profile', name: 'Profile', component: WellnessProfilePage, beforeEnter: requirePelanggan },
    { path: '/cart', name: 'Cart', component: CartPage, beforeEnter: requirePelanggan },

    // ── Admin ─────────────────────────────────
    { path: '/admin', redirect: '/admin/login' },
    { path: '/admin/login', name: 'AdminLogin', component: AdminLoginPage, beforeEnter: redirectIfAdminLoggedIn },
    { path: '/admin/dashboard', name: 'AdminDashboard', component: AdminDashboardPage, beforeEnter: requireAdmin },
    { path: '/admin/obat', name: 'AdminObat', component: AdminObatPage, beforeEnter: requireAdmin },
    { path: '/admin/jenis-obat', name: 'AdminJenisObat', component: AdminJenisObatPage, beforeEnter: requireAdmin },
    { path: '/admin/distributor', name: 'AdminDistributor', component: AdminDistributorPage, beforeEnter: requireAdmin },
    { path: '/admin/pembelian', name: 'AdminPembelian', component: AdminPembelianPage, beforeEnter: requireAdmin },
    { path: '/admin/detail-pembelian', name: 'AdminDetailPembelian', component: AdminDetailPembelianPage, beforeEnter: requireAdmin },
    { path: '/admin/penjualan', name: 'AdminPenjualan', component: AdminPenjualanPage, beforeEnter: requireAdmin },
    { path: '/admin/pembayaran', name: 'AdminPembayaran', component: AdminPembayaranPage, beforeEnter: requireAdmin },
    { path: '/admin/pengiriman', name: 'AdminPengiriman', component: AdminPengirimanPage, beforeEnter: requireAdmin },
    { path: '/admin/metode-pembayaran', name: 'AdminMetodePembayaran', component: AdminMetodePembayaranPage, beforeEnter: requireAdmin },
    { path: '/admin/jenis-pengiriman', name: 'AdminJenisPengiriman', component: AdminJenisPengirimanPage, beforeEnter: requireAdmin },

    // 404
    { path: '/:pathMatch(.*)*', redirect: '/' },
]

export default createRouter({
    history: createWebHistory(),
    routes,
})