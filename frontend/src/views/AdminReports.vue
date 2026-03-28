<template>
  <div class="d-flex bg-light min-vh-100">
    <AdminSidebar />
    <div class="main-content flex-grow-1 p-4" style="margin-left: 260px;">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h2 class="fw-bold text-primary-dark m-0">{{ t('reports.title') }}</h2>
          <p class="text-muted small mb-0">{{ t('reports.desc') }}</p>
        </div>
        <span class="badge bg-danger text-white px-3 py-2 rounded-pill shadow-sm">
          {{ pendingCount }} {{ t('reports.pending') }}
        </span>
      </div>

      <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-0">
          <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-teal"></div>
          </div>

          <div v-else-if="reports.length === 0" class="text-center py-5 text-muted">
            <i class="bi bi-flag display-4"></i>
            <p class="mt-3">Nincsenek jelentések.</p>
          </div>

          <table v-else class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-3">{{ t('reports.reported_user') }}</th>
                <th>{{ t('reports.reporter') }}</th>
                <th>{{ t('reports.reason') }}</th>
                <th>{{ t('reports.description') }}</th>
                <th class="text-center">{{ t('reports.status') }}</th>
                <th class="text-center">{{ t('admin.actions') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in reports" :key="r.id">
                <td class="ps-3">
                  <div class="fw-bold small">{{ r.reported_user ? r.reported_user.name : '?' }}</div>
                  <div class="text-muted small">{{ r.reported_user ? r.reported_user.email : '' }}</div>
                </td>
                <td>
                  <div class="small">{{ r.reporter ? r.reporter.name : '?' }}</div>
                  <div class="text-muted small" v-if="r.hotel">{{ r.hotel.name }}</div>
                </td>
                <td>
                  <span class="badge bg-warning-light text-dark">{{ getReasonLabel(r.reason) }}</span>
                </td>
                <td class="small" style="max-width: 200px;">
                  {{ r.description || '-' }}
                </td>
                <td class="text-center">
                  <span :class="getStatusClass(r.status)" class="badge">
                    {{ t('reports.' + r.status) }}
                  </span>
                </td>
                <td class="text-center">
                  <div class="d-flex justify-content-center gap-1">
                    <button
                      v-if="r.status === 'pending'"
                      @click="issueWarningFromReport(r)"
                      class="btn btn-outline-danger btn-sm rounded-pill px-2"
                      :title="t('reports.issue_warning')">
                      <i class="bi bi-exclamation-triangle me-1"></i>Warn
                    </button>
                    <button
                      v-if="r.status === 'pending'"
                      @click="dismissReport(r.id)"
                      class="btn btn-outline-secondary btn-sm rounded-pill px-2">
                      <i class="bi bi-x-circle"></i>
                    </button>
                    <router-link
                      v-if="r.reported_user"
                      :to="'/chat?hotel=' + r.hotel_id + '&user=' + r.reported_user.id"
                      class="btn btn-outline-teal btn-sm rounded-pill px-2">
                      <i class="bi bi-chat-dots"></i>
                    </router-link>
                  </div>
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
  name: 'AdminReports',
  components: { AdminSidebar },
  data() {
    return { reports: [], loading: true }
  },
  computed: {
    pendingCount() {
      return this.reports.filter(r => r.status === 'pending').length;
    }
  },
  methods: {
    async fetchReports() {
      this.loading = true;
      try {
        this.reports = await api.getReports();
      } catch (e) { console.error(e); }
      finally { this.loading = false; }
    },

    getReasonLabel(reason) {
      const key = 'hotel_admin.report_reasons.' + reason;
      const label = this.t(key);
      return label !== key ? label : reason;
    },

    getStatusClass(status) {
      const map = {
        pending: 'bg-warning text-dark',
        reviewed: 'bg-info text-white',
        warned: 'bg-danger text-white',
        dismissed: 'bg-secondary text-white',
      };
      return map[status] || 'bg-secondary';
    },

    async issueWarningFromReport(report) {
      const { value: reason } = await Swal.fire({
        title: this.t('reports.issue_warning'),
        html:
          `<div class="text-start">
            <p class="small text-muted">${report.reported_user.name} — ${this.getReasonLabel(report.reason)}</p>
            <label class="form-label fw-semibold small">${this.t('warnings.warn_reason')}</label>
            <textarea id="swal-warn-reason" class="form-control" rows="3" placeholder="${this.t('warnings.warn_reason_placeholder')}">${report.description || ''}</textarea>
            <p class="text-muted small mt-2"><i class="bi bi-info-circle me-1"></i>${this.t('warnings.warning_levels')}</p>
          </div>`,
        showCancelButton: true,
        confirmButtonText: this.t('reports.issue_warning'),
        cancelButtonText: this.t('common.cancel'),
        confirmButtonColor: '#e76f51',
        preConfirm: () => {
          const r = document.getElementById('swal-warn-reason').value;
          if (!r.trim()) { Swal.showValidationMessage(this.t('warnings.warn_reason_placeholder')); return false; }
          return r;
        }
      });

      if (reason) {
        try {
          const result = await api.issueWarning({
            user_id: report.reported_user.id,
            reason: reason,
            report_id: report.id,
          });
          const msg = result.suspended_until ? this.t('warnings.warn_suspended') : this.t('warnings.warn_success');
          Swal.fire({ icon: 'success', title: msg, timer: 2500, showConfirmButton: false });
          this.fetchReports();
        } catch (e) {
          Swal.fire({ icon: 'error', title: this.t('common.error'), confirmButtonColor: '#e76f51' });
        }
      }
    },

    async dismissReport(id) {
      try {
        await api.updateReportStatus(id, 'dismissed');
        this.fetchReports();
      } catch (e) { console.error(e); }
    }
  },
  mounted() { this.fetchReports(); }
}
</script>

<style scoped>
.text-primary-dark { color: #264653; }
.text-teal { color: #2a9d8f; }
.bg-warning-light { background: #fff8e1; }
.btn-outline-teal { border: 2px solid #2a9d8f; color: #2a9d8f; }
.btn-outline-teal:hover { background: #2a9d8f; color: #fff; }
@media (max-width: 768px) { .main-content { margin-left: 0 !important; } }
</style>
