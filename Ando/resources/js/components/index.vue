<template>
    <div>
      <header class="header">
        <div class="nav-links">
          <a href="#home" class="nav-link active">Home</a>
          <a href="#new" class="nav-link">New</a>
          <a href="#trends" class="nav-link">Trends</a>
          <a href="#contact" class="nav-link">Contact</a>
        </div>
        <div class="title">
          <span class="four-point-star"></span>
          Fashion Trend
          <span class="four-point-star blue"></span>
        </div>
        <div class="icons">
          <i class="fas fa-search"></i>
          <i class="fas fa-heart"></i>
          <router-link to="/adminlogin" class="fas fa-user"></router-link>
          <i class="fas fa-shopping-cart"></i>
        </div>
      </header>
  
      <section class="hero" id="home">
        <div class="left-column">
          <p>Stay Ahead of the Curve: Discover the Latest in Fashion!</p>
          <h2>Fashion never ends.</h2>
          <p class="highlight-orange">It’s your look.</p>
          <p class="highlight-blue">It’s your style.</p>
          <router-link to="/register" class="shop-now-btn">Shop Now</router-link>
        </div>
        <div class="right-column">
          <img :src="'/img/g1.png'" alt="Fashion Trend Image" />
        </div>
      </section>
  
      <section class="new-arrivals" id="new">
        <h2>Discover New Arrivals</h2>
        <p>Recently added fashion!</p>
        <div class="card-container">
          <div v-for="(item, index) in newArrivals" :key="index" class="card">
            <img :src="item.image" :alt="`New Arrival ${index + 1}`" />
            <div class="description">{{ item.description }}</div>
            <div class="price">{{ item.price }}</div>
            <button @click="addToCart(item.id)">Add to Cart</button>
          </div>
        </div>
      </section>
  
      <section class="promo-section" id="trends">
        <h2>Flat<span class="highlight-red"> 50% </span>Off</h2>
        <p>Valid till 31st only</p>
        <div class="promo-card">
          <div class="promo-content"></div>
        </div>
        <router-link to="/register" class="buy-now-btn">Buy Now</router-link>
      </section>
  
      <section class="trending-outfit">
        <h2>Trending Outfit of The Week</h2>
        <p>
          From runway to real life: Fashion trends you need to know. Stay ahead of
          the curve with these trendsetting outfits.
        </p>
        <div class="outfit-container">
          <div class="left-image">
            <img :src="'/img/8.png'" alt="Main Outfit Image" />
          </div>
          <div class="right-images">
            <img :src="'/img/9.png'" alt="Sub Outfit Image 1" />
            <img :src="'/img/10.png'" alt="Sub Outfit Image 2" />
          </div>
        </div>
        <router-link to="/register" class="see-more-btn">See More Trending</router-link>
      </section>
  
      <section class="contact-us" id="contact">
        <div class="contact-container">
          <div class="contact-left">
            <h2><span class="highlight-red">CONTACT US!</span></h2>
            <p>
              Need to get in touch with us? Either fill out the form with your
              inquiry or message us on <b> fashiontrend@email.com </b>
            </p>
            <div class="info-box">
              <img :src="'/img/11.png'" alt="Information Image" />
            </div>
          </div>
          <div class="contact-right">
            <form @submit.prevent="submitForm">
              <div class="name-fields">
                <input type="text" placeholder="First Name" v-model="form.firstName" />
                <input type="text" placeholder="Last Name" v-model="form.lastName" />
              </div>
              <input type="email" placeholder="Email" v-model="form.email" />
              <textarea placeholder="Your Message" v-model="form.message"></textarea>
              <button type="submit" class="submit-btn">Submit</button>
            </form>
          </div>
        </div>
      </section>
  
      <footer class="footer">
        <div class="footer-content">
          <div class="footer-column">
            <h3>About Us</h3>
            <p>Learn more about our fashion journey and mission.</p>
          </div>
          <div class="footer-column">
            <h3>Contact</h3>
            <p>Email us at support@fashiontrend.com</p>
          </div>
          <div class="footer-icons">
            <i class="fab fa-facebook-f"></i>
            <i class="fab fa-twitter"></i>
            <i class="fab fa-instagram"></i>
          </div>
        </div>
        <hr class="footer-divider" />
        <div class="footer-bottom"> 2023 Fashion Trend. All rights reserved.</div>
      </footer>
  
      <button class="scroll-to-top" @click="scrollToTop" v-show="isTopVisible">
        ↑
      </button>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue'
  import axios from 'axios'
  import { useRouter } from 'vue-router'

  const isTopVisible = ref(false)
  const newArrivals = ref([
    { id: 1, image: '/img/1.png', description: 'Lorem Ipsum', price: '$499' },
    { id: 2, image: '/img/2.png', description: 'Lorem Ipsum', price: '$199' },
    { id: 3, image: '/img/3.png', description: 'Lorem Ipsum', price: '$200' },
    { id: 4, image: '/img/4.png', description: 'Lorem Ipsum', price: '$300' },
    { id: 5, image: '/img/5.png', description: 'Lorem Ipsum', price: '$1000' },
    { id: 6, image: '/img/6.png', description: 'Lorem Ipsum', price: '$600' },
  ])

  const form = ref({
    firstName: '',
    lastName: '',
    email: '',
    message: '',
  })

  const router = useRouter()

  const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }

  const submitForm = () => {
    console.log('Form submitted:', form.value)
    // handle form submission
  }

  const addToCart = async (productId, quantity = 1) => {
    try {
      const response = await axios.post('/api/user/cart', {
        product_id: productId,
        quantity: quantity
      }, {
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
      });

      if (response.data?.success) {
        // Show success message
        alert('Product added to cart successfully!');
        // Redirect to cart page
        router.push('/user/cart');
      } else {
        throw new Error(response.data?.message || 'Failed to add product to cart');
      }
    } catch (error) {
      console.error('Error adding to cart:', error);
      alert(error.response?.data?.message || 'Failed to add product to cart. Please try again.');
    }
  }

  onMounted(() => {
    window.addEventListener('scroll', () => {
      isTopVisible.value = window.scrollY > 300
    })
  })
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
    color: #ff0000;
    font-weight: bold;
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

  .hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0px 60px;
    background-color: #eef1c4;
    min-height: 500px;
    position: relative;
    overflow: hidden;
  }

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
    opacity: 0.08;
    pointer-events: none;
    z-index: 1;
  }

  .hero .left-column {
    flex: 1;
    position: relative;
    z-index: 2;
  }

  .hero .right-column {
    flex: 1;
    position: relative;
    display: flex;
    align-items: flex-end;
    justify-content: flex-end;
    z-index: 2;
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
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
  }

  .card {
    background-color: #ffffff;
    border-radius: 10px;
    display: block;
    text-decoration: none;
    color: inherit;
    transition: transform 0.2s;
    text-align: center;
    padding: 20px;
  }

  .card:hover {
    transform: scale(1.05);
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

  @media (max-width: 768px) {
    .card-container {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (max-width: 480px) {
    .card-container {
      grid-template-columns: 1fr;
    }
  }

  .promo-section {
    padding: 80px;
    text-align: left;
  }

  .promo-section h2 {
    font-size: 2.5rem;
    color: #000000;
    margin-bottom: 5px;
  }

  .highlight-red {
    color: #ff0000;
  }

  .promo-section p {
    font-size: 1.2rem;
    color: #666;
    margin-bottom: 30px;
  }

  .promo-card {
    background-image: url("/img/7.png");
    background-size: cover;
    background-position: center;
    border-radius: 15px;
    height: 500px;
    margin-bottom: 20px;
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
    padding: 10px;
  }

  .info-box img {
    width: 100%;
    border-radius: 10px;
  }

  .contact-right {
    flex: 2;
    background-color: rgba(0, 102, 255, 0.5);
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
    color: #ffffff;
    padding: 12px 30px;
    font-size: 1.1rem;
    font-weight: bold;
    border: none;
    border-radius: 30px;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }

  .submit-btn:hover {
    background-color: #e66c00;
  }

  @media (max-width: 768px) {
    .contact-container {
      flex-direction: column;
    }
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
    position: fixed;
    bottom: 20px;
    right: 20px;
    background-color: #ff0000;
    color: #fff;
    border: none;
    border-radius: 50%;
    padding: 10px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
    display: none;
    text-align: center;
    font-size: 18px;
    z-index: 1000;
  }

  .scroll-to-top:hover {
    background-color: #d00000;
  }
</style>