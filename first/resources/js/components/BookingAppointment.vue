<template>
    <div class="dashboard-container">
      <!-- Sidebar -->
      <div class="dashboard-sidebar">
        <CustomerDashboard />
      </div>

      <!-- Booking Form Content -->
      <div class="dashboard-content">
        <div class="booking-form-container">
          <div class="booking-form">
            <div class="form-header">
              <h2 class="title">Book an Appointment</h2>
              <p class="subtitle">Schedule your air conditioning service</p>
            </div>

            <!-- Success message -->
            <div v-if="successMessage" class="success-message">
              <i class="bi bi-check-circle"></i>
              {{ successMessage }}
            </div>

            <!-- Error message -->
            <div v-if="error" class="error-message">
              <i class="bi bi-exclamation-triangle"></i>
              {{ error }}
            </div>

            <form @submit.prevent="submitBooking">
              <div class="form-grid">
                <div class="form-group">
                  <label for="date">
                    <i class="bi bi-calendar-event"></i> Select Date
                  </label>
                  <input
                    type="date"
                    id="date"
                    class="form-control"
                    v-model="date"
                    required
                    :min="minDate"
                    @change="checkAvailability"
                  >
                </div>

                <div class="form-group">
                  <label for="time">
                    <i class="bi bi-clock"></i> Select Time
                  </label>
                  <select
                    id="time"
                    class="form-control"
                    v-model="time"
                    required
                    :disabled="!date"
                  >
                    <option value="" disabled>Select Time</option>
                    <option
                      v-for="slot in timeSlots"
                      :value="slot.value"
                      :key="slot.value"
                      :disabled="slot.booked >= 2"
                    >
                      {{ slot.label }} {{ slot.booked >= 2 ? '(Fully Booked)' : `(${2 - slot.booked} slots left)` }}
                    </option>
                  </select>
                </div>

                <div class="form-group">
                  <label for="service">
                    <i class="bi bi-tools"></i> Service Type
                  </label>
                  <select
                    id="service"
                    class="form-control"
                    v-model="serviceType"
                    required
                  >
                    <option value="" disabled>Select Service</option>
                    <option value="AC Installation">AC Installation</option>
                    <option value="AC Maintenance">AC Maintenance</option>
                    <option value="AC Repair">AC Repair</option>
                  </select>
                </div>

                <div class="form-group">
                  <label for="airconType">
                    <i class="bi bi-fan"></i> Air Conditioner Type
                  </label>
                  <div v-for="(type, index) in airconTypeList" :key="index" class="mb-2">
  <input
    type="text"
    class="form-control"
    v-model="airconTypeList[index]"
    :placeholder="'Aircon Type ' + (index + 1)"
    required
  />
</div>

<button
  v-if="airconTypeList.length < 5"
  type="button"
  class="btn btn-light mt-2"
  @click="addAirconType"
>
  + Add Another Aircon Type
</button>

                </div>

                <div class="form-group full-width">
                  <label class="recurring-label">
                    <i class="bi bi-arrow-repeat"></i> Continue Service Every 3 Months?
                  </label>
                  <div class="radio-group">
                    <div class="radio-option">
                      <input type="radio" id="yes" value="Yes" v-model="recurringService" required>
                      <label for="yes">Yes</label>
                    </div>

                    <div class="radio-option">
                      <input type="radio" id="no" value="No" v-model="recurringService" required>
                      <label for="no">No</label>
                    </div>
                  </div>
                </div>
              </div>

              <div class="form-alert" style="color: red;">
                <label>
                <i class="bi bi-exclamation-triangle"></i >
                  You cannot cancel the booking 24 hours before the scheduled time.
                </label>
             </div>

              <div class="form-actions">
                <button type="button" class="btn-cancel" @click="resetForm">Cancel</button>
                <button type="submit" class="btn-submit" :disabled="loading || !time">
                  <i class="bi" :class="loading ? 'bi-hourglass-split' : 'bi-calendar-check'"></i>
                  {{ loading ? 'Processing...' : 'Confirm Booking' }}
                </button>
              </div>
            </form>
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
        date: '',
        time: '',
        serviceType: '',
        airconTypeList: [''],
        recurringService: '',
        loading: false,
        error: null,
        successMessage: null,
        timeSlots: [
          { value: '10:00', label: '10:00 AM', booked: 0 },
          { value: '12:00', label: '12:00 PM', booked: 0 },
          { value: '14:00', label: '2:00 PM', booked: 0 },
          { value: '16:00', label: '4:00 PM', booked: 0 }
        ]
      };
    },
    computed: {
      minDate() {
        const today = new Date();
        return today.toISOString().split('T')[0];
      }
    },
    methods: {
      async checkAvailability() {
        if (!this.date) {
          this.time = '';
          return;
        }

        try {
          const token = localStorage.getItem('auth_token');
          if (!token) {
            this.$router.push('/login');
            return;
          }

          axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

          // Reset booked counts
          this.timeSlots.forEach(slot => slot.booked = 0);

          // Get bookings for the selected date
          const response = await axios.get(`http://127.0.0.1:8000/api/bookings/availability?date=${this.date}`);

          // Update booked counts
          response.data.forEach(booking => {
            const slot = this.timeSlots.find(s => s.value === booking.time);
            if (slot) {
              slot.booked = booking.count;
            }
          });
        } catch (error) {
          console.error('Error checking availability:', error);
        }
      },

      async submitBooking() {
        if (!this.date || !this.time || !this.serviceType || this.airconTypeList.some(type => type.trim() === '') || !this.recurringService) {
          this.error = "Please fill in all fields.";
          return;
        }

        this.loading = true;
        this.error = null;
        this.successMessage = null;

        try {
          const token = localStorage.getItem('auth_token');
          if (!token) {
            this.$router.push('/login');
            return;
          }

          axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

          const response = await axios.post('http://127.0.0.1:8000/api/bookings', {
            date: this.date,
            time: this.time,
            service_type: this.serviceType,
            aircon_type: this.airconTypeList.filter(a => a.trim() !== '').join(', '),
            recurring_service: this.recurringService
          });

          this.successMessage = "Appointment successfully booked!";
          this.checkAvailability(); // Refresh availability after booking
          setTimeout(() => {
            this.$router.push("/dashboard/all-bookings");
          }, 2000);

        } catch (error) {
          console.error('Booking error:', error);
          if (error.response && error.response.data && error.response.data.errors) {
            const errors = error.response.data.errors;
            const errorMessages = Object.values(errors).flat();
            this.error = errorMessages.join(' ');
          } else if (error.response && error.response.data && error.response.data.message) {
            this.error = error.response.data.message;
          } else {
            this.error = 'Failed to book appointment. Please try again.';
          }
        } finally {
          this.loading = false;
        }
      },

      resetForm() {
        this.date = '';
        this.time = '';
        this.serviceType = '';
        this.airconType = '';
        this.recurringService = '';
        this.error = null;
        this.timeSlots.forEach(slot => slot.booked = 0);
      },
      addAirconType() {
    if (this.airconTypeList.length < 5) {
      this.airconTypeList.push('');
    }
  }
    }
  };
  </script>

  <style scoped>
  @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');
  @import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css');

  /* Main Container */
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
    margin-left: 280px;
    padding: 30px;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  /* Booking Form Container */
  .booking-form-container {
    width: 100%;
    max-width: 900px;
    animation: fadeIn 0.8s ease-in-out;
  }

  /* Booking Form */
  .booking-form {
    background: #06a5ff;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: white;
  }

  /* Form Header */
  .form-header {
    margin-bottom: 30px;
    text-align: center;
  }

  .title {
    font-size: 2.2rem;
    font-weight: 700;
    color: white;
    margin-bottom: 10px;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  }

  .subtitle {
    font-size: 1.1rem;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 20px;
  }

  /* Form Grid */
  .form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 25px;
    margin-bottom: 30px;
  }

  /* Form Group */
  .form-group {
    text-align: left;
  }

  .full-width {
    grid-column: span 2;
  }

  /* Labels */
  .form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: white;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .form-group label i {
    font-size: 1.1rem;
  }

  /* Form Controls */
  .form-control {
    width: 100%;
    padding: 14px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 10px;
    font-size: 1rem;
    background: rgba(255, 255, 255, 0.9);
    color: #333;
    transition: all 0.3s ease;
  }

  .form-control:focus {
    border-color: white;
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
    outline: none;
  }

  /* Radio Group */
  .radio-group {
    display: flex;
    gap: 30px;
    margin-top: 10px;
  }

  .radio-option {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .radio-option input[type="radio"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
  }

  .radio-option label {
    cursor: pointer;
    font-size: 1.1rem;
  }

  /* Form Actions */
  .form-actions {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    margin-top: 30px;
  }

  /* Submit Button */
  .btn-submit {
    flex: 2;
    padding: 16px;
    background-color: #003366;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
  }

  .btn-submit:hover {
    background-color: #002244;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
  }

  .btn-submit:active {
    transform: translateY(0);
  }

  /* Cancel Button */
  .btn-cancel {
    flex: 1;
    padding: 16px;
    background-color: rgba(255, 255, 255, 0.2);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 10px;
    font-size: 1.1rem;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .btn-cancel:hover {
    background-color: rgba(255, 255, 255, 0.3);
  }

  /* Recurring Label */
  .recurring-label {
    margin-bottom: 15px;
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

  /* Success and Error Messages */
  .success-message {
    background-color: rgba(0, 128, 0, 0.2);
    color: white;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .success-message i {
    font-size: 1.5rem;
  }

  .error-message {
    background-color: rgba(255, 0, 0, 0.2);
    color: white;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .error-message i {
    font-size: 1.5rem;
  }

  /* Disabled States */
  .btn-submit:disabled {
    background-color: #667788;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
  }

  /* Loading Animation */
  @keyframes spin {
    to { transform: rotate(360deg); }
  }

  .bi-hourglass-split {
    animation: spin 2s linear infinite;
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

    .booking-form {
      padding: 25px;
    }

    .form-grid {
      grid-template-columns: 1fr;
      gap: 20px;
    }

    .full-width {
      grid-column: span 1;
    }

    .form-actions {
      flex-direction: column-reverse;
    }

    .btn-submit, .btn-cancel {
      width: 100%;
    }
  }
  </style>
