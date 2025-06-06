<template>
  <div class="space-y-4">
    <!-- Search -->
    <div class="relative">
      <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Search your recipes..."
        class="form-input pl-10 w-full"
        @input="handleSearch"
      />
    </div>

    <!-- Recipe List -->
    <div class="max-h-80 overflow-y-auto border border-gray-200 rounded-lg">
      <div v-if="loading" class="flex justify-center py-8">
        <div class="loading-spinner"></div>
      </div>

      <div v-else-if="availableRecipes.length" class="divide-y divide-gray-200">
        <label
          v-for="recipe in availableRecipes"
          :key="recipe._id"
          class="flex items-center p-4 hover:bg-gray-50 cursor-pointer"
        >
          <input
            v-model="selectedRecipes"
            :value="recipe._id"
            type="checkbox"
            class="h-4 w-4 text-recipe-primary-600 focus:ring-recipe-primary-500 border-gray-300 rounded"
          />
          <div class="ml-3 flex-1">
            <div class="flex items-center justify-between">
              <div>
                <h4 class="text-sm font-medium text-gray-900">{{ recipe.name }}</h4>
                <p class="text-sm text-gray-600">{{ recipe.servings || 'N/A' }} servings</p>
              </div>
              <div class="flex items-center text-xs text-gray-500">
                <ClockIcon class="w-3 h-3 mr-1" />
                <span>{{ formatDate(recipe.created_at) }}</span>
              </div>
            </div>
          </div>
        </label>
      </div>

      <div v-else class="p-8 text-center">
        <BookOpenIcon class="w-12 h-12 text-gray-400 mx-auto mb-4" />
        <h3 class="text-sm font-medium text-gray-900 mb-2">No recipes available</h3>
        <p class="text-sm text-gray-600">
          {{ searchQuery ? 'No recipes match your search.' : 'You don\'t have any recipes to add.' }}
        </p>
      </div>
    </div>

    <!-- Selection Summary -->
    <div v-if="selectedRecipes.length" class="bg-recipe-primary-50 rounded-lg p-4">
      <p class="text-sm text-recipe-primary-700">
        {{ selectedRecipes.length }} {{ selectedRecipes.length === 1 ? 'recipe' : 'recipes' }} selected
      </p>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-end space-x-4">
      <button
        @click="$emit('cancel')"
        class="btn-outline"
      >
        Cancel
      </button>

      <button
        @click="addSelectedRecipes"
        :disabled="selectedRecipes.length === 0"
        class="btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
      >
        Add {{ selectedRecipes.length }} {{ selectedRecipes.length === 1 ? 'Recipe' : 'Recipes' }}
      </button>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRecipeStore } from '../../stores/recipes';
import {
  MagnifyingGlassIcon,
  BookOpenIcon,
  ClockIcon
} from '@heroicons/vue/24/outline';

export default {
  name: 'RecipeSelector',
  components: {
    MagnifyingGlassIcon,
    BookOpenIcon,
    ClockIcon
  },
  props: {
    excludeRecipeIds: {
      type: Array,
      default: () => []
    }
  },
  emits: ['recipes-selected', 'cancel'],
  setup(props, { emit }) {
    const recipeStore = useRecipeStore();
    const searchQuery = ref('');
    const selectedRecipes = ref([]);

    const loading = computed(() => recipeStore.loading);

    const availableRecipes = computed(() => {
      let recipes = recipeStore.recipes.filter(recipe => 
        !props.excludeRecipeIds.includes(recipe._id)
      );

      // Apply search filter
      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        recipes = recipes.filter(recipe =>
          recipe.name.toLowerCase().includes(query) ||
          recipe.ingredients?.toLowerCase().includes(query)
        );
      }

      return recipes;
    });

    const handleSearch = () => {
      // Search is handled reactively through computed property
    };

    const addSelectedRecipes = () => {
      if (selectedRecipes.value.length > 0) {
        emit('recipes-selected', selectedRecipes.value);
        selectedRecipes.value = [];
      }
    };

    const formatDate = (dateString) => {
      const date = new Date(dateString);
      const now = new Date();
      const diffTime = Math.abs(now - date);
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

      if (diffDays === 1) return 'Yesterday';
      if (diffDays < 7) return `${diffDays} days ago`;
      return date.toLocaleDateString();
    };

    onMounted(async () => {
      // Load recipes if not already loaded
      if (recipeStore.recipes.length === 0) {
        await recipeStore.fetchRecipes();
      }
    });

    return {
      searchQuery,
      selectedRecipes,
      loading,
      availableRecipes,
      handleSearch,
      addSelectedRecipes,
      formatDate
    };
  }
};
</script>
