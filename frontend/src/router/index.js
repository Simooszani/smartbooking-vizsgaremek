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
import HotelAdminDashboard from '../views/HotelAdminDashboard.vue'
import NotificationsView from '../views/NotificationsView.vue'

Vue.use(VueRouter)

const adminGuard = (to, from, next) => {
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  if (user.role === 'admin' || user.role === 'super_admin') {
    next();
  } else {
    Swal.fire({
      icon: 'error',
      title: 'Access Denied',
      text: 'Nincs admin jogosultságod!',
      confirmButtonColor: '#e76f51'
    });
    next('/');
  }
};

const hotelAdminGuard = (to, from, next) => {
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  if (user.role === 'hotel_admin' || user.role === 'admin' || user.role === 'super_admin') {
    next();
  } else {
    Swal.fire({
      icon: 'error',
      title: 'Access Denied',
      text: 'Nincs jogosultságod!',
      confirmButtonColor: '#e76f51'
    });
    next('/');
  }
};

const authGuard = (to, from, next) => {
  if (localStorage.getItem('access_token')) {
    next();
  } else {
    next('/login');
  }
};

const routes = [
  { path: '/', component: HomeView },
  { path: '/login', component: Login },
  { path: '/dashboard', component: Dashboard },
  { path: '/notifications', component: NotificationsView, beforeEnter: authGuard },

  {
    path: '/hotel-admin',
    component: HotelAdminDashboard,
    beforeEnter: hotelAdminGuard
  },

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
