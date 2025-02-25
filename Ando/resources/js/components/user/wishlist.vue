<template>
  <div class="wishlist-container">
    <header class="header">
      <div class="nav-links">
        <router-link :to="{ name: 'home' }"><b>Home</b></router-link>
        <router-link :to="{ name: 'news' }"><b>New</b></router-link>
        <router-link :to="{ name: 'user-trends' }"><b>Trends</b></router-link>
        <router-link :to="{ name: 'contact' }"><b>Contact</b></router-link>
      </div>
      <div class="title">
        <span class="four-point-star"></span>
        Fashion Trend
        <span class="four-point-star blue"></span>
      </div>
      <div class="icons">
        <i class="fas fa-search"></i>
        <router-link :to="{ name: 'user-wishlist' }" class="fas fa-heart"><span v-if="wishlistCount" class="badge">{{ wishlistCount }}</span></router-link>
        <router-link :to="{ name: 'purchase' }" class="fas fa-user"></router-link>
        <router-link :to="{ name: 'user-cart' }" class="fas fa-shopping-cart"><span v-if="cartCount" class="badge">{{ cartCount }}</span></router-link>
        <i class="fas fa-sign-out-alt" @click="logout"></i>
      </div>
    </header>

    <main class="main-content">
      <section class="wishlist-section">
        <h2>My Wishlist</h2>

        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
          <i class="fas fa-spinner fa-spin"></i>
          <p>Loading wishlist...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="error-state">
          <i class="fas fa-exclamation-circle"></i>
          <p>{{ error }}</p>
          <button @click="fetchWishlist" class="action-btn">Retry</button>
        </div>

        <!-- Empty Wishlist State -->
        <div v-else-if="!wishlistItems || wishlistItems.length === 0" class="empty-state">
          <i class="fas fa-heart"></i>
          <h3>Your wishlist is empty</h3>
          <p>Browse our products and add items to your wishlist</p>
          <router-link :to="{ name: 'user-trends' }" class="shop-now-btn">Continue Shopping</router-link>
        </div>

        <!-- Wishlist Items -->
        <div v-else class="wishlist-items">
          <div v-for="item in wishlistItems" :key="item.id" class="wishlist-item">
            <div class="item-image">
              <img 
                :src="item.image_url || '/img/placeholder.png'" 
                :alt="item.name"
                @error="handleImageError"
                class="product-image"
              >
            </div>
            <div class="item-details">
              <h3>{{ item.name }}</h3>
              <p class="item-price">₱{{ formatPrice(item.price) }}</p>
              <p v-if="item.stock <= 0" class="stock-status out-of-stock">Out of Stock</p>
              <p v-else-if="item.stock <= 10" class="stock-status low-stock">Low Stock: {{ item.stock }} left</p>
              <p v-else class="stock-status in-stock">In Stock</p>
            </div>
            <div class="item-actions">
              <button 
                @click="addToCart(item)" 
                class="add-to-cart-btn"
                :disabled="item.stock <= 0"
              >
                <i class="fas fa-shopping-cart"></i>
                Add to Cart
              </button>
              <button @click="removeFromWishlist(item.id)" class="remove-btn">
                <i class="fas fa-trash"></i>
                Remove
              </button>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

export default {
  name: 'UserWishlist',
  
  setup() {
    const router = useRouter();
    const loading = ref(false);
    const error = ref(null);
    const wishlistItems = ref([]);
    const wishlistCount = ref(0);
    const cartCount = ref(0);

    const checkAuth = () => {
      const token = localStorage.getItem('user_token');
      if (!token) {
        router.push('/login');
        return false;
      }
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      return true;
    };

    const formatPrice = (price) => {
      if (!price) return '0.00';
      return parseFloat(price).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    };

    const handleImageError = (event) => {
      event.target.src = '/img/placeholder.png';
    };

    const fetchWishlist = async () => {
      if (!checkAuth()) return;
      
      loading.value = true;
      error.value = null;
      try {
        const response = await axios.get('/api/user/wishlist', {
          headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('user_token')}`
          }
        });
        
        if (response.data?.success) {
          wishlistItems.value = response.data.items || [];
          wishlistCount.value = wishlistItems.value.length;
        } else {
          throw new Error('Invalid response format');
        }
      } catch (err) {
        console.error('Error fetching wishlist:', err);
        if (err.response?.status === 401) {
          localStorage.removeItem('user_token');
          router.push('/login');
        } else {
          error.value = err.response?.data?.message || 'Failed to load wishlist. Please try again.';
        }
        wishlistItems.value = [];
        wishlistCount.value = 0;
      } finally {
        loading.value = false;
      }
    };

    const fetchCartCount = async () => {
      if (!checkAuth()) return;
      
      try {
        const response = await axios.get('/api/user/cart/count', {
          headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('user_token')}`
          }
        });
        cartCount.value = response.data.count || 0;
      } catch (err) {
        console.error('Error fetching cart count:', err);
        if (err.response?.status === 401) {
          localStorage.removeItem('user_token');
          router.push('/login');
        }
      }
    };

    const addToCart = async (item) => {
      if (!checkAuth()) return;
      
      try {
        await axios.post('/api/user/cart', {
          product_id: item.id,
          quantity: 1
        }, {
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('user_token')}`
          }
        });
        await fetchCartCount();
      } catch (err) {
        console.error('Error adding to cart:', err);
        if (err.response?.status === 401) {
          localStorage.removeItem('user_token');
          router.push('/login');
        } else {
          alert('Failed to add item to cart. Please try again.');
        }
      }
    };

    const removeFromWishlist = async (productId) => {
      if (!checkAuth()) return;
      
      try {
        await axios.delete(`/api/user/wishlist/${productId}`, {
          headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('user_token')}`
          }
        });
        await fetchWishlist();
      } catch (err) {
        console.error('Error removing from wishlist:', err);
        if (err.response?.status === 401) {
          localStorage.removeItem('user_token');
          router.push('/login');
        } else {
          alert('Failed to remove item from wishlist. Please try again.');
        }
      }
    };

    const logout = () => {
      localStorage.removeItem('user_token');
      router.push('/login');
    };

    onMounted(() => {
      if (checkAuth()) {
        fetchWishlist();
        fetchCartCount();
      }
    });

    return {
      loading,
      error,
      wishlistItems,
      wishlistCount,
      cartCount,
      addToCart,
      removeFromWishlist,
      logout,
      formatPrice,
      handleImageError
    };
  }
};
</script>

<style scoped>
.wishlist-container {
  min-height: 100vh;
  background-color: #f8f9fa;
}

.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 80px;
  background-color: #f2f2f2;
  padding: 0 60px;
}

.nav-links {
  display: flex;
  gap: 20px;
}

.nav-links a {
  text-decoration: none;
  color: #333;
  font-weight: bold;
  font-size: 1rem;
}

.icons {
  display: flex;
  gap: 20px;
  align-items: center;
}

.icons i, .icons a {
  font-size: 1.2rem;
  color: #333;
  cursor: pointer;
  text-decoration: none;
  position: relative;
}

.badge {
  position: absolute;
  top: -8px;
  right: -8px;
  background-color: #ff0000;
  color: white;
  border-radius: 50%;
  padding: 2px 6px;
  font-size: 0.8rem;
}

.main-content {
  padding: 40px 60px;
}

.wishlist-section {
  max-width: 1200px;
  margin: 0 auto;
}

.wishlist-section h2 {
  font-size: 2rem;
  margin-bottom: 30px;
  color: #333;
}

.loading-state, .error-state, .empty-state {
  text-align: center;
  padding: 40px;
}

.loading-state i, .error-state i, .empty-state i {
  font-size: 3rem;
  color: #666;
  margin-bottom: 20px;
}

.error-state {
  color: #dc3545;
}

.action-btn, .shop-now-btn {
  background-color: #007bff;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 5px;
  cursor: pointer;
  text-decoration: none;
  display: inline-block;
  margin-top: 20px;
}

.wishlist-items {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 2rem;
  padding: 2rem;
}

.wishlist-item {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: transform 0.2s ease;
}

.wishlist-item:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.item-image {
  position: relative;
  width: 100%;
  height: 200px;
  overflow: hidden;
}

.product-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.wishlist-item:hover .product-image {
  transform: scale(1.05);
}

.item-details {
  padding: 1rem;
}

.item-details h3 {
  margin: 0 0 0.5rem;
  font-size: 1.1rem;
  color: #333;
}

.item-price {
  font-size: 1.2rem;
  font-weight: bold;
  color: #e44d26;
  margin: 0.5rem 0;
}

.stock-status {
  font-size: 0.9rem;
  margin: 0.5rem 0;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  display: inline-block;
}

.in-stock {
  color: #2ecc71;
  background: rgba(46, 204, 113, 0.1);
}

.low-stock {
  color: #f1c40f;
  background: rgba(241, 196, 15, 0.1);
}

.out-of-stock {
  color: #e74c3c;
  background: rgba(231, 76, 60, 0.1);
}

.item-actions {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  background: #f8f9fa;
}

.add-to-cart-btn, .remove-btn {
  flex: 1;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: all 0.2s ease;
}

.add-to-cart-btn {
  background: #4CAF50;
  color: white;
}

.add-to-cart-btn:hover:not(:disabled) {
  background: #388E3C;
}

.add-to-cart-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.remove-btn {
  background: #f8f9fa;
  color: #dc3545;
  border: 1px solid #dc3545;
}

.remove-btn:hover {
  background: #dc3545;
  color: white;
}

@media (max-width: 768px) {
  .header {
    padding: 0 20px;
  }

  .main-content {
    padding: 20px;
  }

  .wishlist-item {
    flex-direction: column;
  }

  .item-image {
    width: 100%;
    height: 200px;
    margin-right: 0;
    margin-bottom: 20px;
  }

  .item-actions {
    flex-direction: row;
    margin-top: 20px;
  }
}
</style>
