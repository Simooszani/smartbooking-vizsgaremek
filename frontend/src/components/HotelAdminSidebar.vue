<template>
  <div>
    <button class="mobile-burger d-md-none" @click="mobileOpen = !mobileOpen" :aria-label="t('admin.toggle_menu')">
      <i class="bi" :class="mobileOpen ? 'bi-x-lg' : 'bi-list'"></i>
    </button>

    <div v-if="mobileOpen" class="sidebar-overlay d-md-none" @click="mobileOpen = false"></div>

    <div class="sidebar" :class="{ 'sidebar-mobile-open': mobileOpen }">
      <div class="p-4 text-center border-bottom border-secondary">
        <div class="sidebar-logo mb-2">
          <i class="bi bi-building"></i>
        </div>
        <h5 class="fw-bold mb-0 text-accent">SmartBooking</h5>
        <small class="text-muted">{{ t('hotel_admin.panel') }}</small>
      </div>

      <nav class="nav flex-column mt-3">
        <router-link to="/hotel-admin" class="nav-link text-white py-3 px-4 d-flex align-items-center" active-class="active-link" exact @click.native="mobileOpen = false">
          <i class="bi bi-calendar-check me-3"></i> {{ t('hotel_admin.my_bookings') }}
        </router-link>

        <router-link to="/chat" class="nav-link text-white py-3 px-4 d-flex align-items-center" active-class="active-link" @click.native="mobileOpen = false">
          <i class="bi bi-chat-dots me-3"></i> {{ t('chat.title') }}
        </router-link>

        <hr class="mx-3 border-secondary">

        <router-link to="/" class="nav-link text-muted py-3 px-4 d-flex align-items-center" @click.native="mobileOpen = false">
          <i class="bi bi-house-door me-3"></i> {{ t('admin.back_home') }}
        </router-link>
      </nav>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HotelAdminSidebar',
  data() {
    return { mobileOpen: false }
  }
}
</script>

<style scoped>
.sidebar {
  width: 260px;
  min-height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
  z-index: 1050;
  transition: transform 0.3s ease;
}
.sidebar-logo {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #e9c46a, #f4a261);
  border-radius: 14px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 1.5rem;
}
.text-accent { color: #e9c46a; }
.nav-link {
  transition: all 0.3s;
  border-left: 4px solid transparent;
  font-size: 0.9rem;
}
.nav-link:hover {
  background: rgba(255, 255, 255, 0.08);
}
.active-link {
  background: rgba(233, 196, 106, 0.15) !important;
  border-left: 4px solid #e9c46a !important;
  color: #e9c46a !important;
}

.mobile-burger {
  position: fixed;
  top: 70px;
  left: 12px;
  z-index: 1055;
  width: 40px;
  height: 40px;
  border-radius: 10px;
  border: none;
  background: #1a1a2e;
  color: #e9c46a;
  font-size: 1.2rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
  display: flex;
  align-items: center;
  justify-content: center;
}
.sidebar-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.5);
  z-index: 1040;
}

@media (max-width: 767.98px) {
  .sidebar {
    transform: translateX(-100%);
    width: 240px;
  }
  .sidebar.sidebar-mobile-open {
    transform: translateX(0);
  }
}
</style>
