// resources/js/services/AuthService.js
import axios from 'axios';

const API_URL = 'http://127.0.0.1:8000/api';

export default {
  // Login user and get token
  async login(email, password) {
    const response = await axios.post(`${API_URL}/login`, { email, password });

    if (response.data.token) {
      localStorage.setItem('auth_token', response.data.token);
      axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
    }

    return response.data;
  },

  // Logout user
  async logout() {
    try {
      await axios.post(`${API_URL}/logout`);
    } finally {
      localStorage.removeItem('auth_token');
      delete axios.defaults.headers.common['Authorization'];
    }
  },

  // Get current user info
  async getCustomerInfo() {
    return axios.get(`${API_URL}/customer`);
  },

  // Check if user is logged in
  isLoggedIn() {
    return !!localStorage.getItem('auth_token');
  },

  // Get auth token
  getToken() {
    return localStorage.getItem('auth_token');
  },

  // Set auth token in axios headers
  setAuthHeader() {
    const token = this.getToken();
    if (token) {
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    }
  }
};
//
resources/js/services/AuthService.js
