<template>
  <div>
    <!-- Header Section -->
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

    <div v-else>
      <!-- Jackets Section -->
      <section v-if="jackets.length > 0" class="jackets-section">
        <h2 class="jackets-title">JACKETS</h2>
        <div class="card-container">
          <div v-for="product in jackets" :key="product.id" class="card">
            <img :src="product.image" :alt="product.name" class="card-image" />
            <p>{{ product.name }}</p>
            <p class="price">₱{{ formatPrice(product.price) }}</p>
            <button @click="addToCart(product.id)" class="add-to-cart-button">
              <b>Add to Cart</b>
            </button>
          </div>
        </div>
      </section>

      <!-- Shirts Section -->
      <section v-if="shirts.length > 0" class="shirts-section">
        <h2 class="shirts-title">SHIRTS / SWEATSHIRTS</h2>
        <div class="card-container">
          <div v-for="product in shirts" :key="product.id" class="card">
            <img :src="product.image" :alt="product.name" class="card-image" />
            <p>{{ product.name }}</p>
            <p class="price">₱{{ formatPrice(product.price) }}</p>
            <button @click="addToCart(product.id)" class="add-to-cart-button">
              <b>Add to Cart</b>
            </button>
          </div>
        </div>
      </section>

      <!-- Additional Sections (Sandals and Pants) -->
      <div v-show="showAdditionalSections" id="additional-sections">
        <!-- Sandals Section -->
        <section v-if="sandals.length > 0" class="sandals-section">
          <h2 class="sandals-title">SANDALS / SHOES</h2>
          <div class="card-container">
            <div v-for="product in sandals" :key="product.id" class="card">
              <img :src="product.image" :alt="product.name" class="card-image" />
              <p>{{ product.name }}</p>
              <p class="price">₱{{ formatPrice(product.price) }}</p>
              <button @click="addToCart(product.id)" class="add-to-cart-button">
                <b>Add to Cart</b>
              </button>
            </div>
          </div>
        </section>

        <!-- Pants Section -->
        <section v-if="pants.length > 0" class="pants-section">
          <h2 class="pants-title">PANTS</h2>
          <div class="card-container">
            <div v-for="product in pants" :key="product.id" class="card">
              <img :src="product.image" :alt="product.name" class="card-image" />
              <p>{{ product.name }}</p>
              <p class="price">₱{{ formatPrice(product.price) }}</p>
              <button @click="addToCart(product.id)" class="add-to-cart-button">
                <b>Add to Cart</b>
              </button>
            </div>
          </div>
        </section>
      </div>

      <!-- See More Button -->
      <div class="see-more-container">
        <button id="see-more-button" class="see-more-button" @click="toggleSections">
          {{ showAdditionalSections ? 'SEE LESS' : 'SEE MORE' }}
        </button>
      </div>

      <!-- Scroll to Top Button -->
      <a href="#" class="scroll-to-top" id="scrollToTop" v-show="showScrollToTop" @click="scrollToTop">
        <i class="fas fa-chevron-up"></i>
      </a>
    </div>

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
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

export default {
  name: 'TrendsComponent',
  setup() {
    const router = useRouter();
    const products = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const showAdditionalSections = ref(false);
    const showScrollToTop = ref(false);
    const cartCount = ref(0);
    const wishlistCount = ref(0);

    // Computed properties for product categories
    const jackets = computed(() => 
      products.value.filter(p => p.price === 599.00)
    );

    const shirts = computed(() => 
      products.value.filter(p => p.price === 49.99)
    );

    const sandals = computed(() => 
      products.value.filter(p => p.price === 299.00)
    );

    const pants = computed(() => 
      products.value.filter(p => p.price === 499.00)
    );

    // Fetch all products
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
        products.value = response.data.data || [];
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

    const toggleSections = () => {
      showAdditionalSections.value = !showAdditionalSections.value;
    };

    const scrollToTop = () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
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

    // Handle scroll events for scroll-to-top button
    const handleScroll = () => {
      showScrollToTop.value = window.scrollY > 500;
    };

    onMounted(() => {
      fetchProducts();
      fetchCounts();
      window.addEventListener('scroll', handleScroll);
    });

    return {
      products,
      loading,
      error,
      showAdditionalSections,
      showScrollToTop,
      cartCount,
      wishlistCount,
      jackets,
      shirts,
      sandals,
      pants,
      addToCart,
      formatPrice,
      toggleSections,
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

      /* Add your styles here */
      html {
        scroll-behavior: smooth;
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

      /* Custom four-point star styles */
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

      /* Responsive styles */
      @media (max-width: 768px) {
        .card-container {
          grid-template-columns: repeat(2, 1fr); /* 2 columns in a row */
        }
      }

      @media (max-width: 480px) {
        .card-container {
          grid-template-columns: 1fr; /* 1 column in a row */
        }
      }

      /* Responsive adjustments */
      @media (max-width: 768px) {
        .promo-section h2 {
          font-size: 2rem;
        }

        .promo-card {
          height: 250px;
        }

        .buy-now-btn {
          font-size: 1rem;
          padding: 10px 25px;
        }
      }

      /* Responsive adjustments */
      @media (max-width: 768px) {
        .contact-container {
          flex-direction: column;
        }
      }

      /* Footer Styling */
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

      /* Divider Styling */
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

      .jackets-section {
    padding: 20px 60px; /* Add some padding */
    background-color: #fff; /* Background color */
}

.jackets-title {
    color: #FF0000; /* Title color */
    font-size: 2rem; /* Title font size */
    margin-bottom: 20px; /* Space below title */
}

.card-container {
    display: grid; /* Use grid layout for the cards */
    grid-template-columns: repeat(5, 1fr); /* 5 columns */
    gap: 20px; /* Space between cards */
}

.card {
 
    border-radius: 8px; /* Rounded corners */
    padding: 10px; /* Padding inside cards */
    text-align: center; /* Center text */
    background-color: #D9D9D9;
}

.card-image {
    width: 100%; /* Make image responsive */
    height: auto; /* Maintain aspect ratio */
}

.price {
    color: #FF0000; /* Price color */
    font-weight: bold; /* Make price bold */
}


.shirts-section {
    padding: 20px 60px; /* Add some padding */
    background-color: #fff; /* Background color */
}

.shirts-title {
    color: #FF0000; /* Title color */
    font-size: 2rem; /* Title font size */
    margin-bottom: 20px; /* Space below title */
}

.see-more-container {
    display: flex;
    justify-content: center;
    margin: 40px 0; /* Spacing around the button */
}

.see-more-button {
    border-color: #FF0000;
    background-color: #ffffff; /* Button background color */
    color: #ff7a00; /* Text color */
    padding: 12px 28px;
    font-size: 1.1rem;
    font-weight: bold;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    border: 2px solid #ff7a00;
}

.see-more-button:hover {
    background-color: #ff7a00;
    color: #ffffff;
}


.add-to-cart-button {
    background-color: #FF0000; /* Button background color */
    color: #fff; /* Text color */
    border: none;
    padding: 8px 16px;
    font-size: 0.9rem;
    font-weight: bold;
    border-radius: 5px;
    cursor: pointer;
    margin-top: 10px; /* Space between price and button */
    transition: background-color 0.3s ease;
}

.add-to-cart-button:hover {
    margin-top: 20px;
    background-color: #d00000; /* Darker shade on hover */
}

.card {
    text-align: center;
}

.sandals-section,
.pants-section {
    padding: 20px 60px; /* Add some padding */
    background-color: #fff; /* Background color */
}

.sandals-title,
.pants-title {
    color: #FF0000; /* Title color */
    font-size: 2rem; /* Title font size */
    margin-bottom: 20px; /* Space below title */
}

.card-container {
    display: grid; /* Use grid layout for the cards */
    grid-template-columns: repeat(5, 1fr); /* 5 columns */
    gap: 20px; /* Space between cards */
}

.card {
    border-radius: 8px; /* Rounded corners */
    padding: 10px; /* Padding inside cards */
    text-align: center; /* Center text */
    background-color: #D9D9D9; /* Card background color */
}

.card-image {
    width: 100%; /* Make image responsive */
    height: auto; /* Maintain aspect ratio */
}

.price {
    color: #FF0000; /* Price color */
    font-weight: bold; /* Make price bold */
}
</style>