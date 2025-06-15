<template>
    <div class="sidebar">
      <div class="sidebar-header">
        <div class="logo">
          <img src="/images/logo.png" alt="Logo" />

        </div>
        <h2>Customer Dashboard</h2>
      </div>

      <nav class="sidebar-nav">
        <router-link to="/dashboard/customer-info" class="nav-item">
          <i class="bi bi-person"></i>
          <span>My Profile</span>
        </router-link>

        <router-link to="/dashboard/book-appointment" class="nav-item">
          <i class="bi bi-calendar-plus"></i>
          <span>Book Appointment</span>
        </router-link>

        <router-link to="/dashboard/all-bookings" class="nav-item">
          <i class="bi bi-calendar-check"></i>
          <span>My Bookings</span>
        </router-link>

        <router-link to="/dashboard/bookings-history" class="nav-item">
          <i class="bi bi-clock-history"></i>
          <span>History</span>
        </router-link>

        <a href="#" class="nav-item" @click.prevent="logout">
          <i class="bi bi-box-arrow-right"></i>
          <span>Logout</span>
        </a>
      </nav>
    </div>
  </template>

  <script>
  import axios from "axios";

  export default {
    methods: {
      async logout() {
        try {
          const token = localStorage.getItem("auth_token");
          if (token) {
            axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
            await axios.post("http://127.0.0.1:8000/api/logout");
          }
        } catch (error) {
          console.error("Logout error:", error);
        } finally {
          localStorage.removeItem("auth_token");
          this.$router.push("/login");
        }
      },
    },
  };
  </script>

  <style scoped>
  .sidebar {
    width: 260px;
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    height: 100vh;
    padding: 25px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 25px;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
  }

  .sidebar-header {
    display: flex;
    align-items: center; /* Align items vertically */
    gap: 10px; /* Adjust the spacing between the logo and the text */
}

.logo img {
    height: 40px; /* Adjust logo height */
    width: auto; /* Maintain aspect ratio */
}

  nav {
    display: flex;
    flex-direction: column;
    width: 100%;
    gap: 15px;
  }

  nav a {
    display: flex;
    align-items: center;
    gap: 10px;
    color: white;
    font-size: 1.1rem;
    font-weight: bold;
    padding: 12px;
    border-radius: 8px;
    text-decoration: none;
    transition: 0.3s;
  }

  nav a:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.05);
  }

  nav a.router-link-exact-active {
    background: white;
    color: #007bff;
    transform: scale(1.05);
  }

  .logout-btn {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 5px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: background 0.3s ease;
}

.logout-btn:hover {
  background: rgba(255, 0, 0, 0.3);
}

  i {
    font-size: 1.2rem;
  }


  </style>
