/**
 * adminAuth.js
 * * Sistem auth untuk internal staff (Admin, Apoteker, Kasir, dll).
 * Sekarang menggunakan sistem Jabatan (Role-Based Access Control).
 */

import axios from 'axios'

const ADMIN_TOKEN_KEY = 'admin_token'
const ADMIN_DATA_KEY = 'admin_data'

export const adminAuth = {
    /** Mendapatkan Token */

    isAdminEmail() {
        const user = this.getUser();
        // Kalau user ada dan jabatannya bukan pelanggan, kita anggap dia 'admin email' (staff)
        return !!(user && user.jabatan && user.jabatan.toLowerCase() !== 'pelanggan');
    },
    getToken() { 
        return localStorage.getItem(ADMIN_TOKEN_KEY) 
    },

    /** Mendapatkan Data User Lengkap (termasuk jabatan) */
    getUser() { 
        try {
            return JSON.parse(localStorage.getItem(ADMIN_DATA_KEY) || 'null') 
        } catch {
            return null
        }
    },
    getData() {
        return this.getUser();
    },

    /** Cek apakah sudah login */
    isLoggedIn() { 
        return !!localStorage.getItem(ADMIN_TOKEN_KEY) 
    },

    /** * FUNGSI SAKTI: Cek Jabatan
     * Contoh penggunaan: adminAuth.hasRole(['admin', 'apoteker'])
     */
    hasRole(allowedRoles) {
        const user = this.getUser()
        if (!user || !user.jabatan) return false
        
        // Pastikan huruf kecil semua biar gak bentrok (case-insensitive)
        const userRole = user.jabatan.toLowerCase()
        
        // Admin selalu punya akses ke semua (Opsional, tergantung kemauan kamu)
        // if (userRole === 'admin') return true

        return allowedRoles.map(r => r.toLowerCase()).includes(userRole)
    },

    /** Simpan Sesi setelah Login Berhasil */
    setSession(token, data) {
        localStorage.setItem(ADMIN_TOKEN_KEY, token)
        localStorage.setItem(ADMIN_DATA_KEY, JSON.stringify(data))
    },

    /** Hapus Sesi (Logout) */
    clearSession() {
        localStorage.removeItem(ADMIN_TOKEN_KEY)
        localStorage.removeItem(ADMIN_DATA_KEY)
    },
}

/**
 * Axios instance khusus staff internal.
 */
export const adminApi = axios.create({
    baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
})

/** Interceptor: Selalu selipkan Token di Header */
adminApi.interceptors.request.use((config) => {
    const token = adminAuth.getToken()
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    return config
})

/** Interceptor: Tangani error auth (401 atau 403) */
adminApi.interceptors.response.use(
    res => res,
    err => {
        // Jika token mati atau tidak sah
        if (err.response?.status === 401) {
            adminAuth.clearSession()
            window.location.href = '/admin/login'
        }
        // Jika jabatan tidak punya akses ke fitur tersebut
        if (err.response?.status === 403) {
            alert('Ups! Jabatan Anda tidak diizinkan mengakses fitur ini.')
        }
        return Promise.reject(err)
    }
)