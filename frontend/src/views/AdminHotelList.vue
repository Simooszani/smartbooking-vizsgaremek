<template>
  <div class="d-flex bg-light min-vh-100">
    <AdminSidebar />

    <div class="main-content flex-grow-1 p-3 p-md-4">
      <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
          <h2 class="fw-bold text-primary-dark m-0 h3-responsive">{{ t('admin.hotels_management') }}</h2>
          <p class="text-muted small mb-0">{{ t('admin.hotels_desc') }}</p>
        </div>
        <button @click="openCreateModal" class="btn btn-teal shadow-sm px-3 py-2 rounded-pill">
          <i class="bi bi-plus-lg me-2"></i>{{ t('admin.add_hotel') }}
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
            <p class="mt-2 text-muted">{{ t('common.loading') }}</p>
          </div>
        </div>

        <div class="card-body p-0 table-responsive" v-else>
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-4">{{ t('admin.hotel_name') }}</th>
                <th>{{ t('admin.hotel_address') }}</th>
                <th>{{ t('hotel.rating') }}</th>
                <th>{{ t('admin.rooms_count') }}</th>
                <th class="text-center">{{ t('admin.actions') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="hotel in filteredHotels" :key="hotel.id">
                <td class="ps-4">
                  <div class="d-flex align-items-center">
                    <div class="hotel-thumb me-3">
                      <img :src="'https://picsum.photos/seed/hotel' + hotel.id + '/60/60'" :alt="hotel.name">
                    </div>
                    <div>
                      <div class="fw-bold">{{ hotel.name }}</div>
                      <small class="text-muted" v-if="hotel.description">
                        {{ hotel.description.length > 60 ? hotel.description.substring(0, 60) + '...' : hotel.description }}
                      </small>
                    </div>
                  </div>
                </td>
                <td>
                  <i class="bi bi-geo-alt text-muted me-1"></i>
                  <span class="small">{{ hotel.address }}</span>
                </td>
                <td>
                  <span class="badge bg-accent-light text-dark">
                    <i class="bi bi-star-fill text-warning me-1"></i>{{ hotel.rating || '—' }}
                  </span>
                </td>
                <td>
                  <span class="badge bg-teal-light text-teal">
                    {{ hotel.rooms ? hotel.rooms.length : 0 }} {{ t('admin.pieces') }}
                  </span>
                </td>
                <td class="text-center">
                  <button @click="handleDelete(hotel.id)" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                    <i class="bi bi-trash me-1"></i>{{ t('common.delete') }}
                  </button>
                </td>
              </tr>
              <tr v-if="filteredHotels.length === 0">
                <td colspan="5" class="text-center p-4 text-muted">{{ t('admin.no_search_result') }}</td>
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
  name: 'AdminHotelList',
  components: { AdminSidebar },
  data() {
    return {
      hotels: [],
      loading: true,
      searchQuery: ''
    }
  },
  computed: {
    filteredHotels() {
      if (!this.searchQuery) return this.hotels;
      const q = this.searchQuery.toLowerCase();
      return this.hotels.filter(h =>
        h.name.toLowerCase().includes(q) ||
        (h.address && h.address.toLowerCase().includes(q))
      );
    }
  },
  methods: {
    async fetchHotels() {
      this.loading = true;
      try {
        const data = await api.getAllHotels();
        // Load rooms count - we get basic hotel data from public endpoint
        this.hotels = data;
      } catch (error) {
        console.error("Hiba:", error);
      } finally {
        this.loading = false;
      }
    },

    async openCreateModal() {
      const { value: formValues } = await Swal.fire({
        title: this.t('admin.add_hotel'),
        html:
          `<div class="text-start">
            <label class="form-label fw-semibold small">${this.t('admin.hotel_name_label')}</label>
            <input id="swal-name" class="form-control mb-2" placeholder="${this.t('admin.hotel_name_label')}">
            <label class="form-label fw-semibold small mt-2">${this.t('admin.hotel_address')}</label>
            <input id="swal-address" class="form-control mb-2" placeholder="${this.t('admin.hotel_address')}">
            <label class="form-label fw-semibold small mt-2">${this.t('admin.hotel_description')}</label>
            <textarea id="swal-description" class="form-control" rows="3" placeholder="${this.t('admin.hotel_description')}"></textarea>
          </div>`,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: this.t('admin.add'),
        cancelButtonText: this.t('common.cancel'),
        confirmButtonColor: '#2a9d8f',
        preConfirm: () => {
          const name = document.getElementById('swal-name').value;
          const address = document.getElementById('swal-address').value;
          if (!name || !address) {
            Swal.showValidationMessage(this.t('admin.hotel_required'));
            return false;
          }
          return {
            name: name,
            address: address,
            description: document.getElementById('swal-description').value
          }
        }
      });

      if (formValues) {
        try {
          await api.createHotel(formValues);
          Swal.fire({ icon: 'success', title: this.t('admin.success'), text: this.t('admin.hotel_added'), timer: 1500, showConfirmButton: false });
          this.fetchHotels();
        } catch (e) {
          Swal.fire({ icon: 'error', title: this.t('admin.error'), text: this.t('admin.hotel_add_error'), confirmButtonColor: '#e76f51' });
        }
      }
    },

    async handleDelete(id) {
      const result = await Swal.fire({
        title: this.t('admin.delete_confirm'),
        text: this.t('admin.hotel_delete_confirm'),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e76f51',
        confirmButtonText: this.t('admin.yes_delete'),
        cancelButtonText: this.t('admin.cancel')
      });

      if (result.isConfirmed) {
        try {
          await api.deleteHotel(id);
          Swal.fire({ icon: 'success', title: this.t('admin.deleted'), text: this.t('admin.hotel_deleted'), timer: 1500, showConfirmButton: false });
          this.fetchHotels();
        } catch (e) {
          Swal.fire({ icon: 'error', title: this.t('admin.error'), text: this.t('admin.delete_error'), confirmButtonColor: '#e76f51' });
        }
      }
    }
  },
  mounted() {
    this.fetchHotels();
  }
}
</script>

<style scoped>
.text-primary-dark { color: #264653; }
.text-teal { color: #2a9d8f; }
.btn-teal { background: #2a9d8f; color: #fff; border: none; font-weight: 600; }
.btn-teal:hover { background: #238b7e; color: #fff; }
.bg-teal-light { background: #e8f8f5; }
.bg-accent-light { background: #fdf5e6; }
.hotel-thumb {
  width: 45px; height: 45px; border-radius: 10px; overflow: hidden;
}
.hotel-thumb img { width: 100%; height: 100%; object-fit: cover; }
.main-content { margin-left: 260px; }
.h3-responsive { font-size: 1.5rem; }
@media (max-width: 767.98px) {
  .main-content { margin-left: 0 !important; padding-top: 70px !important; }
  .h3-responsive { font-size: 1.15rem; }
}
</style>
