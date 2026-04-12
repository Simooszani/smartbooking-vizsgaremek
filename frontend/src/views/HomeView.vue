<template>
  <div class="home-page">
    <!-- Decorative blurred background elements -->
    <div class="deco-blob deco-blob-1"></div>
    <div class="deco-blob deco-blob-2"></div>
    <div class="deco-blob deco-blob-3"></div>

    <!-- Hero Carousel -->
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
      <div class="carousel-indicators">
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
      </div>
      <div class="carousel-inner">
        <div class="carousel-item active">
          <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1600&q=80')">
            <div class="hero-overlay">
              <div class="container text-center text-white">
                <h1 class="hero-title mb-3">{{ t('hero.slide1_title') }}</h1>
                <p class="hero-subtitle">{{ t('hero.slide1_text') }}</p>
              </div>
            </div>
          </div>
        </div>
        <div class="carousel-item">
          <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?w=1600&q=80')">
            <div class="hero-overlay">
              <div class="container text-center text-white">
                <h1 class="hero-title mb-3">{{ t('hero.slide2_title') }}</h1>
                <p class="hero-subtitle">{{ t('hero.slide2_text') }}</p>
              </div>
            </div>
          </div>
        </div>
        <div class="carousel-item">
          <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=1600&q=80')">
            <div class="hero-overlay">
              <div class="container text-center text-white">
                <h1 class="hero-title mb-3">{{ t('hero.slide3_title') }}</h1>
                <p class="hero-subtitle">{{ t('hero.slide3_text') }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
      </button>
    </div>

    <!-- Search Section -->
    <div class="container">
      <div class="search-card">
        <h2 class="search-title mb-4">
          <i class="bi bi-search me-2"></i>{{ t('search.title') }}
        </h2>
        <BookingForm />
      </div>
    </div>

    <!-- Footer -->
    <footer class="footer-section mt-5">
      <div class="container text-center py-4">
        <p class="mb-0 text-muted small">&copy; 2026 SmartBooking. {{ t('footer.rights') }}</p>
      </div>
    </footer>
  </div>
</template>

<script>
import BookingForm from '@/components/BookingForm.vue'

export default {
  name: 'HomeView',
  components: {
    BookingForm
  }
}
</script>

<style scoped>
.home-page {
  min-height: 100vh;
  background: linear-gradient(180deg, #f8f9fa 0%, #eef2f7 40%, #e8eff8 70%, #f0f4f8 100%);
  position: relative;
  overflow: hidden;
}
.deco-blob {
  position: absolute;
  border-radius: 50%;
  pointer-events: none;
  z-index: 0;
}
.deco-blob-1 {
  top: 480px;
  left: -100px;
  width: 450px;
  height: 450px;
  background: radial-gradient(circle, rgba(42,157,143,0.15) 0%, transparent 70%);
  filter: blur(80px);
}
.deco-blob-2 {
  top: 550px;
  right: -60px;
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, rgba(233,196,106,0.12) 0%, transparent 70%);
  filter: blur(80px);
}
.deco-blob-3 {
  top: 750px;
  left: 30%;
  width: 300px;
  height: 300px;
  background: radial-gradient(circle, rgba(231,111,81,0.08) 0%, transparent 70%);
  filter: blur(80px);
}
.home-page > *:not(.deco-blob) {
  position: relative;
  z-index: 1;
}

/* Carousel */
.hero-slide {
  height: 480px;
  background-size: cover;
  background-position: center;
}
.hero-overlay {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, rgba(38,70,83,0.75) 0%, rgba(26,26,46,0.65) 100%);
}
.hero-title {
  font-size: 3rem;
  font-weight: 800;
  letter-spacing: -1px;
  text-shadow: 0 2px 20px rgba(0,0,0,0.3);
}
.hero-subtitle {
  font-size: 1.25rem;
  font-weight: 300;
  opacity: 0.9;
  max-width: 500px;
  margin: 0 auto;
}
.carousel-indicators button {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  margin: 0 6px;
}
.carousel-control-prev-icon,
.carousel-control-next-icon {
  width: 40px;
  height: 40px;
  background-color: rgba(0,0,0,0.3);
  border-radius: 50%;
  background-size: 50%;
  padding: 10px;
}

/* Search card */
.search-card {
  background: #fff;
  border-radius: 20px;
  padding: 2.5rem;
  margin-top: -60px;
  position: relative;
  z-index: 10;
  box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}
.search-title {
  color: #264653;
  font-weight: 700;
  font-size: 1.5rem;
}

/* Footer */
.footer-section {
  background: linear-gradient(135deg, #264653 0%, #1a1a2e 100%);
  color: rgba(255,255,255,0.7);
  margin-top: auto;
}
.footer-section p {
  color: rgba(255,255,255,0.6) !important;
}

@media (max-width: 767.98px) {
  .hero-slide { height: 300px; }
  .hero-title { font-size: 1.6rem; padding: 0 1rem; }
  .hero-subtitle { font-size: 0.95rem; padding: 0 1rem; }
  .search-card { margin-top: -30px; padding: 1.25rem; border-radius: 14px; }
  .search-title { font-size: 1.15rem; }
  .carousel-control-prev, .carousel-control-next { display: none; }
}
</style>
