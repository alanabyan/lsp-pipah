/**
 * adminAuth.js
 *
 * Sistem auth admin menggunakan endpoint login pelanggan yang sama.
 * Admin dikenali dari email: admin@apotekonline.com
 * Token disimpan terpisah dari token pelanggan biasa.
 */

import axios from 'axios'

const ADMIN_EMAIL = 'admin@apotekonline.com'
const ADMIN_TOKEN_KEY = 'admin_token'
const ADMIN_DATA_KEY = 'admin_data'

export const adminAuth = {
    /** Email yang dianggap admin */
    ADMIN_EMAIL,

    /** Cek apakah email adalah admin */
    isAdminEmail(email) {
        return email?.toLowerCase().trim() === ADMIN_EMAIL
    },

    getToken() { return localStorage.getItem(ADMIN_TOKEN_KEY) },
    getData() { return JSON.parse(localStorage.getItem(ADMIN_DATA_KEY) || 'null') },
    isLoggedIn() { return !!localStorage.getItem(ADMIN_TOKEN_KEY) },

    setSession(token, data) {
        localStorage.setItem(ADMIN_TOKEN_KEY, token)
        localStorage.setItem(ADMIN_DATA_KEY, JSON.stringify(data))
    },

    clearSession() {
        localStorage.removeItem(ADMIN_TOKEN_KEY)
        localStorage.removeItem(ADMIN_DATA_KEY)
    },
}

/**
 * Axios instance khusus admin.
 * Memakai token admin (terpisah dari token pelanggan).
 */
export const adminApi = axios.create({
    baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
})

// Sisipkan token admin di setiap request
adminApi.interceptors.request.use((config) => {
    const token = adminAuth.getToken()
    if (token) config.headers.Authorization = `Bearer ${token}`
    return config
})

// Jika 401 → sesi admin habis → redirect ke login admin
adminApi.interceptors.response.use(
    res => res,
    err => {
        if (err.response?.status === 401) {
            adminAuth.clearSession()
            window.location.href = '/admin/login'
        }
        return Promise.reject(err)
    }
)