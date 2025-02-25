<template>
    <div class="checkout-container">
        <header class="header">
            <div class="nav-links">
                <router-link to="/home"><b>Home</b></router-link>
                <router-link to="/new"><b>New</b></router-link>
                <router-link to="/trends"><b>Trends</b></router-link>
                <router-link to="/contact"><b>Contact</b></router-link>
            </div>
            <div class="title">
                <span class="four-point-star"></span>
                Fashion Trend
                <span class="four-point-star blue"></span>
            </div>
            <div class="icons">
                <i class="fas fa-search" @click="openSearch"></i>
                <router-link to="/wishlist" class="fas fa-heart"><span v-if="wishlistCount" class="badge">{{ wishlistCount }}</span></router-link>
                <router-link to="/purchase" class="fas fa-user"></router-link>
                <router-link to="/user/cart" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <span v-if="cartItems.length" class="cart-badge">{{ cartItems.length }}</span>
                </router-link>
                <i class="fas fa-sign-out-alt" @click="logout"></i>
            </div>
        </header>

        <main class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <h2>Checkout</h2>
                <button class="back-btn" @click="$router.go(-1)">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
            </div>

            <!-- Error Alert -->
            <div v-if="error" class="error-state">
                <i class="fas fa-exclamation-circle"></i>
                <p>{{ error }}</p>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="loading-state">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading checkout details...</p>
            </div>

            <!-- Main Content -->
            <div v-else class="checkout-content">
                <!-- Delivery Address Section -->
                <section class="address-section card">
                    <h3>Delivery Address</h3>
                    <div v-if="address" class="address-details">
                        <p class="name">{{ address.name }} <span class="phone">({{ address.phone }})</span></p>
                        <p class="street">{{ address.street }}, {{ address.barangay }}, {{ address.city }}</p>
                        <p class="region">{{ address.province }} {{ address.postal_code }}</p>
                    </div>
                    <button @click="showAddressModal = true" class="action-btn">
                        {{ address ? 'Change' : 'Add' }} Address
                    </button>
                </section>

                <!-- Products Ordered Section -->
                <section class="products-section card">
                    <h3>Products Ordered</h3>
                    <div v-if="cartItems.length === 0" class="empty-state">
                        <i class="fas fa-shopping-cart"></i>
                        <p>Your cart is empty</p>
                        <router-link to="/trends" class="shop-now-btn">Shop Now</router-link>
                    </div>
                    <div v-else class="product-list">
                        <div v-for="item in cartItems" :key="item.id" class="product-item">
                            <img :src="item.image_url" :alt="item.name" class="product-image">
                            <div class="product-details">
                                <h4>{{ item.name }}</h4>
                                <p v-if="item.variation" class="variation">{{ item.variation }}</p>
                                <p class="price highlight-orange">₱{{ item.price }}</p>
                                <p class="quantity">Quantity: {{ item.quantity }}</p>
                                <p class="subtotal highlight-blue">Subtotal: ₱{{ item.quantity * item.price }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Shipping Options Section -->
                <section class="shipping-section card">
                    <h3>Shipping Option</h3>
                    <select v-model="selectedShipping" class="shipping-select">
                        <option v-for="option in shippingOptions" 
                                :key="option.id" 
                                :value="option">
                            {{ option.label }} - ₱{{ option.cost }}
                        </option>
                    </select>
                    <div v-if="selectedShipping" class="shipping-details">
                        <p>{{ selectedShipping.description }}</p>
                        <p v-if="selectedShipping.guarantee" class="guarantee">{{ selectedShipping.guarantee }}</p>
                        <p class="cost highlight-orange">Shipping Cost: ₱{{ selectedShipping.cost }}</p>
                    </div>
                </section>

                <!-- Payment Method Section -->
                <section class="payment-section card">
                    <h3>Payment Method</h3>
                    <div class="payment-options">
                        <label v-for="method in paymentMethods" :key="method.value" class="payment-option">
                            <input type="radio" 
                                   v-model="selectedPaymentMethod" 
                                   :value="method.value">
                            <span class="radio-label">{{ method.label }}</span>
                        </label>
                    </div>
                </section>

                <!-- Order Summary Section -->
                <section class="order-summary card">
                    <h3>Order Summary</h3>
                    <div class="summary-content">
                        <div class="summary-row">
                            <span>Items ({{ totalItems }}):</span>
                            <span>₱{{ merchandiseSubtotal }}</span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping Fee:</span>
                            <span>₱{{ selectedShipping ? selectedShipping.cost : 0 }}</span>
                        </div>
                        <div class="summary-row">
                            <span>Payment Fee:</span>
                            <span>₱{{ paymentFee }}</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total Payment:</span>
                            <span class="highlight-orange">₱{{ totalPayment }}</span>
                        </div>
                        <button @click="placeOrder" 
                                :disabled="!canPlaceOrder || loading" 
                                class="checkout-btn">
                            {{ loading ? 'Processing...' : 'Place Order' }}
                        </button>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <!-- Address Modal -->
    <div v-if="showAddressModal" class="modal-overlay">
        <div class="modal-content card">
            <div class="modal-header">
                <h3>{{ address ? 'Edit' : 'Add' }} Delivery Address</h3>
                <button class="close-btn" @click="closeAddressModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Error Alert -->
            <div v-if="addressError" class="error-state">
                <div v-if="typeof addressError === 'string'">{{ addressError }}</div>
                <ul v-else>
                    <li v-for="(errors, field) in addressError" :key="field">
                        {{ errors[0] }}
                    </li>
                </ul>
            </div>

            <form @submit.prevent="saveAddress" class="address-form">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" v-model="addressForm.name" required>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" v-model="addressForm.phone" required>
                </div>
                <div class="form-group">
                    <label>Street Address</label>
                    <input type="text" v-model="addressForm.street" required>
                </div>
                <div class="form-group">
                    <label>Barangay</label>
                    <input type="text" v-model="addressForm.barangay" required>
                </div>
                <div class="form-group">
                    <label>City</label>
                    <input type="text" v-model="addressForm.city" required>
                </div>
                <div class="form-group">
                    <label>Province</label>
                    <input type="text" v-model="addressForm.province" required>
                </div>
                <div class="form-group">
                    <label>Postal Code</label>
                    <input type="text" v-model="addressForm.postal_code" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="cancel-btn" @click="closeAddressModal">Cancel</button>
                    <button type="submit" :disabled="loading" class="save-btn">
                        {{ loading ? 'Saving...' : 'Save Address' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import axios from 'axios'
import { onMounted, onBeforeMount } from 'vue'
import { useRouter } from 'vue-router'

export default {
    setup() {
        const router = useRouter()
        
        // Validate checkout data before mounting
        onBeforeMount(() => {
            const checkoutData = localStorage.getItem('checkout_data')
            if (!checkoutData) {
                router.push('/user/cart')
                return
            }

            const data = JSON.parse(checkoutData)
            const timestamp = data.timestamp
            const currentTime = new Date().getTime()
            
            // Check if checkout data is older than 30 minutes
            if (currentTime - timestamp > 30 * 60 * 1000) {
                localStorage.removeItem('checkout_data')
                router.push('/user/cart')
                return
            }
        })
    },
    
    data() {
        return {
            cartItems: [],
            address: null,
            showAddressModal: false,
            addressError: null,
            addressForm: {
                name: '',
                phone: '',
                street: '',
                barangay: '',
                city: '',
                province: '',
                postal_code: ''
            },
            selectedShipping: null,
            shippingOptions: [
                {
                    id: 1,
                    name: 'standard',
                    label: 'Standard Local',
                    cost: 125,
                    description: 'Delivery within 3-5 business days',
                    guarantee: 'Get a ₱50 voucher if delivery exceeds 5 business days'
                },
                {
                    id: 2,
                    name: 'express',
                    label: 'Express Delivery',
                    cost: 200,
                    description: 'Next day delivery',
                    guarantee: 'Get a ₱100 voucher if not delivered next day'
                },
                {
                    id: 3,
                    name: 'same_day',
                    label: 'Same Day Delivery',
                    cost: 350,
                    description: 'Delivery within 24 hours',
                    guarantee: 'Get a ₱200 voucher if not delivered within 24 hours'
                }
            ],
            paymentMethods: [
                { value: 'cash-on-delivery', label: 'Cash on Delivery' },
                { value: 'credit-card', label: 'Credit Card' },
                { value: 'paypal', label: 'PayPal' },
                { value: 'gcash', label: 'GCash' }
            ],
            selectedPaymentMethod: 'cash-on-delivery',
            loading: false,
            error: null
        }
    },
    
    computed: {
        totalItems() {
            return this.cartItems.reduce((sum, item) => sum + parseInt(item.quantity), 0)
        },
        merchandiseSubtotal() {
            return this.cartItems.reduce((total, item) => {
                return total + (parseFloat(item.price) * parseInt(item.quantity))
            }, 0)
        },
        paymentFee() {
            const fees = {
                'cash-on-delivery': 0,
                'credit-card': 50,
                'paypal': 50,
                'gcash': 20
            }
            return fees[this.selectedPaymentMethod] || 0
        },
        totalPayment() {
            return this.merchandiseSubtotal + 
                   (this.selectedShipping ? this.selectedShipping.cost : 0) + 
                   this.paymentFee
        },
        canPlaceOrder() {
            return this.cartItems.length > 0 && 
                   this.address && 
                   this.selectedShipping && 
                   this.selectedPaymentMethod
        }
    },
    
    methods: {
        async loadCheckoutData() {
            try {
                const response = await axios.get('/api/user/cart')
                if (response.data?.success) {
                    this.cartItems = response.data.items
                } else {
                    throw new Error('Failed to load cart data')
                }
            } catch (error) {
                console.error('Error loading cart data:', error)
                this.error = 'Failed to load cart data'
                this.$router.push('/user/cart')
            }
        },
        
        async fetchUserAddress() {
            try {
                const response = await axios.get('/api/user/address', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                
                if (response.data?.success && response.data.address) {
                    this.address = response.data.address;
                    // Pre-fill the form with existing address
                    this.addressForm = { ...response.data.address };
                }
            } catch (error) {
                console.error('Error fetching address:', error)
                if (error.response?.status === 401) {
                    this.$router.push('/login')
                } else {
                    this.error = 'Failed to load delivery address'
                }
            }
        },
        
        closeAddressModal() {
            this.showAddressModal = false;
            this.addressError = null;
            this.addressForm = {
                name: '',
                phone: '',
                street: '',
                barangay: '',
                city: '',
                province: '',
                postal_code: ''
            };
        },

        async saveAddress() {
            this.loading = true;
            this.addressError = null;

            try {
                const response = await axios.post('/api/user/address', this.addressForm, {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.data?.success) {
                    this.address = response.data.address;
                    this.closeAddressModal();
                } else {
                    throw new Error(response.data?.message || 'Failed to save address');
                }
            } catch (error) {
                console.error('Error saving address:', error);
                if (error.response?.status === 422) {
                    this.addressError = error.response.data.errors;
                } else {
                    this.addressError = error.response?.data?.message || 'Failed to save address';
                }
            } finally {
                this.loading = false;
            }
        },
        
        async placeOrder() {
            if (!this.canPlaceOrder) {
                this.error = 'Please fill in all required information'
                return
            }

            this.loading = true
            this.error = null

            try {
                // Format items for the API
                const items = this.cartItems.map(item => ({
                    product_id: item.product_id || item.id, // Try both product_id and id
                    quantity: parseInt(item.quantity),
                    price: parseFloat(item.price)
                }))

                // Log the items for debugging
                console.log('Order items:', items);

                const orderData = {
                    shipping_address: {
                        name: this.address.name,
                        phone: this.address.phone,
                        street: this.address.street,
                        barangay: this.address.barangay,
                        city: this.address.city,
                        province: this.address.province,
                        postal_code: this.address.postal_code
                    },
                    shipping_option: this.selectedShipping.name,
                    payment_method: this.selectedPaymentMethod,
                    items: items,
                    voucher_code: null
                }

                // Log the full order data for debugging
                console.log('Order data:', orderData);

                const response = await axios.post('/api/user/orders', orderData, {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${localStorage.getItem('user_token')}`,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })

                if (response.data?.success) {
                    // Clear checkout data and cart
                    localStorage.removeItem('checkout_data')
                    await axios.delete('/api/user/cart', {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    
                    // Show success message and redirect
                    this.$router.push({
                        path: '/user/orders',
                        query: { 
                            status: 'success',
                            order_id: response.data.order_id,
                            message: 'Order placed successfully!'
                        }
                    })
                } else {
                    throw new Error(response.data?.message || 'Failed to place order')
                }
            } catch (error) {
                console.error('Error placing order:', error)
                this.error = error.response?.data?.message || 'Failed to place order. Please try again.'
                
                // If validation error, show detailed message
                if (error.response?.status === 422) {
                    const errors = error.response.data.errors
                    if (errors) {
                        this.error = Object.values(errors)
                            .flat()
                            .join('\n')
                    }
                }
            } finally {
                this.loading = false
            }
        }
    },
    
    mounted() {
        this.loadCheckoutData()
        this.fetchUserAddress()
    }
}
</script>

<style scoped>
.checkout-container {
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

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.page-header h2 {
    font-size: 24px;
    color: #333;
}

.back-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    transition: color 0.3s;
}

.back-btn:hover {
    color: #ff4d4d;
}

.card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
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

.error-state {
    background: #fff5f5;
}

.error-state i {
    color: #ff4d4d;
}

.checkout-content {
    display: grid;
    gap: 20px;
}

.address-details {
    margin: 15px 0;
}

.address-details p {
    margin: 5px 0;
    color: #666;
}

.name {
    font-weight: 500;
    color: #333;
}

.phone {
    color: #666;
}

.product-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.product-item {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 20px;
    padding: 15px;
    border-bottom: 1px solid #eee;
}

.product-item:last-child {
    border-bottom: none;
}

.product-image {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
}

.product-details h4 {
    font-size: 18px;
    margin-bottom: 8px;
    color: #333;
}

.variation {
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

.shipping-select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 15px;
}

.shipping-details {
    color: #666;
}

.guarantee {
    color: #4d79ff;
    margin: 10px 0;
}

.payment-options {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.payment-option {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.radio-label {
    color: #333;
}

.summary-content {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
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
}

.checkout-btn:hover:not(:disabled) {
    background: #ff3333;
}

.checkout-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
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

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    background: white;
    border-radius: 10px;
    padding: 0;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #eee;
}

.modal-header h3 {
    margin: 0;
    color: #333;
}

.close-btn {
    background: none;
    border: none;
    color: #666;
    cursor: pointer;
    font-size: 20px;
    transition: color 0.3s;
}

.close-btn:hover {
    color: #ff4d4d;
}

.address-form {
    padding: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    color: #666;
}

.form-group input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 5px;
    transition: border-color 0.3s;
}

.form-group input:focus {
    outline: none;
    border-color: #4d79ff;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}

.cancel-btn {
    background: #f8f9fa;
    border: none;
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
}

.cancel-btn:hover {
    background: #e9ecef;
}

.save-btn {
    background: #ff4d4d;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
}

.save-btn:hover:not(:disabled) {
    background: #ff3333;
}

.save-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
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
</style>