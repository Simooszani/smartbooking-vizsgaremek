<template>
  <div>
    <h3>Foglalások listája</h3>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Név</th>
          <th>Email</th>
          <th>Dátum</th>
          <th>Vendégek</th>
          <th>Megjegyzés</th>
          <th>Művelet</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="b in bookings" :key="b.id">
          <td>{{ b.name }}</td>
          <td>{{ b.email }}</td>
          <td>{{ b.booking_date }}</td>
          <td>{{ b.guests }}</td>
          <td>{{ b.note }}</td>
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
  props:['bookings'],
  methods:{
    async deleteBooking(id){
      await API.deleteBooking(id)
      this.$emit('deleted')
    }
  }
}
</script>
