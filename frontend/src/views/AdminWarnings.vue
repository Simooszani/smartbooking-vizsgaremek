<template>
  <div class="d-flex bg-light min-vh-100">
    <AdminSidebar />
    <div class="main-content flex-grow-1 p-3 p-md-4">
      <div class="mb-3">
        <h2 class="fw-bold text-primary-dark m-0 h3-responsive">{{ t('warnings.title') }}</h2>
        <p class="text-muted small mb-0">{{ t('warnings.desc') }}</p>
      </div>

      <div class="alert alert-warning border-0 rounded-3 small mb-4">
        <i class="bi bi-info-circle me-1"></i> {{ t('warnings.warning_levels') }}
        <span class="d-block mt-1 text-muted" style="font-size: 0.75rem;">{{ currentLocale === 'hu' ? '1 figyelmeztetés 3 hónap után automatikusan lejár.' : 'A single warning auto-expires after 3 months.' }}</span>
      </div>

      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-teal"></div>
      </div>

      <div v-else-if="warnedUsers.length === 0" class="text-center py-5 text-muted">
        <i class="bi bi-shield-check display-4"></i>
        <p class="mt-3">{{ currentLocale === 'hu' ? 'Nincsenek aktív figyelmeztetések.' : 'No active warnings.' }}</p>
      </div>

      <template v-else>
        <!-- Desktop table -->
        <div class="card shadow-sm border-0 rounded-3 d-none d-md-block">
          <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="ps-3">{{ t('warnings.user') }}</th>
                  <th class="text-center">{{ t('warnings.count') }}</th>
                  <th class="text-center">{{ t('warnings.suspended_until') }}</th>
                  <th class="text-center">{{ t('warnings.last_warning') }}</th>
                  <th class="text-center" style="white-space: nowrap;">{{ t('admin.actions') }}</th>
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
                      class="badge bg-danger text-white" style="white-space: nowrap;">
                      {{ formatDate(w.user.suspended_until) }}
                    </span>
                    <span v-else class="badge bg-success-light text-success" style="white-space: nowrap;">{{ t('warnings.not_suspended') }}</span>
                  </td>
                  <td class="text-center small text-muted" style="white-space: nowrap;">
                    {{ formatDate(w.last_warning_at) }}
                  </td>
                  <td class="text-center" style="white-space: nowrap;">
                    <button @click="viewDetails(w.user_id)" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                      <i class="bi bi-eye me-1"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Mobile card layout -->
        <div class="d-md-none">
          <div v-for="w in warnedUsers" :key="'m'+w.user_id" class="card shadow-sm border-0 rounded-3 mb-3">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div v-if="w.user">
                  <div class="fw-bold small">{{ w.user.name }}</div>
                  <div class="text-muted small">{{ w.user.email }}</div>
                </div>
                <span class="badge rounded-pill px-3 py-2" :class="getCountBadge(w.warning_count)">
                  {{ w.warning_count }}x
                </span>
              </div>
              <div class="d-flex justify-content-between align-items-center small mb-2">
                <div>
                  <span v-if="w.user && w.user.suspended_until && new Date(w.user.suspended_until) > new Date()"
                    class="badge bg-danger text-white">{{ formatDate(w.user.suspended_until) }}</span>
                  <span v-else class="badge bg-success-light text-success">{{ t('warnings.not_suspended') }}</span>
                </div>
                <span class="text-muted">{{ formatDate(w.last_warning_at) }}</span>
              </div>
              <button @click="viewDetails(w.user_id)" class="btn btn-outline-primary btn-sm rounded-pill px-3 w-100">
                <i class="bi bi-eye me-1"></i>{{ currentLocale === 'hu' ? 'Részletek' : 'Details' }}
              </button>
            </div>
          </div>
        </div>
      </template>
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
            <td class="small" style="white-space:nowrap;">${new Date(w.created_at).toLocaleDateString('hu-HU')}</td>
            <td class="small">${w.reason}</td>
            <td class="small" style="white-space:nowrap;">${w.admin ? w.admin.name : '-'}</td>
            <td class="text-center">
              <button class="btn btn-danger btn-sm px-2 py-1 delete-warn-btn" data-id="${w.id}" style="cursor:pointer;z-index:10;position:relative;">
                <i class="bi bi-trash" style="pointer-events:none;"></i>
              </button>
            </td>
          </tr>`
        ).join('');

        const suspended = data.user.suspended_until && new Date(data.user.suspended_until) > new Date()
          ? `<div class="alert alert-danger small mt-2"><i class="bi bi-ban me-1"></i>${this.currentLocale === 'hu' ? 'Felfüggesztve' : 'Suspended'}: ${new Date(data.user.suspended_until).toLocaleDateString('hu-HU')}-ig</div>`
          : '';

        const deleteLabel = this.currentLocale === 'hu' ? 'Törlés' : 'Delete';

        await Swal.fire({
          title: `${data.user.name} — ${data.count} warn`,
          html: `${suspended}
            <div class="table-responsive">
            <table class="table table-sm text-start mt-2">
              <thead><tr><th>${this.currentLocale === 'hu' ? 'Dátum' : 'Date'}</th><th>${this.currentLocale === 'hu' ? 'Ok' : 'Reason'}</th><th>Admin</th><th>${deleteLabel}</th></tr></thead>
              <tbody>${rows}</tbody>
            </table>
            </div>`,
          width: 700,
          confirmButtonColor: '#2a9d8f',
          didOpen: () => {
            const popup = Swal.getPopup();
            popup.querySelectorAll('.delete-warn-btn').forEach(btn => {
              btn.addEventListener('click', async () => {
                const warnId = btn.getAttribute('data-id');
                try {
                  await api.deleteWarning(warnId);
                  btn.closest('tr').remove();
                  const remaining = popup.querySelectorAll('tbody tr').length;
                  Swal.getTitle().textContent = `${data.user.name} — ${remaining} warn`;
                } catch (e) {
                  // silent
                }
              });
            });
          }
        });

        this.fetchWarnings();
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
.main-content { margin-left: 260px; }
.h3-responsive { font-size: 1.5rem; }
@media (max-width: 767.98px) {
  .main-content { margin-left: 0 !important; padding-top: 70px !important; }
  .h3-responsive { font-size: 1.15rem; }
}
</style>
