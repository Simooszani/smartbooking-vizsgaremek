const BASE_URL = (window.__API_BASE_URL__) || 'http://127.0.0.1:8000/api';

const api = {
    getHeaders() {
        const token = localStorage.getItem('access_token');
        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': token ? `Bearer ${token}` : ''
        };
    },

    // Auth
    async register(userData) {
        const response = await fetch(`${BASE_URL}/register`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(userData)
        });
        const data = await response.json();
        if (!response.ok) throw data;
        if (data.access_token) {
            localStorage.setItem('access_token', data.access_token);
        }
        return data;
    },

    async login(credentials) {
        const response = await fetch(`${BASE_URL}/login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(credentials)
        });
        const data = await response.json();
        if (!response.ok) {
            const err = new Error(data.message || 'Hibás belépési adatok');
            if (data.suspended_until) err.suspended_until = data.suspended_until;
            throw err;
        }
        if (data.access_token) {
            localStorage.setItem('access_token', data.access_token);
            if (data.user) {
                localStorage.setItem('user', JSON.stringify(data.user));
            }
        }
        return data;
    },

    async logout() {
        const response = await fetch(`${BASE_URL}/logout`, {
            method: 'POST',
            headers: this.getHeaders()
        });
        localStorage.removeItem('access_token');
        localStorage.removeItem('user');
        if (!response.ok) console.warn('Szerver oldali hiba a kijelentkezésnél.');
        return { message: 'Sikeres kijelentkezés' };
    },

    async getMe() {
        const response = await fetch(`${BASE_URL}/me`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        if (!response.ok) {
            localStorage.removeItem('access_token');
            throw new Error('Munkamenet lejárt');
        }
        return await response.json();
    },

    // Hotels
    async searchHotels(params) {
        const queryString = new URLSearchParams(params).toString();
        const response = await fetch(`${BASE_URL}/hotels/search?${queryString}`, {
            method: 'GET',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' }
        });
        if (!response.ok) throw new Error('Hiba a keresés során');
        return await response.json();
    },

    async getAllHotels() {
        const response = await fetch(`${BASE_URL}/hotels`);
        if (!response.ok) throw new Error('Hiba a hotelek lekérésekor');
        return await response.json();
    },

    // Bookings
    async getBookings() {
        const response = await fetch(`${BASE_URL}/bookings`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        if (!response.ok) throw new Error('Nem sikerült betölteni a foglalásaidat');
        return await response.json();
    },

    async createBooking(bookingData) {
        const response = await fetch(`${BASE_URL}/bookings`, {
            method: 'POST',
            headers: this.getHeaders(),
            body: JSON.stringify(bookingData)
        });
        const data = await response.json();
        if (!response.ok) throw data;
        return data;
    },

    async deleteBooking(id) {
        const response = await fetch(`${BASE_URL}/bookings/${id}`, {
            method: 'DELETE',
            headers: this.getHeaders()
        });
        if (!response.ok) throw new Error('Sikertelen törlés');
        return await response.json();
    },

    // Reviews
    async createReview(reviewData) {
        const response = await fetch(`${BASE_URL}/reviews`, {
            method: 'POST',
            headers: this.getHeaders(),
            body: JSON.stringify(reviewData)
        });
        const data = await response.json();
        if (!response.ok) throw data;
        return data;
    },

    // Admin: Bookings
    async getAllAdminBookings() {
        const response = await fetch(`${BASE_URL}/admin/bookings`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        if (!response.ok) throw new Error('Nincs jogosultságod az adatokhoz');
        return await response.json();
    },

    // Admin: Users
    async getAllUsers() {
        const response = await fetch(`${BASE_URL}/admin/users`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        if (!response.ok) throw new Error('Nem sikerült lekérni a felhasználókat');
        return await response.json();
    },

    async deleteUser(id) {
        const response = await fetch(`${BASE_URL}/admin/users/${id}`, {
            method: 'DELETE',
            headers: this.getHeaders()
        });
        if (!response.ok) throw new Error('Sikertelen felhasználó törlés');
        return await response.json();
    },

    // Admin: Rooms
    async getAllAdminRooms() {
        const response = await fetch(`${BASE_URL}/admin/rooms`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        if (!response.ok) throw new Error('Nem sikerült lekérni a szobákat');
        return await response.json();
    },

    async updateRoom(id, roomData) {
        const response = await fetch(`${BASE_URL}/admin/rooms/${id}`, {
            method: 'PUT',
            headers: this.getHeaders(),
            body: JSON.stringify(roomData)
        });
        if (!response.ok) throw new Error('Sikertelen módosítás');
        return await response.json();
    },

    async createRoom(roomData) {
        const response = await fetch(`${BASE_URL}/admin/rooms`, {
            method: 'POST',
            headers: this.getHeaders(),
            body: JSON.stringify(roomData)
        });
        if (!response.ok) throw new Error('Sikertelen létrehozás');
        return await response.json();
    },

    async deleteRoom(id) {
        const response = await fetch(`${BASE_URL}/admin/rooms/${id}`, {
            method: 'DELETE',
            headers: this.getHeaders()
        });
        if (!response.ok) throw new Error('Sikertelen törlés');
        return await response.json();
    },

    // Admin: Hotels
    async createHotel(hotelData) {
        const response = await fetch(`${BASE_URL}/admin/hotels`, {
            method: 'POST',
            headers: this.getHeaders(),
            body: JSON.stringify(hotelData)
        });
        const data = await response.json();
        if (!response.ok) throw data;
        return data;
    },

    async updateHotel(id, hotelData) {
        const response = await fetch(`${BASE_URL}/admin/hotels/${id}`, {
            method: 'PUT',
            headers: this.getHeaders(),
            body: JSON.stringify(hotelData)
        });
        if (!response.ok) throw new Error('Sikertelen módosítás');
        return await response.json();
    },

    async deleteHotel(id) {
        const response = await fetch(`${BASE_URL}/admin/hotels/${id}`, {
            method: 'DELETE',
            headers: this.getHeaders()
        });
        if (!response.ok) throw new Error('Sikertelen törlés');
        return await response.json();
    },

    // Admin: Update user role
    async updateUserRole(id, roleData) {
        const response = await fetch(`${BASE_URL}/admin/users/${id}/role`, {
            method: 'PUT',
            headers: this.getHeaders(),
            body: JSON.stringify(roleData)
        });
        if (!response.ok) {
            const data = await response.json();
            throw new Error(data.message || 'Sikertelen módosítás');
        }
        return await response.json();
    },

    // Hotel Admin: saját hotel foglalásai
    async getHotelAdminBookings() {
        const response = await fetch(`${BASE_URL}/hotel-admin/bookings`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        if (!response.ok) throw new Error('Nem sikerült lekérni a foglalásokat');
        return await response.json();
    },

    // Foglalás törlése indoklással
    async deleteBookingWithReason(id, reason) {
        const response = await fetch(`${BASE_URL}/bookings/${id}`, {
            method: 'DELETE',
            headers: this.getHeaders(),
            body: JSON.stringify({ reason })
        });
        if (!response.ok) throw new Error('Sikertelen törlés');
        return await response.json();
    },

    // Notifications
    async getNotifications() {
        const response = await fetch(`${BASE_URL}/notifications`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        if (!response.ok) throw new Error('Nem sikerült lekérni az értesítéseket');
        return await response.json();
    },

    async getUnreadCount() {
        const response = await fetch(`${BASE_URL}/notifications/unread-count`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        if (!response.ok) return { count: 0 };
        return await response.json();
    },

    async markNotificationRead(id) {
        const response = await fetch(`${BASE_URL}/notifications/${id}/read`, {
            method: 'PUT',
            headers: this.getHeaders()
        });
        if (!response.ok) throw new Error('Hiba');
        return await response.json();
    },

    async markAllNotificationsRead() {
        const response = await fetch(`${BASE_URL}/notifications/read-all`, {
            method: 'PUT',
            headers: this.getHeaders()
        });
        if (!response.ok) throw new Error('Hiba');
        return await response.json();
    },

    // Chat Messages
    async getConversations() {
        const response = await fetch(`${BASE_URL}/messages/conversations`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        if (!response.ok) throw new Error('Hiba');
        return await response.json();
    },

    async getMessages(hotelId, userId) {
        const response = await fetch(`${BASE_URL}/messages/${hotelId}/${userId}`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        if (!response.ok) throw new Error('Hiba');
        return await response.json();
    },

    async sendMessage(data) {
        const response = await fetch(`${BASE_URL}/messages`, {
            method: 'POST',
            headers: this.getHeaders(),
            body: JSON.stringify(data)
        });
        if (!response.ok) throw new Error('Hiba');
        return await response.json();
    },

    async deleteMessage(id) {
        const response = await fetch(`${BASE_URL}/messages/${id}`, {
            method: 'DELETE',
            headers: this.getHeaders()
        });
        if (!response.ok) throw new Error('Hiba');
        return await response.json();
    },

    async deleteConversation(hotelId, userId) {
        const response = await fetch(`${BASE_URL}/messages/conversations/${hotelId}/${userId}`, {
            method: 'DELETE',
            headers: this.getHeaders()
        });
        if (!response.ok) throw new Error('Hiba');
        return await response.json();
    },

    async getMessageUnreadCount() {
        const response = await fetch(`${BASE_URL}/messages/unread-count`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        if (!response.ok) return { count: 0 };
        return await response.json();
    },

    // Reports
    async createReport(data) {
        const response = await fetch(`${BASE_URL}/hotel-admin/reports`, {
            method: 'POST',
            headers: this.getHeaders(),
            body: JSON.stringify(data)
        });
        const res = await response.json();
        if (!response.ok) throw res;
        return res;
    },

    async getReports() {
        const response = await fetch(`${BASE_URL}/admin/reports`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        if (!response.ok) throw new Error('Hiba');
        return await response.json();
    },

    async updateReportStatus(id, status) {
        const response = await fetch(`${BASE_URL}/admin/reports/${id}/status`, {
            method: 'PUT',
            headers: this.getHeaders(),
            body: JSON.stringify({ status })
        });
        if (!response.ok) throw new Error('Hiba');
        return await response.json();
    },

    // Warnings
    async getWarnings() {
        const response = await fetch(`${BASE_URL}/admin/warnings`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        if (!response.ok) throw new Error('Hiba');
        return await response.json();
    },

    async getUserWarnings(userId) {
        const response = await fetch(`${BASE_URL}/admin/warnings/${userId}`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        if (!response.ok) throw new Error('Hiba');
        return await response.json();
    },

    async issueWarning(data) {
        const response = await fetch(`${BASE_URL}/admin/warnings`, {
            method: 'POST',
            headers: this.getHeaders(),
            body: JSON.stringify(data)
        });
        const res = await response.json();
        if (!response.ok) throw res;
        return res;
    },
};

export default api;
