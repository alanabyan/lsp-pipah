<template>
    <div class="admin-login">
        <div class="login-card">

            <!-- Brand -->
            <div class="login-brand">
                <div class="brand-icon">
                    <span class="material-symbols-outlined">admin_panel_settings</span>
                </div>
                <h1 class="brand-title">Admin Panel</h1>
                <p class="brand-sub">Apotek Online</p>
            </div>

            <!-- Alert -->
            <transition name="fade">
                <div v-if="errorMsg" class="alert alert--error">
                    <span class="material-symbols-outlined">error_outline</span>
                    {{ errorMsg }}
                </div>
            </transition>

            <!-- Form -->
            <form class="login-form" @submit.prevent="handleLogin">
                <div class="form-group">
                    <label class="form-label">Email Admin</label>
                    <div class="input-wrap">
                        <span class="material-symbols-outlined input-icon">mail</span>
                        <input v-model="form.email" type="email" class="form-input" placeholder="admin@apotekonline.com"
                            required autocomplete="email" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrap">
                        <span class="material-symbols-outlined input-icon">lock</span>
                        <input v-model="form.password" :type="showPass ? 'text' : 'password'" class="form-input"
                            placeholder="••••••••" required autocomplete="current-password" />
                        <button type="button" class="toggle-pass" @click="showPass = !showPass">
                            <span class="material-symbols-outlined">
                                {{ showPass ? 'visibility_off' : 'visibility' }}
                            </span>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login" :disabled="loading">
                    <span v-if="loading" class="material-symbols-outlined spin">progress_activity</span>
                    <span v-else class="material-symbols-outlined">shield_person</span>
                    {{ loading ? 'Memverifikasi...' : 'Masuk sebagai Admin' }}
                </button>
            </form>

            <!-- Info box -->
            <div class="info-box">
                <span class="material-symbols-outlined">info</span>
                <p>Hanya akun <strong>Staff Internal</strong></p>
            </div>

            <router-link to="/" class="back-link">
                <span class="material-symbols-outlined">arrow_back</span>
                Kembali ke Toko
            </router-link>
        </div>
    </div>
</template>

<script>
import { adminAuth, adminApi } from '@/stores/adminAuth.js'

export default {
    name: 'AdminLoginPage',

    data() {
        return {
            form: { email: '', password: '' },
            showPass: false,
            loading: false,
            errorMsg: null,
        }
    },

    mounted() {
        // Jika sudah login sebagai staff, langsung lempar ke dashboard
        if (adminAuth.isLoggedIn()) {
            this.$router.push('/admin/dashboard')
        }
    },

    methods: {
        async handleLogin() {
            this.loading = true
            this.errorMsg = null

            try {
                // 1. Hit API login (endpoint tetap sama /pelanggan/login atau /login)
                const res = await adminApi.post('/staff/login', {
                    email: this.form.email,
                    password: this.form.password,
                })

                // Ambil data user & token (sesuaikan nama 'pelanggan'/'user' dengan response backendmu)
                const { token } = res.data
                const userData = res.data.user || res.data.pelanggan

                // 2. CEK JABATAN: Jika dia cuma pelanggan biasa, tendang keluar!
                if (!userData.jabatan || userData.jabatan.toLowerCase() === 'pelanggan') {
                    this.errorMsg = 'Akses ditolak. Akun ini tidak memiliki hak akses ke Panel Admin.'
                    this.loading = false
                    return
                }

                // 3. Jika lolos, simpan sesi admin
                adminAuth.setSession(token, userData)

                // 4. Redirect ke dashboard admin
                this.$router.push('/admin/dashboard')

            } catch (err) {
                if (err.response?.status === 422) {
                    const errors = err.response.data?.errors || {}
                    this.errorMsg = Object.values(errors).flat()[0] || 'Email atau password salah.'
                } else if (err.response?.status === 401) {
                    this.errorMsg = 'Email atau password salah.'
                } else {
                    this.errorMsg = err.response?.data?.message || 'Gagal login. Coba lagi.'
                }
            } finally {
                this.loading = false
            }
        },
    },
}
</script>

<style scoped>
.admin-login {
    min-height: 100vh;
    background: #070b10;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Plus Jakarta Sans', sans-serif;
    padding: 1rem;
    position: relative;
}

/* Subtle background pattern */
.admin-login::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
        radial-gradient(circle at 20% 50%, rgba(99, 102, 241, 0.06) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(99, 102, 241, 0.04) 0%, transparent 40%);
    pointer-events: none;
}

.login-card {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 22rem;
    background: #0f1319;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 1.25rem;
    padding: 2.5rem 2rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Brand */
.login-brand {
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
}

.brand-icon {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 1rem;
    background: rgba(99, 102, 241, 0.15);
    border: 1px solid rgba(99, 102, 241, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
}

.brand-icon .material-symbols-outlined {
    font-size: 1.75rem;
    color: #a5b4fc;
}

.brand-title {
    font-family: 'Manrope', sans-serif;
    font-size: 1.4rem;
    font-weight: 900;
    color: #fff;
    letter-spacing: -0.02em;
}

.brand-sub {
    font-size: 0.78rem;
    color: rgba(255, 255, 255, 0.35);
}

/* Alert */
.alert {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-radius: 0.625rem;
    font-size: 0.8rem;
    font-weight: 600;
    line-height: 1.5;
}

.alert--error {
    background: rgba(248, 113, 113, 0.1);
    color: #fca5a5;
    border: 1px solid rgba(248, 113, 113, 0.2);
}

.alert .material-symbols-outlined {
    font-size: 1rem;
    flex-shrink: 0;
    margin-top: 1px;
}

/* Form */
.login-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.form-label {
    font-size: 0.72rem;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.4);
    text-transform: uppercase;
    letter-spacing: 0.07em;
}

.input-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.input-icon {
    position: absolute;
    left: 0.75rem;
    font-size: 1rem;
    color: rgba(255, 255, 255, 0.2);
    pointer-events: none;
}

.form-input {
    width: 100%;
    padding: 0.65rem 2.5rem;
    background: rgba(255, 255, 255, 0.05);
    border: 1.5px solid rgba(255, 255, 255, 0.08);
    border-radius: 0.625rem;
    color: #fff;
    font-size: 0.875rem;
    outline: none;
    font-family: inherit;
    transition: border 0.15s, background 0.15s;
}

.form-input:focus {
    border-color: #818cf8;
    background: rgba(99, 102, 241, 0.05);
}

.form-input::placeholder {
    color: rgba(255, 255, 255, 0.2);
}

.toggle-pass {
    position: absolute;
    right: 0.75rem;
    background: none;
    border: none;
    cursor: pointer;
    color: rgba(255, 255, 255, 0.25);
    display: flex;
    transition: color 0.15s;
}

.toggle-pass:hover {
    color: rgba(255, 255, 255, 0.6);
}

.toggle-pass .material-symbols-outlined {
    font-size: 1rem;
}

/* Submit button */
.btn-login {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem;
    border: none;
    border-radius: 0.625rem;
    background: #6366f1;
    color: #fff;
    font-size: 0.875rem;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
    box-shadow: 0 4px 16px rgba(99, 102, 241, 0.3);
    margin-top: 0.25rem;
}

.btn-login:hover:not(:disabled) {
    background: #4f46e5;
    transform: translateY(-1px);
    box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4);
}

.btn-login:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.btn-login .material-symbols-outlined {
    font-size: 1.1rem;
}

.spin {
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Info box */
.info-box {
    display: flex;
    align-items: flex-start;
    gap: 0.6rem;
    padding: 0.75rem 1rem;
    background: rgba(99, 102, 241, 0.07);
    border: 1px solid rgba(99, 102, 241, 0.2);
    border-radius: 0.625rem;
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.45);
    line-height: 1.5;
}

.info-box .material-symbols-outlined {
    font-size: 0.95rem;
    color: #818cf8;
    flex-shrink: 0;
    margin-top: 1px;
}

.info-box strong {
    color: #a5b4fc;
}

/* Back link */
.back-link {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    font-size: 0.78rem;
    color: rgba(255, 255, 255, 0.25);
    text-decoration: none;
    transition: color 0.15s;
}

.back-link:hover {
    color: #a5b4fc;
}

.back-link .material-symbols-outlined {
    font-size: 0.9rem;
}

/* Transition */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>