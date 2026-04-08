<template>
  <div class="auth-layout">
    <!-- Left Panel -->
    <div class="auth-left">
      <div class="auth-left-inner">
        <router-link to="/" class="auth-brand">
          <span class="brand-icon">⚕</span>
          <span class="brand-text">Apotek<span class="brand-accent">Online</span></span>
        </router-link>

        <div class="auth-left-content">
          <div class="left-illustration">
            <div class="pill pill--1"></div>
            <div class="pill pill--2"></div>
            <div class="pill pill--3"></div>
            <span class="material-symbols-outlined left-icon">local_pharmacy</span>
          </div>
          <h2 class="left-heading">Precision care for your digital wellness.</h2>
          <p class="left-sub">Akses ratusan produk farmasi berkualitas, dikirim langsung ke rumah Anda.</p>
        </div>

        <div class="left-features">
          <div class="left-feature">
            <span class="material-symbols-outlined feat-icon">verified</span>
            <span>Produk BPOM Bersertifikat</span>
          </div>
          <div class="left-feature">
            <span class="material-symbols-outlined feat-icon">bolt</span>
            <span>Pengiriman Cepat &amp; Aman</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Panel: Form -->
    <div class="auth-right">
      <div class="auth-form-wrap">
        <!-- Header -->
        <div class="form-header">
          <h1 class="form-title">Welcome Back</h1>
          <p class="form-sub">Masuk ke akun Anda untuk melanjutkan belanja.</p>
        </div>

        <!-- Alert error -->
        <transition name="fade">
          <div v-if="errorMsg" class="alert alert--error">
            <span class="material-symbols-outlined">error_outline</span>
            {{ errorMsg }}
          </div>
        </transition>

        <!-- Form -->
        <form class="auth-form" @submit.prevent="handleLogin">
          <div class="form-group">
            <label class="form-label">Email</label>
            <div class="input-wrap">
              <span class="material-symbols-outlined input-icon">mail</span>
              <input
                v-model="form.email"
                type="email"
                class="form-input"
                placeholder="contoh@email.com"
                autocomplete="email"
                required
              />
            </div>
          </div>

          <div class="form-group">
            <div class="label-row">
              <label class="form-label">Password</label>
              <a href="#" class="forgot-link">Lupa password?</a>
            </div>
            <div class="input-wrap">
              <span class="material-symbols-outlined input-icon">lock</span>
              <input
                v-model="form.password"
                :type="showPass ? 'text' : 'password'"
                class="form-input"
                placeholder="Masukkan password"
                autocomplete="current-password"
                required
              />
              <button type="button" class="toggle-pass" @click="showPass = !showPass">
                <span class="material-symbols-outlined">{{ showPass ? 'visibility_off' : 'visibility' }}</span>
              </button>
            </div>
          </div>

          <div class="form-check">
            <label class="check-label">
              <input v-model="rememberMe" type="checkbox" class="check-input" />
              <span class="check-box"></span>
              Ingat saya
            </label>
          </div>

          <button type="submit" class="btn-submit" :disabled="loading">
            <span v-if="loading" class="material-symbols-outlined spin">progress_activity</span>
            <span v-else class="material-symbols-outlined">login</span>
            {{ loading ? 'Memproses...' : 'Log In' }}
          </button>
        </form>

        <!-- Divider -->
        <div class="divider"><span>atau</span></div>

        <!-- Register link -->
        <p class="switch-auth">
          Belum punya akun?
          <router-link to="/register" class="switch-link">Daftar sekarang</router-link>
        </p>
      </div>

      <!-- Footer -->
      <p class="auth-footer">© 2024 Apotek Online</p>
    </div>
  </div>
</template>

<script>
import api from '@/services/api.js'
import { useAuth } from '@/composables/useAuth.js' // 1. Import useAuth

export default {
  name: 'LoginPage',

  setup() {
    const { setAuth } = useAuth() // 2. Ambil fungsi setAuth
    return { setAuth }
  },

  data() {
    return {
      form: { email: '', password: '' },
      rememberMe: false,
      showPass: false,
      loading: false,
      errorMsg: null,
    }
  },

  methods: {
    async handleLogin() {
      this.loading = true
      this.errorMsg = null

      try {
        const res = await api.post('/pelanggan/login', {
          email: this.form.email,
          password: this.form.password,
        })

        // 3. Gunakan setAuth dari composable agar state global terupdate
        // res.data.user atau res.data.pelanggan (sesuaikan dengan response backendmu)
        const userData = res.data.user || res.data.pelanggan
        this.setAuth(res.data.token, userData)

        // 4. Cek jika ternyata yang login adalah admin/staff di halaman depan
        // Kita arahkan ke dashboard jika dia punya jabatan, kalau tidak ke home
        if (userData.jabatan && userData.jabatan !== 'pelanggan') {
           this.$router.push('/admin/dashboard')
        } else {
           this.$router.push('/')
        }

      } catch (err) {
        if (err.response?.status === 422) {
          const errors = err.response.data?.errors
          this.errorMsg = Object.values(errors || {}).flat()[0] || 'Email atau password salah.'
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
/* ── Layout ── */
.auth-layout {
  display: grid;
  grid-template-columns: 1fr 1fr;
  min-height: 100vh;
  font-family: 'Plus Jakarta Sans', sans-serif;
}

/* ── Left Panel ── */
.auth-left {
  background: linear-gradient(160deg, #021c0f 0%, #064e3b 50%, #065f46 100%);
  position: relative; overflow: hidden;
  display: flex; flex-direction: column;
}
.auth-left::before {
  content: '';
  position: absolute; inset: 0;
  background: radial-gradient(ellipse at top right, rgba(74,222,128,0.15), transparent 60%);
}

.auth-left-inner {
  position: relative; z-index: 1;
  padding: 2.5rem;
  display: flex; flex-direction: column;
  height: 100%; gap: 2rem;
}

.auth-brand {
  display: flex; align-items: center; gap: 0.5rem;
  text-decoration: none;
}
.brand-icon  { font-size: 1.5rem; }
.brand-text  { font-family: 'Manrope', sans-serif; font-size: 1.1rem; font-weight: 800; color: #fff; }
.brand-accent { color: #4ade80; }

.auth-left-content { flex: 1; display: flex; flex-direction: column; justify-content: center; gap: 1.5rem; }

/* Illustration */
.left-illustration {
  position: relative; width: 10rem; height: 10rem;
  margin-bottom: 1rem;
}
.pill {
  position: absolute; border-radius: 9999px;
  background: rgba(74,222,128,0.15);
  border: 1px solid rgba(74,222,128,0.25);
}
.pill--1 { width: 8rem; height: 3.5rem; top: 2rem; left: 0; transform: rotate(-15deg); }
.pill--2 { width: 6rem; height: 2.5rem; top: 0; left: 3rem; transform: rotate(20deg); background: rgba(74,222,128,0.08); }
.pill--3 { width: 4rem; height: 2rem; bottom: 1rem; left: 2rem; transform: rotate(-5deg); }
.left-icon {
  position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
  font-size: 3rem; color: #4ade80;
}

.left-heading {
  font-family: 'Manrope', sans-serif;
  font-size: 1.75rem; font-weight: 800; color: #fff;
  line-height: 1.2; letter-spacing: -0.02em;
}
.left-sub { font-size: 0.875rem; color: rgba(255,255,255,0.55); line-height: 1.7; }

/* Features */
.left-features { display: flex; flex-direction: column; gap: 0.75rem; }
.left-feature {
  display: flex; align-items: center; gap: 0.6rem;
  font-size: 0.8rem; font-weight: 600; color: rgba(255,255,255,0.6);
}
.feat-icon { font-size: 1rem; color: #4ade80; }

/* ── Right Panel ── */
.auth-right {
  background: #0f1f14;
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  padding: 3rem 2rem; position: relative;
}

.auth-form-wrap {
  width: 100%; max-width: 22rem;
  display: flex; flex-direction: column; gap: 1.5rem;
}

/* Form header */
.form-header { display: flex; flex-direction: column; gap: 0.35rem; }
.form-title {
  font-family: 'Manrope', sans-serif;
  font-size: 1.75rem; font-weight: 800; color: #fff; letter-spacing: -0.02em;
}
.form-sub { font-size: 0.82rem; color: rgba(255,255,255,0.45); }

/* Alert */
.alert {
  display: flex; align-items: center; gap: 0.5rem;
  padding: 0.75rem 1rem; border-radius: 0.625rem;
  font-size: 0.82rem; font-weight: 600;
}
.alert--error { background: rgba(220,38,38,0.15); color: #fca5a5; border: 1px solid rgba(220,38,38,0.3); }
.alert .material-symbols-outlined { font-size: 1rem; flex-shrink: 0; }

/* Form */
.auth-form { display: flex; flex-direction: column; gap: 1.1rem; }
.form-group { display: flex; flex-direction: column; gap: 0.4rem; }
.label-row  { display: flex; justify-content: space-between; align-items: center; }
.form-label { font-size: 0.78rem; font-weight: 700; color: rgba(255,255,255,0.7); }
.forgot-link { font-size: 0.72rem; color: #4ade80; text-decoration: none; font-weight: 600; }
.forgot-link:hover { text-decoration: underline; }

.input-wrap  { position: relative; display: flex; align-items: center; }
.input-icon  {
  position: absolute; left: 0.75rem;
  font-size: 1rem; color: rgba(255,255,255,0.3);
  pointer-events: none;
}
.form-input {
  width: 100%;
  padding: 0.65rem 2.5rem 0.65rem 2.5rem;
  background: rgba(255,255,255,0.06);
  border: 1.5px solid rgba(255,255,255,0.1);
  border-radius: 0.625rem;
  color: #fff; font-size: 0.875rem; outline: none;
  font-family: inherit; transition: border 0.15s, background 0.15s;
}
.form-input::placeholder { color: rgba(255,255,255,0.25); }
.form-input:focus {
  border-color: #4ade80;
  background: rgba(74,222,128,0.05);
}
.toggle-pass {
  position: absolute; right: 0.75rem;
  background: none; border: none; cursor: pointer;
  color: rgba(255,255,255,0.3); display: flex;
  transition: color 0.15s;
}
.toggle-pass:hover { color: rgba(255,255,255,0.7); }
.toggle-pass .material-symbols-outlined { font-size: 1rem; }

/* Checkbox */
.form-check { margin-top: -0.25rem; }
.check-label {
  display: flex; align-items: center; gap: 0.6rem;
  font-size: 0.8rem; color: rgba(255,255,255,0.5);
  cursor: pointer;
}
.check-input { display: none; }
.check-box {
  width: 1rem; height: 1rem; border-radius: 0.25rem;
  border: 1.5px solid rgba(255,255,255,0.2);
  background: rgba(255,255,255,0.05);
  flex-shrink: 0; transition: all 0.15s; position: relative;
}
.check-input:checked + .check-box {
  background: #4ade80; border-color: #4ade80;
}
.check-input:checked + .check-box::after {
  content: '✓'; position: absolute; top: -2px; left: 2px;
  font-size: 0.7rem; color: #052e16; font-weight: 800;
}

/* Submit */
.btn-submit {
  width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem;
  padding: 0.75rem; border: none; border-radius: 0.625rem;
  background: #4ade80; color: #052e16;
  font-size: 0.875rem; font-weight: 800; cursor: pointer;
  transition: all 0.2s; font-family: inherit;
  box-shadow: 0 4px 16px rgba(74,222,128,0.3);
  margin-top: 0.25rem;
}
.btn-submit:hover:not(:disabled) {
  background: #22c55e;
  box-shadow: 0 8px 24px rgba(74,222,128,0.4);
  transform: translateY(-1px);
}
.btn-submit:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-submit .material-symbols-outlined { font-size: 1.1rem; }
.spin { animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Divider */
.divider {
  display: flex; align-items: center; gap: 1rem;
  color: rgba(255,255,255,0.2); font-size: 0.75rem;
}
.divider::before, .divider::after {
  content: ''; flex: 1; height: 1px; background: rgba(255,255,255,0.1);
}

/* Switch */
.switch-auth { text-align: center; font-size: 0.82rem; color: rgba(255,255,255,0.4); }
.switch-link { color: #4ade80; font-weight: 700; text-decoration: none; }
.switch-link:hover { text-decoration: underline; }

/* Footer */
.auth-footer {
  position: absolute; bottom: 1.5rem;
  font-size: 0.7rem; color: rgba(255,255,255,0.2);
}

/* Transition */
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* Responsive */
@media (max-width: 768px) {
  .auth-layout { grid-template-columns: 1fr; }
  .auth-left   { display: none; }
}
</style>