<template>
  <div class="home-page">
    <header class="bg-dark text-white py-5 mb-5 shadow">
      <div class="container text-center">
        <h1 class="display-4 fw-bold">Találd meg a pihenésed helyszínét!</h1>
        <p class="lead">Több mint 10 000 szálláshely vár rád országszerte.</p>
      </div>
    </header>

    <div class="container">
      <BookingForm />
    </div>
  </div>
</template>

<script>
import BookingForm from '@/components/BookingForm.vue'

export default {
  name: 'HomeView',
  components: {
    BookingForm
  }
}
</script>

<style scoped>
.home-page {
  min-height: 100vh;
}
header {
  background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://loremflickr.com/1200/400/hotel') no-repeat center center;
  background-size: cover;
}
</style>