<template>
  <div class="admin-login-container">
    <div class="login-card">
      <h2 class="text-center mb-4">Staff Login</h2>
      <form @submit.prevent="loginStaff">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input
            type="email"
            class="form-control"
            v-model="email"
            required
          >
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input
            type="password"
            class="form-control"
            v-model="password"
            required
          >
        </div>
        <div v-if="errorMessage" class="alert alert-danger">
          {{ errorMessage }}
        </div>
        <button type="submit" class="btn btn-primary w-100" :disabled="loading">
          {{ loading ? 'Logging in...' : 'Login' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'AdminLogin',
  data() {
    return {
      email: '',
      password: '',
      errorMessage: '',
      loading: false
    };
  },
  methods: {
    async loginStaff() {
      this.loading = true;
      this.errorMessage = '';

      try {
        // Check for hardcoded admin first
        if (this.email === 'admin@gmail.com' && this.password === 'admin12345') {
          localStorage.setItem('admin_token', 'fake_admin_token');
          localStorage.setItem('user_role', 'admin');
          axios.defaults.headers.common['Authorization'] = `Bearer fake_admin_token`;
          this.$router.push('/admin/dashboard');
          return;
        }

        // Technician login
        const response = await axios.post('http://127.0.0.1:8000/api/technician/login', {
          email: this.email,
          password: this.password
        });

        if (response.data.token) {
          localStorage.setItem('tech_token', response.data.token);
          localStorage.setItem('user_role', 'technician');
          localStorage.setItem('tech_data', JSON.stringify(response.data.technician));
          axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
          this.$router.push('/tech/all-records');
        }
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'Login failed';
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>

<style scoped>
.admin-login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  padding: 20px;
}

.login-card {
  background-color: #06A5FF;
  border-radius: 8px;
  padding: 30px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
}
</style>
