<template>
  <div class="booking-system">
    <div class="card bg-warning shadow-sm mb-5 p-4 border-0">
      <h2 class="mb-3 text-dark fw-bold">Találd meg a tökéletes szállást!</h2>
      <div class="row g-2">
        <div class="col-md-4">
          <label class="form-label fw-bold">Úticél</label>
          <input v-model="search.destination" type="text" class="form-control" placeholder="Hová mész?">
        </div>
        <div class="col-md-3">
          <label class="form-label fw-bold">Érkezés</label>
          <input v-model="search.check_in" type="date" class="form-control" :min="today">
        </div>
        <div class="col-md-3">
          <label class="form-label fw-bold">Távozás</label>
          <input v-model="search.check_out" type="date" class="form-control" :min="search.check_in">
        </div>
        <div class="col-md-2">
          <label class="form-label fw-bold">Vendégek</label>
          <input v-model.number="search.guests" type="number" class="form-control" min="1">
        </div>
        <div class="col-12 mt-3">
          <button @click="performSearch" class="btn btn-primary btn-lg w-100 fw-bold">KERESÉS</button>
        </div>
      </div>
    </div>

    <div v-if="hotels.length > 0">
      <div v-for="hotel in hotels" :key="hotel.id" class="card mb-4 shadow-sm hotel-card">
        <div class="row g-0">
          <div class="col-md-4 position-relative">
            <img :src="'https://loremflickr.com/400/300/hotel?lock=' + hotel.id" class="img-fluid h-100 rounded-start" style="object-fit: cover;">
            <div class="position-absolute top-0 end-0 m-2 badge bg-primary fs-6">{{ hotel.rating }} ★</div>
          </div>
          <div class="col-md-8">
            <div class="card-body">
              <h3 class="card-title fw-bold text-primary">{{ hotel.name }}</h3>
              <p class="text-muted"><i class="bi bi-geo-alt-fill"></i> {{ hotel.address }}</p>
              <p class="card-text">{{ hotel.description }}</p>

              <div class="bg-light p-3 rounded">
                <h5 class="fw-bold mb-3">Választható szobák a megadott létszámra:</h5>
                <div v-for="room in hotel.rooms" :key="room.id" class="d-flex justify-content-between align-items-center border-bottom py-2">
                  <div>
                    <span class="fw-bold">{{ room.type }}</span> 
                    <small class="text-muted ms-2">({{ room.capacity }} főig)</small>
                  </div>
                  <div class="text-end">
                    <div class="fw-bold text-success fs-5">{{ room.price_per_night }} Ft / éj</div>
                    <button @click="bookRoom(hotel, room)" class="btn btn-sm btn-outline-primary mt-1">Kiválasztás</button>
                  </div>
                </div>
              </div>

              <div class="mt-4">
                <h6 class="fw-bold">Vendégek véleménye:</h6>
                <div v-for="rev in hotel.reviews.slice(0, 3)" :key="rev.id" class="small border-start border-3 ps-2 mb-2">
                  <span class="text-warning">★ {{ rev.rating }}</span> - <em>"{{ rev.comment }}"</em>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import API from '../api/api'

export default {
  data() {
    return {
      today: new Date().toISOString().split('T')[0],
      search: { destination: '', check_in: '', check_out: '', guests: 1 },
      hotels: []
    }
  },
  methods: {
    async performSearch() {
      try {
        const query = `?destination=${this.search.destination}&check_in=${this.search.check_in}&check_out=${this.search.check_out}&guests=${this.search.guests}`;
        const res = await fetch(`http://127.0.0.1:8000/hotels/search${query}`);
        this.hotels = await res.json();
      } catch (e) {
        alert("Hiba a keresés során!");
      }
    },
    async bookRoom(hotel, room) {
      if(!this.search.check_in || !this.search.check_out) {
        alert("Kérlek, válassz dátumot a keresőben!");
        return;
      }
      try {
        await API.createBooking({
          room_id: room.id,
          check_in: this.search.check_in,
          check_out: this.search.check_out,
          guests: this.search.guests
        });
        alert("Sikeres foglalás!");
        this.$router.push('/dashboard');
      } catch (e) {
        alert("Jelentkezz be a foglaláshoz!");
      }
    }
  }
}
</script>

<style scoped>
.hotel-card { transition: transform 0.2s; border: 1px solid #ddd; }
.hotel-card:hover { transform: translateY(-5px); border-color: #0d6efd; }
</style>