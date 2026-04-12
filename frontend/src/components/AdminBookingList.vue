<template>
  <div class="mt-3">
    <div v-if="bookings.length === 0" class="text-center py-5 text-muted">
      <i class="bi bi-calendar-x display-4"></i>
      <p class="mt-3">{{ t('admin.no_bookings') }}</p>
    </div>

    <!-- Desktop table -->
    <div v-else class="d-none d-md-block table-responsive">
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
              <div class="fw-bold text-dark small">{{ b.room && b.room.hotel ? b.room.hotel.name : 'N/A' }}</div>
              <span class="badge bg-teal-light text-teal small">{{ b.room ? b.room.type : 'N/A' }}</span>
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
              <span :class="getStatusClass(b.status)" class="badge">
                {{ t('status.' + (b.status || 'confirmed')) }}
              </span>
            </td>
            <td class="text-center">
              <div class="d-flex justify-content-center gap-1">
                <button v-if="b.status !== 'cancelled'" @click="cancelWithReason(b)" class="btn btn-outline-warning btn-sm rounded-pill px-2" :title="t('hotel_admin.cancel_booking')">
                  <i class="bi bi-chat-text me-1"></i>{{ t('admin.cancel_with_reason') }}
                </button>
                <button @click="deleteBooking(b.id)" class="btn btn-outline-danger btn-sm rounded-pill px-2">
                  <i class="bi bi-trash3"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Mobile card layout -->
    <div v-if="bookings.length > 0" class="d-md-none p-3">
      <div v-for="b in bookings" :key="'m'+b.id" class="admin-booking-card mb-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div class="d-flex align-items-center">
            <div class="avatar-circle me-2">{{ b.user ? b.user.name.charAt(0) : '?' }}</div>
            <div>
              <div class="fw-bold small">{{ b.user ? b.user.name : 'N/A' }}</div>
              <div class="text-muted small">{{ b.user ? b.user.email : '-' }}</div>
            </div>
          </div>
          <span :class="getStatusClass(b.status)" class="badge">
            {{ t('status.' + (b.status || 'confirmed')) }}
          </span>
        </div>
        <div class="small mb-1">
          <span class="fw-bold text-dark">{{ b.room && b.room.hotel ? b.room.hotel.name : 'N/A' }}</span>
          <span class="badge bg-teal-light text-teal ms-2">{{ b.room ? b.room.type : '' }}</span>
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
            <i class="bi bi-people me-1"></i>{{ b.guests }} {{ t('common.person') }}
          </span>
          <div class="d-flex gap-1">
            <button v-if="b.status !== 'cancelled'" @click="cancelWithReason(b)" class="btn btn-outline-warning btn-sm rounded-pill px-2">
              <i class="bi bi-chat-text"></i>
            </button>
            <button @click="deleteBooking(b.id)" class="btn btn-outline-danger btn-sm rounded-pill px-2">
              <i class="bi bi-trash3"></i>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import API from '@/api/api'
import Swal from 'sweetalert2'

export default {
  name: 'AdminBookingList',
  props: {
    bookings: { type: Array, required: true }
  },
  methods: {
    formatDate(dateStr) {
      if (!dateStr) return '-';
      return new Date(dateStr).toLocaleDateString(this.currentLocale === 'hu' ? 'hu-HU' : 'en-US', {
        year: 'numeric', month: 'short', day: '2-digit'
      });
    },

    getStatusClass(status) {
      const map = {
        confirmed: 'bg-success-light text-success',
        cancelled: 'bg-danger-light text-danger',
        pending: 'bg-warning-light text-warning',
      };
      return map[status] || 'bg-success-light text-success';
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
          await API.deleteBookingWithReason(booking.id, reason);
          Swal.fire({ icon: 'success', title: this.t('admin.success'), text: this.t('admin.booking_cancelled_with_reason'), timer: 2000, showConfirmButton: false });
          this.$emit('refresh');
        } catch (e) {
          Swal.fire({ icon: 'error', title: this.t('admin.error'), text: e.message || this.t('admin.delete_error'), confirmButtonColor: '#e76f51' });
        }
      }
    },

    async deleteBooking(id) {
      const result = await Swal.fire({
        title: this.t('admin.delete_confirm'),
        text: this.t('admin.delete_irreversible'),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e76f51',
        cancelButtonColor: '#6c757d',
        confirmButtonText: this.t('admin.yes_delete'),
        cancelButtonText: this.t('admin.cancel'),
        reverseButtons: true
      });

      if (result.isConfirmed) {
        try {
          await API.deleteBooking(id);
          Swal.fire({ title: this.t('admin.deleted'), text: this.t('admin.deleted_text'), icon: 'success', timer: 1500, showConfirmButton: false });
          this.$emit('refresh');
        } catch (error) {
          Swal.fire({ title: this.t('admin.error'), text: this.t('admin.delete_error'), icon: 'error', confirmButtonColor: '#e76f51' });
        }
      }
    }
  }
}
</script>

<style scoped>
.avatar-circle {
  width: 35px; height: 35px;
  background: linear-gradient(135deg, #264653, #2a9d8f);
  color: white; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: bold; font-size: 0.8rem; flex-shrink: 0;
}
.text-teal { color: #2a9d8f; }
.bg-teal-light { background: #e8f8f5; }
.bg-success-light { background: #e8f5e9; }
.bg-danger-light { background: #fde8e8; }
.bg-warning-light { background: #fff8e1; }
.admin-booking-card {
  background: #fff;
  border-radius: 12px;
  padding: 1rem;
  border: 1px solid #eee;
  box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
</style>
