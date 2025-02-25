<template>
  <div class="refund-container">
    <div class="header-actions">
      <h2>Return & Refund Management</h2>
      <button class="new-refund-btn" @click="openCreateModal">
        <i class="fas fa-plus"></i> Create New Refund
      </button>
    </div>

    <div v-if="error" class="alert alert-error">
      {{ error }}
    </div>

    <div v-if="loading" class="loading-spinner">
      Loading refunds...
    </div>

    <div v-else class="refund-table-container">
      <table class="refund-table">
        <thead>
          <tr>
            <th>Refund ID</th>
            <th>Order Number</th>
            <th>Customer</th>
            <th>Amount</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="refund in refunds" :key="refund.id">
            <td>#{{ refund?.id || 'N/A' }}</td>
            <td>#{{ refund?.order_number || 'N/A' }}</td>
            <td>{{ refund?.customer_name || 'N/A' }}</td>
            <td>${{ refund?.amount?.toFixed(2) || '0.00' }}</td>
            <td>{{ refund?.reason || 'N/A' }}</td>
            <td>
              <span :class="['status', refund?.status ? refund.status.toLowerCase() : 'pending']">
                {{ refund?.status || 'Pending' }}
              </span>
            </td>
            <td>{{ refund?.created_at || 'N/A' }}</td>
            <td class="actions">
              <button 
                v-if="refund.status === 'Pending'" 
                class="approve-btn" 
                @click="updateStatus(refund.id, 'Approved')"
              >
                <i class="fas fa-check"></i> Approve
              </button>
              <button 
                v-if="refund.status === 'Pending'" 
                class="reject-btn" 
                @click="updateStatus(refund.id, 'Rejected')"
              >
                <i class="fas fa-times"></i> Reject
              </button>
              <button class="view-btn" @click="openViewModal(refund)">
                <i class="fas fa-eye"></i> View
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Create/View Refund Modal -->
    <div v-if="showModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>{{ isViewing ? 'View Refund Details' : 'Create New Refund' }}</h3>
          <button class="close-btn" @click="closeModal">&times;</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="submitForm">
            <div class="form-group">
              <label for="order_id">Order Number</label>
              <input 
                type="text" 
                id="order_id" 
                v-model="formData.order_id" 
                required
                :disabled="isViewing"
                :class="{ 'error': formErrors.order_id }"
              >
              <span class="error-message" v-if="formErrors.order_id">{{ formErrors.order_id }}</span>
            </div>

            <div class="form-group">
              <label for="user_id">Customer ID</label>
              <input 
                type="text" 
                id="user_id" 
                v-model="formData.user_id" 
                required
                :disabled="isViewing"
                :class="{ 'error': formErrors.user_id }"
              >
              <span class="error-message" v-if="formErrors.user_id">{{ formErrors.user_id }}</span>
            </div>

            <div class="form-group">
              <label for="amount">Refund Amount</label>
              <input 
                type="number" 
                id="amount" 
                v-model="formData.amount" 
                step="0.01"
                required
                :disabled="isViewing"
                :class="{ 'error': formErrors.amount }"
              >
              <span class="error-message" v-if="formErrors.amount">{{ formErrors.amount }}</span>
            </div>

            <div class="form-group">
              <label for="reason">Reason</label>
              <textarea 
                id="reason" 
                v-model="formData.reason" 
                required
                :disabled="isViewing"
                :class="{ 'error': formErrors.reason }"
              ></textarea>
              <span class="error-message" v-if="formErrors.reason">{{ formErrors.reason }}</span>
            </div>

            <div v-if="!isViewing" class="form-group">
              <label for="status">Status</label>
              <select 
                id="status" 
                v-model="formData.status" 
                required
                :class="{ 'error': formErrors.status }"
              >
                <option value="Pending">Pending</option>
                <option value="Approved">Approved</option>
                <option value="Rejected">Rejected</option>
              </select>
              <span class="error-message" v-if="formErrors.status">{{ formErrors.status }}</span>
            </div>

            <div class="form-group">
              <label for="admin_notes">Admin Notes</label>
              <textarea 
                id="admin_notes" 
                v-model="formData.admin_notes"
                :disabled="isViewing"
                :class="{ 'error': formErrors.admin_notes }"
              ></textarea>
              <span class="error-message" v-if="formErrors.admin_notes">{{ formErrors.admin_notes }}</span>
            </div>

            <div v-if="!isViewing" class="form-actions">
              <button type="button" class="cancel-btn" @click="closeModal">Cancel</button>
              <button type="submit" class="submit-btn" :disabled="isSubmitting">
                {{ isSubmitting ? 'Saving...' : 'Create Refund' }}
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
  name: 'AdminRefund',
  
  setup() {
    const router = useRouter();
    const refunds = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const showModal = ref(false);
    const isViewing = ref(false);
    const isSubmitting = ref(false);

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

    const formData = reactive({
      order_id: '',
      user_id: '',
      amount: '',
      reason: '',
      status: 'Pending',
      admin_notes: ''
    });

    const formErrors = reactive({
      order_id: '',
      user_id: '',
      amount: '',
      reason: '',
      status: '',
      admin_notes: ''
    });

    const resetForm = () => {
      formData.order_id = '';
      formData.user_id = '';
      formData.amount = '';
      formData.reason = '';
      formData.status = 'Pending';
      formData.admin_notes = '';
      Object.keys(formErrors).forEach(key => formErrors[key] = '');
    };

    const fetchRefunds = async () => {
      if (!checkAuth()) return;
      
      try {
        loading.value = true;
        const response = await axios.get('/api/admin/refunds');
        refunds.value = response.data;
      } catch (err) {
        if (err.response?.status === 401) {
          router.push({ name: 'admin-login' });
        } else {
          error.value = 'Failed to load refunds';
          console.error('Error fetching refunds:', err);
        }
      } finally {
        loading.value = false;
      }
    };

    const openCreateModal = () => {
      if (!checkAuth()) return;
      isViewing.value = false;
      resetForm();
      showModal.value = true;
    };

    const openViewModal = (refund) => {
      if (!checkAuth()) return;
      isViewing.value = true;
      formData.order_id = refund.order_id;
      formData.user_id = refund.user_id;
      formData.amount = refund.amount;
      formData.reason = refund.reason;
      formData.status = refund.status;
      formData.admin_notes = refund.admin_notes || '';
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
          formErrors[key] = errors.response.data.errors[key][0];
        });
      } else {
        error.value = 'An error occurred while saving the refund';
      }
    };

    const submitForm = async () => {
      if (!checkAuth()) return;
      
      try {
        isSubmitting.value = true;
        const response = await axios.post('/api/admin/refunds', formData);
        refunds.value = [response.data, ...refunds.value];
        closeModal();
      } catch (err) {
        handleValidationErrors(err);
      } finally {
        isSubmitting.value = false;
      }
    };

    const updateStatus = async (refundId, newStatus) => {
      if (!checkAuth()) return;
      
      if (!confirm(`Are you sure you want to ${newStatus.toLowerCase()} this refund?`)) {
        return;
      }

      try {
        const response = await axios.put(`/api/admin/refunds/${refundId}`, {
          status: newStatus,
          admin_notes: formData.admin_notes
        });
        
        const index = refunds.value.findIndex(r => r.id === refundId);
        if (index !== -1) {
          refunds.value[index] = response.data;
        }
      } catch (err) {
        if (err.response?.status === 401) {
          router.push({ name: 'admin-login' });
        } else {
          error.value = `Failed to ${newStatus.toLowerCase()} refund`;
          console.error('Error updating refund:', err);
        }
      }
    };

    onMounted(() => {
      if (checkAuth()) {
        fetchRefunds();
      }
    });

    return {
      refunds,
      loading,
      error,
      showModal,
      isViewing,
      isSubmitting,
      formData,
      formErrors,
      openCreateModal,
      openViewModal,
      closeModal,
      submitForm,
      updateStatus
    };
  }
};
</script>

<style scoped>
.refund-container {
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

.new-refund-btn {
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

.new-refund-btn:hover {
  background: #e66e00;
}

.refund-table-container {
  background: white;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.refund-table {
  width: 100%;
  border-collapse: collapse;
}

.refund-table th,
.refund-table td {
  padding: 15px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.refund-table th {
  background: #f9f9f9;
  font-weight: 500;
  color: #666;
}

.refund-table tr:hover {
  background: #f5f5f5;
}

.status {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 500;
  text-transform: capitalize;
}

.status.pending {
  background: #fff3e0;
  color: #ff9800;
}

.status.approved {
  background: #e8f5e9;
  color: #4caf50;
}

.status.rejected {
  background: #ffebee;
  color: #f44336;
}

.actions {
  display: flex;
  gap: 8px;
}

.approve-btn,
.reject-btn,
.view-btn {
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

.approve-btn {
  background: #e8f5e9;
  color: #4caf50;
}

.approve-btn:hover {
  background: #c8e6c9;
}

.reject-btn {
  background: #ffebee;
  color: #f44336;
}

.reject-btn:hover {
  background: #ffcdd2;
}

.view-btn {
  background: #f5f5f5;
  color: #666;
}

.view-btn:hover {
  background: #e0e0e0;
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
}

.modal-content {
  background: white;
  border-radius: 10px;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  padding: 20px;
  border-bottom: 1px solid #eee;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
  color: #333;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #666;
}

.modal-body {
  padding: 20px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  color: #666;
  font-weight: 500;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 0.9rem;
}

.form-group textarea {
  min-height: 100px;
  resize: vertical;
}

.form-group input.error,
.form-group select.error,
.form-group textarea.error {
  border-color: #f44336;
}

.error-message {
  color: #f44336;
  font-size: 0.8rem;
  margin-top: 4px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}

.cancel-btn,
.submit-btn {
  padding: 10px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.3s ease;
}

.cancel-btn {
  background: #f5f5f5;
  color: #666;
}

.submit-btn {
  background: #FF7A00;
  color: white;
}

.submit-btn:disabled {
  background: #ffd0a6;
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
  .refund-table {
    display: block;
    overflow-x: auto;
  }
}
</style>