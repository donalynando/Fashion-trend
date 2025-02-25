<template>
  <div class="orders-container">
    <h2>Manage Orders</h2>
    
    <div v-if="error" class="alert alert-error">
      {{ error }}
    </div>

    <div v-if="loading" class="loading-spinner">
      Loading orders...
    </div>
    
    <div v-else class="orders-table-container">
      <table class="orders-table">
        <thead>
          <tr>
            <th>Order Number</th>
            <th>Customer Name</th>
            <th>Date</th>
            <th>Items</th>
            <th>Total</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in orders" :key="order.id">
            <td>{{ order.order_number }}</td>
            <td>{{ order.customerName }}</td>
            <td>{{ formatDate(order.date) }}</td>
            <td>{{ order.items.length }} items</td>
            <td>${{ order.total }}</td>
            <td>
              <span :class="['status', order.status.toLowerCase()]">
                {{ order.status }}
              </span>
            </td>
            <td class="actions">
              <button class="view-btn" @click="viewOrder(order)">
                <i class="fas fa-eye"></i> View
              </button>
              <button class="edit-btn" @click="editOrder(order)">
                <i class="fas fa-edit"></i> Edit
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- View Order Modal -->
    <div v-if="showViewModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Order Details - {{ selectedOrder?.order_number }}</h3>
          <button class="close-btn" @click="closeViewModal">&times;</button>
        </div>
        <div class="modal-body">
          <div class="order-details">
            <div class="detail-section">
              <h4>Customer Information</h4>
              <p><strong>Name:</strong> {{ selectedOrder?.customerName }}</p>
              <p><strong>Order Date:</strong> {{ formatDate(selectedOrder?.date) }}</p>
            </div>

            <div class="detail-section">
              <h4>Items</h4>
              <table class="items-table">
                <thead>
                  <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in selectedOrder?.items" :key="item.id">
                    <td>{{ item.product_name }}</td>
                    <td>{{ item.quantity }}</td>
                    <td>${{ item.price }}</td>
                    <td>${{ item.subtotal }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="detail-section">
              <h4>Shipping Information</h4>
              <p><strong>Address:</strong></p>
              <p v-if="typeof selectedOrder?.shipping_address === 'object'">
                {{ selectedOrder?.shipping_address.street }},<br>
                {{ selectedOrder?.shipping_address.city }},<br>
                {{ selectedOrder?.shipping_address.state }} {{ selectedOrder?.shipping_address.zip }}
              </p>
              <p><strong>Shipping Option:</strong> {{ selectedOrder?.shipping_option }}</p>
              <p><strong>Shipping Fee:</strong> ${{ selectedOrder?.shipping_fee }}</p>
            </div>

            <div class="detail-section">
              <h4>Payment Information</h4>
              <p><strong>Method:</strong> {{ selectedOrder?.payment_method }}</p>
              <p><strong>Payment Fee:</strong> ${{ selectedOrder?.payment_fee }}</p>
            </div>

            <div class="detail-section totals">
              <p><strong>Subtotal:</strong> ${{ selectedOrder?.subtotal }}</p>
              <p v-if="selectedOrder?.voucher_code">
                <strong>Voucher ({{ selectedOrder.voucher_code }}):</strong>
                -${{ selectedOrder.voucher_discount }}
              </p>
              <p class="total"><strong>Total:</strong> ${{ selectedOrder?.total }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Order Modal -->
    <div v-if="showEditModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Update Order Status - {{ selectedOrder?.order_number }}</h3>
          <button class="close-btn" @click="closeEditModal">&times;</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="updateOrderStatus">
            <div class="form-group">
              <label for="status">Order Status</label>
              <select 
                id="status" 
                v-model="editForm.status" 
                required
              >
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>
            <div class="form-actions">
              <button type="button" class="cancel-btn" @click="closeEditModal">Cancel</button>
              <button type="submit" class="submit-btn" :disabled="updating">
                {{ updating ? 'Updating...' : 'Update Status' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

export default {
  name: 'AdminOrders',
  
  setup() {
    const router = useRouter();
    const orders = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const showViewModal = ref(false);
    const showEditModal = ref(false);
    const selectedOrder = ref(null);
    const updating = ref(false);
    const editForm = ref({
      status: ''
    });

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

    const formatDate = (dateString) => {
      const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      };
      return new Date(dateString).toLocaleDateString(undefined, options);
    };

    const fetchOrders = async () => {
      if (!checkAuth()) return;

      try {
        loading.value = true;
        const response = await axios.get('/api/admin/orders');
        orders.value = response.data;
      } catch (err) {
        if (err.response?.status === 401) {
          router.push({ name: 'admin-login' });
        } else {
          error.value = 'Failed to load orders';
          console.error('Error fetching orders:', err);
        }
      } finally {
        loading.value = false;
      }
    };

    const viewOrder = async (order) => {
      if (!checkAuth()) return;
      try {
        const response = await axios.get(`/api/admin/orders/${order.id}`);
        selectedOrder.value = response.data;
        showViewModal.value = true;
      } catch (err) {
        error.value = 'Failed to load order details';
        console.error('Error loading order details:', err);
      }
    };

    const editOrder = (order) => {
      if (!checkAuth()) return;
      selectedOrder.value = order;
      editForm.value.status = order.status;
      showEditModal.value = true;
    };

    const updateOrderStatus = async () => {
      if (!checkAuth()) return;
      try {
        updating.value = true;
        await axios.put(`/api/admin/orders/${selectedOrder.value.id}`, {
          status: editForm.value.status
        });
        
        // Update the order in the list
        const index = orders.value.findIndex(o => o.id === selectedOrder.value.id);
        if (index !== -1) {
          orders.value[index].status = editForm.value.status;
        }
        
        closeEditModal();
      } catch (err) {
        error.value = 'Failed to update order status';
        console.error('Error updating order status:', err);
      } finally {
        updating.value = false;
      }
    };

    const closeViewModal = () => {
      showViewModal.value = false;
      selectedOrder.value = null;
    };

    const closeEditModal = () => {
      showEditModal.value = false;
      selectedOrder.value = null;
      editForm.value.status = '';
    };

    onMounted(() => {
      if (checkAuth()) {
        fetchOrders();
      }
    });

    return {
      orders,
      loading,
      error,
      showViewModal,
      showEditModal,
      selectedOrder,
      editForm,
      updating,
      formatDate,
      viewOrder,
      editOrder,
      updateOrderStatus,
      closeViewModal,
      closeEditModal
    };
  }
};
</script>

<style scoped>
.orders-container {
  padding: 20px;
}

.orders-container h2 {
  margin-bottom: 20px;
  color: #333;
}

.orders-table-container {
  background: white;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.orders-table {
  width: 100%;
  border-collapse: collapse;
}

.orders-table th,
.orders-table td {
  padding: 15px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.orders-table th {
  background: #f9f9f9;
  font-weight: 500;
  color: #666;
}

.orders-table tr:hover {
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
  color: #FF7A00;
}

.status.completed {
  background: #e8f5e9;
  color: #4CAF50;
}

.status.processing {
  background: #e3f2fd;
  color: #1976d2;
}

.status.cancelled {
  background: #ffebee;
  color: #f44336;
}

.actions {
  display: flex;
  gap: 8px;
}

.view-btn,
.edit-btn {
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

.view-btn {
  background: #e3f2fd;
  color: #1976d2;
}

.edit-btn {
  background: #e8f5e9;
  color: #4CAF50;
}

.view-btn:hover {
  background: #bbdefb;
}

.edit-btn:hover {
  background: #c8e6c9;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 10px;
  width: 90%;
  max-width: 800px;
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
  font-size: 24px;
  cursor: pointer;
  color: #666;
}

.modal-body {
  padding: 20px;
}

.detail-section {
  margin-bottom: 30px;
}

.detail-section h4 {
  color: #333;
  margin-bottom: 15px;
  border-bottom: 1px solid #eee;
  padding-bottom: 10px;
}

.items-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
}

.items-table th,
.items-table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.items-table th {
  background: #f9f9f9;
  font-weight: 500;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  color: #666;
}

.form-group select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 1rem;
}

.form-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
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
  background: #4CAF50;
  color: white;
}

.cancel-btn:hover {
  background: #e0e0e0;
}

.submit-btn:hover {
  background: #43A047;
}

.submit-btn:disabled {
  background: #a5d6a7;
  cursor: not-allowed;
}

.totals {
  border-top: 1px solid #eee;
  padding-top: 15px;
}

.totals .total {
  font-size: 1.2rem;
  color: #4CAF50;
  margin-top: 10px;
}

.alert {
  padding: 15px;
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
</style>