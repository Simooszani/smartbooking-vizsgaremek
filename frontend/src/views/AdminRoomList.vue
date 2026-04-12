<template>
  <div class="d-flex bg-light min-vh-100">
    <AdminSidebar />

    <div class="main-content flex-grow-1 p-3 p-md-4">
      <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
          <h2 class="fw-bold text-primary-dark m-0 h3-responsive">{{ t('admin.rooms_management') }}</h2>
          <p class="text-muted small mb-0">{{ t('admin.rooms_desc') }}</p>
        </div>
        <button @click="openCreateModal" class="btn btn-teal shadow-sm px-3 py-2 rounded-pill">
          <i class="bi bi-plus-lg me-2"></i>{{ t('admin.add_room') }}
        </button>
      </div>

      <!-- Search -->
      <div class="mb-4">
        <div class="input-group shadow-sm border rounded-pill overflow-hidden" style="max-width: 500px; background: white;">
          <span class="input-group-text bg-white border-0 ps-3">
            <i class="bi bi-search text-muted"></i>
          </span>
          <input v-model="searchQuery" type="text" class="form-control border-0 py-2"
            :placeholder="t('admin.search_placeholder')" style="box-shadow: none;">
        </div>
      </div>

      <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0 text-center" v-if="loading">
          <div class="p-5">
            <div class="spinner-border text-teal" role="status"></div>
            <p class="mt-2 text-muted">{{ t('admin.loading_rooms') }}</p>
          </div>
        </div>

        <div class="card-body p-0 table-responsive" v-else>
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-4">{{ t('admin.room_id') }}</th>
                <th>{{ t('admin.hotel_name') }}</th>
                <th>{{ t('admin.type') }}</th>
                <th>{{ t('admin.capacity') }}</th>
                <th>{{ t('admin.price_per_night') }}</th>
                <th class="text-center">{{ t('admin.actions') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="room in filteredRooms" :key="room.id">
                <td class="ps-4 fw-bold text-teal">#{{ room.id }}</td>
                <td>
                  <div class="fw-bold text-dark small">{{ room.hotel ? room.hotel.name : t('admin.unknown_hotel') }}</div>
                  <small class="text-muted" v-if="room.hotel"><i class="bi bi-geo-alt"></i> {{ room.hotel.address }}</small>
                </td>
                <td><span :class="getRoomClass(room.type)" class="badge px-2 py-1">{{ room.type }}</span></td>
                <td><i class="bi bi-people-fill me-1 text-muted"></i> {{ room.capacity }} {{ t('admin.person') }}</td>
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
                <td colspan="6" class="text-center p-4 text-muted">{{ t('admin.no_search_result') }}</td>
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
        this.hotels = await api.getAllHotels();
      } catch (error) {
        console.error("Hiba:", error);
      } finally {
        this.loading = false;
      }
    },

    async openCreateModal() {
      // Build searchable hotel input with datalist
      const hotelOptions = this.hotels.map(h =>
        `<option value="${h.name}" data-id="${h.id}">`
      ).join('');

      const { value: formValues } = await Swal.fire({
        title: this.t('admin.new_room'),
        html:
          `<div class="text-start">
            <label class="form-label fw-semibold small">${this.t('admin.hotel_name')}</label>
            <input id="swal-hotel-search" class="form-control mb-2" list="hotel-list" placeholder="${this.t('admin.search_hotel')}">
            <datalist id="hotel-list">${hotelOptions}</datalist>
            <label class="form-label fw-semibold small mt-2">${this.t('admin.type')}</label>
            <input id="swal-type" class="form-control mb-2" placeholder="${this.t('admin.room_type_placeholder')}">
            <label class="form-label fw-semibold small mt-2">${this.t('admin.price_per_night')}</label>
            <input id="swal-price" type="number" class="form-control mb-2" placeholder="${this.t('admin.price_placeholder')}">
            <label class="form-label fw-semibold small mt-2">${this.t('admin.capacity')}</label>
            <input id="swal-capacity" type="number" class="form-control" placeholder="${this.t('admin.capacity_placeholder')}">
          </div>`,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: this.t('admin.add'),
        cancelButtonText: this.t('common.cancel'),
        confirmButtonColor: '#2a9d8f',
        preConfirm: () => {
          const hotelName = document.getElementById('swal-hotel-search').value;
          const hotel = this.hotels.find(h => h.name === hotelName);
          if (!hotel) {
            Swal.showValidationMessage(this.t('admin.hotel_required'));
            return false;
          }
          return {
            hotel_id: hotel.id,
            type: document.getElementById('swal-type').value,
            price_per_night: document.getElementById('swal-price').value,
            capacity: document.getElementById('swal-capacity').value
          }
        }
      });

      if (formValues) {
        try {
          await api.createRoom(formValues);
          Swal.fire({ icon: 'success', title: this.t('admin.success'), text: this.t('admin.room_added'), timer: 1500, showConfirmButton: false });
          this.fetchRooms();
        } catch (e) {
          Swal.fire({ icon: 'error', title: this.t('admin.error'), text: this.t('admin.save_error'), confirmButtonColor: '#e76f51' });
        }
      }
    },

    async openEditModal(room) {
      const { value: formValues } = await Swal.fire({
        title: this.t('admin.edit_room'),
        html:
          `<div class="text-start">
            <label class="form-label fw-semibold small">${this.t('admin.type')}</label>
            <input id="swal-type" class="form-control mb-2" value="${room.type}">
            <label class="form-label fw-semibold small mt-2">${this.t('admin.price_per_night')}</label>
            <input id="swal-price" type="number" class="form-control mb-2" value="${room.price_per_night}">
            <label class="form-label fw-semibold small mt-2">${this.t('admin.capacity')}</label>
            <input id="swal-capacity" type="number" class="form-control" value="${room.capacity}">
          </div>`,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: this.t('admin.save'),
        cancelButtonText: this.t('common.cancel'),
        confirmButtonColor: '#2a9d8f',
        preConfirm: () => ({
          type: document.getElementById('swal-type').value,
          price_per_night: document.getElementById('swal-price').value,
          capacity: document.getElementById('swal-capacity').value
        })
      });

      if (formValues) {
        try {
          await api.updateRoom(room.id, formValues);
          Swal.fire({ icon: 'success', title: this.t('admin.updated'), text: this.t('admin.room_updated'), timer: 1500, showConfirmButton: false });
          this.fetchRooms();
        } catch (e) {
          Swal.fire({ icon: 'error', title: this.t('admin.error'), text: this.t('admin.save_error'), confirmButtonColor: '#e76f51' });
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
        title: this.t('admin.delete_confirm'),
        text: this.t('admin.room_delete_confirm'),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e76f51',
        confirmButtonText: this.t('admin.yes_delete'),
        cancelButtonText: this.t('admin.cancel')
      });

      if (result.isConfirmed) {
        try {
          await api.deleteRoom(id);
          this.fetchRooms();
          Swal.fire({ icon: 'success', title: this.t('admin.deleted'), text: this.t('admin.room_deleted'), timer: 1500, showConfirmButton: false });
        } catch (e) {
          Swal.fire({ icon: 'error', title: this.t('admin.error'), text: this.t('admin.delete_error'), confirmButtonColor: '#e76f51' });
        }
      }
    }
  },
  async mounted() {
    await this.fetchRooms();
  }
}
</script>

<style scoped>
.text-primary-dark { color: #264653; }
.text-teal { color: #2a9d8f; }
.btn-teal { background: #2a9d8f; color: #fff; border: none; font-weight: 600; }
.btn-teal:hover { background: #238b7e; color: #fff; }
.main-content { margin-left: 260px; }
.h3-responsive { font-size: 1.5rem; }
@media (max-width: 767.98px) {
  .main-content { margin-left: 0 !important; padding-top: 70px !important; }
  .h3-responsive { font-size: 1.15rem; }
}
</style>
