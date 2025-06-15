<template>
  <div class="dashboard-container">
    <div class="dashboard-sidebar">
      <CustomerDashboard />
    </div>
    <div class="dashboard-content">
      <div class="customer-info-container">
        <div class="header">
          <h2>Customer Information</h2>
          <div class="header-buttons">
            <button v-if="!isEditing" @click="startEditing" class="btn btn-edit">
              <i class="bi bi-pencil-fill"></i> Edit Profile
            </button>
            <button @click="logout" class="btn btn-danger">
              <i class="bi bi-box-arrow-right"></i> Logout
            </button>
          </div>
        </div>

        <div v-if="loading" class="loading">
          <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p>Loading your information...</p>
        </div>

        <div v-else-if="error" class="error">{{ error }}</div>

        <!-- View Mode -->
        <div v-else-if="!isEditing" class="customer-details">
          <div class="info-card">
            <div class="info-item">
              <i class="bi bi-person-fill"></i>
              <div class="info-content">
                <strong>Name:</strong>
                <span class="data-value">{{ customer.name }}</span>
              </div>
            </div>
            <div class="info-item">
              <i class="bi bi-envelope-fill"></i>
              <div class="info-content">
                <strong>Email:</strong>
                <span class="data-value">{{ customer.email }}</span>
              </div>
            </div>
            <div class="info-item">
              <i class="bi bi-telephone-fill"></i>
              <div class="info-content">
                <strong>Phone:</strong>
                <span class="data-value">{{ customer.phone }}</span>
              </div>
            </div>
            <div class="info-item">
              <i class="bi bi-geo-alt-fill"></i>
              <div class="info-content">
                <strong>Address:</strong>
                <span class="data-value">{{ customer.address }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Edit Mode -->
        <div v-else class="customer-edit-form">
          <div class="info-card edit-card">
            <form @submit.prevent="updateCustomerInfo">
              <div class="form-group">
                <label for="name">
                  <i class="bi bi-person-fill"></i> Name:
                </label>
                <input
                  type="text"
                  id="name"
                  v-model="editForm.name"
                  class="form-control"
                  required
                />
              </div>

              <div class="form-group">
                <label for="email">
                  <i class="bi bi-envelope-fill"></i> Email:
                </label>
                <input
                  type="email"
                  id="email"
                  v-model="editForm.email"
                  class="form-control"
                  required
                />
              </div>

              <div class="form-group">
                <label for="phone">
                  <i class="bi bi-telephone-fill"></i> Phone:
                </label>
                <input
                  type="text"
                  id="phone"
                  v-model="editForm.phone"
                  class="form-control"
                  required
                />
              </div>

              <div class="form-group">
                <label for="address">
                  <i class="bi bi-geo-alt-fill"></i> Address:
                </label>
                <textarea
                  id="address"
                  v-model="editForm.address"
                  class="form-control"
                  required
                ></textarea>
              </div>

              <div class="form-buttons">
                <button type="submit" class="btn btn-save" :disabled="updating">
                  <i class="bi bi-check-circle"></i>
                  {{ updating ? 'Saving...' : 'Save Changes' }}
                </button>
                <button type="button" class="btn btn-cancel" @click="cancelEdit">
                  <i class="bi bi-x-circle"></i> Cancel
                </button>
              </div>

              <div v-if="updateError" class="update-error">
                {{ updateError }}
              </div>
              <div v-if="updateSuccess" class="update-success">
                {{ updateSuccess }}
              </div>
            </form>
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
      customer: {},
      loading: true,
      error: null,
      isEditing: false,
      updating: false,
      updateError: null,
      updateSuccess: null,
      editForm: {
        name: "",
        email: "",
        phone: "",
        address: ""
      }
    };
  },
  created() {
    this.fetchCustomerInfo();
  },
  methods: {
    async fetchCustomerInfo() {
      this.loading = true;
      this.error = null;

      // Get token from localStorage
      const token = localStorage.getItem("auth_token");

      // If no token, redirect to login
      if (!token) {
        this.$router.push("/login");
        return;
      }

      try {
        // Set authorization header
        axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;

        // Make API request
        const response = await axios.get("http://127.0.0.1:8000/api/customer");

        // Store customer data
        this.customer = response.data;

        // Initialize edit form with current data
        this.editForm = {
          name: this.customer.name,
          email: this.customer.email,
          phone: this.customer.phone,
          address: this.customer.address
        };
      } catch (error) {
        console.error("Error fetching customer info:", error);

        if (error.response && error.response.status === 401) {
          this.error = "Your session has expired. Please log in again.";
          localStorage.removeItem("auth_token");
          setTimeout(() => this.$router.push("/login"), 2000);
        } else {
          this.error = "Failed to load your information. Please try again.";
        }
      } finally {
        this.loading = false;
      }
    },

    startEditing() {
      this.isEditing = true;
      this.updateError = null;
      this.updateSuccess = null;
    },

    cancelEdit() {
      this.isEditing = false;
      this.updateError = null;
      this.updateSuccess = null;

      // Reset form to original values
      this.editForm = {
        name: this.customer.name,
        email: this.customer.email,
        phone: this.customer.phone,
        address: this.customer.address
      };
    },

    async updateCustomerInfo() {
      this.updating = true;
      this.updateError = null;
      this.updateSuccess = null;

      const token = localStorage.getItem("auth_token");

      if (!token) {
        this.$router.push("/login");
        return;
      }

      try {
        axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;

        const response = await axios.put("http://127.0.0.1:8000/api/customer/update", this.editForm);

        // Update local customer data
        this.customer = response.data;

        this.updateSuccess = "Your information has been updated successfully!";

        // Exit edit mode after a short delay
        setTimeout(() => {
          this.isEditing = false;
          this.updateSuccess = null;
        }, 2000);
      } catch (error) {
        console.error("Error updating customer info:", error);

        if (error.response) {
          if (error.response.status === 401) {
            this.updateError = "Your session has expired. Please log in again.";
            localStorage.removeItem("auth_token");
            setTimeout(() => this.$router.push("/login"), 2000);
          } else if (error.response.status === 422) {
            // Validation errors
            const errors = error.response.data.errors;
            const firstError = Object.values(errors)[0][0];
            this.updateError = firstError || "Validation failed. Please check your input.";
          } else {
            this.updateError = error.response.data.message || "Failed to update your information.";
          }
        } else {
          this.updateError = "Network error. Please check your connection.";
        }
      } finally {
        this.updating = false;
      }
    },

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
        delete axios.defaults.headers.common["Authorization"];
        this.$router.push("/login");
      }
    }
  }
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
  margin-left: 280px;
  padding: 30px;
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Customer Info Container - BLUE THEME */
.customer-info-container {
  width: 100%;
  max-width: 800px;
  padding: 40px;
  background: #06a5ff; /* Blue background color */
  border-radius: 12px;
  box-shadow: 0px 5px 20px rgba(0, 0, 0, 0.4);
  animation: fadeIn 0.8s ease-in-out;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

/* Header */
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 2px solid rgba(255, 255, 255, 0.5);
  padding-bottom: 15px;
  margin-bottom: 20px;
}

.header h2 {
  margin: 0;
  font-weight: 700;
  font-size: 1.8rem;
  color: white;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.header-buttons {
  display: flex;
  gap: 10px;
}

/* Loading & Error */
.loading {
  text-align: center;
  padding: 30px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 15px;
}

.error {
  text-align: center;
  padding: 15px;
  border-radius: 8px;
  font-weight: bold;
  background: rgba(255, 0, 0, 0.2);
  color: #ff4d4d;
  border: 1px solid rgba(255, 0, 0, 0.3);
}

/* Customer Details */
.customer-details {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

/* Info Card - BLUE THEME */
.info-card {
  background: rgba(255, 255, 255, 0.9); /* Lighter background for better contrast with black text */
  padding: 25px;
  border-radius: 10px;
  transition: all 0.3s ease-in-out;
  box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.4);
}

.info-card:hover {
  transform: translateY(-5px);
  box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.4);
}

/* Info Items */
.info-item {
  padding: 15px;
  font-size: 1.1rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1); /* Darker border for contrast */
  display: flex;
  align-items: center;
  gap: 15px;
}

.info-item i {
  font-size: 1.3rem;
  color: white;
  background: #06a5ff; /* Blue background for icons */
  padding: 8px;
  border-radius: 50%;
  width: 35px;
  height: 35px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.info-item:last-child {
  border-bottom: none;
}

.info-content {
  flex: 1;
}

.info-item strong {
  color: #06a5ff; /* Blue color for labels */
  font-weight: 600;
  margin-right: 8px;
}

/* Data values in black */
.data-value {
  color: #000000; /* Black text for data values */
  font-weight: 500;
}

/* Edit Form Styles */
.customer-edit-form {
  animation: fadeIn 0.5s ease-in-out;
}

.edit-card {
  background: rgba(255, 255, 255, 0.95);
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #06a5ff;
  font-weight: 600;
  margin-bottom: 8px;
}

.form-group label i {
  font-size: 1.2rem;
}

.form-control {
  width: 100%;
  padding: 10px;
  border: 1px solid rgba(6, 165, 255, 0.3);
  border-radius: 5px;
  font-size: 1rem;
  transition: border 0.3s ease;
}

.form-control:focus {
  outline: none;
  border-color: #06a5ff;
  box-shadow: 0 0 0 2px rgba(6, 165, 255, 0.2);
}

textarea.form-control {
  min-height: 100px;
  resize: vertical;
}

.form-buttons {
  display: flex;
  gap: 10px;
  margin-top: 25px;
}

/* Button Styles */
.btn {
  padding: 10px 16px;
  font-size: 1rem;
  font-weight: bold;
  border-radius: 6px;
  cursor: pointer;
  transition: 0.3s ease;
  display: flex;
  align-items: center;
  gap: 5px;
  border: none;
}

.btn-danger {
  background: #ff4d4d;
  color: white;
}

.btn-danger:hover {
  background: #d83434;
  transform: scale(1.05);
}

.btn-edit {
  background: #00090e;
  color: #06a5ff;
}

.btn-edit:hover {
  background: #001824;
  transform: scale(1.05);
}

.btn-save {
  background: #28a745;
  color: white;
  flex: 1;
}

.btn-save:hover {
  background: #218838;
  transform: scale(1.02);
}

.btn-save:disabled {
  background: #6c757d;
  cursor: not-allowed;
  transform: none;
}

.btn-cancel {
  background: #6c757d;
  color: white;
}

.btn-cancel:hover {
  background: #5a6268;
}

.btn:active {
  transform: scale(0.98);
}

/* Update Messages */
.update-error, .update-success {
  margin-top: 15px;
  padding: 10px;
  border-radius: 5px;
  text-align: center;
  font-weight: 500;
}

.update-error {
  background: rgba(255, 0, 0, 0.1);
  color: #ff4d4d;
  border: 1px solid rgba(255, 0, 0, 0.2);
}

.update-success {
  background: rgba(40, 167, 69, 0.1);
  color: #28a745;
  border: 1px solid rgba(40, 167, 69, 0.2);
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

  .customer-info-container {
    padding: 20px;
  }

  .header {
    flex-direction: column;
    gap: 15px;
    align-items: flex-start;
  }

  .header-buttons {
    width: 100%;
    flex-direction: column;
  }

  .btn {
    width: 100%;
    justify-content: center;
  }

  .info-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }

  .info-item i {
    margin-bottom: 5px;
  }

  .form-buttons {
    flex-direction: column;
  }
}
</style>
