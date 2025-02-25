<template>
  <div class="logo-container" :class="{ 'small': small }">
    <div class="logo-text">
      <span class="fashion" :style="{ color: primaryColor }">Fashion</span>
      <span class="trend" :style="{ color: accentColor }">Trend</span>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FashionTrendLogo',
  props: {
    small: {
      type: Boolean,
      default: false
    },
    primaryColor: {
      type: String,
      default: '#2D3748' // Sophisticated dark gray
    },
    accentColor: {
      type: String,
      default: '#D53F8C' // Fashionable pink
    }
  }
}
</script>

<style scoped>
.logo-container {
  display: inline-flex;
  align-items: center;
  padding: 0.5rem;
  transition: transform 0.3s ease;
}

.logo-container:hover {
  transform: scale(1.05);
}

.logo-text {
  display: flex;
  flex-direction: column;
  line-height: 1;
  text-align: center;
}

.fashion {
  font-family: 'Playfair Display', serif;
  font-size: 2.2rem;
  font-weight: 700;
  letter-spacing: 0.02em;
  margin-bottom: 0.2rem;
}

.trend {
  font-family: 'Montserrat', sans-serif;
  font-size: 1.6rem;
  font-weight: 500;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.small .fashion {
  font-size: 1.6rem;
}

.small .trend {
  font-size: 1.2rem;
}

@keyframes slideIn {
  from { transform: translateX(-20px); opacity: 0; }
  to { transform: translateX(0); opacity: 1; }
}

.logo-container {
  animation: slideIn 0.5s ease-out;
}
</style>
