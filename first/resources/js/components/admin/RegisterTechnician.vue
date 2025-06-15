<template>
  <div class="dashboard-container">
    <div class="dashboard-sidebar">
      <AdminDashboard />
    </div>

    <div class="admin-technician-container">
      <div class="header">
        <h2>Technician Management</h2>
        <button class="btn btn-primary" @click="showRegisterForm = !showRegisterForm">
          <i class="bi bi-plus-lg"></i> Add Technician
        </button>
      </div>

      <!-- Registration Form (shown when showRegisterForm is true) -->
      <div class="register-card card mb-4" v-if="showRegisterForm">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">Register New Technician</h5>
        </div>
        <div class="card-body">
          <form @submit.prevent="registerTechnician">
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input type="text" class="form-control" v-model="name" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" v-model="email" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" class="form-control" v-model="password" required>
            </div>
            <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
            <div v-if="successMessage" class="alert alert-success">{{ successMessage }}</div>
            <div class="d-flex justify-content-end gap-2">
              <button type="button" class="btn btn-outline-secondary" @click="cancelRegistration">
                Cancel
              </button>
              <button type="submit" class="btn btn-primary" :disabled="loading">
                <span v-if="loading" class="spinner-border spinner-border-sm"></span>
                {{ loading ? 'Registering...' : 'Register' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Technician List -->
      <div class="technician-list card">
        <div class="card-header bg-light">
          <h5 class="mb-0">Technician List</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="tech in technicians" :key="tech.id">
                  <td>{{ tech.name }}</td>
                  <td>{{ tech.email }}</td>
                  <td>
                    <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(tech)">
                      <i class="bi bi-trash"></i> Delete
                    </button>
                  </td>
                </tr>
                <tr v-if="technicians.length === 0">
                  <td colspan="3" class="text-center">No technicians found</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Delete Confirmation Modal -->
      <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Confirm Deletion</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              Are you sure you want to delete technician <strong>{{ selectedTech?.name }}</strong>?
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-danger" @click="deleteTechnician" :disabled="deleteLoading">
                <span v-if="deleteLoading" class="spinner-border spinner-border-sm"></span>
                {{ deleteLoading ? 'Deleting...' : 'Delete' }}
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
import { Modal } from 'bootstrap';
import AdminDashboard from './AdminDashboard.vue';

export default {
  name: 'TechnicianManagement',
  components: {
    AdminDashboard
  },
  data() {
    return {
      name: '',
      email: '',
      password: '',
      technicians: [],
      showRegisterForm: false,
      errorMessage: '',
      successMessage: '',
      loading: false,
      deleteLoading: false,
      selectedTech: null,
      deleteModal: null
    };
  },
  mounted() {
    this.fetchTechnicians();
    this.deleteModal = new Modal(document.getElementById('deleteModal'));
  },
  methods: {
    async fetchTechnicians() {
      try {
        const token = localStorage.getItem('admin_token');
        if (token) {
          axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        }
        const response = await axios.get('http://127.0.0.1:8000/api/admin/technicians');
        this.technicians = response.data;
      } catch (error) {
        console.error('Error fetching technicians:', error);
      }
    },
    async registerTechnician() {
      this.loading = true;
      this.errorMessage = '';
      this.successMessage = '';
      try {
        const response = await axios.post('http://127.0.0.1:8000/api/admin/register-technician', {
          name: this.name,
          email: this.email,
          password: this.password
        });

        this.successMessage = 'Technician registered successfully!';
        this.name = '';
        this.email = '';
        this.password = '';
        this.showRegisterForm = false;
        await this.fetchTechnicians();
      } catch (error) {
        if (error.response && error.response.data && error.response.data.message) {
          this.errorMessage = error.response.data.message;
        } else {
          this.errorMessage = 'Failed to register technician. Please try again.';
        }
      } finally {
        this.loading = false;
      }
    },
    cancelRegistration() {
      this.showRegisterForm = false;
      this.errorMessage = '';
      this.successMessage = '';
      this.name = '';
      this.email = '';
      this.password = '';
    },
    confirmDelete(tech) {
      this.selectedTech = tech;
      this.deleteModal.show();
    },
    async deleteTechnician() {
      this.deleteLoading = true;
      try {
        await axios.delete(`http://127.0.0.1:8000/api/admin/technicians/${this.selectedTech.id}`);
        this.deleteModal.hide();
        await this.fetchTechnicians();
      } catch (error) {
        console.error('Error deleting technician:', error);
      } finally {
        this.deleteLoading = false;
      }
    }
  }
};
</script>

<style scoped>
.dashboard-container {
  display: flex;
  min-height: 100vh;
}

.dashboard-sidebar {
  flex: 0 0 250px;
}

.admin-technician-container {
  flex: 1;
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.register-card {
  border: 1px solid #dee2e6;
}

.technician-list {
  margin-top: 20px;
}

.table-responsive {
  overflow-x: auto;
}

.modal-header {
  background-color: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
}

.btn-outline-danger:hover {
  color: white;
}

.btn-sm {
  margin-right: 5px;
}

.table td, .table th {
  vertical-align: middle;
}
</style>
