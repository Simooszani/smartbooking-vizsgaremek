<template>
  <div class="login-page">
    <div class="container">
      <div class="row justify-content-center align-items-center min-vh-100 py-5">
        <div class="col-lg-5 col-md-7">
          <div class="text-center mb-4">
            <div class="login-logo mb-3">
              <i class="bi bi-building"></i>
            </div>
            <h2 class="fw-bold text-primary-dark">{{ t('login.welcome') }}</h2>
            <p class="text-muted">{{ t('login.welcome_text') }}</p>
          </div>

          <div class="login-card">
            <!-- Tab switcher -->
            <div class="login-tabs mb-4">
              <button @click="isLogin = true" class="login-tab" :class="{ active: isLogin }">
                <i class="bi bi-box-arrow-in-right me-2"></i>{{ t('login.title') }}
              </button>
              <button @click="isLogin = false" class="login-tab" :class="{ active: !isLogin }">
                <i class="bi bi-person-plus me-2"></i>{{ t('login.register_title') }}
              </button>
            </div>

            <form @submit.prevent="handleSubmit">
              <transition name="fade" mode="out-in">
                <div v-if="!isLogin" key="name" class="mb-3">
                  <label class="form-label fw-semibold small text-muted text-uppercase">{{ t('login.name') }}</label>
                  <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-person text-muted"></i></span>
                    <input v-model="form.name" type="text" class="form-control border-start-0 ps-0" required>
                  </div>
                </div>
              </transition>

              <div class="mb-3">
                <label class="form-label fw-semibold small text-muted text-uppercase">{{ t('login.email') }}</label>
                <div class="input-group">
                  <span class="input-group-text bg-white"><i class="bi bi-envelope text-muted"></i></span>
                  <input v-model="form.email" type="email" class="form-control border-start-0 ps-0" required>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold small text-muted text-uppercase">{{ t('login.password') }}</label>
                <div class="input-group">
                  <span class="input-group-text bg-white"><i class="bi bi-lock text-muted"></i></span>
                  <input v-model="form.password" type="password" class="form-control border-start-0 ps-0" required>
                </div>
              </div>

              <transition name="fade" mode="out-in">
                <div v-if="!isLogin" key="confirm" class="mb-3">
                  <label class="form-label fw-semibold small text-muted text-uppercase">{{ t('login.password_confirm') }}</label>
                  <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-lock-fill text-muted"></i></span>
                    <input v-model="form.password_confirmation" type="password" class="form-control border-start-0 ps-0" required>
                  </div>
                </div>
              </transition>

              <button type="submit" class="btn btn-login w-100 py-2 mt-2" :disabled="submitting">
                <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                {{ isLogin ? t('login.submit_login') : t('login.submit_register') }}
              </button>
            </form>

            <div class="text-center mt-4">
              <a href="#" @click.prevent="isLogin = !isLogin" class="switch-link">
                {{ isLogin ? t('login.switch_to_register') : t('login.switch_to_login') }}
              </a>
            </div>
          </div>
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
      submitting: false,
      form: { name: '', email: '', password: '', password_confirmation: '' }
    }
  },
  methods: {
    showToast(icon, title) {
      Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
      }).fire({ icon, title });
    },

    async handleSubmit() {
      this.submitting = true;
      try {
        if (this.isLogin) {
          const data = await API.login({
            email: this.form.email,
            password: this.form.password
          });

          localStorage.setItem('loginSuccess', 'true');

          const user = data.user || JSON.parse(localStorage.getItem('user') || '{}');
          if (user.role === 'admin' || user.role === 'super_admin') {
            window.location.href = '/admin';
          } else if (user.role === 'hotel_admin') {
            window.location.href = '/hotel-admin';
          } else {
            window.location.href = '/dashboard';
          }
        } else {
          await API.register(this.form);

          await Swal.fire({
            icon: 'success',
            title: this.t('login.register_success'),
            text: this.t('login.register_success_text'),
            confirmButtonColor: '#2a9d8f'
          });

          this.isLogin = true;
        }
      } catch (e) {
        let errorText = e.message || this.t('login.error_text');
        // Show suspension info
        if (e.suspended_until) {
          const date = new Date(e.suspended_until).toLocaleDateString(this.currentLocale === 'hu' ? 'hu-HU' : 'en-US', {
            year: 'numeric', month: 'long', day: 'numeric'
          });
          errorText = (this.currentLocale === 'hu'
            ? `A fiókod fel van függesztve ${date}-ig!`
            : `Your account is suspended until ${date}!`);
        }
        Swal.fire({
          icon: 'error',
          title: this.t('login.error'),
          text: errorText,
          confirmButtonColor: '#e76f51'
        });
      } finally {
        this.submitting = false;
      }
    }
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f8f9fa 0%, #e8f4f2 50%, #faf3eb 100%);
}

.text-primary-dark { color: #264653; }

.login-logo {
  width: 70px;
  height: 70px;
  background: linear-gradient(135deg, #264653, #2a9d8f);
  border-radius: 18px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 2rem;
}

.login-card {
  background: #fff;
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 10px 40px rgba(0,0,0,0.08);
}

.login-tabs {
  display: flex;
  background: #f8f9fa;
  border-radius: 12px;
  padding: 4px;
}
.login-tab {
  flex: 1;
  padding: 10px;
  border: none;
  background: transparent;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.9rem;
  color: #999;
  transition: all 0.3s;
  cursor: pointer;
}
.login-tab.active {
  background: #fff;
  color: #264653;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.btn-login {
  background: linear-gradient(135deg, #264653, #2a9d8f);
  color: #fff;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  font-size: 1rem;
  transition: all 0.3s;
}
.btn-login:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 15px rgba(42,157,143,0.4);
  color: #fff;
}

.switch-link {
  color: #2a9d8f;
  text-decoration: none;
  font-weight: 500;
  font-size: 0.9rem;
}
.switch-link:hover { color: #264653; text-decoration: underline; }

.form-control:focus {
  border-color: #2a9d8f;
  box-shadow: 0 0 0 0.2rem rgba(42,157,143,0.15);
}

.input-group-text {
  border-color: #dee2e6;
}

.fade-enter-active, .fade-leave-active { transition: all 0.3s ease; }
.fade-enter, .fade-leave-to { opacity: 0; transform: translateY(-10px); }
</style>
