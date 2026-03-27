const BASE_URL = 'http://127.0.0.1:8000/api';

const api = {
    // 1. SEGÉDFÜGGVÉNY A FEJLÉCEKHEZ
    // Ez automatikusan kikeresi a tokent a böngésző memóriájából
    getHeaders() { 
        const token = localStorage.getItem('access_token');
        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': token ? `Bearer ${token}` : ''
        };
    },

    // 2. REGISZTRÁCIÓ
    async register(userData) {
        const response = await fetch(`${BASE_URL}/register`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(userData)
        });

        const data = await response.json();
        if (!response.ok) throw data;

        if (data.access_token) {
            localStorage.setItem('access_token', data.access_token);
        }
        return data;
    },

    // 3. BEJELENTKEZÉS
    async login(credentials) {
        const response = await fetch(`${BASE_URL}/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(credentials)
        });

        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.message || 'Hibás belépési adatok');
        }

        if (data.access_token) {
            localStorage.setItem('access_token', data.access_token);
            // Ha a szerver visszaküldi a user objektumot, azt is mentsük el a jogosultság ellenőrzéshez
            if (data.user) {
                localStorage.setItem('user', JSON.stringify(data.user));
            }
        }
        return data;
    },

    // 4. KIJELENTKEZÉS
    async logout() {
        const response = await fetch(`${BASE_URL}/logout`, {
            method: 'POST',
            headers: this.getHeaders()
        });

        localStorage.removeItem('access_token');
        localStorage.removeItem('user');

        if (!response.ok) {
            console.warn('Szerver oldali hiba a kijelentkezésnél.');
        }
        return { message: 'Sikeres kijelentkezés' };
    },

    // 5. PROFIL ADATOK (ME)
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

    // 6. SZÁLLODÁK KERESÉSE
    async searchHotels(params) {
        const queryString = new URLSearchParams(params).toString();
        const response = await fetch(`${BASE_URL}/hotels/search?${queryString}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) throw new Error('Hiba a keresés során');
        return await response.json();
    },

    // 7. EGYÉNI FOGLALÁSOK (USER)
    async getBookings() {
        const response = await fetch(`${BASE_URL}/bookings`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        if (!response.ok) throw new Error('Nem sikerült betölteni a foglalásaidat');
        return await response.json();
    },

    // 8. ÚJ FOGLALÁS LÉTREHOZÁSA (ÜTKÖZÉSVIZSGÁLATTAL)
    async createBooking(bookingData) {
        const response = await fetch(`${BASE_URL}/bookings`, {
            method: 'POST',
            headers: this.getHeaders(),
            body: JSON.stringify(bookingData)
        });

        const data = await response.json();
        if (!response.ok) {
            // Ha a backend 422-es hibát dob (már foglalt), itt adjuk tovább
            throw data;
        }
        return data;
    },

    // 9. ADMIN: ÖSSZES FOGLALÁS LISTÁZÁSA
    async getAllAdminBookings() {
        const response = await fetch(`${BASE_URL}/admin/bookings`, {
            method: 'GET',
            headers: this.getHeaders()
        });

        if (!response.ok) throw new Error('Nincs jogosultságod az adatokhoz');
        return await response.json();
    },

    // 10. ADMIN: FOGLALÁS TÖRLÉSE
    async deleteBooking(id) {
        const response = await fetch(`${BASE_URL}/bookings/${id}`, {
            method: 'DELETE',
            headers: this.getHeaders()
        });

        if (!response.ok) throw new Error('Sikertelen törlés');
        return await response.json();
    },

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

    async getAllHotels() {
        const response = await fetch(`${BASE_URL}/hotels`);
        if (!response.ok) throw new Error('Hiba a hotelek lekérésekor');
        return await response.json();
    },
};

export default api;