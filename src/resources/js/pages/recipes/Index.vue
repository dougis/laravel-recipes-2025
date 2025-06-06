<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-display font-bold text-gray-900">
              {{ isPublicView ? 'Browse Recipes' : 'My Recipes' }}
            </h1>
            <p class="mt-2 text-gray-600">
              {{ isPublicView ? 'Discover amazing recipes from our community' : 'Manage and organize your recipe collection' }}
            </p>
          </div>
          
          <div v-if="!isPublicView" class="flex items-center space-x-4">
            <router-link
              :to="{ name: 'recipe-create' }"
              class="btn-primary"
              :class="{ 'opacity-50 cursor-not-allowed': !canCreateRecipe }"
            >
              <PlusIcon class="w-5 h-5 mr-2" />
              Add Recipe
            </router-link>
          </div>
        </div>
      </div>

      <!-- Search and Filters -->
      <div class="mb-8 space-y-4">
        <div class="flex flex-col md:flex-row gap-4">
          <!-- Search Bar -->
          <div class="flex-1">
            <div class="relative">
              <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search recipes..."
                class="form-input pl-10 w-full"
                @input="handleSearch"
              />
            </div>
          </div>

          <!-- View Toggle -->
          <div class="flex items-center space-x-2">
            <button
              @click="viewMode = 'grid'"
              :class="[
                'p-2 rounded-md',
                viewMode === 'grid' ? 'bg-recipe-primary-100 text-recipe-primary-600' : 'text-gray-400 hover:text-gray-600'
              ]"
            >
              <Squares2X2Icon class="w-5 h-5" />
            </button>
            <button
              @click="viewMode = 'list'"
              :class="[
                'p-2 rounded-md',
                viewMode === 'list' ? 'bg-recipe-primary-100 text-recipe-primary-600' : 'text-gray-400 hover:text-gray-600'
              ]"
            >
              <Bars3Icon class="w-5 h-5" />
            </button>
          </div>

          <!-- Filters Toggle -->
          <button
            @click="showFilters = !showFilters"
            class="btn-outline flex items-center"
          >
            <FunnelIcon class="w-5 h-5 mr-2" />
            Filters
            <span v-if="activeFiltersCount" class="ml-2 bg-recipe-primary-600 text-white text-xs rounded-full px-2 py-1">
              {{ activeFiltersCount }}
            </span>
          </button>
        </div>

        <!-- Filters Panel -->
        <div v-show="showFilters" class="card">
          <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <!-- Classification Filter -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Classification</label>
                <select v-model="filters.classification" class="form-select">
                  <option value="">All Classifications</option>
                  <option v-for="classification in metadata.classifications" :key="classification._id" :value="classification._id">
                    {{ classification.name }}
                  </option>
                </select>
              </div>

              <!-- Meal Filter -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Meal Type</label>
                <select v-model="filters.meal" class="form-select">
                  <option value="">All Meals</option>
                  <option v-for="meal in metadata.meals" :key="meal._id" :value="meal._id">
                    {{ meal.name }}
                  </option>
                </select>
              </div>

              <!-- Course Filter -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                <select v-model="filters.course" class="form-select">
                  <option value="">All Courses</option>
                  <option v-for="course in metadata.courses" :key="course._id" :value="course._id">
                    {{ course.name }}
                  </option>
                </select>
              </div>

              <!-- Preparation Filter -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Preparation</label>
                <select v-model="filters.preparation" class="form-select">
                  <option value="">All Preparations</option>
                  <option v-for="preparation in metadata.preparations" :key="preparation._id" :value="preparation._id">
                    {{ preparation.name }}
                  </option>
                </select>
              </div>
            </div>

            <div class="mt-4 flex items-center justify-between">
              <button
                @click="clearFilters"
                class="text-sm text-gray-600 hover:text-gray-800"
              >
                Clear all filters
              </button>
              
              <button
                @click="applyFilters"
                class="btn-primary"
              >
                Apply Filters
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Results Info -->
      <div class="mb-6 flex items-center justify-between">
        <p class="text-sm text-gray-600">
          {{ displayedRecipes.length }} {{ displayedRecipes.length === 1 ? 'recipe' : 'recipes' }} found
        </p>
        
        <div class="flex items-center space-x-4">
          <!-- Sort Options -->
          <select v-model="sortBy" @change="sortRecipes" class="form-select text-sm">
            <option value="created_at">Recently Added</option>
            <option value="name">Name (A-Z)</option>
            <option value="servings">Servings</option>
          </select>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-12">
        <div class="loading-spinner"></div>
      </div>

      <!-- Recipes Grid/List -->
      <div v-else-if="displayedRecipes.length">
        <!-- Grid View -->
        <div v-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <RecipeCard
            v-for="recipe in displayedRecipes"
            :key="recipe._id"
            :recipe="recipe"
            :show-privacy="!isPublicView"
            @edit="editRecipe"
            @delete="deleteRecipe"
            @toggle-privacy="toggleRecipePrivacy"
          />
        </div>

        <!-- List View -->
        <div v-else class="space-y-4">
          <RecipeListItem
            v-for="recipe in displayedRecipes"
            :key="recipe._id"
            :recipe="recipe"
            :show-privacy="!isPublicView"
            @edit="editRecipe"
            @delete="deleteRecipe"
            @toggle-privacy="toggleRecipePrivacy"
          />
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12">
        <BookOpenIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">
          {{ isPublicView ? 'No public recipes found' : 'No recipes found' }}
        </h3>
        <p class="text-gray-600 mb-6">
          {{ emptyStateMessage }}
        </p>
        <router-link
          v-if="!isPublicView"
          :to="{ name: 'recipe-create' }"
          class="btn-primary"
        >
          Create Your First Recipe
        </router-link>
      </div>

      <!-- Subscription Limit Warning -->
      <div v-if="!isPublicView && !canCreateRecipe && !authStore.user?.admin_override" class="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex">
          <ExclamationTriangleIcon class="h-5 w-5 text-yellow-400 mt-0.5" />
          <div class="ml-3">
            <h3 class="text-sm font-medium text-yellow-800">
              Recipe limit reached
            </h3>
            <div class="mt-2 text-sm text-yellow-700">
              <p>
                You've reached your limit of {{ subscriptionLimits.recipes }} recipes. 
                <router-link :to="{ name: 'subscription' }" class="font-medium underline">
                  Upgrade your subscription
                </router-link> 
                to create unlimited recipes.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
      v-if="recipeToDelete"
      :is-open="!!recipeToDelete"
      title="Delete Recipe"
      :message="`Are you sure you want to delete '${recipeToDelete.name}'? This action cannot be undone.`"
      confirm-text="Delete"
      confirm-class="bg-red-600 hover:bg-red-700"
      @confirm="confirmDeleteRecipe"
      @cancel="recipeToDelete = null"
    />
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useRecipeStore } from '../../stores/recipes';
import {
  PlusIcon,
  MagnifyingGlassIcon,
  FunnelIcon,
  Squares2X2Icon,
  Bars3Icon,
  BookOpenIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';
import RecipeCard from '../../components/recipes/RecipeCard.vue';
import RecipeListItem from '../../components/recipes/RecipeListItem.vue';
import ConfirmationModal from '../../components/ui/ConfirmationModal.vue';

export default {
  name: 'RecipesIndex',
  components: {
    PlusIcon,
    MagnifyingGlassIcon,
    FunnelIcon,
    Squares2X2Icon,
    Bars3Icon,
    BookOpenIcon,
    ExclamationTriangleIcon,
    RecipeCard,
    RecipeListItem,
    ConfirmationModal
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const authStore = useAuthStore();
    const recipeStore = useRecipeStore();

    // Reactive data
    const searchQuery = ref('');
    const showFilters = ref(false);
    const viewMode = ref('grid');
    const sortBy = ref('created_at');
    const recipeToDelete = ref(null);

    const filters = ref({
      classification: '',
      meal: '',
      course: '',
      preparation: ''
    });

    // Computed properties
    const isPublicView = computed(() => route.query.type === 'public');
    const loading = computed(() => recipeStore.loading);
    const metadata = computed(() => recipeStore.metadata);
    const subscriptionLimits = computed(() => authStore.subscriptionLimits);

    const allRecipes = computed(() => {
      return isPublicView.value ? recipeStore.publicRecipes : recipeStore.recipes;
    });

    const displayedRecipes = computed(() => {
      let recipes = [...allRecipes.value];

      // Apply search
      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        recipes = recipes.filter(recipe =>
          recipe.name.toLowerCase().includes(query) ||
          recipe.ingredients?.toLowerCase().includes(query) ||
          recipe.instructions?.toLowerCase().includes(query)
        );
      }

      // Apply filters
      if (filters.value.classification) {
        recipes = recipes.filter(recipe => recipe.classification_id === filters.value.classification);
      }
      if (filters.value.meal) {
        recipes = recipes.filter(recipe => recipe.meal_ids?.includes(filters.value.meal));
      }
      if (filters.value.course) {
        recipes = recipes.filter(recipe => recipe.course_ids?.includes(filters.value.course));
      }
      if (filters.value.preparation) {
        recipes = recipes.filter(recipe => recipe.preparation_ids?.includes(filters.value.preparation));
      }

      // Apply sorting
      recipes.sort((a, b) => {
        switch (sortBy.value) {
          case 'name':
            return a.name.localeCompare(b.name);
          case 'servings':
            return (b.servings || 0) - (a.servings || 0);
          case 'created_at':
          default:
            return new Date(b.created_at) - new Date(a.created_at);
        }
      });

      return recipes;
    });

    const activeFiltersCount = computed(() => {
      return Object.values(filters.value).filter(value => value !== '').length;
    });

    const canCreateRecipe = computed(() => {
      const limits = subscriptionLimits.value;
      const currentCount = recipeStore.recipes.length;
      return limits.recipes === -1 || currentCount < limits.recipes;
    });

    const emptyStateMessage = computed(() => {
      if (isPublicView.value) {
        return searchQuery.value || activeFiltersCount.value > 0
          ? 'Try adjusting your search or filters to find recipes.'
          : 'No public recipes have been shared yet.';
      } else {
        return searchQuery.value || activeFiltersCount.value > 0
          ? 'No recipes match your search criteria. Try adjusting your search or filters.'
          : 'Get started by creating your first recipe.';
      }
    });

    // Methods
    const loadRecipes = async () => {
      if (isPublicView.value) {
        await recipeStore.fetchPublicRecipes();
      } else {
        await recipeStore.fetchRecipes();
      }
    };

    const handleSearch = () => {
      // Debounced search could be implemented here
    };

    const applyFilters = () => {
      // Filters are applied reactively through computed property
      showFilters.value = false;
    };

    const clearFilters = () => {
      filters.value = {
        classification: '',
        meal: '',
        course: '',
        preparation: ''
      };
      searchQuery.value = '';
    };

    const sortRecipes = () => {
      // Sorting is applied reactively through computed property
    };

    const editRecipe = (recipe) => {
      router.push({ name: 'recipe-edit', params: { id: recipe._id } });
    };

    const deleteRecipe = (recipe) => {
      recipeToDelete.value = recipe;
    };

    const confirmDeleteRecipe = async () => {
      if (recipeToDelete.value) {
        const result = await recipeStore.deleteRecipe(recipeToDelete.value._id);
        if (result.success) {
          window.$toast?.success('Recipe deleted', 'The recipe has been successfully deleted.');
        } else {
          window.$toast?.error('Delete failed', result.message);
        }
        recipeToDelete.value = null;
      }
    };

    const toggleRecipePrivacy = async (recipe) => {
      const result = await recipeStore.toggleRecipePrivacy(recipe._id);
      if (result.success) {
        const status = result.recipe.is_private ? 'private' : 'public';
        window.$toast?.success('Privacy updated', `Recipe is now ${status}.`);
      } else {
        window.$toast?.error('Update failed', result.message);
      }
    };

    // Watchers
    watch(() => route.query.type, () => {
      loadRecipes();
    });

    // Lifecycle
    onMounted(async () => {
      await Promise.all([
        loadRecipes(),
        recipeStore.loadMetadata()
      ]);
    });

    return {
      // Data
      searchQuery,
      showFilters,
      viewMode,
      sortBy,
      filters,
      recipeToDelete,
      
      // Computed
      isPublicView,
      loading,
      metadata,
      displayedRecipes,
      activeFiltersCount,
      canCreateRecipe,
      emptyStateMessage,
      subscriptionLimits,
      authStore,
      
      // Methods
      handleSearch,
      applyFilters,
      clearFilters,
      sortRecipes,
      editRecipe,
      deleteRecipe,
      confirmDeleteRecipe,
      toggleRecipePrivacy
    };
  }
};
</script>
