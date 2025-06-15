<template>
    <div class="dashboard-container">
      <div class="dashboard-sidebar">
        <AdminDashboard />
      </div>

      <div class="admin-records-container">
        <div class="records-table-wrapper card shadow p-4">
          <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Completed Bookings</h4>
            <div class="search-box">
              <div class="input-group">
                <input
                  type="text"
                  class="form-control"
                  placeholder="Search by customer name..."
                  v-model="searchQuery"
                  @keyup.enter="fetchCompletedBookings"
                >
                <button class="btn btn-outline-secondary" @click="fetchCompletedBookings">
                  <i class="bi bi-search"></i> Search
                </button>
                <button
                  class="btn btn-outline-danger"
                  @click="clearSearch"
                  v-if="searchQuery"
                >
                  <i class="bi bi-x-circle"></i> Clear
                </button>
              </div>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-hover">
              <thead class="table-light">
                <tr>
                  <th>Customer</th>
                  <th>Date</th>
                  <th>Service Type</th>
                  <th>Aircon Type</th>
                  <th>Price</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(booking, index) in completedBookings" :key="index">
                  <td>{{ booking.customer?.name || 'N/A' }}</td>
                  <td>{{ formatTableDate(booking.date) }}</td>
                  <td>{{ booking.service_type }}</td>
                  <td>{{ booking.aircon_type }}</td>
                  <td>{{ booking.total_price }}</td>
                  <td>
                    <span class="badge bg-success">
                      {{ booking.status }}
                    </span>
                  </td>
                  <td>
                    <button class="btn btn-sm btn-outline-primary" @click="viewBookingDetails(booking)">
                      View
                    </button>
                  </td>
                </tr>
                <tr v-if="completedBookings.length === 0">
                  <td colspan="7" class="text-center">No completed bookings found</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <nav v-if="totalPages > 1" class="mt-4">
            <ul class="pagination justify-content-center">
              <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <button class="page-link" @click="changePage(currentPage - 1)">Previous</button>
              </li>
              <li
                class="page-item"
                v-for="page in displayedPages"
                :key="page"
                :class="{ active: currentPage === page }"
              >
                <button class="page-link" @click="changePage(page)">{{ page }}</button>
              </li>
              <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                <button class="page-link" @click="changePage(currentPage + 1)">Next</button>
              </li>
            </ul>
          </nav>
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

                      <div v-if="currentBooking.status === 'Completed'">
                        <h6 class="section-title mt-4">Completion Details</h6>
                        <div class="mb-3">
                          <label class="form-label">Total Price</label>
                          <input type="number" class="form-control" v-model="editableBooking.total_price">
                        </div>

                        <div class="mb-3">
                          <label class="form-label">Technician Notes</label>
                          <textarea class="form-control" v-model="editableBooking.notes"></textarea>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button
                  type="button"
                  class="btn btn-primary"
                  @click="saveCompletedDetails"
                  v-if="currentBooking?.status === 'Completed'"
                >
                  Save Changes
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>

  <script>
  import axios from 'axios';
  import AdminDashboard from './AdminDashboard.vue';
  import { Modal } from 'bootstrap';

  export default {
    name: 'AllRecords',
    components: {
      AdminDashboard
    },
    data() {
      return {
        completedBookings: [],
        currentBooking: null,
        bookingModal: null,
        editableBooking: {
          total_price: null,
          notes: null
        },
        searchQuery: '',
        currentPage: 1,
        itemsPerPage: 10,
        totalItems: 0
      };
    },
    computed: {
      totalPages() {
        return Math.ceil(this.totalItems / this.itemsPerPage);
      },
      displayedPages() {
        const range = 2; // Number of pages to show before and after current page
        const start = Math.max(1, this.currentPage - range);
        const end = Math.min(this.totalPages, this.currentPage + range);

        const pages = [];
        for (let i = start; i <= end; i++) {
          pages.push(i);
        }
        return pages;
      }
    },
    mounted() {
      this.bookingModal = new Modal(document.getElementById('bookingModal'));
      this.fetchCompletedBookings();
    },
    methods: {
      async fetchCompletedBookings() {
        try {
          const params = {
            page: this.currentPage,
            per_page: this.itemsPerPage
          };

          if (this.searchQuery) {
            params.search = this.searchQuery;
          }

          const response = await axios.get('http://127.0.0.1:8000/api/admin/completed-bookings', {
            params
          });

          this.completedBookings = response.data.data || response.data;
          this.totalItems = response.data.total || response.data.length;
        } catch (error) {
          console.error('Error fetching completed bookings:', error);
        }
      },
      viewBookingDetails(booking) {
        this.currentBooking = booking;
        this.editableBooking = {
          total_price: booking.total_price,
          notes: booking.notes
        };
        this.bookingModal.show();
      },
      async saveCompletedDetails() {
        try {
          const payload = {
            total_price: parseFloat(this.editableBooking.total_price),
            notes: this.editableBooking.notes
          };

          const response = await axios.put(
            `http://127.0.0.1:8000/api/admin/completed-bookings/${this.currentBooking.id}`,
            payload
          );

          // Preserve the customer data if it's missing in the response
    const updatedBooking = response.data.booking;
    updatedBooking.customer = updatedBooking.customer || this.currentBooking.customer;


          // Update the local data
          this.currentBooking = response.data.booking;
          this.completedBookings = this.completedBookings.map(b =>
            b.id === this.currentBooking.id ? this.currentBooking : b
          );

          alert('Booking details updated successfully!');
          this.bookingModal.hide();
        } catch (error) {
          console.error('Error updating booking:', error);
          alert(`Failed to update booking: ${error.response?.data?.message || error.message}`);
        }
      },
      clearSearch() {
        this.searchQuery = '';
        this.currentPage = 1;
        this.fetchCompletedBookings();
      },
      changePage(page) {
        if (page >= 1 && page <= this.totalPages) {
          this.currentPage = page;
          this.fetchCompletedBookings();
        }
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
      }
    }
  };
  </script>

  <style scoped>
  .dashboard-container {
    display: flex;
    min-height: 100vh;
    background-image: url('/images/background.jpg');
  }

  .dashboard-sidebar {
    flex: 0 0 250px;
  }

  .admin-records-container {
    flex: 1;
    padding: 2rem;
    overflow-y: auto;
  }

  .records-table-wrapper {
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
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

  .search-box {
    width: 400px;
  }

  .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
  }

  .page-link {
    color: #0d6efd;
  }
  </style>
