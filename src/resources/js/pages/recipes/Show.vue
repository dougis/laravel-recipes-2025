<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center min-h-screen">
      <div class="loading-spinner"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="!recipe" class="flex justify-center items-center min-h-screen">
      <div class="text-center">
        <BookOpenIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">Recipe not found</h3>
        <p class="text-gray-600 mb-6">The recipe you're looking for doesn't exist or has been removed.</p>
        <router-link :to="{ name: 'recipes' }" class="btn-primary">
          Browse Recipes
        </router-link>
      </div>
    </div>

    <!-- Recipe Content -->
    <div v-else>
      <!-- Hero Section -->
      <div class="bg-white border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          <div class="flex flex-col lg:flex-row lg:items-start lg:space-x-8">
            <!-- Recipe Image -->
            <div class="flex-shrink-0 mb-6 lg:mb-0">
              <div class="w-full lg:w-80 h-64 lg:h-80 bg-gray-100 rounded-lg flex items-center justify-center">
                <PhotoIcon class="w-16 h-16 text-gray-400" />
              </div>
            </div>

            <!-- Recipe Header Info -->
            <div class="flex-1">
              <div class="flex items-start justify-between mb-4">
                <div>
                  <h1 class="text-3xl font-display font-bold text-gray-900 mb-2">
                    {{ recipe.name }}
                  </h1>
                  
                  <!-- Recipe Meta -->
                  <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 mb-4">
                    <div class="flex items-center">
                      <UsersIcon class="w-4 h-4 mr-1" />
                      <span>{{ recipe.servings || 'N/A' }} servings</span>
                    </div>
                    
                    <div class="flex items-center">
                      <ClockIcon class="w-4 h-4 mr-1" />
                      <span>Added {{ formatDate(recipe.created_at) }}</span>
                    </div>

                    <div v-if="recipe.is_private && (isOwner || isAdmin)" class="flex items-center">
                      <LockClosedIcon class="w-4 h-4 mr-1" />
                      <span>Private</span>
                    </div>
                  </div>

                  <!-- Recipe Tags -->
                  <div v-if="recipe.tags?.length" class="flex flex-wrap gap-2 mb-4">
                    <span
                      v-for="tag in recipe.tags"
                      :key="tag"
                      class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-recipe-primary-100 text-recipe-primary-700"
                    >
                      {{ tag }}
                    </span>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center space-x-2">
                  <!-- Print Button -->
                  <button
                    @click="printRecipe"
                    class="btn-outline"
                    title="Print Recipe"
                  >
                    <PrinterIcon class="w-5 h-5" />
                  </button>

                  <!-- Share Button -->
                  <button
                    @click="shareRecipe"
                    class="btn-outline"
                    title="Share Recipe"
                  >
                    <ShareIcon class="w-5 h-5" />
                  </button>

                  <!-- Owner Actions -->
                  <div v-if="isOwner || isAdmin" class="flex items-center space-x-2">
                    <!-- Privacy Toggle -->
                    <button
                      v-if="canTogglePrivacy"
                      @click="togglePrivacy"
                      class="btn-outline"
                      :title="recipe.is_private ? 'Make Public' : 'Make Private'"
                    >
                      <LockClosedIcon v-if="recipe.is_private" class="w-5 h-5" />
                      <LockOpenIcon v-else class="w-5 h-5" />
                    </button>

                    <!-- Edit Button -->
                    <router-link
                      :to="{ name: 'recipe-edit', params: { id: recipe._id } }"
                      class="btn-outline"
                      title="Edit Recipe"
                    >
                      <PencilIcon class="w-5 h-5" />
                    </router-link>

                    <!-- Delete Button -->
                    <button
                      @click="deleteRecipe"
                      class="btn-outline text-red-600 border-red-600 hover:bg-red-600 hover:text-white"
                      title="Delete Recipe"
                    >
                      <TrashIcon class="w-5 h-5" />
                    </button>
                  </div>
                </div>
              </div>

              <!-- Quick Stats -->
              <div v-if="hasNutritionalInfo" class="grid grid-cols-2 lg:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-lg">
                <div v-if="recipe.calories" class="text-center">
                  <p class="text-lg font-semibold text-gray-900">{{ recipe.calories }}</p>
                  <p class="text-sm text-gray-600">Calories</p>
                </div>
                <div v-if="recipe.protein" class="text-center">
                  <p class="text-lg font-semibold text-gray-900">{{ recipe.protein }}g</p>
                  <p class="text-sm text-gray-600">Protein</p>
                </div>
                <div v-if="recipe.fat" class="text-center">
                  <p class="text-lg font-semibold text-gray-900">{{ recipe.fat }}g</p>
                  <p class="text-sm text-gray-600">Fat</p>
                </div>
                <div v-if="recipe.sodium" class="text-center">
                  <p class="text-lg font-semibold text-gray-900">{{ recipe.sodium }}mg</p>
                  <p class="text-sm text-gray-600">Sodium</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recipe Content -->
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid lg:grid-cols-3 gap-8">
          <!-- Ingredients -->
          <div class="lg:col-span-1">
            <div class="card sticky top-8">
              <div class="card-header">
                <h2 class="text-xl font-semibold text-gray-900">Ingredients</h2>
              </div>
              <div class="card-body">
                <div class="whitespace-pre-line text-gray-700">
                  {{ recipe.ingredients || 'No ingredients listed.' }}
                </div>
              </div>
            </div>
          </div>

          <!-- Instructions -->
          <div class="lg:col-span-2 space-y-6">
            <div class="card">
              <div class="card-header">
                <h2 class="text-xl font-semibold text-gray-900">Instructions</h2>
              </div>
              <div class="card-body">
                <div class="whitespace-pre-line text-gray-700 prose max-w-none">
                  {{ recipe.instructions || 'No instructions provided.' }}
                </div>
              </div>
            </div>

            <!-- Notes -->
            <div v-if="recipe.notes" class="card">
              <div class="card-header">
                <h2 class="text-xl font-semibold text-gray-900">Notes</h2>
              </div>
              <div class="card-body">
                <div class="whitespace-pre-line text-gray-700">
                  {{ recipe.notes }}
                </div>
              </div>
            </div>

            <!-- Recipe Metadata -->
            <div class="card">
              <div class="card-header">
                <h2 class="text-xl font-semibold text-gray-900">Recipe Details</h2>
              </div>
              <div class="card-body">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div v-if="recipe.classification_name">
                    <dt class="text-sm font-medium text-gray-600">Classification</dt>
                    <dd class="text-sm text-gray-900">{{ recipe.classification_name }}</dd>
                  </div>
                  
                  <div v-if="recipe.source_name">
                    <dt class="text-sm font-medium text-gray-600">Source</dt>
                    <dd class="text-sm text-gray-900">{{ recipe.source_name }}</dd>
                  </div>
                  
                  <div v-if="recipe.meal_names?.length">
                    <dt class="text-sm font-medium text-gray-600">Meal Types</dt>
                    <dd class="text-sm text-gray-900">{{ recipe.meal_names.join(', ') }}</dd>
                  </div>
                  
                  <div v-if="recipe.course_names?.length">
                    <dt class="text-sm font-medium text-gray-600">Courses</dt>
                    <dd class="text-sm text-gray-900">{{ recipe.course_names.join(', ') }}</dd>
                  </div>
                  
                  <div v-if="recipe.preparation_names?.length">
                    <dt class="text-sm font-medium text-gray-600">Preparation Methods</dt>
                    <dd class="text-sm text-gray-900">{{ recipe.preparation_names.join(', ') }}</dd>
                  </div>

                  <div>
                    <dt class="text-sm font-medium text-gray-600">Created</dt>
                    <dd class="text-sm text-gray-900">{{ formatFullDate(recipe.created_at) }}</dd>
                  </div>

                  <div v-if="recipe.updated_at !== recipe.created_at">
                    <dt class="text-sm font-medium text-gray-600">Last Updated</dt>
                    <dd class="text-sm text-gray-900">{{ formatFullDate(recipe.updated_at) }}</dd>
                  </div>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
      v-if="showDeleteModal"
      :is-open="showDeleteModal"
      title="Delete Recipe"
      :message="`Are you sure you want to delete '${recipe?.name}'? This action cannot be undone.`"
      confirm-text="Delete"
      confirm-class="bg-red-600 hover:bg-red-700"
      @confirm="confirmDelete"
      @cancel="showDeleteModal = false"
    />
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useRecipeStore } from '../../stores/recipes';
import {
  BookOpenIcon,
  PhotoIcon,
  UsersIcon,
  ClockIcon,
  LockClosedIcon,
  LockOpenIcon,
  PrinterIcon,
  ShareIcon,
  PencilIcon,
  TrashIcon
} from '@heroicons/vue/24/outline';
import ConfirmationModal from '../../components/ui/ConfirmationModal.vue';

export default {
  name: 'RecipeShow',
  components: {
    BookOpenIcon,
    PhotoIcon,
    UsersIcon,
    ClockIcon,
    LockClosedIcon,
    LockOpenIcon,
    PrinterIcon,
    ShareIcon,
    PencilIcon,
    TrashIcon,
    ConfirmationModal
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const authStore = useAuthStore();
    const recipeStore = useRecipeStore();

    const showDeleteModal = ref(false);

    const loading = computed(() => recipeStore.loading);
    const recipe = computed(() => recipeStore.currentRecipe);
    const isAuthenticated = computed(() => authStore.isAuthenticated);
    const isOwner = computed(() => {
      return isAuthenticated.value && 
             recipe.value && 
             authStore.user?._id === recipe.value.user_id;
    });
    const isAdmin = computed(() => authStore.isAdmin);
    const canTogglePrivacy = computed(() => authStore.canCreatePrivateContent);

    const hasNutritionalInfo = computed(() => {
      return recipe.value && (
        recipe.value.calories || 
        recipe.value.protein || 
        recipe.value.fat || 
        recipe.value.sodium
      );
    });

    const formatDate = (dateString) => {
      const date = new Date(dateString);
      const now = new Date();
      const diffTime = Math.abs(now - date);
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

      if (diffDays === 1) return 'yesterday';
      if (diffDays < 7) return `${diffDays} days ago`;
      if (diffDays < 30) return `${Math.ceil(diffDays / 7)} weeks ago`;
      return date.toLocaleDateString();
    };

    const formatFullDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    };

    const printRecipe = () => {
      window.open(`/api/v1/recipes/${route.params.id}/print`, '_blank');
    };

    const shareRecipe = async () => {
      const url = window.location.href;
      
      if (navigator.share) {
        try {
          await navigator.share({
            title: recipe.value.name,
            text: `Check out this recipe: ${recipe.value.name}`,
            url: url
          });
        } catch (error) {
          // Fallback to copying to clipboard
          copyToClipboard(url);
        }
      } else {
        copyToClipboard(url);
      }
    };

    const copyToClipboard = async (text) => {
      try {
        await navigator.clipboard.writeText(text);
        window.$toast?.success('Link copied', 'Recipe link copied to clipboard!');
      } catch (error) {
        window.$toast?.error('Copy failed', 'Unable to copy link to clipboard.');
      }
    };

    const togglePrivacy = async () => {
      const result = await recipeStore.toggleRecipePrivacy(route.params.id);
      if (result.success) {
        const status = result.recipe.is_private ? 'private' : 'public';
        window.$toast?.success('Privacy updated', `Recipe is now ${status}.`);
      } else {
        window.$toast?.error('Update failed', result.message);
      }
    };

    const deleteRecipe = () => {
      showDeleteModal.value = true;
    };

    const confirmDelete = async () => {
      const result = await recipeStore.deleteRecipe(route.params.id);
      if (result.success) {
        window.$toast?.success('Recipe deleted', 'The recipe has been successfully deleted.');
        router.push({ name: 'recipes' });
      } else {
        window.$toast?.error('Delete failed', result.message);
      }
      showDeleteModal.value = false;
    };

    onMounted(async () => {
      const result = await recipeStore.fetchRecipe(route.params.id);
      if (!result.success) {
        window.$toast?.error('Recipe not found', result.message);
      }
    });

    return {
      loading,
      recipe,
      isOwner,
      isAdmin,
      canTogglePrivacy,
      hasNutritionalInfo,
      showDeleteModal,
      formatDate,
      formatFullDate,
      printRecipe,
      shareRecipe,
      togglePrivacy,
      deleteRecipe,
      confirmDelete
    };
  }
};
</script>
