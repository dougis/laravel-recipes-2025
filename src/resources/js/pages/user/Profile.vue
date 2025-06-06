<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-display font-bold text-gray-900">Profile Settings</h1>
        <p class="mt-2 text-gray-600">Manage your account information and preferences</p>
      </div>

      <div class="space-y-8">
        <!-- Profile Information -->
        <div class="card">
          <div class="card-header">
            <h2 class="text-xl font-semibold text-gray-900">Profile Information</h2>
            <p class="text-sm text-gray-600">Update your personal information</p>
          </div>
          <div class="card-body">
            <form @submit.prevent="updateProfile" class="space-y-6">
              <!-- Profile Picture -->
              <div class="flex items-center space-x-6">
                <div class="flex-shrink-0">
                  <div class="w-20 h-20 bg-recipe-primary-600 rounded-full flex items-center justify-center">
                    <span class="text-white text-2xl font-semibold">
                      {{ profileForm.name?.charAt(0)?.toUpperCase() || 'U' }}
                    </span>
                  </div>
                </div>
                <div>
                  <h3 class="text-lg font-medium text-gray-900">{{ profileForm.name }}</h3>
                  <p class="text-sm text-gray-600">{{ user?.email }}</p>
                  <p class="text-sm text-recipe-primary-600 mt-1">{{ subscriptionTierName }}</p>
                </div>
              </div>

              <!-- Name -->
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                  Full Name
                </label>
                <input
                  id="name"
                  v-model="profileForm.name"
                  type="text"
                  required
                  class="form-input"
                  :class="{ 'border-red-500': profileErrors.name }"
                  placeholder="Enter your full name"
                />
                <p v-if="profileErrors.name" class="mt-1 text-sm text-red-600">
                  {{ profileErrors.name[0] }}
                </p>
              </div>

              <!-- Email -->
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                  Email Address
                </label>
                <input
                  id="email"
                  v-model="profileForm.email"
                  type="email"
                  required
                  class="form-input"
                  :class="{ 'border-red-500': profileErrors.email }"
                  placeholder="Enter your email address"
                />
                <p v-if="profileErrors.email" class="mt-1 text-sm text-red-600">
                  {{ profileErrors.email[0] }}
                </p>
              </div>

              <!-- Actions -->
              <div class="flex items-center justify-end space-x-4">
                <button
                  type="button"
                  @click="resetProfileForm"
                  class="btn-outline"
                >
                  Reset
                </button>
                <button
                  type="submit"
                  :disabled="profileLoading"
                  class="btn-primary flex items-center"
                >
                  <div v-if="profileLoading" class="loading-spinner w-5 h-5 mr-2"></div>
                  {{ profileLoading ? 'Updating...' : 'Update Profile' }}
                </button>
              </div>

              <!-- Error Display -->
              <div v-if="profileErrorMessage" class="rounded-md bg-red-50 p-4">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <XCircleIcon class="h-5 w-5 text-red-400" />
                  </div>
                  <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">
                      Profile update failed
                    </h3>
                    <div class="mt-2 text-sm text-red-700">
                      <p>{{ profileErrorMessage }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Change Password -->
        <div class="card">
          <div class="card-header">
            <h2 class="text-xl font-semibold text-gray-900">Change Password</h2>
            <p class="text-sm text-gray-600">Update your password to keep your account secure</p>
          </div>
          <div class="card-body">
            <form @submit.prevent="changePassword" class="space-y-6">
              <!-- Current Password -->
              <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">
                  Current Password
                </label>
                <input
                  id="current_password"
                  v-model="passwordForm.current_password"
                  type="password"
                  required
                  class="form-input"
                  :class="{ 'border-red-500': passwordErrors.current_password }"
                  placeholder="Enter your current password"
                />
                <p v-if="passwordErrors.current_password" class="mt-1 text-sm text-red-600">
                  {{ passwordErrors.current_password[0] }}
                </p>
              </div>

              <!-- New Password -->
              <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">
                  New Password
                </label>
                <input
                  id="new_password"
                  v-model="passwordForm.new_password"
                  type="password"
                  required
                  class="form-input"
                  :class="{ 'border-red-500': passwordErrors.new_password }"
                  placeholder="Enter your new password"
                />
                <p v-if="passwordErrors.new_password" class="mt-1 text-sm text-red-600">
                  {{ passwordErrors.new_password[0] }}
                </p>
                <p class="mt-1 text-sm text-gray-500">
                  Password must be at least 8 characters long
                </p>
              </div>

              <!-- Confirm New Password -->
              <div>
                <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                  Confirm New Password
                </label>
                <input
                  id="new_password_confirmation"
                  v-model="passwordForm.new_password_confirmation"
                  type="password"
                  required
                  class="form-input"
                  :class="{ 'border-red-500': passwordErrors.new_password_confirmation }"
                  placeholder="Confirm your new password"
                />
                <p v-if="passwordErrors.new_password_confirmation" class="mt-1 text-sm text-red-600">
                  {{ passwordErrors.new_password_confirmation[0] }}
                </p>
              </div>

              <!-- Actions -->
              <div class="flex items-center justify-end space-x-4">
                <button
                  type="button"
                  @click="resetPasswordForm"
                  class="btn-outline"
                >
                  Reset
                </button>
                <button
                  type="submit"
                  :disabled="passwordLoading"
                  class="btn-primary flex items-center"
                >
                  <div v-if="passwordLoading" class="loading-spinner w-5 h-5 mr-2"></div>
                  {{ passwordLoading ? 'Changing...' : 'Change Password' }}
                </button>
              </div>

              <!-- Error Display -->
              <div v-if="passwordErrorMessage" class="rounded-md bg-red-50 p-4">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <XCircleIcon class="h-5 w-5 text-red-400" />
                  </div>
                  <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">
                      Password change failed
                    </h3>
                    <div class="mt-2 text-sm text-red-700">
                      <p>{{ passwordErrorMessage }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Account Statistics -->
        <div class="card">
          <div class="card-header">
            <h2 class="text-xl font-semibold text-gray-900">Account Statistics</h2>
          </div>
          <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="text-center">
                <div class="text-2xl font-bold text-recipe-primary-600">{{ stats.recipes }}</div>
                <div class="text-sm text-gray-600">Total Recipes</div>
                <div v-if="subscriptionLimits.recipes > 0" class="text-xs text-gray-500 mt-1">
                  {{ subscriptionLimits.recipes - stats.recipes }} remaining
                </div>
              </div>
              
              <div class="text-center">
                <div class="text-2xl font-bold text-recipe-secondary-600">{{ stats.cookbooks }}</div>
                <div class="text-sm text-gray-600">Total Cookbooks</div>
                <div v-if="subscriptionLimits.cookbooks > 0" class="text-xs text-gray-500 mt-1">
                  {{ subscriptionLimits.cookbooks - stats.cookbooks }} remaining
                </div>
              </div>
              
              <div class="text-center">
                <div class="text-2xl font-bold text-green-600">{{ daysSinceJoined }}</div>
                <div class="text-sm text-gray-600">Days Since Joined</div>
                <div class="text-xs text-gray-500 mt-1">
                  Joined {{ formatDate(user?.created_at) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Subscription Info -->
        <div class="card">
          <div class="card-header">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-xl font-semibold text-gray-900">Subscription</h2>
                <p class="text-sm text-gray-600">Manage your subscription and billing</p>
              </div>
              <router-link :to="{ name: 'subscription' }" class="btn-primary">
                Manage Subscription
              </router-link>
            </div>
          </div>
          <div class="card-body">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-medium text-gray-900">{{ subscriptionTierName }}</h3>
                <p class="text-sm text-gray-600">{{ subscriptionDescription }}</p>
              </div>
              <div class="text-right">
                <div class="text-lg font-semibold text-gray-900">{{ subscriptionPrice }}</div>
                <div class="text-sm text-gray-600">{{ subscriptionBilling }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Danger Zone -->
        <div class="card border-red-200">
          <div class="card-header bg-red-50">
            <h2 class="text-xl font-semibold text-red-900">Danger Zone</h2>
            <p class="text-sm text-red-700">Irreversible and destructive actions</p>
          </div>
          <div class="card-body">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-medium text-gray-900">Delete Account</h3>
                <p class="text-sm text-gray-600">
                  Permanently delete your account and all associated data. This action cannot be undone.
                </p>
              </div>
              <button
                @click="showDeleteModal = true"
                class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition-colors"
              >
                Delete Account
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Account Confirmation Modal -->
    <ConfirmationModal
      v-if="showDeleteModal"
      :is-open="showDeleteModal"
      title="Delete Account"
      message="Are you sure you want to delete your account? This will permanently delete all your recipes, cookbooks, and account data. This action cannot be undone."
      confirm-text="Delete Account"
      confirm-class="bg-red-600 hover:bg-red-700"
      @confirm="deleteAccount"
      @cancel="showDeleteModal = false"
    />
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useRecipeStore } from '../../stores/recipes';
import { useCookbookStore } from '../../stores/cookbooks';
import { XCircleIcon } from '@heroicons/vue/24/outline';
import ConfirmationModal from '../../components/ui/ConfirmationModal.vue';

export default {
  name: 'Profile',
  components: {
    XCircleIcon,
    ConfirmationModal
  },
  setup() {
    const router = useRouter();
    const authStore = useAuthStore();
    const recipeStore = useRecipeStore();
    const cookbookStore = useCookbookStore();

    const profileLoading = ref(false);
    const passwordLoading = ref(false);
    const profileErrors = ref({});
    const passwordErrors = ref({});
    const profileErrorMessage = ref('');
    const passwordErrorMessage = ref('');
    const showDeleteModal = ref(false);

    const profileForm = reactive({
      name: '',
      email: ''
    });

    const passwordForm = reactive({
      current_password: '',
      new_password: '',
      new_password_confirmation: ''
    });

    const stats = ref({
      recipes: 0,
      cookbooks: 0
    });

    const user = computed(() => authStore.user);
    const subscriptionLimits = computed(() => authStore.subscriptionLimits);
    
    const subscriptionTierName = computed(() => {
      if (authStore.user?.admin_override) return 'Admin Override';
      
      const tier = authStore.subscriptionTier;
      switch (tier) {
        case 0: return 'Free Tier';
        case 1: return 'Premium Tier';
        case 2: return 'Professional Tier';
        default: return 'Free Tier';
      }
    });

    const subscriptionDescription = computed(() => {
      if (authStore.user?.admin_override) return 'All features unlocked';
      
      const tier = authStore.subscriptionTier;
      switch (tier) {
        case 0: return 'Basic features with limited recipes and cookbooks';
        case 1: return 'Enhanced features with unlimited recipes';
        case 2: return 'All features including privacy controls and unlimited everything';
        default: return 'Basic features';
      }
    });

    const subscriptionPrice = computed(() => {
      if (authStore.user?.admin_override) return 'Free';
      
      const tier = authStore.subscriptionTier;
      switch (tier) {
        case 0: return 'Free';
        case 1: return '$9.99';
        case 2: return '$19.99';
        default: return 'Free';
      }
    });

    const subscriptionBilling = computed(() => {
      const tier = authStore.subscriptionTier;
      if (tier === 0 || authStore.user?.admin_override) return '';
      return 'per month';
    });

    const daysSinceJoined = computed(() => {
      if (!user.value?.created_at) return 0;
      const joinDate = new Date(user.value.created_at);
      const now = new Date();
      const diffTime = Math.abs(now - joinDate);
      return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    });

    const populateProfileForm = () => {
      if (user.value) {
        profileForm.name = user.value.name || '';
        profileForm.email = user.value.email || '';
      }
    };

    const resetProfileForm = () => {
      populateProfileForm();
      profileErrors.value = {};
      profileErrorMessage.value = '';
    };

    const resetPasswordForm = () => {
      passwordForm.current_password = '';
      passwordForm.new_password = '';
      passwordForm.new_password_confirmation = '';
      passwordErrors.value = {};
      passwordErrorMessage.value = '';
    };

    const updateProfile = async () => {
      profileLoading.value = true;
      profileErrors.value = {};
      profileErrorMessage.value = '';

      const result = await authStore.updateProfile(profileForm);

      if (result.success) {
        window.$toast?.success('Profile Updated', 'Your profile has been successfully updated.');
      } else {
        if (result.errors) {
          profileErrors.value = result.errors;
        } else {
          profileErrorMessage.value = result.message;
        }
        window.$toast?.error('Update Failed', result.message);
      }

      profileLoading.value = false;
    };

    const changePassword = async () => {
      passwordLoading.value = true;
      passwordErrors.value = {};
      passwordErrorMessage.value = '';

      // This would need to be implemented in the auth store
      // For now, just simulate the API call
      try {
        const response = await window.axios.put('/users/password', passwordForm);
        
        if (response.data.success) {
          window.$toast?.success('Password Changed', 'Your password has been successfully updated.');
          resetPasswordForm();
        }
      } catch (error) {
        if (error.response?.data?.errors) {
          passwordErrors.value = error.response.data.errors;
        } else {
          passwordErrorMessage.value = error.response?.data?.message || 'Password change failed';
        }
        window.$toast?.error('Update Failed', passwordErrorMessage.value);
      }

      passwordLoading.value = false;
    };

    const deleteAccount = async () => {
      try {
        const response = await window.axios.delete('/users/account');
        
        if (response.data.success) {
          window.$toast?.success('Account Deleted', 'Your account has been successfully deleted.');
          await authStore.logout();
          router.push({ name: 'home' });
        }
      } catch (error) {
        window.$toast?.error('Delete Failed', error.response?.data?.message || 'Account deletion failed');
      }
      
      showDeleteModal.value = false;
    };

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    };

    const loadStats = async () => {
      // Load user statistics
      await Promise.all([
        recipeStore.fetchRecipes(),
        cookbookStore.fetchCookbooks()
      ]);

      stats.value = {
        recipes: recipeStore.recipes.length,
        cookbooks: cookbookStore.cookbooks.length
      };
    };

    onMounted(() => {
      populateProfileForm();
      loadStats();
    });

    return {
      profileForm,
      passwordForm,
      profileLoading,
      passwordLoading,
      profileErrors,
      passwordErrors,
      profileErrorMessage,
      passwordErrorMessage,
      showDeleteModal,
      stats,
      user,
      subscriptionLimits,
      subscriptionTierName,
      subscriptionDescription,
      subscriptionPrice,
      subscriptionBilling,
      daysSinceJoined,
      resetProfileForm,
      resetPasswordForm,
      updateProfile,
      changePassword,
      deleteAccount,
      formatDate
    };
  }
};
</script>
