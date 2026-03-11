const BASE_URL = 'http://127.0.0.1:8000/api';

const api = {
    // Segédfüggvény a fejlécekhez
    getHeaders() { 
        const token = localStorage.getItem('access_token');
        console.log("Aktuális token küldés előtt:", token); // Debug infó a konzolra
        
        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': token ? `Bearer ${token}` : ''
        };
    },

    // REGISZTRÁCIÓ
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

        if (!response.ok) {
            console.error('Regisztrációs hiba:', data);
            throw data; // Visszaadjuk a validációs hibákat (pl. foglalt email)
        }

        // Ha van token a válaszban, mentsük el
        if (data.access_token) {
            localStorage.setItem('access_token', data.access_token);
        }
        return data;
    },

    // BEJELENTKEZÉS
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
        }
        return data;
    },

    // KIJELENTKEZÉS
    async logout() {
        const response = await fetch(`${BASE_URL}/logout`, {
            method: 'POST',
            headers: this.getHeaders()
        });

        localStorage.removeItem('access_token'); // Töröljük a helyi tokent mindenképp

        if (!response.ok) {
            console.warn('A szerver oldali logout nem sikerült, de a helyi tokent töröltük.');
        }

        return { message: 'Sikeres kijelentkezés' };
    },

    // SAJÁT ADATOK LEKÉRÉSE (Profil)
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

    // SZÁLLODÁK KERESÉSE
    async searchHotels(params) {
    // A params-ban jön a destination, check_in, stb.
    const queryString = new URLSearchParams(params).toString();
    
    // FIGYELJ: Kell az /api/ a BASE_URL miatt!
        const response = await fetch(`${BASE_URL}/hotels/search?${queryString}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Nem sikerült a hoteleket betölteni');
        }
        return await response.json();
    },

    // FOGLALÁSOK LEKÉRÉSE
    async getBookings() {
        const response = await fetch(`${BASE_URL}/bookings`, {
            method: 'GET',
            headers: this.getHeaders()
        });
        return await response.json();
    }
};

export default api;