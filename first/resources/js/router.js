import { createRouter, createWebHistory } from 'vue-router';

// Import your components
import Home from './components/Home.vue';
import Login from './components/Login.vue';
import Signup from './components/Signup.vue';
import CustomerDashboard from "./components/CustomerDashboard.vue";
import CustomerInfo from "./components/CustomerInfo.vue";
import BookingAppointment from './components/BookingAppointment.vue';
import AllBookings from './components/AllBookings.vue';
import AdminLogin from './components/admin/AdminLogin.vue';
import CustomerList from './components/admin/CustomersList.vue';
import CustomersBooking from './components/admin/CustomersBooking.vue';
import AllRecords from './components/admin/AllRecords.vue';
import CustomerHistory from './components/CustomerHistory.vue';
import TechBookingsList from './components/tech/TechBookingsList.vue';
import TechRecords from './components/tech/TechRecords.vue';
import RegisterTechnician from './components/admin/RegisterTechnician.vue';

// Define routes
const routes = [
  { path: '/', component: Home },
  { path: '/login', name: 'Login', component: Login },
  { path: '/signup', name: 'Signup', component: Signup },
  { path: '/admin', name: 'AdminLogin', component: AdminLogin },
  {
    path: '/dashboard/customer-info',
    name: 'CustomerInfo',
    component: CustomerInfo,
    meta: { requiresAuth: true }
  },
  {
    path: '/dashboard/book-appointment',
    name: 'BookAppointment',
    component: BookingAppointment,
    meta: { requiresAuth: true }
  },
  {
    path: '/dashboard/all-bookings',
    name: 'AllBookings',
    component: AllBookings,
    meta: { requiresAuth: true }
  },

  {
    path: '/dashboard/bookings-history',
    name: 'CustomerHistory',
    component: CustomerHistory,
    meta: { requiresAuth: true }
  },

  {
  path: '/admin/dashboard',
  name: 'AdminDashboard',
  component: CustomerList,
  meta: { requiresAdminAuth: true }
  },


  {
    path: '/admin/bookings',
    name: 'AdminBookings' ,
    component: CustomersBooking,
    meta: { requiresAdminAuth: true }
  },

  {
    path: '/admin/all-records',
    name: 'AllRecords',
    component: AllRecords,
    meta: { requiresAdmin: true }
  },

  {
    path: '/tech/all-records',
    name: 'TechBookingsList',
    component: TechBookingsList,
    meta: { requiresAdmin: true }
  },

    {
    path: '/tech/records',
    name: 'TechRecords',
    component: TechRecords,
    meta: { requiresAdmin: true }
    },

    {
    path: '/admin/register-technician',
    name: 'RegisterTechnician',
    component: RegisterTechnician,
    meta: { requiresAdminAuth: true }
    }

];

// Create router with web history instead of hash history
const router = createRouter({
  history: createWebHistory(), // Changed from createWebHashHistory to createWebHistory
  routes,
  scrollBehavior(to) {
    if (to.hash) {
      return {
        el: to.hash,
        behavior: 'smooth'
      };
    } else {
      return { top: 0 };
    }
  }
});

// Navigation guard to check authentication
router.beforeEach((to, from, next) => {
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
  const requiresAdminAuth = to.matched.some(record => record.meta.requiresAdminAuth);
  const isAuthenticated = localStorage.getItem("auth_token"); // Check if token exists
  const isAdminAuthenticated = localStorage.getItem("admin_token");

  if (requiresAdminAuth && !isAdminAuthenticated) {
    next('/admin');

  }else if (requiresAuth && !isAuthenticated) {
    next('/login'); // Redirect to login page if not authenticated
  } else {
    next(); // Allow access if authenticated
  }
});

export default router;
