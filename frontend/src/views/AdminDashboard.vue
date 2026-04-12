<template>
  <div class="d-flex bg-light min-vh-100">
    <AdminSidebar />

    <div class="main-content flex-grow-1 p-3 p-md-4">
      <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
          <h2 class="fw-bold text-primary-dark m-0 h3-responsive">{{ t('admin.bookings_management') }}</h2>
          <p class="text-muted small mb-0">{{ t('admin.system_admin') }}</p>
        </div>
        <div class="text-end">
          <span class="badge bg-white text-dark shadow-sm p-2 border rounded-pill d-none d-sm-inline-block">
            <i class="bi bi-calendar3 me-2 text-teal"></i>{{ currentFriendlyDate }}
          </span>
        </div>
      </div>

      <div v-if="!loading" class="row mb-4 g-3">
        <div class="col-6 col-md-3">
          <div class="stat-card border-teal">
            <h6 class="text-muted text-uppercase small fw-bold">{{ t('admin.total_bookings') }}</h6>
            <h3 class="fw-bold mb-0 text-teal">{{ filteredBookings.length }} {{ t('admin.pieces') }}</h3>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card border-accent">
            <h6 class="text-muted text-uppercase small fw-bold">{{ t('admin.unique_guests') }}</h6>
            <h3 class="fw-bold mb-0 text-accent">{{ uniqueGuestsCount }} {{ t('admin.person') }}</h3>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card border-coral">
            <h6 class="text-muted text-uppercase small fw-bold">{{ t('admin.total_guests') }}</h6>
            <h3 class="fw-bold mb-0 text-coral">{{ totalGuestsCount }} {{ t('admin.person') }}</h3>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="stat-card border-success">
            <h6 class="text-muted text-uppercase small fw-bold">{{ t('admin.system_status') }}</h6>
            <h3 class="fw-bold mb-0 text-success small">{{ t('admin.online') }}</h3>
          </div>
        </div>
      </div>

      <!-- Search -->
      <div v-if="!loading" class="mb-3">
        <div class="position-relative">
          <i class="bi bi-search position-absolute" style="left:14px;top:50%;transform:translateY(-50%);color:#888;"></i>
          <input v-model="searchQuery" type="text" class="form-control rounded-pill ps-5 border-0 shadow-sm"
            :placeholder="t('admin.search_bookings_placeholder')">
        </div>
      </div>

      <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0">
          <div v-if="loading" class="text-center my-5 p-5">
            <div class="spinner-border text-teal" role="status" style="width: 3rem; height: 3rem;"></div>
            <p class="mt-3 fw-bold text-muted">{{ t('admin.loading') }}</p>
          </div>

          <AdminBookingList
            v-else
            :bookings="filteredBookings"
            @refresh="fetchAdminData"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import API from '@/api/api'
import AdminBookingList from '@/components/AdminBookingList.vue'
import AdminSidebar from '@/components/AdminSidebar.vue'
import Swal from 'sweetalert2'

export default {
  name: 'AdminDashboard',
  components: { AdminBookingList, AdminSidebar },
  data() {
    return {
      allBookings: [],
      loading: true,
      searchQuery: ''
    }
  },
  computed: {
    filteredBookings() {
      if (!this.searchQuery.trim()) return this.allBookings;
      const q = this.searchQuery.trim().toLowerCase();
      return this.allBookings.filter(b => {
        const userName = b.user ? b.user.name.toLowerCase() : '';
        const userEmail = b.user ? b.user.email.toLowerCase() : '';
        const hotelName = b.room && b.room.hotel ? b.room.hotel.name.toLowerCase() : '';
        const roomType = b.room ? b.room.type.toLowerCase() : '';
        const code = b.booking_code ? b.booking_code.toLowerCase() : '';
        return userName.includes(q) || userEmail.includes(q) || hotelName.includes(q) || roomType.includes(q) || code.includes(q);
      });
    },
    uniqueGuestsCount() {
      if (!this.filteredBookings.length) return 0;
      const emails = this.filteredBookings.map(b => b.user?.email).filter(e => e);
      return new Set(emails).size;
    },
    totalGuestsCount() {
      return this.filteredBookings.reduce((sum, b) => sum + (b.guests || 0), 0);
    },
    currentFriendlyDate() {
      const loc = this.currentLocale === 'hu' ? 'hu-HU' : 'en-US';
      return new Date().toLocaleDateString(loc, {
        year: 'numeric', month: 'long', day: 'numeric', weekday: 'long'
      });
    }
  },
  methods: {
    async fetchAdminData() {
      this.loading = true;
      try {
        this.allBookings = await API.getAllAdminBookings();
      } catch (error) {
        console.error("Hiba az admin adatoknál:", error);
      } finally {
        this.loading = false;
      }
    }
  },
  mounted() {
    this.fetchAdminData();
    if (localStorage.getItem('loginSuccess')) {
      setTimeout(() => {
        Swal.mixin({
          toast: true, position: 'top-end', showConfirmButton: false,
          timer: 3500, timerProgressBar: true,
        }).fire({ icon: 'success', title: this.t('login.success') });
        localStorage.removeItem('loginSuccess');
      }, 500);
    }
  }
}
</script>

<style scoped>
.text-primary-dark { color: #264653; }
.text-teal { color: #2a9d8f; }
.text-accent { color: #e9c46a; }
.stat-card {
  background: #fff;
  border-radius: 12px;
  padding: 1.25rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  border-left: 5px solid transparent;
}
.border-teal { border-left-color: #2a9d8f !important; }
.border-accent { border-left-color: #e9c46a !important; }
.border-coral { border-left-color: #e76f51 !important; }
.text-coral { color: #e76f51; }
.border-success { border-left-color: #198754 !important; }
.main-content { margin-left: 260px; }
.h3-responsive { font-size: 1.5rem; }
@media (max-width: 767.98px) {
  .main-content { margin-left: 0 !important; padding-top: 70px !important; }
  .h3-responsive { font-size: 1.15rem; }
}
</style>
