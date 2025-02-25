<template>
  <div class="customers-container">
    <div class="header-actions">
      <h2>Customers</h2>
      <button class="add-customer-btn" @click="openAddModal">
        <i class="fas fa-plus"></i> Add New Customer
      </button>
    </div>

    <div v-if="error" class="alert alert-error">
      {{ error }}
    </div>

    <div v-if="loading" class="loading-spinner">
      Loading customers...
    </div>

    <div v-else class="customers-table-container">
      <table class="customers-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Last Name</th>
            <th>First Name</th>
            <th>Middle Name</th>
            <th>Gender</th>
            <th>Age</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="customer in customers" :key="customer.id">
            <td>#{{ customer.id }}</td>
            <td>{{ customer.lastname }}</td>
            <td>{{ customer.firstname }}</td>
            <td>{{ customer.middlename || 'N/A' }}</td>
            <td>{{ customer.gender }}</td>
            <td>{{ customer.age }}</td>
            <td>{{ customer.email }}</td>
            <td>{{ customer.phone }}</td>
            <td>
              <span :class="['status', customer.status.toLowerCase()]">
                {{ customer.status }}
              </span>
            </td>
            <td class="actions">
              <button class="edit-btn" @click="openEditModal(customer)">
                <i class="fas fa-edit"></i>
              </button>
              <button class="delete-btn" @click="deleteCustomer(customer.id)">
                <i class="fas fa-trash"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Customer Form Modal -->
    <div v-if="showModal" class="modal" @click.self="closeModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>{{ isEditing ? 'Edit Customer' : 'Add New Customer' }}</h3>
          <button type="button" class="close-btn" @click="closeModal">&times;</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="handleSubmit" class="customer-form">
            <div class="form-group">
              <label for="lastname">Last Name</label>
              <div class="input-group">
                <i class="fas fa-user"></i>
                <input
                  type="text"
                  id="lastname"
                  v-model="form.lastname"
                  :class="{ 'error': errors.lastname }"
                  placeholder="Enter last name"
                  required
                />
              </div>
              <span class="error-message" v-if="errors.lastname">{{ errors.lastname }}</span>
            </div>

            <div class="form-group">
              <label for="firstname">First Name</label>
              <div class="input-group">
                <i class="fas fa-user"></i>
                <input
                  type="text"
                  id="firstname"
                  v-model="form.firstname"
                  :class="{ 'error': errors.firstname }"
                  placeholder="Enter first name"
                  required
                />
              </div>
              <span class="error-message" v-if="errors.firstname">{{ errors.firstname }}</span>
            </div>

            <div class="form-group">
              <label for="middlename">Middle Name</label>
              <div class="input-group">
                <i class="fas fa-user"></i>
                <input
                  type="text"
                  id="middlename"
                  v-model="form.middlename"
                  :class="{ 'error': errors.middlename }"
                  placeholder="Enter middle name"
                />
              </div>
              <span class="error-message" v-if="errors.middlename">{{ errors.middlename }}</span>
            </div>

            <div class="form-group">
              <label for="gender">Gender</label>
              <div class="input-group">
                <i class="fas fa-venus-mars"></i>
                <select
                  id="gender"
                  v-model="form.gender"
                  :class="{ 'error': errors.gender }"
                  required
                >
                  <option value="" disabled>Select gender</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <span class="error-message" v-if="errors.gender">{{ errors.gender }}</span>
            </div>

            <div class="form-group">
              <label for="age">Age</label>
              <div class="input-group">
                <i class="fas fa-birthday-cake"></i>
                <input
                  type="number"
                  id="age"
                  v-model.number="form.age"
                  :class="{ 'error': errors.age }"
                  placeholder="Enter age"
                  min="0"
                  required
                />
              </div>
              <span class="error-message" v-if="errors.age">{{ errors.age }}</span>
            </div>

            <div class="form-group">
              <label for="email">Email</label>
              <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input
                  type="email"
                  id="email"
                  v-model="form.email"
                  :class="{ 'error': errors.email }"
                  placeholder="Enter email"
                  required
                />
              </div>
              <span class="error-message" v-if="errors.email">{{ errors.email }}</span>
            </div>

            <div class="form-group">
              <label for="phone">Phone Number</label>
              <div class="input-group">
                <i class="fas fa-phone"></i>
                <input
                  type="tel"
                  id="phone"
                  v-model="form.phone"
                  :class="{ 'error': errors.phone }"
                  placeholder="Enter phone number"
                  required
                />
              </div>
              <span class="error-message" v-if="errors.phone">{{ errors.phone }}</span>
            </div>

            <div class="form-group">
              <label for="password">Password</label>
              <div class="input-group">
                <i class="fas fa-lock"></i>
                <input
                  :type="showPassword ? 'text' : 'password'"
                  id="password"
                  v-model="form.password"
                  :class="{ 'error': errors.password }"
                  placeholder="Create password"
                  required
                />
                <i 
                  :class="['fas', showPassword ? 'fa-eye-slash' : 'fa-eye', 'toggle-password']"
                  @click="showPassword = !showPassword"
                ></i>
              </div>
              <span class="error-message" v-if="errors.password">{{ errors.password }}</span>
            </div>

            <div class="modal-actions">
              <button type="button" class="cancel-btn" @click="closeModal">Cancel</button>
              <button type="submit" class="submit-btn" :disabled="isLoading">
                <span v-if="!isLoading">{{ isEditing ? 'Update' : 'Add' }} Customer</span>
                <i v-else class="fas fa-spinner fa-spin"></i>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

export default {
  name: 'AdminCustomers',
  
  setup() {
    const router = useRouter();
    const customers = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const showModal = ref(false);
    const isEditing = ref(false);
    const isLoading = ref(false);
    const currentCustomerId = ref(null);
    const showPassword = ref(false);

    // Check authentication
    const checkAuth = () => {
      const token = localStorage.getItem('admin_token');
      if (!token) {
        router.push({ name: 'admin-login' });
        return false;
      }
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      return true;
    };
    
    const form = reactive({
      lastname: '',
      firstname: '',
      middlename: '',
      gender: '',
      age: '',
      email: '',
      phone: '',
      password: ''
    });

    const errors = reactive({
      lastname: '',
      firstname: '',
      middlename: '',
      gender: '',
      age: '',
      email: '',
      phone: '',
      password: ''
    });

    const resetForm = () => {
      form.lastname = '';
      form.firstname = '';
      form.middlename = '';
      form.gender = '';
      form.age = '';
      form.email = '';
      form.phone = '';
      form.password = '';
      Object.keys(errors).forEach(key => errors[key] = '');
    };

    const fetchCustomers = async () => {
      if (!checkAuth()) return;
      
      try {
        loading.value = true;
        const response = await axios.get('/api/admin/customers');
        customers.value = response.data;
      } catch (err) {
        if (err.response?.status === 401) {
          router.push({ name: 'admin-login' });
        } else {
          error.value = 'Failed to load customers';
          console.error('Error fetching customers:', err);
        }
      } finally {
        loading.value = false;
      }
    };

    const openAddModal = () => {
      if (!checkAuth()) return;
      isEditing.value = false;
      currentCustomerId.value = null;
      resetForm();
      showModal.value = true;
    };

    const openEditModal = (customer) => {
      if (!checkAuth()) return;
      isEditing.value = true;
      currentCustomerId.value = customer.id;
      form.lastname = customer.lastname;
      form.firstname = customer.firstname;
      form.middlename = customer.middlename;
      form.gender = customer.gender;
      form.age = customer.age;
      form.email = customer.email;
      form.phone = customer.phone;
      showModal.value = true;
    };

    const closeModal = () => {
      showModal.value = false;
      resetForm();
    };

    const handleValidationErrors = (errors) => {
      if (errors.response?.status === 401) {
        router.push({ name: 'admin-login' });
      } else if (errors.response?.data?.errors) {
        Object.keys(errors.response.data.errors).forEach(key => {
          errors[key] = errors.response.data.errors[key][0];
        });
      } else {
        error.value = 'An error occurred while saving the customer';
      }
    };

    const handleSubmit = async () => {
      if (!checkAuth()) return;
      
      try {
        isLoading.value = true;
        const data = {
          lastname: form.lastname,
          firstname: form.firstname,
          middlename: form.middlename || null,
          gender: form.gender,
          age: parseInt(form.age),
          email: form.email,
          phone: form.phone,
          password: form.password
        };

        if (isEditing.value) {
          await axios.put(`/api/admin/customers/${currentCustomerId.value}`, data);
          await fetchCustomers(); // Refresh the list after update
        } else {
          const response = await axios.post('/api/admin/customers', data);
          customers.value = [response.data, ...customers.value];
        }

        closeModal();
      } catch (err) {
        handleValidationErrors(err);
        console.error('Error submitting form:', err);
      } finally {
        isLoading.value = false;
      }
    };

    const deleteCustomer = async (customerId) => {
      if (!checkAuth()) return;
      
      if (!confirm('Are you sure you want to delete this customer?')) {
        return;
      }

      try {
        await axios.delete(`/api/admin/customers/${customerId}`);
        customers.value = customers.value.filter(c => c.id !== customerId);
      } catch (err) {
        if (err.response?.status === 401) {
          router.push({ name: 'admin-login' });
        } else {
          error.value = 'Failed to delete customer';
          console.error('Error deleting customer:', err);
        }
      }
    };

    onMounted(() => {
      if (checkAuth()) {
        fetchCustomers();
      }
    });

    return {
      customers,
      loading,
      error,
      showModal,
      isEditing,
      isLoading,
      form,
      errors,
      showPassword,
      openAddModal,
      openEditModal,
      closeModal,
      handleSubmit,
      deleteCustomer
    };
  }
};
</script>

<style scoped>
.customers-container {
  padding: 20px;
}

.header-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.header-actions h2 {
  margin: 0;
  color: #333;
  font-size: 1.5rem;
}

.add-customer-btn {
  background: #FF7A00;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.add-customer-btn:hover {
  background: #e66e00;
}

.customers-table-container {
  background: white;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.customers-table {
  width: 100%;
  border-collapse: collapse;
}

.customers-table th,
.customers-table td {
  padding: 15px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.customers-table th {
  background: #f9f9f9;
  font-weight: 500;
  color: #666;
}

.customers-table tr:hover {
  background: #f5f5f5;
}

.status {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 500;
  text-transform: capitalize;
}

.status.active {
  background: #e8f5e9;
  color: #4CAF50;
}

.status.inactive {
  background: #ffebee;
  color: #f44336;
}

.actions {
  display: flex;
  gap: 8px;
}

.edit-btn,
.delete-btn {
  padding: 6px 12px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 0.85rem;
  transition: all 0.3s ease;
}

.edit-btn {
  background: #f5f5f5;
  color: #666;
}

.edit-btn:hover {
  background: #e0e0e0;
}

.delete-btn {
  background: #ffebee;
  color: #f44336;
}

.delete-btn:hover {
  background: #ffe0e0;
}

/* Modal Styles */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  padding: 20px;
}

.modal-content {
  background: white;
  border-radius: 10px;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
}

.modal-header {
  padding: 20px;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: sticky;
  top: 0;
  background: white;
  z-index: 1;
}

.modal-header h3 {
  margin: 0;
  color: #333;
  font-size: 1.25rem;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #666;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.3s ease;
}

.close-btn:hover {
  background: #f5f5f5;
  color: #333;
}

.modal-body {
  padding: 20px;
}

.customer-form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.form-group {
  margin-bottom: 15px;
}

.input-group {
  position: relative;
  display: flex;
  align-items: center;
}

.input-group i {
  position: absolute;
  left: 12px;
  color: #666;
}

.input-group input,
.input-group select {
  width: 100%;
  padding: 10px 10px 10px 35px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 0.9rem;
  transition: border-color 0.3s ease;
}

.input-group input:focus,
.input-group select:focus {
  outline: none;
  border-color: #FF7A00;
}

.input-group input.error,
.input-group select.error {
  border-color: #f44336;
}

.error-message {
  color: #f44336;
  font-size: 0.8rem;
  margin-top: 4px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #eee;
}

.cancel-btn,
.submit-btn {
  padding: 10px 20px;
  border-radius: 6px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.cancel-btn {
  background: #f5f5f5;
  border: 1px solid #ddd;
  color: #666;
}

.cancel-btn:hover {
  background: #e0e0e0;
}

.submit-btn {
  background: #FF7A00;
  border: none;
  color: white;
  display: flex;
  align-items: center;
  gap: 8px;
}

.submit-btn:hover {
  background: #e66e00;
}

.submit-btn:disabled {
  background: #ffd1a8;
  cursor: not-allowed;
}

.alert {
  padding: 12px 20px;
  border-radius: 6px;
  margin-bottom: 20px;
}

.alert-error {
  background: #ffebee;
  color: #f44336;
  border: 1px solid #ffcdd2;
}

.loading-spinner {
  text-align: center;
  padding: 40px;
  color: #666;
}

@media (max-width: 768px) {
  .customers-table {
    display: block;
    overflow-x: auto;
  }
}
</style>