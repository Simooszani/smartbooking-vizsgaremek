<template>
  <nav class="navbar navbar-expand-lg shadow-sm sticky-top" :class="scrolled ? 'navbar-scrolled' : 'navbar-top'">
    <div class="container">
      <router-link class="navbar-brand d-flex align-items-center" to="/">
        <i class="bi bi-building me-2"></i>
        <span class="fw-bold">Smart<span class="brand-accent">Booking</span></span>
      </router-link>

      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-center">
          <li class="nav-item">
            <router-link class="nav-link px-3" to="/">{{ t('navbar.home') }}</router-link>
          </li>

          <li class="nav-item" v-if="isLoggedIn">
            <router-link class="nav-link px-3" to="/dashboard">
              <i class="bi bi-calendar-check me-1"></i>{{ t('navbar.profile') }}
            </router-link>
          </li>

          <!-- Hotel Admin link -->
          <li class="nav-item" v-if="isHotelAdmin">
            <router-link class="nav-link px-3 hotel-admin-link" to="/hotel-admin">
              <i class="bi bi-building me-1"></i>{{ t('hotel_admin.panel') }}
            </router-link>
          </li>

          <!-- Admin link -->
          <li class="nav-item" v-if="isAdmin">
            <router-link class="nav-link px-3 admin-link" to="/admin">
              <i class="bi bi-shield-lock me-1"></i>{{ t('navbar.admin') }}
            </router-link>
          </li>

          <!-- Notifications bell -->
          <li class="nav-item ms-1" v-if="isLoggedIn">
            <router-link class="nav-link px-2 position-relative" to="/notifications">
              <i class="bi bi-bell fs-5"></i>
              <span v-if="unreadCount > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                {{ unreadCount > 9 ? '9+' : unreadCount }}
              </span>
            </router-link>
          </li>

          <!-- Language switcher -->
          <li class="nav-item dropdown ms-2">
            <a class="nav-link dropdown-toggle lang-toggle px-2" href="#" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-globe2 me-1"></i>
              <span class="text-uppercase small fw-bold">{{ currentLocale }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
              <li>
                <a class="dropdown-item" :class="{ active: currentLocale === 'hu' }" href="#" @click.prevent="switchLang('hu')">
                  <span class="me-2">&#127469;&#127482;</span> {{ t('lang.hu') }}
                </a>
              </li>
              <li>
                <a class="dropdown-item" :class="{ active: currentLocale === 'en' }" href="#" @click.prevent="switchLang('en')">
                  <span class="me-2">&#127468;&#127463;</span> {{ t('lang.en') }}
                </a>
              </li>
            </ul>
          </li>

          <li class="nav-item ms-2">
            <button v-if="isLoggedIn" @click="logout" class="btn btn-outline-coral btn-sm rounded-pill px-3">
              <i class="bi bi-box-arrow-right me-1"></i>{{ t('navbar.logout') }}
            </button>
            <router-link v-else class="btn btn-coral btn-sm rounded-pill px-3" to="/login">
              <i class="bi bi-person me-1"></i>{{ t('navbar.login') }}
            </router-link>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script>
import API from '@/api/api'
import { setLocale } from '@/i18n'

export default {
  name: 'Navbar',
  data() {
    return {
      scrolled: false,
      unreadCount: 0,
      pollInterval: null
    }
  },
  computed: {
    isLoggedIn() {
      return !!localStorage.getItem('access_token');
    },
    isAdmin() {
      const user = JSON.parse(localStorage.getItem('user') || '{}');
      return user.role === 'admin' || user.role === 'super_admin';
    },
    isHotelAdmin() {
      const user = JSON.parse(localStorage.getItem('user') || '{}');
      return user.role === 'hotel_admin';
    }
  },
  methods: {
    async logout() {
      await API.logout();
      this.unreadCount = 0;
      this.$router.push('/login');
      location.reload();
    },
    switchLang(lang) {
      setLocale(lang);
      this.$forceUpdate();
    },
    handleScroll() {
      this.scrolled = window.scrollY > 50;
    },
    async fetchUnreadCount() {
      if (!this.isLoggedIn) return;
      try {
        const data = await API.getUnreadCount();
        this.unreadCount = data.count || 0;
      } catch (e) {
        // silent
      }
    }
  },
  mounted() {
    window.addEventListener('scroll', this.handleScroll);
    this.fetchUnreadCount();
    // Poll every 30s for new notifications
    this.pollInterval = setInterval(() => this.fetchUnreadCount(), 30000);
  },
  beforeDestroy() {
    window.removeEventListener('scroll', this.handleScroll);
    if (this.pollInterval) clearInterval(this.pollInterval);
  }
}
</script>

<style scoped>
.navbar-top {
  background: rgba(38, 70, 83, 0.95);
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
}
.navbar-scrolled {
  background: rgba(38, 70, 83, 1);
  box-shadow: 0 4px 20px rgba(0,0,0,0.15) !important;
  transition: all 0.3s ease;
}
.navbar-brand {
  color: #fff !important;
  font-size: 1.4rem;
  letter-spacing: -0.5px;
}
.brand-accent {
  color: #e9c46a;
}
.nav-link {
  color: rgba(255,255,255,0.85) !important;
  font-weight: 500;
  font-size: 0.9rem;
  transition: color 0.2s;
}
.nav-link:hover, .nav-link.router-link-exact-active {
  color: #e9c46a !important;
}
.admin-link {
  color: #e76f51 !important;
  font-weight: 600;
}
.admin-link:hover {
  color: #f4a261 !important;
}
.hotel-admin-link {
  color: #f4a261 !important;
  font-weight: 600;
}
.hotel-admin-link:hover {
  color: #e9c46a !important;
}
.lang-toggle {
  color: rgba(255,255,255,0.7) !important;
}
.btn-coral {
  background: #e76f51;
  color: #fff;
  border: none;
  font-weight: 600;
}
.btn-coral:hover {
  background: #d45d3f;
  color: #fff;
}
.btn-outline-coral {
  border: 2px solid #e76f51;
  color: #e76f51;
  font-weight: 600;
  background: transparent;
}
.btn-outline-coral:hover {
  background: #e76f51;
  color: #fff;
}
.navbar-toggler-icon {
  filter: invert(1);
}
.dropdown-item.active {
  background-color: #2a9d8f;
}
</style>
