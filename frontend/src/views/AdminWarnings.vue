<template>
  <div class="d-flex bg-light min-vh-100">
    <AdminSidebar />
    <div class="main-content flex-grow-1 p-4" style="margin-left: 260px;">
      <div class="mb-3">
        <h2 class="fw-bold text-primary-dark m-0">{{ t('warnings.title') }}</h2>
        <p class="text-muted small mb-0">{{ t('warnings.desc') }}</p>
      </div>

      <div class="alert alert-warning border-0 rounded-3 small mb-4">
        <i class="bi bi-info-circle me-1"></i> {{ t('warnings.warning_levels') }}
      </div>

      <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0">
          <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-teal"></div>
          </div>

          <div v-else-if="warnedUsers.length === 0" class="text-center py-5 text-muted">
            <i class="bi bi-shield-check display-4"></i>
            <p class="mt-3">Nincsenek figyelmeztett felhasználók.</p>
          </div>

          <table v-else class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-3">{{ t('warnings.user') }}</th>
                <th class="text-center">{{ t('warnings.count') }}</th>
                <th class="text-center">{{ t('warnings.suspended_until') }}</th>
                <th class="text-center">{{ t('warnings.last_warning') }}</th>
                <th class="text-center">{{ t('admin.actions') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="w in warnedUsers" :key="w.user_id">
                <td class="ps-3">
                  <div v-if="w.user">
                    <div class="fw-bold small">{{ w.user.name }}</div>
                    <div class="text-muted small">{{ w.user.email }}</div>
                  </div>
                </td>
                <td class="text-center">
                  <span class="badge rounded-pill px-3 py-2" :class="getCountBadge(w.warning_count)">
                    {{ w.warning_count }}x
                  </span>
                </td>
                <td class="text-center">
                  <span v-if="w.user && w.user.suspended_until && new Date(w.user.suspended_until) > new Date()"
                    class="badge bg-danger text-white">
                    {{ formatDate(w.user.suspended_until) }}
                  </span>
                  <span v-else class="badge bg-success-light text-success">{{ t('warnings.not_suspended') }}</span>
                </td>
                <td class="text-center small text-muted">
                  {{ formatDate(w.last_warning_at) }}
                </td>
                <td class="text-center">
                  <button @click="viewDetails(w.user_id)" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-eye me-1"></i>
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
  name: 'AdminWarnings',
  components: { AdminSidebar },
  data() {
    return { warnedUsers: [], loading: true }
  },
  methods: {
    async fetchWarnings() {
      this.loading = true;
      try {
        this.warnedUsers = await api.getWarnings();
      } catch (e) { console.error(e); }
      finally { this.loading = false; }
    },

    getCountBadge(count) {
      if (count >= 5) return 'bg-danger text-white';
      if (count >= 4) return 'bg-warning text-dark';
      if (count >= 3) return 'bg-orange text-white';
      return 'bg-light text-dark border';
    },

    formatDate(dateStr) {
      if (!dateStr) return '-';
      return new Date(dateStr).toLocaleDateString(this.currentLocale === 'hu' ? 'hu-HU' : 'en-US', {
        year: 'numeric', month: 'short', day: '2-digit'
      });
    },

    async viewDetails(userId) {
      try {
        const data = await api.getUserWarnings(userId);
        let rows = data.warnings.map(w =>
          `<tr>
            <td class="small">${new Date(w.created_at).toLocaleDateString('hu-HU')}</td>
            <td class="small">${w.reason}</td>
            <td class="small">${w.admin ? w.admin.name : '-'}</td>
          </tr>`
        ).join('');

        const suspended = data.user.suspended_until && new Date(data.user.suspended_until) > new Date()
          ? `<div class="alert alert-danger small mt-2"><i class="bi bi-ban me-1"></i>Felfüggesztve: ${new Date(data.user.suspended_until).toLocaleDateString('hu-HU')}-ig</div>`
          : '';

        Swal.fire({
          title: `${data.user.name} — ${data.count} warn`,
          html: `${suspended}
            <table class="table table-sm text-start mt-2">
              <thead><tr><th>Dátum</th><th>Ok</th><th>Admin</th></tr></thead>
              <tbody>${rows}</tbody>
            </table>`,
          width: 600,
          confirmButtonColor: '#2a9d8f',
        });
      } catch (e) {
        Swal.fire({ icon: 'error', title: this.t('common.error'), confirmButtonColor: '#e76f51' });
      }
    }
  },
  mounted() { this.fetchWarnings(); }
}
</script>

<style scoped>
.text-primary-dark { color: #264653; }
.text-teal { color: #2a9d8f; }
.bg-success-light { background: #e8f5e9; }
.bg-orange { background: #e76f51; }
@media (max-width: 768px) { .main-content { margin-left: 0 !important; } }
</style>
