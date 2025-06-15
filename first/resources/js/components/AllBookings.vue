<template>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <div class="dashboard-sidebar">
      <CustomerDashboard />
    </div>

    <!-- Main Content -->
    <div class="dashboard-content">
      <div class="bookings-container">
        <div class="header">
          <h1>Upcoming Bookings</h1>
          <button @click="fetchUpcomingBookings" class="refresh-btn">
            <i class="bi bi-arrow-clockwise"></i> Refresh
          </button>
        </div>

        <div class="form-alert" style="color: red;">
                <label>
                <i class="bi bi-exclamation-triangle"></i >
                  You cannot cancel the booking 24 hours before the scheduled time.
                </label>
             </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-container">
          <div class="spinner"></div>
          <p>Loading your bookings...</p>
        </div>

        <!-- Booking Data -->
        <div v-else-if="upcomingBookings.length" class="bookings-list-container">
          <ul class="booking-list">
            <li v-for="booking in upcomingBookings" :key="booking.id" class="booking-card">
              <div class="booking-header">
                <div class="booking-date">
                  <i class="bi bi-calendar-event"></i>
                  <span>{{ formatDate(booking.date) }}</span>
                </div>
                <div class="booking-status" :class="getStatusClass(booking.status)">
                  {{ booking.status }}
                </div>
              </div>

              <div class="booking-body">
                <div class="booking-detail">
                  <i class="bi bi-clock"></i>
                  <div>
                    <strong>Time</strong>
                    <p>{{ formatTime(booking.time) }}</p>
                  </div>
                </div>

                <div class="booking-detail">
                  <i class="bi bi-tools"></i>
                  <div>
                    <strong>Service</strong>
                    <p>{{ booking.service_type }}</p>
                  </div>
                </div>

                <div class="booking-detail">
                  <i class="bi bi-fan"></i>
                  <div>
                    <strong>AC Type</strong>
                    <p>{{ booking.aircon_type }}</p>
                  </div>
                </div>
              </div>

              <div class="booking-actions">
                <button
                  v-if="booking.status !== 'Cancelled'"
                  @click="cancelBooking(booking.id)"
                  class="btn-cancel"
                  :disabled="cancellingId === booking.id"
                >
                  <i class="bi" :class="cancellingId === booking.id ? 'bi-hourglass-split' : 'bi-x-circle'"></i>
                  {{ cancellingId === booking.id ? 'Cancelling...' : 'Cancel Booking' }}
                </button>
                <span v-else class="cancelled-text">Booking cancelled</span>
              </div>
            </li>
          </ul>
        </div>

        <!-- Empty State -->
        <div v-else-if="!errorMessage" class="empty-state">
          <i class="bi bi-calendar-x"></i>
          <h3>No Upcoming Bookings</h3>
          <p>You don't have any scheduled appointments.</p>
          <router-link to="/dashboard/book-appointment" class="btn-book">
            Book an Appointment
          </router-link>
        </div>

        <!-- Error State -->
        <div v-else class="error-state">
          <i class="bi bi-exclamation-triangle"></i>
          <h3>Something went wrong</h3>
          <p>{{ errorMessage }}</p>
          <button @click="fetchUpcomingBookings" class="btn-retry">
            <i class="bi bi-arrow-repeat"></i> Try Again
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import CustomerDashboard from "@/components/CustomerDashboard.vue";
import axios from "axios";

export default {
  components: {
    CustomerDashboard,
  },
  data() {
    return {
      loading: false,
      errorMessage: "",
      upcomingBookings: [],
      cancellingId: null
    };
  },
  created() {
    this.fetchUpcomingBookings();
  },
  methods: {
    async fetchUpcomingBookings() {
      this.loading = true;
      this.errorMessage = "";

      try {
        // Get token from localStorage
        const token = localStorage.getItem('auth_token');
        if (!token) {
          this.$router.push('/login');
          return;
        }

        // Set authorization header
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

        // Fetch upcoming bookings from API
        const response = await axios.get('http://127.0.0.1:8000/api/bookings/upcoming');
        this.upcomingBookings = response.data;
      } catch (error) {
        console.error('Error fetching bookings:', error);
        if (error.response && error.response.data && error.response.data.message) {
          this.errorMessage = error.response.data.message;
        } else {
          this.errorMessage = "Failed to load your bookings. Please try again.";
        }
      } finally {
        this.loading = false;
      }
    },

    async cancelBooking(bookingId) {
      if (!confirm('Are you sure you want to cancel this booking?')) {
        return;
      }

      this.cancellingId = bookingId;

      try {
        // Get token from localStorage
        const token = localStorage.getItem('auth_token');
        if (!token) {
          this.$router.push('/login');
          return;
        }

        // Set authorization header
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

        // Cancel booking via API
        await axios.put(`http://127.0.0.1:8000/api/bookings/${bookingId}/cancel`);

        // Refresh bookings list
        this.fetchUpcomingBookings();
  } catch (error) {
    console.error('Error cancelling booking:', error);
    if (error.response && error.response.data && error.response.data.message) {
      alert(error.response.data.message); // Display the error message
    } else {
      alert('Failed to cancel booking. Please try again.');
    }
  } finally {
    this.cancellingId = null;
  }
    },

    formatDate(dateString) {
      const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(dateString).toLocaleDateString('en-US', options);
    },

    formatTime(timeString) {
      // Convert 24-hour time format to 12-hour format
      const [hours, minutes] = timeString.split(':');
      const hour = parseInt(hours, 10);
      const ampm = hour >= 12 ? 'PM' : 'AM';
      const hour12 = hour % 12 || 12;
      return `${hour12}:${minutes} ${ampm}`;
    },

    getStatusClass(status) {
      switch (status) {
        case 'Confirmed':
          return 'status-confirmed';
        case 'Completed':
          return 'status-completed';
        case 'Cancelled':
          return 'status-cancelled';
        default:
          return 'status-pending';
      }
    }
  }
};
</script>

<style scoped>
.dashboard-container {
    display: flex;
    min-height: 100vh;
    background-image: url('/images/background.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
    font-family: 'Roboto', sans-serif;
}

.dashboard-sidebar {
  width: 250px;
  background-color: #06a5ff;
  color: white;
  box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
  position: fixed;
  height: 100vh;
  overflow-y: auto;

}
.bookings-list-container{
    background-color: #58baf3;
}

.dashboard-content {
  flex: 1;
  padding: 20px;
  margin-left: 250px; /* Same as sidebar width */
  min-height: 100vh;

}

.bookings-container {
  max-width: 900px;
  margin: 0 auto;
  background-color: #06a5ff;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  padding: 30px;
  color: white;

}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.refresh-btn {
  background-color: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  padding: 8px 15px;
  border-radius: 5px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 5px;
  transition: background-color 0.3s;
}

.refresh-btn:hover {
  background-color: rgba(255, 255, 255, 0.3);
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 50px 0;
}

.spinner {
  border: 4px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top: 4px solid white;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
  margin-bottom: 15px;
}

.empty-state, .error-state {
  text-align: center;
  padding: 50px 0;
}

.empty-state i, .error-state i {
  font-size: 3rem;
  margin-bottom: 15px;
  opacity: 0.7;
}

.btn-book, .btn-retry {
  display: inline-block;
  background-color: rgba(255, 255, 255, 0.2);
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 5px;
  margin-top: 15px;
  cursor: pointer;
  text-decoration: none;
  transition: background-color 0.3s;
}

.btn-book:hover, .btn-retry:hover {
  background-color: rgba(255, 255, 255, 0.3);
}

.booking-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.booking-card {
  background-color: rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 15px;
  margin-bottom: 15px;
}

.booking-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.booking-date {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: bold;
}

.booking-body {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 15px;
  margin-bottom: 15px;
}

.booking-detail {
  display: flex;
  align-items: flex-start;
  gap: 10px;
}

.booking-detail i {
  margin-top: 3px;
}

.booking-detail strong {
  display: block;
  margin-bottom: 5px;
  font-size: 0.9rem;
  opacity: 0.8;
}

.booking-detail p {
  margin: 0;
}

.booking-actions {
  display: flex;
  justify-content: flex-end;
}

.btn-cancel {
  background-color: rgba(255, 0, 0, 0.2);
  border: none;
  color: white;
  padding: 8px 15px;
  border-radius: 5px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 5px;
  transition: background-color 0.3s;
}

.btn-cancel:hover {
  background-color: rgba(255, 0, 0, 0.3);
}


.booking-status {
  padding: 5px 10px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
}

.status-pending {
  background-color: rgba(255, 193, 7, 0.3);
  color: #fff;
}

.status-confirmed {
  background-color: rgba(0, 123, 255, 0.3);
  color: #fff;
}

.status-completed {
  background-color: rgba(40, 167, 69, 0.3);
  color: #fff;
}

.status-cancelled {
  background-color: rgba(220, 53, 69, 0.3);
  color: #fff;
}

.cancelled-text {
  color: rgba(255, 255, 255, 0.7);
  font-style: italic;
}

.btn-cancel:disabled {
  background: rgba(255, 0, 0, 0.1);
  cursor: not-allowed;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.bi-hourglass-split {
  animation: spin 2s linear infinite;
}
</style>
