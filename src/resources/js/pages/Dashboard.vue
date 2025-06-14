<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Welcome Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-display font-bold text-gray-900">
          Welcome back, {{ user?.name }}!
        </h1>
        <p class="mt-2 text-gray-600">
          Manage your recipes and cookbooks from your dashboard
        </p>
      </div>

      <!-- Subscription Status Banner -->
      <div v-if="subscriptionTier === 0" class="mb-8 bg-gradient-to-r from-recipe-primary-500 to-recipe-secondary-500 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-semibold mb-2">Upgrade to Premium</h3>
            <p class="text-recipe-primary-100">
              Unlock unlimited recipes, advanced features, and more with a premium subscription.
            </p>
          </div>
          <div class="flex-shrink-0">
            <router-link
              :to="{ name: 'subscription' }"
              class="bg-white text-recipe-primary-600 hover:bg-gray-100 font-medium py-2 px-4 rounded-lg transition-colors"
            >
              Upgrade Now
            </router-link>
          </div>
        </div>
      </div>

      <!-- Stats Overview -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="card">
          <div class="card-body">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-recipe-primary-100 rounded-lg flex items-center justify-center">
                  <BookOpenIcon class="w-6 h-6 text-recipe-primary-600" />
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Recipes</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.totalRecipes }}</p>
                <p v-if="subscriptionLimits.recipes > 0" class="text-xs text-gray-500">
                  {{ subscriptionLimits.recipes - stats.totalRecipes }} remaining
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-recipe-secondary-100 rounded-lg flex items-center justify-center">
                  <DocumentTextIcon class="w-6 h-6 text-recipe-secondary-600" />
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Cookbooks</p>
                <p class="text-2xl font-bold text-gray-900">{{ stats.totalCookbooks }}</p>
                <p v-if="subscriptionLimits.cookbooks > 0" class="text-xs text-gray-500">
                  {{ subscriptionLimits.cookbooks - stats.totalCookbooks }} remaining
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                  <StarIcon class="w-6 h-6 text-green-600" />
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Subscription</p>
                <p class="text-lg font-bold text-gray-900">{{ subscriptionTierName }}</p>
                <p class="text-xs text-gray-500">{{ subscriptionStatusText }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <router-link
            :to="{ name: 'recipe-create' }"
            class="card hover:shadow-lg transition-shadow cursor-pointer"
            :class="{ 'opacity-50 cursor-not-allowed': !canCreateRecipe }"
          >
            <div class="card-body text-center">
              <PlusIcon class="w-8 h-8 text-recipe-primary-600 mx-auto mb-2" />
              <h3 class="font-medium text-gray-900">Add Recipe</h3>
              <p class="text-sm text-gray-600">Create a new recipe</p>
            </div>
          </router-link>

          <router-link
            :to="{ name: 'cookbook-create' }"
            class="card hover:shadow-lg transition-shadow cursor-pointer"
            :class="{ 'opacity-50 cursor-not-allowed': !canCreateCookbook }"
          >
            <div class="card-body text-center">
              <PlusIcon class="w-8 h-8 text-recipe-secondary-600 mx-auto mb-2" />
              <h3 class="font-medium text-gray-900">New Cookbook</h3>
              <p class="text-sm text-gray-600">Start a cookbook</p>
            </div>
          </router-link>

          <router-link
            :to="{ name: 'recipes', query: { type: 'public' } }"
            class="card hover:shadow-lg transition-shadow cursor-pointer"
          >
            <div class="card-body text-center">
              <MagnifyingGlassIcon class="w-8 h-8 text-blue-600 mx-auto mb-2" />
              <h3 class="font-medium text-gray-900">Browse Recipes</h3>
              <p class="text-sm text-gray-600">Discover new recipes</p>
            </div>
          </router-link>

          <router-link
            :to="{ name: 'profile' }"
            class="card hover:shadow-lg transition-shadow cursor-pointer"
          >
            <div class="card-body text-center">
              <UserIcon class="w-8 h-8 text-gray-600 mx-auto mb-2" />
              <h3 class="font-medium text-gray-900">Profile</h3>
              <p class="text-sm text-gray-600">Manage settings</p>
            </div>
          </router-link>
        </div>
      </div>

      <!-- Recent Recipes -->
      <div class="grid lg:grid-cols-2 gap-8">
        <div>
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Recent Recipes</h2>
            <router-link
              :to="{ name: 'recipes' }"
              class="text-recipe-primary-600 hover:text-recipe-primary-700 text-sm font-medium"
            >
              View all
            </router-link>
          </div>

          <div v-if="recentRecipes.length" class="space-y-4">
            <div
              v-for="recipe in recentRecipes"
              :key="recipe._id"
              class="card hover:shadow-lg transition-shadow cursor-pointer"
              @click="$router.push({ name: 'recipe-show', params: { id: recipe._id } })"
            >
              <div class="card-body">
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <h3 class="font-medium text-gray-900">{{ recipe.name }}</h3>
                    <p class="text-sm text-gray-600 mt-1">
                      {{ recipe.servings }} servings
                    </p>
                    <p class="text-xs text-gray-500 mt-2">
                      Created {{ formatDate(recipe.created_at) }}
                    </p>
                  </div>
                  <div class="flex items-center space-x-2">
                    <span
                      v-if="recipe.is_private"
                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600"
                    >
                      Private
                    </span>
                    <ChevronRightIcon class="w-5 h-5 text-gray-400" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="card">
            <div class="card-body text-center py-8">
              <BookOpenIcon class="w-12 h-12 text-gray-400 mx-auto mb-4" />
              <h3 class="text-lg font-medium text-gray-900 mb-2">No recipes yet</h3>
              <p class="text-gray-600 mb-4">Get started by creating your first recipe</p>
              <router-link
                :to="{ name: 'recipe-create' }"
                class="btn-primary"
              >
                Create Recipe
              </router-link>
            </div>
          </div>
        </div>

        <!-- Recent Cookbooks -->
        <div>
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Recent Cookbooks</h2>
            <router-link
              :to="{ name: 'cookbooks' }"
              class="text-recipe-primary-600 hover:text-recipe-primary-700 text-sm font-medium"
            >
              View all
            </router-link>
          </div>

          <div v-if="recentCookbooks.length" class="space-y-4">
            <div
              v-for="cookbook in recentCookbooks"
              :key="cookbook._id"
              class="card hover:shadow-lg transition-shadow cursor-pointer"
              @click="$router.push({ name: 'cookbook-show', params: { id: cookbook._id } })"
            >
              <div class="card-body">
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <h3 class="font-medium text-gray-900">{{ cookbook.name }}</h3>
                    <p class="text-sm text-gray-600 mt-1">
                      {{ cookbook.recipe_ids?.length || 0 }} recipes
                    </p>
                    <p class="text-xs text-gray-500 mt-2">
                      Updated {{ formatDate(cookbook.updated_at) }}
                    </p>
                  </div>
                  <div class="flex items-center space-x-2">
                    <span
                      v-if="cookbook.is_private"
                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600"
                    >
                      Private
                    </span>
                    <ChevronRightIcon class="w-5 h-5 text-gray-400" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="card">
            <div class="card-body text-center py-8">
              <DocumentTextIcon class="w-12 h-12 text-gray-400 mx-auto mb-4" />
              <h3 class="text-lg font-medium text-gray-900 mb-2">No cookbooks yet</h3>
              <p class="text-gray-600 mb-4">Organize your recipes into cookbooks</p>
              <router-link
                :to="{ name: 'cookbook-create' }"
                class="btn-primary"
              >
                Create Cookbook
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRecipeStore } from '../stores/recipes';
import { useCookbookStore } from '../stores/cookbooks';
import {
  BookOpenIcon,
  DocumentTextIcon,
  PlusIcon,
  MagnifyingGlassIcon,
  UserIcon,
  ChevronRightIcon
} from '@heroicons/vue/24/outline';
import { StarIcon } from '@heroicons/vue/24/solid';

export default {
  name: 'Dashboard',
  components: {
    BookOpenIcon,
    DocumentTextIcon,
    PlusIcon,
    MagnifyingGlassIcon,
    UserIcon,
    ChevronRightIcon,
    StarIcon
  },
  setup() {
    const authStore = useAuthStore();
    const recipeStore = useRecipeStore();
    const cookbookStore = useCookbookStore();

    const stats = ref({
      totalRecipes: 0,
      totalCookbooks: 0
    });

    const user = computed(() => authStore.user);
    const subscriptionTier = computed(() => authStore.subscriptionTier);
    const subscriptionLimits = computed(() => authStore.subscriptionLimits);
    
    const subscriptionTierName = computed(() => {
      if (authStore.user?.admin_override) return 'Admin';
      
      const tier = authStore.subscriptionTier;
      switch (tier) {
        case 0: return 'Free';
        case 1: return 'Premium';
        case 2: return 'Professional';
        default: return 'Free';
      }
    });

    const subscriptionStatusText = computed(() => {
      if (authStore.user?.admin_override) return 'All features unlocked';
      
      const tier = authStore.subscriptionTier;
      if (tier === 0) return 'Limited features';
      return 'Active subscription';
    });

    const recentRecipes = computed(() => {
      return recipeStore.recipes.slice(0, 5);
    });

    const recentCookbooks = computed(() => {
      return cookbookStore.cookbooks.slice(0, 5);
    });

    const canCreateRecipe = computed(() => {
      const limits = subscriptionLimits.value;
      return limits.recipes === -1 || stats.value.totalRecipes < limits.recipes;
    });

    const canCreateCookbook = computed(() => {
      const limits = subscriptionLimits.value;
      return limits.cookbooks === -1 || stats.value.totalCookbooks < limits.cookbooks;
    });

    const formatDate = (dateString) => {
      const date = new Date(dateString);
      const now = new Date();
      const diffTime = Math.abs(now - date);
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

      if (diffDays === 1) return 'Yesterday';
      if (diffDays < 7) return `${diffDays} days ago`;
      if (diffDays < 30) return `${Math.ceil(diffDays / 7)} weeks ago`;
      return date.toLocaleDateString();
    };

    const loadDashboardData = async () => {
      // Load recipes and cookbooks
      await Promise.all([
        recipeStore.fetchRecipes(),
        cookbookStore.fetchCookbooks()
      ]);

      // Update stats
      stats.value = {
        totalRecipes: recipeStore.recipes.length,
        totalCookbooks: cookbookStore.cookbooks.length
      };
    };

    onMounted(() => {
      loadDashboardData();
    });

    return {
      user,
      subscriptionTier,
      subscriptionLimits,
      subscriptionTierName,
      subscriptionStatusText,
      stats,
      recentRecipes,
      recentCookbooks,
      canCreateRecipe,
      canCreateCookbook,
      formatDate
    };
  }
};
</script>
