<template>
  <div class="sidebar bg-dark text-white shadow shadow-lg">
    <div class="p-4 text-center border-bottom border-secondary">
      <h5 class="fw-bold mb-0 text-danger">SmartBooking</h5>
      <small class="text-muted">Admin Panel</small>
    </div>
    
    <nav class="nav flex-column mt-3">
      <router-link to="/admin" class="nav-link text-white py-3 px-4 d-flex align-items-center" active-class="active-link">
        <i class="bi bi-calendar-check me-3"></i> Foglalások
      </router-link>
      
      <router-link to="/admin/users" class="nav-link text-white py-3 px-4 d-flex align-items-center" active-class="active-link">
        <i class="bi bi-people me-3"></i> Felhasználók
      </router-link>
      
      <router-link to="/admin/rooms" class="nav-link text-white py-3 px-4 d-flex align-items-center" active-class="active-link">
        <i class="bi bi-door-open me-3"></i> Szobák kezelése
      </router-link>

      <hr class="mx-3 border-secondary">

      <router-link to="/" class="nav-link text-muted py-3 px-4 d-flex align-items-center shadow-hover">
        <i class="bi bi-house-door me-3"></i> Vissza a főoldalra
      </router-link>
    </nav>
  </div>
</template>

<style scoped>
.sidebar {
  width: 260px;
  min-height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
}
.nav-link {
  transition: all 0.3s;
  border-left: 4px solid transparent;
}
.nav-link:hover {
  background: rgba(255, 255, 255, 0.1);
}
.active-link {
  background: rgba(220, 53, 69, 0.15) !important;
  border-left: 4px solid #dc3545 !important;
  color: #dc3545 !important;
}
</style>