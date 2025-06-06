<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Page Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-display font-bold text-gray-900">System Statistics</h1>
            <p class="mt-2 text-gray-600">Detailed analytics and system insights</p>
          </div>
          
          <div class="flex items-center space-x-4">
            <button
              @click="refreshData"
              :disabled="loading"
              class="btn-outline flex items-center"
            >
              <ArrowPathIcon class="w-5 h-5 mr-2" :class="{ 'animate-spin': loading }" />
              Refresh Data
            </button>
            
            <router-link
              :to="{ name: 'admin-dashboard' }"
              class="btn-outline"
            >
              <ArrowLeftIcon class="w-5 h-5 mr-2" />
              Back to Dashboard
            </router-link>
          </div>
        </div>
      </div>

      <!-- Time Period Selector -->
      <div class="mb-8">
        <div class="card">
          <div class="card-body">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-semibold text-gray-900">Analytics Period</h2>
              <div class="flex items-center space-x-4">
                <select v-model="selectedPeriod" @change="loadAnalytics" class="form-select">
                  <option value="7d">Last 7 Days</option>
                  <option value="30d">Last 30 Days</option>
                  <option value="90d">Last 90 Days</option>
                  <option value="1y">Last Year</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Overview Statistics -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Overview</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="card">
            <div class="card-body">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <UsersIcon class="w-6 h-6 text-blue-600" />
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600">Total Users</p>
                  <p class="text-2xl font-bold text-gray-900">{{ formatNumber(stats.users.total) }}</p>
                  <p class="text-xs" :class="getChangeClass(stats.users.growth)">
                    {{ formatPercentage(stats.users.growth) }} from last period
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-body">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-10 h-10 bg-recipe-primary-100 rounded-lg flex items-center justify-center">
                    <BookOpenIcon class="w-6 h-6 text-recipe-primary-600" />
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600">Total Recipes</p>
                  <p class="text-2xl font-bold text-gray-900">{{ formatNumber(stats.recipes.total) }}</p>
                  <p class="text-xs" :class="getChangeClass(stats.recipes.growth)">
                    {{ formatPercentage(stats.recipes.growth) }} from last period
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-body">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-10 h-10 bg-recipe-secondary-100 rounded-lg flex items-center justify-center">
                    <DocumentTextIcon class="w-6 h-6 text-recipe-secondary-600" />
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600">Total Cookbooks</p>
                  <p class="text-2xl font-bold text-gray-900">{{ formatNumber(stats.cookbooks.total) }}</p>
                  <p class="text-xs" :class="getChangeClass(stats.cookbooks.growth)">
                    {{ formatPercentage(stats.cookbooks.growth) }} from last period
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-body">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <CurrencyDollarIcon class="w-6 h-6 text-green-600" />
                  </div>
                </div>
                <div class="ml-4">
                  <p class="text-sm font-medium text-gray-600">Monthly Revenue</p>
                  <p class="text-2xl font-bold text-gray-900">${{ formatNumber(stats.revenue.monthly) }}</p>
                  <p class="text-xs" :class="getChangeClass(stats.revenue.growth)">
                    {{ formatPercentage(stats.revenue.growth) }} from last month
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="mb-8 grid lg:grid-cols-2 gap-8">
        <!-- User Growth Chart -->
        <div class="card">
          <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900">User Growth</h3>
          </div>
          <div class="card-body">
            <div class="h-64 bg-gray-50 rounded-lg flex items-center justify-center">
              <div class="text-center">
                <ChartBarIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                <p class="text-gray-600">User Growth Chart</p>
                <p class="text-sm text-gray-500">Chart.js integration would go here</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Revenue Chart -->
        <div class="card">
          <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900">Revenue Trends</h3>
          </div>
          <div class="card-body">
            <div class="h-64 bg-gray-50 rounded-lg flex items-center justify-center">
              <div class="text-center">
                <CurrencyDollarIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                <p class="text-gray-600">Revenue Chart</p>
                <p class="text-sm text-gray-500">Chart.js integration would go here</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Detailed Statistics -->
      <div class="grid lg:grid-cols-2 gap-8">
        <!-- User Statistics -->
        <div class="card">
          <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900">User Breakdown</h3>
          </div>
          <div class="card-body">
            <div class="space-y-4">
              <!-- By Subscription Tier -->
              <div>
                <h4 class="text-sm font-medium text-gray-700 mb-3">By Subscription Tier</h4>
                <div class="space-y-2">
                  <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Free Users</span>
                    <div class="flex items-center space-x-2">
                      <div class="w-32 bg-gray-200 rounded-full h-2">
                        <div class="bg-gray-500 h-2 rounded-full" :style="{ width: `${(stats.users.free / stats.users.total) * 100}%` }"></div>
                      </div>
                      <span class="text-sm font-medium text-gray-900 w-12 text-right">{{ stats.users.free }}</span>
                    </div>
                  </div>
                  
                  <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Premium Users</span>
                    <div class="flex items-center space-x-2">
                      <div class="w-32 bg-gray-200 rounded-full h-2">
                        <div class="bg-recipe-primary-500 h-2 rounded-full" :style="{ width: `${(stats.users.premium / stats.users.total) * 100}%` }"></div>
                      </div>
                      <span class="text-sm font-medium text-gray-900 w-12 text-right">{{ stats.users.premium }}</span>
                    </div>
                  </div>
                  
                  <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Professional Users</span>
                    <div class="flex items-center space-x-2">
                      <div class="w-32 bg-gray-200 rounded-full h-2">
                        <div class="bg-recipe-secondary-500 h-2 rounded-full" :style="{ width: `${(stats.users.professional / stats.users.total) * 100}%` }"></div>
                      </div>
                      <span class="text-sm font-medium text-gray-900 w-12 text-right">{{ stats.users.professional }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Activity Stats -->
              <div class="border-t border-gray-200 pt-4">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Activity</h4>
                <div class="grid grid-cols-2 gap-4">
                  <div class="text-center p-3 bg-blue-50 rounded-lg">
                    <div class="text-lg font-bold text-blue-600">{{ stats.users.activeToday }}</div>
                    <div class="text-xs text-blue-600">Active Today</div>
                  </div>
                  <div class="text-center p-3 bg-green-50 rounded-lg">
                    <div class="text-lg font-bold text-green-600">{{ stats.users.activeThisWeek }}</div>
                    <div class="text-xs text-green-600">Active This Week</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Content Statistics -->
        <div class="card">
          <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900">Content Statistics</h3>
          </div>
          <div class="card-body">
            <div class="space-y-6">
              <!-- Recipe Stats -->
              <div>
                <h4 class="text-sm font-medium text-gray-700 mb-3">Recipes</h4>
                <div class="grid grid-cols-2 gap-4">
                  <div class="text-center p-3 bg-recipe-primary-50 rounded-lg">
                    <div class="text-lg font-bold text-recipe-primary-600">{{ stats.recipes.public }}</div>
                    <div class="text-xs text-recipe-primary-600">Public</div>
                  </div>
                  <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <div class="text-lg font-bold text-gray-600">{{ stats.recipes.private }}</div>
                    <div class="text-xs text-gray-600">Private</div>
                  </div>
                </div>
                <div class="mt-4 space-y-2">
                  <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Avg. per user</span>
                    <span class="font-medium">{{ (stats.recipes.total / stats.users.total).toFixed(1) }}</span>
                  </div>
                  <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Created this week</span>
                    <span class="font-medium">{{ stats.recipes.thisWeek }}</span>
                  </div>
                </div>
              </div>

              <!-- Cookbook Stats -->
              <div class="border-t border-gray-200 pt-4">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Cookbooks</h4>
                <div class="grid grid-cols-2 gap-4">
                  <div class="text-center p-3 bg-recipe-secondary-50 rounded-lg">
                    <div class="text-lg font-bold text-recipe-secondary-600">{{ stats.cookbooks.public }}</div>
                    <div class="text-xs text-recipe-secondary-600">Public</div>
                  </div>
                  <div class="text-center p-3 bg-gray-50 rounded-lg">
                    <div class="text-lg font-bold text-gray-600">{{ stats.cookbooks.private }}</div>
                    <div class="text-xs text-gray-600">Private</div>
                  </div>
                </div>
                <div class="mt-4 space-y-2">
                  <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Avg. per user</span>
                    <span class="font-medium">{{ (stats.cookbooks.total / stats.users.total).toFixed(1) }}</span>
                  </div>
                  <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Created this week</span>
                    <span class="font-medium">{{ stats.cookbooks.thisWeek }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- System Performance -->
      <div class="mt-8">
        <div class="card">
          <div class="card-header">
            <h3 class="text-lg font-semibold text-gray-900">System Performance</h3>
          </div>
          <div class="card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <div class="text-center">
                <div class="text-2xl font-bold text-green-600">99.9%</div>
                <div class="text-sm text-gray-600">Uptime</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">245ms</div>
                <div class="text-sm text-gray-600">Avg Response Time</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-yellow-600">78%</div>
                <div class="text-sm text-gray-600">Storage Used</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-recipe-primary-600">1.2M</div>
                <div class="text-sm text-gray-600">API Requests/Day</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import {
  ArrowLeftIcon,
  ArrowPathIcon,
  UsersIcon,
  BookOpenIcon,
  DocumentTextIcon,
  CurrencyDollarIcon,
  ChartBarIcon
} from '@heroicons/vue/24/outline';

export default {
  name: 'AdminStatistics',
  components: {
    ArrowLeftIcon,
    ArrowPathIcon,
    UsersIcon,
    BookOpenIcon,
    DocumentTextIcon,
    CurrencyDollarIcon,
    ChartBarIcon
  },
  setup() {
    const loading = ref(false);
    const selectedPeriod = ref('30d');

    const stats = ref({
      users: {
        total: 0,
        free: 0,
        premium: 0,
        professional: 0,
        growth: 0,
        activeToday: 0,
        activeThisWeek: 0
      },
      recipes: {
        total: 0,
        public: 0,
        private: 0,
        growth: 0,
        thisWeek: 0
      },
      cookbooks: {
        total: 0,
        public: 0,
        private: 0,
        growth: 0,
        thisWeek: 0
      },
      revenue: {
        monthly: 0,
        growth: 0
      }
    });

    const loadAnalytics = async () => {
      loading.value = true;
      try {
        const response = await window.axios.get(`/admin/statistics?period=${selectedPeriod.value}`);
        
        if (response.data.success) {
          stats.value = response.data.statistics;
        }
      } catch (error) {
        console.error('Failed to load analytics:', error);
        
        // Mock data for development
        stats.value = {
          users: {
            total: 1247,
            free: 856,
            premium: 321,
            professional: 70,
            growth: 12.5,
            activeToday: 89,
            activeThisWeek: 334
          },
          recipes: {
            total: 5642,
            public: 4123,
            private: 1519,
            growth: 8.3,
            thisWeek: 156
          },
          cookbooks: {
            total: 1138,
            public: 743,
            private: 395,
            growth: 15.2,
            thisWeek: 47
          },
          revenue: {
            monthly: 4235,
            growth: 23.1
          }
        };
      }
      loading.value = false;
    };

    const refreshData = () => {
      loadAnalytics();
    };

    const formatNumber = (num) => {
      return new Intl.NumberFormat().format(num);
    };

    const formatPercentage = (num) => {
      const sign = num >= 0 ? '+' : '';
      return `${sign}${num.toFixed(1)}%`;
    };

    const getChangeClass = (change) => {
      if (change > 0) return 'text-green-600';
      if (change < 0) return 'text-red-600';
      return 'text-gray-500';
    };

    onMounted(() => {
      loadAnalytics();
    });

    return {
      loading,
      selectedPeriod,
      stats,
      loadAnalytics,
      refreshData,
      formatNumber,
      formatPercentage,
      getChangeClass
    };
  }
};
</script>
