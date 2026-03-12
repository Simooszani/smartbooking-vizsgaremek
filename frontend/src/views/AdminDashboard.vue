<template>
  <div class="d-flex bg-light min-vh-100">
    <AdminSidebar />

    <div class="main-content flex-grow-1 p-4" style="margin-left: 260px;">
      
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="fw-bold text-dark m-0">Foglalások Kezelése</h2>
          <p class="text-muted small mb-0">Rendszer adminisztráció és statisztikák</p>
        </div>
        <div class="text-end">
          <span class="badge bg-white text-dark shadow-sm p-2 border">
            <i class="bi bi-calendar3 me-2 text-danger"></i>{{ currentFriendlyDate }}
          </span>
        </div>
      </div>

      <div v-if="!loading" class="row mb-4">
        <div class="col-md-4 mb-3 mb-md-0">
          <div class="card h-100 shadow-sm border-0 border-start border-danger border-5">
            <div class="card-body">
              <h6 class="text-muted text-uppercase small fw-bold">Összes foglalás</h6>
              <h3 class="fw-bold mb-0 text-danger">{{ allBookings.length }} db</h3>
            </div>
          </div>
        </div>

        <div class="col-md-4 mb-3 mb-md-0">
          <div class="card h-100 shadow-sm border-0 border-start border-warning border-5">
            <div class="card-body">
              <h6 class="text-muted text-uppercase small fw-bold">Egyedi vendégek</h6>
              <h3 class="fw-bold mb-0 text-warning">{{ uniqueGuestsCount }} fő</h3>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <div class="card h-100 shadow-sm border-0 border-start border-success border-5">
            <div class="card-body">
              <h6 class="text-muted text-uppercase small fw-bold">Rendszer állapot</h6>
              <h3 class="fw-bold mb-0 text-success small">Online / Aktív</h3>
            </div>
          </div>
        </div>
      </div>

      <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0"> <div v-if="loading" class="text-center my-5 p-5">
            <div class="spinner-border text-danger" role="status" style="width: 3rem; height: 3rem;"></div>
            <p class="mt-3 fw-bold text-muted">Adatok betöltése...</p>
          </div>

          <AdminBookingList 
            v-else 
            :bookings="allBookings" 
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
  components: {
    AdminBookingList,
    AdminSidebar
  },
  data() {
    return {
      allBookings: [],
      loading: true
    }
  },
  computed: {
    uniqueGuestsCount() {
      if (!this.allBookings.length) return 0;
      const emails = this.allBookings.map(b => b.user?.email).filter(e => e);
      return new Set(emails).size;
    },
    currentFriendlyDate() {
      return new Date().toLocaleDateString('hu-HU', { 
        year: 'numeric',
        month: 'long', 
        day: 'numeric', 
        weekday: 'long' 
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
        const Toast = Swal.mixin({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3500,
          timerProgressBar: true,
        });

        Toast.fire({
          icon: 'success',
          title: 'Sikeres bejelentkezés!'
        });

        localStorage.removeItem('loginSuccess');
      }, 500); 
    }
  }
}
</script>

<style scoped>
.main-content {
  transition: all 0.3s;
  background-color: #f8f9fa;
}

.card {
  transition: transform 0.2s ease;
}

.border-5 {
  border-left-width: 5px !important;
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 0 !important;
  }
}
</style>