<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-display font-bold text-gray-900">Subscription Management</h1>
        <p class="mt-2 text-gray-600">Manage your subscription and billing preferences</p>
      </div>

      <!-- Current Subscription Status -->
      <div class="mb-8">
        <div class="card">
          <div class="card-body">
            <div class="flex items-center justify-between">
              <div>
                <h2 class="text-xl font-semibold text-gray-900">Current Plan</h2>
                <div class="mt-2">
                  <span class="text-2xl font-bold text-recipe-primary-600">{{ currentPlanName }}</span>
                  <span v-if="currentPlanPrice" class="text-lg text-gray-600 ml-2">{{ currentPlanPrice }}/month</span>
                </div>
                <p class="text-sm text-gray-600 mt-1">{{ currentPlanDescription }}</p>
                <div v-if="user?.admin_override" class="mt-2">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    <CrownIcon class="w-4 h-4 mr-1" />
                    Admin Override Active
                  </span>
                </div>
              </div>
              
              <div class="text-right">
                <div v-if="subscriptionStatus" class="text-sm text-gray-600">
                  Status: <span class="font-medium" :class="statusClass">{{ subscriptionStatus }}</span>
                </div>
                <div v-if="nextBillingDate" class="text-sm text-gray-600 mt-1">
                  Next billing: {{ formatDate(nextBillingDate) }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Usage Statistics -->
      <div class="mb-8">
        <div class="card">
          <div class="card-header">
            <h2 class="text-xl font-semibold text-gray-900">Usage Overview</h2>
          </div>
          <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Recipes Usage -->
              <div>
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-medium text-gray-700">Recipes</span>
                  <span class="text-sm text-gray-600">
                    {{ usage.recipes }}{{ limits.recipes > 0 ? ` / ${limits.recipes}` : '' }}
                  </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    class="h-2 rounded-full transition-all duration-300"
                    :class="recipeUsageColor"
                    :style="{ width: `${recipeUsagePercentage}%` }"
                  ></div>
                </div>
                <p v-if="limits.recipes > 0" class="text-xs text-gray-500 mt-1">
                  {{ limits.recipes - usage.recipes }} recipes remaining
                </p>
              </div>

              <!-- Cookbooks Usage -->
              <div>
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-medium text-gray-700">Cookbooks</span>
                  <span class="text-sm text-gray-600">
                    {{ usage.cookbooks }}{{ limits.cookbooks > 0 ? ` / ${limits.cookbooks}` : '' }}
                  </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    class="h-2 rounded-full transition-all duration-300"
                    :class="cookbookUsageColor"
                    :style="{ width: `${cookbookUsagePercentage}%` }"
                  ></div>
                </div>
                <p v-if="limits.cookbooks > 0" class="text-xs text-gray-500 mt-1">
                  {{ limits.cookbooks - usage.cookbooks }} cookbooks remaining
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Available Plans -->
      <div class="mb-8">
        <h2 class="text-2xl font-semibold text-gray-900 mb-6">Available Plans</h2>
        
        <div class="grid md:grid-cols-3 gap-8">
          <!-- Free Tier -->
          <div class="card" :class="{ 'ring-2 ring-recipe-primary-500': currentTier === 0 }">
            <div class="card-header text-center">
              <h3 class="text-xl font-semibold text-gray-900">Free</h3>
              <p class="text-3xl font-bold text-gray-900 mt-2">$0<span class="text-base font-normal text-gray-600">/month</span></p>
            </div>
            <div class="card-body">
              <ul class="space-y-3">
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Up to 25 recipes</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>1 cookbook</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Basic recipe management</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Print individual recipes</span>
                </li>
                <li class="flex items-center text-gray-500">
                  <XMarkIcon class="w-5 h-5 mr-3" />
                  <span>All recipes are public</span>
                </li>
              </ul>
              
              <button
                v-if="currentTier !== 0"
                @click="changePlan(0)"
                :disabled="loading"
                class="btn-outline w-full mt-6"
              >
                Downgrade to Free
              </button>
              <div v-else class="mt-6 text-center">
                <span class="text-recipe-primary-600 font-medium">Current Plan</span>
              </div>
            </div>
          </div>

          <!-- Premium Tier -->
          <div class="card border-recipe-primary-500 border-2 relative" :class="{ 'ring-2 ring-recipe-primary-500': currentTier === 1 }">
            <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
              <span class="bg-recipe-primary-600 text-white px-4 py-1 rounded-full text-sm font-medium">
                Most Popular
              </span>
            </div>
            <div class="card-header text-center">
              <h3 class="text-xl font-semibold text-gray-900">Premium</h3>
              <p class="text-3xl font-bold text-gray-900 mt-2">$9<span class="text-base font-normal text-gray-600">/month</span></p>
            </div>
            <div class="card-body">
              <ul class="space-y-3">
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Unlimited recipes</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Up to 10 cookbooks</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Advanced search & filtering</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Nutritional information</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Print cookbooks</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Export to multiple formats</span>
                </li>
                <li class="flex items-center text-gray-500">
                  <XMarkIcon class="w-5 h-5 mr-3" />
                  <span>All recipes are public</span>
                </li>
              </ul>
              
              <button
                v-if="currentTier !== 1"
                @click="changePlan(1)"
                :disabled="loading"
                class="btn-primary w-full mt-6"
              >
                {{ currentTier === 0 ? 'Upgrade to Premium' : 'Switch to Premium' }}
              </button>
              <div v-else class="mt-6 text-center">
                <span class="text-recipe-primary-600 font-medium">Current Plan</span>
              </div>
            </div>
          </div>

          <!-- Professional Tier -->
          <div class="card" :class="{ 'ring-2 ring-recipe-primary-500': currentTier === 2 }">
            <div class="card-header text-center">
              <h3 class="text-xl font-semibold text-gray-900">Professional</h3>
              <p class="text-3xl font-bold text-gray-900 mt-2">$19<span class="text-base font-normal text-gray-600">/month</span></p>
            </div>
            <div class="card-body">
              <ul class="space-y-3">
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Everything in Premium</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Unlimited cookbooks</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Privacy controls</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Recipe scaling</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Meal planning</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Custom templates</span>
                </li>
                <li class="flex items-center">
                  <CheckIcon class="w-5 h-5 text-green-500 mr-3" />
                  <span>Advanced analytics</span>
                </li>
              </ul>
              
              <button
                v-if="currentTier !== 2"
                @click="changePlan(2)"
                :disabled="loading"
                class="btn-primary w-full mt-6"
              >
                {{ currentTier === 0 ? 'Upgrade to Professional' : 'Upgrade to Professional' }}
              </button>
              <div v-else class="mt-6 text-center">
                <span class="text-recipe-primary-600 font-medium">Current Plan</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Billing History -->
      <div v-if="currentTier > 0" class="mb-8">
        <div class="card">
          <div class="card-header">
            <h2 class="text-xl font-semibold text-gray-900">Billing History</h2>
          </div>
          <div class="card-body">
            <div v-if="billingHistory.length" class="space-y-4">
              <div
                v-for="invoice in billingHistory"
                :key="invoice.id"
                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
              >
                <div>
                  <div class="font-medium text-gray-900">{{ invoice.description }}</div>
                  <div class="text-sm text-gray-600">{{ formatDate(invoice.date) }}</div>
                </div>
                <div class="text-right">
                  <div class="font-medium text-gray-900">${{ invoice.amount }}</div>
                  <div class="text-sm" :class="invoice.status === 'paid' ? 'text-green-600' : 'text-red-600'">
                    {{ invoice.status }}
                  </div>
                </div>
              </div>
            </div>
            
            <div v-else class="text-center py-8">
              <DocumentTextIcon class="w-12 h-12 text-gray-400 mx-auto mb-4" />
              <p class="text-gray-600">No billing history available</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Subscription Management Actions -->
      <div v-if="currentTier > 0 && !user?.admin_override" class="card border-yellow-200">
        <div class="card-header bg-yellow-50">
          <h2 class="text-xl font-semibold text-yellow-900">Subscription Management</h2>
        </div>
        <div class="card-body">
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="font-medium text-gray-900">Cancel Subscription</h3>
                <p class="text-sm text-gray-600">
                  Cancel your subscription. You'll continue to have access until your current billing period ends.
                </p>
              </div>
              <button
                @click="showCancelModal = true"
                class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded-lg transition-colors"
              >
                Cancel Subscription
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Cancel Subscription Modal -->
    <ConfirmationModal
      v-if="showCancelModal"
      :is-open="showCancelModal"
      title="Cancel Subscription"
      message="Are you sure you want to cancel your subscription? You'll continue to have access to premium features until your current billing period ends."
      confirm-text="Cancel Subscription"
      confirm-class="bg-yellow-600 hover:bg-yellow-700"
      @confirm="cancelSubscription"
      @cancel="showCancelModal = false"
    />
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../../stores/auth';
import { useRecipeStore } from '../../stores/recipes';
import { useCookbookStore } from '../../stores/cookbooks';
import {
  CheckIcon,
  XMarkIcon,
  DocumentTextIcon
} from '@heroicons/vue/24/outline';
import { CrownIcon } from '@heroicons/vue/24/solid';
import ConfirmationModal from '../../components/ui/ConfirmationModal.vue';

export default {
  name: 'Subscription',
  components: {
    CheckIcon,
    XMarkIcon,
    DocumentTextIcon,
    CrownIcon,
    ConfirmationModal
  },
  setup() {
    const authStore = useAuthStore();
    const recipeStore = useRecipeStore();
    const cookbookStore = useCookbookStore();

    const loading = ref(false);
    const showCancelModal = ref(false);
    
    const usage = ref({
      recipes: 0,
      cookbooks: 0
    });

    const billingHistory = ref([
      // Mock data - in real implementation, this would come from Stripe
      {
        id: 1,
        description: 'Premium Plan - Monthly',
        amount: '9.99',
        date: '2024-05-01',
        status: 'paid'
      },
      {
        id: 2,
        description: 'Premium Plan - Monthly',
        amount: '9.99',
        date: '2024-04-01',
        status: 'paid'
      }
    ]);

    const user = computed(() => authStore.user);
    const currentTier = computed(() => authStore.subscriptionTier);
    const limits = computed(() => authStore.subscriptionLimits);

    const currentPlanName = computed(() => {
      if (user.value?.admin_override) return 'Admin Override';
      
      switch (currentTier.value) {
        case 0: return 'Free';
        case 1: return 'Premium';
        case 2: return 'Professional';
        default: return 'Free';
      }
    });

    const currentPlanPrice = computed(() => {
      if (user.value?.admin_override || currentTier.value === 0) return '';
      
      switch (currentTier.value) {
        case 1: return '$9.99';
        case 2: return '$19.99';
        default: return '';
      }
    });

    const currentPlanDescription = computed(() => {
      if (user.value?.admin_override) return 'All features unlocked with administrative privileges';
      
      switch (currentTier.value) {
        case 0: return 'Basic features with limited recipes and cookbooks';
        case 1: return 'Enhanced features with unlimited recipes';
        case 2: return 'All features including privacy controls and unlimited everything';
        default: return 'Basic features';
      }
    });

    const subscriptionStatus = computed(() => {
      if (user.value?.admin_override) return 'Active';
      if (currentTier.value === 0) return null;
      return 'Active'; // In real implementation, this would come from Stripe
    });

    const statusClass = computed(() => {
      const status = subscriptionStatus.value;
      if (status === 'Active') return 'text-green-600';
      if (status === 'Canceled') return 'text-yellow-600';
      if (status === 'Past Due') return 'text-red-600';
      return 'text-gray-600';
    });

    const nextBillingDate = computed(() => {
      if (currentTier.value === 0 || user.value?.admin_override) return null;
      // Mock next billing date
      const nextMonth = new Date();
      nextMonth.setMonth(nextMonth.getMonth() + 1);
      return nextMonth.toISOString();
    });

    const recipeUsagePercentage = computed(() => {
      if (limits.value.recipes === -1) return 0;
      return Math.min((usage.value.recipes / limits.value.recipes) * 100, 100);
    });

    const cookbookUsagePercentage = computed(() => {
      if (limits.value.cookbooks === -1) return 0;
      return Math.min((usage.value.cookbooks / limits.value.cookbooks) * 100, 100);
    });

    const recipeUsageColor = computed(() => {
      const percentage = recipeUsagePercentage.value;
      if (percentage >= 90) return 'bg-red-500';
      if (percentage >= 75) return 'bg-yellow-500';
      return 'bg-recipe-primary-500';
    });

    const cookbookUsageColor = computed(() => {
      const percentage = cookbookUsagePercentage.value;
      if (percentage >= 90) return 'bg-red-500';
      if (percentage >= 75) return 'bg-yellow-500';
      return 'bg-recipe-secondary-500';
    });

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    };

    const changePlan = async (tier) => {
      loading.value = true;
      
      try {
        // In a real implementation, this would integrate with Stripe
        // For now, we'll just show a placeholder
        
        if (tier === 0) {
          // Downgrade to free
          window.$toast?.info('Downgrade Scheduled', 'Your subscription will be downgraded at the end of your current billing period.');
        } else {
          // Upgrade/change plan
          window.$toast?.info('Payment Required', 'Stripe payment integration would be implemented here.');
        }
      } catch (error) {
        window.$toast?.error('Plan Change Failed', error.message);
      }
      
      loading.value = false;
    };

    const cancelSubscription = async () => {
      loading.value = true;
      
      try {
        // In a real implementation, this would cancel the Stripe subscription
        window.$toast?.success('Subscription Canceled', 'Your subscription has been canceled. You\'ll continue to have access until your current billing period ends.');
        showCancelModal.value = false;
      } catch (error) {
        window.$toast?.error('Cancellation Failed', error.message);
      }
      
      loading.value = false;
    };

    const loadUsageData = async () => {
      await Promise.all([
        recipeStore.fetchRecipes(),
        cookbookStore.fetchCookbooks()
      ]);

      usage.value = {
        recipes: recipeStore.recipes.length,
        cookbooks: cookbookStore.cookbooks.length
      };
    };

    onMounted(() => {
      loadUsageData();
    });

    return {
      loading,
      showCancelModal,
      usage,
      billingHistory,
      user,
      currentTier,
      limits,
      currentPlanName,
      currentPlanPrice,
      currentPlanDescription,
      subscriptionStatus,
      statusClass,
      nextBillingDate,
      recipeUsagePercentage,
      cookbookUsagePercentage,
      recipeUsageColor,
      cookbookUsageColor,
      formatDate,
      changePlan,
      cancelSubscription
    };
  }
};
</script>
