<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-display font-bold text-gray-900">
              {{ isPublicView ? 'Browse Cookbooks' : 'My Cookbooks' }}
            </h1>
            <p class="mt-2 text-gray-600">
              {{ isPublicView ? 'Discover curated recipe collections from our community' : 'Organize your recipes into beautiful cookbooks' }}
            </p>
          </div>
          
          <div v-if="!isPublicView" class="flex items-center space-x-4">
            <router-link
              :to="{ name: 'cookbook-create' }"
              class="btn-primary"
              :class="{ 'opacity-50 cursor-not-allowed': !canCreateCookbook }"
            >
              <PlusIcon class="w-5 h-5 mr-2" />
              New Cookbook
            </router-link>
          </div>
        </div>
      </div>

      <!-- Search and Filters -->
      <div class="mb-8">
        <div class="flex flex-col md:flex-row gap-4">
          <!-- Search Bar -->
          <div class="flex-1">
            <div class="relative">
              <MagnifyingGlassIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search cookbooks..."
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
        </div>
      </div>

      <!-- Results Info -->
      <div class="mb-6 flex items-center justify-between">
        <p class="text-sm text-gray-600">
          {{ displayedCookbooks.length }} {{ displayedCookbooks.length === 1 ? 'cookbook' : 'cookbooks' }} found
        </p>
        
        <div class="flex items-center space-x-4">
          <!-- Sort Options -->
          <select v-model="sortBy" @change="sortCookbooks" class="form-select text-sm">
            <option value="updated_at">Recently Updated</option>
            <option value="created_at">Recently Added</option>
            <option value="name">Name (A-Z)</option>
            <option value="recipe_count">Recipe Count</option>
          </select>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-12">
        <div class="loading-spinner"></div>
      </div>

      <!-- Cookbooks Grid/List -->
      <div v-else-if="displayedCookbooks.length">
        <!-- Grid View -->
        <div v-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <CookbookCard
            v-for="cookbook in displayedCookbooks"
            :key="cookbook._id"
            :cookbook="cookbook"
            :show-privacy="!isPublicView"
            @edit="editCookbook"
            @delete="deleteCookbook"
            @toggle-privacy="toggleCookbookPrivacy"
          />
        </div>

        <!-- List View -->
        <div v-else class="space-y-4">
          <CookbookListItem
            v-for="cookbook in displayedCookbooks"
            :key="cookbook._id"
            :cookbook="cookbook"
            :show-privacy="!isPublicView"
            @edit="editCookbook"
            @delete="deleteCookbook"
            @toggle-privacy="toggleCookbookPrivacy"
          />
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12">
        <DocumentTextIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">
          {{ isPublicView ? 'No public cookbooks found' : 'No cookbooks found' }}
        </h3>
        <p class="text-gray-600 mb-6">
          {{ emptyStateMessage }}
        </p>
        <router-link
          v-if="!isPublicView"
          :to="{ name: 'cookbook-create' }"
          class="btn-primary"
        >
          Create Your First Cookbook
        </router-link>
      </div>

      <!-- Subscription Limit Warning -->
      <div v-if="!isPublicView && !canCreateCookbook && !authStore.user?.admin_override" class="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex">
          <ExclamationTriangleIcon class="h-5 w-5 text-yellow-400 mt-0.5" />
          <div class="ml-3">
            <h3 class="text-sm font-medium text-yellow-800">
              Cookbook limit reached
            </h3>
            <div class="mt-2 text-sm text-yellow-700">
              <p>
                You've reached your limit of {{ subscriptionLimits.cookbooks }} cookbooks. 
                <router-link :to="{ name: 'subscription' }" class="font-medium underline">
                  Upgrade your subscription
                </router-link> 
                to create unlimited cookbooks.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
      v-if="cookbookToDelete"
      :is-open="!!cookbookToDelete"
      title="Delete Cookbook"
      :message="`Are you sure you want to delete '${cookbookToDelete.name}'? This action cannot be undone.`"
      confirm-text="Delete"
      confirm-class="bg-red-600 hover:bg-red-700"
      @confirm="confirmDeleteCookbook"
      @cancel="cookbookToDelete = null"
    />
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useCookbookStore } from '../../stores/cookbooks';
import {
  PlusIcon,
  MagnifyingGlassIcon,
  Squares2X2Icon,
  Bars3Icon,
  DocumentTextIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';
import CookbookCard from '../../components/cookbooks/CookbookCard.vue';
import CookbookListItem from '../../components/cookbooks/CookbookListItem.vue';
import ConfirmationModal from '../../components/ui/ConfirmationModal.vue';

export default {
  name: 'CookbooksIndex',
  components: {
    PlusIcon,
    MagnifyingGlassIcon,
    Squares2X2Icon,
    Bars3Icon,
    DocumentTextIcon,
    ExclamationTriangleIcon,
    CookbookCard,
    CookbookListItem,
    ConfirmationModal
  },
  setup() {
    const route = useRoute();
    const router = useRouter();
    const authStore = useAuthStore();
    const cookbookStore = useCookbookStore();

    // Reactive data
    const searchQuery = ref('');
    const viewMode = ref('grid');
    const sortBy = ref('updated_at');
    const cookbookToDelete = ref(null);

    // Computed properties
    const isPublicView = computed(() => route.query.type === 'public');
    const loading = computed(() => cookbookStore.loading);
    const subscriptionLimits = computed(() => authStore.subscriptionLimits);

    const allCookbooks = computed(() => {
      return isPublicView.value ? cookbookStore.publicCookbooks : cookbookStore.cookbooks;
    });

    const displayedCookbooks = computed(() => {
      let cookbooks = [...allCookbooks.value];

      // Apply search
      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        cookbooks = cookbooks.filter(cookbook =>
          cookbook.name.toLowerCase().includes(query) ||
          cookbook.description?.toLowerCase().includes(query)
        );
      }

      // Apply sorting
      cookbooks.sort((a, b) => {
        switch (sortBy.value) {
          case 'name':
            return a.name.localeCompare(b.name);
          case 'recipe_count': {
            const aCount = a.recipe_ids?.length || 0;
            const bCount = b.recipe_ids?.length || 0;
            return bCount - aCount;
          }
          case 'created_at':
            return new Date(b.created_at) - new Date(a.created_at);
          case 'updated_at':
          default:
            return new Date(b.updated_at) - new Date(a.updated_at);
        }
      });

      return cookbooks;
    });

    const canCreateCookbook = computed(() => {
      const limits = subscriptionLimits.value;
      const currentCount = cookbookStore.cookbooks.length;
      return limits.cookbooks === -1 || currentCount < limits.cookbooks;
    });

    const emptyStateMessage = computed(() => {
      if (isPublicView.value) {
        return searchQuery.value
          ? 'Try adjusting your search to find cookbooks.'
          : 'No public cookbooks have been shared yet.';
      } else {
        return searchQuery.value
          ? 'No cookbooks match your search criteria. Try adjusting your search.'
          : 'Get started by creating your first cookbook to organize your recipes.';
      }
    });

    // Methods
    const loadCookbooks = async () => {
      if (isPublicView.value) {
        await cookbookStore.fetchPublicCookbooks();
      } else {
        await cookbookStore.fetchCookbooks();
      }
    };

    const handleSearch = () => {
      // Debounced search could be implemented here
    };

    const sortCookbooks = () => {
      // Sorting is applied reactively through computed property
    };

    const editCookbook = (cookbook) => {
      router.push({ name: 'cookbook-edit', params: { id: cookbook._id } });
    };

    const deleteCookbook = (cookbook) => {
      cookbookToDelete.value = cookbook;
    };

    const confirmDeleteCookbook = async () => {
      if (cookbookToDelete.value) {
        const result = await cookbookStore.deleteCookbook(cookbookToDelete.value._id);
        if (result.success) {
          window.$toast?.success('Cookbook deleted', 'The cookbook has been successfully deleted.');
        } else {
          window.$toast?.error('Delete failed', result.message);
        }
        cookbookToDelete.value = null;
      }
    };

    const toggleCookbookPrivacy = async (cookbook) => {
      const result = await cookbookStore.toggleCookbookPrivacy(cookbook._id);
      if (result.success) {
        const status = result.cookbook.is_private ? 'private' : 'public';
        window.$toast?.success('Privacy updated', `Cookbook is now ${status}.`);
      } else {
        window.$toast?.error('Update failed', result.message);
      }
    };

    // Watchers
    watch(() => route.query.type, () => {
      loadCookbooks();
    });

    // Lifecycle
    onMounted(() => {
      loadCookbooks();
    });

    return {
      // Data
      searchQuery,
      viewMode,
      sortBy,
      cookbookToDelete,
      
      // Computed
      isPublicView,
      loading,
      displayedCookbooks,
      canCreateCookbook,
      emptyStateMessage,
      subscriptionLimits,
      authStore,
      
      // Methods
      handleSearch,
      sortCookbooks,
      editCookbook,
      deleteCookbook,
      confirmDeleteCookbook,
      toggleCookbookPrivacy
    };
  }
};
</script>
