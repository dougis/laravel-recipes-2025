<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center min-h-screen">
      <div class="loading-spinner"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="!cookbook" class="flex justify-center items-center min-h-screen">
      <div class="text-center">
        <DocumentTextIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">Cookbook not found</h3>
        <p class="text-gray-600 mb-6">The cookbook you're looking for doesn't exist or has been removed.</p>
        <router-link :to="{ name: 'cookbooks' }" class="btn-primary">
          Browse Cookbooks
        </router-link>
      </div>
    </div>

    <!-- Cookbook Content -->
    <div v-else>
      <!-- Hero Section -->
      <div class="bg-white border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          <div class="flex flex-col lg:flex-row lg:items-start lg:space-x-8">
            <!-- Cookbook Cover -->
            <div class="flex-shrink-0 mb-6 lg:mb-0">
              <div class="w-full lg:w-80 h-64 lg:h-80 bg-gradient-to-br from-recipe-secondary-100 to-recipe-primary-100 rounded-lg flex items-center justify-center">
                <DocumentTextIcon class="w-20 h-20 text-recipe-primary-600" />
              </div>
            </div>

            <!-- Cookbook Header Info -->
            <div class="flex-1">
              <div class="flex items-start justify-between mb-4">
                <div>
                  <h1 class="text-3xl font-display font-bold text-gray-900 mb-2">
                    {{ cookbook.name }}
                  </h1>
                  
                  <!-- Cookbook Meta -->
                  <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 mb-4">
                    <div class="flex items-center">
                      <BookOpenIcon class="w-4 h-4 mr-1" />
                      <span>{{ recipeCount }} {{ recipeCount === 1 ? 'recipe' : 'recipes' }}</span>
                    </div>
                    
                    <div class="flex items-center">
                      <ClockIcon class="w-4 h-4 mr-1" />
                      <span>Updated {{ formatDate(cookbook.updated_at) }}</span>
                    </div>

                    <div v-if="cookbook.is_private && (isOwner || isAdmin)" class="flex items-center">
                      <LockClosedIcon class="w-4 h-4 mr-1" />
                      <span>Private</span>
                    </div>
                  </div>

                  <!-- Cookbook Description -->
                  <div v-if="cookbook.description" class="prose prose-sm max-w-none mb-4">
                    <p class="text-gray-700">{{ cookbook.description }}</p>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center space-x-2">
                  <!-- Print Button -->
                  <button
                    @click="printCookbook"
                    class="btn-outline"
                    title="Print Cookbook"
                  >
                    <PrinterIcon class="w-5 h-5" />
                  </button>

                  <!-- Share Button -->
                  <button
                    @click="shareCookbook"
                    class="btn-outline"
                    title="Share Cookbook"
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
                      :title="cookbook.is_private ? 'Make Public' : 'Make Private'"
                    >
                      <LockClosedIcon v-if="cookbook.is_private" class="w-5 h-5" />
                      <LockOpenIcon v-else class="w-5 h-5" />
                    </button>

                    <!-- Edit Button -->
                    <router-link
                      :to="{ name: 'cookbook-edit', params: { id: cookbook._id } }"
                      class="btn-outline"
                      title="Edit Cookbook"
                    >
                      <PencilIcon class="w-5 h-5" />
                    </router-link>

                    <!-- Delete Button -->
                    <button
                      @click="deleteCookbook"
                      class="btn-outline text-red-600 border-red-600 hover:bg-red-600 hover:text-white"
                      title="Delete Cookbook"
                    >
                      <TrashIcon class="w-5 h-5" />
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Cookbook Content -->
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Add Recipes Section (for owners) -->
        <div v-if="isOwner || isAdmin" class="mb-8">
          <div class="card">
            <div class="card-header">
              <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-900">Manage Recipes</h2>
                <button
                  @click="showAddRecipes = !showAddRecipes"
                  class="btn-primary"
                >
                  <PlusIcon class="w-5 h-5 mr-2" />
                  Add Recipes
                </button>
              </div>
            </div>
            
            <!-- Add Recipes Interface -->
            <div v-if="showAddRecipes" class="card-body border-t border-gray-200">
              <RecipeSelector 
                @recipes-selected="addRecipesToCookbook"
                @cancel="showAddRecipes = false"
              />
            </div>
          </div>
        </div>

        <!-- Recipes in Cookbook -->
        <div v-if="recipes.length">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-semibold text-gray-900">Recipes</h2>
            
            <!-- Reorder Mode Toggle (for owners) -->
            <div v-if="isOwner || isAdmin" class="flex items-center space-x-4">
              <button
                @click="reorderMode = !reorderMode"
                :class="[
                  'btn-outline',
                  reorderMode ? 'bg-recipe-primary-100 text-recipe-primary-700 border-recipe-primary-300' : ''
                ]"
              >
                <ArrowsUpDownIcon class="w-5 h-5 mr-2" />
                {{ reorderMode ? 'Done' : 'Reorder' }}
              </button>
            </div>
          </div>

          <!-- Recipes List -->
          <div class="space-y-4">
            <div
              v-for="(recipe, index) in recipes"
              :key="recipe._id"
              class="card hover:shadow-lg transition-shadow"
              :class="{ 'cursor-move': reorderMode }"
            >
              <div class="card-body">
                <div class="flex items-center space-x-4">
                  <!-- Drag Handle (reorder mode) -->
                  <div v-if="reorderMode" class="flex-shrink-0 text-gray-400 cursor-move">
                    <Bars3Icon class="w-5 h-5" />
                  </div>

                  <!-- Recipe Number -->
                  <div class="flex-shrink-0 w-8 h-8 bg-recipe-primary-100 rounded-full flex items-center justify-center">
                    <span class="text-sm font-medium text-recipe-primary-700">{{ index + 1 }}</span>
                  </div>

                  <!-- Recipe Info -->
                  <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-semibold text-gray-900 hover:text-recipe-primary-600 transition-colors cursor-pointer"
                        @click="viewRecipe(recipe._id)">
                      {{ recipe.name }}
                    </h3>
                    <div class="flex items-center text-sm text-gray-600 mt-1">
                      <UsersIcon class="w-4 h-4 mr-1" />
                      <span>{{ recipe.servings || 'N/A' }} servings</span>
                      
                      <span v-if="recipe.is_private" class="ml-4 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                        <LockClosedIcon class="w-3 h-3 mr-1" />
                        Private
                      </span>
                    </div>
                  </div>

                  <!-- Recipe Actions -->
                  <div class="flex items-center space-x-2">
                    <button
                      @click="viewRecipe(recipe._id)"
                      class="btn-outline text-sm px-3 py-1"
                    >
                      View
                    </button>

                    <!-- Remove from cookbook (for owners) -->
                    <button
                      v-if="isOwner || isAdmin"
                      @click="removeRecipeFromCookbook(recipe._id)"
                      class="p-2 text-gray-400 hover:text-red-600 rounded-md hover:bg-gray-100"
                      title="Remove from cookbook"
                    >
                      <XMarkIcon class="w-4 h-4" />
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty Cookbook State -->
        <div v-else class="text-center py-12">
          <BookOpenIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">No recipes in this cookbook</h3>
          <p class="text-gray-600 mb-6">
            {{ isOwner || isAdmin ? 'Add some recipes to get started.' : 'This cookbook is empty.' }}
          </p>
          <button
            v-if="isOwner || isAdmin"
            @click="showAddRecipes = true"
            class="btn-primary"
          >
            <PlusIcon class="w-5 h-5 mr-2" />
            Add Recipes
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
      v-if="showDeleteModal"
      :is-open="showDeleteModal"
      title="Delete Cookbook"
      :message="`Are you sure you want to delete '${cookbook?.name}'? This action cannot be undone.`"
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
import { useCookbookStore } from '../../stores/cookbooks';
import { useRecipeStore } from '../../stores/recipes';
import {
  DocumentTextIcon,
  BookOpenIcon,
  ClockIcon,
  LockClosedIcon,
  LockOpenIcon,
  PrinterIcon,
  ShareIcon,
  PencilIcon,
  TrashIcon,
  PlusIcon,
  ArrowsUpDownIcon,
  Bars3Icon,
  UsersIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline';
import ConfirmationModal from '../../components/ui/ConfirmationModal.vue';
import RecipeSelector from '../../components/cookbooks/RecipeSelector.vue';

export default {
  name: 'CookbookShow',
  components: {
    DocumentTextIcon,
    BookOpenIcon,
    ClockIcon,
    LockClosedIcon,
    LockOpenIcon,
    PrinterIcon,
    ShareIcon,
    PencilIcon,
    TrashIcon,
    PlusIcon,
    ArrowsUpDownIcon,
    Bars3Icon,
    UsersIcon,
    XMarkIcon,
    ConfirmationModal,
    RecipeSelector
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const authStore = useAuthStore();
    const cookbookStore = useCookbookStore();
    const recipeStore = useRecipeStore();

    const showDeleteModal = ref(false);
    const showAddRecipes = ref(false);
    const reorderMode = ref(false);

    const loading = computed(() => cookbookStore.loading);
    const cookbook = computed(() => cookbookStore.currentCookbook);
    const isAuthenticated = computed(() => authStore.isAuthenticated);
    const isOwner = computed(() => {
      return isAuthenticated.value && 
             cookbook.value && 
             authStore.user?._id === cookbook.value.user_id;
    });
    const isAdmin = computed(() => authStore.isAdmin);
    const canTogglePrivacy = computed(() => authStore.canCreatePrivateContent);

    const recipeCount = computed(() => {
      return cookbook.value?.recipe_ids?.length || 0;
    });

    const recipes = computed(() => {
      // In a real implementation, you would fetch the actual recipe objects
      // For now, return mock data or handle this in the cookbook store
      return cookbook.value?.recipes || [];
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

    const printCookbook = () => {
      window.open(`/api/v1/cookbooks/${route.params.id}/print`, '_blank');
    };

    const shareCookbook = async () => {
      const url = window.location.href;
      
      if (navigator.share) {
        try {
          await navigator.share({
            title: cookbook.value.name,
            text: `Check out this cookbook: ${cookbook.value.name}`,
            url: url
          });
        } catch (error) {
          copyToClipboard(url);
        }
      } else {
        copyToClipboard(url);
      }
    };

    const copyToClipboard = async (text) => {
      try {
        await navigator.clipboard.writeText(text);
        window.$toast?.success('Link copied', 'Cookbook link copied to clipboard!');
      } catch (error) {
        window.$toast?.error('Copy failed', 'Unable to copy link to clipboard.');
      }
    };

    const togglePrivacy = async () => {
      const result = await cookbookStore.toggleCookbookPrivacy(route.params.id);
      if (result.success) {
        const status = result.cookbook.is_private ? 'private' : 'public';
        window.$toast?.success('Privacy updated', `Cookbook is now ${status}.`);
      } else {
        window.$toast?.error('Update failed', result.message);
      }
    };

    const deleteCookbook = () => {
      showDeleteModal.value = true;
    };

    const confirmDelete = async () => {
      const result = await cookbookStore.deleteCookbook(route.params.id);
      if (result.success) {
        window.$toast?.success('Cookbook deleted', 'The cookbook has been successfully deleted.');
        router.push({ name: 'cookbooks' });
      } else {
        window.$toast?.error('Delete failed', result.message);
      }
      showDeleteModal.value = false;
    };

    const viewRecipe = (recipeId) => {
      router.push({ name: 'recipe-show', params: { id: recipeId } });
    };

    const addRecipesToCookbook = async (recipeIds) => {
      const result = await cookbookStore.addRecipesToCookbook(route.params.id, recipeIds);
      if (result.success) {
        window.$toast?.success('Recipes added', 'Selected recipes have been added to the cookbook.');
        showAddRecipes.value = false;
      } else {
        window.$toast?.error('Add failed', result.message);
      }
    };

    const removeRecipeFromCookbook = async (recipeId) => {
      const result = await cookbookStore.removeRecipeFromCookbook(route.params.id, recipeId);
      if (result.success) {
        window.$toast?.success('Recipe removed', 'Recipe has been removed from the cookbook.');
      } else {
        window.$toast?.error('Remove failed', result.message);
      }
    };

    onMounted(async () => {
      const result = await cookbookStore.fetchCookbook(route.params.id);
      if (!result.success) {
        window.$toast?.error('Cookbook not found', result.message);
      }
    });

    return {
      loading,
      cookbook,
      isOwner,
      isAdmin,
      canTogglePrivacy,
      recipeCount,
      recipes,
      showDeleteModal,
      showAddRecipes,
      reorderMode,
      formatDate,
      printCookbook,
      shareCookbook,
      togglePrivacy,
      deleteCookbook,
      confirmDelete,
      viewRecipe,
      addRecipesToCookbook,
      removeRecipeFromCookbook
    };
  }
};
</script>
