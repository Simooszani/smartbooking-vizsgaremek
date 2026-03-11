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
export default {
  data() {
    return {
      isLogin: true,
      form: { name: '', email: '', password: '', password_confirmation: '' }
    }
  },
  methods: {
    async handleSubmit() {
      console.log("Küldés indítása...", this.isLogin ? "Login" : "Register"); // Ellenőrzéshez
      try {
        if (this.isLogin) {
          await API.login(this.form.email, this.form.password);
        } else {
          const response = await API.register(this.form);
          console.log("Backend válasza:", response);
        }
        this.$router.push('/dashboard');
      } catch (e) {
        console.error("Hiba történt:", e);
        alert("Hiba: " + (e.message || "Ismeretlen hiba történt a szerverrel való kommunikáció során."));
      }
    }
  }
}
</script>