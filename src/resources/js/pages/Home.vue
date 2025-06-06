<template>
  <div class="min-h-screen">
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-recipe-primary-50 to-recipe-secondary-50 py-16 lg:py-24">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
          <h1 class="text-4xl md:text-6xl font-display font-bold text-gray-900 mb-6">
            Your Culinary Journey
            <span class="text-recipe-primary-600">Starts Here</span>
          </h1>
          <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
            Organize, create, and share your favorite recipes. Build beautiful cookbooks 
            and discover new flavors with our modern recipe management platform.
          </p>
          
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <router-link
              v-if="!isAuthenticated"
              :to="{ name: 'register' }"
              class="btn-primary text-lg px-8 py-3"
            >
              Get Started Free
            </router-link>
            <router-link
              v-else
              :to="{ name: 'dashboard' }"
              class="btn-primary text-lg px-8 py-3"
            >
              Go to Dashboard
            </router-link>
            
            <router-link
              :to="{ name: 'recipes', query: { type: 'public' } }"
              class="btn-outline text-lg px-8 py-3"
            >
              Browse Recipes
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-display font-bold text-gray-900 mb-4">
            Everything You Need to Manage Your Recipes
          </h2>
          <p class="text-lg text-gray-600">
            From simple note-taking to professional cookbook creation
          </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div class="text-center">
            <div class="w-16 h-16 bg-recipe-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <BookOpenIcon class="w-8 h-8 text-recipe-primary-600" />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Recipe Management</h3>
            <p class="text-gray-600">
              Store and organize all your recipes in one place with rich text formatting 
              and ingredient tracking.
            </p>
          </div>

          <div class="text-center">
            <div class="w-16 h-16 bg-recipe-secondary-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <MagnifyingGlassIcon class="w-8 h-8 text-recipe-secondary-600" />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Smart Search</h3>
            <p class="text-gray-600">
              Find recipes instantly with fuzzy search, filters, and intelligent 
              ranking based on your preferences.
            </p>
          </div>

          <div class="text-center">
            <div class="w-16 h-16 bg-recipe-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <DocumentTextIcon class="w-8 h-8 text-recipe-primary-600" />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Cookbook Creation</h3>
            <p class="text-gray-600">
              Compile your favorite recipes into beautiful cookbooks with 
              professional printing and export options.
            </p>
          </div>

          <div class="text-center">
            <div class="w-16 h-16 bg-recipe-secondary-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <ShareIcon class="w-8 h-8 text-recipe-secondary-600" />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Easy Sharing</h3>
            <p class="text-gray-600">
              Share your favorite recipes and cookbooks with friends and family 
              or keep them private.
            </p>
          </div>

          <div class="text-center">
            <div class="w-16 h-16 bg-recipe-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <DevicePhoneMobileIcon class="w-8 h-8 text-recipe-primary-600" />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Mobile First</h3>
            <p class="text-gray-600">
              Access your recipes anywhere with our responsive design 
              optimized for mobile devices.
            </p>
          </div>

          <div class="text-center">
            <div class="w-16 h-16 bg-recipe-secondary-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <ChartBarIcon class="w-8 h-8 text-recipe-secondary-600" />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Nutrition Tracking</h3>
            <p class="text-gray-600">
              Track nutritional information for your recipes and maintain 
              a healthy cooking lifestyle.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Subscription Tiers Section -->
    <section class="py-16 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-display font-bold text-gray-900 mb-4">
            Choose Your Plan
          </h2>
          <p class="text-lg text-gray-600">
            Start free and upgrade as you grow your recipe collection
          </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
          <!-- Free Tier -->
          <div class="card">
            <div class="card-header text-center">
              <h3 class="text-xl font-semibold text-gray-900">Free</h3>
              <p class="text-3xl font-bold text-gray-900 mt-2">$0<span class="text-base font-normal text-gray-600">/month</span></p>
            </div>
            <div class="card-body">
              <ul class="space-y-3">
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Up to 25 recipes</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>1 cookbook</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Basic recipe management</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Print individual recipes</span>
                </li>
              </ul>
              <router-link
                v-if="!isAuthenticated"
                :to="{ name: 'register' }"
                class="btn-outline w-full mt-6"
              >
                Get Started
              </router-link>
            </div>
          </div>

          <!-- Premium Tier -->
          <div class="card border-recipe-primary-500 border-2 relative">
            <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
              <span class="bg-recipe-primary-600 text-white px-4 py-1 rounded-full text-sm font-medium">
                Most Popular
              </span>
            </div>
            <div class="card-header text-center">
              <h3 class="text-xl font-semibold text-gray-900">Premium</h3>
              <p class="text-3xl font-bold text-gray-900 mt-2">$9<span class="text-base font-normal text-gray-600">/month</span></p>
            </div>
            <div class="card-body">
              <ul class="space-y-3">
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Unlimited recipes</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Up to 10 cookbooks</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Advanced search & filtering</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Nutritional information</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Print cookbooks</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Export to multiple formats</span>
                </li>
              </ul>
              <router-link
                v-if="!isAuthenticated"
                :to="{ name: 'register' }"
                class="btn-primary w-full mt-6"
              >
                Start Premium
              </router-link>
            </div>
          </div>

          <!-- Professional Tier -->
          <div class="card">
            <div class="card-header text-center">
              <h3 class="text-xl font-semibold text-gray-900">Professional</h3>
              <p class="text-3xl font-bold text-gray-900 mt-2">$19<span class="text-base font-normal text-gray-600">/month</span></p>
            </div>
            <div class="card-body">
              <ul class="space-y-3">
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Everything in Premium</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Unlimited cookbooks</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Privacy controls</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Recipe scaling</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Meal planning</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Custom templates</span>
                </li>
              </ul>
              <router-link
                v-if="!isAuthenticated"
                :to="{ name: 'register' }"
                class="btn-primary w-full mt-6"
              >
                Go Professional
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Recent Public Content -->
    <section class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-display font-bold text-gray-900 mb-4">
            Discover Amazing Recipes
          </h2>
          <p class="text-lg text-gray-600">
            Browse recipes shared by our community
          </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8" v-if="featuredRecipes.length">
          <div
            v-for="recipe in featuredRecipes"
            :key="recipe._id"
            class="recipe-card"
            @click="$router.push({ name: 'recipe-show', params: { id: recipe._id } })"
          >
            <div class="card-body">
              <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ recipe.name }}</h3>
              <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                {{ recipe.instructions?.substring(0, 120) }}...
              </p>
              <div class="flex items-center text-sm text-gray-500">
                <ClockIcon class="w-4 h-4 mr-1" />
                <span>{{ recipe.servings }} servings</span>
              </div>
            </div>
          </div>
        </div>

        <div class="text-center mt-8">
          <router-link
            :to="{ name: 'recipes', query: { type: 'public' } }"
            class="btn-primary"
          >
            View All Recipes
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRecipeStore } from '../stores/recipes';
import {
  BookOpenIcon,
  MagnifyingGlassIcon,
  DocumentTextIcon,
  ShareIcon,
  DevicePhoneMobileIcon,
  ChartBarIcon,
  CheckIcon,
  ClockIcon
} from '@heroicons/vue/24/outline';

export default {
  name: 'Home',
  components: {
    BookOpenIcon,
    MagnifyingGlassIcon,
    DocumentTextIcon,
    ShareIcon,
    DevicePhoneMobileIcon,
    ChartBarIcon,
    CheckIcon,
    ClockIcon
  },
  setup() {
    const authStore = useAuthStore();
    const recipeStore = useRecipeStore();
    const featuredRecipes = ref([]);

    const isAuthenticated = computed(() => authStore.isAuthenticated);

    const loadFeaturedRecipes = async () => {
      const result = await recipeStore.fetchPublicRecipes();
      if (result.success) {
        // Show first 6 recipes as featured
        featuredRecipes.value = recipeStore.publicRecipes.slice(0, 6);
      }
    };

    onMounted(() => {
      loadFeaturedRecipes();
    });

    return {
      isAuthenticated,
      featuredRecipes
    };
  }
};
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
