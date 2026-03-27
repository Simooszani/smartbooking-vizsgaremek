import Vue from 'vue'
import VueRouter from 'vue-router'
import Swal from 'sweetalert2'
import Login from '../views/Login.vue'
import Dashboard from '../views/Dashboard.vue'
import HomeView from '../views/HomeView.vue'
import AdminDashboard from '../views/AdminDashboard.vue'
import AdminUserList from '../views/AdminUserList.vue'
import AdminRoomList from '../views/AdminRoomList.vue'
import AdminHotelList from '../views/AdminHotelList.vue'

Vue.use(VueRouter)

const adminGuard = (to, from, next) => {
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  if (user.is_admin) {
    next();
  } else {
    Swal.fire({
      icon: 'error',
      title: 'Access Denied',
      text: 'Nincs admin jogosultságod!',
      confirmButtonColor: '#e76f51'
    });
    next('/dashboard');
  }
};

const routes = [
  { path: '/', component: HomeView },
  { path: '/login', component: Login },
  { path: '/dashboard', component: Dashboard },

  {
    path: '/admin',
    component: AdminDashboard,
    beforeEnter: adminGuard
  },
  {
    path: '/admin/users',
    component: AdminUserList,
    beforeEnter: adminGuard
  },
  {
    path: '/admin/rooms',
    component: AdminRoomList,
    beforeEnter: adminGuard
  },
  {
    path: '/admin/hotels',
    component: AdminHotelList,
    beforeEnter: adminGuard
  }
]

const router = new VueRouter({
  mode: 'history',
  routes
})

export default router
