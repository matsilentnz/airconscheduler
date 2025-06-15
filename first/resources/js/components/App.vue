<template>
    <div>
      <!-- Show Navbar ONLY on specific pages -->
      <nav v-if="showNavbar" class="navbar navbar-expand-lg fixed-top">
        <div class="container">
          <a class="navbar-brand text-white fw-bold" href="#">
            <img src="/images/logo.png" alt="Logo" class="logo me-2">
            AirCon Scheduler
          </a>
          <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
            aria-controls="navbarNav"
            aria-expanded="false"
            aria-label="Toggle navigation"
          >
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
              <li class="nav-item">
                <router-link class="nav-link text-white" to="/#about">About Us</router-link>
              </li>
              <li class="nav-item">
                <router-link class="nav-link text-white" to="/#services">Our Services</router-link>
              </li>
              <li class="nav-item">
                <router-link class="nav-link text-white" to="#whyus">Why Us</router-link>
              </li>
              <li class="nav-item dropdown">
                <a
                  class="nav-link btn btn-warning px-4 dropdown-toggle"
                  href="#"
                  id="loginDropdown"
                  role="button"
                  data-bs-toggle="dropdown"
                  aria-haspopup="true"
                  aria-expanded="false"
                >
                  Login
                </a>
                <ul class="dropdown-menu" aria-labelledby="loginDropdown">
                  <li><router-link to="/login" class="dropdown-item">Customer</router-link></li>
                  <li><router-link to="/admin" class="dropdown-item">Admin</router-link></li>
                </ul>
              </li>
            </ul>
          </div>
        </div>
      </nav>

      <!-- Page Content (Main Pages) -->
      <router-view></router-view>
    </div>
  </template>

  <script>
  export default {
    computed: {
      showNavbar() {
        // Show navbar on specific pages only
        const allowedRoutes = ["/", "/login", "/signup", "/admin", "/login"];
        return allowedRoutes.includes(this.$route.path);
      },
    },
  };
  </script>

  <style>
  /* Navbar */
  .navbar {
    background-color: #06A5FF !important;
    padding: 15px;
  }

  .logo {
    height: 40px; /* Adjust logo height */
    width: auto; /* Maintain aspect ratio */
  }

  html, body {
    height: 100%;
    margin: 0;
    padding: 0;
    background-image: url('/images/background.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
  }
  </style>
