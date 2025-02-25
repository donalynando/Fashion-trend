<template>
  <div class="dashboard-home">
    <!-- Statistics Cards -->
    <div class="statistics-grid">
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-content">
          <h3>Total Orders</h3>
          <p class="stat-number">{{ statistics.totalOrders }}</p>
          <p :class="['stat-change', statistics.changes.orders >= 0 ? 'positive' : 'negative']">
            <i :class="['fas', statistics.changes.orders >= 0 ? 'fa-arrow-up' : 'fa-arrow-down']"></i>
            {{ Math.abs(statistics.changes.orders) }}% from last month
          </p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
          <h3>Total Customers</h3>
          <p class="stat-number">{{ statistics.totalCustomers }}</p>
          <p :class="['stat-change', statistics.changes.customers >= 0 ? 'positive' : 'negative']">
            <i :class="['fas', statistics.changes.customers >= 0 ? 'fa-arrow-up' : 'fa-arrow-down']"></i>
            {{ Math.abs(statistics.changes.customers) }}% from last month
          </p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-content">
          <h3>Total Revenue</h3>
          <p class="stat-number">${{ statistics.totalRevenue }}</p>
          <p :class="['stat-change', statistics.changes.revenue >= 0 ? 'positive' : 'negative']">
            <i :class="['fas', statistics.changes.revenue >= 0 ? 'fa-arrow-up' : 'fa-arrow-down']"></i>
            {{ Math.abs(statistics.changes.revenue) }}% from last month
          </p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-box"></i>
        </div>
        <div class="stat-content">
          <h3>Total Products</h3>
          <p class="stat-number">{{ statistics.totalProducts }}</p>
          <p :class="['stat-change', statistics.changes.products >= 0 ? 'positive' : 'negative']">
            <i :class="['fas', statistics.changes.products >= 0 ? 'fa-arrow-up' : 'fa-arrow-down']"></i>
            {{ Math.abs(statistics.changes.products) }}% from last month
          </p>
        </div>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="recent-orders">
      <div class="section-header">
        <h2>Recent Orders</h2>
        <router-link :to="{ name: 'admin-orders' }" class="view-all">
          View All <i class="fas fa-arrow-right"></i>
        </router-link>
      </div>
      
      <div class="orders-table-container">
        <div v-if="loading" class="loading-state">
          Loading...
        </div>
        <div v-else-if="error" class="error-state">
          {{ error }}
        </div>
        <table v-else class="orders-table">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Customer</th>
              <th>Product</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="order in recentOrders" :key="order.id">
              <td>#{{ order.id }}</td>
              <td>{{ order.customer }}</td>
              <td>{{ order.product }}</td>
              <td>${{ order.amount }}</td>
              <td>
                <span :class="['status', order.status.toLowerCase()]">
                  {{ order.status }}
                </span>
              </td>
              <td>{{ formatDate(order.date) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

export default {
  name: 'AdminHome',
  
  setup() {
    const router = useRouter();
    const statistics = ref({
      totalOrders: 0,
      totalCustomers: 0,
      totalRevenue: '0.00',
      totalProducts: 0,
      changes: {
        orders: 0,
        customers: 0,
        revenue: 0,
        products: 0
      }
    });
    
    const recentOrders = ref([]);
    const loading = ref(true);
    const error = ref(null);

    const fetchDashboardData = async () => {
      try {
        loading.value = true;
        error.value = null;
        
        const token = localStorage.getItem('admin_token');
        if (!token) {
          router.push('/login');
          return;
        }

        const response = await axios.get('/api/admin/dashboard', {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        statistics.value = response.data.statistics;
        recentOrders.value = response.data.recentOrders;
      } catch (err) {
        error.value = 'Failed to load dashboard data: ' + (err.response?.data?.error || err.message);
        console.error('Dashboard data error:', err);
        if (err.response?.status === 401) {
          localStorage.removeItem('admin_token');
          router.push('/login');
        }
      } finally {
        loading.value = false;
      }
    };

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    };

    onMounted(() => {
      fetchDashboardData();
    });

    return {
      statistics,
      recentOrders,
      loading,
      error,
      formatDate
    };
  }
};
</script>

<style scoped>
.dashboard-home {
  padding: 20px;
}

.statistics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  border-radius: 10px;
  padding: 20px;
  display: flex;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  width: 50px;
  height: 50px;
  background: #FF7A00;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
}

.stat-icon i {
  color: white;
  font-size: 1.5rem;
}

.stat-content {
  flex: 1;
}

.stat-content h3 {
  margin: 0;
  font-size: 0.9rem;
  color: #666;
}

.stat-number {
  margin: 5px 0;
  font-size: 1.5rem;
  font-weight: bold;
  color: #333;
}

.stat-change {
  margin: 0;
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  gap: 5px;
}

.stat-change.positive {
  color: #4CAF50;
}

.stat-change.negative {
  color: #f44336;
}

.recent-orders {
  background: white;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.section-header h2 {
  margin: 0;
  font-size: 1.2rem;
  color: #333;
}

.view-all {
  color: #FF7A00;
  text-decoration: none;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 5px;
}

.view-all i {
  font-size: 0.8rem;
}

.orders-table-container {
  overflow-x: auto;
}

.orders-table {
  width: 100%;
  border-collapse: collapse;
}

.orders-table th,
.orders-table td {
  padding: 12px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.orders-table th {
  font-weight: 600;
  color: #666;
  font-size: 0.9rem;
}

.orders-table td {
  color: #333;
  font-size: 0.9rem;
}

.status {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 500;
}

.status.pending {
  background: #FFF3E0;
  color: #FF9800;
}

.status.processing {
  background: #E3F2FD;
  color: #2196F3;
}

.status.completed {
  background: #E8F5E9;
  color: #4CAF50;
}

.status.cancelled {
  background: #FFEBEE;
  color: #f44336;
}

.loading-state {
  text-align: center;
  padding: 20px;
  color: #666;
}

.error-state {
  text-align: center;
  padding: 20px;
  color: #f44336;
}

@media (max-width: 768px) {
  .statistics-grid {
    grid-template-columns: 1fr;
  }
  
  .orders-table th,
  .orders-table td {
    padding: 8px;
  }
}
</style>
