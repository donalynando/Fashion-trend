<template>
  <div class="order-details-container">
    <div class="back-button">
      <button @click="$router.go(-1)" class="back-btn">
        <i class="fas fa-arrow-left"></i> Back to Orders
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <i class="fas fa-spinner fa-spin"></i>
      <p>Loading order details...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <i class="fas fa-exclamation-circle"></i>
      <p>{{ error }}</p>
      <button @click="fetchOrderDetails" class="retry-btn">Retry</button>
    </div>

    <!-- Order Details -->
    <div v-else-if="order" class="order-details">
      <div class="order-header">
        <h1>Order #{{ order.order_number }}</h1>
        <div class="order-status" :class="order.status.toLowerCase()">
          {{ order.status }}
        </div>
      </div>

      <div class="order-info">
        <div class="info-section">
          <h3>Order Information</h3>
          <p><strong>Order Date:</strong> {{ formatDate(order.created_at) }}</p>
          <p><strong>Payment Method:</strong> {{ order.payment_method }}</p>
          <p><strong>Shipping Option:</strong> {{ order.shipping_option }}</p>
        </div>

        <div class="info-section">
          <h3>Shipping Address</h3>
          <p>{{ order.shipping_address.name }}</p>
          <p>{{ order.shipping_address.phone }}</p>
          <p>{{ order.shipping_address.street }}</p>
          <p>{{ order.shipping_address.barangay }}, {{ order.shipping_address.city }}</p>
          <p>{{ order.shipping_address.province }}, {{ order.shipping_address.postal_code }}</p>
        </div>
      </div>

      <div class="order-items">
        <h3>Order Items</h3>
        <div v-for="item in order.items" :key="item.id" class="order-item">
          <img :src="item.image_url" :alt="item.name">
          <div class="item-details">
            <h4>{{ item.name }}</h4>
            <p class="item-price">₱{{ formatPrice(item.price) }}</p>
            <p class="item-quantity">Quantity: {{ item.quantity }}</p>
          </div>
          <div class="item-total">
            ₱{{ formatPrice(item.price * item.quantity) }}
          </div>
        </div>
      </div>

      <div class="order-summary">
        <div class="summary-item">
          <span>Subtotal:</span>
          <span>₱{{ formatPrice(order.subtotal) }}</span>
        </div>
        <div class="summary-item">
          <span>Shipping Fee:</span>
          <span>₱{{ formatPrice(order.shipping_fee) }}</span>
        </div>
        <div v-if="order.payment_fee" class="summary-item">
          <span>Payment Fee:</span>
          <span>₱{{ formatPrice(order.payment_fee) }}</span>
        </div>
        <div class="summary-item total">
          <span>Total:</span>
          <span>₱{{ formatPrice(order.total) }}</span>
        </div>
      </div>

      <div class="order-actions">
        <button 
          v-if="order.status === 'Shipped'" 
          @click="trackOrder" 
          class="track-btn"
        >
          Track Order
        </button>
        <button 
          v-if="order.status === 'Delivered'" 
          @click="writeReview" 
          class="review-btn"
        >
          Write Review
        </button>
        <button 
          v-if="canCancelOrder" 
          @click="cancelOrder" 
          class="cancel-btn"
        >
          Cancel Order
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'OrderDetails',
  data() {
    return {
      order: null,
      loading: true,
      error: null
    };
  },
  computed: {
    orderId() {
      return this.$route.params.id;
    },
    canCancelOrder() {
      return this.order && ['pending', 'processing'].includes(this.order.status.toLowerCase());
    }
  },
  methods: {
    async fetchOrderDetails() {
      this.loading = true;
      this.error = null;

      try {
        const token = localStorage.getItem('user_token');
        if (!token) {
          this.$router.push('/login');
          return;
        }

        const response = await axios.get(`/api/user/orders/${this.orderId}`, {
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
          }
        });

        if (response.data?.success && response.data?.data) {
          this.order = response.data.data;
        } else {
          throw new Error('Failed to fetch order details');
        }
      } catch (error) {
        console.error('Error fetching order details:', error);
        this.error = error.response?.data?.message || 'Failed to fetch order details';
      } finally {
        this.loading = false;
      }
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    formatPrice(price) {
      return parseFloat(price).toFixed(2);
    },
    async cancelOrder() {
      try {
        const token = localStorage.getItem('user_token');
        await axios.post(`/api/user/orders/${this.orderId}/cancel`, {}, {
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
          }
        });
        
        // Refresh order details
        await this.fetchOrderDetails();
      } catch (error) {
        console.error('Error cancelling order:', error);
        alert(error.response?.data?.message || 'Failed to cancel order');
      }
    },
    trackOrder() {
      // Implement order tracking functionality
      alert('Order tracking will be implemented soon');
    },
    writeReview() {
      // Implement review writing functionality
      this.$router.push(`/user/orders/${this.orderId}/review`);
    }
  },
  mounted() {
    this.fetchOrderDetails();
  }
};
</script>

<style scoped>
.order-details-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.back-button {
  margin-bottom: 2rem;
}

.back-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border: none;
  background: none;
  color: #666;
  cursor: pointer;
  font-size: 1rem;
}

.back-btn:hover {
  color: #333;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.order-status {
  padding: 0.5rem 1rem;
  border-radius: 4px;
  font-weight: 600;
  text-transform: capitalize;
}

.order-status.pending { background-color: #fff3cd; color: #856404; }
.order-status.processing { background-color: #cce5ff; color: #004085; }
.order-status.shipped { background-color: #d4edda; color: #155724; }
.order-status.delivered { background-color: #d1e7dd; color: #0f5132; }
.order-status.cancelled { background-color: #f8d7da; color: #721c24; }

.order-info {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
}

.info-section {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 8px;
}

.info-section h3 {
  margin-bottom: 1rem;
  color: #333;
}

.info-section p {
  margin: 0.5rem 0;
  color: #666;
}

.order-items {
  margin-bottom: 2rem;
}

.order-item {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 1.5rem;
  padding: 1.5rem;
  background: #fff;
  border: 1px solid #eee;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.order-item img {
  width: 100px;
  height: 100px;
  object-fit: cover;
  border-radius: 4px;
}

.item-details h4 {
  margin: 0 0 0.5rem 0;
  color: #333;
}

.item-price {
  color: #e67e22;
  font-weight: 600;
  margin: 0.25rem 0;
}

.item-quantity {
  color: #666;
}

.item-total {
  font-weight: 600;
  color: #333;
}

.order-summary {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 8px;
  margin-bottom: 2rem;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  margin: 0.5rem 0;
  color: #666;
}

.summary-item.total {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #ddd;
  font-weight: 600;
  color: #333;
  font-size: 1.1rem;
}

.order-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
}

.order-actions button {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 4px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.track-btn {
  background: #3498db;
  color: white;
}

.track-btn:hover {
  background: #2980b9;
}

.review-btn {
  background: #2ecc71;
  color: white;
}

.review-btn:hover {
  background: #27ae60;
}

.cancel-btn {
  background: #e74c3c;
  color: white;
}

.cancel-btn:hover {
  background: #c0392b;
}

.loading-state,
.error-state {
  text-align: center;
  padding: 2rem;
}

.loading-state i,
.error-state i {
  font-size: 2rem;
  margin-bottom: 1rem;
}

.error-state {
  color: #721c24;
}

.retry-btn {
  margin-top: 1rem;
  padding: 0.5rem 1rem;
  background: #dc3545;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.retry-btn:hover {
  background: #c82333;
}

@media (max-width: 768px) {
  .order-details-container {
    padding: 1rem;
  }

  .order-item {
    grid-template-columns: 1fr;
    text-align: center;
  }

  .order-item img {
    margin: 0 auto;
  }

  .order-actions {
    flex-direction: column;
  }

  .order-actions button {
    width: 100%;
  }
}
</style>
