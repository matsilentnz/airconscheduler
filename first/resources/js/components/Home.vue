<template>
    <div>
      <!-- Hero Section -->
      <header class="hero">
        <h1 style="color: white;">Reliable <span class="highlight" style="color: black;">AirCon</span> Services</h1>
        <p style="color: white;">Fast & affordable air conditioning maintenance and repairs.</p>
        <button class="btn-book" @click="goToLogin">Book Your Appointment</button>
      </header>

      <!-- About Us Section -->
      <section id="about" class="section">
        <h2>About Us</h2>
        <p>At Reliable AirCon Services, our primary expertise lies in providing top-notch air conditioner servicing.
          Over the years, we have expanded to include swift and affordable air conditioning installation and repair services.
          Our mission is to ensure your comfort with high-quality solutions, keeping your environment cool
          and comfortable all year round.</p>
      </section>

      <!-- Services Section -->
      <section id="services" class="services-section">
        <div class="container">
          <h2 class="section-title">Our Services</h2>
          <p class="section-subtitle">We provide top-notch air conditioning services to keep you cool and comfortable.</p>

          <div class="row">
            <div class="col-md-4">
              <div class="service-card">
                <i class="bi bi-tools service-icon"></i>
                <h3>Installation</h3>
                <p>We offer professional AC installation with energy-efficient solutions.</p>
              </div>
            </div>
            <div class="col-md-4">
              <div class="service-card">
                <i class="bi bi-wrench service-icon"></i>
                <h3>Maintenance</h3>
                <p>Regular servicing to ensure your AC runs at peak performance.</p>
              </div>
            </div>
            <div class="col-md-4">
              <div class="service-card">
                <i class="bi bi-shield-check service-icon"></i>
                <h3>Repairs</h3>
                <p>Quick and reliable repairs for all types of air conditioning units.</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Why Us Section -->
      <section id="whyus" class="section">
        <h2>Why Us?</h2>
        <p>Experienced technicians, affordable pricing, and top-quality service.</p>
      </section>
    </div>
  </template>

<script>
export default {
  name: "Home",
  methods: {
    goToLogin() {
      this.$router.push("/login"); // Redirects to the login page
    },
  },
};
</script>


<style scoped>
/* Hero Section */
.hero {
  height: 100vh;
  background: url("/images/ac.jpg") no-repeat center center/cover; /* 🔹 Fixed Image Path */
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  color: white;
  padding: 20px;
}

.hero h1 {
  font-size: 3rem; /* Adjust size (e.g., 2rem, 4rem) */
  font-weight: bold; /* Make text bold */
  font-family: 'Arial', sans-serif; /* Change font */
}

/* 🔹 Make the paragraph text bigger and easier to read */
.hero p {
  font-size: 2.0rem; /* Increase size */
  font-weight: 500; /* Semi-bold */
  font-family: 'Roboto', sans-serif; /* Change font */
}

.highlight {
  color: #ffcc00;
}

/* Button Improvements */
.btn-book {
  background-color: #ffcc00;
  color: black;
  padding: 12px 24px;
  font-size: 1.2rem;
  font-weight: bold;
  border-radius: 30px;
  transition: all 0.3s ease;
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2); /* 🔹 Added Shadow */
}

.btn-book:hover {
  background-color: #ffa500;
  color: white;
}

.services-section {
  padding: 100px 0;
  background-color: #f8f9fa;
  text-align: center;
}

.section-title {
  font-size: 2.5rem;
  color: #06A5FF;
  font-weight: bold;
  margin-bottom: 10px;
}

.section-subtitle {
  font-size: 1.2rem;
  color: #555;
  margin-bottom: 30px;
}

/* Service Cards */
.service-card {
  background: white;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease-in-out;
}

.service-card:hover {
  transform: translateY(-10px);
}

.service-icon {
  font-size: 3rem;
  color: #06A5FF;
  margin-bottom: 15px;
}

/* Section Styles */
.section {
    padding: 100px 20px;
    text-align: center;
    background-color: #d2d4d4;
  }

h2 {
  color: #06A5FF;
}
</style>

