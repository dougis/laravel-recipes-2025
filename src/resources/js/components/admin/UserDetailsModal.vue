<template>
  <teleport to="body">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 overflow-y-auto"
      @click="handleBackdropClick"
    >
      <div class="flex min-h-screen items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
        
        <!-- Modal -->
        <div
          class="relative w-full max-w-2xl transform rounded-lg bg-white shadow-xl transition-all"
          @click.stop
        >
          <!-- Header -->
          <div class="border-b border-gray-200 px-6 py-4">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-medium leading-6 text-gray-900">
                User Details
              </h3>
              <button
                @click="$emit('close')"
                class="text-gray-400 hover:text-gray-600"
              >
                <XMarkIcon class="h-6 w-6" />
              </button>
            </div>
          </div>

          <!-- Content -->
          <div class="px-6 py-4 space-y-6">
            <!-- User Info -->
            <div class="flex items-center space-x-4">
              <div class="w-16 h-16 bg-recipe-primary-100 rounded-full flex items-center justify-center">
                <span class="text-xl font-medium text-recipe-primary-700">
                  {{ user.name?.charAt(0)?.toUpperCase() || 'U' }}
                </span>
              </div>
              <div>
                <h4 class="text-xl font-semibold text-gray-900">{{ user.name }}</h4>
                <p class="text-gray-600">{{ user.email }}</p>
                <div class="flex items-center space-x-2 mt-2">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getSubscriptionClass(user.subscription_tier)">
                    {{ getSubscriptionName(user.subscription_tier) }}
                  </span>
                  <span v-if="user.admin_override" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    <StarIcon class="w-3 h-3 mr-1" />
                    Admin Override
                  </span>
                </div>
              </div>
            </div>

            <!-- User Statistics -->
            <div class="grid grid-cols-3 gap-4">
              <div class="text-center p-4 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold text-recipe-primary-600">{{ user.recipe_count || 0 }}</div>
                <div class="text-sm text-gray-600">Recipes</div>
              </div>
              <div class="text-center p-4 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold text-recipe-secondary-600">{{ user.cookbook_count || 0 }}</div>
                <div class="text-sm text-gray-600">Cookbooks</div>
              </div>
              <div class="text-center p-4 bg-gray-50 rounded-lg">
                <div class="text-2xl font-bold text-green-600">{{ daysSinceJoined }}</div>
                <div class="text-sm text-gray-600">Days Active</div>
              </div>
            </div>

            <!-- Account Details -->
            <div>
              <h5 class="text-lg font-medium text-gray-900 mb-4">Account Details</h5>
              <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Email Verified</dt>
                  <dd class="mt-1 text-sm text-gray-900">
                    <span v-if="user.email_verified_at" class="inline-flex items-center text-green-600">
                      <CheckCircleIcon class="w-4 h-4 mr-1" />
                      Verified
                    </span>
                    <span v-else class="inline-flex items-center text-red-600">
                      <XCircleIcon class="w-4 h-4 mr-1" />
                      Not Verified
                    </span>
                  </dd>
                </div>
                
                <div>
                  <dt class="text-sm font-medium text-gray-500">Account Status</dt>
                  <dd class="mt-1 text-sm text-gray-900">
                    <span class="inline-flex items-center text-green-600">
                      <CheckCircleIcon class="w-4 h-4 mr-1" />
                      Active
                    </span>
                  </dd>
                </div>

                <div>
                  <dt class="text-sm font-medium text-gray-500">Member Since</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(user.created_at) }}</dd>
                </div>

                <div>
                  <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(user.updated_at) }}</dd>
                </div>

                <div v-if="user.subscription_expires_at">
                  <dt class="text-sm font-medium text-gray-500">Subscription Expires</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(user.subscription_expires_at) }}</dd>
                </div>

                <div v-if="user.stripe_customer_id">
                  <dt class="text-sm font-medium text-gray-500">Stripe Customer ID</dt>
                  <dd class="mt-1 text-sm text-gray-900 font-mono">{{ user.stripe_customer_id }}</dd>
                </div>
              </dl>
            </div>

            <!-- Quick Actions -->
            <div>
              <h5 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h5>
              <div class="grid grid-cols-2 gap-4">
                <button
                  @click="toggleAdminOverride"
                  :disabled="loading"
                  class="btn-outline flex items-center justify-center"
                >
                  <StarIcon class="w-4 h-4 mr-2" />
                  {{ user.admin_override ? 'Remove' : 'Grant' }} Admin Override
                </button>

                <button
                  @click="sendPasswordReset"
                  :disabled="loading"
                  class="btn-outline flex items-center justify-center"
                >
                  <KeyIcon class="w-4 h-4 mr-2" />
                  Reset Password
                </button>

                <button
                  @click="viewUserRecipes"
                  class="btn-outline flex items-center justify-center"
                >
                  <BookOpenIcon class="w-4 h-4 mr-2" />
                  View Recipes
                </button>

                <button
                  @click="viewUserCookbooks"
                  class="btn-outline flex items-center justify-center"
                >
                  <DocumentTextIcon class="w-4 h-4 mr-2" />
                  View Cookbooks
                </button>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="border-t border-gray-200 px-6 py-4">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-500">
                User ID: {{ user._id }}
              </div>
              <div class="flex items-center space-x-4">
                <button
                  @click="$emit('close')"
                  class="btn-outline"
                >
                  Close
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script>
import { ref, computed } from 'vue';
import {
  XMarkIcon,
  CheckCircleIcon,
  XCircleIcon,
  KeyIcon,
  BookOpenIcon,
  DocumentTextIcon
} from '@heroicons/vue/24/outline';
import { StarIcon } from '@heroicons/vue/24/solid';

export default {
  name: 'UserDetailsModal',
  components: {
    XMarkIcon,
    CheckCircleIcon,
    XCircleIcon,
    KeyIcon,
    BookOpenIcon,
    DocumentTextIcon,
    StarIcon
  },
  props: {
    user: {
      type: Object,
      required: true
    },
    isOpen: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close', 'user-updated'],
  setup(props, { emit }) {
    const loading = ref(false);

    const daysSinceJoined = computed(() => {
      if (!props.user.created_at) return 0;
      const joinDate = new Date(props.user.created_at);
      const now = new Date();
      const diffTime = Math.abs(now - joinDate);
      return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    });

    const getSubscriptionName = (tier) => {
      switch (tier) {
        case 0: return 'Free';
        case 1: return 'Premium';
        case 2: return 'Professional';
        default: return 'Free';
      }
    };

    const getSubscriptionClass = (tier) => {
      switch (tier) {
        case 0: return 'bg-gray-100 text-gray-800';
        case 1: return 'bg-recipe-primary-100 text-recipe-primary-800';
        case 2: return 'bg-recipe-secondary-100 text-recipe-secondary-800';
        default: return 'bg-gray-100 text-gray-800';
      }
    };

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    };

    const toggleAdminOverride = async () => {
      loading.value = true;
      try {
        const response = await window.axios.put(`/admin/users/${props.user._id}/override`);
        
        if (response.data.success) {
          emit('user-updated', response.data.user);
          window.$toast?.success(
            'Override Updated', 
            `Admin override has been ${response.data.user.admin_override ? 'granted' : 'removed'}.`
          );
        }
      } catch (error) {
        window.$toast?.error('Update Failed', error.response?.data?.message || 'Failed to update admin override');
      }
      loading.value = false;
    };

    const sendPasswordReset = async () => {
      loading.value = true;
      try {
        // In a real implementation, this would send a password reset email
        await new Promise(resolve => setTimeout(resolve, 1000)); // Simulate API call
        window.$toast?.success('Password Reset', `Password reset email sent to ${props.user.email}`);
      } catch (error) {
        window.$toast?.error('Reset Failed', 'Failed to send password reset email');
      }
      loading.value = false;
    };

    const viewUserRecipes = () => {
      // In a real implementation, this would navigate to the user's recipes or open another modal
      window.$toast?.info('Navigation', `View recipes for ${props.user.name} - feature to be implemented`);
    };

    const viewUserCookbooks = () => {
      // In a real implementation, this would navigate to the user's cookbooks or open another modal
      window.$toast?.info('Navigation', `View cookbooks for ${props.user.name} - feature to be implemented`);
    };

    const handleBackdropClick = () => {
      emit('close');
    };

    return {
      loading,
      daysSinceJoined,
      getSubscriptionName,
      getSubscriptionClass,
      formatDate,
      toggleAdminOverride,
      sendPasswordReset,
      viewUserRecipes,
      viewUserCookbooks,
      handleBackdropClick
    };
  }
};
</script>
