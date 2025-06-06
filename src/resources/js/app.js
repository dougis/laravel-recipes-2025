import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './components/App.vue';

// Import global styles
import '../css/app.css';

// Create the Vue app
const app = createApp(App);

// Use Pinia for state management
app.use(createPinia());

// Use Vue Router
app.use(router);

// Mount the app
app.mount('#app');
