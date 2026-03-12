<template>
  <div class="row justify-content-center mt-5">
    <div class="col-md-5">
      <div class="card shadow-lg border-0 p-4">
        <h2 class="text-center mb-4">{{ isLogin ? 'Bejelentkezés' : 'Regisztráció' }}</h2>
        <form @submit.prevent="handleSubmit">
          <div v-if="!isLogin" class="mb-3">
            <label class="form-label">Név</label>
            <input v-model="form.name" type="text" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email cím</label>
            <input v-model="form.email" type="email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Jelszó</label>
            <input v-model="form.password" type="password" class="form-control" required>
          </div>
          <div v-if="!isLogin" class="mb-3">
            <label class="form-label">Jelszó megerősítése</label>
            <input v-model="form.password_confirmation" type="password" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-primary w-100 py-2">{{ isLogin ? 'Belépés' : 'Regisztráció' }}</button>
        </form>
        <div class="text-center mt-3">
          <a href="#" @click.prevent="isLogin = !isLogin" class="text-decoration-none">
            {{ isLogin ? 'Nincs még fiókod? Regisztrálj!' : 'Van már fiókod? Jelentkezz be!' }}
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import API from '../api/api'
import Swal from 'sweetalert2'

export default {
  data() {
    return {
      isLogin: true,
      form: { name: '', email: '', password: '', password_confirmation: '' }
    }
  },
  methods: {
    showToast(icon, title) {
      const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
      });
      Toast.fire({ icon, title });
    },
      async handleSubmit() {
      try {
        if (this.isLogin) {
            await API.login({ 
              email: this.form.email, 
              password: this.form.password 
            });
            
            localStorage.setItem('loginSuccess', 'true');

            window.location.href = '/admin'; 
          } else {
          await API.register(this.form);
          
          await Swal.fire({
            icon: 'success',
            title: 'Sikeres regisztráció!',
            text: 'Most már bejelentkezhetsz a fiókodba.',
            confirmButtonColor: '#0d6efd'
          });
          
          this.isLogin = true;
          return; 
        }


      } catch (e) {
        Swal.fire({
          icon: 'error',
          title: 'Hiba történt',
          text: e.response?.data?.message || "Sikertelen művelet! Ellenőrizd az adataidat.",
          confirmButtonColor: '#dc3545'
        });
      }
    }
  }
}
</script>