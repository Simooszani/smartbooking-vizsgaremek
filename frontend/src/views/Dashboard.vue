<template>
  <div>
    <h1 class="mb-3">Dashboard</h1>
    <button @click="logoutUser" class="btn btn-danger mb-3">Logout</button>

    <booking-form @new-booking="loadBookings" />
    <booking-list :bookings="bookings" @deleted="loadBookings" />
  </div>
</template>

<script>
import API from '../api/api'
import BookingForm from '../components/BookingForm.vue'
import BookingList from '../components/BookingList.vue'

export default {
  components:{BookingForm, BookingList},
  data() {
    return {
      bookings: []
    }
  },
  methods: {
    async logoutUser() {
      await API.logout();
      this.$router.push('/').catch(err => {
        if (err.name !== 'NavigationDuplicated') {
          throw err;
        }
      });
    },
    async loadBookings(){
      this.bookings = await API.getBookings()
    },
    async created() {
      const user = await API.me();
      if (!user.id) {
        this.$router.push('/');
      } else {
        this.fetchBookings();
      }
    }
  },
  mounted() {
      const token = localStorage.getItem('access_token');
      if (!token) {
          this.$router.push('/login');
          return;
      }
      this.loadBookings();
  }
}
</script>
