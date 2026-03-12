<template>
  <div class="d-flex bg-light min-vh-100">
    <AdminSidebar />
    
    <div class="main-content flex-grow-1 p-4" style="margin-left: 260px;">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="fw-bold text-dark m-0">Szobák Kezelése</h2>
          <div class="mt-3 mb-4">
            <div class="input-group shadow-sm border rounded-pill overflow-hidden" style="max-width: 600px; background: white;">
              <span class="input-group-text bg-white border-0 ps-3">
                <i class="bi bi-search text-muted"></i>
              </span>
              <input 
                v-model="searchQuery" 
                type="text" 
                class="form-control border-0 py-2 pe-4" 
                placeholder="Keresés szálloda, cím vagy típus alapján..."
                style="box-shadow: none; min-width: 350px;"
              >
            </div>
          </div>
          <p class="text-muted small mb-0">Szálláshelyek típusai, árai és kapacitása</p>
        </div>
        <button @click="openCreateModal" class="btn btn-danger shadow-sm px-4 py-2">
          <i class="bi bi-plus-lg me-2"></i> Új szoba hozzáadása
        </button>
      </div>

      <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0 text-center" v-if="loading">
            <div class="p-5">
                <div class="spinner-border text-danger" role="status"></div>
                <p class="mt-2">Szobák betöltése...</p>
            </div>
        </div>

        <div class="card-body p-0" v-else>
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Azonosító</th>
                    <th>Szálloda</th> 
                    <th>Típus</th>
                    <th>Kapacitás</th>
                    <th>Ár / éjszaka</th>
                    <th class="text-center">Műveletek</th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="room in filteredRooms" :key="room.id">
                    <td class="ps-4 fw-bold text-danger">#{{ room.id }}</td>
                    
                    <td>
                      <div class="fw-bold text-dark">{{ room.hotel ? room.hotel.name : 'Ismeretlen Hotel' }}</div>
                      <small class="text-muted" v-if="room.hotel"><i class="bi bi-geo-alt"></i> {{ room.hotel.address }}</small>
                    </td>

                    <td><span :class="getRoomClass(room.type)" class="badge px-2 py-1">{{ room.type }}</span></td>
                    <td><i class="bi bi-people-fill me-1 text-muted"></i> {{ room.capacity }} fő</td>
                    <td class="fw-bold">{{ Number(room.price_per_night).toLocaleString('hu-HU') }} Ft</td>

                    <td class="text-center">
                      <div class="d-flex justify-content-center">
                        <button @click="openEditModal(room)" class="btn btn-outline-primary btn-sm rounded-pill me-2">
                          <i class="bi bi-pencil-square"></i>
                        </button>
                        <button @click="handleDelete(room.id)" class="btn btn-outline-danger btn-sm rounded-pill">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </td>
                </tr>
                <tr v-if="filteredRooms.length === 0">
                  <td colspan="6" class="text-center p-4 text-muted">Nincs találat a keresésre.</td>
                </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/api/api'
import AdminSidebar from '@/components/AdminSidebar.vue'
import Swal from 'sweetalert2'

export default {
  name: 'AdminRoomList',
  components: { AdminSidebar },
  data() {
    return {
      rooms: [],
      hotels: [],
      loading: true,
      searchQuery: ''
    }
  },
  computed: {
    filteredRooms() {
        if (!this.searchQuery) return this.rooms;
        const q = this.searchQuery.toLowerCase();
        return this.rooms.filter(room => 
          (room.hotel && room.hotel.name.toLowerCase().includes(q)) || 
          (room.type && room.type.toLowerCase().includes(q)) ||
          (room.hotel && room.hotel.address && room.hotel.address.toLowerCase().includes(q))
        );
    }
  },
  methods: {
    async fetchRooms() {
        this.loading = true;
        try {
            this.rooms = await api.getAllAdminRooms();
            
            const hotelData = await api.getAllHotels();
            console.log("Hotelek válasz:", hotelData);
            this.hotels = hotelData;

        } catch (error) {
            console.error("Hiba történt:", error);
        } finally {
            this.loading = false;
        }
    },

    async openCreateModal() {
        const hotelOptions = this.hotels.map(h => 
            `<option value="${h.id}">${h.name}</option>`
        ).join('');

        const { value: formValues } = await Swal.fire({
            title: 'Új szoba felvétele',
            html:
            `<select id="swal-hotel" class="swal2-input">
                <option value="" disabled selected>Válassz hotelt...</option>
                ${hotelOptions}
            </select>` +
            '<input id="swal-type" class="swal2-input" placeholder="Típus (pl: Deluxe)">' +
            '<input id="swal-price" type="number" class="swal2-input" placeholder="Ár / éjszaka">' +
            '<input id="swal-capacity" type="number" class="swal2-input" placeholder="Kapacitás (fő)">',
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Hozzáadás',
            preConfirm: () => {
              const hotelId = document.getElementById('swal-hotel').value;
              if (!hotelId) {
                  Swal.showValidationMessage('Választanod kell egy szállodát!');
                  return false;
              }
              return {
                  hotel_id: hotelId,
                  type: document.getElementById('swal-type').value,
                  price_per_night: document.getElementById('swal-price').value,
                  capacity: document.getElementById('swal-capacity').value
              }
            }
        });

      if (formValues) {
        try {
          await api.createRoom(formValues);
          Swal.fire('Sikeres!', 'A szoba hozzáadva.', 'success');
          this.fetchRooms();
        } catch (e) {
          Swal.fire('Hiba!', 'Nem sikerült elmenteni.', 'error');
        }
      }
    },

    async openEditModal(room) {
      const { value: formValues } = await Swal.fire({
        title: 'Szoba szerkesztése',
        html:
          `<input id="swal-type" class="swal2-input" value="${room.type}">` +
          `<input id="swal-price" type="number" class="swal2-input" value="${room.price_per_night}">` +
          `<input id="swal-capacity" type="number" class="swal2-input" value="${room.capacity}">`,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Mentés',
        confirmButtonColor: '#0d6efd',
        preConfirm: () => {
          return {
            type: document.getElementById('swal-type').value,
            price_per_night: document.getElementById('swal-price').value,
            capacity: document.getElementById('swal-capacity').value
          }
        }
      });

      if (formValues) {
        try {
          await api.updateRoom(room.id, formValues);
          Swal.fire('Frissítve!', 'A szoba adatai módosultak.', 'success');
          this.fetchRooms();
        } catch (e) {
          Swal.fire('Hiba!', 'Sikertelen frissítés.', 'error');
        }
      }
    },

    getRoomClass(type) {
        if (!type) return 'bg-secondary';
        const t = type.toLowerCase();
        if (t.includes('vip') || t.includes('suite')) return 'bg-warning text-dark';
        if (t.includes('economy')) return 'bg-secondary';
        if (t.includes('standard')) return 'bg-primary';
        if (t.includes('superior') || t.includes('family')) return 'bg-success';
        return 'bg-info text-dark';
    },

    async handleDelete(id) {
      const result = await Swal.fire({
        title: 'Biztosan törlöd?',
        text: "Ez a szoba véglegesen eltűnik!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Igen, töröld!'
      });

      if (result.isConfirmed) {
        try {
          await api.deleteRoom(id);
          this.fetchRooms();
          Swal.fire('Törölve!', '', 'success');
        } catch (e) {
          Swal.fire('Hiba!', 'Nem sikerült törölni.', 'error');
        }
      }
    }
  },
  async mounted() {
    await this.fetchRooms();
  }
}
</script>