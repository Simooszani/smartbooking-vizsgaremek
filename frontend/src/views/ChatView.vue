<template>
  <div class="container py-4" style="max-width: 900px;">
    <h2 class="fw-bold text-primary-dark mb-4">
      <i class="bi bi-chat-dots me-2"></i>{{ t('chat.title') }}
    </h2>

    <div class="chat-layout">
      <!-- Conversations list -->
      <div class="conversations-panel">
        <div class="p-3 border-bottom">
          <h6 class="fw-bold mb-0">{{ t('chat.conversations') }}</h6>
        </div>
        <div v-if="conversations.length === 0" class="text-center py-4 text-muted small">
          {{ t('chat.no_conversations') }}
        </div>
        <div
          v-for="conv in conversations" :key="convKey(conv)"
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
              <!-- Report button for hotel admin -->
              <button
                v-if="isHotelAdmin && activeConv.user_id !== currentUserId"
                @click="reportUser"
                class="btn btn-outline-danger btn-sm rounded-pill px-3">
                <i class="bi bi-flag me-1"></i>{{ t('hotel_admin.report_user') }}
              </button>
            </div>
          </div>

          <!-- Messages -->
          <div class="messages-area" ref="messagesArea">
            <div v-for="msg in messages" :key="msg.id"
              class="message-bubble"
              :class="msg.sender_id === currentUserId ? 'message-sent' : 'message-received'">
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
      pollInterval: null
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
      if (this.isHotelAdmin && conv.user) return conv.user.name;
      if (conv.hotel) return conv.hotel.name;
      return '...';
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
        if (this.isHotelAdmin) {
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
        if (msgs.length > this.messages.length) {
          this.messages = msgs;
          this.$nextTick(() => this.scrollToBottom());
        }
      } catch (e) { /* silent */ }
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
}
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
