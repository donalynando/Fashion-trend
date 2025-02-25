<template>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <FashionTrendLogo class="login-logo" />
        <h1>{{ isAdmin ? 'Admin Login' : 'User Login' }}</h1>
      </div>

      <div class="toggle-container">
        <button 
          :class="['toggle-btn', { active: !isAdmin }]" 
          @click="isAdmin = false"
        >
          User
        </button>
        <button 
          :class="['toggle-btn', { active: isAdmin }]" 
          @click="isAdmin = true"
        >
          Admin
        </button>
      </div>

      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label for="email">Email</label>
          <div class="input-group">
            <i class="fas fa-envelope"></i>
            <input
              type="email"
              id="email"
              v-model="form.email"
              :class="{ 'error': errors.email }"
              placeholder="Enter your email"
              required
            />
          </div>
          <span class="error-message" v-if="errors.email">{{ errors.email }}</span>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-group">
            <i class="fas fa-lock"></i>
            <input
              :type="showPassword ? 'text' : 'password'"
              id="password"
              v-model="form.password"
              :class="{ 'error': errors.password }"
              placeholder="Enter your password"
              required
            />
            <i 
              :class="['fas', showPassword ? 'fa-eye-slash' : 'fa-eye', 'toggle-password']"
              @click="showPassword = !showPassword"
            ></i>
          </div>
          <span class="error-message" v-if="errors.password">{{ errors.password }}</span>
        </div>

        <div class="form-options">
          <label class="remember-me">
            <input type="checkbox" v-model="form.remember" />
            <span>Remember me</span>
          </label>
          <a href="#" class="forgot-password" @click.prevent="handleForgotPassword">
            Forgot Password?
          </a>
        </div>

        <button type="submit" class="login-btn" :disabled="isLoading">
          <span v-if="!isLoading">Login</span>
          <i v-else class="fas fa-spinner fa-spin"></i>
        </button>

        <div v-if="!isAdmin" class="register-link">
          Don't have an account? 
          <router-link to="/register">Register here</router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import FashionTrendLogo from '../shared/FashionTrendLogo.vue';

export default {
  name: 'Login',
  components: {
    FashionTrendLogo
  },
  setup() {
    const router = useRouter();
    const isAdmin = ref(true); 
    const isLoading = ref(false);
    const showPassword = ref(false);

    const form = reactive({
      email: '',
      password: '',
      remember: false
    });

    const errors = reactive({
      email: '',
      password: '',
      general: ''
    });

    const validateForm = () => {
      let isValid = true;
      errors.email = '';
      errors.password = '';
      errors.general = '';

      if (!form.email) {
        errors.email = 'Email is required';
        isValid = false;
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
        errors.email = 'Please enter a valid email address';
        isValid = false;
      }

      if (!form.password) {
        errors.password = 'Password is required';
        isValid = false;
      }

      return isValid;
    };

    const handleLogin = async () => {
      if (!validateForm()) return;

      try {
        isLoading.value = true;
        
        // Reset previous errors
        Object.keys(errors).forEach(key => errors[key] = '');

        const endpoint = isAdmin.value ? '/api/admin/login' : '/api/login';
        console.log('Attempting login with endpoint:', endpoint);
        
        const response = await axios.post(endpoint, {
          email: form.email,
          password: form.password,
          remember: form.remember
        });

        const { token, user } = response.data;
        console.log('Login successful, attempting navigation');
        
        if (isAdmin.value) {
          localStorage.setItem('admin_token', token);
          localStorage.setItem('admin_user', JSON.stringify(user));
          
          // Debug available routes before navigation
          console.log('Available routes:', router.getRoutes().map(r => r.name));
          
          // Try to resolve the route first
          try {
            const resolvedRoute = router.resolve({ name: 'admin-dashboard' });
            console.log('Resolved route:', resolvedRoute);
            await router.push({ name: 'admin-dashboard' });
          } catch (navError) {
            console.error('Navigation error:', navError);
            errors.general = 'Error navigating to dashboard. Please try again.';
          }
        } else {
          localStorage.setItem('user_token', token);
          localStorage.setItem('user', JSON.stringify(user));
          await router.push('/user/dashboard');
        }
      } catch (error) {
        console.error('Login error:', error);
        if (error.response?.status === 422) {
          const serverErrors = error.response.data.errors;
          Object.keys(serverErrors).forEach(key => {
            errors[key] = serverErrors[key][0];
          });
        } else {
          errors.general = 'An error occurred during login. Please try again.';
        }
      } finally {
        isLoading.value = false;
      }
    };

    const handleForgotPassword = () => {
      router.push({ name: 'forgot-password' });
    };

    return {
      isAdmin,
      isLoading,
      showPassword,
      form,
      errors,
      handleLogin,
      handleForgotPassword
    };
  }
};
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #FF7A00 0%, #FFB673 100%);
  padding: 20px;
}

.login-card {
  background: white;
  border-radius: 15px;
  padding: 30px;
  width: 100%;
  max-width: 400px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.login-logo {
  margin-bottom: 1.5rem;
  width: auto;
  height: auto;
}

.toggle-container {
  display: flex;
  justify-content: center;
  margin-bottom: 30px;
  background: #f5f5f5;
  padding: 5px;
  border-radius: 30px;
}

.toggle-btn {
  padding: 8px 20px;
  border: none;
  background: none;
  color: #666;
  cursor: pointer;
  border-radius: 25px;
  transition: all 0.3s ease;
}

.toggle-btn.active {
  background: #FF7A00;
  color: white;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  color: #333;
  font-weight: 500;
}

.input-group {
  position: relative;
  display: flex;
  align-items: center;
}

.input-group i {
  position: absolute;
  left: 12px;
  color: #999;
}

.input-group input {
  width: 100%;
  padding: 12px 40px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.3s ease;
}

.input-group input:focus {
  border-color: #FF7A00;
  outline: none;
}

.input-group input.error {
  border-color: #ff4444;
}

.toggle-password {
  position: absolute;
  right: 12px;
  cursor: pointer;
  color: #999;
}

.error-message {
  color: #ff4444;
  font-size: 12px;
  margin-top: 5px;
  display: block;
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.remember-me {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #666;
  cursor: pointer;
}

.forgot-password {
  color: #FF7A00;
  text-decoration: none;
  font-size: 14px;
}

.forgot-password:hover {
  text-decoration: underline;
}

.login-btn {
  width: 100%;
  padding: 12px;
  background: #FF7A00;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.login-btn:hover {
  background: #e66e00;
}

.login-btn:disabled {
  background: #ffb673;
  cursor: not-allowed;
}

.register-link {
  text-align: center;
  margin-top: 20px;
  color: #666;
}

.register-link a {
  color: #FF7A00;
  text-decoration: none;
  font-weight: 500;
}

.register-link a:hover {
  text-decoration: underline;
}

@media (max-width: 480px) {
  .login-card {
    padding: 20px;
  }

  .form-options {
    flex-direction: column;
    gap: 10px;
    align-items: flex-start;
  }
}
</style>