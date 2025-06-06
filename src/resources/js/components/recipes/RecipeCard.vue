<template>
  <div class="recipe-card group">
    <div class="card-body">
      <!-- Recipe Image Placeholder -->
      <div class="w-full h-48 bg-gray-100 rounded-lg mb-4 flex items-center justify-center">
        <PhotoIcon class="w-12 h-12 text-gray-400" />
      </div>

      <!-- Recipe Header -->
      <div class="flex items-start justify-between mb-2">
        <h3 class="text-lg font-semibold text-gray-900 group-hover:text-recipe-primary-600 transition-colors cursor-pointer"
            @click="viewRecipe">
          {{ recipe.name }}
        </h3>
        
        <!-- Privacy Badge -->
        <span
          v-if="showPrivacy && recipe.is_private"
          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600"
        >
          <LockClosedIcon class="w-3 h-3 mr-1" />
          Private
        </span>
      </div>

      <!-- Recipe Info -->
      <div class="flex items-center text-sm text-gray-600 mb-3">
        <UsersIcon class="w-4 h-4 mr-1" />
        <span>{{ recipe.servings || 'N/A' }} servings</span>
        
        <span class="mx-2">•</span>
        
        <ClockIcon class="w-4 h-4 mr-1" />
        <span>{{ formatDate(recipe.created_at) }}</span>
      </div>

      <!-- Recipe Description -->
      <p class="text-gray-600 text-sm mb-4 line-clamp-3">
        {{ recipe.instructions?.substring(0, 150) }}{{ recipe.instructions?.length > 150 ? '...' : '' }}
      </p>

      <!-- Recipe Tags -->
      <div v-if="recipe.tags?.length" class="flex flex-wrap gap-1 mb-4">
        <span
          v-for="tag in recipe.tags.slice(0, 3)"
          :key="tag"
          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-recipe-primary-100 text-recipe-primary-700"
        >
          {{ tag }}
        </span>
        <span v-if="recipe.tags.length > 3" class="text-xs text-gray-500">
          +{{ recipe.tags.length - 3 }} more
        </span>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center justify-between pt-4 border-t border-gray-200">
        <button
          @click="viewRecipe"
          class="text-recipe-primary-600 hover:text-recipe-primary-700 text-sm font-medium"
        >
          View Recipe
        </button>

        <div v-if="showPrivacy" class="flex items-center space-x-2">
          <!-- Privacy Toggle -->
          <button
            v-if="canTogglePrivacy"
            @click="$emit('toggle-privacy', recipe)"
            :title="recipe.is_private ? 'Make Public' : 'Make Private'"
            class="p-1.5 text-gray-400 hover:text-gray-600 rounded-md hover:bg-gray-100"
          >
            <LockClosedIcon v-if="recipe.is_private" class="w-4 h-4" />
            <LockOpenIcon v-else class="w-4 h-4" />
          </button>

          <!-- Edit Button -->
          <button
            @click="$emit('edit', recipe)"
            title="Edit Recipe"
            class="p-1.5 text-gray-400 hover:text-gray-600 rounded-md hover:bg-gray-100"
          >
            <PencilIcon class="w-4 h-4" />
          </button>

          <!-- Delete Button -->
          <button
            @click="$emit('delete', recipe)"
            title="Delete Recipe"
            class="p-1.5 text-gray-400 hover:text-red-600 rounded-md hover:bg-gray-100"
          >
            <TrashIcon class="w-4 h-4" />
          </button>

          <!-- Menu Button -->
          <div class="relative" ref="menuRef">
            <button
              @click="showMenu = !showMenu"
              class="p-1.5 text-gray-400 hover:text-gray-600 rounded-md hover:bg-gray-100"
            >
              <EllipsisVerticalIcon class="w-4 h-4" />
            </button>

            <!-- Dropdown Menu -->
            <div
              v-show="showMenu"
              class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 py-1 z-10"
            >
              <button
                @click="printRecipe"
                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              >
                <PrinterIcon class="w-4 h-4 inline mr-2" />
                Print Recipe
              </button>
              
              <button
                @click="exportRecipe"
                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              >
                <ArrowDownTrayIcon class="w-4 h-4 inline mr-2" />
                Export PDF
              </button>
              
              <button
                @click="shareRecipe"
                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              >
                <ShareIcon class="w-4 h-4 inline mr-2" />
                Share Recipe
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import {
  PhotoIcon,
  UsersIcon,
  ClockIcon,
  LockClosedIcon,
  LockOpenIcon,
  PencilIcon,
  TrashIcon,
  EllipsisVerticalIcon,
  PrinterIcon,
  ArrowDownTrayIcon,
  ShareIcon
} from '@heroicons/vue/24/outline';

export default {
  name: 'RecipeCard',
  components: {
    PhotoIcon,
    UsersIcon,
    ClockIcon,
    LockClosedIcon,
    LockOpenIcon,
    PencilIcon,
    TrashIcon,
    EllipsisVerticalIcon,
    PrinterIcon,
    ArrowDownTrayIcon,
    ShareIcon
  },
  props: {
    recipe: {
      type: Object,
      required: true
    },
    showPrivacy: {
      type: Boolean,
      default: false
    }
  },
  emits: ['edit', 'delete', 'toggle-privacy'],
  setup(props, { emit }) {
    const router = useRouter();
    const authStore = useAuthStore();
    const showMenu = ref(false);
    const menuRef = ref(null);

    const canTogglePrivacy = computed(() => {
      return authStore.canCreatePrivateContent;
    });

    const viewRecipe = () => {
      router.push({ name: 'recipe-show', params: { id: props.recipe._id } });
    };

    const printRecipe = () => {
      window.open(`/api/v1/recipes/${props.recipe._id}/print`, '_blank');
      showMenu.value = false;
    };

    const exportRecipe = () => {
      window.open(`/api/v1/recipes/${props.recipe._id}/export/pdf`, '_blank');
      showMenu.value = false;
    };

    const shareRecipe = async () => {
      const url = `${window.location.origin}/recipes/${props.recipe._id}`;
      
      if (navigator.share) {
        try {
          await navigator.share({
            title: props.recipe.name,
            text: `Check out this recipe: ${props.recipe.name}`,
            url: url
          });
        } catch (error) {
          // Fallback to copying to clipboard
          copyToClipboard(url);
        }
      } else {
        copyToClipboard(url);
      }
      
      showMenu.value = false;
    };

    const copyToClipboard = async (text) => {
      try {
        await navigator.clipboard.writeText(text);
        window.$toast?.success('Link copied', 'Recipe link copied to clipboard!');
      } catch (error) {
        window.$toast?.error('Copy failed', 'Unable to copy link to clipboard.');
      }
    };

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

    // Close menu when clicking outside
    const handleClickOutside = (event) => {
      if (menuRef.value && !menuRef.value.contains(event.target)) {
        showMenu.value = false;
      }
    };

    onMounted(() => {
      document.addEventListener('click', handleClickOutside);
    });

    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside);
    });

    return {
      showMenu,
      menuRef,
      canTogglePrivacy,
      viewRecipe,
      printRecipe,
      exportRecipe,
      shareRecipe,
      formatDate
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
