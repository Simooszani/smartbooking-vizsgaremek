<template>
  <div class="container py-4" style="max-width: 900px;">
    <h2 class="fw-bold text-primary-dark mb-4">
      <i class="bi bi-chat-dots me-2"></i>{{ t('chat.title') }}
    </h2>

    <div class="chat-layout">
      <!-- Conversations list -->
      <div class="conversations-panel">
        <div class="p-3 border-bottom">
          <h6 class="fw-bold mb-2 d-none d-md-block">{{ t('chat.conversations') }}</h6>
          <div class="position-relative">
            <i class="bi bi-search position-absolute" style="left: 10px; top: 50%; transform: translateY(-50%); color: #888; font-size: 0.8rem;"></i>
            <input
              v-model="searchQuery"
              type="text"
              class="form-control form-control-sm rounded-pill ps-4 d-none d-md-block"
              :placeholder="t('chat.search_placeholder')">
            <button class="btn btn-sm btn-outline-secondary rounded-pill d-md-none w-100" @click="mobileSearchOpen = !mobileSearchOpen">
              <i class="bi bi-search"></i>
            </button>
            <input
              v-if="mobileSearchOpen"
              v-model="searchQuery"
              type="text"
              class="form-control form-control-sm rounded-pill ps-4 mt-2 d-md-none"
              :placeholder="t('chat.search_placeholder')">
          </div>
        </div>
        <div v-if="filteredConversations.length === 0" class="text-center py-4 text-muted small">
          {{ searchQuery ? t('chat.no_search_results') : t('chat.no_conversations') }}
        </div>
        <div
          v-for="conv in filteredConversations" :key="convKey(conv)"
          class="conversation-item"
          :class="{ active: isActiveConv(conv) }"
          @click="openConversation(conv)">
          <div class="d-flex align-items-center">
            <div class="avatar-circle me-2">
              {{ getConvName(conv).charAt(0) }}
            </div>
            <div class="flex-grow-1 overflow-hidden">
              <div class="fw-bold small text-truncate">{{ getConvName(conv) }}</div>
              <div class="text-muted small text-truncate" v-if="conv.hotel">{{ conv.hotel.name }}</div>
              <div class="text-muted small text-truncate" v-if="isSuperAdmin && conv.user" style="font-size: 0.7rem;">{{ conv.user.email }}</div>
            </div>
            <span v-if="conv.unread_count > 0" class="badge bg-danger rounded-pill ms-2">
              {{ conv.unread_count }}
            </span>
          </div>
        </div>
      </div>

      <!-- Chat window -->
      <div class="chat-panel">
        <div v-if="!activeConv" class="d-flex align-items-center justify-content-center h-100 text-muted">
          <div class="text-center">
            <i class="bi bi-chat-square-text display-4"></i>
            <p class="mt-2">{{ t('chat.conversations') }}</p>
          </div>
        </div>

        <template v-else>
          <!-- Chat header -->
          <div class="chat-header">
            <div class="d-flex align-items-center justify-content-between">
              <div>
                <strong>{{ getConvName(activeConv) }}</strong>
                <span class="text-muted small ms-2" v-if="activeConv.hotel">{{ activeConv.hotel.name }}</span>
              </div>
              <div class="d-flex gap-2 flex-wrap">
                <!-- Report button for hotel admin -->
                <button
                  v-if="isHotelAdmin && activeConv.user_id !== currentUserId"
                  @click="reportUser"
                  class="btn btn-outline-danger btn-sm rounded-pill px-3">
                  <i class="bi bi-flag me-1"></i>{{ t('hotel_admin.report_user') }}
                </button>
                <!-- Warn button for super_admin -->
                <button
                  v-if="isSuperAdmin && activeConv.user_id !== currentUserId"
                  @click="warnUser"
                  class="btn btn-outline-warning btn-sm rounded-pill px-3"
                  :title="t('hotel_admin.warn_user')">
                  <i class="bi bi-exclamation-triangle me-1"></i>{{ t('hotel_admin.warn_user') }}
                </button>
                <!-- Delete conversation button for super_admin -->
                <button
                  v-if="isSuperAdmin"
                  @click="deleteConversation"
                  class="btn btn-outline-danger btn-sm rounded-pill px-3"
                  :title="t('chat.delete_conversation')">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Messages -->
          <div class="messages-area" ref="messagesArea">
            <div v-for="msg in messages" :key="msg.id"
              class="message-bubble"
              :class="msg.sender_id === currentUserId ? 'message-sent' : 'message-received'">
              <button
                v-if="canDeleteMessage(msg)"
                @click="deleteMessage(msg)"
                class="msg-delete-btn"
                :title="t('chat.delete_message')">
                <i class="bi bi-x-lg"></i>
              </button>
              <div class="message-sender small fw-bold mb-1" v-if="msg.sender">{{ msg.sender.name }}</div>
              <div class="message-text">{{ msg.message }}</div>
              <div class="message-time">{{ formatTime(msg.created_at) }}</div>
            </div>
          </div>

          <!-- Input -->
          <div class="chat-input">
            <form @submit.prevent="sendMessage" class="d-flex gap-2">
              <input
                v-model="newMessage"
                type="text"
                class="form-control rounded-pill"
                :placeholder="t('chat.type_message')"
                maxlength="2000">
              <button type="submit" class="btn btn-teal rounded-pill px-4" :disabled="!newMessage.trim()">
                <i class="bi bi-send"></i>
              </button>
            </form>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/api/api'
import Swal from 'sweetalert2'

export default {
  name: 'ChatView',
  data() {
    return {
      conversations: [],
      messages: [],
      activeConv: null,
      newMessage: '',
      currentUserId: null,
      isHotelAdmin: false,
      isSuperAdmin: false,
      searchQuery: '',
      mobileSearchOpen: false,
      isAdmin: false,
      pollInterval: null
    }
  },
  computed: {
    filteredConversations() {
      if (!this.searchQuery || !this.searchQuery.trim()) return this.conversations;
      const q = this.searchQuery.trim().toLowerCase();
      return this.conversations.filter(c => {
        const userName = (c.user && c.user.name) ? c.user.name.toLowerCase() : '';
        const userEmail = (c.user && c.user.email) ? c.user.email.toLowerCase() : '';
        const hotelName = (c.hotel && c.hotel.name) ? c.hotel.name.toLowerCase() : '';
        return userName.includes(q) || userEmail.includes(q) || hotelName.includes(q);
      });
    }
  },
  methods: {
    convKey(conv) {
      return conv.hotel_id + '_' + conv.user_id;
    },
    isActiveConv(conv) {
      return this.activeConv && this.activeConv.hotel_id === conv.hotel_id && this.activeConv.user_id === conv.user_id;
    },
    getConvName(conv) {
      if ((this.isHotelAdmin || this.isSuperAdmin) && conv.user) return conv.user.name;
      if (conv.hotel) return conv.hotel.name;
      return '...';
    },

    canDeleteMessage(msg) {
      return msg.sender_id === this.currentUserId || this.isSuperAdmin;
    },

    async deleteMessage(msg) {
      const result = await Swal.fire({
        title: this.t('chat.delete_message'),
        text: this.t('chat.delete_message_confirm'),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: this.t('chat.delete_message'),
        cancelButtonText: this.t('common.cancel'),
        confirmButtonColor: '#e76f51',
      });
      if (!result.isConfirmed) return;
      try {
        await api.deleteMessage(msg.id);
        this.messages = this.messages.filter(m => m.id !== msg.id);
        Swal.fire({ icon: 'success', title: this.t('chat.delete_message_success'), timer: 1500, showConfirmButton: false });
      } catch (e) {
        Swal.fire({ icon: 'error', title: this.t('common.error'), confirmButtonColor: '#e76f51' });
      }
    },

    async deleteConversation() {
      if (!this.activeConv) return;
      const result = await Swal.fire({
        title: this.t('chat.delete_conversation'),
        text: this.t('chat.delete_conversation_confirm'),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: this.t('chat.delete_conversation'),
        cancelButtonText: this.t('common.cancel'),
        confirmButtonColor: '#e76f51',
      });
      if (!result.isConfirmed) return;
      try {
        await api.deleteConversation(this.activeConv.hotel_id, this.activeConv.user_id);
        const key = this.convKey(this.activeConv);
        this.conversations = this.conversations.filter(c => this.convKey(c) !== key);
        this.activeConv = null;
        this.messages = [];
        Swal.fire({ icon: 'success', title: this.t('chat.delete_conversation_success'), timer: 1500, showConfirmButton: false });
      } catch (e) {
        Swal.fire({ icon: 'error', title: this.t('common.error'), confirmButtonColor: '#e76f51' });
      }
    },

    async fetchConversations() {
      try {
        this.conversations = await api.getConversations();
      } catch (e) { /* silent */ }
    },

    async openConversation(conv) {
      this.activeConv = conv;
      try {
        this.messages = await api.getMessages(conv.hotel_id, conv.user_id);
        conv.unread_count = 0;
        this.$nextTick(() => this.scrollToBottom());
      } catch (e) {
        console.error(e);
      }
    },

    async sendMessage() {
      if (!this.newMessage.trim() || !this.activeConv) return;
      try {
        const data = {
          hotel_id: this.activeConv.hotel_id,
          message: this.newMessage,
        };
        if (this.activeConv.user_id && this.activeConv.user_id !== this.currentUserId) {
          data.user_id = this.activeConv.user_id;
        }
        const msg = await api.sendMessage(data);
        this.messages.push(msg);
        this.newMessage = '';
        this.$nextTick(() => this.scrollToBottom());
      } catch (e) {
        Swal.fire({ icon: 'error', title: this.t('common.error'), confirmButtonColor: '#e76f51' });
      }
    },

    async pollMessages() {
      if (!this.activeConv) return;
      try {
        const msgs = await api.getMessages(this.activeConv.hotel_id, this.activeConv.user_id);
        if (msgs.length !== this.messages.length) {
          const shouldScroll = msgs.length > this.messages.length;
          this.messages = msgs;
          if (shouldScroll) this.$nextTick(() => this.scrollToBottom());
        }
      } catch (e) { /* silent */ }
    },

    async warnUser() {
      if (!this.activeConv || !this.activeConv.user_id) return;
      const reasonOptions = ['disrespect', 'inappropriate', 'profanity', 'other']
        .map(r => `<option value="${this.t('hotel_admin.report_reasons.' + r)}">${this.t('hotel_admin.report_reasons.' + r)}</option>`)
        .join('');

      const { value: formValues } = await Swal.fire({
        title: this.t('hotel_admin.warn_user'),
        html:
          `<div class="text-start">
            <p class="small text-muted">${this.t('hotel_admin.warn_user_confirm')}</p>
            <label class="form-label fw-semibold small">${this.t('warnings.warn_reason')}</label>
            <select id="swal-warn-reason-select" class="form-select mb-2">${reasonOptions}</select>
            <textarea id="swal-warn-desc" class="form-control" rows="3" placeholder="${this.t('warnings.warn_reason_placeholder')}"></textarea>
          </div>`,
        showCancelButton: true,
        confirmButtonText: this.t('hotel_admin.warn_user'),
        cancelButtonText: this.t('common.cancel'),
        confirmButtonColor: '#e76f51',
        preConfirm: () => {
          const selected = document.getElementById('swal-warn-reason-select').value;
          const desc = document.getElementById('swal-warn-desc').value.trim();
          return {
            user_id: this.activeConv.user_id,
            reason: desc ? (selected + ' — ' + desc) : selected,
          };
        }
      });

      if (formValues) {
        try {
          const res = await api.issueWarning(formValues);
          const msg = res.suspended_until
            ? this.t('warnings.warn_suspended')
            : this.t('warnings.warn_success');
          Swal.fire({ icon: 'success', title: msg, timer: 2000, showConfirmButton: false });
        } catch (e) {
          Swal.fire({ icon: 'error', title: this.t('common.error'), confirmButtonColor: '#e76f51' });
        }
      }
    },

    async reportUser() {
      const reasonOptions = ['disrespect', 'inappropriate', 'profanity', 'other']
        .map(r => `<option value="${r}">${this.t('hotel_admin.report_reasons.' + r)}</option>`)
        .join('');

      const { value: formValues } = await Swal.fire({
        title: this.t('hotel_admin.report_user'),
        html:
          `<div class="text-start">
            <label class="form-label fw-semibold small">${this.t('hotel_admin.report_reason')}</label>
            <select id="swal-reason" class="form-select mb-3">${reasonOptions}</select>
            <label class="form-label fw-semibold small">${this.t('hotel_admin.report_desc')}</label>
            <textarea id="swal-desc" class="form-control" rows="3" placeholder="${this.t('hotel_admin.report_desc_placeholder')}"></textarea>
          </div>`,
        showCancelButton: true,
        confirmButtonText: this.t('hotel_admin.report_user'),
        cancelButtonText: this.t('common.cancel'),
        confirmButtonColor: '#e76f51',
        preConfirm: () => {
          return {
            reported_user_id: this.activeConv.user_id,
            reason: document.getElementById('swal-reason').value,
            description: document.getElementById('swal-desc').value,
          };
        }
      });

      if (formValues) {
        try {
          await api.createReport(formValues);
          Swal.fire({ icon: 'success', title: this.t('hotel_admin.report_sent'), timer: 2000, showConfirmButton: false });
        } catch (e) {
          Swal.fire({ icon: 'error', title: this.t('common.error'), confirmButtonColor: '#e76f51' });
        }
      }
    },

    scrollToBottom() {
      const area = this.$refs.messagesArea;
      if (area) area.scrollTop = area.scrollHeight;
    },

    formatTime(dateStr) {
      if (!dateStr) return '';
      const d = new Date(dateStr);
      return d.toLocaleString(this.currentLocale === 'hu' ? 'hu-HU' : 'en-US', {
        month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit'
      });
    },

    // Open a new chat from hotel page (called via query params)
    async initFromQuery() {
      const hotelId = this.$route.query.hotel;
      const userId = this.$route.query.user;
      if (hotelId) {
        const targetUserId = userId ? parseInt(userId) : this.currentUserId;
        await this.fetchConversations();
        const existing = this.conversations.find(c => c.hotel_id == hotelId && c.user_id == targetUserId);
        if (existing) {
          this.openConversation(existing);
        } else {
          const conv = { hotel_id: parseInt(hotelId), user_id: targetUserId, hotel: { name: '' } };
          this.activeConv = conv;
          try {
            this.messages = await api.getMessages(hotelId, targetUserId);
          } catch (e) {
            this.messages = [];
          }
        }
      }
    }
  },
  mounted() {
    const userData = localStorage.getItem('user');
    if (userData) {
      const u = JSON.parse(userData);
      this.currentUserId = u.id;
      this.isHotelAdmin = u.role === 'hotel_admin';
      this.isAdmin = u.role === 'admin';
      this.isSuperAdmin = u.role === 'super_admin';
    }
    this.fetchConversations();
    this.initFromQuery();
    // Poll for new messages every 5 seconds
    this.pollInterval = setInterval(() => {
      this.pollMessages();
      this.fetchConversations();
    }, 5000);
  },
  beforeDestroy() {
    if (this.pollInterval) clearInterval(this.pollInterval);
  }
}
</script>

<style scoped>
.text-primary-dark { color: #264653; }
.chat-layout {
  display: flex;
  height: calc(100vh - 180px);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 2px 15px rgba(0,0,0,0.08);
  background: #fff;
}
.conversations-panel {
  width: 280px;
  border-right: 1px solid #eee;
  overflow-y: auto;
  flex-shrink: 0;
}
.conversation-item {
  padding: 0.75rem 1rem;
  cursor: pointer;
  border-bottom: 1px solid #f5f5f5;
  transition: background 0.2s;
}
.conversation-item:hover { background: #f8f9fa; }
.conversation-item.active { background: #e8f8f5; border-left: 3px solid #2a9d8f; }
.avatar-circle {
  width: 36px; height: 36px;
  background: linear-gradient(135deg, #264653, #2a9d8f);
  color: white; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: bold; font-size: 0.8rem; flex-shrink: 0;
}
.chat-panel {
  flex: 1;
  display: flex;
  flex-direction: column;
}
.chat-header {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #eee;
  background: #fafafa;
}
.messages-area {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.message-bubble {
  max-width: 70%;
  padding: 0.6rem 1rem;
  border-radius: 16px;
  word-wrap: break-word;
  position: relative;
}
.msg-delete-btn {
  position: absolute;
  top: -8px;
  right: -8px;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  border: none;
  background: #e76f51;
  color: #fff;
  font-size: 0.65rem;
  display: none;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}
.message-bubble:hover .msg-delete-btn { display: flex; }
.msg-delete-btn:hover { background: #d85a3a; }
.message-sent {
  align-self: flex-end;
  background: linear-gradient(135deg, #2a9d8f, #264653);
  color: #fff;
  border-bottom-right-radius: 4px;
}
.message-received {
  align-self: flex-start;
  background: #f0f0f0;
  color: #333;
  border-bottom-left-radius: 4px;
}
.message-sender { font-size: 0.7rem; opacity: 0.8; }
.message-text { font-size: 0.9rem; }
.message-time { font-size: 0.65rem; opacity: 0.6; text-align: right; margin-top: 2px; }
.message-sent .message-time { color: rgba(255,255,255,0.7); }
.chat-input {
  padding: 0.75rem 1rem;
  border-top: 1px solid #eee;
  background: #fafafa;
}
.btn-teal {
  background: #2a9d8f;
  color: #fff;
  border: none;
}
.btn-teal:hover { background: #238b7e; color: #fff; }

@media (max-width: 768px) {
  .conversations-panel { width: 80px; }
  .conversation-item .text-truncate { display: none; }
}
</style>
