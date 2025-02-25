<template>
  <div class="orders-container">
    <h1>My Orders</h1>

    <!-- Order Filters -->
    <div class="filters">
      <div class="search-filter">
        <input 
          type="text" 
          v-model="searchQuery" 
          placeholder="Search orders..."
          @input="filterOrders"
        >
        <i class="fas fa-search"></i>
      </div>
      <div class="status-filter">
        <select v-model="statusFilter" @change="filterOrders">
          <option value="">All Status</option>
          <option value="pending">Pending</option>
          <option value="processing">Processing</option>
          <option value="shipped">Shipped</option>
          <option value="delivered">Delivered</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
      <div class="date-filter">
        <select v-model="dateFilter" @change="filterOrders">
          <option value="">All Time</option>
          <option value="last_week">Last Week</option>
          <option value="last_month">Last Month</option>
          <option value="last_3_months">Last 3 Months</option>
          <option value="last_6_months">Last 6 Months</option>
          <option value="last_year">Last Year</option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <i class="fas fa-spinner fa-spin"></i>
      <p>Loading orders...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <i class="fas fa-exclamation-circle"></i>
      <p>{{ error }}</p>
      <button @click="fetchOrders" class="retry-btn">Retry</button>
    </div>

    <!-- No Orders State -->
    <div v-else-if="!loading && orders.length === 0" class="no-orders">
      <i class="fas fa-box-open"></i>
      <p>No orders found</p>
      <router-link to="/user/home" class="shop-now-btn">Shop Now</router-link>
    </div>

    <!-- Orders List -->
    <div v-else class="orders-list">
      <div v-for="order in orders" :key="order.id" class="order-card">
        <div class="order-header">
          <div class="order-info">
            <h3>Order #{{ order.id }}</h3>
            <span class="order-date">{{ formatDate(order.created_at) }}</span>
          </div>
          <div class="order-status" :class="order.status.toLowerCase()">
            {{ order.status }}
          </div>
        </div>

        <div class="order-items">
          <div v-for="item in order.items" :key="item.id" class="order-item">
            <img :src="item.image_url" :alt="item.name">
            <div class="item-details">
              <h4>{{ item.name }}</h4>
              <p class="item-price">₱{{ formatPrice(item.price) }}</p>
              <p class="item-quantity">Quantity: {{ item.quantity }}</p>
            </div>
          </div>
        </div>

        <div class="order-summary">
          <div class="summary-item">
            <span>Subtotal:</span>
            <span>₱{{ formatPrice(order.subtotal) }}</span>
          </div>
          <div class="summary-item">
            <span>Shipping:</span>
            <span>₱{{ formatPrice(order.shipping_fee) }}</span>
          </div>
          <div class="summary-item total">
            <span>Total:</span>
            <span>₱{{ formatPrice(order.total) }}</span>
          </div>
        </div>

        <div class="order-actions">
          <button 
            v-if="order.status === 'Shipped'" 
            @click="trackOrder(order.id)" 
            class="track-btn"
          >
            Track Order
          </button>
          <button 
            v-if="order.status === 'Delivered'" 
            @click="reviewOrder(order.id)" 
            class="review-btn"
          >
            Write Review
          </button>
          <button 
            v-if="canCancelOrder(order)" 
            @click="cancelOrder(order.id)" 
            class="cancel-btn"
          >
            Cancel Order
          </button>
          <button 
            @click="viewOrderDetails(order.id)" 
            class="details-btn"
          >
            View Details
          </button>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="pagination">
      <button 
        :disabled="currentPage === 1" 
        @click="changePage(currentPage - 1)"
        class="page-btn"
      >
        Previous
      </button>
      <span class="page-info">Page {{ currentPage }} of {{ totalPages }}</span>
      <button 
        :disabled="currentPage === totalPages" 
        @click="changePage(currentPage + 1)"
        class="page-btn"
      >
        Next
      </button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'UserOrders',
  data() {
    return {
      orders: [],
      loading: true,
      error: null,
      searchQuery: '',
      statusFilter: '',
      dateFilter: '',
      currentPage: 1,
      totalPages: 1,
      itemsPerPage: 10
    };
  },
  methods: {
    async fetchOrders() {
      this.loading = true;
      this.error = null;
      
      try {
        const token = localStorage.getItem('user_token');
        if (!token) {
          this.$router.push('/login');
          return;
        }

        const response = await axios.get('/api/user/orders/recent', {
          headers: { 
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}` 
          },
          params: {
            page: this.currentPage,
            per_page: this.itemsPerPage,
            status: this.statusFilter.toLowerCase(),
            date_filter: this.dateFilter,
            search: this.searchQuery
          }
        });

        // Ensure response.data has the expected structure
        if (response.data?.success && response.data?.data) {
          this.orders = response.data.data;
          this.totalPages = Math.ceil(response.data.total / response.data.per_page);
          this.currentPage = response.data.current_page;
        } else {
          this.orders = [];
          this.totalPages = 1;
        }
      } catch (error) {
        console.error('Error fetching orders:', error);
        this.error = 'Failed to load orders. Please try again.';
        if (error.response?.status === 401) {
          localStorage.removeItem('user_token');
          this.$router.push('/login');
        }
      } finally {
        this.loading = false;
      }
    },
    filterOrders() {
      this.currentPage = 1;
      this.fetchOrders();
    },
    formatDate(dateString) {
      if (!dateString) return '';
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    formatPrice(price) {
      if (!price) return '0.00';
      return Number(price).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    },
    canCancelOrder(order) {
      return order && ['pending', 'processing'].includes(order.status.toLowerCase());
    },
    async cancelOrder(orderId) {
      try {
        const token = localStorage.getItem('user_token');
        await axios.post(`/api/user/orders/${orderId}/cancel`, {}, {
          headers: { Authorization: `Bearer ${token}` }
        });
        await this.fetchOrders();
      } catch (error) {
        console.error('Error cancelling order:', error);
        alert('Failed to cancel order. Please try again.');
      }
    },
    viewOrderDetails(orderId) {
      this.$router.push(`/user/orders/${orderId}`);
    },
    trackOrder(orderId) {
      this.$router.push(`/user/orders/${orderId}/track`);
    },
    reviewOrder(orderId) {
      this.$router.push(`/user/orders/${orderId}/review`);
    },
    async changePage(page) {
      this.currentPage = page;
      await this.fetchOrders();
      window.scrollTo(0, 0);
    }
  },
  mounted() {
    this.fetchOrders();
  },
  watch: {
    '$route.query': {
      handler(query) {
        this.statusFilter = query.status || '';
        this.dateFilter = query.date_filter || '';
        this.searchQuery = query.search || '';
        this.currentPage = parseInt(query.page) || 1;
        this.fetchOrders();
      },
      immediate: true
    }
  }
};
</script>

<style scoped>
.orders-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

h1 {
  margin-bottom: 2rem;
  color: #333;
}

.filters {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  flex-wrap: wrap;
}

.search-filter {
  position: relative;
  flex: 1;
  min-width: 200px;
}

.search-filter input {
  width: 100%;
  padding: 0.75rem 2.5rem 0.75rem 1rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 1rem;
}

.search-filter i {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #666;
}

.status-filter,
.date-filter {
  min-width: 150px;
}

select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 1rem;
  background-color: white;
  cursor: pointer;
}

.loading-state,
.error-state,
.no-orders {
  text-align: center;
  padding: 3rem;
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.loading-state i,
.error-state i,
.no-orders i {
  font-size: 3rem;
  margin-bottom: 1rem;
  color: #666;
}

.error-state {
  color: #d32f2f;
}

.retry-btn,
.shop-now-btn {
  display: inline-block;
  margin-top: 1rem;
  padding: 0.75rem 1.5rem;
  background-color: #2196f3;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  text-decoration: none;
  transition: background-color 0.3s;
}

.retry-btn:hover,
.shop-now-btn:hover {
  background-color: #1976d2;
}

.order-card {
  background-color: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.order-info h3 {
  margin: 0;
  color: #333;
}

.order-date {
  color: #666;
  font-size: 0.9rem;
}

.order-status {
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: 500;
  text-transform: capitalize;
}

.order-status.pending {
  background-color: #fff3e0;
  color: #f57c00;
}

.order-status.processing {
  background-color: #e3f2fd;
  color: #1976d2;
}

.order-status.shipped {
  background-color: #e8f5e9;
  color: #388e3c;
}

.order-status.delivered {
  background-color: #e8f5e9;
  color: #388e3c;
}

.order-status.cancelled {
  background-color: #ffebee;
  color: #d32f2f;
}

.order-items {
  border-top: 1px solid #eee;
  border-bottom: 1px solid #eee;
  padding: 1.5rem 0;
  margin-bottom: 1.5rem;
}

.order-item {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
}

.order-item:last-child {
  margin-bottom: 0;
}

.order-item img {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: 8px;
}

.item-details h4 {
  margin: 0 0 0.5rem 0;
  color: #333;
}

.item-price {
  color: #2196f3;
  font-weight: 500;
  margin: 0 0 0.25rem 0;
}

.item-quantity {
  color: #666;
  margin: 0;
}

.order-summary {
  margin-bottom: 1.5rem;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  color: #666;
}

.summary-item.total {
  color: #333;
  font-weight: 600;
  font-size: 1.1rem;
  border-top: 1px solid #eee;
  padding-top: 0.5rem;
}

.order-actions {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.track-btn,
.review-btn,
.cancel-btn,
.details-btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: background-color 0.3s;
}

.track-btn {
  background-color: #2196f3;
  color: white;
}

.review-btn {
  background-color: #4caf50;
  color: white;
}

.cancel-btn {
  background-color: #f44336;
  color: white;
}

.details-btn {
  background-color: #e3f2fd;
  color: #1976d2;
}

.track-btn:hover {
  background-color: #1976d2;
}

.review-btn:hover {
  background-color: #388e3c;
}

.cancel-btn:hover {
  background-color: #d32f2f;
}

.details-btn:hover {
  background-color: #bbdefb;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
}

.page-btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 8px;
  background-color: #e3f2fd;
  color: #1976d2;
  cursor: pointer;
  transition: background-color 0.3s;
}

.page-btn:disabled {
  background-color: #f5f5f5;
  color: #999;
  cursor: not-allowed;
}

.page-btn:not(:disabled):hover {
  background-color: #bbdefb;
}

.page-info {
  color: #666;
}

@media (max-width: 768px) {
  .orders-container {
    padding: 1rem;
  }

  .filters {
    flex-direction: column;
  }

  .search-filter {
    min-width: 100%;
  }

  .status-filter,
  .date-filter {
    min-width: 100%;
  }

  .order-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .order-item {
    flex-direction: column;
  }

  .order-item img {
    width: 100%;
    height: 200px;
  }

  .order-actions {
    flex-direction: column;
  }

  .track-btn,
  .review-btn,
  .cancel-btn,
  .details-btn {
    width: 100%;
  }
}
</style>