<template>
  <div class="cart-container">
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
        <router-link :to="{ name: 'user-cart' }" class="fas fa-shopping-cart"><span v-if="cartItems.length" class="badge">{{ cartItems.length }}</span></router-link>
        <i class="fas fa-sign-out-alt" @click="logout"></i>
      </div>
    </header>

    <main class="main-content">
      <section class="cart-section">
        <h2>Shopping Cart</h2>

        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
          <i class="fas fa-spinner fa-spin"></i>
          <p>Loading cart...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="error-state">
          <i class="fas fa-exclamation-circle"></i>
          <p>{{ error }}</p>
          <button @click="fetchCart" class="action-btn">Retry</button>
        </div>

        <!-- Empty Cart State -->
        <div v-else-if="!cartItems || cartItems.length === 0" class="empty-state">
          <i class="fas fa-shopping-cart"></i>
          <h3>Your cart is empty</h3>
          <p>Browse our products and add items to your cart</p>
          <router-link :to="{ name: 'user-trends' }" class="shop-now-btn">Continue Shopping</router-link>
        </div>

        <!-- Cart Items -->
        <div v-else class="cart-content">
          <div class="cart-items">
            <div v-for="item in cartItems" :key="item.id" class="cart-item card">
              <div class="item-image">
                <img :src="item.image_url || '/img/placeholder.png'" :alt="item.name">
              </div>
              <div class="item-details">
                <h3>{{ item.name }}</h3>
                <p class="item-category">{{ item.category }}</p>
                <p class="item-price highlight-orange">${{ formatPrice(item.price) }}</p>
              </div>
              <div class="item-quantity">
                <button 
                  @click="updateQuantity(item.id, item.quantity - 1)"
                  :disabled="item.quantity <= 1"
                  class="quantity-btn"
                >
                  <i class="fas fa-minus"></i>
                </button>
                <span>{{ item.quantity }}</span>
                <button 
                  @click="updateQuantity(item.id, item.quantity + 1)"
                  :disabled="item.quantity >= item.stock"
                  class="quantity-btn"
                >
                  <i class="fas fa-plus"></i>
                </button>
              </div>
              <div class="item-total highlight-blue">
                ${{ formatPrice(item.price * item.quantity) }}
              </div>
              <button @click="removeItem(item.id)" class="remove-btn">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>

          <!-- Cart Summary -->
          <div class="cart-summary card">
            <h3>Order Summary</h3>
            <div class="summary-row">
              <span>Subtotal</span>
              <span>${{ formatPrice(subtotal) }}</span>
            </div>
            <div class="summary-row">
              <span>Shipping</span>
              <span>${{ formatPrice(shippingCost) }}</span>
            </div>
            <div class="summary-row total">
              <span>Total</span>
              <span class="highlight-orange">${{ formatPrice(total) }}</span>
            </div>
            <button @click="checkout" class="checkout-btn">
              Proceed to Checkout
            </button>
            <router-link :to="{ name: 'user-trends' }" class="continue-shopping-btn">
              Continue Shopping
            </router-link>
          </div>
        </div>
      </section>
    </main>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

export default {
  name: 'UserCart',
  
  setup() {
    const router = useRouter();
    const cartItems = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const shippingCost = ref(10.00);

    const subtotal = computed(() => {
      return cartItems.value.reduce((sum, item) => {
        return sum + (item.price * item.quantity);
      }, 0);
    });

    const total = computed(() => {
      return subtotal.value + shippingCost.value;
    });

    const formatPrice = (price) => {
      if (!price || isNaN(price)) return '0.00';
      return Number(price).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    };

    const checkAuth = () => {
      const token = localStorage.getItem('user_token');
      if (!token) {
        router.push('/login');
        return false;
      }
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      return true;
    };

    const fetchCart = async () => {
      if (!checkAuth()) return;
      
      try {
        loading.value = true;
        error.value = null;

        const response = await axios.get('/api/user/cart', {
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          }
        });

        if (response.data?.success && Array.isArray(response.data.items)) {
          cartItems.value = response.data.items.map(item => ({
            ...item,
            price: parseFloat(item.price),
            quantity: parseInt(item.quantity),
            stock: parseInt(item.stock)
          }));
        } else {
          console.error('Invalid cart data structure:', response.data);
          error.value = 'Invalid cart data structure received';
        }
      } catch (err) {
        console.error('Error fetching cart:', err);
        if (err.response?.status === 401) {
          router.push('/login');
        } else {
          error.value = err.response?.data?.message || 'Failed to load cart items. Please try again.';
        }
      } finally {
        loading.value = false;
      }
    };

    const updateQuantity = async (itemId, quantity) => {
      if (!checkAuth()) return;
      
      try {
        loading.value = true;
        error.value = null;

        await axios.patch(`/api/user/cart/${itemId}`, { quantity }, {
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          }
        });

        // Refresh cart after update
        await fetchCart();
      } catch (err) {
        console.error('Error updating cart:', err);
        error.value = err.response?.data?.message || 'Failed to update cart. Please try again.';
      } finally {
        loading.value = false;
      }
    };

    const removeItem = async (itemId) => {
      if (!checkAuth()) return;
      
      try {
        loading.value = true;
        error.value = null;

        await axios.delete(`/api/user/cart/${itemId}`, {
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          }
        });

        // Refresh cart after removal
        await fetchCart();
      } catch (err) {
        console.error('Error removing item:', err);
        error.value = err.response?.data?.message || 'Failed to remove item. Please try again.';
      } finally {
        loading.value = false;
      }
    };

    const checkout = async () => {
      if (!checkAuth()) return;
      
      if (!cartItems.value || cartItems.value.length === 0) {
        error.value = 'Your cart is empty!';
        return;
      }

      try {
        loading.value = true;
        error.value = null;
        
        // Validate cart items by fetching latest cart state
        const cartResponse = await axios.get('/api/user/cart', {
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          }
        });

        if (cartResponse.data?.success && Array.isArray(cartResponse.data.items)) {
          const currentCart = cartResponse.data.items;
          
          // Check for any validation issues
          const itemsWithIssues = currentCart.map(item => {
            const issues = [];
            
            // Check stock
            if (!item.stock || item.stock === 0) {
              issues.push('Out of stock');
            } else if (item.quantity > item.stock) {
              issues.push(`Only ${item.stock} available`);
            }
            
            // Check availability
            if (!item.available) {
              issues.push('No longer available');
            }
            
            return {
              name: item.name,
              issues: issues
            };
          }).filter(item => item.issues.length > 0);

          if (itemsWithIssues.length > 0) {
            error.value = 'The following items have issues:\n' + 
              itemsWithIssues.map(item => 
                `${item.name}: ${item.issues.join(', ')}`
              ).join('\n');
            return;
          }

          // Store cart data in localStorage for checkout
          localStorage.setItem('checkout_data', JSON.stringify({
            items: currentCart.map(item => ({
              ...item,
              price: parseFloat(item.price),
              quantity: parseInt(item.quantity),
              stock: parseInt(item.stock)
            })),
            subtotal: subtotal.value,
            shipping: shippingCost.value,
            total: total.value,
            timestamp: new Date().getTime()
          }));

          router.push('/user/checkout');
        } else {
          error.value = 'Unable to validate cart items. Please try again.';
        }
      } catch (err) {
        console.error('Error proceeding to checkout:', err);
        error.value = err.response?.data?.message || 'Failed to proceed to checkout. Please try again.';
      } finally {
        loading.value = false;
      }
    };

    // Initialize cart data
    onMounted(() => {
      fetchCart();
    });

    return {
      cartItems,
      loading,
      error,
      subtotal,
      total,
      shippingCost,
      formatPrice,
      updateQuantity,
      removeItem,
      checkout,
      fetchCart
    };
  }
};
</script>

<style scoped>
.cart-container {
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

.main-content {
  padding: 40px 0;
}

.cart-section {
  margin-bottom: 40px;
}

.cart-section h2 {
  font-size: 24px;
  margin-bottom: 20px;
  color: #333;
}

.loading-state, .error-state, .empty-state {
  text-align: center;
  padding: 40px;
  background: #f8f9fa;
  border-radius: 15px;
  margin: 20px 0;
}

.loading-state i, .error-state i, .empty-state i {
  font-size: 48px;
  color: #ccc;
  margin-bottom: 20px;
}

.cart-content {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 30px;
}

.cart-items {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.cart-item {
  display: grid;
  grid-template-columns: auto 2fr 1fr 1fr auto;
  align-items: center;
  gap: 20px;
  padding: 20px;
  background: white;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.item-image img {
  width: 100px;
  height: 100px;
  object-fit: cover;
  border-radius: 8px;
}

.item-details h3 {
  font-size: 18px;
  margin-bottom: 8px;
  color: #333;
}

.item-category {
  color: #666;
  font-size: 14px;
  margin-bottom: 8px;
}

.highlight-orange {
  color: #ff4d4d;
  font-weight: bold;
}

.highlight-blue {
  color: #4d79ff;
  font-weight: bold;
}

.item-quantity {
  display: flex;
  align-items: center;
  gap: 10px;
}

.quantity-btn {
  background: #f8f9fa;
  border: none;
  width: 30px;
  height: 30px;
  border-radius: 5px;
  cursor: pointer;
  transition: background 0.3s;
}

.quantity-btn:hover:not(:disabled) {
  background: #e9ecef;
}

.quantity-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.remove-btn {
  background: none;
  border: none;
  color: #ff4d4d;
  cursor: pointer;
  transition: color 0.3s;
}

.remove-btn:hover {
  color: #ff3333;
}

.cart-summary {
  background: white;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  height: fit-content;
}

.cart-summary h3 {
  font-size: 20px;
  margin-bottom: 20px;
  color: #333;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 15px;
  color: #666;
}

.summary-row.total {
  font-size: 18px;
  font-weight: bold;
  color: #333;
  border-top: 1px solid #eee;
  padding-top: 15px;
  margin-top: 15px;
}

.checkout-btn {
  width: 100%;
  background: #ff4d4d;
  color: white;
  border: none;
  padding: 15px;
  border-radius: 5px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.3s;
  margin-bottom: 10px;
}

.checkout-btn:hover {
  background: #ff3333;
}

.continue-shopping-btn {
  display: block;
  text-align: center;
  color: #4d79ff;
  text-decoration: none;
  transition: color 0.3s;
}

.continue-shopping-btn:hover {
  color: #3d61cc;
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

.action-btn {
  background: #ff4d4d;
  color: white;
  border: none;
  padding: 8px 15px;
  border-radius: 5px;
  cursor: pointer;
  transition: background 0.3s;
}

.action-btn:hover {
  background: #ff3333;
}
</style>
