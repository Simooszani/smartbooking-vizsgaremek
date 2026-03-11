<template>
  <div class="reviews-section mt-4">
    <h5 class="mb-3">Vendégvélemények ({{ reviews.length }})</h5>
    
    <div v-for="review in reviews" :key="review.id" class="card mb-2 border-0 bg-light shadow-sm">
      <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <strong class="text-primary">{{ review.user ? review.user.name : 'Névtelen utazó' }}</strong>
          <div class="stars text-warning">
            <span v-for="n in 5" :key="n">
              <i :class="n <= review.rating ? 'bi bi-star-fill' : 'bi bi-star'"></i>
            </span>
          </div>
        </div>
        <p class="card-text mb-1 italic">"{{ review.comment }}"</p>
        <small class="text-muted">{{ formatDate(review.created_at) }}</small>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    reviews: {
      type: Array,
      default: () => []
    }
  },
  methods: {
    formatDate(dateStr) {
      if (!dateStr) return '';
      const date = new Date(dateStr);
      return date.toLocaleDateString('hu-HU');
    }
  }
}
</script>

<style scoped>
.italic { font-style: italic; font-size: 0.95rem; }
.stars { font-size: 0.8rem; }
.bi-star-fill, .bi-star { margin-left: 2px; }
</style>