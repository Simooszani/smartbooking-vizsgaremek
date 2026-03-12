<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
      <router-link class="navbar-brand fw-bold text-warning" to="/">
        <i class="bi bi-h-square"></i> SmartBooking
      </router-link>
      
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <router-link class="nav-link" to="/">Kezdőlap</router-link>
          </li>
          
          <li class="nav-item" v-if="isLoggedIn">
            <router-link class="nav-link" to="/dashboard">Profilom</router-link>
          </li>

          <li class="nav-item" v-if="isAdmin">
            <router-link class="nav-link text-danger fw-bold text-uppercase" to="/admin">Admin</router-link>
          </li>

          <li class="nav-item ms-lg-3">
            <button v-if="isLoggedIn" @click="logout" class="btn btn-outline-light btn-sm mt-1">Kijelentkezés</button>
            <router-link v-else class="btn btn-primary btn-sm mt-1" to="/login">Bejelentkezés</router-link>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script>
import API from '@/api/api'

export default {
  name: 'Navbar',
  computed: {
    isLoggedIn() {
      return !!localStorage.getItem('access_token');
    },
    isAdmin() {
      const user = JSON.parse(localStorage.getItem('user') || '{}');
      return user.is_admin === 1 || user.is_admin === true;
    }
  },
  methods: {
    async logout() {
      await API.logout();
      this.$router.push('/login');
      location.reload();
    }
  }
}
</script>