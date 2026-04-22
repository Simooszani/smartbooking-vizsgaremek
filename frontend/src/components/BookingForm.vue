<template>
  <div class="booking-system">
    <!-- Search form -->
    <div class="row g-3 align-items-end">
      <div class="col-12 col-sm-6 col-md-4">
        <label class="form-label fw-semibold text-muted small text-uppercase">{{ t('search.destination') }}</label>
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0"><i class="bi bi-geo-alt text-coral"></i></span>
          <input v-model="search.destination" type="text" class="form-control border-start-0 ps-0"
            :placeholder="t('search.destination_placeholder')">
        </div>
      </div>
      <div class="col-6 col-sm-3 col-md-2">
        <label class="form-label fw-semibold text-muted small text-uppercase">{{ t('search.check_in') }}</label>
        <input v-model="search.check_in" type="date" class="form-control" :min="today">
      </div>
      <div class="col-6 col-sm-3 col-md-2">
        <label class="form-label fw-semibold text-muted small text-uppercase">{{ t('search.check_out') }}</label>
        <input v-model="search.check_out" type="date" class="form-control" :min="search.check_in || today">
      </div>
      <div class="col-6 col-sm-3 col-md-2">
        <label class="form-label fw-semibold text-muted small text-uppercase">{{ t('search.guests') }}</label>
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0"><i class="bi bi-people text-coral"></i></span>
          <input v-model.number="search.guests" type="number" class="form-control border-start-0 ps-0" min="1" max="20">
        </div>
      </div>
      <div class="col-6 col-sm-3 col-md-2">
        <button @click="performSearch" class="btn btn-search w-100 py-2" :disabled="searching">
          <span v-if="searching" class="spinner-border spinner-border-sm me-1"></span>
          <i v-else class="bi bi-search me-1"></i>
          {{ t('search.search_btn') }}
        </button>
      </div>
    </div>

    <!-- Results -->
    <div v-if="hotels.length > 0" class="mt-5">
      <div class="row g-4">
        <div class="col-12" v-for="hotel in hotels" :key="hotel.id">
          <div class="hotel-card" :class="{ 'hotel-card-expanded': expandedHotel === hotel.id }">
            <!-- Hotel header - clickable -->
            <div class="hotel-header" @click="toggleHotel(hotel.id)">
              <div class="row g-0 align-items-center">
                <div class="col-12 col-md-3">
                  <div class="hotel-img-wrapper">
                    <img :src="'https://picsum.photos/seed/hotel' + hotel.id + '/400/280'"
                      :alt="hotel.name" class="hotel-img">
                    <div class="hotel-rating-badge">
                      <i class="bi bi-star-fill me-1"></i>{{ hotel.rating }}
                    </div>
                  </div>
                </div>
                <div class="col-12 col-md-7">
                  <div class="hotel-info px-3 py-2">
                    <h4 class="hotel-name mb-1">{{ hotel.name }}</h4>
                    <p class="hotel-address mb-1">
                      <i class="bi bi-geo-alt-fill text-coral me-1"></i>{{ hotel.address }}
                    </p>
                    <p class="hotel-desc text-muted small mb-0" v-if="hotel.description">
                      {{ hotel.description.length > 120 ? hotel.description.substring(0, 120) + '...' : hotel.description }}
                    </p>
                  </div>
                </div>
                <div class="col-12 col-md-2 text-center">
                  <div class="hotel-price-preview py-2">
                    <div class="price-from text-muted small">{{ getMinPrice(hotel) }}</div>
                    <div class="fw-bold text-coral">Ft / {{ currentLocale === 'hu' ? 'éj' : 'night' }}</div>
                    <button class="btn btn-sm btn-outline-teal mt-2 rounded-pill px-3">
                      <i class="bi" :class="expandedHotel === hotel.id ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                      {{ expandedHotel === hotel.id ? t('hotel.hide_rooms') : t('hotel.show_rooms') }}
                    </button>
                    <button @click.stop="openChat(hotel)" class="btn btn-sm btn-outline-secondary mt-1 rounded-pill px-3" v-if="isLoggedIn">
                      <i class="bi bi-chat-dots me-1"></i>{{ t('chat.contact_hotel') }}
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Expanded content: rooms + reviews -->
            <transition name="slide">
              <div v-if="expandedHotel === hotel.id" class="hotel-details">
                <!-- Rooms grouped by category -->
                <div class="rooms-section p-4" v-if="hotel.rooms && hotel.rooms.length > 0">
                  <h5 class="fw-bold text-primary-dark mb-3">
                    <i class="bi bi-door-open me-2"></i>{{ t('hotel.rooms') }}
                  </h5>
                  <div v-for="cat in getRoomCategories(hotel)" :key="cat.type" class="room-category mb-3">
                    <div class="room-category-header" @click.stop="toggleCategory(hotel.id, cat.type)">
                      <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                          <div class="room-type-badge me-3">{{ cat.type }}</div>
                          <span class="text-muted small">
                            <i class="bi bi-people-fill me-1"></i>{{ cat.capacity }} {{ t('hotel.capacity') }}
                          </span>
                          <span class="badge bg-light text-dark border ms-2">
                            {{ cat.count }} {{ t('common.pieces') }}
                          </span>
                        </div>
                        <div class="d-flex align-items-center">
                          <div class="text-end me-3">
                            <span class="room-price">{{ Number(cat.minPrice).toLocaleString('hu-HU') }}</span>
                            <span class="room-price-label ms-1">{{ t('hotel.per_night') }}</span>
                          </div>
                          <i class="bi" :class="isCategoryOpen(hotel.id, cat.type) ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                        </div>
                      </div>
                    </div>
                    <transition name="slide">
                      <div v-if="isCategoryOpen(hotel.id, cat.type)" class="room-category-rooms mt-2">
                        <div class="row g-2">
                          <div class="col-12 col-sm-6 col-lg-4" v-for="room in cat.rooms" :key="room.id">
                            <div class="room-card-mini">
                              <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-muted">#{{ room.id }}</span>
                                <span class="fw-bold text-primary-dark">{{ Number(room.price_per_night).toLocaleString('hu-HU') }} {{ t('hotel.per_night') }}</span>
                              </div>
                              <div v-if="search.check_in && search.check_out" class="mt-1 small">
                                <span class="fw-bold text-coral">{{ getNights() }} {{ t('booking.nights') }}: {{ (getNights() * Number(room.price_per_night)).toLocaleString('hu-HU') }} Ft</span>
                                <span class="text-muted"> ({{ t('booking.tax_label') }}: {{ Math.round(getNights() * Number(room.price_per_night) * 0.05).toLocaleString('hu-HU') }} Ft)</span>
                              </div>
                              <button @click.stop="bookRoom(hotel, room)" class="btn btn-coral btn-sm w-100 mt-2 rounded-pill">
                                <i class="bi bi-calendar-plus me-1"></i>{{ t('hotel.select') }}
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </transition>
                  </div>
                </div>

                <!-- Reviews -->
                <div class="reviews-section p-4 border-top" v-if="hotel.reviews">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-primary-dark mb-0">
                      <i class="bi bi-chat-quote me-2"></i>{{ t('hotel.reviews') }}
                      <span class="badge bg-light text-muted ms-2" v-if="hotel.reviews.length">{{ hotel.reviews.length }}</span>
                    </h5>
                    <button @click.stop="openReviewModal(hotel)" class="btn btn-sm btn-outline-teal rounded-pill px-3">
                      <i class="bi bi-pencil-square me-1"></i>{{ t('hotel.write_review') }}
                    </button>
                  </div>
                  <div v-if="hotel.reviews.length > 0">
                    <div class="row g-2">
                      <div class="col-md-6" v-for="rev in hotel.reviews.slice(0, 4)" :key="rev.id">
                        <div class="review-card">
                          <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold small">{{ rev.user ? rev.user.name : 'Anonymous' }}</span>
                            <span class="review-stars">
                              <i v-for="n in 5" :key="n" class="bi" :class="n <= rev.rating ? 'bi-star-fill text-warning' : 'bi-star text-muted'"></i>
                            </span>
                          </div>
                          <p class="review-text mb-0">"{{ rev.comment }}"</p>
                        </div>
                      </div>
                    </div>
                  </div>
                  <p v-else class="text-muted small">{{ t('hotel.no_reviews') }}</p>
                </div>
              </div>
            </transition>
          </div>
        </div>
      </div>
    </div>

    <!-- No results -->
    <div v-else-if="searched" class="text-center py-5 mt-4">
      <i class="bi bi-emoji-frown display-1 text-muted"></i>
      <h4 class="mt-3 text-muted">{{ t('search.no_results') }}</h4>
      <p class="text-muted">{{ t('search.no_results_hint') }}</p>
    </div>
  </div>
</template>

<script>
import API from '../api/api'
import Swal from 'sweetalert2'

export default {
  data() {
    return {
      today: new Date().toISOString().split('T')[0],
      search: { destination: '', check_in: '', check_out: '', guests: 1 },
      hotels: [],
      searched: false,
      searching: false,
      expandedHotel: null,
      openCategories: {}
    }
  },
  computed: {
    isLoggedIn() {
      return !!localStorage.getItem('access_token');
    }
  },
  mounted() {
    const pending = localStorage.getItem('pendingBooking');
    if (pending) {
      const data = JSON.parse(pending);
      this.search = data.search;
      localStorage.removeItem('pendingBooking');
      this.performSearch();
    }
  },
  methods: {
    async performSearch() {
      this.searching = true;
      try {
        const data = await API.searchHotels(this.search);
        this.hotels = data;
        this.searched = true;
        this.expandedHotel = null;
      } catch (e) {
        Swal.fire({
          icon: 'error',
          title: this.t('common.error'),
          text: this.t('booking.error'),
          confirmButtonColor: '#e76f51'
        });
      } finally {
        this.searching = false;
      }
    },

    toggleHotel(hotelId) {
      this.expandedHotel = this.expandedHotel === hotelId ? null : hotelId;
    },

    getRoomCategories(hotel) {
      if (!hotel.rooms || hotel.rooms.length === 0) return [];
      const groups = {};
      hotel.rooms.forEach(room => {
        if (!groups[room.type]) {
          groups[room.type] = {
            type: room.type,
            capacity: room.capacity,
            minPrice: Number(room.price_per_night),
            count: 0,
            rooms: []
          };
        }
        groups[room.type].count++;
        groups[room.type].rooms.push(room);
        const price = Number(room.price_per_night);
        if (price < groups[room.type].minPrice) {
          groups[room.type].minPrice = price;
        }
      });
      return Object.values(groups);
    },

    toggleCategory(hotelId, type) {
      const key = hotelId + '_' + type;
      this.$set(this.openCategories, key, !this.openCategories[key]);
    },

    isCategoryOpen(hotelId, type) {
      return !!this.openCategories[hotelId + '_' + type];
    },

    getNights() {
      if (!this.search.check_in || !this.search.check_out) return 0;
      return Math.ceil((new Date(this.search.check_out) - new Date(this.search.check_in)) / 86400000);
    },

    getMinPrice(hotel) {
      if (!hotel.rooms || hotel.rooms.length === 0) return '—';
      const min = Math.min(...hotel.rooms.map(r => Number(r.price_per_night)));
      return Number(min).toLocaleString('hu-HU');
    },

    async bookRoom(hotel, room) {
      if (!this.search.check_in || !this.search.check_out) {
        Swal.fire({
          icon: 'warning',
          title: this.t('booking.date_required'),
          confirmButtonColor: '#264653'
        });
        return;
      }

      const token = localStorage.getItem('access_token');
      if (!token) {
        localStorage.setItem('pendingBooking', JSON.stringify({
          search: this.search
        }));
        Swal.fire({
          icon: 'info',
          title: this.t('booking.login_required'),
          confirmButtonColor: '#e76f51',
          confirmButtonText: this.t('navbar.login')
        }).then(() => {
          this.$router.push('/login?redirect=booking');
        });
        return;
      }

      const nights = Math.ceil((new Date(this.search.check_out) - new Date(this.search.check_in)) / 86400000);
      if (nights > 31) {
        Swal.fire({
          icon: 'warning',
          title: this.t('booking.max_nights_title'),
          text: this.t('booking.max_nights_text'),
          confirmButtonColor: '#264653'
        });
        return;
      }

      const totalPrice = nights * Number(room.price_per_night);
      const tax = Math.round(totalPrice * 0.05);
      const priceFormatted = totalPrice.toLocaleString('hu-HU');
      const taxFormatted = tax.toLocaleString('hu-HU');
      const perNightFormatted = Number(room.price_per_night).toLocaleString('hu-HU');

      const { isConfirmed } = await Swal.fire({
        icon: 'question',
        title: this.t('booking.confirm_title'),
        html: `<div class="text-start">
          <p><strong>${hotel.name}</strong></p>
          <p>${room.type}</p>
          <hr>
          <p>${nights} ${this.t('booking.nights')} × ${perNightFormatted} Ft</p>
          <h4 class="fw-bold">${priceFormatted} Ft</h4>
          <p class="text-muted small">(${this.t('booking.tax_label')}: ${taxFormatted} Ft)</p>
        </div>`,
        showCancelButton: true,
        confirmButtonText: this.t('booking.confirm_btn'),
        cancelButtonText: this.t('common.cancel'),
        confirmButtonColor: '#2a9d8f',
        cancelButtonColor: '#6c757d'
      });

      if (!isConfirmed) return;

      try {
        const bookingData = {
          hotel_id: hotel.id,
          room_id: room.id,
          check_in: this.search.check_in,
          check_out: this.search.check_out,
          guests: this.search.guests
        };

        await API.createBooking(bookingData);

        Swal.fire({
          icon: 'success',
          title: this.t('booking.success'),
          text: this.t('booking.success_text'),
          confirmButtonColor: '#2a9d8f'
        }).then(() => {
          this.$router.push('/dashboard');
        });

      } catch (e) {
        if (e.status === 401 || (e.message && e.message.includes('Unauthenticated'))) {
          Swal.fire({
            icon: 'warning',
            title: this.t('booking.session_expired'),
            confirmButtonColor: '#e76f51'
          });
          localStorage.removeItem('access_token');
          this.$router.push('/login');
        } else {
          Swal.fire({
            icon: 'error',
            title: this.t('booking.failed'),
            text: e.message || this.t('booking.room_taken'),
            confirmButtonColor: '#e76f51'
          });
        }
      }
    },

    openChat(hotel) {
      this.$router.push('/chat?hotel=' + hotel.id + '&hotelName=' + encodeURIComponent(hotel.name));
    },

    async openReviewModal(hotel) {
      const token = localStorage.getItem('access_token');
      if (!token) {
        Swal.fire({
          icon: 'info',
          title: this.t('review.login_required'),
          confirmButtonColor: '#e76f51'
        });
        return;
      }

      const { value: formValues } = await Swal.fire({
        title: this.t('review.title'),
        html:
          `<div class="text-start mb-3">
            <label class="form-label fw-semibold">${this.t('review.rating_label')}</label>
            <select id="swal-rating" class="form-select">
              <option value="5">&#9733;&#9733;&#9733;&#9733;&#9733; (5)</option>
              <option value="4">&#9733;&#9733;&#9733;&#9733;&#9734; (4)</option>
              <option value="3">&#9733;&#9733;&#9733;&#9734;&#9734; (3)</option>
              <option value="2">&#9733;&#9733;&#9734;&#9734;&#9734; (2)</option>
              <option value="1">&#9733;&#9734;&#9734;&#9734;&#9734; (1)</option>
            </select>
          </div>
          <div class="text-start">
            <label class="form-label fw-semibold">${this.t('review.comment_label')}</label>
            <textarea id="swal-comment" class="form-control" rows="3" placeholder="${this.t('review.comment_placeholder')}"></textarea>
          </div>`,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: this.t('review.submit'),
        cancelButtonText: this.t('common.cancel'),
        confirmButtonColor: '#2a9d8f',
        preConfirm: () => {
          const comment = document.getElementById('swal-comment').value;
          if (!comment.trim()) {
            Swal.showValidationMessage(this.t('review.comment_placeholder'));
            return false;
          }
          return {
            hotel_id: hotel.id,
            rating: parseInt(document.getElementById('swal-rating').value),
            comment: comment
          }
        }
      });

      if (formValues) {
        try {
          const newReview = await API.createReview(formValues);
          hotel.reviews.unshift(newReview);

          Swal.fire({
            icon: 'success',
            title: this.t('review.success'),
            timer: 2000,
            showConfirmButton: false
          });
        } catch (e) {
          Swal.fire({
            icon: 'error',
            title: this.t('common.error'),
            text: e.message || this.t('review.already_reviewed'),
            confirmButtonColor: '#e76f51'
          });
        }
      }
    }
  }
}
</script>

<style scoped>
.text-coral { color: #e76f51; }
.text-primary-dark { color: #264653; }

.btn-search {
  background: linear-gradient(135deg, #2a9d8f, #264653);
  color: #fff;
  font-weight: 600;
  border: none;
  border-radius: 10px;
  transition: all 0.3s;
}
.btn-search:hover { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(42,157,143,0.4); color: #fff; }

.btn-coral {
  background: #e76f51;
  color: #fff;
  border: none;
  font-weight: 600;
}
.btn-coral:hover { background: #d45d3f; color: #fff; }

.btn-outline-teal {
  border: 2px solid #2a9d8f;
  color: #2a9d8f;
  font-weight: 500;
}
.btn-outline-teal:hover { background: #2a9d8f; color: #fff; }

/* Hotel card */
.hotel-card {
  background: #fff;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 2px 12px rgba(0,0,0,0.06);
  transition: all 0.3s ease;
  border: 2px solid transparent;
}
.hotel-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,0.1); }
.hotel-card-expanded { border-color: #2a9d8f; box-shadow: 0 8px 30px rgba(42,157,143,0.15); }

.hotel-header { cursor: pointer; transition: background 0.2s; }
.hotel-header:hover { background: #fafafa; }

.hotel-img-wrapper { position: relative; overflow: hidden; height: 180px; }
.hotel-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
.hotel-header:hover .hotel-img { transform: scale(1.05); }

.hotel-rating-badge {
  position: absolute;
  top: 10px;
  right: 10px;
  background: rgba(38,70,83,0.9);
  color: #e9c46a;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 700;
}

.hotel-name { color: #264653; font-weight: 700; font-size: 1.15rem; }
.hotel-address { color: #666; font-size: 0.9rem; }

.price-from { font-size: 1.2rem; font-weight: 700; color: #264653; }

/* Room card */
.room-card {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 1rem;
  border: 1px solid #eee;
  transition: all 0.2s;
}
.room-card:hover { border-color: #2a9d8f; background: #f0faf8; }

.room-type-badge {
  display: inline-block;
  background: linear-gradient(135deg, #264653, #2a9d8f);
  color: #fff;
  padding: 3px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
}
.room-price { font-size: 1.1rem; font-weight: 700; color: #264653; }
.room-price-label { font-size: 0.7rem; color: #999; }

/* Room category */
.room-category-header {
  background: #f8f9fa;
  border-radius: 12px;
  padding: 0.85rem 1rem;
  cursor: pointer;
  border: 1px solid #eee;
  transition: all 0.2s;
}
.room-category-header:hover { border-color: #2a9d8f; background: #f0faf8; }
.room-card-mini {
  background: #fff;
  border-radius: 10px;
  padding: 0.75rem;
  border: 1px solid #e9ecef;
  transition: all 0.2s;
}
.room-card-mini:hover { border-color: #2a9d8f; }

/* Review card */
.review-card {
  background: #fafaf5;
  border-radius: 10px;
  padding: 0.75rem 1rem;
  border-left: 3px solid #e9c46a;
}
.review-text { font-style: italic; font-size: 0.85rem; color: #555; }
.review-stars { font-size: 0.75rem; }

/* Slide transition */
.slide-enter-active, .slide-leave-active {
  transition: all 0.35s ease;
  max-height: 1000px;
  overflow: hidden;
}
.slide-enter, .slide-leave-to {
  max-height: 0;
  opacity: 0;
}

.form-control:focus, .form-select:focus {
  border-color: #2a9d8f;
  box-shadow: 0 0 0 0.2rem rgba(42,157,143,0.15);
}
.input-group-text {
  border-color: #dee2e6;
}

@media (max-width: 768px) {
  .hotel-img-wrapper { height: 200px; }
  .hotel-info { padding: 1rem !important; }
  .hotel-name { font-size: 1rem; }
  .hotel-price-preview { padding: 0.5rem 1rem !important; }
  .room-category-header { padding: 0.65rem 0.75rem; }
  .room-category-header .d-flex { flex-wrap: wrap; gap: 0.5rem; }
}

@media (max-width: 576px) {
  .hotel-img-wrapper { height: 160px; }
  .hotel-name { font-size: 0.95rem; }
  .hotel-address { font-size: 0.8rem; }
  .price-from { font-size: 1rem; }
  .room-price { font-size: 0.95rem; }
}
</style>
