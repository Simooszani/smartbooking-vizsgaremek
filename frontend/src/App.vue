<template>
  <div id="app">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow">
      <div class="container">
        <router-link class="navbar-brand fw-bold" to="/">SmartBooking</router-link>
        <div class="navbar-nav ms-auto">
          <router-link class="nav-link" to="/">Kezdőlap</router-link>
          <router-link class="nav-link" to="/dashboard">Foglalásaim</router-link>
        </div>
      </div>
    </nav>
    <div class="container">
      <router-view></router-view>
    </div>
  </div>
</template>

<style>
@import url('https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
@import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css');

body {
  background-color: #f4f7f6;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
</style>