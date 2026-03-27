<template>
  <div id="app">
    <Navbar ref="navbar" />
    <router-view></router-view>
  </div>
</template>

<script>
import Navbar from '@/components/Navbar.vue'
import API from '@/api/api'

export default {
  name: 'App',
  components: {
    Navbar
  },
  async mounted() {
    // Validate token on app load - clear stale/invalid tokens
    const token = localStorage.getItem('access_token');
    if (token) {
      try {
        await API.getMe();
      } catch (e) {
        localStorage.removeItem('access_token');
        localStorage.removeItem('user');
        if (this.$refs.navbar) {
          this.$refs.navbar.$forceUpdate();
        }
      }
    }
  }
}
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

:root {
  --sb-primary: #264653;
  --sb-secondary: #2a9d8f;
  --sb-accent: #e9c46a;
  --sb-warm: #f4a261;
  --sb-coral: #e76f51;
  --sb-light: #f8f9fa;
  --sb-dark: #1a1a2e;
}

body {
  background-color: var(--sb-light);
  font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #333;
}

#app {
  min-height: 100vh;
}

/* Custom scrollbar */
::-webkit-scrollbar { width: 8px; }
::-webkit-scrollbar-track { background: #f1f1f1; }
::-webkit-scrollbar-thumb { background: var(--sb-secondary); border-radius: 4px; }
::-webkit-scrollbar-thumb:hover { background: var(--sb-primary); }

/* SweetAlert2 custom theme */
.swal2-popup {
  font-family: 'Inter', sans-serif !important;
  border-radius: 16px !important;
}
.swal2-confirm {
  border-radius: 8px !important;
}
.swal2-cancel {
  border-radius: 8px !important;
}
</style>
