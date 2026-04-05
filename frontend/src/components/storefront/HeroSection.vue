<template>
  <section class="hero">
    <!-- Background mesh -->
    <div class="hero-bg">
      <div class="hero-blob hero-blob--1"></div>
      <div class="hero-blob hero-blob--2"></div>
      <div class="hero-noise"></div>
    </div>

    <div class="hero-inner">
      <!-- Left: copy -->
      <div class="hero-copy">
        <span class="hero-eyebrow">
          <span class="eyebrow-dot"></span> Apotek Digital Terpercaya
        </span>
        <h1 class="hero-heading">
          Wellness,<br />
          <em class="heading-em">Redefined</em><br />
          by Science.
        </h1>
        <p class="hero-sub">
          Temukan ratusan obat dan produk kesehatan dengan kualitas farmasi terjamin,
          dikirim langsung ke pintu rumah Anda.
        </p>
        <div class="hero-cta">
          <a href="#katalog" class="btn btn--green">
            <span class="material-symbols-outlined">storefront</span>
            Belanja Sekarang
          </a>
          <a href="#jenis-obat" class="btn btn--ghost">
            Lihat Katalog
            <span class="material-symbols-outlined">arrow_forward</span>
          </a>
        </div>

        <!-- Trust badges -->
        <div class="trust-row">
          <div v-for="trust in trustItems" :key="trust.label" class="trust-item">
            <span class="material-symbols-outlined trust-icon">{{ trust.icon }}</span>
            <span>{{ trust.label }}</span>
          </div>
        </div>
      </div>

      <!-- Right: logo card -->
      <div class="hero-visual">
        <div class="hero-card">
          <div class="hero-card-inner">
            <p class="hero-card-label">COLNEAL</p>
            <p class="hero-card-sub">WELLNESS</p>
            <div class="hero-card-deco"></div>
          </div>
        </div>
        <!-- Floating badges -->
        <div class="float-badge float-badge--tl">
          <span class="material-symbols-outlined">verified</span> BPOM Approved
        </div>
        <div class="float-badge float-badge--br">
          <span class="material-symbols-outlined">local_shipping</span> Free Shipping
        </div>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  name: 'HeroSection',
  data() {
    return {
      trustItems: [
        { icon: 'shield',         label: 'BPOM Bersertifikat' },
        { icon: 'local_pharmacy', label: 'Apoteker Berlisensi' },
        { icon: 'bolt',           label: 'Pengiriman Cepat' },
      ],
    }
  },
}
</script>

<style scoped>
.hero {
  min-height: 100vh;
  background: linear-gradient(135deg, #021c0f 0%, #064e3b 45%, #065f46 100%);
  position: relative; overflow: hidden;
  display: flex; align-items: center;
  padding-top: 4.5rem;
}

/* Background effects */
.hero-bg { position: absolute; inset: 0; pointer-events: none; }
.hero-blob {
  position: absolute; border-radius: 50%;
  filter: blur(80px); opacity: 0.35;
}
.hero-blob--1 {
  width: 40rem; height: 40rem;
  background: radial-gradient(circle, #34d399, transparent);
  top: -10rem; right: -10rem;
}
.hero-blob--2 {
  width: 30rem; height: 30rem;
  background: radial-gradient(circle, #059669, transparent);
  bottom: -8rem; left: -8rem;
}
.hero-noise {
  position: absolute; inset: 0;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
  opacity: 0.4;
}

/* Layout */
.hero-inner {
  max-width: 1200px; margin: 0 auto; padding: 4rem 2rem;
  display: grid; grid-template-columns: 1fr 1fr;
  align-items: center; gap: 4rem; width: 100%;
  position: relative; z-index: 1;
}

/* Copy */
.hero-copy { display: flex; flex-direction: column; gap: 1.5rem; }
.hero-eyebrow {
  display: inline-flex; align-items: center; gap: 0.5rem;
  font-size: 0.78rem; font-weight: 700; letter-spacing: 0.12em;
  text-transform: uppercase; color: #4ade80;
}
.eyebrow-dot {
  width: 6px; height: 6px; border-radius: 50%;
  background: #4ade80; display: inline-block;
  animation: pulse 2s ease-in-out infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.5; transform: scale(1.5); }
}

.hero-heading {
  font-family: 'Manrope', sans-serif;
  font-size: clamp(2.5rem, 5vw, 4rem);
  font-weight: 900; line-height: 1.05;
  color: #fff; letter-spacing: -0.03em;
}
.heading-em { font-style: italic; color: #4ade80; }

.hero-sub {
  font-size: 1rem; color: rgba(255,255,255,0.65);
  line-height: 1.7; max-width: 30rem;
}

/* CTA */
.hero-cta { display: flex; gap: 1rem; flex-wrap: wrap; }
.btn {
  display: inline-flex; align-items: center; gap: 0.45rem;
  padding: 0.75rem 1.5rem; border-radius: 0.625rem;
  font-size: 0.875rem; font-weight: 700; cursor: pointer;
  transition: all 0.2s; text-decoration: none; border: none;
  font-family: inherit;
}
.btn--green {
  background: #4ade80; color: #052e16;
  box-shadow: 0 4px 20px rgba(74,222,128,0.35);
}
.btn--green:hover { background: #22c55e; transform: translateY(-2px); box-shadow: 0 8px 28px rgba(74,222,128,0.4); }
.btn--ghost {
  background: rgba(255,255,255,0.08); color: #fff;
  border: 1px solid rgba(255,255,255,0.2);
}
.btn--ghost:hover { background: rgba(255,255,255,0.15); }
.btn .material-symbols-outlined { font-size: 1.1rem; }

/* Trust */
.trust-row { display: flex; gap: 1.5rem; flex-wrap: wrap; margin-top: 0.5rem; }
.trust-item {
  display: flex; align-items: center; gap: 0.4rem;
  font-size: 0.78rem; font-weight: 600; color: rgba(255,255,255,0.55);
}
.trust-icon { font-size: 0.95rem; color: #4ade80; }

/* Visual */
.hero-visual { position: relative; display: flex; align-items: center; justify-content: center; }
.hero-card {
  width: 20rem; height: 20rem; border-radius: 2rem;
  background: linear-gradient(135deg, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0.02) 100%);
  border: 1px solid rgba(255,255,255,0.12);
  backdrop-filter: blur(20px);
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 24px 64px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.1);
  animation: float 6s ease-in-out infinite;
}
@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-12px); }
}
.hero-card-inner { text-align: center; }
.hero-card-label {
  font-family: 'Manrope', sans-serif;
  font-size: 2rem; font-weight: 900;
  color: #fff; letter-spacing: 0.2em;
}
.hero-card-sub {
  font-size: 0.9rem; font-weight: 600;
  color: #4ade80; letter-spacing: 0.35em;
  margin-top: 0.25rem;
}
.hero-card-deco {
  width: 3rem; height: 3px; background: #4ade80;
  margin: 1rem auto 0; border-radius: 2px;
}

/* Floating badges */
.float-badge {
  position: absolute;
  background: rgba(255,255,255,0.95);
  color: #064e3b; font-size: 0.75rem; font-weight: 700;
  padding: 0.5rem 0.875rem; border-radius: 2rem;
  display: flex; align-items: center; gap: 0.35rem;
  box-shadow: 0 8px 24px rgba(0,0,0,0.2);
  white-space: nowrap;
}
.float-badge .material-symbols-outlined { font-size: 0.95rem; color: #059669; }
.float-badge--tl { top: 1rem; left: -2rem; animation: float 5s ease-in-out infinite; }
.float-badge--br { bottom: 1rem; right: -1.5rem; animation: float 7s ease-in-out infinite reverse; }
</style>