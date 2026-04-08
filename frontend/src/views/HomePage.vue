<template>
  <div class="storefront">
    <StorefrontNavbar :cart-count="cartCount" />
    <HeroSection />
    <JenisObatSection @filter-jenis="onFilterJenis" />
    <KatalogObatSection :filter-jenis-id="filterJenisId" @cart-updated="onCartUpdated" />
    <NewsletterFooter />
  </div>
</template>

<script>
import StorefrontNavbar  from '@/components/storefront/StorefrontNavbar.vue'
import HeroSection       from '@/components/storefront/HeroSection.vue'
import JenisObatSection  from '@/components/storefront/JenisObatSection.vue'
import KatalogObatSection from '@/components/storefront/KatalogObatSection.vue'
import NewsletterFooter  from '@/components/storefront/NewsletterFooter.vue'
import api               from '@/services/api.js'

export default {
  name: 'HomePage',

  components: {
    StorefrontNavbar,
    HeroSection,
    JenisObatSection,
    KatalogObatSection,
    NewsletterFooter,
  },

  data() {
    return {
      filterJenisId: null,
      cartCount: 0,
    }
  },

  mounted() {
    this.fetchCartCount()
  },

  methods: {
    onFilterJenis(jenisId) {
      this.filterJenisId = this.filterJenisId === jenisId ? null : jenisId
      // Scroll ke katalog
      document.getElementById('katalog')?.scrollIntoView({ behavior: 'smooth' })
    },

    async onCartUpdated() {
      await this.fetchCartCount()
    },

    async fetchCartCount() {
      const token = localStorage.getItem('pelanggan_token')
      if (!token) return
      try {
        const res = await api.get('/pelanggan/keranjang')
        this.cartCount = res.data?.length ?? 0
      } catch {
        this.cartCount = 0
      }
    },
  },
}
</script>

<style scoped>
.storefront {
  font-family: 'Plus Jakarta Sans', sans-serif;
  color: #171c1f;
  overflow-x: hidden;
}
</style>