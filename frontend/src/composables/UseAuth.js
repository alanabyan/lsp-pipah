import { ref, computed } from 'vue'

// Deklarasi state di luar fungsi agar singleton (terbagi ke semua komponen)
const token = ref(localStorage.getItem('pelanggan_token') || null)
const user = ref((() => {
    try { 
        return JSON.parse(localStorage.getItem('pelanggan_data') || 'null') 
    } catch { return null }
})())

export function useAuth() {
    // 1. PASTIKAN INI ADA (Ini yang tadi bikin error karena tidak terdefinisi)
    const isLoggedIn = computed(() => !!token.value)

    const isAdmin = computed(() => {
        return !!(user.value && (user.value.jabatan === 'admin' || user.value.role === 'admin'))
    })

    function setAuth(newToken, userData) {
        token.value = newToken
        user.value = userData
        localStorage.setItem('pelanggan_token', newToken)
        localStorage.setItem('pelanggan_data', JSON.stringify(userData))
    }

    function clearAuth() {
        token.value = null
        user.value = null
        localStorage.removeItem('pelanggan_token')
        localStorage.removeItem('pelanggan_data')
    }

    // 2. Pastikan semua yang dipakai di LoginPage ada di return ini
    return { 
        token, 
        user, 
        isLoggedIn, 
        isAdmin, 
        setAuth, 
        clearAuth 
    }
}