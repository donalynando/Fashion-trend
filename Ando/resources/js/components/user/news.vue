<template>
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
      <router-link to="/user/wishlist" class="fas fa-heart">
        <span v-if="wishlistCount" class="badge">{{ wishlistCount }}</span>
      </router-link>
      <router-link to="/user/purchase" class="fas fa-user"></router-link>
      <router-link to="/user/cart" class="fas fa-shopping-cart">
        <span v-if="cartCount" class="badge">{{ cartCount }}</span>
      </router-link>
      <i class="fas fa-sign-out-alt" @click="logout"></i>
    </div>
  </header>

  <!-- Loading State -->
  <div v-if="loading" class="loading-state">
    <i class="fas fa-spinner fa-spin"></i>
    <p>Loading products...</p>
  </div>

  <!-- Error State -->
  <div v-if="error" class="error-state">
    <i class="fas fa-exclamation-circle"></i>
    <p>{{ error }}</p>
    <button @click="fetchProducts" class="retry-btn">Retry</button>
  </div>

  <section v-else class="new-arrivals">
    <h2>Discover New Arrivals</h2>
    <p>Recently added fashion!</p>
    <div class="card-container">
      <div v-for="product in products" :key="product.id" class="card" @click="viewProduct(product)">
        <img :src="product.image" :alt="product.name" />
        <div class="description">{{ product.name }}</div>
        <div class="price">₱{{ formatPrice(product.price) }}</div>
        <button @click.stop="addToCart(product.id)" class="add-to-cart-btn">
          <i class="fas fa-shopping-cart"></i> Add to Cart
        </button>
      </div>
    </div>
  </section>

  <a href="#" class="scroll-to-top" id="scrollToTop" v-show="showScrollToTop" @click="scrollToTop">
    <i class="fas fa-chevron-up"></i>
  </a>

  <!-- Footer Section -->
  <footer class="footer">
    <div class="footer-content">
      <div class="footer-column">
        <div class="title">
          <span class="four-point-star"></span> <b>Fashion Trend</b>
          <span class="four-point-star blue"></span>
        </div>
        <p>
          Lorem ipsum dolor sit amet consectetur. Dictum ut bibendum vulputate
          et. Lacinia est enim eget gravida eu fames euismod.
        </p>
        <div class="footer-icons">
          <i class="fab fa-twitter"></i>
          <i class="fab fa-facebook"></i>
          <i class="fab fa-instagram"></i>
          <i class="fab fa-whatsapp"></i>
        </div>
      </div>
      <div class="footer-column">
        <h3>Feature</h3>
        <p>About</p>
        <p>Product</p>
        <p>Discover</p>
        <p>Contact</p>
      </div>
      <div class="footer-column">
        <h3>Support</h3>
        <p>Cart</p>
        <p>Log in</p>
        <p>Condition</p>
        <p>Privacy</p>
      </div>
      <div class="footer-column">
        <h3>Categories</h3>
        <p>All Furniture</p>
        <p>Furniture</p>
        <p>Wallpaper</p>
        <p>Additional</p>
      </div>
    </div>

    <hr class="footer-divider" />

    <div class="footer-bottom">
      <p>Copyright 2024 Fashion Trend. All Rights Reserved</p>
    </div>
  </footer>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

export default {
  name: 'NewsComponent',
  setup() {
    const router = useRouter();
    const products = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const showScrollToTop = ref(false);
    const cartCount = ref(0);
    const wishlistCount = ref(0);

    // Fetch products
    const fetchProducts = async () => {
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

        const response = await axios.get('/api/products', { headers });
        // Sort products by creation date to show newest first
        products.value = (response.data.data || []).sort((a, b) => 
          new Date(b.created_at) - new Date(a.created_at)
        );
      } catch (err) {
        error.value = err.response?.data?.message || 'Failed to load products';
        if (err.response?.status === 401) {
          localStorage.removeItem('user_token');
          router.push('/login');
        }
      } finally {
        loading.value = false;
      }
    };

    // Fetch cart and wishlist counts
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

    // Add to cart functionality
    const addToCart = async (productId) => {
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

        await axios.post('/api/user/cart/add', {
          product_id: productId,
          quantity: 1
        }, { headers });

        // Refresh cart count after adding item
        fetchCounts();
      } catch (error) {
        console.error('Error adding to cart:', error);
      }
    };

    const formatPrice = (price) => {
      return Number(price).toFixed(2);
    };

    const viewProduct = (product) => {
      router.push({ name: 'user-trends' });
    };

    const scrollToTop = () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    const handleScroll = () => {
      showScrollToTop.value = window.pageYOffset > 100;
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
      fetchProducts();
      fetchCounts();
      window.addEventListener('scroll', handleScroll);
    });

    onUnmounted(() => {
      window.removeEventListener('scroll', handleScroll);
    });

    return {
      products,
      loading,
      error,
      showScrollToTop,
      cartCount,
      wishlistCount,
      addToCart,
      formatPrice,
      viewProduct,
      scrollToTop,
      logout,
      fetchProducts
    };
  }
};
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: "Poppins", sans-serif;
}

.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 80px;
  background-color: #f2f2f2;
  padding: 0 60px;
}

.header .nav-links {
  display: flex;
  gap: 20px;
}

.header .nav-links a {
  text-decoration: none;
  color: #333;
  font-weight: bold;
  font-size: 1rem;
}

.header .nav-links a.active {
  color: #ff0000; /* Red color for active link */
  font-weight: bold; /* Optional: make it bold */
}

.header .title {
  font-size: 1.5rem;
  font-weight: bold;
  display: flex;
  align-items: center;
  gap: 10px;
}

.header .icons {
  display: flex;
  gap: 15px;
}

.header .icons i {
  font-size: 1.2rem;
  cursor: pointer;
  color: #333;
}

.four-point-star {
  width: 25px;
  height: 25px;
  background-color: #ff7a00;
  clip-path: polygon(
    50% 0%,
    65% 35%,
    100% 50%,
    65% 65%,
    50% 100%,
    35% 65%,
    0% 50%,
    35% 35%
  );
  display: inline-block;
}

.four-point-star.blue {
  background-color: #0066ff;
}

.new-arrivals {
  padding: 60px 60px;
  background-color: #ffffff;
  text-align: center;
}

.new-arrivals h2 {
  font-size: 2.5rem;
  color: #333;
  margin-bottom: 20px;
}

.new-arrivals p {
  font-size: 1.2rem;
  color: #666;
  margin-bottom: 40px;
}

.card-container {
  display: grid;
  grid-template-columns: repeat(3, 1fr); /* 3 columns in a row */
  gap: 20px;
}

.card {
  background-color: #ffffff;
  border-radius: 10px;
  display: block; /* Make the entire card clickable */
  text-decoration: none; /* Remove underline from links */
  color: inherit; /* Inherit text color from parent */
  transition: transform 0.2s; /* Add a hover effect */
  text-align: center;
  padding: 20px;
}

.card:hover {
  transform: scale(1.05); /* Scale up the card on hover */
}

.card img {
  width: 100%;
  height: auto;
}

.card .description {
  margin: 15px 0;
  font-size: 1rem;
  color: #333;
}

.card .price {
  font-size: 1.2rem;
  color: #ff0000;
  font-weight: bold;
}

.loading-state,
.error-state {
  text-align: center;
  padding: 2rem;
  margin: 2rem auto;
  max-width: 600px;
}

.loading-state i,
.error-state i {
  font-size: 2rem;
  margin-bottom: 1rem;
}

.error-state {
  color: #dc3545;
}

.retry-btn {
  background-color: #007bff;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  margin-top: 1rem;
}

.retry-btn:hover {
  background-color: #0056b3;
}

.badge {
  position: absolute;
  top: -8px;
  right: -8px;
  background-color: #dc3545;
  color: white;
  border-radius: 50%;
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
}

.add-to-cart-btn {
  background-color: #007bff;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  width: 100%;
  margin-top: 0.5rem;
  transition: background-color 0.2s;
}

.add-to-cart-btn:hover {
  background-color: #0056b3;
}

.card {
  cursor: pointer;
  transition: transform 0.2s;
  padding: 1rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.card:hover {
  transform: translateY(-5px);
}

.card img {
  width: 100%;
  height: auto;
  border-radius: 4px;
}

.card .description {
  margin: 0.5rem 0;
  font-weight: 500;
}

.card .price {
  color: #dc3545;
  font-weight: bold;
  margin-bottom: 0.5rem;
}

.footer {
  background-color: #eef1c4;
  padding: 40px 20px;
}

.footer-content {
  display: flex;
  justify-content: center;
  text-align: center;
  gap: 40px;
  max-width: 1200px;
  margin: 0 auto;
  flex-wrap: wrap;
}

.footer-column {
  flex: 1;
  min-width: 200px;
}

.footer-icons {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-top: 10px;
}

.footer-bottom {
  text-align: center;
  margin-top: 20px;
  font-size: 0.9rem;
  color: #333;
}

.footer-divider {
  width: 80%;
  margin: 20px auto;
  border: none;
  border-top: 1px solid #ccc;
}

.scroll-to-top {
  position: fixed; /* Fixes the button to the viewport */
  bottom: 20px; /* Space from the bottom */
  right: 20px; /* Space from the right */
  background-color: #ff0000; /* Button background color */
  color: #fff; /* Text color */
  border: none; /* No border */
  border-radius: 50%; /* Circular button */
  padding: 10px; /* Padding inside the button */
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3); /* Optional: shadow for depth */
  display: none; /* Initially hidden */
  text-align: center; /* Center the icon */
  font-size: 18px; /* Font size */
  z-index: 1000; /* Ensure it's above other elements */
}

.scroll-to-top:hover {
  background-color: #d00000; /* Darker shade on hover */
}
</style>