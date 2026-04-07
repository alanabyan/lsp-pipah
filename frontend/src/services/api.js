/**
 * api.js — Axios instance untuk Pelanggan
 *
 * Semua endpoint disesuaikan dengan routes/api.php:
 *
 * AUTH (prefix /pelanggan):
 *   POST /pelanggan/register
 *   POST /pelanggan/login
 *   GET  /pelanggan/me              (auth)
 *   POST /pelanggan/logout          (auth)
 *
 * KERANJANG (prefix /pelanggan, auth):
 *   GET    /pelanggan/keranjang
 *   POST   /pelanggan/tambah-keranjang
 *   POST   /pelanggan/edit-keranjang/{id}
 *   DELETE /pelanggan/hapus-keranjang/{id}
 *
 * PUBLIC:
 *   GET /jenis-obat
 *   GET /obat
 *   GET /metode-bayar
 *   GET /jenis-pengiriman
 *
 * PENJUALAN (public di api.php):
 *   GET  /penjualan
 *   POST /penjualan
 *   GET  /penjualan/{id}
 *   PUT  /penjualan/{id}
 *
 * PEMBAYARAN:
 *   POST /pembayaran                   → upload bukti bayar
 *   POST /pembayaran-konfirmasi/{id}   → konfirmasi admin
 */

import axios from 'axios'

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
})

// Sisipkan token pelanggan di setiap request
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('pelanggan_token')
    if (token) config.headers.Authorization = `Bearer ${token}`
    return config
})

export default api