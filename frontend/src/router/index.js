import { createRouter, createWebHistory } from 'vue-router'

// Storefront (Customer)
import HomePage from '@/views/HomePage.vue'
import RegisterPage from '../views/RegisterPage.vue'
import LoginPage from '../views/LoginPage.vue'
import ProductDetailPage from '../views/ProductDetailPage.vue'
import CheckOutPage from '../views/CheckOutPage.vue'
import WellnessProfilePage from '../views/WellnessProfilePage.vue'

const requireAuth = (to, from, next) => {
    if (!localStorage.getItem('pelanggan_token')) {
        next('/login')
    } else {
        next()
    }
}

const redirectIfLoggedIn = (to, from, next) => {
    if (localStorage.getItem('pelanggan_token')) {
        next('/')
    } else {
        next()
    }
}

const routes = [
    {
        path: '/',
        name: 'Home',
        component: HomePage,
    },
    {
        path: '/login',
        name: 'Login',
        component: LoginPage,
        beforeEnter: redirectIfLoggedIn,
    },
    {
        path: '/register',
        name: 'Register',
        component: RegisterPage,
        beforeEnter: redirectIfLoggedIn,
    },
    {
        path: '/product/:id',
        name: 'ProductDetail',
        component: ProductDetailPage,
    },
    {
        path: '/checkout',
        name: 'Checkout',
        component: CheckOutPage,
        beforeEnter: requireAuth,
    },
    {
        path: '/profile',
        name: 'WellnessProfile',
        component: WellnessProfilePage,
        beforeEnter: requireAuth,
    },

    // ── Admin Panel ─────────────────────────────
    {
        path: '/sales-details',
        name: 'SalesDetails',
        component: HomePage,
    },
    {
        path: '/payment-methods',
        name: 'PaymentMethods',
        component: HomePage,
    },

    // Placeholder admin routes (ganti component nanti)
    { path: '/dashboard', name: 'Dashboard', component: HomePage },
    { path: '/inventory', name: 'Inventory', component: HomePage },
    { path: '/medicine-types', name: 'MedicineTypes', component: HomePage },
    { path: '/suppliers', name: 'Suppliers', component: HomePage },
    { path: '/purchasing', name: 'Purchasing', component: HomePage },
    { path: '/purchase-details', name: 'PurchaseDetails', component: HomePage },
    { path: '/sales', name: 'Sales', component: HomePage },
    { path: '/shipping-methods', name: 'ShippingMethods', component: HomePage },
    { path: '/reports', name: 'Reports', component: HomePage },

    // 404 fallback
    { path: '/:pathMatch(.*)*', redirect: '/' },
]

export default createRouter({
    history: createWebHistory(),
    routes,
})