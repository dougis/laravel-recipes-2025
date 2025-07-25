<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Loading State -->
    <div v-if="initialLoading" class="flex justify-center items-center min-h-screen">
      <div class="loading-spinner"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="!recipe" class="flex justify-center items-center min-h-screen">
      <div class="text-center">
        <BookOpenIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">Recipe not found</h3>
        <p class="text-gray-600 mb-6">The recipe you're trying to edit doesn't exist or you don't have permission to edit it.</p>
        <router-link :to="{ name: 'recipes' }" class="btn-primary">
          Back to Recipes
        </router-link>
      </div>
    </div>

    <!-- Edit Form -->
    <div v-else class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-display font-bold text-gray-900">Edit Recipe</h1>
            <p class="mt-2 text-gray-600">Update your recipe details</p>
          </div>
          
          <router-link
            :to="{ name: 'recipe-show', params: { id: recipe._id } }"
            class="btn-outline"
          >
            <ArrowLeftIcon class="w-5 h-5 mr-2" />
            Back to Recipe
          </router-link>
        </div>
      </div>

      <!-- Recipe Form -->
      <form @submit.prevent="handleSubmit" class="space-y-8">
        <!-- Basic Information -->
        <div class="card">
          <div class="card-header">
            <h2 class="text-xl font-semibold text-gray-900">Basic Information</h2>
          </div>
          <div class="card-body space-y-6">
            <!-- Recipe Name -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                Recipe Name *
              </label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                class="form-input"
                :class="{ 'border-red-500': errors.name }"
                placeholder="Enter recipe name"
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">
                {{ errors.name[0] }}
              </p>
            </div>

            <!-- Servings -->
            <div>
              <label for="servings" class="block text-sm font-medium text-gray-700 mb-2">
                Servings
              </label>
              <input
                id="servings"
                v-model.number="form.servings"
                type="number"
                min="1"
                max="100"
                class="form-input"
                :class="{ 'border-red-500': errors.servings }"
                placeholder="Number of servings"
              />
              <p v-if="errors.servings" class="mt-1 text-sm text-red-600">
                {{ errors.servings[0] }}
              </p>
            </div>

            <!-- Tags -->
            <div>
              <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">
                Tags
              </label>
              <input
                id="tags"
                v-model="tagsInput"
                type="text"
                class="form-input"
                placeholder="Enter tags separated by commas (e.g. vegetarian, quick, comfort food)"
              />
              <p class="mt-1 text-sm text-gray-500">
                Separate multiple tags with commas
              </p>
              
              <!-- Tag Display -->
              <div v-if="form.tags.length" class="mt-2 flex flex-wrap gap-2">
                <span
                  v-for="(tag, index) in form.tags"
                  :key="index"
                  class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-recipe-primary-100 text-recipe-primary-700"
                >
                  {{ tag }}
                  <button
                    @click="removeTag(index)"
                    type="button"
                    class="ml-2 text-recipe-primary-500 hover:text-recipe-primary-700"
                  >
                    <XMarkIcon class="w-4 h-4" />
                  </button>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Ingredients -->
        <div class="card">
          <div class="card-header">
            <h2 class="text-xl font-semibold text-gray-900">Ingredients</h2>
          </div>
          <div class="card-body">
            <div>
              <label for="ingredients" class="block text-sm font-medium text-gray-700 mb-2">
                Ingredients *
              </label>
              <textarea
                id="ingredients"
                v-model="form.ingredients"
                required
                rows="8"
                class="form-textarea"
                :class="{ 'border-red-500': errors.ingredients }"
                placeholder="List ingredients, one per line or separated by commas..."
              ></textarea>
              <p v-if="errors.ingredients" class="mt-1 text-sm text-red-600">
                {{ errors.ingredients[0] }}
              </p>
              <p class="mt-1 text-sm text-gray-500">
                List each ingredient on a new line with quantities and measurements
              </p>
            </div>
          </div>
        </div>

        <!-- Instructions -->
        <div class="card">
          <div class="card-header">
            <h2 class="text-xl font-semibold text-gray-900">Instructions</h2>
          </div>
          <div class="card-body">
            <div>
              <label for="instructions" class="block text-sm font-medium text-gray-700 mb-2">
                Instructions *
              </label>
              <textarea
                id="instructions"
                v-model="form.instructions"
                required
                rows="12"
                class="form-textarea"
                :class="{ 'border-red-500': errors.instructions }"
                placeholder="Write step-by-step cooking instructions..."
              ></textarea>
              <p v-if="errors.instructions" class="mt-1 text-sm text-red-600">
                {{ errors.instructions[0] }}
              </p>
              <p class="mt-1 text-sm text-gray-500">
                Provide clear, step-by-step instructions for preparing this recipe
              </p>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div class="card">
          <div class="card-header">
            <h2 class="text-xl font-semibold text-gray-900">Notes</h2>
          </div>
          <div class="card-body">
            <div>
              <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                Additional Notes
              </label>
              <textarea
                id="notes"
                v-model="form.notes"
                rows="4"
                class="form-textarea"
                placeholder="Add any additional notes, tips, or variations..."
              ></textarea>
              <p class="mt-1 text-sm text-gray-500">
                Optional notes about the recipe, cooking tips, or variations
              </p>
            </div>
          </div>
        </div>

        <!-- Classification & Categories -->
        <div class="card">
          <div class="card-header">
            <h2 class="text-xl font-semibold text-gray-900">Classification & Categories</h2>
          </div>
          <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Classification -->
              <div>
                <label for="classification" class="block text-sm font-medium text-gray-700 mb-2">
                  Classification
                </label>
                <select
                  id="classification"
                  v-model="form.classification_id"
                  class="form-select"
                >
                  <option value="">Select classification</option>
                  <option v-for="classification in metadata.classifications" :key="classification._id" :value="classification._id">
                    {{ classification.name }}
                  </option>
                </select>
              </div>

              <!-- Source -->
              <div>
                <label for="source" class="block text-sm font-medium text-gray-700 mb-2">
                  Source
                </label>
                <select
                  id="source"
                  v-model="form.source_id"
                  class="form-select"
                >
                  <option value="">Select source</option>
                  <option v-for="source in metadata.sources" :key="source._id" :value="source._id">
                    {{ source.name }}
                  </option>
                </select>
              </div>

              <!-- Meals -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Meal Types
                </label>
                <div class="space-y-2">
                  <label
                    v-for="meal in metadata.meals"
                    :key="meal._id"
                    class="flex items-center"
                  >
                    <input
                      v-model="form.meal_ids"
                      :value="meal._id"
                      type="checkbox"
                      class="h-4 w-4 text-recipe-primary-600 focus:ring-recipe-primary-500 border-gray-300 rounded"
                    />
                    <span class="ml-2 text-sm text-gray-700">{{ meal.name }}</span>
                  </label>
                </div>
              </div>

              <!-- Courses -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Courses
                </label>
                <div class="space-y-2">
                  <label
                    v-for="course in metadata.courses"
                    :key="course._id"
                    class="flex items-center"
                  >
                    <input
                      v-model="form.course_ids"
                      :value="course._id"
                      type="checkbox"
                      class="h-4 w-4 text-recipe-primary-600 focus:ring-recipe-primary-500 border-gray-300 rounded"
                    />
                    <span class="ml-2 text-sm text-gray-700">{{ course.name }}</span>
                  </label>
                </div>
              </div>

              <!-- Preparation Methods (full width) -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Preparation Methods
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                  <label
                    v-for="preparation in metadata.preparations"
                    :key="preparation._id"
                    class="flex items-center"
                  >
                    <input
                      v-model="form.preparation_ids"
                      :value="preparation._id"
                      type="checkbox"
                      class="h-4 w-4 text-recipe-primary-600 focus:ring-recipe-primary-500 border-gray-300 rounded"
                    />
                    <span class="ml-2 text-sm text-gray-700">{{ preparation.name }}</span>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Nutritional Information -->
        <div class="card">
          <div class="card-header">
            <h2 class="text-xl font-semibold text-gray-900">Nutritional Information</h2>
            <p class="text-sm text-gray-600">Optional nutritional data per serving</p>
          </div>
          <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <div>
                <label for="calories" class="block text-sm font-medium text-gray-700 mb-2">
                  Calories
                </label>
                <input
                  id="calories"
                  v-model.number="form.calories"
                  type="number"
                  min="0"
                  class="form-input"
                  placeholder="0"
                />
              </div>

              <div>
                <label for="protein" class="block text-sm font-medium text-gray-700 mb-2">
                  Protein (g)
                </label>
                <input
                  id="protein"
                  v-model.number="form.protein"
                  type="number"
                  min="0"
                  step="0.1"
                  class="form-input"
                  placeholder="0"
                />
              </div>

              <div>
                <label for="fat" class="block text-sm font-medium text-gray-700 mb-2">
                  Fat (g)
                </label>
                <input
                  id="fat"
                  v-model.number="form.fat"
                  type="number"
                  min="0"
                  step="0.1"
                  class="form-input"
                  placeholder="0"
                />
              </div>

              <div>
                <label for="sodium" class="block text-sm font-medium text-gray-700 mb-2">
                  Sodium (mg)
                </label>
                <input
                  id="sodium"
                  v-model.number="form.sodium"
                  type="number"
                  min="0"
                  class="form-input"
                  placeholder="0"
                />
              </div>

              <div>
                <label for="cholesterol" class="block text-sm font-medium text-gray-700 mb-2">
                  Cholesterol (mg)
                </label>
                <input
                  id="cholesterol"
                  v-model.number="form.cholesterol"
                  type="number"
                  min="0"
                  class="form-input"
                  placeholder="0"
                />
              </div>
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
                Make this recipe private
              </label>
            </div>
            <p class="mt-1 text-sm text-gray-500">
              Private recipes are only visible to you and will not appear in public recipe listings.
            </p>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
          <router-link
            :to="{ name: 'recipe-show', params: { id: recipe._id } }"
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
              {{ loading ? 'Updating...' : 'Update Recipe' }}
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
                Failed to update recipe
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
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useRecipeStore } from '../../stores/recipes';
import {
  ArrowLeftIcon,
  XMarkIcon,
  XCircleIcon,
  BookOpenIcon
} from '@heroicons/vue/24/outline';

export default {
  name: 'RecipeEdit',
  components: {
    ArrowLeftIcon,
    XMarkIcon,
    XCircleIcon,
    BookOpenIcon
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const authStore = useAuthStore();
    const recipeStore = useRecipeStore();

    const initialLoading = ref(true);
    const loading = ref(false);
    const errors = ref({});
    const errorMessage = ref('');
    const tagsInput = ref('');

    const form = reactive({
      name: '',
      ingredients: '',
      instructions: '',
      notes: '',
      servings: 4,
      classification_id: '',
      source_id: '',
      meal_ids: [],
      course_ids: [],
      preparation_ids: [],
      tags: [],
      calories: null,
      fat: null,
      cholesterol: null,
      sodium: null,
      protein: null,
      is_private: false
    });

    const recipe = computed(() => recipeStore.currentRecipe);
    const metadata = computed(() => recipeStore.metadata);
    const canCreatePrivateContent = computed(() => authStore.canCreatePrivateContent);

    // Watch tags input and convert to array
    watch(tagsInput, (newValue) => {
      if (newValue) {
        form.tags = newValue
          .split(',')
          .map(tag => tag.trim())
          .filter(tag => tag.length > 0);
      } else {
        form.tags = [];
      }
    });

    const removeTag = (index) => {
      form.tags.splice(index, 1);
      tagsInput.value = form.tags.join(', ');
    };

    const populateForm = () => {
      if (recipe.value) {
        form.name = recipe.value.name || '';
        form.ingredients = recipe.value.ingredients || '';
        form.instructions = recipe.value.instructions || '';
        form.notes = recipe.value.notes || '';
        form.servings = recipe.value.servings || 4;
        form.classification_id = recipe.value.classification_id || '';
        form.source_id = recipe.value.source_id || '';
        form.meal_ids = recipe.value.meal_ids || [];
        form.course_ids = recipe.value.course_ids || [];
        form.preparation_ids = recipe.value.preparation_ids || [];
        form.tags = recipe.value.tags || [];
        form.calories = recipe.value.calories;
        form.fat = recipe.value.fat;
        form.cholesterol = recipe.value.cholesterol;
        form.sodium = recipe.value.sodium;
        form.protein = recipe.value.protein;
        form.is_private = recipe.value.is_private || false;

        // Update tags input
        tagsInput.value = form.tags.join(', ');
      }
    };

    const validateForm = () => {
      errors.value = {};
      
      if (!form.name.trim()) {
        errors.value.name = ['Recipe name is required'];
      }
      
      if (!form.ingredients.trim()) {
        errors.value.ingredients = ['Ingredients are required'];
      }
      
      if (!form.instructions.trim()) {
        errors.value.instructions = ['Instructions are required'];
      }

      return Object.keys(errors.value).length === 0;
    };

    const handleSubmit = async () => {
      if (!validateForm()) {
        window.$toast?.error('Validation Error', 'Please fix the errors below.');
        return;
      }

      loading.value = true;
      errorMessage.value = '';

      const result = await recipeStore.updateRecipe(route.params.id, {
        ...form,
        // Convert empty strings to null for optional fields
        classification_id: form.classification_id || null,
        source_id: form.source_id || null,
        calories: form.calories || null,
        fat: form.fat || null,
        cholesterol: form.cholesterol || null,
        sodium: form.sodium || null,
        protein: form.protein || null
      });

      if (result.success) {
        window.$toast?.success('Recipe Updated', 'Your recipe has been successfully updated.');
        router.push({ name: 'recipe-show', params: { id: result.recipe._id } });
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
      // Load recipe and metadata
      const [recipeResult, metadataResult] = await Promise.all([
        recipeStore.fetchRecipe(route.params.id),
        recipeStore.loadMetadata()
      ]);

      if (recipeResult.success) {
        populateForm();
      } else {
        window.$toast?.error('Recipe not found', recipeResult.message);
      }

      initialLoading.value = false;
    });

    return {
      form,
      tagsInput,
      initialLoading,
      loading,
      errors,
      errorMessage,
      recipe,
      metadata,
      canCreatePrivateContent,
      removeTag,
      handleSubmit
    };
  }
};
</script>
