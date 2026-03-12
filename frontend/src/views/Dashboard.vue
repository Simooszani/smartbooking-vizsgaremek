<template>
  <div class="dashboard-container py-4">
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-body bg-light d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0 text-primary fw-bold">Saját foglalásaim</h1>
        <span class="badge bg-primary rounded-pill">{{ bookings.length }} db foglalás</span>
      </div>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-body p-0">
        <booking-list :bookings="bookings" @deleted="loadBookings" />
      </div>
    </div>
    
    <div v-if="bookings.length === 0" class="text-center mt-5 text-muted">
      <i class="bi bi-calendar-x fs-1"></i>
      <p class="mt-2">Még nincs egyetlen foglalásod sem.</p>
      <router-link to="/" class="btn btn-outline-primary mt-2">Böngészés a hotelek között</router-link>
    </div>
  </div>
</template>

<script>
import API from '../api/api'
import BookingList from '../components/BookingList.vue'

export default {
    name: 'Dashboard',
    components: { BookingList },
    data() {
      return {
        bookings: []
      }
    },
    methods: {
      async loadBookings() {
        try {
          this.bookings = await API.getBookings();
        } catch (e) {
          console.error("Hiba a foglalások betöltésekor", e);
        }
      }
    },
    async mounted() {
      const token = localStorage.getItem('access_token');
      if (!token) {
        this.$router.push('/login');
        return;
      }
      
      try {
        await API.getMe();
        await this.loadBookings();
      } catch (e) {
        localStorage.removeItem('access_token');
        localStorage.removeItem('user');
        this.$router.push('/login');
      }
    }
}
</script>

<style scoped>
.dashboard-container {
  min-height: 70vh;
}
</style>