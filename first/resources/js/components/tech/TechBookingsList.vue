<template>
    <div class="dashboard-container">
      <div class="dashboard-sidebar">
        <TechDashboard />
      </div>

      <div class="admin-bookings-container">
        <!-- Calendar Section -->
        <div class="calendar-wrapper card shadow p-4 mb-4">
          <FullCalendar :options="calendarOptions" class="fc-custom" />
        </div>

        <!-- All Bookings Table Section -->
        <div class="bookings-table-wrapper card shadow p-4">
          <h4 class="mb-4">All Bookings</h4>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead class="table-light">
                <tr>
                  <th>Customer</th>
                  <th>Date</th>
                  <th>Time</th>
                  <th>Service Type</th>
                  <th>Aircon Type</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(booking, index) in bookings" :key="index">
                  <td>{{ booking.customer?.name || 'N/A' }}</td>
                  <td>{{ formatTableDate(booking.date) }}</td>
                  <td>{{ booking.time }}</td>
                  <td>{{ booking.service_type }}</td>
                  <td>{{ booking.aircon_type }}</td>
                  <td>
                    <div class="dropdown">
                      <button class="btn btn-sm dropdown-toggle"
                              :class="'btn-' + getStatusColor(booking.status)"
                              type="button"
                              id="statusDropdown"
                              data-bs-toggle="dropdown"
                              aria-expanded="false">
                        {{ booking.status }}
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="statusDropdown">
                        <li v-for="status in statusOptions" :key="status">
                          <a class="dropdown-item"
                             href="#"
                             @click.prevent="updateBookingStatus(booking, status)">
                            {{ status }}
                          </a>
                        </li>
                      </ul>
                    </div>
                  </td>
                  <td>
                    <button
                      class="btn btn-sm btn-outline-primary"
                      @click="viewBookingDetails(booking)"
                    >
                      View
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Booking Details Modal -->
        <div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
              <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Booking Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div v-if="currentBooking">
                  <div class="row">
                    <div class="col-md-6">
                      <h6 class="section-title">Customer Information</h6>
                      <p><strong>Name:</strong> {{ currentBooking.customer?.name || 'Not available' }}</p>
                      <p><strong>Email:</strong> {{ currentBooking.customer?.email || 'Not available' }}</p>
                      <p><strong>Phone:</strong> {{ currentBooking.customer?.phone || 'Not available' }}</p>
                      <p><strong>Address:</strong> {{ currentBooking.customer?.address || 'Not available' }}</p>
                    </div>
                    <div class="col-md-6">
                      <h6 class="section-title">Service Details</h6>
                      <p><strong>Date:</strong> {{ formatBookingDate(currentBooking.date) }}</p>
                      <p><strong>Time:</strong> {{ currentBooking.time }}</p>
                      <p><strong>Service Type:</strong> {{ currentBooking.service_type }}</p>
                      <p><strong>Aircon Type:</strong> {{ currentBooking.aircon_type }}</p>
                      <p><strong>Recurring Service:</strong> {{ currentBooking.recurring_service }}</p>
                      <p><strong>Status:</strong>
                        <span class="badge" :class="'bg-' + getStatusColor(currentBooking.status)">
                          {{ currentBooking.status }}
                        </span>
                      </p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal for Price and Technician Notes -->
        <div class="modal fade" id="completionModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Complete Booking</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form @submit.prevent="submitCompletion">
                  <div class="mb-3">
                    <label for="totalPrice" class="form-label">Total Price</label>
                    <input
                      type="number"
                      id="totalPrice"
                      class="form-control"
                      v-model="completionData.totalPrice"
                      min="0"
                      required
                    />
                  </div>
                  <div class="mb-3">
                    <label for="technicianNotes" class="form-label">Technician Notes</label>
                    <textarea
                      id="technicianNotes"
                      class="form-control"
                      v-model="completionData.notes"
                      rows="3"
                      placeholder="Enter any notes (optional)"
                    ></textarea>
                  </div>
                  <button type="submit" class="btn btn-success w-100">Mark as Completed</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>

  <script>
  import axios from 'axios';
  import FullCalendar from '@fullcalendar/vue3';
  import dayGridPlugin from '@fullcalendar/daygrid';
  import { Modal } from 'bootstrap';
import TechDashboard from './TechDashboard.vue';

  export default {
    name: 'TechBookingsList',
    components: {
      TechDashboard,
      FullCalendar
    },
    data() {
      return {
        bookings: [],
        selectedBookings: [],
        selectedDate: null,
        currentBooking: null,
        bookingModal: null,
        statusOptions: ['Booked', 'In Progress', 'Completed'],
        completionData: {
          totalPrice: null,
          notes: '',
        },
      };
    },
    computed: {
      calendarOptions() {
        return {
          plugins: [dayGridPlugin],
          initialView: 'dayGridMonth',
          height: 'auto',
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: ''
          },
          events: this.bookings.map(booking => ({
            title: `${booking.customer?.name || 'Customer'} - ${booking.service_type}`,
            start: booking.date,
            extendedProps: booking,
            backgroundColor: this.getStatusColor(booking.status, true),
            borderColor: this.getStatusColor(booking.status, true)
          })),
          dateClick: this.handleDateClick
        };
      }
    },
    mounted() {
      this.bookingModal = new Modal(document.getElementById('bookingModal'));
      this.fetchBookings();
    },
    methods: {
      async fetchBookings() {
  try {
    const response = await axios.get('http://127.0.0.1:8000/api/technician/bookings');
    this.bookings = response.data.sort((a, b) => new Date(a.date) - new Date(b.date));
  } catch (error) {
    console.error('Error fetching bookings:', error);
  }
},
      async updateBookingStatus(booking, newStatus) {
        if (newStatus === 'Completed') {
          this.currentBooking = booking;
          this.completionData.totalPrice = null;
          this.completionData.notes = '';
          const completionModal = new Modal(document.getElementById('completionModal'));
          completionModal.show();
          return;
        }

        try {
          const response = await axios.put(
            `http://127.0.0.1:8000/api/admin/bookings/${booking.id}/status`,
            { status: newStatus }
          );

          booking.status = newStatus;
          this.bookings = this.bookings.map(b =>
            b.id === booking.id ? { ...b, status: newStatus } : b
          );

          alert('Status updated successfully!');
        } catch (error) {
          console.error('Error updating booking status:', error);
          alert(`Failed to update status: ${error.response?.data?.message || error.message}`);
        }
      },
      async submitCompletion() {
        if (!this.currentBooking) {
          alert('No booking selected.');
          return;
        }

        try {
          const response = await axios.put(
            `http://127.0.0.1:8000/api/admin/bookings/${this.currentBooking.id}/status`,
            {
              status: 'Completed',
              total_price: this.completionData.totalPrice,
              notes: this.completionData.notes,
            }
          );

          this.currentBooking.status = 'Completed';
          this.currentBooking.total_price = this.completionData.totalPrice;
          this.currentBooking.notes = this.completionData.notes;

          this.bookings = this.bookings.map(b =>
            b.id === this.currentBooking.id ? { ...b, ...this.currentBooking } : b
          );

          alert('Booking marked as completed successfully!');
          const completionModal = Modal.getInstance(document.getElementById('completionModal'));
          completionModal.hide();
        } catch (error) {
          console.error('Error completing booking:', error);
          alert(`Failed to complete booking: ${error.response?.data?.message || error.message}`);
        }
      },
      handleDateClick(info) {
        this.selectedDate = info.dateStr;
        this.selectedBookings = this.bookings.filter(booking => {
          const bookingDate = new Date(booking.date).toISOString().split('T')[0];
          return bookingDate === info.dateStr;
        });
        if (this.selectedBookings.length > 0) {
          this.currentBooking = this.selectedBookings[0];
          this.bookingModal.show();
        }
      },
      viewBookingDetails(booking) {
        this.currentBooking = booking;
        this.bookingModal.show();
      },
      formatBookingDate(dateString) {
        return new Date(dateString).toLocaleDateString('en-US', {
          weekday: 'long',
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        });
      },
      formatTableDate(dateString) {
        return new Date(dateString).toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        });
      },
      getStatusColor(status, isCalendar = false) {
        const colors = {
          'Booked': isCalendar ? '#0d6efd' : 'primary',
          'In Progress': isCalendar ? '#ffc107' : 'warning',
          'Completed': isCalendar ? '#198754' : 'success',
        };
        return colors[status] || (isCalendar ? '#6c757d' : 'secondary');
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
    position: relative;
  }

  .dashboard-sidebar {
    flex: 0 0 250px;
  }

  .admin-bookings-container {
    flex: 1;
    padding: 2rem;
    overflow-y: auto;
  }

  .calendar-wrapper {
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  }

  .bookings-table-wrapper {
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  }

  .fc-custom {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }

  .section-title {
    color: #0d6efd;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 0.5rem;
    margin-bottom: 1rem;
  }

  .badge {
    font-size: 0.875em;
    padding: 0.35em 0.65em;
    font-weight: 500;
  }

  .table th {
    font-weight: 600;
  }

  .table-responsive {
    overflow-x: auto;
  }

  .dropdown-toggle::after {
    margin-left: 0.5em;
  }

  .btn-warning {
    color: #000;
  }

  .btn-primary, .btn-success, .btn-danger {
    color: #fff;
  }

  .dropdown-item {
    cursor: pointer;
  }
  </style>
