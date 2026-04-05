<template>
  <section id="jenis-obat" class="section">
    <div class="container">
      <div class="section-header">
        <div>
          <p class="section-eyebrow">Kategori</p>
          <h2 class="section-title">Jenis Obat</h2>
        </div>
        <div class="carousel-controls">
          <button class="ctrl-btn" @click="scrollLeft">
            <span class="material-symbols-outlined">chevron_left</span>
          </button>
          <button class="ctrl-btn" @click="scrollRight">
            <span class="material-symbols-outlined">chevron_right</span>
          </button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="jenis-grid">
        <div v-for="i in 6" :key="i" class="jenis-skeleton"></div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="error-state">
        <span class="material-symbols-outlined">error_outline</span>
        <p>{{ error }}</p>
        <button class="btn-retry" @click="fetchJenisObat">Coba Lagi</button>
      </div>

      <!-- Data -->
      <div v-else ref="carousel" class="jenis-carousel">
        <div
          v-for="jenis in jenisObat"
          :key="jenis.id"
          class="jenis-card"
          @click="$emit('filter-jenis', jenis.id)"
        >
          <div class="jenis-icon-wrap">
            <img
              v-if="jenis.image_url"
              :src="imageUrl(jenis.image_url)"
              :alt="jenis.jenis"
              class="jenis-img"
            />
            <span v-else class="material-symbols-outlined jenis-fallback">medication</span>
          </div>
          <p class="jenis-name">{{ jenis.jenis }}</p>
          <p v-if="jenis.deskripsi_jenis" class="jenis-desc">{{ truncate(jenis.deskripsi_jenis, 40) }}</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import api from '@/services/api.js'

export default {
  name: 'JenisObatSection',

  emits: ['filter-jenis'],

  data() {
    return {
      jenisObat: [],
      loading: true,
      error: null,
    }
  },

  mounted() {
    this.fetchJenisObat()
  },

  methods: {
    async fetchJenisObat() {
      this.loading = true
      this.error = null
      try {
        const res = await api.get('/jenis-obat')
        this.jenisObat = res.data
      } catch (err) {
        this.error = 'Gagal memuat jenis obat. Pastikan server berjalan.'
        console.error(err)
      } finally {
        this.loading = false
      }
    },

    imageUrl(filename) {
      return `${import.meta.env.VITE_API_URL?.replace('/api', '') || 'http://localhost:8000'}/storage/jenis_obat/${filename}`
    },

    truncate(str, len) {
      return str?.length > len ? str.slice(0, len) + '…' : str
    },

    scrollLeft() {
      this.$refs.carousel?.scrollBy({ left: -280, behavior: 'smooth' })
    },
    scrollRight() {
      this.$refs.carousel?.scrollBy({ left: 280, behavior: 'smooth' })
    },
  },
}
</script>

<style scoped>
.section { padding: 5rem 0; background: #fff; }
.container { max-width: 1200px; margin: 0 auto; padding: 0 2rem; }

/* Header */
.section-header {
  display: flex; justify-content: space-between; align-items: flex-end;
  margin-bottom: 2rem;
}
.section-eyebrow { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: #059669; }
.section-title { font-family: 'Manrope', sans-serif; font-size: 1.75rem; font-weight: 800; color: #064e3b; letter-spacing: -0.02em; }
.carousel-controls { display: flex; gap: 0.5rem; }
.ctrl-btn {
  width: 2.25rem; height: 2.25rem; border-radius: 50%;
  border: 1.5px solid #e5e7eb; background: #fff;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; color: #374151; transition: all 0.15s;
}
.ctrl-btn:hover { border-color: #059669; color: #059669; background: #ecfdf5; }

/* Carousel */
.jenis-carousel {
  display: flex; gap: 1rem; overflow-x: auto;
  padding-bottom: 0.75rem;
  scrollbar-width: none;
}
.jenis-carousel::-webkit-scrollbar { display: none; }

/* Card */
.jenis-card {
  flex-shrink: 0; width: 9rem;
  display: flex; flex-direction: column; align-items: center; gap: 0.6rem;
  padding: 1.25rem 0.75rem;
  border: 1.5px solid #f0f4f8; border-radius: 1rem;
  cursor: pointer; transition: all 0.2s; background: #fff;
  text-align: center;
}
.jenis-card:hover {
  border-color: #4ade80; background: #f0fdf4;
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(5,150,105,0.12);
}
.jenis-icon-wrap {
  width: 3.5rem; height: 3.5rem; border-radius: 0.875rem;
  background: #ecfdf5; display: flex; align-items: center; justify-content: center;
  overflow: hidden;
}
.jenis-img { width: 100%; height: 100%; object-fit: cover; }
.jenis-fallback { color: #059669; font-size: 1.5rem; }
.jenis-name { font-size: 0.8rem; font-weight: 700; color: #064e3b; }
.jenis-desc { font-size: 0.68rem; color: #9ca3af; line-height: 1.4; }

/* Skeleton */
.jenis-grid { display: flex; gap: 1rem; }
.jenis-skeleton {
  flex-shrink: 0; width: 9rem; height: 9rem;
  border-radius: 1rem; background: linear-gradient(90deg, #f0f4f8 25%, #e5e7eb 50%, #f0f4f8 75%);
  background-size: 200% 100%;
  animation: shimmer 1.4s infinite;
}
@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* Error */
.error-state {
  display: flex; flex-direction: column; align-items: center; gap: 0.75rem;
  padding: 3rem; color: #9ca3af;
}
.error-state .material-symbols-outlined { font-size: 2.5rem; color: #fca5a5; }
.btn-retry {
  padding: 0.5rem 1.25rem; border: none; border-radius: 0.5rem;
  background: #059669; color: #fff; font-weight: 700; cursor: pointer;
  font-family: inherit;
}
</style>