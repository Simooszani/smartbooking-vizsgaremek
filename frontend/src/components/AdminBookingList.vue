<template>
  <div class="table-responsive mt-3">
    <div v-if="bookings.length === 0" class="text-center py-5 text-muted">
      <i class="bi bi-calendar-x display-4"></i>
      <p class="mt-3">{{ t('admin.no_bookings') }}</p>
    </div>

    <table v-else class="table table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <th class="ps-3">{{ t('admin.guest_data') }}</th>
          <th>{{ t('admin.location_room') }}</th>
          <th class="text-center">{{ t('admin.period') }}</th>
          <th class="text-center">{{ t('admin.headcount') }}</th>
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
            <div class="small fw-semibold">{{ formatDate(b.check_in) }}</div>
            <div class="small text-muted">{{ t('common.to') }}</div>
            <div class="small fw-semibold">{{ formatDate(b.check_out) }}</div>
          </td>

          <td class="text-center">
            <span class="badge bg-light text-dark border px-3 py-2">
              <i class="bi bi-people-fill me-1"></i> {{ b.guests }} {{ t('common.person') }}
            </span>
          </td>

          <td class="text-center">
            <button @click="deleteBooking(b.id)" class="btn btn-outline-danger btn-sm rounded-pill px-3">
              <i class="bi bi-trash3"></i>
            </button>
          </td>
        </tr>
      </tbody>
    </table>
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
          Swal.fire({
            title: this.t('admin.deleted'),
            text: this.t('admin.deleted_text'),
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
          });
          this.$emit('refresh');
        } catch (error) {
          Swal.fire({
            title: this.t('admin.error'),
            text: this.t('admin.delete_error'),
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
.avatar-circle {
  width: 35px;
  height: 35px;
  background: linear-gradient(135deg, #264653, #2a9d8f);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.8rem;
}
.text-teal { color: #2a9d8f; }
.bg-teal-light { background: #e8f8f5; }
</style>
