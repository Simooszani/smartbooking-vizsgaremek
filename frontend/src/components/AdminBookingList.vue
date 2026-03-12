<template>
  <div class="table-responsive mt-3">
    <div v-if="bookings.length === 0" class="alert alert-info text-center shadow-sm">
      <i class="bi bi-info-circle me-2"></i> Jelenleg nincs megjeleníthető foglalás az adatbázisban.
    </div>

    <table v-else class="table table-hover align-middle bg-white shadow-sm border">
      <thead class="table-danger">
        <tr>
          <th class="ps-3">Vendég adatai</th>
          <th>Helyszín & Szobatípus</th>
          <th class="text-center">Időszak</th>
          <th class="text-center">Létszám</th>
          <th class="text-center">Műveletek</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="b in bookings" :key="b.id">
          <td class="ps-3">
            <div class="d-flex align-items-center">
              <div class="avatar-circle me-2">{{ b.user ? b.user.name.charAt(0) : '?' }}</div>
              <div>
                <div class="fw-bold">{{ b.user ? b.user.name : 'Ismeretlen vendég' }}</div>
                <div class="text-muted small">{{ b.user ? b.user.email : '-' }}</div>
              </div>
            </div>
          </td>

          <td>
            <div class="fw-bold text-dark">{{ b.room && b.room.hotel ? b.room.hotel.name : 'N/A' }}</div>
            <span class="badge rounded-pill bg-light text-danger border border-danger small">
              {{ b.room ? b.room.type : 'N/A' }}
            </span>
          </td>

          <td class="text-center">
            <div class="small fw-bold text-primary">{{ formatDate(b.check_in) }}</div>
            <div class="small text-muted">tól - ig</div>
            <div class="small fw-bold text-primary">{{ formatDate(b.check_out) }}</div>
          </td>

          <td class="text-center">
             <span class="badge bg-secondary px-3 py-2"><i class="bi bi-people-fill me-1"></i> {{ b.guests }} fő</span>
          </td>

          <td class="text-center">
            <button @click="deleteBooking(b.id)" class="btn btn-outline-danger btn-sm rounded-pill px-3 shadow-sm hover-elevate">
              <i class="bi bi-trash3-fill"></i> Törlés
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import API from '@/api/api'
import Swal from 'sweetalert2' // 1. Importáld be a SweetAlert-et

export default {
  name: 'AdminBookingList',
  props: {
    bookings: { type: Array, required: true }
  },
  methods: {
    formatDate(dateStr) {
      if (!dateStr) return '-';
      const options = { year: 'numeric', month: 'short', day: '2-digit' };
      return new Date(dateStr).toLocaleDateString('hu-HU', options);
    },
    
    async deleteBooking(id) {
      // 2. SweetAlert2 megerősítő ablak
      const result = await Swal.fire({
        title: 'Biztos benne?',
        text: "Ez a művelet nem vonható vissza!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Igen, töröljem!',
        cancelButtonText: 'Mégse',
        reverseButtons: true
      });

      if (result.isConfirmed) {
        try {
          await API.deleteBooking(id);
          
          Swal.fire({
            title: 'Törölve!',
            text: 'A foglalás sikeresen eltávolítva.',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
          });

          this.$emit('refresh');
        } catch (error) {
          Swal.fire({
            title: 'Hiba!',
            text: 'Nem sikerült törölni a foglalást.',
            icon: 'error'
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
  background-color: #dc3545;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}
.hover-elevate:hover {
  transform: translateY(-2px);
  transition: transform 0.2s;
}
</style>