<template>
  <div class="container py-5" style="max-width: 700px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold text-primary-dark m-0">
          <i class="bi bi-bell me-2"></i>{{ t('notifications.title') }}
        </h2>
        <p class="text-muted small mb-0" v-if="unreadCount > 0">
          {{ unreadCount }} {{ t('notifications.unread') }}
        </p>
      </div>
      <button
        v-if="notifications.length > 0"
        @click="markAllRead"
        class="btn btn-outline-teal btn-sm rounded-pill px-3">
        <i class="bi bi-check-all me-1"></i>{{ t('notifications.mark_all_read') }}
      </button>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-teal" role="status"></div>
    </div>

    <div v-else-if="notifications.length === 0" class="text-center py-5">
      <i class="bi bi-bell-slash display-1 text-muted"></i>
      <h5 class="mt-3 text-muted">{{ t('notifications.empty') }}</h5>
    </div>

    <div v-else>
      <div
        v-for="n in notifications" :key="n.id"
        class="notification-card mb-3"
        :class="[{ 'notification-unread': !n.is_read }, n.type === 'warning' ? 'notification-warning' : '']"
        @click="markRead(n)">
        <div class="d-flex align-items-start">
          <div class="notification-icon me-3" :class="n.type === 'warning' ? 'icon-warning' : ''">
            <i class="bi" :class="getIcon(n.type)"></i>
          </div>
          <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="fw-bold small">{{ getTypeLabel(n.type) }}</span>
              <span class="text-muted small">{{ formatDate(n.created_at) }}</span>
            </div>

            <template v-if="n.type === 'warning'">
              <div class="warning-box">
                <div class="mb-2"><strong>{{ t('notifications.warning_reason') }}:</strong> {{ parseWarning(n).reason }}</div>
                <div class="mb-2"><strong>{{ t('notifications.warning_count') }}:</strong> {{ parseWarning(n).warning_count }}</div>
                <div class="mb-2">
                  <strong>{{ t('notifications.warning_consequence') }}:</strong>
                  <span v-if="parseWarning(n).suspended_until">
                    {{ t('notifications.warning_suspended_until') }}
                    <span class="fw-bold text-danger ms-1">{{ formatDate(parseWarning(n).suspended_until) }}</span>
                  </span>
                  <span v-else>{{ t('notifications.warning_no_suspension') }}</span>
                </div>
                <div class="text-muted small mt-2">
                  <i class="bi bi-info-circle me-1"></i>{{ t('notifications.warning_levels_info') }}
                </div>
                <div class="manual-note mt-2">
                  <i class="bi bi-shield-exclamation me-1"></i>
                  {{ t('notifications.warning_manual_note') }}
                </div>
              </div>
            </template>

            <template v-else>
              <p class="mb-1 small">{{ n.message }}</p>
              <div v-if="n.hotel" class="text-muted small">
                <i class="bi bi-building me-1"></i>{{ n.hotel.name }}
              </div>
            </template>
          </div>
          <div v-if="!n.is_read" class="unread-dot ms-2"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/api/api'

export default {
  name: 'NotificationsView',
  data() {
    return {
      notifications: [],
      loading: true
    }
  },
  computed: {
    unreadCount() {
      return this.notifications.filter(n => !n.is_read).length;
    }
  },
  methods: {
    async fetchNotifications() {
      this.loading = true;
      try {
        this.notifications = await api.getNotifications();
      } catch (e) {
        console.error(e);
      } finally {
        this.loading = false;
      }
    },

    async markRead(n) {
      if (n.is_read) return;
      try {
        await api.markNotificationRead(n.id);
        n.is_read = true;
      } catch (e) {
        // silent
      }
    },

    async markAllRead() {
      try {
        await api.markAllNotificationsRead();
        this.notifications.forEach(n => n.is_read = true);
      } catch (e) {
        // silent
      }
    },

    getIcon(type) {
      const map = {
        booking_cancelled: 'bi-x-circle-fill text-danger',
        message: 'bi-chat-dots-fill text-teal',
        warning: 'bi-exclamation-triangle-fill text-warning',
      };
      return map[type] || 'bi-info-circle-fill text-primary';
    },

    getTypeLabel(type) {
      if (type === 'booking_cancelled') return this.t('notifications.booking_cancelled');
      if (type === 'warning') return this.t('notifications.warning_received');
      return type;
    },

    parseWarning(n) {
      try {
        const data = typeof n.message === 'string' ? JSON.parse(n.message) : n.message;
        return {
          reason: data.reason || '',
          warning_count: data.warning_count || 0,
          suspended_until: data.suspended_until || null,
          manual: data.manual !== false,
        };
      } catch (e) {
        return { reason: n.message, warning_count: 0, suspended_until: null, manual: true };
      }
    },

    formatDate(dateStr) {
      if (!dateStr) return '';
      return new Date(dateStr).toLocaleDateString(this.currentLocale === 'hu' ? 'hu-HU' : 'en-US', {
        year: 'numeric', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit'
      });
    }
  },
  mounted() {
    this.fetchNotifications();
  }
}
</script>

<style scoped>
.text-primary-dark { color: #264653; }
.text-teal { color: #2a9d8f; }
.btn-outline-teal {
  border: 2px solid #2a9d8f;
  color: #2a9d8f;
}
.btn-outline-teal:hover {
  background: #2a9d8f;
  color: #fff;
}
.notification-card {
  background: #fff;
  border-radius: 12px;
  padding: 1rem 1.25rem;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
  border: 1px solid #eee;
  cursor: pointer;
  transition: all 0.2s;
}
.notification-card:hover {
  box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}
.notification-unread {
  background: #f0faf8;
  border-left: 4px solid #2a9d8f;
}
.notification-warning {
  border-left: 4px solid #f4a261 !important;
  background: #fff8f0;
}
.notification-warning.notification-unread {
  background: #fff2e0;
}
.icon-warning {
  background: #fff3cd !important;
}
.warning-box {
  background: #fff;
  border: 1px solid #f4a261;
  border-radius: 10px;
  padding: 0.75rem 1rem;
  font-size: 0.85rem;
}
.manual-note {
  background: #fff3cd;
  border: 1px dashed #f4a261;
  border-radius: 8px;
  padding: 0.5rem 0.75rem;
  color: #7a4a1e;
  font-size: 0.75rem;
}
.notification-icon {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: #f8f9fa;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  flex-shrink: 0;
}
.unread-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #2a9d8f;
  flex-shrink: 0;
  margin-top: 6px;
}
</style>
