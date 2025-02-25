<template>
  <body>
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
        <router-link to="/user/wishlist" class="fas fa-heart"></router-link>
        <router-link to="/user/purchase" class="fas fa-user"></router-link>
        <router-link to="/user/cart" class="fas fa-shopping-cart"></router-link>
        <i class="fas fa-sign-out-alt" @click="logout"></i>
      </div>
    </header>

 
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

  </body>

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
export default {
  mounted() {
    // Move DOM manipulations here
    const scrollToTopBtn = document.querySelector('.scroll-to-top');
    if (scrollToTopBtn) {
      window.addEventListener('scroll', () => {
        if (window.pageYOffset > 100) {
          scrollToTopBtn.style.display = 'block';
        } else {
          scrollToTopBtn.style.display = 'none';
        }
      });
    }
  },
  beforeUnmount() {
    // Clean up event listeners
    window.removeEventListener('scroll', () => {});
  },
  methods: {
    logout() {
      // Implement your logout logic here
      console.log('Logout button clicked');
    }
  }
}
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
</style>