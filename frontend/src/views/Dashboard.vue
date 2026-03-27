<template>
  <div class="dashboard-container py-4">
    <div class="container">
      <!-- Loading state -->
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-teal" role="status" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted">{{ t('common.loading') }}</p>
      </div>

      <template v-else>
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h1 class="h3 mb-0 fw-bold text-primary-dark">
              <i class="bi bi-calendar-check me-2"></i>{{ t('dashboard.title') }}
            </h1>
          </div>
          <span class="badge bg-teal text-white px-3 py-2 rounded-pill">
            {{ bookings.length }} {{ t('dashboard.count') }}
          </span>
        </div>

        <div class="card shadow-sm border-0 rounded-3" v-if="bookings.length > 0">
          <div class="card-body p-0">
            <booking-list :bookings="bookings" @deleted="loadBookings" />
          </div>
        </div>

        <div v-else class="text-center py-5 mt-3">
          <div class="empty-state-icon mb-3">
            <i class="bi bi-calendar-x"></i>
          </div>
          <h4 class="text-muted">{{ t('dashboard.empty') }}</h4>
          <router-link to="/" class="btn btn-teal mt-3 rounded-pill px-4">
            <i class="bi bi-search me-2"></i>{{ t('dashboard.browse') }}
          </router-link>
        </div>
      </template>
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
        bookings: [],
        loading: true
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
      } finally {
        this.loading = false;
      }
    }
}
</script>

<style scoped>
.dashboard-container { min-height: 70vh; }
.text-primary-dark { color: #264653; }
.bg-teal { background: #2a9d8f; }
.text-teal { color: #2a9d8f; }
.btn-teal { background: #2a9d8f; color: #fff; border: none; font-weight: 600; }
.btn-teal:hover { background: #238b7e; color: #fff; }
.empty-state-icon {
  width: 80px;
  height: 80px;
  background: #f0faf8;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  color: #2a9d8f;
}
</style>
