<template>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <FashionTrendLogo class="login-logo" />
        <h1>Register</h1>
      </div>

      <form @submit.prevent="handleRegister" class="login-form">
        <div class="form-group">
          <label for="lastname">Last Name</label>
          <div class="input-group">
            <i class="fas fa-user"></i>
            <input
              type="text"
              id="lastname"
              v-model="form.lastname"
              :class="{ 'error': errors.lastname }"
              placeholder="Enter your last name"
              required
            />
          </div>
          <span class="error-message" v-if="errors.lastname">{{ errors.lastname }}</span>
        </div>

        <div class="form-group">
          <label for="firstname">First Name</label>
          <div class="input-group">
            <i class="fas fa-user"></i>
            <input
              type="text"
              id="firstname"
              v-model="form.firstname"
              :class="{ 'error': errors.firstname }"
              placeholder="Enter your first name"
              required
            />
          </div>
          <span class="error-message" v-if="errors.firstname">{{ errors.firstname }}</span>
        </div>

        <div class="form-group">
          <label for="middlename">Middle Name</label>
          <div class="input-group">
            <i class="fas fa-user"></i>
            <input
              type="text"
              id="middlename"
              v-model="form.middlename"
              :class="{ 'error': errors.middlename }"
              placeholder="Enter your middle name"
            />
          </div>
          <span class="error-message" v-if="errors.middlename">{{ errors.middlename }}</span>
        </div>

        <div class="form-group">
          <label for="gender">Gender</label>
          <div class="input-group">
            <i class="fas fa-venus-mars"></i>
            <select
              id="gender"
              v-model="form.gender"
              :class="{ 'error': errors.gender }"
              required
            >
              <option value="" disabled>Select gender</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="other">Other</option>
            </select>
          </div>
          <span class="error-message" v-if="errors.gender">{{ errors.gender }}</span>
        </div>

        <div class="form-group">
          <label for="age">Age</label>
          <div class="input-group">
            <i class="fas fa-birthday-cake"></i>
            <input
              type="number"
              id="age"
              v-model.number="form.age"
              :class="{ 'error': errors.age }"
              placeholder="Enter your age"
              required
            />
          </div>
          <span class="error-message" v-if="errors.age">{{ errors.age }}</span>
        </div>

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
          <label for="phone">Phone Number</label>
          <div class="input-group">
            <i class="fas fa-phone"></i>
            <input
              type="tel"
              id="phone"
              v-model="form.phone"
              :class="{ 'error': errors.phone }"
              placeholder="Enter your phone number"
              required
            />
          </div>
          <span class="error-message" v-if="errors.phone">{{ errors.phone }}</span>
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
              placeholder="Create password"
              required
            />
            <i 
              :class="['fas', showPassword ? 'fa-eye-slash' : 'fa-eye', 'toggle-password']"
              @click="showPassword = !showPassword"
            ></i>
          </div>
          <span class="error-message" v-if="errors.password">{{ errors.password }}</span>
        </div>

        <div class="form-group">
          <label for="password_confirmation">Confirm Password</label>
          <div class="input-group">
            <i class="fas fa-lock"></i>
            <input
              :type="showConfirmPassword ? 'text' : 'password'"
              id="password_confirmation"
              v-model="form.password_confirmation"
              :class="{ 'error': errors.password_confirmation }"
              placeholder="Confirm password"
              required
            />
            <i 
              :class="['fas', showConfirmPassword ? 'fa-eye-slash' : 'fa-eye', 'toggle-password']"
              @click="showConfirmPassword = !showConfirmPassword"
            ></i>
          </div>
          <span class="error-message" v-if="errors.password_confirmation">{{ errors.password_confirmation }}</span>
        </div>

        <button type="submit" class="login-btn" :disabled="isLoading">
          <span v-if="!isLoading">Register</span>
          <i v-else class="fas fa-spinner fa-spin"></i>
        </button>

        <div class="register-link">
          Already have an account? 
          <router-link to="/login">Login here</router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import FashionTrendLogo from './shared/FashionTrendLogo.vue';
import axios from 'axios';

export default {
  name: 'Register',
  components: {
    FashionTrendLogo
  },
  setup() {
    const router = useRouter();
    const isLoading = ref(false);
    const showPassword = ref(false);
    const showConfirmPassword = ref(false);

    const form = reactive({
      lastname: '',
      firstname: '',
      middlename: '',
      gender: '',
      age: '',
      email: '',
      phone: '',
      password: '',
      password_confirmation: ''
    });

    const errors = reactive({
      lastname: '',
      firstname: '',
      middlename: '',
      gender: '',
      age: '',
      email: '',
      phone: '',
      password: '',
      password_confirmation: '',
      general: ''
    });

    const handleRegister = async () => {
      // Reset errors
      Object.keys(errors).forEach(key => {
        errors[key] = '';
      });

      let isValid = true;

      // Basic validation
      if (!form.lastname) {
        errors.lastname = 'Last name is required';
        isValid = false;
      }

      if (!form.firstname) {
        errors.firstname = 'First name is required';
        isValid = false;
      }

      if (!form.gender) {
        errors.gender = 'Gender is required';
        isValid = false;
      }

      if (!form.age) {
        errors.age = 'Age is required';
        isValid = false;
      } else if (form.age < 1 || form.age > 150) {
        errors.age = 'Please enter a valid age';
        isValid = false;
      }

      if (!form.email) {
        errors.email = 'Email is required';
        isValid = false;
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
        errors.email = 'Please enter a valid email address';
        isValid = false;
      }

      if (!form.phone) {
        errors.phone = 'Phone number is required';
        isValid = false;
      }

      if (!form.password) {
        errors.password = 'Password is required';
        isValid = false;
      } else if (form.password.length < 8) {
        errors.password = 'Password must be at least 8 characters long';
        isValid = false;
      }

      if (!form.password_confirmation) {
        errors.password_confirmation = 'Please confirm your password';
        isValid = false;
      } else if (form.password !== form.password_confirmation) {
        errors.password_confirmation = 'Passwords do not match';
        isValid = false;
      }

      if (!isValid) {
        return;
      }

      try {
        isLoading.value = true;
        const response = await axios.post('/api/register', form);
        
        // Registration successful
        router.push({ 
          path: '/login',
          query: { 
            registered: 'success',
            email: form.email
          }
        });
      } catch (error) {
        if (error.response && error.response.status === 422) {
          // Validation errors from the server
          const serverErrors = error.response.data.errors;
          Object.keys(serverErrors).forEach(key => {
            errors[key] = serverErrors[key][0]; // Take the first error message for each field
          });
        } else {
          // Handle other errors
          console.error('Registration error:', error);
          errors.general = 'An error occurred during registration. Please try again.';
        }
      } finally {
        isLoading.value = false;
      }
    };

    return {
      form,
      errors,
      isLoading,
      showPassword,
      showConfirmPassword,
      handleRegister
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
  max-width: 500px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.login-header {
  text-align: center;
  margin-bottom: 30px;
}

.login-logo {
  width: 120px;
  height: auto;
  margin-bottom: 20px;
}

.login-header h1 {
  color: #333;
  font-size: 24px;
  margin: 0;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  color: #666;
  font-size: 14px;
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

.input-group input,
.input-group select {
  width: 100%;
  padding: 12px 12px 12px 40px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.3s;
}

.input-group input:focus,
.input-group select:focus {
  outline: none;
  border-color: #FF7A00;
}

.input-group input.error,
.input-group select.error {
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
  margin-top: 4px;
}

.login-btn {
  background: #FF7A00;
  color: white;
  border: none;
  padding: 12px;
  border-radius: 8px;
  font-size: 16px;
  cursor: pointer;
  transition: background-color 0.3s;
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
  color: #666;
  font-size: 14px;
}

.register-link a {
  color: #FF7A00;
  text-decoration: none;
  font-weight: 500;
}

.register-link a:hover {
  text-decoration: underline;
}
</style>