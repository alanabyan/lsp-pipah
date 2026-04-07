// src/composables/useAuth.js
// Helper terpusat untuk cek auth & role di seluruh app

import { ref, computed } from 'vue'

const token = ref(localStorage.getItem('token') || null)
const pelanggan = ref((() => {
    try { return JSON.parse(localStorage.getItem('pelanggan') || 'null') }
    catch { return null }
})())

export function useAuth() {
    const isLoggedIn = computed(() => !!token.value)

    const isAdmin = computed(() => {
        const p = pelanggan.value
        return !!(p && p.email === 'admin@apotekonline.com')
    })

    function setAuth(newToken, newPelanggan) {
        token.value = newToken
        pelanggan.value = newPelanggan
        localStorage.setItem('token', newToken)
        localStorage.setItem('pelanggan', JSON.stringify(newPelanggan))
    }

    function clearAuth() {
        token.value = null
        pelanggan.value = null
        localStorage.removeItem('token')
        localStorage.removeItem('pelanggan')
    }

    return { token, pelanggan, isLoggedIn, isAdmin, setAuth, clearAuth }
}