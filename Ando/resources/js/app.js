import './bootstrap';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router.js';
import axios from 'axios';

// Configure axios defaults
axios.defaults.baseURL = 'http://127.0.0.1:8000';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Create Vue app
const app = createApp(App);

// Register router
app.use(router);

// Mount app
app.mount('#app');

// Debug router state
router.isReady().then(() => {
    console.log('Router ready, current route:', router.currentRoute.value);
    console.log('Available routes:', router.getRoutes().map(r => ({
        path: r.path,
        name: r.name,
        component: r.components?.default?.name || 'Unknown'
    })));
}).catch(error => {
    console.error('Router initialization error:', error);
});
