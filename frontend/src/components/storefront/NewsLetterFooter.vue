<template>
  <div>
    <!-- Expert Advice / Newsletter -->
    <section class="newsletter-section">
      <div class="container">
        <div class="newsletter-grid">
          <!-- Left: copy -->
          <div class="newsletter-copy">
            <h2 class="newsletter-title">Expert Advice,<br />Directly to Your Inbox.</h2>
            <p class="newsletter-sub">
              Dapatkan tips kesehatan mingguan, promo eksklusif, dan info obat terbaru
              dari apoteker bersertifikat kami.
            </p>
            <div class="newsletter-form">
              <input v-model="email" type="email" placeholder="Masukkan email kamu..." class="email-input" />
              <button class="btn-subscribe" @click="subscribe">Subscribe</button>
            </div>
            <p v-if="subMsg" class="sub-msg" :class="subMsg.type">{{ subMsg.text }}</p>
          </div>

          <!-- Right: features -->
          <div class="newsletter-features">
            <div v-for="feat in features" :key="feat.title" class="feat-card">
              <div class="feat-icon">
                <span class="material-symbols-outlined">{{ feat.icon }}</span>
              </div>
              <div>
                <p class="feat-title">{{ feat.title }}</p>
                <p class="feat-sub">{{ feat.sub }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
      <div class="container">
        <div class="footer-grid">
          <div class="footer-brand">
            <p class="footer-brand-name">ApotekOnline</p>
            <p class="footer-brand-desc">Apotek digital terpercaya dengan standar farmasi internasional.</p>
            <div class="footer-social">
              <a href="#" class="social-btn"><span class="material-symbols-outlined">language</span></a>
              <a href="#" class="social-btn"><span class="material-symbols-outlined">mail</span></a>
              <a href="#" class="social-btn"><span class="material-symbols-outlined">phone</span></a>
            </div>
          </div>

          <div v-for="col in footerCols" :key="col.title" class="footer-col">
            <p class="footer-col-title">{{ col.title }}</p>
            <ul class="footer-links">
              <li v-for="link in col.links" :key="link"><a href="#" class="footer-link">{{ link }}</a></li>
            </ul>
          </div>
        </div>

        <div class="footer-bottom">
          <p>© 2024 Apotek Online. All rights reserved.</p>
          <p>Terdaftar di BPOM RI — Izin Apotek No. SIPAP-2024-001</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script>
export default {
  name: 'NewsletterFooter',
  data() {
    return {
      email: '',
      subMsg: null,
      features: [
        { icon: 'local_pharmacy', title: 'Konsultasi Gratis',  sub: 'Chat langsung dengan apoteker kami' },
        { icon: 'bolt',           title: 'Delivery Express',   sub: 'Tiba dalam 2 jam untuk area Jakarta' },
        { icon: 'verified',       title: 'Produk Terjamin',    sub: 'Semua produk bersertifikat BPOM' },
      ],
      footerCols: [
        { title: 'Produk', links: ['Katalog Obat', 'Jenis Obat', 'Obat Bebas', 'Resep Dokter', 'Alat Kesehatan'] },
        { title: 'Layanan', links: ['Konsultasi Online', 'Pengiriman', 'Retur Produk', 'Metode Bayar', 'Lacak Pesanan'] },
        { title: 'Kontak', links: ['Tentang Kami', 'Blog Kesehatan', 'FAQ', 'Karir', 'Hubungi Kami'] },
      ],
    }
  },
  methods: {
    subscribe() {
      if (!this.email || !this.email.includes('@')) {
        this.subMsg = { text: 'Masukkan email yang valid.', type: 'msg--error' }
        return
      }
      this.subMsg = { text: '✓ Terima kasih! Kamu sudah berlangganan.', type: 'msg--success' }
      this.email = ''
      setTimeout(() => { this.subMsg = null }, 4000)
    },
  },
}
</script>

<style scoped>
/* Newsletter */
.newsletter-section {
  background: #fff; padding: 5rem 0;
  border-top: 1px solid #f0f4f8;
}
.container { max-width: 1200px; margin: 0 auto; padding: 0 2rem; }

.newsletter-grid {
  display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center;
}
.newsletter-title {
  font-family: 'Manrope', sans-serif;
  font-size: 2rem; font-weight: 900; color: #064e3b; letter-spacing: -0.03em;
  line-height: 1.15; margin-bottom: 0.75rem;
}
.newsletter-sub { font-size: 0.9rem; color: #6b7280; line-height: 1.7; margin-bottom: 1.5rem; }

.newsletter-form { display: flex; gap: 0.75rem; }
.email-input {
  flex: 1; padding: 0.65rem 1rem;
  border: 1.5px solid #e5e7eb; border-radius: 0.625rem;
  font-size: 0.875rem; outline: none; font-family: inherit; color: #171c1f;
  transition: border 0.15s;
}
.email-input:focus { border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,0.1); }
.btn-subscribe {
  padding: 0.65rem 1.5rem; border: none; border-radius: 0.625rem;
  background: #059669; color: #fff; font-weight: 700; cursor: pointer;
  font-size: 0.875rem; transition: all 0.15s; font-family: inherit;
  white-space: nowrap;
}
.btn-subscribe:hover { background: #047857; }

.sub-msg { font-size: 0.8rem; margin-top: 0.5rem; font-weight: 600; }
.msg--success { color: #059669; }
.msg--error   { color: #dc2626; }

/* Features */
.newsletter-features { display: flex; flex-direction: column; gap: 1.25rem; }
.feat-card {
  display: flex; align-items: flex-start; gap: 1rem;
  padding: 1rem; border-radius: 0.875rem;
  background: #f8fafb; border: 1px solid #f0f4f8;
  transition: all 0.15s;
}
.feat-card:hover { border-color: #a7f3d0; background: #f0fdf4; }
.feat-icon {
  width: 2.5rem; height: 2.5rem; border-radius: 0.625rem;
  background: #ecfdf5; display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.feat-icon .material-symbols-outlined { color: #059669; font-size: 1.2rem; }
.feat-title { font-size: 0.875rem; font-weight: 700; color: #171c1f; }
.feat-sub   { font-size: 0.75rem; color: #6b7280; margin-top: 2px; }

/* Footer */
.footer { background: #021c0f; padding: 4rem 0 2rem; }
.footer-grid {
  display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 3rem;
  margin-bottom: 3rem;
}
.footer-brand-name {
  font-family: 'Manrope', sans-serif; font-size: 1rem; font-weight: 800;
  color: #fff; margin-bottom: 0.75rem;
}
.footer-brand-desc { font-size: 0.8rem; color: rgba(255,255,255,0.45); line-height: 1.6; margin-bottom: 1rem; }
.footer-social { display: flex; gap: 0.5rem; }
.social-btn {
  width: 2rem; height: 2rem; border-radius: 50%;
  background: rgba(255,255,255,0.08);
  display: flex; align-items: center; justify-content: center;
  color: rgba(255,255,255,0.5); text-decoration: none; transition: all 0.15s;
}
.social-btn:hover { background: #059669; color: #fff; }
.social-btn .material-symbols-outlined { font-size: 0.95rem; }

.footer-col-title { font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.4); margin-bottom: 1rem; }
.footer-links { list-style: none; display: flex; flex-direction: column; gap: 0.5rem; }
.footer-link { font-size: 0.82rem; color: rgba(255,255,255,0.55); text-decoration: none; transition: color 0.15s; }
.footer-link:hover { color: #4ade80; }

.footer-bottom {
  display: flex; justify-content: space-between;
  padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.06);
  font-size: 0.72rem; color: rgba(255,255,255,0.3);
}
</style>