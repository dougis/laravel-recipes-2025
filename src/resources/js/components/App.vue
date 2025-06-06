<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <Navbar v-if="!isAuthPage" />
    
    <!-- Main Content -->
    <main :class="{ 'pt-16': !isAuthPage }">
      <router-view />
    </main>
    
    <!-- Toast Notifications -->
    <ToastContainer />
    
    <!-- Loading Overlay -->
    <LoadingOverlay v-if="globalLoading" />
  </div>
</template>

<script>
import { computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import Navbar from './layout/Navbar.vue';
import ToastContainer from './ui/ToastContainer.vue';
import LoadingOverlay from './ui/LoadingOverlay.vue';

export default {
  name: 'App',
  components: {
    Navbar,
    ToastContainer,
    LoadingOverlay
  },
  setup() {
    const route = useRoute();
    const authStore = useAuthStore();
    
    const isAuthPage = computed(() => {
      const authPages = ['login', 'register', 'forgot-password', 'reset-password'];
      return authPages.includes(route.name);
    });
    
    const globalLoading = computed(() => authStore.loading);
    
    onMounted(() => {
      // Initialize auth store
      authStore.initialize();
    });
    
    // Set page title based on route
    watch(() => route.name, (newRouteName) => {
      const titles = {
        'home': 'Laravel Recipes 2025',
        'login': 'Login - Laravel Recipes 2025',
        'register': 'Register - Laravel Recipes 2025',
        'dashboard': 'Dashboard - Laravel Recipes 2025',
        'recipes': 'Recipes - Laravel Recipes 2025',
        'cookbooks': 'Cookbooks - Laravel Recipes 2025',
        'profile': 'Profile - Laravel Recipes 2025',
        'subscription': 'Subscription - Laravel Recipes 2025'
      };
      
      document.title = titles[newRouteName] || 'Laravel Recipes 2025';
    }, { immediate: true });
    
    return {
      isAuthPage,
      globalLoading
    };
  }
};
</script>
