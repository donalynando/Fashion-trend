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
      <router-link to="/user/wishlist" class="fas fa-heart"><span v-if="wishlistCount" class="badge">{{ wishlistCount }}</span></router-link>
      <router-link to="/user/purchase" class="fas fa-user"></router-link>
      <router-link to="/user/cart" class="fas fa-shopping-cart"><span v-if="cartCount" class="badge">{{ cartCount }}</span></router-link>
      <i class="fas fa-sign-out-alt" @click="logout"></i>
    </div>
  </header>

  <section class="hero">
    <div class="left-column">
      <p>Stay Ahead of the Curve: Discover the Latest in Fashion!</p>
      <h2>Fashion never ends.</h2>
      <p class="highlight-orange">It's your look.</p>
      <p class="highlight-blue">It's your style.</p>
      <router-link to="/user/trends" class="shop-now-btn">Shop now</router-link>
    </div>
    <div class="right-column">
      <img src="/img/g1.png" alt="Fashion Trend Image" />
    </div>
  </section>

  <section class="new-arrivals">
    <h2>Discover New Arrivals</h2>
    <p>Recently added fashion!</p>
    
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

    <!-- Products Grid -->
    <div v-else-if="newArrivals.length > 0" class="card-container">
      <router-link 
        v-for="product in newArrivals" 
        :key="product.id"
        :to="'/product/' + product.id" 
        class="card"
      >
        <div class="card-image">
          <img 
            :src="product.image_url || '/img/placeholder.png'" 
            :alt="product.name"
            @error="handleImageError"
            class="product-img"
          />
          <div class="card-actions">
            <button @click.prevent="addToWishlist(product.id)" class="action-btn">
              <i class="fas fa-heart"></i>
            </button>
            <button @click.prevent="addToCart(product.id)" class="action-btn">
              <i class="fas fa-shopping-cart"></i>
            </button>
          </div>
        </div>
        <div class="card-content">
          <div class="description">{{ product.name }}</div>
          <div class="category">{{ product.category }}</div>
          <div class="price">₱{{ formatPrice(product.price) }}</div>
        </div>
      </router-link>
    </div>

    <!-- Empty State -->
    <div v-else class="empty-state">
      <i class="fas fa-box-open"></i>
      <p>No new arrivals available at the moment.</p>
    </div>
  </section>

  <section class="promo-section">
    <h2>Flat<span class="highlight-red"> 50% </span>Off</h2>
    <p>Valid till 31st only</p>
    <div class="promo-card">
      <div class="promo-content"></div>
    </div>
    <router-link to="/sale" class="buy-now-btn">Buy Now</router-link>
  </section>

  <section class="trending-outfit">
    <h2>Trending Outfit of The Week</h2>
    <p>
      From runway to real life: Fashion trends you need to know. Stay ahead of
      the curve with these trendsetting outfits.
    </p>
    <div v-if="loading" class="loading-state">
      <i class="fas fa-spinner fa-spin"></i>
      <p>Loading trending items...</p>
    </div>
    <div v-else-if="trendingItems.length > 0" class="outfit-container">
      <div class="left-image">
        <img 
          :src="trendingItems[0]?.image_url || '/img/placeholder.png'" 
          :alt="trendingItems[0]?.name"
          @error="handleImageError"
          class="trending-img"
        />
      </div>
      <div class="right-images">
        <img 
          v-for="(item, index) in trendingItems.slice(1, 3)" 
          :key="item.id"
          :src="item.image_url || '/img/placeholder.png'" 
          :alt="item.name"
          @error="handleImageError"
          class="trending-img"
        />
      </div>
    </div>
    <router-link to="/user/trends" class="see-more-btn">See More Trending</router-link>
  </section>

  <section class="contact-us" >
    <div class="contact-container">
      <div class="contact-left">
        <h2><span class="highlight-red">CONTACT US!</span></h2>
        <p>
          Need to get in touch with us? Either fill out the form with your
          inquiry or message us on <b> fashiontrend@email.com </b>
        </p>
        <div class="info-box">
          <img src="/img/11.png" alt="Information Image" />
        </div>
      </div>
      <div class="contact-right">
        <form style="margin-top: 30px">
          <div class="name-fields">
            <input
              type="text"
              style="margin-bottom: 30px"
              placeholder="First name"
              required
            />
            <input
              type="text"
              style="margin-bottom: 30px"
              placeholder="Last name"
              required
            />
          </div>
          <input
            type="email"
            style="margin-bottom: 30px"
            placeholder="Email address"
            required
          />
          <textarea
            style="margin-bottom: 30px"
            placeholder="What we can help you with?"
            required
          ></textarea>
          <button type="submit" class="submit-btn">Submit</button>
        </form>
      </div>
    </div>
  </section>
 
  <a href="#" class="scroll-to-top" id="scrollToTop">
    <i class="fas fa-chevron-up"></i>
    <!-- Font Awesome Up Arrow Icon -->
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

    <!-- Divider -->
    <hr class="footer-divider" />

    <div class="footer-bottom">
      <p>Copyright 2024 Fashion Trend. All Rights Reserved</p>
    </div>
  </footer>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

export default {
  name: 'UserHome',
  
  setup() {
    const router = useRouter();
    const newArrivals = ref([]);
    const trendingItems = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const cartCount = ref(0);
    const wishlistCount = ref(0);

    const formatPrice = (price) => {
      if (!price || isNaN(price)) return '0.00';
      const numPrice = parseFloat(price);
      return numPrice.toLocaleString('en-US', {
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

    const fetchProducts = async () => {
      if (!checkAuth()) return;
      
      try {
        loading.value = true;
        error.value = null;

        // Fetch products from the user endpoint
        const response = await axios.get('/api/user/products');
        const allProducts = response.data || [];

        // Sort products by date to get new arrivals (latest 6 products)
        newArrivals.value = allProducts
          .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
          .slice(0, 6);

        // Get trending items (products with low stock or featured status)
        trendingItems.value = allProducts
          .filter(product => product.stock < 50)
          .slice(0, 3);

        // Fetch cart count
        try {
          const cartResponse = await axios.get('/api/user/cart/count');
          cartCount.value = parseInt(cartResponse.data.count) || 0;
        } catch (err) {
          console.error('Error fetching cart count:', err);
          cartCount.value = 0;
        }

        // Fetch wishlist count
        try {
          const wishlistResponse = await axios.get('/api/user/wishlist/count');
          wishlistCount.value = parseInt(wishlistResponse.data.count) || 0;
        } catch (err) {
          console.error('Error fetching wishlist count:', err);
          wishlistCount.value = 0;
        }

      } catch (err) {
        console.error('Error fetching products:', err);
        if (err.response?.status === 401) {
          router.push('/login');
        } else {
          error.value = 'Failed to load products. Please try again.';
        }
      } finally {
        loading.value = false;
      }
    };

    const addToCart = async (productId) => {
      if (!checkAuth()) return;
      
      try {
        const response = await axios({
          method: 'post',
          url: '/api/user/cart',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Authorization': `Bearer ${localStorage.getItem('user_token')}`
          },
          data: {
            product_id: productId,
            quantity: 1
          }
        });

        if (response.data.success) {
          cartCount.value++;
          alert('Product added to cart successfully!');
        }
      } catch (err) {
        console.error('Error adding to cart:', err);
        if (err.response?.status === 401) {
          router.push('/login');
        } else if (err.response?.status === 405) {
          // If method not allowed, try alternative endpoint
          try {
            const altResponse = await axios({
              method: 'post',
              url: '/api/cart/add',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Authorization': `Bearer ${localStorage.getItem('user_token')}`
              },
              data: {
                product_id: productId,
                quantity: 1
              }
            });
            
            if (altResponse.data.success) {
              cartCount.value++;
              alert('Product added to cart successfully!');
            }
          } catch (altErr) {
            console.error('Alternative endpoint also failed:', altErr);
            alert('Failed to add item to cart. Please try again.');
          }
        } else {
          alert(err.response?.data?.message || 'Failed to add item to cart. Please try again.');
        }
      }
    };

    const addToWishlist = async (productId) => {
      if (!checkAuth()) return;
      
      try {
        await axios.post('/api/user/wishlist/add', { product_id: productId });
        wishlistCount.value++;
      } catch (err) {
        console.error('Error adding to wishlist:', err);
        alert('Failed to add item to wishlist. Please try again.');
      }
    };

    const logout = () => {
      localStorage.removeItem('user_token');
      router.push('/login');
    };

    // Handle image loading errors
    const handleImageError = (event) => {
      event.target.src = '/img/placeholder.png';
    };

    onMounted(() => {
      fetchProducts();
    });

    return {
      newArrivals,
      trendingItems,
      loading,
      error,
      cartCount,
      wishlistCount,
      formatPrice,
      addToCart,
      addToWishlist,
      logout,
      handleImageError
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

/* Hero Section CSS */
.hero {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0px 60px;
  background-color: #eef1c4;
  min-height: 500px; /* Ensures hero section has enough height */
  position: relative; /* Added position relative */
  overflow: hidden; /* Ensure content stays within bounds */
}

/* Background text effect */
.hero::before {
  content: "Fashion Fashion Fashion Fashion Fashion Fashion Fashion Fashion Fashion Fashion Fashion Fashion Fashion Fashion Fashion Fashion";
  color: #ff7a00;
  font-size: 4rem;
  font-weight: bold;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) rotate(-17.13deg);
  white-space: nowrap;
  opacity: 0.08; /* Light opacity for subtle effect */
  pointer-events: none;
  z-index: 1; /* Set z-index to ensure it stays behind other elements */
}

.hero .left-column {
  flex: 1;
  position: relative;
  z-index: 2; /* Ensure text is above the background text */
}

.hero .right-column {
  flex: 1;
  position: relative;
  display: flex;
  align-items: flex-end; /* Pushes image to the bottom */
  justify-content: flex-end; /* Aligns image to the right */
  z-index: 2; /* Ensure image is above the background text */
}

.hero .right-column img {
  max-width: 100%;
  border-radius: 15px;
}

.hero p {
  font-size: 1.2rem;
  color: #333;
  margin-bottom: 15px;
  line-height: 1.6;
}

.hero h2 {
  color: #ff0000;
  font-size: 2.5rem;
  margin-bottom: 15px;
  line-height: 1.2;
}

.hero .highlight-orange {
  color: #ff7a00;
  font-size: 2rem;
  margin-bottom: 10px;
}

.hero .highlight-blue {
  color: #0066ff;
  font-size: 2rem;
  margin-bottom: 30px;
}

.shop-now-btn {
  display: inline-block;
  padding: 14px 35px;
  background-color: #ff0000;
  color: #fff;
  font-size: 1.1rem;
  border-radius: 30px;
  text-decoration: none;
  font-weight: bold;
  transition: background-color 0.3s ease;
}

.shop-now-btn:hover {
  background-color: #cc0000;
}

/* New Arrivals Section */
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

/* Promotion Section CSS */
.promo-section {
  padding: 80px;
  text-align: left;
}

.promo-section h2 {
  font-size: 2.5rem;
  color: #000000;
  margin-bottom: 5px;
}

/* Red highlight for 50% */
.highlight-red {
  color: #ff0000;
}

.promo-section p {
  font-size: 1.2rem;
  color: #666;
  margin-bottom: 30px;
}

.promo-card {
  background-image: url("/img/7.png"); /* Replace with your image */
  background-size: cover;
  background-position: center;
  border-radius: 15px;
  height: 500px; /* Adjust as needed */
  margin-bottom: 20px; /* Spacing for button */
}

.buy-now-btn {
  display: inline-block;
  background-color: #ff7a00;
  color: #ffffff;
  padding: 12px 30px;
  font-size: 1.1rem;
  font-weight: bold;
  text-decoration: none;
  border-radius: 30px;
  transition: background-color 0.3s ease;
}

.buy-now-btn:hover {
  background-color: #e66c00;
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

.trending-outfit {
  position: relative;
  padding: 20px;
  text-align: center;
}

.trending-outfit h2 {
  font-size: 2rem;
  margin-bottom: 10px;
}

.trending-outfit p {
  font-size: 1.1rem;
  margin-bottom: 20px;
}

.outfit-container {
  display: flex;
  gap: 20px;
  position: relative;
  justify-content: center;
  align-items: center;
}

/* Dots background */
.outfit-container::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-image: radial-gradient(circle, #f58c8c 2px, transparent 2px);
  background-size: 20px 20px;
  opacity: 0.5;
  z-index: 1;
}

/* Ensure images appear above the dots */
.left-image,
.right-images {
  position: relative;
  z-index: 2;
}

.left-image img {
  width: 100%;
  max-width: 580px;
  border-radius: 10px;
}

.right-images {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.right-images img {
  width: 100%;
  max-width: 580px;
  border-radius: 10px;
}

/* Button styling */
.see-more-btn {
  display: inline-block;
  margin-top: 20px;
  padding: 10px 20px;
  background-color: #ffffff;
  border: 2px solid #ff7a00;
  color: #ff7a00;
  font-weight: bold;
  border-radius: 5px;
  text-decoration: none;
}

.see-more-btn:hover {
  background-color: #ff7a00;
  color: #ffffff;
}

/* Contact Us Section */
.contact-us {
  padding: 80px;
  background-color: #ffffff;
}

.contact-container {
  display: flex;
  gap: 30px;
}

.contact-left {
  flex: 1;
}

.contact-left h2 {
  font-size: 2.5rem;
  color: #333;
  margin-bottom: 15px;
}

.contact-left p {
  font-size: 1.2rem;
  color: #666;
  margin-bottom: 20px;
}

.info-box {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 10px; /* Optional padding */
}

.info-box img {
  width: 100%; /* Ensures the image adapts to the box’s width */
  border-radius: 10px; /* Optional */
}

/* Contact Form */
.contact-right {
  flex: 2;
  background-color: rgba(
    0,
    102,
    255,
    0.5
  ); /* Blue background with 50% opacity */
  padding: 30px;
  border-radius: 10px;
}

.contact-right form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.name-fields {
  display: flex;
  gap: 15px;
}

.name-fields input {
  flex: 1;
}

.contact-right input,
.contact-right textarea {
  width: 100%;

  padding: 10px;
  border-radius: 5px;
  border: none;
  font-size: 1rem;
}

.contact-right textarea {
  resize: vertical;
  height: 300px;
}

.submit-btn {
  align-self: flex-start;
  background-color: #ff7a00;
  color: white;
  border: none;
  padding: 12px 30px;
  font-size: 1.1rem;
  font-weight: bold;
  border-radius: 30px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.submit-btn:hover {
  background-color: #e66c00;
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

.loading-state {
  text-align: center;
  padding: 2rem;
}

.loading-state i {
  font-size: 2rem;
  color: #666;
  margin-bottom: 1rem;
}

.error-state {
  text-align: center;
  padding: 2rem;
  color: #dc3545;
}

.retry-btn {
  background-color: #dc3545;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  margin-top: 1rem;
}

.card {
  position: relative;
  background-color: #ffffff;
  border-radius: 10px;
  overflow: hidden;
  transition: transform 0.3s ease;
  text-decoration: none;
  color: inherit;
}

.card:hover {
  transform: translateY(-5px);
}

.card-image {
  position: relative;
  overflow: hidden;
  border-radius: 8px 8px 0 0;
}

.product-img {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-radius: 8px 8px 0 0;
  transition: transform 0.3s ease;
}

.card:hover .product-img {
  transform: scale(1.05);
}

.trending-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 8px;
  transition: transform 0.3s ease;
}

.trending-img:hover {
  transform: scale(1.05);
}

.card-actions {
  position: absolute;
  top: 10px;
  right: 10px;
  display: flex;
  flex-direction: column;
  gap: 5px;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.card:hover .card-actions {
  opacity: 1;
}

.action-btn {
  background-color: white;
  border: none;
  border-radius: 50%;
  width: 35px;
  height: 35px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.action-btn:hover {
  background-color: #f8f9fa;
}

.card-content {
  padding: 1rem;
}

.description {
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.category {
  color: #666;
  font-size: 0.9rem;
  margin-bottom: 0.5rem;
}

.price {
  font-weight: bold;
  color: #e44d26;
}

.badge {
  position: absolute;
  top: -8px;
  right: -8px;
  background-color: #e44d26;
  color: white;
  border-radius: 50%;
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
}

.empty-state {
  text-align: center;
  padding: 2rem;
  color: #666;
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.cart-icon {
  position: relative;
  color: #333;
  text-decoration: none;
  padding: 8px;
  display: inline-flex;
  align-items: center;
  transition: color 0.3s ease;
}

.cart-icon:hover {
  color: #ff7a00;
}

.cart-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background-color: #ff7a00;
  color: white;
  border-radius: 50%;
  padding: 2px 6px;
  font-size: 0.75rem;
  min-width: 18px;
  text-align: center;
}
</style>