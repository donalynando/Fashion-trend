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
        <router-link to="/wishlist" class="fas fa-heart"><span v-if="wishlistCount" class="badge">{{ wishlistCount }}</span></router-link>
        <router-link to="/purchase" class="fas fa-user"></router-link>
        <router-link to="/user/cart" class="cart-icon">
          <i class="fas fa-shopping-cart"></i>
          <span v-if="cartCount" class="cart-badge">{{ cartCount }}</span>
        </router-link>
        <i class="fas fa-sign-out-alt" @click="logout"></i>
      </div>
    </header>

    <main class="main-content">
      <section class="dashboard-welcome">
        <div class="left-column">
          <h2>Welcome Back!</h2>
          <p class="highlight-orange">Your Fashion Journey</p>
          <p class="highlight-blue">Continues Here</p>
        </div>
        <div class="right-column">
          <img src="/img/g1.png" alt="Fashion Dashboard Image" />
        </div>
      </section>

      <section class="quick-actions">
        <h2>Quick Actions</h2>
        <div class="card-container">
          <router-link 
            v-for="action in quickActions" 
            :key="action.id"
            :to="action.link" 
            class="action-card"
          >
            <div class="card-content">
              <i :class="action.icon"></i>
              <div class="description">{{ action.title }}</div>
            </div>
          </router-link>
        </div>
      </section>

      <section class="recent-orders">
        <h2>Recent Orders</h2>
        <div v-if="recentOrders.length > 0" class="card-container">
          <div v-for="order in recentOrders" :key="order.id" class="card">
            <div class="card-content">
              <div class="order-header">
                <span class="order-id">Order #{{ order.id }}</span>
                <span class="order-date">{{ formatDate(order.created_at) }}</span>
              </div>
              <div class="order-details">
                <p class="order-items">{{ order.items_count }} items</p>
                <p class="order-total">Total: ${{ order.total }}</p>
                <p class="order-status" :class="getStatusClass(order.status)">
                  {{ order.status || 'Processing' }}
                </p>
              </div>
              <div class="order-actions">
                <button @click="viewDetails(order.id)" class="action-btn">
                  View Details
                </button>
                <button v-if="canTrackOrder(order.status)" @click="trackOrder(order.id)" class="action-btn">
                  Track Order
                </button>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="empty-state">
          <i class="fas fa-box-open"></i>
          <p>No orders yet? Start your fashion journey today!</p>
          <router-link to="/user/trends" class="shop-now-btn">Shop Now</router-link>
        </div>
      </section>
    </main>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'UserDashboardComponent',
  data() {
    return {
      searchQuery: '',
      user: null,
      cartCount: 0,
      wishlistCount: 0,
      recentOrders: [],
      quickActions: [
        {
          id: 1,
          title: 'Shop Now',
          icon: 'fas fa-shopping-bag',
          link: { name: 'user-trends' }
        },
        {
          id: 2,
          title: 'My Orders',
          icon: 'fas fa-box',
          link: { name: 'user-orders' }
        },
        {
          id: 3,
          title: 'Wishlist',
          icon: 'fas fa-heart',
          link: '/user/wishlist'
        },
        {
          id: 4,
          title: 'Profile',
          icon: 'fas fa-user',
          link: { name: 'edit-profile' }
        },
        {
          id: 5,
          title: 'Settings',
          icon: 'fas fa-cog',
          link: '/user/settings'
        }
      ]
    };
  },
  methods: {
    getStatusClass(status) {
      if (!status) return 'processing';
      return status.toString().toLowerCase().replace(/\s+/g, '-');
    },
    canTrackOrder(status) {
      if (!status) return false;
      const trackableStatuses = ['Shipped', 'Out for Delivery', 'In Transit'];
      return trackableStatuses.includes(status);
    },
    async fetchUserData() {
      try {
        const token = localStorage.getItem('user_token');
        if (!token) {
          this.$router.push('/login');
          return;
        }

        const headers = {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        };

        try {
          const [userResponse, cartResponse, wishlistResponse, ordersResponse] = await Promise.all([
            axios.get('/api/user', { headers }),
            axios.get('/api/user/cart/count', { headers }),
            axios.get('/api/user/wishlist/count', { headers }),
            axios.get('/api/user/orders/recent', { headers })
          ]);

          // Handle user data
          if (userResponse.data) {
            this.user = userResponse.data;
          }

          // Handle cart count
          this.cartCount = cartResponse.data?.count || 0;

          // Handle wishlist count
          this.wishlistCount = wishlistResponse.data?.count || 0;

          // Handle orders with proper error checking
          if (ordersResponse.data?.success && Array.isArray(ordersResponse.data.data)) {
            this.recentOrders = ordersResponse.data.data.map(order => ({
              ...order,
              status: order.status || 'Processing',
              items_count: order.items_count || 0,
              total: order.total || 0
            }));
          } else {
            console.warn('Invalid orders response format:', ordersResponse.data);
            this.recentOrders = [];
          }
        } catch (error) {
          // Handle specific API errors
          if (error.response) {
            if (error.response.status === 401) {
              localStorage.removeItem('user_token');
              this.$router.push('/login');
            } else {
              console.error('API Error:', error.response.data);
            }
          } else if (error.request) {
            console.error('Network Error:', error.request);
          } else {
            console.error('Error:', error.message);
          }
          
          // Reset state on error
          this.recentOrders = [];
          this.cartCount = 0;
          this.wishlistCount = 0;
        }
      } catch (error) {
        console.error('Error in fetchUserData:', error);
        localStorage.removeItem('user_token');
        this.$router.push('/login');
      }
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    },
    viewDetails(orderId) {
      this.$router.push(`/user/orders/${orderId}`);
    },
    trackOrder(orderId) {
      this.$router.push(`/user/orders/${orderId}/track`);
    },
    async logout() {
      try {
        await axios.post('/api/logout');
        localStorage.removeItem('user_token');
        this.$router.push('/login');
      } catch (error) {
        console.error('Logout failed:', error);
      }
    }
  },
  mounted() {
    this.fetchUserData();
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
  padding: 20px 0;
  border-bottom: 1px solid #eee;
}

.nav-links {
  display: flex;
  gap: 20px;
}

.nav-links a {
  color: #333;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s;
}

.nav-links a:hover {
  color: #ff4d4d;
}

.title {
  font-size: 24px;
  font-weight: bold;
  display: flex;
  align-items: center;
  gap: 10px;
}

.four-point-star {
  color: #ff4d4d;
  font-size: 20px;
}

.four-point-star.blue {
  color: #4d79ff;
}

.icons {
  display: flex;
  gap: 20px;
  align-items: center;
}

.icons a, .icons i {
  color: #333;
  font-size: 20px;
  cursor: pointer;
  transition: color 0.3s;
}

.icons a:hover, .icons i:hover {
  color: #ff4d4d;
}

.badge, .cart-badge {
  background: #ff4d4d;
  color: white;
  font-size: 12px;
  padding: 2px 6px;
  border-radius: 10px;
  position: absolute;
  top: -8px;
  right: -8px;
}

.dashboard-welcome {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin: 40px 0;
  padding: 40px;
  background: #f8f9fa;
  border-radius: 15px;
}

.left-column {
  flex: 1;
}

.left-column h2 {
  font-size: 36px;
  margin-bottom: 20px;
  color: #333;
}

.highlight-orange {
  color: #ff4d4d;
  font-size: 24px;
  margin: 10px 0;
}

.highlight-blue {
  color: #4d79ff;
  font-size: 24px;
  margin: 10px 0;
}

.right-column {
  flex: 1;
  display: flex;
  justify-content: center;
}

.right-column img {
  max-width: 100%;
  height: auto;
  border-radius: 15px;
}

.card-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.card {
  background: white;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s;
  overflow: hidden;
}

.card:hover {
  transform: translateY(-5px);
}

.card-content {
  padding: 20px;
}

.action-card {
  text-align: center;
  padding: 20px;
  background: white;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s;
  text-decoration: none;
  color: #333;
}

.action-card:hover {
  transform: translateY(-5px);
}

.action-card i {
  font-size: 24px;
  margin-bottom: 10px;
  color: #ff4d4d;
}

.order-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 15px;
}

.order-id {
  font-weight: bold;
  color: #333;
}

.order-date {
  color: #666;
}

.order-details {
  margin-bottom: 15px;
}

.order-status {
  display: inline-block;
  padding: 5px 10px;
  border-radius: 5px;
  font-size: 14px;
  font-weight: 500;
}

.status-processing { background: #fff3cd; color: #856404; }
.status-shipped { background: #cce5ff; color: #004085; }
.status-delivered { background: #d4edda; color: #155724; }

.action-btn {
  background: #ff4d4d;
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 5px;
  cursor: pointer;
  transition: background 0.3s;
  margin-right: 10px;
}

.action-btn:hover {
  background: #ff3333;
}

.empty-state {
  text-align: center;
  padding: 40px;
}

.empty-state i {
  font-size: 48px;
  color: #ccc;
  margin-bottom: 20px;
}

.shop-now-btn {
  display: inline-block;
  background: #ff4d4d;
  color: white;
  padding: 10px 20px;
  border-radius: 5px;
  text-decoration: none;
  margin-top: 20px;
  transition: background 0.3s;
}

.shop-now-btn:hover {
  background: #ff3333;
}

section {
  margin-bottom: 40px;
}

section h2 {
  font-size: 24px;
  margin-bottom: 20px;
  color: #333;
}
</style>
