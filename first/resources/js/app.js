import { createApp } from 'vue';
import App from './components/App.vue'; // Updated path to App.vue
import router from './router'; // Ensure the correct path to your router file
import axios from 'axios';

// Importing Bootstrap and Bootstrap Icons
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import 'bootstrap-icons/font/bootstrap-icons.css';
import 'bootstrap/dist/js/bootstrap.bundle';


// Set up axios defaults
axios.defaults.withCredentials = true;
axios.defaults.headers.common["Accept"] = "application/json";

// Check for token on app start and auto-attach Authorization token if available
const token = localStorage.getItem('auth_token');
if (token) {
  axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

// Add a response interceptor
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response && error.response.status === 401) {
      // Clear any stored tokens
      localStorage.removeItem('auth_token');

      // Redirect to login page
      router.push('/login');
    }
    return Promise.reject(error);
  }
);

const app = createApp(App);
app.use(router);
app.mount('#app');
