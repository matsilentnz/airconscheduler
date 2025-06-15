<template>
    <div class="dashboard-container">
      <div class="dashboard-sidebar">
        <AdminDashboard />
      </div>

      <div class="customers-list-container">
        <div class="admin-header">
          <h2>Information</h2>
          <div class="admin-actions">
            <button class="btn btn-danger" @click="logout">Logout</button>
          </div>
        </div>

        <!-- Totals Section -->
        <div class="totals-section mb-4">
          <div class="row text-center">
            <div class="col-md-4">
              <div class="card shadow-sm">
                <div class="card-body">
                  <h5>Total Customers</h5>
                  <h3>{{ totalCustomers }}</h3>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card shadow-sm">
                <div class="card-body">
                  <h5>Total Booked Bookings</h5>
                  <h3>{{ totalBooked }}</h3>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card shadow-sm">
                <div class="card-body">
                  <h5>Total In Progress Bookings</h5>
                  <h3>{{ totalInProgress }}</h3>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Search Bar -->
        <div class="search-filter mb-4">
          <div class="input-group">
            <input
              type="text"
              class="form-control"
              placeholder="Search customers..."
              v-model="searchQuery"
              @input="filterCustomers"
            />
            <button class="btn btn-primary" type="button">
              <i class="bi bi-search"></i>
            </button>
          </div>
        </div>

        <!-- Loading, Error, No Data -->
        <div v-if="loading" class="text-center my-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Loading customers...</p>
        </div>

        <div v-else-if="error" class="alert alert-danger">
          {{ error }}
        </div>

        <div v-else-if="filteredCustomers.length === 0" class="alert alert-info">
          No customers found.
        </div>

        <!-- Table -->
        <div v-else class="table-responsive">
          <table class="table table-striped table-hover">
            <thead class="table-dark">
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="customer in filteredCustomers" :key="customer.id">
                <td>{{ customer.id }}</td>
                <td>{{ customer.name }}</td>
                <td>{{ customer.email }}</td>
                <td>{{ customer.phone }}</td>
                <td>{{ customer.address }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </template>

  <script>
  import axios from "axios";
  import AdminDashboard from "./AdminDashboard.vue";

  export default {
    components: {
      AdminDashboard,
    },
    name: "CustomersList",
    data() {
      return {
        customers: [],
        filteredCustomers: [],
        totalCustomers: 0,
        totalBooked: 0,
        totalInProgress: 0,
        loading: true,
        error: null,
        searchQuery: "",
      };
    },
    created() {
      this.checkAdminAuth();
      this.fetchCustomers();
      this.fetchTotals();
    },
    methods: {
      checkAdminAuth() {
        const adminToken = localStorage.getItem("admin_token");
        if (!adminToken) {
          this.$router.push("/admin");
        }
      },
      async fetchCustomers() {
        try {
          this.loading = true;
          const response = await axios.get("http://127.0.0.1:8000/api/admin/customers");

          if (response.data) {
            this.customers = response.data;
            this.filteredCustomers = [...this.customers];
            this.totalCustomers = this.customers.length;
          } else {
            throw new Error("No data received");
          }
        } catch (err) {
          console.error("Fetch failed:", err);
          this.error = "Failed to load customers. Using sample data.";

          // Fallback mock data
          setTimeout(() => {
            this.customers = [
              { id: 1, name: "John Doe", email: "john@example.com", phone: "123-456-7890", address: "123 Main St" },
              { id: 2, name: "Jane Smith", email: "jane@example.com", phone: "987-654-3210", address: "456 Oak Ave" },
              { id: 3, name: "Bob Johnson", email: "bob@example.com", phone: "555-123-4567", address: "789 Pine Rd" },
            ];
            this.filteredCustomers = [...this.customers];
            this.totalCustomers = this.customers.length;
          }, 1000);
        } finally {
          this.loading = false;
        }
      },
      async fetchTotals() {
        try {
          const response = await axios.get("http://127.0.0.1:8000/api/admin/bookings/totals");

          if (response.data) {
            this.totalBooked = response.data.totalBooked;
            this.totalInProgress = response.data.totalInProgress;
          }
        } catch (err) {
          console.error("Error fetching totals:", err);
          this.error = "Failed to load totals.";
        }
      },
      filterCustomers() {
        const query = this.searchQuery.toLowerCase();
        this.filteredCustomers = this.customers.filter((c) =>
          c.name.toLowerCase().includes(query) ||
          c.email.toLowerCase().includes(query) ||
          c.phone.includes(query) ||
          c.address.toLowerCase().includes(query)
        );
      },
      logout() {
        localStorage.removeItem("admin_token");
        this.$router.push("/admin");
      },
    },
  };
  </script>

  <style scoped>
  .dashboard-container {
    display: flex;
    min-height: 100vh;
  }

  .dashboard-sidebar {
    flex-shrink: 0;
  }

  .customers-list-container {
    flex-grow: 1;
    padding: 30px;
    overflow-y: auto;
  }

  .admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 1px solid #dee2e6;
  }

  .admin-header h2 {
    font-weight: bold;
  }

  .search-filter {
    max-width: 400px;
  }

  .table th,
  .table td {
    vertical-align: middle;
  }

  .totals-section .card {
    border: none;
    border-radius: 10px;
    background-color: #f8f9fa;
  }

  .totals-section .card-body {
    padding: 20px;
  }

  .totals-section h5 {
    font-size: 1.2rem;
    margin-bottom: 10px;
  }

  .totals-section h3 {
    font-size: 2rem;
    font-weight: bold;
    color: #007bff;
  }
  </style>
