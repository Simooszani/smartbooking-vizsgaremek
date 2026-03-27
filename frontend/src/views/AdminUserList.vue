<template>
  <div class="d-flex bg-light min-vh-100">
    <AdminSidebar />
    <div class="main-content flex-grow-1 p-4" style="margin-left: 260px;">

      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="fw-bold text-primary-dark m-0">{{ t('admin.users_management') }}</h2>
          <p class="text-muted small mb-0">{{ t('admin.users_desc') }}</p>
        </div>
        <span class="badge bg-teal text-white px-3 py-2 rounded-pill shadow-sm">
          {{ users.length }} {{ t('admin.registered') }}
        </span>
      </div>

      <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0">
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
              <tr v-for="user in users" :key="user.id">
                <td class="ps-4">
                  <div class="d-flex align-items-center">
                    <div class="avatar-circle me-2" :class="user.is_admin ? 'avatar-admin' : ''">
                      {{ user.name.charAt(0) }}
                    </div>
                    <div>
                      <div class="fw-bold small">{{ user.name }}</div>
                      <div class="text-muted small">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td>
                  <span :class="user.is_admin ? 'badge bg-teal' : 'badge bg-light text-dark border'">
                    {{ user.is_admin ? t('admin.admin_role') : t('admin.guest_role') }}
                  </span>
                </td>
                <td class="small text-muted">
                  {{ new Date(user.created_at).toLocaleDateString(currentLocale === 'hu' ? 'hu-HU' : 'en-US') }}
                </td>
                <td class="text-center">
                  <button
                    @click="handleDelete(user.id)"
                    class="btn btn-outline-danger btn-sm rounded-pill px-3"
                    :disabled="user.id === currentUserId">
                    <i class="bi bi-person-x me-1"></i>{{ t('common.delete') }}
                  </button>
                </td>
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
  name: 'AdminUserList',
  components: { AdminSidebar },
  data() {
    return {
      users: [],
      currentUserId: null
    }
  },
  methods: {
    async fetchUsers() {
      try {
        this.users = await api.getAllUsers();
      } catch (error) {
        console.error("Hiba a felhasználók lekérésekor", error);
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
          Swal.fire({
            title: this.t('admin.deleted'),
            text: this.t('admin.deleted_text'),
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
          });
          this.fetchUsers();
        } catch (e) {
          Swal.fire({
            title: this.t('admin.error'),
            text: e.message || this.t('admin.delete_error'),
            icon: 'error',
            confirmButtonColor: '#e76f51'
          });
        }
      }
    }
  },
  mounted() {
    this.fetchUsers();
    const userData = localStorage.getItem('user');
    if (userData) {
      this.currentUserId = JSON.parse(userData).id;
    }
  }
}
</script>

<style scoped>
.text-primary-dark { color: #264653; }
.bg-teal { background: #2a9d8f; }
.avatar-circle {
  width: 35px; height: 35px;
  background: linear-gradient(135deg, #264653, #2a9d8f);
  color: white; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: bold; font-size: 0.8rem;
}
.avatar-admin {
  background: linear-gradient(135deg, #e76f51, #f4a261) !important;
}
@media (max-width: 768px) {
  .main-content { margin-left: 0 !important; }
}
</style>
