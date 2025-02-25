import { createRouter, createWebHistory } from 'vue-router';
import Index from './components/index.vue';
import Register from './components/register.vue';
import Checkout from './components/user/checkout.vue';
import Contact from './components/user/contact.vue';
import Trends from './components/user/trends.vue';
import Editprofile from './components/user/editprofile.vue';
import Home from './components/user/home.vue';
import News from './components/user/news.vue'; 
import Orders from './components/user/orders.vue';
import Purchase from './components/user/purchase.vue';
import UserDashboard from './components/user/userdashboard.vue';
import UserLayout from './components/user/UserLayout.vue';
import Customer from './components/admin/customer.vue';
import Dashboard from './components/admin/dashboard.vue';
import Order from './components/admin/order.vue';
import Product from './components/admin/product.vue';
import Refund from './components/admin/refund.vue';
import Login from './components/admin/login.vue';
import AdminHome from './components/admin/home.vue';
import OrderDetails from './components/user/order-details.vue';

const routes = [
    {
        path: '/',
        component: Index,
        name: 'index'
    },
    // Auth routes
    {
        path: '/login',
        component: Login,
        name: 'login'
    },
    {
        path: '/register',
        component: Register,
        name: 'register'
    },
    // User routes
    {
        path: '/user',
        component: UserLayout,
        children: [
            { 
                path: '', 
                redirect: { name: 'user-dashboard' } 
            },
            { 
                path: 'dashboard', 
                component: UserDashboard, 
                name: 'user-dashboard',
                meta: { requiresUserAuth: true }
            },
            { 
                path: 'checkout', 
                component: Checkout, 
                name: 'checkout',
                meta: { requiresUserAuth: true }
            },
            { 
                path: 'contact', 
                component: Contact, 
                name: 'contact',
                meta: { requiresUserAuth: true }
            },
            { 
                path: 'trends', 
                component: Trends, 
                name: 'user-trends',
                meta: { requiresUserAuth: true }
            },
            { 
                path: 'profile/edit', 
                component: Editprofile, 
                name: 'edit-profile',
                meta: { requiresUserAuth: true }
            },
            { 
                path: 'home', 
                component: Home, 
                name: 'home' 
            },
            { 
                path: 'news', 
                component: News, 
                name: 'news',
                meta: { requiresUserAuth: true }
            },
            { 
                path: 'orders', 
                component: Orders, 
                name: 'user-orders',
                meta: { requiresUserAuth: true }
            },
            { 
                path: 'orders/:id', 
                component: OrderDetails, 
                name: 'order-details',
                meta: { requiresUserAuth: true }
            },
            { 
                path: 'purchase', 
                component: Purchase, 
                name: 'purchase',
                meta: { requiresUserAuth: true }
            },
            { 
                path: 'cart', 
                component: () => import('./components/user/cart.vue'), 
                name: 'user-cart',
                meta: { requiresUserAuth: true }
            },
            { 
                path: 'wishlist', 
                component: () => import('./components/user/wishlist.vue'),
                name: 'user-wishlist',
                meta: { requiresUserAuth: true }
            }
        ]
    },
    // Admin routes
    {
        path: '/admin',
        component: Dashboard,
        meta: { requiresAdminAuth: true },
        children: [
            {
                path: '',
                name: 'admin-dashboard',
                component: AdminHome
            },
            {
                path: 'orders',
                name: 'admin-orders',
                component: Order
            },
            {
                path: 'products',
                name: 'admin-products',
                component: Product
            },
            {
                path: 'customers',
                name: 'admin-customers',
                component: Customer
            },
            {
                path: 'refunds',
                name: 'admin-refunds',
                component: Refund
            }
        ]
    },
    // Catch-all route for 404
    {
        path: '/:pathMatch(.*)*',
        redirect: { name: 'index' }
    }
];

// Create router instance
const router = createRouter({
    history: createWebHistory(),
    routes
});

// Navigation guard
router.beforeEach((to, from, next) => {
    const requiresAdminAuth = to.matched.some(record => record.meta.requiresAdminAuth);
    const requiresUserAuth = to.matched.some(record => record.meta.requiresUserAuth);
    const adminToken = localStorage.getItem('admin_token');
    const userToken = localStorage.getItem('user_token');

    // Debug navigation
    console.log('Navigation Debug:', {
        to: to.name,
        requiresAdminAuth,
        hasAdminToken: !!adminToken,
        matched: to.matched.map(m => m.name)
    });

    // If trying to access admin protected route without admin auth
    if (requiresAdminAuth && !adminToken) {
        next({ name: 'login' });
        return;
    }

    // If trying to access user protected route without user auth
    if (requiresUserAuth && !userToken) {
        next({ name: 'login' });
        return;
    }

    // If trying to access login page while already authenticated
    if (to.name === 'login' && (adminToken || userToken)) {
        next(adminToken ? { name: 'admin-dashboard' } : { name: 'user-dashboard' });
        return;
    }

    next();
});

// Debug route registration
router.isReady().then(() => {
    console.log('Router ready, available routes:', router.getRoutes().map(r => r.name));
});

export default router;
