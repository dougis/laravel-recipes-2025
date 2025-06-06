<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Loading State -->
    <div v-if="initialLoading" class="flex justify-center items-center min-h-screen">
      <div class="loading-spinner"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="!cookbook" class="flex justify-center items-center min-h-screen">
      <div class="text-center">
        <DocumentTextIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">Cookbook not found</h3>
        <p class="text-gray-600 mb-6">The cookbook you're trying to edit doesn't exist or you don't have permission to edit it.</p>
        <router-link :to="{ name: 'cookbooks' }" class="btn-primary">
          Back to Cookbooks
        </router-link>
      </div>
    </div>

    <!-- Edit Form -->
    <div v-else class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-display font-bold text-gray-900">Edit Cookbook</h1>
            <p class="mt-2 text-gray-600">Update your cookbook details</p>
          </div>
          
          <router-link
            :to="{ name: 'cookbook-show', params: { id: cookbook._id } }"
            class="btn-outline"
          >
            <ArrowLeftIcon class="w-5 h-5 mr-2" />
            Back to Cookbook
          </router-link>
        </div>
      </div>

      <!-- Cookbook Form -->
      <form @submit.prevent="handleSubmit" class="space-y-8">
        <!-- Basic Information -->
        <div class="card">
          <div class="card-header">
            <h2 class="text-xl font-semibold text-gray-900">Basic Information</h2>
          </div>
          <div class="card-body space-y-6">
            <!-- Cookbook Name -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                Cookbook Name *
              </label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                class="form-input"
                :class="{ 'border-red-500': errors.name }"
                placeholder="Enter cookbook name"
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">
                {{ errors.name[0] }}
              </p>
            </div>

            <!-- Description -->
            <div>
              <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                Description
              </label>
              <textarea
                id="description"
                v-model="form.description"
                rows="4"
                class="form-textarea"
                :class="{ 'border-red-500': errors.description }"
                placeholder="Describe your cookbook..."
              ></textarea>
              <p v-if="errors.description" class="mt-1 text-sm text-red-600">
                {{ errors.description[0] }}
              </p>
              <p class="mt-1 text-sm text-gray-500">
                Tell people what makes this cookbook special
              </p>
            </div>

            <!-- Cover Image Upload (Future Implementation) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Cookbook Cover
              </label>
              <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                <div class="space-y-1 text-center">
                  <PhotoIcon class="mx-auto h-12 w-12 text-gray-400" />
                  <div class="flex text-sm text-gray-600">
                    <span class="text-recipe-primary-600 hover:text-recipe-primary-500 cursor-pointer">
                      Upload a cover image
                    </span>
                    <p class="pl-1">or drag and drop</p>
                  </div>
                  <p class="text-xs text-gray-500">
                    PNG, JPG, GIF up to 10MB
                  </p>
                </div>
              </div>
              <p class="mt-1 text-sm text-gray-500">
                Cover image upload coming soon
              </p>
            </div>
          </div>
        </div>

        <!-- Privacy Settings -->
        <div v-if="canCreatePrivateContent" class="card">
          <div class="card-header">
            <h2 class="text-xl font-semibold text-gray-900">Privacy Settings</h2>
          </div>
          <div class="card-body">
            <div class="flex items-center">
              <input
                id="is_private"
                v-model="form.is_private"
                type="checkbox"
                class="h-4 w-4 text-recipe-primary-600 focus:ring-recipe-primary-500 border-gray-300 rounded"
              />
              <label for="is_private" class="ml-2 block text-sm text-gray-700">
                Make this cookbook private
              </label>
            </div>
            <p class="mt-1 text-sm text-gray-500">
              Private cookbooks are only visible to you and will not appear in public cookbook listings.
            </p>
          </div>
        </div>

        <!-- Current Recipes -->
        <div class="card">
          <div class="card-header">
            <div class="flex items-center justify-between">
              <h2 class="text-xl font-semibold text-gray-900">Recipes in Cookbook</h2>
              <button
                type="button"
                @click="showRecipeSelector = true"
                class="btn-outline text-sm"
              >
                <PlusIcon class="w-5 h-5 mr-1" />
                Add Recipes
              </button>
            </div>
          </div>
          <div class="card-body">
            <div v-if="currentRecipes.length" class="space-y-3">
              <div
                v-for="(recipe, index) in currentRecipes"
                :key="recipe._id"
                class="flex items-center p-3 bg-gray-50 rounded-lg"
              >
                <div class="flex-shrink-0 w-8 h-8 bg-recipe-primary-100 rounded-full flex items-center justify-center mr-3">
                  <span class="text-sm font-medium text-recipe-primary-700">{{ index + 1 }}</span>
                </div>
                <div class="flex-1">
                  <h4 class="text-sm font-medium text-gray-900">{{ recipe.name }}</h4>
                  <p class="text-xs text-gray-600">{{ recipe.servings || 'N/A' }} servings</p>
                </div>
                <button
                  type="button"
                  @click="removeRecipe(recipe._id)"
                  class="ml-2 text-gray-400 hover:text-red-600"
                  title="Remove from cookbook"
                >
                  <XMarkIcon class="w-4 h-4" />
                </button>
              </div>
            </div>

            <div v-else class="text-center py-8">
              <BookOpenIcon class="w-12 h-12 text-gray-400 mx-auto mb-4" />
              <p class="text-gray-600 mb-4">No recipes in this cookbook</p>
              <button
                type="button"
                @click="showRecipeSelector = true"
                class="btn-outline"
              >
                <PlusIcon class="w-5 h-5 mr-2" />
                Add First Recipe
              </button>
            </div>

            <!-- Recipe Selector -->
            <div v-if="showRecipeSelector" class="mt-6 pt-6 border-t border-gray-200">
              <RecipeSelector 
                :exclude-recipe-ids="form.recipe_ids"
                @recipes-selected="addRecipes"
                @cancel="showRecipeSelector = false"
              />
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
          <router-link
            :to="{ name: 'cookbook-show', params: { id: cookbook._id } }"
            class="btn-outline"
          >
            Cancel
          </router-link>

          <div class="flex items-center space-x-4">
            <button
              type="submit"
              :disabled="loading"
              class="btn-primary flex items-center"
            >
              <div v-if="loading" class="loading-spinner w-5 h-5 mr-2"></div>
              {{ loading ? 'Updating...' : 'Update Cookbook' }}
            </button>
          </div>
        </div>

        <!-- Error Display -->
        <div v-if="errorMessage" class="rounded-md bg-red-50 p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <XCircleIcon class="h-5 w-5 text-red-400" />
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">
                Failed to update cookbook
              </h3>
              <div class="mt-2 text-sm text-red-700">
                <p>{{ errorMessage }}</p>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useCookbookStore } from '../../stores/cookbooks';
import { useRecipeStore } from '../../stores/recipes';
import {
  ArrowLeftIcon,
  DocumentTextIcon,
  PhotoIcon,
  BookOpenIcon,
  PlusIcon,
  XMarkIcon,
  XCircleIcon
} from '@heroicons/vue/24/outline';
import RecipeSelector from '../../components/cookbooks/RecipeSelector.vue';

export default {
  name: 'CookbookEdit',
  components: {
    ArrowLeftIcon,
    DocumentTextIcon,
    PhotoIcon,
    BookOpenIcon,
    PlusIcon,
    XMarkIcon,
    XCircleIcon,
    RecipeSelector
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const authStore = useAuthStore();
    const cookbookStore = useCookbookStore();
    const recipeStore = useRecipeStore();

    const initialLoading = ref(true);
    const loading = ref(false);
    const errors = ref({});
    const errorMessage = ref('');
    const showRecipeSelector = ref(false);

    const form = reactive({
      name: '',
      description: '',
      recipe_ids: [],
      is_private: false
    });

    const cookbook = computed(() => cookbookStore.currentCookbook);
    const canCreatePrivateContent = computed(() => authStore.canCreatePrivateContent);

    const currentRecipes = computed(() => {
      return recipeStore.recipes.filter(recipe => 
        form.recipe_ids.includes(recipe._id)
      );
    });

    const populateForm = () => {
      if (cookbook.value) {
        form.name = cookbook.value.name || '';
        form.description = cookbook.value.description || '';
        form.recipe_ids = cookbook.value.recipe_ids || [];
        form.is_private = cookbook.value.is_private || false;
      }
    };

    const validateForm = () => {
      errors.value = {};
      
      if (!form.name.trim()) {
        errors.value.name = ['Cookbook name is required'];
      }

      return Object.keys(errors.value).length === 0;
    };

    const addRecipes = (recipeIds) => {
      form.recipe_ids = [...new Set([...form.recipe_ids, ...recipeIds])];
      showRecipeSelector.value = false;
    };

    const removeRecipe = (recipeId) => {
      form.recipe_ids = form.recipe_ids.filter(id => id !== recipeId);
    };

    const handleSubmit = async () => {
      if (!validateForm()) {
        window.$toast?.error('Validation Error', 'Please fix the errors below.');
        return;
      }

      loading.value = true;
      errorMessage.value = '';

      const result = await cookbookStore.updateCookbook(route.params.id, form);

      if (result.success) {
        window.$toast?.success('Cookbook Updated', 'Your cookbook has been successfully updated.');
        router.push({ name: 'cookbook-show', params: { id: result.cookbook._id } });
      } else {
        if (result.errors) {
          errors.value = result.errors;
        } else {
          errorMessage.value = result.message;
        }
        window.$toast?.error('Update Failed', result.message);
      }

      loading.value = false;
    };

    onMounted(async () => {
      // Load cookbook, recipes, and metadata
      const [cookbookResult, recipesResult] = await Promise.all([
        cookbookStore.fetchCookbook(route.params.id),
        recipeStore.fetchRecipes()
      ]);

      if (cookbookResult.success) {
        populateForm();
      } else {
        window.$toast?.error('Cookbook not found', cookbookResult.message);
      }

      initialLoading.value = false;
    });

    return {
      form,
      initialLoading,
      loading,
      errors,
      errorMessage,
      showRecipeSelector,
      cookbook,
      canCreatePrivateContent,
      currentRecipes,
      addRecipes,
      removeRecipe,
      handleSubmit
    };
  }
};
</script>
