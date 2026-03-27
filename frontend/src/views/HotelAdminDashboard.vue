<template>
  <div class="d-flex bg-light min-vh-100">
    <HotelAdminSidebar />

    <div class="main-content flex-grow-1 p-4" style="margin-left: 260px;">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="fw-bold text-primary-dark m-0">{{ t('hotel_admin.my_bookings') }}</h2>
          <p class="text-muted small mb-0" v-if="hotelName">
            <i class="bi bi-building me-1"></i>{{ hotelName }}
          </p>
        </div>
        <div class="text-end">
          <span class="badge bg-white text-dark shadow-sm p-2 border rounded-pill">
            <i class="bi bi-calendar3 me-2 text-accent"></i>{{ currentFriendlyDate }}
          </span>
        </div>
      </div>

      <!-- Stats -->
      <div v-if="!loading" class="row mb-4 g-3">
        <div class="col-md-4">
          <div class="stat-card border-accent">
            <h6 class="text-muted text-uppercase small fw-bold">{{ t('admin.total_bookings') }}</h6>
            <h3 class="fw-bold mb-0 text-accent">{{ bookings.length }} {{ t('admin.pieces') }}</h3>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card border-teal">
            <h6 class="text-muted text-uppercase small fw-bold">{{ t('admin.unique_guests') }}</h6>
            <h3 class="fw-bold mb-0 text-teal">{{ uniqueGuestsCount }} {{ t('admin.person') }}</h3>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card border-success">
            <h6 class="text-muted text-uppercase small fw-bold">{{ t('admin.system_status') }}</h6>
            <h3 class="fw-bold mb-0 text-success small">{{ t('admin.online') }}</h3>
          </div>
        </div>
      </div>

      <!-- No hotel assigned -->
      <div v-if="!loading && !hotelName" class="text-center py-5">
        <i class="bi bi-building-x display-1 text-muted"></i>
        <h4 class="mt-3 text-muted">{{ t('hotel_admin.no_hotel') }}</h4>
      </div>

      <!-- Bookings table -->
      <div v-else class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0">
          <div v-if="loading" class="text-center my-5 p-5">
            <div class="spinner-border text-accent" role="status" style="width: 3rem; height: 3rem;"></div>
            <p class="mt-3 fw-bold text-muted">{{ t('admin.loading') }}</p>
          </div>

          <div v-else-if="bookings.length === 0" class="text-center py-5 text-muted">
            <i class="bi bi-calendar-x display-4"></i>
            <p class="mt-3">{{ t('admin.no_bookings') }}</p>
          </div>

          <div v-else class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-3">{{ t('admin.guest_data') }}</th>
                  <th>{{ t('admin.location_room') }}</th>
                  <th class="text-center">{{ t('admin.period') }}</th>
                  <th class="text-center">{{ t('admin.headcount') }}</th>
                  <th class="text-center">{{ t('dashboard.status') }}</th>
                  <th class="text-center">{{ t('admin.actions') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="b in bookings" :key="b.id">
                  <td class="ps-3">
                    <div class="d-flex align-items-center">
                      <div class="avatar-circle me-2">{{ b.user ? b.user.name.charAt(0) : '?' }}</div>
                      <div>
                        <div class="fw-bold small">{{ b.user ? b.user.name : 'N/A' }}</div>
                        <div class="text-muted small">{{ b.user ? b.user.email : '-' }}</div>
                      </div>
                    </div>
                  </td>

                  <td>
                    <span class="badge bg-accent-light text-accent-dark">{{ b.room ? b.room.type : 'N/A' }}</span>
                  </td>

                  <td class="text-center">
                    <div class="small">
                      <span class="text-muted">{{ t('date.check_in') }}:</span>
                      <span class="fw-semibold ms-1">{{ formatDate(b.check_in) }}</span>
                    </div>
                    <div class="small">
                      <span class="text-muted">{{ t('date.check_out') }}:</span>
                      <span class="fw-semibold ms-1">{{ formatDate(b.check_out) }}</span>
                    </div>
                  </td>

                  <td class="text-center">
                    <span class="badge bg-light text-dark border px-3 py-2">
                      <i class="bi bi-people-fill me-1"></i> {{ b.guests }} {{ t('common.person') }}
                    </span>
                  </td>

                  <td class="text-center">
                    <span :class="getStatusBadgeClass(b.status)" class="badge">
                      {{ t('status.' + (b.status || 'confirmed')) }}
                    </span>
                  </td>

                  <td class="text-center">
                    <button
                      v-if="b.status !== 'cancelled'"
                      @click="cancelWithReason(b)"
                      class="btn btn-outline-danger btn-sm rounded-pill px-3">
                      <i class="bi bi-x-circle me-1"></i>{{ t('hotel_admin.cancel_booking') }}
                    </button>
                    <span v-else class="text-muted small">{{ t('status.cancelled') }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/api/api'
import HotelAdminSidebar from '@/components/HotelAdminSidebar.vue'
import Swal from 'sweetalert2'

export default {
  name: 'HotelAdminDashboard',
  components: { HotelAdminSidebar },
  data() {
    return {
      bookings: [],
      hotelName: '',
      loading: true
    }
  },
  computed: {
    uniqueGuestsCount() {
      if (!this.bookings.length) return 0;
      const emails = this.bookings.map(b => b.user?.email).filter(e => e);
      return new Set(emails).size;
    },
    currentFriendlyDate() {
      const loc = this.currentLocale === 'hu' ? 'hu-HU' : 'en-US';
      return new Date().toLocaleDateString(loc, {
        year: 'numeric', month: 'long', day: 'numeric', weekday: 'long'
      });
    }
  },
  methods: {
    formatDate(dateStr) {
      if (!dateStr) return '-';
      return new Date(dateStr).toLocaleDateString(this.currentLocale === 'hu' ? 'hu-HU' : 'en-US', {
        year: 'numeric', month: 'short', day: '2-digit'
      });
    },

    getStatusBadgeClass(status) {
      const map = {
        confirmed: 'bg-success-light text-success',
        cancelled: 'bg-danger-light text-danger',
        pending: 'bg-warning-light text-warning',
      };
      return map[status] || 'bg-success-light text-success';
    },

    async fetchBookings() {
      this.loading = true;
      try {
        this.bookings = await api.getHotelAdminBookings();
        // Get hotel name from user data
        const userData = localStorage.getItem('user');
        if (userData) {
          const u = JSON.parse(userData);
          if (u.managed_hotel) {
            this.hotelName = u.managed_hotel.name;
          }
        }
        // If not in localStorage, try from first booking
        if (!this.hotelName && this.bookings.length > 0 && this.bookings[0].room?.hotel) {
          this.hotelName = this.bookings[0].room.hotel.name;
        }
      } catch (error) {
        console.error('Hiba:', error);
      } finally {
        this.loading = false;
      }
    },

    async cancelWithReason(booking) {
      const { value: reason } = await Swal.fire({
        title: this.t('admin.cancel_booking_title'),
        html:
          `<div class="text-start">
            <p class="text-muted small mb-2">${booking.user ? booking.user.name : ''} — ${booking.room ? booking.room.type : ''}</p>
            <label class="form-label fw-semibold small">${this.t('admin.cancel_reason_label')}</label>
            <textarea id="swal-reason" class="form-control" rows="3" placeholder="${this.t('admin.cancel_reason_placeholder')}"></textarea>
          </div>`,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: this.t('admin.cancel_with_reason'),
        cancelButtonText: this.t('admin.cancel'),
        confirmButtonColor: '#e76f51',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
          const reason = document.getElementById('swal-reason').value;
          if (!reason.trim()) {
            Swal.showValidationMessage(this.t('admin.cancel_reason_placeholder'));
            return false;
          }
          return reason;
        }
      });

      if (reason) {
        try {
          await api.deleteBookingWithReason(booking.id, reason);
          Swal.fire({
            icon: 'success',
            title: this.t('admin.success'),
            text: this.t('admin.booking_cancelled_with_reason'),
            timer: 2000,
            showConfirmButton: false
          });
          this.fetchBookings();
        } catch (e) {
          Swal.fire({
            icon: 'error',
            title: this.t('admin.error'),
            text: e.message || this.t('admin.delete_error'),
            confirmButtonColor: '#e76f51'
          });
        }
      }
    }
  },
  mounted() {
    this.fetchBookings();
  }
}
</script>

<style scoped>
.text-primary-dark { color: #264653; }
.text-teal { color: #2a9d8f; }
.text-accent { color: #e9c46a; }
.text-accent-dark { color: #b8860b; }
.bg-accent-light { background: #fdf6e3; }
.bg-success-light { background: #e8f5e9; }
.bg-danger-light { background: #fde8e8; }
.bg-warning-light { background: #fff8e1; }
.avatar-circle {
  width: 35px; height: 35px;
  background: linear-gradient(135deg, #e9c46a, #f4a261);
  color: white; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: bold; font-size: 0.8rem;
}
.stat-card {
  background: #fff;
  border-radius: 12px;
  padding: 1.25rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  border-left: 5px solid transparent;
}
.border-teal { border-left-color: #2a9d8f !important; }
.border-accent { border-left-color: #e9c46a !important; }
.border-success { border-left-color: #198754 !important; }

@media (max-width: 768px) {
  .main-content { margin-left: 0 !important; }
}
</style>
