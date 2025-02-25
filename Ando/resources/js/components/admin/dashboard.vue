<template>
  <div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar">
      <h2>Admin Dashboard</h2>
      <div class="sidebar-links">
        <router-link :to="{ name: 'admin-dashboard' }" active-class="active">
          <i class="fas fa-tachometer-alt"></i> Dashboard
        </router-link>
        <router-link :to="{ name: 'admin-orders' }" active-class="active">
          <i class="fas fa-box"></i> Orders
        </router-link>
        <router-link :to="{ name: 'admin-products' }" active-class="active">
          <i class="fas fa-box-open"></i> Products
        </router-link>
        <router-link :to="{ name: 'admin-customers' }" active-class="active">
          <i class="fas fa-users"></i> Customers
        </router-link>
        <router-link :to="{ name: 'admin-refunds' }" active-class="active">
          <i class="fas fa-undo"></i> Return Refund
        </router-link>
        <a href="#" @click.prevent="logout">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="content">
      <!-- Header -->
      <header>
        <div class="search-bar">
          <i class="fas fa-search"></i>
          <input type="text" v-model="searchQuery" @input="handleSearch" placeholder="Search...">
        </div>
        <div class="icons">
          <div class="notification-icon" @click="toggleNotifications">
            <i class="fas fa-bell"></i>
            <span v-if="unreadNotifications" class="notification-badge">{{ unreadNotifications }}</span>
          </div>
          <div class="user-info" @click="toggleUserMenu">
            <i class="fas fa-user-circle"></i>
            <span>{{ adminName }}</span>
          </div>
        </div>
      </header>

      <!-- Notifications Dropdown -->
      <div v-if="showNotifications" class="notifications-dropdown">
        <div v-if="notifications.length === 0" class="no-notifications">
          No new notifications
        </div>
        <div v-else class="notification-list">
          <div v-for="notification in notifications" :key="notification.id" class="notification-item">
            <i :class="notification.icon"></i>
            <div class="notification-content">
              <p>{{ notification.message }}</p>
              <small>{{ formatDate(notification.created_at) }}</small>
            </div>
          </div>
        </div>
      </div>

      <!-- User Menu Dropdown -->
      <div v-if="showUserMenu" class="user-menu-dropdown">
        <div class="user-menu-item" @click="editProfile">
          <i class="fas fa-user-edit"></i>
          Edit Profile
        </div>
        <div class="user-menu-item" @click="changePassword">
          <i class="fas fa-key"></i>
          Change Password
        </div>
        <div class="user-menu-item" @click="logout">
          <i class="fas fa-sign-out-alt"></i>
          Logout
        </div>
      </div>

      <!-- Main Content Area -->
      <main class="main-content">
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

export default {
  name: 'AdminDashboard',
  
  setup() {
    const router = useRouter();
    const searchQuery = ref('');
    const notifications = ref([]);
    const unreadNotifications = ref(0);
    const showNotifications = ref(false);
    const showUserMenu = ref(false);
    const adminName = ref('Admin');

    // Setup axios interceptor for authentication
    const setupAxiosInterceptor = () => {
      axios.interceptors.request.use(
        (config) => {
          const token = localStorage.getItem('admin_token');
          if (token) {
            config.headers.Authorization = `Bearer ${token}`;
          }
          return config;
        },
        (error) => {
          return Promise.reject(error);
        }
      );

      axios.interceptors.response.use(
        (response) => response,
        (error) => {
          if (error.response && error.response.status === 401) {
            localStorage.removeItem('admin_token');
            router.push('/login');
          }
          return Promise.reject(error);
        }
      );
    };

    // Fetch notifications
    const fetchNotifications = async () => {
      try {
        const response = await axios.get('/api/admin/notifications');
        notifications.value = response.data.notifications;
        unreadNotifications.value = notifications.value.filter(n => !n.read).length;
      } catch (error) {
        console.error('Error fetching notifications:', error);
        if (error.response && error.response.status === 401) {
          router.push('/login');
        }
      }
    };

    // Handle search
    const handleSearch = () => {
      // Implement search functionality
      console.log('Searching for:', searchQuery.value);
    };

    // Toggle notifications dropdown
    const toggleNotifications = () => {
      showNotifications.value = !showNotifications.value;
      showUserMenu.value = false; // Close user menu if open
    };

    // Toggle user menu dropdown
    const toggleUserMenu = () => {
      showUserMenu.value = !showUserMenu.value;
      showNotifications.value = false; // Close notifications if open
    };

    // Format date
    const formatDate = (date) => {
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    };

    // Edit profile
    const editProfile = () => {
      // Implement edit profile functionality
      console.log('Edit profile clicked');
    };

    // Change password
    const changePassword = () => {
      // Implement change password functionality
      console.log('Change password clicked');
    };

    // Logout function
    const logout = async () => {
      try {
        await axios.post('/api/admin/logout');
        localStorage.removeItem('admin_token');
        window.location.href = '/login';
      } catch (error) {
        console.error('Logout error:', error);
      }
    };

    // Click outside to close dropdowns
    const handleClickOutside = (event) => {
      if (!event.target.closest('.notification-icon') && !event.target.closest('.notifications-dropdown')) {
        showNotifications.value = false;
      }
      if (!event.target.closest('.user-info') && !event.target.closest('.user-menu-dropdown')) {
        showUserMenu.value = false;
      }
    };

    onMounted(() => {
      setupAxiosInterceptor();
      const token = localStorage.getItem('admin_token');
      if (!token) {
        router.push('/login');
        return;
      }
      fetchNotifications();
      document.addEventListener('click', handleClickOutside);
    });

    return {
      searchQuery,
      notifications,
      unreadNotifications,
      showNotifications,
      showUserMenu,
      adminName,
      handleSearch,
      toggleNotifications,
      toggleUserMenu,
      formatDate,
      editProfile,
      changePassword,
      logout
    };
  }
};
</script>

<style scoped>
.dashboard {
  display: flex;
  min-height: 100vh;
}

.sidebar {
  width: 250px;
  background-color: #FF7A00;
  color: white;
  padding: 20px;
  box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
  position: fixed;
  height: 100vh;
  overflow-y: auto;
  z-index: 1000;
}

.sidebar h2 {
  color: white;
  text-align: center;
  margin-bottom: 30px;
  font-size: 1.4rem;
}

.sidebar-links {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.sidebar-links a {
  display: flex;
  align-items: center;
  color: white;
  text-decoration: none;
  padding: 12px 15px;
  border-radius: 8px;
  transition: background-color 0.3s;
}

.sidebar-links a:hover,
.sidebar-links a.active {
  background-color: rgba(255, 255, 255, 0.1);
}

.sidebar-links i {
  margin-right: 10px;
  width: 20px;
  text-align: center;
}

.content {
  flex: 1;
  margin-left: 250px;
  background-color: #f5f5f5;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

header {
  background-color: white;
  padding: 15px 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 100;
}

.search-bar {
  display: flex;
  align-items: center;
  background-color: #f5f5f5;
  padding: 8px 15px;
  border-radius: 8px;
  width: 300px;
}

.search-bar input {
  border: none;
  background: none;
  outline: none;
  margin-left: 10px;
  width: 100%;
}

.icons {
  display: flex;
  align-items: center;
  gap: 20px;
}

.notification-icon {
  position: relative;
  cursor: pointer;
}

.notification-badge {
  position: absolute;
  top: -8px;
  right: -8px;
  background-color: #ff4444;
  color: white;
  border-radius: 50%;
  padding: 2px 6px;
  font-size: 0.7rem;
}

.notifications-dropdown {
  position: absolute;
  top: 60px;
  right: 100px;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  width: 300px;
  max-height: 400px;
  overflow-y: auto;
  z-index: 1000;
}

.notification-list {
  padding: 10px;
}

.notification-item {
  display: flex;
  align-items: start;
  padding: 10px;
  border-bottom: 1px solid #eee;
}

.notification-item:last-child {
  border-bottom: none;
}

.notification-item i {
  margin-right: 10px;
  color: #FF7A00;
}

.notification-content {
  flex: 1;
}

.notification-content p {
  margin: 0;
  font-size: 0.9rem;
}

.notification-content small {
  color: #666;
  font-size: 0.8rem;
}

.no-notifications {
  padding: 20px;
  text-align: center;
  color: #666;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.user-menu-dropdown {
  position: absolute;
  top: 60px;
  right: 30px;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  width: 200px;
  z-index: 1000;
}

.user-menu-item {
  padding: 12px 15px;
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.user-menu-item:hover {
  background-color: #f5f5f5;
}

.user-menu-item i {
  width: 20px;
  text-align: center;
  color: #666;
}

.main-content {
  flex: 1;
  padding: 20px;
  overflow-y: auto;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>