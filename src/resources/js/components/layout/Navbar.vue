<template>
  <nav class="navbar fixed top-0 left-0 right-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <!-- Logo and Brand -->
        <div class="flex items-center">
          <router-link :to="{ name: 'home' }" class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-recipe-primary-600 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-lg">R</span>
            </div>
            <span class="text-xl font-display font-semibold text-gray-900">Laravel Recipes</span>
          </router-link>
        </div>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center space-x-8">
          <router-link
            :to="{ name: 'home' }"
            class="text-gray-700 hover:text-recipe-primary-600 px-3 py-2 text-sm font-medium transition-colors"
            :class="{ 'text-recipe-primary-600 font-semibold': $route.name === 'home' }"
          >
            Home
          </router-link>
          
          <router-link
            :to="{ name: 'recipes', query: { type: 'public' } }"
            class="text-gray-700 hover:text-recipe-primary-600 px-3 py-2 text-sm font-medium transition-colors"
          >
            Browse Recipes
          </router-link>
          
          <router-link
            :to="{ name: 'cookbooks', query: { type: 'public' } }"
            class="text-gray-700 hover:text-recipe-primary-600 px-3 py-2 text-sm font-medium transition-colors"
          >
            Browse Cookbooks
          </router-link>

          <!-- Authenticated User Navigation -->
          <template v-if="isAuthenticated">
            <router-link
              :to="{ name: 'dashboard' }"
              class="text-gray-700 hover:text-recipe-primary-600 px-3 py-2 text-sm font-medium transition-colors"
              :class="{ 'text-recipe-primary-600 font-semibold': $route.name === 'dashboard' }"
            >
              Dashboard
            </router-link>
            
            <router-link
              :to="{ name: 'recipes' }"
              class="text-gray-700 hover:text-recipe-primary-600 px-3 py-2 text-sm font-medium transition-colors"
              :class="{ 'text-recipe-primary-600 font-semibold': $route.name?.startsWith('recipe') }"
            >
              My Recipes
            </router-link>
            
            <router-link
              :to="{ name: 'cookbooks' }"
              class="text-gray-700 hover:text-recipe-primary-600 px-3 py-2 text-sm font-medium transition-colors"
              :class="{ 'text-recipe-primary-600 font-semibold': $route.name?.startsWith('cookbook') }"
            >
              My Cookbooks
            </router-link>

            <!-- Admin Navigation -->
            <router-link
              v-if="isAdmin"
              :to="{ name: 'admin-dashboard' }"
              class="text-gray-700 hover:text-recipe-primary-600 px-3 py-2 text-sm font-medium transition-colors"
              :class="{ 'text-recipe-primary-600 font-semibold': $route.name?.startsWith('admin') }"
            >
              Admin
            </router-link>

            <!-- User Dropdown -->
            <div class="relative" ref="userDropdownRef">
              <button
                @click="showUserDropdown = !showUserDropdown"
                class="flex items-center space-x-2 text-gray-700 hover:text-recipe-primary-600 px-3 py-2 text-sm font-medium transition-colors"
              >
                <div class="w-8 h-8 bg-recipe-primary-600 rounded-full flex items-center justify-center">
                  <span class="text-white text-sm font-medium">
                    {{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}
                  </span>
                </div>
                <ChevronDownIcon class="w-4 h-4" />
              </button>

              <!-- User Dropdown Menu -->
              <div
                v-show="showUserDropdown"
                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 py-1 z-50"
              >
                <div class="px-4 py-2 border-b border-gray-200">
                  <p class="text-sm font-medium text-gray-900">{{ user?.name }}</p>
                  <p class="text-sm text-gray-500">{{ user?.email }}</p>
                  <p class="text-xs text-recipe-primary-600 mt-1">
                    {{ subscriptionTierName }}
                  </p>
                </div>
                
                <router-link
                  :to="{ name: 'profile' }"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  @click="showUserDropdown = false"
                >
                  Profile Settings
                </router-link>
                
                <router-link
                  :to="{ name: 'subscription' }"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  @click="showUserDropdown = false"
                >
                  Subscription
                </router-link>
                
                <button
                  @click="handleLogout"
                  class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                >
                  Sign Out
                </button>
              </div>
            </div>
          </template>

          <!-- Guest Navigation -->
          <template v-else>
            <router-link
              :to="{ name: 'login' }"
              class="text-gray-700 hover:text-recipe-primary-600 px-3 py-2 text-sm font-medium transition-colors"
            >
              Sign In
            </router-link>
            
            <router-link
              :to="{ name: 'register' }"
              class="btn-primary text-sm"
            >
              Get Started
            </router-link>
          </template>
        </div>

        <!-- Mobile menu button -->
        <div class="md:hidden flex items-center">
          <button
            @click="showMobileMenu = !showMobileMenu"
            class="text-gray-700 hover:text-recipe-primary-600 p-2"
          >
            <Bars3Icon v-if="!showMobileMenu" class="w-6 h-6" />
            <XMarkIcon v-else class="w-6 h-6" />
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Navigation Menu -->
    <div v-show="showMobileMenu" class="md:hidden bg-white border-t border-gray-200">
      <div class="px-2 pt-2 pb-3 space-y-1">
        <router-link
          :to="{ name: 'home' }"
          class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-recipe-primary-600 hover:bg-gray-50 rounded-md"
          @click="showMobileMenu = false"
        >
          Home
        </router-link>
        
        <router-link
          :to="{ name: 'recipes', query: { type: 'public' } }"
          class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-recipe-primary-600 hover:bg-gray-50 rounded-md"
          @click="showMobileMenu = false"
        >
          Browse Recipes
        </router-link>
        
        <router-link
          :to="{ name: 'cookbooks', query: { type: 'public' } }"
          class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-recipe-primary-600 hover:bg-gray-50 rounded-md"
          @click="showMobileMenu = false"
        >
          Browse Cookbooks
        </router-link>

        <template v-if="isAuthenticated">
          <div class="border-t border-gray-200 pt-4 mt-4">
            <div class="px-3 py-2">
              <p class="text-base font-medium text-gray-900">{{ user?.name }}</p>
              <p class="text-sm text-gray-500">{{ subscriptionTierName }}</p>
            </div>
            
            <router-link
              :to="{ name: 'dashboard' }"
              class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-recipe-primary-600 hover:bg-gray-50 rounded-md"
              @click="showMobileMenu = false"
            >
              Dashboard
            </router-link>
            
            <router-link
              :to="{ name: 'recipes' }"
              class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-recipe-primary-600 hover:bg-gray-50 rounded-md"
              @click="showMobileMenu = false"
            >
              My Recipes
            </router-link>
            
            <router-link
              :to="{ name: 'cookbooks' }"
              class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-recipe-primary-600 hover:bg-gray-50 rounded-md"
              @click="showMobileMenu = false"
            >
              My Cookbooks
            </router-link>

            <router-link
              v-if="isAdmin"
              :to="{ name: 'admin-dashboard' }"
              class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-recipe-primary-600 hover:bg-gray-50 rounded-md"
              @click="showMobileMenu = false"
            >
              Admin
            </router-link>
            
            <router-link
              :to="{ name: 'profile' }"
              class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-recipe-primary-600 hover:bg-gray-50 rounded-md"
              @click="showMobileMenu = false"
            >
              Profile Settings
            </router-link>
            
            <router-link
              :to="{ name: 'subscription' }"
              class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-recipe-primary-600 hover:bg-gray-50 rounded-md"
              @click="showMobileMenu = false"
            >
              Subscription
            </router-link>
            
            <button
              @click="handleLogout"
              class="block w-full text-left px-3 py-2 text-base font-medium text-gray-700 hover:text-recipe-primary-600 hover:bg-gray-50 rounded-md"
            >
              Sign Out
            </button>
          </div>
        </template>

        <template v-else>
          <div class="border-t border-gray-200 pt-4 mt-4">
            <router-link
              :to="{ name: 'login' }"
              class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-recipe-primary-600 hover:bg-gray-50 rounded-md"
              @click="showMobileMenu = false"
            >
              Sign In
            </router-link>
            
            <router-link
              :to="{ name: 'register' }"
              class="block px-3 py-2 text-base font-medium text-recipe-primary-600 hover:text-recipe-primary-700 hover:bg-gray-50 rounded-md"
              @click="showMobileMenu = false"
            >
              Get Started
            </router-link>
          </div>
        </template>
      </div>
    </div>
  </nav>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { ChevronDownIcon, Bars3Icon, XMarkIcon } from '@heroicons/vue/24/outline';

export default {
  name: 'Navbar',
  components: {
    ChevronDownIcon,
    Bars3Icon,
    XMarkIcon
  },
  setup() {
    const router = useRouter();
    const authStore = useAuthStore();
    const showUserDropdown = ref(false);
    const showMobileMenu = ref(false);
    const userDropdownRef = ref(null);

    const isAuthenticated = computed(() => authStore.isAuthenticated);
    const isAdmin = computed(() => authStore.isAdmin);
    const user = computed(() => authStore.user);
    
    const subscriptionTierName = computed(() => {
      if (authStore.user?.admin_override) return 'Admin Override';
      
      const tier = authStore.subscriptionTier;
      switch (tier) {
        case 0: return 'Free Tier';
        case 1: return 'Premium Tier';
        case 2: return 'Professional Tier';
        default: return 'Free Tier';
      }
    });

    const handleLogout = async () => {
      await authStore.logout();
      showUserDropdown.value = false;
      showMobileMenu.value = false;
      router.push({ name: 'home' });
    };

    // Close dropdowns when clicking outside
    const handleClickOutside = (event) => {
      if (userDropdownRef.value && !userDropdownRef.value.contains(event.target)) {
        showUserDropdown.value = false;
      }
    };

    onMounted(() => {
      document.addEventListener('click', handleClickOutside);
    });

    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside);
    });

    return {
      showUserDropdown,
      showMobileMenu,
      userDropdownRef,
      isAuthenticated,
      isAdmin,
      user,
      subscriptionTierName,
      handleLogout
    };
  }
};
</script>
