<template>
  <div class="cookbook-card group">
    <div class="card-body">
      <!-- Cookbook Cover Image Placeholder -->
      <div class="w-full h-48 bg-gradient-to-br from-recipe-secondary-100 to-recipe-primary-100 rounded-lg mb-4 flex items-center justify-center">
        <DocumentTextIcon class="w-12 h-12 text-recipe-primary-600" />
      </div>

      <!-- Cookbook Header -->
      <div class="flex items-start justify-between mb-2">
        <h3 class="text-lg font-semibold text-gray-900 group-hover:text-recipe-primary-600 transition-colors cursor-pointer"
            @click="viewCookbook">
          {{ cookbook.name }}
        </h3>
        
        <!-- Privacy Badge -->
        <span
          v-if="showPrivacy && cookbook.is_private"
          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600"
        >
          <LockClosedIcon class="w-3 h-3 mr-1" />
          Private
        </span>
      </div>

      <!-- Cookbook Info -->
      <div class="flex items-center text-sm text-gray-600 mb-3">
        <BookOpenIcon class="w-4 h-4 mr-1" />
        <span>{{ recipeCount }} {{ recipeCount === 1 ? 'recipe' : 'recipes' }}</span>
        
        <span class="mx-2">•</span>
        
        <ClockIcon class="w-4 h-4 mr-1" />
        <span>{{ formatDate(cookbook.updated_at) }}</span>
      </div>

      <!-- Cookbook Description -->
      <p class="text-gray-600 text-sm mb-4 line-clamp-3">
        {{ cookbook.description || 'No description provided.' }}
      </p>

      <!-- Action Buttons -->
      <div class="flex items-center justify-between pt-4 border-t border-gray-200">
        <button
          @click="viewCookbook"
          class="text-recipe-primary-600 hover:text-recipe-primary-700 text-sm font-medium"
        >
          View Cookbook
        </button>

        <div v-if="showPrivacy" class="flex items-center space-x-2">
          <!-- Privacy Toggle -->
          <button
            v-if="canTogglePrivacy"
            @click="$emit('toggle-privacy', cookbook)"
            :title="cookbook.is_private ? 'Make Public' : 'Make Private'"
            class="p-1.5 text-gray-400 hover:text-gray-600 rounded-md hover:bg-gray-100"
          >
            <LockClosedIcon v-if="cookbook.is_private" class="w-4 h-4" />
            <LockOpenIcon v-else class="w-4 h-4" />
          </button>

          <!-- Edit Button -->
          <button
            @click="$emit('edit', cookbook)"
            title="Edit Cookbook"
            class="p-1.5 text-gray-400 hover:text-gray-600 rounded-md hover:bg-gray-100"
          >
            <PencilIcon class="w-4 h-4" />
          </button>

          <!-- Delete Button -->
          <button
            @click="$emit('delete', cookbook)"
            title="Delete Cookbook"
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
                @click="printCookbook"
                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              >
                <PrinterIcon class="w-4 h-4 inline mr-2" />
                Print Cookbook
              </button>
              
              <button
                @click="exportCookbook"
                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              >
                <ArrowDownTrayIcon class="w-4 h-4 inline mr-2" />
                Export PDF
              </button>
              
              <button
                @click="shareCookbook"
                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
              >
                <ShareIcon class="w-4 h-4 inline mr-2" />
                Share Cookbook
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
  DocumentTextIcon,
  BookOpenIcon,
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
  name: 'CookbookCard',
  components: {
    DocumentTextIcon,
    BookOpenIcon,
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
    cookbook: {
      type: Object,
      required: true
    },
    showPrivacy: {
      type: Boolean,
      default: false
    }
  },
  emits: ['edit', 'delete', 'toggle-privacy'],
  setup(props) {
    const router = useRouter();
    const authStore = useAuthStore();
    const showMenu = ref(false);
    const menuRef = ref(null);

    const canTogglePrivacy = computed(() => {
      return authStore.canCreatePrivateContent;
    });

    const recipeCount = computed(() => {
      return props.cookbook.recipe_ids?.length || 0;
    });

    const viewCookbook = () => {
      router.push({ name: 'cookbook-show', params: { id: props.cookbook._id } });
    };

    const printCookbook = () => {
      window.open(`/api/v1/cookbooks/${props.cookbook._id}/print`, '_blank');
      showMenu.value = false;
    };

    const exportCookbook = () => {
      window.open(`/api/v1/cookbooks/${props.cookbook._id}/export/pdf`, '_blank');
      showMenu.value = false;
    };

    const shareCookbook = async () => {
      const url = `${window.location.origin}/cookbooks/${props.cookbook._id}`;
      
      if (navigator.share) {
        try {
          await navigator.share({
            title: props.cookbook.name,
            text: `Check out this cookbook: ${props.cookbook.name}`,
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
        window.$toast?.success('Link copied', 'Cookbook link copied to clipboard!');
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
      recipeCount,
      viewCookbook,
      printCookbook,
      exportCookbook,
      shareCookbook,
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
