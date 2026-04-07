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
          <div class="left-orb">
            <span class="material-symbols-outlined orb-icon">health_and_safety</span>
          </div>
          <h2 class="left-heading">Bergabung dengan komunitas sehat kami.</h2>
          <p class="left-sub">Daftar sekarang dan nikmati kemudahan berbelanja produk kesehatan berkualitas.</p>

          <div class="left-steps">
            <div v-for="(step, i) in steps" :key="i" class="step-item">
              <div class="step-num">{{ i + 1 }}</div>
              <div>
                <p class="step-title">{{ step.title }}</p>
                <p class="step-sub">{{ step.sub }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Panel -->
    <div class="auth-right">
      <div class="auth-form-wrap">
        <div class="form-header">
          <h1 class="form-title">Create Account</h1>
          <p class="form-sub">Isi data diri kamu untuk mulai berbelanja.</p>
        </div>

        <!-- Alert -->
        <transition name="fade">
          <div v-if="errorMsg" class="alert alert--error">
            <span class="material-symbols-outlined">error_outline</span>
            {{ errorMsg }}
          </div>
          <div v-else-if="successMsg" class="alert alert--success">
            <span class="material-symbols-outlined">check_circle</span>
            {{ successMsg }}
          </div>
        </transition>

        <form class="auth-form" @submit.prevent="handleRegister">

          <!-- Nama -->
          <div class="form-group">
            <label class="form-label">Nama Lengkap <span class="req">*</span></label>
            <div class="input-wrap">
              <span class="material-symbols-outlined input-icon">person</span>
              <input v-model="form.nama_pelanggan" type="text" class="form-input" placeholder="Nama lengkap kamu" required />
            </div>
          </div>

          <!-- Email -->
          <div class="form-group">
            <label class="form-label">Email <span class="req">*</span></label>
            <div class="input-wrap">
              <span class="material-symbols-outlined input-icon">mail</span>
              <input v-model="form.email" type="email" class="form-input" placeholder="contoh@email.com" autocomplete="email" required />
            </div>
          </div>

          <!-- No Telp -->
          <div class="form-group">
            <label class="form-label">No. Telepon</label>
            <div class="input-wrap">
              <span class="material-symbols-outlined input-icon">phone</span>
              <input v-model="form.no_telp" type="tel" class="form-input" placeholder="08xxxxxxxxxx" />
            </div>
          </div>

          <!-- Password row -->
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Password <span class="req">*</span></label>
              <div class="input-wrap">
                <span class="material-symbols-outlined input-icon">lock</span>
                <input
                  v-model="form.password"
                  :type="showPass ? 'text' : 'password'"
                  class="form-input"
                  placeholder="Min. 6 karakter"
                  required
                />
                <button type="button" class="toggle-pass" @click="showPass = !showPass">
                  <span class="material-symbols-outlined">{{ showPass ? 'visibility_off' : 'visibility' }}</span>
                </button>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Konfirmasi Password <span class="req">*</span></label>
              <div class="input-wrap">
                <span class="material-symbols-outlined input-icon">lock_reset</span>
                <input
                  v-model="form.password_confirm"
                  :type="showPass ? 'text' : 'password'"
                  class="form-input"
                  :class="{ 'input--error': form.password_confirm && form.password !== form.password_confirm }"
                  placeholder="Ulangi password"
                  required
                />
              </div>
              <p v-if="form.password_confirm && form.password !== form.password_confirm" class="field-error">Password tidak cocok</p>
            </div>
          </div>

          <!-- Alamat -->
          <div class="form-group">
            <label class="form-label">Alamat</label>
            <div class="input-wrap">
              <span class="material-symbols-outlined input-icon">location_on</span>
              <input v-model="form.alamat1" type="text" class="form-input" placeholder="Jalan, RT/RW, Kelurahan..." />
            </div>
          </div>

          <!-- Kota & Provinsi -->
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Kota</label>
              <div class="input-wrap">
                <span class="material-symbols-outlined input-icon">apartment</span>
                <input v-model="form.kota1" type="text" class="form-input" placeholder="Kota" />
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Provinsi</label>
              <div class="input-wrap">
                <span class="material-symbols-outlined input-icon">map</span>
                <input v-model="form.propinsi1" type="text" class="form-input" placeholder="Provinsi" />
              </div>
            </div>
          </div>

          <!-- Kode Pos -->
          <div class="form-group">
            <label class="form-label">Kode Pos</label>
            <div class="input-wrap">
              <span class="material-symbols-outlined input-icon">markunread_mailbox</span>
              <input v-model="form.kodepos1" type="text" class="form-input" placeholder="12345" maxlength="5" />
            </div>
          </div>

          <!-- Terms -->
          <div class="form-check">
            <label class="check-label">
              <input v-model="agreeTerms" type="checkbox" class="check-input" required />
              <span class="check-box"></span>
              Saya setuju dengan <a href="#" class="terms-link">Syarat &amp; Ketentuan</a>
            </label>
          </div>

          <button type="submit" class="btn-submit" :disabled="loading || !agreeTerms || form.password !== form.password_confirm">
            <span v-if="loading" class="material-symbols-outlined spin">progress_activity</span>
            <span v-else class="material-symbols-outlined">person_add</span>
            {{ loading ? 'Mendaftarkan...' : 'Daftar Sekarang' }}
          </button>
        </form>

        <p class="switch-auth">
          Sudah punya akun?
          <router-link to="/login" class="switch-link">Masuk di sini</router-link>
        </p>
      </div>

      <p class="auth-footer">© 2024 Apotek Online</p>
    </div>
  </div>
</template>

<script>
import api from '@/services/api.js'

export default {
  name: 'RegisterPage',

  data() {
    return {
      form: {
        nama_pelanggan: '',
        email: '',
        no_telp: '',
        password: '',
        password_confirm: '',
        alamat1: '',
        kota1: '',
        propinsi1: '',
        kodepos1: '',
      },
      agreeTerms: false,
      showPass: false,
      loading: false,
      errorMsg: null,
      successMsg: null,

      steps: [
        { title: 'Isi Data Diri', sub: 'Nama, email, dan nomor telepon' },
        { title: 'Buat Password', sub: 'Minimal 6 karakter untuk keamanan akun' },
        { title: 'Mulai Belanja', sub: 'Akses katalog lengkap produk kesehatan' },
      ],
    }
  },

  methods: {
    async handleRegister() {
      if (this.form.password !== this.form.password_confirm) {
        this.errorMsg = 'Password dan konfirmasi password tidak cocok.'
        return
      }

      this.loading = true
      this.errorMsg = null
      this.successMsg = null

      try {
        const payload = { ...this.form }
        delete payload.password_confirm

        const res = await api.post('/pelanggan/register', payload)

        // Auto-login setelah register
        localStorage.setItem('pelanggan_token', res.data.token)
        localStorage.setItem('pelanggan_data', JSON.stringify(res.data.pelanggan))

        this.successMsg = 'Akun berhasil dibuat! Mengalihkan...'
        setTimeout(() => this.$router.push('/'), 1500)

      } catch (err) {
        if (err.response?.status === 422) {
          const errors = err.response.data?.errors || {}
          this.errorMsg = Object.values(errors).flat()[0] || 'Data tidak valid.'
        } else {
          this.errorMsg = err.response?.data?.message || 'Gagal mendaftar. Coba lagi.'
        }
      } finally {
        this.loading = false
      }
    },
  },
}
</script>

<style scoped>
.auth-layout {
  display: grid;
  grid-template-columns: 1fr 1.4fr;
  min-height: 100vh;
  font-family: 'Plus Jakarta Sans', sans-serif;
}

/* ── Left ── */
.auth-left {
  background: linear-gradient(160deg, #021c0f 0%, #064e3b 55%, #065f46 100%);
  position: relative; overflow: hidden;
}
.auth-left::before {
  content: '';
  position: absolute; inset: 0;
  background: radial-gradient(ellipse at bottom left, rgba(74,222,128,0.12), transparent 60%);
}
.auth-left-inner {
  position: relative; z-index: 1;
  padding: 2.5rem;
  display: flex; flex-direction: column; height: 100%; gap: 2.5rem;
}

.auth-brand     { display: flex; align-items: center; gap: 0.5rem; text-decoration: none; }
.brand-icon     { font-size: 1.5rem; }
.brand-text     { font-family: 'Manrope', sans-serif; font-size: 1.1rem; font-weight: 800; color: #fff; }
.brand-accent   { color: #4ade80; }

.auth-left-content { flex: 1; display: flex; flex-direction: column; justify-content: center; gap: 1.5rem; }

/* Orb */
.left-orb {
  width: 6rem; height: 6rem; border-radius: 50%;
  background: rgba(74,222,128,0.12);
  border: 1px solid rgba(74,222,128,0.25);
  display: flex; align-items: center; justify-content: center;
}
.orb-icon { font-size: 2.5rem; color: #4ade80; }

.left-heading {
  font-family: 'Manrope', sans-serif;
  font-size: 1.6rem; font-weight: 800; color: #fff;
  line-height: 1.2; letter-spacing: -0.02em;
}
.left-sub { font-size: 0.875rem; color: rgba(255,255,255,0.5); line-height: 1.7; }

/* Steps */
.left-steps { display: flex; flex-direction: column; gap: 1rem; margin-top: 0.5rem; }
.step-item  { display: flex; align-items: flex-start; gap: 0.875rem; }
.step-num {
  width: 1.75rem; height: 1.75rem; border-radius: 50%;
  background: rgba(74,222,128,0.15); border: 1px solid rgba(74,222,128,0.3);
  color: #4ade80; font-size: 0.75rem; font-weight: 800;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.step-title { font-size: 0.82rem; font-weight: 700; color: rgba(255,255,255,0.75); }
.step-sub   { font-size: 0.72rem; color: rgba(255,255,255,0.4); margin-top: 1px; }

/* ── Right ── */
.auth-right {
  background: #0f1f14;
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  padding: 3rem 2rem; position: relative; overflow-y: auto;
}
.auth-form-wrap {
  width: 100%; max-width: 28rem;
  display: flex; flex-direction: column; gap: 1.25rem;
}

/* Header */
.form-header { display: flex; flex-direction: column; gap: 0.3rem; }
.form-title {
  font-family: 'Manrope', sans-serif;
  font-size: 1.75rem; font-weight: 800; color: #fff; letter-spacing: -0.02em;
}
.form-sub { font-size: 0.82rem; color: rgba(255,255,255,0.4); }

/* Alert */
.alert {
  display: flex; align-items: flex-start; gap: 0.5rem;
  padding: 0.75rem 1rem; border-radius: 0.625rem;
  font-size: 0.82rem; font-weight: 600; line-height: 1.5;
}
.alert--error   { background: rgba(220,38,38,0.12); color: #fca5a5; border: 1px solid rgba(220,38,38,0.25); }
.alert--success { background: rgba(74,222,128,0.12); color: #86efac; border: 1px solid rgba(74,222,128,0.25); }
.alert .material-symbols-outlined { font-size: 1rem; flex-shrink: 0; margin-top: 1px; }

/* Form */
.auth-form  { display: flex; flex-direction: column; gap: 0.9rem; }
.form-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.form-group { display: flex; flex-direction: column; gap: 0.35rem; }
.form-label { font-size: 0.75rem; font-weight: 700; color: rgba(255,255,255,0.6); }
.req        { color: #f87171; }

.input-wrap { position: relative; display: flex; align-items: center; }
.input-icon {
  position: absolute; left: 0.7rem;
  font-size: 0.95rem; color: rgba(255,255,255,0.25);
  pointer-events: none;
}
.form-input {
  width: 100%;
  padding: 0.6rem 2.25rem 0.6rem 2.25rem;
  background: rgba(255,255,255,0.05);
  border: 1.5px solid rgba(255,255,255,0.08);
  border-radius: 0.5rem; color: #fff;
  font-size: 0.82rem; outline: none; font-family: inherit;
  transition: border 0.15s, background 0.15s;
}
.form-input::placeholder { color: rgba(255,255,255,0.2); }
.form-input:focus { border-color: #4ade80; background: rgba(74,222,128,0.04); }
.form-input.input--error { border-color: #f87171; }

.field-error { font-size: 0.68rem; color: #f87171; font-weight: 600; }

.toggle-pass {
  position: absolute; right: 0.7rem;
  background: none; border: none; cursor: pointer;
  color: rgba(255,255,255,0.25); display: flex; transition: color 0.15s;
}
.toggle-pass:hover { color: rgba(255,255,255,0.6); }
.toggle-pass .material-symbols-outlined { font-size: 0.95rem; }

/* Check */
.form-check { margin-top: -0.15rem; }
.check-label {
  display: flex; align-items: center; gap: 0.6rem;
  font-size: 0.78rem; color: rgba(255,255,255,0.45); cursor: pointer;
}
.check-input { display: none; }
.check-box {
  width: 1rem; height: 1rem; border-radius: 0.25rem;
  border: 1.5px solid rgba(255,255,255,0.15);
  background: rgba(255,255,255,0.04); flex-shrink: 0;
  transition: all 0.15s; position: relative;
}
.check-input:checked + .check-box { background: #4ade80; border-color: #4ade80; }
.check-input:checked + .check-box::after {
  content: '✓'; position: absolute; top: -2px; left: 2px;
  font-size: 0.7rem; color: #052e16; font-weight: 800;
}
.terms-link { color: #4ade80; text-decoration: none; font-weight: 700; }
.terms-link:hover { text-decoration: underline; }

/* Submit */
.btn-submit {
  width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem;
  padding: 0.7rem; border: none; border-radius: 0.625rem;
  background: #4ade80; color: #052e16;
  font-size: 0.875rem; font-weight: 800; cursor: pointer;
  transition: all 0.2s; font-family: inherit;
  box-shadow: 0 4px 16px rgba(74,222,128,0.25);
  margin-top: 0.25rem;
}
.btn-submit:hover:not(:disabled) {
  background: #22c55e; transform: translateY(-1px);
  box-shadow: 0 8px 24px rgba(74,222,128,0.35);
}
.btn-submit:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-submit .material-symbols-outlined { font-size: 1.1rem; }
.spin { animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Switch */
.switch-auth { text-align: center; font-size: 0.8rem; color: rgba(255,255,255,0.35); }
.switch-link { color: #4ade80; font-weight: 700; text-decoration: none; }
.switch-link:hover { text-decoration: underline; }

.auth-footer {
  position: absolute; bottom: 1.25rem;
  font-size: 0.68rem; color: rgba(255,255,255,0.15);
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.25s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

@media (max-width: 768px) {
  .auth-layout { grid-template-columns: 1fr; }
  .auth-left   { display: none; }
  .form-row    { grid-template-columns: 1fr; }
}
</style>