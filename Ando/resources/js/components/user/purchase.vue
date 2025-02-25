<template>
  <div class="dashboard-container">
    <header class="header">
      <div class="nav-links">
        <router-link to="/user/home"><b>Home</b></router-link>
        <router-link to="/user/news"><b>New</b></router-link>
        <router-link to="/user/trends"><b>Trends</b></router-link>
        <router-link to="/user/contact"><b>Contact</b></router-link>
      </div>
      <div class="title">
        <span class="four-point-star"></span>
        Fashion Trend
        <span class="four-point-star blue"></span>
      </div>
      <div class="icons">
        <i class="fas fa-search" @click="openSearch"></i>
        <router-link to="/wishlist" class="fas fa-heart">
          <span v-if="wishlistCount" class="badge">{{ wishlistCount }}</span>
        </router-link>
        <router-link to="/purchase" class="fas fa-user"></router-link>
        <router-link to="/user/cart" class="cart-icon">
          <i class="fas fa-shopping-cart"></i>
          <span v-if="cartCount" class="cart-badge">{{ cartCount }}</span>
        </router-link>
        <i class="fas fa-sign-out-alt" @click="logout"></i>
      </div>
    </header>

    <main class="main-content">
      <div class="page-header">
        <h2>My Purchases</h2>
        <button class="back-btn" @click="$router.back()">
          <i class="fas fa-arrow-left"></i> Back
        </button>
      </div>

      <!-- Tab Navigation -->
      <div class="tab-nav">
        <div 
          v-for="tab in tabs" 
          :key="tab.id"
          :class="['tab', { active: currentTab === tab.id }]"
          @click="currentTab = tab.id"
        >
          {{ tab.name }}
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

      <!-- Orders List -->
      <div v-else class="orders-container">
        <div v-if="filteredOrders.length > 0" class="order-list">
          <div v-for="order in filteredOrders" :key="order.id" class="order-item">
            <div class="order-header">
              <span class="order-id">Order #{{ order.id }}</span>
              <span class="order-date">{{ formatDate(order.created_at) }}</span>
              <span class="order-status" :class="getStatusClass(order.status)">
                {{ order.status }}
              </span>
            </div>
            
            <div v-for="item in order.items" :key="item.id" class="product-item">
              <img :src="item.image_url" :alt="item.name" class="product-image" />
              <div class="product-details">
                <h4>{{ item.name }}</h4>
                <p>Variation: {{ item.variation }}</p>
                <p>Quantity: {{ item.quantity }}</p>
                <p>Price: ₱{{ formatPrice(item.price) }}</p>
              </div>
              <div class="item-total">
                <strong>Subtotal:</strong> ₱{{ formatPrice(item.price * item.quantity) }}
              </div>
            </div>

            <div class="order-footer">
              <div class="total-amount">
                <strong>Total Payment:</strong> ₱{{ formatPrice(order.total) }}
              </div>
              <div class="action-buttons">
                <button @click="contactSeller(order.id)" class="action-btn">
                  Contact Seller
                </button>
                <button 
                  v-if="canCancelOrder(order.status)"
                  @click="cancelOrder(order.id)" 
                  class="action-btn danger"
                >
                  Cancel Order
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="empty-state">
          <i class="fas fa-shopping-bag"></i>
          <p>No {{ currentTab.toLowerCase() }} orders found</p>
          <router-link to="/user/trends" class="shop-now-btn">
            Start Shopping
          </router-link>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import axios from 'axios';
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';

export default {
  name: 'PurchaseComponent',
  setup() {
    const router = useRouter();
    const loading = ref(false);
    const error = ref(null);
    const orders = ref([]);
    const currentTab = ref('all');
    const cartCount = ref(0);
    const wishlistCount = ref(0);

    const tabs = [
      { id: 'all', name: 'All' },
      { id: 'to_pay', name: 'To Pay' },
      { id: 'to_ship', name: 'To Ship' },
      { id: 'to_receive', name: 'To Receive' },
      { id: 'completed', name: 'Completed' },
      { id: 'cancelled', name: 'Cancelled' },
      { id: 'return_refund', name: 'Return/Refund' }
    ];

    const filteredOrders = computed(() => {
      if (currentTab.value === 'all') {
        return orders.value;
      }
      return orders.value.filter(order => {
        const status = order.status?.toLowerCase() || '';
        switch (currentTab.value) {
          case 'to_pay':
            return status === 'pending' || status === 'to_pay';
          case 'to_ship':
            return status === 'processing' || status === 'to_ship';
          case 'to_receive':
            return status === 'shipped' || status === 'out_for_delivery';
          case 'completed':
            return status === 'completed' || status === 'delivered';
          case 'cancelled':
            return status === 'cancelled';
          case 'return_refund':
            return status === 'returned' || status === 'refunded';
          default:
            return false;
        }
      });
    });

    const fetchOrders = async () => {
      loading.value = true;
      error.value = null;

      try {
        const token = localStorage.getItem('user_token');
        if (!token) {
          router.push('/login');
          return;
        }

        const headers = {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        };

        const response = await axios.get('/api/user/orders', { headers });
        
        orders.value = Array.isArray(response.data.data) ? response.data.data : [];
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to load orders';
        if (err.response?.status === 401) {
          localStorage.removeItem('user_token');
          router.push('/login');
        }
      } finally {
        loading.value = false;
      }
    };

    const fetchCounts = async () => {
      try {
        const token = localStorage.getItem('user_token');
        if (!token) return;

        const headers = {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        };

        const [cartRes, wishlistRes] = await Promise.all([
          axios.get('/api/user/cart/count', { headers }),
          axios.get('/api/user/wishlist/count', { headers })
        ]);

        cartCount.value = cartRes.data?.count || 0;
        wishlistCount.value = wishlistRes.data?.count || 0;
      } catch (error) {
        console.error('Error fetching counts:', error);
      }
    };

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    };

    const formatPrice = (price) => {
      return Number(price).toFixed(2);
    };

    const getStatusClass = (status) => {
      return status.toLowerCase().replace(/\s+/g, '-');
    };

    const canCancelOrder = (status) => {
      return ['pending', 'to_pay', 'processing'].includes(status.toLowerCase());
    };

    const contactSeller = async (orderId) => {
      console.log('Contacting seller for order:', orderId);
    };

    const cancelOrder = async (orderId) => {
      try {
        const token = localStorage.getItem('user_token');
        if (!token) {
          router.push('/login');
          return;
        }

        const headers = {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json'
        };

        await axios.post(`/api/user/orders/${orderId}/cancel`, {}, { headers });
        await fetchOrders(); 
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to cancel order';
      }
    };

    const logout = async () => {
      try {
        const token = localStorage.getItem('user_token');
        if (token) {
          await axios.post('/api/logout', {}, {
            headers: { 'Authorization': `Bearer ${token}` }
          });
        }
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        localStorage.removeItem('user_token');
        router.push('/login');
      }
    };

    onMounted(() => {
      fetchOrders();
      fetchCounts();
    });

    return {
      loading,
      error,
      orders,
      currentTab,
      tabs,
      cartCount,
      wishlistCount,
      filteredOrders,
      formatDate,
      formatPrice,
      getStatusClass,
      canCancelOrder,
      contactSeller,
      cancelOrder,
      logout,
      fetchOrders
    };
  }
};
</script>

<style scoped>
.dashboard-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  background: #ffffff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.nav-links {
  display: flex;
  gap: 2rem;
}

.nav-links a {
  color: #333;
  text-decoration: none;
  font-weight: 500;
}

.nav-links a:hover {
  color: #d00000;
}

.title {
  font-size: 1.5rem;
  font-weight: bold;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.four-point-star {
  width: 12px;
  height: 12px;
  background: #d00000;
  transform: rotate(45deg);
}

.four-point-star.blue {
  background: #1a73e8;
}

.icons {
  display: flex;
  gap: 1.5rem;
  align-items: center;
}

.icons i, .icons a {
  font-size: 1.2rem;
  color: #333;
  cursor: pointer;
  position: relative;
}

.badge {
  position: absolute;
  top: -8px;
  right: -8px;
  background: #d00000;
  color: white;
  font-size: 0.7rem;
  padding: 2px 6px;
  border-radius: 50%;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.back-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border: none;
  background: #f5f5f5;
  border-radius: 5px;
  cursor: pointer;
}

.back-btn:hover {
  background: #e0e0e0;
}

.tab-nav {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  border-bottom: 1px solid #e0e0e0;
}

.tab {
  padding: 0.75rem 1.5rem;
  cursor: pointer;
  border-bottom: 2px solid transparent;
}

.tab.active {
  border-bottom: 2px solid #d00000;
  color: #d00000;
}

.orders-container {
  background: #ffffff;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.order-item {
  padding: 1.5rem;
  border-bottom: 1px solid #e0e0e0;
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.order-status {
  padding: 0.25rem 0.75rem;
  border-radius: 15px;
  font-size: 0.875rem;
}

.order-status.pending {
  background: #fff3cd;
  color: #856404;
}

.order-status.processing {
  background: #cce5ff;
  color: #004085;
}

.order-status.completed {
  background: #d4edda;
  color: #155724;
}

.order-status.cancelled {
  background: #f8d7da;
  color: #721c24;
}

.product-item {
  display: flex;
  gap: 1.5rem;
  padding: 1rem 0;
  border-bottom: 1px solid #f0f0f0;
}

.product-image {
  width: 100px;
  height: 100px;
  object-fit: cover;
  border-radius: 8px;
}

.product-details {
  flex: 1;
}

.product-details h4 {
  margin: 0 0 0.5rem 0;
  color: #333;
}

.product-details p {
  margin: 0.25rem 0;
  color: #666;
}

.item-total {
  min-width: 150px;
  text-align: right;
}

.order-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 1rem;
  padding-top: 1rem;
}

.action-buttons {
  display: flex;
  gap: 1rem;
}

.action-btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  background: #1a73e8;
  color: white;
}

.action-btn.danger {
  background: #dc3545;
}

.action-btn:hover {
  opacity: 0.9;
}

.loading-state,
.error-state,
.empty-state {
  text-align: center;
  padding: 3rem;
}

.loading-state i,
.error-state i,
.empty-state i {
  font-size: 3rem;
  color: #666;
  margin-bottom: 1rem;
}

.retry-btn,
.shop-now-btn {
  display: inline-block;
  padding: 0.75rem 1.5rem;
  margin-top: 1rem;
  background: #d00000;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  text-decoration: none;
}

.retry-btn:hover,
.shop-now-btn:hover {
  background: #b00000;
}

@media (max-width: 768px) {
  .header {
    flex-direction: column;
    gap: 1rem;
    padding: 1rem;
  }

  .nav-links {
    gap: 1rem;
  }

  .tab-nav {
    flex-wrap: wrap;
  }

  .product-item {
    flex-direction: column;
  }

  .item-total {
    text-align: left;
  }

  .order-footer {
    flex-direction: column;
    gap: 1rem;
  }
}
</style>