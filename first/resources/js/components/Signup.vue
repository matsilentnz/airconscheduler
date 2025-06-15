<template>
    <div class="signup-container">
      <h2>Sign Up</h2>
      <form @submit.prevent="signup">
        <div class="mb-3">
          <label class="form-label">Name</label>
          <input type="text" class="form-control" v-model="name" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" v-model="email" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" class="form-control" v-model="password" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Address</label>
          <input type="text" class="form-control" v-model="address" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Phone</label>
          <input type="text" class="form-control" v-model="phone" required>
        </div>
        <button type="submit" class="btn btn-primary">Sign Up</button>
      </form>

      <p class="signup-link">
        Have an account?
        <router-link to="/login">Login</router-link>
      </p>

    </div>
  </template>

  <script>
  import axios from 'axios';

  export default {
    data() {
      return {
        name: '',
        email: '',
        password: '',
        address: '',
        phone: ''
      };
    },
    methods: {
        signup() {
  axios.post('http://127.0.0.1:8000/api/register', {
    name: this.name,
    email: this.email,
    password: this.password,
    address: this.address,
    phone: this.phone
  })
  .then(response => {
    alert(response.data.message);
    console.log('Success:', response.data);
    this.$router.push('/login');
  })
  .catch(error => {
    if (error.response) {
      console.error('Signup failed:', error.response.data);
      if (error.response.data.errors) {
        alert('Signup failed: ' + JSON.stringify(error.response.data.errors));
      } else {
        alert('Signup failed: ' + error.response.data.message);
      }
    } else {
      console.error('Error:', error.message);
      alert('Signup failed: ' + error.message);
    }
  });
}

}

  };
  </script>

  <style scoped>
  .signup-container {
    max-width: 400px;
    margin: 100px auto;
    padding: 20px;
    background: #06A5FF;
    box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    text-align: center;
    margin-top: 200px;
  }

  form label {
  text-align: left;
  display: block;
}

form input {
  text-align: left;
  display: block;
  width: 100%;
}

  .login-link {
    margin-top: 15px;
  }

  .login-link a {
    color: white;
    text-decoration: none;
    font-weight: bold;
  }

  .login-link a:hover {
    text-decoration: underline;
  }
  </style>
