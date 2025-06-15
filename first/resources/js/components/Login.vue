<template>
    <div class="login-container">
      <h2>Login</h2>

      <form @submit.prevent="login">
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" v-model="email" name="email" required />
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" v-model="password" required />
        </div>

        <button type="submit" class="btn btn-primary" :disabled="loading">
          {{ loading ? "Logging in..." : "Login" }}
        </button>

        <p v-if="errorMessage" class="text-danger mt-2">{{ errorMessage }}</p>
      </form>

      <p class="signup-link">
        Don't have an account?
        <router-link to="/signup">Sign Up</router-link>
      </p>
    </div>
  </template>

  <script>
  import axios from "axios";

  export default {
    data() {
      return {
        email: "",
        password: "",
        errorMessage: "",
        loading: false,
      };
    },
    mounted() {
      // Redirect if already logged in
      if (localStorage.getItem("auth_token")) {
        this.$router.push("/dashboard/customer-info");
      }
    },
    methods: {
      async login() {
        this.errorMessage = "";
        this.loading = true;

        try {
          const response = await axios.post("http://127.0.0.1:8000/api/login", {
            email: this.email,
            password: this.password,
          });

          console.log("Login Response:", response.data); // Debugging

          if (response.data.token) {
            localStorage.setItem("auth_token", response.data.token);
            axios.defaults.headers.common["Authorization"] = "Bearer " + response.data.token;
            this.$router.push("/dashboard/customer-info");
          } else {
            this.errorMessage = "Login failed. No token received.";
          }
        } catch (error) {
          if (error.response) {
            if (error.response.status === 401) {
              this.errorMessage = "Invalid email or password. Please try again.";
            } else {
              this.errorMessage = `Login failed: ${error.response.data.message || "Server error."}`;
            }
          } else {
            this.errorMessage = "Network error. Please check your connection.";
          }
        } finally {
          this.loading = false;
        }
      },
    },
  };
  </script>

  <style scoped>
  .login-container {
    max-width: 400px;
    margin: 100px auto;
    padding: 20px;
    background: #06a5ff;
    box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    text-align: center;
    margin-top: 200px;
    color: white;
  }

  .signup-link {
    margin-top: 15px;
    font-weight: bold;
  }

  .signup-link a {
    color: white;
    text-decoration: none;
  }

  .signup-link a:hover {
    text-decoration: underline;
  }

  form label {
    text-align: left;
    display: block;
  }

  form input {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 5px;
  }

  .btn-primary {
    width: 100%;
    padding: 10px;
    margin-top: 15px;
    background-color: #00090e;
    color: #06a5ff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s ease;
  }

  .btn-primary:hover {
    background-color: lightgray;
  }

  .text-danger {
    color: #ff4d4d;
  }

  body {
    background: url("@/assets/images/background.jpg") no-repeat center center fixed;
    background-size: cover;
  }
  </style>
