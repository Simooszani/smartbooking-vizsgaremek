<template>
  <div class="d-flex bg-light min-vh-100">
    <AdminSidebar />
    <div class="main-content flex-grow-1 p-4" style="margin-left: 260px;">
      
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="fw-bold text-dark m-0">Felhasználók Kezelése</h2>
          <p class="text-muted small mb-0">Regisztrált vendégek és jogosultságok</p>
        </div>
        <span class="badge bg-danger px-3 py-2 shadow-sm">{{ users.length }} regisztrált tag</span>
      </div>

      <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-4">Név / Email</th>
                <th>Szerepkör</th>
                <th>Regisztráció</th>
                <th class="text-center">Műveletek</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users" :key="user.id">
                <td class="ps-4">
                  <div class="fw-bold">{{ user.name }}</div>
                  <div class="text-muted small">{{ user.email }}</div>
                </td>
                <td>
                  <span :class="user.is_admin ? 'badge bg-primary' : 'badge bg-info text-dark'">
                    {{ user.is_admin ? 'Adminisztrátor' : 'Vendég' }}
                  </span>
                </td>
                <td>{{ new Date(user.created_at).toLocaleDateString('hu-HU') }}</td>
                <td class="text-center">
                  <button 
                    @click="handleDelete(user.id)" class="btn btn-outline-danger btn-sm rounded-pill" :disabled="user.id === currentUserId" >
                    <i class="bi bi-person-x-fill"></i> Törlés
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
        title: 'Biztosan törlöd?',
        text: "Ezzel a felhasználó összes adata elvész!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Igen, töröld!',
        cancelButtonText: 'Mégse'
      });

      if (result.isConfirmed) {
        try {
          await api.deleteUser(id);
          
          Swal.fire({
            title: 'Törölve!',
            text: 'A felhasználó eltávolítva.',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
          });
          
          this.fetchUsers();
        } catch (e) {
          Swal.fire({
            title: 'Hiba!',
            text: e.message || 'Nem sikerült a törlés.',
            icon: 'error'
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