<template>
  <div>
    <h3>Foglalások listája</h3>
    <table class="table table-striped table-hover mt-3">
      <thead class="table-dark">
        <tr>
          <th>Szálloda / Szoba</th>
          <th>Időszak</th>
          <th>Vendégek</th>
          <th>Státusz</th>
          <th>Művelet</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="b in bookings" :key="b.id">
          <td>
            <div v-if="b.room">
              <strong class="d-block">{{ b.room.hotel ? b.room.hotel.name : 'Szálloda' }}</strong>
              
              <span class="badge bg-info text-dark me-1">{{ b.room.type }}</span>
              
              <small class="text-muted">
                {{ b.room.room_number ? b.room.room_number + '. szoba' : '(' + b.room.capacity + ' fős férőhely)' }}
              </small>
            </div>
            <span v-else class="text-danger">Nincs szoba adat</span>
          </td>
          
          <td>
            {{ b.check_in }} <br> 
            <small class="text-muted">ig: {{ b.check_out }}</small>
          </td>

          <td class="text-center">{{ b.guests }} fő</td>

          <td>
            <span class="badge bg-success">{{ b.status }}</span>
          </td>

          <td>
            <button @click="deleteBooking(b.id)" class="btn btn-sm btn-danger">Törlés</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import API from '../api/api'

export default {
  props: ['bookings'],
  methods: {
    async deleteBooking(id) {
      if (confirm('Biztosan törölni szeretnéd ezt a foglalást?')) {
        try {
          await API.deleteBooking(id);
          this.$emit('deleted');
        } catch (error) {
          console.error("Hiba a törlésnél:", error);
          alert("Nem sikerült törölni a foglalást.");
        }
      }
    }
  }
}
</script>