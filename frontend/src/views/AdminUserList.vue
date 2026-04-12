<template>
  <div class="d-flex bg-light min-vh-100">
    <AdminSidebar />
    <div class="main-content flex-grow-1 p-3 p-md-4">

      <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
          <h2 class="fw-bold text-primary-dark m-0 h3-responsive">{{ t('admin.users_management') }}</h2>
          <p class="text-muted small mb-0">{{ t('admin.users_desc') }}</p>
        </div>
        <span class="badge bg-teal text-white px-3 py-2 rounded-pill shadow-sm">
          {{ users.length }} {{ t('admin.registered') }}
        </span>
      </div>

      <!-- Search + role filter -->
      <div class="mb-4 d-flex flex-wrap gap-2 align-items-center">
        <div class="input-group shadow-sm border rounded-pill overflow-hidden flex-grow-1" style="max-width: 500px; min-width: 200px; background: white;">
          <span class="input-group-text bg-white border-0 ps-3">
            <i class="bi bi-search text-muted"></i>
          </span>
          <input v-model="searchQuery" type="text" class="form-control border-0 py-2"
            :placeholder="t('admin.search_users')" style="box-shadow: none;">
        </div>
        <select v-model="roleFilter" class="form-select rounded-pill shadow-sm" style="max-width: 200px;">
          <option value="">{{ t('admin.all_roles') }}</option>
          <option value="user">{{ t('admin.guest_role') }}</option>
          <option value="hotel_admin">{{ t('admin.hotel_admin_role') }}</option>
          <option value="admin">{{ t('admin.admin_role') }}</option>
          <option value="super_admin">{{ t('admin.super_admin_role') }}</option>
        </select>
      </div>

      <!-- Desktop table -->
      <div class="card shadow-sm border-0 rounded-3 d-none d-md-block">
        <div class="card-body p-0 table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-4">{{ t('admin.name_email') }}</th>
                <th>{{ t('admin.role') }}</th>
                <th>{{ t('admin.registration') }}</th>
                <th class="text-center">{{ t('admin.actions') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in filteredUsers" :key="user.id">
                <td class="ps-4">
                  <div class="d-flex align-items-center">
                    <div class="avatar-circle me-2" :class="getAvatarClass(user.role)">
                      {{ user.name.charAt(0) }}
                    </div>
                    <div>
                      <div class="fw-bold small">{{ user.name }}</div>
                      <div class="text-muted small">{{ user.email }}</div>
                      <div v-if="user.role === 'hotel_admin' && user.managed_hotel" class="text-teal small">
                        <i class="bi bi-building me-1"></i>{{ user.managed_hotel.name }}
                      </div>
                      <div v-if="user.role === 'admin' && user.admin_city" class="text-info small">
                        <i class="bi bi-geo-alt me-1"></i>{{ user.admin_city }}
                      </div>
                    </div>
                  </div>
                </td>
                <td>
                  <span :class="getRoleBadgeClass(user.role)" class="badge">
                    {{ getRoleLabel(user.role) }}
                  </span>
                </td>
                <td class="small text-muted">
                  {{ new Date(user.created_at).toLocaleDateString(currentLocale === 'hu' ? 'hu-HU' : 'en-US') }}
                </td>
                <td class="text-center">
                  <div class="d-flex justify-content-center gap-1">
                    <button
                      @click="openRoleModal(user)"
                      class="btn btn-outline-primary btn-sm rounded-pill px-3"
                      :disabled="user.id === currentUserId || (user.role === 'super_admin' && currentUserRole !== 'super_admin')">
                      <i class="bi bi-shield-check me-1"></i>{{ t('admin.change_role') }}
                    </button>
                    <button
                      @click="handleDelete(user.id)"
                      class="btn btn-outline-danger btn-sm rounded-pill px-3"
                      :disabled="user.id === currentUserId || (user.role === 'super_admin' && currentUserRole !== 'super_admin')">
                      <i class="bi bi-person-x"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Mobile card layout -->
      <div class="d-md-none">
        <div v-for="user in filteredUsers" :key="'m'+user.id" class="card shadow-sm border-0 rounded-3 mb-3">
          <div class="card-body p-3">
            <div class="d-flex align-items-center mb-2">
              <div class="avatar-circle me-2" :class="getAvatarClass(user.role)">
                {{ user.name.charAt(0) }}
              </div>
              <div class="flex-grow-1">
                <div class="fw-bold small">{{ user.name }}</div>
                <div class="text-muted small">{{ user.email }}</div>
              </div>
              <span :class="getRoleBadgeClass(user.role)" class="badge ms-2">
                {{ getRoleLabel(user.role) }}
              </span>
            </div>
            <div v-if="user.role === 'hotel_admin' && user.managed_hotel" class="text-teal small mb-2">
              <i class="bi bi-building me-1"></i>{{ user.managed_hotel.name }}
            </div>
            <div v-if="user.role === 'admin' && user.admin_city" class="text-info small mb-2">
              <i class="bi bi-geo-alt me-1"></i>{{ user.admin_city }}
            </div>
            <div class="d-flex gap-2 mt-2">
              <button
                @click="openRoleModal(user)"
                class="btn btn-outline-primary btn-sm rounded-pill px-3 flex-grow-1"
                :disabled="user.id === currentUserId || (user.role === 'super_admin' && currentUserRole !== 'super_admin')">
                <i class="bi bi-shield-check me-1"></i>{{ t('admin.change_role') }}
              </button>
              <button
                @click="handleDelete(user.id)"
                class="btn btn-outline-danger btn-sm rounded-pill px-3"
                :disabled="user.id === currentUserId || (user.role === 'super_admin' && currentUserRole !== 'super_admin')">
                <i class="bi bi-person-x"></i>
              </button>
            </div>
          </div>
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
  name: 'AdminUserList',
  components: { AdminSidebar },
  data() {
    return {
      users: [],
      hotels: [],
      currentUserId: null,
      currentUserRole: null,
      searchQuery: '',
      roleFilter: ''
    }
  },
  computed: {
    filteredUsers() {
      const q = this.searchQuery.trim().toLowerCase();
      return this.users.filter(u => {
        if (this.roleFilter && u.role !== this.roleFilter) return false;
        if (!q) return true;
        return u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q);
      });
    },
    cities() {
      const citySet = new Set();
      this.hotels.forEach(h => {
        if (h.address) {
          const match = h.address.match(/\d{4}\s+([^,]+)/);
          if (match) citySet.add(match[1].trim());
        }
      });
      return Array.from(citySet).sort();
    }
  },
  methods: {
    async fetchUsers() {
      try {
        this.users = await api.getAllUsers();
        this.hotels = await api.getAllHotels();
      } catch (error) {
        console.error("Hiba:", error);
      }
    },

    getRoleLabel(role) {
      const map = {
        super_admin: this.t('admin.super_admin_role'),
        admin: this.t('admin.admin_role'),
        hotel_admin: this.t('admin.hotel_admin_role'),
        user: this.t('admin.guest_role'),
      };
      return map[role] || role;
    },

    getRoleBadgeClass(role) {
      const map = {
        super_admin: 'bg-danger',
        admin: 'bg-teal',
        hotel_admin: 'bg-warning text-dark',
        user: 'bg-light text-dark border',
      };
      return map[role] || 'bg-secondary';
    },

    getAvatarClass(role) {
      if (role === 'super_admin') return 'avatar-super';
      if (role === 'admin') return 'avatar-admin';
      if (role === 'hotel_admin') return 'avatar-hotel';
      return '';
    },

    async openRoleModal(user) {
      let roleOptions = '';
      const roles = [
        { value: 'user', label: this.t('admin.guest_role') },
        { value: 'hotel_admin', label: this.t('admin.hotel_admin_role') },
      ];

      if (this.currentUserRole === 'super_admin') {
        roles.push({ value: 'admin', label: this.t('admin.admin_role') });
      }

      roleOptions = roles.map(r =>
        `<option value="${r.value}" ${user.role === r.value ? 'selected' : ''}>${r.label}</option>`
      ).join('');

      const hotelOptions = this.hotels.map(h =>
        `<option value="${h.name} — ${h.address}">${h.name} — ${h.address}</option>`
      ).join('');

      const cityOptions = this.cities.map(c =>
        `<option value="${c}" ${user.admin_city === c ? 'selected' : ''}>${c}</option>`
      ).join('');

      const { value: formValues } = await Swal.fire({
        title: this.t('admin.change_role'),
        html:
          `<div class="text-start">
            <label class="form-label fw-semibold small">${this.t('admin.role')}</label>
            <select id="swal-role" class="form-select mb-3" onchange="
              document.getElementById('hotel-select-group').style.display = this.value === 'hotel_admin' ? 'block' : 'none';
              document.getElementById('city-select-group').style.display = this.value === 'admin' ? 'block' : 'none';
            ">
              ${roleOptions}
            </select>
            <div id="hotel-select-group" style="display: ${user.role === 'hotel_admin' ? 'block' : 'none'}">
              <label class="form-label fw-semibold small">${this.t('admin.hotel_name')}</label>
              <input id="swal-hotel-search" class="form-control" list="role-hotel-list" placeholder="${this.t('admin.search_hotel')}" value="${user.managed_hotel ? user.managed_hotel.name + ' — ' + (user.managed_hotel.address || '') : ''}">
              <datalist id="role-hotel-list">${hotelOptions}</datalist>
            </div>
            <div id="city-select-group" style="display: ${user.role === 'admin' ? 'block' : 'none'}">
              <label class="form-label fw-semibold small">${this.currentLocale === 'hu' ? 'Terület (város)' : 'Area (city)'}</label>
              <select id="swal-city" class="form-select">
                <option value="">${this.currentLocale === 'hu' ? 'Összes város (nincs szűrés)' : 'All cities (no filter)'}</option>
                ${cityOptions}
              </select>
              <small class="text-muted">${this.currentLocale === 'hu' ? 'Ha kiválasztasz várost, az admin csak az adott város hoteljeihez tartozó chateket látja.' : 'If you select a city, the admin will only see chats from hotels in that city.'}</small>
            </div>
          </div>`,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: this.t('admin.save'),
        cancelButtonText: this.t('common.cancel'),
        confirmButtonColor: '#2a9d8f',
        preConfirm: () => {
          const role = document.getElementById('swal-role').value;
          let managed_hotel_id = null;
          let admin_city = null;

          if (role === 'hotel_admin') {
            const searchVal = document.getElementById('swal-hotel-search').value;
            const hotel = this.hotels.find(h => searchVal.includes(h.name));
            if (!hotel) {
              Swal.showValidationMessage(this.t('admin.select_hotel'));
              return false;
            }
            managed_hotel_id = hotel.id;
          }

          if (role === 'admin') {
            admin_city = document.getElementById('swal-city').value || null;
          }

          return { role, managed_hotel_id, admin_city };
        }
      });

      if (formValues) {
        try {
          await api.updateUserRole(user.id, formValues);
          Swal.fire({ icon: 'success', title: this.t('admin.success'), text: this.t('admin.role_updated'), timer: 1500, showConfirmButton: false });
          this.fetchUsers();
        } catch (e) {
          Swal.fire({ icon: 'error', title: this.t('admin.error'), text: e.message || this.t('admin.role_update_error'), confirmButtonColor: '#e76f51' });
        }
      }
    },

    async handleDelete(id) {
      const result = await Swal.fire({
        title: this.t('admin.user_delete_title'),
        text: this.t('admin.user_delete_text'),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e76f51',
        cancelButtonColor: '#6c757d',
        confirmButtonText: this.t('admin.yes_delete'),
        cancelButtonText: this.t('admin.cancel')
      });

      if (result.isConfirmed) {
        try {
          await api.deleteUser(id);
          Swal.fire({ title: this.t('admin.deleted'), text: this.t('admin.deleted_text'), icon: 'success', timer: 1500, showConfirmButton: false });
          this.fetchUsers();
        } catch (e) {
          Swal.fire({ title: this.t('admin.error'), text: e.message || this.t('admin.delete_error'), icon: 'error', confirmButtonColor: '#e76f51' });
        }
      }
    }
  },
  mounted() {
    this.fetchUsers();
    const userData = localStorage.getItem('user');
    if (userData) {
      const u = JSON.parse(userData);
      this.currentUserId = u.id;
      this.currentUserRole = u.role;
    }
  }
}
</script>

<style scoped>
.text-primary-dark { color: #264653; }
.text-teal { color: #2a9d8f; }
.bg-teal { background: #2a9d8f; }
.avatar-circle {
  width: 35px; height: 35px;
  background: linear-gradient(135deg, #264653, #2a9d8f);
  color: white; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: bold; font-size: 0.8rem; flex-shrink: 0;
}
.avatar-super { background: linear-gradient(135deg, #dc3545, #ff6b6b) !important; }
.avatar-admin { background: linear-gradient(135deg, #e76f51, #f4a261) !important; }
.avatar-hotel { background: linear-gradient(135deg, #e9c46a, #f4a261) !important; }
.main-content { margin-left: 260px; }
.h3-responsive { font-size: 1.5rem; }
@media (max-width: 767.98px) {
  .main-content { margin-left: 0 !important; padding-top: 70px !important; }
  .h3-responsive { font-size: 1.15rem; }
}
</style>
