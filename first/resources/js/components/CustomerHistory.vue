<template>
    <div class="dashboard-container">
      <div class="dashboard-sidebar">
        <CustomerDashboard />
      </div>
      <div class="dashboard-content">
        <div class="container my-5">
          <h3 class="mb-4 text-center">Completed Bookings</h3>

          <!-- Loading State -->
          <div v-if="loading" class="text-center">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p>Loading completed bookings...</p>
          </div>

          <!-- Error State -->
          <div v-if="errorMessage" class="alert alert-danger text-center">
            {{ errorMessage }}
          </div>

          <!-- Completed Bookings Table -->
          <div v-if="completedBookings.length > 0">
            <table class="table table-bordered table-hover">
              <thead class="table-light">
                <tr>

                  <th>Date</th>
                  <th>Time</th>
                  <th>Service</th>
                  <th>Aircon Type</th>
                  <th>Price</th>
                  <th>Notes</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(booking, index) in completedBookings" :key="index">
                  <td>{{ formatDate(booking.date) }}</td>
                  <td>{{ booking.time }}</td>
                  <td>{{ booking.service_type }}</td>
                  <td>{{ booking.aircon_type }}</td>
                  <td>{{ booking.total_price}}</td>
                  <td>{{ booking.notes }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty State -->
          <div v-else-if="!loading && !errorMessage" class="text-center text-muted">
            <p>No completed bookings found.</p>
          </div>

          <!-- Booking Details Modal -->
          <div class="modal fade" id="bookingModal" tabindex="-1">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Booking Details</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" v-if="currentBooking">
                  <p><strong>Name:</strong> {{ currentBooking.customer?.name }}</p>
                  <p><strong>Email:</strong> {{ currentBooking.customer?.email }}</p>
                  <p><strong>Date:</strong> {{ formatDate(currentBooking.date) }}</p>
                  <p><strong>Time:</strong> {{ currentBooking.time }}</p>
                  <p><strong>Service:</strong> {{ currentBooking.service_type }}</p>
                  <p><strong>Aircon Type:</strong> {{ currentBooking.aircon_type }}</p>
                  <p><strong>Notes:</strong> {{ currentBooking.notes || 'N/A' }}</p>
                  <p><strong>Total Price:</strong> {{ currentBooking.total_price || 'N/A' }}</p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>

  <script>
  import axios from "axios";
  import CustomerDashboard from "@/components/CustomerDashboard.vue";

  export default {
    components: {
      CustomerDashboard
    },
    data() {
      return {
        completedBookings: [],
        currentBooking: null,
        loading: false,
        errorMessage: "",
      };
    },
    created() {
      this.fetchCompletedBookings();
    },
    methods: {
      async fetchCompletedBookings() {
        this.loading = true;
        this.errorMessage = "";

        try {
          const token = localStorage.getItem("auth_token");
          if (!token) {
            this.$router.push("/login");
            return;
          }

          axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
          const response = await axios.get("http://127.0.0.1:8000/api/bookings/completed");
          this.completedBookings = response.data;
        } catch (error) {
          console.error("Error fetching completed bookings:", error);
          this.errorMessage = error.response?.data?.message || "Failed to fetch completed bookings.";
        } finally {
          this.loading = false;
        }
      },
      viewBookingDetails(booking) {
        this.currentBooking = booking;
        const modal = new bootstrap.Modal(document.getElementById("bookingModal"));
        modal.show();
      },
      formatDate(dateString) {
        return new Date(dateString).toLocaleDateString("en-US", {
          year: "numeric",
          month: "long",
          day: "numeric",
        });
      },
    },
  };
  </script>

  <style scoped>
  @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');

  /* Main Container */
  .dashboard-container {
    display: flex;
    min-height: 100vh;
    background-image: url('/images/background.jpg');
    color: white;
    font-family: 'Roboto', sans-serif;
    overflow-x: hidden;
  }

  /* Sidebar */
  .dashboard-sidebar {
    width: 280px;
    background: rgba(0, 0, 0, 0.2);
    backdrop-filter: blur(8px);
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
    border-right: 1px solid rgba(255, 255, 255, 0.2);
    position: fixed;
    height: 100vh;
    z-index: 10;
  }

  /* Content Area */
  .dashboard-content {
    flex: 1;
  padding: 20px;
  margin-left: 250px; /* Same as sidebar width */
  min-height: 100vh;
  }

  .container {
    width: 100%;
    max-width: 1200px;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0px 5px 20px rgba(0, 0, 0, 0.4);
    animation: fadeIn 0.8s ease-in-out;
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #333;
  }

  .table {
    background-color: white;
  }

  .badge {
    font-size: 0.9rem;
  }

  /* Fade-in Animation */
  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .dashboard-sidebar {
      width: 100%;
      height: auto;
      position: relative;
    }

    .dashboard-container {
      flex-direction: column;
    }

    .dashboard-content {
      margin-left: 0;
      padding: 20px;
    }

    .container {
      padding: 20px;
    }
  }
  </style>
