<template>
  <!-- Jika admin → tampilkan konten -->
  <slot v-if="isAdmin" />

  <!-- Jika bukan admin → tampilkan pesan akses ditolak -->
  <div v-else class="access-denied">
    <div class="denied-box">
      <span class="denied-icon">⛔</span>
      <h2>Akses Ditolak</h2>
      <p>Halaman ini hanya bisa diakses oleh Administrator.</p>
      <button @click="$router.push('/')">Kembali ke Beranda</button>
    </div>
  </div>
</template>

<script>
import { useAuth } from '@/composables/useAuth.js'

export default {
  name: 'AdminGuard',
  setup() {
    const { isAdmin } = useAuth()
    return { isAdmin }
  }
}
</script>

<style scoped>
.access-denied {
  min-height: 100vh;
  background: #0a0e14;
  display: flex;
  align-items: center;
  justify-content: center;
}
.denied-box {
  text-align: center;
  padding: 48px;
  background: #0f1620;
  border: 1px solid rgba(252,129,129,0.15);
  border-radius: 20px;
}
.denied-icon { font-size: 48px; display: block; margin-bottom: 16px; }
h2 { color: #fc8181; font-size: 22px; margin: 0 0 8px; }
p { color: #718096; font-size: 14px; margin: 0 0 24px; }
button {
  background: rgba(99,209,158,0.1);
  border: 1px solid rgba(99,209,158,0.2);
  color: #63d19e;
  padding: 10px 24px;
  border-radius: 9px;
  cursor: pointer;
  font-size: 14px;
}
</style>