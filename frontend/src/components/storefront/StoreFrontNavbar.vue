<template>
  <header class="navbar" :class="{ 'navbar--scrolled': scrolled }">
    <div class="navbar-inner">
      <!-- Logo -->
      <router-link to="/" class="navbar-brand">
        <span class="brand-icon">⚕</span>
        <span class="brand-text">Apotek<span class="brand-dot">Online</span></span>
      </router-link>

      <!-- Nav Links -->
      <nav class="navbar-nav" :class="{ 'hidden-nav': isProfilePage }">
        <a href="#jenis-obat" class="nav-link">Jenis Obat</a>
        <a href="#katalog" class="nav-link">Katalog</a>
        <a href="#tentang" class="nav-link">Tentang</a>
        <a href="#kontak" class="nav-link">Kontak</a>
      </nav>

      <!-- Actions -->
      <div class="navbar-actions">
        <button v-if="!isProfilePage" class="icon-btn" title="Cari">
          <span class="material-symbols-outlined">search</span>
        </button>
        <router-link v-if="!isProfilePage" to="/cart" class="icon-btn cart-btn" title="Keranjang">
          <span class="material-symbols-outlined">shopping_cart</span>
          <span v-if="cartCount > 0" class="cart-badge">{{ cartCount }}</span>
        </router-link>
        <router-link v-if="!isLoggedIn" to="/login" class="btn btn--outline-white">Masuk</router-link>
        <router-link v-if="!isLoggedIn" to="/register" class="btn btn--white">Daftar</router-link>
        <div v-if="isLoggedIn" class="user-menu profile" @click.self="goToProfile" style="cursor:pointer">
          <span class="material-symbols-outlined" @click="goToProfile">account_circle</span>
        </div>
        <div v-if="isLoggedIn" @click.self="goToProfile">
          <button class="btn btn--outline-white" @click.stop="logout">Keluar</button>
        </div>
      </div>
    </div>
  </header>
</template>

<script>
export default {
  name: 'StorefrontNavbar',

  props: {
    cartCount: { type: Number, default: 0 },
  },

  data() {
    return {
      scrolled: false,
      // Buat reaktif — bukan computed yang baca localStorage langsung
      loggedIn: !!localStorage.getItem('pelanggan_token') || !!localStorage.getItem('admin_token'),
    }
  },

  computed: {
    isLoggedIn() {
      return this.loggedIn
    },
    isAdmin() {
      return !!localStorage.getItem('admin_token')
    },
    isProfilePage() {
      return this.$route.path.startsWith('/profile')
    }
  },

  mounted() {
    window.addEventListener('scroll', this.handleScroll)
    // Supaya reaktif kalau token berubah di tab lain
    window.addEventListener('storage', this.syncAuth)
  },
  beforeUnmount() {
    window.removeEventListener('scroll', this.handleScroll)
    window.removeEventListener('storage', this.syncAuth)
  },

  methods: {
    handleScroll() {
      this.scrolled = window.scrollY > 40
    },
    syncAuth() {
      this.loggedIn = !!localStorage.getItem('pelanggan_token') || !!localStorage.getItem('admin_token')
    },
    goToProfile() {
      if (this.isAdmin) {
        this.$router.push('/admin/dashboard')
      } else {
        this.$router.push('/profile')
      }
    },
    logout() {
      localStorage.removeItem('pelanggan_token')
      this.loggedIn = false
      this.$router.push('/')
    },
  },
}
</script>

<style scoped>
.navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 100;
  background: transparent;
  transition: background 0.3s, box-shadow 0.3s;
  padding: 0 2rem;
}

.navbar--scrolled {
  background: rgba(2, 28, 15, 0.97);
  backdrop-filter: blur(16px);
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.25);
}

.hidden-nav {
  visibility: hidden;
  /* tetap ambil space tapi tidak terlihat */
}

.navbar-inner {
  max-width: 1200px;
  margin: 0 auto;
  height: 4.5rem;
  display: flex;
  align-items: center;
  gap: 2rem;
}

/* Brand */
.navbar-brand {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  text-decoration: none;
  flex-shrink: 0;
}

.brand-icon {
  font-size: 1.5rem;
}

.brand-text {
  font-family: 'Manrope', sans-serif;
  font-size: 0.95rem;
  font-weight: 800;
  color: #fff;
  letter-spacing: -0.01em;
}

.brand-dot {
  color: #4ade80;
}

/* Nav */
.navbar-nav {
  display: flex;
  gap: 0.25rem;
  flex: 1;
  justify-content: center;
}

.nav-link {
  padding: 0.4rem 0.85rem;
  font-size: 0.85rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.75);
  text-decoration: none;
  border-radius: 0.5rem;
  transition: color 0.15s, background 0.15s;
}

.nav-link:hover {
  color: #fff;
  background: rgba(255, 255, 255, 0.08);
}

/* Actions */
.navbar-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-shrink: 0;
}

.icon-btn {
  background: rgba(255, 255, 255, 0.1);
  border: none;
  cursor: pointer;
  width: 2.25rem;
  height: 2.25rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  text-decoration: none;
  position: relative;
  transition: background 0.15s;
}

.icon-btn:hover {
  background: rgba(255, 255, 255, 0.2);
}

.icon-btn .material-symbols-outlined {
  font-size: 1.15rem;
}

.cart-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  background: #4ade80;
  color: #052e16;
  font-size: 0.6rem;
  font-weight: 800;
  width: 1rem;
  height: 1rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.profile {
  color: white;
  text-decoration: none;
}

.btn {
  padding: 0.45rem 1rem;
  border-radius: 0.5rem;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  font-family: inherit;
}

.btn--white {
  background: #fff;
  color: #064e3b;
  border: none;
}

.btn--white:hover {
  background: #d1fae5;
}

.btn--outline-white {
  background: transparent;
  color: #fff;
  border: 1px solid rgba(255, 255, 255, 0.35);
}

.btn--outline-white:hover {
  background: rgba(255, 255, 255, 0.1);
}

.user-menu {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #fff;
}
</style>