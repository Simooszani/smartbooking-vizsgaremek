<template>
  <div>
    <!-- Desktop table -->
    <div class="d-none d-md-block table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-4">{{ t('dashboard.hotel') }}</th>
            <th>{{ t('dashboard.period') }}</th>
            <th class="text-center">{{ t('dashboard.guests') }}</th>
            <th class="text-center">{{ t('dashboard.status') }}</th>
            <th class="text-center">{{ t('dashboard.actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="b in bookings" :key="b.id">
            <td class="ps-4">
              <div v-if="b.room">
                <strong class="d-block text-primary-dark">{{ b.room.hotel ? b.room.hotel.name : '' }}</strong>
                <span class="badge bg-teal-light text-teal me-1">{{ b.room.type }}</span>
                <small class="text-muted">({{ b.room.capacity }} {{ t('dashboard.person') }})</small>
              </div>
              <span v-else class="text-danger small">{{ t('dashboard.no_room') }}</span>
            </td>
            <td>
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
              <span class="badge bg-light text-dark border">{{ b.guests }} {{ t('dashboard.person') }}</span>
            </td>
            <td class="text-center">
              <span :class="getStatusClass(b.status)" class="badge">{{ t('status.' + (b.status || 'confirmed')) }}</span>
            </td>
            <td class="text-center">
              <button @click="deleteBooking(b.id)" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                <i class="bi bi-x-circle me-1"></i>{{ t('dashboard.delete') }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Mobile card layout -->
    <div class="d-md-none p-3">
      <div v-for="b in bookings" :key="'m'+b.id" class="booking-card-mobile mb-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div>
            <strong class="text-primary-dark" v-if="b.room && b.room.hotel">{{ b.room.hotel.name }}</strong>
            <span v-if="b.room" class="badge bg-teal-light text-teal ms-2 small">{{ b.room.type }}</span>
          </div>
          <span :class="getStatusClass(b.status)" class="badge">{{ t('status.' + (b.status || 'confirmed')) }}</span>
        </div>
        <div class="row g-2 mb-2 small">
          <div class="col-6">
            <span class="text-muted d-block">{{ t('date.check_in') }}</span>
            <span class="fw-semibold">{{ formatDate(b.check_in) }}</span>
          </div>
          <div class="col-6">
            <span class="text-muted d-block">{{ t('date.check_out') }}</span>
            <span class="fw-semibold">{{ formatDate(b.check_out) }}</span>
          </div>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <span class="badge bg-light text-dark border">
            <i class="bi bi-people me-1"></i>{{ b.guests }} {{ t('dashboard.person') }}
          </span>
          <button @click="deleteBooking(b.id)" class="btn btn-sm btn-outline-danger rounded-pill px-3">
            <i class="bi bi-x-circle me-1"></i>{{ t('dashboard.delete') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import API from '../api/api'
import Swal from 'sweetalert2'

export default {
  props: ['bookings'],
  methods: {
    getStatusClass(status) {
      const map = {
        confirmed: 'bg-success-light text-success',
        cancelled: 'bg-danger-light text-danger',
        pending: 'bg-warning-light text-warning',
      };
      return map[status] || 'bg-success-light text-success';
    },
    formatDate(dateStr) {
      if (!dateStr) return '-';
      return new Date(dateStr).toLocaleDateString(this.currentLocale === 'hu' ? 'hu-HU' : 'en-US', {
        year: 'numeric', month: 'short', day: '2-digit'
      });
    },
    async deleteBooking(id) {
      const result = await Swal.fire({
        title: this.t('dashboard.delete_confirm_title'),
        text: this.t('dashboard.delete_confirm_text'),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e76f51',
        cancelButtonColor: '#6c757d',
        confirmButtonText: this.t('dashboard.delete_confirm_btn'),
        cancelButtonText: this.t('dashboard.cancel_btn'),
        reverseButtons: true
      });

      if (result.isConfirmed) {
        try {
          await API.deleteBooking(id);
          Swal.fire({
            title: this.t('dashboard.deleted'),
            text: this.t('dashboard.deleted_text'),
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
          });
          this.$emit('deleted');
        } catch (error) {
          Swal.fire({
            title: this.t('common.error'),
            text: this.t('dashboard.delete_error'),
            icon: 'error',
            confirmButtonColor: '#e76f51'
          });
        }
      }
    }
  }
}
</script>

<style scoped>
.text-primary-dark { color: #264653; }
.text-teal { color: #2a9d8f; }
.bg-teal-light { background: #e8f8f5; }
.bg-success-light { background: #e8f5e9; }
.bg-danger-light { background: #fde8e8; }
.bg-warning-light { background: #fff8e1; }
.booking-card-mobile {
  background: #fff;
  border-radius: 12px;
  padding: 1rem;
  border: 1px solid #eee;
  box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
</style>
