import { createRouter, createWebHistory } from 'vue-router';

// Admin Components
import AdminLogin from '../components/admin/login.vue';
import AdminDashboard from '../components/admin/dashboard.vue';
import AdminHome from '../components/admin/home.vue';
import AdminOrders from '../components/admin/order.vue';
import AdminProducts from '../components/admin/product.vue';
import AdminCustomers from '../components/admin/customer.vue';
import AdminRefunds from '../components/admin/refund.vue';

// User Components
import UserDashboard from '../components/user/userdashboard.vue';

// Debug available routes
const logRoutes = (routes) => {
    console.log('Available routes:', routes.map(route => ({
        name: route.name,
        path: route.path,
        component: route.component?.name || 'Unknown'
    })));
};

// Define routes
const routes = [
    {
        path: '/',
        redirect: { name: 'user-dashboard' }
    },
    {
        path: '/login',
        name: 'login',
        component: AdminLogin
    },
    {
        path: '/user/dashboard',
        name: 'user-dashboard',
        component: UserDashboard,
        meta: { requiresUserAuth: true }
    },
    {
        path: '/admin',
        component: AdminDashboard,
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
                component: AdminOrders
            },
            {
                path: 'products',
                name: 'admin-products',
                component: AdminProducts
            },
            {
                path: 'customers',
                name: 'admin-customers',
                component: AdminCustomers
            },
            {
                path: 'refunds',
                name: 'admin-refunds',
                component: AdminRefunds
            }
        ]
    }
];

// Create router instance
const router = createRouter({
    history: createWebHistory(),
    routes
});

// Debug router configuration
logRoutes(routes);

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

export default router;